<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<div class="text-center mb-3">
    <h3>
        Empleados con sus beneficiarios<br>
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
                <th>Beneficiario</th>
                <th>Porcentaje del beneficiario</th>
                <th>Parentesco</th>
            </tr>
        </thead>
        <tbody>
            <?php 

                $dataEmpleados = $cloud->rows("
                    SELECT
                          personaId,
                          CONCAT(nombre1, ' ', nombre2, ' ', apellido1, ' ', apellido2) AS nombreCompleto
                    FROM th_personas
                    WHERE estadoPersona = ? AND flgDelete = ? AND prsTipoId = '1'
                ", ['Activo', 0]);
                $n = 0;
                foreach ($dataEmpleados as $dataEmpleados) {

                    $dataSucursal = $cloud->row("
                        SELECT
                            sc.sucursal AS sucursal
                        FROM th_expediente_personas exp
                        JOIN cat_sucursales_departamentos sd ON sd.sucursalDepartamentoId = exp.sucursalDepartamentoId
                        JOIN cat_sucursales sc ON sc.sucursalId = sd.sucursalId
                        WHERE exp.personaId = ? AND exp.estadoExpediente = ? AND exp.flgDelete = '0'
                    ", [$dataEmpleados->personaId,"Activo"]);

                    if ($dataSucursal) {
                        $sucursal = $dataSucursal->sucursal;
                    } else {
                        $sucursal = 'Sin sucursal';
                    }
                    
                    $n++;

                    echo "  <tr>
                            <td>$n</td>
                            <td>$dataEmpleados->nombreCompleto</td>
                            <td>$sucursal</td>
                        ";

                    $dataBeneficiario = $cloud->rows("
                        SELECT
                            tpf.nombreFamiliar AS nombreFamiliar,
                            tpf.apellidoFamiliar AS apellidoFamiliar,
                            tpf.porcentajeBeneficiario AS porcentajeBeneficiario,
                            cpr.tipoPrsRelacion AS tipoPrsRelacion
                        FROM th_personas_familia tpf
                        JOIN cat_personas_relacion cpr ON cpr.catPrsRelacionId = tpf.catPrsRelacionId
                        WHERE tpf.personaId = ? AND tpf.flgBeneficiario = ? AND tpf.flgDelete = ? AND cpr.flgDelete = ?
                    ", [$dataEmpleados->personaId,'Sí', 0, 0]);

                    echo "<td";
                    if(count($dataBeneficiario) == 0){
                        echo " style='background-color:red; color:white;'>Sin beneficiario";
                        echo "</td><td>-</td>";
                    } else {
                        echo ">";
                        foreach($dataBeneficiario as $beneficiario){
                             $nombre = ucwords(strtolower($beneficiario->nombreFamiliar));
                            echo $nombre .$beneficiario->apellidoFamiliar."<br>";
                        }
                        echo "</td>";

                        echo "<td>";
                            foreach($dataBeneficiario as $beneficiario){
                                echo $beneficiario->porcentajeBeneficiario . "%" . "<br>";
                            }
                        echo "</td>";
                        // PARENTESCO
                        echo "<td>";
                        foreach($dataBeneficiario as $beneficiario){
                            echo $beneficiario->tipoPrsRelacion . "<br>";
                        }
                        echo "</td>";
                    }

                    echo "</tr>";
                }
            ?>
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        $("#btnReporteExcel").click(function(e) {
            $("#tblReporte").table2excel({
                name: `Empleados con sus beneficiarios`,
                filename: `Empleados con sus beneficiarios`,
                preserveColors: true
            });
        });
    });
</script>