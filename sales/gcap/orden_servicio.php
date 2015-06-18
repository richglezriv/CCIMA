<?php
$path_to_root = "../..";
$page_security = 'SA_SALESQUOTE';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui/contacts_view.inc");
require($path_to_root . '/sales/gcap/fpdf.php');

$id = $_REQUEST['PARAM_0'];

$sql = "SELECT so.*, cu.debtor_ref as 'name' FROM 1_sales_orders so INNER JOIN 1_debtors_master cu ON so.debtor_no = cu.debtor_no " .
       " WHERE so.trans_type = 30 AND so.order_no = " . db_escape($id);

$result = db_query($sql);
$row = db_fetch($result);

$fecha = $row['delivery_date'];
$folio = $row['reference'];
$cliente = $row['name'];
$recibe = $row['customer_ref'];
$telRecibe = $row['contact_phone'];
$comments = $row['comments'];
$direccion = $row['delivery_address'];
$date = date_create($row['fin_obra']);
$llegada = date_format($date, 'H:i');
$trabajo = $row['trabajo'];

$vendedor = $row['vendedor'];

//quien solicita el servicio (contacto del cliente)
$sql = "SELECT CONCAT(pe.name, ' ', pe.name2) as 'nombre', pe.phone, sm.salesman_name
FROM 1_crm_contacts co
INNER JOIN 1_crm_persons pe ON co.person_id = pe.id
INNER JOIN 1_cust_branch cu ON co.entity_id = cu.debtor_no
LEFT JOIN 1_salesman sm ON sm.salesman_code = cu.salesman
WHERE co.type = 'customer' AND co.action = 'order'
AND co.entity_id = ".db_escape($row['debtor_no']).
" AND cu.branch_code = ".db_escape($row['branch_code']);

$result = db_query($sql);
$row = db_fetch($result);

$solicita = $row['nombre'];
$telSolicita = $row['phone'];

//vendedor
$sql = "SELECT salesman_name FROM 1_salesman WHERE salesman_code = ".db_escape($vendedor);
$result = db_query($sql);
$row = db_fetch($result);
$vendedor = $row['salesman_name'];

$sql = "SELECT so.* FROM 1_sales_order_details so " .
       " WHERE so.trans_type = 30 AND so.order_no = " . db_escape($id);

error_log($sql);
$result = db_query($sql);
$row = db_fetch($result);
$equipo = $row['stk_code'];
$precio = number_format($row['unit_price'], 2, '.', ',');
$precioXUnidad = '$ '.$precio.' por '.$row['unidad'];


class PDF extends FPDF{

    function Header(){
        $this->SetFont('Arial','','15');
        $this->Cell(80);
        // Framed title
        $this->Image('os_layout2.png',0,5);
        $this->Ln();
        // Line break
        
    }

}
$pdf = new PDF('P','mm','A4');

$pdf->AddPage();
$pdf->SetFont('Arial','','8');
$pdf->Cell(10,60,'');
$pdf->Cell(95,77, $fecha);
$pdf->Cell(30,77, $folio);
$pdf->Ln(45);
$pdf->Cell(10,0,'');
$pdf->Cell(60,0, $cliente);
$pdf->Ln(7);
$pdf->Cell(15,0,'');
$pdf->Cell(80,0, $solicita);
$pdf->Cell(50,0, $telSolicita);
$pdf->Ln(5);
$pdf->Cell(15,0,'');
$pdf->Cell(80,0, $recibe);
$pdf->Cell(50,0, $telRecibe);
$pdf->Ln(6);
$pdf->Cell(15,0,'');
$pdf->Cell(80,0, $vendedor);
$pdf->Cell(50,0, $_SESSION["wa_current_user"]->name);
$pdf->Ln(6);
$pdf->Cell(20,0,'');
$pdf->Cell(10,0, $equipo);
$pdf->Ln(6);
$pdf->Cell(20,0,'');
$pdf->Cell(10,0, $direccion);
$pdf->Ln(3);
$pdf->Cell(20,0,'');
$pdf->MultiCell(100,5, $trabajo);
$pdf->SetY(103);
$pdf->Ln(21);
$pdf->Cell(112,0,'');
$pdf->Cell(20,0,$llegada);
$pdf->Ln(15);


$pdf->MultiCell(50,5,$comments);

$pdf->SetY(133);

$pdf->SetX(90);

$pdf->Cell(0,30, $precioXUnidad);
$pdf->Output();
?>

