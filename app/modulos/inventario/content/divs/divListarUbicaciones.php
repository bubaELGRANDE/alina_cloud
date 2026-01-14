<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

$sucursalId = $_POST["sucursalId"];

$data = $cloud->rows("
    SELECT ubicacionId AS id, nombreUbicacion AS nombre, tipoUbicacion AS tipo
    FROM cat_sucursales_ubicaciones
    WHERE sucursalId = $sucursalId AND flgDelete = 0
    ORDER BY nombreUbicacion ASC
");

echo json_encode($data);
?>