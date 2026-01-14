<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
$contaPeriodo = $_POST['fechaInicio'] ?? 0;

$dataPeriodo = $cloud->row("SELECT CONCAT(mesNombre, ' ', anio) AS periodo FROM conta_partidas_contables_periodos WHERE partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);

$dataEncabezados = $cloud->rows('
SELECT
    p.partidaContableId,
    p.tipoPartidaId,
    p.numPartida,
    p.descripcionPartida,
    p.fechaPartida,
    t.descripcionPartida AS tipoPartidaNombre
FROM
    conta_partidas_contables p
    LEFT JOIN cat_tipo_partida_contable t ON t.tipoPartidaId = p.tipoPartidaId
    WHERE p.partidaContaPeriodoId = ? AND p.flgDelete = ?
', [$contaPeriodo, 0]);

$nombre_archivo = "LibroDiario" . $dataPeriodo->periodo;

function fmt($n)
{
    return number_format((float) $n, 2, '.', ',');
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
            <h4 class="fw-bold">Libro diario general</h4>
            <h5 class="text-muted"><?= $dataPeriodo->periodo ?></h5>
            <small class="text-secondary fst-italic">(En dólares de los Estados Unidos de América)</small>
        </div>
    </div>

    <?php
    foreach ($dataEncabezados as $encabezado) {
        echo '
        <table class="tablePrint">
            <tbody>
                <tr>
                    <td><strong>N° Partida:</strong>' . $encabezado->numPartida . '</td>
                    <td><strong>Fecha:</strong> ' . $encabezado->fechaPartida . '</td>
                </tr>
                <tr>
                    <td><strong>Tipo:</strong>' . $encabezado->tipoPartidaNombre . '</td>
                    <td><strong>Concepto:</strong>' . $encabezado->descripcionPartida . '</td>
                </tr>
            </tbody>
        </table>
        ';

        $dataCuerpo = $cloud->rows('
            SELECT  
            cu.numeroCuenta,
            cu.descripcionCuenta,
            MIN(cd.descripcionPartidaDetalle) AS descripcionPartidaDetalle,
            SUM(cd.cargos) AS totalCargos, 
            SUM(cd.abonos) AS totalAbonos
            FROM conta_partidas_contables_detalle cd
            JOIN conta_cuentas_contables cu ON cu.cuentaContaId = cd.cuentaContaId
            WHERE cd.partidaContableId = ? AND cd.flgDelete = ?
            GROUP BY cd.cuentaContaId, cu.numeroCuenta, cu.descripcionCuenta, cu.nivelCuenta
            ', [$encabezado->partidaContableId, 0]);

        echo '<table class="tablePrint table-responsive" tabindex="0">
        <thead>
            <tr>
                <th>N° Cuenta</th>
                <th>Cuenta</th>
                <th>Concepto Movimiento</th>
                <th>Cargo</th>
                <th>Abono</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($dataCuerpo as $cuerpo) {
            echo '
            <tr>
                <td>' . $cuerpo->numeroCuenta . '&nbsp;</td>
                <td>' . $cuerpo->descripcionCuenta . '</td>
                <td>' . $cuerpo->descripcionPartidaDetalle . '</td>
                <td>' . fmt($cuerpo->totalCargos) . '</td>
                <td>' . fmt($cuerpo->totalAbonos) . '</td>
            </tr>
        ';
        }

        echo '</tbody></table><br>';
    }
    ?>
</div>

<script>
    $(document).ready(function () {
        $("#btnReporteExcel").click(function (e) {
            // Clonar todas las tablas con la clase .tablePrint y concatenarlas
            let contenido = '';
            $(".tablePrint").each(function () {
                contenido += $(this)[0].outerHTML + '<br>';
            });

            // Crear un contenedor temporal
            let $divTemporal = $('<div id="contenedorExcel" style="display:none;"></div>').appendTo('body');
            $divTemporal.html(contenido);

            // Exportar ese contenedor como Excel
            $divTemporal.table2excel({
                name: "<?php echo $nombre_archivo; ?>",
                filename: "<?php echo $nombre_archivo; ?>.xls"
            });

            // Limpiar
            $divTemporal.remove();
        });
    });

</script>