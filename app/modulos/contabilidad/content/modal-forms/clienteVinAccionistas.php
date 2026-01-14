<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

?>

<form id="frmClienteAccionistas" style="display:none">
    <input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
	<input type="hidden" id="operation" name="operation" value="clientes-accionistas">
	<input type="hidden" id="clienteId" name="clienteId" value="<?php echo $_POST['clienteId']; ?>">
	<input type="hidden" id="nombreCliente" name="nombreCliente" value="<?php echo $_POST['nombreCliente']; ?>">
	<input type="hidden" id="accionistaId" name="accionistaId" value="">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-outline">
                <i class="fas flist-ul trailing"></i>
                <input type="text" id="nombreAccionista" class="form-control" name="nombreAccionista" required>
                <label class="form-label" for="nombreAccionista">Nombre completo</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-select-control">
                <select id="pais" name="pais" style="width: 100%;" required>
                    <option></option>
                    <option value="61">El Salvador</option>
                    <?php 
                        $dataPaises = $cloud->rows("
                            SELECT
                                paisId,
                                pais
                            FROM cat_paises
                            WHERE flgDelete = '0' AND paisId <> '61' ORDER BY pais ASC
                        ");
                        foreach ($dataPaises as $dataPaises) {
                            echo '<option value="'.$dataPaises->paisId.'">'.$dataPaises->pais.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-outline mb-4">
                <i class="fas fa-id-card trailing"></i>
                <input type="text" id="nitAccionista" class="form-control masked-nit masked" name="nitAccionista">
                <label class="form-label" for="nitAccionista">NIT</label>
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
            <i class="fas fa-users"></i> Agregar accionista
        </button>
    </div>
</div>
<hr>
<div class="table-responsive">
    <table id="tblAccionistas" class="table table-hover mt-3" style="width: 100%;">
    <thead>
        <tr id="filterboxrow-tblUbicaciones">
            <th>#</th>
            <th>Nombre</th>
            <th>Nacionalidad</th>
            <th>NIT</th>
            <th>Porcentaje de participación</th>
            <th>Acciones</th>
        </tr>
    </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function updtAccionista(id){
        asyncDoDataReturn(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getClienteAccionista", 
            $("#frmModal").serialize()+ '&accionistaId=' + id,
            function(data) {
                var result = JSON.parse(data);

                $("#typeOperation").val("update");
                $("#accionistaId").val(result.accionistaId);
                $("#nombreAccionista").val(result.nombreAccionista);
                $("#nitAccionista").val(result.nitAccionista);
                $("#participacion").val(result.porcentajeParticipacion);
                $("#pais").val(result.paisId).trigger('change');
                

                $("#submit").html
                button_icons("submit", "fas fa-edit", "Actualizar");
				
                $("#frmClienteAccionistas").show();
                $("#botonAgregar").hide();
                
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            }
        );
    }
    function delAccionista(frmData){
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
                                $('#tblAccionistas').DataTable().ajax.reload(null, false);
                                $('#tblClienteJ').DataTable().ajax.reload(null, false);
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
        Maska.create('.masked-nit',{
            mask: '####-######-###-#'
        });
        $("#pais").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Nacionalidad', 
            allowClear: true
        });

        $("#addFam").click(function(){
            $("#frmClienteAccionistas").show();
            $("#botonAgregar").hide();
        });
        $("#cancelFam").click(function(){
            $("#frmClienteAccionistas").hide();
            $("#botonAgregar").show();

            
            $("#frmClienteAccionistas")[0].reset();
            $("#typeOperation").val("insert");
            $("#pais").val('').trigger('change');
        });

        $("#frmClienteAccionistas").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation.php", 
                    $("#frmClienteAccionistas").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");

                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Información de persona expuesta politicamente agregada correctamente.',
                                "success"
                            );
                            $('#tblAccionistas').DataTable().ajax.reload(null, false);
                            $('#tblClienteJ').DataTable().ajax.reload(null, false);
                            $("#frmClienteAccionistas").hide();
                            $("#botonAgregar").show();
                            $("#frmClienteAccionistas")[0].reset();
                            $("#nacionalidad").val('').trigger('change');
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

        $('#tblAccionistas thead tr#filterboxrow-tblAccionistas th').each(function(index) {
            if(index==1 || index == 2) {
                var title = $('#tblAccionistas thead tr#filterboxrow-tblAccionistas th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-tblAccionistas" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblAccionistas.column($(this).index()).search($(`#input${$(this).index()}-tblAccionistas`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblAccionistas = $('#tblAccionistas').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableClientesAccionistas",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "clienteId": '<?php echo $_POST["clienteId"]; ?>',
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "25%"},
                null,
                null,
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