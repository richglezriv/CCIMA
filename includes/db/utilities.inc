<?php
include_once($path_to_root."/admin/db/company_db.inc");

function notifica_autorizacion($liga, $motivoCorreo, $descripcion, $seccionMensaje){
    
    global $path_to_root;
    //obtiene el email del rol direccion
	$sql = "SELECT email FROM ".TB_PREF."users WHERE role_id = 10";
    $result = db_query($sql, "imposible obtener el email del rol gerencia");
    $row = db_fetch_row($result);
    $to = $row[0];


	require_once($path_to_root . "/reporting/includes/class.mail.inc");
	$company = get_company_prefs();
    $from = $company['coy_name'] . "<sistemas@grupoccima.com>";
    
    $msg = "<p>Buen dia.</p><p>".descripcion."</p>";
    $msg .= $seccionMensaje;
    $msg .= "<p>Acceda desde la siguiente liga para generar la transaccion correspondiente <br/><br/>";
    $msg .= "<a href='$liga'>".$liga."</a>";

    $mail = new email($company['coy_name'], "sistemas@grupoccima.com");
    $mail->to($to);
    $mail->subject($motivoCorreo);
    $mail->html($msg);

    // To send HTML mail, the Content-type header must be set
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

    
    $envio = 'no enviado';
    
    $envio = 'no enviado';
    if (mail($to, $motivoCorreo, $msg, $headers)){
        $envio = 'mensaje enviado';
    }
    return $envio;


}

?>