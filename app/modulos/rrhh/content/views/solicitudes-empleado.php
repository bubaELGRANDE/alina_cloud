<?php 
	@session_start();
?>

<h2>
    Solicitudes de empleados
</h2>
<hr>
<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1" role="tab" aria-controls="ntab-content-1" aria-selected="true">
            Incapacidades
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2" role="tab" aria-controls="ntab-content-2" aria-selected="false">
            Ausencias
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-3" data-mdb-toggle="pill" href="#ntab-content-3" role="tab" aria-controls="ntab-content-3" aria-selected="false">
            Vacaciones
        </a>
    </li>
</ul>
<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">
        <div class="row">
            <div class="col text-end">
                <?php 
                    $jsonReporte = [
                        "modalForm"            => "reportesIncapacidades",
                        "modalTitle"           => "Reportes de incapacidades"
                    ];
                ?>
                <button type="button" class="btn btn-info ttip" onclick="modalReportes(<?php echo htmlspecialchars(json_encode($jsonReporte)); ?>);">
                    <i class="fas fa-print"></i> Reportes
                    <span class="ttiptext">Reportes de incapacidades</span>
                </button>
                <button type="button" class="btn btn-primary" onclick="modalIncapacidad('insert');"><i class="fas fa-plus-circle"></i> Nueva incapacidad</button>
            </div>
            <div class="col-12">
                <div class="table-responsive">
                    <table id="tblIncapacidad" class="table table-hover" style="width: 100%;">
                        <thead>
                            <tr id="filterboxrow-incapacidad">
                                <th>#</th>
                                <th>Empleado</th>
                                <th>Motivo de incapacidad</th>
                                <th>Vigencia</th>
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
            <div class="col text-end">
                <button type="button" class="btn btn-primary" onclick="modalSolicitud('nuevo');"><i class="fas fa-plus-circle"></i> Nueva ausencia</button>
            </div>
            <div class="col-md-12">
                <div class="table-responsive">
                    <table id="tblAusencia" class="table table-hover" style="width: 100%;">
                        <thead>
                            <tr id="filterboxrow-ausencias">
                                <th>#</th>
                                <th>Empleado</th>
                                <th>Motivo ausencia</th>
                                <th>Vigencia</th>
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
    <div class="tab-pane fade" id="ntab-content-3" role="tabpanel" aria-labelledby="ntab-3">
        <div class="row">
            <div class="col text-end">
                <button type="button" class="btn btn-primary" onclick="modalVacaciones('insert');"><i class="fas fa-plus-circle"></i> Nuevo periodo de vacaciones</button>
            </div>
            <div class="col-md-12">
                <div class="table-responsive">
                    <table id="tblVacacion" class="table table-hover" style="width: 100%;">
                        <thead>
                            <tr id="filterboxrow-vacaciones">
                                <th>#</th>
                                <th>Vacaciones</th>
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
    function anular (formData){
        let arrayData = formData.split('^');
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Anular solicitud de ausencia, empleado:<br> ${arrayData[3]}`,
                modalForm: 'anularSolicitudAusencia',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function modalSolicitud(formData) {
        
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Nueva solicitud de ausencia`,
                modalForm: 'expedienteAusencia',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalIncapacidad(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Nueva solicitud de incapacidad`,
                modalForm: 'expedienteIncapacidades',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalVacaciones(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Nueva solicitud de vacaciones`,
                modalForm: 'expedienteVacaciones',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function anularVacaciones(formData) {
        let arrayData = formData.split('^');
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Anular solicitud de vacaciones, empleado: <br> ${arrayData[4]}`,
                modalForm: 'anularVacaciones',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function verAdjuntoModal(data) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: `Ver archivo adjunto`,
                modalForm: 'adjunto',
                formData: data,
                /* buttonAcceptShow: false,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save', */
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function modalReportes(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'fullscreen',
                modalTitle: frmData.modalTitle,
                modalForm: frmData.modalForm,
                formData: '',
                buttonResetShow: true,
                buttonAcceptShow: true,
                buttonAcceptText: 'Generar reporte',
                buttonAcceptIcon: 'print',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );   
    }

    $(document).ready(function() {
        // Tab: Incapacidades
        $('#tblIncapacidad thead tr#filterboxrow-incapacidad th').each(function(index) {
            if(index == 1 || index == 2 || index == 3) {
                var title = $('#tblIncapacidad thead tr#filterboxrow-incapacidad th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}incapacidad" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}incapacidad">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblIncapacidad.column($(this).index()).search($(`#input${$(this).index()}incapacidad`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblIncapacidad = $('#tblIncapacidad').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableExpedienteIncapacidades",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Activo'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                {"width": "30%"},
                {"width": "30%"},
                {"width": "25%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Ausencias

        $('#tblAusencia thead tr#filterboxrow-ausencias th').each(function(index) {
            if(index == 1 || index == 2 || index == 3) {
                var title = $('#tblAusencia thead tr#filterboxrow-ausencias th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}ausencias" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}ausencias">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblAusencia.column($(this).index()).search($(`#input${$(this).index()}ausencias`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblAusencia = $('#tblAusencia').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableExpedienteAusencias",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Activo'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                {"width": "25%"},
                {"width": "30%"},
                {"width": "25%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1,2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
        //Tab: Vacaciones

        $('#tblVacacion thead tr#filterboxrow-vacaciones th').each(function(index) {
            if(index == 1 || index == 2) {
                var title = $('#tblVacacion thead tr#filterboxrow-vacaciones th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}vacaciones" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}vacaciones">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblVacacion.column($(this).index()).search($(`#input${$(this).index()}vacaciones`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblVacacion = $('#tblVacacion').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableExpedienteVacaciones",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Activo'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                {"width": "40%"},
                {"width": "40%"},
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
