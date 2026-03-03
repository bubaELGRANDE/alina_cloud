<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$cotizacionId = isset($_POST['cotizacionId']) ? (int) $_POST['cotizacionId'] : 0;

if($cotizacionId <= 0) {
    echo json_encode(['data' => '']);
    exit;
}

$data = $cloud->rows(
    "SELECT
        cotizacionDetalleId,
        ordenDetalle,
        productoId,
        codProductoCotizacion,
        nombreProductoCotizacion,
        cantidadProducto,
        precioUnitario,
        porcentajeDescuento,
        descuentoTotal,
        totalDetalle,
        totalDetalleIVA
    FROM fel_cotizacion_detalle
    WHERE cotizacionId = ? AND flgDelete = 0
    ORDER BY ordenDetalle ASC, cotizacionDetalleId ASC",
    [$cotizacionId]
);

$n = 0;
$output = ['data' => []];
foreach($data as $row) {
    $n++;

    $producto = '<b><i class="fas fa-barcode"></i> SKU: </b> ' . ($row->codProductoCotizacion ?? '') .
        '<br><b><i class="fas fa-box"></i> Producto: </b> ' . ($row->nombreProductoCotizacion ?? '');

    $cant = number_format((float)$row->cantidadProducto, 2, '.', ',');
    $precio = ($_SESSION['monedaSimbolo'] ?? '$') . ' ' . number_format((float)$row->precioUnitario, 6, '.', ',');

    $desc = number_format((float)$row->porcentajeDescuento, 2, '.', ',') . '%';
    $desc .= '<br><b>Desc. total: </b>' . ($_SESSION['monedaSimbolo'] ?? '$') . ' ' . number_format((float)$row->descuentoTotal, 2, '.', ',');

    $total = '<b>Sin IVA: </b>' . ($_SESSION['monedaSimbolo'] ?? '$') . ' ' . number_format((float)$row->totalDetalle, 2, '.', ',') .
        '<br><b>Con IVA: </b>' . ($_SESSION['monedaSimbolo'] ?? '$') . ' ' . number_format((float)$row->totalDetalleIVA, 2, '.', ',');

    $jsonDel = htmlspecialchars(json_encode([
        'cotizacionId' => $cotizacionId,
        'cotizacionDetalleId' => (int)$row->cotizacionDetalleId
    ]));

    $acciones = '<button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarDetalleCotizacion(' . $jsonDel . ')">
        <i class="fas fa-trash-alt"></i>
        <span class="ttiptext">Eliminar</span>
    </button>';

    $output['data'][] = [
        $n,
        $producto,
        $cant,
        $precio,
        $desc,
        $total,
        $acciones
    ];
}

if($n > 0) {
    echo json_encode($output);
} else {
    echo json_encode(['data' => '']);
}
