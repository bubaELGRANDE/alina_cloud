<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
require_once("../../../../../libraries/includes/logic/functions/funciones-conta.php");
@session_start();
$cuentaIdInicio = isset($_POST['cuentaIdInicio']) && is_numeric($_POST['cuentaIdInicio'])
    ? (int) $_POST['cuentaIdInicio']
    : 0;
$periodoId = isset($_POST['periodoId']) && is_numeric($_POST['periodoId'])
    ? (int) $_POST['periodoId']
    : 0;
$fechaInicio = isset($_POST['fechaInicio']) && $_POST['fechaInicio'] !== ''
    ? date('Y-m-d', strtotime($_POST['fechaInicio']))
    : null;
$fechaFin = isset($_POST['fechaFin']) && $_POST['fechaFin'] !== ''
    ? date('Y-m-d', strtotime($_POST['fechaFin']))
    : null;
if ($cuentaIdInicio === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Debe seleccionar una cuenta contable. -------------'
    ]);
    exit;
}
$mayorizacion = [];

if ($periodoId > 0) {
    $row = $cloud->row("
        SELECT  
            numeroCuentaMayorizacion,
            descripcionCuentaMayorizacion,
            saldoInicialMayorizacion,
            saldoFinalMayorizacion,
            cargoMayorizacion,
            abonoMayorizacion,
            cuentaPadreId
        FROM conta_mayorizacion_2025
        WHERE numeroCuentaMayorizacion = ?
        AND partidaContaPeriodoId = ?
    ", [$cuentaIdInicio, $periodoId]);
    if ($row) {
        $mayorizacion[] = [
            'numero' => $row->numeroCuentaMayorizacion,
            'descripcion' => $row->descripcionCuentaMayorizacion,
            'inicial' => '$' . number_format((float) $row->saldoInicialMayorizacion, 2, '.', ','),
            'cargo' => '$' . number_format((float) $row->cargoMayorizacion, 2, '.', ','),
            'abono' => '$' . number_format((float) $row->abonoMayorizacion, 2, '.', ','),
            'final' => '$' . number_format((float) $row->saldoFinalMayorizacion, 2, '.', ','),
        ];
    }
} else {
    if (!$fechaInicio || !$fechaFin) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Debe proporcionar fecha inicio y fecha fin o un periodo.'
        ]);
        exit;
    }
    $periodoInicioId = buscarPeriodoFecha($fechaInicio, $cloud);
    $periodoFinId = buscarPeriodoFecha($fechaFin, $cloud);
    if ($periodoInicioId === 0 || $periodoFinId === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No se encontraron periodos para las fechas indicadas. --------- 1'
        ]);
        exit;
    }
    $rowInicio = $cloud->row("
    SELECT saldoInicialMayorizacion, numeroCuentaMayorizacion, descripcionCuentaMayorizacion
    FROM conta_mayorizacion_2025
    WHERE numeroCuentaMayorizacion = ? AND partidaContaPeriodoId = ?
    ORDER BY partidaContaPeriodoId ASC
    LIMIT 1
", [$cuentaIdInicio, $periodoInicioId]);

    // Saldo final del último periodo
    $rowFin = $cloud->row("
    SELECT saldoFinalMayorizacion
    FROM conta_mayorizacion_2025
    WHERE numeroCuentaMayorizacion = ? AND partidaContaPeriodoId = ?
    ORDER BY partidaContaPeriodoId DESC
    LIMIT 1
", [$cuentaIdInicio, $periodoFinId]);

    // Cargos y abonos acumulados entre ambos periodos
    $rowSuma = $cloud->row("
    SELECT SUM(cargoMayorizacion) AS totalCargo, SUM(abonoMayorizacion) AS totalAbono
    FROM conta_mayorizacion_2025
    WHERE numeroCuentaMayorizacion = ? AND partidaContaPeriodoId BETWEEN ? AND ?
", [$cuentaIdInicio, $periodoInicioId, $periodoFinId]);
    if ($rowInicio && $rowFin && $rowSuma) {
        $mayorizacion[] = [
            'numero' => $rowInicio->numeroCuentaMayorizacion,
            'descripcion' => $rowInicio->descripcionCuentaMayorizacion,
            'inicial' => '$' . number_format((float) $rowInicio->saldoInicialMayorizacion, 2, '.', ','),
            'cargo' => '$' . number_format((float) $rowSuma->totalCargo, 2, '.', ','),
            'abono' => '$' . number_format((float) $rowSuma->totalAbono, 2, '.', ','),
            'final' => '$' . number_format((float) $rowFin->saldoFinalMayorizacion, 2, '.', ','),
        ];
    }
}

if ($mayorizacion) {
    $row = $mayorizacion[0];
    echo json_encode([
        'status' => 'success',
        'numero' => $row['numero'],
        'descripcion' => $row['descripcion'],
        'inicial' => $row['inicial'],
        'cargo' => $row['cargo'],
        'abono' => $row['abono'],
        'final' => $row['final']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => $periodoInicioId . '' . $periodoFinId . 'No se encontraron datos para los criterios seleccionados. ---------2'
    ]);
}
?>