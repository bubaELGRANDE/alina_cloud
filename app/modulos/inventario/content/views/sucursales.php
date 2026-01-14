<?php
@session_start();
?>
<style>
    .card-sucursal {
        border: 1px solid #e3e6f0;
        border-radius: .75rem;
        transition: all .2s ease-in-out;
        background: #fff;
        height: 100%;
    }

    .card-sucursal:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .card-sucursal .logo-wrap {
        height: 80px;
        width: 80px;
        overflow: hidden;
        border-radius: .5rem;
        border: 1px solid #ddd;
    }

    .card-sucursal img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        
    }

    .card-sucursal .acciones i {
        font-size: 1.2rem;
        cursor: pointer;
        padding: 6px;
        border-radius: .4rem;
        transition: .15s;
    }

    .card-sucursal .acciones i:hover {
        background: rgba(0, 0, 0, 0.07);
    }

    .card-sucursal .titulo {
        font-size: 1.15rem;
        font-weight: 600;
    }

    .card-sucursal .direccion {
        font-size: .9rem;
        color: #666;
    }
</style>
<h2 class="mb-3">Gestión de Sucursales</h2>
<hr>

<div class="text-end mb-4">
    <?php if (in_array(9, $_SESSION["arrayPermisos"]) || in_array(21, $_SESSION["arrayPermisos"])) { ?>
        <button class="btn btn-primary" onclick="nuevaSuc();">
            <i class="fas fa-plus-circle"></i> Nueva sucursal
        </button>
    <?php } ?>
</div>

<div id="containerSucursales" class="row g-3"></div>


<script>

    function nuevaSuc() {
        loadModal(
            "modal-container",
            {
                modalDev: "9^21",
                modalSize: 'lg',
                modalTitle: `Nueva sucursal`,
                modalForm: 'nuevaSucursal',
                //formData: tipoCon,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function editSucursal(idSuc) {
        loadModal(
            "modal-container",
            {
                modalDev: "9^22",
                modalSize: 'lg',
                modalTitle: `Editar sucursal`,
                modalForm: 'editarSucursal',
                formData: idSuc,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function contactoSucursal(idSuc) {
        loadModal(
            "modal-container",
            {
                modalDev: "9^24",
                modalSize: 'lg',
                modalTitle: `Contactos de sucursal`,
                modalForm: 'nuevoContactoSucursal',
                formData: idSuc,
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function departamentosSucursal(idSuc) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Departamentos de sucursal`,
                modalForm: 'sucursalDepartamentos',
                formData: idSuc,
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function delSucursal(idSucursal) {
        let title = "Aviso:"
        let msj = "¿Está seguro que quiere eliminar este registro?";
        let btnAccepTxt = "Confirmar";
        let msjDone = "Se eliminó correctamente el registro.";

        mensaje_confirmacion(
            title, msj, `warning`, function (param) {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
                    {
                        typeOperation: 'delete',
                        operation: 'delSucursal',
                        idSucursal: idSucursal,
                    },
                    function (data) {
                        if (data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, msjDone, `success`, function () {

                                $('#tblSucursal').DataTable().ajax.reload(null, false);
                                //$("#modal-container").modal("hide"); // para aprobar y rechazar se usa modal
                            });
                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            btnAccepTxt,
            `Cancelar`
        );
    }

    $(document).ready(function () {
        cargarSucursales();
    });

    function cargarSucursales() {
        $.ajax({
            url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/divSucursales",
            type: "POST",
            success: function (respuesta) {

                let data = JSON.parse(respuesta);
                let html = "";

                data.forEach(s => {

                    html += `
                <div class="col-md-4 col-lg-3">
                    <div class="card-sucursal p-3 d-flex flex-column">

                        <div class="d-flex align-items-center mb-3">
                            <div class="logo-wrap me-3">
                                <img src="../libraries/resources/images/${s.logo}" alt="${s.sucursal}">
                            </div>
                            <div>
                                <div class="titulo">${s.sucursal}</div>
                                <div class="direccion"><i class="fas fa-map-marker-alt"></i> ${s.direccion}</div>
                            </div>
                        </div>

                        <div class="mt-auto acciones d-flex justify-content-end gap-3">
                            ${s.acciones.contactos ? `<i class="fas fa-address-card text-primary" title="Contactos" onclick="contactoSucursal(${s.id})"></i>` : ""}
                            <i class="fas fa-building text-warning" title="Departamentos" onclick="departamentosSucursal(${s.id})"></i>
                            ${s.acciones.editar ? `<i class="fas fa-pencil-alt text-info" title="Editar" onclick="editSucursal(${s.id})"></i>` : ""}
                            ${s.acciones.eliminar ? `<i class="fas fa-trash-alt text-danger" title="Eliminar" onclick="delSucursal(${s.id})"></i>` : ""}
                        </div>

                    </div>
                </div>`;
                });

                $("#containerSucursales").html(html);
            }
        });
    }


</script>