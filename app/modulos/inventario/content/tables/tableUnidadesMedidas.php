<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$datUniMed = $cloud->rows("
        SELECT 
            unidadMedidaId,
            nombreUnidadMedida,
            abreviaturaUnidadMedida,
            tipoMagnitud,
            codigoMH
        FROM cat_unidades_medida
        WHERE flgDelete = ? 
        ORDER BY codigoMH
    ", [0]);

$n = 0;
foreach ($datUniMed as $datUniMed) {
    $n += 1;
    $unidadMedida = '<b><i class="fas fa-ruler-combined"></i> Unidad de Medida: </b>' . $datUniMed->nombreUnidadMedida . '
               <br><i class="fas fa-thumbtack"></i> <b>Abreviatura: </b> ' . $datUniMed->abreviaturaUnidadMedida . '
               <br><i class="fas fa-list"></i> <b>Tipo de Magnitud: </b> ' . $datUniMed->tipoMagnitud . '
               <br><i class="fas fa-list-ol"></i> <b>Código Hacienda: </b> ' . ($datUniMed->codigoMH == "" ? '-' : $datUniMed->codigoMH);


    $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalUnidadMedida(`update^' . $datUniMed->unidadMedidaId . '`);">
                    <i class="fas fa-pen"></i>
                    <span class="ttiptext">Editar</span>
                </button>
                <button type="button" class="btn btn-primary btn-sm ttip" onClick="modalEquivalenciasUDM(`insert^' . $datUniMed->unidadMedidaId . '`);">
                    <i class="fas fa-exchange-alt"></i>
                    <span class="ttiptext">Equivalencias</span>
                </button>
                <button type="button" class="btn btn-danger btn-sm ttip" onClick="eliminarUnidadMedida(`' . $datUniMed->unidadMedidaId . '^' . $datUniMed->nombreUnidadMedida . '`);">
                    <i class="fas fa-trash-alt"></i>
                    <span class="ttiptext">Eliminar</span>
                </button>';


    $output['data'][] = array(
        $n, // es #, se dibuja solo en el JS de datatable
        $unidadMedida,
        $acciones
    );
} // foreach

if ($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data' => ''));
}