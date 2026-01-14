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
			case "eliminar-menu":
				/*
					POST:
					typeOperation
					operation
					id - menuId
				*/
				if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(37, $_SESSION["arrayPermisos"])) {
					$dataMenuEliminar = $cloud->row("
						SELECT
							moduloId,
							menu,
							menuSuperior,
							numOrdenMenu
						FROM conf_menus
						WHERE menuId = ?
					", [$_POST["id"]]);

					$stringSuccess = "success^";
					$cloud->deleteById("conf_menus", "menuId", $_POST["id"]);
					
					$stringSuccess .= "El menú: ".$dataMenuEliminar->menu." ha sido eliminado con éxito.";

					// Eliminar menus-permiso
					$permisosEliminados = $cloud->deleteById("conf_menus_permisos", "menuId", $_POST["id"]);

					$stringSuccess .= "<br>Se han eliminado " . $permisosEliminados . " menú-permisos.";

					$iterarOrdenRepetidos = $cloud->rows("
						SELECT
							menuId,
							menu,
							numOrdenMenu
						FROM conf_menus
						WHERE moduloId = ? AND menuSuperior = ? AND numOrdenMenu >= ? AND menuId <> ? AND flgDelete = '0'
					", [$dataMenuEliminar->moduloId, $dataMenuEliminar->menuSuperior, $dataMenuEliminar->numOrdenMenu, $_POST["id"]]);
					$n = 0;
					foreach ($iterarOrdenRepetidos as $iterarOrdenRepetidos) {
						if($n == 0) { // Si existen registros, mostrar leyenda
							$stringSuccess .= "<br>Se ha actualizado el orden de los menús (-1):";
						} else {
						}

						$n += 1;
						$numOrdenMenu = $iterarOrdenRepetidos->numOrdenMenu - 1;
						$update = [
							'numOrdenMenu' 	=> $numOrdenMenu,
						];
						$where = ['menuId' => $iterarOrdenRepetidos->menuId];
						$cloud->update('conf_menus', $update, $where);
						$stringSuccess .= "<br>" . $n . ". " . $iterarOrdenRepetidos->menu . " (" . $iterarOrdenRepetidos->numOrdenMenu . " -> " . $numOrdenMenu . ")";
					}
					// Bitácora de usuario final / jefes
			        $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el menú: ".$dataMenuEliminar->menu.", ");
					echo $stringSuccess;
				} else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y eliminar un menú [".$_POST["id"]."] (manejo desde consola), ");
        			echo "Acción no válida.";
				}
			break;

			case "eliminar-menu-permiso":
				/*
					POST:
					typeOperation
					operation
					id - menuPermisoId
				*/
	        	if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(41, $_SESSION["arrayPermisos"])) {
		            $cloud->deleteById('conf_menus_permisos', "menuPermisoId", $_POST["id"]);
					// Bitácora de usuario final / jefes
					$dataNombreMenuPermiso = $cloud->row("
						SELECT
							mp.permisoMenu AS permisoMenu,
						    m.menu AS menu
						FROM conf_menus_permisos mp
						JOIN conf_menus m ON m.menuId = mp.menuId
						WHERE menuPermisoId = ?
					", [$_POST["id"]]);
			        $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el permiso: ".$dataNombreMenuPermiso->permisoMenu." del menú: ".$dataNombreMenuPermiso->menu.", ");
		            echo "success";	
	        	} else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y eliminar un permiso [".$_POST["id"]."] (manejo desde consola), ");
        			echo "Acción no válida.";
	        	}		
			break;

	        case "estudio":
                /*
					POST:
					typeOperation
					operation
					idArea
				*/
                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(17, $_SESSION["arrayPermisos"])) {
                	$cantidadIdEnUso = $cloud->count("
                		SELECT
                			prsEducacionId
                		FROM th_personas_educacion
                		WHERE prsArEstudioId = ? AND flgDelete = '0'
                	", [$_POST["idArea"]]);

                	if($cantidadIdEnUso == 0) {
						$cloud->deleteById('cat_personas_ar_estudio', "prsArEstudioId", $_POST["idArea"]);

	                    $dataAreasEst = $cloud->row("
	                        SELECT prsArEstudioId, areaEstudio FROM cat_personas_ar_estudio WHERE prsArEstudioId =?
	                    ", [$_POST["idArea"]]);
	                    $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el área de estudio: ".$dataAreasEst->areaEstudio.", ");
	                    echo "success";
                	} else {
		                $dataArea = $cloud->row("
		                	SELECT
		                		areaEstudio
		                	FROM cat_personas_ar_estudio 
		                	WHERE prsArEstudioId = ?
		                ", [$_POST["idArea"]]);
	                	echo "El área de estudio " . $dataArea->areaEstudio . " se encuentra asignada a " . $cantidadIdEnUso . " perfiles de empleado, por lo que no se puede eliminar.";
                	}
                } else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y eliminar un area [".$_POST["idArea"]."] (manejo desde consola), ");
        			echo "Acción no válida.";
	        	}		
	        break;
	            
	        case "experiencia":
                /*
					POST:
					typeOperation
					operation
					idArea
				*/
                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(17, $_SESSION["arrayPermisos"])) {
                	$cantidadIdEnUso = $cloud->count("
                		SELECT
                			prsExpLaboralId
                		FROM th_personas_exp_laboral
                		WHERE prsArExperienciaId = ? AND flgDelete = '0'
                	", [$_POST["idArea"]]);

                	if($cantidadIdEnUso == 0) {
	                    $cloud->deleteById('cat_personas_ar_experiencia', "prsArExperienciaId", $_POST["idArea"]);

	                    $dataAreasExp = $cloud->row("
	                        SELECT prsArExperienciaId, areaExperiencia FROM cat_personas_ar_experiencia WHERE prsArExperienciaId = ?
	                    ", [$_POST["idArea"]]);
	                    $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el área de experiencia: ".$dataAreasExp->areaExperiencia.", ");
	                    echo "success";
                	} else {
		                $dataArea = $cloud->row("
		                	SELECT
		                		areaExperiencia
		                	FROM cat_personas_ar_experiencia 
		                	WHERE prsArExperienciaId = ?
		                ", [$_POST["idArea"]]);
	                	echo "El área de experiencia " . $dataArea->areaExperiencia . " se encuentra asignada a " . $cantidadIdEnUso . " perfiles de empleado, por lo que no se puede eliminar.";
                	}
                } else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y eliminar un area [".$_POST["idArea"]."] (manejo desde consola), ");
        			echo "Acción no válida.";
	        	}	
	        break;        
	        case "software":
                /*
					POST:
					typeOperation
					operation
					idArea
				*/
                //if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(17, $_SESSION["arrayPermisos"])) {
                	// No se guarda ID, asi que necesito traer el nombre del software
                	$dataNombreHabilidad = $cloud->row("
                		SELECT
                			nombreSoftware
                		FROM cat_personas_software
                		WHERE prsSoftwareId = ?
                	", [$_POST["idArea"]]);
                	$cantidadIdEnUso = $cloud->count("
                		SELECT
                			prsHabilidadId
                		FROM th_personas_habilidades
                		WHERE tipoHabilidad = 'Informática' AND habilidadPersona = ? AND flgDelete = '0'
                	", [$dataNombreHabilidad->nombreSoftware]);

                	if($cantidadIdEnUso == 0) {
	                    $cloud->deleteById('cat_personas_software', "prsSoftwareId", $_POST["idArea"]);

	                    $dataAreasExp = $cloud->row("
	                        SELECT prsSoftwareId, nombreSoftware FROM cat_personas_software WHERE prsSoftwareId  = ?
	                    ", [$_POST["idArea"]]);
	                    $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el software: ".$dataAreasExp->nombreSoftware.", ");
	                    echo "success";
                	} else {
	                	echo "El programa informático " . $dataNombreHabilidad->nombreSoftware . " se encuentra asignado a " . $cantidadIdEnUso . " perfiles de empleado, por lo que no se puede eliminar.";
                	}
                /* } else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y eliminar un area [".$_POST["idArea"]."] (manejo desde consola), ");
        			echo "Acción no válida.";
	        	}	 */
	        break;        
	        case "herraEqu":
                /*
					POST:
					typeOperation
					operation
					idArea
				*/
                //if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(17, $_SESSION["arrayPermisos"])) {
                	// No se guarda ID, asi que necesito traer el nombre de la herramienta-equipo
                	$dataNombreHabilidad = $cloud->row("
                		SELECT
                			nombreHerrEquipo
                		FROM cat_personas_herr_equipos
                		WHERE prsHerrEquipoId = ?
                	", [$_POST["idArea"]]);
                	$cantidadIdEnUso = $cloud->count("
                		SELECT
                			prsHabilidadId
                		FROM th_personas_habilidades
                		WHERE tipoHabilidad = 'Equipo' AND habilidadPersona = ? AND flgDelete = '0'
                	", [$dataNombreHabilidad->nombreHerrEquipo]);

                	if($cantidadIdEnUso == 0) {
	                    $cloud->deleteById('cat_personas_herr_equipos', "prsHerrEquipoId", $_POST["idArea"]);

	                    $dataAreasExp = $cloud->row("
	                        SELECT prsHerrEquipoId, nombreHerrEquipo FROM cat_personas_herr_equipos WHERE prsHerrEquipoId  = ?
	                    ", [$_POST["idArea"]]);
	                    $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó la herramienta/equipo: ".$dataAreasExp->nombreHerrEquipo.", ");
	                    echo "success";
                	} else {
	                	echo "La Herramienta/Equipo " . $dataNombreHabilidad->nombreHerrEquipo . " se encuentra asignado a " . $cantidadIdEnUso . " perfiles de empleado, por lo que no se puede eliminar.";
                	}
                /* } else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y eliminar un area [".$_POST["idArea"]."] (manejo desde consola), ");
        			echo "Acción no válida.";
	        	}	 */
	        break;      
	        case "tipoRelacion":
                /*
					POST:
					typeOperation
					operation
					id
				*/
                //if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(17, $_SESSION["arrayPermisos"])) {
					$dataRelacion = $cloud->row("
					SELECT catPrsRelacionId, tipoPrsRelacion FROM cat_personas_relacion WHERE catPrsRelacionId = ?
					", [$_POST["id"]]);
					
                	$cantidadIdEnUso = $cloud->count("
                		SELECT
						catPrsRelacionId
                		FROM th_personas_relacion
                		WHERE catPrsRelacionId = ? AND flgDelete = '0'
                	", [$_POST["id"]]);

                	if($cantidadIdEnUso == 0) {
	                    $cloud->deleteById('cat_personas_relacion', "catPrsRelacionId", $_POST["id"]);

	                    
	                    $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó la relación: ".$dataRelacion->tipoPrsRelacion.", ");
	                    echo "success";
                	} else {
	                	echo "La relación " . $dataRelacion->tipoPrsRelacion . " se encuentra asignado a " . $cantidadIdEnUso . " perfiles de empleado, por lo que no se puede eliminar.";
                	}
                /* } else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y eliminar un area [".$_POST["idArea"]."] (manejo desde consola), ");
        			echo "Acción no válida.";
	        	}	 */
	        break;      

	        case "delTipoCon":
                /*
                	POST:
                	typeOperation
					operation
					idTipoCon
                */
                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(20, $_SESSION["arrayPermisos"])) {
					// Verificar que este id no esté en uso en las tablas
	                $cantidadIdEnUsoSucursales = $cloud->count("
	                	SELECT
	                		sucursalContactoId
	                	FROM cat_sucursales_contacto
	                	WHERE tipoContactoId = ? AND flgDelete = '0'
	                ",[$_POST["idTipoCon"]]);

	                $cantidadIdEnUsoPersonas = $cloud->count("
	                	SELECT
	                		prsContactoId
	                	FROM th_personas_contacto
	                	WHERE tipoContactoId = ? AND flgDelete = '0'
	                ",[$_POST["idTipoCon"]]);

	                // Considerar validar también en futuras tablas de contactos
	                $totalIdEnUso = $cantidadIdEnUsoSucursales + $cantidadIdEnUsoPersonas;

	                if($totalIdEnUso == 0) {
	                    $cloud->deleteById('cat_tipos_contacto', "tipoContactoId", $_POST["idTipoCon"]);

	                    $dataTipoContacto = $cloud->row("
	                        SELECT 
	                        	tipoContacto 
	                        FROM cat_tipos_contacto 
	                        WHERE tipoContactoId  = ?
	                    ", [$_POST["idTipoCon"]]);
	                    $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el tipo de contacto: ".$dataTipoContacto->tipoContacto.", ");
	                    echo "success";
	                } else {
		                $dataTipoContacto = $cloud->row("
		                	SELECT
		                		tipoContacto
		                	FROM cat_tipos_contacto 
		                	WHERE tipoContactoId = ?
		                ", [$_POST["idTipoCon"]]);
	                	echo "El tipo de contacto: " . $dataTipoContacto->tipoContacto . " se encuentra asignado a " . $totalIdEnUso . " registros (sucursales, empleados), por lo que no se puede eliminar.";
	                }
                } else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y eliminar un tipo de contacto [".$_POST["idTipoCon"]."] (manejo desde consola), ");
        			echo "Acción no válida.";
	        	}	
	        break;
	        
	        case "menu-permisos-usuario":
	        	/*
					POST:
					typeOperation
					operation
					menuId
					usuarioId
					menuSuperior
	        	*/
				if(in_array(10, $_SESSION["arrayPermisos"]) || in_array(53, $_SESSION["arrayPermisos"])) {
		        	$stringDropdown = "";
		        	// iterar los permisos del menú a eliminar
					$whereDesarrollo = (in_array(10, $_SESSION["arrayPermisos"])) ? "" : "AND permisoMenu<>'Desarrollo'";

					$dataPermisosMenu = $cloud->rows("
						SELECT
							menuPermisoId,
							permisoMenu
						FROM conf_menus_permisos
						WHERE menuId = ? $whereDesarrollo AND flgDelete = '0'
					", [$_POST["menuId"]]);
					$permisosEliminados = 0;
					foreach($dataPermisosMenu as $dataPermisosMenu) {
		    			// Verificar si lo tenia asignado
		    			$existePermiso = $cloud->count("
		    				SELECT 
		    					permisoUsuarioId
		    				FROM conf_permisos_usuario
		    				WHERE menuPermisoId = ? AND usuarioId = ? AND flgDelete = '0'
		    			", [$dataPermisosMenu->menuPermisoId, $_POST["usuarioId"]]);
		    			if($existePermiso > 0) {
		    				// Validar si era submenu, y si tiene otros submenu del mismo "padre asignados", sino eliminar padre tambien
		    				if($_POST["menuSuperior"] > 0) { // Validar si es submenú
		    					$otroSubmenuMismoPadre = $cloud->count("
									SELECT
										m.menuId,
									    m.menu
									FROM conf_permisos_usuario prs
									JOIN conf_menus_permisos mp ON mp.menuPermisoId = prs.menuPermisoId
									JOIN conf_menus m ON m.menuId = mp.menuId
									WHERE m.menuSuperior = ? AND m.menuId <> ? AND prs.usuarioId = ? AND prs.flgDelete = '0' AND mp.flgDelete = '0' AND m.flgDelete = '0'
		    					", [$_POST["menuSuperior"], $_POST["menuId"], $_POST["usuarioId"]]);
		    					if($otroSubmenuMismoPadre == 0) { // No tiene otros submenus del mismo padre, eliminar permiso "Dropdown"
		    						$permisoDropdownId = $cloud->row("
		    							SELECT
		    								menuPermisoId
		    							FROM conf_menus_permisos
		    							WHERE menuId = ? AND permisoMenu = 'Dropdown' AND flgDelete = '0'
		    						", [$_POST["menuSuperior"]]);
		    						$where = [
		    							'menuPermisoId'		=> $permisoDropdownId->menuPermisoId,
		    							'usuarioId'			=> $_POST["usuarioId"],
		    						];
		    						$cloud->delete("conf_permisos_usuario", $where);
		    						$stringDropdown = "<br>Se eliminó el permiso de Dropdown, del menú superior.";
		    					} else {
		    						// Tiene otros submenu del mismo padre, no eliminar permiso "Dropdown"
		    					}
		    				} else {
		    					// Era menú único, omitir
		    				}
		    				// Tiene este permiso asignado, eliminar
		    				$where = [
		    					'menuPermisoId' 	=> $dataPermisosMenu->menuPermisoId,
		    					'usuarioId'			=> $_POST["usuarioId"],
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
						    ", [$_POST["usuarioId"]]);

	        				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el permiso: ".$dataNombreMenuPermiso->permisoMenu." del menú: ".$dataNombreMenuPermiso->menu." al usuario: ".$dataNombreUsuario->nombrePersona.", ");
		    			} else {
		    				// No tenia este permiso asignado, omitir
		    			} 
					}
		        	echo "success^Permiso-menú eliminado con éxito.<br>Se eliminaron " . $permisosEliminados . " permisos." . $stringDropdown;
				} else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y eliminar un permiso de usuario [m = ".$_POST["menuId"].", u = ".$_POST["usuarioId"]."] (manejo desde consola), ");
        			echo "Acción no válida.";					
				}
	        break;
	        
	        case "delContactoSucursal":
                /*
					POST:
					typeOperation
					operation
					idContacto
	        	*/
                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(26, $_SESSION["arrayPermisos"])) {
                    $cloud->deleteById('cat_sucursales_contacto', "sucursalContactoId ", $_POST["idContacto"]);

                    $dataContactos = $cloud->row("
                        SELECT sucursalContactoId, sucursalId, contactoSucursal, descripcionCSucursal FROM cat_sucursales_contacto WHERE sucursalContactoId =?
                    ", [$_POST["idContacto"]]);
                    $dataSucs = $cloud->row("
                        SELECT sucursal FROM cat_sucursales WHERE sucursalId = ?
                    ", [$dataContactos->sucursalId]);
                    $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el contacto: ".$dataContactos->descripcionCSucursal ." (".$dataContactos->contactoSucursal.") de la sucursal: ".$dataSucs->sucursal.", ");
                    echo "success";
                } else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y eliminar un contacto contacto: ".$dataContactos->descripcionCSucursal ." (".$dataContactos->contactoSucursal.") de la sucursal: ".$dataSucs->sucursal." (manejo desde consola), ");
        			echo "Acción no válida.";
	        	}	
	        break;
	            
	        case "delSucursal":
                /*
					POST:
					typeOperation
					operation
					idSucursal
	        	*/
                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(23, $_SESSION["arrayPermisos"])) {
                    $cloud->deleteById('cat_sucursales', "sucursalId ", $_POST["idSucursal"]);
                    $dataSucs = $cloud->row("
                        SELECT sucursal FROM cat_sucursales WHERE sucursalId = ?
                    ", [$_POST["idSucursal"]]);
                    $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó la sucursal: ".$dataSucs->sucursal.", ");
                    echo "success";
                } else {
        			// No tiene permisos
        			// Bitacora
        			$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y eliminar la sucursal: ".$dataSucs->sucursal." (manejo desde consola), ");
        			echo "Acción no válida.";
	        	}	
	        break;

			case "enfermedad-alergia":
				/*
					POST:
                    typeOperation
                    operation
                    id
				*/
				// Verificar que este id no esté en uso en la tabla de "th_"
                $cantidadIdEnUso = $cloud->count("
                	SELECT
                		prsEnfermedadId
                	FROM th_personas_enfermedades
                	WHERE catPrsEnfermedadId = ? AND flgDelete = '0'
                ",[$_POST["id"]]);

                if($cantidadIdEnUso == 0) {
	                $cloud->deleteById('cat_personas_enfermedades', "catPrsEnfermedadId ", $_POST["id"]);
	                
	                $dataEnfermedad = $cloud->row("
	                	SELECT
	                		tipoEnfermedad,
	                		nombreEnfermedad
	                	FROM cat_personas_enfermedades 
	                	WHERE catPrsEnfermedadId = ?
	                ", [$_POST["id"]]);

	                $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó la ".$dataEnfermedad->tipoEnfermedad ." - ".$dataEnfermedad->nombreEnfermedad." del catálogo general, ");
	                echo "success";
                } else {
	                $dataEnfermedad = $cloud->row("
	                	SELECT
	                		tipoEnfermedad,
	                		nombreEnfermedad
	                	FROM cat_personas_enfermedades 
	                	WHERE catPrsEnfermedadId = ?
	                ", [$_POST["id"]]);
                	echo "La " . $dataEnfermedad->tipoEnfermedad . " - " . $dataEnfermedad->nombreEnfermedad . " se encuentra asignada a " . $cantidadIdEnUso . " perfiles de empleado, por lo que no se puede eliminar.";
                }
			break;

			case "organizacion":
				/*
					POST:
                    typeOperation
                    operation
                    id
				*/
				// Verificar que este id no esté en uso en la tabla de "th_"
                $cantidadIdEnUso = $cloud->count("
                	SELECT
                		personaId
                	FROM th_personas
                	WHERE nombreOrganizacionIdAFP = ? OR nombreOrganizacionIdISSS = ? AND flgDelete = '0'
                ",[$_POST["id"], $_POST["id"]]);
                // Considerar validación para futuras tablas de planilla en la consulta de arriba o nueva consulta (por ser tabla distinta)

                if($cantidadIdEnUso == 0) {
	                $cloud->deleteById('cat_nombres_organizaciones', "nombreOrganizacionId ", $_POST["id"]);
	                
	                $dataOrganizacion = $cloud->row("
	                	SELECT
	                		tipoOrganizacion,
	                		nombreOrganizacion,
	                		abreviaturaOrganizacion
	                	FROM cat_nombres_organizaciones 
	                	WHERE nombreOrganizacionId = ?
	                ", [$_POST["id"]]);

	                $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó la organización ".$dataOrganizacion->nombreOrganizacion ." (Tipo: ".$dataOrganizacion->tipoOrganizacion." - Abreviatura: ".$dataOrganizacion->abreviaturaOrganizacion.") del catálogo general, ");
	                echo "success";
                } else {
	                $dataOrganizacion = $cloud->row("
	                	SELECT
	                		tipoOrganizacion,
	                		nombreOrganizacion,
	                		abreviaturaOrganizacion
	                	FROM cat_nombres_organizaciones 
	                	WHERE nombreOrganizacionId = ?
	                ", [$_POST["id"]]);
                	echo "La organización " . $dataOrganizacion->nombreOrganizacion . " se encuentra asignada a " . $cantidadIdEnUso . " perfiles de empleado, por lo que no se puede eliminar.";
                }
			break;

			case "cargo-persona":
				/*
					POST:
                    typeOperation
                    operation
                    id
				*/
				// Verificar que este id no esté en uso en la tabla de "th_"
                $cantidadIdEnUso = $cloud->count("
                	SELECT
                		prsExpedienteId
                	FROM th_expediente_personas
                	WHERE prsCargoId = ? AND flgDelete = '0'
                ",[$_POST["id"]]);
                // Considerar validación para futuras tablas de planilla en la consulta de arriba o nueva consulta (por ser tabla distinta)

                if($cantidadIdEnUso == 0) {
	                $cloud->deleteById('cat_personas_cargos', "prsCargoId ", $_POST["id"]);
	                
	                $dataCargoPersona = $cloud->row("
	                	SELECT
	                		cargoPersona,
	                		descripcionCargoPersona
	                	FROM cat_personas_cargos 
	                	WHERE prsCargoId = ?
	                ", [$_POST["id"]]);

	                $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el cargo ".$dataCargoPersona->cargoPersona ." (Descripción: ".$dataCargoPersona->descripcionCargoPersona.") del catálogo general, ");
	                echo "success";
                } else {
	                $dataCargoPersona = $cloud->row("
	                	SELECT
	                		cargoPersona,
	                		descripcionCargoPersona
	                	FROM cat_personas_cargos 
	                	WHERE prsCargoId = ?
	                ", [$_POST["id"]]);
                	echo "El cargo " . $dataCargoPersona->cargoPersona . " se encuentra asignado a " . $cantidadIdEnUso . " expedientes, por lo que no se puede eliminar.";
                }
			break;

			case "sucursal-departamento":
				/*
					POST:
                    typeOperation
                    operation
                    id
				*/
				// Verificar que este id no esté en uso en la tabla de "th_"
                $cantidadIdEnUso = $cloud->count("
                	SELECT
                		prsExpedienteId
                	FROM th_expediente_personas
                	WHERE sucursalDepartamentoId = ? AND flgDelete = '0'
                ",[$_POST["id"]]);
                // Considerar validación para futuras tablas de planilla en la consulta de arriba o nueva consulta (por ser tabla distinta)

                if($cantidadIdEnUso == 0) {
	                $cloud->deleteById('cat_sucursales_departamentos', "sucursalDepartamentoId ", $_POST["id"]);
	                
	                $dataDepartamento = $cloud->row("
	                	SELECT
	                		sd.codSucursalDepartamento AS codSucursalDepartamento,
	                		sd.departamentoSucursal AS departamentoSucursal,
                            s.sucursal AS sucursal
	                	FROM cat_sucursales_departamentos sd
                        JOIN cat_sucursales s ON s.sucursalId = sd.sucursalId
	                	WHERE sd.sucursalDepartamentoId = ?
	                ", [$_POST["id"]]);

	                $cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó el departamento ".$dataDepartamento->departamentoSucursal ." (Código del departamento: ".$dataDepartamento->codSucursalDepartamento." - Sucursal: ".$dataDepartamento->sucursal.") del catálogo general, ");
	                echo "success";
                } else {
	                $dataDepartamento = $cloud->row("
	                	SELECT
	                		codSucursalDepartamento,
	                		departamentoSucursal
	                	FROM cat_sucursales_departamentos 
	                	WHERE sucursalDepartamentoId = ?
	                ", [$_POST["id"]]);
                	echo "El departamento " . $dataDepartamento->departamentoSucursal . " (".$dataDepartamento->codSucursalDepartamento.") se encuentra asignado a " . $cantidadIdEnUso . " expedientes, por lo que no se puede eliminar.";
                }
			break;

			case 'crud':
				/**
				  POST:
                    typeOperation
                    operation
                    id: crudId
				*/
				$datCrud = $cloud->row("
					SELECT
						nombreCrud, 
						moduloId
					FROM ejemplo_crud
					WHERE crudId = ? 
				", [$_POST['id']]);

				$datModulo = $cloud->row("
	        		SELECT 
	        			modulo 
	        		FROM conf_modulos
	        		WHERE moduloId = ?
	        	", [$datCrud->moduloId]);

	        	$cloud->deleteById('ejemplo_crud','crudId',$_POST['id']);

	        	$cloud->writeBitacora("movDelete","(".$fhActual.") Eliminó el crud: ".$datCrud->nombreCrud." del módulo ".$datModulo->modulo);
	        	
				echo "success";
			break;

			case 'tipo-producto':
				/**
				 * tableData = tipoProductoId^nombretipoProducto
				 */
				$tableData  = explode("^", $_POST["id"]);
				$queryExist = "
	        		SELECT 
	        			productoId
	        		FROM prod_productos
	        		WHERE unidadMedidaId = ? AND flgDelete = 0
	        	";
	        	$existe = $cloud->count($queryExist,[$tableData[0]]);
	        	if ($existe==0) {
	        		$cloud->deleteById('cat_inventario_tipos_producto','tipoProductoId',$tableData[0]);

		        	$cloud->writeBitacora("movDelete","(".$fhActual.") Eliminó el Tipo de Producto: ".$tableData[1]);
		        	
					echo "success";
	        	}else{
	        		echo "El Tipo de producto: ".$tableData[1]." ya tiene productos asignados, por lo que no se puede eliminar.";
	        	}
			break;

			case 'bodega-sucursal':
				/**
				 * tableData = bodegaId^bodegaSucursal
				 */
				$tableData  = explode("^", $_POST["id"]);
				$queryExist = "
	        		SELECT 
	        			bodegaId
	        		FROM inv_ubicaciones
	        		WHERE bodegaId = ? AND flgDelete = 0
	        	";
	        	$existe = $cloud->count($queryExist,[$tableData[0]]);
	        	if ($existe==0) {
	        		$cloud->deleteById('cat_sucursales_bodegas','bodegaId',$tableData[0]);

		        	$cloud->writeBitacora("movDelete","(".$fhActual.") Eliminó la Bodega: ".$tableData[1]);
		        	
					echo "success";
	        	}else{
	        		echo "La Bodega: ".$tableData[1]." ya tiene ubicaciones asignados, por lo que no se puede eliminar.";
	        	}
			break;

			case 'ubicacion-bodega':
				/**
				 * tableData = inventarioUbicacionId ^ nombreUbicacion
				 */
				$tableData = explode("^",$_POST["id"]);
				$queryExist = "
	        		SELECT 
	        			inventarioUbicacionId
	        		FROM inv_ubicaciones
	        		WHERE ubicacionSuperiorId = ? AND flgDelete = 0
	        	";
	        	$existe = $cloud->count($queryExist,[$tableData[0]]);
	        	if ($existe==0) {
	        		$cloud->deleteById('inv_ubicaciones','inventarioUbicacionId',$tableData[0]);

		        	$cloud->writeBitacora("movDelete","(".$fhActual.") Eliminó la ubicación: ".$tableData[1]);
		        	
					echo "success";
	        	}else{
	        		echo "La Ubicación: ".$tableData[1]." ya tiene subniveles asignados, por lo que no se puede eliminar.";
	        	}
			break;
			
			case 'pais':
				/*
					POST:
					paisId
					pais
				*/
				$existeDepartamento = $cloud->count("
					SELECT paisDepartamentoId FROM cat_paises_departamentos
					WHERE paisId = ? AND flgDelete = ?
				", [$_POST["paisId"], 0]);
				if($existeDepartamento > 0) {
					$existeMunicipio = $cloud->count("
						SELECT pm.paisMunicipioId AS paisMunicipioId
						FROM cat_paises_municipios pm
						JOIN cat_paises_departamentos pd ON pd.paisDepartamentoId = pm.paisDepartamentoId
						WHERE pd.paisId = ? AND pm.flgDelete = ?
					", [$_POST["paisId"], 0]);
					if($existeMunicipio > 0) {
						$dataMunicipios = $cloud->rows("
							SELECT pm.paisMunicipioId AS paisMunicipioId
							FROM cat_paises_municipios pm
							JOIN cat_paises_departamentos pd ON pd.paisDepartamentoId = pm.paisDepartamentoId
							WHERE pd.paisId = ? AND pm.flgDelete = ?
						", [$_POST["paisId"], 0]);
						foreach($dataMunicipios as $municipio) {
							$cloud->deleteById('cat_paises_municipios', 'paisMunicipioId', $municipio->paisMunicipioId);
						}
					} else {
						// No tiene municipios, solo eliminar departamentos
					}
					$cloud->deleteById('cat_paises_departamentos', 'paisId', $_POST["paisId"]);
				} else {
					// No tiene departamentos, solo eliminar pais
				}

				$cloud->deleteById('cat_paises', 'paisId', $_POST["paisId"]);
				$cloud->writeBitacora("movDelete", "(".$fhActual.") Eliminó el país: $_POST[pais]");

				echo "success";
			break;

			case 'departamento':
				/*
					POST:
					paisDepartamentoId
					departamentoPais
				*/
				$existeMunicipio = $cloud->count("
					SELECT paisMunicipioId
					FROM cat_paises_municipios
					WHERE paisDepartamentoId = ? AND flgDelete = ?
				", [$_POST["paisDepartamentoId"], 0]);
				if ($existeMunicipio > 0) {
					$cloud->deleteById('cat_paises_municipios', 'paisDepartamentoId',$_POST["paisDepartamentoId"]);
				} else {
					// No tiene municipios, solo eliminar departamento
				}
			
				$cloud->deleteById('cat_paises_departamentos', 'paisDepartamentoId', $_POST["paisDepartamentoId"]);
				$cloud->writeBitacora("movDelete", "(".$fhActual.") Eliminó el departamento: $_POST[paisDepartamentoId]");
			
				echo "success";
				break;

				case 'municipio':
					/*
						POST:
						paisMunicipioId
						municipioPais
					*/
					$existeMunicipio = $cloud->count("
						SELECT paisMunicipioId
						FROM cat_paises_municipios
						WHERE paisMunicipioId = ? AND flgDelete = ?
					", [$_POST["paisMunicipioId"], 0]);

				
					$cloud->deleteById('cat_paises_municipios', 'paisMunicipioId', $_POST["paisMunicipioId"]);
					$cloud->writeBitacora("movDelete", "(".$fhActual.") Eliminó el municipio: $_POST[paisMunicipioId]");
				
					echo "success";
					break;
				case "nueva-persona-sucursal":

					$cloud->deleteById('conf_personas_sucursales', 'personaSucursalId', $_POST["personaSucursalId"]);
					$cloud->writeBitacora("movDelete", "(".$fhActual.") Eliminó la persona: $_POST[nombrePersona] de la sucursal: $_POST[sucursal]");
				
					echo "success";
				break;	
				case "permisos-DTE":

					$cloud->deleteById('conf_personas_sucursales_dte', 'personaSucursalDTEId', $_POST["personaSucursalDTEId"]);
					$cloud->writeBitacora("movDelete", "(".$fhActual.") Se eliminó el permiso: $_POST[tipoDTE] de la persona: $_POST[personaSucursalId]");
				
					echo "success";
				break;
				case "eliminar-sucursal-persona":
					/*
					typeOperation: delete
					operation: eliminar-sucursal-persona
					sucursalId: 7
					personaId: 71
					*/

					$cloud->deleteById('conf_personas_sucursales', 'personaSucursalId', $_POST["personaSucursalId"]);
					$cloud->writeBitacora("movDelete", "(".$fhActual.") Eliminó la sucursal : $_POST[sucursalId] de la persona: $_POST[personaId]");
				
					echo "success";
				break;
				case "eliminar-bodega-persona":
					/*
					typeOperation: delete
					operation: eliminar-bodega-persona
					bodegaId: 4
					personaSucursalBodegaId: 2
					personaSucursalId: 64
					*/

					$cloud->deleteById('conf_personas_sucursales_bodegas', 'personaSucursalBodegaId', $_POST["personaSucursalBodegaId"]);
					$cloud->writeBitacora("movDelete", "(".$fhActual.") Eliminó la bodega : $_POST[bodegaId] de la persona: $_POST[personaId]");
					echo "success";
				case "notificacion-persona":
					$cloud->deleteById("conf_notificacion_persona","notificacionPersonaId",$_POST["id"]);
					$cloud->writeBitacora("movDelete","(".$fhActual.") Eliminó la empleado de lista  : $_POST[lista] de la persona: $_POST[persona]");
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