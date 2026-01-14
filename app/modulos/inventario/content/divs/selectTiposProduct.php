<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$data = $cloud->rows("SELECT tipoProductoId,nombreTipoProducto FROM cat_inventario_tipos_producto
WHERE flgDelete = 0 ");

if (!empty($data)) {
    foreach ($data as $item) {
        $udm[] = array("id" => $item->tipoProductoId, "text" => $item->nombreTipoProducto);
    }
    echo json_encode($udm);
} else {
    echo json_encode(array("id" => NULL, "text" => "Sin resultados"));
}
