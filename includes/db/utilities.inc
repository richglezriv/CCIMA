<?php
include_once($path_to_root."/admin/db/company_db.inc");

function notifica_autorizacion($liga, $motivoCorreo, $descripcion, $seccionMensaje, $correo=''){

    global $path_to_root;
    $to = "";
    //obtiene el email del rol direccion
    if ($correo == ''){
	    $sql = "SELECT email FROM ".TB_PREF."users WHERE role_id = 10";
        $result = db_query($sql, "imposible obtener el email de los roles");
        $row = db_fetch_row($result);
        $to = $row[0];
        error_log($to);
    }
    else {
        $to = $correo;
        $to = substr($to, 0, strlen($to) - 1);
        
    }

	require_once($path_to_root . "/reporting/includes/class.mail.inc");
	$company = get_company_prefs();
    $from = $company['coy_name'] . "<sistemas@grupoccima.com>";

    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $envio = 'no enviado';
    $ligaValida = '';

    ini_set('SMTP','mail.gcapconsulting.com.mx');
    ini_set('smtp_port', 587);
    ini_set('sendmail_from', 'sistemas@grupoccima.com');
    
    
    $msg = "<p>Buen dia.</p><p>".$descripcion."</p>";
    $msg .= $seccionMensaje;
    $msg .= "<p>Acceda desde la siguiente liga para generar la transaccion correspondiente <br/><br/>";
    $msg .= "<a href='$liga'>".$liga."</a>";

    EnviaCorreo($to, $motivoCorreo, $msg);

    return "fin";


}

function EnviaCorreo($to, $asunto, $msg){
    global $path_to_root;
    require($path_to_root."/includes/db/PHPMailer/class.phpmailer.php");
    require($path_to_root."/includes/db/PHPMailer/class.smtp.php");

    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Host = "mail.gcapconsulting.com.mx";
    $mail->Port = 587;
    $mail->Username = "ricardo.gonzalez@gcapconsulting.com.mx";
    $mail->Password = "Rabraco10";
    $mail->SMTPAuth = TRUE;
    $mail->From     = "ricardo.gonzalez@gcapconsulting.com.mx";
    $mail->AddAddress($to);
    $mail->Subject  = $asunto;
    $mail->MsgHTML($msg);
    $mail->WordWrap = 50;

    if(!$mail->Send()){
        error_log('Error: '.$mail->ErrorInfo);
    }
}

function notifica_autorizacion_masiva($liga, $motivoCorreo, $descripcion="", $seccionMensaje=""){
    $envio = "fin";
    global $path_to_root;
    $sql = "SELECT email FROM ".TB_PREF."users WHERE role_id IN (3,4,5,6,9,10)";
    //$sql = "SELECT email FROM ".TB_PREF."users WHERE role_id IN (9,10)";
    $result = db_query($sql, "imposible obtener el email de los roles");
    

	require_once($path_to_root . "/reporting/includes/class.mail.inc");
	$company = get_company_prefs();
    $from = $company['coy_name'] . "<sistemas@grupoccima.com>";
    
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $envio = 'no enviado';
    $ligaValida = '';

    ini_set('SMTP','gcapconsulting.com.mx');
    ini_set('smtp_port', 587);
    $to = "";

    while($row = db_fetch_row($result)){
        $to .= $row[0] . ",";
        $ligaValida = $liga . md5($row[1]) ."&mega=".$descripcion;
    
        
    }

    $to = substr($to, 0, strlen($to) - 1);

    $msg = "<p>Buen dia.</p>";
    $msg .= $seccionMensaje;
    $msg .= "<p>Se le notifica que se ha registrado una orden de MEGA con folio [".$descripcion."] para su revisi&oacute;n.</p>";
    $msg .= "<p>Haga clic en la siguiente liga para notificar de <a href='".$ligaValida."'>enterado</a></p>";
    
    /**
    if (mail($to, $motivoCorreo, $msg, $headers)){
        $envio = 'mensaje enviado';
    }
    **/
    EnviaCorreo($to, $motivoCorreo, $msg);
    
    return $envio;

}

function notifica_nueva_requisicion($requerido){
    $envio = "fin";
    global $path_to_root;
    $sql = "SELECT email FROM ".TB_PREF."users WHERE role_id IN (6)";
    //$sql = "SELECT email FROM ".TB_PREF."users WHERE role_id IN (9,10)";
    $result = db_query($sql, "imposible obtener el email de los roles");
    

	require_once($path_to_root . "/reporting/includes/class.mail.inc");
	$company = get_company_prefs();
    $from = $company['coy_name'] . "<sistemas@grupoccima.com>";
    
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $envio = 'no enviado';
    $ligaValida = '';

    ini_set('SMTP','gcapconsulting.com.mx');
    ini_set('smtp_port', 587);
    $to = "";

    while($row = db_fetch_row($result)){
        $to .= $row[0] . ",";
    }

    $to = substr($to, 0, strlen($to) - 1);

    $msg = "<p>Buen dia.</p>";
    $msg .= "<p>Se le notifica que se ha registrado una nueva requisici&oacute;n</p>";
    $msg .= "<p>".$requerido."</p>";
    

    EnviaCorreo($to, $motivoCorreo, $msg);
    /**
    if (mail($to, "Nueva requisicion", $msg, $headers)){
        $envio = 'mensaje enviado';
    }
    **/
    
    return $envio;

}

function notifica_requisicion_completada($items, $usuario){
    $envio = "fin";
    global $path_to_root;
    $sql = "SELECT correo_jefe FROM ".TB_PREF."users WHERE user_id = ".db_escape($usuario);
    //$sql = "SELECT email FROM ".TB_PREF."users WHERE role_id IN (9,10)";
    $result = db_query($sql, "imposible obtener el email de los roles");
    

	require_once($path_to_root . "/reporting/includes/class.mail.inc");
	$company = get_company_prefs();
    $from = $company['coy_name'] . "<sistemas@grupoccima.com>";
    
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $envio = 'no enviado';
    $ligaValida = '';

    ini_set('SMTP','gcapconsulting.com.mx');
    ini_set('smtp_port', 587);
    ini_set('FROM', 'sistemas@grupoccima.com');
    $to = "";

    while($row = db_fetch_row($result)){
        $to .= $row[0] . ",";
    }

    $to = substr($to, 0, strlen($to) - 1);

    $msg = "<p>Buen dia.</p>";
    $msg .= "<p>Se le notifica que ha sido enviada una requisicion para el inventario que a continuacion se describe:</p>";
    $msg .= $items;
    
    
    $mail = new email($company['coy_name'], "sistemas@grupoccima.com");
    $mail->to($to);
    $mail->subject($motivoCorreo);
    $mail->html($msg);

    /**
    if (mail($to, "Notificacion de Requisicion", $msg, $headers)){
        $envio = 'mensaje enviado';
    }
    **/
    EnviaCorreo($to, $motivoCorreo, $msg);

    return $envio;

}

?>