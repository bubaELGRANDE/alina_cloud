<?php

function agregarNotificacion(
    $categoria,
    $correoNotificacion,
    $sistemaNotificacion,
    $fechaInicioNotificacion,
    $fechaFinNotificacion,
    $cloud
): int {
    $insert = [
        'categoria' => $categoria,
        'correoNotificacion' => $correoNotificacion,
        'sistemaNotificacion' => $sistemaNotificacion,
        'fechaInicioNotificacion' => $fechaInicioNotificacion,
        'fechaFinNotificacion' => $fechaFinNotificacion,
        'estadoNotificacion' => 'ACTIVO'
    ];

    return $cloud->insert("conf_notificaciones", $insert);
}

function nuevaNotificacion(
    $data,
    $enviarCorreo,
    $cloud
): bool {
    // $plantilla = plantillas($tipo);

    $replacementData = array_filter($data, function($value) {
        return !is_array($value);
    });
    foreach ($data as $key => $texto) {
        if (!is_array($texto)) {
        $plantilla[$key] = str_replace(array_keys($replacementData), array_values($replacementData), $texto);
        } else {
            // Handle array values separately if needed
            $plantilla[$key] = $texto;
        }
    }
    
    $catLista = [];
    $notificacionId = 0;
    foreach ($plantilla["lista"] as $lista) {
        $catLista[] = $lista;
        $notificacionId = agregarNotificacion(
            $lista,
            $plantilla["correoNotificacion"],
            $plantilla["sistemaNotificacion"],
            $plantilla["fechaInicioNotificacion"],
            $plantilla["fechaFinNotificacion"],
            $cloud
        );
    }

    if ($notificacionId > 0) {
        if ($enviarCorreo) {
            // $catLista = rtrim($catLista, ',');
            $placeholders = implode(',', array_fill(0, count($plantilla["lista"]), '?'));
            $params = $catLista;
            $params[] = 0; 
            
            $correosRaw = $cloud->rows(
                "SELECT n.correo,
                CONCAT(
                    IFNULL(p.apellido1, '-'),
                    ' ',
                    IFNULL(p.apellido2, '-'),
                    ', ',
                    IFNULL(p.nombre1, '-'),
                    ' ',
                    IFNULL(p.nombre2, '-')
                ) AS nombreCompleto
                FROM conf_notificacion_persona n
                JOIN th_personas p ON p.personaId = n.personaId
                WHERE n.categoria IN ($placeholders) AND n.flgDelete = ?",
                $params
            );

            //
            $correos = array_map(function ($row) {
                return $row->correo;
            }, $correosRaw);
            // foreach a correos y sustituir nombres
            enviarCorreoNotificacion(
                $plantilla["asuntoCorreo"],
                $plantilla["correoNotificacion"],
                $correos
            );
        }
        return true;
    } else {
        return false;
    }
}
// Funciones de envio de correo notificaciones

require_once __DIR__ . '/../../../packages/php/vendor/PHPMailer/Exception.php';
require_once __DIR__ . '/../../../packages/php/vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../../packages/php/vendor/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarCorreoNotificacion(
    $asuntoCorreo,
    $textoCorreo,
    $direccionDestino,
    $CC = null,
    $pdf = null,
    $nombrePDF = null,
    $direccionEmisor = "desarrollo@indupal.cloud"
) {
    // Cargar plantilla
    $rutaPlantilla = __DIR__ . '/../../parts/mail/bodyMail.php';
    $contenidoPlantilla = file_exists($rutaPlantilla)
        ? file_get_contents($rutaPlantilla)
        : $textoCorreo; // fallback si no hay plantilla

    $contenidoPlantilla = str_replace('{{textoCorreo}}', $textoCorreo, $contenidoPlantilla);
    $contenidoPlantilla = str_replace('{{anio}}', date("Y"), $contenidoPlantilla);

    $mail = new PHPMailer(true);

    error_log("Intentando enviar correo: $asuntoCorreo");
    error_log("Destinatarios: " . (is_array($direccionDestino) ? implode(', ', $direccionDestino) : $direccionDestino));

    try {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host = 'indupal.cloud';
        $mail->SMTPAuth = true;
        $mail->Username = 'desarrollo@indupal.cloud';
        $mail->Password = 'Felsito2024$';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = "UTF-8";

        // Remitente
        $mail->setFrom($direccionEmisor, 'Indupal Cloud - Notificaciones del sistema');

        // Destinatario(s)
        if (is_array($direccionDestino)) {
            foreach ($direccionDestino as $destino) {
                $mail->addAddress(trim($destino));
            }
        } else {
            $mail->addAddress(trim($direccionDestino));
        }

        // Copias visibles (CC)
        if (!empty($CC)) {
            if (is_array($CC)) {
                foreach ($CC as $cc) {
                    $mail->addCC(trim($cc));
                }
            } else {
                $mail->addCC(trim($CC));
            }
        }

        // Adjuntar PDF si existe
        if (!empty($pdf) && !empty($nombrePDF)) {
            $mail->addStringAttachment($pdf, $nombrePDF);
        }

        // Embebidos
        $rutaLogoBlanco = __DIR__ . '/../../../resources/images/logos/sucursales/indupal-logo-blanco-min.png';
        if (file_exists($rutaLogoBlanco)) {
            $mail->AddEmbeddedImage($rutaLogoBlanco, 'logoBlanco');
        }
        $rutaIcono = __DIR__ . '/../../../resources/images/logos/icon.png';
        if (file_exists($rutaIcono)) {
            $mail->AddEmbeddedImage($rutaIcono, 'logo');
        }

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asuntoCorreo;
        $mail->Body = $contenidoPlantilla;
        $mail->AltBody = strip_tags($textoCorreo);

        // Envío
        $mail->send();
        error_log("Correo enviado exitosamente");
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        error_log("Excepción: " . $e->getMessage());
        return false;
    }
}

// Fin de de envio de correo