<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$clienteId = isset($_POST['clienteId']) ? (int) $_POST['clienteId'] : 0;
$term = isset($_POST['busquedaSelect']) ? trim($_POST['busquedaSelect']) : '';

if($clienteId <= 0) {
    echo json_encode([['id' => null, 'text' => 'Seleccione un cliente']]);
    exit;
}

$where = "";
$params = [$clienteId];

if($term !== '') {
    $where = "AND (ub.nombreClienteUbicacion LIKE ? OR ub.direccionClienteUbicacion LIKE ? OR ub.tipoUbicacion LIKE ?)";
    $params[] = "%$term%";
    $params[] = "%$term%";
    $params[] = "%$term%";
}

$data = $cloud->rows(
    "SELECT
        ub.clienteUbicacionId,
        ub.nombreClienteUbicacion,
        ub.tipoUbicacion,
        ub.direccionClienteUbicacion
    FROM fel_clientes_ubicaciones ub
    WHERE ub.clienteId = ? AND ub.flgDelete = 0 $where
    ORDER BY ub.nombreClienteUbicacion ASC
    LIMIT 50",
    $params
);

if($data) {
    $json = [];
    foreach($data as $u) {
        $text = ($u->nombreClienteUbicacion ?? 'Ubicación');
        if(!is_null($u->tipoUbicacion) && $u->tipoUbicacion !== '') {
            $text .= " - $u->tipoUbicacion";
        }
        if(!is_null($u->direccionClienteUbicacion) && $u->direccionClienteUbicacion !== '') {
            $text .= " | $u->direccionClienteUbicacion";
        }
        $json[] = [
            'id' => (int) $u->clienteUbicacionId,
            'text' => $text
        ];
    }
    echo json_encode($json);
} else {
    echo json_encode([['id' => null, 'text' => 'Sin resultados']]);
}
