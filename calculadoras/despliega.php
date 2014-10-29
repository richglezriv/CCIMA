<?php
/**********************************************************************
	Despliega calculadoras
***********************************************************************/
$page_security = 'SA_OPEN';
$path_to_root = "..";
include($path_to_root . "/includes/db_pager.inc");
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include($path_to_root . "/calculadoras/includes/db/creacion_db.inc");
simple_page_mode(true);
$_SESSION['page_title'] = "Despliega calculadoras";
$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
if ($use_date_picker)
	$js .= get_js_date_picker();
/*****************************************************************************/
function recupera_variables() {
	global $variables;
	foreach($_POST['variables'] as $var) {
		foreach($var as $key=>$campo) {
			$key = trim($key,'\"');
			$r[$key] = $campo;
		}
		$variables[] = $r;
	}
}
/*****************************************************************************/
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
if (get_post('calcular')) {
	
	recupera_variables();
	$operaciones = $_POST['operaciones'];
		
	foreach($variables as $var) {
		eval('\''.$var['variable'] . ' = ' . $_POST[$var['nombre']].'\'');
	}
	
	foreach($operaciones as $operacion) {
		eval('\''.$operacion.'\'');
	}
}
if (get_post('cancelar')) {
	unset($_POST);
	meta_forward('http://localhost/CCIMA/index.php');
}
/*****************************************************************************/
function apply_uncoded($param) {
	
    $arreglo = explode('(',$param);
	return rtrim($arreglo[1],')');
    
}
function convierte_formula($formula) {
	
	
}
/*****************************************************************************/
page($_SESSION['page_title'], false, false, "", $js);
	start_form();
		div_start('div_cabecera');
			
			start_table(TABLESTYLE_NOBORDER,"width=80%");
			 	start_row();
					calculadora_list_cells(_("Calculadora:"), 'lista_calculadora', null, 
						null, true, check_value('show_inactive'));
					text_cells(null,'nombre_calculadora', $nombre_calculadora, 61, 60,null,'','','READONLY');
				end_row();
	
				start_row();
					calculadora_list_cells(_("Calculo a ser utilizado en cotizacion:"), 'calculo', null, 
						null, true, check_value('show_inactive'));
					text_cells(null,'cotizacion', $cotizacion, 61, 60,null,'','','READONLY');
				end_row();
				
			end_table(1);
		div_end();
		
		echo "<div class='contentBox'>\n";
			div_start('div_detalles');
				$sql = get_sql_for_calculadora_search($id_calculadora);
				$result = db_query($sql,"No se encontraron componentes");
				
				start_table(TABLESTYLE_NOBORDER,"width=80%");
					$operaciones = '';
					//$primera_vez = 0;
					while ($control = db_fetch($result)) {
						start_row();
							if ($control['tipo'] == 'Etiqueta') {
								label_cells($control['valor'],'','','',$control['nombre']);
							} else {
								$_POST[$control['nombre']] = 0;
								$variables[] = array('nombre' => $control['nombre'], 'variable' => '$'.$control['nombre']);
								if (strpos($control['valor'],"FOR") !== false) {
									$operacion = $control['nombre'].' = '.apply_uncoded($control['valor']);
									foreach($variables as $var) {
										$operacion = str_replace($var['nombre'], $var['variable'], $operacion);
									}
									$operaciones[] .= $operacion;
								}
								text_cells(null, $control['nombre'], 
									strpos($control['valor'],"CON") !== false ? apply_uncoded($control['valor']) : '',
									31,60,null,'','',$control['editable'] == 'Si' ? '' : 'READONLY');
							}
						end_row();
					}
				end_table();
				hidden_array('variables', $variables);
				hidden_array('operaciones',$operaciones);
				
			div_end();
		echo "</div>\n";
		
		start_outer_table(TABLESTYLE_NOBORDER, "width=80%");
			submit('calcular', _("Realizar calculos"), true, '', true);
			submit('guardar', _("Guardar calculos"), true, '', true);
			submit('cancelar', _("Cancelar calculos"), true, '', true);
		end_table(1); 
		
	end_form();
end_page();
?>