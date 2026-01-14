<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$tipoMagnitud = $_POST["tipoMagnitud"] ?? 0;

$dataUDM = $cloud->rows("SELECT
unidadMedidaId,nombreUnidadMedida,abreviaturaUnidadMedida
FROM cat_unidades_medida WHERE tipoMagnitud =  ? AND
flgDelete = ?", [$tipoMagnitud, 0]);

if (!empty($dataUDM)) {
    foreach ($dataUDM as $dataU) {
        $udm[] = array(
            "id" => $dataU->unidadMedidaId,
            "text" => $dataU->nombreUnidadMedida . ' (' . $dataU->abreviaturaUnidadMedida . ')',
            "abreviaturaUnidadMedida" => $dataU->abreviaturaUnidadMedida
        );
    }
    echo json_encode($udm);
} else {
    echo json_encode(array("id" => NULL, "text" => "Sin resultados"));
}
