<?php 
	/*
        $insert = [
            'campo1'		=> "hola xd",
            'campo2'     => "hola 2222222",
        ];
        $cloud->insert('nombre_tabla', $insert);
	*/
    if(isset($_SESSION["usuarioId"]) && isset($operation)) {
		switch($operation) {
			case "empleado-estudio":
				/*
					POST:
					hiddenFormData = nuevo ^ personaId ^ nombreCompleto 
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
				$personaId = $arrayHiddenForm[1];
				$nombreCompleto = $arrayHiddenForm[2];
				$mesesAnio = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
				$insert = [
					'personaId'			=> $personaId,
					'centroEstudio'		=> $_POST["centroEstudio"],
					'nivelEstudio'		=> $_POST["nivelEstudio"],
					'paisId'			=> $_POST["paisId"],
					'numMesInicio'		=> $_POST["mesInicio"],
					'mesInicio'			=> $mesesAnio[$_POST["mesInicio"]],
					'anioInicio'		=> $_POST["anioInicio"],
					'estadoEstudio'		=> $_POST["estadoEstudio"],
				];
				if($_POST["nivelEstudio"] == "Técnico/Profesional" || $_POST["nivelEstudio"] == "Universidad" || $_POST["nivelEstudio"] == "Postgrado" || $_POST["nivelEstudio"] == "Diplomado" || $_POST["nivelEstudio"] == "Curso" || $_POST["nivelEstudio"] == "Curso - INSAFORP") {
					$insert += [
						'prsArEstudioId'	=> $_POST["prsArEstudioId"],
						'nombreCarrera'		=> $_POST["nombreCarrera"],
					];
				} else {
				}
				if($_POST["estadoEstudio"] == "Finalizado") {
					$insert += [
						'numMesFinalizacion'	=> $_POST["mesFinalizacion"],
						'mesFinalizacion'		=> $mesesAnio[$_POST["mesFinalizacion"]],
						'anioFinalizacion'		=> $_POST["anioFinalizacion"],
					];
				} else {
				}
				
				$cloud->insert('th_personas_educacion', $insert);
		        // Bitácora de usuario final / jefes
		        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo estudio al empleado: " . $nombreCompleto . " Centro de estudio: ".$_POST["centroEstudio"].", ");

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
					vehiculos = lista de vehiculos
                    departamentoDUI
                    municipioDUI
                    direccionDUI
                    departamentoActual
                    municipioActual
                    direccionActual
                */
               	$existePersona = $cloud->count("
               		SELECT personaId FROM th_personas
               		WHERE numIdentidad = ? AND flgDelete = '0'
               	", [$_POST["numIdentidad"]]);
               	if($existePersona == 0) {

                    if (isset($_POST["vehiculo"])){
                        $vehiculo = $_POST["vehiculo"];
                    } else {
                        $vehiculo = "No";
                    }

					$listaVehiculos = "";

					if (isset($_POST["listaVehiculos"])){
						$vehiculos = $_POST["listaVehiculos"];
						foreach ($vehiculos as $vehiculoItem){
							$listaVehiculos .= $vehiculoItem.",";
						}	
					}	

					// YYYY-mm pasa a mm-YYYY porque ya se tienen reportes con este formato
					$arrayFechaExpiracion = explode("-", $_POST['fechaExpiracionIdentidad']);
					$fechaExpiracion = $arrayFechaExpiracion[1] . "-" . $arrayFechaExpiracion[0];

                    $insert = [
                    	'docIdentidad'				=> $_POST["docIdentidad"],
                        'numIdentidad'	        	=> $_POST["numIdentidad"],
                        'fechaExpiracionIdentidad'	=> $fechaExpiracion,
                        'fechaExpedicionIdentidad'	=> $_POST['fechaExpedicionIdentidad'],
                        'paisMunicipioIdExpedicion' => $_POST['paisMunicipioIdExpedicion'],
                        'nit'               		=> $_POST["nit"],
                        'nombre1'           		=> $_POST["nombre1"],
                        'nombre2'           		=> $_POST["nombre2"],
                        'nombre3'           		=> $_POST["nombre3"],
                        'apellido1'         		=> $_POST["apellido1"],
                        'apellido2'         		=> $_POST["apellido2"],
                        'apellido3'         		=> $_POST["apellido3"],
                        'fechaNacimiento'   		=> date("Y-m-d", strtotime($_POST["fechaNac"])),
                        'sexo'              		=> $_POST["sexo"],
                        'estadoCivil'       		=> $_POST["estCivil"],
                        'tipoSangre' 				=> $_POST["tipoSangre"],
                        'nombreOrganizacionIdAFP'	=> $_POST["nombreOrganizacionIdAFP"],
                        'nup'						=> $_POST["nup"],
                        'nombreOrganizacionIdISSS'	=> '1', // cat_nombres_organizaciones quemado
                        'numISSS'					=> $_POST["numISSS"],
                        'paisId'            		=> $_POST["pais"],
                        'paisMunicipioIdDUI'   		=> $_POST["municipioDUI"],
                        'zonaResidenciaDUI'    		=> $_POST["direccionDUI"],
                        'paisMunicipioIdActual' 	=> $_POST["municipioActual"],
                        'zonaResidenciaActual'  	=> $_POST["direccionActual"],
                        'vehiculoPropio'    		=> $vehiculo,
						'vehiculosPropios'			=> $listaVehiculos,
                        'prsTipoId'         		=> "1",
                        'estadoPersona'     		=> "Activo",
                	];
	                $cloud->insert('th_personas', $insert);
	                $nombreCompleto = $_POST["apellido1"] . " " . $_POST["apellido2"] . ", " . $_POST["nombre1"] . " " . $_POST["nombre2"];
	                // Bitácora de usuario final / jefes
        			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo empleado: " . $nombreCompleto . ", ");
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
        			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Intentó ingresar un empleado existente: " . $dataPersonaDuplicada->nombreCompleto . ", ");
                   	echo "El empleado que está intentando agregar ya fue registrado con el nombre " . $dataPersonaDuplicada->nombreCompleto . " y se encuentra con estado " . $dataPersonaDuplicada->estadoPersona;
               	}
            break;

            case "contactoEmpleado":
                /*
                    POST:
                    hiddenFormData
                    typeOperation
                    personaId
                    updatePrsContactoId
                    tipoContacto
                    contactoPersona
                    visibilidadContacto
                    descripcionContacto
                    flgContactoEmergencia (sino se ha marcado no viene, si se marca = 1)
                    tblContactosEmpleado_length
                */
                // Posee permiso
                //if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(24, $_SESSION["arrayPermisos"])) {
                    $insert = [
                        'personaId'					=> $_POST["personaId"],
                        'tipoContactoId'     		=> $_POST["tipoContacto"],
                        'contactoPersona ' 			=> $_POST["contactoPersona"],
                        'descripcionPrsContacto' 	=> $_POST["descripcionContacto"],
                        'visibilidadContacto'		=> $_POST["visibilidadContacto"],
                        'estadoContacto' 			=> "Activo"
                    ];

                    if(isset($_POST['flgContactoEmergencia'])) {
                    	$insert += [
                    		'flgContactoEmergencia' => $_POST["flgContactoEmergencia"]
                    	];
                    } else {
                    	// Tiene default 0 la columna flgContactoEmergencia en la bd
                    }

                    $cloud->insert('th_personas_contacto', $insert);

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
                    $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo contacto para la sucursal: ".$dataPersona->nombreCompleto." (".$_POST["descripcionContacto"].": ".$_POST["contactoPersona"]."), ");
                    echo "success";
                // }else {
                //     // No tiene permisos
                //     // Bitacora
                //     $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados e insertar un contacto de sucursal (manejo desde consola), ");
                //     echo "Acción no válida.";
                // }
	            
	        break;

			case "empleado-experiencia-laboral":
				/*
					POST:
					hiddenFormData = nuevo ^ personaId ^ nombreCompleto
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
				$personaId = $arrayHiddenForm[1];
				$nombreCompleto = $arrayHiddenForm[2];
				$mesesAnio = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
				$insert = [
					'personaId'				=> $personaId,
					'lugarTrabajo'			=> $_POST["lugarTrabajo"],
					'paisId'				=> $_POST["paisId"],
					'prsArExperienciaId'	=> $_POST["prsArExperienciaId"],
					'cargoTrabajo'			=> $_POST["cargoTrabajo"],
					'numMesInicio'			=> $_POST["mesInicio"],
					'mesInicio'				=> $mesesAnio[$_POST["mesInicio"]],
					'anioInicio'			=> $_POST["anioInicio"],
					'numMesFinalizacion'	=> $_POST["mesFinalizacion"],
					'mesFinalizacion'		=> $mesesAnio[$_POST["mesFinalizacion"]],
					'anioFinalizacion'		=> $_POST["anioFinalizacion"],
					'motivoRetiro'			=> $_POST["motivoRetiro"]
				];
				$cloud->insert('th_personas_exp_laboral', $insert);
		        // Bitácora de usuario final / jefes
		        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva experiencia laboral al empleado: " . $nombreCompleto . " Lugar de trabajo: ".$_POST["lugarTrabajo"].", ");

				echo "success";
			break;

			case "empleado-habilidad":
				/*
					POST:
					hiddenFormData = nuevo ^ personaId ^ Valdez García, Samuel Wilfredo ^ idioma
					typeOperation
					operation
					personaId - Lo necesitaba para el update ya que no iba en el hiddenFormData
					flgOtro
					habilidadPersona
					nombreOtro
					nivelHabilidad
				*/
				$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
				$nombreCompleto = $arrayHiddenForm[2];

				if($arrayHiddenForm[3] == "idioma") {
					$tipoHabilidad = "Idioma";
					$habilidadPersona = $_POST["habilidadPersona"];
				} else if($arrayHiddenForm[3] == "informática") {
					$tipoHabilidad = "Informática";
					if($_POST["flgOtro"] == 0) {
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
						if($existeCINFDuplicado == 0) {
							// insert catalogo software para que la proxima vez pueda seleccionarlo
							$insert = [
								'nombreSoftware'		=> $_POST["nombreOtro"]
							];
							$cloud->insert('cat_personas_software', $insert);	
							// Bitácora de usuario final / jefes
			        		$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo conocimiento informático al catálogo: " . $_POST["nombreOtro"] . ", ");	
						} else {
							// Ya existe, solo tomar la palabra y guardar la habilidad
						}				
					}
				} else if($arrayHiddenForm[3] == "equipo") {
					$tipoHabilidad = "Equipo";
					if($_POST["flgOtro"] == 0) {
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
						if($existeEquipoDuplicado == 0) {
							// insert catalogo software para que la proxima vez pueda seleccionarlo
							$insert = [
								'nombreHerrEquipo'		=> $_POST["nombreOtro"]
							];
							$cloud->insert('cat_personas_herr_equipos', $insert);	
							// Bitácora de usuario final / jefes
			        		$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva herramienta/equipo al catálogo: " . $_POST["nombreOtro"] . ", ");	
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
					WHERE personaId = ? AND tipoHabilidad = ? AND habilidadPersona = ? AND flgDelete = '0'
				";
				$existeHabilidad = $cloud->count($queryHabilidadDuplicada, [$arrayHiddenForm[1], $tipoHabilidad, $habilidadPersona]);
				if($existeHabilidad == 0) {
					$insert = [
						'personaId'				=> $arrayHiddenForm[1],
						'tipoHabilidad'			=> $tipoHabilidad,
						'habilidadPersona'		=> $habilidadPersona,
						'nivelHabilidad'		=> $_POST["nivelHabilidad"]
					];
					$cloud->insert('th_personas_habilidades', $insert);
			        // Bitácora de usuario final / jefes
			        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva habilidad al empleado: " . $nombreCompleto . " Tipo de Habilidad: ".$tipoHabilidad." Habilidad: ".$_POST["habilidadPersona"].", ");
		        	echo "success";
				} else {
					$dataHabilidadExistente = $cloud->row($queryHabilidadDuplicada, [$arrayHiddenForm[1], $tipoHabilidad, $habilidadPersona]);
					echo $dataHabilidadExistente->habilidadPersona . " ya existe con el nivel: " . $dataHabilidadExistente->nivelHabilidad;
				}
			break;
			
			case "empleado-enfermedad":
				/*
					POST
					hiddenFormData = nuevo ^ personaId ^ nombreCompleto
					typeOperation
					operation
					flgOtro
					personaId - lo necesitaba acá ya que en el case de update ya no viene el hiddenFormData
					catPrsEnfermedadId
					tipoEnfermedad
					nombreEnfermedad
				*/
				$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
				$nombreCompleto = $arrayHiddenForm[2];

				if($_POST["flgOtro"] == 0) {
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
					if($existeEnfermedadDuplicada == 0) {
						// insert catalogo enfermedades para que la proxima vez pueda seleccionarlo
						$insert = [
							'tipoEnfermedad'		=> $_POST["tipoEnfermedad"],
							'nombreEnfermedad'		=> $_POST["nombreEnfermedad"]
						];
						$catPrsEnfermedadId = $cloud->insert('cat_personas_enfermedades', $insert);	
						// Bitácora de usuario final / jefes
		        		$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva enfermedad/alergia al catálogo: " . $_POST["nombreEnfermedad"] . " (".$_POST["tipoEnfermedad"]."), ");	
					} else {
						// Ya existe en el catálogo, traer el ID sin duplicarla y permitir el insert
						$dataEnfermedadId = $cloud->row("
							SELECT 
								catPrsEnfermedadId
							FROM cat_personas_enfermedades
							WHERE tipoEnfermedad = ? AND nombreEnfermedad = ? AND flgDelete = '0'
						",[$_POST["tipoEnfermedad"], $_POST["nombreEnfermedad"]]);
						$catPrsEnfermedadId = $dataEnfermedadId->catPrsEnfermedadId;
					}
				}

				// Verificar sino existe la enfermedad en la tabla de th_
				$existeEnfermedad = $cloud->count("
					SELECT
						catPrsEnfermedadId
					FROM th_personas_enfermedades
					WHERE personaId = ? AND catPrsEnfermedadId = ? AND flgDelete = '0'
				", [$arrayHiddenForm[1], $catPrsEnfermedadId]);
				if($existeEnfermedad == 0) {
					$insert = [
						'personaId'				=> $arrayHiddenForm[1],
						'catPrsEnfermedadId'	=> $catPrsEnfermedadId
					];
					$cloud->insert('th_personas_enfermedades', $insert);

					$dataNombreEnfermedad = $cloud->row("
						SELECT
							tipoEnfermedad,
							nombreEnfermedad
						FROM cat_personas_enfermedades
						WHERE catPrsEnfermedadId = ?
					", [$catPrsEnfermedadId]);

			        // Bitácora de usuario final / jefes
			        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva enfermedad/alergia al empleado: " . $nombreCompleto . " ".$dataNombreEnfermedad->tipoEnfermedad.": ".$dataNombreEnfermedad->nombreEnfermedad.", ");
		        	echo "success";
				} else {
					$dataEnfermedadExistente = $cloud->row("
						SELECT
							tipoEnfermedad,
							nombreEnfermedad
						FROM cat_personas_enfermedades
						WHERE catPrsEnfermedadId = ?
					", [$catPrsEnfermedadId]);
					echo "La ".$dataEnfermedadExistente->tipoEnfermedad." - " . $dataEnfermedadExistente->nombreEnfermedad . " ya fue agregada al empleado.";
				}
			break;

			case "adjuntoEmpleado":
				/*
					POST
					hiddenFormData
					typeOperation
					operation
					personaId
					user
					tipoAdjunto
					descripcionAdjunto
					adjunto
				*/
				$imagenNombre = $_FILES['adjunto']['name'];
				
				if ($_POST["tipoAdjunto"] == "Foto de empleado"){
					$directorioBase = "mi-perfil/";
					$flgPrf = 1;
				} else {
					$directorioBase = "adjuntos-personas/";
					$flgPrf = 0;
				}

				$dirname = $_POST["personaId"] . "-" . $_POST["user"];
				
				$filename = "../../../../libraries/resources/images/" . $directorioBase . $dirname . "/";
				
				if(!file_exists($filename)) {
					//chmod("../../../../libraries/resources/images", 0777);
					mkdir("../../../../libraries/resources/images/" . $directorioBase . $dirname, 0755);
				} 
				
				$ubicacion = $filename ."/".$imagenNombre; 
				$flgSubir = 1;
				$imagenFormato = pathinfo($ubicacion,PATHINFO_EXTENSION);


				$formatosPermitidos = array("jpg","jpeg","png","pdf");
				$formatosImgPerfil = array("jpg","jpeg","png");

				switch ($flgPrf){
					case 1:
						if(!in_array(strtolower($imagenFormato),$formatosImgPerfil)){
							$flgSubir = 0;
						} else {
							$flgSubir = 1;
						}
					break;
					case 0:
						if(!in_array(strtolower($imagenFormato),$formatosPermitidos)) {
							$flgSubir = 0;
						} else {
							$flgSubir = 1;
							}
					break;
				}

				if($flgSubir == 0) {
					// Validación de formato nuevamente por si se evade la de Javascript
					echo "El archivo seleccionado no coincide con un formato válido. Por favor vuelva a seleccionar un archivo con formato válido.";
				} else {
					// Verificar si existe
					$n = 1;
					$originalNombre = $imagenNombre;
					while($n > 0) {
						if(file_exists($ubicacion)) {

							$imagenNombre = "(" . $n . ")" . $originalNombre;
							$ubicacion = $filename . "/".$imagenNombre;
							$n += 1;
						} else {
							// No existe, se mantiene el flujo normal
							$n = 0;
						}
					}
					
					// check si ya tiene imagen de perfil
					$checkUserImg = $cloud->row("SELECT COUNT(prsAdjuntoId) AS usuarioImg FROM th_personas_adjuntos WHERE personaId = ? AND descripcionPrsAdjunto = 'Actual' AND flgDelete = '0'", [$_POST["personaId"]]);
					if ($flgPrf == 1 and $checkUserImg->usuarioImg == 1 ){ //check validacion
						
						$imgPerfOld = $cloud->row("
						SELECT 
						prsAdjuntoId
						FROM th_personas_adjuntos 
						WHERE personaId = ? AND descripcionPrsAdjunto = 'Actual' AND flgDelete = '0'
						", [$_POST["personaId"]]);
						
						$update = [
							'descripcionPrsAdjunto'		=> "Anterior",
						];
						$where = ['prsAdjuntoId' => $imgPerfOld->prsAdjuntoId];
						$cloud->update('th_personas_adjuntos', $update, $where);
						
					}
					
					//Upload file
					if(move_uploaded_file($_FILES['adjunto']['tmp_name'],$ubicacion)) {
						$insert = [
							'personaId' => $_POST["personaId"],
							'tipoPrsAdjunto' => $_POST["tipoAdjunto"],
							'descripcionPrsAdjunto' => $_POST["descripcionAdjunto"],
							'urlPrsAdjunto' => $directorioBase . $dirname . "/".$imagenNombre
						];
						$adjuntoId = $cloud->insert('th_personas_adjuntos', $insert);


						//update img sidebar
						$checkUser = $cloud->row("SELECT COUNT(usuarioId) AS usuario FROM conf_usuarios WHERE personaId = ?", [$_POST["personaId"]]);

						if ($checkUser->usuario == 1 and $flgPrf == 1){
							$userData = $cloud->row("SELECT usuarioId FROM conf_usuarios WHERE personaId = ?", [$_POST["personaId"]]);
							$update = [
								'custom' => $directorioBase . $dirname . "/".$imagenNombre
							];
							$where = ['usuarioId' => $userData->usuarioId, 'tipoCustom' => "Avatar"];
							$cloud->update('mip_perfil_custom', $update, $where);
						}

						// update amonestacion

						if (isset($_POST["amonestacionId"])){

							$update = [
								'prsAdjuntoId' => $adjuntoId
							];
							$where = ['expedienteAmonestacionId' => $_POST["amonestacionId"]];
							$cloud->update('th_expediente_amonestaciones', $update, $where);
						}

						//bitacora
						$dataUser = $cloud->row("SELECT nombre1, apellido1 FROM th_personas WHERE flgDelete = 0 AND personaId = ? ", [$_POST["personaId"]]);
						$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo archivo: ".$_POST["tipoAdjunto"]." para el empleado " . $dataUser->nombre1 . " " . $dataUser->apellido1 . ", ");
						echo "success";

					} else {
						echo "Problema al cargar la imagen. Por favor comuniquese con el departamento Desarrollo.";
					}
				}
			break;

			case "empleado-licencia":
				/*
					POST:
						hiddenFormData = nuevo ^ personaId ^ nombreCompleto ^ categoriaLicencia
						typeOperation
						operation
						personaId
						tipoLicencia
						numLicenciaDUI
						numLicenciaNIT
						numLicenciaArma
						fechaExpiracionLicencia
				*/
				$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
				$nombreCompleto = $arrayHiddenForm[2];

				if($arrayHiddenForm[3] == "conducir") {
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
					WHERE personaId = ? AND categoriaLicencia = ? AND tipoLicencia = ? AND flgDelete = '0' 
				", [$_POST["personaId"], $categoriaLicencia, $tipoLicencia]);
				// Saltarse la validación para el caso de licencias de armas, puede tener más de una del mismo tipo
				if($existeLicencia == 0 || $categoriaLicencia == "Arma") {
					$insert = [
						'personaId'					=> $_POST["personaId"],
						'categoriaLicencia'			=> $categoriaLicencia,
						'tipoLicencia'				=> $tipoLicencia,
						'numLicencia'				=> $numLicencia,
						'fechaExpiracionLicencia'	=> $_POST["fechaExpiracionLicencia"]
					];

					if($categoriaLicencia == "Arma") {
						$insert += [
							'descripcionLicencia' 	=> $_POST['descripcionLicencia']
						];
					} else {
						// conducir
					}

					$cloud->insert('th_personas_licencias', $insert);

			        // Bitácora de usuario final / jefes
			        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva licencia al empleado: " . $nombreCompleto . " Tipo de Licencia: ".$tipoLicencia." Número de licencia: ".$numLicencia.", ");
		        	echo "success";
				} else {
					echo "La " . $tipoLicencia . " ya fue agregada al empleado.";
				}
			break;

			case "empleado-expediente":
				/*
					POST:
						hiddenFormData
						typeOperation
						operation
						persona
						cargo
						flgInsertCargo = 0: selecciono el "Cargo", 1: insert cargo al catalogo
						cargoPersona = hidden, input text
						descripcionCargoPersona = hidden, textarea
						funcionCargoPersona = hidden, textarea
						sucursal
						departamentoSuc
						tipoContrato
						fechaContratacion
						tipoVacacion
						salario
				*/
			
				//check si hay expediente
				$poseeExpediente = $cloud->count("
					SELECT prsExpedienteId FROM th_expediente_personas
					WHERE personaId = ? AND estadoExpediente = 'Activo' AND flgDelete = '0'
				",[$_POST["persona"]]);
				if($poseeExpediente > 0 AND !isset($_POST["expedienteId"])) {
					echo "Esta persona ya posee un expediente activo";
				} else {
					// Validar el cargo
					if($_POST["flgInsertCargo"] == 0) {
						$prsCargoId = $_POST["cargo"];
					} else { // Insert cargo al catalogo
						// Verificar sino existe en el catalogo
						$queryCargoDuplicado = "
							SELECT 
								prsCargoId
							FROM cat_personas_cargos
							WHERE cargoPersona = ? AND flgDelete = '0'
						";
						$existeDuplicado = $cloud->count($queryCargoDuplicado, [$_POST["cargoPersona"]]);
						if($existeDuplicado == 0) {
							$insert = [
								'cargoPersona'				=> $_POST["cargoPersona"],
								'descripcionCargoPersona'	=> $_POST["descripcionCargoPersona"],
								'funcionCargoPersona' 		=> $_POST['funcionCargoPersona']
							];
							$prsCargoId = $cloud->insert('cat_personas_cargos', $insert);	
							// Bitácora de usuario final / jefes
			        		$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo cargo al catálogo desde el expediente: " . $_POST["cargoPersona"] . " (Descripción: ".$_POST["descripcionCargoPersona"]."), ");
						} else {
							// Ya existe en el catálogo, traer id
							$dataCargoId = $cloud->row($queryCargoDuplicado, [$_POST["cargoPersona"]]);
							$prsCargoId = $dataCargoId->prsCargoId;
						}
					}

					$fechaContratacion = strtotime($_POST['fechaContratacion']);
					$fechaActual = strtotime(date("Y-m-d"));

					$estadoExpediente = ""; $estadoSalario = "";
					switch ($_POST['tipoContrato']) {
						case 'Tiempo indefinido':
							if($fechaContratacion > $fechaActual) { 
								// Es una contratación a futuro, todavía no se tomará en cuenta
								$estadoExpediente = "Pendiente";
								$estadoSalario = "Pendiente";
							} else {
								// La fecha de contratación es menor o igual, el expediente será activo
								$estadoExpediente = "Activo";
								$estadoSalario = "Activo";
							}
						break;
						
						default:
							// Periodo determinado, Servicios profesionales, Periodo de prueba, Interinato
							if($fechaContratacion > $fechaActual) {
								// Es una contratación a futuro, todavía no se tomará en cuenta
								$estadoExpediente = "Pendiente";
								$estadoSalario = "Pendiente";
							} else {
								// Evaluar la fecha de finalización para determinar si ya finalizó el periodo y se está registrando en sistema solo por motivos de historial (para no tener que desactivarlo de forma manual)
								$fechaFinalizacion = strtotime($_POST['fechaFinalizacion']);
								if($fechaFinalizacion >= $fechaActual) {
									// Es una contratación que todavía está en el plazo de tiempo correcto
									$estadoExpediente = "Activo";
									$estadoSalario = "Activo";
								} else {
									// Es menor a la fecha actual, ya su periodo finalizó y se está registrando solo por motivos de historial
									$estadoExpediente = "Finalizado";
									$estadoSalario = "Inactivo";
								}
							}
						break;
					}

					// expediente
					$insert = [
						'personaId'					=> $_POST["persona"],
						'prsCargoId'				=> $prsCargoId,
						'sucursalDepartamentoId'	=> $_POST["departamentoSuc"],
						'tipoContrato'				=> $_POST["tipoContrato"],
						'fechaInicio'				=> date("Y-m-d", strtotime($_POST["fechaContratacion"])),
						'fechaFinalizacion'			=> (!isset($_POST["fechaFinalizacion"])) ? NULL : date("Y-m-d", strtotime($_POST["fechaFinalizacion"])),
						'estadoExpediente'			=> $estadoExpediente,
						'tipoVacacion'				=> $_POST["tipoVacacion"]
					];
					$prsExpedienteId = $cloud->insert('th_expediente_personas', $insert);
	
					// horarios
					$x = 0;

					$diaInicio = $_POST["diaInicio"];
					foreach($diaInicio as $horario){
						$insert = [
							'prsExpedienteId'	=> $prsExpedienteId,
							'diaInicio'			=> $_POST["diaInicio"][$x],
							'diaFin'			=> $_POST["diaFin"][$x],
							'horaInicio'		=> $_POST["horaInicio"][$x],
							'horaFin'			=> $_POST["horaFin"][$x],
							'horasLaborales'	=> $_POST["totalH"][$x]
						];
						$cloud->insert('th_expediente_horarios', $insert);

						$x++;
					}

					/* // salario
					$insert = [
						'prsExpedienteId'			=> $prsExpedienteId,
						'salarioTipoRemuneracionId'			=> $_POST["tipoRemuneracion"],
						'tipoSalario'				=> 'Inicial',
						'fechaInicioVigencia'		=> date("Y-m-d", strtotime($_POST["fechaContratacion"])),
						'salario'					=> $_POST["salario"],
						'descripcionSalario'		=> "Agregado automáticamente al crear su expediente",
						'estadoSalario'				=> $estadoSalario
					];
					$expedienteSalarioId = $cloud->insert('th_expediente_salarios', $insert); */
	
					// Desactivar cargo anterior
					if(isset($_POST["accionExpediente"])) { // Se está actualizando el expediente
						if($estadoExpediente == "Activo" || $estadoExpediente == "Finalizado") {
							$update = [
								'estadoExpediente' 			=> "Inactivo",
								'justificacionEstado' 		=> $_POST['justificacion'],
							];
							$where = ['prsExpedienteId' => $_POST["expedienteId"]];
							$cloud->update('th_expediente_personas', $update, $where);
						} else {
							// Es un expediente Pendiente, se cambiará a inactivo desde el Cronjob
							// Pero se debe mantener la justificación que se le digitó
							$update = [
								'justificacionEstado' 		=> $_POST['justificacion']
							];
							$where = ['prsExpedienteId' => $_POST["expedienteId"]];
							$cloud->update('th_expediente_personas', $update, $where);
						}

						// Actualizar el prsExpedienteId antiguo por el nuevo en la gestión de jefaturas
						// Primero como jefe
						$update = [
							'jefeId' 				=> $prsExpedienteId
						];
						// el -duplicado es porque el update lleva la misma columna del where y al hacer
						// array_values se sustituye y solo queda 1 parametro, el wrapper retirará la palabra del where
						$where = ['jefeId-duplicado' => $_POST['expedienteId']];
						$cloud->update('th_expediente_jefaturas', $update, $where);

						// Luego como empleado que tenia un jefe a cargo
						$update = [
							'prsExpedienteId' 		=> $prsExpedienteId
						];
						// el -duplicado es porque el update lleva la misma columna del where y al hacer
						// array_values se sustituye y solo queda 1 parametro, el wrapper retirará la palabra del where
						$where = ['prsExpedienteId-duplicado' => $_POST['expedienteId']];
						$cloud->update('th_expediente_jefaturas', $update, $where);

						// Validar si tiene usuario actual
						$poseeUsuario = $cloud->count("
							SELECT usuarioId FROM conf_usuarios
							WHERE personaId = ?
						", [$_POST["persona"]]);

						if($poseeUsuario > 0) {
							if($estadoExpediente == "Activo" || $estadoExpediente == "Finalizado") {
								// No se pueden duplicar usuarios, pero le agrego LIMIT 1 para prevenir bugs
								$dataUsuario = $cloud->row("
									SELECT 
										usuarioId 
									FROM conf_usuarios
									WHERE personaId = ?
									LIMIT 1
								", [$_POST["persona"]]);

								// Eliminar permisos de usuario actual ya que cambió de cargo y por seguridad deben volver a configurar sus permisos 
								//$cloud->deleteById('conf_permisos_usuario', "usuarioId ", $dataUsuario->usuarioId);

								// Actualizar flgMensaje, en caso que tenga un default por cambio de cargo, cronjob, etc
								/*
								$update = [
									'flgMensaje' 		=> 'Cambio-expediente'
								];
								$where = [
									'usuarioId' 		=> $dataUsuario->usuarioId
								];
								$cloud->update("conf_usuarios", $update, $where);
								*/
							} else {
								// Es un expediente Pendiente, los permisos se le eliminarán en el Cronjob
							}
						} else {
							// No tenia usuario
						}
					} else { 
						// Se hizo desde "Nuevo expediente"
						// Actualiza la fecha de ingreso (antiguedad del empleado)
						// Esto actualizará también la fecha de antiguedad de empleados que se retiraron/dieron de baja y regresaron a laborar a la empresa
						$update = [
							'fechaInicioLabores' => date("Y-m-d", strtotime($_POST["fechaContratacion"]))
						];
						$where = ["personaId" => $_POST["persona"]];
						$cloud->update("th_personas", $update, $where);

						// Si son vacaciones individuales, crear su registro de "saldo" de vacaciones
						if($_POST["tipoVacacion"] == "Individuales") {
							$insert = [
								"personaId" 				=> $_POST['persona'], 
								"anio" 						=> substr($_POST["fechaContratacion"], 0, 4), 
								"diasRestantesVacacion" 	=> 15
							];
							$cloud->insert('ctrl_persona_vacaciones', $insert);
						} else {
							// Colectivas no lleva control de dias de vacacion
						}
					}

					//traer nombre
					$dataPersona = $cloud->row("
						SELECT CONCAT(
							IFNULL(apellido1, '-'),
							' ',
							IFNULL(apellido2, '-'),
							', ',
							IFNULL(nombre1, '-'),
							' ',
							IFNULL(nombre2, '-')
						) AS nombreCompleto FROM th_personas WHERE flgDelete = 0 AND personaId = ?
					", [$_POST["persona"]]);
					// jefes/organigrama

					$dataPersona = $cloud->row("
						SELECT CONCAT(
							IFNULL(apellido1, '-'),
							' ',
							IFNULL(apellido2, '-'),
							', ',
							IFNULL(nombre1, '-'),
							' ',
							IFNULL(nombre2, '-')
						) AS nombreCompleto FROM th_personas WHERE flgDelete = 0 AND personaId = ?
					", [$_POST["persona"]]);
					// Bitácora de usuario final 
					$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo expediente al empleado: " . $dataPersona->nombreCompleto . " , ");
					
					echo "success";
				}
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
				if($_POST['flgOtroPersona1'] == 0) {
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
					if($existeDuplicado == 0) {
						$insert = [
							'tipoPrsRelacion'				=> $_POST["nombreRelacionPersona1"]
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
				if($_POST['flgOtroPersona2'] == 0) {
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
					if($existeDuplicado == 0) {
						$insert = [
							'tipoPrsRelacion'				=> $_POST["nombreRelacionPersona2"]
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

				//Persona 1
				$insert = [
					'personaId1'			=> $_POST["persona1"], 
					'personaId2'			=> $_POST["persona2"], 
					'catPrsRelacionId'		=> $relacion2, 
				];
				$prsRelacionId1 = $cloud->insert('th_personas_relacion', $insert);

				//Persona 2
				$insert = [
					'personaId1'			=> $_POST["persona2"], 
					'personaId2'			=> $_POST["persona1"], 
					'catPrsRelacionId'		=> $relacion1, 
				];
				$prsRelacionId2 = $cloud->insert('th_personas_relacion', $insert);

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

				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Se creó la relacion de : ".$dataRelaciones1->nombreCompleto."(". $dataRelaciones1->personaRelacion .") y ".$dataRelaciones2->nombreCompleto." (". $dataRelaciones2->personaRelacion ."),  ");
				
				echo "success";
			break;
			
			case "incapacidad":
				/* 
					hiddenFormData
					typeOperation
					typeOperation
					persona
					fechaIni
					fechaFin
					motivoIncapacidad
					motivoIncapacidad
					motivoIncapacidad
					flgAdjuntar
					adjunto
				*/
				// Solicitud Óscar Ochoa 11-07-2023: Adjunto de incapacidad no requerido
				if($_POST['flgAdjuntar'] == "Sí") {
					$imagenNombre = $_FILES['adjunto']['name'];

					$directorioBase = "adjuntos-personas/";

					$dataNombreCarpeta = $cloud->row("
					SELECT
						pers.personaId as personaId, 
						exp.prsExpedienteId as expedienteId,
						CONCAT(
							IFNULL(pers.nombre1, '-'),
							IFNULL(pers.apellido1, '-')
						) AS nombreCompleto
						FROM th_personas pers
						JOIN th_expediente_personas exp ON pers.personaId = exp.personaId
						WHERE exp.prsExpedienteId = ?
					", [$_POST["persona"]]);
					
					$dirname = $dataNombreCarpeta->personaId . "-" . $dataNombreCarpeta->nombreCompleto;
					
					$filename = "../../../../libraries/resources/images/" . $directorioBase . $dirname . "/";
					
					if (!file_exists($filename)) {
						//chmod("../../../../libraries/resources/images", 0777);
						mkdir("../../../../libraries/resources/images/" . $directorioBase . $dirname, 0755);

					} 
					
					$ubicacion = $filename ."/".$imagenNombre; 
					$flgSubir = 1;
					$imagenFormato = pathinfo($ubicacion,PATHINFO_EXTENSION);


					$formatosPermitidos = array("jpg","jpeg","png","pdf","doc","docx","xls","xlsx");
					$formatosImgPerfil = array("jpg","jpeg","png");

					if(!in_array(strtolower($imagenFormato),$formatosPermitidos)) {
						$flgSubir = 0;
					} else {
						$flgSubir = 1;
					}

					if($flgSubir == 0) {
						// Validación de formato nuevamente por si se evade la de Javascript
						echo "El archivo seleccionado no coincide con un formato válido. Por favor vuelva a seleccionar un archivo con formato válido.";
					} else {
						// Verificar si existe
						$n = 1;
						$originalNombre = $imagenNombre;
						while($n > 0) {
							if(file_exists($ubicacion)) {

								$imagenNombre = "(" . $n . ") " . $originalNombre;
								$ubicacion = $filename . "/".$imagenNombre;
								$n += 1;
							} else {
								// No existe, se mantiene el flujo normal
								$n = 0;
							}
						}

						/* Upload file */
						if(move_uploaded_file($_FILES['adjunto']['tmp_name'],$ubicacion)) {
							$insert = [
								'personaId' => $dataNombreCarpeta->personaId,
								'tipoPrsAdjunto' => "Incapacidad",
								'descripcionPrsAdjunto' => "Comprobante de incapacidad",
								'urlPrsAdjunto' => $directorioBase . $dirname . "/".$imagenNombre
							];
							$adjuntoId = $cloud->insert('th_personas_adjuntos', $insert);
						}
					}
				} else {
					$adjuntoId = 0;
				}

				$insert = [
					'expedienteId'			=> $_POST["persona"],
					'fechaExpedicion'     	=> date("Y-m-d", strtotime($_POST["fechaExpedicion"])),
					'fechaInicio'     		=> date("Y-m-d", strtotime($_POST["fechaIni"])),
					'fechaFin'     			=> date("Y-m-d", strtotime($_POST["fechaFin"])),
					'motivoIncapacidad'     => $_POST["motivoIncapacidad"],
					'tipoIncapacidad'     	=> $_POST["tipoIncapacidad"],
					'riesgoIncapacidad'   	=> $_POST["riesgo"],
					'incapacidadSubsidio'   => $_POST["subsidio"],
					'prsAdjuntoId'     		=> $adjuntoId,
				];
				$cloud->insert('th_expediente_incapacidades', $insert);

				echo "success";
			break;

			case 'empleado-ausencia':
				/*
				POST:
				hiddenFormData: nuevo
				typeOperation
				operation
				persona = expedienteId^nombrePersona
				fechaSolicitud
				fechaAu
				fechaFinAu
				totalDias
				horaInicio
				horaFin
				totalHoras
				motivoAusencia
				goceSueldo
				autorizadoPor
				fechaApro
				*/
			
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
				$dataPersonas = $cloud->row($select,[$persona]);
				
				$queryExiste = "
					SELECT 
						expedienteAusenciaId
					FROM th_expediente_ausencias
					WHERE expedienteId = ? AND fechaAusencia = ? AND fechaFinAusencia = ? AND horaAusenciaInicio = ? AND horaAusenciaFin = ? AND flgDelete = '0'
				";
				$where           = [$persona,$_POST["fechaAu"],$_POST["fechaFinAu"],$_POST["horaInicio"],$_POST["horaFin"]];
				$existeSolicitud = $cloud->count($queryExiste,$where);
				if ($existeSolicitud == 0) {
					$insert = [
						'expedienteId'         => $persona,
						'expedienteIdAutoriza' => $_POST["autorizadoPor"],
						'fechaAutorizacion'    => $_POST["fechaApro"],
						'fhSolicitud'          => $_POST["fechaSolicitud"],
						'fechaAusencia'        => $_POST["fechaAu"],
						'fechaFinAusencia'     => $_POST["fechaFinAu"],
						'totalDias'            => $_POST["totalDias"],
						'horaAusenciaInicio'   => $_POST["horaInicio"],
						'horaAusenciaFin'      => $_POST["horaFin"],
						'totalHoras'           => $_POST["totalHoras"],
						'estadoSolicitudAu'	   => 'Autorizada',
						'motivoAusencia'       => $_POST["motivoAusencia"],
						'goceSueldo'           => $_POST["goceSueldo"]
					];
					$cloud->insert('th_expediente_ausencias',$insert);

					$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó solicitud de ausencia, para la persona: ".$dataPersonas->nombreCompleto.", motivo: ".$_POST["motivoAusencia"].", fechas ".$_POST["fechaAu"]." al ".$_POST["fechaFinAu"].", ");

					echo "success";
				}else{
					echo "Ya exíste una solicitud con los mismos datos para la persona ".$dataPersonas->nombreCompleto;
				}
				

			break;
			case "amonestacion":
				/*
					hiddenFormData
					typeOperation
					amonestacionId
					operation
					persona
					fechaAmonestacion
					argo
					depto
					jefeId
					tipoSancion
					suspension
					reincidencia
					amonestacionAnt
					fechaIniSus
					fechaFinSus
					totalDias
					causaSancion
					descripcion
					consecuencia
					advertencia
					compromiso
					fechaVigenciaIni
					fechaVigenciaFin

					amonestacionId: 0
					operation: amonestacion
					persona: 142
					fechaAmonestacion: 2024-07-15
					cargo: Jefe de desarrollo Cloud
					depto: Desarrollo 
					jefeId: 
					tipoSancion: Verbal  y Escrita
					reincidencia: No
					amonestacionAnt: 
					causaSancion: Otros
					descripcion: Esta es una falta nueva
					descripcionFalta: Hizo x falta cometida hoy
					consecuencia: Destitución
					descripcionConsecuencia
					fechaVigenciaIni: 2024-07-15
					fechaVigenciaFin: 2024-08-10
					compromiso: SI SE COMPROMETE A MEJORAR LA FALTA COMETIDA 
				 */
				if (!isset($_POST["suspension"])){
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
				if ($_POST["amonestacionAnt"] == ''){
					$amonestacionAnterior = NULL;
				} else {
					$amonestacionAnterior = $_POST["amonestacionAnt"];
				}

				$insert = [
					'expedienteIdJefe'			=> $_POST["jefeId"],
					'expedienteId'				=> $_POST["persona"],
					'fechaAmonestacion'			=> $_POST["fechaAmonestacion"],
					'tipoAmonestacion'			=> $_POST["tipoSancion"],
					//'suspension'				=> $suspension,
					//'totalDiasSuspension'		=> $totalDias,
					'causaFalta'				=> $_POST["causaSancion"],
					'descripcionFalta'			=> $_POST["descripcionFalta"],
					'descripcionOtroCausa'		=> $_POST["descripcion"],
					'consecuenciaFalta'			=> $_POST["consecuencia"],
					'descripcionConsecuencia' 	=> $_POST["descripcionConsecuencia"],
					//'compromisoMejora'			=> $_POST["compromiso"],
					'fechaSuspensionInicio'     => empty($_POST["fechaVigenciaIni"]) ? NULL : $_POST["fechaVigenciaIni"],
    				'fechaSuspensionFin'        => empty($_POST["fechaVigenciaFin"]) ? NULL : $_POST["fechaVigenciaFin"],
					'flgReincidencia'			=> $_POST["reincidencia"],
					'amonestacionAnteriorId'	=> $amonestacionAnterior,
					'estadoAmonestacion'		=> "Activo",
				];
				$cloud->insert('th_expediente_amonestaciones', $insert);


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
				$dataPersonas = $cloud->row($select,[$persona]);

				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una amonestación, para la persona: ".$dataPersonas->nombreCompleto.", motivo: ".$_POST["causaSancion"].", ");
	
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
				$dataPersonas = $cloud->row($select,[$_POST["expedienteId"]]);

				//restar dias disponibles

				if ($_POST["numDias"] == ''){
					$numeroDias = 15;
					$numeroDiasInsert = 15;
				} else {
					$numeroDias = $_POST["numDias"];
					$numeroDiasInsert = $_POST["numDias"];
				}


				$contarDiasVaca = $cloud->row("SELECT SUM(diasRestantesVacacion) as totalDias FROM ctrl_persona_vacaciones WHERE personaId = ? AND flgDelete = ?", [$dataPersonas->personaId, 0]);

				//validar si tiene suficientes dias disponibles
				if($numeroDias > $contarDiasVaca->totalDias){
					echo "El número de días supera los días disponibles para vacaciones.";
				}else{
					$diasDisponibles = $cloud->rows("SELECT ctrlVacacionId, diasRestantesVacacion, anio FROM ctrl_persona_vacaciones WHERE personaId = ? AND flgDelete = ? ORDER BY anio ASC", [$dataPersonas->personaId, 0]);

					foreach($diasDisponibles as $dias){
						
						if($numeroDias > 0){
							$total = $dias->diasRestantesVacacion - $numeroDias;
						} else {
							$total = $dias->diasRestantesVacacion;
						}
	
						if($total < $dias->diasRestantesVacacion){
							$numeroDias = abs($total);
						}
						if($total == $numeroDias){
							$numeroDias = 0;
						}
						if($total <= 0){
							$total = 0;
						}
	
						//update
						$update = [
							'diasRestantesVacacion'		=> $total,
						];
						$where = ['ctrlVacacionId' => $dias->ctrlVacacionId]; // ids, soporta múltiple where
						
						$cloud->update('ctrl_persona_vacaciones', $update, $where);
					}
	
					$insert = [
						'expedienteId'			=> $_POST["expedienteId"],
						'expedienteJefeId'		=> $_POST["autorizadoPor"],
						'fhSolicitud'			=> $_POST["fechaSolicitud"],
						//'flgIngresoSolicitud'	=> $_POST[""],
						'periodoVacaciones'		=> $_POST["tipoVaca"],
						'numDias'				=> $numeroDiasInsert,
						'fechaInicio'			=> $_POST["fechaIni"],
						'fechaFin'				=> $_POST["fechaFin"],
						'fhaprobacion'			=> $_POST["fechaApro"],
						'estadoSolicitud'		=> "Aprobado",
						
					];
					$cloud->insert('th_expedientes_vacaciones', $insert);

					$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un periodo de vacaciones para la persona: ".$dataPersonas->nombreCompleto.", Duración: ".$numeroDiasInsert."(Desde: ".$_POST["fechaIni"].", Hasta: ".$_POST["fechaFin"]."), ");
	
					echo "success";
				}
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

				if (!isset($_POST["ramaSuperior"])){
					$ramaSuperior = NULL;
				} else {
					$ramaSuperior = $_POST["ramaSuperior"];
				}
				$insert = [
					'organigramaRama'				=> $_POST["nombreRama"],
					'organigramaRamaDescripcion'	=> $_POST["organigramaRamaDescripcion"],
					'ramaSuperiorId'				=> $ramaSuperior
				];
				$cloud->insert('cat_organigrama_ramas', $insert);

				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una rama al organigrama: ".$_POST["nombreRama"].", ");

				echo "success";

			break;
			
			case "empleado-organigrama":
				/*
				hiddenFormData // idRama
				typeOperation
				operation
				persona
				 */

				$x = 0;
				$personaId = $_POST["persona"];

				if (is_array($personaId)){
					foreach($personaId as $persona){
						$insert = [
							'prsExpedienteId'				=> $_POST["persona"][$x],
							'organigramaRamaId'				=> $_POST["hiddenFormData"],
						];
						$cloud->insert('th_expediente_organigrama', $insert);
						$x++;
					}
				} else{
					$insert = [
						'prsExpedienteId'				=> $_POST["persona"],
						'organigramaRamaId'				=> $_POST["hiddenFormData"],
					];
					$cloud->insert('th_expediente_organigrama', $insert);
					$x++;
				}

				$totalPersonas = $x + 1;


				$select = "
                    SELECT
                    organigramaRama
                    FROM cat_organigrama_ramas
                    WHERE organigramaRamaId = ?
                ";
				$dataRama = $cloud->row($select,[$_POST["hiddenFormData"]]);

				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó ".$totalPersonas." personas al área: ".$dataRama->organigramaRama.", ");

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
					WHERE personaId = ? AND nombreOrganizacionId = ? AND numeroCuenta = ? AND flgDelete = ?
				", [$_POST["personaId"], $_POST['nombreOrganizacionId'], $_POST['numeroCuenta'], '0']);
				// Saltarse la validación para el caso de licencias de armas, puede tener más de una del mismo tipo
				if($existeCuenta == 0) {
					// Validar si se va a agregar una nueva cuenta planillera
					if(isset($_POST['flgCuentaPlanilla'])) {
						// Validar si ya existe una cuenta planillera
						$existeCuentaPlanilla = $cloud->count("
							SELECT prsCBancariaId FROM th_personas_cbancaria
							WHERE prsCBancariaId <> ? AND personaId = ? AND flgCuentaPlanilla = ? AND flgDelete = ?
						", [$_POST['prsCBancariaId'], $_POST["personaId"], '1', '0']);
					} else {
						// No se va a agregar una cuenta planillera, no hay necesidad de consulta, setear a 0
						$existeCuentaPlanilla = 0;
					}

					if($existeCuentaPlanilla == 0) {
						$insert = [
							'personaId'					=> $_POST["personaId"],
							'nombreOrganizacionId' 		=> $_POST['nombreOrganizacionId'],
							'numeroCuenta' 				=> $_POST['numeroCuenta'], 
							'descripcionCuenta' 		=> $_POST['descripcionCuenta']
						];

	                    if(isset($_POST['flgCuentaPlanilla'])) {
	                    	$insert += [
	                    		'flgCuentaPlanilla' => $_POST["flgCuentaPlanilla"]
	                    	];
	                    } else {
	                    	// Tiene default 0 la columna flgCuentaPlanilla en la bd
	                    }

						$cloud->insert('th_personas_cbancaria', $insert);

				        // Bitácora de usuario final / jefes
				        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva cuenta bancaria al empleado: " . $nombreCompleto . " Número de cuenta: ".$_POST['numeroCuenta'].", ");
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

				$insert = [
					"personaId" 						=> $_POST['personaId'],
					"catPrsRelacionId" 					=> $_POST['parentesco'],
					"nombreFamiliar" 					=> $_POST['nombreFamiliar'],
					"apellidoFamiliar"					=> $_POST['apellidoFamiliar'],
					"fechaNacimiento" 					=> $_POST["fechaNacimiento"],
					"flgBeneficiario" 					=> $_POST['flgBeneficiario'],
					"flgDependeEconomicamente"			=> $_POST['flgDependeEconomicamente'],
					"flgVivenJuntos" 					=> $_POST['flgVivenJuntos']
 				];

				if($_POST['flgBeneficiario'] == "Sí") {
					$insert += [
						"porcentajeBeneficiario" 		=> $_POST['porcentajeBeneficiario']
					];
				} else {
					// No se agregó beneficiario
				}

				if($_POST['flgVivenJuntos'] == "Sí") {
					$insert += [
						"direccionVivenJuntos" 			=> $dataPersona->zonaResidenciaActual
					];
				} else {
					$insert += [
						"direccionVivenJuntos" 			=> $_POST['direccionVivenJuntos']
					];
				}

				$cloud->insert('th_personas_familia', $insert);

		        // Bitácora de usuario final / jefes
		        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva relación familiar del empleado: " . $_POST['nombreEmpleado'] . " Parentesco: ".$dataParentesco->tipoPrsRelacion.", ");
	        	echo "success";
			break;

			case "expediente-jefatura":
				/*
					hiddenFormData
					typeOperation
					operation
					jefeId
					prsExpedienteId = multiple
				*/
				foreach($_POST['prsExpedienteId'] as $prsExpedienteId) {
					if($prsExpedienteId == $_POST['jefeId']) {
						// Omitir insert, no puede asignarse la misma persona como jefe
					} else {
						// Validar si el jefe ya tiene a esa persona asignada
						$existeJefatura = $cloud->count("
							SELECT expedienteJefaturaId FROM th_expediente_jefaturas
							WHERE jefeId = ? AND prsExpedienteId = ? AND flgDelete = ?
						", [$_POST['jefeId'], $prsExpedienteId, 0]);
						if($existeJefatura == 0) {
							$insert = [
								"jefeId" 					=> $_POST['jefeId'], 
								"prsExpedienteId" 			=> $prsExpedienteId, 
								"ordenJefatura" 			=> 0, 
								"flgHeredarPersonal" 		=> "No",
								"flgJefeInmediato" 			=> "No"
							];
							$cloud->insert("th_expediente_jefaturas", $insert);
						} else {
							// Omitir insert, ya fue asignada esa jefatura
						}
					}
				}
				echo "success";
			break;

			case "expediente-jefatura-empleado":
				/*
					POST:
					hiddenFormData
					typeOperation
					operation
					prsExpedienteId
					jefeId = multiple
				*/
				foreach($_POST['jefeId'] as $jefeId) {
					if($jefeId == $_POST['jefeId']) {
						// Omitir insert, no puede asignarse la misma persona como jefe
					} else {
						// Validar si el jefe ya tiene a esa persona asignada
						$existeJefatura = $cloud->count("
							SELECT expedienteJefaturaId FROM th_expediente_jefaturas
							WHERE jefeId = ? AND prsExpedienteId = ? AND flgDelete = ?
						", [$jefeId, $_POST['prsExpedienteId'], 0]);
						if($existeJefatura == 0) {
							$insert = [
								"jefeId" 					=> $jefeId, 
								"prsExpedienteId" 			=> $_POST['prsExpedienteId'], 
								"ordenJefatura" 			=> 0, 
								"flgHeredarPersonal" 		=> "No"
							];
							$cloud->insert("th_expediente_jefaturas", $insert);
						} else {
							// Omitir insert, ya fue asignada esa jefatura
						}
					}
				}
				echo "success";
			break;
			case "capacitaciones":
				/*
					POST:
					expedienteId[]
					capacitacion
					fechaInicio
					fechaFin
					organizador
					duracion
					selectModalidad
					selectTipoFormacion
					costoInsaford
					costoEmpresa
				
				*/
					$insert = [ 
						"descripcionCapacitacion" 	=> $_POST['capacitacion'], 
						"nombreOrganizador" 		=> $_POST['organizador'],
						"tipoFormacion"				=> $_POST['selectTipoFormacion'],
						"fechaIniCapacitacion"		=> $_POST['fechaInicio'],
						"fechaFinCapacitacion"		=> $_POST['fechaFin'],
						"duracionCapacitacion" 		=> $_POST['duracion'],
						"tipoModalidad"				=> $_POST['selectModalidad'],
						"costoInsaforp"				=> $_POST['costoInsaforp'],
						"costoalina"				=> $_POST['costoEmpresa']
					];
					$expedienteCapacitacionId = $cloud->insert("th_expediente_capacitaciones", $insert);
					foreach ($_POST["expedienteId"] as $prsExpedienteId) {
						$insert = [ 
							"expedienteCapacitacionId" 	=> $expedienteCapacitacionId, 
							"prsExpedienteId" 			=> $prsExpedienteId
						];
						 $cloud->insert("th_expediente_capacitacion_detalle", $insert);
					}
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