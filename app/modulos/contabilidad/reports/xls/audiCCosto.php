<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$contaPeriodoI = $_POST['fechaInicio'] ?? 0;
$contaPeriodoF = $_POST['fechaFin'] ?? 0;

$dataPeriodoInicio = $cloud->row("
    SELECT mesNombre, anio 
    FROM conta_partidas_contables_periodos
    WHERE partidaContaPeriodoId = ? AND flgDelete = 0
", [$contaPeriodoI]);

$dataPeriodoFinal = $cloud->row("
    SELECT mesNombre, anio 
    FROM conta_partidas_contables_periodos
    WHERE partidaContaPeriodoId = ? AND flgDelete = 0
", [$contaPeriodoF]);

$dataCentroCosto = $cloud->rows("
    SELECT *
    FROM conta_cuentas_contables
    WHERE numeroCuenta LIKE '5%' AND tipoCuenta = 'Mayor'
    AND nivelCuenta = 6
");



function fmt($n)
{
    return number_format((float)$n, 2, '.', ',');
}

function tituloRangoMeses($inicio, $fin)
{

    if ($inicio->mesNombre === $fin->mesNombre && $inicio->anio === $fin->anio) {
        return "{$inicio->mesNombre} {$inicio->anio}";
    }


    return "{$inicio->mesNombre} {$inicio->anio} – {$fin->mesNombre} {$fin->anio}";
}

$tituloPeriodo = tituloRangoMeses($dataPeriodoInicio, $dataPeriodoFinal);

$nombre_archivo = "Detalle de cuentas para auditoria " . $tituloPeriodo;

function renderArbol($padreId, $cloud, $periodoInicio, $periodoFinal)
{
    $detalle = $cloud->rows("
        SELECT 
            cuentaContaId,
            numeroCuentaMayorizacion AS numeroCuenta,
            descripcionCuentaMayorizacion AS descripcionCuenta,
            SUM(saldoInicialMayorizacion) AS saldoInicial,
            SUM(cargoMayorizacion) AS cargo,
            SUM(abonoMayorizacion) AS abono,
            SUM(saldoFinalMayorizacion) AS saldoFinal
        FROM conta_mayorizacion_2025
        WHERE descripcionCuentaMayorizacion IN (
            'SUELDOS', 'COMISIONES', 'VACACIONES',
            'TRANSPORTE Y VIATICOS', 'HORAS EXTRAS','PARTICIPACION Y BONIFICACIONES', 'PART. Y BONIFICACIONES','OTRAS PRESTACIONES SOCIALES'
        )
        AND numeroCuentaMayorizacion LIKE '5%'
        AND partidaContaPeriodoId BETWEEN ? AND ?
        AND cuentaPadreId = ?
        GROUP BY cuentaContaId, numeroCuentaMayorizacion, descripcionCuentaMayorizacion
    ", [$periodoInicio, $periodoFinal, $padreId]);


    // Totales del centro de costo
    $totalSI = 0;
    $totalCargo = 0;
    $totalAbono = 0;
    $totalSF = 0;

    foreach ($detalle as $cuenta) {

        echo "<tr>
            <td>{$cuenta->numeroCuenta}</td>
            <td>{$cuenta->descripcionCuenta}</td>
            <td>" . fmt($cuenta->saldoInicial) . "</td>
            <td>" . fmt($cuenta->cargo) . "</td>
            <td>" . fmt($cuenta->abono) . "</td>
            <td>" . fmt($cuenta->saldoFinal) . "</td>
        </tr>";

        // Acumular totales
        $totalSI += $cuenta->saldoInicial;
        $totalCargo += $cuenta->cargo;
        $totalAbono += $cuenta->abono;
        $totalSF += $cuenta->saldoFinal;
    }

    return [
        "si" => $totalSI,
        "cargo" => $totalCargo,
        "abono" => $totalAbono,
        "sf" => $totalSF
    ];
}

?>

<div class="container-fluid px-4">
    <div class="row mb-3">
        <div class="col d-flex justify-content-end">
            <button type="button" id="btnExportarExcel" class="btn btn-success shadow-sm">
                <i class="fas fa-file-excel me-2"></i>Exportar a Excel
            </button>
        </div>
    </div>

    <div class="row mb-4 text-center">
        <div class="col">
            <h4 class="fw-bold">Detalle de cuentas para auditoria</h4>
            <h5 class="text-muted"><?= $tituloPeriodo ?></h5>
            <small class="text-secondary fst-italic">(En dólares de los Estados Unidos de América)</small>
        </div>
    </div>

    <div class="table-responsive">
        <table id="tablaBalance" class="table table-hover table-sm">
            <?php
            // TOTAL GENERAL
            $grandSI = 0;
            $grandCargo = 0;
            $grandAbono = 0;
            $grandSF = 0;

            foreach ($dataCentroCosto as $cc) {
                echo "
                <thead>
                    <tr>
                        <th><b>Centro de costo:</b></th>
                        <th colspan='5'><b>{$cc->numeroCuenta} - {$cc->descripcionCuenta}</b></th>
                    </tr>
                    <tr class='table-light'>
                        <th>Cuenta</th>
                        <th>Descripción</th>
                        <th>Saldo Inicial</th>
                        <th>Cargo</th>
                        <th>Abono</th>
                        <th>Saldo Final</th>
                    </tr>
                </thead>
                <tbody>
                ";
                // Obtener subtotales por centro de costo
                $totales = renderArbol($cc->cuentaContaId, $cloud, $contaPeriodoI, $contaPeriodoF);
                // Subtotales
                echo "
                    <tr class='table-secondary fw-bold'>
                        <td colspan='2' class='text-end'>TOTAL :</td>
                        <td>" . fmt($totales['si']) . "</td>
                        <td>" . fmt($totales['cargo']) . "</td>
                        <td>" . fmt($totales['abono']) . "</td>
                        <td>" . fmt($totales['sf']) . "</td>
                    </tr>
                ";
                echo "</tbody>";
                // Acumular al total general
                $grandSI += $totales['si'];
                $grandCargo += $totales['cargo'];
                $grandAbono += $totales['abono'];
                $grandSF += $totales['sf'];
            }
            echo "
            <tfoot>
            <tr class='fw-bold'>
                <td colspan='2' class='text-end'>GRAN TOTAL GENERAL: </td>
                <td>" . fmt($grandSI) . "</td>
                <td>" . fmt($grandCargo) . "</td>
                <td>" . fmt($grandAbono) . "</td>
                <td>" . fmt($grandSF) . "</td>
            </tr>
            </tfoot>
            ";
            ?>

        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#btnExportarExcel").click(function() {
            $("#tablaBalance").table2excel({
                name: "<?= $nombre_archivo ?>",
                filename: "<?= $nombre_archivo ?>",
                exclude_links: true
            });
        });
    });
</script>