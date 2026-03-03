<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$clienteUbicacionId = isset($_POST['clienteUbicacionId']) ? (int) $_POST['clienteUbicacionId'] : 0;
$term = isset($_POST['busquedaSelect']) ? trim($_POST['busquedaSelect']) : '';

if($clienteUbicacionId <= 0) {
    echo json_encode([['id' => null, 'text' => 'Seleccione una ubicación']]);
    exit;
}

$where = "";
$params = [$clienteUbicacionId];
if($term !== '') {
    $where = "AND (c.contactoCliente LIKE ? OR c.descripcionContactoCliente LIKE ?)";
    $params[] = "%$term%";
    $params[] = "%$term%";
}

$data = $cloud->rows(
    "SELECT
        c.clienteContactoId,
        c.contactoCliente,
        c.descripcionContactoCliente,
        c.flgContactoPrincipal,
        tc.tipoContacto
    FROM fel_clientes_contactos c
    LEFT JOIN cat_tipos_contacto tc ON tc.tipoContactoId = c.tipoContactoId
    WHERE c.flgDelete = 0 AND c.clienteUbicacionId = ? $where
    ORDER BY c.flgContactoPrincipal DESC, c.clienteContactoId DESC
    LIMIT 50",
    $params
);

if($data) {
    $json = [];
    foreach($data as $c) {
        $text = '';
        if(!is_null($c->tipoContacto) && $c->tipoContacto !== '') {
            $text .= '(' . $c->tipoContacto . ') ';
        }
        $text .= ($c->contactoCliente ?? 'Contacto');
        if(!is_null($c->descripcionContactoCliente) && $c->descripcionContactoCliente !== '') {
            $text .= ' - ' . $c->descripcionContactoCliente;
        }
        if((int)($c->flgContactoPrincipal ?? 0) === 1) {
            $text .= ' (Principal)';
        }

        $json[] = [
            'id' => (int) $c->clienteContactoId,
            'text' => $text
        ];
    }
    echo json_encode($json);
} else {
    echo json_encode([['id' => null, 'text' => 'Sin resultados']]);
}
