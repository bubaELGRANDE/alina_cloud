<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataVacacionActual = $cloud->row("
    SELECT
        tipoVacacion
    FROM view_expedientes
    WHERE estadoPersona = ? AND estadoExpediente = ? AND personaId = ?
    ORDER BY nombreCompleto
    ", ['Activo','Activo',$_POST['personaId']]);

    $dataRetornar = json_encode(
        array(
        "tipoVacacionActual" =>  $dataVacacionActual->tipoVacacion,
        "tipoVacacionNueva" => ($dataVacacionActual->tipoVacacion == "Individuales" ? "Colectivas" : "Individuales")
        )
    );
    echo $dataRetornar;
?>