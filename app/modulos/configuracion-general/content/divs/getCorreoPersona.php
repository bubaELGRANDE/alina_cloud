<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

	$queryCorreoPersona = "
		SELECT
			contactoPersona
		FROM th_personas_contacto
		WHERE personaId = ? AND contactoPersona LIKE '%@alina.jewelry%' AND tipoContactoId = '1' AND estadoContacto = 'Activo' AND flgDelete = '0'
		LIMIT 1
	";
	$existeCorreo = $cloud->count($queryCorreoPersona, [$_POST["personaId"]]);

	if($existeCorreo == 0) {
		echo '';
	} else {
		$dataCorreoPersona = $cloud->row($queryCorreoPersona, [$_POST["personaId"]]);
		echo $dataCorreoPersona->contactoPersona;
	}
?>