<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
	// arrayFormData = personaId ^ nombrePersona
	$arrayFormData = explode("^", $_POST["arrayFormData"]);

    $dataEstadoPersona = $cloud->row("
        SELECT estadoPersona FROM th_personas
        WHERE personaId = ?
    ",[$arrayFormData[0]]);

    if($dataEstadoPersona->estadoPersona == "Inactivo") {
        $disabledInactivo = "disabled";
    } else {
        $disabledInactivo = "";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="empleado-cuenta-bancaria">
<input type="hidden" id="personaId" name="personaId" value="<?php echo $arrayFormData[0]; ?>">
<input type="hidden" id="prsCBancariaId" name="prsCBancariaId" value="0">
<div id="divBtnForm" class="row">
	<div class="col-4 offset-8">
		<button type="button" class="btn btn-primary btn-block" onclick="showHideForm(1, 'insert');" <?php echo $disabledInactivo; ?>>
			<i class="fas fa-plus-circle"></i>
			Nueva Cuenta
		</button>
	</div>
</div>
<div id="divFormModal">
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="form-select-control mb-4">
                <select id="nombreOrganizacionId" name="nombreOrganizacionId" style="width: 100%;" required>
                    <option></option>
                    <?php 
                        $dataBancos = $cloud->rows("
                            SELECT
                                nombreOrganizacionId, nombreOrganizacion, abreviaturaOrganizacion
                            FROM cat_nombres_organizaciones
                            WHERE tipoOrganizacion = ? AND flgDelete = ?
                            ORDER BY nombreOrganizacion
                        ", ['Banco', '0']);
                        foreach ($dataBancos as $dataBancos) {
                            echo '<option value="'.$dataBancos->nombreOrganizacionId.'">'.$dataBancos->abreviaturaOrganizacion.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div> 
        <div class="col-6">
            <div class="form-outline mb-4">
                <i class="fas fa-money-check-alt trailing"></i>
                <input type="text" id="numeroCuenta" class="form-control masked" name="numeroCuenta" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required />
                <label class="form-label" for="numeroCuenta">Número de cuenta</label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="form-outline mb-4">
                <i class="fas fa-edit trailing"></i>
                <textarea type="text" id="descripcionCuenta" class="form-control" name="descripcionCuenta"></textarea>
                <label class="form-label" for="descripcionCuenta">Descripción</label>
            </div>            
        </div>
    </div>
    <div class="row">
        <div class="col-12 d-flex justify-content-end mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="flgCuentaPlanilla" name="flgCuentaPlanilla" value="1" />
                <label class="form-check-label" for="flgCuentaPlanilla">
                    Cuenta planillera
                </label>
            </div>
        </div>
    </div>
    <div class="row">
    	<div class="col-3 offset-6">
    		<button type="submit" class="btn btn-primary btn-block" <?php echo $disabledInactivo; ?>>
    			<i class="fas fa-save"></i> Guardar
    		</button>
    	</div>
    	<div class="col-3">
    		<button type="button" class="btn btn-secondary btn-block" onclick="showHideForm(0, 'insert');">
    			<i class="fas fa-times-circle"></i> Cancelar
    		</button>
    	</div>
    </div>
</div>
<div class="table-responsive">
	<table id="tblEmpleadoCuentasBanco" class="table table-hover" style="width: 100%;">
	    <thead>
	    	<tr id="filterboxrow-cuentasbanco">
	    		<th>#</th>
		        <th>Cuenta</th>
		        <th>Acciones</th>
	    	</tr>
	    </thead>
	    <tbody>
        </tbody>
	</table>
</div>
<script>
	function showHideForm(flg, tipo) {
		if(flg == 0) { // hide
			$("#divBtnForm").show();
			$("#divFormModal").hide();
            $('#frmModal').trigger("reset");
            $("#typeOperation").val("insert");
            $('#nombreOrganizacionId').trigger('change');
		} else { // show
			$("#divBtnForm").hide();
			$("#divFormModal").show();
            $("#typeOperation").val(tipo);
		}
	}

	function editarEmpleadoCuenta(tableData) {
        $("#prsCBancariaId").val(tableData);
		asyncDoDataReturn(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getEmpleadoCuentasBancarias", 
            $("#frmModal").serialize(),
            function(data) {
                let result = JSON.parse(data);

                $("#typeOperation").val("update");
                $("#nombreOrganizacionId").val(result.nombreOrganizacionId).trigger('change');
                $("#numeroCuenta").val(result.numeroCuenta);
                $("#descripcionCuenta").val(result.descripcionCuenta);
                if(result.flgCuentaPlanilla == 1) {
                    $("#flgCuentaPlanilla").prop("checked", true);
                } else {
                    $("#flgCuentaPlanilla").prop("checked", false);
                }
				$("#divBtnForm").hide();
				$("#divFormModal").show();

                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            }
        );
	}

	function delEmpleadoCuenta(id) {
        <?php 
            if($dataEstadoPersona->estadoPersona == "Inactivo") {
        ?>
                mensaje(
                    "Aviso:",
                    'No es posible eliminar la información de un empleado inactivo.',
                    "warning"
                );
        <?php 
            } else {
        ?>                
                mensaje_confirmacion(
                    "Aviso:", 
                    "¿Está seguro que quiere eliminar esta cuenta bancaria?", 
                    `warning`, 
                    function(param) {
                        asyncDoDataReturn(
                            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                            {
                                typeOperation: 'delete',
                                operation: 'empleado-cuenta-bancaria',
                                prsCBancariaId: id,
                                nombreEmpleado: '<?php echo $arrayFormData[1]; ?>'
                            },
                            function(data) {
                                if(data == "success") {
                                    mensaje_do_aceptar(
                                        `Operación completada:`, 
                                        `Cuenta bancaria eliminada con éxito.`, 
                                        `success`, 
                                        function() {
                                            showHideForm(0, 'insert'); // para que se oculte
                                            $('#tblEmpleadoCuentasBanco').DataTable().ajax.reload(null, false);
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
                    "Sí, eliminar",
                    `Cancelar`
                );
        <?php 
            }
        ?>
    }
	

    $(document).ready(function() {
    	$("#divFormModal").hide();

        $("#nombreOrganizacionId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Banco'
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Cuenta bancaria guardada con éxito.',
                                "success"
                            );
                            $('#tblEmpleadoCuentasBanco').DataTable().ajax.reload(null, false);
                            showHideForm(0, 'insert'); // para que se oculte
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

        $('#tblEmpleadoCuentasBanco thead tr#filterboxrow-cuentasbanco th').each(function(index) {
            if(index==1) {
                var title = $('#tblEmpleadoCuentasBanco thead tr#filterboxrow-cuentasbanco th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-cuentasbanco" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-cuentasbanco">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpleadoCuentasBanco.column($(this).index()).search($(`#input${$(this).index()}-cuentasbanco`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).update();
	            });
            } else {
            }
        });

        let tblEmpleadoCuentasBanco = $('#tblEmpleadoCuentasBanco').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoCuentasBancarias",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $arrayFormData[0]; ?>'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                {"width": "20%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1,2] },
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>