<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
$contaPeriodo = $_POST['fechaInicio'] ?? 0;

$dataPeriodo = $cloud->row("SELECT CONCAT(mesNombre, ' ', anio) AS periodo FROM conta_partidas_contables_periodos WHERE partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);


$detalle = $cloud->rows("
SELECT
    cuentaContaId,
    cuentaPadreId,
    nivelCuenta,
    numeroCuentaMayorizacion AS numeroCuenta,
    descripcionCuentaMayorizacion AS descripcionCuenta,
    saldoFinalMayorizacion AS saldoFinal
FROM conta_mayorizacion_2025
WHERE (
    saldoInicialMayorizacion <> ?
    OR cargoMayorizacion <> ?
    OR abonoMayorizacion <> ?
    OR saldoFinalMayorizacion <> ?
)
AND tipoCuenta = ?
AND partidaContaPeriodoId = ?
AND flgDelete = ?
ORDER BY LENGTH(numeroCuentaMayorizacion), numeroCuentaMayorizacion
", [0, 0, 0, 0, 'Mayor', $contaPeriodo, 0]);

$nombre_archivo = "Balance de comprobación al " . $dataPeriodo->periodo;

function fmt($n)
{
    return number_format((float) $n, 2, '.', ',');
}

// Organizar jerarquía
$cuentasById = [];
$hijosPorPadre = [];

foreach ($detalle as $cuenta) {
    $cuentasById[$cuenta->cuentaContaId] = $cuenta;
    $hijosPorPadre[$cuenta->cuentaPadreId ?? 0][] = $cuenta;
}

function renderTablaBalance($padreId, $nivel, $cuentasById, $hijosPorPadre, &$procesados)
{
    if (!isset($hijosPorPadre[$padreId]))
        return;

    foreach ($hijosPorPadre[$padreId] as $cuenta) {
        if (in_array($cuenta->cuentaContaId, $procesados))
            continue;
        if ($cuenta->cuentaContaId == $cuenta->cuentaPadreId)
            continue;

        $procesados[] = $cuenta->cuentaContaId;

        $colSubcuenta = $colMayor = $colGrupo = $colRubro = '';
        $saldo = fmt($cuenta->saldoFinal);

        switch ((int) $cuenta->nivelCuenta) {
            case 1:
                $colRubro = $saldo;
                break;
            case 2:
                $colGrupo = $saldo;
                break;
            case 3:
                $colMayor = $saldo;
                break;
            default:
                $colSubcuenta = $saldo;
                break;
        }

        echo "<tr>
            <td>{$cuenta->numeroCuenta}</td>
            <td>{$cuenta->descripcionCuenta}</td>
            <td>{$colSubcuenta}</td>
            <td>{$colMayor}</td>
            <td>{$colGrupo}</td>
            <td>{$colRubro}</td>
        </tr>";

        renderTablaBalance($cuenta->cuentaContaId, $nivel + 1, $cuentasById, $hijosPorPadre, $procesados);
    }
}
?>
<div class="container-fluid px-4">
    <div class="row mb-3">
        <div class="col d-flex justify-content-end">
            <button type="button" id="btnReporteExcel" class="btn btn-success shadow-sm">
                <i class="fas fa-file-excel me-2"></i>Exportar a Excel
            </button>
        </div>
    </div>

    <div class="row mb-4 text-center">
        <div class="col">
            <h4 class="fw-bold">Balance de Comprobación</h4>
            <h5 class="text-muted"><?= $dataPeriodo->periodo ?></h5>
            <small class="text-secondary fst-italic">(En dólares de los Estados Unidos de América)</small>
        </div>
    </div>

    <div class="table-responsive" tabindex="0">
        <table id="tblBalanceComprobacion" class="table table-hover table-sm">
            <thead>
                <tr>
                    <th>Cuenta</th>
                    <th>Descripción</th>
                    <th>Sub-Cuenta</th>
                    <th>Cuenta Mayor</th>
                    <th>Grupo</th>
                    <th>Rubro</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $procesados = [];
                renderTablaBalance(0, 0, $cuentasById, $hijosPorPadre, $procesados);
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function () {
        $(" #btnReporteExcel").click(function (e) {
            $("#tblBalanceComprobacion").table2excel({
                name: `<?php echo $nombre_archivo; ?>`, filename: `<?php echo $nombre_archivo; ?>`
            });
        });
    });
</script>