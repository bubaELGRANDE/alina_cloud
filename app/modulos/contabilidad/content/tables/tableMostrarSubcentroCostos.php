<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();


$dataClasificacionDetalle = $cloud->rows("
        SELECT 
            subCentroCostoId,
            codigoSubcentroCosto,
            nombreSubcentroCosto
        FROM conta_subcentros_costo 
        WHERE flgDelete = ?
        ORDER BY subCentroCostoId
    ", [0]);
$n = 0;
foreach ($dataClasificacionDetalle as $dataClasificacionDetalle) {
    $n += 1;

    $clasificacion = '
            <b><i class="fas fa-user"></i></b> ' . $dataClasificacionDetalle->nombreSubcentroCosto . '
        ';

    $jsonEditar = array(
        "typeOperation"              => "update",
        "subCentroCostoId"           => $dataClasificacionDetalle->subCentroCostoId,
        "tblsubCentroDetalle"          => "tblsubCentroDetalle"
    );

    $acciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalNuevoSubCentroCosto(' . htmlspecialchars(json_encode($jsonEditar)) . ');">
                <i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
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
