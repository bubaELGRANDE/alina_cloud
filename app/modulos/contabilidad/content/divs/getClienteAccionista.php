<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataAccionista = $cloud->row("SELECT 
        clienteAccionistaid, clienteId, nombreAccionista, paisId, nitAccionista, porcentajeParticipacion
        FROM fel_clientes_accionistas 
        WHERE clienteAccionistaid = ?
    ",[$_POST['accionistaId']]);


$salida = array(
    "accionistaId"              => $dataAccionista->clienteAccionistaid,
    "nombreAccionista"          => $dataAccionista->nombreAccionista,
    "paisId"                    => $dataAccionista->paisId,
    "nitAccionista"             => $dataAccionista->nitAccionista,
    "porcentajeParticipacion"   => $dataAccionista->porcentajeParticipacion,
);

echo json_encode($salida);