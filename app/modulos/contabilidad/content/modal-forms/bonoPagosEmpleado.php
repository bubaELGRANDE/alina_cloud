<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

	/*
		POST:
		bonoPersonaDetalleId
		periodoBonoId
		personaId
		nombreCompleto
		bonoPersonaId
		nombreEncargado
	*/

	$arrayMeses = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

	$dataEmpleado = $cloud->row("
		SELECT prsExpedienteId, cargoPersona FROM view_expedientes
		WHERE personaId = ? AND estadoPersona = ? AND estadoExpediente = ?
	", [$_POST['personaId'], 'Activo', 'Activo']);

    $dataPeriodo = $cloud->row("
        SELECT mes, anio, estadoPeriodoBono, fechaPagoBono FROM conta_periodos_bonos
        WHERE periodoBonoId = ? AND flgDelete = ?
    ", [$_POST['periodoBonoId'], 0]);

    $txtPeriodo = $arrayMeses[$dataPeriodo->mes] . " - " . $dataPeriodo->anio;
?>
<input type="hidden" id="typeOperationModal" name="typeOperation" value="insert">
<input type="hidden" id="operationModal" name="operation" value="bonos-pagos-empleado">
<input type="hidden" id="periodoBonoIdModal" name="periodoBonoId" value="<?php echo $_POST['periodoBonoId']; ?>">
<input type="hidden" id="bonoPersonaDetalleIdModal" name="bonoPersonaDetalleId" value="<?php echo $_POST['bonoPersonaDetalleId']; ?>">
<input type="hidden" id="prsExpedienteIdModal" name="prsExpedienteId" value="<?php echo $dataEmpleado->prsExpedienteId; ?>">
<input type="hidden" id="nombreCompletoModal" name="nombreCompleto" value="<?php echo $_POST['nombreCompleto']; ?>">
<input type="hidden" id="nombreEncargadoModal" name="nombreEncargado" value="<?php echo $_POST['nombreEncargado']; ?>">
<div class="row">
	<div class="col-md-6 mb-4">
		<b><i class="fas fa-user-tie"></i> Empleado:</b> <?php echo $_POST['nombreCompleto']; ?><br>
		<b><i class="fas fa-briefcase"></i> Cargo actual:</b> <?php echo $dataEmpleado->cargoPersona; ?>
	</div>
	<div class="col-md-6 mb-4">
        <?php 
            if($dataPeriodo->estadoPeriodoBono == "Pendiente") {
        ?>
	            <b><i class="fas fa-calendar-day"></i> Periodo: </b> <?php echo $txtPeriodo; ?><br>
	            <b><i class="fas fa-user-clock"></i> Estado: </b> <font class="text-warning fw-bold">Pendiente de pago</font>
        <?php 
            } else {
        ?>
                <b><i class="fas fa-calendar-day"></i> Periodo: </b> <?php echo $txtPeriodo; ?><br>
                <b><i class="fas fa-user-clock"></i> Estado: </b> <font class="text-success fw-bold">Pagado</font><br>
                <b><i class="fas fa-calendar-day"></i> Fecha de pago: </b> <?php echo date("d/m/Y", strtotime($dataPeriodo->fechaPagoBono)); ?>
        <?php 
            }
        ?>
	</div>
</div>
<?php 
	if($dataPeriodo->estadoPeriodoBono == "Pendiente") {
?>
		<div class="row">
			<div class="col-md-4 mb-4">
				<div class="form-select-control">
				    <select class="form-select" id="cuentaBonoIdModal" name="cuentaBonoId" style="width:100%;" class="form-control" required>
				        <option></option>
				        <?php 
				        	$dataCuentas = $cloud->rows("
				        		SELECT
				        			cuentaBonoId, 
				        			numCuentaContable, 
				        			nombreCuentaContable, 
				        			obsCuentaContable
				        		FROM conta_cuentas_bonos
				        		WHERE flgDelete = ?
				        		ORDER BY obsCuentaContable
				        	", [0]);

				        	foreach($dataCuentas as $cuenta) {
				        		echo "<option value='$cuenta->cuentaBonoId' ".($_POST['cuentaBonoId'] == $cuenta->cuentaBonoId ? "selected" : "").">$cuenta->obsCuentaContable</option>";
				        	}
				        ?>
				    </select>
				</div>
			</div>
			<div class="col-md-4 mb-4">
			    <div class="form-outline">
			        <i class="fas fa-dollar-sign trailing"></i>
			        <input type="number" id="montoBonoModal" name="montoBono" class="form-control" onchange="limitarDecimales();" min="0.01" step="0.01" required />
			        <label class="form-label" for="montoBono">Monto del bono</label>
			    </div>
			</div>
			<div class="col-md-4 mb-4">
		        <div class="form-outline">
		            <i class="fas fa-edit trailing"></i>
		            <textarea type="text" id="obsBonoModal" class="form-control" name="obsBono" rows="1"></textarea>
		            <label class="form-label" for="obsBono">Observaciones/Comentarios</label>
		        </div>
			</div>
		</div>
		<div class="text-end">
			<button type="submit" class="btn btn-primary ttip">
				<i class="fas fa-plus-circle"></i> Asignar bono
				<span class="ttiptext">Asignar bono al empleado</span>
			</button>
		</div>
<?php 
	} else {
		// No mostrar el formulario
	}
?>
<div class="table-responsive">
    <table id="tblBonosEmpleado" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-bonos-empleado">
                <th>#</th>
                <th>Encargado</th>
                <th>Cuenta</th>
                <th>Observaciones</th>
                <th>Bono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<script>
    function limitarDecimales() {
        $("#montoBono").val(parseFloat($("#montoBono").val()).toFixed(2));
    }

    $(document).ready(function() {
        $("#cuentaBonoIdModal").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Cuenta/División"
        });

        $('#tblBonosEmpleado thead tr#filterboxrow-bonos-empleado th').each(function(index) {
			if(index == 1 || index == 2 || index == 3 || index == 4) {
				var title = $('#tblBonosEmpleado thead tr#filterboxrow-bonos-empleado th').eq($(this).index()).text();
				$(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-bonos-empleado" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-bonos-empleado">Buscar</label></div>${title}`);
				$(this).on('keyup change', function() {
					tblBonosEmpleado.column($(this).index()).search($(`#input${$(this).index()}-bonos-empleado`).val()).draw();
				});
				document.querySelectorAll('.form-outline').forEach((formOutline) => {
					new mdb.Input(formOutline).init();
				});
			} else {
			}
		});

		let tblBonosEmpleado = $('#tblBonosEmpleado').DataTable({
			"dom": 'lrtip',
			"ajax": {
				"method": "POST",
				"url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableBonosPagosPlanilla",
				"data": { 
					"periodoBonoId": "<?php echo $_POST['periodoBonoId']; ?>",
					"personaId": "<?php echo $_POST['personaId']; ?>",
					"nombreCompleto": "<?php echo $_POST['nombreCompleto']; ?>"
				}
			},
			"autoWidth": false,
			"columns": [
				null,
				null,
				null,
				null,
				null,
				null
			],
			"columnDefs": [
				{ "orderable": false, "targets": [1, 2, 3, 4, 5] }
			],
			"language": {
				"url": "../libraries/packages/js/spanish_dt.json"
			}
		});

		<?php 
			if($dataPeriodo->estadoPeriodoBono == "Pendiente") {
		?>
		        $("#frmModal").validate({
		            submitHandler: function(form) {
		                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
		                asyncData(
		                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
		                    $("#frmModal").serialize(),
		                    function(data) {
		                        button_icons("btnModalAccept", "fas fa-plus-circle", "Agregar", "enabled");
		                        if(data == "success") {
		                            mensaje(
		                                "Operación completada:",
		                                `Se asignó el bono al empleado con éxito.`,
		                                "success"
		                            );
		                            $('#tblBonosDetalle').DataTable().ajax.reload(null, false);
		                            $('#tblBonosEmpleado').DataTable().ajax.reload(null, false);
		                            $("#cuentaBonoIdModal").val('').trigger("change");
		                            $("#frmModal")[0].reset();
		                            //$('#modal-container').modal("hide");
		                        } else {
		                            mensaje(
		                                "Aviso:",
		                                data,
		                                "warning"
		                            );
		                        }
		                    }
		                );
		            }
		        });
		<?php 
			} else {
				// No dibujar la acción para agregar bono, por seguridad
			}
		?>
    });
</script>