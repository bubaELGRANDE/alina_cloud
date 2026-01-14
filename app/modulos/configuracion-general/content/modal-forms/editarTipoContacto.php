<?php 
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    $arrayFormData = $_POST["arrayFormData"];

    $dataEditTipoCon = $cloud->row("
        SELECT tipoContacto, formatoContacto FROM cat_tipos_contacto WHERE   	tipoContactoId = ?
    ", [$arrayFormData]);
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="tipoContacto">
<input type="hidden" id="tipoContactoId" name="tipoContactoId" value="<?php echo $arrayFormData; ?>">

<div class="row justify-content-md-center">
    <div class="col-6">
        <div class="form-outline">
            <i class="fas fa-address-book trailing"></i>
            <input type="text" id="nombreContacto" class="form-control" name="nombreContacto" value="<?php echo $dataEditTipoCon->tipoContacto; ?>" required />
            <label class="form-label" for="nombreContacto">Tipo de contacto</label>
        </div>
    </div> 
    <div class="col-6">
        <div class="form-outline">
            <i class="fas fa-mask trailing"></i>
            <input type="text" id="masca" class="form-control" name="masca" value="<?php echo $dataEditTipoCon->formatoContacto; ?>" required />
            <label class="form-label" for="masca">Máscara</label>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
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
                            "Se ha actualizado el area", 
                            "success"
                        );
                        $("#tblTipoCont").DataTable().ajax.reload(null, false);
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
        }
    });

    $("#modalTitle").html('Editar tipo de contacto: <?php echo $dataEditTipoCon->tipoContacto; ?>');
});
</script>