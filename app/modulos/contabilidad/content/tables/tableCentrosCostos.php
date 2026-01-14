<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();


$dataClasificacion = $cloud->rows("
        SELECT
            centroCostoId,codigoCentroCosto ,nombreCentroCosto
        FROM conta_centros_costo
        WHERE flgDelete = ?
        ORDER BY nombreCentroCosto
    ", [0]);
$n = 0;
foreach ($dataClasificacion as $dataClasificacion) {
    $n += 1;

    $totalClasifDetalle = $cloud->count("
            SELECT centroCostoId FROM conta_centros_costo_detalle
            WHERE centroCostoId = ? AND flgDelete = ?
        ", [$dataClasificacion->centroCostoId, 0]);

    $clasificacion = '
            <b><i class="fas fa-user"></i></b> ' . $dataClasificacion->nombreCentroCosto . '
        ';

    $jsonEditar = array(
        "typeOperation"                     => "update",
        "centroCostoId"           => $dataClasificacion->centroCostoId,
        "tblCentroCosto"                         => "tblCentroCosto"

    );

    $jsonEliminar = array(
        "typeOperation"                     => "delete",
        "operation"                         => "UDN-clasificacion",
        "centroCostoId"           => $dataClasificacion->centroCostoId,
        "tblCentroCosto"                         => "tblCentroCosto"

    );

    $jsonDetalle = array(
        "centroCostoId"        => $dataClasificacion->centroCostoId,
        "tituloModal"          => "Clasificación de : $dataClasificacion->nombreCentroCosto",
        "tblCentroCosto"       => "tblCentroCosto"
    );

    $acciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalNuevoCentroCosto(' . htmlspecialchars(json_encode($jsonEditar)) . ');">
                <i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
            </button>
            
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalNuevoCentroCostoDetalle(' . htmlspecialchars(json_encode($jsonDetalle)) . ');">
                <span class="badge rounded-pill bg-light" style="color: black;">' . $totalClasifDetalle . '</span>
                <i class="fas fa-list-ul"></i>
                <span class="ttiptext">Subcentros de costos</span>
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
