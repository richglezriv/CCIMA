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

$cotizacion = $_REQUEST['cot'];
$sql = "SELECT * FROM 0_tb_cotizador WHERE id = ".db_escape($cotizacion);
$result = db_query($sql);
$row = db_fetch($result);

$iniciales = '';
$contacts = new contacts('contacts', $row['customer_id'], 'customer');
$arreglo = $contacts->get_contacto_cotizado($row['contact_id']);

$salesman = get_salesmen(TRUE);
while ($myrow = db_fetch($salesman))
    {
        if ($myrow['salesman_code'] == $row['salesman_id']){
            $iniciales = $myrow['initials'];
        }
    }

$cotizados = array();

$sql = "SELECT * FROM 0_tb_datos_cotizados where cotizador_id = ".db_escape($cotizacion);
$granTotal = 0.0;
$datos = db_query($sql);
while($reg = db_fetch($datos)){
    $indice = count($cotizados);
    $cotizador = new Cotizador($reg['concepto']);
    $cotizador->unidad = $reg['unidad'];
    $cotizador->cantidad = $reg['cantidad'];
    $cotizador->unitario = $reg['precio'];
    $cotizador->calcula_importe();

    $granTotal = $granTotal + $cotizador->importe;
    $cotizados[$indice] = $cotizador;    
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
$pdf->MultiCell(200,4,$row['header'],0,2);
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
    $pdf->Cell(20,3,$contador.'.-',0,0,'C');
    $cellHeight = strlen($registro->concepto) / 85;
    $pdf->MultiCell(90,3, utf8_decode($registro->concepto));
    $y2 = $pdf->GetY();
    $yh = $y2 - $y1;
    $pdf->SetXY($x + 110, $y1);
    $pdf->Cell(20,0,$registro->unidad,0,0,'C');
    $pdf->Cell(20,0,$registro->cantidad,0,0,'C');
    $pdf->Cell(20,0,'$ '.price_format($registro->unitario),0,0,'C');
    $pdf->Cell(20,0,'$ '.price_format($registro->importe),0,0,'C');
    
    $pdf->Ln($yh + 3);

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
$pdf->MultiCell(150,4,utf8_decode($row['notes']));
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
