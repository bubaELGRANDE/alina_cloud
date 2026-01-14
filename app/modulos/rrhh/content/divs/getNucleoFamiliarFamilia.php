<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataNucleoFamiliar = $cloud->row("
        SELECT 
            personaId, catPrsRelacionId, nombreFamiliar, apellidoFamiliar, fechaNacimiento, flgBeneficiario, porcentajeBeneficiario,
            flgDependeEconomicamente, flgVivenJuntos, direccionVivenJuntos
        FROM th_personas_familia
        WHERE prsFamiliaId = ?
    ", [$_POST["prsFamiliaId"]]);

    $salida = array(
        "parentesco"                    => $dataNucleoFamiliar->catPrsRelacionId,
        "nombreFamiliar"                => $dataNucleoFamiliar->nombreFamiliar,
        "apellidoFamiliar"              => $dataNucleoFamiliar->apellidoFamiliar,
        "fechaNacimiento"               => $dataNucleoFamiliar->fechaNacimiento,
        "flgBeneficiario"               => $dataNucleoFamiliar->flgBeneficiario,
        "porcentajeBeneficiario"        => $dataNucleoFamiliar->porcentajeBeneficiario,
        "flgDependeEconomicamente"      => $dataNucleoFamiliar->flgDependeEconomicamente,
        "flgVivenJuntos"                => $dataNucleoFamiliar->flgVivenJuntos,
        "direccionVivenJuntos"          => $dataNucleoFamiliar->direccionVivenJuntos,
    );
    echo json_encode($salida);
?>