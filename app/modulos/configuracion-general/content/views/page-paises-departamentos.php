<?php 
    @session_start();

    $paisId = $_POST["paisId"];
    $jsonDepartamento = array(
        "typeOperation" => "insert",
        "paisId"        => $paisId,
        "tituloModal"   => "Nuevo departamento"  
    );
?>
<h2>
    Departamentos del país: <?php echo $_POST["pais"]; ?>
</h2>
<hr>
<div class="row mb-4">
    <div class="col-6">
        <button type="button" class="btn btn-secondary ttip" onclick="asyncPage(4, 'submenu', '');">
            <i class="fas fa-chevron-circle-left"></i>
            Volver a países
            <span class="ttiptext">Volver a países</span>
        </button>
    </div>
    <div class="col-6 text-end">
        <button type="button" class="btn btn-primary ttip" onclick="modalDepartamento(<?php echo htmlspecialchars(json_encode($jsonDepartamento)); ?>);">
            <i class="fas fa-plus-circle"></i> 
            Nuevo Departamento                
            <span class="ttiptext">Nuevo departamento</span>
        </button>
    </div>
</div>
<div class="table-responsive">
    <table id="tblDepartamentos" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Departamento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    function modalDepartamento(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: frmData.tituloModal,
                modalForm: 'paisDepartamento',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarDepartamento(frmData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar el departamento?`, 
            `Ya no podrá seleccionarse en otras operaciones.`, 
            `warning`, 
            function(param) {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    frmData,
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Departamento eliminado con éxito`, `success`, function() {
                                $("#tblDepartamentos").DataTable().ajax.reload(null, false);
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
        $('#tblDepartamentos thead tr#filterboxrow th').each(function(index) {
            if(index == 1) {
                var title = $('#tblDepartamentos thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblDepartamentos.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblDepartamentos = $('#tblDepartamentos').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePaisDepartamentos",
                "data": {
                   "paisId": '<?php echo $paisId; ?>',
                   "pais": '<?php echo $_POST["pais"]; ?>'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "10%"},
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