<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataContactos = $cloud->row("
        SELECT tipoContactoId, contactoSucursal, descripcionCSucursal FROM cat_sucursales_contacto WHERE flgDelete = 0 AND sucursalId =? AND sucursalContactoId  =?
    ", [$_POST["idSucursal"], $_POST["idContacto"]]);

$salida = array(
    "tipoContactoId" => $dataContactos->tipoContactoId,
    "contactoSucursal" => $dataContactos->contactoSucursal,
    "descripcionCSucursal" => $dataContactos->descripcionCSucursal,
    
);
echo json_encode($salida);