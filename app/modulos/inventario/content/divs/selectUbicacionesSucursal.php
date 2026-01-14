<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();


$data = $cloud->rows("SELECT inventarioUbicacionId,nombreUbicacion,codigoUbicacion FROM inv_ubicaciones WHERE sucursalId = ? AND flgDelete = 0", [$_POST["sucursalId"]]);

if (!empty($data)) {
    foreach ($data as $item) {
        $udm[] = array("id" => $item->inventarioUbicacionId, "text" => "($item->codigoUbicacion) $item->nombreUbicacion");
    }
    echo json_encode($udm);
} else {
    echo json_encode(array("id" => NULL, "text" => "Sin resultados"));
}
