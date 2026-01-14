<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $arrayMeses = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

    $filtroSucursalMultiple = $_POST['filtroSucursalMultiple'];

    if($filtroSucursalMultiple == "Todos") {
        $whereSucursal = "";
    } else {
        $arraySucursales = implode(",", $_POST["selectSucursalMultiple"]);
        $whereSucursal = "AND sucursalId IN ($arraySucursales)";
    }
?>
<div class="text-center mb-3">
    <h3>
        Cumpleaños por mes: Personal<br>
        <?php echo ($filtroSucursalMultiple == "Todos" ? "Todas las sucursales" : "Sucursales específicas") . " - " . $arrayMeses[$_POST['selectMesAnio']] . " - " . date("Y"); ?>
    </h3>
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
                <th>#</th>
                <th>Empleado</th>
                <th>Sucursal</th>
                <th>Cargo</th>
                <th>Fecha de nacimiento</th>
                <th>Edad actual</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                // La vista ya lleva flgDelete
                $dataLabores = $cloud->rows("
                    SELECT
                        nombreCompleto, sucursal, cargoPersona, fechaNacimiento,
                        DATE_FORMAT(fechaNacimiento, '%d/%m/%Y') AS fechaNacimientoFormat
                    FROM view_expedientes
                    WHERE estadoPersona = ? AND estadoExpediente = ? AND MONTH(fechaNacimiento) = ?
                    $whereSucursal
                    ORDER BY DAY(fechaNacimiento)
                ", ['Activo', 'Activo', $_POST['selectMesAnio']]);
                $n = 0;
                foreach ($dataLabores as $labores) {
                    $n++;

                    $calcularEdad = date_diff(date_create($labores->fechaNacimiento), date_create(date("Y-m-d")));

                    echo "
                        <tr>
                            <td>$n</td>
                            <td>$labores->nombreCompleto</td>
                            <td>$labores->sucursal</td>
                            <td>$labores->cargoPersona</td>
                            <td>$labores->fechaNacimientoFormat</td>
                            <td>".$calcularEdad->format('%y')." años</td>
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
                name: `Cumpleaños por mes - Personal - <?php echo $arrayMeses[$_POST['selectMesAnio']] . " - " . date("Y"); ?>`,
                filename: `Cumpleaños por mes - Personal - <?php echo $arrayMeses[$_POST['selectMesAnio']] . " - " . date("Y"); ?>`
            });
        });
    });
</script>
<?php 
    function diferenciaFechasYMD($fechaPublicacion,$fechaActual) {
        $diferencia = ($fechaPublicacion - $fechaActual);

        $anios = floor($diferencia / (365*60*60*24));
        $meses = floor(($diferencia - $anios * 365*60*60*24)/ (30*60*60*24) );
        $dias  = floor(($diferencia - $anios * 365*60*60*24 - $meses *30*60*60*24) / (60*60*24)+1);

        $txtAnio = ($anios == 0 ? "" : ($anios == 1 ? "Un año, " : $anios . " años, "));
        $txtMeses = ($meses == 0 ? "" : ($meses == 1 ? "Un mes, " : $meses . " meses, "));
        $txtDias = ($dias == 1 ? "Un día" : $dias . " días");

        $antiguiedad = $txtAnio . $txtMeses . $txtDias;

        if($fechaActual == "") {
            // Para los que no se les creó expediente
            return "-";
        } else {
            if($anios >= 0) { // La fecha de publicacion era mayor que la actual (contratación a futuro)
                return $antiguiedad;
            } else {
                return "-";
            }
        }
    }
?>