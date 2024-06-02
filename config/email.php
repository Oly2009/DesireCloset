<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function enviarEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP de SendGrid
        $mail->isSMTP();
        $mail->Host = 'smtp.sendgrid.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'apikey'; // SendGrid API Key como nombre de usuario
        $mail->Password = 'your_sendgrid_api_key'; // Tu SendGrid API Key
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente y destinatarios
        $mail->setFrom('no-reply@desirecloset.com', 'DesireCloset');
        $mail->addAddress($to);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        echo "Correo enviado correctamente a $to.<br>";
    } catch (Exception $e) {
        // Manejo de errores
        echo "Error al enviar el correo a $to: {$mail->ErrorInfo}<br>";
    }
}
?>
