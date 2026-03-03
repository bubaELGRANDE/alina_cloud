<?php
/*
    Insert - Ventas
*/

if(isset($_SESSION["usuarioId"]) && isset($operation)) {
    require_once("cotizacion_helpers.php");

    switch($operation) {
        case 'cotizacion':
            // Crear encabezado (el cotizacionId será el correlativo)
            $sucursalId = isset($_POST['sucursalId']) ? (int) $_POST['sucursalId'] : 0;
            $clienteId = isset($_POST['clienteId']) ? (int) $_POST['clienteId'] : 0; // 0 = Cliente en sala
            $nombreClienteSala = isset($_POST['nombreClienteSala']) ? trim($_POST['nombreClienteSala']) : null;

            if($sucursalId <= 0) {
                echo 'Debe seleccionar una sucursal.';
                break;
            }

            // Cliente registrado o Cliente en sala
            if($clienteId <= 0) {
                if(is_null($nombreClienteSala) || $nombreClienteSala === '') {
                    echo 'Debe ingresar el nombre del cliente en sala.';
                    break;
                }
                // Limitar ubicaciones/contactos cuando es cliente en sala
                $clienteId = 0;
            }

            $fechaEmision = $_POST['fechaEmision'] ?? date('Y-m-d');
            $horaEmision = date('H:i:s');

            $fechaVencimiento = $_POST['fechaVencimiento'] ?? null;
            if($fechaVencimiento === '') $fechaVencimiento = null;

            $clienteUbicacionId = $_POST['clienteUbicacionId'] ?? null;
            if($clienteUbicacionId === '' || $clienteUbicacionId === '0') $clienteUbicacionId = null;

            $clienteContactoId = $_POST['clienteContactoId'] ?? null;
            if($clienteContactoId === '' || $clienteContactoId === '0') $clienteContactoId = null;

            // Si es cliente en sala, ignorar ubicación/contacto
            if($clienteId === 0) {
                $clienteUbicacionId = null;
                $clienteContactoId = null;
            }

            $monedaId = isset($_POST['monedaId']) ? (int) $_POST['monedaId'] : 1;
            $tasaIVA = isset($_POST['tasaIVA']) ? (float) $_POST['tasaIVA'] : 0.13;

            $insert = [
                'sucursalId' => $sucursalId,
                'fechaEmision' => $fechaEmision,
                'horaEmision' => $horaEmision,
                'fechaVencimiento' => $fechaVencimiento,
                'clienteId' => $clienteId,
                'nombreClienteSala' => ($clienteId === 0 ? $nombreClienteSala : null),
                'clienteUbicacionId' => $clienteUbicacionId,
                'clienteContactoId' => $clienteContactoId,
                'vendedorPersonaId' => $_SESSION['personaId'] ?? null,
                'monedaId' => $monedaId,
                'tasaIVA' => $tasaIVA,
                'estadoCotizacion' => 'Borrador',
                'observaciones' => $_POST['observaciones'] ?? null
            ];

            try {
                $cotizacionId = $cloud->insert('fel_cotizacion', $insert);
                echo json_encode([
                    'status' => 'success',
                    'cotizacionId' => (int) $cotizacionId
                ]);
            } catch (Throwable $e) {
                echo 'error: ' . $e->getMessage();
            }
            break;

        case 'cotizacion-detalle':
            // Agregar producto al detalle
            $cotizacionId = isset($_POST['cotizacionId']) ? (int) $_POST['cotizacionId'] : 0;
            $productoId = isset($_POST['productoId']) ? (int) $_POST['productoId'] : 0;

            $cantidad = isset($_POST['cantidadProducto']) ? (float) $_POST['cantidadProducto'] : 1;
            $porcDesc = isset($_POST['porcentajeDescuento']) ? (float) $_POST['porcentajeDescuento'] : 0;

            if($cotizacionId <= 0) {
                echo 'Cotización inválida.';
                break;
            }
            if($productoId <= 0) {
                echo 'Debe seleccionar un producto.';
                break;
            }
            if($cantidad <= 0) {
                echo 'La cantidad debe ser mayor a 0.';
                break;
            }
            if($porcDesc < 0) $porcDesc = 0;
            if($porcDesc > 100) $porcDesc = 100;

            $cot = ventas_get_cotizacion($cloud, $cotizacionId);
            if(!$cot) {
                echo 'No se encontró la cotización.';
                break;
            }

            $prod = $cloud->row(
                "SELECT
                    p.productoId,
                    p.codInterno,
                    p.nombreProducto,
                    udm.abreviaturaUnidadMedida,
                    pp.precioVenta,
                    pp.precioVentaIVA,
                    pp.costoPromedio
                FROM prod_productos p
                LEFT JOIN cat_unidades_medida udm ON udm.unidadMedidaId = p.unidadMedidaId
                LEFT JOIN prod_productos_precios pp
                    ON pp.productoId = p.productoId
                    AND pp.estadoPrecio = 'Activo'
                    AND pp.flgDelete = 0
                WHERE p.productoId = ? AND p.flgDelete = 0",
                [$productoId]
            );

            if(!$prod) {
                echo 'No se encontró el producto.';
                break;
            }

            $next = $cloud->row(
                "SELECT COALESCE(MAX(ordenDetalle), 0) + 1 AS nextOrden
                 FROM fel_cotizacion_detalle
                 WHERE cotizacionId = ? AND flgDelete = 0",
                [$cotizacionId]
            );
            $ordenDetalle = (int) ($next->nextOrden ?? 1);

            $tasaIVA = (float) ($cot->tasaIVA ?? 0);
            $precioUnitario = (float) ($prod->precioVenta ?? 0);

            $subTotalDetalle = $precioUnitario * $cantidad;
            $descuentoUnitario = $precioUnitario * ($porcDesc / 100);
            $descuentoTotal = $descuentoUnitario * $cantidad;

            $precioVenta = $precioUnitario - $descuentoUnitario;
            $totalDetalle = $precioVenta * $cantidad;

            $ivaUnitario = $precioVenta * $tasaIVA;
            $ivaTotal = $totalDetalle * $tasaIVA;

            $precioUnitarioIVA = $precioUnitario * (1 + $tasaIVA);
            $precioVentaIVA = $precioVenta * (1 + $tasaIVA);

            $subTotalDetalleIVA = $subTotalDetalle * (1 + $tasaIVA);
            $totalDetalleIVA = $totalDetalle + $ivaTotal;

            $insert = [
                'cotizacionId' => $cotizacionId,
                'ordenDetalle' => $ordenDetalle,
                'productoId' => $productoId,
                'codProductoCotizacion' => $prod->codInterno ?? null,
                'nombreProductoCotizacion' => $prod->nombreProducto ?? 'Producto',
                'costoPromedio' => (float) ($prod->costoPromedio ?? 0),
                'precioUnitario' => $precioUnitario,
                'precioUnitarioIVA' => $precioUnitarioIVA,
                'precioVenta' => $precioVenta,
                'precioVentaIVA' => $precioVentaIVA,
                'cantidadProducto' => $cantidad,
                'porcentajeDescuento' => $porcDesc,
                'descuentoUnitario' => $descuentoUnitario,
                'descuentoTotal' => $descuentoTotal,
                'ivaUnitario' => $ivaUnitario,
                'ivaTotal' => $ivaTotal,
                'subTotalDetalle' => $subTotalDetalle,
                'subTotalDetalleIVA' => $subTotalDetalleIVA,
                'totalDetalle' => $totalDetalle,
                'totalDetalleIVA' => $totalDetalleIVA,
            ];

            try {
                $cloud->insert('fel_cotizacion_detalle', $insert);
                $totales = ventas_recalcular_totales_cotizacion($cloud, $cotizacionId);

                echo json_encode([
                    'status' => 'success',
                    'totales' => $totales
                ]);
            } catch (Throwable $e) {
                echo 'error: ' . $e->getMessage();
            }
            break;

        default:
            echo "No se encontró la operación.";
        break;
    }
} else {
    header("Location: /alina-cloud/app/");
}
?>
