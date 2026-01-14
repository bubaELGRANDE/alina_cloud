<?php 
    @session_start();
?>
<h2>Amonestaciones</h2>
<hr>

<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1" role="tab" aria-controls="ntab-content-1" aria-selected="true">
            Vigentes
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2" role="tab" aria-controls="ntab-content-3" aria-selected="false">
            Anuladas
        </a>
    </li>
</ul>
<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">
        <div class="row">
            <div class="col text-end">
                <?php
                    $jsonAmonestacion = array(
                        "typeOperation"                 => "insert",
                        "expedienteAmonestacionId"      => 0
                    );
                ?>
                <button id="btn" type="button" class="btn btn-info" onclick="modalReportesAmonestacion({expedienteAmonestacionId: 0});"><i class="fas fa-print"></i> Reportes</button>
                <button type="button" class="btn btn-primary" onclick="modalAmonestacion(<?php echo htmlspecialchars(json_encode($jsonAmonestacion)); ?>);"><i class="fas fa-plus-circle"></i> Nueva amonestación</button>
            </div>
            <div class="col-md-12">
                <div class="table-responsive">
                    <table id="tblAmonestaciones" class="table table-hover" style="width: 100%;">
                        <thead>
                            <tr id="filterboxrow-activos">
                                <th>#</th>
                                <th>Amonestaciones</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-content-2" role="tabpanel" aria-labelledby="ntab-2">
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table id="tblAmonestacionesAnuladas" class="table table-hover" style="width: 100%;">
                        <thead>
                            <tr id="filterboxrow-anulados">
                                <th>#</th>
                                <th>Amonestaciones</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>    
//Me quedé en lo del reporte, ver reportesQuedan 

    function modalReportesAmonestacion(idQ) {
            loadModal(
                "modal-container",
                {
                    modalDev: "-1",
                    modalSize: 'fullscreen',
                    modalTitle: `Reportes de Amonestaciones`,
                    modalForm: 'reportesAmonestaciones',
                    formData: idQ,
                    buttonResetShow: true,
                    buttonAcceptShow: true,
                    buttonAcceptText: 'Generar reporte',
                    buttonAcceptIcon: 'print',
                    buttonCancelShow: true,
                    buttonCancelText: 'Cerrar'
                }
            );
        }
    function modalAmonestacion(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: `Nueva amonestación`,
                modalForm: 'expedienteAmonestacion',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalVerAmonestacion(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Amonestación`,
                modalForm: 'verAmonestaciones',
                formData: formData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalAnularAmonestacion(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Anular amonestación`,
                modalForm: 'anularAmonestacion',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    $(document).ready(function() {
        // Tab: Activos
        $('#tblAmonestaciones thead tr#filterboxrow-activos th').each(function(index) {
            if(index == 1 || index == 2) {
                var title = $('#tblAmonestaciones thead tr#filterboxrow-activos th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}activos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}activos">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblAmonestaciones.column($(this).index()).search($(`#input${$(this).index()}activos`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblAmonestaciones = $('#tblAmonestaciones').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableExpedienteAmonestaciones",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Activo'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                {"width": "30%"},
                {"width": "50%"},
                null,
            ],
            "columnDefs": [
                { "orderable": false, "targets": [2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Anuladas
        $('#tblAmonestacionesAnuladas thead tr#filterboxrow-anulados th').each(function(index) {
            if(index == 1 || index == 2) {
                var title = $('#tblAmonestacionesAnuladas thead tr#filterboxrow-anulados th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}anulados" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}anulados">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblAmonestacionesAnuladas.column($(this).index()).search($(`#input${$(this).index()}anulados`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblAmonestacionesAnuladas = $('#tblAmonestacionesAnuladas').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableExpedienteAmonestaciones",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Anulado'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                {"width": "30%"},
                {"width": "50%"},
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