<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataContactos = $cloud->row("
        SELECT tipoContactoId, contactoPersona, visibilidadContacto, descripcionPrsContacto, flgContactoEmergencia FROM th_personas_contacto WHERE flgDelete = 0 AND personaId =? AND prsContactoId =?
    ", [$_POST["personaId"], $_POST["idContacto"]]);

$salida = array(
    "tipoContactoId" => $dataContactos->tipoContactoId,
    "contactoPersona" => $dataContactos->contactoPersona,
    "visibilidadContacto" => $dataContactos->visibilidadContacto,
    "descripcionCPersona" => $dataContactos->descripcionPrsContacto,
    "flgContactoEmergencia" => $dataContactos->flgContactoEmergencia,
);
echo json_encode($salida);