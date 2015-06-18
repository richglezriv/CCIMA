<?php
$path_to_root = "../..";

include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui/contacts_view.inc");

include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$page_security = 'SA_SALESTRANSVIEW';

set_page_security( @$_POST['order_view_mode'],
	array(	'OutstandingOnly' => 'SA_SALESDELIVERY',
			'InvoiceTemplates' => 'SA_SALESINVOICE'),
	array(	'OutstandingOnly' => 'SA_SALESDELIVERY',
			'InvoiceTemplates' => 'SA_SALESINVOICE')
);

$folio = "";
$js = '';
if ($use_date_picker) {
	$js .= get_js_date_picker();
}

if ($_REQUEST['cot'] !=''){
    $id = $_REQUEST['cot'];
    $sql = "DELETE FROM  0_tb_datos_cotizados  where cotizador_id = " .db_escape($id);
    db_query($sql);

    $sql = "DELETE FROM  0_tb_cotizador  where id =" .db_escape($id);
    db_query($sql);
}
function prt_link($row)
{
	global $trans_type;
        $uri = "../../sales/repCotizador.php?cot=".$row["id"]."&firma=0";
        return '<a href="'.$uri.'" target="_blank">'.set_icon('print.png', 'imprimir').'</a>';
        
}

function edit_link($row){
    $uri = "../../sales/cotizador.php?cot=".$row["id"]."&firma=0";
    return '<a href="'.$uri.'" >'.set_icon('edit.gif','editar').'</a>';

}
function del_link($row){
    $uri = "cotizaciones_obra.php?cot=".$row["id"]."&firma=0";
    return '<a href="'.$uri.'" >'.set_icon('delete.gif','eliminar').'</a>';

}
function set_so($row){
    $uri = "../../sales/sales_order_entry.php?NewOrder=Yes&qo=".$row["id"];
    return '<a href="'.$uri.'" >'.set_icon('invoice.gif','generar orden de MEGA').'</a>';
}

page("Consulta de Cotizaciones de Obra", false, false, "", $js);
start_form();
start_table(TABLESTYLE_NOBORDER);
start_row();
ref_cells(_("Ref"), 'OrderReference', '',null, '', true);

  	date_cells(_("from:"), 'OrdersAfterDate', '', null, -30);
  	date_cells(_("to:"), 'OrdersToDate', '', null, 1);

customer_list_cells(_("Select a customer: "), 'customer_id', null, true, true);
echo ('<td><input type="submit" id="SearchOrders" name="SearchOrders" value="Buscar"/></td>');
end_row();
end_table();

if ($_POST['SearchOrders'] == "Buscar"){
    $date_after = date2sql($_POST['OrdersAfterDate']);
	$date_before = date2sql($_POST['OrdersToDate']);
    $sql = "select co.id,cu.debtor_ref, co.folio, co.fecha, sum(da.precio * da.cantidad) as subtotal 
            from 0_tb_cotizador co
            inner join 0_tb_datos_cotizados da on da.cotizador_id = co.id
            inner join 0_debtors_master cu on cu.debtor_no = co.customer_id";

    $sql .=  " WHERE co.fecha >= '$date_after'"
		    ." AND co.fecha <= '$date_before'";

    if ($_POST['OrderReference'] != ''){
        $sql .= " AND co.folio = ".db_escape($_POST['OrderReference']);
    }


    if ($_POST['customer_id'] != ''){
        $sql .= " AND cu.debtor_no = ".db_escape($_POST['customer_id']);
    }

    $sql .= " GROUP BY co.id";

    $cols = array(_("Id"),
                _("Customer"),
                _("Ref"),
                _("Order Date"),
                _("Subtotal")=> array ('type' => 'amount'), 
                array('insert'=>true, 'fun'=>'prt_link'),
                array('insert'=>true, 'fun'=>'edit_link'),
                array('insert'=>true, 'fun'=>'del_link'),
                array('insert'=>TRUE, 'fun'=>'set_so')
            );
    $table =& new_db_pager('orders_tbl', $sql, $cols);
    $table->width = "80%";
    
    display_db_pager($table);

    
    
}

end_form();
end_page();
?>
