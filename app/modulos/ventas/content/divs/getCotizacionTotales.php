<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$cotizacionId = isset($_POST['cotizacionId']) ? (int) $_POST['cotizacionId'] : 0;
if($cotizacionId <= 0) {
    echo json_encode([]);
    exit;
}

$data = $cloud->row(
    "SELECT
        subTotal,
        totalDescuento,
        totalIVA,
        totalCotizacion,
        estadoCotizacion
     FROM fel_cotizacion
     WHERE cotizacionId = ? AND flgDelete = 0",
    [$cotizacionId]
);

if($data) {
    echo json_encode([
        'subTotal' => (float) ($data->subTotal ?? 0),
        'totalDescuento' => (float) ($data->totalDescuento ?? 0),
        'totalIVA' => (float) ($data->totalIVA ?? 0),
        'totalCotizacion' => (float) ($data->totalCotizacion ?? 0),
        'estadoCotizacion' => $data->estadoCotizacion ?? 'N/A'
    ]);
} else {
    echo json_encode([]);
}
