<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$where = (isset($_POST['busquedaSelect']) ? "AND (nombreUnidadMedida LIKE '%$_POST[busquedaSelect]%' OR abreviaturaUnidadMedida LIKE '%$_POST[busquedaSelect]%')" : "");


$dataUDM = $cloud->rows("SELECT unidadMedidaId, nombreUnidadMedida,  abreviaturaUnidadMedida 
    FROM cat_unidades_medida 
    WHERE flgDelete = 0 $where
    ORDER BY nombreUnidadMedida
");

if (!empty($dataUDM)) {
    foreach ($dataUDM as $dataU) {
        $udm[] = array("id" => $dataU->unidadMedidaId, "text" => $dataU->nombreUnidadMedida . ' (' . $dataU->abreviaturaUnidadMedida . ')');
    }
    echo json_encode($udm);
} else {
    echo json_encode(array("id" => NULL, "text" => "Sin resultados"));
}
