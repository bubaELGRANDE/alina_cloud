<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$where = (isset($_POST['busquedaSelect']) ? "AND (nombreMarca LIKE '%$_POST[busquedaSelect]%' OR abreviaturaMarca LIKE '%$_POST[busquedaSelect]%')" : "");


$dataUDM = $cloud->rows("SELECT marcaId,nombreMarca,abreviaturaMarca FROM cat_inventario_marcas
    WHERE flgDelete = 0 $where
    ORDER BY nombreMarca
");

if (!empty($dataUDM)) {
    foreach ($dataUDM as $dataU) {
        $udm[] = array("id" => $dataU->marcaId, "text" => $dataU->nombreMarca . ' (' . $dataU->abreviaturaMarca . ')');
    }
    echo json_encode($udm);
} else {
    echo json_encode(array("id" => NULL, "text" => "Sin resultados"));
}
