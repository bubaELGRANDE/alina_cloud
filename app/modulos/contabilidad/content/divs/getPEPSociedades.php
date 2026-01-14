<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataPEPSoc = $cloud->row("SELECT 
        clientePEPPatrimonialId, clientePEPId, razonSocial, porcentajeParticipacion
        FROM fel_clientes_pep_relpatrimonial 
        WHERE clientePEPPatrimonialId = ?
    ",[$_POST['PEPSocId']]);


$salida = array(
    "clientePEPPatrimonialId"   => $dataPEPSoc->clientePEPPatrimonialId,
    "clientePEPId"              => $dataPEPSoc->clientePEPId,
    "razonSocial"               => $dataPEPSoc->razonSocial,
    "porcentajeParticipacion"   => $dataPEPSoc->porcentajeParticipacion,
);

echo json_encode($salida);