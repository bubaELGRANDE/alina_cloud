<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

if(isset($_POST["expedienteId"])){
    $dataExpediente = $cloud->row("
        SELECT
            cargoPersona, departamentoSucursal
        FROM view_expedientes
        WHERE prsExpedienteId = ? AND estadoPersona = ? AND estadoExpediente = ?
    ", [$_POST["expedienteId"], "Activo", "Activo"]);
    
    $salida = array(
        "cargoPersona"             => $dataExpediente->cargoPersona,
        "departamentoSucursal"      => $dataExpediente->departamentoSucursal
    );
} else {
    $salida = array("mensaje" => "No se encontró el expediente de la persona");
}

echo json_encode($salida);