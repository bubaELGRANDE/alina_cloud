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
			case "nuevo-modulo":
				/*
					POST:
					hiddenFormData
					typeOperation
					operation
					modulo
					carpeta
					icono
					flgBadge
					badgeColor
					badgeText
					descripcionModulo
				*/
				if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(32, $_SESSION["arrayPermisos"])) {
					$queryCarpeta = "
						SELECT 
							moduloId,
							modulo
						FROM conf_modulos
						WHERE urlModulo = ? AND flgDelete = '0'
					";
					$existeCarpetaAsignada = $cloud->count($queryCarpeta, [$_POST["carpeta"]]);
					if($existeCarpetaAsignada == 0) {
						$badgeColor = ($_POST["flgBadge"] == 0) ? null : $_POST["badgeColor"];
						$badgeText = ($_POST["flgBadge"] == 0) ? null : $_POST["badgeText"];

						$insert = [
							'modulo'				=> $_POST["modulo"],
							'descripcionModulo'		=> $_POST["descripcionModulo"],
							'iconModulo'			=> $_POST["icono"],
							'urlModulo'				=> $_POST["carpeta"],
							'badgeColor'			=> $badgeColor,
							'badgeText'				=> $badgeText,
						];
						$cloud->insert('conf_modulos', $insert);
				        // Bitácora de usuario final / jefes
				        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo módulo: ".$_POST["modulo"].", ");
						echo "success";
					} else {
						$nombreCarpeta = $cloud->row($queryCarpeta, [$_POST["carpeta"]]);
						echo "La carpeta seleccionada ya ha sido asignada al módulo: " . $nombreCarpeta->modulo;
					}
				} else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y crear un nuevo módulo ".$_POST["modulo"]." (manejo desde consola), ");
        			echo "Acción no válida.";
				}
			break;

			case "nuevo-menu":
				/*
					POST:
					hiddenFormData
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
				*/
				if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(35, $_SESSION["arrayPermisos"])) {
					$queryExisteURL = "
						SELECT
							menu
						FROM conf_menus
						WHERE moduloId = ? AND urlMenu = ? AND flgDelete = '0'
					";
					$existeURL = $cloud->count($queryExisteURL, [$_POST["moduloId"], $_POST["urlMenu"]]);

					if($existeURL == 0) {
						$stringSuccess = "success^";

						$badgeColor = ($_POST["flgBadge"] == 0) ? null : $_POST["badgeColor"];
						$badgeText = ($_POST["flgBadge"] == 0) ? null : $_POST["badgeText"];

						if($_POST["tipoMenu"] == "unico") {

							$insert = [
								'moduloId'				=> $_POST["moduloId"],
								'menu'					=> $_POST["menu"],
								'iconMenu'				=> $_POST["icono"],
								'urlMenu'				=> $_POST["urlMenu"],
								'numOrdenMenu'			=> $_POST["ordenMenu"],
								'menuSuperior'			=> '0',
								'badgeColor'			=> $badgeColor,
								'badgeText'				=> $badgeText,
							];
							$menuIdInsert = $cloud->insert('conf_menus', $insert);

							$menuSuperior = 0;
							$stringSuccess .= "Menú agregado con éxito.";
						} else { // Submenu
							$insert = [
								'moduloId'				=> $_POST["moduloId"],
								'menu'					=> $_POST["menu"],
								'iconMenu'				=> $_POST["icono"],
								'urlMenu'				=> $_POST["urlMenu"],
								'numOrdenMenu'			=> $_POST["ordenMenu"],
								'menuSuperior'			=> $_POST["menuSuperior"],
								'badgeColor'			=> $badgeColor,
								'badgeText'				=> $badgeText,
							];
							$menuIdInsert = $cloud->insert('conf_menus', $insert);
							$menuSuperior = $_POST["menuSuperior"];

							// Validar si el menú padre tenía menús
							$queryPermisosMenuSup = "
								SELECT
									menuPermisoId,
									permisoMenu
								FROM conf_menus_permisos
								WHERE menuId = ? AND flgDelete = '0'
							";
							$existenPermisosMenuSup = $cloud->count($queryPermisosMenuSup,[$_POST["menuSuperior"]]);
							if($existenPermisosMenuSup > 0) {
								// Eliminar esos permisos, exceptuando el dropdown
								$dataPermisosMenuSup = $cloud->rows($queryPermisosMenuSup, [$_POST["menuSuperior"]]);
								$existeDrop = 0;
								foreach ($dataPermisosMenuSup as $dataPermisosMenuSup) {
									if($dataPermisosMenuSup->permisoMenu != 'Dropdown') {
										// Eliminar, ya que este menu ahora sera dropdown
										$cloud->deleteById("conf_menus_permisos", "menuPermisoId", $dataPermisosMenuSup->menuPermisoId);
									} else {
										// Omitir, es el permiso Dropdown
										$existeDrop = 1;
									}
								}
								if($existeDrop == 1) {
									$crearDropdown = 0;
								} else {
									$crearDropdown = 1;
								}
							} else { // No existe ningún permiso, crear el dropdown
								$crearDropdown = 1;
							}

							if($crearDropdown == 1) {
								$insert = [
									'menuId'			=> $_POST["menuSuperior"],
									'permisoMenu'		=> 'Dropdown',
								];
								$cloud->insert('conf_menus_permisos', $insert);
							} else {
							}

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
							$stringSuccess .= "Submenú agregado con éxito.<br>El menú: " . $nombreDropdown->menu . ' ha pasado a ser "dropdown".';
						}

						// Crear permiso "Desarrollo"
						$insert = [
							'menuId'		=> $menuIdInsert,
							'permisoMenu'	=> 'Desarrollo',
						];
						$cloud->insert("conf_menus_permisos", $insert);

						// Validar si se deben actualizar los ordenMenu. Ej: Recomendado 8 pero coloqué "6", se debe actualizar el 6, 7 y 8 actual
						if($_POST["ordenMenu"] != $_POST["ordenRecomendado"]) {
							if($menuSuperior == 0) {
								$stringSuccess .= "<br>Se ha actualizado el orden de los menús (+1):";
							} else {
								$stringSuccess .= "<br>Se ha actualizado el orden de los submenús (+1):";
							}
							
							$iterarOrdenRepetidos = $cloud->rows("
								SELECT
									menuId,
									menu,
									numOrdenMenu
								FROM conf_menus
								WHERE moduloId = ? AND menuSuperior = ? AND numOrdenMenu >= ? AND menuId <> ? AND flgDelete = '0'
							", [$_POST["moduloId"], $menuSuperior, $_POST["ordenMenu"], $menuIdInsert]);
							$n = 0;
							foreach ($iterarOrdenRepetidos as $iterarOrdenRepetidos) {
								$n += 1;
								$numOrdenMenu = $iterarOrdenRepetidos->numOrdenMenu + 1;
								$update = [
									'numOrdenMenu' 	=> $numOrdenMenu,
								];
								$where = ['menuId' => $iterarOrdenRepetidos->menuId];
								$cloud->update('conf_menus', $update, $where);
								$stringSuccess .= "<br>" . $n . ". " . $iterarOrdenRepetidos->menu . " (" . $iterarOrdenRepetidos->numOrdenMenu . " -> " . $numOrdenMenu . ")";
							}
						} else {
							// No actualizar los numOrden, ya que se insertó el recomendado
						}

						// Bitácora de usuario final / jefes
				        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo menú: ".$_POST["menu"]." (Tipo: ".$_POST["tipoMenu"]."), ");
						echo $stringSuccess;
					} else { // Existe URL
						$nombreMenuURL = $cloud->row($queryExisteURL, [$_POST["moduloId"], $_POST["urlMenu"]]);
						echo "La URL ya fue asignada al menú: " . $nombreMenuURL->menu;
					}
				} else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y crear un menú ".$_POST["menu"]." tipo: ".$_POST["tipoMenu"]." (manejo desde consola), ");
        			echo "Acción no válida.";
				}
			break;

			case "menu-permisos-usuario":
				/*
					POST:
					hiddenFormData
					typeOperation
					operation
					flgStep
					totalContinuar
					flgContinuarActual
					usuarioId[] - Multiple
					moduloId
					menuId[] - Multiple
					checkPermisos1[] - checkPermisosN acorde a la cantidad de menus seleccionados - Multiple
				*/
				if(in_array(10, $_SESSION["arrayPermisos"]) || in_array(50, $_SESSION["arrayPermisos"]) || in_array(51, $_SESSION["arrayPermisos"])) {
					$stringSuccess = "";
					$stringPermisosOmitidos = ""; $permisosOmitidos = 0;
					// Iterar los menús
					$n = 0;
					foreach ($_POST["menuId"] as $menuId) {
						$n += 1;
						$checkPermisos = "checkPermisos" . $n; // Crear el nombre del POST según correlativo
						// Iterar los permisos
						foreach ($_POST[$checkPermisos] as $menuPermisoId) {
							// Iterar los usuarios
							foreach ($_POST["usuarioId"] as $usuarioId) {
								// Validar si este usuario ya tiene ese permiso-menú para evitar duplicar
								$existePermisoUsuario = $cloud->count("
									SELECT
										permisoUsuarioId
									FROM conf_permisos_usuario
									WHERE menuPermisoId = ? AND usuarioId = ? AND flgDelete = '0'
								",[$menuPermisoId, $usuarioId]);
								if($existePermisoUsuario == 0) {
									// Verificar si ese menuId es submenú
									$esSubmenu = $cloud->row("
										SELECT
											menuSuperior
										FROM conf_menus
										WHERE menuId = ?
									",[$menuId]);

									if($esSubmenu->menuSuperior > 0) { // Es submenú, traer el permiso "Dropdown" del menuSuperior
										$menuPermisoDropdown = $cloud->row("
											SELECT
												menuPermisoId
											FROM conf_menus_permisos
											WHERE menuId = ? AND permisoMenu = 'Dropdown' AND flgDelete = '0'
										",[$esSubmenu->menuSuperior]);

										// En caso que se seleccionen más de 1 permiso de un submenú, validar si ya se insertó anteriormente el dropdown
										$asignacionDropdown = $cloud->count("
											SELECT
												permisoUsuarioId
											FROM conf_permisos_usuario
											WHERE menuPermisoId = ? AND usuarioId = ? AND flgDelete = '0'
										",[$menuPermisoDropdown->menuPermisoId, $usuarioId]);

										if($asignacionDropdown == 0) {
											// Insertar el permiso "Dropdown"
											$insert = [
												"menuPermisoId" 	=> $menuPermisoDropdown->menuPermisoId,
												"usuarioId"			=> $usuarioId,
											];
											$cloud->insert("conf_permisos_usuario", $insert);	
										} else {
											// Ya se le asignó el dropdown
										}	
									} else {
										// Es menú único, no sucederá nada...
									}
									// Insert normal, ya que no existe el permiso a este menu/submenu
									$insert = [
										"menuPermisoId" 	=> $menuPermisoId,
										"usuarioId"			=> $usuarioId,
									];
									$permisoUsuarioIdInsert = $cloud->insert("conf_permisos_usuario", $insert);

									// Actualizar flgMensaje, en caso que tenga un default por cambio de cargo, cronjob, etc
									$update = [
										'flgMensaje' 		=> null
									];
									$where = [
										'usuarioId' 		=> $usuarioId
									];
									$cloud->update("conf_usuarios", $update, $where);

									// Bitácora de usuario final / jefes
									$dataNombreMenuPermiso = $cloud->row("
										SELECT
											mp.permisoMenu AS permisoMenu,
										    m.menu AS menu
										FROM conf_menus_permisos mp
										JOIN conf_menus m ON m.menuId = mp.menuId
										WHERE menuPermisoId = ?
									", [$menuPermisoId]);

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
								    ", [$usuarioId]);

			        				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Asignó el permiso: ".$dataNombreMenuPermiso->permisoMenu." del menú: ".$dataNombreMenuPermiso->menu." al usuario: ".$dataNombreUsuario->nombrePersona.", ");
								} else {
									// Existe el permiso, sumar para mostrar mensaje al finalizar el proceso
									$permisosOmitidos += $existePermisoUsuario;
								}
							}
						}
					}
					if($permisosOmitidos == 0) {
						$stringSuccess = 'success^Permisos asignados con éxito. Notificar a los usuarios que actualicen la pestaña del navegador.';
					} else {
						$stringSuccess = 'success^Permisos asignados con éxito.<br>Se omitieron ' . $permisosOmitidos . ' permisos ya asignados.';
					}
					echo $stringSuccess;
				} else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y asignar permisos de usuario [m = ".$_POST["menuId"].", u = ".$_POST["usuarioId"]."] (manejo desde consola), ");
        			echo "Acción no válida.";				
				}
			break;

	        case "estudio":
                /*
                    POST:
                    hiddenFormData
                    typeOperation
                    operation
                    nombreArea
                */
                if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(15, $_SESSION["arrayPermisos"])) {
                	$queryExisteEstudio = "
                		SELECT 
                			areaEstudio
                		FROM cat_personas_ar_estudio
                		WHERE areaEstudio = ? AND flgDelete = 0
                	";
                	$existeEstudio = $cloud->count($queryExisteEstudio,[$_POST["nombreArea"]]);
                	if ($existeEstudio==0) {
                		$insert=[
	                        'areaEstudio' => $_POST["nombreArea"]
	                    ];
	                    $cloud->insert('cat_personas_ar_estudio', $insert);

	                    $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó el área de estudio: ".$_POST["nombreArea"].", ");
	                    echo "success";
                	}else{
                		echo "El área de estudio: ".$_POST["nombreArea"]." ya existe en el catálogo.";
                	}
                    
                }else {
                    // No tiene permisos
                    // Bitacora
                    $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados e insertar una nueva área (manejo desde consola), ");
                    echo "Acción no válida.";
                }
	        break;

	        case "experiencia":
                /*
                    POST:
                    hiddenFormData
                    typeOperation
                    operation
                    nombreArea
                */
                if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(15, $_SESSION["arrayPermisos"])) {
                	$queryExisteExperiencia = "
                		SELECT 
                			areaExperiencia
                		FROM cat_personas_ar_experiencia
                		WHERE areaExperiencia = ? AND flgDelete = 0
                	";
                	$existeExperiencia = $cloud->count($queryExisteExperiencia,[$_POST["nombreArea"]]);
                	if ($existeExperiencia==0) {
                		$insert=[
	                        'areaExperiencia' => $_POST["nombreArea"]
	                    ];
	                    $cloud->insert('cat_personas_ar_experiencia', $insert);
	                    $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó el área de experiencia: ".$_POST["nombreArea"].", ");
	                    echo "success";
                	}else{
                		echo "El área de experiencia: ".$_POST["nombreArea"]." ya existe en el catálogo.";
                	}
                    
                }else {
                    // No tiene permisos
                    // Bitacora
                    $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados e insertar una nueva área (manejo desde consola), ");
                    echo "Acción no válida.";
                }
	        break;

	        case "software":
                /*
                    POST:
                    hiddenFormData
                    typeOperation
                    operation
                    nombreArea
                */
                //if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(15, $_SESSION["arrayPermisos"])) {
	                $queryExisteSoftware = "
	                	SELECT
	                		nombreSoftware
	                	FROM cat_personas_software
	                	WHERE nombreSoftware = ? AND flgDelete = 0
	                ";
	                $existeSoftware = $cloud->count($queryExisteSoftware,[$_POST["nombreArea"]]);
	                if ($existeSoftware==0) {
	                	$insert=[
	                        'nombreSoftware' => $_POST["nombreArea"]
	                    ];
	                    $cloud->insert('cat_personas_software', $insert);
	                    $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó el software: ".$_POST["nombreArea"].", ");
	                    echo "success";
	                }else{
	                	echo "El programa informático: ".$_POST["nombreArea"]." ya existe en el catálogo.";
	                }
                    
                //}else {
                    // No tiene permisos
                    // Bitacora
                   // $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados e insertar una nueva área (manejo desde consola), ");
                    //echo "Acción no válida.";
                //}
	        break;
	        case "herraEqu":
                /*
                    POST:
                    hiddenFormData
                    typeOperation
                    operation
                    nombreArea
                */
                //if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(15, $_SESSION["arrayPermisos"])) {
                	$queryExisteHerraEqu = "
                		SELECT
                			nombreHerrEquipo
                		FROM cat_personas_herr_equipos
                		WHERE nombreHerrEquipo = ? AND flgDelete = 0
                	";
                	$existeHerrEqu = $cloud->count($queryExisteHerraEqu,[$_POST["nombreArea"]]);
                	if ($existeHerrEqu==0) {
                		$insert=[
	                        'nombreHerrEquipo' => $_POST["nombreArea"]
	                    ];
	                    $cloud->insert('cat_personas_herr_equipos', $insert);
	                    $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó la herramienta/equipo: ".$_POST["nombreArea"].", ");
	                    echo "success";
                	}else{
                		echo "La herramienta/equipo: ".$_POST["nombreArea"]." ya existe en el catálogo.";
                	}
                    
                //}else {
                    // No tiene permisos
                    // Bitacora
                   // $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados e insertar una nueva área (manejo desde consola), ");
                    //echo "Acción no válida.";
                //}
	        break;

	        case "tipoRelacion":
                /*
                    POST:
                    hiddenFormData
                    typeOperation
                    operation
                    nombreRelacion
                */
                //if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(15, $_SESSION["arrayPermisos"])) {
                	$queryExisteTipoRelacion = "
                		SELECT
                			tipoPrsRelacion
                		FROM cat_personas_relacion
                		WHERE tipoPrsRelacion = ? AND flgDelete = 0
                	";
                	$existeTipoRelacion = $cloud->count($queryExisteTipoRelacion,[$_POST["nombreRelacion"]]);
                	if ($existeTipoRelacion==0) {
                		$insert=[
	                        'tipoPrsRelacion' => $_POST["nombreRelacion"]
	                    ];
	                    $cloud->insert('cat_personas_relacion', $insert);
	                    $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó la relación: ".$_POST["nombreRelacion"].", ");
	                    echo "success";
                	}else{
                		echo "El tipo de relación: ".$_POST["nombreRelacion"]." ya existe en el catálogo.";
                	}
                    
                //}else {
                    // No tiene permisos
                    // Bitacora
                   // $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados e insertar una nueva área (manejo desde consola), ");
                    //echo "Acción no válida.";
                //}
	        break;

	        case "menu-permiso":
	        	/*
					POST:
					hiddenFormData
					typeOperation
					operation
					menuId
					flgInsertUpdate - para enviar a insert o update
					updateMenuPermisoId - para enviar a insert o update
					permisoMenu
	        	*/
	        	if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(39, $_SESSION["arrayPermisos"])) {
		            $insert=[
		                'menuId' 		=> $_POST["menuId"],
		                'permisoMenu'	=> $_POST["permisoMenu"],
		            ];
		            $cloud->insert('conf_menus_permisos', $insert);
					// Bitácora de usuario final / jefes
					$dataNombreMenu = $cloud->row("
						SELECT
							menu
						FROM conf_menus
						WHERE menuId = ?
					", [$_POST["menuId"]]);
			        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo permiso: ".$_POST["permisoMenu"]." (Menú: ".$dataNombreMenu->menu."), ");
		            echo "success";
	        	} else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y agregar un nuevo permiso ".$_POST["permisoMenu"]." (manejo desde consola), ");
        			echo "Acción no válida.";
	        	}
	        break;
	            
	        case "tipoContacto":
                /*
                    POST:
                    hiddenFormData
                    typeOperation
                    nombreContacto
                    masca
                */
                if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(18, $_SESSION["arrayPermisos"])) {
                	$existeTipoContacto = $cloud->count("
                		SELECT 
                			tipoContacto
                		FROM cat_tipos_contacto
                		WHERE tipoContacto = ? AND formatoContacto = ? AND flgDelete = 0
                	",[$_POST["nombreContacto"],$_POST["masca"]]);
                	if ($existeTipoContacto == 0) {
                		$insert = [
	                        'tipoContacto' => $_POST["nombreContacto"],
	                        'formatoContacto' => $_POST["masca"]
	                    ];
	                    $cloud->insert('cat_tipos_contacto', $insert);
	                    $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un tipo de contacto: ".$_POST["nombreContacto"]." (Máscara: ".$_POST["masca"]."), ");
	                    echo "success";
                	}else{
                		echo "El tipo de contacto: ".$_POST["nombreContacto"]." / ".$_POST["masca"]." ya existe en el catálogo.";
                	}
                }else {
                    // No tiene permisos
                    // Bitacora
                    $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados e insertar un nuevo tipo de contacto (manejo desde consola), ");
                    echo "Acción no válida.";
                }
	        break;
	            
	        case "nuevaSucursal":
	            /*
                    POST:
                    hiddenFormData
                    typeOperation
                    operation
                    numOrdenSucursal
                    nombreSucursal
                    departamento
                    municipio
                    direccion
                    subirLogo
                */
                
                if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(21, $_SESSION["arrayPermisos"])) {
                    $imagenNombre = $_FILES['subirLogo']['name'];

                    $ubicacion = "../../../../libraries/resources/images/logos/sucursales/".$imagenNombre;
                    $flgSubir = 1;
                    $imagenFormato = pathinfo($ubicacion,PATHINFO_EXTENSION);


                    $formatosPermitidos = array("jpg","jpeg","png");

                    if(!in_array(strtolower($imagenFormato),$formatosPermitidos)) {
                        $flgSubir = 0;
                    } else {
                        $flgSubir = 1;
                    }

                    if($flgSubir == 0) {
                        // Validación de formato nuevamente por si se evade la de Javascript
                       echo "El archivo seleccionado no coincide con una imagen. Por favor vuelva a seleccionar una imagen con formato válido.";
                    } else {
                        /* Upload file */
                        if(move_uploaded_file($_FILES['subirLogo']['tmp_name'],$ubicacion)) {
                            $insert = [
                                'sucursal' => $_POST["nombreSucursal"],
                                'paisMunicipioId' => $_POST["municipio"],
                                'direccionSucursal' => $_POST["direccion"],
                                'numOrdenSucursal' => $_POST["numOrdenSucursal"],
                                'urlLogoSucursal' => 'logos/sucursales/' . $imagenNombre
                            ];
                            $cloud->insert('cat_sucursales', $insert);
                            $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva sucursal: ".$_POST["nombreSucursal"].", ");
                            echo "success";

                        } else {
                            echo "Problema al cargar la imagen. Por favor comuniquese con el departamento de Informática.";
                        }
                    }
	            }else {
                    // No tiene permisos
                    // Bitacora
                    $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados e insertar una sucursal (manejo desde consola), ");
                    echo "Acción no válida.";
                }
	        break;
	            
	        case "ContactoSucursal":
                /*
                    POST:
                    hiddenFormData
                    typeOperation
                    idSucursal
                    idContact
                    tipoContacto
                    contactoSucursal
                    descripcionContacto
                    tblContactSuc_length
                */
                // Posee permiso
                if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(24, $_SESSION["arrayPermisos"])) {
                    $insert = [
                        'sucursalId'		=> $_POST["idSucursal"],
                        'tipoContactoId'     => $_POST["tipoContacto"],
                        'contactoSucursal ' => $_POST["contactoSucursal"],
                        'descripcionCSucursal' => $_POST["descripcionContacto"]
                    ];
                    $cloud->insert('cat_sucursales_contacto', $insert);

                    $dataSucs = $cloud->row("
                        SELECT sucursal FROM cat_sucursales WHERE flgDelete = 0 AND sucursalId =?
                    ", [$_POST["idSucursal"]]);
                    $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo contacto para la sucursal: ".$dataSucs->sucursal." (".$_POST["descripcionContacto"].": ".$_POST["contactoSucursal"]."), ");
                    echo "success";
                }else {
                    // No tiene permisos
                    // Bitacora
                    $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados e insertar un contacto de sucursal (manejo desde consola), ");
                    echo "Acción no válida.";
                }
	            
	        break;

	        case "nuevo-usuario":
	        	/*
	        		POST - Empleado:
					hiddenFormData
					typeOperation
					operation
					flgPersona
					personaId
					correo
					flgInsertCorreo

					POST - Externo:
					hiddenFormData
					typeOperation
					operation
					flgPersona
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
	        	if($_POST["flgPersona"] == "Empleado") {
	        		// Posee permiso
	        		if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(58, $_SESSION["arrayPermisos"])) {
		        		if($_POST["flgInsertCorreo"] == 1) { // No se encontró correo en RRHH			        
					        // Registrar su correo institucional en personas contacto
					        $insert = [
					            'personaId'					=> $_POST["personaId"],
					            'tipoContactoId'   			=> 1,
					            'contactoPersona'    		=> $_POST["correo"],
					            'descripcionPrsContacto'  	=> "Correo institucional agregado automáticamente al crear su usuario",
					            'visibilidadContacto'		=> "Público",
					            'estadoContacto'			=> "Activo",
					        ];
					        $cloud->insert('th_personas_contacto', $insert);	
		        		} else {
		        			// Se obtuvo el correo digitado por RRHH
		        		}
		        		$usuario = explode("@", $_POST["correo"]);
		        		$defaultPass = "alina" . date("Y") . "$";
		        		$insert = [
		        			'personaId'			=> $_POST["personaId"],
		        			'usuario'			=> strtoupper($usuario[0]),
		        			'correo'			=> $_POST["correo"],
		        			'pass'				=> password_hash(base64_encode(md5($defaultPass)), PASSWORD_BCRYPT),
		        			'estadoUsuario'		=> 'Activo',
		        		];
		        		$usuarioId = $cloud->insert("conf_usuarios", $insert);

		        		$dataNombrePersona = $cloud->row("
					        SELECT
					            CONCAT(
					                IFNULL(apellido1, '-'),
					                ' ',
					                IFNULL(apellido2, '-'),
					                ', ',
					                IFNULL(nombre1, '-'),
					                ' ',
					                IFNULL(nombre2, '-')
					            ) AS nombrePersona,
					            apellido1, 
					            nombre1
					        FROM th_personas
					        WHERE personaId = ?
		        		",[$_POST["personaId"]]);

		        		$sideName = $dataNombrePersona->nombre1 . " " . $dataNombrePersona->apellido1;

		        		// Validar si ya se le cargó foto de perfil en RRHH
		        		$poseeFotoPerfil = $cloud->count("
		        			SELECT
		        				urlPrsAdjunto
		        			FROM th_personas_adjuntos
		        			WHERE personaId = ? AND tipoPrsAdjunto = 'Foto de empleado' AND descripcionPrsAdjunto = 'Actual' AND flgDelete = '0'
		        		", [$_POST["personaId"]]);

		        		if($poseeFotoPerfil > 0) {
			        		$dataFotoPerfil = $cloud->row("
			        			SELECT
			        				urlPrsAdjunto
			        			FROM th_personas_adjuntos
			        			WHERE personaId = ? AND tipoPrsAdjunto = 'Foto de empleado' AND descripcionPrsAdjunto = 'Actual' AND flgDelete = '0'
			        			LIMIT 1
			        		", [$_POST["personaId"]]);
							$fotoPerfil = $dataFotoPerfil->urlPrsAdjunto;
		        		} else { // No posee, colocar la default
							$fotoPerfil = "mi-perfil/user-default.jpg";		        			
		        		}

				        // Insert default customs para que cuando ingrese cargue todo en la sidebar mip_perfil_custom
				        for ($i=0; $i < 4; $i++) { 
				        	if($i == 0) {
				        		$tipoCustom = "Avatar";
				        		$custom = $fotoPerfil;
				        	} else if($i == 1) {
				        		$tipoCustom = "Estado";
				        		$custom = "En línea";
				        	} else if($i == 2) {
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

						// Bitácora de usuario final / jefes
				        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Creó un nuevo usuario para el empleado: ".$dataNombrePersona->nombrePersona." (Correo: ".$_POST["correo"]."), ");

		        		echo "success^Usuario creado con éxito.<br>El empleado ".$dataNombrePersona->nombrePersona." ya puede iniciar sesión con la contraseña asignada por defecto.";
	        		} else {
	        			// No tiene permisos
	        			// Bitacora
	        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados e insertar un usuario de tipo interno (manejo desde consola), ");
	        			echo "Acción no válida.";
	        		}
	        	} else { // Distribuidor, Externo
	        		// Posee permisos
	        		if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(61, $_SESSION["arrayPermisos"])) {
						$existePersona = $cloud->count("
							SELECT 
								personaId
							FROM th_personas
							WHERE numIdentidad = ? AND flgDelete = '0'
						", [$_POST["dui"]]);
						$existeUsuario = $cloud->count("
							SELECT 
								usuarioId
							FROM conf_usuarios
							WHERE correo = ? AND flgDelete = '0'
						", [$_POST["correo"]]);
						if($existePersona == 0 && $existeUsuario == 0) {
							$sideName = $_POST["nombre1"] . " " . $_POST["apellido1"];
							// Insert persona
						    $insertPersona = [
						        'prsTipoId'   		=> $_POST["tipoPersona"],
						        'docIdentidad'		=> 'DUI',
						        'numIdentidad'      => $_POST["dui"],
						        'nombre1'   		=> $_POST["nombre1"],
						        'nombre2'           => $_POST["nombre2"],
						        'apellido1'       	=> $_POST["apellido1"],
						        'apellido2'		    => $_POST["apellido2"],
						        'fechaNacimiento'   => date("Y-m-d", strtotime($_POST["fechaNacimiento"])),
						        'sexo'	        	=> $_POST["sexo"],
						        'estadoCivil'		=> "Soltero/a",
						        'paisId'			=> 61,
						        'estadoPersona'		=> "Activo",
						    ];
						    $personaId = $cloud->insert('th_personas', $insertPersona); // retorna el ultimoId insertado

						    $defaultPass = "alina" . date("Y") . "$";
						    // Insert usuario
						    $nombreUsuario = explode("@", $_POST["correo"]);
						    $insertUsuario = [
						        'personaId'   		=> $personaId,
						        'usuario'           => strtoupper($nombreUsuario[0]),
						        'correo'   			=> $_POST["correo"],
						        'pass'           	=> password_hash(base64_encode(md5($defaultPass)), PASSWORD_BCRYPT),
						        'estadoUsuario'     => "Activo",
						    ];
						    $usuarioId = $cloud->insert('conf_usuarios', $insertUsuario); // retorna el ultimoId insertado

					        // Registrar su correo en personas contacto
					        $insert = [
					            'personaId'					=> $personaId,
					            'tipoContactoId'   			=> 1,
					            'contactoPersona'    		=> $_POST["correo"],
					            'descripcionPrsContacto'  	=> "Correo personal agregado automáticamente al crear su usuario",
					            'visibilidadContacto'		=> "Público",
					            'estadoContacto'			=> "Activo",
					        ];
					        $cloud->insert('th_personas_contacto', $insert);  

					        // Insert default customs para que cuando ingrese cargue todo en la sidebar mip_perfil_custom
					        for ($i=0; $i < 4; $i++) { 
					        	if($i == 0) {
					        		$tipoCustom = "Avatar";
					        		$custom = "mi-perfil/user-default.jpg";
					        	} else if($i == 1) {
					        		$tipoCustom = "Estado";
					        		$custom = "En línea";
					        	} else if($i == 2) {
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

					        // Bitácora de usuario final / jefes
			        		$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Creó un nuevo usuario para la persona: ".$_POST["apellido1"].", ".$_POST["nombre1"]." (Correo: ".$_POST["correo"]."), ");
					        echo "success^Usuario creado con éxito.<br>La persona ".$_POST["apellido1"] . ", " . $_POST["nombre1"]." ya puede iniciar sesión con la contraseña asignada por defecto.";
						} else {
							echo "La persona " . $_POST["apellido1"] . ", " . $_POST["nombre1"] . " ya cuenta con credenciales asignadas. Verificar la información que se ha ingresado.";
						}
	        		} else {
	        			// No tiene permisos
	        			// Bitacora
	        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados e insertar un usuario de tipo externo (manejo desde consola), ");
	        			echo "Acción no válida.";
	        		}
	        	}
	        break;

	        case "enfermedad-alergia":
				/*
					POST
					hiddenFormData = nuevo
					typeOperation
					operation
					tipoEnfermedad
					nombreEnfermedad
				*/
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
	        		echo "success";
				} else {
					// Ya existe en el catálogo, mostrar aviso
					echo "La " . $_POST["tipoEnfermedad"] . " - " . $_POST["nombreEnfermedad"] . " ya existe en el catálogo";
				}
	        break;

	        case "organizacion":
				/*
					POST
					hiddenFormData = nuevo
					typeOperation
					operation
					tipoOrganizacion
					nombreOrganizacion
					abreviaturaOrganizacion
				*/
				// Verificar sino existe en el catalogo
				$queryOrganizacionDuplicada = "
					SELECT 
						nombreOrganizacionId
					FROM cat_nombres_organizaciones
					WHERE tipoOrganizacion = ? AND abreviaturaOrganizacion = ? AND flgDelete = '0'
				";
				$existeDuplicado = $cloud->count($queryOrganizacionDuplicada, [$_POST["tipoOrganizacion"], $_POST["abreviaturaOrganizacion"]]);
				if($existeDuplicado == 0) {
					// insert catalogo enfermedades para que la proxima vez pueda seleccionarlo
					$insert = [
						'tipoOrganizacion'			=> $_POST["tipoOrganizacion"],
						'nombreOrganizacion'		=> $_POST["nombreOrganizacion"],
						'abreviaturaOrganizacion'	=> $_POST["abreviaturaOrganizacion"]
					];
					$nombreOrganizacionId = $cloud->insert('cat_nombres_organizaciones', $insert);	
					// Bitácora de usuario final / jefes
	        		$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva organización al catálogo: " . $_POST["nombreOrganizacion"] . " (".$_POST["tipoOrganizacion"]." - Abreviatura: ".$_POST["abreviaturaOrganizacion"]."), ");	
	        		echo "success";
				} else {
					// Ya existe en el catálogo, mostrar aviso
					echo "La organización " . $_POST["nombreOrganizacion"] . " ya existe en el catálogo";
				}
	        break;

	        case "cargo-persona":
				/*
					POST
					hiddenFormData = nuevo
					typeOperation
					operation
					cargoPersona
					descripcionCargoPersona
					funcionCargoPersona
					herramientasCargoPersona
				*/
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
						'funcionCargoPersona' 		=> $_POST['funcionCargoPersona'],
						'herramientasCargoPersona' 	=> $_POST['herramientasCargoPersona']
					];
					$prsCargoId = $cloud->insert('cat_personas_cargos', $insert);	
					// Bitácora de usuario final / jefes
	        		$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo cargo al catálogo: " . $_POST["cargoPersona"] . " (Descripción: ".$_POST["descripcionCargoPersona"]."), ");	
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
					WHERE codSucursalDepartamento = ? AND sucursalId = ? AND flgDelete = '0'
				";
				$existeDuplicado = $cloud->count(
					$queryDepartamentoDuplicado, 
					[$_POST["codSucursalDepartamento"], $_POST['hiddenFormData']]
				);
				if($existeDuplicado == 0) {
					$insert = [
						'sucursalId'				=> $_POST["hiddenFormData"],
						'codSucursalDepartamento'	=> $_POST["codSucursalDepartamento"],
						'departamentoSucursal'		=> $_POST["departamentoSucursal"]
					];
					$sucursalDepartamentoId = $cloud->insert('cat_sucursales_departamentos', $insert);	

				    $dataNombreSucursal = $cloud->row("
				        SELECT
				            sucursal
				        FROM cat_sucursales
				        WHERE sucursalId = ?
				    ", [$_POST['hiddenFormData']]); 

					// Bitácora de usuario final / jefes
	        		$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo departamento: " . $_POST["departamentoSucursal"] . " (Código del departamento: ".$_POST["codSucursalDepartamento"]." - Sucursal: ".$dataNombreSucursal->sucursal."), ");	
	        		echo "success";
				} else {
					// Ya existe en el catálogo, mostrar aviso
					echo "El departamento " . $_POST["departamentoSucursal"] . " (".$_POST["codSucursalDepartamento"].") ya ha sido creado en esta sucursal.";
				}
	        break;

	        case 'crud':
	        	/**
	        	 POST:
	        		typeOperation
					operation
					nombreCrud
					moduloId
					descripcion 
	        	*/
	        	$insert = [
					'nombreCrud'  => $_POST["nombreCrud"],
					'moduloId'    => $_POST["moduloId"],
					'flgDelete'   => 0,
					'descripcion' => $_POST["descripcion"]
	        	];
	        	
	        	$cloud->insert('ejemplo_crud',$insert);

	        	$datModulo = $cloud->row("
	        		SELECT 
	        			modulo 
	        		FROM conf_modulos
	        		WHERE moduloId = ?
	        	", [$_POST['moduloId']]);
	        	
	        	// Bitácora de usuario final / jefes
	        	$cloud->writeBitacora("movInsert", "(".$fhActual.") Ingresó un nuevo crud: ".$_POST['nombreCrud']." Pertenece al Módulo: ".$datModulo->modulo);
	        	
	        	echo "success";
	        break;

	        case 'tipo-producto':
	        	/**
				 * POST:
					hiddenFormData: insert
					typeOperation
					operation
				*/
				$queryExist = "
	        		SELECT 
	        			nombreTipoProducto
	        		FROM cat_inventario_tipos_producto
	        		WHERE nombreTipoProducto = ? AND flgDelete = 0
	        	";
	        	$existe = $cloud->count($queryExist,[$_POST["nombreTipoProducto"]]);
	        	if ($existe==0) {
	        		$insert = [
						'nombreTipoProducto' => $_POST['nombreTipoProducto']
					];

					$cloud->insert('cat_inventario_tipos_producto',$insert);
					$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo Tipo de producto: ".$_POST["nombreTipoProducto"].", ");

					echo "success";
	        	}else{
	        		echo "El tipo de producto: ".$_POST["nombreTipoProducto"]." ya existe en el catálogo.";
	        	}
	        break;

	        case 'bodega-sucursal':
	        	/**
				 * POST:
					hiddenFormData: insert
					typeOperation
					operation
				*/
				$queryExist = "
	        		SELECT 
	        			bodegaSucursal
	        		FROM cat_sucursales_bodegas
	        		WHERE codSucursalBodega = ? AND bodegaSucursal = ? AND flgDelete = 0
	        	";
	        	$existe = $cloud->count($queryExist,[$_POST["codSucursalBodega"],$_POST["bodegaSucursal"]]);
	        	if ($existe==0) {
	        		$insert = [
						'sucursalId'        => $_POST['sucursalId'],
						'codSucursalBodega' => $_POST['codSucursalBodega'],
						'bodegaSucursal'    => $_POST['bodegaSucursal']
					];

					$datSucursal = $cloud->row("
		        		SELECT 
		        			sucursal 
		        		FROM cat_sucursales
		        		WHERE sucursalId = ?
		        	", [$_POST['sucursalId']]);

					$cloud->insert('cat_sucursales_bodegas',$insert);
					$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva bodega: ".$_POST["bodegaSucursal"].", en la sucursal: ".$datSucursal->sucursal);

					echo "success";
	        	}else{
	        		echo "La bodega: ".$_POST["bodegaSucursal"]." ya existe en esta sucursal.";
	        	}
	        break;

	        case 'ubicacion-bodega':
	        	/**
				 * POST:
					hiddenFormData: insert
					typeOperation
					operation
					bodegaId
					codigoUbicacion
					nombreUbicacion
					
					enviar estos alters
					ALTER TABLE `alinac_cloud`.`inv_ubicaciones` MODIFY COLUMN `ubicacionSuperiorId` BIGINT(20) UNSIGNED DEFAULT 0;
					ALTER TABLE `alinac_cloud`.`inv_ubicaciones` ADD COLUMN `orden` VARCHAR(45) AFTER `ubicacionSuperiorId`;
					ALTER TABLE `alinac_cloud`.`inv_ubicaciones` MODIFY COLUMN `orden` INT(10) UNSIGNED ZEROFILL DEFAULT NULL;


				*/
			if (isset($_POST["ubicacionSuperiorId"])) {
				// Insert de subnivel
				$queryExist = "
	        		SELECT 
	        			nombreUbicacion
	        		FROM inv_ubicaciones
	        		WHERE (codigoUbicacion = ? OR nombreUbicacion = ?) AND ubicacionSuperiorId = ? AND flgDelete = 0
	        	";
	        	$existe = $cloud->count($queryExist,[$_POST["codigoUbicacion"],$_POST["nombreUbicacion"],$_POST["ubicacionSuperiorId"]]);

	        	$insert = [
					'bodegaId'            => $_POST['bodegaId'],
					'codigoUbicacion'     => $_POST['codigoUbicacion'],
					'nombreUbicacion'     => $_POST['nombreUbicacion'],
					'ubicacionSuperiorId' => $_POST["ubicacionSuperiorId"],
					'nivel'               => $_POST["nivel"],
					'orden'               => $_POST["orden"]
				];

				$msj = "(Subnivel)";

			}else{
				//Nivel 1 => superior
				$queryExist = "
	        		SELECT 
	        			nombreUbicacion
	        		FROM inv_ubicaciones
	        		WHERE (codigoUbicacion = ? OR nombreUbicacion = ?) AND flgDelete = 0
	        	";
	        	$existe = $cloud->count($queryExist,[$_POST["codigoUbicacion"],$_POST["nombreUbicacion"]]);


	        	//Generar correlativo de orden
	        	$insert = [
					'bodegaId'        => $_POST['bodegaId'],
					'codigoUbicacion' => $_POST['codigoUbicacion'],
					'nombreUbicacion' => $_POST['nombreUbicacion'],
					'nivel'           => 1,
					'orden'           => '01'
				];

				$msj = "";

			}

			if ($existe==0) {

				$datBodega = $cloud->row("
	        		SELECT 
	        			bodegaSucursal 
	        		FROM cat_sucursales_bodegas
	        		WHERE bodegaId = ?
	        	", [$_POST['bodegaId']]);

				$cloud->insert('inv_ubicaciones',$insert);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva ubicación: ".$msj." ".$_POST["nombreUbicacion"].", en la bodega: ".$datBodega->bodegaSucursal);

				echo "success";
        	}else{
        		echo "La Ubicación: ".$msj." ".$_POST["codigoUbicacion"]." - ".$_POST["nombreUbicacion"].", ya existe en esta bodega.";
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
					WHERE abreviaturaPais = ? OR telefonoCodPais = ? AND flgDelete = ?
				", [$_POST["abreviaturaPais"], $_POST["telefonoCodPais"], 0]);

				$existeCodigoMH = $cloud->count("
				SELECT pais FROM cat_paises
				WHERE codigoMH = ? AND flgDelete = ?
			", [$_POST['codigoMH'], 0]);

				if($existePais == 0 && $existeCodigoMH ==0 ) {
					$insert = [
						"pais" 					=> $_POST["pais"],
						"abreviaturaPais" 		=> $_POST["abreviaturaPais"],
						"iconBandera" 			=> $_POST["iconBandera"],
						"telefonoCodPais"		=> $_POST["telefonoCodPais"],
						"codigoMH" 				=> $_POST["codigoMH"]
					];
					$cloud->insert("cat_paises", $insert);
					$cloud->writeBitacora("movInsert", "(".$fhActual.") Agregó un nuevo país: $_POST[pais]");

					echo "success";
				} else {
					if($existePais > 0) {
						echo "El País $_POST[pais] ya fue registrado, verifique la información";
					} else {
						echo "El código de Hacienda $_POST[codigoMH] ya fue asignado";
					}
				}
			break;
			case 'departamento':
				/*
				POST:
				paisId,
				departamentoPais
				*/ 
				$existeDepartamento = $cloud->count("
					SELECT paisDepartamentoId FROM cat_paises_departamentos 
					WHERE departamentoPais = ? AND paisId = ? AND flgDelete = ? "
					, [$_POST["departamentoPais"], $_POST["paisId"], 0]);

					$existeCodigoMH = $cloud->count("
					SELECT DepartamentoPais FROM cat_paises_departamentos
					WHERE codigoMH = ? AND flgDelete = ?
				", [$_POST['codigoMH'], 0]);
			
				if ($existeDepartamento == 0 && $existeCodigoMH ==0 ) {
					$insert = [
						"paisId" 			    => $_POST["paisId"],
						"departamentoPais" 		=> $_POST["departamentoPais"],
						"codigoMH" 				=> $_POST["codigoMH"]
					];
					$cloud->insert("cat_paises_departamentos", $insert);
					$cloud->writeBitacora("movInsert", "(".$fhActual.") Agregó un nuevo DEPARTAMENTO: $_POST[departamentoPais]");
					echo "success";
				} else {
					if($existeDepartamento > 0) {
						echo "El Departamento $_POST[departamentoPais] ya fue registrado, verifique la información";
					} else {
						echo "El código de Hacienda $_POST[codigoMH] ya fue asignado";
					}
				}
			break;

			case 'municipio':
				/*
				POST:
				paisDepartamentoId,
				municipioPais
				*/ 
				$existeMunicipio = $cloud->count("
					SELECT paisMunicipioId FROM cat_paises_municipios 
					WHERE municipioPais = ? AND paisDepartamentoId = ? AND flgDelete = ? "
					, [$_POST["municipioPais"], $_POST["paisDepartamentoId"], 0]); 

					$existeCodigoMH = $cloud->count("
					SELECT municipioPais FROM cat_paises_municipios
					WHERE codigoMH = ? AND flgDelete = ?
				", [$_POST['codigoMH'], 0]);
			
				if ($existeMunicipio == 0 && $existeCodigoMH ==0 ) {
					$insert = [
						"paisDepartamentoId" 	=> $_POST["paisDepartamentoId"],
						"municipioPais" 		=> $_POST["municipioPais"],
						"codigoMH" 				=> $_POST["codigoMH"]
					];
					$cloud->insert("cat_paises_municipios", $insert);
					$cloud->writeBitacora("movInsert", "(".$fhActual.") Agregó un nuevo municipio: $_POST[municipioPais]");
					echo "success";
				} else {
					if($existeMunicipio > 0) {
						echo "El Municipio $_POST[municipioPais] ya fue registrado, verifique la información";
					} else {
						echo "El código de Hacienda $_POST[codigoMH] ya fue asignado";
					}
				}
				break;
			case "nueva-persona-sucursal":
				foreach($_POST["selectSucursal"] as $sucursal){
					foreach($_POST["selectPersonas"] as $personaId) {
						$existePersona = $cloud->count("
						SELECT 
							personaId 
						FROM conf_personas_sucursales
						WHERE sucursalId = ? AND personaId = ? AND flgDelete = ?
					", [$sucursal, $personaId, 0]);
						if($existePersona == 0) {
							$insert = [
								"personaId" 	=> $personaId,
								"sucursalId" 	=> $sucursal
							];
							$cloud->insert("conf_personas_sucursales", $insert);
							$cloud->writeBitacora("movInsert", "(".$fhActual.") Se agregaron personas a la sucursal: $sucursal");
							
						} else {
							// Ya existe la persona en la sucursal, omitir este insert
						}
					}
				}

				echo "success";
			break;
			case "permisos-DTE":
				foreach($_POST['tipoDTE'] as $tipoDTE){
						$existeDTE = $cloud->count("
							SELECT 
								tipoDTEId 
							FROM conf_personas_sucursales_dte
							WHERE tipoDTEId = ? AND flgDelete = ?
						", [$tipoDTE, 0]);
						if($existeDTE == 0){
							$insert = [
								"personaSucursalId" 	=> $_POST['personaSucursalId'],
								"tipoDTEId" 			=> $tipoDTE
							];
							$cloud->insert("conf_personas_sucursales_dte", $insert);
							$cloud->writeBitacora("movInsert", "(".$fhActual.") Se agregó el permiso: $tipoDTE a la persona: $_POST[personaSucursalId]");							
						
						}else{
							//Ya existe el DTE 
						}
					}
				echo "success";
			break;

			case "proveedores-carga":

				$fechaFile = date("Y-m-d");

				$archivoNombre = "(Proveedores $fechaFile) " . $_FILES['adjunto']['name'];

				$filename = "../../../../libraries/resources/files/txt/compras/proveedores/";

				// Crear la carpeta inventario
				if(!file_exists("../../../../libraries/resources/files/txt/compras/")) {
					mkdir("../../../../libraries/resources/files/txt/compras/", 0755);
				} 
				// Crear la carpeta inventario > validacion-productos
				if (!file_exists($filename)) {
					mkdir($filename, 0755);
				} 

				$ubicacion = $filename . $archivoNombre; 
				$flgSubir = 1;
				$archivoFormato = pathinfo($ubicacion,PATHINFO_EXTENSION);

				$formatosPermitidos = array("txt");

				if(!in_array(strtolower($archivoFormato),$formatosPermitidos)) {
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
					$originalNombre = $archivoNombre;
					while($n > 0) {
						if(file_exists($ubicacion)) {
							$archivoNombre = "($n) $originalNombre";
							$ubicacion = $filename . $archivoNombre;
							$n += 1;
						} else {
							// No existe, se mantiene el flujo normal
							$n = 0;
						}
					}
					
					/* Upload file */
					if(move_uploaded_file($_FILES['adjunto']['tmp_name'],$ubicacion)) {
						$n = 0;

						// Iterar cada FILA del archivo
						$file = new SplFileObject($ubicacion);
						while (!$file->eof()) {
							$n += 1;
							$row = $file->fgets();
							if($row <> '') {
								// Vienen separadas por un tab, hacer explode
						        $columnasArchivo = explode("\t", $row);

								// var_dump($columnasArchivo);
								// Importación de códigos
								// trim para quitar espacios de más y utf8 para caracteres especiales
								$codMagic 	= trim(utf8_decode($columnasArchivo[0]));
								$Nombre 	= trim(utf8_decode($columnasArchivo[1]));
								$Nombre 	= str_replace("'", " ", $Nombre);
								$Direccion = trim(utf8_decode($columnasArchivo[2]));
								$Direccion = str_replace("'", " ", $Direccion);
								$NRC 	= trim(utf8_decode($columnasArchivo[3]));
								// $Giro 	=	trim(utf8_decode($columnasArchivo[4]));
								// $Categoria 	= trim(utf8_decode($columnasArchivo[5]));
								$Municipio	= trim(utf8_decode($columnasArchivo[6]));
								$Departamento = trim(utf8_decode($columnasArchivo[9]));
								$Telefono1 = trim(utf8_decode($columnasArchivo[10]));
								$Telefono2 = trim(utf8_decode($columnasArchivo[11]));
								$TipoProveedor = trim(utf8_decode($columnasArchivo[10]));
								// $NIT = trim(utf8_decode($columnasArchivo[11]));
/* 								$Abonos = trim(utf8_decode($columnasArchivo[12]));;
								$SaldoActual = trim(utf8_decode($columnasArchivo[13]));
								$FechaUltimoPago = trim(utf8_decode($columnasArchivo[14]));
								$FechaProximoPago = trim(utf8_decode($columnasArchivo[15]));
								$CodigoContable = trim(utf8_decode($columnasArchivo[16]));
								$ValorProximoPago = trim(utf8_decode($columnasArchivo[17]));
								$UltimoPedidoPagado = trim(utf8_decode($columnasArchivo[18])); */
								$Tipo = trim(utf8_decode($columnasArchivo[21])); 
								$doc = 	trim(utf8_decode($columnasArchivo[22]));
								// $Cargos = trim(utf8_decode($columnasArchivo[21]));
								// $SaldoInicial = trim(utf8_decode($columnasArchivo[22]));
								
								
								$dataProductoIdImportado = $cloud->row("
									SELECT codProveedorMagic FROM comp_proveedores
									WHERE codProveedorMagic = ? 
									LIMIT 1
								", [$codMagic]);

								if ($Tipo == '1'){
									$tipoDocumento = 'NIT';
									$tipoDocumentoMH = 1;
									$tipoProveedor = 'Empresa local';
								} else {
									$tipoDocumento = 'DUI';
									$tipoDocumentoMH = 2;
									$tipoProveedor = 'Empresa local';
								}
								
								$paises = array('ALEMANIA', 'BRASIL', ' U.S.A.', 'SUIZA', 'ILLINOIS', 'MEXICO', 'ENGLAND', 'VENEZUELA', 'USA', 'INGLATERRA','GUATENALA', 'EQUADOR', 'CANADA', 'ITALIA', 'COSTA RICA', 'HONG KONG', 'ESPANA', 'CHINA', 'NICARAGUA', 'COLOMBIA', 'PANAMA');

								if (in_array($Municipio, $paises) || in_array($Departamento, $paises)){
									$tipoProveedor = 'Empresa extranjera';
								}

								// echo $Telefono1 . ' ' . $Telefono2 . ' ' . $Municipio . ' ' . $Departamento . '<br>';
								if(!is_object($dataProductoIdImportado)) {

									$insert = [
										// "tipoPersonaMH" 			=> "",
										"tipoProveedor" 			=> $tipoProveedor,
										"nrcProveedor" 				=> $NRC,
										"tipoDocumentoMH" 			=> $tipoDocumentoMH,
										"tipoDocumento" 			=> $tipoDocumento,
										"numDocumento" 				=> $doc,
										"nombreProveedor" 			=> $Nombre,
										// "nombreComercial" 			=> "",
										// "descripcionExtranjero" 	=> "",
										"codProveedorMagic"			=> $codMagic,
										// "actividadEconomicaId"		=> "",
										"estadoProveedor"			=> "Activo"
									];

									$proveedorId = $cloud->insert("comp_proveedores", $insert);

									
									if ($Direccion !== ''){
										$paisMuni = NULL;
										if ($Municipio !== ''){
	
											$checkMuni = $cloud->row('SELECT m.paisMunicipioId, m.municipioPais 
											FROM cat_paises_municipios m
											JOIN cat_paises_departamentos d ON d.paisDepartamentoId = m.paisDepartamentoId
											WHERE m.municipioPais = ?', [$Municipio]);
	
											if (is_object($checkMuni)){
												$paisMuni = $checkMuni->paisMunicipioId;
											} 
										}
										$insertUbi = [
											"proveedorId" 					=> $proveedorId,
											"nombreProveedorUbicacion" 		=> "DIRECCION PRINCIPAL",
											"direccionProveedorUbicacion" 	=> $Direccion,
											"estadoProveedorUbicacion" 		=> "Activo",
											"paisMunicipioId"				=> $paisMuni
										];
	
										$ubicacionID = $cloud->insert("comp_proveedores_ubicaciones", $insertUbi);
	
										if ($Telefono2 !== ''){
											$insertContacto = [
												"proveedorUbicacionId" 			=> $ubicacionID,
												"tipoContactoId" 				=> 10,
												"contactoProveedor" 			=> $Telefono2,
												"descripcionProveedorContacto" 	=> "Teléfono de contacto",
											];
		
											$cloud->insert("comp_proveedores_contactos", $insertContacto);
										}
									}


								} 
						    
							}
						} // while file->eof

						echo "success";
					} else {
						echo "Problema al cargar el archivo. Por favor comuníquese con el Equipo de Desarrollo.";
					}
				} 
				// echo "success";
			break;
			case 'sucursales-empleado':
	        	/*
				typeOperation: insert
				operation: sucursales-empleado
				personaId: 11
				sucursales: 7
				tblSucursales_length:  
	        	*/
	        	$insert = [
					'personaId'  => $_POST["personaId"],
					'sucursalId'    => $_POST["sucursales"]
	        	];
	        	
	        	$cloud->insert('conf_personas_sucursales',$insert);
	        	
	        	// Bitácora de usuario final / jefes
	        	$cloud->writeBitacora("movInsert", "(".$fhActual.") Ingresó una nueva Sucursal a la persona: ".$_POST['personaId']);
	        	
	        	echo "success";
	        break;
			case 'bodegas-empleado':
	        	/*
					POST:
					personaId
					bodegas[]  
	        	*/
	        	foreach ($_POST['bodegas'] as $bodegaId) {
	        		// Buscar la sucursal con la bodega
	        		$dataSucursalId = $cloud->row("
	        			SELECT sucursalId FROM cat_sucursales_bodegas
	        			WHERE bodegaId = ? AND flgDelete = ?
	        		", [$bodegaId, 0]);

	        		// Buscar el permiso con la sucursal
	        		// No se debe validar nada más, porque el input select que muestra las bodegas, ya está limitado por los permisos en la sucursal
					$dataPersonaSucursal = $cloud->row("
						SELECT personaSucursalId FROM conf_personas_sucursales
						WHERE personaId = ? AND sucursalId = ? AND flgDelete = ?
					", [$_POST['personaId'], $dataSucursalId->sucursalId, 0]);	        		

		        	$insert = [
						'personaSucursalId'  => $dataPersonaSucursal->personaSucursalId,
						'bodegaId'    => $bodegaId
		        	];
		        	
		        	$cloud->insert('conf_personas_sucursales_bodegas',$insert);
	        	}
	        	
	        	// Bitácora de usuario final / jefes
	        	$cloud->writeBitacora("movInsert", "(".$fhActual.") Asignó bodega(s) a la persona: ".$_POST['personaId']);
	        	
	        	echo "success";
	        break;

			case 'cotizacionesCorrelativo':
				$dataExiste = $cloud->row("
					SELECT 
						tipoCorrelativo,
						origenCorrelativo,
						origenCorrelativoId
					FROM fel_correlativo_cotizacion 
					WHERE flgDelete = ? AND estadoCorrelativo = ? AND tipoCorrelativo = ? AND origenCorrelativo = ? AND origenCorrelativoId = ?
				", [0,"Activo",$_POST['tipoCorrelativo'], $_POST['origenCorrelativo'], $_POST['sucursalId']]);

				if($dataExiste){
					echo "Ya existe un registro.";
				} else {
					$insert = [
						'tipoCorrelativo'    	=> $_POST['tipoCorrelativo'],
						'origenCorrelativo'		=> $_POST['origenCorrelativo'],
						'origenCorrelativoId'	=> $_POST['sucursalId'],
						'anio'					=> DATE('Y'),
						'correlativoActual'		=> 1,
						'estadoCorrelativo'		=> 'Activo'
					];
					
					$cloud->insert('fel_correlativo_cotizacion',$insert);
					$cloud->writeBitacora("movInsert", "(".$fhActual.") Se agrego el correlativo ");
				
					echo "success";
				}
			break;

			case "exportar-pagos-bonos-magic":
				/*
					POST:
					periodoBonoId
				*/
				require_once("../../../../libraries/includes/logic/mgc/datos24.php");

				$dataPeriodo = $cloud->row("
					SELECT mes, anio, fechaPagoBono FROM conta_periodos_bonos
					WHERE periodoBonoId = ? AND flgDelete = ?
				", [$_POST['periodoBonoId'], 0]);

	            $dataBonosEmpleado = $cloud->rows("
	                SELECT 
	                    bpd.personaId AS personaId,
	                    SUM(pb.montoBono) AS totalMontoBono
	                FROM conta_planilla_bonos pb
	                JOIN conf_bonos_personas_detalle bpd ON bpd.bonoPersonaDetalleId = pb.bonoPersonaDetalleId
	                WHERE pb.periodoBonoId = ? AND pb.flgDelete = ?
	                GROUP BY bpd.personaId
	            ", [$_POST['periodoBonoId'], 0]);

	            foreach($dataBonosEmpleado as $bonoEmpleado) {
	            	$existePagoBono = $magic->row("
	            		SELECT planillaBonificacionId FROM conta_planilla_bonificaciones
	            		WHERE mes = ? AND anio = ? AND personaId = ?
	            	", [$dataPeriodo->mes, $dataPeriodo->anio, $bonoEmpleado->personaId]);

	            	if($existePagoBono) {
	            		$update = [
	            			"bonificacionTotal" 		=> $bonoEmpleado->totalMontoBono
	            		];
	            		$where = ["planillaBonificacionId" => $existePagoBono->planillaBonificacionId];
	            		$magic->update("conta_planilla_bonificaciones", $update, $where);
	            	} else {	            		
		            	$insertMagic = [
		            		"mes" 						=> $dataPeriodo->mes,	
		            		"anio" 						=> $dataPeriodo->anio,	
		            		"personaId" 				=> $bonoEmpleado->personaId,	
		            		"bonificacionTotal" 		=> $bonoEmpleado->totalMontoBono
		            	];
		            	$planillaBonificacionId = $magic->insert("conta_planilla_bonificaciones", $insertMagic);
	            	}
	            }
				$insertBitacora = [
					"descripcionExportacion" 			=> "conta_planilla_bonos", 
					"personaId" 						=> $_SESSION['personaId'], 
					"fhExportacion" 					=> date("Y-m-d H:i:s")
				];
				$bitExportacionMagicId = $cloud->insert("bit_exportaciones_magic", $insertBitacora);

				echo "success";
			break;

			case "exportar-pagos-comisiones-magic":
				/*
					POST:
					comisionPagarPeriodoId
				*/
				require_once("../../../../libraries/includes/logic/mgc/datos24.php");

				$dataPeriodoComision = $cloud->row("
					SELECT 
						numMes, anio
					FROM conta_comision_pagar_periodo
					WHERE comisionPagarPeriodoId = ? AND flgDelete = ?
				", [$_POST['comisionPagarPeriodoId'], 0]);

				$empleadosComisionan = array(
					"ALEJANDRO BOTON DE LEON",
					"ALFREDO COLORADO",
					"ALEXANDER HERNANDEZ",
					"BRIAN DOMINGUEZ",
					"CARLOS RIVERA",
					"FRANCISCO RODRIGUEZ",
					"GUADALUPE DE DIAS",
					"GREGORIO HERNANDEZ",
					"KELVIN ARIAS",
					"LEONARDO BENAVIDES",
					"LEONEL BATRES",
					"LUIS NAVAS",
					"MARLON LOPEZ",
					"OSCAR CONTRERAS",
					"ROBERTO AMAYA",
					"RODOLFO HENRIQUEZ",
					"SALVADOR DIAZ",
					"SERGIO LOVO"
				);
				
				$empleadosComisionanImplode = implode(",", array_map(function($nombre) {
				    return "'" . addslashes($nombre) . "'"; // `addslashes` escapa caracteres especiales
				}, $empleadosComisionan));

				$dataVendedoresComision = $cloud->rows("
					SELECT 
						codEmpleado,
						codVendedor,
					    nombreEmpleado
					FROM conta_comision_pagar_calculo
					WHERE comisionPagarPeriodoId = ? AND flgDelete = ? AND nombreEmpleado IN ($empleadosComisionanImplode)
					GROUP BY nombreEmpleado
					ORDER BY nombreEmpleado
				", [$_POST['comisionPagarPeriodoId'], 0]);
				$n = 0;
				$totalGeneralContado = 0;
				$totalGeneralAbono = 0;
				$totalGeneralCompartida = 0;
				$totalGeneral = 0;
				foreach($dataVendedoresComision as $dataVendedoresComision) {
					$n++;

					// Sumar las comisiones normales
					$dataComisionVendedorVenta = $cloud->row("
						SELECT 
							SUM(
						        CASE 
						            WHEN flgComisionEditar = '1' THEN comisionPagarEditar 
						            ELSE comisionPagar 
						        END
						    ) AS totalVendedorComision
						FROM conta_comision_pagar_calculo
						WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = 'F' AND flgDelete = '0'
					", [$_POST['comisionPagarPeriodoId'], $dataVendedoresComision->nombreEmpleado]);

					$totalVendedorComision = $dataComisionVendedorVenta->totalVendedorComision;
					// Verificar si $totalVendedorVenta no es nulo y es un número antes de aplicar number_format
					if (isset($totalVendedorComision) && is_numeric($totalVendedorComision)) {
					    $totalVendedorComisionFormat = number_format($totalVendedorComision, 2, '.', ',');
					} else {
					    // Manejo de casos donde $totalVendedorVenta es null o no es un número
					    $totalVendedorComision = 0;
					    $totalVendedorComisionFormat = "0.00";
					}

					$totalVendedorComisionAbono = 0;
					$dataComisionVendedorAbono = $cloud->rows("
						SELECT 
						    comisionAbonoPagar,
						    flgComisionEditar,
						    comisionPagarEditar
						FROM conta_comision_pagar_calculo
						WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = 'A' AND flgDelete = '0'
						GROUP BY nombreEmpleado, correlativoFactura, tipoFactura, fechaAbono, totalAbono, flgRepetidoDiferente
					", [$_POST['comisionPagarPeriodoId'], $dataVendedoresComision->nombreEmpleado]);
					foreach ($dataComisionVendedorAbono as $dataComisionVendedorAbono) {
						$totalVendedorComisionAbono += ($dataComisionVendedorAbono->flgComisionEditar == '1') ? $dataComisionVendedorAbono->comisionPagarEditar : $dataComisionVendedorAbono->comisionAbonoPagar;
					}

				    $dataComisionesCompartidas = $cloud->row("
				        SELECT 
				        	SUM(ccomp.comisionCompartidaPagar) AS comisionCompartidaPagar
				        FROM conta_comision_compartida_calculo ccomp
				        JOIN conta_comision_pagar_calculo cc ON cc.comisionPagarCalculoId = ccomp.comisionPagarCalculoId
				        JOIN conta_comision_compartida_parametrizacion_detalle cparamd ON cparamd.comisionCompartidaParamDetalleId = ccomp.comisionCompartidaParamDetalleId
				        WHERE cc.comisionPagarPeriodoId = ? AND cparamd.nombreEmpleado = ? AND ccomp.flgDelete = ?
				    ", [$_POST['comisionPagarPeriodoId'], $dataVendedoresComision->nombreEmpleado, 0]);

				    $totalComisionCompartida = $dataComisionesCompartidas->comisionCompartidaPagar;

				    $totalComisionVendedor = $totalVendedorComision + $totalVendedorComisionAbono + $totalComisionCompartida;

				    $existeComisionMagic = $magic->row("
	            		SELECT planillaComisionId FROM conta_planilla_comisiones
	            		WHERE mes = ? AND anio = ? AND mgCodEmpleado = ? AND mgCodVendedor = ?
				    ", [
				    	$dataPeriodoComision->numMes,
				    	$dataPeriodoComision->anio,
				    	$dataVendedoresComision->codEmpleado,
				    	$dataVendedoresComision->codVendedor
				    ]);

				    if($existeComisionMagic) {
				    	$updateMagic = [
							"comisionContado" 			=> $totalVendedorComision,
							"comisionAbono"	 			=> $totalVendedorComisionAbono,
							"comisionCompartida" 		=> $totalComisionCompartida,
							"comisionTotal" 			=> $totalComisionVendedor
				    	];
				    	$whereMagic = ["planillaComisionId" => $existeComisionMagic->planillaComisionId];
				    	$magic->update("conta_planilla_comisiones", $updateMagic, $whereMagic);
				    } else {				    	
					    $insertMagic = [
							"mes" 						=> $dataPeriodoComision->numMes,
							"anio" 						=> $dataPeriodoComision->anio,	
							"mgCodEmpleado" 			=> $dataVendedoresComision->codEmpleado,
							"mgCodVendedor" 			=> $dataVendedoresComision->codVendedor,	
							"mgNombreVendedor" 			=> $dataVendedoresComision->nombreEmpleado,
							"comisionContado" 			=> $totalVendedorComision,
							"comisionAbono"	 			=> $totalVendedorComisionAbono,
							"comisionCompartida" 		=> $totalComisionCompartida,
							"comisionTotal" 			=> $totalComisionVendedor
					    ];
					    $planillaComisionId = $magic->insert("conta_planilla_comisiones", $insertMagic);
				    }

					$totalGeneralContado += $totalVendedorComision;
					$totalGeneralAbono += $totalVendedorComisionAbono;
					$totalGeneralCompartida += $totalComisionCompartida;
					$totalGeneral += $totalComisionVendedor;
				}

				$insertBitacora = [
					"descripcionExportacion" 			=> "conta_comision_pagar_calculo", 
					"personaId" 						=> $_SESSION['personaId'], 
					"fhExportacion" 					=> date("Y-m-d H:i:s")
				];
				$bitExportacionMagicId = $cloud->insert("bit_exportaciones_magic", $insertBitacora);

				echo "success";
			break;

			case "exportar-pagos-bonos-cuentas-magic":
				/*
					POST:
					periodoBonoId
					fechaCuenta
				*/
				require_once("../../../../libraries/includes/logic/mgc/datos24.php");

				$dataPeriodo = $cloud->row("
					SELECT mes, anio, fechaPagoBono FROM conta_periodos_bonos
					WHERE periodoBonoId = ? AND flgDelete = ?
				", [$_POST['periodoBonoId'], 0]);

	            $dataBonosEmpleado = $cloud->rows("
					SELECT 
						bpd.personaId AS personaId,
					    cb.numCuentaContable AS numCuentaContable,
					    cb.obsCuentaContable AS obsCuentaContable,
						pb.montoBono AS montoCuenta
					FROM conta_planilla_bonos pb
					JOIN conf_bonos_personas_detalle bpd ON bpd.bonoPersonaDetalleId = pb.bonoPersonaDetalleId
					JOIN conta_cuentas_bonos cb ON cb.cuentaBonoId = pb.cuentaBonoId
					WHERE pb.periodoBonoId = ? AND pb.flgDelete = ?
	            ", [$_POST['periodoBonoId'], 0]);
	
				// old
	            /* $existeCargoCuentas = $magic->rows("
	            	SELECT cuentaBonificacionId FROM conta_cuentas_bonificaciones
	            	WHERE mes = ? AND anio = ? AND flgDelete = ?
	            ", [$dataPeriodo->mes, $dataPeriodo->anio, 0]); */
	            
				$existeCargoCuentas = $magic->rows("
	            	SELECT cuentaBonificacionId FROM contaCuentasBonificaciones
	            	WHERE mes = ? AND anio = ? AND flgDelete = ?
	            ", [$dataPeriodo->mes, $dataPeriodo->anio, 0]);

	            if($existeCargoCuentas > 0) {
	            	// Borrar antes de iterar lo de abajo
					$whereExiste = [
						"mes" 				=> $dataPeriodo->mes,
						"anio" 				=> $dataPeriodo->anio,
						"flgDelete" 		=> 0
					];
					// $magic->delete("conta_cuentas_bonificaciones", $whereExiste); // old
					$magic->delete("contaCuentasBonificaciones", $whereExiste);
	            } else {
	            	// Solamente iterar lo de abajo
	            }

	            foreach($dataBonosEmpleado as $bonoEmpleado) {
            		$insertMagic = [
            			"mes" 							=> $dataPeriodo->mes,
            			"anio" 							=> $dataPeriodo->anio,
            			"fechaPagoBonificacion" 		=> $_POST['fechaCuenta'],
            			"personaId" 					=> $bonoEmpleado->personaId,
            			"numCuentaContable" 			=> $bonoEmpleado->numCuentaContable,
            			"montoCuenta" 					=> $bonoEmpleado->montoCuenta
            		];
	            	// $cuentaBonificacionId = $magic->insert("conta_cuentas_bonificaciones", $insertMagic);
	            	$cuentaBonificacionId = $magic->insert("contaCuentasBonificaciones", $insertMagic);
	            }
				$insertBitacora = [
					"descripcionExportacion" 			=> "conta_planilla_bonos - cuenta contable", 
					"personaId" 						=> $_SESSION['personaId'], 
					"fhExportacion" 					=> date("Y-m-d H:i:s")
				];
				$bitExportacionMagicId = $cloud->insert("bit_exportaciones_magic", $insertBitacora);

				echo "success";
			break;

			case "exportar-pagos-comisiones-cuentas-magic":
				/*
					POST:
					comisionPagarPeriodoId
					fechaCuenta
				*/
				require_once("../../../../libraries/includes/logic/mgc/datos24.php");

				$dataPeriodoComision = $cloud->row("
					SELECT 
						numMes, anio
					FROM conta_comision_pagar_periodo
					WHERE comisionPagarPeriodoId = ? AND flgDelete = ?
				", [$_POST['comisionPagarPeriodoId'], 0]);

				$dataPeriodoCreditos = $cloud->row("
					SELECT facturaNotaPeriodoId FROM cred_facturas_notas_periodo
					WHERE numMes = ? AND anio = ? AND flgDelete = ?
				", [$dataPeriodoComision->numMes, $dataPeriodoComision->anio, 0]);

				if($dataPeriodoCreditos) {
					$facturaNotaPeriodoId = $dataPeriodoCreditos->facturaNotaPeriodoId;
				} else {
					$facturaNotaPeriodoId = 0;
				}

				$empleadosComisionan = array(
					"ALEJANDRO BOTON DE LEON",
					"ALFREDO COLORADO",
					"ALEXANDER HERNANDEZ",
					"BRIAN DOMINGUEZ",
					"CARLOS RIVERA",
					"CELESTE ORELLANA",
					"DANIEL MORALES",
					"FRANCISCO RODRIGUEZ",
					"GUADALUPE DE DIAS",
					"GREGORIO HERNANDEZ",
					"KELVIN ARIAS",
					"LEONARDO BENAVIDES",
					"LEONEL BATRES",
					"LUIS NAVAS",
					"MARLON LOPEZ",
					"MITZY QUIJANO",
					"OSCAR CONTRERAS",
					"ROBERTO AMAYA",
					"RODOLFO HENRIQUEZ",
					"SALVADOR DIAZ",
					"SERGIO LOVO"
				);
				
				$empleadosComisionanImplode = implode(",", array_map(function($nombre) {
				    return "'" . addslashes($nombre) . "'"; // `addslashes` escapa caracteres especiales
				}, $empleadosComisionan));

				$dataNombresVendedores = $cloud->rows("
					SELECT 
						codEmpleado,
						codVendedor,
					    nombreEmpleado
					FROM conta_comision_pagar_calculo
					WHERE comisionPagarPeriodoId = ? AND flgDelete = ? AND nombreEmpleado IN ($empleadosComisionanImplode)
					GROUP BY nombreEmpleado
					ORDER BY nombreEmpleado
				", [$_POST['comisionPagarPeriodoId'], 0]);

				$marcasCuentasContable = array(1, 2, 3, 4, 5, 6, 7);
				$marcasCuentasContableImplode = implode(",", $marcasCuentasContable);

		        $dataClasificacionLineas = $cloud->rows("
		            SELECT 
		                comisionClasificacionId, tituloClasificacion, numCuentaContable
		            FROM conta_comision_reporte_clasificacion
		            WHERE comisionClasificacionId IN ($marcasCuentasContableImplode) AND tipoClasificacion = ? AND flgDelete = ?
		            ORDER BY tituloClasificacion
		        ", ['Línea', 0]);

			    $whereVendedoresCredito = " AND nombreEmpleado NOT IN (";
			    // Inicializar array
			    foreach ($dataNombresVendedores as $vendedor) {
			        foreach($dataClasificacionLineas as $clasificacionLinea) {
			            $arrayProvisionComisiones[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"] = 0;
			            $arrayProvisionComisiones[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Abonos"] = 0;
			            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"] = 0;
			            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Abonos"] = 0;
			        }
			        $whereVendedoresCredito .= "'$vendedor->nombreEmpleado',";
			    }
			    $whereClean = rtrim($whereVendedoresCredito, ",");
			    $whereVendedoresCredito = $whereClean . ")";
			    // Por esta linea es que se cargaron los otros vendedores
			    // No tomarlos en cuenta para mandar las cuentas contables
			    /*
			    $dataNombresVendedoresCredito = $cloud->rows("
			        SELECT
			            nombreEmpleado
			        FROM cred_facturas_notas_creditos
			        WHERE facturaNotaPeriodoId = ? AND flgDelete = ?
			        $whereVendedoresCredito
			        GROUP BY nombreEmpleado
			        ORDER BY nombreEmpleado
			    ", [$facturaNotaPeriodoId, 0]);

			    foreach ($dataNombresVendedoresCredito as $vendedor) {
			        foreach($dataClasificacionLineas as $clasificacionLinea) {
			            $arrayProvisionComisiones[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"] = 0;
			            $arrayProvisionComisiones[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Abonos"] = 0;
			            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"] = 0;
			            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Abonos"] = 0;
			        }
			    }
			    $dataVendedores = array_merge($dataNombresVendedores, $dataNombresVendedoresCredito);

			    $dataNombresVendedores = $dataVendedores;
			    */

			    $arrayTotalesFooter["totalVendedorHorizontal"] = 0;
			    $arrayTotalesFooter["totalVendedorHorizontalCalculo"] = 0;
			    $arrayTotalesFooter["totalVendedorHorizontalAbonos"] = 0;
			    $arrayTotalesFooter["totalVendedorAbonosCalculo"] = 0;

			   	// Calculo de provision con cuentas contables
                $n = 0;
                foreach($dataClasificacionLineas as $clasificacionLinea) {
                    // Detalle de clasificaciones
                    $dataClasificacionLineasDetalle = $cloud->rows("
                        SELECT
                            comisionClasificacionDetalleId, valorClasificacion
                        FROM conta_comision_reporte_clasificacion_detalle
                        WHERE comisionClasificacionId = ? AND flgDelete = ?
                    ", [$clasificacionLinea->comisionClasificacionId, 0]);
                    foreach($dataClasificacionLineasDetalle as $clasificacionLineaDetalle) {
                        $lineaProducto = substr($clasificacionLineaDetalle->valorClasificacion, 1, 2);
                        // Provisión: Contados
                        $dataProvisionComision = $cloud->rows("
                            SELECT
                                SUM(
                                    CASE
                                        WHEN flgComisionEditar = 1 THEN comisionPagarEditar
                                        ELSE comisionPagar
                                    END
                                ) AS totalComision,
                                nombreEmpleado
                            FROM conta_comision_pagar_calculo
                            WHERE comisionPagarPeriodoId = ? AND flgIdentificador = ? AND lineaProducto = ? AND flgDelete = ?
                            GROUP BY nombreEmpleado
                            ORDER BY nombreEmpleado
                        ", [$_POST['comisionPagarPeriodoId'], 'F', $lineaProducto, 0]);
                        foreach($dataProvisionComision as $provisionComision) {
                            if(isset($arrayProvisionComisiones[$provisionComision->nombreEmpleado][$clasificacionLinea->tituloClasificacion])) {
                                $arrayProvisionComisiones[$provisionComision->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"] += $provisionComision->totalComision;
                            } else {
                                // Vendedores filtrados
                            }
                        } // dataProvisionComision
                        // Provisión: Abonos
                        $dataProvisionComisionAbonos = $cloud->rows("
                            SELECT
                                SUM(
                                    CASE
                                        WHEN flgComisionEditar = 1 AND comisionAbonoTotal = 0 THEN comisionPagarEditar / (
                                                SELECT COUNT(correlativoFactura)
                                                FROM conta_comision_pagar_calculo sub
                                                WHERE sub.flgComisionEditar = 1 AND sub.comisionAbonoTotal = 0 AND sub.correlativoFactura = pagar.correlativoFactura AND sub.fechaAbono = pagar.fechaAbono
                                            )
                                        WHEN flgComisionEditar = 1 THEN ((((comisionPagarEditar * 100) / comisionAbonoTotal) / 100) * comisionPagar)
                                        ELSE ((ROUND((comisionAbonoPagar * 100) / comisionAbonoTotal , 2) / 100) * comisionPagar)
                                    END
                                ) AS totalComision,
                                nombreEmpleado
                            FROM conta_comision_pagar_calculo pagar
                            WHERE comisionPagarPeriodoId = ? AND flgIdentificador = ? AND lineaProducto = ? AND flgDelete = ?
                            GROUP BY nombreEmpleado
                            ORDER BY nombreEmpleado
                        ", [$_POST['comisionPagarPeriodoId'], 'A', $lineaProducto, 0]);
                        foreach($dataProvisionComisionAbonos as $provisionComision) {
                            if(isset($arrayProvisionComisiones[$provisionComision->nombreEmpleado][$clasificacionLinea->tituloClasificacion])) {
                                $arrayProvisionComisiones[$provisionComision->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Abonos"] += $provisionComision->totalComision;
                            } else {
                                // Vendedores filtrados
                            }
                        } // dataProvisionComisionAbonos
                    } // dataClasificacionLineasDetalle
                } // dataClasificacionLineas

                // Dibujar tabla
                foreach ($dataNombresVendedores as $vendedor) {
                    $n++;
                    $totalVendedorHorizontal = 0; $totalVendedorHorizontalAbonos = 0;
                    foreach($dataClasificacionLineas as $clasificacionLinea) {
                        $provisionComision = $arrayProvisionComisiones[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"];
                        $provisionComisionAbonos = $arrayProvisionComisiones[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Abonos"];

                        // Nuevo requerimiento para no mandar 2 cuentas separadas, sino todo consolidado en la misma
                        
                        $totalComisionRequerimiento = $provisionComision + $provisionComisionAbonos;

                        if($totalComisionRequerimiento > 0) {                        	
	                        $insertTotal = [
								"mes" 						=> $dataPeriodoComision->numMes,
								"anio" 						=> $dataPeriodoComision->anio,	
								"fechaPagoComision" 		=> $_POST['fechaCuenta'],	
								"tipoComision" 				=> "Comisión por Ventas y Cobros - {$clasificacionLinea->tituloClasificacion}",	
								"mgCodEmpleado" 			=> $vendedor->codEmpleado,
								"mgCodVendedor" 			=> $vendedor->codVendedor,	
								"mgNombreVendedor" 			=> $vendedor->nombreEmpleado,	
								"numCuentaContable" 		=> $clasificacionLinea->numCuentaContable,
								"montoCuenta" 				=> $totalComisionRequerimiento
	                        ];
	                        // $cuentaComisionId = $magic->insert("conta_cuentas_comisiones", $insertTotal);
	                        $cuentaComisionId = $magic->insert("contaCuentasComisiones", $insertTotal);
                        } else {
                        	// No comisionó de contado, no es necesario mandarla
                        }

                        /*
                        if($provisionComision > 0) {
	                        $insertVentas = [
								"mes" 						=> $dataPeriodoComision->numMes,
								"anio" 						=> $dataPeriodoComision->anio,	
								"fechaPagoComision" 		=> $_POST['fechaCuenta'],	
								"tipoComision" 				=> "Comisión por Ventas (Contado) - {$clasificacionLinea->tituloClasificacion}",	
								"mgCodEmpleado" 			=> $vendedor->codEmpleado,
								"mgCodVendedor" 			=> $vendedor->codVendedor,	
								"mgNombreVendedor" 			=> $vendedor->nombreEmpleado,	
								"numCuentaContable" 		=> $clasificacionLinea->numCuentaContable,
								"montoCuenta" 				=> $provisionComision
	                        ];
	                        $cuentaComisionId = $magic->insert("conta_cuentas_comisiones", $insertVentas);
                        } else {
                        	// No comisionó de contado, no es necesario mandarla
                        }

                        if($provisionComisionAbonos > 0) {
	                        $insertCobros = [
								"mes" 						=> $dataPeriodoComision->numMes,
								"anio" 						=> $dataPeriodoComision->anio,	
								"fechaPagoComision" 		=> $_POST['fechaCuenta'],	
								"tipoComision" 				=> "Comisión por Cobros (Abono) - {$clasificacionLinea->tituloClasificacion}",	
								"mgCodEmpleado" 			=> $vendedor->codEmpleado,
								"mgCodVendedor" 			=> $vendedor->codVendedor,	
								"mgNombreVendedor" 			=> $vendedor->nombreEmpleado,	
								"numCuentaContable" 		=> $clasificacionLinea->numCuentaContable,
								"montoCuenta" 				=> $provisionComisionAbonos
	                        ];
	                        $cuentaComisionId = $magic->insert("conta_cuentas_comisiones", $insertCobros);
                        } else {
                        	// No comisionó abonos, no es necesario mandarla
                        }
                        */
                        $totalVendedorHorizontal += $provisionComision;
                        $totalVendedorHorizontalAbonos += $provisionComisionAbonos;
                        $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"] += $provisionComision;
                        $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Abonos"] += $provisionComisionAbonos;
                    }
                    // El calculo que había de aquí hacia abajo, es para dibujar las columnas de totales, no es necesario para exportar las cuentas contables a contabilidad
                }
                echo "success";
			break;
			case "importar-cuentasContables-magic":
				require_once("../../../../libraries/includes/logic/mgc/datos24.php");

				//Insertar todas las cuentas sin jerarquía
				$getCuentas = $magic->rows("SELECT 
					Cuenta,
					Descripcion,
					Tipo,
					AplicaMovimiento,
					CuentaMayor,
					SaldoInicial,
					Cargos,
					Abonos,
					SaldoFinal,
					Balance,
					CentrodeCostos,
					Categoria,
					Auxiliares,
					Comparativos,
					Auditorias,
					TipodeMayoreo,
					Reporte2,
					Reporte3,
					TipoCuenta,
					Naturaleza
				FROM CatalogoCuentas");

				// Guardar todas temporalmente en un array para despues asignarles el padre y el nivel, por eso estaba reventando xDD
				$mapCuentas = [];

				foreach ($getCuentas as $cuenta){
					$numeroCuenta = trim($cuenta->Cuenta);
					if ($numeroCuenta === '0') {
						continue;
					}

					$cuentaMayor = trim($cuenta->CuentaMayor);
					$mapCuentas[$numeroCuenta] = $cuentaMayor;

					$checkCuenta = $cloud->row("SELECT cuentaContaId FROM conta_cuentas_contables WHERE numeroCuenta = ?", [$numeroCuenta]);

					if (!$checkCuenta) {
								// categoriaCuenta
							// Traducir la categoría de la cuenta 
							$categoriaMap = [
								'P' => 'Pasivo',
								'C' => 'Capital',
								'I' => 'Ingresos',
								'G' => 'Gastos',
								'A' => 'Activo',
								'R' => 'Resultado'

							];

							$categoriaLetra = strtoupper(trim($cuenta->Categoria));
							$categoriaTraducida = isset($categoriaMap[$categoriaLetra]) ? $categoriaMap[$categoriaLetra] : 'Otro';

							// Determinar tipoMayoreo
						
							if (in_array($categoriaTraducida, ['Pasivo', 'Capital', 'Ingresos', 'Resultado'])) {
								$tipoMayoreo = 'Cargo';
							} else {
								$tipoMayoreo = 'Abono';
							}
						$insert = [
							"numeroCuenta"         => $numeroCuenta,
							"descripcionCuenta"    => str_replace("'", " ", $cuenta->Descripcion),
							"tipoCuenta"           => ($cuenta->Tipo == 'M' && $cuenta->AplicaMovimiento == '0') ? 'Mayor' : 'Auxiliar',
							"categoriaCuenta"      => $categoriaTraducida,
							"tipoMayoreo"          => $tipoMayoreo,
							//"cargoCuenta"          => $cuenta->Cargos,
							//"abonoCuenta"          => $cuenta->Abonos,
							//"balanceCuenta"        => $cuenta->Balance,
							"saldoFinal"			=> $cuenta->SaldoInicial

						];

						$cloud->insert("conta_cuentas_contables", $insert);
					}
				}

				//Asignar cuentaPadreId y nivelCuenta
				$allCuentas = $cloud->rows("SELECT cuentaContaId, numeroCuenta FROM conta_cuentas_contables");

				foreach ($allCuentas as $cuenta) {
					$cuentaId = $cuenta->cuentaContaId;
					$numeroCuenta = trim($cuenta->numeroCuenta);
					$cuentaMayor = isset($mapCuentas[$numeroCuenta]) ? trim($mapCuentas[$numeroCuenta]) : null;

					if (!empty($cuentaMayor)) {
						$padre = $cloud->row("SELECT cuentaContaId, nivelCuenta FROM conta_cuentas_contables WHERE numeroCuenta = ?", [$cuentaMayor]);

						if ($padre) {
							$nivel = ($padre->nivelCuenta !== null) ? ($padre->nivelCuenta + 1) : 2;
							$cloud->update("conta_cuentas_contables", [
								"cuentaPadreId" => $padre->cuentaContaId,
								"nivelCuenta" => $nivel
							], [
								"cuentaContaId" => $cuentaId
							]);
						} else {
							// Padre no encontrado, asignar como raíz (nivel 1)
							$cloud->update("conta_cuentas_contables", [
								"nivelCuenta" => 1
							], [
								"cuentaContaId" => $cuentaId
							]);
						}
					} else {
						// No tiene padre, es raíz
						// Ojo ver este codigo para mover otros volumenes grandes de datos :) 
						$cloud->update("conta_cuentas_contables", [
							"nivelCuenta" => 1
						], [
							"cuentaContaId" => $cuentaId
						]);
					}
				}

				echo "Importación completa: cuentas insertadas, padres asignados y niveles calculados.";
			break;

				/*case "importar-matriz-centros-costos":
					require_once("../../../../libraries/includes/logic/mgc/datos24.php");

					// Obtener los datos de la tabla fuente
					$getMatriz = $magic->rows("SELECT 
						Cta_Ctb,
						Cod_depto,
						Signo
					FROM matrizCentroCostos");

					// Recorrer e insertar solo los que no existen
					foreach ($getMatriz as $fila) {
						$cuentaTabla = trim($fila->Cta_Ctb);
						$codDepto = (int)$fila->Cod_depto;
						$signo = trim($fila->Signo);

						// Verificar si ya existe en la tabla destino
						$existe = $cloud->row("SELECT mattrizId FROM matrizcentroscostos WHERE cuentaTabla = ? AND Cod_depto = ? AND Signo = ?", [
							$cuentaTabla,
							$codDepto,
							$signo
						]);

						if (!$existe) {
							$insert = [
								"cuentaTabla" => $cuentaTabla,
								"Cod_depto"   => $codDepto,
								"Signo"       => $signo
							];

							$cloud->insert("matrizcentroscostos", $insert);
						}
					}

					echo "Importación completada: datos insertados con Signo incluido.";
				break;
				*/

				/*case "importar-partidasContables-magic":
					require_once("../../../../libraries/includes/logic/mgc/datos24.php");

					//Insertar todas las partidas detalle
					$getDetallePartidas = $magic->rows("SELECT 
						numeroPartida,
						tipoPartida,
						fecha,
						cuenta,
						concepto,
						Cargo,
						Abono,
						correlativo,
						documento
					FROM contaDetallePartidas");

					foreach ($getDetallePartidas as $row) {
						$cuenta = trim($row->cuenta);
						$concepto = htmlspecialchars($row->concepto, ENT_QUOTES, 'UTF-8');
						$cargos = (float)$row->Cargo;
						$abonos = (float)$row->Abono;
						$partidaContableId = (int)$row->numeroPartida;
						$documentoId = !empty($row->documento) ? (int)$row->documento : null;
						$fecha = $row->fecha;

						// Obtener el periodo desde el mes de la fecha
						$mes = (int)date("n", strtotime($fecha)); // 1 a 12
						$partidaContaPeriodoId = $mes;

						// Buscar cuentaContaId por numeroCuenta
						$cuentaData = $cloud->row("SELECT cuentaContaId FROM conta_cuentas_contables WHERE numeroCuenta = ?", [$cuenta]);

						if (!$cuentaData) {
							echo "Cuenta no encontrada: {$cuenta} <br>";
							continue;
						}

						$cuentaContaId = $cuentaData->cuentaContaId;

						// Insertar en conta_partidas_contables_detalle
						$insert = [
							"partidaContableId" => $partidaContableId,
							"cuentaContaId" => $cuentaContaId,
							"partidaContaPeriodoId" => $partidaContaPeriodoId,
							"descripcionPartidaDetalle" => $concepto,
							"cargos" => $cargos,
							"abonos" => $abonos
						];

						$cloud->insert("conta_partidas_contables_detalle", $insert);
					}



					echo "Migración completada correctamente.";


					foreach ($getEncabezadoPartidas as $row) {
						$tipoPartida = $row->tipoPartida;
						$fecha = $row->fecha;
						$numPartida = (int)$row->numero;
						$descripcionPartida = htmlspecialchars($row->conceptoUno, ENT_QUOTES, 'UTF-8');
						$cargoPartida = (float)$row->Cargo;
						$abonoPartida = (float)$row->Abono;

						// Obtener el periodo desde el mes de la fecha
						$mes = (int)date("n", strtotime($fecha)); // 1 a 12
						$partidaContaPeriodoId = $mes;

						// Buscar tipoPartidaId por descripcionPartida
						$tipoPartidaData = $cloud->row("SELECT tipoPartidaId FROM cat_tipo_partida_contable WHERE descripcionPartida = ?", [$tipoPartida]);

						if (!$tipoPartidaData) {
							echo "Tipo de partida no encontrado: {$tipoPartida} <br>";
							continue;
						}

						$tipoPartidaId = $tipoPartidaData->tipoPartidaId;

						// Insertar en conta_partidas_contables
						$insertEncabezado = [
							"tipoPartidaId" => $tipoPartidaId,
							"partidaContaPeriodoId" => $partidaContaPeriodoId,
							"numPartida" => $numPartida,
							"descripcionPartida" => $descripcionPartida,
							"fechaPartida" => $fecha,
							"cargoPartida" => $cargoPartida,
							"abonoPartida" => $abonoPartida,
							"estadoPartidaContable" => "Activo",
							"userAdd" => $_SESSION['user'],
							'flgDelete' => 0
						];

						$cloud->insert("conta_partidas_contables", $insertEncabezado);
					}

					echo "Migración completada correctamente.";
			break;*/
			/*case "importar-partidas-contables-encabezado":
				require_once("../../../../libraries/includes/logic/mgc/datos24.php");
				$getEncabezadoPartidas = $magic->rows("SELECT 
					idEncPdas,
					tipoPartida,
					fecha,
					numero,
					conceptoUno,
					Cargo,
					Abono
				FROM [contaEncaPartidas]");

				$mapTipoPartida = [
					'A' => 'VENTAS CREDITO',
					'B' => 'REMESAS',
					'C' => 'GASTOS',
					'D' => 'AJUSTES',
					'E' => 'VENTAS CONTADO',
					'F' => 'CIERRE',
					'G' => 'APERTURA'
				];

				foreach ($getEncabezadoPartidas as $row) {
					$descripcion = isset($mapTipoPartida[trim($row->tipoPartida)]) ? $mapTipoPartida[trim($row->tipoPartida)] : null;

					$tipoPartidaRow = $cloud->row("SELECT tipoPartidaId FROM cat_tipo_partida_contable WHERE descripcionPartida = '$descripcion'");
					$tipoPartidaId = $tipoPartidaRow ? $tipoPartidaRow->tipoPartidaId : null;

					$fecha = $row->fecha;
					$periodo = $cloud->row("SELECT partidaContaPeriodoId FROM conta_partidas_contables_periodos WHERE MONTH('$fecha') = mes AND YEAR('$fecha') = anio");
					$partidaContaPeriodoId = $periodo ? $periodo->partidaContaPeriodoId : null;

					if ($tipoPartidaId && $partidaContaPeriodoId) {
						$cloud->insert("conta_partidas_contables", [
							"tipoPartidaId" => $tipoPartidaId,
							"partidaContaPeriodoId" => $partidaContaPeriodoId,
							"numPartida" => $row->numero,
							"descripcionPartida" => $row->conceptoUno,
							"fechaPartida" => $fecha,
							"cargoPartida" => $row->Cargo,
							"abonoPartida" => $row->Abono,
							"estadoPartidaContable" => "Finalizada"
						]);
					}
				}

				echo "Encabezados importados correctamente.";
				break;*/
			
				case "importar-partidas-contables-encabezado":
					require_once("../../../../libraries/includes/logic/mgc/datos24.php");
					$getEncabezadoPartidas = $magic->rows("SELECT 
						idEncPdas,
						tipoPartida,
						fecha,
						numero,
						conceptoUno,
						Cargo,
						Abono,
						flgImportacion
					FROM contaEncaPartidas WHERE flgImportacion = 0");

					$mapTipoPartida = [
						'A' => 'VENTAS CREDITO',
						'B' => 'REMESAS',
						'C' => 'GASTOS',
						'D' => 'AJUSTES',
						'E' => 'VENTAS CONTADO',
						'F' => 'CIERRE',
						'G' => 'APERTURA'
					];

					foreach ($getEncabezadoPartidas as $row) {
						$descripcion = isset($mapTipoPartida[trim($row->tipoPartida)]) ? $mapTipoPartida[trim($row->tipoPartida)] : null;

						$tipoPartidaRow = $cloud->row("SELECT tipoPartidaId FROM cat_tipo_partida_contable WHERE descripcionPartida = '$descripcion'");
						$tipoPartidaId = $tipoPartidaRow ? $tipoPartidaRow->tipoPartidaId : null;

						$fecha = $row->fecha;
						$periodo = $cloud->row(
							"SELECT partidaContaPeriodoId
							FROM conta_partidas_contables_periodos
							WHERE MONTH(?) = mes AND YEAR(?) = anio",
							[$fecha, $fecha]
						);
						$partidaContaPeriodoId = $periodo ? $periodo->partidaContaPeriodoId : null;

						if ($tipoPartidaId && $partidaContaPeriodoId) {
							$cloud->insert("conta_partidas_contables", [
								"tipoPartidaId" => $tipoPartidaId,
								"idEncPdas" => $row->idEncPdas,
								"partidaContaPeriodoId" => $partidaContaPeriodoId,
								"numPartida" => $row->numero,
								"descripcionPartida" => $row->conceptoUno,
								"fechaPartida" => $fecha,
								"cargoPartida" => $row->Cargo,
								"abonoPartida" => $row->Abono,
								"estadoPartidaContable" => "Finalizada"
							]);
						}
					$update = ["flgImportacion"		=> 1];
					$whereMagic = ["idEncPdas" => $row->idEncPdas];
					//Actualizar flgImportacion a 1 en contaDetallePartidas
    				$magic->update("contaEncaPartidas", $update, $whereMagic);

					}

					echo "Encabezados importados correctamente.";
				break;


			case "importar-partidasContables-magic":
				require_once("../../../../libraries/includes/logic/mgc/datos24.php");

				// Definir el mapeo de tipos de partida 
				$mapTipoPartida = [
					'A' => 'VENTAS CREDITO',
					'B' => 'REMESAS',
					'C' => 'GASTOS',
					'D' => 'AJUSTES',
					'E' => 'VENTAS CONTADO',
					'F' => 'CIERRE',
					'G' => 'APERTURA'
				];

				// Insertar todas las partidas detalle
				$getDetallePartidas = $magic->rows("SELECT 
					idDetPartida,
					numeroPartida,
					tipoPartida,
					fecha,
					cuenta,
					concepto,
					Cargo,
					Abono,
					correlativo,
					documento,
					flgImportacion
				FROM contaDetallePartidas where flgImportacion = 0");
				// Actualizar el flag de importación para no duplicar en futuras ejecuciones


				foreach ($getDetallePartidas as $row) {
					$cuenta = trim($row->cuenta);
					$concepto = htmlspecialchars($row->concepto, ENT_QUOTES, 'UTF-8');
					$cargos = (float)$row->Cargo;
					$abonos = (float)$row->Abono;
					$numeroPartida = (int)$row->numeroPartida;
					$tipoPartida = trim($row->tipoPartida);
					$fecha = $row->fecha;
					$documentoId = !empty($row->documento) ? (int)$row->documento : null;

					// Convertir el tipo de partida usando el mapeo (igual que en encabezado)
					$descripcionTipoPartida = isset($mapTipoPartida[$tipoPartida]) ? $mapTipoPartida[$tipoPartida] : $tipoPartida;

					// Primero obtener el ID del encabezado correspondiente
					$encabezado = $cloud->row("
						SELECT p.partidaContableId 
						FROM conta_partidas_contables p
						JOIN cat_tipo_partida_contable t ON p.tipoPartidaId = t.tipoPartidaId
						WHERE p.numPartida = ? 
						AND t.descripcionPartida = ?
						AND p.fechaPartida = ?
					", [
						$numeroPartida,
						$descripcionTipoPartida,
						$fecha
					]);

					if (!$encabezado) {
						echo "No se encontró encabezado para partida #{$numeroPartida}, tipo {$tipoPartida} ({$descripcionTipoPartida}), fecha {$fecha}<br>";
						continue;
					}

					$partidaContableId = $encabezado->partidaContableId;
 
					// Obtener el periodo desde el mes de la fecha
					$mes = (int)date("n", strtotime($fecha)); // 1 a 12
					$partidaContaPeriodoId = $mes;

					// Buscar cuentaContaId por numeroCuenta
					$cuentaData = $cloud->row("SELECT cuentaContaId FROM conta_cuentas_contables WHERE numeroCuenta = ?", [$cuenta]);

					if (!$cuentaData) {
						echo "Cuenta no encontrada: {$cuenta} <br>";
						continue;
					}

					$cuentaContaId = $cuentaData->cuentaContaId;

					// Insertar en conta_partidas_contables_detalle
					$insert = [
						"partidaContableId" => $partidaContableId,
						"idDetPartida" => $row->idDetPartida,
						"cuentaContaId" => $cuentaContaId,
						"partidaContaPeriodoId" => $partidaContaPeriodoId,
						"descripcionPartidaDetalle" => $concepto,
						"cargos" => $cargos,
						"abonos" => $abonos,
						"numDocumento" => $documentoId
					];

					$cloud->insert("conta_partidas_contables_detalle", $insert);
						$update = [
								"flgImportacion"		=> 1
								];

						$whereMagic = ["idDetPartida" => $row->idDetPartida];
					//Actualizar flgImportacion a 1 en contaDetallePartidas
    				$magic->update("contaDetallePartidas", $update, $whereMagic);
				}

				echo "Detalles importados correctamente.";

			break;
			
					
			// Importar proveedores y compras de Magic
				
			case "importar-proveedores-magic":
				require_once("../../../../libraries/includes/logic/mgc/datos24.php");

				$getProveedores = $magic->rows("SELECT 
				Numero
				,idCloud
				,Nombre
				,Direccion
				,NoRegistroIva
				,Giro
				,Categoria
				,Municipio
				,Departamento
				,Telefono1
				,Telefono2
				,TipoProveedor
				,Nit
				,Abonos
				,SaldoActual
				,FechaUltimoPago
				,FechaProximoPago
				,CodigoContable
				,ValorProximoPago
				,UltimoPedidoPagado
				,TipoDocumento
				,Documento
				,Cargos
				,SaldoInicial
				,Creado
				FROM Proveedores
				WHERE Documento IN ('112159', '11296', '260159', '975474', '112189', '112284', '112197', '202357687', '15856641662950', '46127635000236', '112721', '46276', '00528215', '11218', '30368282', '92501721', '90448', '5067758', '222521', '11349231', '112292', '15856641', '98000064', '11212', '112338', '910212', '112371', '164649', '1128', '7405156', '430168840', '303682824', '13223025', '11277', '11278', '112113', '789921', '92500883')");

				foreach ($getProveedores as $proveedor){

					$nrcClean = str_replace("-", "",$proveedor->NoRegistroIva);
					$checkNRC = $cloud->row("SELECT proveedorId, nrcProveedor FROM comp_proveedores WHERE REPLACE(nrcProveedor, '-', '') = ?", [$nrcClean]);

					if ($checkNRC){
						$update = [
							"codProveedorMagic"				=> $proveedor->Numero,
							"cuentaContable"				=> $proveedor->CodigoContable,
							"estadoProveedor"				=> "Activo",
						];

						$where = ['proveedorId' => $checkNRC->proveedorId]; // ids, soporta múltiple where
						$cloud->update('comp_proveedores', $update, $where);
					} else {
						$insert = [
							"tipoProveedor"					=> "Empresa extranjera",
							"nrcProveedor"					=> $proveedor->NoRegistroIva,
							"tipoDocumentoMH"				=> 3,
							"tipoDocumento"					=> "Otro",
							"numDocumento"					=> $proveedor->Documento,
							"nombreProveedor"				=> str_replace("'", "''",$proveedor->Nombre),
							"nombreComercial"				=> str_replace("'", "''",$proveedor->Nombre),
							"descripcionExtranjero"			=> $proveedor->Giro,
							"codProveedorMagic"				=> $proveedor->Numero,
							"cuentaContable"				=> $proveedor->CodigoContable,
							"direccionProveedorUbicacion"	=> $proveedor->Direccion,
							"estadoProveedor"				=> "Activo",
						];
						$proveedorId = $cloud->insert("comp_proveedores", $insert);
					}

				}

				echo "success";
			break;
			case "importar-compras-magic":
				require_once("../../../../libraries/includes/logic/mgc/datos24.php");

				$getCompras = $magic->rows("SELECT 
				c.No_Pedido AS No_Pedido
				,c.Documento AS Documento
				,c.No_Factura AS No_Factura
				,c.Vendedor AS Vendedor
				,c.Proveedor AS Proveedor
				,c.Fecha_declaracion AS Fecha_declaracion
				,c.Fecha_documento AS Fecha_documento
				,c.IVA AS IVA
				,c.Semana AS Semana
				,c.Total_Gravadas AS Total_Gravadas
				,c.Total_Excentas AS Total_Excentas
				,c.Fovial AS Fovial
				,c.Total_a_Pagar AS Total_a_Pagar
				,c.Total_Excluidos AS Total_Excluidos
				,c.NS_de_Registro AS NS_de_Registro
				,c.Tipo_Documento AS Tipo_Documento
				,c.Documento_Referencia AS Documento_Referencia
				,p.Nombre AS Nombre
				,p.Direccion AS Direccion
				,p.CodigoContable AS CodigoContable
				,p.Numero AS Numero
				,c.Serie AS Serie
				,c.Formulario_Unico AS Formulario_Unico
				,c.Numero_Control AS Numero_Control
				FROM EncabezadoCompra c
				LEFT JOIN Proveedores p ON c.Proveedor = p.Numero
				WHERE No_Pedido IN ('327451','428076','428053','327457','448802','448590','448595','448840','448740','327414','427830','327480','327408','327407','327412')"
				); // ids especificos de compras pendientes 
				//WHERE Fecha_declaracion BETWEEN ? AND ? AND NOT c.Documento = 'I' and c.Total_a_Pagar is not null ", ['2025-01-01','2025-01-31']
				foreach ($getCompras as $compra){

					$nrcClean = str_replace("-", "",$compra->NS_de_Registro);
					$checkNRC = $cloud->row("SELECT proveedorId, nrcProveedor FROM comp_proveedores WHERE REPLACE(nrcProveedor, '-', '') = ?", [$nrcClean]);

					if ($checkNRC){
						$proveedorId = $checkNRC->proveedorId;
					} else {
						if ($compra->Tipo_Documento == 1){
							$tipoDocumento = "NIT";
						} else {
							$tipoDocumento = "DUI";
						}
						$insert = [
							"tipoProveedor"					=> "Empresa local",
							"nrcProveedor"					=> $compra->NS_de_Registro,
							"tipoDocumentoMH"				=> $compra->Tipo_Documento,
							"tipoDocumento"					=> $tipoDocumento,
							"numDocumento"					=> $compra->Documento_Referencia,
							"nombreProveedor"				=> str_replace("'", "''",$compra->Nombre),
							"nombreComercial"				=> str_replace("'", "''",$compra->Nombre),
							"codProveedorMagic"				=> $compra->Numero,
							"cuentaContable"				=> $compra->CodigoContable,
							"direccionProveedorUbicacion"	=> $compra->Direccion,
							"estadoProveedor"				=> "Activo",
						];
						$proveedorId = $cloud->insert("comp_proveedores", $insert);
					}

					$numFacturaClean = str_replace("-", "",$compra->No_Factura);
					$checkFactura = $cloud->row("SELECT numFactura FROM comp_compras_2025 WHERE REPLACE(numFactura, '-', '') = ? AND flgDelete = ?", [$numFacturaClean, 0]);

					/* if ($checkFactura){
						// ya existe esa factura
						// echo $compra->No_Factura .'<br>';
					} else { */
						if ($compra->Documento == 'D'){
							$tipoGeneracion = 2;
							$compraClaseDocumentoId = 4;
						} else {
							$tipoGeneracion = 1;
							$compraClaseDocumentoId = 1;
						}
		
						$insert = [
							'tipoCompra' 				=> "Local",
							'sucursalId' 				=> 1,
							'tipoDTEId' 				=> 2,
							'tipoGeneracionDocId' 		=> $tipoGeneracion,
							'compraClaseDocumentoId' 	=> $compraClaseDocumentoId,
							'numeroControl'				=> $compra->Numero_Control,
							'selloRecibido'				=> $compra->Serie,
							'numFactura'				=> $compra->No_Factura,
							'proveedorId'				=> $proveedorId,
							'fechaFactura'				=> $compra->Fecha_documento,
							'fechaDeclaracion'			=> $compra->Fecha_declaracion,
							'monedaId'					=> 1,
							'estadoCompra'				=> 'Finalizado',
							'totalExenta'				=> $compra->Total_Excentas,
							'totalIVA'					=> $compra->IVA,
							'subTotal'					=> $compra->Total_Gravadas,
							'totalCompra'				=> $compra->Total_a_Pagar
						];
		
						$cloud->insert("comp_compras_2025", $insert);
					// }
				}
				
				echo "success";
			break;
		case "notificacion-persona":
			$empleados = (array) $_POST["empleado"];
			foreach ($empleados as $empleado) {
				$correo = $cloud->row("SELECT correo FROM conf_usuarios WHERE personaId = ? AND flgDelete = ?", [$empleado, 0]);
				$insert = [
					"personaId" => $empleado,
					"correo" => $correo->correo,
					"categoria" => $_POST['categoria'],
				];
				$cloud->insert("conf_notificacion_persona", $insert);
			}
			echo "success";
			break;


			case "importar-historiales-retaceos-magic":
				require_once("../../../../libraries/includes/logic/mgc/datos24.php");

				// IMPORTACIONES: comp_retaceo_historial
				$getHistoriales = $magic->rows("
					SELECT 
						NoImportacion,
						FechaComienzo,
						FechaFin,
						Descripcion,
						Proveedor,
						ValorGastos,
						ValorFacturas,
						ValorTotal,
						Estatus,
						UltimaLinea,
						TotalProductos,
						ImpuestoDirecto,
						Flete,
						Acentado
					FROM Importaciones
				");

				foreach ($getHistoriales as $hist) {
					$insertImportacion = [
						"NoImportacion"        => $hist->NoImportacion,
						"FechaComienzo"        => $hist->FechaComienzo,
						"FechaFin"             => $hist->FechaFin,
						"DescripcionHistorial" => str_replace("'", " ", $hist->Descripcion),
						"ProveedorId"          => $hist->Proveedor,
						"ValorGastos"          => $hist->ValorGastos,
						"ValorFacturas"        => $hist->ValorFacturas,
						"ValorTotal"           => $hist->ValorTotal,
						"Estatus"              => str_replace("'", " ", $hist->Estatus),
						"UltimaLinea"          => $hist->UltimaLinea,
						"TotalProductos"       => $hist->TotalProductos,
						"ImpuestoDirecto"      => $hist->ImpuestoDirecto,
						"Flete"                => $hist->Flete,
						"Acentado"             => $hist->Acentado
					];
					$cloud->insert("comp_retaceo_historial", $insertImportacion);
				}

				// ENCABEZADO IMPORTACION: comp_retaceo_historial_encabezado
				$getEncabezados = $magic->rows("
					SELECT 
						Correlativo,
						Documento,
						No_Factura,
						Proveedor,
						Fecha_Pedido,
						Fecha_Reclamo,
						IVA,
						Total_Gravadas,
						Total_Excentas,
						Retencion,
						Total_a_Pagar,
						Saldo_Factura,
						Condiciones,
						Dias_Credito,
						Fecha_Vencimiento,
						Estatus,
						Estatus2,
						Ultima_Linea,
						Cuenta_a_Cargar,
						Cuenta_a_Abonar,
						Cheque_No,
						Valor_Pagado,
						Fecha_Pagado,
						Fecha_Quedan,
						No_Importacion,
						Moneda_1,
						Tasa_1,
						Tasa_2,
						No_Pedido,
						Peso_Bruto,
						Via,
						Observaciones,
						DMN
					FROM EncabezadoImportacion
				");

				foreach ($getEncabezados as $enca) {
					$insertEncabezado = [
						"correlativo"      => $enca->Correlativo,
						"documento"        => str_replace("'", " ", $enca->Documento),
						"noFactura"        => str_replace("'", " ", $enca->No_Factura),
						"proveedorId"      => $enca->Proveedor,
						"fechaPedido"      => $enca->Fecha_Pedido,
						"FechaReclamo"     => $enca->Fecha_Reclamo,
						"IVA"              => $enca->IVA,
						"totalGravadas"    => $enca->Total_Gravadas,
						"totalExcentas"    => $enca->Total_Excentas,
						"retencion"        => $enca->Retencion,
						"totalPagar"       => $enca->Total_a_Pagar,
						"saldoFactura"     => $enca->Saldo_Factura,
						"condiciones"      => str_replace("'", " ", $enca->Condiciones),
						"diasCredito"      => $enca->Dias_Credito,
						"fechaVencimiento" => $enca->Fecha_Vencimiento,
						"estatus"          => str_replace("'", " ", $enca->Estatus),
						"estatus2"         => str_replace("'", " ", $enca->Estatus2),
						"ultimaLinea"      => $enca->Ultima_Linea,
						"cuentaACargar"    => str_replace("'", " ", $enca->Cuenta_a_Cargar),
						"cuentaAbonar"     => str_replace("'", " ", $enca->Cuenta_a_Abonar),
						"chequeNo"         => str_replace("'", " ", $enca->Cheque_No),
						"valorPagado"      => $enca->Valor_Pagado,
						"fechaPagado"      => $enca->Fecha_Pagado,
						"fechaQuedan"      => $enca->Fecha_Quedan,
						"NoImportacion"    => $enca->No_Importacion,
						"moneda1"          => str_replace("'", " ", $enca->Moneda_1),
						"tasa1"            => $enca->Tasa_1,
						"tasa2"            => $enca->Tasa_2,
						"NoPedido"         => str_replace("'", " ", $enca->No_Pedido),
						"pesoBruto"        => str_replace("'", " ", $enca->Peso_Bruto),
						"Via"              => str_replace("'", " ", $enca->Via),
						"observaciones"    => str_replace("'", " ", $enca->Observaciones),
						"DMN"              => $enca->DMN
					];
					$cloud->insert("comp_retaceo_historial_encabezado", $insertEncabezado);
				}

				// LINEAS IMPORTACION: comp_retaceo_historial_lienas
				$getLineas = $magic->rows("
					SELECT 
						Correlativo,
						No_Linea,
						Departamento,
						Linea,
						Clase,
						Modelo,
						Codigo_Producto,
						Cantidad,
						Precio_Compra,
						Importe,
						Diferencial_Precio,
						Porcentaje_Impuesto,
						Valor_Moneda_1,
						Valor_Dolares,
						Subtotal_Impuesto_1,
						No_Pedido,
						Imp_Directo
					FROM LineasImportacion
				");

				foreach ($getLineas as $linea) {
					$insertLinea = [
						"correlativo"        => $linea->Correlativo,
						"noLinea"            => $linea->No_Linea,
						"departamento"       => str_replace("'", " ", $linea->Departamento),
						"linea"              => str_replace("'", " ", $linea->Linea),
						"clase"              => str_replace("'", " ", $linea->Clase),
						"modelo"             => $linea->Modelo,
						"codigoProducto"     => str_replace("'", " ", $linea->Codigo_Producto),
						"cantidad"           => $linea->Cantidad,
						"precioCompra"       => $linea->Precio_Compra,
						"importe"            => $linea->Importe,
						"diferencialPrecio"  => $linea->Diferencial_Precio,
						"porcentajeImpuesto" => $linea->Porcentaje_Impuesto,
						"valorMoneda1"       => $linea->Valor_Moneda_1,
						"valorDolares"       => $linea->Valor_Dolares,
						"subtotalImpuesto1"  => $linea->Subtotal_Impuesto_1,
						"noPedido"           => str_replace("'", " ", $linea->No_Pedido),
						"impDirecto"         => $linea->Imp_Directo
					];
					$cloud->insert("comp_retaceo_historial_lineas", $insertLinea);
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