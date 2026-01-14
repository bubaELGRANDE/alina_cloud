<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$numeroCuenta = isset($_POST['numeroCuenta']) && $_POST['numeroCuenta'] !== ''
    ? $_POST['numeroCuenta']
    : '';

$direccion = isset($_POST['direccion']) && in_array($_POST['direccion'], ['anterior', 'siguiente'])
    ? $_POST['direccion']
    : 'siguiente';

if ($numeroCuenta === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Debe proporcionar un número de cuenta válido.'
    ]);
    exit;
}

if ($direccion === 'anterior') {
    $row = $cloud->row("
        SELECT cuentaContaId, numeroCuenta
        FROM conta_cuentas_contables
        WHERE CAST(numeroCuenta AS CHAR) < CAST(? AS CHAR) AND flgDelete = 0
        ORDER BY CAST(numeroCuenta AS CHAR) DESC
        LIMIT 1
    ", [$numeroCuenta]);
} else {
    $row = $cloud->row("
        SELECT cuentaContaId, numeroCuenta
        FROM conta_cuentas_contables
        WHERE CAST(numeroCuenta AS CHAR) > CAST(? AS CHAR) AND flgDelete = 0
        ORDER BY CAST(numeroCuenta AS CHAR) ASC
        LIMIT 1
    ", [$numeroCuenta]);
}

if ($row) {
    echo json_encode([
        'status' => 'success',
        'id'     => (int)$row->cuentaContaId,
        'numero' => (string)$row->numeroCuenta
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se encontró cuenta ' . $direccion . '.'
    ]);
}

?>