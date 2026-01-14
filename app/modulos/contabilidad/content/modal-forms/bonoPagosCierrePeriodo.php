<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

	$arrayMeses = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
	/*
		POST:
		periodoBonoId
		txtPeriodo
	*/
    $dataPeriodo = $cloud->row("
        SELECT mes, anio, estadoPeriodoBono, fechaPagoBono FROM conta_periodos_bonos
        WHERE periodoBonoId = ? AND flgDelete = ?
    ", [$_POST['periodoBonoId'], 0]);

    $txtPeriodo = $arrayMeses[$dataPeriodo->mes] . " - " . $dataPeriodo->anio;
?>
<input type="hidden" id="extension" name="extension" value="pdf">
<input type="hidden" id="file" name="file" value="bonos-empleados">
<input type="hidden" id="typeOperationModal" name="typeOperation" value="update">
<input type="hidden" id="operationModal" name="operation" value="bonos-pagos-cierre">
<input type="hidden" id="periodoBonoIdModal" name="periodoBonoId" value="<?php echo $_POST['periodoBonoId']; ?>">
<input type="hidden" id="txtPeriodoModal" name="txtPeriodo" value="<?php echo $_POST['txtPeriodo']; ?>">
<div class="row">
	<div class="col-md-3">
		<?php 
			if($dataPeriodo->estadoPeriodoBono == "Pendiente") {
		?>
				<div id="divFormatoReporte" class="mb-4">
					<div class="form-select-control">
					    <select class="form-select" id="formatoReporteModal" name="formatoReporte" style="width:100%;" class="form-control" required>
					        <option></option>
					        <option value="PDF" selected>PDF</option>
					        <option value="Excel">Excel</option>
					    </select>
					</div>
				</div>
				<div id="divFechaCierre" class="mb-4">			
					<div class="form-outline mb-2">
					    <input type="date" id="fechaPagoBonoModal" name="fechaPagoBono" class="form-control" value="<?php echo date('Y-m-d'); ?>" required />
					    <label class="form-label" for="fechaPagoBono">Fecha de cierre/pago de bono</label>
					</div>
					<b>Nota: </b> Al realizar el cierre de periodo, ningún encargado podrá agregar ni eliminar los bonos de los empleados asignados.
				</div>
				<div class="mb-4 mt-4">
					<button type="submit" class="btn btn-primary">
						<i class="fas fa-user-lock"></i> Cerrar periodo
					</button>
				</div>
		<?php 
			} else {
		?>
				<div id="divFormatoReporte" class="mb-4">
					<div class="form-select-control">
					    <select class="form-select" id="formatoReporteModal" name="formatoReporte" style="width:100%;" class="form-control" required>
					        <option></option>
					        <option value="PDF" selected>PDF</option>
					        <option value="Excel">Excel</option>
					    </select>
					</div>
				</div>
				<b><i class="fas fa-user-clock"></i> Estado: </b> <font class="text-success fw-bold">Pagado</font><br>
				<b><i class="fas fa-calendar-day"></i> Fecha de pago: </b> <?php echo date("d/m/Y", strtotime($dataPeriodo->fechaPagoBono)); ?>
		<?php
			}
		?>
	</div>
	<div id="divReporte" class="col-md-9">
	</div>
</div>
<script>
	function reporteBonosEmpleado() {
        asyncData(
            "<?php echo $_SESSION['currentRoute']; ?>reportes", 
            $("#frmModal").serialize(),
            function(data) {
                $("#divReporte").html(data);
            }
        );
	}

	$(document).ready(function() {
		reporteBonosEmpleado();

		$("#formatoReporteModal").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Formato del reporte"
		});

		$("#formatoReporteModal").change(function(e) {
			reporteBonosEmpleado();
		});

        $("#frmModal").validate({
            submitHandler: function(form) {
		        mensaje_confirmacion(
		            `¿Está seguro que desea cerrar el periodo: <?php echo $_POST["txtPeriodo"]; ?>?`, 
		            `Los bonos asignados a cada empleado serán consolidados y enviados a la planilla.`, 
		            `warning`, 
		            function(param) {
		            	button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
		                asyncData(
		                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
		                    $("#frmModal").serialize(),
		                    function(data) {
		                    	button_icons("btnModalAccept", "fas fa-user-lock", "Cerrar periodo", "enabled");
		                        if(data == "success") {
		                            mensaje_do_aceptar(
		                                `Operación completada:`, 
		                                `Cierre de periodo: <?php echo $_POST["txtPeriodo"]; ?> aplicado con éxito.`, 
		                                `success`, 
		                                function() {
		                                    $("#periodoBonoId").trigger("change");
		                                    $('#modal-container').modal("hide");
		                                }
		                            );
		                        } else {
		                            mensaje(
		                                "Aviso:",
		                                data,
		                                "warning"
		                            );
		                        }
		                    }
		                );
		            },
		            `Sí, cerrar periodo`,
		            `Cancelar`
		        );
            }
        });
	});
</script>