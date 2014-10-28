<?php
/**********************************************************************
	Visualización de calculadoras
***********************************************************************/
$page_security = 'SA_OPEN';
$path_to_root = "..";

include($path_to_root . "/includes/db_pager.inc");
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include($path_to_root . "/calculadoras/includes/db/creacion_db.inc");

simple_page_mode(true);

$_SESSION['page_title'] = "Visualización de calculadoras";

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
if ($use_date_picker)
	$js .= get_js_date_picker();

/*****************************************************************************/

if (list_updated(lista_calculadora)) {
	
	$cabecera = split(':-:',$_POST['lista_calculadora']);
	if (count($cabecera) != 3) {
		$cabecera = array(0,'','');
	}
	
	$id_calculadora = $cabecera[0];
	$nombre_calculadora = $cabecera[1];
	$borrado_calculadora = $cabecera[2];
	
	$Ajax->activate('nombre_calculadora');
	$Ajax->activate('div_detalles');
}

/*****************************************************************************/

page($_SESSION['page_title'], false, false, "", $js);

	start_form();
		div_start('div_cabecera');
			
			start_table(TABLESTYLE_NOBORDER,"width=80%");
			 	start_row();
					calculadora_list_cells(_("Calculadora:"), 'lista_calculadora', null, 
						null, true, check_value('show_inactive'));
					check_cells_right(_("Mostrar registros borrados logicamente:"), 'show_inactive', $borrado_calculadora == 'X', true);
				end_row();
	
				start_row();
					text_cells_colspan(_("Nombre de la calculadora:"),'nombre_calculadora', $nombre_calculadora, 61, 60,null,'','','READONLY',3);
				end_row();
				
			end_table();
			
			start_outer_table(TABLESTYLE_NOBORDER, "width=80%");
				submit('show', _("Mostrar detalles"), true, '', true);
				submit('delete', _("Borrar logicamente"), true, '', true);
			end_table(1);
		div_end();
		
		div_start('div_detalles');
			$sql = get_sql_for_calculadora_search($id_calculadora);
			
			//$result = db_query($sql,"No orders were returned");
			
			//show a table of the orders returned by the sql
			$cols = array(
					_("Bloque"),
					_("Posicion"),
					_("Tipo"),
					_("Nombre"),
					_("Valor"),
					_("Estilo_de_font"),
					_("Posicion_xy"),
					_("Editable"),
					array('insert'=>true, 'fun'=>'edit_link'),
					array('insert'=>true, 'fun'=>'cancel_link')
			);
			
			$table =& new_db_pager('controles_tbl', $sql, $cols);
			//$table->set_marker('check_overdue', _("Marked orders have overdue items."));
			
			$table->width = "80%";
			
			display_db_pager($table);
		div_end();
	end_form();
end_page();
?>