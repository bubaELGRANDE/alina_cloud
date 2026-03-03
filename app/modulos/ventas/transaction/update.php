<?php
/*
    Update - Ventas
*/

if(isset($_SESSION["usuarioId"]) && isset($operation)) {
    require_once("cotizacion_helpers.php");

    switch($operation) {
        case 'cotizacion':
            $cotizacionId = isset($_POST['cotizacionId']) ? (int) $_POST['cotizacionId'] : 0;
            if($cotizacionId <= 0) {
                echo 'Cotización inválida.';
                break;
            }

            $sucursalId = isset($_POST['sucursalId']) ? (int) $_POST['sucursalId'] : 0;
            $clienteId = isset($_POST['clienteId']) ? (int) $_POST['clienteId'] : 0; // 0 = Cliente en sala
            $nombreClienteSala = isset($_POST['nombreClienteSala']) ? trim($_POST['nombreClienteSala']) : null;

            if($sucursalId <= 0) {
                echo 'Debe seleccionar una sucursal.';
                break;
            }

            if($clienteId <= 0) {
                if(is_null($nombreClienteSala) || $nombreClienteSala === '') {
                    echo 'Debe ingresar el nombre del cliente en sala.';
                    break;
                }
                $clienteId = 0;
            }

            $fechaEmision = $_POST['fechaEmision'] ?? null;
            if($fechaEmision === '') $fechaEmision = null;

            $fechaVencimiento = $_POST['fechaVencimiento'] ?? null;
            if($fechaVencimiento === '') $fechaVencimiento = null;

            $clienteUbicacionId = $_POST['clienteUbicacionId'] ?? null;
            if($clienteUbicacionId === '' || $clienteUbicacionId === '0') $clienteUbicacionId = null;

            $clienteContactoId = $_POST['clienteContactoId'] ?? null;
            if($clienteContactoId === '' || $clienteContactoId === '0') $clienteContactoId = null;

            if($clienteId === 0) {
                $clienteUbicacionId = null;
                $clienteContactoId = null;
            }

            $update = [
                'sucursalId' => $sucursalId,
                'clienteId' => $clienteId,
                'nombreClienteSala' => ($clienteId === 0 ? $nombreClienteSala : null),
                'clienteUbicacionId' => $clienteUbicacionId,
                'clienteContactoId' => $clienteContactoId,
                'fechaEmision' => $fechaEmision ?? date('Y-m-d'),
                'fechaVencimiento' => $fechaVencimiento,
                'observaciones' => $_POST['observaciones'] ?? null,
            ];

            try {
                $cloud->update('fel_cotizacion', $update, ['cotizacionId' => $cotizacionId]);
                echo 'success';
            } catch (Throwable $e) {
                echo 'error: ' . $e->getMessage();
            }
            break;

        case 'cotizacion-emitir':
            $cotizacionId = isset($_POST['cotizacionId']) ? (int) $_POST['cotizacionId'] : 0;
            if($cotizacionId <= 0) {
                echo 'Cotización inválida.';
                break;
            }
            $cloud->update('fel_cotizacion', ['estadoCotizacion' => 'Emitida'], ['cotizacionId' => $cotizacionId]);
            echo 'success';
            break;

        case 'cotizacion-anular':
            $cotizacionId = isset($_POST['cotizacionId']) ? (int) $_POST['cotizacionId'] : 0;
            if($cotizacionId <= 0) {
                echo 'Cotización inválida.';
                break;
            }
            $cloud->update('fel_cotizacion', ['estadoCotizacion' => 'Anulada'], ['cotizacionId' => $cotizacionId]);
            echo 'success';
            break;

        default:
            echo "No se encontró la operación.";
        break;
    }
} else {
    header("Location: /alina-cloud/app/");
}
?>
