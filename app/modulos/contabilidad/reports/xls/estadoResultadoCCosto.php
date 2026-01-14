<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
$contaPeriodo = $_POST['fechaInicio'] ?? 0;
$dataPeriodo = $cloud->row("SELECT CONCAT(mesNombre, ' ', anio) AS periodo FROM conta_partidas_contables_periodos WHERE partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);
$nombre_archivo = "ER MENSUAL " . $dataPeriodo->periodo;

function fmt($n)
{
    return number_format((float) $n, 2, '.', ',');
}

//TODOS: cuentas de ventas

$totalVentasBombas = $cloud->row("
SELECT 
   sum(abonoMayorizacion - cargoMayorizacion)  AS ventas
FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion IN (
6110100101,
6110100201,
6110100301,
6110100501,
6110100505,
6110100601,
6110100701
)
AND partidaContaPeriodoId = ? AND flgDelete = ?
", [$contaPeriodo, 0]);

$totalVentasEmpaquetadora = $cloud->row("
SELECT 
   sum(abonoMayorizacion - cargoMayorizacion)  AS ventas
FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion IN (
6110100103,
6110100108,
6110100203,
6110100208,
6110100303,
6110100308,
6110100403,
6110100503,
6110100603
)
AND partidaContaPeriodoId = ? AND flgDelete = ?
", [$contaPeriodo, 0]);

$totalVentasKarcher = $cloud->row("
SELECT 
 sum(abonoMayorizacion - cargoMayorizacion)  AS ventas
FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion IN (
6110100102,
6110100202,
6110100302,
6110100402,
6110100405,
6110100502,
6110100602,
6110100702
)
AND partidaContaPeriodoId = ? AND flgDelete = ?
", [$contaPeriodo, 0]);

$totalVentasSTHLI = $cloud->row("
SELECT 
  sum(abonoMayorizacion - cargoMayorizacion) AS ventas
FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion IN (
6110100107,
6110100207,
6110100307,
6110100407,
6110100507,
6110100607,
6110100707,
6110100205,
6110100305,
6110100105
)
AND partidaContaPeriodoId = ? AND flgDelete = ?
", [$contaPeriodo, 0]);

$totalVentasAgricola = $cloud->row("
SELECT 
   sum(abonoMayorizacion - cargoMayorizacion)  AS ventas
FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion IN (
6110100106,
6110100206,
6110100306,
6110100406,
6110100506,
6110100605,
6110100606,
6110100705,
6110100706
)
AND partidaContaPeriodoId = ? AND flgDelete = ?
", [$contaPeriodo, 0]);

$totalVentasMaquinaria = $cloud->row("
SELECT 
   sum(abonoMayorizacion - cargoMayorizacion)  AS ventas
FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion IN (
6110100104,
6110100204,
6110100604,
6110100704
)
AND partidaContaPeriodoId = ? AND flgDelete = ?
", [$contaPeriodo, 0]);

//! Calculo de total de ventas

$totalVenta = $totalVentasBombas->ventas +
    $totalVentasKarcher->ventas +
    $totalVentasAgricola->ventas +
    $totalVentasMaquinaria->ventas +
    $totalVentasSTHLI->ventas +
    $totalVentasEmpaquetadora->ventas;


//TODOS: consulta de Descuento y DEV

$totalDescBombas = $cloud->row("
SELECT 
   sum(cargoMayorizacion - abonoMayorizacion)  AS ventas
FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion IN (
61300101,
61300501,
61300704
)
AND partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);

$totalDescEmpaquetadora = $cloud->row("
SELECT 
   sum(cargoMayorizacion - abonoMayorizacion)AS ventas
FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion IN (
61300103,
61300108,
61300207,
61300307
)
AND partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);

$totalDescKarcher = $cloud->row("
SELECT 
  sum(cargoMayorizacion - abonoMayorizacion) AS ventas
FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion IN (
61300102,
61300402
)
AND partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);

$totalDescSTHLI = $cloud->row("
SELECT 
    sum(cargoMayorizacion - abonoMayorizacion) AS ventas
FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion IN (
61300107,
61300206,
61300306,
61300506,
61300405
)
AND partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);


$totalDescAgricola = $cloud->row("
SELECT 
    sum(cargoMayorizacion - abonoMayorizacion) AS ventas
FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion IN (
61300105,
61300205,
61300505,
61300106,
613006
)
AND partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);

//! otros ingresos

$totalOtrosIngresos = $cloud->row("SELECT 
  sum(abonoMayorizacion - cargoMayorizacion) AS abonoMayorizacion FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion = ? AND partidaContaPeriodoId = ? AND flgDelete = ?", [71101, $contaPeriodo, 0]);

//! Total de descuentos

$totalDescuento = $totalDescBombas->ventas +
    $totalDescKarcher->ventas +
    $totalDescAgricola->ventas +
    $totalDescSTHLI->ventas +
    $totalDescEmpaquetadora->ventas;

//TODOS: cuentas de detalle

$cuentasDetalle = [
    'bombas' => [
        541010010201,
        541010010202,
        541010010203,
        541010010204,
        541010010205,
        541010010206,
        541010010207,
        541010010208,
        541010010209,
        541010010211,
        541010010212,
        541010010213,
        541010010214,
        541010010215,
        541010010216,
        541010010219,
        541010010220,
        541010010221,
        541010010222,
        541010010223,
        541010010224,
        541010010226,
        541010010228,
        541010010229,
        541010010230,
        541010010232,
        541010010236,
        541010010237,
        541010010238,
        541010010239,
        541010010240,
        541010010242,
        541010010244,
        541010010246,
        541010010249,
        541010010252,
        541010010254,
        541010010255,
        541010010263,
        5410100103 //! CUENTA ADD: PROYECTO
    ],
    'empaquetadora' => [
        541010010401,
        541010010402,
        541010010403,
        541010010404,
        541010010405,
        541010010406,
        541010010407,
        541010010408,
        541010010409,
        541010010410,
        541010010411,
        541010010413,
        541010010414,
        541010010415,
        541010010417,
        541010010419,
        541010010420,
        541010010421,
        541010010424,
        541010010426,
        541010010427,
        541010010428,
        541010010429,
        541010010430,
        541010010432,
        541010010438,
        541010010439,
        541010010440,
        541010010444,
        541010010446,
        541010010449,
        541010010454,
        541010010459,
    ],
    'karcher' => [
        541010040101,
        541010040102,
        541010040103,
        541010040104,
        541010040105,
        541010040106,
        541010040107,
        541010040108,
        541010040109,
        541010040110,
        541010040111,
        541010040112,
        541010040113,
        541010040114,
        541010040115,
        541010040116,
        541010040117,
        541010040118,
        541010040119,
        541010040120,
        541010040121,
        541010040123,
        541010040124,
        541010040125,
        541010040126,
        541010040128,
        541010040129,
        541010040130,
        541010040132,
        541010040136,
        541010040137,
        541010040138,
        541010040139,
        541010040140,
        541010040142,
        541010040144,
        541010040145,
        541010040146,
        541010040149,
        541010040152,
        541010040154,
        541010040155,
        541010040122

    ],
    'sthil' => [
        541010010801,
        541010010802,
        541010010803,
        541010010804,
        541010010805,
        541010010806,
        541010010807,
        541010010808,
        541010010809,
        541010010810,
        541010010811,
        541010010812,
        541010010813,
        541010010814,
        541010010815,
        541010010816,
        541010010818,
        541010010819,
        541010010820,
        541010010821,
        541010010823,
        541010010824,
        541010010826,
        541010010828,
        541010010830,
        541010010832,
        541010010836,
        541010010838,
        541010010839,
        541010010840,
        541010010842,
        541010010844,
        541010010846,
        541010010847,
        541010010849,
        541010010852,
        541010010854,
        541010010855,
        541010010862

    ],
    'agricola' => [
        541010010901,
        541010010902,
        541010010903,
        541010010904,
        541010010905,
        541010010906,
        541010010907,
        541010010908,
        541010010910,
        541010010911,
        541010010912,
        541010010913,
        541010010914,
        541010010915,
        541010010917,
        541010010919,
        541010010920,
        541010010921,
        541010010923,
        541010010924,
        541010010926,
        541010010928,
        541010010930,
        541010010936,
        541010010937,
        541010010938,
        541010010939,
        541010010940,
        541010010941,
        541010010942,
        541010010944,
        541010010946,
        541010010949,
        541010010954,
        541010010955,
        541010010956

    ],
    'maquinaria' => [
        541010010519,
        541010010523,
        541010010524,
        541010010530,
        541010010546,
        5410100107
    ]
];


// Función para consultar datos por tipo
function obtenerDetallePorTipo($cloud, $cuentas, $contaPeriodo)
{
    if (empty($cuentas))
        return [];

    $placeholders = implode(',', array_fill(0, count($cuentas), '?'));
    $params = array_merge($cuentas, [$contaPeriodo]);
    $sql = "SELECT 
        numeroCuentaMayorizacion,
        descripcionCuentaMayorizacion, 
        (cargoMayorizacion - abonoMayorizacion) AS res
    FROM conta_mayorizacion_2025 
    WHERE numeroCuentaMayorizacion  IN ($placeholders) 
        AND partidaContaPeriodoId = ? 
        AND flgDelete = 0";
    return $cloud->rows($sql, $params);
}

function obtenerTotalPorTipo($cloud, $cuentas, $contaPeriodo)
{
    if (empty($cuentas))
        return [];

    $placeholders = implode(',', array_fill(0, count($cuentas), '?'));
    $params = array_merge($cuentas, [$contaPeriodo]);
    $sql = "SELECT SUM(cargoMayorizacion - abonoMayorizacion) AS total
            FROM conta_mayorizacion_2025 
            WHERE numeroCuentaMayorizacion IN ($placeholders)
            AND partidaContaPeriodoId = ? 
            AND flgDelete = 0";
    return $cloud->row($sql, $params);
}

// Ejecutar para cada tipo
$bombasData = obtenerDetallePorTipo($cloud, $cuentasDetalle['bombas'], $contaPeriodo);
$empaquetadoraData = obtenerDetallePorTipo($cloud, $cuentasDetalle['empaquetadora'], $contaPeriodo);
$karcherData = obtenerDetallePorTipo($cloud, $cuentasDetalle['karcher'], $contaPeriodo);
$sthilData = obtenerDetallePorTipo($cloud, $cuentasDetalle['sthil'], $contaPeriodo);
$agricolaData = obtenerDetallePorTipo($cloud, $cuentasDetalle['agricola'], $contaPeriodo);
$maquinariaData = obtenerDetallePorTipo($cloud, $cuentasDetalle['maquinaria'], $contaPeriodo);


// Función para convertir los resultados a formato [descripcion => saldo]

function procesarDatos($datos)
{
    $res = [];
    foreach ($datos as $row) {
        $desc = trim(strtoupper($row->descripcionCuentaMayorizacion));
        $res[$desc] = floatval($row->res);
    }
    return $res;
}

$bombas = procesarDatos($bombasData);
$empaquetadora = procesarDatos($empaquetadoraData);
$karcher = procesarDatos($karcherData);
$sthil = procesarDatos($sthilData);
$agricola = procesarDatos($agricolaData);
$maquinaria = procesarDatos($maquinariaData);

// Obtener todas las descripciones únicas
$descripciones = array_unique(array_merge(
    array_keys($bombas),
    array_keys($empaquetadora),
    array_keys($karcher),
    array_keys($sthil),
    array_keys($agricola),
    array_keys($maquinaria)
));


//TODOS: Calculo de porsentajes 
if ($totalVenta != 0) {
    // 1. Calcular proporciones iniciales
    $valores = [
        'bombas' => $totalVentasBombas->ventas / $totalVenta,
        'empaq' => $totalVentasEmpaquetadora->ventas / $totalVenta,
        'karcher' => $totalVentasKarcher->ventas / $totalVenta,
        'sthli' => $totalVentasSTHLI->ventas / $totalVenta,
        'agricola' => $totalVentasAgricola->ventas / $totalVenta,
        'maqui' => $totalVentasMaquinaria->ventas / $totalVenta,
    ];

    // 2. Redondeo inicial (ejemplo: 4 decimales)
    foreach ($valores as $k => $v) {
        $valores[$k] = round($v, 4);
    }

    // 3. Ver la suma y calcular diferencia
    $suma = array_sum($valores);
    $diferencia = 1 - $suma;

    // 4. Reajustar todos los valores proporcionalmente
    if (abs($diferencia) > 0.00001) {
        foreach ($valores as $k => $v) {
            $valores[$k] += ($v / $suma) * $diferencia;
        }
    }

    // 5. Guardar en variables finales
    $porcBombas = $valores['bombas'];
    $porcEmpaquetadora = $valores['empaq'];
    $porcKarcher = $valores['karcher'];
    $porcSTHLI = $valores['sthli'];
    $porcAgricola = $valores['agricola'];
    $porcMaquinaria = $valores['maqui'];
} else {
    // Sin ventas, todo 0
    $porcBombas = $porcEmpaquetadora = $porcKarcher = $porcSTHLI = $porcAgricola = $porcMaquinaria = 0;
}


//TODOS: Calculo de gastos cuentas


// Gasto financieros 55
$totalGFinacieros = $cloud->row('SELECT sum(cargoMayorizacion) - sum(abonoMayorizacion) AS res FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion = ? AND partidaContaPeriodoId = ? AND flgDelete = ?', ['55', $contaPeriodo, 0]);

// Gasto departamento de administracion 5310100105
$totalGAdmin = $cloud->row('SELECT sum(cargoMayorizacion) - sum(abonoMayorizacion) AS res FROM conta_mayorizacion_2025
WHERE numeroCuentaMayorizacion = ? AND partidaContaPeriodoId = ? AND flgDelete = ?', ['5310100105', $contaPeriodo, 0]);


// Gastos generales : OTROS DEPARTAMENTOS (540100106) GASTOS DE VENTA SUC.SANTA ANA (5410100201) GASTOS DE VENTA SUC.SAN MIGUEL (5401003)
$totalGGal = $cloud->row("
SELECT sum(cargoMayorizacion - abonoMayorizacion) AS res              
FROM conta_mayorizacion_2025 WHERE numeroCuentaMayorizacion IN (5410100101,5410100106,54101002,54101003) 
AND partidaContaPeriodoId = ? AND flgDelete = 0
", [$contaPeriodo]);


//TODOS: varibles de gasto

$cuotaVentaGralBombas = $totalGGal->res * $porcBombas;
$cuotaVentaGralEmpaquetadora = $totalGGal->res * $porcEmpaquetadora;
$cuotaVentaGralKarcher = $totalGGal->res * $porcKarcher;
$cuotaVentaGralSTHLI = $totalGGal->res * $porcSTHLI;
$cuotaVentaGralMaquinaria = $totalGGal->res * $porcMaquinaria;
$cuotaVentaGralAgricola = ($totalGGal->res * $porcAgricola);

$cuotaGAdminBombas = $totalGAdmin->res * $porcBombas;
$cuotaGAdminEmpaquetadora = $totalGAdmin->res * $porcEmpaquetadora;
$cuotaGAdminKarcher = $totalGAdmin->res * $porcKarcher;
$cuotaGAdminSTHLI = $totalGAdmin->res * $porcSTHLI;
$cuotaGAdminMaquinaria = $totalGAdmin->res * $porcMaquinaria;
$cuotaGAdminAgricola = ($totalGAdmin->res * $porcAgricola);

$cuotaGFinancierosBombas = $totalGFinacieros->res * $porcBombas;
$cuotaGFinancierosEmpaquetadora = $totalGFinacieros->res * $porcEmpaquetadora;
$cuotaGFinancierosKarcher = $totalGFinacieros->res * $porcKarcher;
$cuotaGFinancierosSTHLI = $totalGFinacieros->res * $porcSTHLI;
$cuotaGFinancierosMaquinaria = $totalGFinacieros->res * $porcMaquinaria;
$cuotaGFinancierosAgricola = ($totalGFinacieros->res * $porcAgricola);

$totalDetBombas = obtenerTotalPorTipo($cloud, $cuentasDetalle['bombas'], $contaPeriodo);
$totalDetEmpaquetadora = obtenerTotalPorTipo($cloud, $cuentasDetalle['empaquetadora'], $contaPeriodo);
$totalDetKarcher = obtenerTotalPorTipo($cloud, $cuentasDetalle['karcher'], $contaPeriodo);
$totalDetSthil = obtenerTotalPorTipo($cloud, $cuentasDetalle['sthil'], $contaPeriodo);
$totalDetAgricola = obtenerTotalPorTipo($cloud, $cuentasDetalle['agricola'], $contaPeriodo);
$totalDetMaquinaria = obtenerTotalPorTipo($cloud, $cuentasDetalle['maquinaria'], $contaPeriodo);


//!TOTAL GENERAL
$totalBombas = $totalDetBombas->total + $cuotaVentaGralBombas + $cuotaGAdminBombas + $cuotaGFinancierosBombas;
$totalEmpaquetadora = $totalDetEmpaquetadora->total + $cuotaVentaGralEmpaquetadora + $cuotaGAdminEmpaquetadora + $cuotaGFinancierosEmpaquetadora;
$totalKarcher = $totalDetKarcher->total + $cuotaVentaGralKarcher + $cuotaGAdminKarcher + $cuotaGFinancierosKarcher;
$totalSthil = $totalDetSthil->total + $cuotaVentaGralSTHLI + $cuotaGAdminSTHLI + $cuotaGFinancierosSTHLI;
$totalAgricola = $totalDetAgricola->total + $cuotaVentaGralAgricola + $cuotaGAdminAgricola + $cuotaGFinancierosAgricola;
$totalMaquina = $totalDetMaquinaria->total + $cuotaVentaGralMaquinaria + $cuotaGAdminMaquinaria + $cuotaGFinancierosMaquinaria;
$totalAgriMa = $totalAgricola + $totalMaquina;
?>



<div class="container-fluid">
    <div class="row mb-3">
        <div class="col d-flex justify-content-end">
            <button type="button" id="btnReporteExcel" class="btn btn-success shadow-sm">
                <i class="fas fa-file-excel me-2"></i>Exportar a Excel
            </button>
        </div>
    </div>

    <div class="row mb-4 text-center">
        <div class="col">
            <h4 class="fw-bold">Estado de resultado / Centro de costo</h4>
            <h5 class="text-muted"><?= $dataPeriodo->periodo ?></h5>
            <small class="text-secondary fst-italic">(En dólares de los Estados Unidos de América)</small>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover" id="tblEstadoResultado" style="border: 1px solid black;">
            <thead>
                <tr>
                    <th></th>
                    <th colspan="2"
                        style="border: 1px solid black; text-align: center; background-color: #BDD7EE; font-weight: bold;">
                        <?= number_format($porcBombas * 100, 2) ?>%
                    </th>
                    <th colspan="2"
                        style="border: 1px solid black; text-align: center; background-color: #F8CBAD; font-weight: bold;">
                        <?= number_format($porcEmpaquetadora * 100, 2) ?>%
                    </th>
                    <th colspan="2"
                        style="border: 1px solid black; text-align: center; background-color: #FFFF00; font-weight: bold;">
                        <?= number_format($porcKarcher * 100, 2) ?>%
                    </th>
                    <th colspan="2"
                        style="border: 1px solid black; text-align: center; background-color: #FFC000; font-weight: bold;">
                        <?= number_format($porcSTHLI * 100, 2) ?>%
                    </th>
                    <th colspan="2"
                        style="border: 1px solid black; text-align: center; background-color: #92D050; font-weight: bold;">
                        <?= number_format(($porcAgricola + $porcMaquinaria) * 100, 2) ?>%
                    </th>
                    <th></th>
                </tr>

                <tr>
                    <th><b>DESCRIPCIÓN</b></th>
                    <th colspan="2"
                        style="border: 1px solid black; text-align: center; background-color: #BDD7EE; font-weight: bold;">
                        <b>BOMBAS</b>
                    </th>
                    <th colspan="2"
                        style="border: 1px solid black; text-align: center; background-color: #F8CBAD; font-weight: bold;">
                        <b>EMPAQUETADORA</b>
                    </th>
                    <th colspan="2"
                        style="border: 1px solid black; text-align: center; background-color: #FFFF00; font-weight: bold;">
                        <b>KARCHER</b>
                    </th>
                    <th colspan="2"
                        style="border: 1px solid black; text-align: center; background-color: #FFC000; font-weight: bold;">
                        <b>STHIL</b>
                    </th>
                    <th colspan="2"
                        style="border: 1px solid black; text-align: center; background-color: #92D050; font-weight: bold;">
                        <b>L.AGRICOLA</b>
                    </th>
                    <th style="text-align: center; "><b>TOTAL</b></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid black;"><b>VENTAS</b></td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($totalVentasBombas->ventas) ?></b></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($totalVentasEmpaquetadora->ventas) ?></b></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($totalVentasKarcher->ventas) ?></b></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($totalVentasSTHLI->ventas) ?></b></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">
                        <b>$<?= fmt($totalVentasAgricola->ventas + $totalVentasMaquinaria->ventas) ?></b>
                    </td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($totalVenta) ?></b></td>
                </tr>
                <tr>
                    <td style="border: 1px solid black;">Menos: Descto. y Devoluc.</td>
                    <td style="border: 1px solid black;">$<?= fmt($totalDescBombas->ventas) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($totalDescEmpaquetadora->ventas) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($totalDescKarcher->ventas) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($totalDescSTHLI->ventas) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($totalDescAgricola->ventas) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($totalDescuento) ?></td>
                </tr>
                <tr>
                    <?php
                    $ventaNetaBombas = $totalVentasBombas->ventas - $totalDescBombas->ventas;
                    $ventaNetaEmpaquetadora = $totalVentasEmpaquetadora->ventas - $totalDescEmpaquetadora->ventas;
                    $ventaNetaKarcher = $totalVentasKarcher->ventas - $totalDescKarcher->ventas;
                    $ventaNetaSTHLI = $totalVentasSTHLI->ventas - $totalDescSTHLI->ventas;
                    $ventaNetaAgricola = $totalVentasAgricola->ventas + $totalVentasMaquinaria->ventas - $totalDescAgricola->ventas;
                    $totalVentaNeta = $totalVenta - $totalDescuento;
                    ?>
                <tr>
                    <td style="border: 1px solid black;"><b>VENTAS NETAS</b></td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($ventaNetaBombas) ?></b></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($ventaNetaEmpaquetadora) ?></b></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($ventaNetaKarcher) ?></b></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($ventaNetaSTHLI) ?></b></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($ventaNetaAgricola) ?></b></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($totalVentaNeta) ?></b></td>
                </tr>
                <tr style="color: red">
                    <td><b>Menos: COSTO DE VENTAS</b></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input id="costoVentaBombas" type="number" class="form-control" placeholder="0.00" min="0"
                                step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="costoVentaBombaT"></span> %</b></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input id="costoVentaEmp" type="number" class="form-control" placeholder="0.00" min="0"
                                step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="costoVentasEmpT"></span> %</b></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input id="costoVentaKar" type="number" class="form-control" placeholder="0.00" min="0"
                                step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="costoVentasKarT"></span> %</b></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input id="costoVentaSth" type="number" class="form-control" placeholder="0.00" min="0"
                                step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="costoVentasSthT"></span> %</b></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input id="costoVentaAgro" type="number" class="form-control" placeholder="0.00" min="0"
                                step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="costoVentasAgroT"></span> %</b></td>
                    <td style="border: 1px solid black;"><b><span id="totalCostoVenta"></span></b></td>
                </tr>
                <tr>
                    <td style="border: 1px solid black;">Mas: COSTOS DE VENTAS N/C</td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input id="costoVentaBombasNC" type="number" class="form-control" placeholder="0.00" min="0"
                                step="0.01">
                        </div>
                    </td>
                    <td></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input id="costoVentaEmpNC" type="number" class="form-control" placeholder="0.00" min="0"
                                step="0.01">
                        </div>
                    </td>
                    <td></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input id="costoVentaKarNC" type="number" class="form-control" placeholder="0.00" min="0"
                                step="0.01">
                        </div>
                    </td>
                    <td></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input id="costoVentaSthNC" type="number" class="form-control" placeholder="0.00" min="0"
                                step="0.01">
                        </div>
                    </td>
                    <td></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input id="costoVentaAgroNC" type="number" class="form-control" placeholder="0.00" min="0"
                                step="0.01">
                        </div>
                    </td>
                    <td></td>
                    <td style="border: 1px solid black;"><b><span id="totalCostoVentaNC"></span></b></td>
                </tr>
                <tr>
                    <td style="border: 1px solid black;"><b>UTILIDAD BRUTA</b></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input disabled id="utilidadBombas" type="number" class="form-control" placeholder="0.00"
                                min="0" step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="utilidadBombasT"></span>%</b></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input disabled id="utilidadEmp" type="number" class="form-control" placeholder="0.00"
                                min="0" step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="utilidadEmpT"></span>%</b> </td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input disabled id="utilidadKar" type="number" class="form-control" placeholder="0.00"
                                min="0" step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="utilidadKarT"></span>%</b> </td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input disabled id="utilidadSth" type="number" class="form-control" placeholder="0.00"
                                min="0" step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b>
                            <span id="utilidadSthT"></span>%</b> </td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input disabled id="utilidadAgro" type="number" class="form-control" placeholder="0.00"
                                min="0" step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="utilidadAgroT"></span>%</b></td>
                    <td style="border: 1px solid black;"><b><span id="totalUtili"></span></b></td>
                </tr>
                <tr style="color: red">
                    <td style="border: 1px solid black;"><b>Menos: GASTOS DE OPERACION</b></td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($totalBombas) ?></b></td>
                    <td style="border: 1px solid black;">
                        <b><?= round(($totalBombas / $ventaNetaBombas), 4) * 100 ?>%</b>
                    </td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($totalEmpaquetadora) ?></b></td>
                    <td style="border: 1px solid black;">
                        <b><?= round(($totalEmpaquetadora / $ventaNetaEmpaquetadora), 4) * 100 ?>%</b>
                    </td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($totalKarcher) ?></b></td>
                    <td style="border: 1px solid black;">
                        <b><?= round(($totalKarcher / $ventaNetaKarcher), 3) * 100 ?>%</b>
                    </td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($totalSthil) ?></b></td>
                    <td style="border: 1px solid black;"><b><?= round(($totalSthil / $ventaNetaSTHLI), 4) * 100 ?>%</b>
                    </td>
                    <td style="border: 1px solid black;"><b>$<?= fmt($totalAgriMa) ?></b></td>
                    <td style="border: 1px solid black;">
                        <b><?= round(($totalAgriMa / $ventaNetaAgricola), 4) * 100 ?>%</b>
                    </td>
                    <td style="border: 1px solid black;">
                        <b>$<?= fmt(($totalBombas + $totalEmpaquetadora + $totalKarcher + $totalSthil + $totalAgriMa)) ?></b>
                    </td>
                </tr>
                </tr>
                <?php foreach ($descripciones as $desc): ?>
                    <?php
                    $valBombas = $bombas[$desc] ?? 0;
                    $valEmpaque = $empaquetadora[$desc] ?? 0;
                    $valKarcher = $karcher[$desc] ?? 0;
                    $valSthil = $sthil[$desc] ?? 0;
                    $valAgricola = $agricola[$desc] ?? 0;
                    $valMaquinaria = $maquinaria[$desc] ?? 0;
                    $total = $valBombas + $valEmpaque + $valKarcher + $valSthil + $valAgricola + $valMaquinaria;
                    ?>
                    <tr>
                        <td style="border: 1px solid black;"><?= htmlentities($desc) ?></td>
                        <td style="border: 1px solid black;">$ <?= fmt($valBombas) ?></td>
                        <td style="border: 1px solid black;"></td>
                        <td style="border: 1px solid black;">$ <?= fmt($valEmpaque) ?></td>
                        <td style="border: 1px solid black;"></td>
                        <td style="border: 1px solid black;">$ <?= fmt($valKarcher) ?></td>
                        <td style="border: 1px solid black;"></td>
                        <td style="border: 1px solid black;">$ <?= fmt($valSthil) ?></td>
                        <td style="border: 1px solid black;"></td>
                        <td style="border: 1px solid black;">$ <?= fmt($valAgricola + $valMaquinaria) ?>
                        </td>
                        <td style="border: 1px solid black;"></td>
                        <td style="border: 1px solid black;">$ <?= fmt($total) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td style="border: 1px solid black;">Cuota Part. Gts. de Vta. Gral.</td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaVentaGralBombas) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaVentaGralEmpaquetadora) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaVentaGralKarcher) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaVentaGralSTHLI) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">
                        $<?= fmt($cuotaVentaGralAgricola + $cuotaVentaGralMaquinaria) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">
                        $<?= fmt($cuotaVentaGralBombas + $cuotaVentaGralEmpaquetadora + $cuotaVentaGralKarcher + $cuotaVentaGralSTHLI + $cuotaVentaGralAgricola + $cuotaVentaGralMaquinaria) ?>
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black;">Cuota Part. Gts. Adtvos.</td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaGAdminBombas) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaGAdminEmpaquetadora) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaGAdminKarcher) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaGAdminSTHLI) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaGAdminAgricola + $cuotaGAdminMaquinaria) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">
                        $<?= fmt($cuotaGAdminBombas + $cuotaGAdminEmpaquetadora + $cuotaGAdminKarcher + $cuotaGAdminSTHLI + $cuotaGAdminAgricola + $cuotaGAdminMaquinaria) ?>
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black;">Cuota Part. Gts. Financieros</td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaGFinancierosBombas) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaGFinancierosEmpaquetadora) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaGFinancierosKarcher) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">$<?= fmt($cuotaGFinancierosSTHLI) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">
                        $<?= fmt($cuotaGFinancierosAgricola + $cuotaGFinancierosMaquinaria) ?></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">
                        $<?= fmt($cuotaGFinancierosAgricola + $cuotaGFinancierosMaquinaria + $cuotaGFinancierosSTHLI + $cuotaGFinancierosKarcher + $cuotaGFinancierosEmpaquetadora + $cuotaGFinancierosBombas) ?>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black;"><b>UTILIDAD DE OPERACION </b></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input disabled id="utilidadBombasOp" type="number" class="form-control" placeholder="0.00"
                                min="0" step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="utilidadBombasTOp"></span>%</b> </td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input disabled id="utilidadEmpOp" type="number" class="form-control" placeholder="0.00"
                                min="0" step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="utilidadEmpTOp"> </span>%</b></td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input disabled id="utilidadKarOp" type="number" class="form-control" placeholder="0.00"
                                min="0" step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="utilidadKarTOp"></span>%</b> </td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input disabled id="utilidadSthOp" type="number" class="form-control" placeholder="0.00"
                                min="0" step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b><span id="utilidadSthTOp"></span>%</b> </td>
                    <td style="border: 1px solid black;">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input disabled id="utilidadAgroOp" type="number" class="form-control" placeholder="0.00"
                                min="0" step="0.01">
                        </div>
                    </td>
                    <td style="border: 1px solid black;"><b id="utilidadAgroTOp"></b> %</td>
                    <td style="border: 1px solid black;"><b><span id="totalUtiliOp"></span></b></td>
                </tr>
                <tr>
                    <td style="border: 1px solid black;">Más: Otros Ingresos</td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">
                        $ <?= fmt($totalOtrosIngresos->abonoMayorizacion) ?>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black;"><b>UTILIDAD NETA</b></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"><b id="utilidadNeta"></b></td>
                </tr>
            </tbody>
        </table>
    </div>
    <h3>MAQUINARIAS:</h3>
    <!--<p>Ventas agricola <?= $totalVentasAgricola->ventas ?></p>-->
    <p> GASTOS : <?= round($porcMaquinaria, 4) * 100 ?>% Total de ventas:
        $<?= fmt($totalVentasMaquinaria->ventas) ?></p>
    <p> Total Gastos : $<?= fmt($totalMaquina) ?></p>
    <p> Cuota Part. Gts. de Vta. Gral. : $<?= fmt($cuotaVentaGralMaquinaria) ?></p>
    <p> Cuota Part. Gts. Adtvos. : $<?= fmt($cuotaGAdminMaquinaria) ?></p>
    <p> Cuota Part. Gts. Financieros : $<?= fmt($cuotaGFinancierosMaquinaria) ?></p>
</div>


<script>
    function formatoDolar(valor) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(valor || 0);
    }

    // Convierte inputs a texto antes de exportar
    function prepararTablaParaExcel() {
        document.querySelectorAll("table input").forEach(input => {
            const valor = parseFloat(input.value) || 0;
            const parent = input.closest("td");
            if (parent) {
                parent.setAttribute("data-original", parent.innerHTML); // guarda el html original
                parent.innerHTML = formatoDolar(valor);
            }
        });
    }

    // Restaura la tabla después de exportar
    function restaurarTabla() {
        document.querySelectorAll("td[data-original]").forEach(td => {
            td.innerHTML = td.getAttribute("data-original");
            td.removeAttribute("data-original");
        });
    }

    function getTotalCostoVenta() {
        let bomba = parseFloat($("#costoVentaBombas").val()) || 0;
        let Emp = parseFloat($("#costoVentaEmp").val()) || 0;
        let Kar = parseFloat($("#costoVentaKar").val()) || 0;
        let Sth = parseFloat($("#costoVentaSth").val()) || 0;
        let Agro = parseFloat($("#costoVentaAgro").val()) || 0;

        return formatoDolar(bomba + Emp + Kar + Sth + Agro);
    }

    function getTotalCostoVentaNC() {
        let bomba = parseFloat($("#costoVentaBombasNC").val()) || 0;
        let Emp = parseFloat($("#costoVentaEmpNC").val()) || 0;
        let Kar = parseFloat($("#costoVentaKarNC").val()) || 0;
        let Sth = parseFloat($("#costoVentaSthNC").val()) || 0;
        let Agro = parseFloat($("#costoVentaAgroNC").val()) || 0;

        return formatoDolar(bomba + Emp + Kar + Sth + Agro);
    }

    function getTotalUtilidad() {
        let bomba = parseFloat($("#utilidadBombas").val()) || 0;
        let Emp = parseFloat($("#utilidadEmp").val()) || 0;
        let Kar = parseFloat($("#utilidadKar").val()) || 0;
        let Sth = parseFloat($("#utilidadSth").val()) || 0;
        let Agro = parseFloat($("#utilidadAgro").val()) || 0;

        return formatoDolar(bomba + Emp + Kar + Sth + Agro);
    }

    function getTotalUtilidadOP() {
        let bomba = parseFloat($("#utilidadBombasOp").val()) || 0;
        let Emp = parseFloat($("#utilidadEmpOp").val()) || 0;
        let Kar = parseFloat($("#utilidadKarOp").val()) || 0;
        let Sth = parseFloat($("#utilidadSthOp").val()) || 0;
        let Agro = parseFloat($("#utilidadAgroOp").val()) || 0;

        let totalOP = bomba + Emp + Kar + Sth + Agro;
        let totalNeta = totalOP + <?= $totalOtrosIngresos->abonoMayorizacion ?>;

        $("#utilidadNeta").html(formatoDolar(totalNeta))

        return formatoDolar(totalOP);
    }


    $(document).ready(function() {
        $("#costoVentaBombas").on("change keyup", function() {
            let valor = $(this).val() || 0;
            const Calculo = (valor / <?= $ventaNetaBombas ?>) * 100;
            $("#costoVentaBombaT").html(Calculo.toFixed(2));
            $("#totalCostoVenta").html(getTotalCostoVenta());
            $("#costoVentaBombasNC").trigger("change");
        });

        $("#costoVentaEmp").on("change keyup", function() {
            let valor = $(this).val() || 0;
            const Calculo = (valor / <?= $ventaNetaEmpaquetadora ?>) * 100;
            $("#costoVentasEmpT").html(Calculo.toFixed(2));
            $("#totalCostoVenta").html(getTotalCostoVenta());
            $("#costoVentaEmpNC").trigger("change");
        });

        $("#costoVentaKar").on("change keyup", function() {
            let valor = $(this).val() || 0;
            const Calculo = (valor / <?= $ventaNetaKarcher ?>) * 100;
            console.log(Calculo);
            $("#costoVentasKarT").html(Calculo.toFixed(2));
            $("#totalCostoVenta").html(getTotalCostoVenta());
            $("#costoVentaKarNC").trigger("change");
        });

        $("#costoVentaSth").on("change keyup", function() {
            let valor = $(this).val() || 0;
            const Calculo = (valor / <?= $ventaNetaSTHLI ?>) * 100;
            $("#costoVentasSthT").html(Calculo.toFixed(2));
            $("#totalCostoVenta").html(getTotalCostoVenta());
            $("#costoVentaSthNC").trigger("change");
        });

        $("#costoVentaAgro").on("change keyup", function() {
            let valor = $(this).val() || 0;
            const Calculo = (valor / <?= $ventaNetaAgricola ?>) * 100;
            $("#costoVentasAgroT").html(Calculo.toFixed(2));
            $("#totalCostoVenta").html(getTotalCostoVenta());
            $("#costoVentaAgroNC").trigger("change");
        });

        //!=====================================================

        $("#costoVentaBombasNC").on("change keyup", function() {
            let valor = parseFloat($(this).val()) || 0;
            let cv = parseFloat($("#costoVentaBombas").val()) || 0;
            const Calculo = valor + <?= $ventaNetaBombas ?> - cv;
            $("#totalCostoVentaNC").html(getTotalCostoVentaNC());
            $("#utilidadBombas").val(Calculo).trigger("change");
        });

        //UTLIDAD BRUTA
        $("#utilidadBombas").on("change", function() {
            let valor = parseFloat($(this).val()) || 0;
            const Calculo = (valor / <?= $ventaNetaBombas ?>) * 100;
            $("#utilidadBombasT").html(Calculo.toFixed(2));
            $("#totalUtili").html(getTotalUtilidad());
            const calculoUtilidaOp = valor - <?= $totalBombas ?>;
            $("#utilidadBombasOp").val(calculoUtilidaOp).trigger("change");

        });

        //UTILIDAD OPERACION
        $("#utilidadBombasOp").on("change", function() {
            let valor = parseFloat($(this).val()) || 0;
            const Calculo = (valor / <?= $ventaNetaBombas ?>) * 100;
            $("#utilidadBombasTOp").html(Calculo.toFixed(2));
            $("#totalUtiliOp").html(getTotalUtilidadOP());
        });

        //TODOS: Empa

        $("#costoVentaEmpNC").on("change keyup", function() {
            let valor = parseFloat($(this).val()) || 0;
            let cv = parseFloat($("#costoVentaEmp").val()) || 0;
            const Calculo = valor + <?= $ventaNetaEmpaquetadora ?> - cv;
            $("#totalCostoVentaNC").html(getTotalCostoVentaNC());
            $("#utilidadEmp").val(Calculo).trigger("change");
        });

        //UTLIDAD BRUTA
        $("#utilidadEmp").on("change", function() {
            let valor = parseFloat($(this).val()) || 0;
            const Calculo = (valor / <?= $ventaNetaEmpaquetadora ?>) * 100;
            $("#utilidadEmpT").html(Calculo.toFixed(2));
            $("#totalUtili").html(getTotalUtilidad());
            const calculoUtilidaOp = valor - <?= $totalEmpaquetadora ?>;
            $("#utilidadEmpOp").val(calculoUtilidaOp).trigger("change");
        });

        //UTILIDAD OPERACION
        $("#utilidadEmpOp").on("change", function() {
            let valor = parseFloat($(this).val()) || 0;
            const Calculo = (valor / <?= $ventaNetaEmpaquetadora ?>) * 100;
            $("#utilidadEmpTOp").html(Calculo.toFixed(2));
            $("#totalUtiliOp").html(getTotalUtilidadOP());
        });

        //TODOS: KARCHER

        $("#costoVentaKarNC").on("change keyup", function() {
            let valor = parseFloat($(this).val()) || 0;
            let cv = parseFloat($("#costoVentaKar").val()) || 0;
            const Calculo = valor + <?= $ventaNetaKarcher ?> - cv;
            $("#totalCostoVentaNC").html(getTotalCostoVentaNC());
            $("#utilidadKar").val(Calculo).trigger("change");
        });

        //UTLIDAD BRUTA
        $("#utilidadKar").on("change", function() {
            let valor = parseFloat($(this).val()) || 0;
            const Calculo = (valor / <?= $ventaNetaKarcher ?>) * 100;
            $("#utilidadKarT").html(Calculo.toFixed(2));
            $("#totalUtili").html(getTotalUtilidad());
            const calculoUtilidaOp = valor - <?= $totalKarcher ?>;
            $("#utilidadKarOp").val(calculoUtilidaOp).trigger("change");
        });

        //UTILIDAD OPERACION
        $("#utilidadKarOp").on("change", function() {
            let valor = parseFloat($(this).val()) || 0;
            const Calculo = (valor / <?= $ventaNetaKarcher ?>) * 100;
            $("#utilidadKarTOp").html(Calculo.toFixed(2));
            $("#totalUtiliOp").html(getTotalUtilidadOP());
        });


        //TODOS: STIHL

        $("#costoVentaSthNC").on("change keyup", function() {
            let valor = parseFloat($(this).val()) || 0;
            let cv = parseFloat($("#costoVentaSth").val()) || 0;
            const Calculo = valor + <?= $ventaNetaSTHLI ?> - cv;
            $("#totalCostoVentaNC").html(getTotalCostoVentaNC());
            $("#utilidadSth").val(Calculo).trigger("change");
        });

        //UTLIDAD BRUTA
        $("#utilidadSth").on("change", function() {
            let valor = parseFloat($(this).val()) || 0;
            const Calculo = (valor / <?= $ventaNetaSTHLI ?>) * 100;
            $("#utilidadSthT").html(Calculo.toFixed(2));
            $("#totalUtili").html(getTotalUtilidad());
            const calculoUtilidaOp = valor - <?= $totalSthil ?>;
            $("#utilidadSthOp").val(calculoUtilidaOp).trigger("change");
        });

        //UTILIDAD OPERACION
        $("#utilidadSthOp").on("change", function() {
            let valor = parseFloat($(this).val()) || 0;
            const Calculo = (valor / <?= $ventaNetaSTHLI ?>) * 100;
            $("#utilidadSthTOp").html(Calculo.toFixed(2));
            $("#totalUtiliOp").html(getTotalUtilidadOP());
        });

        //TODOS: AGRICOLA 

        $("#costoVentaAgroNC").on("change keyup", function() {
            let valor = parseFloat($(this).val()) || 0;
            let cv = parseFloat($("#costoVentaAgro").val()) || 0;
            const Calculo = valor + <?= $ventaNetaAgricola ?> - cv;
            $("#totalCostoVentaNC").html(getTotalCostoVentaNC());
            $("#utilidadAgro").val(Calculo).trigger("change");
        });

        //UTLIDAD BRUTA
        $("#utilidadAgro").on("change", function() {
            let valor = parseFloat($(this).val()) || 0;
            const Calculo = (valor / <?= $ventaNetaAgricola ?>) * 100;
            $("#utilidadAgroT").html(Calculo.toFixed(2));
            $("#totalUtili").html(getTotalUtilidad());
            const calculoUtilidaOp = valor - <?= $totalAgriMa ?>;
            $("#utilidadAgroOp").val(calculoUtilidaOp).trigger("change");
        });

        //UTILIDAD OPERACION
        $("#utilidadAgroOp").on("change", function() {
            let valor = parseFloat($(this).val()) || 0;
            const Calculo = (valor / <?= $ventaNetaAgricola ?>) * 100;
            $("#utilidadAgroTOp").html(Calculo.toFixed(2));
            $("#totalUtiliOp").html(getTotalUtilidadOP());
        });

        $("#btnReporteExcel").click(function(e) {
            prepararTablaParaExcel();
            $("#tblEstadoResultado").table2excel({
                name: `<?php echo $nombre_archivo; ?>`,
                filename: `<?php echo $nombre_archivo; ?>`
            });
            restaurarTabla();
        });
    });
</script>