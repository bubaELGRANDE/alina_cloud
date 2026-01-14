<?php 
    @session_start();
    include("../mgc/datos94.php");
    $urlMail = "../../../";
    include("../../parts/mail/mailTemplate.php");
    error_reporting(0);

    $fhActual = date("Y-m-d H:i:s");
    $fhActualFormat = date("H:i:s d/m/Y");
    $_SESSION["writeBitacora"] = "no";

    $remoteIp = $_SERVER['REMOTE_ADDR'];
    $forwardIp = $_SERVER['HTTP_X_FORWARDED_FOR'];

    /*
    	POST:
    	token
    	passRestablecer
    	passRestablecerConfirm
    */

	$token = base64_decode($_POST['token']);
	$pass = $_POST['passRestablecer'];

	if($pass == $_POST['passRestablecerConfirm']) {
		$existeToken = $cloud->count("
			SELECT correo, numIdentidad, fhSolicitud, estadoRestablecer FROM bit_login_restablecer
			WHERE tokenRestablecer = ? AND flgDelete = ?
		", [$token, 0]);
		if($existeToken > 0) {
			$dataToken = $cloud->row("
				SELECT loginRestablecerId, usuarioId, correo, numIdentidad, fhSolicitud, estadoRestablecer FROM bit_login_restablecer
				WHERE tokenRestablecer = ? AND flgDelete = ?
			", [$token, 0]);
			
			if($dataToken->estadoRestablecer == "Pendiente") {
				// Estas validaciones son por si son un enlace antiguo
                $dataUsuario = $cloud->row("
                    SELECT usuarioId, personaId, estadoUsuario FROM conf_usuarios
                    WHERE usuarioId = ? AND flgDelete = ?
                ", [$dataToken->usuarioId, 0]);
                $dataNombrePersona = $cloud->row("
                	SELECT nombre1, apellido1 FROM th_personas
                	WHERE personaId = ? AND flgDelete = ?
               	", [$dataUsuario->personaId, 0]);
               	$nombrePersona = $dataNombrePersona->nombre1 . " " . $dataNombrePersona->apellido1;
                // Validar si el usuario no ha sido suspendido
                if($dataUsuario->estadoUsuario == "Suspendido") {
					$_SESSION['intentosExternos'] += 1;
			        // Correo de notificación
			        $asuntoCorreo = 'Intento de restablecer token de un usuario suspendido';
			        $textoCorreo = "
			        <p>
			            Se intentó restablecer un acceso con un token de un usuario suspendido.<br>
			            <b>Token:</b> $token <br>
			            <b>Empleado:</b> $nombrePersona <br>
			            <b>Contraseña intento: </b> $pass
			        </p>
			        <p>
			            <b>Datos adicionales:</b><br>
			            IP: $remoteIp <br>
			            Fecha y hora: $fhActualFormat
			        </p>";
			        $mailRestablecer = 'cloud@alina.jewelry';
					$output = array(
						"tituloMensaje" 			=> "Aviso:",
						"txtMensaje" 				=> "Problema al intentar restablecer su acceso, comuníquese con el Equipo de Desarrollo",
						"tipoMensaje" 				=> "warning"
					);
                } else {
					// Validar si el empleado está activo en empleados y expediente
					$existeEmpleadoActivo = $cloud->count("
						SELECT 
							per.personaId AS personaId
						FROM th_personas per
						JOIN th_expediente_personas exp ON exp.personaId = per.personaId
						WHERE per.personaId = ? AND per.estadoPersona = ? AND exp.estadoExpediente = ? AND per.flgDelete = ?
					", [$dataUsuario->personaId, 'Activo', 'Activo', 0]);
					if($existeEmpleadoActivo == 1) {
						// Restablecer acceso
						$nuevaPassword = password_hash(base64_encode(md5($pass)), PASSWORD_BCRYPT);
					    $usuarioId = $dataToken->usuarioId;

					    $update = [
					    	"estadoRestablecer" 			=> "Restablecido",
					    	"fhRestablecer" 				=> $fhActual
					    ];
					    $where = ["loginRestablecerId" => $dataToken->loginRestablecerId];
					    $cloud->update("bit_login_restablecer", $update, $where);

					    $update = [
					    	"estadoUsuario" 				=> "Activo",
					    	"intentosLogin" 				=> 0,
					    	"pass" 							=> $nuevaPassword
					    ];
					    $where = ["usuarioId" => $dataToken->usuarioId];
					    $cloud->update("conf_usuarios", $update, $where);

						$_SESSION['intentosExternos'] = 0;
				        // Correo de notificación
				        $asuntoCorreo = 'Acceso restablecido';
				        $textoCorreo = "
				        <p>
				            Estimado $nombrePersona, le informamos que su acceso en alina - Cloud ha sido <b>restablecido con éxito</b> a las $fhActualFormat. Recuerde que deberá iniciar sesión utilizando su nueva contraseña.
				        </p>
				        <p><a href='https://alina.cloud/' target='_blank'>Iniciar sesión</a></p>
                        <p>
                            Si usted no ha solicitado restablecer su acceso debe comunicarse con el Equipo de Desarrollo.
                        </p>
                        <p>
                            Por favor no responda a este correo ya que fue generado de forma automática y no recibirá respuesta. Cualquier duda o consulta puede hacerla comunicándose con el Equipo de Desarrollo.
                        </p>";
				        $mailRestablecer = $dataToken->correo;
						$output = array(
							"tituloMensaje" 			=> "Operación completada:",
							"txtMensaje" 				=> "Su acceso ha sido restablecido con éxito, por favor inicie sesión utilizando su nueva contraseña",
							"tipoMensaje" 				=> "success"
						);	
					} else {
						// Es cero o tiene expediente duplicado (que no debería pasar) pero igual se valida
						$_SESSION['intentosExternos'] += 1;
				        // Correo de notificación
				        $asuntoCorreo = 'Intento de restablecer token de un empleado inactivo';
				        $textoCorreo = "
				        <p>
				            Se intentó restablecer un acceso con un token de un empleado inactivo.<br>
				            <b>Token:</b> $token <br>
				            <b>Empleado:</b> $nombrePersona <br>
				            <b>Contraseña intento: </b> $pass
				        </p>
				        <p>
				            <b>Datos adicionales:</b><br>
				            IP: $remoteIp <br>
				            Fecha y hora: $fhActualFormat
				        </p>";
				        $mailRestablecer = 'cloud@alina.jewelry';
						$output = array(
							"tituloMensaje" 			=> "Aviso:",
							"txtMensaje" 				=> "Problema al intentar restablecer su acceso, comuníquese con el Equipo de Desarrollo",
							"tipoMensaje" 				=> "warning"
						);						
					}
                }
			} else {
				$_SESSION['intentosExternos'] += 1;
		        // Correo de notificación
		        $asuntoCorreo = 'Intento de restablecer token con uno expirado';
		        $textoCorreo = "
		        <p>
		            Se intentó restablecer un acceso con un token que ya expiró.<br>
		            <b>Token:</b> $token <br>
		            <b>Contraseña intento: </b> $pass
		        </p>
		        <p>
		            <b>Datos adicionales:</b><br>
		            IP: $remoteIp <br>
		            Fecha y hora: $fhActualFormat
		        </p>";
		        $mailRestablecer = 'cloud@alina.jewelry';
				$output = array(
					"tituloMensaje" 			=> "Aviso:",
					"txtMensaje" 				=> "Problema al intentar restablecer su acceso, comuníquese con el Equipo de Desarrollo",
					"tipoMensaje" 				=> "warning"
				);
			}
		} else {
			$_SESSION['intentosExternos'] += 1;
	        // Correo de notificación
	        $asuntoCorreo = 'Intento de restablecer token con uno erróneo';
	        $textoCorreo = "
	        <p>
	            Se intentó restablecer un acceso con un token que no existe.<br>
	            <b>Token:</b> $token <br>
	            <b>Contraseña intento: </b> $pass
	        </p>
	        <p>
	            <b>Datos adicionales:</b><br>
	            IP: $remoteIp <br>
	            Fecha y hora: $fhActualFormat
	        </p>";
	        $mailRestablecer = 'cloud@alina.jewelry';
			$output = array(
				"tituloMensaje" 			=> "Aviso:",
				"txtMensaje" 				=> "Problema al intentar restablecer su acceso, comuníquese con el Equipo de Desarrollo",
				"tipoMensaje" 				=> "warning"
			);
		} 
	} else {
		$output = array(
			"tituloMensaje" 			=> "Aviso:",
			"txtMensaje" 				=> "Las contraseñas no coinciden",
			"tipoMensaje" 				=> "warning"
		);
	}

	enviarCorreo($asuntoCorreo, $textoCorreo, $mailRestablecer);

	echo json_encode($output);
?>