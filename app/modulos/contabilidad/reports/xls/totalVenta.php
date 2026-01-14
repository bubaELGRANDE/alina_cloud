<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
require_once("../../../../../libraries/includes/logic/functions/funciones-conta.php");
@session_start();

$contaPeriodo = $_POST['fechaInicio'] ?? 0;

// OBTENEMOS MES Y AÑO REALES DEL PERIODO
$dataPeriodo = $cloud->row("
    SELECT 
        CONCAT(mesNombre, ' ', anio) AS periodo,
        mes,
        anio
    FROM conta_partidas_contables_periodos 
    WHERE partidaContaPeriodoId = ? 
    AND flgDelete = 0
", [$contaPeriodo]);

$mes = $dataPeriodo->mes;
$anio = $dataPeriodo->anio;

// RANGO DEL MES
$fechaInicio = "$anio-$mes-01";
$fechaFin = date("Y-m-t", strtotime($fechaInicio));

$periodo = new DatePeriod(
    new DateTime($fechaInicio),
    new DateInterval('P1D'),
    new DateTime("$fechaFin +1 day")
);

// ACUMULADORES DEL TOTAL FINAL
$totalesFinal = [
    'ventasContado' => 0,
    'ventasCredito' => 0,
    'notasCredito' => 0,
    'abonos'        => 0,
    'comprasPost'   => 0
];

// FUNCIÓN PARA SUMAR LOS VALORES DEL JSON
function safe($arr, $key)
{
    return isset($arr[$key]) ? (float)$arr[$key] : 0;
}

?>
<div class="container-fluid px-4">

    <div class="row mb-3">
        <div class="col d-flex justify-content-end">
            <button type="button" id="btnExcel" class="btn btn-success shadow-sm">
                <i class="fas fa-file-excel me-2"></i>Exportar Excel
            </button>
        </div>
    </div>

    <h4 class="fw-bold text-center">Movimientos del Mes</h4>
    <h5 class="text-muted text-center"><?= $dataPeriodo->periodo ?></h5>

    <div class="table-responsive">
        <table id="tblReporteDiario" class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Ventas Contado</th>
                    <th>Ventas Crédito</th>
                    <th>Notas Crédito</th>
                    <th>Abonos</th>
                    <th>Compras Post</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($periodo as $fecha) {
                    $fechaActual = $fecha->format("Y-m-d");

                    // LLAMADA A TU FUNCIÓN
                    $json = obtenerTotalesVentasPorFecha($cloud, $fechaActual);
                    $data = json_decode($json, true);

                    if ($data['status'] !== 'success') continue;

                    $v = $data['totales']; // ACORTAMOS

                    // Datos del día
                    $ventasContado = safe($v['ventasContado'], 'subTotal');
                    $ventasCredito = safe($v['ventasCredito'], 'subTotal');
                    $notasCredito  = safe($v['notasCredito'], 'subTotal');
                    $abonos        = safe($v['notasAbono'], 'totalAbonos');
                    $comprasPost   = safe($v['comprasPost'], 'subTotal');

                    // ACUMULAR TOTALES DEL MES
                    $totalesFinal['ventasContado'] += $ventasContado;
                    $totalesFinal['ventasCredito'] += $ventasCredito;
                    $totalesFinal['notasCredito']  += $notasCredito;
                    $totalesFinal['abonos']        += $abonos;
                    $totalesFinal['comprasPost']   += $comprasPost;

                    echo "
                    <tr>
                        <td>$fechaActual</td>
                        <td>$" . number_format($ventasContado, 2, '.', ',') . "</td>
                        <td>$" . number_format($ventasCredito, 2, '.', ',') . "</td>
                        <td>$" . number_format($notasCredito, 2, '.', ',') . "</td>
                        <td>$" . number_format($abonos, 2, '.', ',') . "</td>
                        <td>$" . number_format($comprasPost, 2, '.', ',') . "</td>
                    </tr>";
                }
                ?>
            </tbody>

            <!-- TOTAL FINAL -->
            <tfoot class="table-dark fw-bold">
                <tr>
                    <td>Total del Mes</td>
                    <td>$<?= number_format($totalesFinal['ventasContado'], 2, '.', ',') ?></td>
                    <td>$<?= number_format($totalesFinal['ventasCredito'], 2, '.', ',') ?></td>
                    <td>$<?= number_format($totalesFinal['notasCredito'], 2, '.', ',') ?></td>
                    <td>$<?= number_format($totalesFinal['abonos'], 2, '.', ',') ?></td>
                    <td>$<?= number_format($totalesFinal['comprasPost'], 2, '.', ',') ?></td>
                </tr>
            </tfoot>

        </table>
    </div>
</div>

<script>
    $(function() {
        $("#btnExcel").click(function() {
            $("#tblReporteDiario").table2excel({
                name: "Reporte Diario",
                filename: "Reporte_Mensual_<?= $dataPeriodo->periodo ?>"
            });
        });
    });
</script>