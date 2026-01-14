<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $whereEstado = '';
    if($_POST['estadoPersona'] == 'Activo') {
        $whereEstado = "AND pers.estadoPersona = 'Activo' AND exp.estadoExpediente = 'Activo'";
    } else {
        // Inactivos, para expedientes son: Despido, Renuncia, Abandono, Defunción, Traslado, Jubilado
        // Indicados en empleadoDarDeBaja
        $whereEstado = "AND pers.estadoPersona = 'Inactivo' AND (exp.estadoExpediente = 'Despido' OR exp.estadoExpediente = 'Renuncia' OR exp.estadoExpediente = 'Abandono' OR exp.estadoExpediente = 'Defunción' OR exp.estadoExpediente = 'Traslado' OR exp.estadoExpediente = 'Jubilado')";
    }

    $dataExpedientes = $cloud->rows("
        SELECT
        pers.personaId as personaId, 
        exp.prsExpedienteId as expedienteId,
        CONCAT(
            IFNULL(pers.apellido1, '-'),
            ' ',
            IFNULL(pers.apellido2, '-'),
            ', ',
            IFNULL(pers.nombre1, '-'),
            ' ',
            IFNULL(pers.nombre2, '-')
        ) AS nombreCompleto
        FROM th_personas pers
        JOIN th_expediente_personas exp ON pers.personaId = exp.personaId
        WHERE pers.prsTipoId = ? AND pers.flgDelete = ? $whereEstado
        ORDER BY apellido1, apellido2, nombre1, nombre2
    ", ['1', '0']);

    $n = 0;
    foreach($dataExpedientes as $dataExpedientes) {
        $output[] = array("id" => $dataExpedientes->expedienteId, "valor" => $dataExpedientes->nombreCompleto);
        $n += 1;
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>