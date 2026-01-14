<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataPEPFam = $cloud->row("SELECT 
        clientePEPFamiliaId, clientePEPId, catPrsRelacionId, nombreFamiliar
        FROM fel_clientes_pep_familia 
        WHERE clientePEPFamiliaId = ?
    ",[$_POST['PEPFamId']]);


$salida = array(
    "clientePEPFamiliaId"   => $dataPEPFam->clientePEPFamiliaId,
    "clientePEPId"          => $dataPEPFam->clientePEPId,
    "catPrsRelacionId"      => $dataPEPFam->catPrsRelacionId,
    "nombreFamiliar"        => $dataPEPFam->nombreFamiliar,
);

echo json_encode($salida);