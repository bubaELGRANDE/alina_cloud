<?php 
	@session_start();
    error_reporting(0);

	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	require '../../../../../libraries/packages/php/vendor/PHPMailer/Exception.php';
	require '../../../../../libraries/packages/php/vendor/PHPMailer/PHPMailer.php';
	require '../../../../../libraries/packages/php/vendor/PHPMailer/SMTP.php';

    function enviarCorreo($asuntoCorreo, $textoCorreo, $direccionDestino, $pdf, $nombrePDF, $json, $nombreJson, $direccionEmisor = "fel@indupal.cloud") {


        // Cargar la plantilla HTML del correo
        $rutaPlantilla = "../../../../../libraries/includes/parts/mail/bodyMail.php";
        $contenidoPlantilla = file_get_contents($rutaPlantilla);

        // Insertar el texto del correo en la plantilla
        $contenidoPlantilla = str_replace('{{textoCorreo}}', $textoCorreo, $contenidoPlantilla);
        $contenidoPlantilla = str_replace('{{anio}}', date("Y"), $contenidoPlantilla);

    
        // Crear una nueva instancia de PHPMailer
        $mail = new PHPMailer(true);
        try {
        $mail->isSMTP();
        $mail->Host = 'indupal.cloud';
        $mail->SMTPAuth = true;
        $mail->Username = 'fel@indupal.cloud';  // Coloca tu nombre de usuario SMTP
        $mail->Password = 'Felsito2024$';    // Coloca tu contraseña SMTP
        $mail->SMTPSecure = 'tls';  // O 'ssl' si tu servidor SMTP lo requiere
        $mail->Port = 587;  // Puerto de tu servidor SMTP, podría ser 465 para SSL

        $mail->CharSet = "UTF-8";
        $mail->SetFrom($direccionEmisor, 'Indupal Cloud');  
        $mail->addAddress($direccionDestino);
        $mail->addBCC($direccionEmisor);
        // $mail->addAddress('cloud@indupal.com');
        $mail->addStringAttachment($pdf, $nombrePDF);
        $mail->addStringAttachment($json, $nombreJson);
        // Configurar el asunto y el cuerpo del correo
        $mail->Subject = $asuntoCorreo;
        $mail->Body    = $contenidoPlantilla;
        $mail->AltBody = $textoCorreo;
        $rutaLogoBlanco = "../../../../../libraries/resources/images/logos/sucursales/indupal-logo-blanco-min.png";
        $mail->AddEmbeddedImage($rutaLogoBlanco, 'logoBlanco');
        $rutaIcono = "../../../../../libraries/resources/images/logos/icon.png";
        $mail->AddEmbeddedImage($rutaIcono, 'logo');
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
