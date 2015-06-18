<?php
$page_security = 'SA_SUPPTRANSVIEW';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root."/purchasing/includes/purchasing_db.inc");

$begin = date2sql($_REQUEST['i']);
$end = date2sql($_REQUEST['f']);

$sql = " select po.reference, su.supp_ref, pd.item_code, pd.description, 
            pd.delivery_date, pd.quantity_ordered, pd.quantity_received, 
            pd.unit_price, (pd.quantity_received * pd.unit_price) as total, 
            loc.location_name, po.requisition_no
            from 0_purch_orders po 
            inner join 0_purch_order_details pd on po.order_no = pd.order_no
            inner join 0_suppliers su on po.supplier_id = su.supplier_id
            inner join 0_locations loc on loc.loc_code = po.into_stock_location
            where pd.delivery_date >= ".db_escape($begin)." and pd.delivery_date <= ".db_escape($end);

$result = db_query($sql);

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=compras.xls");

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title></title>
    </head>
    <body>
        <table>
            <tr>
                <td>Almacen</td>
                <td>Producto</td>
                <td>Fecha</td>
                <td>O.C.</td>
                <td>Factura</td>
                <td>Unidades</td>
                <td>Costo Unitario</td>
                <td>Neto</td>
                <td>IVA</td>
                <td>Total</td>
                <td>Proveedor</td>
            </tr>
<?php
                while ($myrow = db_fetch($result))
		        {
                    $iva = $myrow['total'] * .16;
                    $total = $myrow['total'] + $iva;
                    echo '<tr>';
                    echo '<td>'.$myrow['location_name'].'</td>';
                    echo '<td>'.$myrow['description'].'</td>';
                    echo '<td>'.$myrow['delivery_date'].'</td>';
                    echo '<td>'.$myrow['reference'].'</td>';
                    echo '<td>'.$myrow['requisition_no'].'</td>';
                    echo '<td>'.$myrow['quantity_received'].'</td>';
                    echo '<td>'.$myrow['unit_price'].'</td>';
                    echo '<td>'.$myrow['total'].'</td>';
                    echo '<td>'.$iva.'</td>';
                    echo '<td>'.$total.'</td>';
                    echo '<td>'.$myrow['supp_ref'].'</td>';
                    echo '</tr>';
                }
?>
        </table>
    </body>
</html>
