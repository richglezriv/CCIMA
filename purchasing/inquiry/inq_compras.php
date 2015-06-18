<?php
$page_security = 'SA_SUPPTRANSVIEW';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include($path_to_root . "/purchasing/includes/purchasing_ui.inc");

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
if ($use_date_picker)
	$js .= get_js_date_picker();

if (get_post('btnGenerar')) {
    $uri = "repCompras.php?i=".$_POST['OrdersAfterDate']."&f=".$_POST['OrdersToDate'];
    echo '<script>window.open("'.$uri.'");</script>';
}


page(_($help_context = "Consulta de registros de compras"), false, false, "", $js);

start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();


date_cells(_("from:"), 'OrdersAfterDate', '', null, -30);
date_cells(_("to:"), 'OrdersToDate');

echo '<td>';
echo '<input type="submit" value="Generar Reporte" class="inputsubmit" id="btnGenerar" name="btnGenerar"/> ';
echo '</td>';

end_row();

end_table();

end_form();
end_page();
?>
