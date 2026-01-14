<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
        POST:
        pagoTransferenciaId
        bancoId
        tipoTransferencia
        estadoPago
    */
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="pagos-transferencias-quedan">
<input type="hidden" id="pagoTransferenciaId" name="pagoTransferenciaId" value="<?php echo $_POST['pagoTransferenciaId']; ?>">
<input type="hidden" id="tipoTransferencia" name="tipoTransferencia" value="<?php echo $_POST['tipoTransferencia']; ?>">
<input type="hidden" id="bancoId" name="bancoId" value="<?php echo $_POST['bancoId']; ?>">
<?php 
    if($_POST['estadoPago'] == "Pendiente") {
?>
        <b>Importación de Quedan por rango de fechas</b>
        <div class="row mt-2">
            <div class="col-4 mb-4">
                <div class="form-outline">
                    <input type="date" id="fechaInicioQuedan" name="fechaInicioQuedan" class="form-control" required />
                    <label class="form-label" for="fechaInicioQuedan">Fecha inicio</label>
                </div>
            </div>
            <div class="col-4 mb-4">
                <div class="form-outline">
                    <input type="date" id="fechaFinQuedan" name="fechaFinQuedan" class="form-control" required />
                    <label class="form-label" for="fechaFinQuedan">Fecha fin</label>
                </div>
            </div>
            <div class="col-4 mb-4">
                <button type="submit" id="btnImportarQuedan" class="btn btn-primary btn-sm ttip">
                    <i class="fas fa-sync-alt"></i> Importar
                    <span class="ttiptext">Importar quedan en el rango de fechas</span>
                </button>
            </div>
        </div>
<?php 
    } else {
        // Otro estado, no mostrar inputs
    }
?>
<div class="table-responsive">
    <table id="tblQuedanTransferencia" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-quedan">
                <th>#</th>
                <th>Concepto</th>
                <th>Proveedor</th>
                <th>Cuenta</th>
                <th>Monto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
<script>
    function eliminarDetalleQuedan(frmData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar el quedan de la transferencia?`, 
            `Ya no será aplicado ni consolidado en los archivos de banco de la transferencia.`, 
            `warning`, 
            function(param) {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    frmData,
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Quedan eliminado de la transferencia con éxito`, `success`, function() {
                                $('#tblTransferencias').DataTable().ajax.reload(null, false);
                                $('#tblQuedanTransferencia').DataTable().ajax.reload(null, false);
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
                button_icons("btnImportarQuedan", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnImportarQuedan", "fas fa-sync-alt", "Importar", "enabled");
                        if(data.respuesta == "success") {
                            mensaje(
                                "Operación completada:",
                                `Se han importado ${data.numRegistros} quedan en el rango de fechas seleccionado con éxito.`,
                                "success"
                            );
                            $('#tblTransferencias').DataTable().ajax.reload(null, false);
                            $('#tblQuedanTransferencia').DataTable().ajax.reload(null, false);
                            $("#frmModal")[0].reset();
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

        $('#tblQuedanTransferencia thead tr#filterboxrow-quedan th').each(function(index) {
            if(index==1 || index == 2 || index == 3 || index == 4) {
                var title = $('#tblQuedanTransferencia thead tr#filterboxrow-quedan th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-quedan" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-quedan">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblQuedanTransferencia.column($(this).index()).search($(`#input${$(this).index()}-quedan`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblQuedanTransferencia = $('#tblQuedanTransferencia').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableQuedanPagosTransferencias",
                "data": {
                	"pagoTransferenciaId": '<?php echo $_POST["pagoTransferenciaId"]; ?>'
                }
            },
            "autoWidth": false,
            "footerCallback": function(tfoot) {
                var response = this.api().ajax.json();
                if(response && Object.keys(response.footer).length !== 0) {
                    var td = $(tfoot).find('td');
                    td.eq(2).html(response["footer"][2]);
                    td.eq(4).html(response["footer"][4]);
                } else {
                    // Es la primera vez que sse dibuja la DT
                }
            },
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