<?php
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_ro_root ."/sales/includes/sales_order_db.inc");


$page_security = 'SA_OPEN';

if($_SESSION['wa_current_user']->access != 10 && $_SESSION['wa_current_user']->access != 2){
    $page_security = 'SA_DENIED';    
}

if ($use_date_picker)
	$js .= get_js_date_picker();
    



page("Seguimiento de ordenes de MEGA", false, false, "", $js);
start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();

    date_cells(_("from:"), 'OrdersAfterDate', '', null, -30);
  	date_cells(_("to:"), 'OrdersToDate', '', null, 1);
    customer_list_cells(_("Select a customer: "), 'customer_id', null, true, true);
    submit_cells('SearchOrders', _("Search"),'',_('Select documents'), false);
end_row();
end_table(1);

$inicio = $_POST['OrdersAfterDate'];
$fin = $_POST['OrdersToDate'];
$cliente = $_POST['customer_id'];

if($_POST['SearchOrders']){
    start_table(1, 'width=60%');
    $result = get_Ordenes_Enteradas($cliente, $inicio, $fin);    
    start_row();
    echo('<td class="label">Referencia [MEGA]</td>');
    echo('<td class="label">Almacen</td>');
    echo('<td class="label">Compras</td>');
    echo('<td class="label">Administracion</td>');
    echo('<td class="label">Direccion</td>');
    end_row();


   while($myrow = db_fetch($result)){
        start_row();
        label_cell($myrow['reference']);
	    label_cell($myrow['almacen'] == 0 ? 'No Enterado' : 'Enterado');
        label_cell($myrow['compras'] == 0 ? 'No Enterado' : 'Enterado');
        label_cell($myrow['administracion'] == 0 ? 'No Enterado' : 'Enterado');
        label_cell($myrow['direccion'] == 0 ? 'No Enterado' : 'Enterado');
        end_row();
    }
    end_table();

}




end_form();
end_page();
?>