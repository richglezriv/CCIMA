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
function eval_formula($mathString){
    
    //$array = explode('(', $mathString);
    $array = substr($mathString, 4);
    //$finalString = rtrim($array[1], ')');
    $finalString = rtrim($array, ')');
    $valores = $finalString;
    
    $cArray = ereg_replace('[+-\*\/\(\) ]', ':--:', $finalString);
    $c = split(':--:', $cArray);

    foreach($c as $field){
        if ($_POST[$field] != ''){
            $finalString = str_replace($field, $_POST[$field], $finalString);
        }
    }

    $compute = create_function('', "return (".$finalString.");");

    return $finalString;
}
/**
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
}**/

if (get_post('cancelar')) {
	unset($_POST);
	meta_forward('http://localhost/CCIMA/index.php');
}

/*****************************************************************************/

function display_calculadora($id_calculadora) {
	
	global $operaciones, $variables;

	echo "<div class='contentBox'>\n";
		div_start('div_detalles');
			$sql = get_sql_for_calculadora_search($id_calculadora);
			$result = db_query($sql,"No se encontraron componentes");
			
			start_table(TABLESTYLE_NOBORDER,"width=80%");
				$variables = '';
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
						$tipoFormula = strpos($control['valor'],"FOR") !== false;
						$tipoSelect = strpos($control['valor'],"SEL") !== false;
						$tipoConstante = strpos($control['valor'],"CON") !== false;

						if($tipoSelect){
                            $sql = apply_uncoded($control['valor']);  
                        }
                        
                        /**$sql = "SELECT
									item_code,
									description
									FROM ".TB_PREF."item_codes
									WHERE item_code = '5489665' or item_code = '526859'  ";**/

                        
												
						$variables[] = array('variable' => $control['nombre'],
							'tipo' => ($tipoFormula ? 'FOR' : ($tipoSelect ? 'SEL' : 'CON')),  
						 	'valor' => $tipoSelect ? $control['id_campo'] : apply_uncoded($control['valor']),
							'sql' => $sql,
						    'control' => $tipoConstante ? apply_uncoded($control['valor']) : '', 
							'aplicado' => $tipoConstante);
						
						if ($tipoSelect) { 
							
							echo "<td><select id='".$control['nombre']."' name='".$control['nombre']."' class='combo'>";
							foreach($variables as $key => $val1)
									$sqlvar = $val1['sql'];
										
							$resultsel = db_query($sqlvar,"No se encontraron componentes");
						    
							while ($controlselx = db_fetch($resultsel)) {
                                $selected = $controlselx[0] == $_POST[$control['nombre']] ? "selected" : "";
								echo "<option value='".$controlselx[0]. "' ".$selected.">".$controlselx[1]."</option>";

							}
							echo "</select></td>";
						} else {
                            $valor = 'ND';
                            //if ($tipoFormula && $_POST['calcular'] == 'Realizar calculos'){
                            if ($tipoFormula){
                                $valor = eval_formula($control['valor']);
                            }

                            text_cells(null, $control['nombre'],
								$tipoFormula ? $valor : $_POST[$control['nombre']],
								31,60,null,'','',($control['editable'] == 'Si' ? '' : 'READONLY style="background-color: yellow"').' tipo="calculo"');
						}
						 
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
			
				
			echo "<input type='hidden' name='varArrStr' value='".serialize($variables)."'>";
		
            start_table(TABLESTYLE_NOBORDER, "width=80%");
		        submit('calcular', _("Realizar calculos"), true, '', false);
		        submit('guardar', _("Guardar calculos"), true, '', true);
		        submit('cancelar', _("Cancelar calculos"), true, '', true);
	        end_table();
        
        div_end();
	echo "</div>\n";
	
	
}


/*****************************************************************************/

page($_SESSION['page_title'], false, false, "", $js);
	start_form();
		div_start('div_cabecera');
		
        if(isset($_POST['lista_calculadora'])){

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

			start_table(TABLESTYLE_NOBORDER,"width=80%");
			 	start_row();
					calculadora_list_cells(_("Calculadora:"), 'lista_calculadora', null, 
						null, false, check_value('show_inactive'));
					text_cells(null,'nombre_calculadora', $nombre_calculadora, 61, 60,null,'','','READONLY');
				end_row();
	
				start_row();
					calculadora_list_cells(_("Calculo a ser utilizado en cotizacion:"), 'calculo', null, 
						null, false, check_value('show_inactive'));
					text_cells(null,'cotizacion', $cotizacion, 61, 60,null,'','','READONLY');
				end_row();
                start_row();
                    echo ('<td><input type="submit" value="Buscar" id="buscaCalc" name="buscaCalc" class="inputsubmit"></td>');
                end_row();
				
			end_table(1);
            
		div_end();
		
        if ($_POST['lista_calculadora'] != ''){
                display_calculadora($id_calculadora);    
        }
        
		
		
	end_form();

    /**
	echo "<script src=\"//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js\"></script>
			<script src=\"../js/calculadora.js\"></script>
			<script>
				$(document).ready(function() {
   					$('#calcular').click(cargaCombos);
				});
			</script>";**/
            
	
end_page();
?>