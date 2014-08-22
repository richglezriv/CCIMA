<?php

$page_security = 'SA_OPEN';

$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/db/calculadoras_db.inc");

add_js_file('conversormats.js');

$_SESSION['page_title'] = "Cálculos del Arco";

$mats = array();

/* -----------------------------------------------------------------*/

function inicializa_mats()
{
	global $mats;
	
	$mats["ancho"] = 25;//0;
	$mats["largo"] = 20;//0;
	$mats["altura"] = 14;//0;
	$mats["naves"] = 1;
	$mats["porcentajedescmin"] = 15;  
	$mats["porcentajedescmax"] = 27;  
	
	$mats["diasroladora"] = 1;
	$mats["diasgruatitan"] = 1;//0;
	$mats["porcentajeindirectos"] = 25;
	$mats["costoflete"] = 100;//0;
}

/* Calculo de dimensiones de materiales */
function calculo_materiales()
{
	global $mats;
	
	$mats["calibre"] = 28;//1; // de la bd
	$mats["pesocalibre"] = 4.65;//1; // de la bd
	$mats["tipolamina"] = 1; // de la bd
	$mats["costoroladora"] = 1; // de la bd
//	$mats["costolaminakg"] = 318; // de acuerdo a la bd
	$mats["pesocalibre"] = 2.5; // de acuerdo a la bd
	$mats["costogruahora"] = 1; // de acuerdo a la bd
	$mats["costomanoobrakgm2"] = 1; // de acuerdo a la bd
	$mats["costoplacasfijacion"] = 1; // de acuerdo a la bd
	$mats["costotornillosancla"] = 1; // de acuerdo a la bd
	
	$mats["area"] = $mats["ancho"] * $mats["largo"];
	$mats["longitudporarco"] = $mats["ancho"]  * 1.1035 * 1.03;
	$mats["numarcos"] = $mats["largo"] / 0.61;
	$mats["longitudlamina"] = $mats["naves"] * $mats["longitudporarco"] * $mats["numarcos"];
	$mats["pesototal"] = $mats["pesocalibre"] * $mats["longitudlamina"];
	
	$mats["costolamina"] = $mats["costolaminakg"] - (1 - $mats["costolaminakg"] * $mats["porcentajedesc"] / 100);
	$mats["costodirectolam"] = $mats["pesototal"] * $mats["costolamina"];
	
	$mats["costoroladoflete"] = $mats["diasroladora"] * $mats["costoroladora"] + $mats["costoflete"];
	$mats["costogrua"] = 8 * $mats["diasgruatitan"] * $mats["costogruahora"];
	$mats["costomanoobra"] = $mats["area"] * $mats["costomanoobrakgm2"];
	$mats["cantidadplacas"] = 2 * $mats["numarcos"] + 2;
	$mats["cantidadtornillos"] = 8 * $mats["numarcos"] + 16;
	$mats["costoplacastornillos"] = $mats["cantidadplacas"] * $mats["costoplacasfijacion"]
		+ $mats["cantidadtornillos"] * $mats["costotornillosancla"];
	$mats["costodirectototal"] = $mats["costodirectolam"] + $mats["costoroladoflete"]
		+ $mats["costogrua"] + $mats["costomanoobra"] + $mats["costoplacastornillos"];
	$mats["c2m2"] = $mats["costodirectototal"] / $mats["area"];
	$mats["cfm2"] = $mats["c2m2"] * (1 + $mats["porcentajeindirectos"] / 100);
	
}

/* -----------------------------------------------------------------*/

//inicializa_mats();	
//calculo_materiales();

$sangria = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

page($_SESSION['page_title']);

	start_form();
	if (!isset($tipo_lamina_id)) {
		$myrow = get_caliber_per_width(null);
		$ancho = $myrow["ancho_inicial"];
		$calibre = $myrow["calibre"];
		$pesocalibre = $myrow["peso_en_kg_por_m"];
		
		hidden('ancho',$ancho);
		hidden('calibre',$calibre);
		hidden('pesocalibre',$pesocalibre);
		
		$myrow = get_item_material_cost($tipo_lamina_id);
		$costolaminakg = $myrow["material_cost"];
		
		hidden('tipo_lamina_id', $tipo_lamina_id);
		hidden('costolaminakg', $costolaminakg);echo $costolaminakg."<br/>";
	}

		start_table(TABLESTYLE2);
		    date_row(_("Date") . ":", 'date_', '', true);
		    locations_list_row(_("Lugar de la obra").":", 'Location',null);
		    user_list_row(_("Elaboro").":",'user_id', null, false);
		    //label_row(_("Elaboro").":",$_SESSION["wa_current_user"]->username);
		end_table(1);
		
		start_table(TABLESTYLE2);
			table_section_title(_("Medidas"));
			amount_row(_("Ancho")." (m):", 'Ancho', $ancho);
			amount_row(_("Largo")." (m):", 'Largo', $mats["largo"]);
			label_row(_("Calibre").":", $calibre,"",'nowrap align=right style="font-weight:bold"',0,'Calibre');
			label_row(_("Peso segun calibre")." (Kg/m):", number_format2($pesocalibre,2),"",'nowrap align=right style="font-weight:bold"',0,'Peso');
			amount_row(_("Altura de desplante de arco")." (m):", 'Altura', $mats["altura"]);
			amount_row(_("Cantidad de naves a cubrir").":", 'Naves', $mats["naves"]);
			label_row(_("Area a cubrir").":", number_format2($mats["area"],2),"",'nowrap align=right style="font-weight:bold"',0,'Area');
		end_table(1);

		start_table(TABLESTYLE2);
			label_row(_("Longitud de lamina por cada arco").":", number_format2($mats["longitudporarco"],2),"",'nowrap align=right style="font-weight:bold"',0,'LongitudXArco');
			label_row(_("Numero de arcos").":", number_format2($mats["numarcos"],2),"",'nowrap align=right style="font-weight:bold"',0,'NumArcos');
			label_row(_("Longitud de lamina necesaria para la obra").":", number_format2($mats["longitudlamina"],2),"",'nowrap align=right style="font-weight:bold"',0,'LongitudLamina');
			label_row(_("Peso total de lamina").":", number_format2($mats["pesototal"],2),"",'nowrap align=right style="font-weight:bold"',0,'PesoTotal');
		end_table(1);

		start_table(TABLESTYLE2);
			table_section_title(_("Lamina"));
			sheet_list_row($sangria._("Tipo de lamina").":",'tipo_lamina_id',null,false,null,$calibre);
			//label_row($sangria._("Tipo de lamina").":", 'desconocido',"",'nowrap align=right style="font-weight:bold"',0,'TipoLamina');
			label_row($sangria._("Costo de lamina")." ($/Kg):", "$".price_format($costolaminakg/*$mats["costolamina"]*/),"",'nowrap align=right style="font-weight:bold"',0,'CostoLaminaKg');
			number_list_row($sangria._("Porcentaje de descuento")." (%):", 'percent_desc', $mats["porcentajedesc"], 15, 27);
			label_row(_("Costo directo de lamina").":", "$".price_format($mats["costodirectolam"]),"",'nowrap align=right style="font-weight:bold"',0,'CostoDirectoLam');
		end_table(1);
		
		start_outer_table(TABLESTYLE2);
			table_section(1);
			amount_row(_("Dias de roladora").":", 'DiasRoladora', $mats["diasroladora"]);
			label_row(_("Costo rolado con flete").":", "$".price_format($mats["costoroladoflete"]),"",'nowrap align=right style="font-weight:bold"',0,'CostoRoladoFlete');
			amount_row(_("Dias de grua Titan").":", 'DiasGrua', $mats["diasgruatitan"]);
			label_row(_("Costo de grua").":", "$".price_format($mats["costogrua"]),"",'nowrap align=right style="font-weight:bold"',0,'CostoGrua');
			label_row(_("Costo de mano de obra").":", "$".price_format($mats["costomanoobra"]),"",'nowrap align=right style="font-weight:bold"',0,'CostoManoObra');
		
			table_section(2);
			label_row(_("Costo de roladora")." ($/dia):", "$".price_format($mats["costoroladora"]),"",'nowrap align=right style="font-weight:bold"',0,'CostoRoladoraDia');
			amount_row(_("Costo de flete")." ($ Entrega/Retiro):", 'CostoFlete', $mats["costoflete"]);
			label_row(_("Costo de grua")." ($/hora):", "$".price_format($mats["costogruahora"]),"",'nowrap align=right style="font-weight:bold"',0,'CostaGruaHora');
			label_row($sangria);
			label_row(_("Costo de la mano de obra")." ($/m<sup>2</sup>):", "$".price_format($mats["costomanoobra"]),"",'nowrap align=right style="font-weight:bold"',0,'CostoManoObraM2');
		end_outer_table(1);
		
		start_outer_table(TABLESTYLE2);
			table_section(1);		
			label_row(_("Cantidad de placas").":", number_format2($mats["cantidadplacas"],2),"",'nowrap align=right style="font-weight:bold"',0,'CantidadPlacas');
			label_row(_("Cantidad de tornillos").":", number_format2($mats["cantidadtornillos"],2),"",'nowrap align=right style="font-weight:bold"',0,'CantidadTornillos');
			label_row(_("Costo de placas y tornillos").":", "$".price_format($mats["costoplacastornillos"]),"",'nowrap align=right style="font-weight:bold"',0,'CostoPyT');
		
			table_section(2);
			label_row(_("Costo de placas de fijacion")." ($/pza):", "$".price_format($mats["costoplacasfijacion"]),"",'nowrap align=right style="font-weight:bold"',0,'CostoPlacasFijacion');
			label_row(_("Costo de tornillos")."(ancla) ($/pza):", "$".price_format($mats["costotornillosancla"]),"",'nowrap align=right style="font-weight:bold"',0,'CostoTornillosAncla');
		end_outer_table(1);
		
		start_table(TABLESTYLE2);
			label_row(_("Costo directo total").":", "$".price_format($mats["costodirectototal"]),"",'nowrap align=right style="font-weight:bold"',0,'CostoDirectoTotal');
			label_row(_("CD / M2")." ($/Kg):", "$".price_format($mats["c2m2"]),"",'nowrap align=right style="font-weight:bold"',0,'C2M2');
			amount_row(_("Porcentaje de indirectos y utilidad")." (%):", 'PorcentajeIU', $mats["porcentajeindirectos"]);
			label_row(_("CF / M2").":", "$".price_format($mats["cfm2"]),"",'nowrap align=right style="font-weight:bold"',0,'CFM2');
		end_table(1);
		
		$tipo_lamina_id = -1;
		
	end_form();

end_page();
?>