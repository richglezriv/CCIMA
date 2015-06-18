<?php
$path_to_root = "..";
$page_security = 'SA_SALESORDER';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui/contacts_view.inc");

include_once($path_to_root . "/includes/session.inc");

class Cotizador{
    var $concepto;
    var $unidad;
    var $cantidad;
    var $unitario;
    var $importe;

    function Cotizador($c){
        $this->concepto = $c;
    }

    function calcula_importe(){
        $this->importe = ($this->cantidad * $this->unitario);
    }
}

$folio = "";
$vuelta = $_POST['vuelta'];
$js = '';
if ($use_date_picker) {
	$js .= get_js_date_picker();
}
//encabezado y notas
if ($_POST["txtHeader"] == ''){
    $_POST["txtHeader"] = "POR MEDIO DE LA PRESENTE TENEMOS EL GUSTO DE PRESENTAR A USTED LA SIGUIENTE COTIZACION REFERENTE A: ";
}

if ($_POST['notas'] == ''){
    $_POST['notas'] = utf8_decode("NOTAS: EL PRECIO ES MAS I.V.A.

TIEMPO DE ENTREGA: NOS AJUSTAMOS A SUS NECESIDADES.

FORMA DE PAGO: 50% ANTICIPO, 30% CONTRA AVISO DE ENTREGA DE MATERIALES Y 20% CONTRA AVANCE.

LOS CONCEPTOS NO INCLUIDOS EN ESTE PRESUPUESTO SERAN COTIZADOS POR SEPARADO.

PRECIOS ESTAN SUJETOS A CAMBIO SIN PREVIO AVISO.

LA ENERGIA ELECTRICA SERÁ SUMINISTRADA POR EL CLIENTE.

EL CLIENTE SERÁ RESPONSABLE DE REALIZAR TODOS LOS TRÁMITES DE LICENCIAS Y PERMISOS DE CONSTRUCCIÓN.

LOS PAGOS REALIZADOS: ANTICIPOS, DURANTE EL PROCESO DE EJECUCION, FINIQUITOS,Y/O CUALQUIER OTRA TRANSACCIÓN, DEBERAN SER DEPOSITADOS Y/O TRANSFERIDOS A LA CUENTA DE GRUPO CCIMA SA DE CV O A LADE RICARDO MEDINA EXCLUSIVAMENTE. EN CASO DE SER PAGO EN EFECTIVO DEBERA HACERSE DIRECTAMENTE EN LAS OFICINAS DE “GRUPO CCIMA SA DE CV”. NO SE TOMARA EN CUENTA CUALQUIER TIPO DE PAGO QUE SEA ENTREGADO DIRECTAMENTE AL VENDEDOR SIN PREVIO AVISO POR PARTE DEL CLIENTE Y SOLO CON AUTORIZACION DEL DEPARTAMENTO DE COBRANZA.

SIN MÁS POR EL MOMENTO Y EN ESPERA DE PODER BRINDARLE NUESTROS SERVICIOS QUEDA A SUS ÓRDENES PARA CUALQUIER ACLARACION O DUDA:");
}

//registra cotizacion
if ($_POST["registrar"] == "Registrar Cotizacion"){
    
    $idCotizacion = 0;
    $date = date2sql($_POST['fechaCotizacion']);
    $folio = genera_folio_cotizacion();
    
    $sql = '';
    
    if ($_POST['vuelta'] != ''){
        $sql = "select * from 0_tb_cotizador where id = ".db_escape($_POST['vuelta']);
        $result = db_query($sql);
        $row = db_fetch($result);

        $folio = genera_siguiente_folio_cotizacion($row['folio']);
        
    }

        
    
    $sql = "INSERT INTO ".TB_PREF."tb_cotizador (fecha, folio, customer_id, salesman_id, contact_id, header, notes) VALUES (".db_escape($date).
    ",".db_escape($folio).",".db_escape($_POST['customer_id']).",".$_POST['vendedor'].",".$_POST['radSeleccion'].",'".
    $_POST['txtHeader']."','".utf8_encode($_POST['notas'])."')";
    
    db_query($sql);

    $arreglo = $_SESSION["items"];

    $sql = "SELECT id FROM ".TB_PREF."tb_cotizador WHERE folio = ".db_escape($folio);
    
    $result = db_query($sql);
    $idCotizacion = db_fetch_row($result);

    foreach($arreglo as $registro){
        $concept = utf8_encode($registro->concepto);
        error_log($concept);
        $sql = "INSERT INTO ".TB_PREF."tb_datos_cotizados (cotizador_id, concepto, unidad, cantidad, precio) VALUES (".
        db_escape($idCotizacion[0]).",'".$concept."',".db_escape($registro->unidad).",".
        db_escape($registro->cantidad).",".db_escape($registro->unitario).")";
        error_log($sql);
        db_query($sql);

        error_log($sql);
    }

    echo '<script>window.open("repCotizador.php?cot='.$idCotizacion[0].'&firma='.$_POST['radFirma'].'");</script>';    
}

function beginSection(){
    
    echo '<div style="margin:10px 0px 20px 0px;">';
}

function endSection(){
    
    echo '</div>';
}

function GetVendedores(){
    $result = get_salesmen(TRUE);
    
    echo'<td><select id="vendedor" name="vendedor">';
    
    while ($myrow = db_fetch($result))
    {
        $selected = $_POST['vendedor'] == $myrow["salesman_code"] ? "selected" : "";
        echo '<option '.$selected.' value="'.$myrow["salesman_code"].'">'.$myrow["initials"].'</option>';
    }

    echo'</select></td>';
}
$cabecera = user_company() == 1? "Cotizador de Servicio" : "Cotizador de Obra";
page($cabecera, false, false, "", $js);
start_form();


if ($_REQUEST['cot'] != '' && $_POST['vuelta'] == ''){

    $sql = "select * from 0_tb_cotizador where id = ".db_escape($_REQUEST['cot']);
    $result = db_query($sql);
    $row = db_fetch($result);

    $_POST['txtHeader'] = $row['header'];
    $_POST['notas'] = $row['notes'];
    $_POST['customer_id'] = $row['customer_id'];
    $folio = $row['folio'];
    $fecha = new DateTime($row['fecha']);
    $_POST['fechaCotizacion'] = date_format($fecha, 'm/d/Y');
    $_POST['vendedor'] = $row['salesman_id'];
    
}


beginSection();
start_table(1, 'width=700px');
start_row();
date_cells(_("Fecha"), 'fechaCotizacion', '', null, -30);
echo '<td style="width=15%;">Folio</td>';
echo '<td style="width=30%;">'.$folio.'</td>';
end_row();

start_row();
customer_list_cells(_("Select a customer: "), 'customer_id', null, true, true);
echo '<td>Vendedor</td>';
//vendedor
GetVendedores();
end_row();

end_table();

//contactos de clientes
if (get_post('customer_id')){
    
    $contacts = new contacts('contacts', $_POST['customer_id'], 'customer');
	$contacts->listado_cotizador($_POST['radSeleccion']);
    
}
endSection();

beginSection();
start_table(1, "width=700px;");
echo '<tr><td style="text-align:left;"><h4>Presente</h4></td></tr>';
echo '<tr><td ><textarea style="width:90%;height:40px;" id="txtHeader" name="txtHeader" >'.$_POST['txtHeader'].'</textarea></tr>';

end_table();
endSection();
start_table(1, 'width=700px');
start_row();
echo '<td class="label">Concepto</td>';
echo '<td class="label">Unidad</td>';
echo '<td class="label">Cantidad</td>';
echo '<td class="label">P.U.</td>';
echo '<td class="label">&nbsp;</td>';

end_row();
start_row();
echo '<td><textarea style="width: 300px;height: 50px;text-transform: uppercase" id="txtConcepto" name="txtConcepto"></textarea></td>';
echo '<td><input type="text" style="width: 50px;text-transform: uppercase" id="unidad" name="unidad"/></td>';
echo '<td><input type="text" style="width: 50px;" id="cantidad" name="cantidad"/></td>';
echo '<td><input type="text" style="width: 50px;" id="unitario" name="unitario"/></td>';
echo '<td ><input type="submit" id="btnOpcion" name="btnOpcion" class="button" value="Agregar" /></td>';
end_row();
end_table();

echo '<br/><br/>';

if ($_POST["btnOpcion"] == "Agregar"){

    $notas = $_POST['notas'];
    $encabezado = $_POST['txtHeader'];

    if (count($_SESSION["items"]) == 0){
        $arreglo = array();
    }
    else{
        $arreglo = $_SESSION["items"];
    }
    $indice = count($arreglo);
    $cotizador = new Cotizador(strtoupper($_POST['txtConcepto']));
    $cotizador->unidad = strtoupper($_POST['unidad']);
    $cotizador->cantidad = $_POST['cantidad'];
    $cotizador->unitario = $_POST['unitario'];
    $cotizador->calcula_importe();

    $arreglo[$indice] = $cotizador;

    $_SESSION["items"] = $arreglo;

    GeneraTabla();
    

}
else if ($_POST["btnOpcion"] == "Eliminar"){

    $arreglo = $_SESSION["items"];

    foreach($_POST['chkEliminar'] as $check) {
            unset($arreglo[$check]);
    }

    $_SESSION["items"] = $arreglo;

    GeneraTabla();
}
else{
    unset($_SESSION["items"]);
}


if ($_REQUEST['cot'] != '' && $_POST['vuelta'] == ''){
    $sql = "select * from 0_tb_datos_cotizados where cotizador_id = ".db_escape($_REQUEST['cot']);
    $result = db_query($sql);
    $vuelta = $_REQUEST['cot'];
    $arreglo = array();

    while ($myrow = db_fetch($result)){
        $indice = count($arreglo);
        $cotizador = new Cotizador($myrow['concepto']);
        $cotizador->unidad = $myrow['unidad'];
        $cotizador->cantidad = $myrow['cantidad'];
        $cotizador->unitario = $myrow['precio'];
        $cotizador->calcula_importe();

        $arreglo[$indice] = $cotizador;
    }

    
    $_SESSION["items"] = $arreglo;
    
    GeneraTabla();
}

function GeneraTabla(){

    $arreglo = $_SESSION["items"];
    $indice = 0;

    start_table(2, 'width=750px');
    start_row();
    echo '<td class="label">Concepto</td>';
    echo '<td class="label">Unidad</td>';
    echo '<td class="label">Cantidad</td>';
    echo '<td class="label">P.U.</td>';
    echo '<td class="label">Importe</td>';
    echo '<td class="label">&nbsp;</td>';
    end_row();

   
        
    foreach($arreglo as $registro){
        start_row();
        echo '<td>'.$registro->concepto.'</td>';
        echo '<td>'.$registro->unidad.'</td>';
        echo '<td>'.number_format2($registro->cantidad, 2).'</td>';
        echo '<td>$&nbsp;'.price_format($registro->unitario).'</td>';
        echo '<td>$&nbsp;'.price_format($registro->importe).'</td>';
        echo '<td><input type="checkbox" id="chkEliminar[]" name="chkEliminar[]" value="'.$indice.'"/></td>';
        end_row();
        $indice += 1;
    }

    start_row();
    echo '<td colspan="6" style="text-align:right;"><input type="submit" value="Eliminar" id="btnOpcion" name="btnOpcion" class="button"/> </td>';
    end_row();

    end_table();
}

//NOtas
beginSection();
start_table(1, "width=700px;");
echo '<tr><td style="text-align:left;"><h4>Notas</h4></td></tr>';
echo '<tr><td ><textarea name="notas" id="notas" style="width:90%;height:100px;" >'.$_POST['notas'].'</textarea></tr>';

end_table();
endSection();

beginSection();
start_table(1, "width=700px;");
echo '<tr><td style="text-align:left;"><h4>Firma</h4></td>';
echo '<td>';
echo 'Federico Benitez Aguilar&nbsp;<input type="radio" value="0" name="radFirma" id="radFirma"/></td>';
echo '<td>Ricardo Medina Rojas&nbsp;<input type="radio" value="1" name="radFirma" id="radFirma"/>';
echo '</td></tr>';
end_table();
endSection();

//grua a utilizar
if (user_company() == 1){
    beginSection();
    start_table(1, "width=700px;");
    echo '<tr><td style="text-align:left;"><h4>Equipo a utilizar</h4></td>';
    stock_items_list_cells(null, 'stock_id', null,
	      FALSE, FALSE, FALSE);
    echo '</tr>';
    endSection();
}


start_table(0,"width:120px");
start_row();
echo '<td><input type="submit" value="Registrar Cotizacion" id="registrar" name="registrar"/></td>';

end_row();
end_table();
echo ('<input type="hidden" id="vuelta" value="'.$vuelta.'" name="vuelta">');
end_form();
end_page();
?>