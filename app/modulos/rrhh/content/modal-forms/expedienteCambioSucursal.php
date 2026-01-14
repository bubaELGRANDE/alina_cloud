<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="expediente-cambio-sucursal">
<input type="hidden" id="prsExpedienteId" name="prsExpedienteId" value="<?php echo $_POST['prsExpedienteId']; ?>">
<input type="hidden" id="sucursalDepartamentoIdAnterior" name="sucursalDepartamentoIdAnterior" value="<?php echo $_POST['sucursalDepartamentoId']; ?>">
<input type="hidden" id="nombreCompleto" name="nombreCompleto" value="<?php echo $_POST['nombreCompleto']; ?>">
<div class="row">
    <div class="col-8 fs-6 mb-4">
        <div id="divInfoActual" class="row">
            <div class="col-6">
                <b><i class="fas fa-building"></i> Sucursal actual: </b> <?php echo $_POST['sucursal']; ?>
            </div>
            <div class="col-6">
                <b><i class="far fa-building"></i> Departamento actual: </b> <?php echo $_POST['departamentoSucursal']; ?>
            </div>
        </div>
    </div>
    <div id="divBtnForm" class="col-4 mb-4 text-end">
    	<button type="button" class="btn btn-primary ttip" onclick="showHideForm(1, 'update');">
    		<i class="fas fa-sync-alt"></i>
    		Sucursal/departamento
            <span class="ttiptext">Cambiar de sucursal/departamento</span>
    	</button>
    </div>
</div>
<div id="divFormModal">
    <div class="row">
        <div class="col-md-6 form-select-control mb-4">
            <select id="sucursalCambio" name="sucursalCambio" style="width:100%;" required>
                <option></option>
                <?php 
                    $dataSucursales = $cloud->rows("
                        SELECT
                            sucursalId, 
                            sucursal 
                        FROM cat_sucursales
                        WHERE flgDelete = '0'
                    ");
                    foreach ($dataSucursales as $dataSucursales) {
                        echo '<option value="'.$dataSucursales->sucursalId.'">'.$dataSucursales->sucursal .'</option>';
                    }
                ?>
            </select>
        </div>
        <div class="col-md-6 form-select-control mb-4">
            <select id="departamentoCambio" name="departamentoCambio" style="width:100%;" required>
                <option></option>
            </select>
        </div>
    </div>
    <div class="row">
    	<div class="col-md-3 offset-md-6">
    		<button type="submit" class="btn btn-primary btn-block">
    			<i class="fas fa-save"></i> Guardar
    		</button>
    	</div>
    	<div class="col-md-3">
    		<button type="button" class="btn btn-secondary btn-block" onclick="showHideForm(0, 'update');">
    			<i class="fas fa-times-circle"></i> Cancelar
    		</button>
    	</div>
    </div>
</div>
<div class="table-responsive">
	<table id="tblExpedienteSucursales" class="table table-hover" style="width: 100%;">
	    <thead>
	    	<tr id="filterboxrow-cuentasbanco">
	    		<th>#</th>
		        <th>Sucursal/Departamento anterior</th>
                <th>Sucursal/Departamento cambio</th>
                <th>Fecha y hora del cambio</th>
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
            $("#sucursalCambio").val(null).trigger("change");
            $('#frmModal')[0].reset();
            // Aqui es insert pero tiene update porque esta modal es solo para actualizar
            $("#typeOperation").val("update");
		} else { // show
			$("#divBtnForm").hide();
			$("#divFormModal").show();
            $("#typeOperation").val(tipo);
		}
	}

    $(document).ready(function() {
    	$("#divFormModal").hide();

        $("#sucursalCambio").select2({
            placeholder: "Sucursal nueva",
            dropdownParent: $('#modal-container')
        });

        $("#departamentoCambio").select2({
            placeholder: "Departamento nuevo",
            dropdownParent: $('#modal-container')
        });

        $("#sucursalCambio").change(function() {
            asyncSelect(
                "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectSucursalDepartamentos",
                {sucursal: $(this).val()},
                `departamentoCambio`,
                function(){}
            );
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
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Sucursal y departamento actualizado con éxito.`,
                                "success",
                                function() {
                                    $("#divInfoActual").html(`
                                        <div class="col-6">
                                            <b><i class="fas fa-building"></i> Sucursal actual: </b> ${$("#sucursalCambio :selected").text()}
                                        </div>
                                        <div class="col-6">
                                            <b><i class="far fa-building"></i> Departamento actual: </b> ${$("#departamentoCambio :selected").text()}
                                        </div>
                                    `);
                                    $("#tblExpedienteSucursales").DataTable().ajax.reload(null, false);
                                    changePage(`<?php echo $_SESSION['currentRoute']; ?>`, `expediente-empleado`, `personaId=<?php echo $_POST['personaId']; ?>&nombreCompleto=<?php echo $_POST['nombreCompleto']; ?>&estadoExpediente=<?php echo $_POST['estadoExpediente']; ?>`);
                                    showHideForm(0, 'update'); // para que se oculte
                                }
                            );
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

        $('#tblExpedienteSucursales thead tr#filterboxrow-sucursal th').each(function(index) {
            if(index==1) {
                var title = $('#tblExpedienteSucursales thead tr#filterboxrow-sucursal th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-sucursal" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-sucursal">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblExpedienteSucursales.column($(this).index()).search($(`#input${$(this).index()}-sucursal`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).update();
	            });
            } else {
            }
        });

        let tblExpedienteSucursales = $('#tblExpedienteSucursales').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableExpedienteSucursales",
                "data": {
                    "prsExpedienteId": '<?php echo $_POST["prsExpedienteId"]; ?>'
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
                { "orderable": false, "targets": [1, 2, 3] },
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>