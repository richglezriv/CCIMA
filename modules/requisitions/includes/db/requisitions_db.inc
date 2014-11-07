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
	$sql = "INSERT INTO ".TB_PREF."requisitions (point_of_use, narrative, details) VALUES (".
		db_escape($point_of_use).",".db_escape($narrative).",".db_escape($details).")";

	db_query($sql, "could not add requisitions");
}

function update_requisition($selected_id, $point_of_use, $narrative, $details)
{
	$sql = "UPDATE ".TB_PREF."requisitions SET point_of_use=".db_escape($point_of_use)
			.", narrative=".db_escape($narrative)
			.", details=".db_escape($details)
			."WHERE requisition_id=".db_escape($selected_id);

	db_query($sql, "could not update Requisition");
}

function get_all_requisitions($all=false)
{
	$sql = "SELECT * FROM ".TB_PREF."requisitions WHERE (completed = 0)";
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
	$sql = "UPDATE ".TB_PREF."requisitions SET completed =  1 WHERE requisition_id=".db_escape($selected_id);

	db_query($sql, "could not complete Requisition");
}

function generate_po()
{
	$sql = "SELECT ".TB_PREF."generate_po()";

	$result = db_query($sql, "could not process Requisition to Purchase Order");
	$row = db_fetch($result);
	return $row[0] == 'Done';
}

function requisitions_in_details($selected_id)
{
	$sql="SELECT COUNT(*) FROM ".TB_PREF."requisition_details WHERE requisition_id=".db_escape($selected_id);

	$result = db_query($sql, "could not query assets");
	$myrow = db_fetch_row($result);
	return ($myrow[0] > 0); 
}

//valida si se puede generar la orden de compra con base a un parametro maximo
function puede_procesar(){
    
    $total = 0;
    $sql = "SELECT SUM(price) AS 'total' FROM ".TB_PREF."requisition_details WHERE lpo_id = 0";
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
        notificaAutorizacionRequisicion();
        return false;
    }
    

    

}

//notifica la autorizacion de la requisicion
function notificaAutorizacionRequisicion(){

    // Enviarlo
    global $path_to_root;
    $seccionMensaje = getItemsPendientesReq();
    $liga = "";
    $motivoCorreo = "Autorizacion de Requisicion";
    $descripcion = "Solicitud de autorizacion de orden de compra para los items requeridos";
    
    require_once($path_to_root . "/includes/db/utilities.inc");
    require_once($path_to_root.'/includes/sysnames.inc');
    //generacion de liga
    $liga = get_serveruri() . 'remotos/requisitions.php?po=f6122c971aeb03476bf01623b09ddfd4';
    $notificacion = notifica_autorizacion($liga, $motivoCorreo, $descripcion, $seccionMensaje);
    //echo($notificacion);
}

function getItemsPendientesReq(){

    $sql = "SELECT * FROM ".TB_PREF."item_codes ic INNER JOIN ".TB_PREF."requisition_details rd ON ic.item_code = rd.item_code
	INNER JOIN ".TB_PREF."requisitions r ON rd.requisition_id = r.requisition_id
	LEFT JOIN ".TB_PREF."suppliers s ON rd.supplier_id = s.supplier_id
	WHERE (r.completed = 1) AND (rd.lpo_id = 0)
	ORDER BY r.requisition_id";
    $result = db_query($sql, "could not get all requisition_details");
    $html = "<table style='width:100%;'>";
    $html .= "<tr style='font-weight:bold;'>";
    $html .= "<td>Utilidad</td>";
    $html .= "<td>Cantidad</td>";
    $html .= "<td>Precio</td>";
    $html .= "<td>Proveedor</td></tr>";

    while ($myrow = db_fetch($result)) 
    {
	    $html .= "<tr>";
        
        $html .= "<td>".$myrow["point_of_use"]."</td>";
        $html .= "<td>".$myrow["quantity"]."</td>";
        $html .= "<td>".$myrow["price"]."</td>";
        $html .= "<td>".$myrow["supp_name"]."</td>";

	    $html .= "</tr>";
    }
    
    $html .= "</table>";

	return $html;
}
?>
