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

function recupera_operaciones() {

	global $operaciones;

	foreach($_POST['operaciones'] as $ops) {
		foreach($ops as $key=>$campo) {
			$key = trim($key,'\"');
			$r[$key] = $campo;
		}
		$operaciones[] = $r;
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
	recupera_operaciones();
		
	foreach($variables as $var) {
		$$var['variable'] = $_POST[$var['nombre']];
	}
	
	foreach($operaciones as $operacion) {
		$mathstr = $operacion['op'];
		foreach($variables as $var) {
			$mathstr = str_replace($var['nombre'], $$var['variable'], $mathstr);
		}
		$$operacion['var'] = evalua_formula($mathstr);
		$_POST[$operacion['nomop']] = $$operacion['var'];
		
		$Ajax->addUpdate($operacion['nomop'], $operacion['nomop'], $$operacion['var']);
		$Ajax->activate($operacion['nomop']);
	}
}

if (get_post('cancelar')) {

	unset($_POST);
	meta_forward('http://localhost/CCIMA/index.php');
}
/*****************************************************************************/

function apply_uncoded($param) {
	
	return rtrim(explode('(',$param)[1],')');
}

function evalua_formula($mathString)    {
    $mathString = trim($mathString);
    $mathString = ereg_replace('[^0-9\+-\*\/\(\) ]', '', $mathString);
 
    $compute = create_function("", "return (" . $mathString . ");" );
    return 0 + $compute();
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
					calculadora_list_cells(_("Cálculo a ser utilizado en cotización:"), 'calculo', null, 
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
					$bloque_anterior = 0;
					$seccion = 0;
					$renglon = 0;
					while ($control = db_fetch($result)) {
						if ($bloque_anterior != $control['bloque']) {
							$bloque_anterior = $control['bloque'];
							
							if ($seccion == 2) {
								end_outer_table(1);
							}
							
							$seccion++;
							if ($seccion > 2) {
								$seccion = 1;
							}
							if ($seccion == 1) {
								start_outer_table(TABLESTYLE2);
							}
							table_section($seccion);
						}
						if ($control['tipo'] == 'Etiqueta') {
							if ($renglon == 1) {
								end_row(); 
							}
							start_row(); 
							$renglon = 1;
							
							label_cells($control['valor'],'','','',$control['nombre']);
						} else {
							$_POST[$control['nombre']] = 0;
							$variables[] = array('nombre' => $control['nombre'], 'variable' => '$'.$control['nombre']);
							if (strpos($control['valor'],"FOR") !== false) {
								$operaciones[] = array('nomop' => $control['nombre'],
										'var' => '$'.$control['nombre'],
										'op' => apply_uncoded($control['valor']));
							}
							text_cells(null, $control['nombre'],
								strpos($control['valor'],"CON") !== false ? apply_uncoded($control['valor']) : '',
									31,60,null,'','',$control['editable'] == 'Si' ? '' : 'READONLY');
							 
							end_row(); 
							$renglon = 0;
						}
					}
						
					if ($seccion != 0) {
						if ($renglon == 1) {
							end_row();
						}
						end_outer_table(1);
					}
				end_table();
					
				hidden_array('variables', $variables);
				hidden_array('operaciones',$operaciones);
					
			div_end();
		echo "</div>\n";
		
		start_outer_table(TABLESTYLE_NOBORDER, "width=80%");
			submit('calcular', _("Realizar cálculos"), true, '', true);
			submit('guardar', _("Guardar cálculos"), true, '', true);
			submit('cancelar', _("Cancelar cálculos"), true, '', true);
		end_table(1); 
		
	end_form();
end_page();
?>
