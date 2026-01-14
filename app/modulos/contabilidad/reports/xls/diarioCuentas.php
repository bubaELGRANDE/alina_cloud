<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
$periodoId = $_POST['fechaInicio'] ?? 0;
$numeroCuenta = $_POST['numCuenta'] ?? 0;
function fmt($n)
{
    return number_format((float)$n, 2, '.', ',');
}

if($numeroCuenta == 10){
    $textNum = '';
}
else{
    $textNum = 'AND numeroCuenta LIKE "'.$numeroCuenta.'%"';
}

$data = $cloud->rows("
    SELECT 
        pc.numPartida,
        t.descripcionPartida AS tipoPartida,
        pc.fechaPartida,
        CONCAT(cp.mesNombre, ' ', cp.anio) AS periodo,
        cu.numeroCuenta,
        cu.descripcionCuenta,
        cd.descripcionPartidaDetalle,
        cd.cargos,
        cd.abonos,
        cu.categoriaCuenta,
        cd.tipoDTEId,
        cd.documentoId,
        cd.numDocumento
    FROM conta_partidas_contables_detalle cd
        LEFT JOIN conta_centros_costo cc ON cd.centroCostoId = cc.centroCostoId
        LEFT JOIN conta_partidas_contables pc ON cd.partidaContableId = pc.partidaContableId
        LEFT JOIN conta_partidas_contables_periodos cp ON cd.partidaContaPeriodoId = cp.partidaContaPeriodoId
        LEFT JOIN conta_cuentas_contables cu ON cd.cuentaContaId = cu.cuentaContaId
        LEFT JOIN conta_subcentros_costo su ON cd.subCentroCostoId = su.subCentroCostoId
        LEFT JOIN cat_tipo_partida_contable t ON t.tipoPartidaId = pc.tipoPartidaId
    WHERE cd.partidaContaPeriodoId = ?
      AND cd.flgDelete = 0
      AND pc.flgDelete = 0 $textNum
    ORDER BY pc.fechaPartida, cd.partidaContableDetalleId ASC
", [$periodoId]);
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
            <h4 class="fw-bold">Reporte Diario de Movimientos de Cuentas</h4>
            <h6 class="text-muted"><?= $data[0]->periodo ?? '' ?></h6>
        </div>
    </div>
    <div class="table-responsive">
        <table id="tablaDiario" class="table table-hover table-sm">
            <?php
            $diaActual = "";
            $totalDiaCargo = 0;
            $totalDiaAbono = 0;
            $granCargo = 0;
            $granAbono = 0;
            foreach ($data as $row) {
                if ($diaActual != $row->fechaPartida) {
                    if ($diaActual != "") {
                        echo "
                        <tr class='table-secondary fw-bold'>
                            <td colspan='6' class='text-end'>TOTAL DEL DÍA</td>
                            <td>" . fmt($totalDiaCargo) . "</td>
                            <td>" . fmt($totalDiaAbono) . "</td>
                        </tr>";
                    }
                    $totalDiaCargo = 0;
                    $totalDiaAbono = 0;
                    echo "
                    <thead>
                        <tr class='table-primary'>
                            <th colspan='8'>Fecha: " . date("d/m/Y", strtotime($row->fechaPartida)) . "</th>
                        </tr>
                        <tr class='table-light'>
                            <th># Partida</th>
                            <th>Tipo</th>
                            <th>Cuenta</th>
                            <th>Descripción</th>
                            <th>Detalle</th>
                            <th>Documento</th>
                            <th>Cargos</th>
                            <th>Abonos</th>
                        </tr>
                    </thead>
                    <tbody>
                    ";
                    $diaActual = $row->fechaPartida;
                }
                echo "
                <tr>
                    <td>{$row->numPartida}</td>
                    <td>{$row->tipoPartida}</td>
                    <td>{$row->numeroCuenta}</td>
                    <td>{$row->descripcionCuenta}</td>
                    <td>{$row->descripcionPartidaDetalle}</td>
                    <td>{$row->numDocumento}</td>
                    <td>" . fmt($row->cargos) . "</td>
                    <td>" . fmt($row->abonos) . "</td>
                </tr>";
                $totalDiaCargo += $row->cargos;
                $totalDiaAbono += $row->abonos;
                $granCargo += $row->cargos;
                $granAbono += $row->abonos;
            }
            /*echo "
            <tr class='table-secondary fw-bold'>
                <td colspan='6' class='text-end'>TOTAL DEL DÍA</td>
                <td>" . fmt($totalDiaCargo) . "</td>
                <td>" . fmt($totalDiaAbono) . "</td>
            </tr>";*/
            ?>
            <tfoot>
                <tr class="table-dark fw-bold">
                    <td colspan="6" class="text-end">GRAN TOTAL GENERAL</td>
                    <td><?= fmt($granCargo) ?></td>
                    <td><?= fmt($granAbono) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#btnExportarExcel").click(function() {
            $("#tablaDiario").table2excel({
                name: "Reporte Diario"
            });
        });
    });
</script>