<?php
/*
        $update = [
            'campo1'		=> "hola :o",
            'campo2'     => "hola",
        ];
        $where = ['testId' => id]; // ids, soporta múltiple where
        
        $cloud->update('test', $update, $where);
	*/
if (isset($_SESSION["usuarioId"]) && isset($operation)) {
	switch ($operation) {
		case "solicitud-acceso-empleado":
			/*
					POST:
					typeOperation
					operation
					solicitud - tipo de solicitud = Autorizar / Rechazar
					id - usuarioId
					pId - personaId
					nombrePersona
					sideName
					correoPersona
					justificacionEstado - Si es autorizar, viene vacio
					
				*/
			if ($_POST["solicitud"] == "autorizar") {
				if (in_array(14, $_SESSION["arrayPermisos"]) || in_array(46, $_SESSION["arrayPermisos"])) {
					$defaultPass = "alina" . date("Y") . "$";
					$stringBitacora = "(" . $fhActual . ") Autorizó la solicitud de acceso de " . $_POST["nombrePersona"] . ", ";
					$update = [
						'estadoUsuario'	=> 'Activo',
						'pass'			=> password_hash(base64_encode(md5($defaultPass)), PASSWORD_BCRYPT),
					];
					$where = ['usuarioId' => $_POST["id"]];
					$cloud->update('conf_usuarios', $update, $where);

					// Validar si ya se le cargó foto de perfil en RRHH
					$poseeFotoPerfil = $cloud->count("
		        			SELECT
		        				urlPrsAdjunto
		        			FROM th_personas_adjuntos
		        			WHERE personaId = ? AND tipoPrsAdjunto = 'Foto de empleado' AND descripcionPrsAdjunto = 'Actual' AND flgDelete = '0'
		        		", [$_POST["pId"]]);

					if ($poseeFotoPerfil > 0) {
						$dataFotoPerfil = $cloud->row("
			        			SELECT
			        				urlPrsAdjunto
			        			FROM th_personas_adjuntos
			        			WHERE personaId = ? AND tipoPrsAdjunto = 'Foto de empleado' AND descripcionPrsAdjunto = 'Actual' AND flgDelete = '0'
			        			LIMIT 1
			        		", [$_POST["pId"]]);
						$fotoPerfil = $dataFotoPerfil->urlPrsAdjunto;
					} else { // No posee, colocar la default
						$fotoPerfil = "mi-perfil/user-default.jpg";
					}

					// Insert default customs para que cuando ingrese cargue todo en la sidebar mip_perfil_custom
					for ($i = 0; $i < 4; $i++) {
						if ($i == 0) {
							$tipoCustom = "Avatar";
							$custom = $fotoPerfil;
						} else if ($i == 1) {
							$tipoCustom = "Estado";
							$custom = "En línea";
						} else if ($i == 2) {
							$tipoCustom = "Nombre";
							$custom = $_POST["sideName"];
						} else {
							$tipoCustom = "Tema";
							$custom = "default";
						}
						$insert = [
							'usuarioId'		=> $_POST["id"],
							'tipoCustom'    => $tipoCustom,
							'custom'    	=> $custom,
							'estadoCustom'  => "Activo",
						];
						$cloud->insert('mip_perfil_custom', $insert);
					}

					// Registrar en tabla de bitacora - historial de solicitudes
					$insert = [
						'tipoSolicitud'			=> "Empleado",
						'id'					=> $_POST["id"],
						'usuarioIdAutoriza'   	=> $_SESSION["usuarioId"],
						'estadoSolicitud'    	=> "Autorizada",
						'fhSolicitud'  			=> $fhActual,
					];
					$cloud->insert('bit_solicitudes_acc_historial', $insert);
					// Verificar si el correo institucional ya lo agregaron
					$existeCorreo = $cloud->count("
							SELECT prsContactoId FROM th_personas_contacto
							WHERE personaId = ? AND tipoContactoId = '1' AND contactoPersona = ? AND flgDelete = '0'
						", [$_POST["pId"], $_POST["correoPersona"]]);
					if ($existeCorreo == 0) {
						// Registrar su correo institucional en personas contacto
						$insert = [
							'personaId'					=> $_POST["pId"],
							'tipoContactoId'   			=> 1,
							'contactoPersona'    		=> $_POST["correoPersona"],
							'descripcionPrsContacto'  	=> "Correo institucional",
							'estadoContacto'			=> "Activo",
						];
						$cloud->insert('th_personas_contacto', $insert);
					} else {
						// RRHH ya digitó su correo, no duplicar
					}
					// Bitácora de usuario final / jefes
					$cloud->writeBitacora("movUpdate", $stringBitacora);
					echo "success";
				} else {
					// No tiene permisos
					// Bitacora
					$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y autorizar una solicitud de acceso - empleado de " . $_POST["nombrePersona"] . " (manejo desde consola), ");
					echo "Acción no válida.";
				}
			} else { // Se rechazó, eliminar la solicitud
				if (in_array(14, $_SESSION["arrayPermisos"]) || in_array(47, $_SESSION["arrayPermisos"])) {
					$stringBitacora = "(" . $fhActual . ") Rechazó la solicitud de acceso de " . $_POST["nombrePersona"] . ", ";
					$cloud->deleteById('conf_usuarios', "usuarioId", $_POST["id"]);
					// Registrar en tabla de bitacora - historial de solicitudes
					$insert = [
						'tipoSolicitud'			=> "Empleado",
						'id'					=> $_POST["id"],
						'usuarioIdAutoriza'   	=> $_SESSION["usuarioId"],
						'estadoSolicitud'    	=> "Rechazada",
						'justificacionEstado'	=> $_POST["justificacionEstado"],
						'fhSolicitud'  			=> $fhActual,
					];
					$cloud->insert('bit_solicitudes_acc_historial', $insert);
					// Bitácora de usuario final / jefes
					$cloud->writeBitacora("movUpdate", $stringBitacora);
					echo "success";
				} else {
					// No tiene permisos
					// Bitacora
					$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y rechazar una solicitud de acceso - empleado de " . $_POST["nombrePersona"] . " (manejo desde consola), ");
					echo "Acción no válida.";
				}
			}
			break;

		case "solicitud-acceso-externa":
			/*
					POST:
					typeOperation
					operation
					solicitud
					nombrePersona
					dui
					fechaNacimiento
					correo
					id - solicitudAccesoId
					justificacionEstado
				*/
			// Este método ya no se utiliza, todo se recibe con $_POST, acá se empezaba a hacer pruebas con las librerías
			// todo viene concatenado con input=value&input=value en el post justificacionEstado
			// 0 = inputHiddenData
			// 1 = nombre1, 2 = nombre2, 3 = apellido1, 4 = apellido2
			// 5 = sexo, 6 = tipoPersona
			// 7 = dui, 8 = fechaNacimiento (se debe dar format a SQL yyyy-mm-dd)
			// 9 = correoInput

			$solicitudAccesoId = $_POST["id"];

			if ($_POST["solicitud"] == "autorizar") {
				$arrayFormData = explode("&", $_POST["justificacionEstado"]);
				$nombre1 = urldecode(explode("=", $arrayFormData[1])[1]);
				$nombre2 = urldecode(explode("=", $arrayFormData[2])[1]);
				$apellido1 = urldecode(explode("=", $arrayFormData[3])[1]);
				$apellido2 = urldecode(explode("=", $arrayFormData[4])[1]);
				$sexo = explode("=", $arrayFormData[5])[1];
				$tipoPersona = explode("=", $arrayFormData[6])[1];
				$dui = explode("=", $arrayFormData[7])[1];
				$fechaNacimiento = new DateTime(explode("=", $arrayFormData[8])[1]);
				$correo = urldecode(explode("=", $arrayFormData[9])[1]);
				//$correo = str_replace("%40", "@", $correo);
			} else {
				// Bug si se rechaza la solicitud
			}

			$flgSuccess = 0;
			if ($_POST["solicitud"] == "autorizar") {
				if (in_array(14, $_SESSION["arrayPermisos"]) || in_array(48, $_SESSION["arrayPermisos"])) {
					$sideName = $nombre1 . " " . $apellido1;

					$existePersona = $cloud->count("
							SELECT 
								personaId
							FROM th_personas
							WHERE numIdentidad = ? AND flgDelete = '0'
						", [$dui]);

					$existeUsuario = $cloud->count("
							SELECT 
								usuarioId
							FROM conf_usuarios
							WHERE correo = ? AND flgDelete = '0'
						", [$correo]);

					if ($existePersona == 0 && $existeUsuario == 0) {
						$stringBitacora = "(" . $fhActual . ") Aceptó la solicitud de acceso de " . $_POST["nombrePersona"] . " (Solicitud Externa), ";

						// Insert persona
						$insertPersona = [
							'prsTipoId'   		=> $tipoPersona,
							'docIdentidad'		=> 'DUI',
							'numIdentidad'      => $dui,
							'nombre1'   		=> $nombre1,
							'nombre2'           => $nombre2,
							'apellido1'       	=> $apellido1,
							'apellido2'		    => $apellido2,
							'fechaNacimiento'   => $fechaNacimiento->format('Y-m-d'),
							'sexo'	        	=> $sexo,
							'estadoCivil'		=> "Soltero/a",
							'paisId'			=> 61,
							'estadoPersona'		=> "Activo",
						];
						$personaId = $cloud->insert('th_personas', $insertPersona); // retorna el ultimoId insertado

						$defaultPass = "alina" . date("Y") . "$";
						// Insert usuario
						$nombreUsuario = explode("@", $correo);
						$insertUsuario = [
							'personaId'   		=> $personaId,
							'usuario'           => strtoupper($nombreUsuario[0]),
							'correo'   			=> $correo,
							'pass'           	=> password_hash(base64_encode(md5($defaultPass)), PASSWORD_BCRYPT),
							'estadoUsuario'     => "Activo",
						];
						$usuarioId = $cloud->insert('conf_usuarios', $insertUsuario); // retorna el ultimoId insertado

						// Registrar su correo en personas contacto
						$insert = [
							'personaId'					=> $personaId,
							'tipoContactoId'   			=> 1,
							'contactoPersona'    		=> $correo,
							'descripcionPrsContacto'  	=> "Correo personal",
							'estadoContacto'			=> "Activo",
						];
						$cloud->insert('th_personas_contacto', $insert);

						// Registrar en tabla de bitacora - historial de solicitudes
						$insert = [
							'tipoSolicitud'			=> "Externa",
							'id'					=> $solicitudAccesoId,
							'usuarioIdAutoriza'   	=> $_SESSION["usuarioId"],
							'estadoSolicitud'    	=> "Autorizada",
							'fhSolicitud'  			=> $fhActual,
						];
						$cloud->insert('bit_solicitudes_acc_historial', $insert);

						// Insert default customs para que cuando ingrese cargue todo en la sidebar mip_perfil_custom
						for ($i = 0; $i < 4; $i++) {
							if ($i == 0) {
								$tipoCustom = "Avatar";
								$custom = "mi-perfil/user-default.jpg";
							} else if ($i == 1) {
								$tipoCustom = "Estado";
								$custom = "En línea";
							} else if ($i == 2) {
								$tipoCustom = "Nombre";
								$custom = $sideName;
							} else {
								$tipoCustom = "Tema";
								$custom = "default";
							}
							$insert = [
								'usuarioId'		=> $usuarioId,
								'tipoCustom'    => $tipoCustom,
								'custom'    	=> $custom,
								'estadoCustom'  => "Activo",
							];
							$cloud->insert('mip_perfil_custom', $insert);
						}
						// Eliminar la solicitud
						$cloud->deleteById('bit_solicitudes_acceso', "solicitudAccesoId", $solicitudAccesoId);
						// Bitácora de usuario final / jefes
						$cloud->writeBitacora("movUpdate", $stringBitacora);
						echo "success";
					} else { // Persona o usuario YA existe, rechazar automáticamente
						$flgSuccess = 1;
						$stringBitacora = "(" . $fhActual . ") Se rechazó automáticamente la solicitud de acceso de " . $_POST["nombrePersona"] . " debido a que ya se le asignaron credenciales (Solicitud Externa), ";
						$cloud->deleteById('bit_solicitudes_acceso', "solicitudAccesoId", $_POST["id"]);
						// Registrar en tabla de bitacora - historial de solicitudes
						// Pendiente guardar justificacionEstado
						$insert = [
							'tipoSolicitud'			=> "Externa",
							'id'					=> $_POST["id"],
							'usuarioIdAutoriza'   	=> $_SESSION["usuarioId"],
							'estadoSolicitud'    	=> "Rechazada",
							'justificacionEstado'	=> "Solicitud rechazada automáticamente: La persona ya tiene credenciales asignadas",
							'fhSolicitud'  			=> $fhActual,
						];
						$cloud->insert('bit_solicitudes_acc_historial', $insert);
						echo "ya-existe"; // El mensaje completo/aviso está en la función procesarSolicitudExterna del archivo solicitudes-acceso para cerrar la modal después de rechazar automáticamente
						// Bitácora de usuario final / jefes
						$cloud->writeBitacora("movUpdate", $stringBitacora);
						// Se mostró un aviso de duplicado, etc
					}
				} else {
					// No tiene permisos
					// Bitacora
					$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y autorizar una solicitud de acceso - externa de " . $apellido1 . ", " . $nombre1 . " (manejo desde consola), ");
					echo "Acción no válida.";
				}
			} else { // Se rechazó, eliminar la solicitud
				if (in_array(14, $_SESSION["arrayPermisos"]) || in_array(49, $_SESSION["arrayPermisos"])) {
					$stringBitacora = "(" . $fhActual . ") Rechazó la solicitud de acceso de " . $_POST["nombrePersona"] . " (Solicitud Externa), ";
					$cloud->deleteById('bit_solicitudes_acceso', "solicitudAccesoId", $_POST["id"]);
					// Registrar en tabla de bitacora - historial de solicitudes
					// Pendiente guardar justificacionEstado
					$insert = [
						'tipoSolicitud'			=> "Externa",
						'id'					=> $_POST["id"],
						'usuarioIdAutoriza'   	=> $_SESSION["usuarioId"],
						'estadoSolicitud'    	=> "Rechazada",
						'justificacionEstado'	=> $_POST["justificacionEstado"],
						'fhSolicitud'  			=> $fhActual,
					];
					$cloud->insert('bit_solicitudes_acc_historial', $insert);
					// Bitácora de usuario final / jefes
					$cloud->writeBitacora("movUpdate", $stringBitacora);
					echo "success";
				} else {
					// No tiene permisos
					// Bitacora
					$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y rechazar una solicitud de acceso - externa de " . $apellido1 . ", " . $nombre1 . " (manejo desde consola), ");
					echo "Acción no válida.";
				}
			}
			break;

		case "editar-modulo":
			/*
					POST:
					hiddenFormData
					typeOperation
					operation
					moduloId
					modulo
					carpeta
					icono
					flgBadge
					badgeColor
					badgeText
					descripcionModulo
				*/
			if (in_array(12, $_SESSION["arrayPermisos"]) || in_array(33, $_SESSION["arrayPermisos"])) {
				$queryCarpeta = "
						SELECT 
							moduloId,
							modulo
						FROM conf_modulos
						WHERE urlModulo = ? AND moduloId <> ? AND flgDelete = '0'
					";
				$existeCarpetaAsignada = $cloud->count($queryCarpeta, [$_POST["carpeta"], $_POST["moduloId"]]);
				if ($existeCarpetaAsignada == 0) {
					$badgeColor = ($_POST["flgBadge"] == 0) ? null : $_POST["badgeColor"];
					$badgeText = ($_POST["flgBadge"] == 0) ? null : $_POST["badgeText"];

					$update = [
						'modulo'				=> $_POST["modulo"],
						'descripcionModulo'		=> $_POST["descripcionModulo"],
						'iconModulo'			=> $_POST["icono"],
						'urlModulo'				=> $_POST["carpeta"],
						'badgeColor'			=> $badgeColor,
						'badgeText'				=> $badgeText,
					];
					$where = ['moduloId' => $_POST["moduloId"]];
					$cloud->update('conf_modulos', $update, $where);

					// Bitácora de usuario final / jefes
					$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la información del módulo: " . $_POST["modulo"] . ", ");
					echo "success";
				} else {
					$nombreCarpeta = $cloud->row($queryCarpeta, [$_POST["carpeta"]]);
					echo "La carpeta seleccionada ya ha sido asignada al módulo: " . $nombreCarpeta->modulo;
				}
			} else {
				// No tiene permisos
				// Bitacora
				$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y editar un módulo " . $_POST["modulo"] . " (manejo desde consola), ");
				echo "Acción no válida.";
			}
			break;

		case "menu-permiso":
			/*
					POST:
					typeOperation
					operation
					menuPermisoId
					permisoMenu
				*/
			if (in_array(12, $_SESSION["arrayPermisos"]) || in_array(40, $_SESSION["arrayPermisos"])) {
				$update = [
					'permisoMenu' => $_POST["permisoMenu"]
				];
				$where = ["menuPermisoId" => $_POST["menuPermisoId"]];
				$cloud->update('conf_menus_permisos', $update, $where);
				// Bitácora de usuario final / jefes
				$dataNombreMenuPermiso = $cloud->row("
						SELECT
							mp.permisoMenu AS permisoMenu,
						    m.menu AS menu
						FROM conf_menus_permisos mp
						JOIN conf_menus m ON m.menuId = mp.menuId
						WHERE menuPermisoId = ?
					", [$_POST["menuPermisoId"]]);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la información del permiso: " . $_POST["permisoMenu"] . " (Menú: " . $dataNombreMenuPermiso->menu . "), ");
				echo "success";
			} else {
				// No tiene permisos
				// Bitacora
				$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y editar un permiso " . $_POST["permisoMenu"] . " (manejo desde consola), ");
				echo "Acción no válida.";
			}
			break;

		case "estudio":
			/*
					POST:
					hiddenFormData
					typeOperation
					operation
					idArea
                    nombreArea
				*/
			if (in_array(9, $_SESSION["arrayPermisos"]) || in_array(16, $_SESSION["arrayPermisos"])) {
				$existeDuplicado = $cloud->count("
                		SELECT
                			areaEstudio
                		FROM cat_personas_ar_estudio
                		WHERE areaEstudio = ? AND prsArEstudioId != ? AND flgDelete = 0
                	", [$_POST["nombreArea"], $_POST["idArea"]]);

				if ($existeDuplicado == 0) {
					$update = [
						'areaEstudio' => $_POST["nombreArea"]
					];
					$where = ["prsArEstudioId" => $_POST["idArea"]];
					$cloud->update('cat_personas_ar_estudio', $update, $where);
					$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó el área de estudio: " . $_POST["nombreArea"] . ", ");
					echo "success";
				} else {
					echo "El área de estudio: " . $_POST["nombreArea"] . " ya existe en el catálogo.";
				}
			} else {
				// No tiene permisos
				// Bitacora
				$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y modificar el área de experiencia: " . $_POST["nombreArea"] . ", ");
				echo "Acción no válida.";
			}
			break;

		case "experiencia":
			/*
					POST:
					hiddenFormData
					typeOperation
					operation
					idArea
                    nombreArea
				*/
			if (in_array(9, $_SESSION["arrayPermisos"]) || in_array(16, $_SESSION["arrayPermisos"])) {
				$existeDuplicado = $cloud->count("
                		SELECT 
                			areaExperiencia
                		FROM cat_personas_ar_experiencia
                		WHERE areaExperiencia = ? AND prsArExperienciaId != ? AND flgDelete = 0
                	", [$_POST["nombreArea"], $_POST["idArea"]]);

				if ($existeDuplicado == 0) {
					$update = [
						'areaExperiencia' => $_POST["nombreArea"]
					];
					$where = ["prsArExperienciaId " => $_POST["idArea"]];
					$cloud->update('cat_personas_ar_experiencia', $update, $where);
					$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó el área de experiencia: " . $_POST["nombreArea"] . ", ");
					echo "success";
				} else {
					echo "El área de experiencia: " . $_POST["nombreArea"] . " ya existe en el catálogo.";
				}
			} else {
				// No tiene permisos
				// Bitacora
				$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y modificar el área de experiencia: " . $_POST["nombreArea"] . ", ");
				echo "Acción no válida.";
			}
			break;
		case "software":
			/*
					POST:
					hiddenFormData
					typeOperation
					operation
					idArea
                    nombreArea
				*/
			//if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(16, $_SESSION["arrayPermisos"])) {
			$existeDuplicado = $cloud->count("
                		SELECT
                			nombreSoftware
                		FROM cat_personas_software
                		WHERE nombreSoftware = ? AND prsSoftwareId != ? AND flgDelete = 0
                	", [$_POST["nombreArea"], $_POST["idArea"]]);

			if ($existeDuplicado == 0) {
				$update = [
					'nombreSoftware' => $_POST["nombreArea"]
				];
				$where = ["prsSoftwareId" => $_POST["idArea"]];
				$cloud->update('cat_personas_software', $update, $where);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó el software: " . $_POST["nombreArea"] . ", ");
				echo "success";
			} else {
				echo "El programa informático: " . $_POST["nombreArea"] . " ya existe en el catálogo.";
			}

			/*} else {
                    // No tiene permisos
                    // Bitacora
                    $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y modificar el área de experiencia: " . $_POST["nombreArea"] . ", ");
                    echo "Acción no válida.";
                }*/
			break;
		case "herraEqu":
			/*
					POST:
					hiddenFormData
					typeOperation
					operation
					idArea
                    nombreArea
				*/
			//if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(16, $_SESSION["arrayPermisos"])) {
			$existeDuplicado = $cloud->count("
                		SELECT
                			nombreHerrEquipo
                		FROM cat_personas_herr_equipos
                		WHERE nombreHerrEquipo = ? AND prsHerrEquipoId != ? AND flgDelete = 0
                	", [$_POST["nombreArea"], $_POST["idArea"]]);

			if ($existeDuplicado == 0) {
				$update = [
					'nombreHerrEquipo' => $_POST["nombreArea"]
				];
				$where = ["prsHerrEquipoId" => $_POST["idArea"]];
				$cloud->update('cat_personas_herr_equipos', $update, $where);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la herramienta/equipo: " . $_POST["nombreArea"] . ", ");
				echo "success";
			} else {
				echo "La herramienta/equipo: " . $_POST["nombreArea"] . " ya existe en el catálogo.";
			}

			/*} else {
                    // No tiene permisos
                    // Bitacora
                    $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y modificar el área de experiencia: " . $_POST["nombreArea"] . ", ");
                    echo "Acción no válida.";
                }*/
			break;
		case "tipoRelacion":
			/*
					POST:
					hiddenFormData
					typeOperation
					operation
					idArea
                    nombreRelacion
				*/
			//if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(16, $_SESSION["arrayPermisos"])) {
			$existeDuplicado = $cloud->count("
                		SELECT
                			tipoPrsRelacion
                		FROM cat_personas_relacion
                		WHERE tipoPrsRelacion = ? AND catPrsRelacionId != ? AND flgDelete = 0
                	", [$_POST["nombreRelacion"], $_POST["idRelacion"]]);

			if ($existeDuplicado == 0) {
				$update = [
					'tipoPrsRelacion' => $_POST["nombreRelacion"]
				];
				$where = ["catPrsRelacionId" => $_POST["idRelacion"]];
				$cloud->update('cat_personas_relacion', $update, $where);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la relación: " . $_POST["nombreRelacion"] . ", ");
				echo "success";
			} else {
				echo "El tipo de relación: " . $_POST["nombreRelacion"] . " ya existe en el catálogo.";
			}

			/*} else {
                    // No tiene permisos
                    // Bitacora
                    $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y modificar el área de experiencia: " . $_POST["nombreArea"] . ", ");
                    echo "Acción no válida.";
                }*/
			break;

		case "editar-menu":
			/*
					POST:
					hiddenFormData: menuId ^ moduloId ^ nombreMenu
					typeOperation
					operation
					moduloId
					ordenRecomendado
					menu
					tipoMenu
					menuSuperior
					icono
					flgBadge
					badgeColor
					badgeText
					urlMenu
					ordenMenu
					flgMenuDisponible
				*/
			$arrayFormData = explode("^", $_POST["hiddenFormData"]);
			$menuId = $arrayFormData[0];
			$nombreMenu = $arrayFormData[2];

			$queryExisteURL = "
					SELECT
						menu
					FROM conf_menus
					WHERE moduloId = ? AND urlMenu = ? AND menuId <> ? AND flgDelete = '0'
				";
			$existeURL = $cloud->count($queryExisteURL, [$_POST["moduloId"], $_POST["urlMenu"], $menuId]);

			if ($existeURL == 0) {
				$stringSuccess = "success^";

				$badgeColor = ($_POST["flgBadge"] == 0) ? null : $_POST["badgeColor"];
				$badgeText = ($_POST["flgBadge"] == 0) ? null : $_POST["badgeText"];

				$dataMenuActual = $cloud->row("
						SELECT
							numOrdenMenu,
							menuSuperior,
							urlMenu
						FROM conf_menus
						WHERE menuId = ?
					", [$menuId]);

				$estadoMenu = "";
				if (isset($_POST['flgMenuDisponible'])) {
					$estadoMenu = "Disponible";
				} else {
					$estadoMenu = "Mantenimiento";
				}

				if ($_POST["tipoMenu"] == "unico") {
					$update = [
						'menu'					=> $_POST["menu"],
						'iconMenu'				=> $_POST["icono"],
						'urlMenu'				=> (is_null($dataMenuActual->urlMenu) || $dataMenuActual->urlMenu == "") ? NULL : $_POST["urlMenu"],
						'numOrdenMenu'			=> $_POST["ordenMenu"],
						'menuSuperior'			=> '0',
						'badgeColor'			=> $badgeColor,
						'badgeText'				=> $badgeText,
						'estadoMenu' 			=> $estadoMenu
					];
					$where = ['menuId' => $menuId];
					$cloud->update('conf_menus', $update, $where);
					$menuSuperior = 0;

					if ($dataMenuActual->menuSuperior > 0) { // Era submenú y se convirtió en único
						$stringSuccess .= "Menú actualizado con éxito.<br>El submenú: " . $_POST["menu"] . ' ha pasado a ser menú "Único".';
					} else {
						$stringSuccess .= "Menú actualizado con éxito.";
					}
				} else {
					$update = [
						'menu'					=> $_POST["menu"],
						'iconMenu'				=> $_POST["icono"],
						'urlMenu'				=> $_POST["urlMenu"],
						'numOrdenMenu'			=> $_POST["ordenMenu"],
						'menuSuperior'			=> $_POST["menuSuperior"],
						'badgeColor'			=> $badgeColor,
						'badgeText'				=> $badgeText,
						'estadoMenu' 		=> $estadoMenu
					];
					$where = ['menuId' => $menuId];
					$cloud->update('conf_menus', $update, $where);
					$menuSuperior = $_POST["menuSuperior"];

					// Convertir el id de "menuSuperior (menuId)" a dropdown
					$update = [
						'urlMenu' 	=> null,
					];
					$where = ['menuId' => $_POST["menuSuperior"]];
					$cloud->update('conf_menus', $update, $where);
					$nombreDropdown = $cloud->row("
							SELECT
								menu
							FROM conf_menus
							WHERE menuId = ?
						", [$_POST["menuSuperior"]]);

					if ($dataMenuActual->menuSuperior == 0) { // Era menú y se convirtió en submenú
						$stringSuccess .= "Submenú actualizado con éxito.<br>El menú: " . $_POST["menu"] . ' ha pasado a ser menú "Submenú".';
					} else {
						$stringSuccess .= "Submenú actualizado con éxito.";
					}

					$stringSuccess .= "<br>El menú: " . $nombreDropdown->menu . ' ha pasado a ser "dropdown".';
				}

				// Validar si se deben actualizar los ordenMenu. Si el ordenActual es diferente al colocado, se debe ordenar porque se afectó la cadena correlativa que se traía
				// Lo único que no ordena es al convertir un único a submenú, el resto de únicos queda con correlativo perdido xD
				if ($dataMenuActual->numOrdenMenu != $_POST["ordenMenu"]) {
					if ($menuSuperior == 0) {
						$stringSuccess .= "<br>Se ha actualizado el orden de los menús:";
					} else {
						$stringSuccess .= "<br>Se ha actualizado el orden de los submenús:";
					}

					if ($dataMenuActual->numOrdenMenu > $_POST["ordenMenu"]) {
						$menor = $_POST["ordenMenu"];
						$mayor = $dataMenuActual->numOrdenMenu;
						$flgSumRes = "suma";
					} else {
						$menor = $dataMenuActual->numOrdenMenu;
						$mayor = $_POST["ordenMenu"];
						$flgSumRes = "resta";
					}

					$iterarOrdenRepetidos = $cloud->rows("
							SELECT
								menuId,
								menu,
								numOrdenMenu
							FROM conf_menus
							WHERE moduloId = ? AND menuSuperior = ? AND numOrdenMenu BETWEEN ? AND ? AND menuId <> ? AND flgDelete = '0'
						", [$_POST["moduloId"], $menuSuperior, $menor, $mayor, $menuId]);
					$n = 0;
					foreach ($iterarOrdenRepetidos as $iterarOrdenRepetidos) {
						$n += 1;

						if ($flgSumRes == "suma") {
							$numOrdenMenu = $iterarOrdenRepetidos->numOrdenMenu + 1;
						} else {
							$numOrdenMenu = $iterarOrdenRepetidos->numOrdenMenu - 1;
						}

						$update = [
							'numOrdenMenu' 	=> $numOrdenMenu,
						];
						$where = ['menuId' => $iterarOrdenRepetidos->menuId];
						$cloud->update('conf_menus', $update, $where);
						$stringSuccess .= "<br>" . $n . ". " . $iterarOrdenRepetidos->menu . " (" . $iterarOrdenRepetidos->numOrdenMenu . " -> " . $numOrdenMenu . ")";
					}
				} else {
					// No actualizar los numOrden, ya que se mantuvo el actual (no se modificó ese input)
				}
				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la información del menú " . $_POST["menu"] . ", ");
				echo $stringSuccess;
			} else {
				$nombreMenuURL = $cloud->row($queryExisteURL, [$_POST["moduloId"], $_POST["urlMenu"]]);
				echo "La URL ya fue asignada al menú: " . $nombreMenuURL->menu;
			}
			break;

		case "tipoContacto":
			/*
					POST:
	        		hiddenFormData
					typeOperation
					operation
					tipoContactoId
                    nombreContacto
                    masca
	        	*/
			if (in_array(9, $_SESSION["arrayPermisos"]) || in_array(19, $_SESSION["arrayPermisos"])) {
				$existeDuplicado = $cloud->count("
                		SELECT
                			tipoContacto
                		FROM cat_tipos_contacto
                		WHERE tipoContacto = ? AND formatoContacto = ? AND tipoContactoId != ? AND flgDelete = 0
                	", [$_POST["nombreContacto"], $_POST["masca"], $_POST["tipoContactoId"]]);

				if ($existeDuplicado == 0) {
					$update = [
						'tipoContacto'    => $_POST["nombreContacto"],
						'formatoContacto' => $_POST["masca"],
					];
					$where = ['tipoContactoId' => $_POST["tipoContactoId"]]; // ids, soporta múltiple where

					$cloud->update('cat_tipos_contacto', $update, $where);
					$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó el tipo de contacto: " . $_POST["nombreContacto"] . " (Máscara: " . $_POST["masca"] . "), ");
					echo "success";
				} else {
					echo "El tipo de contacto: " . $_POST["nombreContacto"] . " / " . $_POST["masca"] . " ya existe en el catálogo.";
				}
			} else {
				// No tiene permisos
				// Bitacora
				$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y modificar el tipo de contacto: " . $_POST["nombreContacto"] . " (Máscara: " . $_POST["masca"] . "), ");
				echo "Acción no válida.";
			}
			break;

		case "menu-permisos-usuario":
			/*
					POST:
	        		hiddenFormData - menuId ^ usuarioId ^ nombrePersona ^ modulo ^ menu ^ tipoMenu
					typeOperation
					operation
					checkPermisos[] - Multiple
	        	*/
			$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);

			if (in_array(10, $_SESSION["arrayPermisos"]) || in_array(52, $_SESSION["arrayPermisos"])) {
				// Evitar que se dibuje "Desarrollo" a otros usuarios
				$whereDesarrollo = (in_array(10, $_SESSION["arrayPermisos"])) ? "" : "AND permisoMenu<>'Desarrollo'";

				$stringSuccess = "success^";

				// Iterar todos los permisos de ese menú
				$dataPermisosMenu = $cloud->rows("
						SELECT
							menuPermisoId,
							permisoMenu
						FROM conf_menus_permisos
						WHERE menuId = ? $whereDesarrollo AND flgDelete = '0'
					", [$arrayHiddenForm[0]]);
				$permisosAgregados = 0;
				$permisosEliminados = 0;
				foreach ($dataPermisosMenu as $dataPermisosMenu) {
					// Iterar permisos "marcados" en la interfaz (checkbox)
					$flgChecked = 0;
					foreach ($_POST["checkPermisos"] as $checkPermiso) { // checkPermisos = menuPermisoId
						if ($dataPermisosMenu->menuPermisoId == $checkPermiso) { // Se encontró el permiso marcado
							$flgChecked = 1;
							$existePermiso = $cloud->count("
			        				SELECT 
			        					permisoUsuarioId
			        				FROM conf_permisos_usuario
			        				WHERE menuPermisoId = ? AND usuarioId = ? AND flgDelete = '0'
			        			", [$checkPermiso, $arrayHiddenForm[1]]);

							if ($existePermiso == 0) {
								// No tiene este permiso y está marcado, insertar
								$insert = [
									'menuPermisoId' 	=> $checkPermiso,
									'usuarioId'			=> $arrayHiddenForm[1],
								];
								$cloud->insert("conf_permisos_usuario", $insert);
								$permisosAgregados += 1;

								// Bitácora de usuario final / jefes
								$dataNombreMenuPermiso = $cloud->row("
										SELECT
											mp.permisoMenu AS permisoMenu,
										    m.menu AS menu
										FROM conf_menus_permisos mp
										JOIN conf_menus m ON m.menuId = mp.menuId
										WHERE menuPermisoId = ?
									", [$checkPermiso]);

								$dataNombreUsuario = $cloud->row("
								        SELECT
								            CONCAT(
								                IFNULL(per.apellido1, '-'),
								                ' ',
								                IFNULL(per.apellido2, '-'),
								                ', ',
								                IFNULL(per.nombre1, '-'),
								                ' ',
								                IFNULL(per.nombre2, '-')
								            ) AS nombrePersona
								        FROM conf_usuarios us
								        JOIN th_personas per ON per.personaId = us.personaId
								        WHERE us.usuarioId = ?
								    ", [$arrayHiddenForm[1]]);

								$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Asignó el permiso: " . $dataNombreMenuPermiso->permisoMenu . " del menú: " . $dataNombreMenuPermiso->menu . " al usuario: " . $dataNombreUsuario->nombrePersona . ", ");
								// movInsert porque aunque esté en update, es un insert el que se hace
							} else {
								// Ya tiene este permiso, omitir
							}
						} else {
							// Permiso no marcado, pasar al siguiente
						}
					}

					if ($flgChecked == 0) { // No se encontró marcado, se "desmarcó" posiblemente
						// Verificar si lo tenia asignado
						$existePermiso = $cloud->count("
		        				SELECT 
		        					permisoUsuarioId
		        				FROM conf_permisos_usuario
		        				WHERE menuPermisoId = ? AND usuarioId = ? AND flgDelete = '0'
		        			", [$dataPermisosMenu->menuPermisoId, $arrayHiddenForm[1]]);
						if ($existePermiso > 0) {
							// Tiene este permiso asignado, pero no venia marcado, eliminar
							$where = [
								'menuPermisoId' 	=> $dataPermisosMenu->menuPermisoId,
								'usuarioId'			=> $arrayHiddenForm[1],
							];
							$cloud->delete("conf_permisos_usuario", $where);
							$permisosEliminados += 1;

							// Bitácora de usuario final / jefes
							$dataNombreMenuPermiso = $cloud->row("
									SELECT
										mp.permisoMenu AS permisoMenu,
									    m.menu AS menu
									FROM conf_menus_permisos mp
									JOIN conf_menus m ON m.menuId = mp.menuId
									WHERE menuPermisoId = ?
								", [$dataPermisosMenu->menuPermisoId]);

							$dataNombreUsuario = $cloud->row("
							        SELECT
							            CONCAT(
							                IFNULL(per.apellido1, '-'),
							                ' ',
							                IFNULL(per.apellido2, '-'),
							                ', ',
							                IFNULL(per.nombre1, '-'),
							                ' ',
							                IFNULL(per.nombre2, '-')
							            ) AS nombrePersona
							        FROM conf_usuarios us
							        JOIN th_personas per ON per.personaId = us.personaId
							        WHERE us.usuarioId = ?
							    ", [$arrayHiddenForm[1]]);

							$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el permiso: " . $dataNombreMenuPermiso->permisoMenu . " del menú: " . $dataNombreMenuPermiso->menu . " al usuario: " . $dataNombreUsuario->nombrePersona . ", ");
							// movDelete porque aunque esté en update es un delete el que se hace
						} else {
							// No tenia este permiso, aunque no viniera marcado, omitir
						}
					} else {
						// Estaba marcado, ya se procesó
					}
				}

				if ($permisosAgregados == 0) {
					$stringSuccess .= "<br>No se asignó ningún permiso.";
				} else {
					$stringSuccess .= "<br>Se asignaron " . $permisosAgregados . " permisos.";
				}

				if ($permisosEliminados > 0) {
					$stringSuccess .= "<br>Se eliminaron " . $permisosEliminados . " permisos.";
				} else {
					// No redactar nada    			
				}

				echo $stringSuccess;
			} else {
				// No tiene permisos
				// Bitacora
				$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y editar permisos de usuario [m = " . $arrayHiddenForm[0] . ", u = " . $arrayHiddenForm[1] . "] (manejo desde consola), ");
				echo "Acción no válida.";
			}
			break;

		case "editarSucursal":
			/*
					POST:
	        		hiddenFormData
					typeOperation
					operation
					sucursalId
                    nombreSucursal
                    departamento
                    municipio
                    direccion
                    subirLogo
	        	*/

			if (in_array(9, $_SESSION["arrayPermisos"]) || in_array(22, $_SESSION["arrayPermisos"])) {
				$imagenNombre = $_FILES['subirLogo']['name'];

				if ($imagenNombre) {
					$ubicacion = "../../../../libraries/resources/images/logos/sucursales/" . $imagenNombre;
					$flgSubir = 1;
					$imagenFormato = pathinfo($ubicacion, PATHINFO_EXTENSION);

					$formatosPermitidos = array("jpg", "jpeg", "png");

					if (!in_array(strtolower($imagenFormato), $formatosPermitidos)) {
						$flgSubir = 0;
					} else {
						$flgSubir = 1;
					}
					if ($flgSubir == 0) {
						// Validación de formato nuevamente por si se evade la de Javascript
						echo "El archivo seleccionado no coincide con una imagen. Por favor vuelva a seleccionar una imagen con formato válido.";
					} else {
						if (move_uploaded_file($_FILES['subirLogo']['tmp_name'], $ubicacion)) {
							$update = [
								'sucursal' => $_POST["nombreSucursal"],
								'paisMunicipioId' => $_POST["municipio"],
								'direccionSucursal' => $_POST["direccion"],
								'urlLogoSucursal' => 'logos/sucursales/' . $imagenNombre
							];
							$where = ['sucursalId' => $_POST["sucursalId"]]; // ids, soporta múltiple where
							$cloud->update('cat_sucursales', $update, $where);
							$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó los datos de sucursal: " . $_POST["nombreSucursal"] . ", ");
							echo "success";
						} else {
							echo "Problema al cargar la imagen. Por favor comuniquese con el departamento de Informática.";
						}
					}
				} else {
					$update = [
						'sucursal' => $_POST["nombreSucursal"],
						'paisMunicipioId' => $_POST["municipio"],
						'direccionSucursal' => $_POST["direccion"],
					];
					$where = ['sucursalId' => $_POST["sucursalId"]]; // ids, soporta múltiple where
					$cloud->update('cat_sucursales', $update, $where);
					$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó los datos de sucursal: " . $_POST["nombreSucursal"] . ", ");
					echo "success";
				}
			} else {
				// No tiene permisos
				// Bitacora
				$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y modificar los datos de sucursal: " . $_POST["nombreSucursal"] . ", ");
				echo "Acción no válida.";
			}

			break;

		case "ContactoSucursal":
			/*
					POST:
	        		hiddenFormData
					typeOperation
					operation
					idSucursal
                    idContact
                    tipoContacto
                    contactoSucursal
                    descripcionContacto
                    tblContactSuc_length
	        	*/
			if (in_array(9, $_SESSION["arrayPermisos"]) || in_array(25, $_SESSION["arrayPermisos"])) {
				$update = [
					'tipoContactoId'     => $_POST["tipoContacto"],
					'contactoSucursal ' => $_POST["contactoSucursal"],
					'descripcionCSucursal' => $_POST["descripcionContacto"]
				];
				$where = ['sucursalContactoId' => $_POST["idContact"], 'sucursalId' => $_POST["idSucursal"]];
				$cloud->update('cat_sucursales_contacto', $update, $where);
				$dataSucs = $cloud->row("
                        SELECT sucursal FROM cat_sucursales WHERE flgDelete = 0 AND sucursalId =?
                    ", [$_POST["idSucursal"]]);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó el contacto: " . $_POST["descripcionContacto"] . " de la sucursal: " . $dataSucs->sucursal . ", ");
				echo "success";
			} else {
				// No tiene permisos
				// Bitacora
				$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y modificar el contacto de sucursal: " . $dataSucs->sucursal . ", ");
				echo "Acción no válida.";
			}

			break;

		case "editar-usuario":
			/*
					POST - Empleado:
					hiddenFormData - usuarioId ^ nombreUsuario ^ tipoPersona
					typeOperation
					operation
					flgPersona
					correo
					
					POST - Externo:
					hiddenFormData - usuarioId ^ nombreUsuario ^ tipoPersona
					typeOperation
					operation
					flgPersona
					personaId
					nombre1
					nombre2
					apellido1
					apellido2
					sexo
					tipoPersona
					dui
					fechaNacimiento
					correo
	        	*/
			$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
			// Para bitacora
			$dataNombreUsuario = $cloud->row("
			        SELECT
			        	us.personaId AS personaId,
			            CONCAT(
			                IFNULL(per.apellido1, '-'),
			                ' ',
			                IFNULL(per.apellido2, '-'),
			                ', ',
			                IFNULL(per.nombre1, '-'),
			                ' ',
			                IFNULL(per.nombre2, '-')
			            ) AS nombrePersona,
			            us.correo AS correo
			        FROM conf_usuarios us
			        JOIN th_personas per ON per.personaId = us.personaId
			        WHERE us.usuarioId = ?
			    ", [$arrayHiddenForm[0]]);

			if ($arrayHiddenForm[2] == "Empleado") {
				// Posee permiso
				if (in_array(9, $_SESSION["arrayPermisos"]) || in_array(59, $_SESSION["arrayPermisos"])) {
					$updateUsuario = [
						'correo'	=> $_POST["correo"],
					];
					$whereUsuario = ['usuarioId' => $arrayHiddenForm[0]];
					$cloud->update('conf_usuarios', $updateUsuario, $whereUsuario);

					// Actualizar tabla contactos persona
					$updateContacto = [
						'contactoPersona'	=> $_POST["correo"],
					];
					$whereContacto = [
						'personaId'					=> $dataNombreUsuario->personaId,
						'tipoContactoId'			=> '1',
						'descripcionPrsContacto' 	=> 'Correo institucional',
					];
					$cloud->update('th_personas_contacto', $updateContacto, $whereContacto);

					// Bitácora de usuario final / jefes
					$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó el correo del usuario: " . $dataNombreUsuario->nombrePersona . " (Correo: " . $_POST["correo"] . " - Empleado), ");
					echo "success^Usuario actualizado con éxito.";
				} else {
					// No tiene permisos
					// Bitacora
					$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y modificar la información del usuario: " . $dataNombreUsuario->nombrePersona . " (Correo: " . $_POST["correo"] . " - Interno, manejo desde consola), ");
					echo "Acción no válida.";
				}
			} else {
				if (in_array(9, $_SESSION["arrayPermisos"]) || in_array(62, $_SESSION["arrayPermisos"])) {
					$update = [
						'numIdentidad'		=> $_POST["dui"],
						'nombre1'			=> $_POST["nombre1"],
						'nombre2'			=> $_POST["nombre2"],
						'apellido1'			=> $_POST["apellido1"],
						'apellido2'			=> $_POST["apellido2"],
						'fechaNacimiento'	=> date("Y-m-d", strtotime($_POST["fechaNacimiento"])),
						'sexo'				=> $_POST["sexo"],
					];
					$where = ['personaId' => $_POST["personaId"]];
					$cloud->update('th_personas', $update, $where);
					$updateUsuario = [
						'correo'	=> $_POST["correo"],
					];
					$whereUsuario = ['usuarioId' => $arrayHiddenForm[0]];
					$cloud->update('conf_usuarios', $updateUsuario, $whereUsuario);

					// Actualizar tabla contactos persona
					$updateContacto = [
						'contactoPersona'	=> $_POST["correo"],
					];
					$whereContacto = [
						'personaId'					=> $dataNombreUsuario->personaId,
						'tipoContactoId'			=> '1',
						'descripcionPrsContacto' 	=> 'Correo institucional',
					];
					$cloud->update('th_personas_contacto', $updateContacto, $whereContacto);

					// Bitácora de usuario final / jefes
					$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la información del usuario: " . $dataNombreUsuario->nombrePersona . " (Correo: " . $_POST["correo"] . " - Externo), ");
					echo "success^Usuario actualizado con éxito.";
				} else {
					// No tiene permisos
					// Bitacora
					$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y modificar la información del usuario: " . $dataNombreUsuario->nombrePersona . " (Correo: " . $_POST["correo"] . " - Externo, manejo desde consola), ");
					echo "Acción no válida.";
				}
			}
			break;

		case "estado-credenciales":
			/*
	        		POST:
					typeOperation
					operation
					tipoEstado
					tipoPersona
					id - usuarioId
					nombreUsuario
					justificacionEstado
	        	*/
			$flgPermiso = 0;
			if ($_POST["tipoPersona"] == "Empleado") {
				$permiso = 60;
			} else {
				$permiso = 63;
			}

			if (in_array(9, $_SESSION["arrayPermisos"]) || in_array($permiso, $_SESSION["arrayPermisos"])) {
				$flgPermiso = 1;
			} else {
			}

			if ($flgPermiso == 1) {
				$dataNombreUsuario = $cloud->row("
				        SELECT
				            CONCAT(
				                IFNULL(per.apellido1, '-'),
				                ' ',
				                IFNULL(per.apellido2, '-'),
				                ', ',
				                IFNULL(per.nombre1, '-'),
				                ' ',
				                IFNULL(per.nombre2, '-')
				            ) AS nombrePersona
				        FROM conf_usuarios us
				        JOIN th_personas per ON per.personaId = us.personaId
				        WHERE us.usuarioId = ?
				    ", [$_POST["id"]]);

				if ($_POST["tipoEstado"] == "activar") {
					$defaultPass = "alina" . date("Y") . "$";
					$update = [
						'pass'					=> password_hash(base64_encode(md5($defaultPass)), PASSWORD_BCRYPT),
						'estadoUsuario'			=> 'Activo',
						'justificacionEstado'	=> null,
						'intentosLogin'			=> 0,
					];
					$where = ['usuarioId' => $_POST["id"]];

					$cloud->update('conf_usuarios', $update, $where);
					// Bitácora de usuario final / jefes
					$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó el estado del usuario: " . $dataNombreUsuario->nombrePersona . " (Estado: Activo), ");
				} else { // suspender
					$defaultPass = base64_encode(md5("( ͡° ͜ʖ ͡°)")); // default pass al suspender xd
					$update = [
						'pass'					=> $defaultPass,
						'estadoUsuario'			=> 'Suspendido',
						'justificacionEstado'	=> $_POST["justificacionEstado"],
					];
					$where = ['usuarioId' => $_POST["id"]];

					$cloud->update('conf_usuarios', $update, $where);

					// Eliminar sus permisos
					$cloud->deleteById('conf_permisos_usuario', "usuarioId ", $_POST['id']);

					// Bitácora de usuario final / jefes
					$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó el estado del usuario: " . $dataNombreUsuario->nombrePersona . " (Estado: Suspendido), ");
				}
				echo "success";
			} else {
				// No tiene permisos
				// Bitacora
				$estado = ($_POST["tipoEstado"] == "activar") ? "Suspendido" : "Activo";
				$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y modificar el estado del usuario: " . $dataNombreUsuario->nombrePersona . " (Estado: " . $estado . " manejo desde consola), ");
				echo "Acción no válida.";
			}
			break;

		case "enfermedad-alergia":
			/*
					POST
					hiddenFormData = editar ^ catPrsEnfermedadId
					typeOperation
					operation
					tipoEnfermedad
					nombreEnfermedad
				*/
			$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
			// Verificar sino existe en el catalogo
			$queryEnfermedadDuplicada = "
					SELECT 
						catPrsEnfermedadId
					FROM cat_personas_enfermedades
					WHERE tipoEnfermedad = ? AND nombreEnfermedad = ? AND catPrsEnfermedadId <> ? AND flgDelete = '0'
				";
			$existeEnfermedadDuplicada = $cloud->count($queryEnfermedadDuplicada, [$_POST["tipoEnfermedad"], $_POST["nombreEnfermedad"], $arrayHiddenForm[1]]);
			if ($existeEnfermedadDuplicada == 0) {
				// update catalogo enfermedades para que la proxima vez pueda seleccionarlo
				$update = [
					'tipoEnfermedad'		=> $_POST["tipoEnfermedad"],
					'nombreEnfermedad'		=> $_POST["nombreEnfermedad"]
				];
				$where = ['catPrsEnfermedadId' => $arrayHiddenForm[1]];
				$cloud->update('cat_personas_enfermedades', $update, $where);
				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la enfermedad/alergia del catálogo: " . $_POST["nombreEnfermedad"] . " (" . $_POST["tipoEnfermedad"] . "), ");
				echo "success";
			} else {
				// Ya existe en el catálogo, mostrar aviso
				echo "La " . $_POST["tipoEnfermedad"] . " - " . $_POST["nombreEnfermedad"] . " ya existe en el catálogo";
			}
			break;

		case "organizacion":
			/*
					POST
					hiddenFormData = editar ^ nombreOrganizacionId
					typeOperation
					operation
					tipoOrganizacionHidden - Ya que tipoOrganizacion es disabled en editar
					nombreOrganizacion
					abreviaturaOrganizacion
				*/
			$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
			// Verificar sino existe en el catalogo
			$queryOrganizacionDuplicada = "
					SELECT 
						nombreOrganizacionId
					FROM cat_nombres_organizaciones
					WHERE tipoOrganizacion = ? AND abreviaturaOrganizacion = ? AND nombreOrganizacionId <> ? AND flgDelete = '0'
				";
			$existeDuplicado = $cloud->count($queryOrganizacionDuplicada, [$_POST["tipoOrganizacionHidden"], $_POST["abreviaturaOrganizacion"], $arrayHiddenForm[1]]);
			if ($existeDuplicado == 0) {
				// insert catalogo enfermedades para que la proxima vez pueda seleccionarlo
				$update = [
					'nombreOrganizacion'		=> $_POST["nombreOrganizacion"],
					'abreviaturaOrganizacion'	=> $_POST["abreviaturaOrganizacion"]
				];
				$where = ['nombreOrganizacionId' => $arrayHiddenForm[1]];
				$cloud->update('cat_nombres_organizaciones', $update, $where);
				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la organización del catálogo: " . $_POST["nombreOrganizacion"] . " (" . $_POST["tipoOrganizacionHidden"] . " - Abreviatura: " . $_POST["abreviaturaOrganizacion"] . "), ");
				echo "success";
			} else {
				// Ya existe en el catálogo, mostrar aviso
				echo "La organización " . $_POST["nombreOrganizacion"] . " ya existe en el catálogo";
			}
			break;

		case "cargo-persona":
			/*
					POST
					hiddenFormData = editar ^ prsCargoId
					typeOperation
					operation
					cargoPersona
					descripcionCargoPersona
					funcionCargoPersona
					herramientasCargoPersona
				*/
			$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
			// Verificar sino existe en el catalogo
			$queryCargoDuplicado = "
					SELECT 
						prsCargoId
					FROM cat_personas_cargos
					WHERE cargoPersona = ? AND prsCargoId <> ? AND flgDelete = '0'
				";
			$existeDuplicado = $cloud->count($queryCargoDuplicado, [$_POST["cargoPersona"], $arrayHiddenForm[1]]);
			if ($existeDuplicado == 0) {
				$update = [
					'cargoPersona'				=> $_POST["cargoPersona"],
					'descripcionCargoPersona'	=> $_POST["descripcionCargoPersona"],
					'funcionCargoPersona' 		=> $_POST['funcionCargoPersona'],
					'herramientasCargoPersona' 	=> $_POST['herramientasCargoPersona']
				];
				$where = ['prsCargoId' => $arrayHiddenForm[1]];
				$cloud->update('cat_personas_cargos', $update, $where);
				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó el cargo del catálogo: " . $_POST["cargoPersona"] . " (Descripción: " . $_POST["descripcionCargoPersona"] . "), ");
				echo "success";
			} else {
				// Ya existe en el catálogo, mostrar aviso
				echo "El cargo " . $_POST["cargoPersona"] . " ya existe en el catálogo.";
			}
			break;

		case "sucursal-departamento":
			/*
	        		POST:
						hiddenFormData = sucursalId
						typeOperation
						operation
						sucursalDepartamentoId
						departamentoSucursal
						codSucursalDepartamento
	        	*/
			// Verificar sino existe en el catalogo
			$queryDepartamentoDuplicado = "
					SELECT 
						sucursalDepartamentoId
					FROM cat_sucursales_departamentos
					WHERE codSucursalDepartamento = ? AND sucursalId = ? AND sucursalDepartamentoId <> ? AND flgDelete = '0'
				";
			$existeDuplicado = $cloud->count(
				$queryDepartamentoDuplicado,
				[$_POST["codSucursalDepartamento"], $_POST['hiddenFormData'], $_POST['sucursalDepartamentoId']]
			);
			if ($existeDuplicado == 0) {
				$update = [
					'codSucursalDepartamento'	=> $_POST["codSucursalDepartamento"],
					'departamentoSucursal'		=> $_POST["departamentoSucursal"]
				];
				$where = ["sucursalDepartamentoId" => $_POST["sucursalDepartamentoId"]];
				$cloud->update('cat_sucursales_departamentos', $update, $where);

				$dataNombreSucursal = $cloud->row("
				        SELECT
				            sucursal
				        FROM cat_sucursales
				        WHERE sucursalId = ?
				    ", [$_POST['hiddenFormData']]);

				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la información del departamento: " . $_POST["departamentoSucursal"] . " (Código del departamento: " . $_POST["codSucursalDepartamento"] . " - Sucursal: " . $dataNombreSucursal->sucursal . "), ");
				echo "success";
			} else {
				// Ya existe en el catálogo, mostrar aviso
				echo "El departamento " . $_POST["departamentoSucursal"] . " (" . $_POST["codSucursalDepartamento"] . ") ya ha sido creado en esta sucursal.";
			}
			break;

		case 'crud':
			/*
	        	 	POST:
	        	 	hiddenFormData: editar^crudId
	        		typeOperation
					operation
					nombreCrud
					moduloId
					descripcion 
	        	*/
			$formData = explode("^", $_POST['hiddenFormData']);
			$update = [
				'nombreCrud'  => $_POST["nombreCrud"],
				'moduloId'    => $_POST["moduloId"],
				'descripcion' => $_POST["descripcion"]
			];
			$where = ["crudId" => $formData[1]];
			$cloud->update('ejemplo_crud', $update, $where);
			#$cloud->update('cat_sucursales_departamentos', $update, $where);	

			$datModulo = $cloud->row("
        		SELECT 
        			modulo 
        		FROM conf_modulos
        		WHERE moduloId = ?
        	", [$_POST['moduloId']]);

			// Bitácora de usuario final / jefes
			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la información del crud: " . $_POST['nombreCrud'] . " del Módulo: " . $datModulo->modulo);
			echo "success";
			break;

		case 'tipo-producto':
			/*
				 	POST:
					hiddenFormData: update
					typeOperation
					operation
				*/
			$queryExist = "
	        		SELECT 
	        			nombreTipoProducto
	        		FROM cat_inventario_tipos_producto
	        		WHERE nombreTipoProducto = ? AND tipoProductoId != ? AND flgDelete = 0
	        	";
			$existe = $cloud->count($queryExist, [$_POST["nombreTipoProducto"], $_POST["tipoProductoId"]]);
			if ($existe == 0) {
				$update = [
					'nombreTipoProducto' => $_POST['nombreTipoProducto']
				];
				$where = ['tipoProductoId' => $_POST['tipoProductoId']];

				$cloud->update('cat_inventario_tipos_producto', $update, $where);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó el Tipo de producto: " . $_POST["nombreTipoProducto"] . ", ");

				echo "success";
			} else {
				echo "El tipo de producto: " . $_POST["nombreTipoProducto"] . " ya existe en el catálogo.";
			}
			break;

		case 'bodega-sucursal':
			/*
				 	POST:
					hiddenFormData: update
					typeOperation
					operation
				*/
			$queryExist = "
	        		SELECT 
	        			bodegaSucursal
	        		FROM cat_sucursales_bodegas
	        		WHERE codSucursalBodega = ? AND bodegaSucursal = ? AND bodegaId!= ? AND flgDelete = 0
	        	";
			$existe = $cloud->count($queryExist, [$_POST["codSucursalBodega"], $_POST["bodegaSucursal"], $_POST["bodegaId"]]);
			if ($existe == 0) {
				$update = [
					'codSucursalBodega' => $_POST['codSucursalBodega'],
					'bodegaSucursal'    => $_POST['bodegaSucursal']
				];

				$where = ['bodegaId' => $_POST['bodegaId']];

				$datSucursal = $cloud->row("
		        		SELECT 
		        			sucursal 
		        		FROM cat_sucursales
		        		WHERE sucursalId = ?
		        	", [$_POST['sucursalId']]);

				$cloud->update('cat_sucursales_bodegas', $update, $where);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la bodega: " . $_POST["bodegaSucursal"] . ", de la sucursal: " . $datSucursal->sucursal);

				echo "success";
			} else {
				echo "La bodega: " . $_POST["bodegaSucursal"] . " ya existe en esta sucursal.";
			}
			break;

		case 'ubicacion-bodega':
			/*
				 	POST:
					hiddenFormData: update
					typeOperation
					operation
					bodegaId
					codigoUbicacion
					nombreUbicacion
				*/
			$queryExist = "
	        		SELECT 
	        			codigoUbicacion,
	        			nombreUbicacion
	        		FROM inv_ubicaciones
	        		WHERE (codigoUbicacion = ? OR nombreUbicacion = ?) AND inventarioUbicacionId != ? AND flgDelete = 0
	        	";
			$existe = $cloud->count($queryExist, [$_POST["codigoUbicacion"], $_POST["nombreUbicacion"], $_POST["inventarioUbicacionId"]]);
			if ($existe == 0) {
				$update = [
					'codigoUbicacion' => $_POST['codigoUbicacion'],
					'nombreUbicacion' => $_POST['nombreUbicacion']
				];
				$where = ['inventarioUbicacionId' => $_POST["inventarioUbicacionId"]];

				$datBodega = $cloud->row("
		        		SELECT 
		        			bodegaSucursal 
		        		FROM cat_sucursales_bodegas
		        		WHERE bodegaId = ?
		        	", [$_POST['bodegaId']]);

				$cloud->update('inv_ubicaciones', $update, $where);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la ubicación: " . $_POST["nombreUbicacion"] . ", de la bodega: " . $datBodega->bodegaSucursal);

				echo "success";
			} else {
				echo "La Ubicación: " . $_POST["codigoUbicacion"] . " - " . $_POST["nombreUbicacion"] . ", ya existe en esta bodega.";
			}
			break;

		case 'pais':
			/*
					POST:
					paisId
					pais
					abreviaturaPais
					iconBandera
					telefonoCodPais
					codigoMH
				*/
			$existePais = $cloud->count("
					SELECT paisId FROM cat_paises
					WHERE abreviaturaPais = ? AND paisId <> ? AND flgDelete = ?
				", [$_POST["abreviaturaPais"], $_POST["paisId"], 0]);

			$existeCodigoMH = $cloud->count("
				SELECT pais FROM cat_paises
				WHERE codigoMH = ? AND paisId <> ? AND flgDelete = ?
			", [$_POST['codigoMH'], $_POST["paisId"], 0]);

			if ($existePais == 0 && $existeCodigoMH == 0) {
				$update = [
					"pais" 					=> $_POST["pais"],
					"abreviaturaPais" 		=> $_POST["abreviaturaPais"],
					"iconBandera" 			=> $_POST["iconBandera"],
					"telefonoCodPais"		=> $_POST["telefonoCodPais"],
					"codigoMH" 				=> $_POST["codigoMH"]
				];
				$where = ["paisId" => $_POST["paisId"]];
				$cloud->update("cat_paises", $update, $where);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la información del país: $_POST[pais]");

				echo "success";
			} else {
				if ($existePais > 0) {
					echo "El País $_POST[pais] ya fue registrado, verifique la información";
				} else {
					echo "El código de Hacienda $_POST[codigoMH] ya fue asignado";
				}
			}
			break;
		case 'departamento':
			/*
				POST:
        		paisDepartamentoId,
				departamentoPais
				*/
			$existeDepartamento = $cloud->count("
					SELECT paisDepartamentoId FROM cat_paises_departamentos
					WHERE departamentoPais = ? AND paisId = ? AND paisDepartamentoId <> ? AND flgDelete = ?
				", [$_POST["departamentoPais"], $_POST["paisId"], $_POST["paisDepartamentoId"], 0]);

			$existeCodigoMH = $cloud->count("
				SELECT departamentoPais FROM cat_paises_departamentos
				WHERE codigoMH = ?  AND paisDepartamentoId <> ? AND flgDelete = ?
			", [$_POST['codigoMH'], $_POST["paisDepartamentoId"], 0]);
			if ($existeDepartamento == 0 && $existeCodigoMH == 0) {
				$update = [
					"paisId"				=> $_POST["paisId"],
					"departamentoPais" 		=> $_POST["departamentoPais"],
					"codigoMH" 				=> $_POST["codigoMH"]
				];
				$where = ["paisDepartamentoId" => $_POST["paisDepartamentoId"]];
				$cloud->update("cat_paises_departamentos", $update, $where);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la información del departamento: $_POST[departamentoPais]");

				echo "success";
			} else {
				if ($existeDepartamento > 0) {
					echo "El Departamento $_POST[departamentoPais] ya fue registrado, verifique la información";
				} else {
					echo "El código de Hacienda $_POST[codigoMH] ya fue asignado";
				}
			}
			break;

		case 'municipio':
			/*
				POST:
        		paisMunicipioId,
				municipioPais
				*/
			$existeMunicipio = $cloud->count("
					SELECT paisMunicipioId FROM cat_paises_municipios
					WHERE municipioPais = ? AND paisDepartamentoId = ? AND paisMunicipioId <> ? AND flgDelete = ?
				", [$_POST["municipioPais"], $_POST["paisDepartamentoId"], $_POST["paisMunicipioId"], 0]);

			$existeCodigoMH = $cloud->count("
				SELECT municipioPais FROM cat_paises_municipios
				WHERE codigoMH = ? AND paisDepartamentoId = ? AND flgDelete = ?
			", [$_POST['codigoMH'], $_POST["paisDepartamentoId"], 0]);

			if ($existeMunicipio == 0 && $existeCodigoMH == 0) {
				$update = [
					"paisDepartamentoId"				=> $_POST["paisDepartamentoId"],
					"municipioPais" 					=> $_POST["municipioPais"],
					"codigoMH" 							=> $_POST["codigoMH"]
				];
				$where = ["paisMunicipioId" => $_POST["paisMunicipioId"]];
				$cloud->update("cat_paises_municipios", $update, $where);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la información del municipio: $_POST[municipioPais]");

				echo "success";
			} else {
				if ($existeMunicipio > 0) {
					echo "El Municipio $_POST[municipioPais] ya fue registrado, verifique la información";
				} else {
					echo "El código de Hacienda $_POST[codigoMH] ya fue asignado";
				}
			}
			break;

		case 'cotizacionesCorrelativo':
			$update = [
				"estadoCorrelativo"		=> 'Inactivo'
			];
			$where = ["correlativoCotizacionId" => $_POST["correlativoCotizacionId"]];
			$cloud->update("fel_correlativo_cotizacion", $update, $where);
			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Se cambio el estado a Inactivo");

			echo "success";
			break;

		case 'exportar-empleados-magic':
			require_once("../../../../libraries/includes/logic/mgc/datos24.php");

			$get_empleados = $cloud->rows("SELECT personaId, nombre1, nombre2, apellido1, apellido2, estadoPersona FROM th_personas");
			foreach ($get_empleados as $empleado) {
				$checkMG = $magic->row("SELECT personaId, nombre1, nombre2, apellido1, apellido2, estadoPersona FROM th_personas WHERE personaId = ?", [$empleado->personaId]);

				if ($checkMG) {
					$update = [
						"nombre1"			=> $empleado->nombre1,
						"nombre2" 			=> $empleado->nombre2,
						"apellido1" 		=> $empleado->apellido1,
						"apellido2" 		=> $empleado->apellido2,
						"estadoPersona" 	=> $empleado->estadoPersona,
					];
					$where = ["personaId" => $empleado->personaId];

					$magic->update("th_personas", $update, $where);
				} else {
					$insert = [
						"nombre1"			=> $empleado->nombre1,
						"nombre2" 			=> $empleado->nombre2,
						"apellido1" 		=> $empleado->apellido1,
						"apellido2" 		=> $empleado->apellido2,
						"estadoPersona" 	=> $empleado->estadoPersona,
					];

					$magic->insert("th_personas", $insert);
				}
			}

			$insertBitacora = [
				"descripcionExportacion" 			=> "th_personas",
				"personaId" 						=> $_SESSION['personaId'],
				"fhExportacion" 					=> date("Y-m-d H:i:s")
			];
			$bitExportacionMagicId = $cloud->insert("bit_exportaciones_magic", $insertBitacora);

			echo "success";
			break;
		/*
			case 'importar-productos-magic':
				require_once("../../../../libraries/includes/logic/mgc/datos24.php");

				$getProducos = $magic->rows("SELECT 
						FechaUltMov,
						FechaPedido,
						Linea,
						Clasificacion,
						Equivalencia,
						CodigoFabricante,
						Descripcion,
						Unidad,
						Tipo,
						PrecioCompra,
						PrecioVenta,
      					Existencia,
						Ubicacion,
						Proveedor,
						Departamento,
						CostoAnterior,
						CostoPromedio,
						CostoFOB,
						Origen,
						ExistenciaInicial,
						FechaUltimaVenta,
						FechaUltimaCompra,
      					FechaCambioPrecio
						FROM Productos");

				foreach($getProducos as $producto){

					$codMagic = $producto->CodigoFabricante;
					$codProducto = ltrim($codMagic, '-');
					$paisOrigen = $producto->Origen;
					$nombreProducto = str_replace("'", " ", $producto->Descripcion);

					$FechaUltMov = DateTime::createFromFormat('d/m/y', $producto->FechaUltMov);
					$FechaPedido = DateTime::createFromFormat('d/m/y', $producto->FechaPedido);
					$FechaUltimaCompra = DateTime::createFromFormat('d/m/y', $producto->FechaUltimaCompra);
					$FechaUltimaVenta = DateTime::createFromFormat('d/m/y', $producto->FechaUltimaVenta);

					$checkProd = $cloud->row("SELECT productoId FROM prod_productos WHERE codMagic = ?", [$producto->CodigoFabricante]);

					if ($checkProd){
						//update

						$update = [
							"costoPromedio"				=> $producto->CostoPromedio,
							"costoUnitarioRetaceo"		=> $producto->CostoFOB,
							"precioVenta"		=> $producto->PrecioVenta
						];

						// fechas
						if($FechaPedido == "1901-01-01") {
							// No insertar
						} else {
							$update += [
								"fechaApertura" 	=> $producto->FechaPedido
							];
						}
						if($producto->FechaUltimaVenta == "1901-01-01") {
							// No insertar
						} else {
							$update += [
								"ultimaVenta" 		=> $producto->FechaUltimaVenta
							];
						}
						if($producto->FechaUltimaCompra == "1901-01-01") {
							// No insertar
						} else {
							$update += [
								"ultimaCompra" 		=> $producto->FechaUltimaCompra
							];
						}
						if($producto->FechaUltMov == "1901-01-01") {
							// No insertar
						} else {
							$update += [
								"ultimoOtroMovimiento" 		=> $producto->FechaUltMov
							];
						}

						$where = ["productoId" => $checkProd->productoId];
						$cloud->update("prod_productos", $update, $where);

						// verificar precio actual
						$checkPrecio = $cloud->row("SELECT productoPrecioId, precioVenta FROM prod_productos_precios WHERE productoId = ? AND estadoPrecio = ?", [$checkProd->productoId, "Activo"]);

						if (!$checkPrecio){
							$insertPrecios = [
								"productoId"		=> $checkProd->productoId,
								"precioVenta"		=> $producto->PrecioVenta,
								"costoUnitarioFOB"	=> $producto->CostoFOB,
								"costoUnitario"		=> $producto->PrecioCompra,
								"costoPromedio"		=> $producto->CostoPromedio,
								"estadoPrecio"		=> "Activo"
							];
	
							$cloud->insert("prod_productos_precios", $insertPrecios);
						} else {
							if ($checkPrecio->precioVenta != $producto->PrecioVenta && $producto->PrecioVenta > 0){
								
								$update = [
									"estadoPrecio"		=> "Inactivo"
								];

								$where = ["productoPrecioId" => $checkPrecio->productoPrecioId];
								$cloud->update("prod_productos_precios", $update, $where);

								$insertPrecios = [
									"productoId"		=> $checkProd->productoId,
									"precioVenta"		=> $producto->PrecioVenta,
									"costoUnitarioFOB"	=> $producto->CostoFOB,
									"costoUnitario"		=> $producto->PrecioCompra,
									"costoPromedio"		=> $producto->CostoPromedio,
									"estadoPrecio"		=> "Activo"
								];
		
								$cloud->insert("prod_productos_precios", $insertPrecios);
							}
						}


					} else {
						//insert

						$insert = [
							"codFabricante" 		=> $codProducto,
							"codInterno" 			=> $codProducto,
							"codMagic" 				=> $codMagic,
							"nombreProducto" 		=> $nombreProducto, // Corregido: añadí la 'd' final
							"costoPromedio" 		=> $producto->CostoPromedio, // Corregido: "promedio" con 'o'
							"costoUnitarioRetaceo" 	=> $producto->CostoFOB,
							"estadoProducto" 		=> "No validado",
							"fechaApertura" 		=> $producto->FechaPedido,
							"ultimaVenta" 			=> $producto->FechaUltimaVenta,
							"ultimaCompra" 			=> $producto->FechaUltimaCompra,
							"ultimoOtroMovimiento" 	=> $producto->FechaUltMov,
							"precioVenta"			=> $producto->PrecioVenta
						];

						$dataMarcaId = $cloud->row("
							SELECT marcaId FROM cat_inventario_marcas
							WHERE abreviaturaMarca = ? AND flgDelete = ?
							LIMIT 1
						", [$producto->Linea, 0]);

						if(is_object($dataMarcaId)) {
							$insert += [
								"marcaId" 				=> $dataMarcaId->marcaId
							];
						} 
						// Reemplazo de nombres mal escritos en magic
						if($paisOrigen == "USA") {
							$paisOrigen = "Estados Unidos";
						} else if($paisOrigen == "ESPAÑA") {
							$paisOrigen = "España";
						} else if($paisOrigen == "BRAZIL" || $paisOrigen == "BRASIL R") {
							$paisOrigen = "Brasil";
						} else if($paisOrigen == "LOCAL" || $paisOrigen == "PLAZA" || $paisOrigen == "COMPRA EN PLA" || $paisOrigen == "COMPRA" || $paisOrigen == "C" || $paisOrigen == "COMPRA PLAZA" || $paisOrigen == "E" || $paisOrigen == "EL SALVADOR C" || $paisOrigen == "COMUNES" || $paisOrigen == "PRIMAC") {
							$paisOrigen = "El Salvador";
						} else if($paisOrigen == "G" || $paisOrigen == "GUATEMALA R") {
							$paisOrigen = "Guatemala";
						} else if($paisOrigen == "A") {
							$paisOrigen = "Alemania";
						} else if($paisOrigen == "AU") {
							$paisOrigen = "Austria";
						} else if($paisOrigen == "COSTARICA") {
							$paisOrigen = "Costa Rica";
						} else if($paisOrigen == "CHINA ZA") {
							$paisOrigen = "China";
						} else {
							// Mantener tal como viene
						}

						$dataPaisOrigen = $cloud->row("
							SELECT paisId FROM cat_paises
							WHERE pais = ? AND flgDelete = ? 
							LIMIT 1
						", [$paisOrigen, 0]);

						if(is_object($dataPaisOrigen)) {
							$insert += [
								"paisIdOrigen" 			=> $dataPaisOrigen->paisId
							];
						} 

						
						

						// var_dump($insert);
						$productoId = $cloud->insert("prod_productos", $insert);


					/* 	//insert de precio de venta
						$insertPrecios = [
							"productoId" => $productoId, // Corregido: "productoId" bien escrito
							"precioVenta" => $producto->PrecioVenta,
							"costoUnitarioFOB" => $producto->CostoFOB,
							"costoUnitario" => $producto->PrecioCompra,
							"costoPromedio" => $producto->CostoPromedio, // Corregido
							"estadoPrecio" => "Activo"
						];

						$cloud->insert("prod_productos_precios", $insert); 
						
					}

				}
				$insertBitacora = [
					"descripcionExportacion" 			=> "prod_productos", 
					"personaId" 						=> $_SESSION['personaId'], 
					"fhExportacion" 					=> date("Y-m-d H:i:s")
				];
				$bitExportacionMagicId = $cloud->insert("bit_exportaciones_magic", $insertBitacora);
				echo "success";
			break;
			*/

		case 'importar-precios-magic':
			require_once("../../../../libraries/includes/logic/mgc/datos24.php");

			$productos = $magic->rows("
					SELECT DISTINCT
						CodigoFabricante,
						PrecioVenta,
						CostoFOB,
						PrecioCompra,
						CostoPromedio
					FROM Productos
				");

			foreach ($productos as $producto) {
				$checkProd = $cloud->row(
					"SELECT productoId 
						FROM prod_productos 
						WHERE codMagic = ?",
					[$producto->CodigoFabricante]
				);

				if ($checkProd) {
					// verificar precio actual 
					$checkPrecio = $cloud->row(
						"SELECT productoPrecioId, precioVenta 
							FROM prod_productos_precios 
							WHERE productoId = ? AND estadoPrecio = ?",
						[$checkProd->productoId, "Activo"]
					);

					if (!$checkPrecio) {
						// no existe precio → insertar
						$insertPrecios = [
							"productoId"        => $checkProd->productoId,
							"precioVenta"       => $producto->PrecioVenta,
							"costoUnitarioFOB"  => $producto->CostoFOB,
							"costoUnitario"     => $producto->PrecioCompra,
							"costoPromedio"     => $producto->CostoPromedio,
							"estadoPrecio"      => "Activo"
						];
						$cloud->insert("prod_productos_precios", $insertPrecios);
					} else {
						// existe precio activo → validar si cambió y es válido
						if ($checkPrecio->precioVenta != $producto->PrecioVenta && $producto->PrecioVenta > 0) {

							// inactivar precio anterior
							$update = [
								"estadoPrecio" => "Inactivo"
							];
							$where = ["productoPrecioId" => $checkPrecio->productoPrecioId];
							$cloud->update("prod_productos_precios", $update, $where);

							// insertar nuevo precio activo
							$insertPrecios = [
								"productoId"        => $checkProd->productoId,
								"precioVenta"       => $producto->PrecioVenta,
								"costoUnitarioFOB"  => $producto->CostoFOB,
								"costoUnitario"     => $producto->PrecioCompra,
								"costoPromedio"     => $producto->CostoPromedio,
								"estadoPrecio"      => "Activo"
							];
							$cloud->insert("prod_productos_precios", $insertPrecios);
						}
					}
				}
			}

			// registrar bitácora
			$cloud->insert("bit_exportaciones_magic", [
				"descripcionExportacion" => "prod_productos_precios",
				"personaId"              => $_SESSION['personaId'],
				"fhExportacion"          => date("Y-m-d H:i:s")
			]);

			echo "success";
			break;



		case 'importar-productos-magic':
			require_once("../../../../libraries/includes/logic/mgc/datos24.php");

			$productos = $magic->rows("
					SELECT DISTINCT
						CodigoFabricante,
						Descripcion,
						Linea,
						Origen,
						FechaPedido,
						FechaUltimaVenta,
						FechaUltimaCompra,
						FechaUltMov,
						CostoFOB,
						CostoPromedio,
						PrecioVenta
					FROM Productos
				");

			foreach ($productos as $producto) {
				$codMagic = $producto->CodigoFabricante;
				$codProducto = ltrim($codMagic, '-');
				$nombreProducto = str_replace("'", " ", $producto->Descripcion);

				$checkProd = $cloud->row("SELECT productoId FROM prod_productos WHERE codMagic = ?", [$codMagic]);

				if ($checkProd) {
					// UPDATE con validaciones de fechas
					$update = [
						"nombreProducto"        => $nombreProducto,
						"costoPromedio"         => $producto->CostoPromedio,
						"costoUnitarioRetaceo"  => $producto->CostoFOB,
						"precioVenta"           => $producto->PrecioVenta
					];

					if ($producto->FechaPedido != "1901-01-01") {
						$update["fechaApertura"] = $producto->FechaPedido;
					}
					if ($producto->FechaUltimaVenta != "1901-01-01") {
						$update["ultimaVenta"] = $producto->FechaUltimaVenta;
					}
					if ($producto->FechaUltimaCompra != "1901-01-01") {
						$update["ultimaCompra"] = $producto->FechaUltimaCompra;
					}
					if ($producto->FechaUltMov != "1901-01-01") {
						$update["ultimoOtroMovimiento"] = $producto->FechaUltMov;
					}

					$cloud->update("prod_productos", $update, ["productoId" => $checkProd->productoId]);
				} else {
					// INSERT con validaciones de fechas
					$insert = [
						"codFabricante"         => $codProducto,
						"codInterno"            => $codProducto,
						"codMagic"              => $codMagic,
						"nombreProducto"        => $nombreProducto,
						"costoPromedio"         => $producto->CostoPromedio,
						"costoUnitarioRetaceo"  => $producto->CostoFOB,
						"estadoProducto"        => "No validado",
						"precioVenta"           => $producto->PrecioVenta
					];

					if ($producto->FechaPedido != "1901-01-01") {
						$insert["fechaApertura"] = $producto->FechaPedido;
					}
					if ($producto->FechaUltimaVenta != "1901-01-01") {
						$insert["ultimaVenta"] = $producto->FechaUltimaVenta;
					}
					if ($producto->FechaUltimaCompra != "1901-01-01") {
						$insert["ultimaCompra"] = $producto->FechaUltimaCompra;
					}
					if ($producto->FechaUltMov != "1901-01-01") {
						$insert["ultimoOtroMovimiento"] = $producto->FechaUltMov;
					}

					// Marca
					$dataMarcaId = $cloud->row("
							SELECT marcaId FROM cat_inventario_marcas
							WHERE abreviaturaMarca = ? AND flgDelete = 0
							LIMIT 1
						", [$producto->Linea]);

					if (is_object($dataMarcaId)) {
						$insert["marcaId"] = $dataMarcaId->marcaId;
					}

					// País de origen (normalización)
					$paisOrigen = $producto->Origen;
					if ($paisOrigen == "USA") $paisOrigen = "Estados Unidos";
					else if ($paisOrigen == "ESPAÑA") $paisOrigen = "España";
					else if ($paisOrigen == "BRAZIL" || $paisOrigen == "BRASIL R") $paisOrigen = "Brasil";
					else if (in_array($paisOrigen, ["LOCAL", "PLAZA", "COMPRA EN PLA", "COMPRA", "C", "COMPRA PLAZA", "E", "EL SALVADOR C", "COMUNES", "PRIMAC"])) $paisOrigen = "El Salvador";
					else if ($paisOrigen == "G" || $paisOrigen == "GUATEMALA R") $paisOrigen = "Guatemala";
					else if ($paisOrigen == "A") $paisOrigen = "Alemania";
					else if ($paisOrigen == "AU") $paisOrigen = "Austria";
					else if ($paisOrigen == "COSTARICA") $paisOrigen = "Costa Rica";
					else if ($paisOrigen == "CHINA ZA") $paisOrigen = "China";

					$dataPaisOrigen = $cloud->row("
							SELECT paisId FROM cat_paises
							WHERE pais = ? AND flgDelete = 0 
							LIMIT 1
						", [$paisOrigen]);

					if (is_object($dataPaisOrigen)) {
						$insert["paisIdOrigen"] = $dataPaisOrigen->paisId;
					}

					$cloud->insert("prod_productos", $insert);
				}
			}

			$cloud->insert("bit_exportaciones_magic", [
				"descripcionExportacion" => "prod_productos",
				"personaId"              => $_SESSION['personaId'],
				"fhExportacion"          => date("Y-m-d H:i:s")
			]);

			echo "success";
			break;
		/*
			case 'importar-existencia-magic':
				require_once("../../../../libraries/includes/logic/mgc/datos24.php");

				$getProducos = $magic->rows("SELECT 
						FechaUltMov,
						FechaPedido,
						Linea,
						Clasificacion,
						Equivalencia,
						CodigoFabricante,
						Descripcion,
						Unidad,
						Tipo,
						PrecioCompra,
						PrecioVenta,
      					Existencia,
						Ubicacion,
						Proveedor,
						Departamento,
						CostoAnterior,
						CostoPromedio,
						Origen,
						ExistenciaInicial,
						FechaUltimaVenta,
						FechaUltimaCompra,
      					FechaCambioPrecio
						FROM Productos");

				foreach($getProducos as $producto){

					$getBodega = $cloud->row("SELECT bodegaId, codSucursalBodega FROM cat_sucursales_bodegas WHERE codSucursalBodega = ? AND flgDelete = 0", [$producto->Departamento]);
					if ($getBodega){
						$getUbicacion = $cloud->row("SELECT inventarioUbicacionId, nombreUbicacion FROM inv_ubicaciones WHERE bodegaId = ? AND flgDelete = 0", [$getBodega->bodegaId]);
						$getProd = $cloud->row("SELECT productoId, codInterno FROM prod_productos WHERE codMagic = ?", [$producto->CodigoFabricante]);

						if ($getUbicacion){
							$checkExistencia = $cloud->row("SELECT ubicacionProductoId FROM inv_ubicaciones_productos WHERE productoId = ? AND inventarioUbicacionId = ?", [$getProd->productoId,$getUbicacion->inventarioUbicacionId]);

							if ($checkExistencia) {
								 $update = [
									'existenciaProducto'	=> $producto->Existencia,
								];
								$where = ['ubicacionProductoId' => $checkExistencia->ubicacionProductoId]; // 
        						$cloud->update('inv_ubicaciones_productos', $update, $where); 
							} else {

								$insert = [
									'inventarioUbicacionId' => $getUbicacion->inventarioUbicacionId,
									'productoId' 			=> $getProd->productoId,
									'existenciaProducto'	=> $producto->Existencia,
								];
						
								$cloud->insert("inv_ubicaciones_productos", $insert);
		
								// insert a kardex inicial
								$obsMovimientoKardex = "Se asignó existencia inicial del producto: $getProd->codInterno a la ubicación: $getUbicacion->nombreUbicacion, cantidad asignada: $producto->ExistenciaInicial";
		
								$arrayInventario = [
									"inventarioUbicacionId" 	=> $getUbicacion->inventarioUbicacionId,
									"productoId" 				=> $getProd->productoId,
									"existenciaMovimiento" 		=> $producto->Existencia
								];
				
								$arrayMovimiento = [
									"tablaMovimiento" 			=> "inv_ubicaciones_productos",
									"tablaMovimientoId" 		=> 0,
									"fechaDocumento" 			=> date("Y-m-d"),
									"costoUnitarioMovimiento" 	=> $producto->CostoPromedio
								];
				
								$kardexId = inicialKardex($cloud, $obsMovimientoKardex, $arrayInventario, $arrayMovimiento, $yearBD);
							}
						}
													
					}
				}

				$insertBitacora = [
					"descripcionExportacion" 			=> "inv_ubicaciones_productos", 
					"personaId" 						=> $_SESSION['personaId'], 
					"fhExportacion" 					=> date("Y-m-d H:i:s")
				];
				$bitExportacionMagicId = $cloud->insert("bit_exportaciones_magic", $insertBitacora);

				echo "success";
			break;
			*/

		case 'importar-existencia-magic':
			require_once("../../../../libraries/includes/logic/mgc/datos24.php");

			$getProducos = $magic->rows("SELECT 
					idUbicacion
					,sucursalEstacion
					,departamento
					,codigo
					,ubicacion
					,existencia
					,costoPromedio
					FROM ubicaciones
					WHERE LEFT(departamento, 1) NOT IN ('C','V')
					");

			foreach ($getProducos as $producto) {
				$ubicacion = str_replace(" ", "",$producto->ubicacion);

				// Buscar bodega
				$getBodega = $cloud->row(
					"SELECT bodegaId, codSucursalBodega 
					FROM cat_sucursales_bodegas 
					WHERE codSucursalBodega = ? AND flgDelete = 0",
					[$producto->departamento]
				);

				if ($getBodega) {
					// Buscar ubicación
					$getUbicacion = $cloud->row("SELECT inventarioUbicacionId 
					FROM inv_ubicaciones 
					WHERE codigoUbicacion = ? AND bodegaId = ? AND flgDelete = ?",
					[$ubicacion,$getBodega->bodegaId,0]);

					// Buscar producto
					$getProd = $cloud->row(
						"SELECT productoId, codInterno 
						FROM prod_productos 
						WHERE codMagic = ?",
						[$producto->codigo]
					);

					if ($getProd && $getUbicacion) {
						// Validar existencia en ubicación
						$checkExistencia = $cloud->row(
							"SELECT ubicacionProductoId 
								FROM inv_ubicaciones_productos 
								WHERE productoId = ? AND inventarioUbicacionId = ?",
							[$getProd->productoId, $getUbicacion->inventarioUbicacionId]
						);

						if ($checkExistencia) {
							// Si ya existe, puedes decidir si actualizas
							
								$update = [
									'existenciaProducto' => $producto->existencia,
								];
								$where = ['ubicacionProductoId' => $checkExistencia->inventarioUbicacionId];
								$cloud->update('inv_ubicaciones_productos', $update, $where);
								
								/* // Insert en Kardex inicial
								$obsMovimientoKardex = "Se asignó existencia inicial del producto: 
										{$getProd->codInterno} a la ubicación: {$checkExistencia->inventarioUbicacionId}, 
										cantidad asignada: {$producto->existencia}";

								$arrayInventario = [
									"inventarioUbicacionId" => $checkExistencia->inventarioUbicacionId,
									"productoId"            => $getProd->productoId,
									"existenciaMovimiento"  => $producto->existencia,
								];

								$arrayMovimiento = [
									"tablaMovimiento"          => "inv_ubicaciones_productos",
									"tablaMovimientoId"        => 0,
									"fechaDocumento"           => date("Y-m-d"),
									"costoUnitarioMovimiento"  => $producto->costoPromedio,
									"claseId"					=> 29
								];

								$kardexId = inicialKardex(
									$cloud,
									$obsMovimientoKardex,
									$arrayInventario,
									$arrayMovimiento,
									$yearBD
								); */
						} else {
							// Insert en inv_ubicaciones_productos
							$insert = [
								'inventarioUbicacionId' => $getUbicacion->inventarioUbicacionId,
								'productoId'            => $getProd->productoId,
								'existenciaProducto'    => $producto->existencia,
							];
							$cloud->insert("inv_ubicaciones_productos", $insert);

							// Insert en Kardex inicial
							/* $obsMovimientoKardex = "Se asignó existencia inicial del producto: 
									{$getProd->codInterno} a la ubicación: {$getUbicacion->inventarioUbicacionId}, 
									cantidad asignada: {$producto->existencia}";

							$arrayInventario = [
								"inventarioUbicacionId" => $getUbicacion->inventarioUbicacionId,
								"productoId"            => $getProd->productoId,
								"existenciaMovimiento"  => $producto->existencia,
							];

							$arrayMovimiento = [
								"tablaMovimiento"          => "inv_ubicaciones_productos",
								"tablaMovimientoId"        => 0,
								"fechaDocumento"           => date("Y-m-d"),
								"costoUnitarioMovimiento"  => $producto->costoPromedio,
								"claseId"					=> 29
							];

							$kardexId = inicialKardex(
								$cloud,
								$obsMovimientoKardex,
								$arrayInventario,
								$arrayMovimiento,
								$yearBD
							); */
						}
					} else if ($getProd){
						// usar ubicacion default
						$getUbicacionDefault = $cloud->row("SELECT inventarioUbicacionId 
						FROM inv_ubicaciones 
						WHERE codigoUbicacion = ? AND bodegaId = ? AND flgDelete = ?",
						['UBI-G',$getBodega->bodegaId,0]);

						if ($getUbicacionDefault){

							$checkExistencia = $cloud->row(
								"SELECT ubicacionProductoId 
									FROM inv_ubicaciones_productos 
									WHERE productoId = ? AND inventarioUbicacionId = ?",
								[$getProd->productoId, $getUbicacionDefault->inventarioUbicacionId]
							);
	
							if ($checkExistencia) {
								// Si ya existe, puedes decidir si actualizas
								
									$update = [
										'existenciaProducto' => $producto->existencia,
									];
									$where = ['ubicacionProductoId' => $checkExistencia->ubicacionProductoId];
									$cloud->update('inv_ubicaciones_productos', $update, $where);

									// Insert en Kardex inicial
								/* $obsMovimientoKardex = "Se asignó existencia inicial del producto: 
									{$getProd->codInterno} a la ubicación: {$getUbicacionDefault->inventarioUbicacionId}, 
									cantidad asignada: {$producto->existencia}";

							$arrayInventario = [
								"inventarioUbicacionId" => $getUbicacionDefault->inventarioUbicacionId,
								"productoId"            => $getProd->productoId,
								"existenciaMovimiento"  => $producto->existencia,
							];

							$arrayMovimiento = [
								"tablaMovimiento"          => "inv_ubicaciones_productos",
								"tablaMovimientoId"        => 0,
								"fechaDocumento"           => date("Y-m-d"),
								"costoUnitarioMovimiento"  => $producto->costoPromedio,
								"claseId"					=> 29
							];

							$kardexId = inicialKardex(
								$cloud,
								$obsMovimientoKardex,
								$arrayInventario,
								$arrayMovimiento,
								$yearBD
							); */
									
							} else {
								// Insert en inv_ubicaciones_productos
								$insert = [
									'inventarioUbicacionId' => $getUbicacionDefault->inventarioUbicacionId,
									'productoId'            => $getProd->productoId,
									'existenciaProducto'    => $producto->existencia,
								];
								$cloud->insert("inv_ubicaciones_productos", $insert);
	
								// Insert en Kardex inicial
							/* $obsMovimientoKardex = "Se asignó existencia inicial del producto: 
								{$getProd->codInterno} a la ubicación: {$getUbicacionDefault->inventarioUbicacionId}, 
								cantidad asignada: {$producto->existencia}";

							$arrayInventario = [
								"inventarioUbicacionId" => $getUbicacionDefault->inventarioUbicacionId,
								"productoId"            => $getProd->productoId,
								"existenciaMovimiento"  => $producto->existencia,
							];

							$arrayMovimiento = [
								"tablaMovimiento"          	=> "inv_ubicaciones_productos",
								"tablaMovimientoId"        	=> 0,
								"fechaDocumento"           	=> date("Y-m-d"),
								"costoUnitarioMovimiento"  	=> $producto->costoPromedio,
								"claseId"					=> 29
							];

							$kardexId = inicialKardex(
								$cloud,
								$obsMovimientoKardex,
								$arrayInventario,
								$arrayMovimiento,
								$yearBD
							); */
							} 
						} else {
							error_log("No se pudo cargar existencia codMagic {$producto->codigo} en bodega {$getBodega->bodegaId} {$producto->departamento}");
						}

					} else {
						// Producto o ubicación no encontrado → log
						error_log("No se pudo asignar producto con codMagic {$producto->codigo} en bodega {$getBodega->bodegaId} {$producto->departamento}");
					}
				} else {
					// Bodega no encontrada → log

					$primerCaracter = substr($producto->departamento, 0, 1);
					if ($primerCaracter === 'C' || $primerCaracter === 'V') {
					} else {
						error_log("No se encontró bodega para codSucursalBodega: {$producto->departamento}");
					}
				}
			}

			// Insertar en bitácora
			$insertBitacora = [
				"descripcionExportacion" => "inv_ubicaciones_productos",
				"personaId"              => $_SESSION['personaId'],
				"fhExportacion"          => date("Y-m-d H:i:s")
			];
			$bitExportacionMagicId = $cloud->insert("bit_exportaciones_magic", $insertBitacora);

			echo "success";
			break;

		case 'importar-ubicaciones-magic':
			require_once("../../../../libraries/includes/logic/mgc/datos24.php");

			$getProductos = $magic->rows("SELECT DISTINCT 
				ubicacion,sucursalEstacion,departamento
			FROM ubicaciones
			WHERE NULLIF(LTRIM(RTRIM(ubicacion)), '') IS NOT NULL");

			foreach ($getProductos as $producto) {
				$ubicacion = str_replace(" ", "",$producto->ubicacion);

				// Buscar bodega
				$getBodega = $cloud->row(
				"SELECT bodegaId, codSucursalBodega,flgBodegaUbicacion
					FROM cat_sucursales_bodegas 
					WHERE codSucursalBodega = ? AND flgDelete = 0",[$producto->departamento]);

				if ($getBodega){
					// Buscar ubicacion
					$getUbi = $cloud->row("SELECT inventarioUbicacionId FROM inv_ubicaciones WHERE codigoUbicacion = ? AND bodegaId = ? AND flgDelete = ?",[$ubicacion,$getBodega->bodegaId,0]);
	
					if ($getUbi){
						// nada :v
					} else {
						
						// Insertar en bitácora
						$insert = [
							"bodegaId"				=> $getBodega->bodegaId,
							"nombreUbicacion"		=> "-",
							"codigoUbicacion" 		=> $ubicacion,
							"ubicacionSuperiorId"   => 0,
							"nivel"          		=> 1,
							"orden"         		=> 1
						];
						$cloud->insert("inv_ubicaciones", $insert);
	
						//habilitar ubicaciones
						$update = [
							"flgBodegaUbicacion"   => "Si",
						];
						$where = [
							"bodegaId" => $getBodega->bodegaId
						];
	
						$cloud->update("cat_sucursales_bodegas", $update, $where);
					}
					
				} else {
					echo $producto->departamento .',';
				}

			}

			
			echo "success";
			break;

		case "importar-saldosPartidasContables-magic":
			require_once("../../../../libraries/includes/logic/mgc/datos24.php");

			// Traer todas las cuentas con saldos
			$getCuentas = $magic->rows("SELECT 
					Cuenta,
					Descripcion,
					SaldoInicial,
					Cargos,
					Abonos,
					SaldoFinal
				FROM CatalogoCuentasMayor");

			$cuentasActualizadas = []; // Para registrar las cuentas actualizadas

			foreach ($getCuentas as $cuenta) {
				// Actualizar los saldos en conta_cuentas_contables según el número de cuenta
				$update = [
					"saldoInicial"   => $cuenta->SaldoInicial,
					"cargoCuenta"    => $cuenta->Cargos,
					"abonoCuenta"    => $cuenta->Abonos,
					"saldoFinal"     => $cuenta->SaldoFinal
				];

				$where = [
					"numeroCuenta" => $cuenta->Cuenta
				];

				$cloud->update("conta_cuentas_contables", $update, $where);

				// Guardamos la cuenta actualizada
				$cuentasActualizadas[] = "'" . $cuenta->Cuenta . "'";
			}

			// Convertir a string para usar en el WHERE NOT IN
			$listaCuentas = implode(",", $cuentasActualizadas);

			// Establecer a cero las cuentas que no fueron actualizadas
			if (!empty($listaCuentas)) {
				$cloud->rows("
						UPDATE conta_cuentas_contables 
						SET 
							saldoInicial = 0,
							cargoCuenta = 0,
							abonoCuenta = 0,
							saldoFinal = 0
						WHERE numeroCuenta NOT IN ($listaCuentas)
					");
			}

			echo "Actualización completa: saldos, cargos y abonos actualizados.";
			break;

		default:
			echo "No se encontró la operación.";
			break;
	}
} else {
	header("Location: /alina-cloud/app/");
}
