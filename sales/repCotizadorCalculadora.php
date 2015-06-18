<?php
$path_to_root = "..";
$page_security = 'SA_SALESORDER';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui/contacts_view.inc");

require($path_to_root . '/sales/gcap/fpdf.php');

class Cotizador{
    var $concepto;
    var $unidad;
    var $cantidad;
    var $unitario;
    var $importe;

    function Cotizador($c){
        $this->concepto = $c;
    }

    function calcula_importe(){
        $this->importe = ($this->cantidad * $this->unitario);
    }
}


class PDF extends FPDF{
    function Header(){
        $this->SetFont('Arial','','15');
        $this->Cell(80);
        // Framed title
        $this->Image('ccima.png',45,0);
        // Line break
        $this->Ln(20);
    }

}
$deCalculadora = FALSE;
$sql = "SELECT cot.*, os.calculadora FROM 0_tb_cotizador cot
        INNER JOIN 0_sales_orders os on cot.folio = os.reference 
        where cot.folio = ".db_escape($_REQUEST['fol']);

$result = db_query($sql);
$row = db_fetch($result);

$iniciales = '';
$cabecera = $row['header'];
$notas = $row['notes'];
$contacts = new contacts('contacts', $row['customer_id'], 'customer');
$arreglo = $contacts->get_contacto_cotizado($row['contact_id']);
$salesman = get_salesmen(TRUE);
while ($myrow = db_fetch($salesman))
{
    if ($myrow['salesman_code'] == $row['salesman_id']){
        $iniciales = $myrow['initials'];
    }
}

$conceptos = array();
if ($row['calculadora'] != '0'){
    
    $deCalculadora = TRUE;
    $sql = "SELECT * FROM 0_calculadora_concepto WHERE id_calc_reg = ".db_escape($row['calculadora']);
    $result = db_query($sql);
    while($row = db_fetch($result)){
        $indice = count($conceptos);
        $conceptos[$indice] = array($row['concepto']);
    }
    
    
}

$cotizados = array();
$sql = "select sd.*, sm.units from 0_sales_order_details sd 
        inner join 0_sales_orders so on so.order_no = sd.order_no
        inner join 0_stock_master sm on sm.stock_id = sd.stk_code
        where so.reference = ".db_escape($_REQUEST['fol']);
$granTotal = 0.0;
$datos = db_query($sql);
while($reg = db_fetch($datos)){
    $indice = count($cotizados);
    $cotizador = new Cotizador($reg['description']);
    $cotizador->unidad = $reg['units'];
    $cotizador->cantidad = $reg['quantity'];
    $cotizador->unitario = $reg['unit_price'];
    $cotizador->calcula_importe();

    $granTotal = $granTotal + $cotizador->importe;
    $cotizados[$indice] = $cotizador;    
}


if ($deCalculadora == TRUE){
    //Concepto UNO LAMINA
    $cotConceptos = array();
    $cotizador = new Cotizador($conceptos[0][0]);
    $cotizador->unidad = "PZA";
    $cotizador->cantidad = $cotizados[0]->cantidad;
    $cotizador->unitario = $cotizados[0]->unitario;
    $cotizador->calcula_importe();
    $cotConceptos[0] = $cotizador;
    //Concepto DOS GRUAS Y ROLADORAS
    $cotizador = new Cotizador($conceptos[1][0]);
    $cotizador->unidad = "DIA";
    $cotizador->cantidad = ($cotizados[1]->cantidad + $cotizados[2]->cantidad) / 2;
    $cotizador->unitario = $cotizados[1]->unitario + $cotizados[2]->unitario;
    $cotizador->calcula_importe();
    $cotConceptos[1] = $cotizador;

    //Concepto TRES PLACAS Y TORNILLOS
    $cotizador = new Cotizador($conceptos[2][0]);
    $cotizador->unidad = "PZA";
    $cotizador->cantidad = ($cotizados[3]->cantidad + $cotizados[4]->cantidad) / 2;
    $cotizador->unitario = $cotizados[3]->unitario + $cotizados[4]->unitario;
    $cotizador->calcula_importe();
    $cotConceptos[2] = $cotizador;
    
    
    //$cotizados = $cotConceptos;
}

function getMes(){
    $mes = date('M');
    if ($mes == 'Jan'){return 'Enero';}
    else if ($mes == 'Feb'){return 'Febrero';}
    else if ($mes == 'Mar'){return 'Marzo';}
    else if ($mes == 'Apr'){return 'Abril';}
    else if ($mes == 'May'){return 'Mayo';}
    else if ($mes == 'Jun'){return 'Junio';}
    else if ($mes == 'Jul'){return 'Julio';}
    else if ($mes == 'Ago'){return 'Agosto';}
    else if ($mes == 'Sep'){return 'Septiembre';}
    else if ($mes == 'Oct'){return 'Octubre';}
    else if ($mes == 'Nov'){return 'Noviembre';}
    else if ($mes == 'Dec'){return 'Diciembre';}
}

$pdf = new PDF();
//$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',8);
$fecha = date('d').' de '.getMes().' de '.date('Y');
$pdf->Cell(150,0,'QUERETARO, QRO. A ' . $fecha);
$pdf->Cell(0,0,'COT No.'.$row['folio']);
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->Cell(150,20,"AT'N ".$arreglo[0]);
$pdf->Cell(0,10,$iniciales);
$pdf->SetFont('Arial','',8);
$pdf->Ln();
$pdf->Cell(150,8,$arreglo[3]);
$pdf->Ln();
$pdf->Cell(150,0,$arreglo[2]);
$pdf->Ln();
$pdf->Cell(150,8,$arreglo[1]);
$pdf->Ln();
$pdf->SetFont('Arial','B',8);
$pdf->Cell(150,10,"PRESENTE");
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(200,4,$cabecera,0,2);
$pdf->Ln();

$pdf->SetFont('Arial','B',7);
$pdf->Cell(20,7,'PART.',0,0,'C');
$pdf->Cell(90,7,'CONCEPTO.',0,0,'C');
$pdf->Cell(20,7,'UNIDAD.',0,0,'C');
$pdf->Cell(20,7,'CANTIDAD.',0,0,'C');
$pdf->Cell(20,7,'P.U.',0,0,'C');
$pdf->Cell(20,7,'IMPORTE.',0,0,'C');
$pdf->Ln();
$pdf->SetFont('Arial','',7);
//todos los conceptos
$contador = 1;
foreach($cotizados as $registro){
    $y1 = $pdf->GetY();
    $x = $pdf->GetX();
    $pdf->Cell(20,0,$contador.'.-',0,0,'C');

    $pdf->MultiCell(90,3, $registro->concepto);
    $y2 = $pdf->GetY();
    $yh = $y2 - $y1;
    $pdf->SetXY($x + 110, $y1);
    $pdf->Cell(20,0,$registro->unidad,0,0,'C');
    $pdf->Cell(20,0,$registro->cantidad,0,0,'C');
    $pdf->Cell(20,0,'$ '.price_format($registro->unitario),0,0,'C');
    $pdf->Cell(20,0,'$ '.price_format($registro->importe),0,0,'C');
    $pdf->Ln(10);
    $contador = $contador + 1;
}

//total
$pdf->Ln();
$pdf->Ln();
$pdf->Cell(155,7,'');
$pdf->Cell(17,7,'IMPORTE');
$pdf->Cell(20,7,"$ ".price_format($granTotal));
$pdf->Ln();
$pdf->Ln();
$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,4,'NOTA:',0,0,'C');
$pdf->SetFont('Arial','',7);
$pdf->MultiCell(150,4,$notas);
$pdf->Ln();
$pdf->Ln();
$pdf->SetFont('Arial','B',8);
$pdf->Cell(0,30,'ATENTAMENTE',0,0,'C');
$pdf->SetFont('Arial','',8);
$pdf->Ln();
$firma = $_REQUEST['firma'];
if ($firma == 0){
    $pdf->Cell(0,4,'Ing. Federico Benitez Aguilar',0,0,'C');    
}
else {
    $pdf->Cell(0,4,'Ing. Ricardo Medina Rojas',0,0,'C');    
}

$pdf->Ln();
$pdf->Cell(0,4,'TELS: (442) 291 22 23 Y 291 23 24 FAX EXT. 1',0,0,'C');
$pdf->Ln();
$pdf->Cell(0,4,'CARR. QUERETARO/SAN LUIS POTOSI, KM 17.5, QRO.',0,0,'C');

$pdf->Output();
?>
