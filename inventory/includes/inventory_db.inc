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
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/manufacturing.inc");

include_once($path_to_root . "/inventory/includes/db/items_category_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_trans_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_prices_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_purchases_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_codes_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_locations_db.inc");
include_once($path_to_root . "/inventory/includes/db/movement_types_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_adjust_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_transfer_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_units_db.inc");

function item_img_name($stock_id)
{
	$stock_id = strtr($stock_id, "><\\/:|*?", '________');
	return clean_file_name($stock_id);
}

function calculate_reorder_level($location, $line, &$st_ids, &$st_names, &$st_num, &$st_reorder)
{
	$sql = "SELECT ".TB_PREF."loc_stock.*, ".TB_PREF."locations.location_name, ".TB_PREF."locations.email
		FROM ".TB_PREF."loc_stock, ".TB_PREF."locations
		WHERE ".TB_PREF."loc_stock.loc_code=".TB_PREF."locations.loc_code
		AND ".TB_PREF."loc_stock.stock_id = '" . $line->stock_id . "'
		AND ".TB_PREF."loc_stock.loc_code = '" . $location . "'";
	$res = db_query($sql,"a location could not be retreived");
	$loc = db_fetch($res);
	if ($loc['email'] != "")
	{
		$qoh = get_qoh_on_date($line->stock_id, $location);
		$qoh -= get_demand_qty($line->stock_id, $location);
		$qoh -= get_demand_asm_qty($line->stock_id, $location);
		$qoh -= $line->quantity;
		if ($qoh < $loc['reorder_level'])
		{
			$st_ids[] = $line->stock_id;
			$st_names[] = $line->item_description;
			$st_num[] = $qoh - $loc['reorder_level'];
			$st_reorder[] = $loc['reorder_level'];
		}
	}
	return $loc;
}

function send_reorder_email($loc, $st_ids, $st_names, $st_num, $st_reorder)
{
	global $path_to_root;
	
	require_once($path_to_root . "/reporting/includes/class.mail.inc");
	$company = get_company_prefs();
	$mail = new email($company['coy_name'], $company['email']);
	$from = $company['coy_name'] . " <" . $company['email'] . ">";
	$to = $loc['location_name'] . " <" . $loc['email'] . ">";
	$subject = _("Stocks below Re-Order Level at " . $loc['location_name']);
	$msg = "\n";
	for ($i = 0; $i < count($st_ids); $i++)
		$msg .= $st_ids[$i] . " " . $st_names[$i] . ", " . _("Re-Order Level") . ": " . $st_reorder[$i] . ", " . _("Below") . ": " . $st_num[$i] . "\n";
	$msg .= "\n" . _("Please reorder") . "\n\n";
	$msg .= $company['coy_name'];
	$mail->to($to);
	$mail->subject($subject);
	$mail->text($msg);
	return $mail->send();
}

function can_adjust($items, $location){

    $canAdjust = TRUE;

    foreach ($items as $line_item)
	{
        
        $quantity = $line_item->quantity;
        $qoh = get_qoh_on_date($line_item->stock_id, $location);
        
        $percentMax = ($qoh * .10);
        if ($quantity > $percentMax){
            $canAdjust = FALSE;
        }
		
    }


    return $canAdjust;
}

function get_horas_acumuladas($stock_id, $fecha_mantto=null){
    $valida = TRUE;
    if ($fecha_mantto == null){
        $valida = FALSE;
    }  
    $date = date2sql($fecha_mantto);
    $hoy = date2sql(Today());

    $sql = "select sum(od.qty_sent)
            from ".TB_PREF."sales_orders os
            inner join ".TB_PREF."sales_order_details od on os.order_no = od.order_no
            inner join ".TB_PREF."stock_master st on st.stock_id = od.stk_code
            where od.trans_type = os.trans_type
            and os.trans_type = 30 
            and st.stock_id = ".db_escape($stock_id);

    if ($valida){
        $sql .= " and os.order_date >= ".db_escape($date)." and os.order_date <= ".db_escape($hoy);
    }

    $result = db_query($sql);

    $myrow = db_fetch_row($result);
	
    return $myrow[0];
}

function get_FactoresConversion($stock_id){
        $fLineales = '';
        $fPiezas = '';
        $fKilogramo = '';
        $fM2 = '';

        $fLineales = getFactor('fac_ML', $stock_id);
        $fPiezas = getFactor('fac_PZA', $stock_id);
        $fKilogramo = getFactor('fac_KG', $stock_id);
        $fM2 = getFactor('fac_M2', $stock_id);

        text_row(_("Factor ML:"), 'fac_ML', $fLineales, 5, 20);
        text_row(_("Factor KG:"), 'fac_KG', $fKilogramo, 5, 20);
        text_row(_("Factor PZA:"), 'fac_PZA', $fPiezas, 5, 20);
        text_row(_("Factor M2:"), 'fac_M2', $fM2, 5, 20);
}

function getFactor($nombre, $stock_id){
    
    $factor = '';
    $sql = "SELECT formula FROM 0_conversion_stock WHERE nombre = ".db_escape($nombre)." AND stock_id = ".db_escape($stock_id);    
    $result = db_query($sql);
    
    $row = db_fetch_row($result);
    $factor = $row[0] != '' ? $row[0] : '';
    
    return $factor;

}
?>
