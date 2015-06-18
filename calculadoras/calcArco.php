<?php
$page_security = 'SA_OPEN';
$path_to_root = "..";
include($path_to_root . "/includes/db_pager.inc");
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include($path_to_root . "/calculadoras/includes/db/creacion_db.inc");

simple_page_mode(true);
$_SESSION['page_title'] = "Calculadora de Arco";
$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
if ($use_date_picker)
	$js .= get_js_date_picker();

    page($_SESSION['page_title'], false, false, "", $js);
	start_form();

function getColeccion($sql){
    $objeto = array();
    $objetos = array();
    $indice = 0;
    
    $result = db_query($sql,"No hay informacion");
    
    while($row = db_fetch($result)){
        $cadena = str_replace('"'," pulg.", $row[1]);
        $objeto = array('stockId'=>$row[0],'description'=>$cadena,'price'=>$row[2]);
        $objetos[$indice] = $objeto;
        $indice += 1;
    }
    return $objetos;
}

if (isset($_POST['btnSubmit'])){

    $objetos = html_entity_decode($_POST['jsObject']);
    $jsObjects = json_decode($objetos, TRUE);
    
    //var_dump($jsObjects);

    //registrar datos de calculadora
    $sql = "INSERT INTO 0_calculadora_registro (nombre,tipo) VALUES (".db_escape($_POST['nombreCalculadora']).",1)";
    db_query($sql);
    $id = mysql_insert_id();

    //cabeceras de concepto
    insert_section($id, $_POST['concepto1']);
    insert_section($id, $_POST['concepto2']);
    insert_section($id, $_POST['concepto3']);
    insert_section($id, $_POST['concepto4']);

    //inserta los articulos
    insert_item($id, $jsObjects['lamina'], $jsObjects['cantidadLamina']);
    insert_item($id, $jsObjects['roladora'], $jsObjects['diasRoladora']);
    insert_item($id, $jsObjects['grua'], $jsObjects['diasGrua']);
    insert_item($id, $jsObjects['placas'], $jsObjects['cantidadPlacas']);
    insert_item($id, $jsObjects['tornillos'], $jsObjects['cantidadTornillos']);
    insert_item($id, 'VIATICOS', $jsObjects['viaticosDias'], $jsObjects['viaticosCosto']);
    
    global $SERVER_URI;
    $uri = $SERVER_URI.'sales/sales_order_entry.php?NewQuotation=Yes&Ref='.$id;
    header('Location: '.$uri, TRUE);
    
}

function insert_section($id, $sectionName){
    
    $sql = "INSERT INTO 0_calculadora_concepto (concepto, id_calc_reg) VALUES (".db_escape($sectionName).",".db_escape($id).")";
    db_query($sql);
}

function insert_item($id, $stock_id, $qty='0', $price='0'){
    $sql = "INSERT INTO 0_calculadora_items (stock_id, cantidad, precio, id_calc_reg) VALUES (".
        db_escape($stock_id).",".
        db_escape($qty).",".
        db_escape($price).",".
        db_escape($id).")";

    db_query($sql);
}

function getCalibres(){
    $objeto = array();
    $objetos = array();
    $indice = 0;
    $sql = "SELECT * FROM 0_pesos_y_calibres";   
    $result = db_query($sql,"No hay informacion");
    
    while($row = db_fetch($result)){
        $objeto = array('inicial'=>$row[0],'final'=>$row[1],'calibre'=>$row[2],'peso'=>$row[3]);
        $objetos[$indice] = $objeto;
        $indice += 1;
    }
    return $objetos;
}

$calibres = json_encode(getCalibres());
$laminas = json_encode(getColeccion("SELECT sm.stock_id, sm.description, pr.price 
            FROM 0_stock_master sm 
            LEFT JOIN 0_prices pr on sm.stock_id = pr.stock_id
            WHERE sm.stock_id like 'Lam%'
            and pr.sales_type_id = 1"));
$roladoras = json_encode(getColeccion("SELECT sm.stock_id, sm.description, pr.price 
            FROM 1_stock_master sm 
            LEFT JOIN 1_prices pr on sm.stock_id = pr.stock_id
            WHERE sm.stock_id like 'MQR%'
            and pr.sales_type_id = 4"));
$gruas = json_encode(getColeccion("SELECT sm.stock_id, sm.description, pr.price 
            FROM 1_stock_master sm 
            LEFT JOIN 1_prices pr on sm.stock_id = pr.stock_id
            WHERE sm.stock_id like 'T%'
            and pr.sales_type_id = 3"));
$tornillos = json_encode(getColeccion("SELECT sm.stock_id, sm.description, pr.price 
            FROM 0_stock_master sm 
            LEFT JOIN 0_prices pr on sm.stock_id = pr.stock_id
            WHERE sm.stock_id like 'Torn%'
            and pr.sales_type_id = 1"));
$placas = json_encode(getColeccion("SELECT sm.stock_id, sm.description, pr.price 
            FROM 0_stock_master sm 
            LEFT JOIN 0_prices pr on sm.stock_id = pr.stock_id
            WHERE sm.stock_id like 'pla%'
            and pr.sales_type_id = 1"));
?>
<style>
    .prices{
        width: 50px;
    }
    .concepto{
        width: 500px;
    }
    .section{
        margin-bottom: 20px;
        border-bottom: 1px solid #212c80;
        width: 100%;
    }
    .col1{
        width:200px;
    }
    .col2{width: 200px;}
    .no-border{border: none;}
</style>
<div style="width: 90%;margin:0 auto;">
    <table class="section no-border">
        <tr>
            <td class="col1 label">Nombre de calculadora</td>
            <td class="col2"><input type="text" style="width: 300px;" name="nombreCalculadora" id="nombreCalculadora" /></td>
            <td>&nbsp;</td>
        </tr>
    </table>

    <table class="section">
        <tr >
            <td class="label">Concepto de grupo</td>
            <td colspan="7"><textarea id="concepto1" name="concepto1" class="concepto"></textarea></td>
        </tr>
        <tr>
		<td class="col1 label">Ancho</td>
		<td class="col2">
			<input name="ancho" type="text" id="ancho"/>&nbsp;mt.
		</td>
		<td>&nbsp;</td>
        <td>&nbsp;</td>
        <td class="label">Longitud de lamina por cada arco</td>
		<td><span id="longitud"></span></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="label">Largo</td>
		<td>
			<input name="largo" type="text" id="largo"/>&nbsp;mt.
		</td>
		<td>&nbsp;</td>
        <td>&nbsp;</td>
        <td class="label">Numero de arcos</td>
		<td><span id="arcos"></span></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="label">Calibre</td>
		<td><select id="calibres" name="calibres"></select></td>
		<td>&nbsp;</td>
        <td>&nbsp;</td>
        <td class="label">Lamina necesaria para obra</td>
		<td><span id="laminaNecesaria"></span></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="label">Peso segun calibre</td>
		<td><span id="pesoCalibre" ></span></td>
		<td>&nbsp;</td>
        <td>&nbsp;</td>
        <td class="label">Peso total de lamina</td>
		<td><span id="pesoTotal"></span></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="label">Altura de desplante de arco</td>
		<td><input name="Text3" type="text" />
		</td>
		<td>&nbsp;</td>
        <td>&nbsp;</td>
        <td class="label">Costo de lamina</td>
		<td><select id="catalogoLaminas" name="catalogoLaminas"></select></td>
		<td><span id="costoLamina"></span></td>
	</tr>
	<tr>
		<td class="label">Cantidad de naves a cubrir</td>
		<td>
			<input name="naves" id="naves" type="text" />
		</td>
		<td>&nbsp;</td>
        <td>&nbsp;</td>
        <td class="label">Costo directo de lamina</td>
		<td><span id="costoDirecto" ></span></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="label">Area a cubrir</td>
		<td><span id="area" ></span></td>
		<td>&nbsp;</td>
	</tr>
</table>
<table class="section">
    <tr>
            <td class="label">Concepto de grupo</td>
            <td colspan="7"><textarea id="concepto2" name="concepto2" class="concepto"></textarea></td>
    </tr>
    <tr>
        <td class="col1 label">Roladora</td>    
        <td class="col2"><select id="catalogoRoladoras" name="catalogoRoladoras"></select></td>
        <td class="prices"><span id="precioRoladora"></span></td>
        <td>&nbsp;</td>
        <td class="label">Distancia de la obra</td>
        <td><input type="text" id="distancia" name="distancia" /></td>
    </tr>
	<tr>
		<td class="label">Dias de roladora</td>
		<td><input type="text" id="diasRoladora" name="diasRoladora" /></td>
		<td>&nbsp;</td>
        <td>&nbsp;</td>
        <td class="label">Costo de flete</td>
		<td><span id="costoFlete"></span></td>
		<td>&nbsp;</td>
	
	</tr>
	<tr>
		<td class="label">Costo rolado con flete</td>
		<td><span id="costoRolado"></span></td>
		<td>&nbsp;</td>
        <td>&nbsp;</td>
        
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	
	</tr>
    <tr>
        <td class="label">Grua Titan</td>
        <td><select id="catalogoGruas" name="catalogoGruas" ></select></td>
        <td class="prices"><span id="precioGrua"></span></td>
        <td>&nbsp;</td>
        <td class="label">Dias de grua</td>
		<td><input type="text" id="diasGrua" name="diasGrua" /></td>
		<td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Costo de grua</td>
        <td><span id="costoGrua"></span></td>
    </tr>
</table>
<table class="section">
    <tr>
            <td class="label">Concepto de grupo</td>
            <td colspan="7"><textarea id="concepto3" name="concepto3" class="concepto"></textarea></td>
        </tr>
    <tr>
        <td class="col1 label">Mano de obra</td>
        <td class="col2"><input type="text" id="costoMO" name="costoMO" /></td>
            <td class="prices">&nbsp;</td>
            <td>&nbsp;</td>
            <td class="label">Costo de mano de obra</td>
            <td><span id="totalMO" ></span></td>
    </tr>
    <tr>
        <td class="label">Placas</td>
        <td><select id="placas" name="placas"></select></td>
        <td><span id="cantidadPlacas"></span></td>
        <td>&nbsp;</td>
        <td class="label">Tornillos</td>
        <td><select id="tornillos" name="tornillos"></select></td>
        <td><span id="cantidadTornillos"></span></td>
    </tr>
	<tr>
		<td class="label">Costo de placas y tornillos</td>
		<td><span id="costoPlacasTornillos"></span></td>
		<td>&nbsp;</td>
	</tr>
</table>
<table class="section">
    <tr>
            <td class="label">Concepto de grupo</td>
            <td colspan="7"><textarea id="concepto4" name="concepto4" class="concepto"></textarea></td>
        </tr>
    <tr>
        <td class="col1 label">Cantidad de gente para la obra</td>
        <td class="col2"><input type="text" id="cantidadGente" name="cantidadGente"/></td>
        <td class="prices">&nbsp;</td>
        <td>&nbsp;</td>
        <td class="label">Dias de traslado</td>
        <td class="col2"><span id="diasTraslado"></span></td>
        <td class="prices">&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Dias de trabajo</td>
        <td><span id="diasTrabajo"></span></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td class="label">Datos aproximados fuera de compromiso</td>
        <td><span id="datosFueraComp"></span></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Costo de viaticos</td>
        <td><span id="costoViaticos"></span></td>
    </tr>
</table>
<table class="section">
	<tr>
		<td class="col1 label">Costo directo total</td>
		<td class="col2"><span id="costoDirectoTotal"></span></td>
		<td class="prices">&nbsp;</td>
        <td>&nbsp;</td>
	</tr>
	<tr>
		<td class="label">CD / M2</td>
		<td><span id="cdm2"></span></td>
	</tr>
	<tr>
		<td class="label">Porcentaje de indirectos y utilidad</td>
		<td><input name="porcIndirectos" id="porcIndirectos" type="text" /></td>
		<td><input type="button" value="Calcular" onclick="calcular();" class="inputsubmit"/></td>
	</tr>
	<tr>
		<td class="label">CF / M2</td>
		<td><span id="cfm2"></span></td>
	</tr>
    <tr>
        <td><input type="hidden" id="jsObject" name="jsObject" /></td>
    </tr>
    </table>
    <div style="text-align: center"><input type="submit" value="Registrar" name="btnSubmit" id="btnSubmit" class="inputsubmit"/></div>
</div>
<script src="http://code.jquery.com/jquery-1.11.2.min.js"></script>
<script>
    var jCalibres = '<?php echo $calibres;?>';
    var oCalibres = eval(jCalibres);

    var jLaminas = '<?php echo $laminas?>';
    var oLaminas = eval(jLaminas);
    
    var jRoladoras = '<?php echo $roladoras?>';
    var oRoladoras = eval(jRoladoras);
    
    var jGruas = '<?php echo $gruas?>';
    var oGruas = eval(jGruas);

    var jTornillos = '<?php echo $tornillos?>';
    var oTornillos = eval(jTornillos);

    var jPlacas = '<?php echo $placas?>';
    var oPlacas = eval(jPlacas);

    var jItems;
</script>
<script>

    loadCatalogues();

    function fireCalculos() {
        $('#longitud').html(setLongitudLamina());
        $('#arcos').html(setNumeroArcos());
        $('#pesoCalibre').html(setPeso());
        $('#area').html(setArea());
        $('#laminaNecesaria').html(setLaminaNecesaria());
        $('#pesoTotal').html(setPesoTotal());
        setLaminaAUtilizar();
        $('#costoDirecto').html(setCostoDirectoLamina());
        setCostoPlacasTornillos();
    }

    function CalibrePeso() {
        var ancho = $('#ancho').val();
        $('#calibres').val(oCalibres[0].calibre);

        for (i = 0; i < oCalibres.length; i++) {
            if (ancho >= oCalibres[i].inicial && ancho <= oCalibres[i].final) {
                $('#calibres').val(oCalibres[i].calibre);
            }

        }

        fireCalculos();

    }

    function setCostoDirectoLamina() {
        var lamina = $('#catalogoLaminas').val();
        var peso = setPesoTotal();

        for (i = 0; i < oLaminas.length; i++) {
            if (oLaminas[i].stockId == lamina) {
                $('#costoLamina').html('$ ' + oLaminas[i].price);
                return oLaminas[i].price * peso;
            }
        }
    }

    function setLaminaAUtilizar() {
        var calibre = $('#calibres').val();
        var lamina = '';

        for (i = 0; i < oLaminas.length; i++) {
            lamina = oLaminas[i].stockId;
            if (lamina.substring(10) == calibre) {
                $('#catalogoLaminas').val(lamina);
                break;
            }
        }
    }

    function setLongitudLamina() {
        var ancho = $('#ancho').val();
        var longitud = ancho * 1.1035 * 1.03;
        return longitud;
    }

    function setNumeroArcos() {
        var largo = $('#largo').val();
        var arcos = largo / 0.61;
        return arcos;
    }

    function setArea() {
        var ancho = $('#ancho').val();
        var largo = $('#largo').val();
        var area = $('#naves').val() * ancho * largo;

        return area;
    }

    function setLaminaNecesaria() {
        var long = setLongitudLamina();
        var arcos = setNumeroArcos();
        var lamina = long * arcos * $('#naves').val();

        return lamina;

    }

    function setPeso() {
        var peso = $('#calibres').val();
        for (i = 0; i < oCalibres.length; i++) {
            if (oCalibres[i].calibre == peso) {
                peso = oCalibres[i].peso;
                break;
            }

        }
        return peso;
    }

    function setPesoTotal() {
        var peso = setPeso();
        var lamina = setLaminaNecesaria();
        var result = peso * lamina;

        return result;

    }

    function loadCatalogues() {
        //calibres
        for (i = 0; i < oCalibres.length; i++) {
            $('<option>').val(oCalibres[i].calibre).text(oCalibres[i].calibre).appendTo('#calibres');
        }
        //laminas
        fillCombo(oLaminas, 'catalogoLaminas');
        //roladoras
        fillCombo(oRoladoras, 'catalogoRoladoras');
        //gruas
        fillCombo(oGruas, 'catalogoGruas');
        //tornillos
        fillCombo(oTornillos, 'tornillos');
        //placas
        fillCombo(oPlacas, 'placas');
    }

    function fillCombo(objetos, control) {
        var combo = '#' + control;
        for (i = 0; i < objetos.length; i++) {
            $('<option>').val(objetos[i].stockId).text(objetos[i].description).appendTo(combo);
        }
    }

    function setFlete() {

        var distancia = $('#distancia').val();
        var flete = distancia * 50;

        return flete;
    }

    function setCostoRoladora() {
        var costoRoladora = 0.0;

        for (i = 0; i < oRoladoras.length; i++) {
            if ($('#catalogoRoladoras').val() == oRoladoras[i].stockId) {
                costoRoladora = oRoladoras[i].price;
            }
        }

        var dias = $('#diasRoladora').val();
        var flete = setFlete();

        var costoTotal = (dias * costoRoladora) + flete;

        $('#costoRolado').html(costoTotal);
        $('#precioRoladora').html('$ ' + costoRoladora);
    }

    function setCostoGrua() {
        var costoGrua = 0.0;
        var horas = $('#diasGrua').val();

        for (i = 0; i < oGruas.length; i++) {
            if (oGruas[i].stockId == $('#catalogoGruas').val()) {
                costoGrua = oGruas[i].price;
            }
        }

        var costo = costoGrua * horas * 8;

        $('#costoGrua').html(costo);
        $('#precioGrua').html('$ ' + costoGrua);
    }

    function setCostoMO() {

        var area = setArea();
        var costo = $('#costoMO').val();
        var total = area * costo;

        $('#totalMO').html(total);
    }

    function setCostoPlacasTornillos() {

        var placa = 0.0;
        var tornillo = 0.0;

        for (i = 0; i < oTornillos.length; i++) {
            if (oTornillos[i].stockId == $('#tornillos').val()) {
                placa = oTornillos[i].price;
                break;
            }
        }

        for (i = 0; i < oPlacas.length; i++) {
            if (oPlacas[i].stockId == $('#placas').val()) {
                tornillo = oPlacas[i].price;
                break;
            }
        }

        var cPlaca = (setNumeroArcos() * 2) + 2;
        var cTornillo = (setNumeroArcos() * 8) + 16;

        var total = (cPlaca * placa) + (cTornillo * tornillo);

        $('#costoPlacasTornillos').html(total);
    }

    function setViaticos() {

        var gente = $('#cantidadGente').val();
        var diasTraslado = ($('#distancia').val() * 2) / 500;
        var diasTrabajo = setPesoTotal() * 0.0005;
        var fueraCompromiso = diasTraslado + diasTrabajo;
        var costoViaticos = gente * fueraCompromiso * 250;

        $('#diasTraslado').html(diasTraslado);
        $('#diasTrabajo').html(diasTrabajo);
        $('#datosFueraComp').html(fueraCompromiso);
        $('#costoViaticos').html(costoViaticos);
    }

    function calcular() {

        fireCalculos();

        //costo viaticos + costo placas/tornillos + costo mano de obra + costo grua + costo rolado/flete + costo directo lamina
        var viaticos = $('#costoViaticos').html();
        var placasTornillos = $('#costoPlacasTornillos').html();
        var mo = $('#totalMO').html();
        var rolado = $('#costoRolado').html();
        var grua = $('#costoGrua').html();
        var lamina = setCostoDirectoLamina();

        var total = Number(viaticos) + Number(placasTornillos) + Number(mo) + Number(rolado) + Number(grua) + Number(lamina);

        $('#costoDirectoTotal').html(total);

        var area = setArea();
        var cdm2 = total / area;

        $('#cdm2').html(cdm2);

        var porcentaje = $('#porcIndirectos').val() / 100;

        var cfm2 = area * (1 + porcentaje);
        $('#cfm2').html(cfm2);


        var cPlaca = (setNumeroArcos() * 2) + 2;
        var cTornillo = (setNumeroArcos() * 8) + 16;

        jItems = {"lamina":$('#catalogoLaminas').val(),"cantidadLamina":setPesoTotal(),
                    "roladora":$('#catalogoRoladoras').val(), "diasRoladora": $('#diasRoladora').val(),
                    "grua":$('#catalogoGruas').val(), "diasGrua":$('#diasGrua').val(),
                    "placas":$('#placas').val(), "cantidadPlacas":cPlaca,
                    "tornillos":$('#tornillos').val(), "cantidadTornillos":cTornillo, 
                    "viaticosDias":$('#diasTrabajo').html(), "viaticosCosto":$('#costoViaticos').html()
                 };
        
        $('#jsObject').val(JSON.stringify(jItems));

        
        
    }

    $('#ancho').change(function () {
        CalibrePeso();
    });

    $('#largo').change(function () { CalibrePeso(); });

    $('#catalogoLaminas').change(function () { fireCalculos(); });

    $('#calibres').change(function () { fireCalculos(); });

    $('#naves').change(function () { fireCalculos(); });

    $('#distancia').change(function () { $('#costoFlete').html(setFlete()); setCostoRoladora(); });

    $('#diasRoladora').change(function () { setCostoRoladora(); });
    $('#catalogoRoladoras').change(function () { setCostoRoladora(); });

    $('#diasGrua').change(function () { setCostoGrua(); });
    $('#catalogoGruas').change(function () { setCostoGrua(); });

    $('#costoMO').change(function () { setCostoMO(); });

    $('#placas').change(function () { setCostoPlacasTornillos(); });
    $('#tornillos').change(function () { setCostoPlacasTornillos(); });

    $('#cantidadGente').change(function () { setViaticos(); });

</script>
<?php
    end_form();
    end_page();
?>
