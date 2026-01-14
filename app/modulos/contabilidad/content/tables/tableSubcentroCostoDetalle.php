<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();


$dataClasificacionDetalle = $cloud->rows("
        SELECT 
            centroCostoDetalleId,
            nombreCentroCostoDetalle
        FROM conta_centros_costo_detalle 
        WHERE centroCostoId = ? AND flgDelete = ?
        ORDER BY centroCostoId
    ", [$_POST['centroCostoId'], 0]);
$n = 0;
foreach ($dataClasificacionDetalle as $dataClasificacionDetalle) {
    $n += 1;

    $clasificacion = '
            <b><i class="fas fa-user"></i></b> ' . $dataClasificacionDetalle->nombreCentroCostoDetalle . '
        ';

    $jsonEliminar = array(
        "typeOperation"                 => "delete",
        "operation"                     => "subcuenta-detalle"
    );

    $acciones = '
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarClasificacion(' . htmlspecialchars(json_encode($jsonEliminar)) . ');">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
        ';

    $output['data'][] = array(
        $n, // es #, se dibuja solo en el JS de datatable
        $clasificacion,
        $acciones
    );
} // foreach

if ($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data' => ''));
}
