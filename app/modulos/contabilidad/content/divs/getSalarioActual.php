<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $existeSalario = $cloud->count("
        SELECT
            expedienteSalarioId, 
            prsExpedienteId, 
            tipoSalario, 
            fechaInicioVigencia, 
            salario, 
            descripcionSalario
        FROM th_expediente_salarios
        WHERE prsExpedienteId = ? AND estadoSalario = 'Activo' AND flgDelete = '0'
        LIMIT 1
    ", [$_POST["id"]]);

    if($existeSalario == 0) {
        echo "0.00";
    } else {
        $dataSalarioActual = $cloud->row("
            SELECT
                expedienteSalarioId, 
                prsExpedienteId, 
                tipoSalario, 
                fechaInicioVigencia, 
                salario, 
                descripcionSalario
            FROM th_expediente_salarios
            WHERE prsExpedienteId = ? AND estadoSalario = 'Activo' AND flgDelete = '0'
            LIMIT 1
        ", [$_POST["id"]]);
        echo number_format($dataSalarioActual->salario, 2, '.', ',');
    }
?>