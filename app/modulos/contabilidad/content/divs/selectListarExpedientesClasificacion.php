<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataExpedientes = $cloud->rows("
        SELECT 
            exp.prsExpedienteId as prsExpedienteId, 
            exp.personaId as personaId,
            exp.sucursalDepartamentoId as sucursalDepartamentoId, 
            CONCAT(
                IFNULL(per.apellido1, '-'),
                ' ',
                IFNULL(per.apellido2, '-'),
                ', ',
                IFNULL(per.nombre1, '-'),
                ' ',
                IFNULL(per.nombre2, '-')
            ) AS nombreCompleto,
            car.cargoPersona as cargoPersona
        FROM th_expediente_personas exp
        LEFT JOIN th_personas per ON per.personaId = exp.personaId
        LEFT JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
        WHERE exp.flgDelete = ? AND exp.estadoExpediente = ? AND exp.clasifGastoSalarioId = ?
        ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
    ", [0, 'Activo', $_POST['clasifGastoSalarioId']]);
    $n = 0;
    foreach($dataExpedientes as $expedientes) {
        $output[] = array("id" => $expedientes->prsExpedienteId, "valor" => "$expedientes->nombreCompleto ($expedientes->cargoPersona)");
        $n ++;
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>