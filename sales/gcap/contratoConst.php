<?php
$path_to_root = "../..";
$page_security = 'SA_SALESQUOTE';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui/contacts_view.inc");
require($path_to_root . '/sales/gcap/fpdf.php');

class PDF extends FPDF{

    function Header(){
        $this->SetFont('Arial','','15');
        $this->Cell(80);
        // Framed title
        $this->Ln(15);
        // Line break
        
    }

}

function dec($letra){
    $decoded = utf8_decode($letra);
    return $decoded;
}

$id = $_REQUEST['PARAM_0'];
//sales order
$sql = "SELECT so.*, cu.name, sd.description  
        FROM 0_sales_orders so INNER JOIN 0_debtors_master cu ON so.debtor_no = cu.debtor_no  
        INNER JOIN 0_sales_order_details sd ON sd.order_no = so.order_no " .
        " WHERE so.order_no = " . db_escape($id);

$result = db_query($sql);
$row = db_fetch($result);
$cotizacion = $row['reference'];
$cliente = $row['name'];
$entrega = $row['delivery_address'];
//contact data
$sql = "select pe.* FROM
0_crm_persons pe 
INNER JOIN 0_crm_contacts co on co.person_id = pe.id
WHERE co.type = 'customer' AND action='delivery' 
AND co.entity_id = " . db_escape($row['debtor_no']);
$result = db_query($sql);
$contacto = db_fetch($result);


//----- PDF Generation
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B','7');

$pdf->MultiCell(190,4,utf8_decode(sprintf('CONTRATO PRIVADO DE COMPRAVENTA CON RESERVA DE DOMINIO QUE CELEBRAN POR UNA GRUPO CCIMA S.A. DE C.V., A QUIEN EN LO SUCESIVO SE LE DENOMINARÁ "EL CONTRATISTA" Y POR LA OTRA, EL %s A QUIEN VA DIRIGIDA LA COTIZACIÓN %s MISMAS QUE SE ANEXA AL PRESENTE CONTRATO Y A QUIEN EN LO SUCESIVO SE LE DENOMINARÁ "EL CLIENTE" A LO ANTERIOR DE LAS SIGUIENTES:', $cliente, $cotizacion)));

$pdf->Line(15);
$pdf->Cell(190,10,utf8_decode('C L Á U S U L A S'),0,1,'C');

$pdf->Line(10);
$text = sprintf('                El "CONTRATISTA", con domicilio fiscal en Carretera Querétaro - San Luis Potosí, KM 17.5, Col. La Cruz, Delegación de Santa Rosa Jáuregui, Querétaro, vende al "CLIENTE", los productos y servicios que se especifican en la cotización número %s, contenida en el anverso del presente documento, con reserva de dominio.

La operación de compraventa a que se refiere el presente Contrato, se hace en la inteligencia que de los precios unitarios, costos de mano de obra y transportación, han sido previamente aprobados por el "CLIENTE", manifestando ambas partes estar conformes con él, por ser el justo. Todo trabajo solicitado no especificado en la cotización, será considerado adicional.', $cotizacion);
$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'PRIMERA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,utf8_decode($text),0,'J');

$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'SEGUNDA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                     El "CLIENTE" con domicilio en: '.$entrega.' se obliga a pagar el precio TOTAL de la operaci'.
dec('ó').'n siendo el 50% de anticipo, 30% contra entrega de material y 20% contra avances. Por falta de pago puntual "EL CONTRATISTA" podr'.
dec('á').' suspender los trabajos y/o suministros de material, sin que para ello sea necesario dar aviso a "EL CLIENTE" y constituya una falta al "CONTRATISTA"',0,'J');


$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'TERCERA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                "EL CONTRATISTA" entregar'.
dec('á').' los materiales en el tiempo estipulado dentro de la cotizaci'.
dec('ó').'n autorizada dicho tiempo podr'.
dec('á').' variar dependiendo del tama'.
dec('ñ').'o y complejidad de los trabajos a realizar. ',0,'J');

$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'CUARTA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                  "EL CLIENTE" se constituye en mero depositario de los materiales y herramientas necesarias para la ejecuci'.
dec('ó').'n de la obra, en cuanto no se hayan cubierto al cien por ciento el pago de los mismos, proporcionando un '.
dec('á').'rea adecuada para su almacenaje. Cualquier deterioro o p'.
dec('é').'rdida, tanto de los materiales como de las herramientas en dep'.
dec('ó').'sito de "EL CLIENTE" ser'.
dec('á').'n a cargo del mismo.',0,'J');


$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'QUINTA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                Cuando no pueda darse inicio o se suspenda la obra por causas imputables a "EL CLIENTE", no se dar'.
dec('á').' reinicio, si antes no existe convenio de las partes, asent'.dec('á').'ndose en bit'.dec('á').'cora de obra y prorrog'.
dec('á').'ndose los tiempos de entrega. Nos reservamos el derecho de aceptar modificaciones.',0,'J');


$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'SEXTA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                Todos aquellos trabajos, materiales y/o instalaciones que no est'.
dec('á').'n especificados dentro de la cotizaci'.dec('ó').'n, as'.dec('í').' como vol'.
dec('ú').'menes excedentes, se consideran como adicionales y tendr'.dec('á').'n un costo extra del cual se entregara cotizaci'.
dec('ó').'n y se deber'.dec('á').' cubrir dentro de las mismas condiciones de pago que el resto del importe.',0,'J');


$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'S'.dec('É').'PTIMA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                Ser'.dec('á').'n a cargo de "EL CLIENTE" todos los tr'.
dec('á').'mites y pagos correspondientes a licencias de construcci'.dec('ó').'n y dem'.
dec('á').'s requisitos que las autoridades administrativas del lugar de residencia de la obra a ejecutarse imponga, liberando a "EL CONTRATISTA", de cualquier responsabilidad derivada por esos conceptos.',0,'J');


$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'OCTAVA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                 "El CONTRATISTA" asume la responsabilidad derivada de la relaci'.
dec('ó').'n individual de trabajo con sus trabajadores, manifestando encontrarse debidamente registrado como patr'.
dec('ó').'n ante el Instituto Mexicano del Seguro Social bajo el n'.dec('ú').'mero E236813910.',0,'J');

$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'NOVENA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                 "EL CONTRATISTA" se reserva la propiedad de todos y cada uno de los bienes a que el presente contrato se refiere, hasta en tanto no sea liquidada la totalidad del precio convenido. Por tal motivo en caso de falta o pago o cualquier otro incumplimiento al presente contrato, "EL CONTRATISTA" podr'.
dec('á').' optar entre pedir cumplimiento forzoso o la rescisi'.
dec('ó').'n de contrato. En cualquiera de los casos "EL CONTRATISTA" podr'.dec('á').' recoger sus bienes y / o materiales, oblig'.
dec('á').'ndose "EL CLIENTE" a permitir el acceso para su retiro y renunciando a cualquier tipo de oposici'.dec('ó').'n.',0,'J');


$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'DECIMA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                En caso de mora en el pago del presente contrato "EL CLIENTE" pagar'.dec('á').' un inter'.
dec('é').'s mensual de CUATRO POR CIENTO, sobre las cantidades que no sean cubiertas en su oportunidad sin perjuicio del derecho conteniendo en el '.dec('ú').'ltimo p'.dec('á').'rrafo de la cl'.dec('á').'usula segunda, que refiere a que "EL CONTRATISTA" podr'.dec('á').' suspender los trabajos a ejecutar. ',0,'J');

$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'D'.dec('É').'CIMA PRIMERA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                               "EL CONTRATISTA" no se har'.dec('á').' responsable por da'.
dec('ñ').'os y tiempos muertos en el proceso de la obra, adjudicados a fen'.
dec('ó').'menos naturales y clima que impida ejecutar el trabajo.');

$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'D'.dec('É').'CIMA SEGUNDA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                                La cotizaci'.
dec('ó').'n no incluye la elaboraci'.dec('ó').'n de planos arquitect'.
dec('ó').'nicos ni planos de taller o pruebas de materiales, solo cuando este especificado en la cotizaci'.dec('ó').'n.',0,'J');

$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'D'.dec('É').'CIMA TERCERA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                                  Para la interpretaci'.dec('ó').'n o cumplimiento del presente contrato ambas partes se someten a la jurisdicci'.
dec('ó').'n y competencia de las leyes y Tribunales de la Ciudad de QUER'.
dec('É').'TARO, renunciando en consecuencia, al fuero de sus domicilios presente o futuro.',0,'J');

$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$pdf->AddPage();
$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,utf8_decode("DÉCIMA CUARTA.-"));
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                                  Al presente contrato se anexa copia de identificaci'.
dec('ó').'n oficial as'.dec('í').' como comprobante de domicilio del "CLIENTE".',0,'J');

$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'D'.dec('É').'CIMA QUINTA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                              Todas las notas escritas en el anverso de este documento se consideran cl'.
dec('á').'usulas del mismo, adem'.dec('á').'s de que la firma en cualquier parte de esta cotizaci'.
dec('ó').'n/contrato se considerar'.dec('á').' como obligaci'.dec('ó').'n al cumplimiento de todas las cl'.dec('á').'usulas. ',0,'J');

$pdf->Cell(190,5,'',0,1);
$pdf->Line(5);

$varY = $pdf->GetY();
$pdf->SetFont('Arial','B','8');
$pdf->Cell(20,4,'D'.dec('É').'CIMA SEXTA.-');
$pdf->SetFont('Arial','','8');
$pdf->SetY($varY);
$pdf->MultiCell(190,4,'                          Ambas partes declaran estar conformes en suscribir el presente contrato y pasar por el en todo momento y a falta de clausula expresa en este contrato para su interpretaci'.
dec('ó').'n del mismo en cuanto a su objeto se someten a las disposiciones del c'.
dec('ó').'digo civil vigente en el estado, firman el presente contrato.',0,'J');            


//Document Sign
$pdf->Ln(30);
$pdf->Cell(25,5,'');
$pdf->Cell(60, 5, '"EL CONTRATISTA"',0,0,'C');
$pdf->Cell(30,5,'');
$pdf->Cell(60, 5, '"EL CLIENTE"',0,0,'C');
$pdf->Ln(30);
$pdf->Cell(25,5,'');
$pdf->Cell(60, 5, 'ING. RICARDO MEDINA ROJAS','T',0,'C');
$pdf->Cell(30,5,'');
$pdf->Cell(60, 5, '                         ','T',0,'C');

$pdf->AddPage();
$pdf->SetFont('Arial','B','7');
$pdf->Cell(100,2,"ANEXO 2.- NOMBRAMIENTO DE REPRESENTANTE.");
$pdf->Ln(5);
$pdf->Cell(190,2,"Fecha: ".date('d/m/Y'),0,0,"R");
$pdf->Output();
?>

    