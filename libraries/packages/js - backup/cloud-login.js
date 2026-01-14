let flgServer = 1;

function asyncDoDataReturn(url, data, callback, tipoJS = false) {
    // backup wamp: url: url,
    let srvUrl = "";
    if(url.slice(-1) == `/` && flgServer == 1) {
        srvUrl = url.substring(0, url.length - 1);
    } else {
        srvUrl = url;
    }
    $.ajax({
        type: "POST",
        url: srvUrl,
        beforeSend: function() {
            mensaje_espera("Verificando credenciales","Por favor espere...");
        },
        data: data,
        success: function(data) {
            //swal.close();
            if(tipoJS) {
                callback(JSON.parse(data));
            } else {
                callback(data);
            }
        }
    });
}

function button_icons(btnId, icon, txt, prop, propValue) {
    $(`#${btnId}`).html(`<i class="${icon}"></i> ${txt}` );
    $(`#${btnId}`).prop(prop, propValue);
}

$(document).ready(function() {
    $('#fechaNacimiento').on('change', function() { 
       $(this).addClass("active"); 
    });
    
    $('#mailRegister').on('change', function(){
        var mail = $(this).val();
        let mailContent = mail.toString().split("@");
        if(mailContent[1] == "alina.jewelry"){
            $("#nombreHidden").css("display", "none");
        }else{
            $("#nombreHidden").css("display", "block");
        }
    });

    $('#fechaNacimientoRestablecer').on('change', function() { 
       $(this).addClass("active"); 
    });

    $('.input-daterange').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        calendarWeeks : false,
        clearBtn: true,
        disableTouchKeyboard: true,
        todayHighlight: true
    });

    $("#frmLogin").validate({
        submitHandler: function(form) {
            let loginCheck = (flgServer == 1 ? 'loginCheck' : 'loginCheck/');
            button_icons("btnLogin", "fas fa-circle-notch fa-spin", "Cargando...", "disabled", "true");
            asyncDoDataReturn(`libraries/includes/logic/session/${loginCheck}`, $("#frmLogin").serialize(), function(data) {
                button_icons("btnLogin", "fas fa-sign-in-alt", "Iniciar sesión", "disabled", null);
                if(data == "success") {
                    location.href = "app/";
                } else {
                    let verifyData = data.split("^");
                    if(verifyData[0] == "error") {
                        mensaje_footer(
                            "Mensaje de error", 
                            verifyData[1], 
                            "error", 
                            verifyData[2], 
                            function(param) {
                            }
                        );
                    } else if(verifyData[0] == "lim") {
                        mensaje_do_aceptar(
                            "Credenciales incorrectas", 
                            verifyData[1], 
                            "warning",
                            function() {
                                location.reload();
                            }
                        );
                    } else {
                        mensaje_do_aceptar(
                            "Credenciales incorrectas", 
                            data, 
                            "warning", 
                            function(param) {
                            }
                        );
                    }
                }
            });
        },
    });
    
    Maska.create('#frmRegist .masked');
    
    $("#frmRegist").validate({
        messages: {
            dui: {
                minlength: "Número de DUI no válido"
            }
        },
        submitHandler: function(form) {
            let register = (flgServer == 1 ? 'register' : 'register/')
            button_icons("btnRegister", "fas fa-circle-notch fa-spin", "Cargando...", "disabled", "true");
            asyncDoDataReturn(`libraries/includes/logic/session/${register}`, $("#frmRegist").serialize(), function(data) {
                button_icons("btnRegister", "fas fa-sign-in-alt", "Registrarse", "disabled", null);
                if(data == "success") {
                    mensaje_do_aceptar(
                        "Su solicitud ha sido recibida", 
                        "Sus credenciales se enviarán a su correo al ser habilitado su usuario", 
                        "success",
                        function() {
                            location.reload();
                        }
                    );
                } else if(data == "existe") {
                    mensaje_do_aceptar(
                        "Aviso:", 
                        "Sus credenciales se encuentran en proceso de autorización.", 
                        "warning",
                        function() {
                            location.reload();
                        }
                    );
                } else if(data == "noIndupal") {
                    mensaje_do_aceptar(
                        "Aviso:", 
                        "Debe utilizar su cuenta institucional (@alina.jewelry)", 
                        "warning",
                        function() {
                            location.reload();
                        }
                    );
                } else {
                    mensaje_do_aceptar(
                        "Aviso:", 
                        "Parece que algo salió mal, intente nuevamente.", 
                        "warning",
                        function() {
                            location.reload();
                        }
                    );
                }
        });
        }
    });

    Maska.create('#frmOlvido .masked');
    
    $("#frmOlvido").validate({
        messages: {
            dui: {
                minlength: "Número de DUI no válido"
            }
        },
        submitHandler: function(form) {
            let recovery = (flgServer == 1 ? 'recoveryCheck' : 'recoveryCheck/')
            button_icons("btnRecuperar", "fas fa-circle-notch fa-spin", "Cargando...", "disabled", "true");
            asyncDoDataReturn(
                `libraries/includes/logic/session/${recovery}`, 
                $("#frmOlvido").serialize(), 
                function(data) {
                    button_icons("btnRecuperar", "fas fa-sign-in-alt", "Restablecer acceso", "disabled", null);
                    mensaje_do_aceptar(
                        "Su solicitud ha sido recibida", 
                        "Si la información proporcionada es correcta recibirá un correo electrónico con un enlace para restablecer su cuenta.", 
                        "success",
                        function() {
                            location.reload();
                        }
                    );
                }
            );
        }
    });

    $("#frmRestablecer").validate({
        rules: {
            passRestablecerConfirm: {
                equalTo: "#passRestablecer"
            }
        },
        messages: {
            passRestablecerConfirm: {
                equalTo: "Las contraseñas no coinciden"
            }
        },
        submitHandler: function(form) {
            let recovery = (flgServer == 1 ? 'recoveryRestore' : 'recoveryRestore/')
            button_icons("btnRestablecer", "fas fa-circle-notch fa-spin", "Cargando...", "disabled", "true");
            asyncDoDataReturn(
                `libraries/includes/logic/session/${recovery}`, 
                $("#frmRestablecer").serialize(), 
                function(data) {
                    button_icons("btnRestablecer", "fas fa-sign-in-alt", "Restablecer acceso", "disabled", null);
                    mensaje_do_aceptar(
                        data.tituloMensaje,
                        data.txtMensaje,
                        data.tipoMensaje,
                        function() {
                            location.href = location.href.split('?')[0];
                        }
                    );
                },
                true
            );
        }
    });

    $("#regist").click(function() {
        $("#login").hide(200);
        $("#registro").show(200);
    });

    $("#inisec").click(function() {
        $("#login").show(200);
        $("#nombreHidden").css("display", "none");
        $("#registro").hide(200);
    });

    $("#forgot").click(function() {
        $("#login").hide(200);
        $("#divOlvido").show(200);
    });

    $("#inisec2").click(function() {
        $("#login").show(200);
        $("#divOlvido").hide(200);
    });

    $("#inisec3").click(function() {
        $("#login").show(200);
        $("#divRestablecer").hide(200);
    });

    $("#showHidePass").click(function() {
        $(this).is(":checked") ? $("#txtShowPass").html("Ocultar contraseña") : $("#txtShowPass").html("Mostrar contraseña");
        $(this).is(":checked") ? $("#passLogin").attr("type", "text") : $("#passLogin").attr("type", "password");
    });
    $("#showHidePassRestablecer").click(function() {
        if($(this).is(":checked")) {
            $("#txtShowPassRestablecer").html("Ocultar contraseña");
            $("#passRestablecer").attr("type", "text");
            $("#passRestablecerConfirm").attr("type", "text");
        } else {
            $("#txtShowPassRestablecer").html("Mostrar contraseña");
            $("#passRestablecer").attr("type", "password");
            $("#passRestablecerConfirm").attr("type", "password");
        }
    });
});