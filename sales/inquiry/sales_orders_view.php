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
$path_to_root = "../..";

include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$page_security = 'SA_SALESTRANSVIEW';

set_page_security( @$_POST['order_view_mode'],
	array(	'OutstandingOnly' => 'SA_SALESDELIVERY',
			'InvoiceTemplates' => 'SA_SALESINVOICE'),
	array(	'OutstandingOnly' => 'SA_SALESDELIVERY',
			'InvoiceTemplates' => 'SA_SALESINVOICE')
);

if (get_post('type'))
	$trans_type = $_POST['type'];
elseif (isset($_GET['type']) && $_GET['type'] == ST_SALESQUOTE)
	$trans_type = ST_SALESQUOTE;
else
	$trans_type = ST_SALESORDER;

if ($trans_type == ST_SALESORDER)
{
	if (isset($_GET['OutstandingOnly']) && ($_GET['OutstandingOnly'] == true))
	{
		$_POST['order_view_mode'] = 'OutstandingOnly';
        $cadena = user_company() == 0 ? "Buscar Entregas de Obra Pendientes" : _("Search Outstanding Sales Orders");
		$_SESSION['page_title'] = _($help_context = $cadena);
	}
	elseif (isset($_GET['InvoiceTemplates']) && ($_GET['InvoiceTemplates'] == true))
	{
		$_POST['order_view_mode'] = 'InvoiceTemplates';
		$_SESSION['page_title'] = _($help_context = "Search Template for Invoicing");
	}
	elseif (isset($_GET['DeliveryTemplates']) && ($_GET['DeliveryTemplates'] == true))
	{
		$_POST['order_view_mode'] = 'DeliveryTemplates';
		$_SESSION['page_title'] = _($help_context = "Select Template for Delivery");
	}
	elseif (!isset($_POST['order_view_mode']))
	{
		$_POST['order_view_mode'] = false;
		$_SESSION['page_title'] = _($help_context = "Search All Sales Orders");
	}
}
else
{
	$_POST['order_view_mode'] = "Quotations";
	$_SESSION['page_title'] = _($help_context = "Search All Sales Quotations");
}

if (!@$_GET['popup'])
{
	$js = "";
	if ($use_popup_windows)
		$js .= get_js_open_window(900, 600);
	if ($use_date_picker)
		$js .= get_js_date_picker();
	page($_SESSION['page_title'], false, false, "", $js);
}

if (isset($_GET['selected_customer']))
{
	$selected_customer = $_GET['selected_customer'];
}
elseif (isset($_POST['selected_customer']))
{
	$selected_customer = $_POST['selected_customer'];
}
else
	$selected_customer = -1;

//---------------------------------------------------------------------------------------------

if (isset($_POST['SelectStockFromList']) && ($_POST['SelectStockFromList'] != "") &&
	($_POST['SelectStockFromList'] != ALL_TEXT))
{
 	$selected_stock_item = $_POST['SelectStockFromList'];
}
else
{
	unset($selected_stock_item);
}
//---------------------------------------------------------------------------------------------
//	Query format functions
//
function check_overdue($row)
{
	global $trans_type;

	if ($trans_type == ST_SALESQUOTE)
		if (user_company() == 0)
            return (date1_greater_date2(Today(), sql2date($row['inicio_obra'])));
        else 
            return (date1_greater_date2(Today(), sql2date($row['delivery_date'])));
	else
        if (user_company() == 0)
		    return ($row['type'] == 0
			    && date1_greater_date2(Today(), sql2date($row['inicio_obra']))
			    && ($row['TotDelivered'] < $row['TotQuantity']));
        else 
            return ($row['type'] == 0
			    && date1_greater_date2(Today(), sql2date($row['delivery_date']))
			    && ($row['TotDelivered'] < $row['TotQuantity']));
}   

function view_link($dummy, $order_no)
{
	global $trans_type;
	return  get_customer_trans_view_str($trans_type, $order_no);
}

function prt_link($row)
{
	global $trans_type;
    if (user_company() == 1){
        $uri = '';
        if ($trans_type == ST_SALESORDER)
            $uri =  $path_to_root.'/erp_ccima/sales/gcap/orden_servicio.php?PARAM_0='.$row['order_no'];
        else if ($trans_type == ST_SALESQUOTE)
            $uri =  $path_to_root.'/erp_ccima/sales/gcap/cotizacion.php?PARAM_0='.$row['order_no'];

        return '<a href="'.$uri.'" target="_blank">'.set_icon('print.png').'</a>';
        
    }
    else{
         if ($trans_type == ST_SALESQUOTE)
            return print_document_link($row['order_no'], _("Print"), true, $trans_type, ICON_PRINT);
        else if ($trans_type == ST_SALESORDER){
            $uri =  $path_to_root.'/erp_ccima/sales/gcap/contratoConst.php?PARAM_0='.$row['order_no'];
            return '<a href="'.$uri.'" target="_blank">'.set_icon('print.png').'</a>';
        }
            

        
    }
	
}

function edit_link($row) 
{
	if (@$_GET['popup'])
		return '';
	global $trans_type;
	$modify = ($trans_type == ST_SALESORDER ? "ModifyOrderNumber" : "ModifyQuotationNumber");
  return pager_link( _("Edit"),
    "/sales/sales_order_entry.php?$modify=" . $row['order_no'], ICON_EDIT);
}

function dispatch_link($row)
{
	global $trans_type;
	if ($trans_type == ST_SALESORDER)
  		return pager_link( _("Dispatch"),
			"/sales/customer_delivery.php?OrderNumber=" .$row['order_no'], ICON_DOC);
	else		
  		return pager_link( _("Sales Order"),
			"/sales/sales_order_entry.php?OrderNumber=" .$row['order_no'], ICON_DOC);
}

function invoice_link($row)
{
	global $trans_type;
	if ($trans_type == ST_SALESORDER)
  		return pager_link( _("Invoice"),
			"/sales/sales_order_entry.php?NewInvoice=" .$row["order_no"], ICON_DOC);
	else
		return '';
}

function delivery_link($row)
{
  return pager_link( _("Delivery"),
	"/sales/sales_order_entry.php?NewDelivery=" .$row['order_no'], ICON_DOC);
}

function order_link($row)
{
  return pager_link( _("Sales Order"),
	"/sales/sales_order_entry.php?NewQuoteToSalesOrder=" .$row['order_no'], ICON_DOC);
}

function tmpl_checkbox($row)
{
	global $trans_type;
	if ($trans_type == ST_SALESQUOTE)
		return '';
	if (@$_GET['popup'])
		return '';
	$name = "chgtpl" .$row['order_no'];
	$value = $row['type'] ? 1:0;

// save also in hidden field for testing during 'Update'

 return checkbox(null, $name, $value, true,
 	_('Set this order as a template for direct deliveries/invoices'))
	. hidden('last['.$row['order_no'].']', $value, false);
}
//---------------------------------------------------------------------------------------------
// Update db record if respective checkbox value has changed.
//
function change_tpl_flag($id)
{
	global	$Ajax;
	
  	$sql = "UPDATE ".TB_PREF."sales_orders SET type = !type WHERE order_no=$id";

  	db_query($sql, "Can't change sales order type");
	$Ajax->activate('orders_tbl');
}

$id = find_submit('_chgtpl');
if ($id != -1)
	change_tpl_flag($id);

if (isset($_POST['Update']) && isset($_POST['last'])) {
	foreach($_POST['last'] as $id => $value)
		if ($value != check_value('chgtpl'.$id))
			change_tpl_flag($id);
}

$show_dates = !in_array($_POST['order_view_mode'], array('OutstandingOnly', 'InvoiceTemplates', 'DeliveryTemplates'));
//---------------------------------------------------------------------------------------------
//	Order range form
//
if (get_post('_OrderNumber_changed') || get_post('_OrderReference_changed')) // enable/disable selection controls
{
	$disable = get_post('OrderNumber') !== '' || get_post('OrderReference') !== '';

  	if ($show_dates) {
			$Ajax->addDisable(true, 'OrdersAfterDate', $disable);
			$Ajax->addDisable(true, 'OrdersToDate', $disable);
	}

	$Ajax->activate('orders_tbl');
}

if (!@$_GET['popup'])
	start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();
ref_cells(_("#:"), 'OrderNumber', '',null, '', true);
ref_cells(_("Ref"), 'OrderReference', '',null, '', true);
if ($show_dates)
{
  	date_cells(_("from:"), 'OrdersAfterDate', '', null, -30);
  	date_cells(_("to:"), 'OrdersToDate', '', null, 1);
}
locations_list_cells(_("Location:"), 'StockLocation', null, true, true);

if($show_dates) {
	end_row();
	end_table();

	start_table(TABLESTYLE_NOBORDER);
	start_row();
}
stock_items_list_cells(_("Item:"), 'SelectStockFromList', null, true, true);
if (!@$_GET['popup'])
	customer_list_cells(_("Select a customer: "), 'customer_id', null, true, true);
if ($trans_type == ST_SALESQUOTE)
	check_cells(_("Show All:"), 'show_all');
   

submit_cells('SearchOrders', _("Search"),'',_('Select documents'), 'default');
hidden('order_view_mode', $_POST['order_view_mode']);
hidden('type', $trans_type);

end_row();

end_table(1);
//---------------------------------------------------------------------------------------------
//	Orders inquiry table
//
if ($trans_type == ST_SALESQUOTE){
    $onlyAvailable = $_POST['show_all'] == 1 ? FALSE : TRUE;
}
else{
    $onlyAvailable = FALSE;
}
$sql = get_sql_for_sales_orders_view($selected_customer, $trans_type, $_POST['OrderNumber'], $_POST['order_view_mode'],
	@$selected_stock_item, @$_POST['OrdersAfterDate'], @$_POST['OrdersToDate'], @$_POST['OrderReference'], $_POST['StockLocation'], $_POST['customer_id'], $onlyAvailable);


if ($trans_type == ST_SALESORDER)
	$cols = array(
		_("Id") => array('fun'=>'view_link'),
        _("Customer") => array('type' => 'debtor.name' , 'ord' => '') ,
		_("Ref") => array('type' => 'sorder.reference', 'ord' => '') ,
        _("Obra"),
		_("Order Date") => array('type' =>  'date', 'ord' => ''),
		_("Required By") =>array('type'=>'date', 'ord'=>''),
		_("Delivery To"), 
        _("Subtotal") => array ('type' => 'amount'),
        _("IVA") => array ('type' => 'amount'),
		_("Order Total") => array('type'=>'amount', 'ord'=>''),
		'Type' => 'skip',
		_("Currency") => 'skip'
	);
else
	$cols = array(
		_("Id") => array('fun'=>'view_link'),
        _("Customer"),
		_("Ref"),
        _("Obra"),
		_("Quote Date") => 'date',
		_("Required By") => 'date',
		_("Delivery To"), 
        _("Subtotal") => array ('type' => 'amount'),
        _("IVA") => array ('type' => 'amount'),
		_("Quote Total") => array('type'=>'amount', 'ord'=>''),
		'Type' => 'skip',
		_("Currency") => 'skip'
	);
if ($_POST['order_view_mode'] == 'OutstandingOnly') {
	//array_substitute($cols, 4, 1, _("Cust Order Ref"));
	array_append($cols, array(
		array('insert'=>true, 'fun'=>'dispatch_link'),
		array('insert'=>true, 'fun'=>'edit_link')));

} elseif ($_POST['order_view_mode'] == 'InvoiceTemplates') {
	array_substitute($cols, 4, 1, _("Description"));
	array_append($cols, array( array('insert'=>true, 'fun'=>'invoice_link')));

} else if ($_POST['order_view_mode'] == 'DeliveryTemplates') {
	array_substitute($cols, 4, 1, _("Description"));
	array_append($cols, array(
			array('insert'=>true, 'fun'=>'delivery_link'))
	);

} elseif ($trans_type == ST_SALESQUOTE) {
	 array_append($cols,array(
					array('insert'=>true, 'fun'=>'edit_link'),
					array('insert'=>true, 'fun'=>'order_link'),
					array('insert'=>true, 'fun'=>'prt_link')));
} elseif ($trans_type == ST_SALESORDER) {
	 array_append($cols,array(
			_("Tmpl") => array('insert'=>true, 'fun'=>'tmpl_checkbox'),
					array('insert'=>true, 'fun'=>'edit_link'),
					array('insert'=>true, 'fun'=>'prt_link')));
};


$table =& new_db_pager('orders_tbl', $sql, $cols);
$table->set_marker('check_overdue', _("Marked items are overdue."));

$table->width = "80%";

display_db_pager($table);
submit_center('Update', _("Update"), true, '', null);

if (!@$_GET['popup'])
{
	end_form();
	end_page();
}
?>
