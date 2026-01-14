<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
        POST:
        bitExportacionMagicId
        descripcionExportacion
        estadoExportacion
    */
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="sincronizacion-magic-compras">
<input type="hidden" id="bitExportacionMagicId" name="bitExportacionMagicId" value="<?php echo $_POST['bitExportacionMagicId']; ?>">
<input type="hidden" id="descripcionExportacion" name="descripcionExportacion" value="<?php echo $_POST['descripcionExportacion']; ?>">
<?php 
    if($_POST['estadoExportacion'] == "Pendiente") {
?>
        <b>Sincronización de Compras por rango de fechas</b>
        <div class="row mt-2">
            <div class="col-4 mb-4">
                <div class="form-outline">
                    <input type="date" id="fechaInicioCompras" name="fechaInicioCompras" class="form-control" required />
                    <label class="form-label" for="fechaInicioCompras">Fecha inicio</label>
                </div>
            </div>
            <div class="col-4 mb-4">
                <div class="form-outline">
                    <input type="date" id="fechaFinCompras" name="fechaFinCompras" class="form-control" required />
                    <label class="form-label" for="fechaFinCompras">Fecha fin</label>
                </div>
            </div>
            <div class="col-4 mb-4">
                <button type="submit" id="btnSincronizarCompras" class="btn btn-primary btn-sm ttip">
                    <i class="fas fa-sync-alt"></i> Sincronizar
                    <span class="ttiptext">Sincronizar Compras en el rango de fechas</span>
                </button>
            </div>
        </div>
<?php 
    } else {
        // Otro estado, no mostrar inputs
    }
?>
<div class="table-responsive">
    <table id="tblSincronizacionDetalle" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-detalle">
                <th>#</th>
                <th>Documento de Compra</th>
                <th>Proveedor</th>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    function eliminarSincronizacionDetalle(frmData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar la compra para que no sea considerada en la sincronización con Magic?`, 
            `No será sincronizada ni reflejada en registros de Magic, quedará como historial únicamente en Cloud.`, 
            `warning`, 
            function(param) {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    frmData,
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Compra eliminada de la sincronización con éxito`, `success`, function() {
                                $('#tblSincronizacionCompras').DataTable().ajax.reload(null, false);
                                $('#tblSincronizacionDetalle').DataTable().ajax.reload(null, false);
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
            `Sí, eliminar`,
            `Cancelar`
        );
    }

	$(document).ready(function() {
        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnSincronizarCompras", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnSincronizarCompras", "fas fa-sync-alt", "Sincronizar", "enabled");
                        if(data.respuesta == "success") {
                            $("#frmModal")[0].reset();
                            $("#bitExportacionMagicId").val(data.bitExportacionMagicId);
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se han cargado ${data.numRegistros} compras en el rango de fechas seleccionado con éxito.`,
                                "success",
                                function() {
                                    $('#tblSincronizacionCompras').DataTable().ajax.reload(null, false);
                                    $('#tblSincronizacionDetalle').DataTable().ajax.reload(null, false);
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

        $('#tblSincronizacionDetalle thead tr#filterboxrow-detalle th').each(function(index) {
            if(index==1 || index == 2 || index == 3 || index == 4) {
                var title = $('#tblSincronizacionDetalle thead tr#filterboxrow-detalle th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-detalle" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-detalle">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblSincronizacionDetalle.column($(this).index()).search($(`#input${$(this).index()}-detalle`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblSincronizacionDetalle = $('#tblSincronizacionDetalle').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableSincronizacionMagicDetalle",
                "data": function() { // En caso que se quiera volver a consultar la variable
                    return {
                    	"bitExportacionMagicId": $("#bitExportacionMagicId").val(),
                    	"descripcionExportacion": '<?php echo $_POST["descripcionExportacion"]; ?>',
                    	"estadoExportacion": '<?php echo $_POST["estadoExportacion"]; ?>'
                    }
                }
            },
            "autoWidth": false,
            "columns": [
                null, null, null, null, null, null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3, 4, 5] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });  
	});
</script>