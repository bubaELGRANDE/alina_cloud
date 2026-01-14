<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
	// arrayFormData = personaId ^ nombrePersona
	$arrayFormData = explode("^", $_POST["arrayFormData"]);

    $dataEstadoPersona = $cloud->row("
        SELECT
            estadoPersona
        FROM th_personas
        WHERE personaId = ?
    ",[$arrayFormData[0]]);

    if($dataEstadoPersona->estadoPersona == "Inactivo") {
        $disabledInactivo = "disabled";
    } else {
        $disabledInactivo = "";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="contactoEmpleado">
<input type="hidden" id="personaId" name="personaId" value="<?php echo $arrayFormData[0]; ?>">
<input type="hidden" id="idContact" name="idContact" value="<?php echo $arrayFormData[0]; ?>">
<div id="divBtnNuevoPermiso" class="row">
	<div class="col-lg-4 offset-lg-8">
		<button type="button" id="btnNuevoPermiso" class="btn btn-primary btn-block" onclick="showHideForm(1, 'insert');" <?php echo $disabledInactivo; ?>>
			<i class="fas fa-plus-circle"></i>
			Nuevo Contacto
		</button>
	</div>
</div>
<div id="divFrmNuevoContacto">
    <div class="row justify-content-md-center">
        <div class="col-md-6">
            <div class="form-select-control mb-4">
                <select id="tipoContacto" name="tipoContacto" style="width: 100%;" required>
                    <option></option>
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
        <div class="col-md-6">
			<input type="hidden" id="contactoPersonaHidden" class="form-control contactoPersona masked" name="contactoPersonaHidden" disabled />
            <div id="change-contact" class="form-outline form-update-contacto mb-4">
                <i class="fas fa-address-book trailing"></i>
                <input type="text" id="contactoPersona" class="form-control masked" name="contactoPersona" disabled />
                <label class="form-label" for="nombreContacto">Contacto</label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-4">
            <label>Privacidad del Contacto</label>
            <div class="form-check-validate">
                <?php 
                    $visibilidadContactos = array("Público", "Privado");
                    for ($i=0; $i < count($visibilidadContactos); $i++) { 
                        echo '
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="visibilidadContacto" id="visibilidadContacto'.$i.'" value="'.$visibilidadContactos[$i].'" required>
                                <label class="form-check-label" for="visibilidadContacto'.$i.'">'.$visibilidadContactos[$i].'</label>
                            </div>                    
                        ';
                    }
                ?>
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-outline mb-4">
                <i class="fas fa-edit trailing"></i>
                <textarea type="text" id="descripcionContacto" class="form-control" name="descripcionContacto" required></textarea>
                <label class="form-label" for="descripcionContacto">Descripción de contacto</label>
            </div>            
        </div>
    </div>
    <div class="d-flex justify-content-end mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="flgContactoEmergencia" name="flgContactoEmergencia" value="1" />
            <label class="form-check-label" for="flgContactoEmergencia">
                Contacto de emergencia
            </label>
        </div>
    </div>
    <div class="row">
    	<div class="col-lg-3 offset-lg-6">
    		<button type="submit" class="btn btn-primary btn-block" <?php echo $disabledInactivo; ?>>
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
<div class="table-responsive">
	<table id="tblContactosEmpleado" class="table table-hover" style="width: 100%;">
	    <thead>
	    	<tr id="filterboxrow-permisos">
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
			$("#divBtnNuevoPermiso").show();
			$("#divFrmNuevoContacto").hide();
            $('#frmModal').trigger("reset");
		} else { // show
			$("#divBtnNuevoPermiso").hide();
			$("#divFrmNuevoContacto").show();
		}
	}

	function editarContactoEmpleado(tableData) {
		asyncDoDataReturn(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getContactoEmpleado", 
            $("#frmModal").serialize()+ '&idContacto=' + tableData,
            function(data) {
                var result = JSON.parse(data);

                $("#typeOperation").val("update");
                $("#tipoContacto").val(result.tipoContactoId);
                $("#tipoContacto").trigger('change');
                $("input[name='visibilidadContacto'][value='"+result.visibilidadContacto+"']").prop("checked",true).trigger('change');
                $("#descripcionContacto").val(result.descripcionCPersona);
                if(result.flgContactoEmergencia == 1) {
                    $("#flgContactoEmergencia").prop("checked", true);
                } else {
                    $("#flgContactoEmergencia").prop("checked", false);
                }
                $("#contactoPersonaHidden").val(result.contactoPersona);
                $("#idContact").val(tableData);
				$("#divBtnNuevoPermiso").hide();
				$("#divFrmNuevoContacto").show();

                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            }
        );
	}

	function delContactoEmpleado(idContacto){
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
                let title = "Aviso:"
                let msj = "¿Esta seguro que quiere eliminar este registro?";
                let btnAccepTxt = "Confirmar";
                let msjDone = "Se eliminó correctamente el registro.";
                
                mensaje_confirmacion(
                    title, msj, `warning`, function(param) {
                        asyncDoDataReturn(
                            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                            {
                                typeOperation: 'delete',
                                operation: 'delContactoEmpleado',
                                idContacto: idContacto,
                            },
                            function(data) {
                                if(data == "success") {
                                    mensaje_do_aceptar(`Operación completada:`, msjDone, `success`, function() {
                                    $('#tblContactosEmpleado').DataTable().ajax.reload(null, false);
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
        <?php 
            }
        ?>
    }
	

    $(document).ready(function() {
    	$("#divFrmNuevoContacto").hide();

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
                    $("#contactoPersona").val($("#contactoPersonaHidden").val());
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
                            mensaje(
                                "Operación completada:",
                                'Se ha guardado con éxito la información de contacto.',
                                "success"
                            );
                            $('#tblContactosEmpleado').DataTable().ajax.reload(null, false);
                            $('#frmModal').trigger("reset");
                            $("#typeOperation").val("insert");
                            $("#contactoSucursalHidden").val("");
                            $('#tipoContacto').val('').trigger('change');
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

        $('#tblContactosEmpleado thead tr#filterboxrow-permisos th').each(function(index) {
            if(index==1) {
                var title = $('#tblContactosEmpleado thead tr#filterboxrow-permisos th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-permisos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-permisos">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblContactosEmpleado.column($(this).index()).search($(`#input${$(this).index()}-permisos`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).update();
	            });
            } else {
            }
        });

        let tblContactosEmpleado = $('#tblContactosEmpleado').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableContactosEmpleado",
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