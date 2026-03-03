<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$term = isset($_POST['busquedaSelect']) ? trim($_POST['busquedaSelect']) : '';
$where = "";
$params = [];

if($term !== '') {
    $where = "AND (
        c.nombreCliente LIKE ? OR
        c.nombreComercialCliente LIKE ? OR
        c.nrcCliente LIKE ? OR
        c.numDocumento LIKE ?
    )";
    $params = ["%$term%", "%$term%", "%$term%", "%$term%"];
}

$data = $cloud->rows(
    "SELECT
        c.clienteId,
        c.nombreCliente,
        c.nombreComercialCliente,
        c.nrcCliente,
        c.numDocumento
    FROM fel_clientes c
    WHERE c.flgDelete = 0 AND c.estadoCliente = 'Activo' $where
    ORDER BY c.nombreCliente ASC
    LIMIT 50",
    $params
);

if($data) {
    $json = [];
    foreach($data as $c) {
        $nombre = $c->nombreCliente;
        if($nombre == '' || is_null($nombre)) {
            $nombre = $c->nombreComercialCliente;
        }
        $extra = [];
        if(!is_null($c->nrcCliente) && $c->nrcCliente !== '') $extra[] = "NRC: $c->nrcCliente";
        if(!is_null($c->numDocumento) && $c->numDocumento !== '') $extra[] = "Doc: $c->numDocumento";

        $text = $nombre;
        if(count($extra) > 0) {
            $text .= " (" . implode(' / ', $extra) . ")";
        }

        $json[] = [
            'id' => (int) $c->clienteId,
            'text' => $text
        ];
    }
    echo json_encode($json);
} else {
    echo json_encode([
        ['id' => null, 'text' => 'Sin resultados']
    ]);
}
