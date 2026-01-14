<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<h2>
    Parametrización: Panel de ventas
</h2>
<hr>
<div class="row mb-4">
    <div class="col-md-3">
        <button type="button" id="btnVolverDashboard" class="btn btn-secondary btn-block ttip">
            <i class="fas fa-chevron-circle-left"></i>
            Panel de ventas
            <span class="ttiptext">Volver al panel de ventas</span>
        </button>
    </div>
</div>
<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="ntab-modal-1" data-mdb-toggle="pill" href="#ntab-modal-content-1" role="tab" aria-controls="ntab-modal-content-1" aria-selected="true">
            Sucursales
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-modal-2" data-mdb-toggle="pill" href="#ntab-modal-content-2" role="tab" aria-controls="ntab-modal-content-2" aria-selected="false">
            Marcas
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-modal-3" data-mdb-toggle="pill" href="#ntab-modal-content-3" role="tab" aria-controls="ntab-modal-content-3" aria-selected="false">
            Unidades de negocio
        </a>
    </li>
</ul>
<div class="tab-content" id="ntab-content">
    <div class="tab-pane fade show active" id="ntab-modal-content-1" role="tabpanel" aria-labelledby="ntab-modal-1">
        <div class="row">
            <div class="col-4 offset-8 mb-4">
                <button type="button" class="btn btn-primary btn-block ttip" onclick="modalParametrizacion('insert^0^sucursal');">
                    <i class="fas fa-plus-circle"></i>
                    Sucursal
                    <span class="ttiptext">Parametrizar una nueva sucursal</span>
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="tblParamSucursal" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-sucursal">
                        <th>#</th>
                        <th>Sucursal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-modal-content-2" role="tabpanel" aria-labelledby="ntab-modal-2">
        <div class="row">
            <div class="col-4 offset-8 mb-4">
                <button type="button" class="btn btn-primary btn-block ttip" onclick="modalParametrizacion('insert^0^marca');">
                    <i class="fas fa-plus-circle"></i>
                    Marca
                    <span class="ttiptext">Parametrizar una nueva marca</span>
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="tblParamMarca" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-marca">
                        <th>#</th>
                        <th>Marca</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-modal-content-3" role="tabpanel" aria-labelledby="ntab-modal-3">
        <div class="row">
            <div class="col-4 offset-8 mb-4">
                <button type="button" class="btn btn-primary btn-block ttip" onclick="modalParametrizacion('insert^0^udn');">
                    <i class="fas fa-plus-circle"></i>
                    Unidad de negocio
                    <span class="ttiptext">Parametrizar una nueva unidad de negocio</span>
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="tblParamUDN" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-udn">
                        <th>#</th>
                        <th>Unidad de negocio</th>
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
    function modalParametrizacion(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Parametrización`,
                modalForm: 'parametrizacionVentas',
                formData: `${formData}`,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function modalParametrizacionDetalle(formData, tituloModal) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `${tituloModal}`,
                modalForm: 'parametrizacionVentasDetalle',
                formData: `${formData}`,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function eliminarParametrizacion(id, table, operation) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar esta parametrización?`, 
            `Los registros relacionados a este en el panel de ventas no serán mostrados.`, 
            `warning`, 
            function(param) {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    {
                        typeOperation: `delete`,
                        operation: `${operation}`,
                        id: id
                    },
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Parametrización eliminada con éxito`, `success`, function() {
                                $(`#${table}`).DataTable().ajax.reload(null, false);
                                if(operation == "parametrizacion-ventas-detalle") {
                                    $('#tblParamDetalle').DataTable().ajax.reload(null, false);
                                } else {
                                    // Solo el ajax anterior
                                }
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
        $("#btnVolverDashboard").click(function(e) {
            // id 37 de tbl menus, no se puede usar changePage porque es exclusiva para "page-"
            asyncPage(37, 'submenu', '');
        });

        // Tab: Sucursales
        $('#tblParamSucursal thead tr#filterboxrow-sucursal th').each(function(index) {
            if(index==1 || index == 2) {
                var title = $('#tblParamSucursal thead tr#filterboxrow-sucursal th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}sucursal" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}sucursal">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblParamSucursal.column($(this).index()).search($(`#input${$(this).index()}sucursal`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblSucursal = $('#tblParamSucursal').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableParametrizacionVentas",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoParametrizacion": 'Sucursal'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                {"width": "25%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Marcas
        $('#tblParamMarca thead tr#filterboxrow-marca th').each(function(index) {
            if(index==1 || index == 2) {
                var title = $('#tblParamMarca thead tr#filterboxrow-marca th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}marca" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}marca">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblParamMarca.column($(this).index()).search($(`#input${$(this).index()}marca`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblMarca = $('#tblParamMarca').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableParametrizacionVentas",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoParametrizacion": 'Marca'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                {"width": "25%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Unidades de negocio
        $('#tblParamUDN thead tr#filterboxrow-udn th').each(function(index) {
            if(index==1 || index == 2) {
                var title = $('#tblParamUDN thead tr#filterboxrow-udn th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}udn" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}udn">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblParamUDN.column($(this).index()).search($(`#input${$(this).index()}udn`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblUDN = $('#tblParamUDN').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableParametrizacionVentas",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoParametrizacion": 'Unidad de negocio'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                {"width": "25%"}
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