<?php 
    @session_start();

    $paisDepartamentoId = $_POST["paisDepartamentoId"];
    $pageDepartamento = "paisId=$_POST[paisId]&pais=$_POST[pais]";

    $jsonMunicipio = array(
        "typeOperation"             => "insert",
        "paisDepartamentoId"        => $paisDepartamentoId,
        "tituloModal"               => "Nuevo municipio"  
    );
?>
<h2>
    Municipios del departamento: <?php echo $_POST["departamentoPais"]; ?>
</h2>
<hr>
<div class="row mb-4">
    <div class="col-6">
        <button type="button" class="btn btn-secondary ttip" onclick="changePage(`<?php echo $_SESSION['currentRoute']; ?>`, `paises-departamentos`, `<?php echo $pageDepartamento; ?>`);">
            <i class="fas fa-chevron-circle-left"></i>
            Volver a departamentos
            <span class="ttiptext">Volver a departamentos</span>
        </button>
    </div>
    <div class="col-6 text-end">
        <button type="button" class="btn btn-primary ttip" onclick="modalMunicipio(<?php echo htmlspecialchars(json_encode($jsonMunicipio)); ?>);">
            <i class="fas fa-plus-circle"></i> 
            Nuevo Municipio                
            <span class="ttiptext">Nuevo municipio</span>
        </button>
    </div>
</div>
<div class="table-responsive">
    <table id="tblMunicipios" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Municipio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
        function modalMunicipio(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: frmData.tituloModal,
                modalForm: 'paisMunicipio',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarMunicipio(frmData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar el Municipio?`, 
            `Ya no podrá seleccionarse en otras operaciones.`, 
            `warning`, 
            function(param) {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    frmData,
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Municipio eliminado con éxito`, `success`, function() {
                                $("#tblMunicipios").DataTable().ajax.reload(null, false);
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
        $('#tblMunicipios thead tr#filterboxrow th').each(function(index) {
            if(index == 1) {
                var title = $('#tblMunicipios thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblMunicipios.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblMunicipios = $('#tblMunicipios').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePaisMunicipios",
                "data": {
                   "paisDepartamentoId": '<?php echo $paisDepartamentoId; ?>',
                   "departamentoPais": '<?php echo $_POST["departamentoPais"]; ?>'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "10%"},
                null,
                {"width": "15%"}
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