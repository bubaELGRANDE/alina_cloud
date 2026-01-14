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
    WHERE CONCAT(
            IFNULL(apellido1, '-'),
            ' ',
            IFNULL(apellido2, '-'),
            ', ',
            IFNULL(nombre1, '-'),
            ' ',
            IFNULL(nombre2, '-')
    ) LIKE ?
    AND flgDelete = ?;
", ["%" . $_POST['busquedaSelect'] . "%", 0]);

$n = 0;
foreach ($dataEmpleados as $dataEmpleado) {
    $n++;
    $json[] = ["id" => $dataEmpleado->personaId, "text" => $dataEmpleado->nombreCompleto];
}

if ($n > 0) {
    echo json_encode($json);
} else {
    // No retornar nada para evitar error "null"
    $json[] = ['id' => '', 'text' => 'No se encontraron resultados...'];
    echo json_encode($json);
}
?>