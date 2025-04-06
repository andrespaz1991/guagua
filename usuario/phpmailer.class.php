<?php
class phpmailer_smtp_class{
public $smtp;
public $smtp_port;
public $sendmail_from;
public $sendmail_from_password;


function __construct($sendmail_from="",$sendmail_from_password="",$smtp="smtp.gmail.com",$smtp_port="587") {
    $this->config_correo($sendmail_from,$sendmail_from_password,$smtp,$smtp_port);
}
function config_correo($sendmail_from="",$sendmail_from_password="",$smtp="",$smtp_port="") {
$this->smtp=$smtp;
$this->smtp_port=$smtp_port;
$this->sendmail_from=$sendmail_from;
$this->sendmail_from_password=$sendmail_from_password;
}
#mail($email, $asunto, $mensaje, $cabeceras);//funcion normal de mail
function enviar_correo($de, $para, $asunto, $contenido,$adjunto=""){
/**
 * This example shows settings to use when sending via Google's Gmail servers.
 */
//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require_once('PHPMailer-master/PHPMailerAutoload.php');

//Create a new PHPMailer instance
$mail = new PHPMailer;

//Tell PHPMailer to use SMTP
$mail->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 0;

//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';

//Set the hostname of the mail server
//echo $this->smtp;
$mail->Host = $this->smtp;
// use
// $mail->Host = gethostbyname('smtp.gmail.com');
// if your network does not support SMTP over IPv6

//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$mail->Port = $this->smtp_port;
//echo $this->smtp_port;
//Set the encryption system to use - ssl (deprecated) or tls
$mail->SMTPSecure = 'tls';

//Whether to use SMTP authentication
$mail->SMTPAuth = true;

//Username to use for SMTP authentication - use full email address for gmail
$mail->Username = $this->sendmail_from;
//echo $this->sendmail_from;
//Password to use for SMTP authentication
$mail->Password = $this->sendmail_from_password;
//echo $this->sendmail_from_password;
//Set who the message is to be sent from
$mail->setFrom($de, $de);

//Set an alternative reply-to address
//$mail->addReplyTo('proinfox.ie@gmail.com', 'First Last');

//Set who the message is to be sent to
$mail->addAddress($para, $para);

//Set the subject line
$mail->Subject = $asunto;

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->msgHTML($contenido);

//Replace the plain text body with one created manually
$mail->AltBody = $contenido;

//Attach an image file
//'images/phpmailer_mini.png'
if ($adjunto!="") $mail->addAttachment($adjunto);
$mail->smtpConnect([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);/* Este método smtpConnect es útil para parametrizar el tipo de autenticación, en este caso ssl*/
//send the message, check for errors

if (!$mail->send()) {
    echo "Error al enviar: " . $mail->ErrorInfo;
    return false;
} else {
    echo "Mensage enviado!";
    return true;
}
}//fin function enviar_correo

}//fin class sesion
?>