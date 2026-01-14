<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

	$bonoPersonaId = $_POST['bonoPersonaId'];
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="bonos-encargados-detalle">
<input type="hidden" id="bonoPersonaId" name="bonoPersonaId" value="<?php echo $_POST['bonoPersonaId']; ?>">
<div class="row mt-4">
	<div class="col-9 mb-4">
		<div class="form-select-control">
		    <select class="form-select" id="personaIdDetalle" name="personaIdDetalle[]" style="width:100%;" class="form-control" multiple="multiple" required>
		        <option></option>
		        <?php 
		        	$dataEncargados = $cloud->rows("
		        		SELECT 
		        			exp.personaId AS personaId, 
		        			exp.nombreCompleto AS nombreCompleto
		        		FROM view_expedientes exp
		        		WHERE exp.estadoPersona = ? AND exp.estadoExpediente = ? AND exp.personaId NOT IN (
		        			SELECT bpd.personaId FROM conf_bonos_personas_detalle bpd
		        			WHERE bpd.bonoPersonaId = $bonoPersonaId AND bpd.flgDelete = 0
		        		)
		        		ORDER BY exp.apellido1, exp.apellido2, exp.nombre1, exp.nombre2
		        	", ["Activo", "Activo"]);

		        	foreach($dataEncargados as $encargado) {
		        		echo "<option value='$encargado->personaId'>$encargado->nombreCompleto</option>";
		        	}
		        ?>
		    </select>
		</div>
	</div>
	<div class="col-3 mb-4">
		<button type="submit" class="btn btn-primary btn-block btn-sm ttip">
			<i class="fas fa-plus-circle"></i> Asignar
			<span class="ttiptext">Asignar empleado al encargado</span>
		</button>
	</div>
</div>
<div class="table-responsive">
    <table id="tableEmpleadosAsignados" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-asignados">
                <th>#</th>
                <th>Empleado</th>
                <th>Cargo actual</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        $("#personaIdDetalle").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Empleado(s) asignado(s) al encargado"
        });

        $('#tableEmpleadosAsignados thead tr#filterboxrow-asignados th').each(function(index) {
			if(index == 1 || index == 2) {
				var title = $('#tableEmpleadosAsignados thead tr#filterboxrow-asignados th').eq($(this).index()).text();
				$(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-asignados" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-asignados">Buscar</label></div>${title}`);
				$(this).on('keyup change', function() {
					tableEmpleadosAsignados.column($(this).index()).search($(`#input${$(this).index()}-asignados`).val()).draw();
				});
				document.querySelectorAll('.form-outline').forEach((formOutline) => {
					new mdb.Input(formOutline).init();
				});
			} else {
			}
		});

		let tableEmpleadosAsignados = $('#tableEmpleadosAsignados').DataTable({
			"dom": 'lrtip',
			"ajax": {
				"method": "POST",
				"url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableBonosParametrizacionPersonas",
				"data": { 
					"tipoPersona": 'Detalle',
					"bonoPersonaId": "<?php echo $_POST['bonoPersonaId']; ?>",
					"nombreEncargado": "<?php echo $_POST['nombreCompleto']; ?>"
				}
			},
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

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-plus-circle", "Asignar", "enabled");
                        if(data.respuesta == "success") {
                            mensaje(
                                "Operación completada:",
                                `Se asignaron ${data.registros} empleados con éxito.`,
                                "success"
                            );
                            $('#tblBonosPersonas').DataTable().ajax.reload(null, false);
                            $('#tableEmpleadosAsignados').DataTable().ajax.reload(null, false);
                            $("#personaIdDetalle").val([]).trigger("change");
                            divBtnBonosParametrizacion();
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
    });
</script>