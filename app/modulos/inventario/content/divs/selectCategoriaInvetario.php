<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
header('Content-Type: application/json; charset=utf-8');
$where = (isset($_POST['busquedaSelect']) ? "AND (nombreCategoria LIKE '%$_POST[busquedaSelect]%' OR abreviaturaCategoria LIKE '%$_POST[busquedaSelect]%')" : "");

$data = $cloud->rows("SELECT inventarioCategoriaId,nombreCategoria,abreviaturaCategoria,flgPrincipal FROM cat_inventario_categorias
WHERE flgDelete = ? AND flgPrincipal = ? $where", [0, $_POST["flgPrincipal"]]);

if (!empty($data)) {
    foreach ($data as $item) {
        $udm[] = array("id" => $item->inventarioCategoriaId, "text" => $item->nombreCategoria, "abreviaturaCategoria" => $item->abreviaturaCategoria);
    }
    echo json_encode($udm);
} else {
    echo json_encode(array("id" => NULL, "text" => "Sin resultados"));
}