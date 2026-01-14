<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataCargosExpediente = $cloud->rows("
		SELECT
			exp.prsExpedienteId AS prsExpedienteId,
			pc.cargoPersona AS cargoPersona
		FROM th_expediente_personas exp
		JOIN cat_personas_cargos pc ON pc.prsCargoId = exp.prsCargoId
		WHERE exp.personaId = ? AND exp.flgDelete = ?
    ", [$_POST['personaId'], 0]);

    $n = 0;
    foreach($dataCargosExpediente as $cargoExpediente) {
        $output[] = array("id" => $cargoExpediente->prsExpedienteId, "valor" => $cargoExpediente->cargoPersona);
        $n += 1;
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>