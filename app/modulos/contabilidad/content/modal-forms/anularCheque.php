<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="anular-cheque">
<input type="hidden" id="chequeId" name="chequeId" value="<?php echo $_POST['chequeId']; ?>">
<input type="hidden" id="numCheque" name="numCheque" value="<?php echo $_POST['numCheque']; ?>">
<div class="form-outline input-daterange mb-4">
    <input type="date" id="fechaAnulacion" class="form-control" name="fechaAnulacion" value="<?php echo date('Y-m-d'); ?>" required />
    <label class="form-label" for="fechaAnulacion">Fecha de anulación</label>
</div>

<div class="form-outline">
    <i class="fas fa-list-ol trailing"></i>
    <textarea id="justificacion" class="form-control" name="justificacion" required rows="3"></textarea>
    <label class="form-label" for="justificacion">Justificación de la anulación</label>
</div>

<script>
    $(document).ready(function() {
        $("#frmModal").validate({
            submitHandler: function(form) {
                mensaje_confirmacion(
                    '¿Está seguro que desea anular el cheque?', 
                    `Pasará a estado anulado y no se consolidará en los reportes y estados de cuenta.`, 
                    `warning`, 
                    function(param) {
                        button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                        asyncData(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                            $("#frmModal").serialize(),
                            function(data) {
                                button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                                if(data == "success") {
                                    $(`#tblChequesEmitidos`).DataTable().ajax.reload(null, false);
                                    $(`#tblChequesEntregados`).DataTable().ajax.reload(null, false);
                                    $(`#tblChequesAnulado`).DataTable().ajax.reload(null, false);
                                    $('#modal-container').modal("hide");
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
                    'Sí, anular cheque',
                    `Cancelar`
                );
            }
        });
    });
</script>