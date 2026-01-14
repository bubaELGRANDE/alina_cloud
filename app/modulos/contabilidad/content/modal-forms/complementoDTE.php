<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

    $yearBD = $_POST['yearBD'];
?>

<div id="agregarComplemento" style="display:none;">
    <input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation'];?>">
	<input type="hidden" id="operation" name="operation" value="complemento-DTE">
    <input type="hidden" id="facturaId" name="facturaId" value="<?php echo $_POST['facturaId'];?>">
    <input type="hidden" id="proveedorUbicacionId" name="proveedorUbicacionId" value="<?php echo $_POST['proveedorUbicacionId'];?>">
    <input type="hidden" name="facturaComplementoId" id="facturaComplementoId" value="">
    <input type="hidden" id="yearBDModal" name="yearBD" value="<?php echo $yearBD; ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="form-outline mb-4">
                <i class="fas fa-list trailing"></i>
                <textarea id="descripcionComplemento" class="form-control" name="descripcionComplemento" required></textarea>
                <label class="form-label" for="descripcionComplemento">Descripción del complemento</label>
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
</div>
<div class="row">
    <div class="col text-end">
        <div class="col-md-12 text-end">
            <button type="button" id="nuevoBTN" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Nuevo complemento</button>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table id="tablDescripcionComplemento" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    function editComplemento(tableData){
        asyncData(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getDescripcionComplemento", 
            $("#frmModal").serialize()+ '&facturaComplementoId=' + tableData,
            function(data) {
                //var result = JSON.parse(data);

                $("#typeOperation").val("update");

                $("#descripcionComplemento").val(data.complementoFactura);
                $("#facturaComplementoId").val(tableData);

                button_icons("submit", "fas fa-edit", "Actualizar");
				
                $("#nuevoBTN").hide();
				$("#agregarComplemento").show();
                
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            }
        );
    }

    function eliminarComplemento (frmData){
        mensaje_confirmacion(
            `¿Esta seguro que desea eliminar esta complemento?`,
            `Se eliminará del catálogo.`,
            `warning`,
            (param) => {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation/',
                    frmData,
                    (data) => {
                        if (data=="success") {
                            mensaje_do_aceptar(
                                `Operación completada`,
                                `Complemento eliminado con éxito`,
                                `success`,
                                () => {
                                $(`#tablDescripcionComplemento`).DataTable().ajax.reload(null,false);
                            });
                        }else{
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Eliminar`,
            `Cancelar`
        )
    }

    $(document).ready(function() {

        $("#nuevoBTN").click(function(e){
            $("#agregarComplemento").toggle();
            $("#nuevoBTN").toggle();
        });
        $("#cancelarNContacto").click(function(e){
            $("#agregarComplemento").toggle();
            $("#nuevoBTN").toggle();

            $("#frmModal")[0].reset();
            button_icons("submit", "fas fa-plus-circle", "Agregar", "enabled");
        });
        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("submit", "fas fa-plus-circle", "Agregar", "enabled");

						if(data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                'Complemento registrado con éxito',
                                "success",
                                function(){
                                    $("#agregarComplemento").toggle();
                                    $("#nuevoBTN").toggle();
                                    $("#frmModal")[0].reset();
                                    $('#tablDescripcionComplemento').DataTable().ajax.reload(null, false);

                                    button_icons("submit", "fas fa-plus-circle", "Agregar");
                                }
                            )

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

        $('#tablDescripcionComplemento thead tr#filterboxrow th').each(function(index) {
			if(index==1) {
				var title = $('#tablDescripcionComplemento thead tr#filterboxrow th').eq($(this).index()).text();
				$(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
				$(this).on('keyup change', function() {
					tablDescripcionComplemento.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
				});
				document.querySelectorAll('.form-outline').forEach((formOutline) => {
					new mdb.Input(formOutline).init();
				});
			} else {
			}
		});

		let tablDescripcionComplemento = $('#tablDescripcionComplemento').DataTable({
			"dom": 'lrtip',
			"ajax": {
				"method": "POST",
				"url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableComplementoDTE",
				"data": { 
                    "facturaId" : <?php echo $_POST['facturaId'];?>,
                    "yearBD": "<?php echo $yearBD; ?>"
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