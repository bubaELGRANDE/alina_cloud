<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<div class="text-center mb-3">
    <h3>Domicilios de empleados: Según DUI y actual <br> <?php echo date("d/m/Y"); ?></h3>
</div>
<div class="row mb-4">
    <div class="col-9">
        <button type="button" id="btnReporteExcel" class="btn btn-success ttip">
            <i class="fas fa-file-excel"></i> Excel
            <span class="ttiptext">Descargar reporte en Excel</span>
        </button>
    </div>
</div>
<div class="table-responsive" tabindex="0">
    <table id="tblReporte" class="table table-hover table-sm">
        <thead class="fw-bold">
            <tr>
                <td rowspan="2">#</td>
                <td rowspan="2">Empleado</td>
                <td colspan="3">Domicilio según DUI</td>
                <td colspan="3">Domicilio actual</td>
            </tr>
            <tr>
                <td>Departamento</td>
                <td>Municipio</td>
                <td>Domicilio</td>
                <td>Departamento</td>
                <td>Municipio</td>
                <td>Domicilio</td>
            </tr>
        </thead>
        <tbody>
            <?php 
                // La vista ya lleva flgDelete
                $dataDomicilios = $cloud->rows("
                    SELECT
                        nombreCompleto,
                        departamentoPaisDUI,
                        municipioPaisDUI,
                        zonaResidenciaDUI,
                        departamentoPaisActual,
                        municipioPaisActual,
                        zonaResidenciaActual
                    FROM view_expedientes
                    WHERE estadoPersona = ? AND estadoExpediente = ?
                    ORDER BY apellido1, apellido2, nombre1, nombre2
                ", ['Activo', 'Activo']);
                $n = 0;
                foreach ($dataDomicilios as $domicilio) {
                    $n++;
                    echo "
                        <tr>
                            <td>$n</td>
                            <td>$domicilio->nombreCompleto</td>
                            <td>$domicilio->departamentoPaisDUI</td>
                            <td>$domicilio->municipioPaisDUI</td>
                            <td>$domicilio->zonaResidenciaDUI</td>
                            <td>$domicilio->departamentoPaisActual</td>
                            <td>$domicilio->municipioPaisActual</td>
                            <td>$domicilio->zonaResidenciaActual</td>
                        </tr>
                    ";
                }
            ?>
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        $("#btnReporteExcel").click(function(e) {
            $("#tblReporte").table2excel({
                name: `Domicilios de empleado - <?php echo date("d-m-Y"); ?>`,
                filename: `Domicilios de empleado - <?php echo date("d-m-Y"); ?>`
            });
        });
    });
</script>