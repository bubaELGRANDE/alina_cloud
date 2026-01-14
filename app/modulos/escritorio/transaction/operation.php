<?php 
	include("../../../../libraries/includes/logic/mgc/datos94.php");

	$yearBD = "_" . date("Y");
	$fhActual = date("Y-m-d H:i:s");

	$typeOperation = (isset($_POST["typeOperation"])) ? $_POST["typeOperation"] : "no-definida";
	$operation = (isset($_POST["operation"])) ? $_POST["operation"] : "no-definida";

    if(isset($_SESSION["usuarioId"])) {
        // Verificar si la persona continua activa (en caso que se dé de baja y tenga su sesión abierta)
        $dataEstadoPersona = $cloud->row("
            SELECT
                estadoPersona
            FROM th_personas
            WHERE personaId = ?
        ", [$_SESSION['personaId']]);
        // Verificar si su usuario sigue activo (en caso que se suspenda su acceso desde admin. usuarios)
        $dataEstadoUsuario = $cloud->row("
        	SELECT
        		flgMensaje,
        		estadoUsuario
        	FROM conf_usuarios
        	WHERE usuarioId = ?
        ", [$_SESSION['usuarioId']]);
        if($dataEstadoPersona->estadoPersona == "Activo" && $dataEstadoUsuario->estadoUsuario == "Activo" && ($dataEstadoUsuario->flgMensaje == "" || is_null($dataEstadoUsuario->flgMensaje))) {
			switch($typeOperation) {
				case 'insert':
					// Definir variables / permisos
					include("insert.php");
				break;

				case 'update':
					// Definir variables / permisos
					include("update.php");
				break;

				case 'delete':
					// Definir variables / permisos
					include("delete.php");
				break;

				default:
					echo "Operación no definida.";
				break;
			}
		} else {
            // Cerrar su sesión
            echo 'logout-status';
		}
    } else {
    	// Cerrar sesión por inactividad
        echo 'logout-timeout';
    }
?>