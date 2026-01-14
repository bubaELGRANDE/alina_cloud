<?php 
	/*
		DELETE ESPECIFICANDO CAMPOS (condiciones):
	        $delete = ['columnas' => "hola xd"];
	        $cloud->delete('test', $delete);
		DELETE POR ID:
			$cloud->deleteById('tabla', "columnaId", id);
		DELETE MULTIPLE ID:
			$cloud->deleteByIds('tabla', "columnaId", "2, 4, 6, N");
	*/
	if(isset($_SESSION["usuarioId"]) && isset($operation)) {
		switch($operation) {
			case "empleado-estudio":
                /*
					POST:
					typeOperation
					operation
					id,
					nombreCompleto
	        	*/
                $cloud->deleteById('th_personas_educacion', "prsEducacionId ", $_POST["id"]);

                $dataCentroEstudio = $cloud->row("
                	SELECT centroEstudio FROM th_personas_educacion
                	WHERE prsEducacionId = ?
                ", [$_POST["id"]]);

                $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó un estudio del empleado: ".$_POST["nombreCompleto"]." Centro de estudio: ".$dataCentroEstudio->centroEstudio.", ");
                echo "success";
			break;

			case "empleado-experiencia-laboral":
				/*
					POST:
					typeOperation
					operation
					id
					nombreCompleto
				*/
                $cloud->deleteById('th_personas_exp_laboral', "prsExpLaboralId ", $_POST["id"]);

                $dataLugarTrabajo = $cloud->row("
                	SELECT lugarTrabajo FROM th_personas_exp_laboral
                	WHERE prsExpLaboralId = ?
                ", [$_POST["id"]]);

                $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó una experiencia laboral del empleado: ".$_POST["nombreCompleto"]." Lugar de trabajo: ".$dataLugarTrabajo->lugarTrabajo.", ");
                echo "success";
			break;

			case "empleado-habilidad":
				/*
					POST:
					typeOperation
					operation
					id
					tipoHabilidad
					nombreCompleto
				*/
                $cloud->deleteById('th_personas_habilidades', "prsHabilidadId ", $_POST["id"]);
                
                $dataHabilidad = $cloud->row("
                	SELECT habilidadPersona, nivelHabilidad FROM th_personas_habilidades
                	WHERE prsHabilidadId = ?
                ", [$_POST["id"]]);
                $txtHabilidad = $dataHabilidad->habilidadPersona . " (".$dataHabilidad->nivelHabilidad.")";

                $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó una habilidad del empleado: ".$_POST["nombreCompleto"]." Habilidad: ".$txtHabilidad.", ");
                echo "success";
			break;

			case "empleado-enfermedad":
				/*
					POST:
                    typeOperation
                    operation:
                    id
                    nombreCompleto
				*/
                $cloud->deleteById('th_personas_enfermedades', "prsEnfermedadId ", $_POST["id"]);
                
                $dataEnfermedad = $cloud->row("
                	SELECT
                		cpe.tipoEnfermedad AS tipoEnfermedad,
                		cpe.nombreEnfermedad AS nombreEnfermedad
                	FROM th_personas_enfermedades pe
                	JOIN cat_personas_enfermedades cpe ON cpe.catPrsEnfermedadId = pe.catPrsEnfermedadId
                	WHERE pe.prsEnfermedadId = ?
                ", [$_POST["id"]]);

                $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó la ".$dataEnfermedad->tipoEnfermedad ." - ".$dataEnfermedad->nombreEnfermedad.", ");
                echo "success";
			break;

			case "delContactoEmpleado":
                /*
					POST:
					typeOperation
					operation
					idContacto
	        	*/
                //if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(26, $_SESSION["arrayPermisos"])) {
                    $cloud->deleteById('th_personas_contacto', "prsContactoId ", $_POST["idContacto"]);

                    $dataContactos = $cloud->row("
                        SELECT  personaId, contactoPersona, descripcionPrsContacto FROM th_personas_contacto WHERE prsContactoId =?
                    ", [$_POST["idContacto"]]);
                    $dataPersona = $cloud->row("
					SELECT CONCAT(
						IFNULL(apellido1, '-'),
						' ',
						IFNULL(apellido2, '-'),
						', ',
						IFNULL(nombre1, '-'),
						' ',
						IFNULL(nombre2, '-')
					) AS nombreCompleto FROM th_personas WHERE flgDelete = 0 AND personaId =?
                    ", [$dataContactos->personaId]);
                    $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el contacto: ".$dataContactos->descripcionPrsContacto ." (".$dataContactos->contactoPersona.") del empleado: ".$dataPersona->nombreCompleto.", ");
                    echo "success";
                /* } else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y eliminar un contacto contacto: ".$dataContactos->descripcionCSucursal ." (".$dataContactos->contactoSucursal.") de la sucursal: ".$dataSucs->sucursal." (manejo desde consola), ");
        			echo "Acción no válida.";
	        	}	 */
	        break;

			case "delAdjunto":
				/*
					POST
					typeOperation
					operation
					idAdjunto
				*/
				$dataUrlArchivo = $cloud->row("
					SELECT
						urlPrsAdjunto
					FROM th_personas_adjuntos
					WHERE prsAdjuntoId = ?
				", [$_POST["idAdjunto"]]);

				$oldUrl = "../../../../libraries/resources/images/" . $dataUrlArchivo->urlPrsAdjunto;

				// adjuntos-personas/InicialApellido/archivo.extension
				$arrayUrl = explode("/", $dataUrlArchivo->urlPrsAdjunto);

				$newUrl = "../../../../libraries/resources/images/adjuntos-personas/".$arrayUrl[1]."/ (ELIMINADO " . date("d_m_Y H_i_s") . ") " . $arrayUrl[2];

				rename($oldUrl, $newUrl);

				$cloud->deleteById('th_personas_adjuntos', "prsAdjuntoId ", $_POST["idAdjunto"]);

				$dataAdjuntos = $cloud->row("SELECT tipoPrsAdjunto, descripcionPrsAdjunto FROM th_personas_adjuntos WHERE prsAdjuntoId = ?", [$_POST["idAdjunto"]]);
				$dataPersona = $cloud->row("
					SELECT CONCAT(
						IFNULL(apellido1, '-'),
						' ',
						IFNULL(apellido2, '-'),
						', ',
						IFNULL(nombre1, '-'),
						' ',
						IFNULL(nombre2, '-')
					) AS nombreCompleto FROM th_personas WHERE flgDelete = 0 AND personaId =?
				", [$_POST["idPersona"]]);
				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el adjunto: ". $dataAdjuntos->tipoPrsAdjunto ." (".$dataAdjuntos->descripcionPrsAdjunto.") del empleado: ".$dataPersona->nombreCompleto.", ");
				echo "success";
			break;

			case "empleado-licencia":
				/*
					POST:
						typeOperation
						operation
						id
						categoriaLicencia
						nombreCompleto
				*/
                $cloud->deleteById('th_personas_licencias', "prsLicenciaId ", $_POST["id"]);
                
                $dataLicencia = $cloud->row("
                	SELECT tipoLicencia, numLicencia FROM th_personas_licencias
                	WHERE prsLicenciaId = ?
                ", [$_POST["id"]]);
                $txtLicencia = $dataLicencia->tipoLicencia . " (".$dataLicencia->numLicencia.")";

                $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó una licencia del empleado: ".$_POST["nombreCompleto"]." Tipo: ".$txtLicencia.", ");
                echo "success";
			break;

			case "horariosTrabajo":
				/*
					POST:

				*/
				$dataPersona = $cloud->row("
					SELECT CONCAT(
						IFNULL(apellido1, '-'),
						' ',
						IFNULL(apellido2, '-'),
						', ',
						IFNULL(nombre1, '-'),
						' ',
						IFNULL(nombre2, '-')
					) AS nombreCompleto FROM th_personas WHERE flgDelete = 0 AND personaId =?
				", [$_POST["personaId"]]);

				$cloud->deleteById('th_expediente_horarios', "expedienteHorarioId ", $_POST["id"]);
				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó un horario del empleado: ".$dataPersona->nombreCompleto	.", ");
				echo "success";
			break;

			case "capacitaciones-detalle":

				$eliminarCurso = $cloud->count("SELECT expedienteCapacitacionId 
								FROM th_expediente_capacitacion_detalle
								WHERE expedienteCapacitacionId = ? AND flgDelete = ?
							",[$_POST['expedienteCapacitacionId'], 0]);

				if($eliminarCurso == 1){
					$cloud->deleteById('th_expediente_capacitaciones', "expedienteCapacitacionId", $_POST["expedienteCapacitacionId"]);
				}else{
					//todavia hay mas empleados
				}
				$cloud->deleteById('th_expediente_capacitacion_detalle', "expedienteCapacitacionDetalleId", $_POST["expedienteCapacitacionDetalleId"]);
				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Se elimino el empleado:  (".$_POST['nombreCompleto'].") con la capacitación: ".$_POST['descripcionCapacitacion'].", ");
				echo "success";
				
			break;

			case "anularAmonestacion":

				$update = [
					'justificacionAnulada'			=> $_POST["motivo"],
					'estadoAmonestacion'			=> "Anulado",
				];
				$cloud->update('th_expediente_amonestaciones',$update,['expedienteAmonestacionId' => $_POST['amonestacionId']]);

				$persona = $_POST["expedienteId"];
				$select = "
                    SELECT
                    pers.personaId as personaId, 
                    exp.prsExpedienteId as expedienteId,
                    CONCAT(
                        IFNULL(pers.apellido1, '-'),
                        ' ',
                        IFNULL(pers.apellido2, '-'),
                        ', ',
                        IFNULL(pers.nombre1, '-'),
                        ' ',
                        IFNULL(pers.nombre2, '-')
                    ) AS nombreCompleto
                    FROM th_personas pers
                    JOIN th_expediente_personas exp ON pers.personaId = exp.personaId
                    WHERE exp.prsExpedienteId = ?
                ";
				$dataPersonas = $cloud->row($select,[$persona]);

				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Anuló la amonestación (N°: ".$_POST['amonestacionId'].") para el empleado: ".$dataPersonas->nombreCompleto.", ");
	
				echo "success";
			break;

			case "anularVacaciones":

				$diasDisponibles = $cloud->rows("SELECT ctrlVacacionId, diasRestantesVacacion, anio FROM ctrl_persona_vacaciones WHERE personaId = ? ORDER BY anio DESC", [$_POST['personaId']]);
				$numeroDIas = $_POST['numeroDias'];
				$diasRestantes = 0;

				foreach($diasDisponibles as $dias){


					if ($dias->diasRestantesVacacion < 15){
						$totalDias = $dias->diasRestantesVacacion + $numeroDIas;

						if($totalDias < 15){
							$diasRestantes = $totalDias;
							$totalDias = 0;
						} else {
							$numeroDIas = $totalDias - 15;
							$diasRestantes = 15;
							$totalDias = $numeroDIas;
						}

					}
				
					$update = [
						'diasRestantesVacacion'	=> $diasRestantes,
					];
					$where = ['ctrlVacacionId' => $dias->ctrlVacacionId]; 
					$cloud->update('ctrl_persona_vacaciones', $update, $where);
				}
				
				$update = [
					'justificacionAnulada'		=> $_POST["motivo"],
					'estadoSolicitud'			=> "Anulado",
				];
				$cloud->update('th_expedientes_vacaciones',$update,['expedienteVacacionesId' => $_POST['expedienteVacacionesId']]);

				$select = "
				SELECT
				pers.personaId as personaId, 
				exp.prsExpedienteId as expedienteId,
				CONCAT(
					IFNULL(pers.apellido1, '-'),
					' ',
					IFNULL(pers.apellido2, '-'),
					', ',
					IFNULL(pers.nombre1, '-'),
					' ',
					IFNULL(pers.nombre2, '-')
				) AS nombreCompleto
				FROM th_personas pers
				JOIN th_expediente_personas exp ON pers.personaId = exp.personaId
				WHERE exp.personaId = ? AND exp.estadoExpediente = 'Activo'
                ";
				$dataPersonas = $cloud->row($select,[$_POST['personaId']]);
				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Anuló una solicitud de vacaciones (Id: ".$_POST['expedienteVacacionesId'].") para la persona: ".$dataPersonas->nombreCompleto.", ");
				
				echo "success";

			break;

			case "organigrama-rama":
				/* POST
				operation
				id
				typeOperation
				*/
				$existeRamaHija = $cloud->count("SELECT 
					organigramaRamaId,
					organigramaRama,
					ramaSuperiorId
				FROM cat_organigrama_ramas 
				WHERE flgDelete = '0' AND ramaSuperiorId =?", [$_POST["id"]]);

				$numPersonas = $cloud->count("
				SELECT expedienteOrganigramaId FROM th_expediente_organigrama
				WHERE organigramaRamaId = ? AND flgDelete = '0'
				", [$_POST["id"]]);

				if ($existeRamaHija == 0 && $numPersonas == 0){
					$cloud->deleteById('cat_organigrama_ramas', "organigramaRamaId", $_POST["id"]);
					
					$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó una rama: ".$_POST["nombreRama"].", ");
					echo "success";
				} elseif ($existeRamaHija >= 1 && $numPersonas >= 1){
					echo "No es posible eliminar la rama, aún tiene otras ramas y personas asignadas.";
				} elseif ($existeRamaHija >= 1 && $numPersonas >= 0) {
					echo "No es posible eliminar la rama, aún tiene otras ramas asignadas.";
				} elseif ($existeRamaHija >= 0 && $numPersonas >= 1) {
					echo "No es posible eliminar la rama, aún tiene personas asignadas.";
				}

			break;

			case "empleado-organigrama":
				/*
				 	POST
					typeOperation
					operation
					nombreEmpleado
					idExpOrg
				*/
				$cloud->deleteById('th_expediente_organigrama', "expedienteOrganigramaId", $_POST["idExpOrg"]);
				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó a: ".$_POST["nombreEmpleado"]." del área, ");
				echo "success";
			break;

			case "empleado-cuenta-bancaria":
				/*
				 	POST
					typeOperation
					operation
					prsCBancariaId
					nombreEmpleado
				*/
				$cloud->deleteById('th_personas_cbancaria', "prsCBancariaId", $_POST["prsCBancariaId"]);
				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó una cuenta bancaria del empleado: ".$_POST["nombreEmpleado"]." (N° reg: ".$_POST["prsCBancariaId"]."), ");
				echo "success";
			break;

			case "nucleo-familiar-familia":
				/*
				 	POST
					typeOperation
					operation
					prsFamiliaId
					nombreEmpleado
				*/
				$cloud->deleteById('th_personas_familia', "prsFamiliaId", $_POST["prsFamiliaId"]);
				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó una relación familiar del empleado: ".$_POST["nombreEmpleado"]." (N° reg: ".$_POST["prsFamiliaId"]."), ");
				echo "success";
			break;

			case "expediente-jefatura":
				/*
				 	POST
					typeOperation
					operation
					id
					flg
					jefeId
				*/
                $dataNombreEmpleado = $cloud->row("
                    SELECT 
                        CONCAT(
                            IFNULL(per.apellido1, '-'),
                            ' ',
                            IFNULL(per.apellido2, '-'),
                            ', ',
                            IFNULL(per.nombre1, '-'),
                            ' ',
                            IFNULL(per.nombre2, '-')
                        ) AS nombreCompleto
                    FROM th_expediente_personas exp
                    JOIN th_personas per ON per.personaId = exp.personaId
                    WHERE exp.prsExpedienteId = ? AND exp.flgDelete = ?
                    LIMIT 1
                ",[$_POST['id'], 0]);

                $dataNombreEmpleadoJefe = $cloud->row("
                    SELECT 
                        CONCAT(
                            IFNULL(per.apellido1, '-'),
                            ' ',
                            IFNULL(per.apellido2, '-'),
                            ', ',
                            IFNULL(per.nombre1, '-'),
                            ' ',
                            IFNULL(per.nombre2, '-')
                        ) AS nombreCompleto
                    FROM th_expediente_personas exp
                    JOIN th_personas per ON per.personaId = exp.personaId
                    WHERE exp.prsExpedienteId = ? AND exp.flgDelete = ?
                    LIMIT 1
                ",[$_POST['jefeId'], 0]);

				if($_POST['flg'] == "jefe") {
					$cloud->deleteById('th_expediente_jefaturas', "jefeId", $_POST["id"]);

					$txtBitacora = "Eliminó la jefatura del empleado: " . $dataNombreEmpleado->nombreCompleto . " (N° jefe: " . $_POST['id'] . "), ";
				} else {
					// Empleado especifico de un jefe
					$where = [
						"prsExpedienteId" 		=> $_POST['id'],
						"jefeId" 				=> $_POST['jefeId']
					];
					$cloud->delete('th_expediente_jefaturas', $where);
					$txtBitacora = "Eliminó al empleado: " . $dataNombreEmpleado->nombreCompleto . " de la jefatura de: ".$dataNombreEmpleadoJefe->nombreCompleto." (N° empleado: " . $_POST['id'] . ", N° jefe: ".$_POST['jefeId']."), ";
				}

				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") $txtBitacora");
				echo "success";
			break;

			case "vacaciones-periodo":
				/*
					POST:
					ctrlVacacionId
					nombreCompleto
					anio
				*/
				$cloud->deleteById("ctrl_persona_vacaciones", "ctrlVacacionId", $_POST['ctrlVacacionId']);
				$cloud->writeBitacora("movDelete", "($fhActual) Eliminó el periodo de vacación: $_POST[anio] del empleado: $_POST[nombreCompleto]");
				echo "success";
			break;

			default:
				echo "No se encontró la operación.";
			break;
		}
    } else {
    	header("Location: /alina-cloud/app/");
    }
?>