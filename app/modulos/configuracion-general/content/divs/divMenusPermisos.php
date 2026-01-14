<?php 
	/*
		POST:
		flgStep: step2
		totalContinuar: 2
		flgContinuarActual: 1
		usuarioId[]: 2
		moduloId: 3
		menuId[]: 
	*/
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

	$n = 0;
	foreach ($_POST["menuId"] as $menuId) {
		$n += 1;

		$hideDiv = ($n > 1) ? 'style="display: none;"' : '';

		$dataNombreMenu = $cloud->row("
			SELECT
				menu 
			FROM conf_menus
			WHERE menuId = ?
		", [$menuId]);

		if(in_array(10, $_SESSION["arrayPermisos"])) { // Mostrar todos los permisos
			$dataPermisosMenu = $cloud->rows("
				SELECT
					menuPermisoId,
					permisoMenu
				FROM conf_menus_permisos
				WHERE menuId = ? AND flgDelete = '0'
			", [$menuId]);
		} else { // Solo mostrar los permisos que tiene asignados y NO MOSTRAR Desarrollo
			$dataPermisosMenu = $cloud->rows("
	            SELECT
	            	mp.menuPermisoId AS menuPermisoId,
	                mp.permisoMenu AS permisoMenu
	            FROM conf_permisos_usuario perm
	            JOIN conf_menus_permisos mp ON mp.menuPermisoId = perm.menuPermisoId
	            WHERE mp.permisoMenu<>'Desarrollo' AND perm.usuarioId = ? AND mp.menuId = ? AND
	            (perm.flgDelete = '0' AND mp.flgDelete = '0')
			", [$_SESSION['usuarioId'], $menuId]);
		}

		$divPermisos = '';
		$x = 0;
		foreach ($dataPermisosMenu as $dataPermisosMenu) {
			$x += 1;
			$divPermisos .= '
				<div class="form-check">
					<input type="checkbox" class="form-check-input" id="checkPermisos-'.$n.'-'.$x.'" name="checkPermisos'.$n.'[]" value="'.$dataPermisosMenu->menuPermisoId.'">
					<label class="form-check-label" for="checkPermisos-'.$n.'-'.$x.'">
						'.$dataPermisosMenu->permisoMenu.'
					</label>
				</div>
			';
		}

		// No debería suceder ya que se inserta por default "Desarrollo"
		if($x == 0) {
			$divPermisos = '<br>No se han asignado permisos a este menú.';
		} else {
		}

		echo '
			<div id="divPM'.$n.'" '.$hideDiv.'>
				<b><i class="fas fa-bars"></i> Menú: </b>' . $dataNombreMenu->menu . '<br><br>
				<b><i class="fas fa-user-lock"></i> Seleccionar permisos:</b>
				'.$divPermisos.'
			</div>
		';
	}
?>