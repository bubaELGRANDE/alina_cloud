<?php 
    @session_start();
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="cambiar-password">
<div class="row">
    <div class="col-lg-12">
        <p align="justify">Estimado(a) <?php echo $_SESSION["nombrePersona"]; ?>, por motivos de seguridad solicitamos que actualice su contraseña (debe ser diferente a la asignada por defecto). Las credenciales son para su uso exclusivo, y cada movimiento que se realice dentro del sistema quedará registrado. Mientras no realice el cambio no podrá realizar ninguna acción dentro de Cloud. <b>Esto es de carácter obligatorio.</b></p>
    </div>
</div>
<div class="row justify-content-md-center">
    <div class="col-lg-10">
        <div class="form-outline mb-4">
            <i class="fas fa-lock trailing"></i>
            <input type="password" id="passwordNew" class="form-control" name="passwordNew" data-rule-minlength="4" required />
            <label class="form-label" for="passwordNew">Contraseña nueva</label>
        </div>
    </div>
</div>
<div class="row justify-content-md-center">
    <div class="col-lg-10">
        <div class="form-outline mb-4">
            <i class="fas fa-lock trailing"></i>
            <input type="password" id="passwordConfirm" class="form-control" name="passwordConfirm" data-rule-equalTo="#passwordNew" required />
            <label class="form-label" for="passwordConfirm">Confirmar contraseña</label>
        </div>
    </div>
</div>
<div class="row justify-content-md-center">
    <div class="col-lg-10">
        <div id="divShowHidePass" class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" id="showHidePass" />
            <label id="txtShowPass" class="form-check-label" for="showHidePass">Mostrar contraseñas</label>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#frmModal").validate({
            messages: {
                passwordConfirm: {
                    equalTo: "Las contraseñas deben ser iguales"
                }
            },
            submitHandler: function(form) {
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/", 
                    $("#frmModal").serialize(),
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:", 
                                "Su contraseña ha sido actualizada. Inicie sesión con su nueva contraseña.", 
                                "success", 
                                function() {
                                    location.href = '../libraries/includes/logic/session/logout';
                                }, 
                                "Iniciar sesión"
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

        $("#btnModalCancelAction").click(function() {
            mensaje_confirmacion(
                '¿Está seguro que desea cerrar sesión?',
                'La próxima vez que ingrese deberá cambiar su contraseña.',
                'warning',
                function(param) {
                    location.href = '../libraries/includes/logic/session/logout';
                },
                'Cerrar sesión',
                'Cancelar'
            );
        });

        $("#showHidePass").click(function() {
            $(this).is(":checked") ? $("#txtShowPass").html("Ocultar contraseña") : $("#txtShowPass").html("Mostrar contraseña");
            $(this).is(":checked") ? $("#passwordNew").attr("type", "text") : $("#passwordNew").attr("type", "password");
            $(this).is(":checked") ? $("#passwordConfirm").attr("type", "text") : $("#passwordConfirm").attr("type", "password");
        });
    });
</script>