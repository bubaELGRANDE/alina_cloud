let flgServer = 1;

function loadModal(modal, paramsModal) {
    $("#modal-container").modal("hide");
    asyncDoDataReturn(`../libraries/includes/parts/modals/${modal}`, paramsModal, function(data) {
        $("#divModal").html(data);
        $(`#${modal}`).modal("show");
    });
}
function button_icons(btnId, icon, txt, prop) {
    $(`#${btnId}`).html(`<i class="${icon}"></i> ${txt}` );
    if(prop == "disabled") {
        $(`#${btnId}`).attr("disabled", true);
    } else {
        $(`#${btnId}`).removeAttr("disabled");
    }
}
function changePage(url, page, data) {
    // backup wamp: ${url}content/views/page-${page}/
    if(flgServer == 0) {
        page += `/`;
    } else {
        // No concatenar pleca server
    }
    asyncDoDataReturn(`${url}content/views/page-${page}`, data, function(data) {
        $("#container-page").html(data);
        document.querySelectorAll('.form-outline').forEach((formOutline) => {
            new mdb.Input(formOutline).init();
        });
    });
}
$('#cerrarSesion').click(function (e) {
    mensaje_confirmacion(
        '¿Está seguro que desea cerrar sesión?',
        'Recuerde guardar los últimos cambios realizados para evitar perderlos.',
        'warning',
        function(param) {
            location.href = '../libraries/includes/logic/session/logout';
        },
        'Cerrar sesión',
        'Cancelar'
    );
});
function asyncDoDataReturn(url, data, callback, callbackBefore = 0) {
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
            if(callbackBefore == 0) {                
                mensaje_espera("","Por favor espere...");                
            } else {
                callbackBefore();
            }
        },
        data: data,
        success: function(data) {
            Swal.close();
            if(data == "logout-timeout") {
                location.href = "../libraries/includes/logic/session/logout?flg=0";
            } else if(data == "logout-status") {
                location.href = "../libraries/includes/logic/session/logout?flg=baja-emp";
            } else {
                callback(data);
            }
        }
    });
}
function asyncData(url, data, callback, callbackBefore = 0) {
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
            if(callbackBefore == 0) {                
                mensaje_espera("","Por favor espere...");                
            } else {
                callbackBefore();
            }
        },
        data: data,
        success: function(data) {
            Swal.close();
            if(tryParseJSON(data)) {
                callback(JSON.parse(data));
            } else {
                callback(data);
            }
        }
    });
}
function asyncFile(url, data, callback) {
    // backup wamp: url: url,
    let srvUrl = "";
    if(url.slice(-1) == `/` && flgServer == 1) {
        srvUrl = url.substring(0, url.length - 1);
    } else {
        srvUrl = url;
    }

    $.ajax({
        type: "POST",
        url: url,
        beforeSend: function() {
            mensaje_espera("","Por favor espere...");
        },
        processData: false,
        contentType: false,
        data: data,
        success: function(data) {
            Swal.close();
            if(data == "logout-timeout") {
                location.href = "../libraries/includes/logic/session/logout?flg=0";
            } else if(data == "logout-status") {
                location.href = "../libraries/includes/logic/session/logout?flg=baja-emp";
            } else {
                if(tryParseJSON(data)) {
                    callback(JSON.parse(data));
                } else {
                    callback(data);
                }
            }
        }
    });
}
function asyncSelect(url, data, nameSelect, callback) {
    $.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        data: data
    }).done(function(data){
        $(`#${nameSelect}`).empty();
        $(`#${nameSelect}`).append("<option></option>");
        for (let i = 0; i < data.length; i++){
            $(`#${nameSelect}`).append($('<option>', {
                value: data[i]['id'],
                text: data[i]['valor']
            }));
        }
        // Verificar si se proporcionó una función de devolución de llamada y ejecutarla
        if(typeof callback === 'function') {
            callback();
        } else {
            // No se proporciono nada
        }
    });
}
function asyncXLS(url, data, callback, callbackBefore = 0) {
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
            if(callbackBefore == 0) {                
                mensaje_espera("","Por favor espere...");                
            } else {
                callbackBefore();
            }
        },
        data: data,
        xhrFields: {
            responseType: 'blob'
        },
        success: function(data) {
            Swal.close();
            if(tryParseJSON(data)) {
                callback(JSON.parse(data));
            } else {
                callback(data);
            }
        }
    });
}
function mobileControl(doFn = 0) {
    if($("#pin-sidebar").is(":visible")) {
    } else { // Resolución pequeña
        if(doFn == 0) { // no se pasaron parametros, no hacer nada
        } else if(doFn == "check-mobile") { // body clic
            if($('.page-wrapper').hasClass('toggled')) { // cambiar iconos
                $("#menuShow").toggle();
                $("#menuHide").toggle();
            } else { // sidebar estaba oculta
            }
            $('.page-wrapper').removeClass('toggled');
        } else {
            doFn();
        }
    }
}
function startApp() {
    $("#pinExpand").hide();
    $("#menuShow").hide();
    $("#overlayAfk").hide();
    mobileControl("check-mobile");
    loadJcloudS();
}
function loadJcloudS() {
    let data = {lurl: '../libraries/', flgInactividad: '1'}; //flg = 1 es para hacer refreshSession, 0 = muestra countdown
    asyncDoDataReturn('../libraries/includes/logic/session/JcloudS', data, function(data) {
        $("#AFK").html(data);
    });
}
function tryParseJSON(data) {
    let parsedData, isJSON;
    try {
        parsedData = JSON.parse(data);
        isJSON = true;
    } catch (error) {
        parsedData = null;
        isJSON = false;
    }
    return isJSON;
}
function number_formatjs(x) {
    return x.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
}
document.addEventListener('keydown', function(event) {
    // Ctrl U
    if (event.ctrlKey && (event.key === 'u' || event.key === 'U')) {
        event.preventDefault(); // Prevenir el comportamiento predeterminado
    }
});
/*
// Contextmenu (clic derecho)
$(document).on("contextmenu", function(event) {
    event.preventDefault();
    // Mostramos el menú contextual personalizado
    $("#rightclick-menu").css({
        display: "block",
        left: event.pageX,
        top: event.pageY
    });
});
// Ocultamos el menú contextual personalizado
$(document).on("click", function(event) {
    $("#rightclick-menu").hide();
});
*/