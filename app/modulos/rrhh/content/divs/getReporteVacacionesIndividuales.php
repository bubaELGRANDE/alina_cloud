<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
        POST:
        extension
        file
        filtroEmpleado
        anioDesde
        anioHasta
    */
    if($_POST["filtroEmpleado"] == "Especificos") {
       $arrayExpedientes = implode(",", $_POST["selectEmpleadosEspecificos"]);
       $whereEmpleados = "AND prsExpedienteId IN ($arrayExpedientes)";
    } else {
        $whereEmpleados = "";
    }
?>
<div class="text-center mb-3">
    <h3>Programa de vacaciones individuales<br>
        <small><?php echo "De $_POST[anioDesde] al $_POST[anioHasta]"; ?></small>
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
        <thead>
            <tr>
                <td>#</td>
                <td>Empleado</td>
                <td>Sucursal</td>
                <td>Plan de vacaciones</td>
                <?php
                    for ($i=$_POST["anioDesde"]; $i <= $_POST["anioHasta"]; $i++) { 
                        $arrayTotales[] = 0;
                        echo "<td>$i</td>";
                    }
                    $arrayTotales[] = 0;
                ?>
                <td>Total</td>
            </tr>
        </thead>
        <tbody>
            <?php 
                $dataReporteVacaciones= $cloud->rows("
                    SELECT prsExpedienteId,
                        estadoVacacion,
                        personaId, 
                        nombreCompleto,
                        cargoPersona,
                        sucursal,
                        departamentoSucursal
                    FROM view_expedientes 
                    WHERE estadoPersona = ? AND estadoExpediente = ? AND tipoVacacion = ? AND estadoVacacion = ? $whereEmpleados
                    ORDER BY apellido1, apellido2, nombre1, nombre2
                ",['Activo','Activo','Individuales','Activo']);
                $x = 0;
                foreach($dataReporteVacaciones as $dataReporteVacaciones){
                $x += 1;
            ?>
            <tr>
                <td><?php echo "$x" ?></td>
                <td><?php echo "$dataReporteVacaciones->nombreCompleto";?></td>
                <td><?php echo "$dataReporteVacaciones->sucursal";?></td>
                <td>15 días</td>
                <?php
                 $dias = 0;
                    $n = 0;
                    for ($i=$_POST["anioDesde"]; $i <= $_POST["anioHasta"]; $i++) { 
                        // row porque solo será un registro de un año en especifico
                        // SELECT a ctrl_personas_vacacion WHERE personaId = ? AND anio = ? AND flgDelete = ?, 
                        // [variable for de arriba, $i, 0]
                        
                        $dataDiasRestantesReporte = $cloud->row("
                                SELECT  anio,
                                diasRestantesVacacion
                                FROM ctrl_persona_vacaciones
                                WHERE personaId = ? AND anio = ? AND flgDelete = ?
                            ",[$dataReporteVacaciones->personaId, $i, 0]);
                        $diasRestantes = 0;
                        if(empty($dataDiasRestantesReporte)){
                            $diasRestantes = 0;
                        }else{
                            $diasRestantes = $dataDiasRestantesReporte->diasRestantesVacacion;
                            $dias += $dataDiasRestantesReporte->diasRestantesVacacion;                       
                        }
                        echo "<td>$diasRestantes</td>";
                        $arrayTotales[$n] += $diasRestantes;
                        $n++;
                    }
                ?>
                <td class="text-end">
                    <?php 
                        $arrayTotales[$n] += $dias;
                        echo $dias;
                    ?>
                </td>
            </tr>
            <?php  }?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Total</td>
                    <?php
                        $n = 0;
                        for ($i=$_POST["anioDesde"]; $i <= $_POST["anioHasta"]; $i++) { 
                                    echo "<td>".$arrayTotales[$n]."</td>";
                            $n++;
                        }
                    ?>
                <td class="text-end"><?php echo $arrayTotales[$n]; ?></td>
            </tr>
        </tfoot>
    </table>
</div>
<script>
    $(document).ready(function() {
        $("#btnReporteExcel").click(function(e) {
            $("#tblReporte").table2excel({
                name: `Programa de vacaciones individuales - Del <?php echo $_POST['anioDesde'] . ' al ' . $_POST['anioHasta']; ?>`,
                filename: `Programa de vacaciones individuales - Del <?php echo $_POST['anioDesde'] . ' al ' . $_POST['anioHasta']; ?>`
            });
        });
    });
</script>