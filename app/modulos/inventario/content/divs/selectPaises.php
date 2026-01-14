<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$where = (isset($_POST['busquedaSelect']) ? "AND (pais LIKE '%$_POST[busquedaSelect]%' OR abreviaturaPais LIKE '%$_POST[busquedaSelect]%')" : "");

$data = $cloud->rows("SELECT paisId,pais,abreviaturaPais,iconBandera FROM cat_paises
WHERE flgDelete = ? $where", [0]);

if (!empty($data)) {
    foreach ($data as $item) {
        $udm[] = array("id" => $item->paisId, "text" => $item->pais, "abreviaturaCategoria" => $item->abreviaturaPais);
    }
    echo json_encode($udm);
} else {
    echo json_encode(array("id" => NULL, "text" => "Sin resultados"));
}
