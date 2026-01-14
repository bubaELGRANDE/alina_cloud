<?php
@session_start();

?>

<h2>
    Lista de notificaciones
</h2>
<hr>

<div class="row">
    <div class="col-lg-3 offset-lg-9">
        <button type="button" id="btnNuevo" class="btn btn-primary btn-block" onclick="modalAddLista()">
            <i class="fas fa-plus-circle"></i>
            Agregar personas
        </button>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-select-control">
            <select class="form-select" id="listaContacto" name="categoria" style="width:100%;">
                <option value=""></option>
                <option value="DEV-CLOUD">DESARROLLO CLOUD</option>
                <option value="DEV-MAGIC">DESARROLLO MAGIC</option>
                <option value="INVENTARIOS">Inventarios</option>
                <option value="BODEGA-01">(0001) Bodega Stihl</option>
                <option value="BODEGA-19">(0019) Bodega Kärcher</option>
                <option value="BODEGA-27">(0027) Bodega Hidropal</option>
                <option value="BODEGA-30">(0030) Bodega 0030</option>
                <option value="BODEGA-31">(0031) Bodega 0031</option>
                <option value="BODEGA-32">(0032) Bodega Agropal</option>
                <option value="JEFATURA-KARCHER">Jefatura Kärcher</option>
                <option value="JEFATURA-STIHL">Jefatura STIHL</option>
                <option value="JEFATURA-HIDROPAL">Jefatura Hidropal</option>
                <option value="JEFATURA-AGROPAL">Jefatura Agropal</option>
                <option value="JEFATURA-INDUSTRIAL">Jefatura Industrial</option>
                <option value="JEFATURA-CHESTERTON">Jefatura Chesterton</option>
                <option value="ADMIN-RETACEOS">Administrativos retaceos</option>
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <button type="button" id="btnNuevo" class="btn btn-secondary btn-block" onclick="buscar()">
            <i class="fas fa-search"></i>
            Buscar
        </button>
    </div>
</div>


<div class="table-responsive">
    <table id="tblListas" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Lista</th>
                <th>Persona</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>

    function deleteDetalle(frmData) {
        mensaje_confirmacion(
            '¿Está seguro de eliminar este empleado de la lista?',
            '',
            `warning`,
            function (param) {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    frmData,
                    function (data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if (data == 'success') {

                            mensaje(
                                "Operación completada:",
                                'Empleado Eliminado.',
                                "success"
                            );
                            $('#tblListas').DataTable().ajax.reload(null, false);
                            $("#modal-container").modal("hide");
                        } else {
                            mensaje("Aviso:", data, "warning");
                        }
                    }
                );
            },
            'Sí, Eliminar',
            `Cancelar`
        );
    }

    function modalAddLista() {
        loadModal(
            "modal-container", {
            modalDev: "-1",
            modalSize: 'md',
            modalTitle: "Agregar personas",
            modalForm: 'addNotificacionPersona',
            formData: null,
            buttonAcceptShow: true,
            buttonAcceptText: 'Guardar',
            buttonAcceptIcon: 'save',
            buttonCancelShow: true,
            buttonCancelText: 'Cancelar'
        }
        );
    }


    function buscar() {
        $('#tblListas').DataTable().ajax.reload(null, false);
    }

    $(document).ready(function () {


        $("#listaContacto").select2({
            placeholder: "Lista de contactos",
            allowClear: true
        });


        let tblListas = $('#tblListas').DataTable({
            dom: 'lfrtip',
            processing: false,
            ajax: {
                method: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableNotificaciones",
                data: function (d) {
                    d.categoria = $("#listaContacto").val() || "";
                }
            },
            autoWidth: false,
            columns: [
                null,
                null,
                { width: "70%" },
                null
            ],
            columnDefs: [
                { orderable: false, targets: [1, 2] }
            ],
            language: {
                url: "../libraries/packages/js/spanish_dt.json"
            }
        });

    });
</script>