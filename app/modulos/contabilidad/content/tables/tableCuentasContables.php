<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

// Obtener todas las cuentas ordenadas por jerarquía
$dataCuentasBrutas = $cloud->rows("
    SELECT cuentaContaId, numeroCuenta, descripcionCuenta, tipoCuenta, tipoMayoreo, categoriaCuenta, cuentaPadreId, flgCentroCostos 
    FROM conta_cuentas_contables
    WHERE flgDelete = ?
    ORDER BY LENGTH(numeroCuenta), numeroCuenta
", [0]);

// Reindexar cuentas por ID y por cuentaPadreId para jerarquía
$cuentasById = [];
$hijosPorPadre = [];

foreach ($dataCuentasBrutas as $cuenta) {
    $cuentasById[$cuenta->cuentaContaId] = $cuenta;
    $hijosPorPadre[$cuenta->cuentaPadreId ?? 0][] = $cuenta;
}

// Recursivamente construir árbol de cuentas
function construirArbolCuentas($padreId, $nivel, $cuentasById, $hijosPorPadre, &$output, &$n, $cloud) {
    if (!isset($hijosPorPadre[$padreId])) return;

    foreach ($hijosPorPadre[$padreId] as $cuenta) {
        $n++;

        $jsonEditar = [
            "typeOperation"   => "update",
            "cuentaContaId"   => $cuenta->cuentaContaId
        ];

        $jsonDel = [
            "typeOperation"     => "delete",
            "operation"         => "eliminar-parametrizacion-compartida-detalle",
            "cuentaContaId"     => $cuenta->cuentaContaId,
            "descripcionCuenta" => $cuenta->descripcionCuenta
        ];

        $numeroCuenta      = $cuenta->numeroCuenta;
        $descripcionCuenta = $cuenta->descripcionCuenta;
        $tipoCuenta        = $cuenta->tipoCuenta;
        $tipoMayoreo       = $cuenta->tipoMayoreo;
        $categoriaCuenta   = $cuenta->categoriaCuenta;

        $aplicaMovimiento  = ($tipoCuenta == "Auxiliar") ? "Sí" : "No";
        $centroCosto       = ($cuenta->flgCentroCostos == "Si") ? "Sí" : "No";

        // Mostrar cuenta mayor
        $cuentaMayor = "-";
        if (!empty($cuenta->cuentaPadreId) && isset($cuentasById[$cuenta->cuentaPadreId])) {
            $cuentaMayor = $cuentasById[$cuenta->cuentaPadreId]->numeroCuenta;
        }

        $acciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onClick="modalNuevaCuenta(' .htmlspecialchars(json_encode($jsonEditar)).');">
                <i class="fas fa-pen"></i>
                <span class="ttiptext">Editar</span>
            </button>
        ';

        $output['data'][] = [
            $n,
            $numeroCuenta,
            $descripcionCuenta,
            $cuentaMayor,
            $tipoCuenta,
            $aplicaMovimiento,
            $centroCosto,
            $tipoMayoreo,
            $categoriaCuenta,
            $acciones
        ];

        // Procesar hijos
        construirArbolCuentas($cuenta->cuentaContaId, $nivel + 1, $cuentasById, $hijosPorPadre, $output, $n, $cloud);
    }
}

$n = 0;
$output = ['data' => []];
construirArbolCuentas(0, 0, $cuentasById, $hijosPorPadre, $output, $n, $cloud);

echo json_encode($output);
