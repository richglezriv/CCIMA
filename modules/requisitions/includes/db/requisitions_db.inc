<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
function add_requisition($point_of_use, $narrative, $details)
{
	$sql = "INSERT INTO ".TB_PREF."requisitions (point_of_use, narrative, details, genera) VALUES (".
		db_escape($point_of_use).",".db_escape($narrative).",".db_escape($details).",".db_escape($_SESSION["wa_current_user"]->username).")";

	db_query($sql, "could not add requisitions");

    global $path_to_root;    
    require_once($path_to_root . "/includes/db/utilities.inc");
    $resultado = notifica_nueva_requisicion($point_of_use);

}

function update_requisition($selected_id, $point_of_use, $narrative, $details)
{
	$sql = "UPDATE ".TB_PREF."requisitions SET point_of_use=".db_escape($point_of_use)
			.", narrative=".db_escape($narrative)
			.", details=".db_escape($details)
			."WHERE requisition_id=".db_escape($selected_id);

	db_query($sql, "could not update Requisition");

    global $path_to_root;
    require_once($path_to_root . "/includes/db/utilities.inc");
    $resultado = notifica_nueva_requisicion($point_of_use);
    echo "se logro".$resultado;
}

function get_all_requisitions($all=false)
{
	$sql = "SELECT * FROM ".TB_PREF."requisitions WHERE (completed = 0) AND (autorizada = 0) AND (genera = ".db_escape($_SESSION["wa_current_user"]->username).")";
	if (!$all) $sql .= " AND !inactive";

	return db_query($sql, "could not get all requisitions");
}

function get_one_requisition($selected_id)
{
	$sql = "SELECT * FROM ".TB_PREF."requisitions WHERE requisition_id=".db_escape($selected_id);

	return db_query($sql, "could not get Requisition");
}

function get_requisition($selected_id)
{
	$sql = "SELECT * FROM ".TB_PREF."requisitions WHERE requisition_id=".db_escape($selected_id);

	$result = db_query($sql, "could not get Requisition");

	return db_fetch($result);
}

function delete_requisition($selected_id)
{
	$sql="DELETE FROM ".TB_PREF."requisitions WHERE requisition_id=".db_escape($selected_id);

	db_query($sql, "could not delete Requisition");
}

function complete_requisition($selected_id)
{
	//$sql = "UPDATE ".TB_PREF."requisitions SET completed =  1 WHERE requisition_id=".db_escape($selected_id);

	//db_query($sql, "could not complete Requisition");

    notificaRequisicionCompletada($selected_id);
}

function generate_po($idRequisicion='')
{
    error_log('generando oc para requisicion '.$idRequisicion);
	$sql = "SELECT ".TB_PREF."generate_po(".db_escape($idRequisicion).")";

	$result = db_query($sql, "could not process Requisition to Purchase Order");
	$row = db_fetch($result);
	return $row[0] == 'Done';
}

function confirmaRequisicion($req){

    $sql = "UPDATE ".TB_PREF."requisitions SET autorizada = 1, completed = 1 WHERE requisition_id = ".db_escape($req);
    db_query($sql, "could not update Requisition");
}

function requisitions_in_details($selected_id)
{
	$sql="SELECT COUNT(*) FROM ".TB_PREF."requisition_details WHERE requisition_id=".db_escape($selected_id);

	$result = db_query($sql, "could not query assets");
	$myrow = db_fetch_row($result);
	return ($myrow[0] > 0); 
}

//valida si se puede generar la orden de compra con base a un parametro maximo
function puede_procesar($idRequisicion=''){
    
    $total = 0;
    $sql = "SELECT SUM(price*quantity) AS 'total' FROM ".TB_PREF."requisition_details WHERE lpo_id = 0";
    if ($idRequisicion != ''){
        $sql.= " AND requisition_id = ".db_escape($idRequisicion);
    }
    
    $result = db_query($sql, "no se pudo obtener el total de la requisicion");
    $myrow = db_fetch_row($result);
    
    $total = $myrow[0];
    
    $sql = "SELECT value FROM ".TB_PREF."sys_prefs WHERE name = 'max_req'";
    
    $result = db_query($sql, "no se pudo obtener el parametro maximo de monto");
    $myrow = db_fetch_row($result);

    $parametro = $myrow[0];
    
    if ($_SESSION["wa_current_user"]->access == 10 || $_SESSION["wa_current_user"]->access == 2){
        return true;
    }
    else if ($parametro > $total){
        return true;
    }
    else {
        notificaAutorizacionRequisicion($idRequisicion);
        return false;
    }
    

    

}

//notifica la autorizacion de la requisicion
function notificaAutorizacionRequisicion($idRequisicion=''){

    global $path_to_root, $SERVER_URI;
    $seccionMensaje = getItemsPendientesReq($idRequisicion);
    $liga = "";
    $motivoCorreo = "Autorizacion de Requisicion";
    $descripcion = "Solicitud de autorizacion de orden de compra para los items requeridos";
    
    require_once($path_to_root . "/includes/db/utilities.inc");
    //generacion de liga
    $liga = $SERVER_URI . 'remotos/requisitions.php?po=f6122c971aeb03476bf01623b09ddfd4&req='.$idRequisicion;

    $notificacion = notifica_autorizacion($liga, $motivoCorreo, $descripcion, $seccionMensaje);
    //echo($notificacion);
}

function notificaRequisicionCompletada($idRequisicion=''){

    global $path_to_root, $SERVER_URI;
    $seccionMensaje = getItemsRequeridos($idRequisicion);
    $liga = $SERVER_URI . 'remotos/autorizar.php?re='.$idRequisicion;
    $motivoCorreo = "Autorizacion de solicitud de requisicion";
    $descripcion = "Solicitud de autorizacion de requisicion para los items a continuacion";
    
    //obtiene el correo del jefe
    $sql = " SELECT us.correo_jefe 
            FROM ".TB_PREF."users us INNER JOIN ".TB_PREF."requisitions re ON re.genera = us.user_id 
            WHERE re.requisition_id=".db_escape($idRequisicion);

    error_log($sql);
    $result = db_query($sql, "imposible obtener el email de los roles");
    $correo = '';

    while($row = db_fetch_row($result)){
         $correo .= $row[0] . ",";
    }

    require_once($path_to_root . "/includes/db/utilities.inc");
    //$notificacion = notifica_requisicion_completada($seccionMensaje, $_SESSION["wa_current_user"]->username);
    $notificacion = notifica_autorizacion($liga, $motivoCorreo, $descripcion, $seccionMensaje, $correo);
    
}

function getItemsRequeridos($idRequisicion=''){
    $sql = "SELECT ic.description, rd.order_quantity, rd.purpose, rd.estimate_price, (rd.estimate_price * rd.order_quantity) as total 
            FROM ".TB_PREF."item_codes ic INNER JOIN ".TB_PREF."requisition_details rd ON ic.item_code = rd.item_code
	        INNER JOIN ".TB_PREF."requisitions r ON rd.requisition_id = r.requisition_id
	        LEFT JOIN ".TB_PREF."suppliers s ON rd.supplier_id = s.supplier_id
	        WHERE (r.completed = 0) AND (rd.lpo_id = 0)";

    
    if ($idRequisicion != ''){
        $sql.= " AND r.requisition_id=".db_escape($idRequisicion);
    }

    $sql.= " ORDER BY r.requisition_id";

    $result = db_query($sql, "could not get all requisition_details");
    $html = "<table style='width:100%;'>";
    $html .= "<tr style='font-weight:bold;border:1px solid black;background-color:#E6E6E6;'>";
    $html .= "<td>Cantidad</td>";
    $html .= "<td>Descripcion</td>";
    $html .= "<td>Proposito</td>";
    $html .= "<td>Precio estimado</td>";
    $html .= "<td>Total</td></tr>";
    

    while ($myrow = db_fetch($result)) 
    {
	    $html .= "<tr style='border:1px solid black;'>";
        
        $html .= "<td>".$myrow["order_quantity"]."</td>";
        $html .= "<td>".$myrow["description"]."</td>";
        $html .= "<td>".$myrow["purpose"]."</td>";
        $html .= "<td>".$myrow["estimate_price"]."</td>";
        $html .= "<td>".$myrow["total"]."</td>";
	    $html .= "</tr>";
    }
    
    $html .= "</table>";

	return $html;
}

function getItemsPendientesReq($idRequisicion=''){

    $total;
    $granTotal = 0.0;
    $sql = "SELECT rd.purpose, rd.quantity, rd.price, s.supp_name, ic.description FROM ".TB_PREF."item_codes ic INNER JOIN ".TB_PREF."requisition_details rd ON ic.item_code = rd.item_code
	INNER JOIN ".TB_PREF."requisitions r ON rd.requisition_id = r.requisition_id
	LEFT JOIN ".TB_PREF."suppliers s ON rd.supplier_id = s.supplier_id
	WHERE (r.completed = 1) AND (rd.lpo_id = 0) AND (r.requisition_id=".db_escape($idRequisicion).")";

    $sql.= " ORDER BY r.requisition_id";

    $result = db_query($sql, "could not get all requisition_details");
    $html = "<table style='width:100%;' >";
    $html .= "<tr style='font-weight:bold;border:1px solid black;'>";
    $html .= "<td>Cantidad</td>";
    $html .= "<td>Descripcion</td>";
    $html .= "<td>Precio</td>";
    $html .= "<td>Utilidad</td>";
    $html .= "<td>Proveedor</td>";
    $html .= "<td>Total</td></tr>";

    while ($myrow = db_fetch($result)) 
    {
	    $html .= "<tr style='border:1px solid black;'>";
        
        $html .= "<td style='border:1px solid black;'>".$myrow["quantity"]."</td>";
        $html .= "<td style='border:1px solid black;'>".$myrow["description"]."</td>";
        $html .= "<td style='border:1px solid black;'>".$myrow["price"]."</td>";
        $html .= "<td style='border:1px solid black;'>".$myrow["purpose"]."</td>";
        $html .= "<td style='border:1px solid black;'>".$myrow["supp_name"]."</td>";
        $total = $myrow["price"] * $myrow["quantity"];
        $html .= "<td style='border:1px solid black;'>".number_format($total, 2, '.', ',')."</td>";

	    $html .= "</tr>";
        $granTotal += $total;
    }
    
    //TOTALES
    $iva = $granTotal * .16;
    $html .= "<tr><td colspan='5' style='text-align:right;'>SubTotal</td>";
    $html .= "<td style='border:1px solid black;'>".number_format($granTotal, 2, '.', ',')."</td></tr>";
    $html .= "<tr><td colspan='5' style='text-align:right;'>IVA</td>";
    $html .= "<td style='border:1px solid black;'>".number_format($iva, 2, '.', ',')."</td></tr>";
    $html .= "<tr><td colspan='5' style='text-align:right;'>Total</td>";
    $html .= "<td style='border:1px solid black;'>".number_format(($iva + $granTotal), 2, '.', ',')."</td></tr>";

    $html .= "</table>";

	return $html;
}
?>
