<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /* arrayFormData 
        Nuevo = nuevo
        Editar = editar ^ expedienteAusenciaId ^ personaId 
    */
    $arrayFormData = explode("^",$_POST['arrayFormData']);

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="anular-solicitud-ausencia">
<input type="hidden" name="personaId" id="personaId" value="<?php echo $arrayFormData[2];?>">

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
                            $("#tblAusencia").DataTable().ajax.reload(null, false);
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

