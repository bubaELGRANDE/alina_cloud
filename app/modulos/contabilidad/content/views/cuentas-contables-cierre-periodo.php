<?php
@session_start();
$jsonCerrarActual = array(
    "typeOperation" => "insert",
    "tituloModal" => "Agregar de período contable"
);
$funcionAgregar = htmlspecialchars(json_encode($jsonCerrarActual));
?>

<h2>Cierre de período contable</h2>
<div class="text-end">
    <button id="btn" type="button" class="btn btn-primary"
        onclick="modalAgregar(<?php echo $funcionAgregar; ?>);">
        <i class="fas fa-plus"></i> Agregar de período contable
    </button>
</div>
<div class="table-responsive">
    <table id="tblPeriodos" class="table table-hover mt-3" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-tblPediodo">
                <th>#</th>
                <th>Período</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    function modalAgregar(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: frmData.tituloModal,
                modalForm: 'agregarPeriodoContable',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function abrirPeriodo(frmData) {
        mensaje_confirmacion(
            `¿Está seguro que desea volver a abrir este período contable ?`,
            `Se modificara el período contable.`,
            `warning`,
            (param) => {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
                    frmData,
                    (data) => {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                `Operación completada`,
                                `Período modificado con éxito`,
                                `success`,
                                () => {
                                    $(`#tblPeriodos`).DataTable().ajax.reload(null, false);
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
            `Modificar`,
            `Cancelar`
        )
    }

    function cerrarPeriodo(frmData) {
        mensaje_confirmacion(
            `¿Está seguro que desea cerrar este período contable ?`,
            `Se modificara el período contable.`,
            `warning`,
            (param) => {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
                    frmData,
                    (data) => {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                `Operación completada`,
                                `Período modificado con éxito`,
                                `success`,
                                () => {
                                    $(`#tblPeriodos`).DataTable().ajax.reload(null, false);
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
            `Modificar`,
            `Cancelar`
        )
    }

    function delPeriodo(frmData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar este período contable ?`,
            `Se eliminara el período contable.`,
            `warning`,
            (param) => {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
                    frmData,
                    (data) => {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                `Operación completada`,
                                `Período eliminado con éxito`,
                                `success`,
                                () => {
                                    $(`#tblPeriodos`).DataTable().ajax.reload(null, false);
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
            `Modificar`,
            `Cancelar`
        )
    }

    $(document).ready(function () {
        let tblPeriodos = $('#tblPeriodos').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableCuentasContablesCierrePeriodo",
                "data": {
                    "x": ""
                }
            },
            "rowReorder": true,
            "autoWidth": false,
            "columns": [
                null,
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        $('#tblPeriodos thead tr#filterboxrow-tblPediodo th').each(function (index) {
            if (index == 1) {
                var title = $('#tblPeriodos thead tr#filterboxrow-tblPediodo th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function () {
                    tblPeriodos.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

    })
</script>