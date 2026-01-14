<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
include("../../../../../libraries/includes/logic/functions/funciones-conta.php");
@session_start();

$partidaContableId = $_POST['partidaContableId'] ?? 0;

if (!$partidaContableId) {
    echo json_encode(['status' => 'error', 'message' => 'No se envió partidaContableId']);
    exit;
}

// Obtenemos la partida con sus totales actuales (sin redondear)
$row = $cloud->row("
    SELECT 
        p.partidaContableId,
        p.cargoPartida,
        p.abonoPartida,
        COALESCE(SUM(d.cargos), 0) AS totalCargos,
        COALESCE(SUM(d.abonos), 0) AS totalAbonos
    FROM conta_partidas_contables p
    LEFT JOIN conta_partidas_contables_detalle d 
        ON p.partidaContableId = d.partidaContableId 
        AND d.flgDelete = 0
    WHERE p.partidaContableId = ? 
      AND p.flgDelete = 0
    GROUP BY p.partidaContableId, p.cargoPartida, p.abonoPartida
", [$partidaContableId]);

if (!$row) {
    echo json_encode(['status' => 'error', 'message' => 'Partida no encontrada']);
    exit;
}

// Conversión cruda sin formateo ni redondeo
$totalCargos = (float) $row->totalCargos;
$totalAbonos = (float) $row->totalAbonos;
$cargoPartida = (float) $row->cargoPartida;
$abonoPartida = (float) $row->abonoPartida;

// Calcular balance
$balance = $totalCargos - $totalAbonos;

// Tolerancia mínima (se considera cuadrado si la diferencia es menor a esto)
$tolerancia = getToteranciaPartidas();

// Determinar si está descuadrada con precisión decimal
$descuadrada = (
    abs($totalCargos - $totalAbonos) > $tolerancia ||
    abs($totalCargos - $cargoPartida) > $tolerancia ||
    abs($totalAbonos - $abonoPartida) > $tolerancia
);

// Si está descuadrada, actualizar estado a Pendiente
if ($descuadrada) {
    $cloud->update("conta_partidas_contables", [
        'estadoPartidaContable' => 'Pendiente'
    ], ['partidaContableId' => $partidaContableId]);
}

echo json_encode([
    'status' => 'ok',
    'partidaContableId' => $partidaContableId,
    'totalCargos' => $totalCargos,
    'totalAbonos' => $totalAbonos,
    'cargoPartida' => $cargoPartida,
    'abonoPartida' => $abonoPartida,
    'balance' => $balance,
    'diferencia' => abs($balance),
    'descuadrada' => $descuadrada,
    'estado' => $descuadrada ? 'Pendiente' : 'Cuadrada',
    'tolerancia' => $tolerancia
]);
?>