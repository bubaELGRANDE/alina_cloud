<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$lista = isset($_POST['categoria']) ? (string) $_POST['categoria'] : null;

$condiciones = ["c.flgDelete = 0"];

if ($lista) {
    $condiciones[] = "c.categoria = '{$lista}'";
}

$whereSQL = implode(" AND ", $condiciones);

$data = $cloud->rows("SELECT 
    c.notificacionPersonaId,
    CONCAT(
        COALESCE(p.apellido1, '-'), ' ',
        COALESCE(p.apellido2, '-'), ', ',
        COALESCE(p.nombre1, '-'), ' ',
        COALESCE(p.nombre2, '-')
    ) AS nombreCompleto,
    c.categoria,
    c.correo
FROM conf_notificacion_persona c
JOIN th_personas p ON c.personaId = p.personaId
WHERE {$whereSQL}
ORDER BY c.categoria, nombreCompleto");

$n = 0;

foreach ($data as $item) {
    $n += 1;
    $persona = '<b>Nombre: </b> ' . $item->nombreCompleto . '<br><b>Correo:  </b> ' . $item->correo;

    $jsonEliminar = array(
        "typeOperation" => "delete",
        "operation" => "notificacion-persona",
        "id" => $item->notificacionPersonaId,
        "lista" => $item->categoria,
        "persona" => $item->nombreCompleto
    );
    $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

    $acciones = '  <button type="button" onclick="event.stopPropagation(); deleteDetalle(' . $funcionEliminar . ');" class="btn btn-danger btn-sm ttip">
    <i class="fas fa-trash-alt"></i>
    <span class="ttiptext">Eliminar</span>
    </button>';

    $output['data'][] = array(
        $n,
        $item->categoria,
        $persona,
        $acciones
    );
}
// foreach
if ($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data' => ''));
}

?>