<?php 
    require_once("../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<h2>
    Organigrama
</h2>
<hr>
<div class="row justify-content-end">
    <div class="col-md-3 text-end">
        <button type="button" id="btnPageModulos" class="btn btn-primary" onclick="modalOrganigramaRama(`insert`,0)">
            <i class="fas fa-plus-circle"></i>
            Nueva rama
        </button>
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1" role="tab" aria-controls="ntab-content-1" aria-selected="true">
            Vista gráfica
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2" role="tab" aria-controls="ntab-content-2" aria-selected="false">
            Vista de lista
        </a>
    </li>
</ul>
<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">
        
    </div>
    <div class="tab-pane fade" id="ntab-content-2" role="tabpanel" aria-labelledby="ntab-2">
        <div class="table-responsive">
            <table id="tblOrganigrama" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-renuncias">
                        <th>#</th>
                        <th>Área</th>
                        <th>Área superior</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>




<script>
    function getOrganigrama(personaId) {
        asyncDoDataReturn(
            '<?php echo $_SESSION["currentRoute"]; ?>content/divs/getOrganigrama',
            {
                personaId: personaId
            },
            function(data) {
                $("#ntab-content-1").html(data);
            }
        );  
    }

    function modalOrganigramaRama(operation, id, e) {
        operacion = "";
        if (operation == 'update') {
            operacion = "Editar"
        } else {
            operacion = "Nueva"
        }
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: operacion +` rama de organigrama`,
                modalForm: 'organigramaRama',
                formData: id,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
        //e.preventDefault();
    }
    function eliminarRama(idRama, nombreRama) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar la rama: ${nombreRama}?`, 
            `Se eliminará del organigrama.`, 
            `warning`, 
            function(param) {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    {
                        typeOperation: `delete`,
                        operation: `organigrama-rama`,
                        nombreRama: nombreRama,
                        id: idRama
                    },
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Rama: ${nombreRama} eliminada con éxito`, `success`, function() {
                                $(`#tblOrganigrama`).DataTable().ajax.reload(null, false);
                                getOrganigrama(0);
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
        getOrganigrama(0);
        // Tab: Activos
        $('#tblOrganigrama thead tr#filterboxrow-activos th').each(function(index) {
            if(index == 1 || index == 2) {
                var title = $('#tblOrganigrama thead tr#filterboxrow-activos th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}activos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}activos">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblOrganigrama.column($(this).index()).search($(`#input${$(this).index()}activos`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblOrganigrama = $('#tblOrganigrama').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableOrganigrama",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Activo'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                {"width": "50%"},
                {"width": "30%"},
                null,
            ],
            "columnDefs": [
                { "orderable": false, "targets": [2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>