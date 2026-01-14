<?php 
    // tipoSolicitud ^ nombrePersona ^ id ^ pId ^ sideName ^ correoPersona ^ Empleado
    // tipoSolicitud ^ nombrePersona ^ dui ^ fechaNacimiento ^ correo ^ solicitudAccesoId ^ Externa
    $arrayFormData = explode("^", $_POST["arrayFormData"]);
?>
<div class="form-outline">
    <i class="fas fa-edit trailing"></i>
    <textarea class="form-control" id="justificacionEstado" name="justificacionEstado" rows="4" required></textarea>
    <label class="form-label" for="justificacionEstado">Justificación/Motivo</label>
</div>
<br><br>
<?php 
    if($arrayFormData[6] == "Empleado") {
?>
        <script>
            $(document).ready(function() {
                $("#frmModal").validate({
                    submitHandler: function(form) {
                        procesarSolicitud(
                            '<?php echo $arrayFormData[0]; ?>', 
                            '<?php echo $arrayFormData[1]; ?>', 
                            '<?php echo $arrayFormData[2]; ?>', 
                            '<?php echo $arrayFormData[3]; ?>', 
                            '<?php echo $arrayFormData[4]; ?>', 
                            '<?php echo $arrayFormData[5]; ?>', 
                            $("#justificacionEstado").val()
                        );
                    }
                });
            });
        </script>
<?php 
    } else { // Externa
?>
        <script>
            $(document).ready(function() {
                $("#frmModal").validate({
                    submitHandler: function(form) {
                        procesarSolicitudExterna(
                            '<?php echo $arrayFormData[0]; ?>', 
                            '<?php echo $arrayFormData[1]; ?>', 
                            '<?php echo $arrayFormData[2]; ?>', 
                            '<?php echo $arrayFormData[3]; ?>', 
                            '<?php echo $arrayFormData[4]; ?>', 
                            '<?php echo $arrayFormData[5]; ?>', 
                            $("#justificacionEstado").val()
                        );                    
                    }
                });
            });
        </script>
<?php 
    }
?>