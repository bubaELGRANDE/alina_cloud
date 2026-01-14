<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    
    $idSucursal = $_POST["arrayFormData"];

    $datSucursal = $cloud->row("
        SELECT sucursal FROM cat_sucursales WHERE sucursalId = ?
    ", [$idSucursal]);
?>
<?php if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(24, $_SESSION["arrayPermisos"])) { ?>
<!--<h5>Agregar contactos</h5>
<hr>-->
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="ContactoSucursal">
<input type="hidden" id="idSucursal" name="idSucursal" value="<?php echo $idSucursal; ?>">
<input type="hidden" id="idContact" name="idContact" value="<?php echo $idSucursal; ?>">

<div id="contactos" class="mb-4">
    <div class="row justify-content-md-center">
        <div class="col-6">
            <div class="form-select-control mb-4">
                <select class="form-select tipoContacto" id="tipoContacto" name="tipoContacto" style="width: 100%;" required >
                    <option disabled selected>Seleccione un Tipo de contacto</option>
                    <?php $dataTipoCon = $cloud->rows("
                        SELECT tipoContactoId, tipoContacto FROM cat_tipos_contacto WHERE flgDelete = 0
                        ORDER BY tipoContacto
                    ");
                        foreach($dataTipoCon as $dataTipo){
                            echo '<option value="'. $dataTipo->tipoContactoId .'">' . $dataTipo->tipoContacto . '</option>';
                        }
                    ?>
                </select>
            </div>
        </div> 
        <div class="col-6">
                <input type="hidden" id="contactoSucursalHidden" class="form-control contactoSucursal masked" name="contactoSucursalhidden" disabled />
            <div id="change-contact" class="form-outline mb-4">
                <i class="fas fa-address-book trailing"></i>
                <input type="text" id="contactoSucursal" class="form-control contactoSucursal masked" name="contactoSucursal" disabled />
                <label class="form-label" for="nombreContacto">Contacto</label>
            </div>
        </div>
    </div>
    <div class="form-outline mb-4">
        <i class="fas fa-edit trailing"></i>
        <textarea type="text" id="descripcionContacto" class="form-control" name="descripcionContacto" required ></textarea>
        <label class="form-label" for="descripcionContacto">Descripción de contacto</label>
    </div>
<button type="submit" id="agregar" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button>
</div>
<?php } ?>
<h5>Contactos</h5>
<hr>
<div class="table-responsive">
    <table id="tblContactSuc" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Contacto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    //Maska.create('#frmModal .masked');
    
    function editContactoSucursal(idContacto){
        asyncDoDataReturn(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getContactoSucursal", 
            $("#frmModal").serialize()+ '&idContacto=' + idContacto,
            function(data) {
                var result = JSON.parse(data);

                $("#typeOperation").val("update");
                $("#tipoContacto").val(result.tipoContactoId);
                $("#tipoContacto").trigger('change');
                $("#descripcionContacto").val(result.descripcionCSucursal);
                $("#contactoSucursalHidden").val(result.contactoSucursal);
                $("#idContact").val(idContacto);

                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            }
        );
    }
    function delContactoSucursal(idContacto){
        let title = "Aviso:"
        let msj = "¿Está seguro que quiere eliminar este registro?";
        let btnAccepTxt = "Confirmar";
        let msjDone = "Se eliminó correctamente el registro.";
        
        mensaje_confirmacion(
			title, msj, `warning`, function(param) {
				asyncDoDataReturn(
					'<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
					{
						typeOperation: 'delete',
						operation: 'delContactoSucursal',
						idContacto: idContacto,
					},
					function(data) {
						if(data == "success") {
							mensaje_do_aceptar(`Operación completada:`, msjDone, `success`, function() {
								

                        $('#tblContactSuc').DataTable().ajax.reload(null, false);
                        $('#tblSucursal').DataTable().ajax.reload(null, false);
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
        $("#tipoContacto").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de Contacto'
        });
        
        $("#tipoContacto").on("change", function(){
                asyncDoDataReturn(
                "<?php echo $_SESSION['currentRoute']; ?>content/divs/divInputMasca", 
                $("#frmModal").serialize(),
                function(data) {
                    $("#change-contact").html(data);
                    $("#contactoSucursal").val($("#contactoSucursalHidden").val());
                    document.querySelectorAll('.form-outline').forEach((formOutline) => {
                        new mdb.Input(formOutline).init();
                    });
                }
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
                            mensaje(
                                "Operación completada:",
                                'Se ha guardado con éxito la información de contacto.',
                                "success"
                            );
                            $('#tblContactSuc').DataTable().ajax.reload(null, false);
                            $('#tblSucursal').DataTable().ajax.reload(null, false);
                            $('#frmModal').trigger("reset");
                            $("#typeOperation").val("insert");
                            $("#contactoSucursalHidden").val("");
                            $('#tipoContacto').val('').trigger('change');
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
        
        
        $('#tblContactSuc thead tr#filterboxrow th').each(function(index) {
            if(index==1 ) {
                var title = $('#tblContactSuc thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblSucursal.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblEstudio = $('#tblContactSuc').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableContactosSucursales",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "idSucursal": '<?php echo $idSucursal; ?>'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        $("#modalTitle").html('Contactos de sucursal: <?php echo $datSucursal->sucursal; ?>');
    });
</script>