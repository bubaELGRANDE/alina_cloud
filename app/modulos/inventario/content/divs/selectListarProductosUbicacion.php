<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$where = (isset($_POST['busquedaSelect']) ? "AND (nombreProducto LIKE '%$_POST[busquedaSelect]%' OR codInterno LIKE '%$_POST[busquedaSelect]%')" : "");


$dataUDM = $cloud->rows("SELECT 
    up.ubicacionProductoId,
    p.codInterno,
    p.nombreProducto,
    up.existenciaProducto,
    udm.abreviaturaUnidadMedida
FROM
    inv_ubicaciones_productos up
        LEFT JOIN
    prod_productos p ON p.productoId = up.productoId
        LEFT JOIN
    cat_unidades_medida udm ON udm.unidadMedidaId = p.unidadMedidaId
    WHERE up.inventarioUbicacionId = ? AND up.flgDelete = 0 $where
    ORDER BY p.nombreProducto", [$_POST['ubicacionId']]);

if (!empty($dataUDM)) {
    foreach ($dataUDM as $dataU) {
        $udm[] = array(
            "id" => $dataU->ubicacionProductoId,
            "text" => '(' . $dataU->codInterno . ')' . $dataU->nombreProducto . '-' . $dataU->existenciaProducto . ' ' . $dataU->abreviaturaUnidadMedida
        );
    }
    echo json_encode($udm);
} else {
    echo json_encode(array("id" => NULL, "text" => "Sin resultados"));
}
