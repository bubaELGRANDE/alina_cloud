<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    // arrayFormData = menuId ^ usuarioId ^ nombrePersona ^ modulo ^ menu ^ tipoMenu
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    if($arrayFormData[1] == $_SESSION['usuarioId'] && !in_array(10, $_SESSION["arrayPermisos"])) { // Un usuario no puede editarse sus propios permisos, a excepción que sea un desarrollador !en_arreglo
    	// Se cargará la interfaz solo para visualización
?>
		<div class="row mb-4">
		    <div class="col-lg-12">
		    	<b><i class="fas fa-user"></i> Usuario: </b> <?php echo $arrayFormData[2]; ?>
		    </div>
		</div>
		<div class="row mb-4">
		    <div class="col-lg-12">
		    	<b><i class="fas fa-folder-open"></i> Módulo: </b> <?php echo $arrayFormData[3]; ?>
		    </div>
		</div>
		<div class="row mb-4">
		    <div class="col-lg-12">
		    	<b><i class="fas fa-bars"></i> Menú: </b> <?php echo $arrayFormData[4]; ?>
		    </div>
		</div>
		<div class="row mb-4">
			<div class="col-lg-12">
				<b><i class="fas fa-user-lock"></i> Seleccionar permisos:</b>
				<?php 
					if(in_array(10, $_SESSION["arrayPermisos"])) { // Mostrar todos los permisos
						$dataPermisosMenu = $cloud->rows("
							SELECT
								menuPermisoId,
								permisoMenu
							FROM conf_menus_permisos
							WHERE menuId = ? AND flgDelete = '0'
						", [$arrayFormData[0]]);
					} else { // Solo mostrar los permisos que tiene asignados y NO MOSTRAR Desarrollo
						$dataPermisosMenu = $cloud->rows("
				            SELECT
				            	mp.menuPermisoId AS menuPermisoId,
				                mp.permisoMenu AS permisoMenu
				            FROM conf_permisos_usuario perm
				            JOIN conf_menus_permisos mp ON mp.menuPermisoId = perm.menuPermisoId
				            WHERE mp.permisoMenu<>'Desarrollo' AND perm.usuarioId = ? AND mp.menuId = ? AND
				            (perm.flgDelete = '0' AND mp.flgDelete = '0')
						", [$_SESSION['usuarioId'], $arrayFormData[0]]);
					}

					$x = 0;
					foreach ($dataPermisosMenu as $dataPermisosMenu) {
						$x += 1;

						// Comparar si se le ha asignado este permiso
						$existePermisoAsignado = $cloud->count("
							SELECT
								menuPermisoId
							FROM conf_permisos_usuario
							WHERE menuPermisoId = ? AND usuarioId = ? AND flgDelete = '0'
						", [$dataPermisosMenu->menuPermisoId, $arrayFormData[1]]);

						$checked = ($existePermisoAsignado == 0) ? "" : "checked";

						if($arrayFormData[5] == "dropdown" && $dataPermisosMenu->permisoMenu == "Dropdown") { // Prevenir que se quite el permiso "dropdown"
							echo '
								<div class="form-check">
									<input type="checkbox" class="form-check-input" id="checkPermisos-'.$x.'" '.$checked.' disabled>
									<label class="form-check-label" for="checkPermisos-'.$x.'">
										'.$dataPermisosMenu->permisoMenu.'
									</label>
								</div>
							';
						} else {
							echo '
								<div class="form-check">
									<input type="checkbox" class="form-check-input" id="checkPermisos-'.$x.'" '.$checked.' disabled>
									<label class="form-check-label" for="checkPermisos-'.$x.'">
										'.$dataPermisosMenu->permisoMenu.'
									</label>
								</div>
							';
						}
					}
					if($x == 0) {
						echo "<br>Permisos de desarrollo.";
					} else {
						// Se dibujaron checkbox
					}
				?>
			</div>
		</div>
		<script>
			$(document).ready(function() {
				$('#btnModalAccept').prop("disabled",true);
			});
		</script>
<?php
    } else { // Acá es el editar
?>
		<input type="hidden" id="typeOperation" name="typeOperation" value="update">
		<input type="hidden" id="operation" name="operation" value="menu-permisos-usuario">
		<div class="row mb-4">
		    <div class="col-lg-12">
		    	<b><i class="fas fa-user"></i> Usuario: </b> <?php echo $arrayFormData[2]; ?>
		    </div>
		</div>
		<div class="row mb-4">
		    <div class="col-lg-12">
		    	<b><i class="fas fa-folder-open"></i> Módulo: </b> <?php echo $arrayFormData[3]; ?>
		    </div>
		</div>
		<div class="row mb-4">
		    <div class="col-lg-12">
		    	<b><i class="fas fa-bars"></i> Menú: </b> <?php echo $arrayFormData[4]; ?>
		    </div>
		</div>
		<div class="row mb-4">
			<div class="col-lg-12">
				<b><i class="fas fa-user-lock"></i> Seleccionar permisos:</b>
				<?php 
					if(in_array(10, $_SESSION["arrayPermisos"])) { // Mostrar todos los permisos
						$dataPermisosMenu = $cloud->rows("
							SELECT
								menuPermisoId,
								permisoMenu
							FROM conf_menus_permisos
							WHERE menuId = ? AND flgDelete = '0'
						", [$arrayFormData[0]]);
					} else { // Solo mostrar los permisos que tiene asignados y NO MOSTRAR Desarrollo
						$dataPermisosMenu = $cloud->rows("
				            SELECT
				            	mp.menuPermisoId AS menuPermisoId,
				                mp.permisoMenu AS permisoMenu
				            FROM conf_permisos_usuario perm
				            JOIN conf_menus_permisos mp ON mp.menuPermisoId = perm.menuPermisoId
				            WHERE mp.permisoMenu<>'Desarrollo' AND perm.usuarioId = ? AND mp.menuId = ? AND
				            (perm.flgDelete = '0' AND mp.flgDelete = '0')
						", [$_SESSION['usuarioId'], $arrayFormData[0]]);
					}

					$flgDev = 0;

					if(!in_array(10, $_SESSION["arrayPermisos"])) { // Si no es un desarrollador, validar si escogió a un usuario con permiso de desarrollo
						$esDesarrollo = $cloud->count("
							SELECT 
								pm.menuPermisoId
							FROM conf_permisos_usuario pm
							JOIN conf_menus_permisos mp ON mp.menuPermisoId = pm.menuPermisoId
							WHERE mp.permisoMenu = 'Desarrollo' AND pm.usuarioId = ? AND mp.menuId = ? AND (pm.flgDelete = '0' AND mp.flgDelete = '0')
						", [$arrayFormData[1], $arrayFormData[0]]);
					} else { // Es un desarrollador y puede editar permisos, no es necesario hacer la consulta
						$esDesarrollo = 0;
					}


					if($esDesarrollo == 0) {
						$x = 0;
						foreach ($dataPermisosMenu as $dataPermisosMenu) {
							$x += 1;

							// Comparar si se le ha asignado este permiso
							$existePermisoAsignado = $cloud->count("
								SELECT
									menuPermisoId
								FROM conf_permisos_usuario
								WHERE menuPermisoId = ? AND usuarioId = ? AND flgDelete = '0'
							", [$dataPermisosMenu->menuPermisoId, $arrayFormData[1]]);

							$checked = ($existePermisoAsignado == 0) ? "" : "checked";

							if($arrayFormData[5] == "dropdown" && $dataPermisosMenu->permisoMenu == "Dropdown") { // Prevenir que se quite el permiso "dropdown"
								echo '
									<div class="form-check">
										<input type="checkbox" class="form-check-input" id="checkPermisos-'.$x.'" name="checkPermisos[]" value="'.$dataPermisosMenu->menuPermisoId.'" '.$checked.' disabled>
										<label class="form-check-label" for="checkPermisos-'.$x.'">
											'.$dataPermisosMenu->permisoMenu.'
										</label>
									</div>
								';
							} else {
								echo '
									<div class="form-check">
										<input type="checkbox" class="form-check-input" id="checkPermisos-'.$x.'" name="checkPermisos[]" value="'.$dataPermisosMenu->menuPermisoId.'" '.$checked.'>
										<label class="form-check-label" for="checkPermisos-'.$x.'">
											'.$dataPermisosMenu->permisoMenu.'
										</label>
									</div>
								';
							}
						}
						if($x == 0) { // No debería de suceder
							echo "<br>Permisos no asignados.";
						} else {
							// Se dibujaron checkbox
						}
					} else { // Se encontró permiso de desarrollo
						$flgDev = 1;
						echo '
							<div class="form-check">
								<input type="checkbox" class="form-check-input" id="checkPermisos-1" checked disabled>
								<label class="form-check-label" for="checkPermisos-1">
									Desarrollo
								</label>
							</div>
						';
					}
				?>
			</div>
		</div>
		<script>
			$(document).ready(function() {
				if('<?php echo $arrayFormData[5]; ?>' == "dropdown" || '<?php echo $flgDev ?>' == '1') { // deshabilitar submit si es dropdown
					button_icons("btnModalAccept", "fas fa-save", "Guardar", "disabled");
				} else {
				}

			    $("#frmModal").validate({
			        submitHandler: function(form) {
			            if($(`input[name="checkPermisos[]"]`).is(":checked")) {
			                // enviar solicitud
				            button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
				            asyncDoDataReturn(
				                "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
				                $("#frmModal").serialize(),
				                function(data) {
				                    button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
			                        let arrayData = data.split("^");
			                        if(arrayData[0] == "success") {
			                            mensaje(
			                                "Operación completada:",
			                                arrayData[1],
			                                "success"
			                            );
			                            $('#tblPermisos').DataTable().ajax.reload(null, false);
			                            $('#modal-container').modal("hide");
			                        } else {
			                            mensaje(
			                                "Aviso:",
			                                data,
			                                "warning"
			                            );
			                        }
				                }
				            );
			            } else { // no ha marcado ningún permiso
			                mensaje("Aviso:", "Seleccione al menos 1 permiso", "warning");
			            }
			        }
			    });
			});
		</script>
<?php 
	}
?>