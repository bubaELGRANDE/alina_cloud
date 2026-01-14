<?php 
    @session_start();
?>
<h2>
    Catálogo de Categorías
</h2>
<hr>
<div class="row">
    <div class="col text-end">
        <button id="btn" type="button" class="btn btn-primary" onclick="modalCategoria('insert');"><i class="fas fa-plus-circle"></i> Nueva Categoría</button>
    </div>
</div>
<div class="table-responsive">
    <table id="tblCatagoria" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Categoría</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function modalCategoria(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Nueva Categoría`,
                modalForm: 'categorias',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function modalEsp(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Especificaciones por categoria`,
                modalForm: 'unidadMedidaCategoria',
                formData: formData,
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarCategoria (tableData){
        mensaje_confirmacion(
            `¿Esta seguro que desea eliminar esta Categoría?`,
            `Se eliminará del catálogo.`,
            `warning`,
            (param) => {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation/',
                    {
                        typeOperation:`delete`,
                        operation: `categoria`,
                        id: tableData
                    },
                    (data) => {
                        if (data=="success") {
                            mensaje_do_aceptar(`Operación completada`,`Categoría eliminada con éxito`,`success`,() => {
                                $(`#tblCatagoria`).DataTable().ajax.reload(null,false);
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
    
        $('#tblCatagoria thead tr#filterboxrow th').each(function(index) {
            if(index==1) {
                var title = $('#tblCatagoria thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblCatagoria.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblCatagoria = $('#tblCatagoria').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableCategorias",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoSolicitud": 'categoria'
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