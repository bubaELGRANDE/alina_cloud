<?php
@session_start();
?>
<h2>
    Catálogo de Marcas
</h2>
<hr>
<div class="row">
    <div class="col text-end">
        <button id="btn" type="button" class="btn btn-primary" onclick="modalMarca('insert');"><i
                class="fas fa-plus-circle"></i> Nueva Marca</button>
    </div>
</div>
<div class="table-responsive">
    <table id="tblMarca" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Marca</th>
                <th>Logo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function modalMarca(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Nueva Marca`,
                modalForm: 'frmMarcas',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function habilitarMarca(tableData) {
        let tableDataArray = tableData.split('^');
        mensaje_confirmacion(
            `¿Esta seguro que desea habilitar esta Marca?`,
            `Se habilitará la Marca <b>${tableDataArray[1]}</b>.`,
            `info`,
            (param) => {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation/',
                    {
                        typeOperation: `update`,
                        operation: `habilitar-marca`,
                        id: tableData
                    },
                    (data) => {
                        if (data == "success") {
                            mensaje_do_aceptar(`Operación completada`, `Marca <b>${tableDataArray[1]}</b> habilitada con éxito`, `success`, () => {
                                $(`#tblMarca`).DataTable().ajax.reload(null, false);
                            });
                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warnig"
                            );
                        }
                    }
                );
            },
            `Si, habilitar`,
            `Cancelar`
        )
    }

    function eliminarMarca(tableData) {
        mensaje_confirmacion(
            `¿Esta seguro que desea eliminar esta Marca?`,
            `Se eliminará del catálogo.`,
            `warning`,
            (param) => {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation/',
                    {
                        typeOperation: `delete`,
                        operation: `marca`,
                        id: tableData
                    },
                    (data) => {
                        if (data == "success") {
                            mensaje_do_aceptar(`Operación completada`, `Marca eliminada con éxito`, `success`, () => {
                                $(`#tblMarca`).DataTable().ajax.reload(null, false);
                            });
                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warnig"
                            );
                        }
                    }
                );
            },
            `Eliminar`,
            `Cancelar`
        )
    }

    $(document).ready(function () {

        $('#tblMarca thead tr#filterboxrow th').each(function (index) {
            if (index == 1) {
                var title = $('#tblMarca thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function () {
                    tblMarca.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblMarca = $('#tblMarca').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableMarcas",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoSolicitud": 'marca'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                { "width": "15%" },
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