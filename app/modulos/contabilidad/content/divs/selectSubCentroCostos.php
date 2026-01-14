<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataCentroDetalle = $cloud->rows("  
SELECT centroCostoDetalleId,nombreCentroCostoDetalle FROM conta_centros_costo_detalle
WHERE centroCostoId = ? AND flgDelete = ?", [$_POST['centroCostoId'], 0]);

$n = 0;
$output = ['<option value="">Seleccione una opción</option>'];
foreach ($dataCentroDetalle as $subcentros) {
    $n++;
    $output[] = '<option value="'.$subcentros->centroCostoDetalleId.'">'.$subcentros->nombreCentroCostoDetalle.'</option>';
}

if ($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(null);
}
?>