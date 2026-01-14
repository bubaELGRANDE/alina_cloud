<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
?>

<input type="hidden" id="typeOperation" name="typeOperation"  value="update">
<input type="hidden" id="operation" name="operation" value="anulacion-interna">
<input type="hidden" id="facturaId" name="facturaId" value="<?php echo $_POST['facturaId']; ?>">

<div class="row" >
    <div class="col-md-12">
        <div class="form-outline" data-mdb-input-init>
            <textarea name="obsAnulacionInterna" id="obsAnulacionInterna" class="form-control" required></textarea>
            <label class="form-label" for="obsAnulacionInterna">Motivo de anulación</label>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
        $("#frmModal").validate({
        submitHandler: function(form) {
            button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                $("#frmModal").serialize(),
                function(data) {
                    button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                    if(data == "success") {
                        mensaje_do_aceptar(
                            "Operación completada:",
                            "DTE anulado de manera interna con éxito",
                            "success",
                            function() {
                                // Se puede utilizar en más de una interfaz a futuro
                                // Por eso se usó reload en lugar de las function especificas
                                location.reload();
                                $('#modal-container').modal("hide");
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

});
</script>