function mensaje(titulo, msj, tipo, btnConfirmTxt = 'Aceptar') {
    Swal.fire({
        icon: tipo,
        title: titulo,
        html: msj,
        backdrop: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        confirmButtonText: btnConfirmTxt
    });
}

function mensaje_do_aceptar(titulo, msj, tipo, doFunction, btnConfirmTxt = 'Aceptar') {
    Swal.fire({
        icon: tipo,
        title: titulo,
        html: msj,
        backdrop: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        confirmButtonText: btnConfirmTxt
    }).then((result) => {
        if(result.isConfirmed) {
            doFunction();
        } else {
        }
    });
}

function mensaje_footer(titulo, msj, tipo, footer, doFunction, btnConfirmTxt = 'Aceptar') {
    Swal.fire({
        icon: tipo,
        title: titulo,
        html: msj,
        footer: footer,
        backdrop: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        confirmButtonText: btnConfirmTxt
    }).then((result) => {
        if(result.isConfirmed) {
            doFunction();
        } else {
        }
    });
}

function mensaje_confirmacion(titulo, msj, tipo, doFunction, btnConfirmTxt = 'Aceptar', btnCancelTxt = 'Cancelar') {
    Swal.fire({
        title: titulo,
        html: msj,
        icon: tipo,
        showConfirmButton:true,
        confirmButtonText: btnConfirmTxt,
        showCancelButton:true,
        cancelButtonText: btnCancelTxt,
        showCloseButton:false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            doFunction();
        } else {
        }
    });
}

function mensaje_espera(titulo, msj) {
    Swal.fire({
        title: titulo,
        html: msj,
        timerProgressBar: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        didOpen: () => {
            Swal.showLoading()
         }
    });
}

function mensaje_toast(posicion, tiempo = 3500, icono, msj) {
    const Toast = Swal.mixin({
        toast: true,
        position: posicion,
        showConfirmButton: false,
        timer: tiempo,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
            Swal.hideLoading()
        }
    });

    Toast.fire({
        icon: icono,
        title: msj
    });
}

function mensaje_countdown(titulo, msj, time, fnCancelTime, fnFinishTime) {
    let timerInterval
    Swal.fire({
        title: titulo,
        html: msj,
        timer: time,
        timerProgressBar: true,
        showCloseButton: true,
        allowOutsideClick: true,
        allowEscapeKey: true,
        allowEnterKey: true,
        didOpen: () => {
            Swal.showLoading()
            timerInterval = setInterval(() => {
                const content = Swal.getContent();
                if(content) {
                    const b = content.querySelector('b');
                    if (b) {
                        b.textContent = ~~(Swal.getTimerLeft() * 0.001);
                    }
                }
            }, 1000)
        },
        willClose: () => {
            clearInterval(timerInterval);
        }
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.timer) {
            fnFinishTime();
        } else {
            fnCancelTime();
        }
    });
}