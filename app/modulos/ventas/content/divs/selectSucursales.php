<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$where = (isset($_POST['busquedaSelect']) ? "AND (sucursal LIKE '%$_POST[busquedaSelect]%' OR numOrdenSucursal LIKE '%$_POST[busquedaSelect]%')" : "");

$data = $cloud->rows("SELECT 
    sucursalId, sucursal, numOrdenSucursal
FROM
    cat_sucursales
    WHERE flgDelete = ? $where", [0]);

if (!empty($data)) {
    foreach ($data as $item) {
        $udm[] = array("id" => $item->sucursalId, "text" => "(0$item->numOrdenSucursal) $item->sucursal");
    }
    echo json_encode($udm);
} else {
    echo json_encode(array("id" => NULL, "text" => "Sin resultados"));
}
