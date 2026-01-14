<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $fechaInicio = $_POST['fechaInicio'];

?>
<div class="text-center mb-3">
    <h3>
        Censo de empleados con hijos menores o igual a 4 años
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
                <th>Nombre del colaborador</th>
                <th>Nombre del hijo (a)</th>
                <th>Edad</th>
                <th>Fecha de nacimiento</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $fechaInicio = $_POST['fechaInicio'];
                $fechaActual = date("Y-m-d");

                $dataFamiliares = $cloud->rows("
                SELECT 
                    pf.nombreFamiliar AS nombreFamiliar,
                    pf.fechaNacimiento AS fechaNacimiento,
                    ve.nombreCompleto AS nombreCompleto 
                FROM th_personas_familia pf
                JOIN view_expedientes ve ON ve.personaId = pf.personaId
                WHERE pf.flgDelete = ? AND ve.estadoPersona = ? AND ve.estadoExpediente = ? AND pf.catPrsRelacionId IN (6,7) AND pf.fechaNacimiento BETWEEN '$fechaInicio' AND '$fechaActual';                
                ORDER BY ve.apellido1, ve.apellido2, ve.nombre1, ve.nombre2
                ", [0,"Activo","Activo"]);
                $n = 0;
                foreach ($dataFamiliares as $dataFamiliares) {
                    $n++;

                    $calcularEdad = date_diff(date_create($dataFamiliares->fechaNacimiento), date_create(date("Y-m-d")));

                    echo "
                        <tr>
                            <td>$n</td>
                            <td>$dataFamiliares->nombreCompleto</td>
                            <td>$dataFamiliares->nombreFamiliar</td>
                            <td>".($calcularEdad->format('%y') == "0" ? $calcularEdad->format('%m') . " meses" : $calcularEdad->format('%y') . " años y " .$calcularEdad->format('%m') . " meses")."</td>
                            <td>".date("d/m/Y", strtotime($dataFamiliares->fechaNacimiento))."</td>
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
                name: `Censo de empleados con hijos menores o igual a 4 años `,
                filename: `Censo de empleados con hijos menores o igual a 4 años`
               });
        });
    });
</script>