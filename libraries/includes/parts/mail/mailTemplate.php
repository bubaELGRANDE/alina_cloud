<?php 
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require '../../../packages/php/vendor/PHPMailer/Exception.php';
    require '../../../packages/php/vendor/PHPMailer/PHPMailer.php';
    require '../../../packages/php/vendor/PHPMailer/SMTP.php';

    
    function enviarCorreo($asuntoCorreo, $textoCorreo, $direccionDestino) {
        // Cargar la plantilla HTML del correo
        $contenidoPlantilla = file_get_contents("../../parts/mail/bodyMail.php");

        // Insertar el texto del correo en la plantilla
        $contenidoPlantilla = str_replace('{{textoCorreo}}', $textoCorreo, $contenidoPlantilla);
        $contenidoPlantilla = str_replace('{{anio}}', date("Y"), $contenidoPlantilla);

    
        // Crear una nueva instancia de PHPMailer
        $mail = new PHPMailer(true);
        try {
        $mail->isSMTP();
        $mail->Host = 'alina.cloud';
        $mail->SMTPAuth = true;
        $mail->Username = 'fel@alina.cloud';  // Coloca tu nombre de usuario SMTP
        $mail->Password = 'Felsito2024$';    // Coloca tu contraseña SMTP
        $mail->SMTPSecure = 'tls';  // O 'ssl' si tu servidor SMTP lo requiere
        $mail->Port = 587;  // Puerto de tu servidor SMTP, podría ser 465 para SSL
        // $mail->addAddress('cloud@alina.jewelry');
        // Configurar el asunto y el cuerpo del correo
        $mail->Subject = $asuntoCorreo;
        $mail->Body    = $contenidoPlantilla;
        $mail->AltBody = $textoCorreo;
        $mail->AddEmbeddedImage('../../../resources/images/logos/sucursales/alina-logo-blanco-min.png', 'logoBlanco');
        $mail->AddEmbeddedImage('../../../resources/images/logos/favicon.ico', 'logo');
        // $email->IsHTML(true);
    
        // Enviar el correo
        $mail->send();
        return true;

        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
    }
}