<?php
require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // SMTP -- CHAPA Y PINTURA 146, 147
    $mail->isSMTP();
    $mail->Host       = 'free.mboxhosting.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply@arstudiocutz.es';
    $mail->Password   = 'Agmround1!';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Remitente
    $mail->setFrom('noreply@arstudiocutz.es', 'ARSTUDIO');

    // Destinatario
    $mail->addAddress('alfonsogm99@gmail.com');

    // Contenido
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Confirmación de cita';
    $mail->Body    = '<h1>Hola 👋</h1><p>Este es un email de prueba.</p>';
    $mail->AltBody = 'Hola, este es un email de prueba.';

    // Enviar
    $mail->send();
    echo '✅ Correo enviado correctamente';
} catch (Exception $e) {
    echo "❌ Error al enviar: {$mail->ErrorInfo}";
}