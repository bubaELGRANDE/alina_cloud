<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
	/*
        POST:
        prsExpedienteId
        estadoExpediente
    */
    $dataExpediente = $cloud->row("
        SELECT 
            exp.prsExpedienteId as prsExpedienteId, 
            exp.personaId as personaId,
            exp.sucursalDepartamentoId as sucursalDepartamentoId, 
            exp.tipoContrato as tipoContrato, 
            exp.fechaInicio as fechaInicio, 
            exp.fechaFinalizacion as fechaFinalizacion, 
            exp.estadoExpediente as estadoExpediente,
            per.estadoPersona as estadoPersona,
            CONCAT(
                IFNULL(per.apellido1, '-'),
                ' ',
                IFNULL(per.apellido2, '-'),
                ', ',
                IFNULL(per.nombre1, '-'),
                ' ',
                IFNULL(per.nombre2, '-')
            ) AS nombreCompleto,
            car.cargoPersona as cargoPersona,
            dep.departamentoSucursal as departamentoSucursal,
            dep.sucursalId as sucursalId,
            sal.tipoSalario as tipoSalario,
            sal.salario as salario
        FROM th_expediente_personas exp
        LEFT JOIN th_personas per ON per.personaId = exp.personaId
        LEFT JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
        LEFT JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
        LEFT JOIN th_expediente_salarios sal ON sal.prsExpedienteId = exp.prsExpedienteId
        WHERE exp.prsExpedienteId = ?
    ", [$_POST['prsExpedienteId']]);
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="historial-salario">
<input type="hidden" id="salarioActual" name="salarioActual" value="0.00">
<input type="hidden" id="prsExpedienteId" name="prsExpedienteId" value="<?php echo $_POST['prsExpedienteId']; ?>">
<input type="hidden" id="nombreCompleto" name="nombreCompleto" value="<?php echo $dataExpediente->nombreCompleto; ?>">
<input type="hidden" id="cargoPersona" name="cargoPersona" value="<?php echo $dataExpediente->cargoPersona; ?>">
<div id="divBtnNuevoSalario" class="row mb-4">
    <div id="divSalarioActual" class="col-md-8">
    </div>
    <div class="col-md-4 text-end">
        <?php if ($_POST['estadoExpediente'] == "Activo"){ ?>
        <button type="button" class="btn btn-primary" onclick="showHideForm(1, 'insert');">
            <i class="fas fa-sync-alt"></i>
            Cambiar Salario
        </button>
        <?php } ?>
    </div>
</div>
<div id="divFrmNuevoSalario">
	<div class="row mb-4">
		<div class="col-md-6">
		    <div class="form-outline">
		        <i class="fas fa-dollar-sign trailing"></i>
		        <input type="number" id="salario" name="salario" class="form-control" onchange="limitarDecimales();" required />
		        <label class="form-label" for="salario">Nuevo Salario</label>
		    </div>
		</div>
		<div class="col-md-6">
            <div class="form-outline mb-4 input-daterange">
                <i class="fas fa-calendar-check trailing"></i>
                <input type="text" id="fechaInicioVigencia" class="form-control masked" name="fechaInicioVigencia" data-mask="##-##-####" required />
                <label class="form-label" for="fechaInicioVigencia">Fecha de inicio de vigencia</label>
            </div>
		</div>
	</div>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="form-select-control">
                <select id="tipoRemuneracion" name="tipoRemuneracion" style="width:100%;" required>
                    <option></option>
                    <?php 
                        $dataTipoRemuneracion = $cloud->rows("
                            SELECT
                                salarioTipoRemuneracionId, 
                                tipoRemuneracion 
                            FROM cat_salarios_tipo_remuneracion
                            WHERE flgDelete = ?
                        ", [0]);
                        foreach ($dataTipoRemuneracion as $tipoRemuneracion) {
                            echo '<option value="'.$tipoRemuneracion->salarioTipoRemuneracionId.'">'.$tipoRemuneracion->tipoRemuneracion .'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-outline">
                <i class="fas fa-edit trailing"></i>
                <textarea id="descripcionSalario" class="form-control" name="descripcionSalario" required ></textarea>
                <label class="form-label" for="descripcionSalario">Descripción</label>
            </div>
        </div>
    </div>
    <div class="text-end mb-4">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Guardar
        </button>
        <button type="button" class="btn btn-secondary" onclick="showHideForm(0, 'insert');">
            <i class="fas fa-times-circle"></i> Cancelar
        </button>
    </div>
</div>
<div class="table-responsive">
	<table id="tblHistorialSalarios" class="table table-hover" style="width: 100%;">
	    <thead>
	    	<tr id="filterboxrow-historial-salario">
	    		<th>#</th>
		        <th>Salarios</th>
	    	</tr>
	    </thead>
	    <tbody>
        </tbody>
	</table>
</div>
<script>
    function limitarDecimales() {
        $("#salario").val(parseFloat($("#salario").val()).toFixed(2));
    }

    function getSalarioActual(id) {
        asyncDoDataReturn(
            '<?php echo $_SESSION["currentRoute"]; ?>content/divs/getSalarioActual',
            {
                id: id
            },
            function(data) {
                $("#divSalarioActual").html(`<h5 class="fw-bold">Salario actual: $ ${data}</h5>`);
                $("#salarioActual").val(data);
            }
        ); 
    }

	function showHideForm(flg, tipo) {
		if(flg == 0) { // hide
			$("#divBtnNuevoSalario").show();
			$("#divFrmNuevoSalario").hide();
			$("#typeOperation").val("insert"); // regresar la flg para que al presionar los botones vuelvan a su función inicial "insert"
            $('#frmModal').trigger("reset");
            $("#tipoRemuneracion").val(null).trigger('change');
		} else { // show
			$("#divBtnNuevoSalario").hide();
			$("#divFrmNuevoSalario").show();
			$("#typeOperation").val(tipo); // para definir insert/update y validar en el submitHandler
		}
	}

    $(document).ready(function() {
        $("#salario").attr({
            min: '<?php echo $dataExpediente->salario; ?>'
        });

        $("#tipoRemuneracion").select2({
            placeholder: "Tipo de remuneracion",
            dropdownParent: $('#modal-container')
        });

        Maska.create('#frmModal .masked');

        getSalarioActual(<?php echo $_POST['prsExpedienteId']; ?>);

    	$("#divFrmNuevoSalario").hide();

        $('#fechaInicioVigencia').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            calendarWeeks : false,
            clearBtn: true,
            disableTouchKeyboard: true,
            todayHighlight: true
        });

        $('#fechaInicioVigencia').on('change', function() { 
            $(this).addClass("active"); 
        });

        $("#modalTitle").html("Historial de Salarios - Expediente: <?php echo $dataExpediente->nombreCompleto . ' (' . $dataExpediente->cargoPersona . ')'; ?> ");

        $("#frmModal").validate({
            messages:{
                salario: {
                    min: "Ingrese un salario mayor o igual a <?php echo $dataExpediente->salario?>"
                }
            },
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            // Actualizar la page para que cambie el salario
                            // Usé esta función porque el changePage me cerraba el mensaje de Operación completada
                            mensaje_do_aceptar(
                                "Operación completada:", 
                                'Se ha actualizado el salario del expediente.', 
                                "success", 
                                function() {
                                    <?php 
                                        // Se actualiza el salario desde Planilla, llamar las functions de allá
                                        if(isset($_POST['flgPlanilla'])) {
                                    ?>
                                            $('#tblHistorialSalarios').DataTable().ajax.reload(null, false);
                                            getSalarioActual(<?php echo $_POST['prsExpedienteId']; ?>);
                                            showHideForm(0, 'insert');
                                            cargarCalculoEmpleado($("#prsExpedienteId").val(), $("#nombreCompleto").val());
                                    <?php 
                                        // Se actualiza desde los cruds normal
                                        } else {
                                    ?>
                                            $('#tblExpedientesActivos').DataTable().ajax.reload(null, false);
                                            $('#tblHistorialSalarios').DataTable().ajax.reload(null, false);
                                            getSalarioActual(<?php echo $_POST['prsExpedienteId']; ?>);
                                            showHideForm(0, 'insert');
                                    <?php 
                                        }
                                    ?>
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
            }
        });

        $('#tblHistorialSalarios thead tr#filterboxrow-historial-salarios th').each(function(index) {
            if(index==1) {
                var title = $('#tblHistorialSalarios thead tr#filterboxrow-historial-salarios th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-historial-salarios" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-historial-salarios">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblHistorialSalarios.column($(this).index()).search($(`#input${$(this).index()}-historial-salarios`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).update();
	            });
            } else {
            }
        });

        let tblHistorialSalarios = $('#tblHistorialSalarios').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableExpedienteHistorialSalario",
                "data": { // prsExpedienteId
                    "prsExpedienteId": '<?php echo $_POST['prsExpedienteId']; ?>'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1] },
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>