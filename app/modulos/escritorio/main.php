<?php 
  	@session_start();

  	if(isset($page) && isset($route)) {
	  	// Obtener permisos
	  	$titlePage = $dataGetPage->menu . " - " . $dataGetPage->modulo;
	  	$_SESSION["titlePage"] = $titlePage;

		if(file_exists($route . $page)) {
			// Aqui no se validan permisos porque es el escritorio
			$columnaBitacora = "movInterfaces";
			$bitacora = "(" . $fhActual . ") Ingresó a " . $titlePage . ", ";
			$cloud->writeBitacora($columnaBitacora, $bitacora);

		   include($page); // página / view del usuario
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
  		header("Location: /alina-cloud/app/");
  	}
?>