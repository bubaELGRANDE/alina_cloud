<?php
/*
    Delete - Ventas
*/

if(isset($_SESSION["usuarioId"]) && isset($operation)) {
    require_once("cotizacion_helpers.php");

    switch($operation) {
        case 'cotizacion-detalle':
            $cotizacionDetalleId = isset($_POST['cotizacionDetalleId']) ? (int) $_POST['cotizacionDetalleId'] : 0;
            $cotizacionId = isset($_POST['cotizacionId']) ? (int) $_POST['cotizacionId'] : 0;

            if($cotizacionDetalleId <= 0 || $cotizacionId <= 0) {
                echo 'Datos inválidos.';
                break;
            }

            try {
                $cloud->deleteById('fel_cotizacion_detalle', 'cotizacionDetalleId', $cotizacionDetalleId);
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
