<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataReincidencia = $cloud->rows("
        SELECT
            ea.expedienteAmonestacionId AS expedienteAmonestacionId,
            DATE_FORMAT( ea.fechaAmonestacion, '%d/%m/%Y') AS fechaAmonestacion,
            ea.causaFalta AS causaFalta
        FROM th_expediente_amonestaciones ea
        WHERE ea.flgDelete = ? AND ea.expedienteId = ? AND ea.expedienteAmonestacionId NOT IN (
            SELECT rein.amonestacionAnteriorId FROM th_expediente_amonestaciones rein
            WHERE rein.amonestacionAnteriorId = ea.expedienteAmonestacionId AND rein.flgDelete = 0
        )
    ", [0, $_POST["expedienteId"]]);

    $n = 0;
    foreach($dataReincidencia as $reincidencia) {
        $n += 1;
        $selectReturn[] = array("id" => $reincidencia->expedienteAmonestacionId, "valor" => "$reincidencia->fechaAmonestacion - $reincidencia->causaFalta");
    }

    if($n > 0) {
        echo json_encode($selectReturn);
    }else{
        echo json_encode(array('data'=>''));
    }