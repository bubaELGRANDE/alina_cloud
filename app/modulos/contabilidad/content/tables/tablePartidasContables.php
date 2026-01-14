<?php
require_once ('../../../../../libraries/includes/logic/mgc/datos94.php');
require_once ('../../../../../libraries/includes/logic/functions/funciones-conta.php');
@session_start();

// --- Función para convertir fechas ---
function convertirFechaSQL($fecha)
{
    if (!$fecha)
        return null;
    $fecha = str_replace('/', '-', $fecha);
    $timestamp = strtotime($fecha);
    return $timestamp ? date('Y-m-d', $timestamp) : null;
}

// --- Parámetros recibidos ---
$numPartida = isset($_POST['numPartida']) ? (int) $_POST['numPartida'] : 0;
$periodo = isset($_POST['periodo']) ? (int) $_POST['periodo'] : 0;
$tipo = isset($_POST['tipo']) ? (int) $_POST['tipo'] : 0;
$fechaInicio = isset($_POST['inicio']) ? convertirFechaSQL($_POST['inicio']) : null;
$fechaFinal = isset($_POST['final']) ? convertirFechaSQL($_POST['final']) : null;
$estado = isset($_POST['estado']) ? ($_POST['estado'] == true ? 'Pendiente' : null) : null;
$automatica = isset($_POST['automatica']) ? ($_POST['automatica'] == true ? 1 : 0) : null;

// --- Meses ---
$arrayMeses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

// --- Condiciones dinámicas ---
$condiciones = ['p.flgDelete = 0'];

if ($numPartida > 0)
    $condiciones[] = "p.numPartida = {$numPartida}";
if ($periodo > 0)
    $condiciones[] = "p.partidaContaPeriodoId = {$periodo}";
if ($tipo > 0)
    $condiciones[] = "p.tipoPartidaId = {$tipo}";
if ($estado)
    $condiciones[] = "p.estadoPartidaContable = '{$estado}'";
if ($automatica)
    $condiciones[] = 't.flgAutomatica = 1';

if ($fechaInicio && $fechaFinal) {
    $condiciones[] = "p.fechaPartida BETWEEN '$fechaInicio' AND '$fechaFinal'";
} elseif ($fechaInicio) {
    $condiciones[] = "p.fechaPartida = '$fechaInicio'";
} elseif ($fechaFinal) {
    $condiciones[] = "p.fechaPartida <= '$fechaFinal'";
}

$whereSQL = implode(' AND ', $condiciones);

// --- Consulta principal ---
$dataPartidas = $cloud->rows("
    SELECT
        p.partidaContableId,
        p.estadoPartidaContable,
        p.cargoPartida,
        p.abonoPartida,
        p.tipoPartidaId,
        p.partidaContaPeriodoId,
        p.numPartida,
        p.descripcionPartida,
        p.fechaPartida,
        t.descripcionPartida AS tipoPartidaNombre,
        per.mes, per.anio
    FROM conta_partidas_contables p
    LEFT JOIN cat_tipo_partida_contable t ON t.tipoPartidaId = p.tipoPartidaId
    LEFT JOIN conta_partidas_contables_periodos per ON per.partidaContaPeriodoId = p.partidaContaPeriodoId
    WHERE {$whereSQL}
    ORDER BY per.anio DESC, per.mes DESC
");

$tolerancia = getToteranciaPartidas();
$n = 0;
$output = ['data' => []];

foreach ($dataPartidas as $dataPartida) {
    $n++;
    $periodos = $arrayMeses[(int) $dataPartida->mes] . ' ' . $dataPartida->anio;

    if (in_array(359, $_SESSION['arrayPermisos'])) {
        $numPartidaFmt = str_pad($dataPartida->numPartida, 8, '0', STR_PAD_LEFT) . '<br><b>Numero Interno (Desarrollo): </b>' . $dataPartida->partidaContableId;
    } else {
        $numPartidaFmt = str_pad($dataPartida->numPartida, 8, '0', STR_PAD_LEFT);
    }

    $tipo = "<b>Concepto Gral.:</b> {$dataPartida->descripcionPartida}<br>
             <b>Tipo de partida: </b> {$dataPartida->tipoPartidaNombre}";

    $periodo = "<b>Periodo: </b>{$periodos}<br><b>Fecha: </b>{$dataPartida->fechaPartida}";

    $cargo = (float) $dataPartida->cargoPartida;
    $abono = (float) $dataPartida->abonoPartida;
    $balance = $cargo - $abono;

    // --- Balance con tolerancia ---
    if (abs($balance) <= $tolerancia) {
        $balanceT = '<p class="text-secondary"><i class="fas fa-equals"></i><b> $0.00 </b></p>';
    } elseif ($balance > 0) {
        $balanceT = '<p class="text-success"><i class="fas fa-arrow-up"></i><b> $' . number_format($balance, 2, '.', ',') . '</b></p>';
    } else {
        $balanceT = '<p class="text-danger"><i class="fas fa-arrow-down"></i><b> $' . number_format(abs($balance), 2, '.', ',') . '</b></p>';
    }

    // --- Estado visual ---
    if ($dataPartida->estadoPartidaContable === 'Pendiente') {
        $estado = '<p class="small text-danger fw-bold">
                       <i class="fas fa-exclamation-circle me-1"></i>Pendiente
                   </p>';
        $labelIcon = 'fa-sync-alt';
        $labelAction = 'Continuar';
    } else {
        $estado = '<p class="small text-success fw-bold">
                       <i class="fas fa-check-circle me-1"></i>Finalizada
                   </p>';
        $labelIcon = 'fa-eye';
        $labelAction = 'Ver';
    }

    // --- JSONs para botones ---
    $jsonEditar = [
        'typeOperation' => 'update',
        'operation' => 'abrir-partida-contable',
        'partidaContableId' => $dataPartida->partidaContableId
    ];

    $jsonDelete = [
        'typeOperation' => 'delete',
        'operation' => 'partida-contable',
        'partidaContableId' => $dataPartida->partidaContableId
    ];

    $jsonDetalle = [
        'partidaContableId' => $dataPartida->partidaContableId,
        'tipoPartidaId' => $dataPartida->tipoPartidaId
    ];

    // --- Botones de acción ---
    if ($dataPartida->estadoPartidaContable === 'Pendiente') {
        $acciones = '
            <button class="btn btn-primary btn-sm"
                onclick="changePage(`' . $_SESSION['currentRoute'] . '`, `general-partida`, `data=' . htmlspecialchars(json_encode($jsonDetalle)) . '`)">
                <i class="fas ' . $labelIcon . '"></i> ' . $labelAction . '
            </button>';
    } else {
        if (in_array(356, $_SESSION['arrayPermisos'])) {
            $acciones = '
                <button class="btn btn-warning btn-sm" 
                    onclick="abrirPartida(' . htmlspecialchars(json_encode($jsonEditar)) . ',' . htmlspecialchars(json_encode($jsonDetalle)) . ')">
                    <i class="fas fa-lock-open"></i> Habilitar
                </button>
                <button class="btn btn-primary btn-sm"
                    onclick="changePage(`' . $_SESSION['currentRoute'] . '`, `general-partida`, `data=' . htmlspecialchars(json_encode($jsonDetalle)) . '`)">
                    <i class="fas ' . $labelIcon . '"></i> ' . $labelAction . '
                </button>';
        } else {
            $acciones = '
                <button class="btn btn-primary btn-sm"
                    onclick="changePage(`' . $_SESSION['currentRoute'] . '`, `general-partida`, `data=' . htmlspecialchars(json_encode($jsonDetalle)) . '`)">
                    <i class="fas ' . $labelIcon . '"></i> ' . $labelAction . '
                </button>';
        }
    }

    if (in_array(367, $_SESSION['arrayPermisos'])) {
        $acciones .= '
                <button class="btn btn-danger btn-sm" 
                    onclick="eliminar(' . htmlspecialchars(json_encode($jsonDelete)) . ')">
                    <i class="fas fa-trash"></i> Borrar
                </button>';
    }

    // --- Armar fila final ---
    $output['data'][] = [
        $n,
        $numPartidaFmt,
        $tipo,
        $periodo,
        '$' . number_format($cargo, 2, '.', ','),
        '$' . number_format($abono, 2, '.', ','),
        $balanceT,
        $estado,
        $acciones
    ];
}

echo json_encode($output);
