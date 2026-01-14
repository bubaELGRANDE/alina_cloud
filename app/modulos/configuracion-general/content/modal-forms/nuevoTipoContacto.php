<?php 
    @session_start();
    $tipoContacto = $_POST["arrayFormData"];
?>

<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="tipoContacto">
<div class="row justify-content-md-center">
    <div class="col">
        <p>El formato de la máscara debe definirse con los siguientes caracteres en las posiciones correspondietes, por ejemplo "####-####" para indicar un número de teléfono, cada "#" indica un número.</p>
        <p>
            '#': { patrón: /[0-9]/ }<br>
            'X': { patrón: /[0-9a-zA-Z]/ }<br>
            'S': { patrón: /[a-zA-Z]/ }<br>
            'A': { patrón: /[a-zA-Z]/, uppercase: true }<br>
            'a': { patrón: /[a-zA-Z]/, lowercase: true }<br>
            '!': { escape: true }<br>
            '*': { repetir: true }
        </p>
    </div>
</div>
<div class="row justify-content-md-center">
    <div class="col-6">
        <div class="form-outline">
            <i class="fas fa-address-book trailing"></i>
            <input type="text" id="nombreContacto" class="form-control" name="nombreContacto" required />
            <label class="form-label" for="nombreContacto">Tipo de contacto</label>
        </div>
    </div> 
    <div class="col-6">
        <div class="form-outline">
            <i class="fas fa-mask trailing"></i>
            <input type="text" id="masca" class="form-control" name="masca" required />
            <label class="form-label" for="masca">Máscara</label>
        </div>
    </div>
</div>

<script>
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
                            'Se ha creado con éxito el tipo de contacto.',
                            "success"
                        );
                        var tablaUpd = $("#operation").val();
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
</script>