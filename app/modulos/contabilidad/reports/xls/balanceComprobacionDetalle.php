<?php
@session_start();
ini_set('memory_limit', '-1');
ini_set("pcre.backtrack_limit", "10000000");

require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

$contaPeriodo = $_POST['fechaInicio'] ?? 0;

function fmt($n)
{
    return number_format((float) $n, 2, '.', ',');
}

$dataPeriodo = $cloud->row("SELECT CONCAT(mesNombre,' ',anio) AS periodo FROM conta_partidas_contables_periodos WHERE partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);
$nombre_archivo = "Balance de comprobación(Anexo)" . $dataPeriodo->periodo;
$detalle = $cloud->rows("
    SELECT
        cuentaContaId,
        cuentaPadreId,
        nivelCuenta,
        numeroCuentaMayorizacion AS numeroCuenta,
        descripcionCuentaMayorizacion AS descripcionCuenta,
        saldoInicialMayorizacion AS saldoInicial,
        cargoMayorizacion AS cargo,
        abonoMayorizacion AS abono,
        saldoFinalMayorizacion AS saldoFinal
    FROM conta_mayorizacion_2025
    WHERE (
        saldoInicialMayorizacion <> ?
        OR cargoMayorizacion <> ?
        OR abonoMayorizacion <> ?
        OR saldoFinalMayorizacion <> ?
    ) 
    AND partidaContaPeriodoId = ?
    AND flgDelete = ?
    ORDER BY LENGTH(numeroCuentaMayorizacion), numeroCuentaMayorizacion
", [0, 0, 0, 0, $contaPeriodo, 0]);

$nombreArchivo = "Balance_Comprobacion_" . str_replace(' ', '_', $dataPeriodo->periodo);

$cuentasById = [];
$hijosPorPadre = [];

foreach ($detalle as $cuenta) {
    $cuentasById[$cuenta->cuentaContaId] = $cuenta;
    $hijosPorPadre[$cuenta->cuentaPadreId ?? 0][] = $cuenta;
}

function renderArbol($padreId, $nivel, $cuentasById, $hijosPorPadre, &$procesados)
{
    if (!isset($hijosPorPadre[$padreId]))
        return;

    foreach ($hijosPorPadre[$padreId] as $cuenta) {
        if (in_array($cuenta->cuentaContaId, $procesados))
            continue;
        if ($cuenta->cuentaContaId == $cuenta->cuentaPadreId)
            continue;

        $procesados[] = $cuenta->cuentaContaId;

        $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $nivel);
        echo "<tr>
            <td>{$cuenta->numeroCuenta}</td>
            <td>{$cuenta->descripcionCuenta}</td>
            <td>" . fmt($cuenta->saldoInicial) . "</td>
            <td>" . fmt($cuenta->cargo) . "</td>
            <td>" . fmt($cuenta->abono) . "</td>
            <td>" . fmt($cuenta->saldoFinal) . "</td>
        </tr>";

        renderArbol($cuenta->cuentaContaId, $nivel + 1, $cuentasById, $hijosPorPadre, $procesados);
    }
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
            <h4 class="fw-bold">Balance de Comprobación (Anexo)</h4>
            <h5 class="text-muted"><?= $dataPeriodo->periodo ?></h5>
            <small class="text-secondary fst-italic">(En dólares de los Estados Unidos de América)</small>
        </div>
    </div>

    <div class="table-responsive">
        <table id="tablaBalance" class="table table-hover table-sm">
            <thead>
                <tr>
                    <th>Cuenta</th>
                    <th>Descripción</th>
                    <th>Saldo Inicial</th>
                    <th>Cargo</th>
                    <th>Abono</th>
                    <th>Saldo Final</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $procesados = [];
                renderArbol(0, 0, $cuentasById, $hijosPorPadre, $procesados);
                ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    $(document).ready(function () {
        $("#btnExportarExcel").click(function (e) {
            $("#tablaBalance").table2excel({
                name: `<?php echo $nombre_archivo; ?>`, filename: `<?php echo $nombre_archivo; ?>`
            });
        });
    });
</script>