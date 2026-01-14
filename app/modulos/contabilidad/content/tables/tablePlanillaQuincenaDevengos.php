<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

	$tipoDevengoJS = ($_POST['tipoDevengo'] == "No Gravado" ? "NoGravado" : $_POST['tipoDevengo']);

	if($_POST['estadoQuincena'] == "Pendiente") {
		// Traer los descuentos programados ya que no han sido aplicados
		$dataDevengosEmpleado = $cloud->rows("
			SELECT
				pdev.planillaDevengoProgramadoId AS devengoId, 
				pdev.catPlanillaDevengoId AS catPlanillaDevengoId, 
				cpdev.nombreDevengo AS nombreDevengo,
				cpdev.codigoContable AS codigoContable,
				pdev.descripcionDevengoProgramado AS descripcionDevengo, 
				pdev.montoDevengoProgramado AS montoDevengo
			FROM conta_planilla_programado_devengos pdev
			JOIN cat_planilla_devengos cpdev ON cpdev.catPlanillaDevengoId = pdev.catPlanillaDevengoId
			WHERE pdev.quincenaId = ? AND pdev.prsExpedienteId = ? AND cpdev.tipoDevengo = ? AND pdev.estadoDevengoProgramado = ? AND pdev.flgDelete = ?
		", [$_POST['quincenaId'], $_POST['prsExpedienteId'], $_POST['tipoDevengo'], 'Programado', 0]);
		// Botones de accion libres
		$arrayAcciones = array(
			"Editar" 		=> "",
			"Eliminar" 		=> ""
		);
		$planillaId = 0;
	} else {
		// Traer los descuentos, ya fueron aplicados
		$dataDevengosEmpleado = $cloud->rows("
			SELECT
				planillaDescuentoId, planillaId, tipoDescuento, idDescuento, descripcionDevengo, montoDevengo, planillaDevengoProgramadoId
			FROM conta_planilla_descuentos
		", []);
		if($_POST['estadoQuincena'] == 'Cerrada') {
			// Bloquear los botones de acción
			$arrayAcciones = array(
				"Editar" 		=> "disabled",
				"Eliminar" 		=> "disabled"
			);
		} else {
			// Calculada, todavia se puede editar
		}
		// Asignar el planillaId correspondiente
		$planillaId = "";
	}
?>
<table id="tblDevengos<?php echo $tipoDevengoJS; ?>Empleado" class="table table-hover">
	<thead>
		<tr id="filterboxrow-tblDevengos<?php echo $tipoDevengoJS; ?>Empleado">
			<th>#</th>
			<th>Concepto</th>
			<th>Devengo</th>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			$n = 0; $totalDevengos = 0;
			foreach ($dataDevengosEmpleado as $devengosEmpleado) {
				$totalDevengos += $devengosEmpleado->montoDevengo;
				$n++;

		        $jsonDevengosUpdate = array(
		            "nombreCompleto"        => $_POST['nombreCompleto'],
		            "prsExpedienteId"       => $_POST['prsExpedienteId'],
		            "quincenaId"            => $_POST['quincenaId'],
		            "devengoId" 			=> $devengosEmpleado->devengoId,
		            "tipoDevengo" 			=> $_POST['tipoDevengo']
		        );
		        $jsonDevengosUpdate = htmlspecialchars(json_encode($jsonDevengosUpdate));

		        $jsonDevengosDelete = array(
		        	"typeOperation" 		=> "delete",
		        	"operation" 			=> "planilla-devengos",
		            "nombreCompleto"        => $_POST['nombreCompleto'],
		            "prsExpedienteId"       => $_POST['prsExpedienteId'],
		            "quincenaId"            => $_POST['quincenaId'],
		            "devengoId" 			=> $devengosEmpleado->devengoId,
		            "nombreDevengo" 		=> $devengosEmpleado->nombreDevengo,
		            "tipoDevengo" 			=> $_POST['tipoDevengo']
		        );
		        $jsonDevengosDelete = htmlspecialchars(json_encode($jsonDevengosDelete));

				echo "
					<tr>
						<td>$n</td>
						<td>
							<b><i class='fas fa-list-ol'></i> Código contable: </b> $devengosEmpleado->codigoContable <br>
							<b><i class='fas fa-money-check-alt'></i> Concepto: </b> $devengosEmpleado->nombreDevengo <br>
							<b><i class='fas fa-edit'></i> Descripción: </b> ".($devengosEmpleado->descripcionDevengo == '' ? '-' : $devengosEmpleado->descripcionDevengo)."
						</td>
						<td class='text-end fw-bold'>$ ".number_format($devengosEmpleado->montoDevengo, 2, '.', ',')."</td>
						<td>
				            <button type='button' class='btn btn-primary btn-sm ttip' onclick='modalDevengos($jsonDevengosUpdate);' ".$arrayAcciones['Editar'].">
				                <i class='fas fa-pencil-alt'></i>
				                <span class='ttiptext'>Editar</span>
				            </button>
				            <button type='button' class='btn btn-danger btn-sm ttip' onclick='eliminarDevengo($jsonDevengosDelete);' ".$arrayAcciones['Eliminar'].">
				                <i class='fas fa-trash-alt'></i>
				                <span class='ttiptext'>Eliminar</span>
				            </button>
						</td>
					</tr>
				";
			}
		?>
	</tbody>
	<tfoot>
		<tr class="fw-bold">
			<td colspan="2">Total: Devengos <?php echo $_POST['tipoDevengo'] . "s"; ?></td>
			<td class="text-end">$ <?php echo number_format($totalDevengos, 2, '.', ','); ?></td>
			<td></td>
		</tr>
	</tfoot>
</table>
<script>
	$(document).ready(function() {
        // Tab: Activos
        $('#tblDevengos<?php echo $tipoDevengoJS; ?>Empleado thead tr#filterboxrow-tblDevengos<?php echo $tipoDevengoJS; ?>Empleado th').each(function(index) {
            if(index == 1 || index == 2){
                var title = $('#tblDevengos<?php echo $tipoDevengoJS; ?>Empleado thead tr#filterboxrow-tblDevengos<?php echo $tipoDevengoJS; ?>Empleado th').eq($(this).index()).text();
                    $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}tblDevengos<?php echo $tipoDevengoJS; ?>Empleado" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}tblDevengos<?php echo $tipoDevengoJS; ?>Empleado">Buscar</label></div>${title}`);
                    $(this).on('keyup change', function() {
                        tblDevengosEmpleado.column($(this).index()).search($(`#input${$(this).index()}tblDevengos<?php echo $tipoDevengoJS; ?>Empleado`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            }else{
            }
        });
        
        let tblDevengosEmpleado = $('#tblDevengos<?php echo $tipoDevengoJS; ?>Empleado').DataTable({
            "dom": 'lrtip',
            "rowReorder": true,
            "autoWidth": false,
            "columns": [
                null,
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
	});
</script>