<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();
	if($_POST['estadoQuincena'] == "Pendiente") {
		// Traer los descuentos programados ya que no han sido aplicados
		$dataDescuentosEmpleado = $cloud->rows("
			SELECT
				pdesc.planillaDescuentoProgramadoId AS descuentoId, 
				pdesc.idDescuentoProgramado AS catPlanillaDescuentoId, 
				cpdesc.nombreDescuento AS nombreDescuento,
				cpdesc.codigoContable AS codigoContable,
				pdesc.descripcionDescuentoProgramado AS descripcionDescuento, 
				pdesc.montoDescuentoProgramado AS montoDescuento
			FROM conta_planilla_programado_descuentos pdesc
			JOIN cat_planilla_descuentos cpdesc ON cpdesc.catPlanillaDescuentoId = pdesc.idDescuentoProgramado
			WHERE pdesc.quincenaId = ? AND pdesc.prsExpedienteId = ? AND pdesc.tipoDescuentoProgramado = ? AND pdesc.estadoDescuentoProgramado = ? AND pdesc.flgDelete = ?
		", [$_POST['quincenaId'], $_POST['prsExpedienteId'], 'Otros descuentos', 'Programado', 0]);
		// Botones de accion libres
		$arrayAcciones = array(
			"Editar" 		=> "",
			"Eliminar" 		=> ""
		);
		$planillaId = 0;
	} else {
		// Traer los descuentos, ya fueron aplicados
		$dataDescuentosEmpleado = $cloud->rows("
			SELECT
				planillaDescuentoId, planillaId, tipoDescuento, idDescuento, descripcionDescuento, montoDescuento, planillaDescuentoProgramadoId
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
<table id="tblOtrosDescuentosEmpleado" class="table table-hover">
	<thead>
		<tr id="filterboxrow-tblOtrosDescuentosEmpleado">
			<th>#</th>
			<th>Concepto</th>
			<th>Descuento</th>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			$n = 0; $totalDescuentos = 0;
			foreach ($dataDescuentosEmpleado as $descuentosEmpleado) {
				$totalDescuentos += $descuentosEmpleado->montoDescuento;
				$n++;

		        $jsonOtrosDescuentosUpdate = array(
		            "nombreCompleto"        => $_POST['nombreCompleto'],
		            "prsExpedienteId"       => $_POST['prsExpedienteId'],
		            "quincenaId"            => $_POST['quincenaId'],
		            "descuentoId" 			=> $descuentosEmpleado->descuentoId
		        );
		        $jsonOtrosDescuentosUpdate = htmlspecialchars(json_encode($jsonOtrosDescuentosUpdate));

		        $jsonOtrosDescuentosDelete = array(
		        	"typeOperation" 		=> "delete",
		        	"operation" 			=> "planilla-otros-descuentos",
		            "nombreCompleto"        => $_POST['nombreCompleto'],
		            "prsExpedienteId"       => $_POST['prsExpedienteId'],
		            "quincenaId"            => $_POST['quincenaId'],
		            "descuentoId" 			=> $descuentosEmpleado->descuentoId,
		            "nombreDescuento" 		=> $descuentosEmpleado->nombreDescuento
		        );
		        $jsonOtrosDescuentosDelete = htmlspecialchars(json_encode($jsonOtrosDescuentosDelete));

				echo "
					<tr>
						<td>$n</td>
						<td>
							<b><i class='fas fa-list-ol'></i> Código contable: </b> $descuentosEmpleado->codigoContable <br>
							<b><i class='fas fa-money-check-alt'></i> Concepto: </b> $descuentosEmpleado->nombreDescuento <br>
							<b><i class='fas fa-edit'></i> Descripción: </b> ".($descuentosEmpleado->descripcionDescuento == '' ? '-' : $descuentosEmpleado->descripcionDescuento)."
						</td>
						<td class='text-end fw-bold'>$ ".number_format($descuentosEmpleado->montoDescuento, 2, '.', ',')."</td>
						<td>
				            <button type='button' class='btn btn-primary btn-sm ttip' onclick='modalOtrosDescuentos($jsonOtrosDescuentosUpdate);' ".$arrayAcciones['Editar'].">
				                <i class='fas fa-pencil-alt'></i>
				                <span class='ttiptext'>Editar</span>
				            </button>
				            <button type='button' class='btn btn-danger btn-sm ttip' onclick='eliminarDescuento($jsonOtrosDescuentosDelete);' ".$arrayAcciones['Eliminar'].">
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
			<td colspan="2">Total: Otros descuentos</td>
			<td class="text-end">$ <?php echo number_format($totalDescuentos, 2, '.', ','); ?></td>
			<td></td>
		</tr>
	</tfoot>
</table>
<script>
	$(document).ready(function() {
        // Tab: Activos
        $('#tblOtrosDescuentosEmpleado thead tr#filterboxrow-tblOtrosDescuentosEmpleado th').each(function(index) {
            if(index == 1 || index == 2){
                var title = $('#tblOtrosDescuentosEmpleado thead tr#filterboxrow-tblOtrosDescuentosEmpleado th').eq($(this).index()).text();
                    $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}tblOtrosDescuentosEmpleado" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}tblOtrosDescuentosEmpleado">Buscar</label></div>${title}`);
                    $(this).on('keyup change', function() {
                        tblOtrosDescuentosEmpleado.column($(this).index()).search($(`#input${$(this).index()}tblOtrosDescuentosEmpleado`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            }else{
            }
        });
        
        let tblOtrosDescuentosEmpleado = $('#tblOtrosDescuentosEmpleado').DataTable({
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