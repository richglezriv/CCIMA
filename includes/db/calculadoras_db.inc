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

function get_caliber()
{
	$sql = "SELECT ancho_inicial, ancho_final, calibre, peso_en_kg_por_m FROM ".TB_PREF."pesos_y_calibres";

	$calibres = array();
	$result = db_query($sql, "The material cost cannot be retrieved");
	while ($calibrerow = db_fetch($result)) {
		$calibres[] = array("ancho_inicial"=>$calibrerow["ancho_inicial"], 
				"ancho_final"=>$calibrerow["ancho_final"],
				"calibre"=>$calibrerow["calibre"],
				"peso_en_kg_por_m"=>$calibrerow["peso_en_kg_por_m"]);
	}
	return $calibres; 
}

function get_item_material_sheet()
{
	$sql = "SELECT a.calibre, a.stock_id, b.description, b.material_cost ".
			"FROM ".TB_PREF."calibres_y_laminas a, ".TB_PREF."stock_master b ".
			"WHERE a.stock_id = b.stock_id";

	$sheets = array();
	$result = db_query($sql, "The material cost cannot be retrieved"); 
	while ($sheetsrow = db_fetch($result)) {
		$sheets[] = array("calibre"=>$sheetsrow["calibre"],
				"stock_id"=>$sheetsrow["stock_id"],
				"description"=>$sheetsrow["description"],
				"material_cost"=>$sheetsrow["material_cost"]);
	}
	return $sheets;
}

function get_servicios()
{
	$sql = "SELECT stock_id, material_cost ".
			"FROM ".TB_PREF."stock_master b ".
			"WHERE stock_id in ('S1','S2','S3','S4','S5') ".
			"ORDER BY stock_id";
	
	$servicios = array();
	$result = db_query($sql, "The material cost cannot be retrieved");
	while ($serviciosrow = db_fetch($result)) {
		$servicios[$serviciosrow["stock_id"]] = $serviciosrow["material_cost"];
	}
	return $servicios;
}
