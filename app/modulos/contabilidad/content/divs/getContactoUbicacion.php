<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataContactos = $cloud->row("
        SELECT clienteUbicacionId, tipoContactoId, contactoCliente, descripcionContactoCliente, flgContactoPrincipal 
        FROM fel_clientes_contactos 
        WHERE flgDelete = 0 AND clienteContactoId =?
    ", [$_POST["idContacto"]]);

$salida = array(
    "tipoContactoId" => $dataContactos->tipoContactoId,
    "contactoCliente" => $dataContactos->contactoCliente,
    "flgContactoPrincipal" => $dataContactos->flgContactoPrincipal,
    "descripcionContactoCliente" => $dataContactos->descripcionContactoCliente,
    "clienteUbicacionId" => $dataContactos->clienteUbicacionId,
);
echo json_encode($salida);