<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$facturas = $cloud->rows('
    SELECT fe.facturaId, fe.clienteId, 
           concat(fe.fechaEmision," ",fe.horaEmision) AS fecha, 
           fe.totalFactura, cl.nombreCliente
    FROM fel_factura fe, fel_clientes cl
    WHERE fe.clienteId = cl.clienteId
    AND fe.flgDelete = ?
    AND fe.facturaId LIKE ?
', [0, '%' . $_POST['facturaId'] . '%']);

foreach ($facturas as $factura) {
    $selectReturn[] = array(
        "facturaId" => $factura->facturaId,
        "clienteId" => $factura->clienteId,
        "fecha" => $factura->fecha,
        "totalFactura" => number_format($factura->totalFactura, 2),
        "nombreCliente" => $factura->nombreCliente,
    );
}


echo json_encode($selectReturn);
?>