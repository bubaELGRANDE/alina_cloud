<?php 
    // id, usuario, tipoPersona
    $arrayFormData = explode("^", $_POST["arrayFormData"]);
?>
<div class="form-outline mb-4">
    <i class="fas fa-edit trailing"></i>
    <textarea class="form-control" id="justificacionEstado" name="justificacionEstado" rows="4" required></textarea>
    <label class="form-label" for="justificacionEstado">Justificación/Motivo</label>
</div>
<script>
    $(document).ready(function() {
        $("#frmModal").validate({
            submitHandler: function(form) {
                procesarEstado( 
                    '<?php echo $arrayFormData[0]; ?>', 
                    '<?php echo $arrayFormData[1]; ?>', 
                    'suspender', 
                    '<?php echo $arrayFormData[2]; ?>', 
                    $("#justificacionEstado").val()
                );
            }
        });
    });
</script>