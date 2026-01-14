<?php 
    @session_start();
?>
<h2>
    Catálogo de Unidades de Medida
</h2>
<hr>
<div class="row">
    <div class="col text-end">
        <button id="btn" type="button" class="btn btn-primary" onclick="modalUnidadMedida('insert');"><i class="fas fa-plus-circle"></i> Nueva unidad de medida</button>
    </div>
</div>
<div class="table-responsive">
    <table id="tblUnidadMedida" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Unidad de Medida</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function modalUnidadMedida(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Unidad de Medida`,
                modalForm: 'unidadDeMedida',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function modalEquivalenciasUDM(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Nueva equivalencia de unidad de medida`,
                modalForm: 'equivalenciasUDM',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarUnidadMedida (tableData){
        mensaje_confirmacion(
            `¿Esta seguro que desea eliminar esta Unidad de Medida?`,
            `Se eliminará del catálogo.`,
            `warning`,
            (param) => {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation/',
                    {
                        typeOperation:`delete`,
                        operation: `unidad-medida`,
                        id: tableData
                    },
                    (data) => {
                        if (data=="success") {
                            mensaje_do_aceptar(`Operación completada`,`Unidad de Medida eliminada con éxito`,`success`,() => {
                                $(`#tblUnidadMedida`).DataTable().ajax.reload(null,false);
                            });
                        }else{
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Eliminar`,
            `Cancelar`
        )
    }

    $(document).ready(function() {
    
        $('#tblUnidadMedida thead tr#filterboxrow th').each(function(index) {
            if(index==1) {
                var title = $('#tblUnidadMedida thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblUnidadMedida.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblUnidadMedida = $('#tblUnidadMedida').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableUnidadesMedidas",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoSolicitud": 'unidadesMedida'
                }
            },
            "autoWidth": false,
            "columns": [
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