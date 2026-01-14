<?php
    @session_start();
    include("../mgc/datos94.php");
    $urlMail = "../../../";
    include("../../parts/mail/mailTemplate.php");
    error_reporting(0);

    $fhActual = date("Y-m-d H:i:s");
    $fhActualFormat = date("H:i:s d/m/Y");
    $_SESSION["writeBitacora"] = "no";
    /*
        POST:
        duiRestablecer
        fechaNacimientoRestablecer
        mailRestablecer
    */
    $correo = $_POST['mailRestablecer'];
    $dui = $_POST['duiRestablecer'];
    $fechaNacimiento = $_POST['fechaNacimientoRestablecer'];
    $fechaNacimientoSQL = date("Y-m-d", strtotime($fechaNacimiento));

    $remoteIp = $_SERVER['REMOTE_ADDR'];
    $forwardIp = $_SERVER['HTTP_X_FORWARDED_FOR'];

    $token = "N/A";
    $usuarioId = 0;
    // Validar si posee usuario
    $existeUsuario = $cloud->count("
        SELECT usuarioId FROM conf_usuarios
        WHERE correo = ? AND flgDelete = ?
    ", [$correo, 0]);
    if($existeUsuario > 0) {
        // Validar si la información proporcionada coincide
        $existePersona = $cloud->count("
            SELECT personaId FROM th_personas
            WHERE numIdentidad = ? AND fechaNacimiento = ? AND flgDelete = ?
        ", [$dui, $fechaNacimientoSQL, 0]);
        if($existePersona > 0) {
            $dataPersona = $cloud->row("
                SELECT personaId, estadoPersona, nombre1, apellido1 FROM th_personas
                WHERE numIdentidad = ? AND fechaNacimiento = ? AND flgDelete = ?
                LIMIT 1
            ", [$dui, $fechaNacimientoSQL, 0]);
            if($dataPersona->estadoPersona == "Activo") {
                $existeExpediente = $cloud->count("
                    SELECT prsExpedienteId FROM th_expediente_personas
                    WHERE personaId = ? AND flgDelete = ?
                ", [$dataPersona->personaId, 0]);
                if($existeExpediente > 0) {
                    $dataExpediente = $cloud->row("
                        SELECT prsExpedienteId, estadoExpediente FROM th_expediente_personas
                        WHERE personaId = ? AND flgDelete = ?
                        LIMIT 1
                    ", [$dataPersona->personaId, 0]);
                    if($dataExpediente->estadoExpediente == "Activo") {
                        $dataUsuario = $cloud->row("
                            SELECT usuarioId, correo, estadoUsuario FROM conf_usuarios
                            WHERE personaId = ? AND flgDelete = ?
                            LIMIT 1
                        ", [$dataPersona->personaId, 0]);
                        if($dataUsuario->correo == $correo) {
                            if($dataUsuario->estadoUsuario == "Suspendido") {
                                // No proceder con las validaciones, el usuario fue suspendido
                                $_SESSION["intentosExternos"] += 1;
                                // Correo de notificación
                                $asuntoCorreo = 'Intento de restablecer acceso con usuario suspendido';
                                $textoCorreo = "
                                <p>
                                    Se intentó restablecer un acceso con un usuario que fue suspendido.<br>
                                    <b>Correo:</b> $correo <br>
                                    <b>DUI:</b> $dui <br>
                                    <b>Fecha de nacimiento:</b> $fechaNacimiento
                                </p>
                                <p>
                                    <b>Datos adicionales:</b><br>
                                    IP: $remoteIp <br>
                                    Fecha y hora: $fhActualFormat
                                </p>";
                                
                                $mailRestablecer = 'cloud@alina.jewelry';
                                $estadoRestablecer = "No aplica, usuario suspendido";
                            } else {
                                // Todo ok, pasó todos los filtros
                                $_SESSION["intentosExternos"] = 0;
                                // Correo de notificación
                                $token = generarToken(8);
                                $urlVariables = base64_encode($token);

                                $nombrePersona = $dataPersona->nombre1 . " " . $dataPersona->apellido1;
                                $asuntoCorreo = 'Restablecer acceso';
                                $textoCorreo = "
                                <p>
                                    Estimado $nombrePersona, este correo es para informarle que se solicitó restablecer su acceso en alina - Cloud a las $fhActualFormat.
                                </p>
                                <p>
                                    Para restablecer el acceso a su cuenta, siga las indicaciones del siguiente enlace: <a href='https://alina.cloud/?url=$urlVariables' target='_blank'>Restablecer acceso</a>.
                                </p>
                                <p>
                                    Si usted no ha solicitado restablecer su acceso debe comunicarse con el Equipo de Desarrollo.
                                </p>
                                <p>
                                    Por favor no responda a este correo ya que fue generado de forma automática y no recibirá respuesta. Cualquier duda o consulta puede hacerla comunicándose con el Equipo de Desarrollo.
                                </p>
                                ";
                                $mailRestablecer = $correo;
                                $estadoRestablecer = "Pendiente";
                                $usuarioId = $dataUsuario->usuarioId;
                            }
                        } else {
                            // No proceder con las validaciones, correo no coincide
                            $_SESSION["intentosExternos"] += 1;
                            // Correo de notificación
                            $asuntoCorreo = 'Intento de restablecer acceso con correo que no coincide';
                            $textoCorreo = "
                            <p>
                                Se intentó restablecer un acceso con un correo que no coincide con la información del empleado.<br>
                                <b>Correo:</b> $correo <br>
                                <b>DUI:</b> $dui <br>
                                <b>Fecha de nacimiento:</b> $fechaNacimiento
                            </p>
                            <p>
                                <b>Datos adicionales:</b><br>
                                IP: $remoteIp <br>
                                Fecha y hora: $fhActualFormat
                            </p>";
                            
                            $mailRestablecer = 'cloud@alina.jewelry';
                            $estadoRestablecer = "No aplica, correo no coincide"; 
                        }
                    } else {
                        // No proceder con las validaciones, expediente inactivo
                        $_SESSION["intentosExternos"] += 1;
                        // Correo de notificación
                        $asuntoCorreo = 'Intento de restablecer acceso de un expediente inactivo';
                        $textoCorreo = "
                        <p>
                            Se intentó restablecer un acceso de un expediente inactivo.<br>
                            <b>Correo:</b> $correo <br>
                            <b>DUI:</b> $dui <br>
                            <b>Fecha de nacimiento:</b> $fechaNacimiento
                        </p>
                        <p>
                            <b>Datos adicionales:</b><br>
                            IP: $remoteIp <br>
                            Fecha y hora: $fhActualFormat
                        </p>";
                        
                        $mailRestablecer = 'cloud@alina.jewelry';
                        $estadoRestablecer = "No aplica, expediente inactivo";                
                    }
                } else {
                    // No proceder con las validaciones, no posee expediente
                    $_SESSION["intentosExternos"] += 1;
                    // Correo de notificación
                    $asuntoCorreo = 'Intento de restablecer acceso de un empleado sin expediente';
                    $textoCorreo = "
                    <p>
                        Se intentó restablecer un acceso de un empleado que no tiene creado un expediente.<br>
                        <b>Correo:</b> $correo <br>
                        <b>DUI:</b> $dui <br>
                        <b>Fecha de nacimiento:</b> $fechaNacimiento
                    </p>
                    <p>
                        <b>Datos adicionales:</b><br>
                        IP: $remoteIp <br>
                        Fecha y hora: $fhActualFormat
                    </p>";
                    
                    $mailRestablecer = 'cloud@alina.jewelry';
                    $estadoRestablecer = "No aplica, empleado sin expediente";  
                }
            } else {
                // No proceder con las validaciones, es una persona inactiva
                $_SESSION["intentosExternos"] += 1;
                // Correo de notificación
                $asuntoCorreo = 'Intento de restablecer acceso de un empleado inactivo';
                $textoCorreo = "
                <p>
                    Se intentó restablecer un acceso de un empleado inactivo.<br>
                    <b>Correo:</b> $correo <br>
                    <b>DUI:</b> $dui <br>
                    <b>Fecha de nacimiento:</b> $fechaNacimiento
                </p>
                <p>
                    <b>Datos adicionales:</b><br>
                    IP: $remoteIp <br>
                    Fecha y hora: $fhActualFormat
                </p>";
                
                $mailRestablecer = 'cloud@alina.jewelry';
                $estadoRestablecer = "No aplica, empleado inactivo";               
            }
        } else {
            // No proceder con las validaciones, los datos de la persona son erroneos
            $_SESSION["intentosExternos"] += 1;
            // Correo de notificación
            $asuntoCorreo = 'Intento de restablecer acceso con información de persona errónea';
            $textoCorreo = "
            <p>
                Se intentó restablecer un acceso con información de persona errónea.<br>
                <b>Correo:</b> $correo <br>
                <b>DUI:</b> $dui <br>
                <b>Fecha de nacimiento:</b> $fechaNacimiento
            </p>
            <p>
                <b>Datos adicionales:</b><br>
                IP: $remoteIp <br>
                Fecha y hora: $fhActualFormat
            </p>";

            $mailRestablecer = 'cloud@alina.jewelry';
            $estadoRestablecer = "No aplica, persona información errónea";
        }
    } else {
        // No proceder con las validaciones, no existe ese correo
        $_SESSION["intentosExternos"] += 1;
        // Correo de notificación
        $asuntoCorreo = 'Intento de restablecer acceso con correo externo';
        $textoCorreo = "
        <p>
            Se intentó restablecer un acceso con un correo externo.<br>
            <b>Correo:</b> $correo <br>
            <b>DUI:</b> $dui <br>
            <b>Fecha de nacimiento:</b> $fechaNacimiento
        </p>
        <p>
            <b>Datos adicionales:</b><br>
            IP: $remoteIp <br>
            Fecha y hora: $fhActualFormat
        </p>";
        $mailRestablecer = 'cloud@alina.jewelry';
        $estadoRestablecer = "No aplica, correo externo";
    }

    enviarCorreo($asuntoCorreo, $textoCorreo, $mailRestablecer);
    $insert = [
        "remoteIp"                  => $remoteIp,
        "forwardIp"                 => $forwardIp,
        "usuarioId"                 => $usuarioId,
        "correo"                    => $correo,
        "numIdentidad"              => $dui,
        "tokenRestablecer"          => $token,
        "fhSolicitud"               => $fhActual,
        "estadoRestablecer"         => $estadoRestablecer
    ];
    $loginRestablecerId = $cloud->insert("bit_login_restablecer", $insert);

    echo "success";

    function generarToken($longitud) {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
        $token = '';
        $max = strlen($caracteres) - 1;

        for ($i = 0; $i < $longitud; $i++) {
            $token .= $caracteres[random_int(0, $max)];
        }

        return $token;
    }
?>