<?php 
	@session_start();
?>
<h2>
    Catálogos de Inventario
</h2>
<hr>
<div class="row">
    <div class="col-md-3">
        <div class="nav flex-column nav-tabs text-center" id="v-tabs-tab" role="tablist" aria-orientation="vertical">
            <a class="nav-link active" id="v-tabs-1-tab" data-mdb-toggle="tab" href="#v-tabs-1" role="tab" aria-controls="v-tabs-1" aria-selected="true">Tipos de producto</a>
            <!--<a class="nav-link" id="v-tabs-2-tab" data-mdb-toggle="tab" href="#v-tabs-2" role="tab" aria-controls="v-tabs-2" aria-selected="false">Tab 2</a>-->
        </div>
    </div>  
    <div class="col-md-9">
        <div class="tab-content" id="vtabs-mainContent">
            <div class="tab-pane fade show active" id="v-tabs-1" role="tabpanel" aria-labelledby="v-tabs-1-tab">
                <div class="text-end">
                    <button type="button" class="btn btn-primary" onclick="modalOpenForm('insert','Nuevo Tipo de producto','tipoDeProducto','md');">
                        <i class="fas fa-plus-circle"></i> Nuevo Tipo de Producto
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="tblTipoProducto" class="table table-hover" style="width: 100%;">
                        <thead>
                            <tr id="filterboxrow-tipoProducto">
                                <th>#</th>
                                <th>Tipo de  producto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <!--
            <div class="tab-pane fade" id="v-tabs-2" role="tabpanel" aria-labelledby="v-tabs-2-tab">
                Contenido del tab 2
            </div>
            -->
        </div>
    </div>
</div>

<script>
    function modalOpenForm(formData,modalTitle,modalForm,modalSize) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: modalSize,
                modalTitle: modalTitle,
                modalForm: modalForm,
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarTipoProducto (tableData){
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar este Tipo de producto?`,
            `Se eliminará del catálogo.`,
            `warning`,
            (param) => {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
                    {
                        typeOperation:`delete`,
                        operation: `tipo-producto`,
                        id: tableData
                    },
                    (data) => {
                        if (data=="success") {
                            mensaje_do_aceptar(`Operación completada`,`Tipo de producto eliminado con éxito`,`success`,() => {
                                $(`#tblTipoProducto`).DataTable().ajax.reload(null,false);
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

        $('#tblTipoProducto thead tr#filterboxrow-tipoProducto th').each(function(index) {
            if(index==1) {
                var title = $('#tblTipoProducto thead tr#filterboxrow-tipoProducto th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}tipoProducto" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}tipoProducto">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblTipoProducto.column($(this).index()).search($(`#input${$(this).index()}tipoProducto`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblTipoProducto = $('#tblTipoProducto').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableTiposProductos",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoSolicitud": ''
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "70%"},
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
