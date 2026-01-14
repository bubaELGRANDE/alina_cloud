<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

?>

<form id="agregarContacto" style="display:none;">
    <input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation'];?>">
	<input type="hidden" id="operation" name="operation" value="contacto-cliente">
	<input type="hidden" id="idClienteC" name="idClienteC" value="<?php echo $_POST['clienteUbicacionId']; ?>">
	<input type="hidden" id="idCliente" name="idCliente" value="<?php echo $_POST['clienteId']; ?>">
	<input type="hidden" id="nombreCliente" name="nombreCliente" value="<?php echo $_POST['nombreCliente']; ?>">
	<input type="hidden" id="contactoId" name="contactoId" value="">
    <div class="row">
        <div class="col-md-6">
            <div class="form-select-control">
                <select id="tipoContacto" name="tipoContacto" style="width: 100%;" required>
                    <option></option>
                    <?php $dataTipoCon = $cloud->rows("
                        SELECT tipoContactoId, tipoContacto FROM cat_tipos_contacto 
                        WHERE tipoContactoId IN (10,11,12,13,14) AND flgDelete = 0
                        ORDER BY tipoContacto
                    ");
                        foreach($dataTipoCon as $dataTipo){
                            echo '<option value="'. $dataTipo->tipoContactoId .'">' . $dataTipo->tipoContacto . '</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-outline mb-4">
                <i class="fas fa-id-card trailing"></i>
                <input type="text" id="contacto" class="form-control contactoUbicacion masked" name="contacto" required>
                <label id="labelContacto" class="form-label" for="contacto">Contacto</label>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-outline mb-4">
                <i class="fas fa-id-card trailing"></i>
                <input type="text" id="descripcion" class="form-control" name="descripcion" required>
                <label class="form-label" for="descripcion">Nombre o descripción de contacto</label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col text-end">
            <div class="col-md-12 text-end">
                <button type="submit" id="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Agregar</button>
                <button type="button" id="cancelarNContacto" class="btn btn-secondary"><i class="fas fa-times-circle"></i> Cancelar</button>
            </div>
        </div>
    </div>
</form>
<div class="row">
    <div class="col text-end">
        <div class="col-md-12 text-end">
            <button type="button" id="nuevoBTN" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Nuevo contacto</button>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table id="tabContacto" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Ubicación</th>
                <th>Contacto</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
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
                                $('#tabContacto').DataTable().ajax.reload(null, false);
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
    function editUbicacion(tableData){
        asyncDoDataReturn(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getContactoUbicacion", 
            $("#frmModal").serialize()+ '&idContacto=' + tableData,
            function(data) {
                var result = JSON.parse(data);

                $("#typeOperation").val("update");
                $("#tipoContacto").val(result.tipoContactoId).trigger('change');
                // $("input[name='visibilidadContacto'][value='"+result.visibilidadContacto+"']").prop("checked",true).trigger('change');
                $("#descripcion").val(result.descripcionContactoCliente);
                
                $("#contacto").val(result.contactoCliente);
                $("#contactoId").val(tableData);

                $("#submit").html
                button_icons("submit", "fas fa-edit", "Actualizar");
				
                $("#nuevoBTN").hide();
				$("#agregarContacto").show();

                
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            }
        );
    }

    $(document).ready(function() {
        $("#tipoContacto").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de contacto', 
            allowClear: true
        });
        $("#tipoContacto").change(function(e) {
            if($('#tipoContacto').val() == "10" || $('#tipoContacto').val() == "11" || $('#tipoContacto').val() == "12") {
                $("#contacto").val('');
				$("#contacto").attr("type", "text");
                Maska.create('.contactoUbicacion',{
                    mask: '####-####'
                });
                $("#contacto").attr("minlength", 9);
				$("#labelContacto").html("Número de contacto");
            } else if ($('#tipoContacto').val() == "13"){
                $("#contacto").removeAttr("minlength");
				$("#contacto").attr("type", "email");
				var mask = Maska.create('.contactoUbicacion');
                mask.destroy();
				$("#labelContacto").html("Correo electrónico de contacto");
			} else{
                $("#contacto").val('');
				$("#contacto").attr("type", "text");
                var mask = Maska.create('.contactoUbicacion');
                mask.destroy();
                $("#contacto").removeAttr("minlength");
				$("#labelContacto").html("Número de contacto");
            }
        });
        $("#nuevoBTN").click(function(e){
            $("#agregarContacto").toggle();
            $("#nuevoBTN").toggle();
        });
        $("#cancelarNContacto").click(function(e){
            $("#agregarContacto").toggle();
            $("#nuevoBTN").toggle();

            $("#frmModal")[0].reset();
            $("#typeOperation").val("insert");
            $("#tipoContacto").val('').trigger('change');
        });

        $("#agregarContacto").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation.php/", 
                    $("#agregarContacto").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");

						if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Contacto agregado con éxito.',
                                "success"
                            );
							$("#agregarContacto").toggle();
                            $("#nuevoBTN").toggle();
                            $("#frmModal")[0].reset();
                            $("#tipoContacto").val('').trigger('change');
                            $('#tabContacto').DataTable().ajax.reload(null, false);
                            $('#tblUbicaciones').DataTable().ajax.reload(null, false);

                            button_icons("submit", "fas plus-circle", "Guardar");
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
		$("#copyName").click(function(e){
			let nombreCliente = $("#nombreCliente").val();
			$("#nombreComercial").val(nombreCliente);
			$("#nombreComercial").addClass('active');
		});

        $('#tabContacto thead tr#filterboxrow th').each(function(index) {
			if(index==1 || index==2) {
				var title = $('#tabContacto thead tr#filterboxrow th').eq($(this).index()).text();
				$(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
				$(this).on('keyup change', function() {
					tabContacto.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
				});
				document.querySelectorAll('.form-outline').forEach((formOutline) => {
					new mdb.Input(formOutline).init();
				});
			} else {
			}
		});

		let tabContacto = $('#tabContacto').DataTable({
			"dom": 'lrtip',
			"ajax": {
				"method": "POST",
				"url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableClientesVinUbicacionesContacto.php",
				"data": { // En caso que se quiera enviar variable a la consulta
					"id": <?php echo $_POST['clienteUbicacionId']; ?>,
                    "nombreCliente": "<?php echo $_POST['nombreCliente']; ?>",
                    "nombreUbicacion": "<?php echo $_POST['nombreUbicacion']; ?>"
				}
			},
			"autoWidth": false,
			"columns": [
				null,
				{"width": "75%"},
				null
			],
			"columnDefs": [
				{ "orderable": false, "targets": [0, 1, 2] }
			],
			"language": {
				"url": "../libraries/packages/js/spanish_dt.json"
			}
		});
    });
</script>