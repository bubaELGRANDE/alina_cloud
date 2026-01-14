<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$idCuenta = $_POST['cuentaContaId']; 

if (!$idCuenta) {
    echo json_encode(null);
    exit;
}


$rows = $cloud->row("SELECT categoriaCuenta,flgCentroCostos FROM conta_cuentas_contables
WHERE cuentaContaId = ? AND flgDelete = ?", [$idCuenta,0]);
if ($rows) {
    echo json_encode([
        "categoriaCuenta"=> $rows->categoriaCuenta,
        "isCentro"=> $rows->flgCentroCostos,
    ]);
}
