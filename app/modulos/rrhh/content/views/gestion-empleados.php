<?php 
	@session_start();

    /* 
        0 = Tab Activos
        1 = Tab Activos contenido
        2 = Tab Inactivos
        3 = Tab Inactivos contenido
    */
    $arrayTabs = array("", "", "", "");
    if($_POST["urlVariables"] == "0" || $_POST['urlVariables'] == "activos") { // Default de la function en la sidebar y el valor asignado en page-perfil
        $arrayTabs[0] = "active";
        $arrayTabs[1] = "show active";
    } else { // Inactivos
        $arrayTabs[2] = "active";
        $arrayTabs[3] = "show active";
    }
?>
<h2>
    Gestión de Empleados
</h2>
<hr>
<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php echo $arrayTabs[0]; ?>" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1" role="tab" aria-controls="ntab-content-1" aria-selected="true">
            Empleados Activos
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php echo $arrayTabs[2]; ?>" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2" role="tab" aria-controls="ntab-content-2" aria-selected="false">
            Empleados Inactivos
        </a>
    </li>
</ul>
<div class="tab-content" id="ntab-content">
    <div class="tab-pane fade <?php echo $arrayTabs[1]; ?>" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">
        <div class="row">
            <div class="col text-end">
                <button type="button" class="btn btn-info ttip" onclick="modalReportesEmpleado();">
                    <i class="fas fa-print"></i> Reportes
                    <span class="ttiptext">Reportes de empleados</span>
                </button>
                <button type="button" class="btn btn-primary" onclick="modalEmpleado();"><i class="fas fa-plus-circle"></i> Nuevo Empleado</button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="tblEmpleados" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow">
                        <th>#</th>
                        <th>Empleado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade <?php echo $arrayTabs[3]; ?>" id="ntab-content-2" role="tabpanel" aria-labelledby="ntab-2">
        <div class="table-responsive">
            <table id="tblEmpleadosInactivos" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-inactivos">
                        <th>#</th>
                        <th>Empleado</th>
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
<script>
    function modalEmpleado() {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: `Nuevo empleado`,
                modalForm: 'empleado',
                formData: 'nuevo^0',
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalContactosEmpleado(tableData) {
        // id, nombrePersona
        let arrayData = tableData.split("^");
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Contactos del empleado: ${arrayData[1]}`,
                modalForm: 'contactosEmpleado',
                formData: tableData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalRelacionEmpleado(tableData) {
        // id, nombrePersona
        let arrayData = tableData.split("^");
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Núcleo familiar del empleado: ${arrayData[1]}`,
                modalForm: 'empleadoNucleoFamiliar',
                formData: tableData,
                /* buttonAcceptShow: false,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save', */
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalRecontratacionEmpleado(tableData) {
        // id
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: `Recontratación de empleado`,
                modalForm: 'empleadoRecontratacion',
                formData: tableData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function modalEmpleadoCuentasBanco(tableData) {
        // id, nombrePersona
        let arrayData = tableData.split("^");
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Cuentas bancarias del empleado: ${arrayData[1]}`,
                modalForm: 'empleadoCuentasBancarias',
                formData: tableData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalReportesEmpleado() {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'fullscreen',
                modalTitle: `Reportes de empleados`,
                modalForm: 'reportesEmpleado',
                formData: 'nuevo^0',
                buttonResetShow: true,
                buttonAcceptShow: true,
                buttonAcceptText: 'Generar reporte',
                buttonAcceptIcon: 'print',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalBajaEmpleado(tableData) {
        let arrayData = tableData.split("^");
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Baja de empleado: ${arrayData[0]}`,
                modalForm: 'empleadoDarDeBaja',
                formData: tableData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    $(document).ready(function() {
        // Tab: Empleados Activos
        $('#tblEmpleados thead tr#filterboxrow th').each(function(index) {
            if(index==1 || index==2) {
                var title = $('#tblEmpleados thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpleados.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblEmpleados = $('#tblEmpleados').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleados",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Activo'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "40%"},
                {"width": "30%"},
                {"width": "25%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Empleados Inactivos
        $('#tblEmpleadosInactivos thead tr#filterboxrow-inactivos th').each(function(index) {
            if(index==1 || index==2) {
                var title = $('#tblEmpleadosInactivos thead tr#filterboxrow-inactivos th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}inactivos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}inactivos">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpleadosInactivos.column($(this).index()).search($(`#input${$(this).index()}inactivos`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblEmpleadosInactivos = $('#tblEmpleadosInactivos').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleados",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Inactivo'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "40%"},
                {"width": "30%"},
                {"width": "20%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>