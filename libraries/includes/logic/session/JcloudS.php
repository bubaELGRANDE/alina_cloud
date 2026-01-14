<?php 
    @session_start();
    date_default_timezone_set('America/El_Salvador');
    
    $hora = date("H"); $toastSaludo = "";
    if($hora >= 5 && $hora < 12) {
        $toastSaludo = "Buenos días, " . $_SESSION["nombrePersona"];
    } else if($hora >= 12 && $hora < 18) {
        $toastSaludo = "Buenas tardes, " . $_SESSION["nombrePersona"];
    } else if($hora >= 18 && $hora <= 23) {
        $toastSaludo = "Buenas noches, " . $_SESSION["nombrePersona"];
    } else {
        $toastSaludo = "¿Buenas madrugadas, " . $_SESSION["nombrePersona"] . "?";
    }
?>
<script>
    let conteoMinutos = 0, checkSesion = "0", flgRefreshSesion = 0, flgStart = 0;

    $(document).ready(function() {
        extendSidebarMenu();
        asyncPage(1,'start-app');

        $(window).click(function() {
            conteoMinutos = 0;
            checkSesion = '<?php echo $_SESSION["inactividad"]; ?>';
            if(checkSesion == "1") {
                location.href = '<?php echo $_POST["lurl"]; ?>includes/logic/session/logout?flg=0';
            } else {
            }
            if(flgRefreshSesion == 1) {
                $("#overlayAfk").hide();
                fnRefresh(1);
                flgRefreshSesion = 0;
            } else {
            }
        });
        $(window).keypress(function() {
            conteoMinutos = 0;
            checkSesion = '<?php echo $_SESSION["inactividad"]; ?>';
            if(checkSesion == "1") {
                location.href = '<?php echo $_POST["lurl"]; ?>includes/logic/session/logout?flg=0';
            } else {
            }
            if(flgRefreshSesion == 1) {
                $("#overlayAfk").hide();
                fnRefresh(1);
            } else {
            }
        });
        $(window).mousemove(function() {
            conteoMinutos = 0;
            if(flgRefreshSesion == 1) {
                $("#overlayAfk").hide();
                fnRefresh(1);
                flgRefreshSesion = 0;
            } else {
            }
        });
        $(window).scroll(function() {
            conteoMinutos = 0;
            if(flgRefreshSesion == 1) {
                $("#overlayAfk").hide();
                fnRefresh(1);
                flgRefreshSesion = 0;
            } else {
            }
        });

        let intervaloTiempo = setInterval(conteoTiempo, 60000); //found
    });

    function fnRefresh(flg = 0) { // para saber si ya volvió el usuario
        if(flg == 1) {
            $("#sideStatusClass").removeClass("user-status-ausente").addClass("user-status-online");
            $("#sideStatus").html("En línea");
        } else {
        }
        $.ajax({
            type: "POST",
            url: '<?php echo $_POST["lurl"]; ?>includes/logic/session/refreshSession',
            data: {
                x: ''
            },
            success: function(data) {
                //
            }
        });
    }

    function conteoTiempo() {
        conteoMinutos = conteoMinutos + 1;
        let flgInactividad = '<?php echo $_POST["flgInactividad"]; ?>';

        if(conteoMinutos > 8) { // para validar 9 minutos
            if(flgInactividad == "1") { // flgCerrar en interfaz, flgInactividad
                $("#sideStatusClass").removeClass("user-status-online").addClass("user-status-ausente");
                $("#sideStatus").html("Ausente");
                fnRefresh(0);
                flgRefreshSesion = 1;
                conteoMinutos = 0;
                $("#overlayAfk").show();
            } else {
                $("#overlayAfk").show();
                mensaje_countdown(
                    "Inactividad detectada", 
                    "Su sesión expirará en <b></b> segundos", 
                    60000, 
                    function() {
                        conteoMinutos = 0;
                        if(flgRefreshSesion == 1) {
                            flgRefreshSesion = 0;
                        } else {
                        }
                    }, 
                    function() {
                        //console.log("Expiró, cerrar sesión"); acá iría el href pero el setInterval ya lleva el conteo automático
                    }
                );
                flgRefreshSesion = 1;
            } 
        } else {
        }

        if(conteoMinutos > 9) { // 10 minutos - no sucederá, flgInactividad 1
            location.href = '<?php echo $_POST["lurl"]; ?>includes/logic/session/logout?flg=0';
        } else {
        }
    }

       /* function conteoTiempo() {
            conteoMinutos++;

            let flgInactividad = '<?php echo $_POST["flgInactividad"]; ?>';

            // Aviso antes de cerrar sesión (5 min antes del límite)
            if (conteoMinutos === 235) { // 3 horas 55 minutos
                if (flgInactividad == "1") {
                    $("#sideStatusClass").removeClass("user-status-online").addClass("user-status-ausente");
                    $("#sideStatus").html("Ausente");
                    fnRefresh(0);
                    flgRefreshSesion = 1;
                    $("#overlayAfk").show();
                } else {
                    $("#overlayAfk").show();
                    mensaje_countdown(
                        "Inactividad detectada",
                        "Su sesión expirará en <b></b> segundos",
                        60000,
                        function() {
                            conteoMinutos = 0;
                            if (flgRefreshSesion == 1) {
                                flgRefreshSesion = 0;
                            }
                        },
                        function() {
                            // Cierre al terminar la cuenta regresiva
                            location.href = '<?php echo $_POST["lurl"]; ?>includes/logic/session/logout?flg=0';
                        }
                    );
                    flgRefreshSesion = 1;
                }
            }

            // Cierre definitivo al llegar a las 4 horas
            if (conteoMinutos >= 240) { 
                location.href = '<?php echo $_POST["lurl"]; ?>includes/logic/session/logout?flg=0';
            }
        }*/




    // page = menuId, token =  page o submenu, urlVariables = para pasar Id, clases, variables a conveniencia
    function asyncPage(page,token = 'page', urlVariables = '0') {
        if(<?php echo $_SESSION["flgPassword"]; ?> == 1) {
            loadModal(
                "modal-container",
                {
                    modalDev: '-1',
                    modalSize: 'lg',
                    modalTitle: 'Cambiar contraseña - <?php echo $_SESSION["nombrePersona"] ?>',
                    modalForm: 'changeDefaultPassword',
                    buttonAcceptShow: true,
                    buttonAcceptIcon: 'sync-alt',
                    buttonAcceptText: 'Cambiar contraseña',
                    buttonCancelActionShow: true,
                    buttonCancelActionIcon: 'sign-out-alt',
                    buttonCancelActionText: 'Cerrar sesión'
                }
            );
        } else {
            let data = {page: page, token: token, urlVariables: urlVariables};
            asyncDoDataReturn('../libraries/includes/logic/session/loadPage', data, function(data) {
                $("#container-page").html(data);
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
                mobileControl("check-mobile");
                if(<?php echo $_SESSION["loginStart"]; ?> == 0 && flgStart == 0) {
                    // Se quitó para dejar el mensaje ya fijo en el "banner"
                    //mensaje_toast("top-end", 3500, "success", '<?php //echo $toastSaludo; ?>');
                    flgStart = 1;
                } else {
                }
            });
        }
    }
    function extendSidebarMenu() {
        // 0 = "m" + moduloId + "-" + menuId
        // 1 = modulo
        // 2 = menuSuperiorId
        let menuActive = '<?php echo $_SESSION["pageActive"]; ?>';
        let arrayActive = menuActive.split("^");
        if(arrayActive[1] == 1 || arrayActive[1] == 2) {
            // es principal o institucional
        } else {
            if(arrayActive[2] == 0) { // es menú único
                $(`#m${arrayActive[1]}`).next(".sidebar-submenu").slideDown(200); // abrir módulos
            } else { // es sub-submenu
                $(`#m${arrayActive[1]}`).next(".sidebar-submenu").slideDown(200); // abrir módulos
                $(`#m${arrayActive[1]}-${arrayActive[2]}`).next(".sidebar-sub-submenu").slideDown(200); // abrir submenu
            }
        }
    }
</script>