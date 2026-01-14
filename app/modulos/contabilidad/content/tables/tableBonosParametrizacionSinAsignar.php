<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataBonosSinAsignar = $cloud->rows("
        SELECT
            exp.personaId AS personaId,
            exp.nombreCompleto AS nombreCompleto,
            exp.cargoPersona AS cargoPersona
        FROM view_expedientes exp
        WHERE exp.estadoPersona = ? AND exp.estadoExpediente = ? AND (exp.personaId NOT IN (
            SELECT bpd.personaId FROM conf_bonos_personas_detalle bpd
            WHERE bpd.personaId = exp.personaId AND bpd.flgDelete = 0
        ) AND exp.personaId NOT IN (164, 222))
        ORDER BY exp.apellido1, exp.apellido2, exp.nombre1, exp.nombre2
    ", ["Activo", "Activo"]);

    $n = 0;
    foreach($dataBonosSinAsignar as $bonoSinAsignar) {
        $n++;

        $columnaEmpleado = $bonoSinAsignar->nombreCompleto;
        $columnaCargo = $bonoSinAsignar->cargoPersona; 

        $output['data'][] = array(
            $n, 
            $columnaEmpleado,
            $columnaCargo
        );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        $output['data'] = '';
        // No retornar nada para evitar error "null"
        echo json_encode($output); 
    }
?>