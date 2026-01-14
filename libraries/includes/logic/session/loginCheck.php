<?php 
    @session_start();
    include("../mgc/datos94.php");

	include("../../parts/mail/mailTemplate.php");

    $post_correo = $_POST["mailLogin"];
    $post_password = base64_encode(md5($_POST["passLogin"]));
    //$post_rememberData = (isset($_POST["rememberData"]) ? 1 : 0);

    $fhActual = date("Y-m-d H:i:s");

    if(isset($_SESSION["intentosExternos"])) {
    } else {
        $_SESSION["intentosExternos"] = 0;
    }
    
    // quitar comentarios al subirlo al server
    //$remoteIp = $_SERVER['REMOTE_ADDR'];
    //$forwardIp = $_SERVER['HTTP_X_FORWARDED_FOR'];

    $remoteIp = "localhost";
    $forwardIp = "localhost";

    // Para utilizar en clase-db
    $_SESSION["usuario"] = "N/A";
    $_SESSION["writeBitacora"] = "yes";

    try {
    	$queryUsuario = "
	    	SELECT 
	    		usuarioId, usuario, personaId, correo, pass, estadoUsuario, numLogin, intentosLogin
	    	FROM conf_usuarios 
	    	WHERE correo = ? AND flgDelete='0'
    	";
	    $existeUsuario = $cloud->count($queryUsuario, [$post_correo]);

	    if($existeUsuario > 0 && $_SESSION["intentosExternos"] <= 5) {
	    	$dataUsuario = $cloud->row($queryUsuario,[$post_correo]);

	    	if (password_verify($post_password, $dataUsuario->pass) && $dataUsuario->estadoUsuario == "Activo") {
	    		$dataPersona = $cloud->row("
	    			SELECT
	    				prsTipoId, nombre1, apellido1, fechaNacimiento, estadoPersona
	    			FROM th_personas
	    			WHERE personaId = ? AND flgDelete='0'
	    		",[$dataUsuario->personaId]);
	    		// Considerar validar también el estado del expediente
	    		if($dataPersona->estadoPersona == "Activo") {
	    			$dataExpediente = $cloud->row("
	    				SELECT 
	    					prsExpedienteId,
	    					sucursalId,
	    					nombreCompleto,
	    					nombreCompletoNA,
	    					cargoPersona
	    				FROM view_expedientes
	    				WHERE personaId = ? AND estadoPersona = ? AND estadoExpediente = ?
	    			", [$dataUsuario->personaId, 'Activo', 'Activo']);

	    			// Sesiones Usuario
	    			$_SESSION["usuarioId"] = $dataUsuario->usuarioId;
	    			$_SESSION["usuario"] = $dataUsuario->usuario;
	    			$_SESSION["correo"] = $dataUsuario->correo;

	    			// Sesiones Persona
	    			$_SESSION["personaId"] = $dataUsuario->personaId;
	    			$_SESSION["cargoPersona"] = $dataExpediente->cargoPersona;
	    			$_SESSION["prsTipoId"] = $dataPersona->prsTipoId;
	    			$_SESSION["nombrePersona"] = $dataPersona->nombre1 . " " . $dataPersona->apellido1;
	    			$_SESSION["nombreCompleto"] = $dataExpediente->nombreCompleto;
	    			$_SESSION["nombreCompletoNA"] = $dataExpediente->nombreCompletoNA;
	    			$_SESSION["sucursalId"] = $dataExpediente->sucursalId;

	    			// Otras sesiones
	    			$_SESSION["inactividad"] = 0;
	    			$_SESSION["birthday"] = 0;
	    			$_SESSION["loginStart"] = 0;
	    			$_SESSION["monedaId"] = 1;
	    			$_SESSION["moneda"] = "USD";
	    			$_SESSION["monedaSimbolo"] = "$";

				    $dataUserNameCustom = $cloud->row("
				        SELECT 
				            custom
				        FROM mip_perfil_custom 
				        WHERE usuarioId = ? AND tipoCustom = 'Nombre' AND flgDelete = '0'
				    ", [$_SESSION["usuarioId"]]);
				    // nombre que se mostrará únicamente en la sidebar, para el resto de mensaje se usará nombrePersona
	    			$_SESSION["nombrePersonaSide"] = $dataUserNameCustom->custom;

	                $fechaActual = date("Y-m-d");
	                $arrayFecha = explode("-",$fechaActual);

	                if(strpos($dataPersona->fechaNacimiento, $arrayFecha[1] . "-" . $arrayFecha[2])) {
	                	$_SESSION["birthday"] = 1;
	                } else {
	                	// No es fecha de cumpleaños
	                }	    			

	                // Reiniciar intentosLogin
	                $numLogin = $dataUsuario->numLogin + 1;
			        $updateResetLogin = [
			            'intentosLogin'		=> 0,
			            'numLogin'     		=> $numLogin,
			            'fhUltimoLogin'		=> $fhActual,
			            'enLinea'			=> 1,
			        ];
			        $where = ['usuarioId' => $_SESSION["usuarioId"]];
			        
			        $_SESSION["writeBitacora"] = "no";
			        $cloud->update('conf_usuarios', $updateResetLogin, $where);

			        $insertBitVariables = [
			            'usuarioId'			=> $_SESSION["usuarioId"],
			            'fhLogin'			=> $fhActual,
			            'movInterfaces'		=> "(" . $fhActual . ") Inició sesión, ",
			            'movInsert'			=> "(" . $fhActual . ") Inició sesión, ",
			            'movUpdate'			=> "(" . $fhActual . ") Inició sesión, ",
			            'movDelete'			=> "(" . $fhActual . ") Inició sesión, ",
			            'remoteIp'			=> $remoteIp,
			            'forwardIp' 		=> $forwardIp,
			            'navegador'	=> $_SERVER['HTTP_USER_AGENT']
			        ];
			        $insertBitacoraInicial = $cloud->insert('bit_login_usuarios', $insertBitVariables);

			        $_SESSION["loginUsuarioId"] = $insertBitacoraInicial; // insert() devuelve por defecto el último id

	                $defaultPassword = "alina" . date("Y") . "$";
	                if($_POST["passLogin"] == $defaultPassword) {
	                	$_SESSION["flgPassword"] = 1; // Obligar a cambiar contraseña por defecto en modulos.php
	                } else {
	                	$_SESSION["flgPassword"] = 0;
	                }

					
					// Redactar correo de notificación: usuario que ingresó
					$asuntoCorreo = 'Notificación de inicio de sesión.';
					$fechaHoraInicioSesion = date("H:i:s d-m-Y");
					$textoCorreo = "<p>
					Estimado $_SESSION[nombrePersona], el motivo de este correo es para informarle que se inició sesión en la plataforma Alina Jewerly con sus credenciales a las $fechaHoraInicioSesion. 
					</p>
					<p>
					Por favor no responda a este correo ya que fue generado de forma automática y no recibirá respuesta. Cualquier duda o consulta puede hacerla comunicándose con el equipo de desarrollo.
					</p>";
					/*
					if (enviarCorreo($asuntoCorreo, $textoCorreo, $post_correo)){
						// Redactar correo de notificación: cloud
	
						$textoCorreoSistema = "<p>
						Notificación de nuevo inicio de sesión detectado, con las credenciales de la persona: $_SESSION[nombrePersona]<br>
						$post_correo<br>
						$fechaHoraInicioSesion
						</p>
	
						";
						
						enviarCorreo($asuntoCorreo, $textoCorreoSistema, 'cloud@alina.jewelry');

						echo 'success';
					}
					*/
					echo 'success';

	    		} else {
	    			// Persona / Expediente inactivo
	    			// Redactar correo de notificación
					$fechaHoraInicioSesion = date("H:i:s d-m-Y");
					$asuntoCorreo = 'Notificación de inicio de sesión - Inactivo.';
					$textoCorreoSistema = "<p>
					Notificación de nuevo intento de sesión detectado de un empleado inactivo, con las credenciales:<br>
					$post_correo<br>
					$remoteIp<br>
					$forwardIp<br>
					$fechaHoraInicioSesion
					</p>
					";
					
					enviarCorreo($asuntoCorreo, $textoCorreoSistema, 'cloud@alina.jewelry');


			        $insertBitacora = [
			            'remoteIp'	=> $remoteIp,
			            'forwardIp'	=> $forwardIp,
			            'correo'	=> $post_correo,
			            'fhIntento' => $fhActual,
			            'navegador'	=> $_SERVER['HTTP_USER_AGENT']
			        ];
			        $_SESSION["writeBitacora"] = "no";
			        $cloud->insert('bit_login_inactivos', $insertBitacora);
	                // No mostrar mensaje de credenciales/empleado inactivo, simplemente mostrarlo como incorrectas
	                echo "Las credenciales ingresadas no son válidas, por favor verifique la información e intente nuevamente";	    		
	    		}
	    	} else { // Contraseña incorrecta o estado cambiado
	            if($dataUsuario->estadoUsuario == "Inhabilitado" || $dataUsuario->estadoUsuario == "Suspendido") { // Usuario Inhabilitado = RRHH, Suspendido = Solicitud a Informática
	                // Redactar correo de notificación
					$fechaHoraInicioSesion = date("H:i:s d-m-Y");
					$asuntoCorreo = 'Notificación de inicio de sesión - Suspendido.';
					$textoCorreoSistema = "<p>
					Notificación de nuevo intento de sesión detectado de un empleado suspendido, con las credenciales:<br>
					$post_correo<br>
					$remoteIp<br>
					$forwardIp<br>
					$fechaHoraInicioSesion
					Estado usuario: $dataUsuario->estadoUsuario
					</p>
					";
					
					enviarCorreo($asuntoCorreo, $textoCorreoSistema, 'cloud@alina.jewelry');

			        $insertBitacora = [
			            'remoteIp'	=> $remoteIp,
			            'forwardIp'	=> $forwardIp,
			            'correo'	=> $post_correo,
			            'fhIntento' => $fhActual,
			            'navegador'	=> $_SERVER['HTTP_USER_AGENT']
			        ];
			        $_SESSION["writeBitacora"] = "no";
			        $cloud->insert('bit_login_inactivos', $insertBitacora);

	                // No mostrar mensaje de credenciales desactivadas, simplemente mostrarlo como incorrectas
	                echo "Las credenciales ingresadas no son válidas, por favor verifique la información e intente nuevamente";
	            } else if($dataUsuario->estadoUsuario == "Bloqueado") { // Automático por sistema, alcanzó el limite de 5 intentos
	                echo "Sus credenciales han sido bloqueadas por alcanzar el límite de intentos fallidos. Por favor, comuníquese con el Equipo de Desarrollo";
	            } else if($dataUsuario->estadoUsuario == "Pendiente") { // Solicitud todavia no aprobada
	            	echo "Sus credenciales se encuentran en proceso de autorización.";
	            } else {
	                $intentosLogin = $dataUsuario->intentosLogin + 1;
			        $updateSumIntentos = [
			            'intentosLogin'	=> $intentosLogin
			        ];
			        $where = ['correo' => $post_correo];

			        $_SESSION["writeBitacora"] = "no";
			        $cloud->update('conf_usuarios', $updateSumIntentos, $where);

	                if($intentosLogin == 4) {
	                    // AL 2DO INTENTO, MOSTRAR MENSAJE QUE EL SIGUIENTE INTENTO FALLIDO BLOQUEARÁ SUS CREDENCIALES.
	                    echo "El próximo intento fallido bloqueará sus credenciales, por favor verifique la información e intente nuevamente";
	                } else if($intentosLogin == 5) {
				        $updateEstadoUsuario = [
				            'estadoUsuario'	=> "Bloqueado"
				        ];
				        $where = ['correo' => $post_correo];

				        $_SESSION["writeBitacora"] = "no";
				        $cloud->update('conf_usuarios', $updateEstadoUsuario, $where);
	                    // AL 5TO INTENTO MOSTRAR QUE SE DESACTIVARON SUS CREDENCIALES POR INTENTOS FALLIDOS.
						$fechaHoraInicioSesion = date("H:i:s d-m-Y");
						$asuntoCorreo = 'Notificación de inicio de sesión.';
						$urlVariables = base64_encode("forgot-password");
						$textoCorreoSistema = '
						<p>
							El motivo de este correo es para informarle que su acceso a Alina Jewerly, ha sido bloqueado por alcanzar el límite de intentos fallidos.
						</p>
						<p>
							Para restablecer su acceso utilice el siguiente enlace: <a href="https://alina.jewelry/cloud/?url='.$urlVariables.'" target="_blank">Olvidé mi contraseña</a> o dé clic en la opción "¿Olvidó su contraseña?" en la pantalla de inicio de sesión.
						</p>
						';
						
						enviarCorreo($asuntoCorreo, $textoCorreoSistema, $post_correo);
	                    
						echo "Sus credenciales han sido bloqueadas por alcanzar el límite de intentos fallidos. Por favor, comuníquese con el Equipo de Desarrollo.";
	                } else {
	                    // LA CONTRASEÑA NO COINCIDE CON EL CORREO, PERO SE MOSTRARÁ COMO CREDENCIALES NO VÁLIDAS POR MOTIVOS DE SEGURIDAD
	                    echo "Las credenciales ingresadas no son válidas, por favor verifique la información e intente nuevamente";
	                }
	            }
	    	}
	    } else { // intento externo / correo no registrado
	        $_SESSION["intentosExternos"] += 1;

	        // Redactar correo de notificación
			$fechaHoraInicioSesion = date("H:i:s d-m-Y");
			$asuntoCorreo = 'Notificación de inicio de sesión - Externo.';
			$textoCorreoSistema = "<p>
			Notificación de nuevo intento de sesión externo detectado, con las credenciales:<br>
			$post_correo<br>
			$remoteIp<br>
			$forwardIp<br>
			$fechaHoraInicioSesion
			</p>
			";
			
			enviarCorreo($asuntoCorreo, $textoCorreoSistema, 'cloud@alina.jewelry');

	        $insertBitacora = [
	            'remoteIp'	=> $remoteIp,
	            'forwardIp'	=> $forwardIp,
	            'correo'	=> $post_correo,
	            'fhIntento' => $fhActual,
	            'navegador'	=> $_SERVER['HTTP_USER_AGENT']
	        ];
	        $_SESSION["writeBitacora"] = "no";
	        $cloud->insert('bit_login_externos', $insertBitacora);

	        // NO SE ENCONTRÓ UN USUARIO CON ESE CORREO, PERO EL MENSAJE SE MOSTRARÁ COMO CREDENCIALES NO VÁLIDAS POR MOTIVOS DE SEGURIDAD
	        // Aviso "lim" a la function validarLogin para bloquear inputs
	        if($_SESSION["intentosExternos"] > 5) {
	        	echo "lim^Las credenciales ingresadas no son válidas, por favor verifique la información e intente nuevamente";
	        } else {
	        	echo "Las credenciales ingresadas no son válidas, por favor verifique la información e intente nuevamente";
	        }
	    }
    } catch(Exception $e) { // code/sql error
    	echo 'error^Parece que algo salió mal, por favor intente nuevamente.^¿El problema persiste?&nbsp;<a href="soporte-cloud" target="_blank">Solicitar ayuda</a>&nbsp;';
    }
?>