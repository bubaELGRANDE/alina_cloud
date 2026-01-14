<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$search = $_POST['searchTerm'] ?? '';
$tipoDTEId = $_POST['tipoDTEId'] ?? null;

if (!$tipoDTEId) {
    echo json_encode([]);
    exit;
}

$searchTerm = "%" . $search . "%";

$rows = $cloud->rows("
    SELECT f.facturaId, c.nombreCliente
    FROM fel_factura f
    LEFT JOIN fel_clientes c ON f.clienteUbicacionId = c.clienteId
    WHERE f.flgDelete = 0 AND f.tipoDTEId = ? 
    AND (f.facturaId LIKE ? OR c.nombreCliente LIKE ?)
    ORDER BY f.facturaId ASC
    LIMIT 20
", [$tipoDTEId, $searchTerm, $searchTerm]);

$output = [];
foreach ($rows as $row) {
    $output[] = [
        'id' => $row->facturaId,
        'text' => "Núm. DTE: $row->facturaId - " . $row->nombreCliente
    ];
}

echo json_encode($output);
