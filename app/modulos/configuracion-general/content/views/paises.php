<?php 
    @session_start();

    $jsonPais = array(
        "typeOperation" => "insert",
        "tituloModal"   => "Nuevo país"  
    );
?>
<h2>
    Países
</h2>
<hr>
<div class="text-end">
    <button type="button" class="btn btn-primary ttip" onclick="modalPais(<?php echo htmlspecialchars(json_encode($jsonPais)); ?>);">
        <i class="fas fa-plus-circle"></i> 
        Nuevo País                
        <span class="ttiptext">Nuevo país</span>
    </button>
</div>
<div class="table-responsive">
    <table id="tblPaises" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>País</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    function modalPais(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: frmData.tituloModal,
                modalForm: 'pais',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function eliminarPais(frmData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar el país?`, 
            `Ya no podrá seleccionarse en otras operaciones.`, 
            `warning`, 
            function(param) {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    frmData,
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `País eliminado con éxito`, `success`, function() {
                                $("#tblPaises").DataTable().ajax.reload(null, false);
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
            `Sí, eliminar`,
            `Cancelar`
        );
    }
    $(document).ready(function() {
        $('#tblPaises thead tr#filterboxrow th').each(function(index) {
            if(index == 1) {
                var title = $('#tblPaises thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblPaises.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblPaises = $('#tblPaises').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePaises",
                "data": {
                    "x": ''
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1,2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>