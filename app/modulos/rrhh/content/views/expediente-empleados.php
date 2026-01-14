<?php
@session_start();
/* 
    0 = Tab Activos
    1 = Tab Activos contenido
    2 = Tab Inactivos
    3 = Tab Inactivos contenido
    4 = Tab Bajas
    5 = Tab Bajas contenido
    6 = Tab Contratos Finalizados
    7 = Tab Contratos Finalizados contenido
    8 = Tab Jubilados
    9 = Tab Jubilados contenido
*/
$arrayTabs = array("", "", "", "", "", "", "", "", "", "");

switch ($_POST["urlVariables"]) {
    case "Pendiente":
        $arrayTabs[2] = "active";
        $arrayTabs[3] = "show active";
        break;

    case "Finalizado":
        $arrayTabs[6] = "active";
        $arrayTabs[7] = "show active";
        break;

    case "Jubilado":
        $arrayTabs[8] = "active";
        $arrayTabs[9] = "show active";
        break;

    case "Despido":
        $arrayTabs[4] = "active";
        $arrayTabs[5] = "show active";
        break;

    case "Abandono":
        $arrayTabs[4] = "active";
        $arrayTabs[5] = "show active";
        break;

    case "Defunción":
        $arrayTabs[4] = "active";
        $arrayTabs[5] = "show active";
        break;

    case "Renuncia":
        $arrayTabs[4] = "active";
        $arrayTabs[5] = "show active";
        break;

    case "Traslado":
        $arrayTabs[4] = "active";
        $arrayTabs[5] = "show active";
        break;

    default: // Activo o 0 (sidebar)
        $arrayTabs[0] = "active";
        $arrayTabs[1] = "show active";
        break;
}
?>
<h2>
    Expediente de Empleados
</h2>
<hr>
<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php echo $arrayTabs[0]; ?>" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1"
            role="tab" aria-controls="ntab-content-1" aria-selected="true">
            Activos
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php echo $arrayTabs[2]; ?>" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2"
            role="tab" aria-controls="ntab-content-2" aria-selected="false">
            Pendientes
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php echo $arrayTabs[4]; ?>" id="ntab-3" data-mdb-toggle="pill" href="#ntab-content-3"
            role="tab" aria-controls="ntab-content-3" aria-selected="false">
            Bajas
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php echo $arrayTabs[6]; ?>" id="ntab-4" data-mdb-toggle="pill" href="#ntab-content-4"
            role="tab" aria-controls="ntab-content-4" aria-selected="false">
            Contratos Finalizados
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php echo $arrayTabs[8]; ?>" id="ntab-5" data-mdb-toggle="pill" href="#ntab-content-5"
            role="tab" aria-controls="ntab-content-5" aria-selected="false">
            Jubilados
        </a>
    </li>
</ul>
<div class="tab-content" id="ntab-content">
    <div class="tab-pane fade <?php echo $arrayTabs[1]; ?>" id="ntab-content-1" role="tabpanel"
        aria-labelledby="ntab-1">
        <div class="row">
            <div class="col text-end">
                <button type="button" class="btn btn-info ttip" onclick="modalReportesEmpleado(`expediente^0`);">
                    <i class="fas fa-print"></i> Reportes
                    <span class="ttiptext">Reportes de empleados</span>
                </button>
                <button type="button" class="btn btn-primary" onclick="modalExpediente('nuevo');"><i
                        class="fas fa-plus-circle"></i> Nuevo Expediente</button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="tblExpedientesActivos" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-activos">
                        <th>#</th>
                        <th>Expediente</th>
                        <th>Estado del cargo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade <?php echo $arrayTabs[3]; ?>" id="ntab-content-2" role="tabpanel"
        aria-labelledby="ntab-2">
        <div class="table-responsive">
            <table id="tblExpedientesPendientes" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-pendientes">
                        <th>#</th>
                        <th>Expediente</th>
                        <th>Estado del cargo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade <?php echo $arrayTabs[5]; ?>" id="ntab-content-3" role="tabpanel"
        aria-labelledby="ntab-3">
        <div class="table-responsive">
            <table id="tblExpedientesBajas" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-despedidos">
                        <th>#</th>
                        <th>Expediente</th>
                        <th>Estado del cargo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade <?php echo $arrayTabs[7]; ?>" id="ntab-content-4" role="tabpanel"
        aria-labelledby="ntab-4">
        <div class="table-responsive">
            <table id="tblExpedientesFinalizados" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-finalizados">
                        <th>#</th>
                        <th>Expediente</th>
                        <th>Estado del cargo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade <?php echo $arrayTabs[9]; ?>" id="ntab-content-5" role="tabpanel"
        aria-labelledby="ntab-5">
        <div class="table-responsive">
            <table id="tblExpedientesJubilados" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-renuncias">
                        <th>#</th>
                        <th>Expediente</th>
                        <th>Estado del cargo</th>
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
    function modalReportesEmpleado(data) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'fullscreen',
                modalTitle: `Reportes de empleados`,
                modalForm: 'reportesEmpleado',
                formData: data,
                buttonResetShow: true,
                buttonAcceptShow: true,
                buttonAcceptText: 'Generar reporte',
                buttonAcceptIcon: 'print',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function modalExpediente(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Nuevo Expediente`,
                modalForm: 'expedienteEmpleado',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function modalFechaInicio(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Edición de fecha: Inicio de labores`,
                modalForm: 'editarFechaInicioLabores',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function modalBajaExpediente(tableData) {
        let arrayData = tableData.split("^");
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Baja de empleado: ${arrayData[1]} (${arrayData[2]})`,
                modalForm: 'expedienteDarDeBaja',
                formData: tableData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function modalJefaturaEmpleado(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Jefatura`,
                modalForm: 'expedienteJefaturasEmpleado',
                formData: formData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function eliminarJefatura(id, flg, jefeId) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar al empleado de la jefatura?`,
            `Se eliminará al empleado de la jefatura.`,
            `warning`,
            (param) => {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
                    {
                        typeOperation: `delete`,
                        operation: `expediente-jefatura`,
                        id: id,
                        flg: flg,
                        jefeId: jefeId
                    },
                    (data) => {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                `Operación completada`,
                                `Empleado eliminado de la jefatura con éxito`,
                                `success`, () => {
                                    cargarJefaturasEmpleado();
                                }
                            );
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
        )
    }

    function jefeInmediato(id, flg) {
        asyncDoDataReturn(
            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
            {
                typeOperation: `update`,
                operation: `expediente-jefatura-inmediata`,
                id: id,
                flg: flg
            },
            (data) => {
                if (data == "success") {
                    cargarJefaturasEmpleado();
                } else {
                    mensaje(
                        "Aviso:",
                        data,
                        "warning"
                    );
                }
            }
        );
    }

    function modalHistorialSalario(id, estado) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: `Historial de Salario`,
                modalForm: 'expedienteSalarios',
                formData: id,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function modalHorarios(id, estado) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Horarios de trabajo`,
                modalForm: 'expedienteHorarios',
                formData: id,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function modalCambioSucursal(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: `Cambio de sucursal/departamento - Empleado: ${frmData.nombreCompleto}`,
                modalForm: 'expedienteCambioSucursal',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    $(document).ready(function () {
        // Tab: Activos
        $('#tblExpedientesActivos thead tr#filterboxrow-activos th').each(function (index) {
            if (index == 1 || index == 2) {
                var title = $('#tblExpedientesActivos thead tr#filterboxrow-activos th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}activos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}activos">Buscar</label></div>${title}`);
                $(this).on('keyup change', function () {
                    tblExpedientesActivos.column($(this).index()).search($(`#input${$(this).index()}activos`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblExpedientesActivos = $('#tblExpedientesActivos').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableExpedientes",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Activo'
                }
            },
            "rowReorder": true,
            "autoWidth": false,
            "columns": [
                null,
                { "width": "45%" },
                { "width": "25%" },
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Pendientes
        $('#tblExpedientesPendientes thead tr#filterboxrow-pendientes th').each(function (index) {
            if (index == 1 || index == 2) {
                var title = $('#tblExpedientesPendientes thead tr#filterboxrow-pendientes th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}pendientes" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}pendientes">Buscar</label></div>${title}`);
                $(this).on('keyup change', function () {
                    tblExpedientesPendientes.column($(this).index()).search($(`#input${$(this).index()}pendientes`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblExpedientesPendientes = $('#tblExpedientesPendientes').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableExpedientes",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Pendiente'
                }
            },
            "autoWidth": false,
            "columns": [
                { "width": "5%" },
                { "width": "55%" },
                { "width": "30%" },
                null,
            ],
            "columnDefs": [
                { "orderable": false, "targets": [2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Bajas
        $('#tblExpedientesBajas thead tr#filterboxrow-despedidos th').each(function (index) {
            if (index == 1 || index == 2) {
                var title = $('#tblExpedientesBajas thead tr#filterboxrow-despedidos th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}despedidos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}despedidos">Buscar</label></div>${title}`);
                $(this).on('keyup change', function () {
                    tblExpedientesBajas.column($(this).index()).search($(`#input${$(this).index()}despedidos`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblExpedientesBajas = $('#tblExpedientesBajas').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableExpedientes",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Baja'
                }
            },
            "autoWidth": false,
            "columns": [
                { "width": "5%" },
                { "width": "45%" },
                { "width": "30%" },
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Contratos Finalizados
        $('#tblExpedientesFinalizados thead tr#filterboxrow-finalizados th').each(function (index) {
            if (index == 1 || index == 2) {
                var title = $('#tblExpedientesFinalizados thead tr#filterboxrow-finalizados th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}finalizados" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}finalizados">Buscar</label></div>${title}`);
                $(this).on('keyup change', function () {
                    tblExpedientesFinalizados.column($(this).index()).search($(`#input${$(this).index()}finalizados`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblExpedientesFinalizados = $('#tblExpedientesFinalizados').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableExpedientes",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Finalizado'
                }
            },
            "autoWidth": false,
            "columns": [
                { "width": "5%" },
                { "width": "55%" },
                { "width": "30%" },
                null,
            ],
            "columnDefs": [
                { "orderable": false, "targets": [2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Jubilados
        $('#tblExpedientesJubilados thead tr#filterboxrow-jubilados th').each(function (index) {
            if (index == 1 || index == 2) {
                var title = $('#tblExpedientesJubilados thead tr#filterboxrow-jubilados th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}jubilados" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}jubilados">Buscar</label></div>${title}`);
                $(this).on('keyup change', function () {
                    tblExpedientesJubilados.column($(this).index()).search($(`#input${$(this).index()}jubilados`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblExpedientesJubilados = $('#tblExpedientesJubilados').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableExpedientes",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Jubilado'
                }
            },
            "autoWidth": false,
            "columns": [
                { "width": "5%" },
                { "width": "55%" },
                { "width": "30%" },
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