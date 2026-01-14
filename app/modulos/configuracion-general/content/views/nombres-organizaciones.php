<?php 
	@session_start();
?>
<h2>
    Organizaciones
</h2>
<hr>
<div class="row">
    <div class="col text-end">
        <button id="btnNuevaOrganizacion" type="button" class="btn btn-primary" onclick="modalOrganizacion('nuevo');"><i class="fas fa-plus-circle"></i> Nueva Organización</button>
    </div>
</div>
<div class="table-responsive">
    <table id="tblOrganizaciones" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Nombre de la organización</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    function modalOrganizacion(tipo, tableData = "N/A") {
        let formData = ""; modalDev = "-1";
        if(tipo == "editar") {
            formData = 'editar^'+tableData;
            modalDev = "-1";
        } else {
            formData = 'nuevo';
            modalDev = "-1";
        }
        loadModal(
            "modal-container",
            {
                modalDev: -1,
                modalSize: 'md',
                modalTitle: `Organización`,
                modalForm: 'organizacion',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarOrganizacion(tableData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar esta organización?`, 
            `Se eliminará del catálogo.`, 
            `warning`, 
            function(param) {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    {
                        typeOperation: `delete`,
                        operation: `organizacion`,
                        id: tableData
                    },
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Organización eliminada con éxito`, `success`, function() {
                                $(`#tblOrganizaciones`).DataTable().ajax.reload(null, false);
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
            `Eliminar`,
            `Cancelar`
        );
    }

    $(document).ready(function() {
        $('#tblOrganizaciones thead tr#filterboxrow th').each(function(index) {
            if(index==1) {
                var title = $('#tblOrganizaciones thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblOrganizaciones.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblOrganizaciones = $('#tblOrganizaciones').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableListaOrganizaciones",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "x": ''
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
