<?php
/**********************************************************************
	Creación de calculadoras
***********************************************************************/
$page_security = 'SA_OPEN';
$path_to_root = "..";

include($path_to_root . "/includes/db_pager.inc");
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include($path_to_root . "/calculadoras/includes/db/creacion_db.inc");

simple_page_mode(true);

$_SESSION['page_title'] = "Creación de calculadoras";

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
if ($use_date_picker)
	$js .= get_js_date_picker();
$posicion = 1;

if ($_POST['id_calculadora'])
	$id_calculadora = $_POST['id_calculadora'];
if ($_POST['nombre_calculadora'])
	$nombre_calculadora = $_POST['nombre_calculadora']; 

/*****************************************************************************/

function can_process_control() {
	
	if (strlen($_POST['bloque']) == 0) {
		display_error( _("No puede omitir el número de bloque."));
		set_focus('bloque');
		return false;
	}

	if (!is_numeric($_POST['bloque'])) {
		display_error( _("El bloque debe ser numérico."));
		set_focus('bloque');
		return false;
	}
	
	if ($_POST['tipo'] != 'Etiqueta' && $_POST['tipo'] != 'Valor') {
		display_error( _("El tipo es incorrecto."));
		set_focus('tipo');
		return false;
	}
	
	if (strlen($_POST['nombre']) < 3) {
		display_error( _("El nombre es demasiado corto."));
		set_focus('nombre');
		return false;
	}
	
	if (is_numeric(substr($_POST['nombre'],0,1))) {
		display_error( _("El primer caracter del nombre no puede ser un número."));
		set_focus('nombre');
		return false;
	}
	
	if (strlen($_POST['valor']) < 3) {
		display_error( _("El valor es demasiado corto."));
		set_focus('valor');
		return false;
	}
	
	if (is_numeric(substr($_POST['valor'],0,1))) {
		display_error( _("El primer caracter del valor no puede ser un número."));
		set_focus('valor');
		return false;
	}
	
	$pos_xy = split(',',$_POST['posicion_xy']);
	if (count($pos_xy) != 2 || !is_numeric($pos_xy[0]) || !is_numeric($pos_xy[1])) {
		display_error( _("La posicion XY debe ser un par de números separados por coma (,)"));
		set_focus('posicion_xy');
		return false;
	}
		
	if ($_POST['editable'] != 'Na' && $_POST['editable'] != 'Si' 
			&& $_POST['editable'] != 'No' && $_POST['editable'] != 'no') {
		display_error( _("Editable solo puede tener los valores Na, Si, No o no"));
		set_focus('posicion_xy');
		return false;
	}
	
	return true;
}

//-------------------------------------------------------------------------------------------------

function clear_campos() {
	
	global $selected_id, $bloque, $posicion, $tipo, $nombre, $valor, 
		$estilo_de_font, $posicion_xy, $editable, $controles;
	
	$bloque = '';
	$tipo = '';
	$nombre = '';
	$valor = '';
	$estilo_de_font = '';
	$posicion_xy = '';
	$editable = '';
	
	$posicion = count($controles) + 1;
	$selected_id = -1;
}

function recupera_controles() {

	global $controles;
	
	foreach($_POST['controles'] as $control) {
		foreach($control as $key=>$campo) {
			$key = trim($key,'\"');
			$r[$key] = $campo;
		}
		$controles[] = $r;
	}
}

function get_control() {
	
	global $controles, $selected_id;
	
	$i = 1;
	foreach($controles as $control) {
		if ($i++ == $selected_id) {
			foreach($control as $key=>$campo) {
				$r[$key] = $campo;
			} 
			return $r;
		}
	}
}

function edita_campos() {
	
	global $bloque, $posicion, $tipo, $nombre, $valor, $estilo_de_font, $posicion_xy, $editable;
	
	$myrow = get_control();
	
//	$id_calculadora = $myrow['id_calculadora'];
	$bloque = $myrow['bloque'];
	$posicion = $myrow['posicion'];
	$tipo = $myrow['tipo'];
	$nombre = $myrow['nombre'];
	$valor = $myrow['valor'];
	$estilo_de_font = $myrow['estilo_de_font'];
	$posicion_xy = $myrow['posicion_xy'];
	$editable = $myrow['editable'];
}

function asignar_captura() {
	
	$row = array();
	//$row['id_campo'] = count($controles) + 1;
	$row['id_calculadora'] = $_POST['id_calculadora'];
	$row['bloque'] = $_POST['bloque'];
	$row['posicion'] = $_POST['posicion'];
	$row['tipo'] = $_POST['tipo'];
	$row['nombre'] = $_POST['nombre'];
	$row['valor'] = $_POST['valor'];
	$row['estilo_de_font'] = $_POST['estilo_de_font'];
	$row['posicion_xy'] = $_POST['posicion_xy'];
	$row['editable'] = $_POST['editable'];
	
	return $row;
}

/*****************************************************************************/

if ($Mode == 'Delete') {
	
	recupera_controles();

	array_splice($controles,$selected_id-1,1);

	display_notification_centered(_("Control eliminado"));
	
	clear_campos();

	$Ajax->activate('calculadora_div');
	$Ajax->activate('control_div');
	set_focus('bloque');
	
	$Mode = 'RESET';
}

if ($Mode == 'Edit')
{
	recupera_controles();
	
	edita_campos(); 
	
	$Ajax->activate('control_div');
	
	set_focus('bloque');
}

/*****************************************************************************/

if (get_post('CancelEdit'))
{
	recupera_controles();

	clear_campos();

	$Ajax->activate('calculadora_div');
	$Ajax->activate('control_div');
	set_focus('bloque');
}

if (get_post('AddControl') and can_process_control())
{
	recupera_controles();
	
	$controles[] = asignar_captura();
	
	clear_campos();
		
	$Ajax->activate('calculadora_div');
	$Ajax->activate('control_div');

	set_focus('bloque');
}

if (get_post('UpdateControl') and can_process_control()) {
	
	recupera_controles();

	$controles[$_POST['posicion']-1] = asignar_captura();
	
	clear_campos();
	
	$Ajax->activate('calculadora_div');
	$Ajax->activate('control_div');

	set_focus('bloque');
}

if (get_post('CANCEL_CALC')) {
	
	unset($_POST);
	meta_forward('http://localhost/CCIMA/index.php');
}

if (get_post('ADD_CALC')) {
	
	recupera_controles();

	if (add_calculadora($id_calculadora, $nombre_calculadora, $controles)) {
		display_notification_centered('Se agregó calculadora!');
		
		unset($controles);
		$nombre_calculadora = '';
	
		$Ajax->activate('cabecera_div');
		$Ajax->activate('calculadora_div');
		$Ajax->activate('control_div');
		
		set_focus($nombre_calculadora);
	} else {
		display_error('No fue posible agregar la calculadora!');
	}
}

/*****************************************************************************/

function display_calculadora_items()
{
	global $id_calculadora, $controles, $selected_id;

		div_start('calculadora_div'); //var_dump($controles);
		start_table(TABLESTYLE, "width=80%");
			$th = array(_("Id Calculadora"), _("Bloque"), _("Posición"),
				_("Tipo"), _("Nombre"), _("Valor"), _("Estilo de font"),
				_("Posición XY"), _("Editable"), '', '');
			table_header($th);

			$k = 0;
			$posicion = 1;
			foreach($controles as $myrow) {
				alt_table_row_color($k);
				
				$myrow['posicion'] = $posicion++;
		
				label_cell(str_pad($id_calculadora,3,'0',STR_PAD_LEFT));
				label_cell(str_pad($myrow['bloque'],3,'0',STR_PAD_LEFT));
				label_cell(str_pad($myrow['posicion'],3,'0',STR_PAD_LEFT));
				label_cell($myrow['tipo']);
				label_cell($myrow['nombre']);
				label_cell($myrow['valor']);
				label_cell($myrow['estilo_de_font']);
				label_cell($myrow['posicion_xy']);
				label_cell($myrow['editable']);
				if ($selected_id == -1) {
					edit_button_cell("Edit".$myrow['posicion'], _("Edit"));
					delete_button_cell("Delete".$myrow['posicion'], _("Delete"));
				}
				end_row();
			}
			hidden_array('controles',$controles);
			
		end_table();
	div_end();
}

function display_control_item()
{
	global $id_calculadora, $posicion;
	global $bloque, $tipo, $nombre, $valor, $estilo_de_font, $posicion_xy, $editable, $selected_id;
	
	div_start('control_div');
		start_table(TABLESTYLE2, "width=80%");
		
			start_row();
				label_cell('Id');
				label_cell('Bloque');
				label_cell('Posición');
				label_cell('Tipo');
				label_cell('Nombre');
				label_cell('Valor');
				label_cell('Estilo de font');
				label_cell('Posición XY');
				label_cell('Editable');
				label_cell('');
				if ($selected_id != -1) 
					label_cell('');
				end_row();
			
			start_row();
				label_cells('',$id_calculadora,'','','id_calculadora');
				text_cells('','bloque',$bloque,6,5,false);
				label_cells('',str_pad($posicion,3,'0',STR_PAD_LEFT),'','','posicion');
				text_cells('','tipo', $tipo,9,8,false);
				text_cells('','nombre', $nombre,6,30,false);
				text_cells('','valor', $valor,4,200,false);
				text_cells('','estilo_de_font', $estilo_de_font,8,10,false);
				text_cells('','posicion_xy', $posicion_xy,6,10,false);
				text_cells('','editable', $editable,4,2,false);
				if ($selected_id != -1) {
					submit_cells('UpdateControl', _('Update'),  "", 'Aplicar modificación al control', 'default');
					submit_cells('CancelEdit', _('Cancel'),  "", 'Cancelar edición', 'cancel');
				} else {
					submit_cells('AddControl', _('Add new'), "", 'Agregar control a la calculadora', 'default');
				}
			end_row();
			hidden('posicion', $posicion);
			
		end_table(1);
				
		start_table(TABLESTYLE_NOBORDER, "width=80%");
			if ($selected_id == -1) 
				submit_add_and_cancel_row(false, '', 'Agregar calculadora', 'Salir','default');
		end_table(1);
				
	div_end();
}

/*****************************************************************************/

page($_SESSION['page_title'], false, false, "", $js);

	start_form();
		$id_calculadora = get_next_id_calculadora();
		
		div_start('cabecera_div');
			start_table(TABLESTYLE_NOBORDER);
	
				start_row();
				label_row(_("Id de Calculadora #:"), str_pad($id_calculadora,3,'0',STR_PAD_LEFT), '', '', 0,'id_calculadora');
				end_row();
					
				text_row(_("Nombre de la Calculadora"),'nombre_calculadora', $nombre_calculadora, 61, 60);
			
			end_table(1);
			
			display_calculadora_items();
			echo "<br>";
			display_control_item();
			echo "<br>";
		
			hidden('id_calculadora', $id_calculadora);
		div_end();
				
	end_form();
end_page();
?>