<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
        POST:
		periodoBonoId
		txtPeriodo
        fechaPagoBono
    */
    $periodoBonoId = $_POST['periodoBonoId'];
    $txtPeriodo = $_POST['txtPeriodo'];
?>
<div class="text-center mb-3">
    <h3>Pago de bonificaciones a empleados</h3>
    <h4>Periodo: <?php echo $txtPeriodo; ?></h4>
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
    			<th>#</th>
    			<th>Empleado</th>
    			<th>Bono total</th>
    		</tr>
    	</thead>
    	<tbody>
    		<?php
			    $dataBonosEmpleado = $cloud->rows("
			        SELECT 
			            bpd.personaId AS personaId,
			            exp.nombreCompleto AS nombreCompleto,
			            exp.nombreCompletoNA AS nombreCompletoNA,
			            SUM(pb.montoBono) AS totalMontoBono
			        FROM conta_planilla_bonos pb
			        JOIN conf_bonos_personas_detalle bpd ON bpd.bonoPersonaDetalleId = pb.bonoPersonaDetalleId
			        JOIN view_expedientes exp ON exp.prsExpedienteId = pb.prsExpedienteId
			        WHERE pb.periodoBonoId = ? AND pb.flgDelete = ?
			        GROUP BY bpd.personaId, exp.nombreCompleto
			        ORDER BY exp.nombre1
			    ", [$periodoBonoId, 0]);
			    // ORDER BY exp.nombre1, exp.nombre2, exp.apellido1, exp.apellido2
			    $n = 0; $totalGeneral = 0;
			    foreach ($dataBonosEmpleado as $bonoEmpleado) {
			    	$n++;
			    	$totalGeneral += $bonoEmpleado->totalMontoBono;

			    	echo "
			    		<tr>
			    			<td>{$n}</td>
			    			<td>".mb_strtoupper($bonoEmpleado->nombreCompletoNA)."</td>
			    			<td class='text-end'>{$_SESSION['monedaSimbolo']} ".number_format($bonoEmpleado->totalMontoBono, 2, '.', ',')."</td>
			    		</tr>
			    	";
			    }
    		?>
		</tbody>
		<tfoot>
			<tr class="fw-bold">
				<td colspan="2">Total general</td>
				<td class="text-end"><?php echo $_SESSION['monedaSimbolo'] . " " . number_format($totalGeneral, 2, ".", ","); ?></td>
			</tr>
		</tfoot>
    </table>
</div>
<script>
	$(document).ready(function() {
        $("#btnReporteExcel").click(function(e) {
            $("#tblReporte").table2excel({
                name: `Pago de bonificaciones a empleados - Periodo: <?php echo $_POST['txtPeriodo']; ?>`,
                filename: `Pago de bonificaciones a empleados - <?php echo $_POST['txtPeriodo']; ?>`
            });
        });
    });
</script>