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

    // DATOS USUARIO

    $nombre = "Alfonso";
    $id_nuevo = "1234";

    // Contenido
    $mail->isHTML(true);
    $mail->CharSet = "UTF-8";
    $mail->Subject = "¡Ya casi estás dentro! Activar cuenta";
    $mail->Body    = '<p>¡Hola <strong>'. $nombre .'</strong>!</p>

<p>Me complace informarle de que ha sido registrado correctamente como cliente en <strong>ARSTUDIO</strong> ✂️.</p>

<p>Por favor, haga clic en el siguiente
<a href="http://arbarber.online/activar_usuario.php?id=<?php echo $id_nuevo; ?>">enlace</a> para activar su cuenta y agendar sus citas en un futuro 📅.</p>

<p><i>En caso de no haber solicitado este registro, puede ignorar este mensaje sin realizar ninguna acción.</i></p>

<br><br>

<p style="font-size:12px; color:gray;">
Este correo ha sido enviado de forma automática, por lo que le rogamos no responder al mismo.<br>
Para cualquier consulta, puede ponerse en contacto a través del correo electrónico 
<a href="mailto:contacto@arstudiocutz.es">contacto@arstudiocutz.es</a>.
</p>';
    $mail->addEmbeddedImage('../../../img/arstudio_negativo.png', 'logo');
    $mail->AltBody = "No responder";

    // Enviar
    $mail->send();
    echo '✅ Correo enviado correctamente';
} catch (Exception $e) {
    echo "❌ Error al enviar: {$mail->ErrorInfo}";
}