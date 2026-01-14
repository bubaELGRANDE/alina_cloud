<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

?>

<form id="frmSociedades" style="display:none">
    <input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
	<input type="hidden" id="operation" name="operation" value="datos-PEP-sociedades">
	<input type="hidden" id="PEPId" name="PEPId" value="<?php echo $_POST['PEPId']; ?>">
	<input type="hidden" id="PEPSocId" name="PEPSocId" value="">
	<input type="hidden" id="nombrePEP" name="nombrePEP" value="<?php echo $_POST['nombrePEP']; ?>">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-outline">
                <i class="fas flist-ul trailing"></i>
                <input type="text" id="razonSocial" class="form-control" name="razonSocial" required>
                <label class="form-label" for="razonSocial">Razón social</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-outline">
                <input type="number" id="participacion" class="form-control" name="participacion" required>
                <label class="form-label" for="participacion">Porcentaje de participación</label>
            </div>
        </div>
    </div>
    <div class="row mb-3 justify-content-end">
        <div class="col-3">
            <button id="submit" type="submit" class="btn btn-primary btn-block btn-sm">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
        <div class="col-3">
            <button id="cancelFam" type="button" class="btn btn-secondary btn-block btn-sm">
                <i class="fas fa-times-circle"></i> Cancelar
            </button>
        </div>
    </div>
</form>
<div id="botonAgregar" class="row mb-3 justify-content-end">
    <div class="col-4">
        <button type="button" id="addFam" class="btn btn-primary btn-block btn-sm">
            <i class="fas fa-users"></i> Agregar sociedad relacionada
        </button>
    </div>
</div>
<hr>
<div class="table-responsive">
    <table id="tblPEPSoc" class="table table-hover mt-3" style="width: 100%;">
    <thead>
        <tr id="filterboxrow-tblUbicaciones">
            <th>#</th>
            <th>Razón social</th>
            <th>% de participación</th>
            <th>Acciones</th>
        </tr>
    </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function updtPEPSoc(PEPId){
        asyncDoDataReturn(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getPEPSociedades", 
            $("#frmModal").serialize()+ '&PEPSocId=' + PEPId,
            function(data) {
                var result = JSON.parse(data);

                $("#typeOperation").val("update");
                $("#PEPSocId").val(result.clientePEPPatrimonialId);
                $("#razonSocial").val(result.razonSocial);
                $("#participacion").val(result.porcentajeParticipacion);
                

                $("#submit").html
                button_icons("submit", "fas fa-edit", "Actualizar");
				
                $("#frmSociedades").show();
                $("#botonAgregar").hide();
                
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            }
        );
    }
    function delPEPFam(frmData){
        let title       = "Aviso:"
        let msj         = "¿Está seguro que quiere eliminar este registro?";
        let btnAccepTxt = "Confirmar";
        let msjDone     = "Se eliminó correctamente el registro.";
        
        mensaje_confirmacion(
			title, msj, `warning`, function(param) {
				asyncDoDataReturn(
					'<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
					frmData,
					function(data) {
						if(data == "success") {
							mensaje_do_aceptar(`Operación completada:`, msjDone, `success`, function() {
                                $('#tblPEPSoc').DataTable().ajax.reload(null, false);
                                $('#tblPEP').DataTable().ajax.reload(null, false);
                                //$("#modal-container").modal("hide"); // para aprobar y rechazar se usa modal
                            });

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
			btnAccepTxt,
			`Cancelar`
		);
    }

    $(document).ready(function() {
        $("#parentesco").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Parentesco', 
            allowClear: true
        });

        $("#addFam").click(function(){
            $("#frmSociedades").show();
            $("#botonAgregar").hide();
        });
        $("#cancelFam").click(function(){
            $("#frmSociedades").hide();
            $("#botonAgregar").show();
            $("#typeOperation").val('insert');
            $("#frmSociedades")[0].reset();
        });

        $("#frmSociedades").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation.php", 
                    $("#frmSociedades").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");

                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Información de persona expuesta politicamente agregada correctamente.',
                                "success"
                            );
                            $('#tblPEPSoc').DataTable().ajax.reload(null, false);
                            $('#tblPEP').DataTable().ajax.reload(null, false);
                            $("#frmSociedades").hide();
                            $("#botonAgregar").show();
                            $("#typeOperation").val('insert');
                            $("#frmSociedades")[0].reset();
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

        $('#tblPEPSoc thead tr#filterboxrow-tblPEPSoc th').each(function(index) {
            if(index==1 || index == 2) {
                var title = $('#tblPEPSoc thead tr#filterboxrow-tblPEPSoc th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-tblPEPSoc" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblPEPSoc.column($(this).index()).search($(`#input${$(this).index()}-tblPEPSoc`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblPEPSoc = $('#tblPEPSoc').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableClientePEPSociedades",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "PEPId": '<?php echo $_POST["PEPId"]; ?>',
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "50%"},
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1,2,3] },
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
        });
</script>