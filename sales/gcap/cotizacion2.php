<?php
$path_to_root = "../..";
$page_security = 'SA_SALESQUOTE';
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
$folio = '';
class PDF extends FPDF{

    function Header(){
        $this->SetFont('Arial','','15');
        $this->Cell(80);
        // Framed title
        $this->Image('ccima.png',5,5);
        $this->Cell(100,0,'COTIZACION',0,0,'C');
        $this->Image('logo_rentas.png',180,5);
        // Line break
        
    }

    function setFolio($folio){
        $this->SetFont('Arial','','12');
        $this->Ln(7);
        $this->Cell(80);
        $this->Cell(100,0,$folio,0,0,'C');
        $this->Ln(8);
    }

}

$cotizacion = $_REQUEST['cot'];
$sql = "SELECT * FROM 1_tb_cotizador WHERE id = ".db_escape($cotizacion);

$result = db_query($sql);
$row = db_fetch($result);
$folio = $row['folio'];

$sql = "SELECT * FROM 1_stock_master WHERE stock_id = ".db_escape($row['id_inventario']);
error_log($sql);
$result = db_query($sql);
$inventario = db_fetch($result);

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

$sql = "SELECT * FROM 1_tb_datos_cotizados where cotizador_id = ".db_escape($cotizacion);
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



//generacion de pdf
$pdf = new PDF();

//$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->setFolio($folio);
$pdf->SetFont('Arial','',8);
$pdf->Cell(95,0,$arreglo[0],0,0);
$pdf->Cell(95,0,$arreglo[6],0,0);
$pdf->Ln(5);
$pdf->Cell(95,0,$arreglo[2],0,0);
$pdf->Cell(95,0,$arreglo[7],0,0);
$pdf->Ln(5);
$pdf->Cell(95,0,$arreglo[1],0,0);
$pdf->Cell(95,0,$arreglo[8],0,0);
$pdf->Ln(5);
$pdf->Cell(95,0,$arreglo[5],0,0);
$pdf->Cell(95,0,$arreglo[4],0,0);
$pdf->Ln(5);

$pdf->Cell(190,0, $row['header']);
$pdf->Ln(10);

//datos cotizados
$pdf->SetFillColor(255,0,0);
$pdf->Cell(40,5,'CONCEPTO',0,0,'C',TRUE);
$pdf->Cell(40,5,'UNIDAD',0,0,'C',TRUE);
$pdf->Cell(30,5,'CANTIDAD',0,0,'C',TRUE);
$pdf->Cell(40,5,'P.U.',0,0,'C',TRUE);
$pdf->Cell(40,5,'IMPORTE',0,0,'C',TRUE);
$pdf->Ln();
//ciclo de datos
$pdf->SetFillColor(255,255,255);
foreach($cotizados as $registro){
    $pdf->Cell(40,5,$registro->concepto,0,0,'L',TRUE);
    $pdf->Cell(40,5,$registro->unidad,0,0,'C',TRUE);
    $pdf->Cell(30,5,$registro->cantidad,0,0,'C',TRUE);
    $pdf->Cell(40,5,'$ '.$registro->unitario,0,0,'R',TRUE);
    $pdf->Cell(40,5,'$ '.$registro->importe,0,0,'R',TRUE);
    $pdf->Ln();
}
$pdf->SetFillColor(255,0,0);
$pdf->Cell(150,5,'TOTAL',0,0,'C',TRUE);
$pdf->SetFont('Arial','B','8');
$pdf->Cell(40,5,'$ '.$granTotal,0,0,'R',FALSE);
$pdf->Ln(10);
$pdf->SetFont('Arial','','12');
$pdf->Cell(90,5,'Alcances CCIMA');
$pdf->Cell(90,5,'Alcances del Cliente');
$pdf->Ln(5);
$y1 = $pdf->GetY();
$pdf->SetFont('Arial','','8');
$pdf->MultiCell(90,5,'*Personal: 1 Operador de grua hidraulica.			
*Accesorios de Izaje basicos (estrobos, grilletes, eslingas, etc.).			
*El operador seguira las instrucciones del cliente durante el servicio.			
*El operador podra opinar durante el servicio, pero el cliente sera el unico responsable de los riesgos y la maniobra.			
*El operador tiene la posibilidad de declinar las instrucciones del cliente cuando estas pongan en riesgo al personal y a la unidad en servicio.');
$pdf->SetXY(100, $y1);
$pdf->MultiCell(100,5,'*Terreno a nivel, bien compactado y libre de obstaculos aereos, superficiales e inducidos.			
*En el sitio de los trabajos debera garantizarse la integridad (seguridad) del equipo.			
*Todos los permisos necesarios para el acceso, salida y libre transito de nuestro personal y equipos dentro de sus instalaciones.			
*Limitacion de acceso a areas de la grua hidraulica (cintas de prevencion, conos, etc.).');
$pdf->Ln(10);
$pdf->SetFont('Arial','','12');
$pdf->Cell(190,0,'Condiciones Generales',0,0,'C');
$pdf->Ln(5);
$pdf->SetFont('Arial','','8');
$y1 = $pdf->GetY();
$pdf->MultiCell(90,5,'*El equipo debera de trabajar dentro de sus capacidades y especificaciones tecnicas.
*El tiempo del equipo empieza a contar desde el momento en el que sale de patio y termina hasta que regresa al mismo.
*En caso de ser requerido el servicio de flete, se cobraran $15.00 por kilometro a recorrer, extra al precio unitario por hora.
*Trabajo fuera de zona urbana tienen costo de $15.00 por km.
*En caso de requerir ayudante maniobrista, el costo por persona es de $150.00  por hora y deberá ser solicitado al momento de requerir el servicio.
*El equipo extra deberá ser solicitado por el cliente por anticipado y su costo se sumará al importe de la cotización.
*Las jornadas normales de trabajo son de 8hrs y 1hra de comida, nuestros horarios de servicio de 8:00hrs a 17:00hrs de Lunes a Viernes.
*Si se tiene que laborar fuera de los horarios de servicio ya descritos, sábado, domingo o días festivos se cobrará un cargo adicional del 20% por hora sobre el precio normal.
*Es responsabilidad del cliente los da'.utf8_decode("ñ").'os causados al personal ajeno a nuestra empresa que se encuentre dentro del radio de trabajo del equipo.');

$pdf->SetXY(100, $y1);
$pdf->MultiCell(90,5, '*Es responsabilidad del cliente los da'.utf8_decode("ñ").'os sufridos en el equipo y/o accesorios, durante el transporte y/o maniobras del mismo.
*Es responsabilidad del cliente el tener todos los permisos necesarios ante la autoridad competente para la realizacion de los trabajos.
*El servicio debera ser pagado 100% por anticipado
*Las horas se cobran completas
*No se realiza rembolso alguno
*Nuestros precios son mas IVA
*La cotizacion es valida por 30 dias
*Servicio sujeto a disponibilidad del equipo
*Contamos con servicio de maniobra la cual se contrata por un tanto.
*En los servicios de renta las maniobras son completa responsabilidad del cliente.
* En caso de requerir un seguro de las piezas a mover, la grua no cuenta con servicio de seguro de maniobra, por tanto, el cliente lo contratara por separado.');
$pdf->Ln(5);
$pdf->Cell(90,5,'');

$pdf->SetFont('Arial','B','8');
$pdf->Cell(90,5,'Podemos mejorar cualquier presupuesto');

$pdf->AddPage();

$pdf->setFolio($folio);
$pdf->SetFont('Arial','','8');
$pdf->Cell(190,0,'EN LA SIGUIENTE GRAFICA SE ESPECIFICAN LAS CAPACIDADES DE TRABAJO DE LA UNIDAD',0,0);
$pdf->Image('http://localhost:3391/company/1/images/'.$inventario["stock_id"].'/capacidades.png',5,30);
$pdf->Ln(30);
$y1 = $pdf->GetY();

$pdf->Ln(120);
$pdf->Cell(95,10,'Confirmo y estoy de acuerdo con las condiciones anteriores',0,0,'C');
$pdf->Cell(90,10,'GRUPO CCIMA S.A. de C.V.',0,0,'C');

$pdf->Ln(20);
$pdf->Cell(90,10,'Nombre y Firma','TOP',0,'C');
$pdf->Cell(3,10,'');
$pdf->Cell(90,10,'Nombre y Firma','TOP',0,'C');

$pdf->SetY(-35);
$pdf->SetFont('Arial','B','8');
$pdf->Cell(200,0, 'Carretera: QRO - SAN LUIS POTOSI K.M. 17.5',0,0,'C');
$pdf->Ln(3);
$pdf->Cell(200,0, 'Tels: (442) 291 22 23 y 291 23 24',0,0,'C');
$pdf->Ln(3);
$pdf->Cell(200,0, 'rentas@grupoccima.com - auxrentas@grupoccima.com - gerenciarentas@grupoccima.com',0,0,'C');
$pdf->Ln(5);
$pdf->SetFillColor(255,0,0);
$pdf->Cell(190,3,'',0,0,'C',TRUE);

$pdf->Output();


?>