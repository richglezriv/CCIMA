<?php

$path_to_root = "..";

include_once($path_to_root . "/calculadoras/includes/db/creacion_db.inc");

function evalua_formula($mathString)    {
		$mathString = trim($mathString);
		$mathString = ereg_replace('[^0-9\+-\*\/\(\) ]', '', $mathString);
	
		$compute = create_function("", "return (" . $mathString . ");" );
		return 0 + $compute();
	}

	$c = split(":--:",$_POST['strCampos']);
	$v = unserialize($_POST['varArrStr']);
	
	foreach($c as $camp) {
		$r = split("=",$camp);
		$campos[$r[0]] = $r[1];
	}

	foreach($v as $var) {
		$rr = '';
		$nombre = '';
		foreach($var as $key=>$campo) {
			$key = trim($key,'\"');
			if ($key == 'variable') {
				$nombre = $campo;
			}
			$rr[$key] = $campo;
		}
		if ($rr['tipo'] != 'FOR') {
			$rr['control'] = $campos[$nombre];
		}
		$variables[$nombre] = $rr;
	}

 	foreach($variables as $key=>$params) {
 		
 		$tipoFor = $params['tipo'] == 'FOR';
 		$tipoSel = $params['tipo'] == 'SEL';
/* 		
 		if ($tipoSel) {
 			$debug = get_sql_from_valor_field($params['valor']); 
 		}
 		var_dump($debug);
*/			
		if ($tipoFor || $tipoSel) {
			$mathstr = $tipoSel ? str_replace('xx_',$_SESSION["wa_current_user"]->company.'_',
					apply_uncoded(get_sql_from_valor_field($params['valor'])))
				: $params['valor'];

			$exprCorrecta = true;
			foreach($variables as $key2=>$valor) {
				if (strpos($mathstr,$key2) !== false) {
					if ($valor['control'] != null && $valor['control'] != '') {
						if ($tipoSel || is_numeric($valor['control'])) {
							$mathstr = str_replace($key2, $valor['control'],$mathstr);
						} else {
							$exprCorrecta = false;
						}
					} else {
						$exprCorrecta = false;
					} 
				}
				
			}
			if ($exprCorrecta) {
				if ($tipoFor) {
					$variables[$key]['control'] = evalua_formula($mathstr);
				} else {
					$result = db_query($mathstr,"No se encontraron registros");
					$variables[$key]['control'] = '';
					while ($renglon = db_fetch($result)) {
						$variables[$key]['control'] .= "<option value='".$renglon[0]."'>".$renglon[0]."</option>";
					}
				}
			}
		}
 	}

header('Content-type: application/json');
echo json_encode($variables);

?>