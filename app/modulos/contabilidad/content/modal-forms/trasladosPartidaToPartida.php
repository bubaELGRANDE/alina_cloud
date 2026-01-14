<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="traspasar-partida-contable-detalle">
<input type="hidden" id="partidaIdOrigen" name="partidaIdOrigen" value="<?= $_POST['partidaId'] ?>">
<input type="hidden" id="detalleId" name="detalleId" value="<?= $_POST['detalleId'] ?>">
<p class="text-muted small mb-2">
    Indica la <strong>fecha</strong> de las compras diarias que deseas liquidar.
</p>
<input type="number" class="form-control" name="partidaIdDestino" required>
<script>
    $("#frmModal").validate({
        submitHandler: function (form) {
            button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                $("#frmModal").serialize(),
                function (data) {
                    button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                    if (data.status === "success") {
                        mensaje_do_aceptar(
                            "Operación completada:",
                            "La partida contable se generó correctamente.",
                            "success",
                            function () {
                                $('#tblPartidasDetalle').DataTable().ajax.reload(null, false);
                                $("#modal-container").modal("hide");
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
</script>