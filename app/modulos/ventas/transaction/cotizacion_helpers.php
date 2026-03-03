<?php

/**
 * Recalcula los totales del encabezado de la cotización en base a su detalle.
 * Retorna un array con los totales actualizados.
 */
function ventas_recalcular_totales_cotizacion($cloud, $cotizacionId) {
    $sum = $cloud->row(
        "SELECT
            COALESCE(SUM(subTotalDetalle), 0) AS subTotal,
            COALESCE(SUM(descuentoTotal), 0) AS totalDescuento,
            COALESCE(SUM(ivaTotal), 0) AS totalIVA,
            COALESCE(SUM(totalDetalleIVA), 0) AS totalCotizacion
        FROM fel_cotizacion_detalle
        WHERE cotizacionId = ? AND flgDelete = 0",
        [$cotizacionId]
    );

    $update = [
        'subTotal' => $sum->subTotal ?? 0,
        'totalDescuento' => $sum->totalDescuento ?? 0,
        'totalIVA' => $sum->totalIVA ?? 0,
        'totalCotizacion' => $sum->totalCotizacion ?? 0,
    ];

    $cloud->update('fel_cotizacion', $update, ['cotizacionId' => $cotizacionId]);

    return [
        'subTotal' => (float) ($update['subTotal'] ?? 0),
        'totalDescuento' => (float) ($update['totalDescuento'] ?? 0),
        'totalIVA' => (float) ($update['totalIVA'] ?? 0),
        'totalCotizacion' => (float) ($update['totalCotizacion'] ?? 0),
    ];
}

function ventas_get_cotizacion($cloud, $cotizacionId) {
    return $cloud->row(
        "SELECT
            cotizacionId,
            sucursalId,
            fechaEmision,
            horaEmision,
            fechaVencimiento,
            clienteId,
            clienteUbicacionId,
            clienteContactoId,
            vendedorPersonaId,
            monedaId,
            tasaIVA,
            subTotal,
            totalDescuento,
            totalIVA,
            totalCotizacion,
            estadoCotizacion,
            observaciones
        FROM fel_cotizacion
        WHERE cotizacionId = ? AND flgDelete = 0",
        [$cotizacionId]
    );
}
