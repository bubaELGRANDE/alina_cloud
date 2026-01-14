<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$tipoDTEId = $_POST['tipoDTEId'] ?? null;
if (!$tipoDTEId) {
    echo json_encode([]);
    exit;
}

$facturas = $cloud->rows("
    SELECT f.facturaId, c.nombreCliente
    FROM fel_factura f
    LEFT JOIN fel_clientes c ON f.clienteUbicacionId = c.clienteId
    WHERE f.flgDelete = 0 AND f.tipoDTEId = ?
    ORDER BY f.facturaId
", [$tipoDTEId]);

$resultado = [];
foreach ($facturas as $f) {
    $resultado[] = [
        'id' => $f->facturaId,
        'text' => "$f->facturaId - " . $f->nombreCliente
    ];
}

echo json_encode($resultado);
