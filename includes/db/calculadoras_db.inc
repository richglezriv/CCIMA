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

function get_caliber_per_width($width=null)
{
	if ($width == null) {
		$sql = "SELECT ancho_inicial, calibre, peso_en_kg_por_m FROM ".TB_PREF."pesos_y_calibres limit 1";
	} else {
		$sql = "SELECT calibre, peso_en_kg_por_m FROM ".TB_PREF."pesos_y_calibres ".
			"WHERE ".db_escape($width)." between ancho_inicial and ancho_final";
	}
	$result = db_query($sql, "The material cost cannot be retrieved");

	return db_fetch($result);
}

function get_item_material_cost($stock_id)
{
	$sql = "SELECT material_cost FROM ".TB_PREF."stock_master WHERE stock_id=".db_escape($stock_id);
	$result = db_query($sql, "The material cost cannot be retrieved");

	return db_fetch($result);
}

