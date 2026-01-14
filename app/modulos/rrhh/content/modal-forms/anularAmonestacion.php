<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $arrayFormData = explode("^",$_POST['arrayFormData']);

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="delete">
<input type="hidden" id="operation" name="operation" value="anularAmonestacion">
<input type="hidden" name="amonestacionId" id="amonestacionId" value="<?php echo $arrayFormData[1];?>">
<input type="hidden" name="expedienteId" id="expedienteId" value="<?php echo $arrayFormData[2];?>">

<div class="form-outline mb-4">
    <i class="fas flist-ul trailing"></i>
    <textarea type="text" id="motivo" class="form-control" name="motivo" required ></textarea>
    <label class="form-label" for="motivo">Motivo anulación</label>
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
                                'Anulación realizada con exito',
                                "success"
                            );
                            $("#tblAmonestaciones").DataTable().ajax.reload(null, false);
                            $("#tblAmonestacionesAnuladas").DataTable().ajax.reload(null, false);
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
    });
</script>

