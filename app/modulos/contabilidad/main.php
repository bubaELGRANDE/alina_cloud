<?php 
  	@session_start();

  	if(isset($page) && isset($route)) {
	  	// Obtener permisos
	  	$titlePage = $dataGetPage->menu . " - " . $dataGetPage->modulo;
	  	$_SESSION["titlePage"] = $titlePage;

		if(file_exists($route . $page)) {
			// Validar que tenga permisos a la interfaz
			$existePermiso = $cloud->count("
				SELECT
					ps.menuPermisoId
				FROM conf_permisos_usuario ps
				JOIN conf_menus_permisos mp ON mp.menuPermisoId = ps.menuPermisoId
				WHERE mp.menuId = ? AND ps.usuarioId = ? AND ps.flgDelete = '0' AND mp.flgDelete = '0'
			", [$pageId, $_SESSION["usuarioId"]]);

			if($existePermiso > 0) {
				$columnaBitacora = "movInterfaces";
				$bitacora = "(" . $fhActual . ") Ingresó a " . $titlePage . ", ";
				$cloud->writeBitacora($columnaBitacora, $bitacora);

				// FORMAR arrayPermisos (ver pizarra de referencia)
			   include($page); // página / view del usuario
			} else {
				// Registrar en bitácora
				$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y visualizar otra interfaz (manejo desde consola), ");
				include "../../../../app/modulos/escritorio/content/views/403.php";
			}
		} else {
		   include "../../../../app/modulos/escritorio/content/views/404.php";
		}
?>
		<script>
			$(document).ready(function() {
				document.title = '<?php echo $titlePage; ?>';
			});
		</script>
<?php
  	} else { // Acceso por URL
  		require_once("../../../libraries/includes/logic/mgc/datos94.php"); // Se incluye todo porque manejo la URL y se interpreta como que está en este sitio directamente
  		$fhActual = date("Y-m-d H:i:s");
  		// Registrar en bitácora
		$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y visualizar otra interfaz (manejo de URL), ");
  		header("Location: /cloud-lite/app/");
  	}
?>