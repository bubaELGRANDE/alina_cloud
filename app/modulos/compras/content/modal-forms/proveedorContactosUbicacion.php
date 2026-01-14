<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    
    /*
        arrayFormData = proveedorUbicacionId^nombreProveedorUbicacion^nombreProveedor    
    */
    /*$arrayFormData        = explode('^', $_POST["arrayFormData"]);

    $proveedorUbicacionId = $arrayFormData[0];*/
    $proveedorUbicaciones = $cloud->row("
        SELECT 
            proveedorUbicacionId,
            nombreProveedorUbicacion 
        FROM comp_proveedores_ubicaciones 
        WHERE proveedorUbicacionId = ? AND flgDelete = ?
    ",[$_POST["proveedorUbicacionId"],0]);

?>
<!--<h5>Agregar contactos</h5>
<hr>-->
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="contacto-proveedor-ubicacion">
<input type="hidden" id="proveedorUbicacionId" name="proveedorUbicacionId" value="<?php echo $proveedorUbicaciones->proveedorUbicacionId;?>">
<input type="hidden" id="nombreProveedorUbicacion" name="nombreProveedorUbicacion" value="<?php echo $proveedorUbicaciones->nombreProveedorUbicacion;?>">
<input type="hidden" id="proveedorContactoId" name="proveedorContactoId" value="0">

<?php 
    if((in_array(85, $_SESSION["arrayPermisos"]) || in_array(119, $_SESSION["arrayPermisos"])) && $_POST['estadoProveedorUbicacion'] == "Activo") {
?>
        <div id="divBtnNuevoContacto" class="row">
            <div class="col-lg-4 offset-lg-8">
                <button type="button" id="btnNuevoPermiso" class="btn btn-primary btn-block" onclick="showHideForm(1, 'insert');">
                    <i class="fas fa-plus-circle"></i>
                    Nuevo Contacto
                </button>
            </div>
        </div>

        <div id="divFrmNuevoContacto" >
            <div class="row justify-content-md-center">
                <div class="col-lg-6">
                    <div class="form-select-control mb-4">
                        <select class="form-select tipoContacto" id="tipoContacto" name="tipoContacto" style="width: 100%;" required >
                            <option disabled selected>Seleccione un Tipo de contacto</option>
                            <?php $dataTipoCon = $cloud->rows("
                                SELECT 
                                    tipoContactoId, 
                                    tipoContacto 
                                FROM cat_tipos_contacto 
                                WHERE flgDelete = 0
                                ORDER BY tipoContacto
                            ");
                                foreach($dataTipoCon as $dataTipo){
                                    echo '<option value="'. $dataTipo->tipoContactoId .'">' . $dataTipo->tipoContacto . '</option>';
                                }
                            ?>
                        </select>
                    </div>
                </div> 
                <div class="col-lg-6">
                    <input type="hidden" id="contactoUbicacionHidden" class="form-control contactoUbicacion masked" name="contactoUbicacionHidden" disabled />
                    <div id="change-contact" class="form-outline form-update-contacto mb-4">
                        <i class="fas fa-address-book trailing"></i>
                        <input type="text" id="contactoUbicacion" class="form-control contactoUbicacion masked" name="contactoUbicacion" disabled />
                        <label class="form-label" for="contactoUbicacion">Contacto</label>
                    </div>
                </div>
            </div>
            <div class="form-outline mb-4">
                <i class="fas fa-edit trailing"></i>
                <textarea type="text" id="descripcionContacto" class="form-control" name="descripcionContacto" required ></textarea>
                <label class="form-label" for="descripcionContacto">Descripción de contacto</label>
            </div>
            <div class="row">
                <div class="col-lg-3 offset-lg-6">
                    <button type="submit" id="agregar" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
                <div class="col-lg-3">
                    <button type="button" class="btn btn-secondary btn-block" onclick="showHideForm(0, 'insert');">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
<?php 
    } else {
        // Ubicación inactiva o no tiene permisos
    }
?>
<h5>Contactos</h5>
<hr>
<div class="table-responsive">
    <table id="tblContactosUbicacion" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-contactos">
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
    function showHideForm(flg, tipo) {
        if(flg == 0) { // hide
            $("#divBtnNuevoContacto").show();
            $("#divFrmNuevoContacto").hide();
            $("#flgInsertUpdate").val("insert"); // regresar la flg para que al presionar los botones vuelvan a su función inicial "insert"
            $('#frmModal').trigger("reset");
        } else { // show
            $("#divBtnNuevoContacto").hide();
            $("#divFrmNuevoContacto").show();
            $("#flgInsertUpdate").val(tipo); // para definir insert/update y validar en el submitHandler
        }
    }

    function editContactoUbicacion(idContacto){
        asyncDoDataReturn(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getContactoUbicacion", 
            $("#frmModal").serialize()+ '&idContacto=' + idContacto,
            function(data) {
                var result = JSON.parse(data);

                $("#typeOperation").val("update");
                $("#tipoContacto").val(result.tipoContactoId);
                $("#tipoContacto").trigger('change');
                $("#descripcionContacto").val(result.descripcionProveedorContacto);
                $("#contactoUbicacionHidden").val(result.contactoProveedor);
                $("#proveedorContactoId").val(idContacto);
                $("#divBtnNuevoContacto").hide();
                $("#divFrmNuevoContacto").show();

                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            }
        );
    }
    function delContactoUbicacion(frmData){
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
                                $('#tblContactosUbicacion').DataTable().ajax.reload(null, false);
                                $('#tblUbicaciones').DataTable().ajax.reload(null, false);
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
        $("#divFrmNuevoContacto").hide();

        $("#tipoContacto").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de Contacto'
        });
        
        $("#tipoContacto").on("change", function(){
                asyncDoDataReturn(
                "<?php echo $_SESSION['currentRoute']; ?>content/divs/divInputMasca/", 
                $("#frmModal").serialize(),
                function(data) {
                    $("#change-contact").html(data);
                    $("#contactoUbicacion").val($("#contactoUbicacionHidden").val());
                    document.querySelectorAll('.form-update-contacto').forEach((formOutline) => {
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
                            mensaje_do_aceptar(
                                "Operación completada:",
                                'Se ha guardado con éxito la información de contacto.',
                                "success",
                                function() {
                                    $('#tblContactosUbicacion').DataTable().ajax.reload(null, false);
                                    // $('#tblUbicaciones').DataTable().ajax.reload(null, false);
                                    $("#tblProveedores").DataTable().ajax.reload(null, false);
                                    $('#frmModal').trigger("reset");
                                    $("#typeOperation").val("insert");
                                    $("#contactoUbicacionHidden").val("");
                                    $('#tipoContacto').val('').trigger('change');
                                    showHideForm(0, 'insert'); // para que se oculte
                                    //$('#modal-container').modal("hide");
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
        
        
        $('#tblContactosUbicacion thead tr#filterboxrow-contactos th').each(function(index) {
            if(index==1 ) {
                var title = $('#tblContactosUbicacion thead tr#filterboxrow-contactos th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-contactos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblContactosUbicacion.column($(this).index()).search($(`#input${$(this).index()}-contactos`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblContactosUbicacion = $('#tblContactosUbicacion').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableProveedorContactosUbicacion",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "proveedorUbicacionId": '<?php echo $proveedorUbicaciones->proveedorUbicacionId; ?>',
                    "nombreProveedor": '<?php echo $proveedorUbicaciones->nombreProveedorUbicacion; ?>',
                    "estadoProveedorUbicacion": '<?php echo $_POST["estadoProveedorUbicacion"]; ?>'
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

        $("#modalTitle").html('Contactos de proveedor - ubicación: <?php echo $proveedorUbicaciones->nombreProveedorUbicacion; ?>');
    });
</script>