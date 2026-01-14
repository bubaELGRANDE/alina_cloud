<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();
	if($_POST['estadoQuincena'] == "Pendiente") {
		if($_POST['clasifGastoSalarioId'] == "Todos") {
			$whereClasificacion = "";
		} else {
			$whereClasificacion = "AND clasifGastoSalarioId = $_POST[clasifGastoSalarioId]";
		}
		// Traer la nomina de empleados actual
		$dataEmpleadosPlanilla = $cloud->rows("
			SELECT
				prsExpedienteId,
				personaId,
				codEmpleado,
				nombreCompleto
			FROM view_expedientes
			WHERE estadoPersona = ? AND estadoExpediente = ? $whereClasificacion
			ORDER BY apellido1, apellido2, nombre1, nombre2, clasifGastoSalarioId
		", ['Activo', 'Activo']);
	} else {
		// Traer la nomina de empleados que se calculó en ese momento (empezar desde conta_planilla)
		$dataEmpleadosPlanilla = '';
	}
?>
<table id="tblPlanillaEmpleados" class="table table-hover">
	<thead>
		<tr id="filterboxrow">
			<th>#</th>
			<th>Cód.</th>
			<th>Empleado</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			$n = 0;
			foreach ($dataEmpleadosPlanilla as $empleadoPlanilla) {
				$n++;
				echo "
					<tr class='filasExpedientes' id='filaExp$empleadoPlanilla->prsExpedienteId' onclick='cargarPlanillaEmpleado(`$empleadoPlanilla->prsExpedienteId`);' style='cursor: pointer;'>
						<td>$n</td>
						<td>$empleadoPlanilla->codEmpleado</td>
						<td>$empleadoPlanilla->nombreCompleto</td>
					</tr>
				";
			}
		?>
	</tbody>
</table>
<script>
	$(document).ready(function() {
        let tblPlanillaEmpleados = $('#tblPlanillaEmpleados').DataTable({
            "dom": 'lpf',
            "rowReorder": true,
            "autoWidth": false,
            "columns": [
            	{"width": "5%"},
                {"width": "10%"},
                null
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            },
            "pagingType": 'simple'
        });

	    // Evento draw.dt para restaurar la clase de selección después de cada redibujado de la tabla
	    tblPlanillaEmpleados.on('draw.dt', function() {
	    	$(`#filaExp${$(`#prsExpedienteActual`).val()}`).addClass('bg-primary text-white');
	    });

		// Evento page.dt para quitar las clases de color cuando cambia de página
		tblPlanillaEmpleados.on('page.dt', function() {
			tblPlanillaEmpleados.rows().nodes().to$().removeClass('bg-primary text-white');
			// Se llama draw.dt automáticamente, por eso es que se logra ese efecto
		});
	});
</script>