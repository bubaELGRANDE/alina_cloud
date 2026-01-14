<?php
@session_start();
?>
<h2>
    Catálogo de Especificaciones
</h2>
<hr>
<div class="row">
    <div class="col text-end">
        <?php if (in_array(9, $_SESSION["arrayPermisos"]) || in_array(18, $_SESSION["arrayPermisos"])) { ?>
            <button id="btn" type="button" class="btn btn-primary" onclick="modalEspecificaciones('insert');"><i
                    class="fas fa-plus-circle"></i> Nueva especificación</button>
        <?php } ?>
    </div>
</div>
<div class="table-responsive">
    <table id="tblEspecificaciones" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Especificación</th>
                <th>Tipo de especificación</th>
                <th>Tipo de magnitud</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function modalEspecificaciones(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Agregar especificaciones a producto`,
                modalForm: 'especificacionProducto',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function eliminarEsp(idEsp) {
        let title = "Aviso:"
        let msj = "¿Esta seguro que quiere eliminar esta especificación?";
        let btnAccepTxt = "Confirmar";
        let msjDone = "Se eliminó correctamente la especificación.";

        mensaje_confirmacion(
            title, msj, `warning`, function (param) {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation/',
                    {
                        typeOperation: 'delete',
                        operation: 'especificacion',
                        idEsp: idEsp,
                    },
                    function (data) {
                        if (data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, msjDone, `success`, function () {
                                $("#tblEspecificaciones").DataTable().ajax.reload(null, false);
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

        $('#tblEspecificaciones thead tr#filterboxrow th').each(function (index) {
            if (index == 1) {
                var title = $('#tblEspecificaciones thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function () {
                    tblEspecificaciones.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblEspecificaciones = $('#tblEspecificaciones').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEspecificaciones",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoSolicitud": 'unidadesMedida'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>