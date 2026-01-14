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
		case "empleado-estudio":
			/*
				POST:
				hiddenFormData = editar ^ prsEducacionId ^ nombreCompleto
				typeOperation
				operation
				centroEstudio
				nivelEstudio
				nombreCarrera
				prsArEstudioId
				paisId
				estadoEstudio
				mesInicio
				anioInicio
				mesFinalizacion
				anioFinalizacion
				actualmente = no se utiliza, solo se muestra al usuario
			*/
			$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
			$nombreCompleto = $arrayHiddenForm[2];
			$mesesAnio = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
			$update = [
				'centroEstudio' => $_POST["centroEstudio"],
				'nivelEstudio' => $_POST["nivelEstudio"],
				'paisId' => $_POST["paisId"],
				'numMesInicio' => $_POST["mesInicio"],
				'mesInicio' => $mesesAnio[$_POST["mesInicio"]],
				'anioInicio' => $_POST["anioInicio"],
				'estadoEstudio' => $_POST["estadoEstudio"],
			];
			if ($_POST["nivelEstudio"] == "Técnico/Profesional" || $_POST["nivelEstudio"] == "Universidad" || $_POST["nivelEstudio"] == "Postgrado" || $_POST["nivelEstudio"] == "Diplomado" || $_POST["nivelEstudio"] == "Curso" || $_POST["nivelEstudio"] == "Curso - INSAFORP") {
				$update += [
					'prsArEstudioId' => $_POST["prsArEstudioId"],
					'nombreCarrera' => $_POST["nombreCarrera"],
				];
			} else { // Set en NULL porque se cambió la información
				$update += [
					'prsArEstudioId' => null,
					'nombreCarrera' => null,
				];
			}
			if ($_POST["estadoEstudio"] == "Finalizado") {
				$update += [
					'numMesFinalizacion' => $_POST["mesFinalizacion"],
					'mesFinalizacion' => $mesesAnio[$_POST["mesFinalizacion"]],
					'anioFinalizacion' => $_POST["anioFinalizacion"],
				];
			} else { // Set en NULL porque se cambió la información
				$update += [
					'numMesFinalizacion' => null,
					'mesFinalizacion' => null,
					'anioFinalizacion' => null,
				];
			}

			$where = [
				'prsEducacionId' => $arrayHiddenForm[1]
			];

			$cloud->update('th_personas_educacion', $update, $where);
			// Bitácora de usuario final / jefes
			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó un estudio del empleado: " . $nombreCompleto . " Centro de estudio: " . $_POST["centroEstudio"] . ", ");

			echo "success";
			break;

		case "cambiar-estadoVacacion":
			$dataEmpleadoExpediente = $cloud->row("
					SELECT
						prsExpedienteId,
						estadoVacacion
					FROM th_expediente_personas
					WHERE prsExpedienteId = ?
				", [$_POST["prsExpedienteId"]]);

			if ($dataEmpleadoExpediente->estadoVacacion == "Activo") {
				$update = [
					'estadoVacacion' => "Inactivo"
				];
				$where = ['prsExpedienteId' => $_POST["prsExpedienteId"]];
				$cloud->update("th_expediente_personas", $update, $where);
				$cloud->writeBitacora("movUpdate", "($fhActual) Se actualizó el estado de la vacación");
			} else {
				$update = [
					'estadoVacacion' => "Activo"
				];
				$where = ['prsExpedienteId' => $_POST["prsExpedienteId"]];
				$cloud->update("th_expediente_personas", $update, $where);
				$cloud->writeBitacora("movUpdate", "($fhActual) Se actualizó el estado de la vacación");
			}

			echo "success";


			break;

		case "cambiar-vacaciones":
			$dataEmpleadoExpediente = $cloud->row("
					SELECT
						prsExpedienteId,
						nombreCompleto,
						tipoVacacion
					FROM view_expedientes
					WHERE personaId = ? AND estadoPersona = ? AND estadoExpediente = ?
				", [$_POST["empleadosVacacion"], "Activo", "Activo"]);

			// Es el tipo de vacación actual, revertir con los if
			$nuevoTipoVacacion = "";
			if ($dataEmpleadoExpediente->tipoVacacion == "Colectivas") {
				$nuevoTipoVacacion = "Individuales";
				// Hacer insert a ctrl_persona_vacacion
				$insert = [
					"personaId" => $_POST["empleadosVacacion"],
					"anio" => date("Y"),
					"diasRestantesVacacion" => $_POST["inputDiasDisponibles"]
				];
				$cloud->insert("ctrl_persona_vacaciones", $insert);
			} else {
				// Son individuales, revertir
				$nuevoTipoVacacion = "Colectivas";
				$where = [
					"personaId" => $_POST["empleadosVacacion"],
					"anio" => date("Y")
				];
				$cloud->delete("ctrl_persona_vacaciones", $where);
			}
			$update = [
				'tipoVacacion' => $nuevoTipoVacacion,
				'estadoVacacion' => 'Activo'
			];
			$where = ['prsExpedienteId' => $dataEmpleadoExpediente->prsExpedienteId];
			$cloud->update("th_expediente_personas", $update, $where);
			$cloud->writeBitacora("movUpdate", "($fhActual) Se actualizó la vacación");
			echo "success";
			break;

		case "capacitaciones":
			$update = [
				"descripcionCapacitacion" => $_POST['capacitacion'],
				"nombreOrganizador" => $_POST['organizador'],
				"tipoFormacion" => $_POST['selectTipoFormacion'],
				"fechaIniCapacitacion" => $_POST['fechaInicio'],
				"fechaFinCapacitacion" => $_POST['fechaFin'],
				"duracionCapacitacion" => $_POST['duracion'],
				"tipoModalidad" => $_POST['selectModalidad'],
				"costoInsaforp" => $_POST['costoInsaforp'],
				"costoalina" => $_POST['costoEmpresa']
			];
			$where = ['expedienteCapacitacionId' => $_POST["expedienteCapacitacionId"]];
			$cloud->update("th_expediente_capacitaciones", $update, $where);
			$cloud->writeBitacora("movUpdate", "($fhActual) Se actualizó la capacitación: $_POST[capacitacion]");
			echo "success";
			break;

		case "adjuntoCapacitacion":

			$imagenNombre = $_FILES['adjunto']['name'];

			$directorioBase = "adjuntos-personas/";

			$dirname = $_POST["personaId"] . "-" . $_POST["carpetaEmpleado"];

			$filename = "../../../../libraries/resources/images/" . $directorioBase . $dirname . "/";

			if (!file_exists($filename)) {
				//chmod("../../../../libraries/resources/images", 0777);
				mkdir("../../../../libraries/resources/images/" . $directorioBase . $dirname, 0755);

			}

			$ubicacion = $filename . "/" . $imagenNombre;
			$flgSubir = 1;
			$imagenFormato = pathinfo($ubicacion, PATHINFO_EXTENSION);


			$formatosPermitidos = array("jpg", "jpeg", "png", "pdf", "doc", "docx", "xls", "xlsx");
			$formatosImgPerfil = array("jpg", "jpeg", "png");

			if (!in_array(strtolower($imagenFormato), $formatosPermitidos)) {
				$flgSubir = 0;
			} else {
				$flgSubir = 1;
			}

			if ($flgSubir == 0) {
				// Validación de formato nuevamente por si se evade la de Javascript
				echo "El archivo seleccionado no coincide con un formato válido. Por favor vuelva a seleccionar un archivo con formato válido.";
			} else {
				// Verificar si existe
				$n = 1;
				$originalNombre = $imagenNombre;
				while ($n > 0) {
					if (file_exists($ubicacion)) {

						$imagenNombre = "(" . $n . ") " . $originalNombre;
						$ubicacion = $filename . "/" . $imagenNombre;
						$n += 1;
					} else {
						// No existe, se mantiene el flujo normal
						$n = 0;
					}
				}

				/* Upload file */
				if (move_uploaded_file($_FILES['adjunto']['tmp_name'], $ubicacion)) {
					$insert = [
						'personaId' => $_POST["personaId"],
						'tipoPrsAdjunto' => "Capacitación interna",
						'descripcionPrsAdjunto' => "Comprobante de capacitación interna",
						'urlPrsAdjunto' => $directorioBase . $dirname . "/" . $imagenNombre
					];
					$adjuntoId = $cloud->insert('th_personas_adjuntos', $insert);

					$update = [
						"prsAdjuntoId" => $adjuntoId
					];
					$where = ['expedienteCapacitacionDetalleId' => $_POST["expedienteCapacitacionDetalleId"]];
					$cloud->update("th_expediente_capacitacion_detalle", $update, $where);
					$cloud->writeBitacora("movUpdate", "($fhActual) Se actualizó la capacitación");
					echo "success";
				}
			}

			break;

		case "contactoEmpleado":
			/*
				POST:
				hiddenFormData
				typeOperation
				operation
				personaId
				idContact
				tipoContacto
				contactoPersona
				visibilidadContacto
				descripcionContacto
				flgContactoEmergencia (sino se ha marcado no viene, si se marca = 1)
				tblContactosEmpleado_length
			*/
			//if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(25, $_SESSION["arrayPermisos"])) {
			$update = [
				'tipoContactoId' => $_POST["tipoContacto"],
				'contactoPersona ' => $_POST["contactoPersona"],
				'visibilidadContacto' => $_POST["visibilidadContacto"],
				'descripcionPrsContacto' => $_POST["descripcionContacto"]
			];

			if (isset($_POST['flgContactoEmergencia'])) {
				$update += [
					'flgContactoEmergencia' => $_POST["flgContactoEmergencia"]
				];
			} else {
				// Setear a 0 por si era de emergencia y se actualizo a que ya no
				$update += [
					'flgContactoEmergencia' => 0
				];
			}

			$where = ['prsContactoId' => $_POST["idContact"], 'personaId' => $_POST["personaId"]];
			$cloud->update('th_personas_contacto', $update, $where);
			$dataContact = $cloud->row("
                        SELECT contactoPersona FROM th_personas_contacto WHERE flgDelete = 0 AND prsContactoId =?
                    ", [$_POST["idContact"]]);
			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó el contacto: " . $_POST["descripcionContacto"] . " de la sucursal: " . $dataContact->contactoPersona . ", ");
			echo "success";
			/* } else {
				// No tiene permisos
				// Bitacora
				$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y modificar el contacto de sucursal: " . $dataSucs->sucursal . ", ");
				echo "Acción no válida.";
			}     */

			break;

		case "nucleo-familiar-empresa":
			/*
				POST:
				hiddenFormData: personaId ^ nombreCompleto
				typeOperation
				operation
				flgOtroPersona1: En caso se haya dado clic en "Otro"
				flgOtroPersona2: En caso se haya dado clic en "Otro"
				nombrePersona: Es el readonly del empleado al que se ingresó
				persona1
				relacionId1
				relacion1
				nombreRelacionPersona1: Input que tomará el valor cuando se dé clic en "Otro"
				persona2
				relacionId2
				relacion2
				nombreRelacionPersona2: Input que tomará el valor cuando se dé clic en "Otro"
			*/

			// Para la relacion1
			// Validar si se dio clic en "Otro" para agregarlo al catálogo
			if ($_POST['flgOtroPersona1'] == 0) {
				// No se dio clic en Otro para la primer relación, usar el POST del select
				$relacion1 = $_POST['relacion1'];
			} else { // Insert en el catálogo
				// Se dio clic en otro, validar si ya existe en el catálogo lo que se digitó
				// Verificar sino existe en el catalogo
				$queryRelacionDuplicada = "
						SELECT 
							catPrsRelacionId
						FROM cat_personas_relacion
						WHERE tipoPrsRelacion = ? AND flgDelete = '0'
					";
				$existeDuplicado = $cloud->count($queryRelacionDuplicada, [$_POST["nombreRelacionPersona1"]]);
				if ($existeDuplicado == 0) {
					$insert = [
						'tipoPrsRelacion' => $_POST["nombreRelacionPersona1"]
					];
					$relacion1 = $cloud->insert('cat_personas_relacion', $insert);
					// Bitácora de usuario final / jefes
					$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva relación (1) al catálogo desde el menú de Relaciones de empleados: " . $_POST["nombreRelacionPersona1"] . ", ");
				} else {
					// Ya existe en el catálogo, traer id
					$dataRelacionId = $cloud->row($queryRelacionDuplicada, [$_POST["nombreRelacionPersona1"]]);
					$relacion1 = $dataRelacionId->catPrsRelacionId;
				}
			}

			// Para la relacion2
			// Validar si se dio clic en "Otro" para agregarlo al catálogo
			if ($_POST['flgOtroPersona2'] == 0) {
				// No se dio clic en Otro para la primer relación, usar el POST del select
				$relacion2 = $_POST['relacion2'];
			} else { // Insert en el catálogo
				// Se dio clic en otro, validar si ya existe en el catálogo lo que se digitó
				// Verificar sino existe en el catalogo
				$queryRelacionDuplicada = "
						SELECT 
							catPrsRelacionId
						FROM cat_personas_relacion
						WHERE tipoPrsRelacion = ? AND flgDelete = '0'
					";
				$existeDuplicado = $cloud->count($queryRelacionDuplicada, [$_POST["nombreRelacionPersona2"]]);
				if ($existeDuplicado == 0) {
					$insert = [
						'tipoPrsRelacion' => $_POST["nombreRelacionPersona2"]
					];
					$relacion2 = $cloud->insert('cat_personas_relacion', $insert);
					// Bitácora de usuario final / jefes
					$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva relación (2) al catálogo desde el menú de Relaciones de empleados: " . $_POST["nombreRelacionPersona2"] . ", ");
				} else {
					// Ya existe en el catálogo, traer id
					$dataRelacionId = $cloud->row($queryRelacionDuplicada, [$_POST["nombreRelacionPersona2"]]);
					$relacion2 = $dataRelacionId->catPrsRelacionId;
				}
			}

			//persona 1
			$update = [
				'personaId1' => $_POST["persona1"],
				'personaId2' => $_POST["persona2"],
				'catPrsRelacionId' => $relacion2,
			];

			$where = ['prsRelacionId' => $_POST["relacionId1"]];
			$cloud->update('th_personas_relacion', $update, $where);

			//persona 2
			$update = [
				'personaId1' => $_POST["persona2"],
				'personaId2' => $_POST["persona1"],
				'catPrsRelacionId' => $relacion1,
			];
			$where = ['prsRelacionId' => $_POST["relacionId2"]];
			$cloud->update('th_personas_relacion', $update, $where);

			//Bitacora

			$dataRelaciones1 = $cloud->row("
				SELECT 
					thpr.prsRelacionId as prsRelacionId,
					thpr.personaId2 as personaId2,
					CONCAT(
						IFNULL(per.apellido1, '-'),
						' ',
						IFNULL(per.apellido2, '-'),
						', ',
						IFNULL(per.nombre1, '-'),
						' ',
						IFNULL(per.nombre2, '-')
					) AS nombreCompleto,
					pr.tipoPrsRelacion as personaRelacion
					FROM ((th_personas_relacion thpr
					JOIN th_personas per ON per.personaId = thpr.personaId2)
					JOIN cat_personas_relacion pr ON pr.catPrsRelacionId = thpr.catPrsRelacionId)
					WHERE thpr.personaId2 = ?
				", [$_POST["persona1"]]);

			$dataRelaciones2 = $cloud->row("
				SELECT 
					thpr.prsRelacionId as prsRelacionId,
					thpr.personaId2 as personaId2,
					CONCAT(
						IFNULL(per.apellido1, '-'),
						' ',
						IFNULL(per.apellido2, '-'),
						', ',
						IFNULL(per.nombre1, '-'),
						' ',
						IFNULL(per.nombre2, '-')
					) AS nombreCompleto,
					pr.tipoPrsRelacion as personaRelacion
					FROM ((th_personas_relacion thpr
					JOIN th_personas per ON per.personaId = thpr.personaId2)
					JOIN cat_personas_relacion pr ON pr.catPrsRelacionId = thpr.catPrsRelacionId)
					WHERE thpr.personaId2 = ?
				", [$_POST["persona2"]]);

			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la relacion de : " . $dataRelaciones1->nombreCompleto . "(" . $dataRelaciones1->personaRelacion . ") y " . $dataRelaciones2->nombreCompleto . " (" . $dataRelaciones2->personaRelacion . "),  ");

			echo "success";
			break;

		case "empleado":
			/*
				POST:
					hiddenFormData
					typeOperation
					operation
					personaId
					flgMunicipio = Para hacer el change + trigger al editar
					nombre1
					nombre2
					nombre3
					apellido1
					apellido2
					apellido3
					fechaNac
					sexo
					estCivil
					tipoSangre
					pais
					docIdentidad
					numIdentidad
					fechaExpiracionIdentidad
					departamentoExpedicion
					paisMunicipioIdExpedicion
					fechaExpedicionIdentidad
					nit
					nombreOrganizacionIdAFP
					nup
					numISSS
					vehiculo = checkbox, sino se marca no viene
					departamentoDUI
					municipioDUI
					direccionDUI
					departamentoActual
					municipioActual
					direccionActual
			*/
			$existePersona = $cloud->count("
               		SELECT personaId FROM th_personas
               		WHERE numIdentidad = ? AND personaId <> ? AND flgDelete = '0'
               	", [$_POST["numIdentidad"], $_POST["personaId"]]);
			if ($existePersona == 0) {
				if (isset($_POST["vehiculo"])) {
					$vehiculo = $_POST["vehiculo"];
				} else {
					$vehiculo = "No";
				}
				$listaVehiculos = "";
				if (isset($_POST["listaVehiculos"])) {
					$vehiculos = $_POST["listaVehiculos"];
					foreach ($vehiculos as $vehiculoItem) {
						$listaVehiculos .= $vehiculoItem . ",";
					}
				}

				// YYYY-mm pasa a mm-YYYY porque ya se tienen reportes con este formato
				$arrayFechaExpiracion = explode("-", $_POST['fechaExpiracionIdentidad']);
				$fechaExpiracion = $arrayFechaExpiracion[1] . "-" . $arrayFechaExpiracion[0];

				$update = [
					'docIdentidad' => $_POST["docIdentidad"],
					'numIdentidad' => $_POST["numIdentidad"],
					'fechaExpiracionIdentidad' => $fechaExpiracion,
					'fechaExpedicionIdentidad' => $_POST['fechaExpedicionIdentidad'],
					'paisMunicipioIdExpedicion' => $_POST['paisMunicipioIdExpedicion'],
					'nit' => $_POST["nit"],
					'nombre1' => $_POST["nombre1"],
					'nombre2' => $_POST["nombre2"],
					'nombre3' => $_POST["nombre3"],
					'apellido1' => $_POST["apellido1"],
					'apellido2' => $_POST["apellido2"],
					'apellido3' => $_POST["apellido3"],
					'fechaNacimiento' => date("Y-m-d", strtotime($_POST["fechaNac"])),
					'sexo' => $_POST["sexo"],
					'estadoCivil' => $_POST["estCivil"],
					'tipoSangre' => $_POST['tipoSangre'],
					'nombreOrganizacionIdAFP' => $_POST["nombreOrganizacionIdAFP"],
					'nup' => $_POST["nup"],
					'nombreOrganizacionIdISSS' => '1', // cat_nombres_organizaciones quemado
					'numISSS' => $_POST["numISSS"],
					'paisId' => $_POST["pais"],
					'paisMunicipioIdDUI' => $_POST["municipioDUI"],
					'zonaResidenciaDUI' => $_POST["direccionDUI"],
					'paisMunicipioIdActual' => $_POST["municipioActual"],
					'zonaResidenciaActual' => $_POST["direccionActual"],
					'vehiculoPropio' => $vehiculo,
					'vehiculosPropios' => $listaVehiculos
				];
				$where = ['personaId' => $_POST["personaId"]];
				$cloud->update('th_personas', $update, $where);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la informacion de : " . $_POST["apellido1"] . " " . $_POST["apellido2"] . ", " . $_POST["nombre1"] . " " . $_POST["nombre2"] . "");
				echo "success";
			} else {
				$dataPersonaDuplicada = $cloud->row("
	               		SELECT 
	               			CONCAT(
				                IFNULL(apellido1, '-'),
				                ' ',
				                IFNULL(apellido2, '-'),
				                ', ',
				                IFNULL(nombre1, '-'),
				                ' ',
				                IFNULL(nombre2, '-')
				            ) AS nombreCompleto,
				            estadoPersona
	               		FROM th_personas
	               		WHERE numIdentidad = ? AND flgDelete = '0'
	               	", [$_POST["numIdentidad"]]);
				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Intentó actualizar un empleado existente: " . $dataPersonaDuplicada->nombreCompleto . ", ");
				echo "El empleado que está intentando actualizar ya fue registrado con el nombre " . $dataPersonaDuplicada->nombreCompleto . " y se encuentra con estado " . $dataPersonaDuplicada->estadoPersona;
			}
			break;

		case "empleado-experiencia-laboral":
			/*
				POST:
				hiddenFormData = editar ^ prsExpLaboralId ^ nombreCompleto
				typeOperation
				operation
				lugarTrabajo
				paisId
				cargoTrabajo
				prsArExperienciaId
				mesInicio
				anioInicio
				mesFinalizacion
				anioFinalizacion
				motivoRetiro
			*/
			$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
			$nombreCompleto = $arrayHiddenForm[2];
			$mesesAnio = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
			$update = [
				'lugarTrabajo' => $_POST["lugarTrabajo"],
				'paisId' => $_POST["paisId"],
				'prsArExperienciaId' => $_POST["prsArExperienciaId"],
				'cargoTrabajo' => $_POST["cargoTrabajo"],
				'numMesInicio' => $_POST["mesInicio"],
				'mesInicio' => $mesesAnio[$_POST["mesInicio"]],
				'anioInicio' => $_POST["anioInicio"],
				'numMesFinalizacion' => $_POST["mesFinalizacion"],
				'mesFinalizacion' => $mesesAnio[$_POST["mesFinalizacion"]],
				'anioFinalizacion' => $_POST["anioFinalizacion"],
				'motivoRetiro' => $_POST["motivoRetiro"]
			];
			$where = [
				'prsExpLaboralId' => $arrayHiddenForm[1]
			];

			$cloud->update('th_personas_exp_laboral', $update, $where);
			// Bitácora de usuario final / jefes
			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó una experiencia laboral del empleado: " . $nombreCompleto . " Lugar de trabajo: " . $_POST["lugarTrabajo"] . ", ");

			echo "success";
			break;

		case "empleado-habilidad":
			/*
				POST:
				hiddenFormData = editar ^ prsHabilidadId ^ nombreCompleto ^ tipoHabilidad
				typeOperation
				operation
				flgOtro
				personaId
				habilidadPersona
				nombreOtro
				nivelHabilidad
			*/
			$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
			$nombreCompleto = $arrayHiddenForm[2];
			if ($arrayHiddenForm[3] == "idioma") {
				$tipoHabilidad = "Idioma";
				$habilidadPersona = $_POST["habilidadPersona"];
			} else if ($arrayHiddenForm[3] == "informática") {
				$tipoHabilidad = "Informática";
				if ($_POST["flgOtro"] == 0) {
					$habilidadPersona = $_POST["habilidadPersona"];
				} else {
					$habilidadPersona = $_POST["nombreOtro"];
					// Verificar sino existe en el catalogo
					$queryCINFDuplicado = "
							SELECT 
								prsSoftwareId
							FROM cat_personas_software
							WHERE nombreSoftware = ? AND flgDelete = '0'
						";
					$existeCINFDuplicado = $cloud->count($queryCINFDuplicado, [$habilidadPersona]);
					if ($existeCINFDuplicado == 0) {
						// insert catalogo software para que la proxima vez pueda seleccionarlo
						$insert = [
							'nombreSoftware' => $_POST["nombreOtro"]
						];
						$cloud->insert('cat_personas_software', $insert);
						// Bitácora de usuario final / jefes
						$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo programa informático al catálogo: " . $_POST["nombreOtro"] . ", ");
					} else {
						// Ya existe, solo tomar la palabra y guardar la habilidad
					}
				}
			} else if ($arrayHiddenForm[3] == "equipo") {
				$tipoHabilidad = "Equipo";
				if ($_POST["flgOtro"] == 0) {
					$habilidadPersona = $_POST["habilidadPersona"];
				} else {
					$habilidadPersona = $_POST["nombreOtro"];
					// Verificar sino existe en el catalogo
					$queryHerrEquipoDuplicado = "
							SELECT 
								prsHerrEquipoId
							FROM cat_personas_herr_equipos
							WHERE nombreHerrEquipo = ? AND flgDelete = '0'
						";
					$existeEquipoDuplicado = $cloud->count($queryHerrEquipoDuplicado, [$habilidadPersona]);
					if ($existeEquipoDuplicado == 0) {
						// insert catalogo software para que la proxima vez pueda seleccionarlo
						$insert = [
							'nombreHerrEquipo' => $_POST["nombreOtro"]
						];
						$cloud->insert('cat_personas_herr_equipos', $insert);
						// Bitácora de usuario final / jefes
						$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nueva nueva herramienta/equipo al catálogo: " . $_POST["nombreOtro"] . ", ");
					} else {
						// Ya existe, solo tomar la palabra y guardar la habilidad
					}
				}
			} else {
				$tipoHabilidad = "Habilidad";
				$habilidadPersona = $_POST["habilidadPersona"];
			}
			// Verificar sino existe la habilidad
			$queryHabilidadDuplicada = "
					SELECT 
						prsHabilidadId, 
						habilidadPersona,
						nivelHabilidad
					FROM th_personas_habilidades
					WHERE personaId = ? AND tipoHabilidad = ? AND habilidadPersona = ? AND prsHabilidadId <> ? AND flgDelete = '0'
				";
			$existeHabilidad = $cloud->count($queryHabilidadDuplicada, [$_POST["personaId"], $tipoHabilidad, $habilidadPersona, $arrayHiddenForm[1]]);
			if ($existeHabilidad == 0) {
				$update = [
					'tipoHabilidad' => $tipoHabilidad,
					'habilidadPersona' => $habilidadPersona,
					'nivelHabilidad' => $_POST["nivelHabilidad"]
				];
				$where = [
					'prsHabilidadId' => $arrayHiddenForm[1]
				];
				$cloud->update('th_personas_habilidades', $update, $where);

				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó una habilidad del empleado: " . $nombreCompleto . " Tipo de Habilidad: " . $tipoHabilidad . " Habilidad: " . $_POST["habilidadPersona"] . ", ");

				echo "success";
			} else {
				$dataHabilidadExistente = $cloud->row("
						SELECT 
							prsHabilidadId, 
							habilidadPersona,
							nivelHabilidad
						FROM th_personas_habilidades
						WHERE personaId = ? AND tipoHabilidad = ? AND habilidadPersona = ? AND flgDelete = '0'	        			
	        		", [$_POST["personaId"], $tipoHabilidad, $habilidadPersona]);
				echo $dataHabilidadExistente->habilidadPersona . " ya existe con el nivel: " . $dataHabilidadExistente->nivelHabilidad;
			}
			break;

		case "empleado-enfermedad":
			/*
				POST:
				hiddenFormData = editar ^ prsEnfermedadId ^ nombreCompleto
				typeOperation
				operation
				flgOtro
				personaId
				catPrsEnfermedadId
				tipoEnfermedad
				nombreEnfermedad
			*/
			$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
			$nombreCompleto = $arrayHiddenForm[2];

			if ($_POST["flgOtro"] == 0) {
				$catPrsEnfermedadId = $_POST["catPrsEnfermedadId"];
			} else {
				// Verificar sino existe en el catalogo
				$queryEnfermedadDuplicada = "
						SELECT 
							catPrsEnfermedadId
						FROM cat_personas_enfermedades
						WHERE tipoEnfermedad = ? AND nombreEnfermedad = ? AND flgDelete = '0'
					";
				$existeEnfermedadDuplicada = $cloud->count($queryEnfermedadDuplicada, [$_POST["tipoEnfermedad"], $_POST["nombreEnfermedad"]]);
				if ($existeEnfermedadDuplicada == 0) {
					// insert catalogo enfermedades para que la proxima vez pueda seleccionarlo
					$insert = [
						'tipoEnfermedad' => $_POST["tipoEnfermedad"],
						'nombreEnfermedad' => $_POST["nombreEnfermedad"]
					];
					$catPrsEnfermedadId = $cloud->insert('cat_personas_enfermedades', $insert);
					// Bitácora de usuario final / jefes
					$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva enfermedad/alergia al catálogo: " . $_POST["nombreEnfermedad"] . " (" . $_POST["tipoEnfermedad"] . "), ");
				} else {
					// Ya existe en el catálogo, traer el ID sin duplicarla y permitir el insert
					$dataEnfermedadId = $cloud->row("
							SELECT 
								catPrsEnfermedadId
							FROM cat_personas_enfermedades
							WHERE tipoEnfermedad = ? AND nombreEnfermedad = ? AND flgDelete = '0'
						", [$_POST["tipoEnfermedad"], $_POST["nombreEnfermedad"]]);
					$catPrsEnfermedadId = $dataEnfermedadId->catPrsEnfermedadId;
				}
			}

			// Verificar sino existe la enfermedad en la tabla de th_
			$existeEnfermedad = $cloud->count("
					SELECT
						catPrsEnfermedadId
					FROM th_personas_enfermedades
					WHERE personaId = ? AND catPrsEnfermedadId = ? AND prsEnfermedadId <> ? AND flgDelete = '0'
				", [$_POST["personaId"], $catPrsEnfermedadId, $arrayHiddenForm[1]]);
			if ($existeEnfermedad == 0) {
				$update = [
					'personaId' => $_POST["personaId"],
					'catPrsEnfermedadId' => $catPrsEnfermedadId
				];
				$where = ['prsEnfermedadId' => $arrayHiddenForm[1]];
				$cloud->update('th_personas_enfermedades', $update, $where);

				$dataNombreEnfermedad = $cloud->row("
						SELECT
							tipoEnfermedad,
							nombreEnfermedad
						FROM cat_personas_enfermedades
						WHERE catPrsEnfermedadId = ?
					", [$catPrsEnfermedadId]);

				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la información de la enfermedad/alergia del empleado: " . $nombreCompleto . " " . $dataNombreEnfermedad->tipoEnfermedad . ": " . $dataNombreEnfermedad->nombreEnfermedad . ", ");
				echo "success";
			} else {
				$dataEnfermedadExistente = $cloud->row("
						SELECT
							tipoEnfermedad,
							nombreEnfermedad
						FROM cat_personas_enfermedades
						WHERE catPrsEnfermedadId = ?
					", [$catPrsEnfermedadId]);
				echo "La " . $dataEnfermedadExistente->tipoEnfermedad . " - " . $dataEnfermedadExistente->nombreEnfermedad . " ya fue agregada al empleado.";
			}
			break;

		case "adjuntoEmpleado":
			/*
				POST
				hiddenFormData
				hiddenFormData
				operation
				adjuntoId
				personaId
				tipoAdjunto
				descripcionAdjunto
			*/

			$update = [
				'tipoPrsAdjunto' => $_POST["tipoAdjunto"],
				'descripcionPrsAdjunto' => $_POST["descripcionAdjunto"]
			];
			$where = ['prsAdjuntoId' => $_POST["adjuntoId"]];
			$cloud->update('th_personas_adjuntos', $update, $where);

			$dataUser = $cloud->row("SELECT nombre1, apellido1 FROM th_personas WHERE flgDelete = 0 AND personaId = ? ", [$_POST["personaId"]]);
			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la información del archivo: " . $_POST["descripcionAdjunto"] . " para el empleado " . $dataUser->nombre1 . " " . $dataUser->apellido1 . ", ");
			echo "success";

			break;

		case "empleado-licencia":
			/*
				POST:
					hiddenFormData = editar ^ prsLicenciaId ^ nombreCompleto ^ categoriaLicencia
					typeOperation
					operation
					personaId
					tipoLicencia
					numLicenciaDUI
					numLicenciaNIT
					numLicenciaCarnet: 2525363553535
					numLicenciaArma
					fechaExpiracionLicencia
			*/
			$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
			$nombreCompleto = $arrayHiddenForm[2];

			if ($arrayHiddenForm[3] == "conducir") {
				$categoriaLicencia = "Conducir";
				$numLicencia = ($_POST['duiNit'] == 'DUI' ? $_POST['numLicenciaDUI'] : ($_POST['duiNit'] == 'NIT' ? $_POST['numLicenciaNIT'] : $_POST['numLicenciaCarnet']));
				$tipoLicencia = $_POST['tipoLicencia'];
			} else { // arma
				$categoriaLicencia = "Arma";
				$numLicencia = $_POST['numLicenciaArma'];
				$tipoLicencia = "Licencia para el uso de armas de fuego";
			}
			// Validar que no se repita el tipo de licencia
			$existeLicencia = $cloud->count("
					SELECT prsLicenciaId FROM th_personas_licencias
					WHERE personaId = ? AND categoriaLicencia = ? AND tipoLicencia = ? AND prsLicenciaId <> ? AND flgDelete = '0' 
				", [$_POST["personaId"], $categoriaLicencia, $tipoLicencia, $arrayHiddenForm[1]]);

			// Saltarse la validación para el caso de licencias de armas, puede tener más de una del mismo tipo
			if ($existeLicencia == 0 || $categoriaLicencia == "Arma") {
				$update = [
					'categoriaLicencia' => $categoriaLicencia,
					'tipoLicencia' => $tipoLicencia,
					'numLicencia' => $numLicencia,
					'fechaExpiracionLicencia' => $_POST["fechaExpiracionLicencia"]
				];

				if ($categoriaLicencia == "Arma") {
					$update += [
						'descripcionLicencia' => $_POST['descripcionLicencia']
					];
				} else {
					// conducir
				}

				$where = ['prsLicenciaId' => $arrayHiddenForm[1]];
				$cloud->update('th_personas_licencias', $update, $where);

				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la información de la licencia del empleado: " . $nombreCompleto . " Tipo de Licencia: " . $tipoLicencia . " Número de licencia: " . $numLicencia . ", ");
				echo "success";
			} else {
				echo "La " . $tipoLicencia . " ya fue agregada al empleado.";
			}
			break;

		case "expediente-procesar-baja":
			/*
				POST:
					hiddenFormData = prsExpedienteId
					typeOperation
					operation
					personaId
					nombreCompleto
					nombreUsuario = para la carpeta de adjuntos
					fechaBaja
					estadoBaja
					contratable
					justificacionBaja
					flgAdjunto = Si o No para adjuntar archivo
					adjunto = input file con el archivo adjuntado 
			*/
			$estadoExpediente = $_POST['estadoBaja'];
			$arrayFormData = explode('^', $_POST["hiddenFormData"]);

			$prsExpedienteId = $arrayFormData[0];

			// Validar el input hidden si está dando de baja desde expediente
			// O desde persona, si es desde persona hacer un insert de un expediente
			// genérico y cambiar el valor de la variable de arriba

			if (isset($_POST["flgPersona"])) {
				$poseeExpediente = $cloud->count("
					SELECT prsExpedienteId FROM th_expediente_personas
					WHERE personaId = ? AND estadoExpediente = 'Activo' AND flgDelete = '0'
					", [$_POST["personaId"]]);
				if ($poseeExpediente == 0 and !isset($_POST["expedienteId"])) {
					// expediente
					$insert = [
						'personaId' => $_POST["personaId"],
						'prsCargoId' => NULL,
						'sucursalDepartamentoId' => NULL,
						'tipoContrato' => NULL,
						'fechaInicio' => NULL,
						'fechaFinalizacion' => (!isset($_POST["fechaBaja"])) ? NULL : date("Y-m-d", strtotime($_POST["fechaBaja"])),
						'estadoExpediente' => $estadoExpediente,
						'tipoVacacion' => NULL
					];
					$prsExpedienteId = $cloud->insert('th_expediente_personas', $insert);

				} else {
					$prsExpedienteId = $_POST["expedienteId"];
				}
			}

			$fechaBaja = date("Y-m-d", strtotime($_POST["fechaBaja"]));
			$fechaHoy = date("Y-m-d");
			$flgPendiente = 0;

			//comparar fecha de baja con fecha de hoy para determinar si se desctiva perfil+usuario instantaneamente o por cronjob
			if ($fechaBaja > $fechaHoy) {
				$flgPendiente = 1;
			}

			$update = [
				"fechaFinalizacion" => date("Y-m-d", strtotime($_POST["fechaBaja"])),
				"justificacionEstado" => $_POST["justificacionBaja"],
				"estadoExpediente" => $estadoExpediente
			];
			$where = ["prsExpedienteId" => $prsExpedienteId];

			$cloud->update('th_expediente_personas', $update, $where);

			// Bitácora de bajas
			$insert = [
				"prsExpedienteId" => $prsExpedienteId,
				"fechaBaja" => date("Y-m-d", strtotime($_POST["fechaBaja"])),
				"contratable" => $_POST["contratable"],
				"justificacionBaja" => $_POST["justificacionBaja"],
				"estadoBaja" => 'Activo' // Inactivo = Existe re-contratación
			];
			$personaBajaId = $cloud->insert('bit_personas_bajas', $insert);

			// Cambiar estado de la persona

			// $flgPendiente = 1 : no se debe desactivar persona
			if ($flgPendiente == 0) {

				$update = [
					"estadoPersona" => "Inactivo"
				];
				$where = ["personaId" => $_POST["personaId"]];

				$cloud->update('th_personas', $update, $where);
			}

			// Desactivar usuario

			// $flgPendiente = 1 : no se debe desactivar usuario
			if ($flgPendiente == 0) {

				$poseeUsuario = $cloud->count("
						SELECT usuarioId FROM conf_usuarios
						WHERE personaId = ?
					", [$_POST["personaId"]]);

				if ($poseeUsuario > 0) {
					$update = [
						"estadoUsuario" => "Inhabilitado",
						"justificacionEstado" => "Usuario inhabilitado automáticamente: Baja de empleado " . $_POST['fechaBaja']
					];
					$where = ["personaId" => $_POST["personaId"]];

					$cloud->update('conf_usuarios', $update, $where);

					// Validar si el estado de la baja es pendiente (renuncia a futuro), no desactivar todavía, sino eliminar sus permisos.
					// No se pueden duplicar usuarios, pero le agrego LIMIT 1 para prevenir bugs
					$dataUsuario = $cloud->row("
							SELECT 
								usuarioId 
							FROM conf_usuarios
							WHERE personaId = ?
							LIMIT 1
						", [$_POST["personaId"]]);
					// Eliminar sus permisos, en caso de recontratación se le tienen que volver a asignar
					$cloud->deleteById('conf_permisos_usuario', "usuarioId ", $dataUsuario->usuarioId);
				} else {
					// No tenia usuario
				}
			}

			$bitacoraAdjunto = "";
			// Verificar si se adjuntará archivo
			if ($_POST['flgAdjunto'] == "Sí") {
				$imagenNombre = $_FILES['adjunto']['name'];

				$directorioBase = "adjuntos-personas/";
				$flgPrf = 0;

				$dirname = $_POST["personaId"] . "-" . $_POST["nombreUsuario"];

				$filename = "../../../../libraries/resources/images/" . $directorioBase . $dirname . "/";

				if (!file_exists($filename)) {
					//chmod("../../../../libraries/resources/images", 0777);
					mkdir("../../../../libraries/resources/images/" . $directorioBase . $dirname, 0755);
				}

				$ubicacion = $filename . "/" . $imagenNombre;
				$flgSubir = 1;
				$imagenFormato = pathinfo($ubicacion, PATHINFO_EXTENSION);

				$formatosPermitidos = array("jpg", "jpeg", "png", "pdf", "doc", "docx", "xls", "xlsx");

				if (!in_array(strtolower($imagenFormato), $formatosPermitidos)) {
					$flgSubir = 0;
				} else {
					$flgSubir = 1;
				}

				if ($flgSubir == 0) {
					// Validación de formato nuevamente por si se evade la de Javascript
					echo "El archivo seleccionado no coincide con un formato válido. Por favor vuelva a seleccionar un archivo con formato válido.";
				} else {
					// Verificar si existe
					$n = 1;
					$originalNombre = $imagenNombre;
					while ($n > 0) {
						if (file_exists($ubicacion)) {
							$imagenNombre = "(" . $n . ") " . $originalNombre;
							$ubicacion = $filename . "/" . $imagenNombre;
							$n += 1;
						} else {
							// No existe, se mantiene el flujo normal
							$n = 0;
						}
					}

					/* Upload file */
					if (move_uploaded_file($_FILES['adjunto']['tmp_name'], $ubicacion)) {
						$insert = [
							'personaId' => $_POST["personaId"],
							'tipoPrsAdjunto' => 'Baja de empleado',
							'descripcionPrsAdjunto' => 'El archivo fue adjuntado en el formulario de Baja de empleado. Fecha de baja del empleado: ' . $_POST["fechaBaja"],
							'urlPrsAdjunto' => $directorioBase . $dirname . "/" . $imagenNombre
						];
						$cloud->insert('th_personas_adjuntos', $insert);

						//bitacora
						$bitacoraAdjunto = " Agregó un adjunto a la baja del empleado: " . $imagenNombre;
					} else {
						echo "Problema al cargar la imagen. Por favor comuniquese con el departamento de Informática.";
					}
				}
			} else {
				// No se adjuntará archivo
			}

			// Bitácora de usuario final / jefes
			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó el expediente del empleado (dar de baja): " . $_POST["nombreCompleto"] . " Fecha de baja: " . $_POST["fechaBaja"] . " Justificación: " . $_POST["justificacionBaja"] . $bitacoraAdjunto);

			echo "success";
			break;

		case "empleado-recontratacion":
			/*
				POST:
					hiddenFormData
					typeOperation
					operation
					personaId
					flgMunicipio = Para hacer el change + trigger al editar
					nombre1
					nombre2
					nombre3
					apellido1
					apellido2
					apellido3
					fechaNac
					sexo
					estCivil
					pais
					docIdentidad
					numIdentidad
					fechaExpiracionIdentidad
					nit
					nombreOrganizacionIdAFP
					nup
					numISSS
					vehiculo = checkbox, sino se marca no viene
					departamentoDUI
					municipioDUI
					direccionDUI
					departamentoActual
					municipioActual
					direccionActual
					fechaRecontratacion
					justificacionRecontratacion
			*/
			$existePersona = $cloud->count("
               		SELECT personaId FROM th_personas
               		WHERE numIdentidad = ? AND personaId <> ? AND flgDelete = '0'
               	", [$_POST["numIdentidad"], $_POST["personaId"]]);
			if ($existePersona == 0) {
				if (isset($_POST["vehiculo"])) {
					$vehiculo = $_POST["vehiculo"];
				} else {
					$vehiculo = "No";
				}
				$listaVehiculos = "";
				if (isset($_POST["listaVehiculos"])) {
					$vehiculos = $_POST["listaVehiculos"];
					foreach ($vehiculos as $vehiculoItem) {
						$listaVehiculos .= $vehiculoItem . ",";
					}
				}
				$update = [
					'docIdentidad' => $_POST["docIdentidad"],
					'numIdentidad' => $_POST["numIdentidad"],
					'fechaExpiracionIdentidad' => $_POST["fechaExpiracionIdentidad"],
					'nit' => $_POST["nit"],
					'nombre1' => $_POST["nombre1"],
					'nombre2' => $_POST["nombre2"],
					'nombre3' => $_POST["nombre3"],
					'apellido1' => $_POST["apellido1"],
					'apellido2' => $_POST["apellido2"],
					'apellido3' => $_POST["apellido3"],
					'fechaNacimiento' => date("Y-m-d", strtotime($_POST["fechaNac"])),
					'sexo' => $_POST["sexo"],
					'estadoCivil' => $_POST["estCivil"],
					'nombreOrganizacionIdAFP' => $_POST["nombreOrganizacionIdAFP"],
					'nup' => $_POST["nup"],
					'nombreOrganizacionIdISSS' => '1', // cat_nombres_organizaciones quemado
					'numISSS' => $_POST["numISSS"],
					'paisId' => $_POST["pais"],
					'paisMunicipioIdDUI' => $_POST["municipioDUI"],
					'zonaResidenciaDUI' => $_POST["direccionDUI"],
					'paisMunicipioIdActual' => $_POST["municipioActual"],
					'zonaResidenciaActual' => $_POST["direccionActual"],
					'vehiculoPropio' => $vehiculo,
					'vehiculosPropios' => $listaVehiculos,
					'estadoPersona' => "Activo"
				];
				$where = ['personaId' => $_POST["personaId"]];
				$cloud->update('th_personas', $update, $where);

				// Traer su último expediente
				$dataUltimoExpediente = $cloud->row("
		                SELECT
		                    exp.prsExpedienteId AS prsExpedienteId, 
		                    exp.personaId AS personaId, 
		                    exp.prsCargoId AS prsCargoId,
		                    pc.cargoPersona AS cargoPersona,
		                    sc.sucursalId AS sucursalId,
		                    sc.sucursal AS sucursal,
		                    exp.sucursalDepartamentoId AS sucursalDepartamentoId, 
		                    sd.codSucursalDepartamento AS codSucursalDepartamento,
		                    sd.departamentoSucursal AS departamentoSucursal,
		                    exp.tipoContrato AS tipoContrato, 
		                    exp.fechaInicio AS fechaInicio, 
		                    exp.fechaFinalizacion AS fechaFinalizacion, 
		                    exp.justificacionEstado AS justificacionEstado, 
		                    exp.estadoExpediente AS estadoExpediente
		                FROM th_expediente_personas exp
		                LEFT JOIN cat_personas_cargos pc ON pc.prsCargoId = exp.prsCargoId
		                LEFT JOIN cat_sucursales_departamentos sd ON sd.sucursalDepartamentoId = exp.sucursalDepartamentoId
		                LEFT JOIN cat_sucursales sc ON sc.sucursalId = sd.sucursalId
		                WHERE exp.personaId = ? AND exp.flgDelete = '0'
		                ORDER BY prsExpedienteId 
		                LIMIT 1
		            ", [$_POST["personaId"]]);

				// Desactivar su bitácora de baja
				$update = [
					"estadoBaja" => "Inactivo"
				];
				$where = ["prsExpedienteId" => $dataUltimoExpediente->prsExpedienteId];

				$cloud->update('bit_personas_bajas', $update, $where);

				// Insertar a bitácora de recontratación
				$insert = [
					"personaId" => $_POST["personaId"],
					"fechaRecontratacion" => date("Y-m-d", strtotime($_POST["fechaRecontratacion"])),
					"justificacionRecontratacion" => $_POST["justificacionRecontratacion"]
				];
				$personaRecontratacionId = $cloud->insert('bit_personas_recontratacion', $insert);

				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Realizó el proceso de recontratación del empleado: " . $_POST["apellido1"] . " " . $_POST["apellido2"] . ", " . $_POST["nombre1"] . " " . $_POST["nombre2"] . " (Fecha de recontratación: " . $_POST["fechaRecontratacion"] . " Justificación: " . $_POST["justificacionRecontratacion"] . ")");
				echo "success";
			} else {
				$dataPersonaDuplicada = $cloud->row("
	               		SELECT 
	               			CONCAT(
				                IFNULL(apellido1, '-'),
				                ' ',
				                IFNULL(apellido2, '-'),
				                ', ',
				                IFNULL(nombre1, '-'),
				                ' ',
				                IFNULL(nombre2, '-')
				            ) AS nombreCompleto,
				            estadoPersona
	               		FROM th_personas
	               		WHERE numIdentidad = ? AND flgDelete = '0'
	               	", [$_POST["numIdentidad"]]);
				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Intentó actualizar un empleado existente: " . $dataPersonaDuplicada->nombreCompleto . ", ");
				echo "El empleado que está intentando actualizar ya fue registrado con el nombre " . $dataPersonaDuplicada->nombreCompleto . " y se encuentra con estado " . $dataPersonaDuplicada->estadoPersona;
			}
			break;

		case "horariosTrabajo":
			/* 
				POST

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

			$x = 0;
			$diaInicio = $_POST["diaInicio"];
			foreach ($diaInicio as $horario) {

				if (isset($_POST["horarioId"][$x])) {
					$update = [
						'prsExpedienteId' => $_POST["expedienteId"],
						'diaInicio' => $_POST["diaInicio"][$x],
						'diaFin' => $_POST["diaFin"][$x],
						'horaInicio' => $_POST["horaInicio"][$x],
						'horaFin' => $_POST["horaFin"][$x],
						'horasLaborales' => $_POST["totalH"][$x]
					];
					$where = ['expedienteHorarioId' => $_POST["horarioId"][$x]];
					$cloud->update('th_expediente_horarios', $update, $where);

					$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Se actualizaron los horarios del empleado: " . $dataPersona->nombreCompleto . ", ");
				} else {
					$insert = [
						'prsExpedienteId' => $_POST["expedienteId"],
						'diaInicio' => $_POST["diaInicio"][$x],
						'diaFin' => $_POST["diaFin"][$x],
						'horaInicio' => $_POST["horaInicio"][$x],
						'horaFin' => $_POST["horaFin"][$x],
						'horasLaborales' => $_POST["totalH"][$x]
					];

					$cloud->insert('th_expediente_horarios', $insert);

					$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Se actualizarón los horarios del empleado: " . $dataPersona->nombreCompleto . ", ");
				}
				$x++;
			}
			echo "success";
			break;

		case "incapacidad":


			$update = [
				'motivoIncapacidad' => $_POST["motivoIncapacidad"]
			];
			$where = ['expedienteIncapacidadId' => $_POST["expedienteIncapacidadId"]]; // ids, soporta múltiple where
			$cloud->update('th_expediente_incapacidades', $update, $where);


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

			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Se actualizó la información de la incapacidad de: " . $dataPersona->nombreCompleto . ", ");

			echo "success";
			break;

		case 'empleado-ausencia':
			$update = [
				'motivoAusencia' => $_POST["motivoAusencia"]
			];
			$where = ["expedienteAusenciaId" => $_POST["expedienteAusenciaId"]];

			$cloud->update('th_expediente_ausencias', $update, $where);

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
			$dataPersona = $cloud->row($select, [$_POST["expedienteId"]]);

			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Se actualizó la información de la solicitud de ausencia de: " . $dataPersona->nombreCompleto . ", ");

			echo "success";
			break;

		case 'anular-solicitud-ausencia':
			$expedienteAusenciaId = explode('^', $_POST['hiddenFormData']);

			$update = [
				'estadoSolicitudAu' => 'Anulada',
				'motivoAnulacion' => $_POST['motivo']
			];

			/*Cuando se una a la planilla, se debe validar que la planilla no haya sido generada*/
			$cloud->update('th_expediente_ausencias', $update, ['expedienteAusenciaId' => $expedienteAusenciaId[1]]);

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

			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Se anuló la solicitud de ausencia de: " . $dataPersona->nombreCompleto . ", ");

			echo "success";
			break;

		case "amonestacion":
			if (!isset($_POST["suspension"])) {
				$suspension = NULL;
				$fechaSusIni = NULL;
				$fechaSusFin = NULL;
				$totalDias = 0;
			} else {
				$suspension = $_POST["suspension"];
				$fechaSusIni = $_POST["fechaIniSus"];
				$fechaSusFin = $_POST["fechaFinSus"];
				$totalDias = $_POST["totalDias"];
			}
			if ($_POST["amonestacionAnt"] == '') {
				$amonestacionAnterior = NULL;
			} else {
				$amonestacionAnterior = $_POST["amonestacionAnt"];
			}

			$update = [
				'expedienteIdJefe' => $_POST["jefeId"],
				'expedienteId' => $_POST["persona"],
				'fhAmonestacion' => $_POST["fechaAmonestacion"],
				'tipoAmonestacion' => $_POST["tipoSancion"],
				'suspension' => $suspension,
				'totalDiasSuspension' => $totalDias,
				'causaFalta' => $_POST["causaSancion"],
				'descripcionFalta' => $_POST["descripcion"],
				'consecuenciaFalta' => $_POST["consecuencia"],
				'descripcionConsecuencia' => $_POST["descripcionConsecuencia"],
				//'compromisoMejora'			=> $_POST["compromiso"],
				'advertenciaSiguienteFalta' => $_POST["advertencia"],
				'fechaVigenciaInicio' => $_POST["fechaVigenciaIni"],
				'fechaVigenciaFin' => $_POST["fechaVigenciaFin"],
				'fechaSuspensionInicio' => $fechaSusIni,
				'fechaSuspensionFin' => $fechaSusFin,
				'flgReincidencia' => $_POST["reincidencia"],
				'amonestacionAnteriorId' => $amonestacionAnterior,
				'estadoAmonestacion' => "Activo",
			];
			$cloud->update('th_expediente_amonestaciones', $update, ['expedienteAmonestacionId' => $_POST['amonestacionId']]);


			$persona = $_POST["persona"];
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
			$dataPersonas = $cloud->row($select, [$persona]);

			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la amonestación, para la persona: " . $dataPersonas->nombreCompleto . ", motivo: " . $_POST["causaSancion"] . ", ");

			echo "success";

			break;

		case "vacaciones":
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
			$dataPersonas = $cloud->row($select, [$_POST["expId"]]);

			//restar dias disponibles

			if ($_POST["numDias"] == '') {
				$nuevoNumeroDias = 15;
				$numeroDiasInsert = 15;
			} else {
				$nuevoNumeroDias = $_POST["numDias"];
				$numeroDiasInsert = $_POST["numDias"];
			}
			//comparar numero de dias nuevo con actual

			$numeroDIasAnterior = $cloud->row("SELECT numDias FROM th_expedientes_vacaciones WHERE expedienteVacacionesId = ?", [$_POST["expedienteVacaId"]]);

			$totalDiasDisponibles = $cloud->rows("SELECT ctrlVacacionId, diasRestantesVacacion, anio FROM ctrl_persona_vacaciones WHERE personaId = ? ORDER BY anio DESC", [$dataPersonas->personaId]);

			if ($nuevoNumeroDias > $totalDiasDisponibles) {
				echo "El número de días supera los días disponibles para vacaciones.";
			} else {
				$diferencia = abs($nuevoNumeroDias - $numeroDIasAnterior->numDias);

				foreach ($totalDiasDisponibles as $dias) {


					if ($nuevoNumeroDias > $numeroDIasAnterior->numDias) {
						$diasRestantes = $dias->diasRestantesVacacion - $diferencia;
						if ($diasRestantes > 15) {
							$diferencia = $diasRestantes - 15;
						} else {
							$diferencia = 0;
						}
					} else {
						$diasRestantes = $dias->diasRestantesVacacion + $diferencia;
						$diferencia = 0;
					}

					if ($dias->diasRestantesVacacion = 0) {
						$diasRestantes = $diferencia;
						$diferencia = 0;
					}

					$update = [
						'diasRestantesVacacion' => $diasRestantes,
					];
					$where = ['ctrlVacacionId' => $dias->ctrlVacacionId];

					$cloud->update('ctrl_persona_vacaciones', $update, $where);
				}
			}


			$update = [
				'expedienteId' => $_POST["expId"],
				'expedienteJefeId' => $_POST["autorizadoPor"],
				'fhSolicitud' => $_POST["fechaSolicitud"],
				//'flgIngresoSolicitud'	=> $_POST[""],
				'periodoVacaciones' => $_POST["tipoVaca"],
				'numDias' => $numeroDiasInsert,
				'fechaInicio' => $_POST["fechaIni"],
				'fechaFin' => $_POST["fechaFin"],
				'fhaprobacion' => $_POST["fechaApro"],
				'estadoSolicitud' => "Aprobado",
			];
			$where = ['expedienteVacacionesId' => $_POST["expedienteVacaId"]]; // ids, soporta múltiple where

			$cloud->update('th_expedientes_vacaciones', $update, $where);
			echo "success";

			break;

		case "organigrama-rama":
			/*
			hiddenFormData
			typeOperation
			operation
			organigramaRamaId
			nombreRama	
			ramaSuperior
			organigramaRamaDescripcion
			 */

			if (!isset($_POST["ramaSuperior"])) {
				$ramaSuperior = NULL;
			} else {
				$ramaSuperior = $_POST["ramaSuperior"];
			}
			$update = [
				'organigramaRama' => $_POST["nombreRama"],
				'organigramaRamaDescripcion' => $_POST["organigramaRamaDescripcion"],
				'ramaSuperiorId' => $ramaSuperior
			];
			$where = ['organigramaRamaId' => $_POST["organigramaRamaId"]]; // ids, soporta múltiple where
			$cloud->update('cat_organigrama_ramas', $update, $where);

			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó los datos de la rama del organigrama: " . $_POST["nombreRama"] . ", ");

			echo "success";

			break;

		case "empleado-organigrama":
			/*
			hiddenFormData // idRama
			typeOperation
			operation
			idExpOrg
			rama
			 */


			$update = [
				'organigramaRamaId' => $_POST["rama"],
			];
			$where = ['expedienteOrganigramaId' => $_POST["hiddenFormData"]]; // ids, soporta múltiple where
			$cloud->update('th_expediente_organigrama', $update, $where);

			$select = "
                    SELECT
                    organigramaRama
                    FROM cat_organigrama_ramas
                    WHERE organigramaRamaId = ?
                ";
			$dataRama = $cloud->row($select, [$_POST["rama"]]);

			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Cambió perfil al área: " . $dataRama->organigramaRama . ", ");

			echo "success";
			break;
		case "jefe-organigrama":
			/*
			hiddenFormData // idRama
			typeOperation
			operation
			idExpOrg
			rama
			 */

			//eliminar jefe anterior si existe
			if ($_POST["expedienteOrganigrama"] > 0) {
				$update = [
					'flgJefe' => NULL,
				];
				$where = ['expedienteOrganigramaId' => $_POST["expedienteOrganigrama"]];
				$cloud->update('th_expediente_organigrama', $update, $where);
			}

			//nuevo jefe
			$update = [
				'flgJefe' => "Jefe",
			];
			$where = ['prsExpedienteId' => $_POST["personaJefe"], 'flgDelete' => 0]; // ids, soporta múltiple where
			$cloud->update('th_expediente_organigrama', $update, $where);

			$select = "
                    SELECT
                    organigramaRama
                    FROM cat_organigrama_ramas
                    WHERE organigramaRamaId = ?
                ";
			$dataRama = $cloud->row($select, [$_POST["rama"]]);

			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Agrego jefe al área: " . $dataRama->organigramaRama . ", ");

			echo "success";
			break;

		case "empleado-cuenta-bancaria":
			/*
				POST:
				hiddenFormData = personaId ^ nombreCompleto
				typeOperation
				operation
				personaId
				prsCBancariaId
				nombreOrganizacionId
				numeroCuenta
				descripcionCuenta
			*/
			$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
			$nombreCompleto = $arrayHiddenForm[1];

			// Validar que no se repita la cuenta bancaria
			$existeCuenta = $cloud->count("
					SELECT prsCBancariaId FROM th_personas_cbancaria
					WHERE prsCBancariaId <> ? AND personaId = ? AND nombreOrganizacionId = ? AND numeroCuenta = ? AND flgDelete = ?
				", [$_POST['prsCBancariaId'], $_POST["personaId"], $_POST['nombreOrganizacionId'], $_POST['numeroCuenta'], '0']);
			// Saltarse la validación para el caso de licencias de armas, puede tener más de una del mismo tipo
			if ($existeCuenta == 0) {
				// Validar si se va a actualizar a cuenta planillera
				if (isset($_POST['flgCuentaPlanilla'])) {
					// Validar si ya existe una cuenta planillera
					$existeCuentaPlanilla = $cloud->count("
							SELECT prsCBancariaId FROM th_personas_cbancaria
							WHERE prsCBancariaId <> ? AND personaId = ? AND flgCuentaPlanilla = ? AND flgDelete = ?
						", [$_POST['prsCBancariaId'], $_POST["personaId"], '1', '0']);
				} else {
					// No se va a actualizar, no hay necesidad de consulta, setear a 0
					$existeCuentaPlanilla = 0;
				}

				if ($existeCuentaPlanilla == 0) {
					$update = [
						'nombreOrganizacionId' => $_POST['nombreOrganizacionId'],
						'numeroCuenta' => $_POST['numeroCuenta'],
						'descripcionCuenta' => $_POST['descripcionCuenta']
					];

					if (isset($_POST['flgCuentaPlanilla'])) {
						$update += [
							'flgCuentaPlanilla' => $_POST["flgCuentaPlanilla"]
						];
					} else {
						// Setear a cero, en caso fuera planillera y se cambio
						$update += [
							'flgCuentaPlanilla' => 0
						];
					}

					$where = ['prsCBancariaId' => $_POST['prsCBancariaId']];
					$cloud->update('th_personas_cbancaria', $update, $where);

					// Bitácora de usuario final / jefes
					$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la información de la cuenta bancaria del empleado: " . $nombreCompleto . " Número de cuenta: " . $_POST['numeroCuenta'] . ", ");
					echo "success";
				} else {
					echo "Ya se registró la cuenta planillera del empleado";
				}
			} else {
				echo "El número de cuenta del banco seleccionado ya fue agregada al empleado.";
			}
			break;

		case "nucleo-familiar-familia":
			/*
				POST:
				typeOperation
				operation
				personaId
				prsFamiliaId
				nombreEmpleado
				parentesco
				nombreFamiliar
				fechaNacimiento
				flgBeneficiario
				porcentajeBeneficiario
				flgDependeEconomicamente
				flgVivenJuntos
				direccionVivenJuntos
			*/

			$dataPersona = $cloud->row("
					SELECT zonaResidenciaActual FROM th_personas
        			WHERE personaId = ? AND flgDelete = ?
				", [$_POST['personaId'], 0]);

			$dataParentesco = $cloud->row("
					SELECT tipoPrsRelacion FROM cat_personas_relacion
					WHERE catPrsRelacionId = ? AND flgDelete = ?
				", [$_POST['parentesco'], '0']);

			$update = [
				"catPrsRelacionId" => $_POST['parentesco'],
				"nombreFamiliar" => $_POST['nombreFamiliar'],
				"apellidoFamiliar" => $_POST['apellidoFamiliar'],
				"fechaNacimiento" => $_POST["fechaNacimiento"],
				"flgBeneficiario" => $_POST['flgBeneficiario'],
				"flgDependeEconomicamente" => $_POST['flgDependeEconomicamente'],
				"flgVivenJuntos" => $_POST['flgVivenJuntos']
			];

			if ($_POST['flgBeneficiario'] == "Sí") {
				$update += [
					"porcentajeBeneficiario" => $_POST['porcentajeBeneficiario']
				];
			} else {
				// No se agregó beneficiario
			}

			if ($_POST['flgVivenJuntos'] == "Sí") {
				$update += [
					"direccionVivenJuntos" => $dataPersona->zonaResidenciaActual
				];
			} else {
				$update += [
					"direccionVivenJuntos" => $_POST['direccionVivenJuntos']
				];
			}

			$where = ["prsFamiliaId" => $_POST['prsFamiliaId']];
			$cloud->update('th_personas_familia', $update, $where);

			// Bitácora de usuario final / jefes
			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la información de la relación familiar del empleado: " . $_POST['nombreEmpleado'] . " Parentesco: " . $dataParentesco->tipoPrsRelacion . ", ");
			echo "success";
			break;

		case "expediente-jefatura-heredar":
			/*
				POST:
				typeOperation
				operation
				id = expedienteJefaturaId
				flg = Sí/No
			*/
			if ($_POST['flg'] == "No") {
				$update = [
					"flgHeredarPersonal" => "Sí"
				];
			} else {
				// Sí
				$update = [
					"flgHeredarPersonal" => "No"
				];
			}
			$where = ["expedienteJefaturaId" => $_POST['id']];
			$cloud->update("th_expediente_jefaturas", $update, $where);
			echo "success";
			break;

		case "expediente-jefatura-inmediata":
			/*
				POST:
				typeOperation
				operation
				id = expedienteJefaturaId
				flg = Sí/No
			*/
			// Setear No al jefeInmediato (si existe), ya que solo puede existir 1
			$dataJefatura = $cloud->row("
					SELECT prsExpedienteId FROM th_expediente_jefaturas
					WHERE expedienteJefaturaId = ? AND flgDelete = ?
				", [$_POST['id'], 0]);

			$update = [
				"flgJefeInmediato" => "No"
			];
			$where = ["prsExpedienteId" => $dataJefatura->prsExpedienteId];
			$cloud->update("th_expediente_jefaturas", $update, $where);

			if ($_POST['flg'] == "No") {
				$update = [
					"flgJefeInmediato" => "Sí"
				];
			} else {
				// Sí
				$update = [
					"flgJefeInmediato" => "No"
				];
			}
			$where = ["expedienteJefaturaId" => $_POST['id']];
			$cloud->update("th_expediente_jefaturas", $update, $where);
			echo "success";
			break;
		case "empleado-expediente":
			// expediente
			$update = [
				'tipoContrato' => $_POST["tipoContrato"],
				'fechaInicio' => date("Y-m-d", strtotime($_POST["fechaContratacion"])),
				'fechaFinalizacion' => (!isset($_POST["fechaFinalizacion"])) ? NULL : date("Y-m-d", strtotime($_POST["fechaFinalizacion"])),
				'tipoVacacion' => $_POST["tipoVacacion"]
			];
			$where = ["prsExpedienteId" => $_POST["prsExpedienteId"]];
			$prsExpedienteId = $cloud->update('th_expediente_personas', $update, $where);
			echo "success";
			break;
		case "fecha-inicio-labores":
			// expediente provicional edit
			$update = [
				'fechaInicioLabores' => (!isset($_POST["fechaInicioLabores"])) ? NULL : date("Y-m-d", strtotime($_POST["fechaInicioLabores"]))
			];
			$where = ["personaId" => $_POST["personaId"]];
			$prsExpedienteId = $cloud->update('th_personas', $update, $where);
			echo "success";
			break;
		case "expediente-cambio-sucursal":
			/*
				POST:
				prsExpedienteId
				sucursalDepartamentoIdAnterior
				nombreCompleto
				sucursalCambio
				departamentoCambio
			*/
			$update = [
				"sucursalDepartamentoId" => $_POST['departamentoCambio']
			];
			$where = ["prsExpedienteId" => $_POST['prsExpedienteId']];
			$cloud->update("th_expediente_personas", $update, $where);

			$insert = [
				"prsExpedienteId" => $_POST['prsExpedienteId'],
				"sucursalDepartamentoIdAnterior" => $_POST['sucursalDepartamentoIdAnterior'],
				"sucursalDepartamentoIdNuevo" => $_POST['departamentoCambio']
			];
			$bitExpedienteSucursalId = $cloud->insert("bit_expediente_sucursales", $insert);

			$cloud->writeBitacora("movUpdate", "($fhActual) Actualizó la sucursal y departamento del empleado: $_POST[nombreCompleto] (Depto. anterior: $_POST[sucursalDepartamentoIdAnterior] Depto. nuevo: $_POST[departamentoCambio])");

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