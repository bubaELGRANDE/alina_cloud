<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$rows = $cloud->rows("
    SELECT sucursalId, sucursal, direccionSucursal, urlLogoSucursal 
    FROM cat_sucursales 
    WHERE flgDelete = 0
");

$output = [];

foreach ($rows as $row) {

    $perms = $_SESSION["arrayPermisos"];

    $acciones = [
        "contactos" => (in_array(9, $perms) || in_array(24, $perms)),
        "departamentos" => true,
        "editar" => (in_array(9, $perms) || in_array(22, $perms)),
        "eliminar" => (in_array(9, $perms) || in_array(23, $perms))
    ];

    $output[] = [
        "id" => $row->sucursalId,
        "sucursal" => $row->sucursal,
        "direccion" => $row->direccionSucursal,
        "logo" => $row->urlLogoSucursal,
        "acciones" => $acciones
    ];
}

echo json_encode($output);
