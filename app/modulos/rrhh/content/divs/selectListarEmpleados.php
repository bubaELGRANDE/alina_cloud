<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataEmpleados = $cloud->rows("
        SELECT
            personaId, 
            CONCAT(
                IFNULL(apellido1, '-'),
                ' ',
                IFNULL(apellido2, '-'),
                ', ',
                IFNULL(nombre1, '-'),
                ' ',
                IFNULL(nombre2, '-')
            ) AS nombreCompleto
        FROM th_personas
        WHERE prsTipoId = ? AND estadoPersona = ? AND flgDelete = ?
        ORDER BY apellido1, apellido2, nombre1, nombre2
    ", ['1', $_POST['estadoPersona'], '0']);

    $n = 0;
    foreach($dataEmpleados as $dataEmpleados) {
        $output[] = array("id" => $dataEmpleados->personaId, "valor" => $dataEmpleados->nombreCompleto);
        $n += 1;
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>