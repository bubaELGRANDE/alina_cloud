<?php 
@session_start();
?>
<h2>Proveedores</h2>
<hr>
<ul class="nav nav-tabs nav-justified mt-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1" role="tab" aria-controls="ntab-content-1" aria-selected="true">
            LOCALES
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2" role="tab" aria-controls="ntab-content-2" aria-selected="false">
            EXTRANJEROS
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-3" data-mdb-toggle="pill" href="#ntab-content-3" role="tab" aria-controls="ntab-content-3" aria-selected="false">
            SUJETOS EXCLUIDOS
        </a>
    </li>
</ul>
<div class="tab-content mt-3" id="ntab-content">
    <!-- TAB LOCALES -->
    <div class="tab-pane fade show active" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">
        <div class="row">
            <div class="col text-end">
                <?php 
                    if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(114, $_SESSION["arrayPermisos"])) { 
                        $jsonAgregarLocal = array(
                            "typeOperation" => "insert",
                            "tituloModal"   => "Nuevo proveedor",
                            "tipoProveedor" => "Local"
                        );
                        $funcionAgregarLocal = htmlspecialchars(json_encode($jsonAgregarLocal));
                ?>
                        <button id="btn" type="button" class="btn btn-primary" onclick="modalProveedorN(<?php echo $funcionAgregarLocal; ?>);">
                            <i class="fas fa-plus-circle"></i> Nuevo proveedor local
                        </button>
                <?php 
                    } else {
                        // No tiene permisos
                    }
                ?>
            </div>
        </div>
        <hr>
        <form id="frmFiltros">
            <div class="row" >
                <div class="col-md-4">
                    <div class="form-outline mb-4">
                        <input type="text" id="filtroNrc" class="form-control" name="filtroNrc"/>
                        <label class="form-label" for="filtroNrc">NRC</label>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-outline mb-4">
                        <input type="text" id="filtroNumDocumento" class="form-control" name="filtroNumDocumento"/>
                        <label class="form-label" for="filtroNumDocumento">NIT o DUI</label>
                    </div>
                </div>
            </div>
            
            <div class="row" >
                <div class="col-md-12">
                    <div class="form-outline mb-4">
                        <input type="text" id="filtroNombreRazonSocial" class="form-control" name="filtroNombreRazonSocial"/>
                        <label class="form-label" for="filtroNombreRazonSocial">Nombre o razón social</label>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-6">
                    <button type="button" id="btnLimpiarFiltros" class="btn btn-secondary">
                        <i class="fas fa-undo-alt"></i> Limpiar
                    </button>
                </div>
                <div class="col-6 text-end">
                    <button type="submit" id="btnBuscarProveedor" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar proveedor
                    </button>
                </div>
            </div>
        </form>
        <!-- Tabla Proveedores Locales -->
        <div class="table-responsive">
            <table id="tblProveedores" class="table table-hover mt-3" style="width: 100%;">
            <thead>
                    <tr id="filterboxrow-tblProveedores">
                        <th>#</th>
                        <th>Proveedor</th>
                        <th>Nombres</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <!-- ... -->
            </table>
        </div>
    </div>

    <!-- TAB EXTRANJEROS -->
    <div class="tab-pane fade mt-3" id="ntab-content-2" role="tabpanel" aria-labelledby="ntab-2">
        <div class="row">
            <div class="col text-end">
                <?php 
                    if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(114, $_SESSION["arrayPermisos"])) { 
                        $jsonAgregarExtranjero = array(
                            "typeOperation" => "insert",
                            "tituloModal"   => "Nuevo proveedor",
                            "tipoProveedor" => "Extranjero"
                        );
                        $funcionAgregarExtranjero = htmlspecialchars(json_encode($jsonAgregarExtranjero));
                ?>
                        <button id="btn" type="button" class="btn btn-primary" onclick="modalProveedorN(<?php echo $funcionAgregarExtranjero; ?>);">
                            <i class="fas fa-plus-circle"></i> Nuevo proveedor extranjero
                        </button>
                <?php 
                    } else {
                        // No tiene permisos
                    }
                ?>
            </div>
        </div>
        <hr>
        <!-- Tabla Proveedores Extranjeros -->
        <div class="table-responsive">
            <table id="tblProveedoresInactivos" class="table table-hover mt-3" style="width: 100%;">
            <thead>
                    <tr id="filterboxrow-tblProveedores">
                        <th>#</th>
                        <th>Proveedor</th>
                        <th>Nombres</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <!-- ... -->
            </table>
        </div>
    </div>

    <!-- TAB SUJETOS EXCLUIDOS -->
    <div class="tab-pane fade mt-3" id="ntab-content-3" role="tabpanel" aria-labelledby="ntab-3">
        <div class="row">
            <div class="col text-end">
                <?php 
                    if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(114, $_SESSION["arrayPermisos"])) { 
                        $jsonAgregarExcluido = array(
                            "typeOperation" => "insert",
                            "tituloModal"   => "Nuevo sujeto excluido",
                            "tipoProveedor" => "Sujeto Excluido"
                        );
                        $funcionAgregarExcluido = htmlspecialchars(json_encode($jsonAgregarExcluido));
                ?>
                        <button id="btn" type="button" class="btn btn-primary" onclick="modalProveedorExcluido(<?php echo $funcionAgregarExcluido; ?>);">
                            <i class="fas fa-plus-circle"></i> Nuevo sujeto excluido
                        </button>
                <?php 
                    } else {
                        // No tiene permisos
                    }
                ?>
            </div>
        </div>
        <hr>
        <!-- Tabla Proveedores Sujetos Excluidos -->
        <div class="table-responsive">
            <table id="tblProveedoresExcluidos" class="table table-hover mt-3" style="width: 100%;">
            <thead>
                    <tr id="filterboxrow-tblProveedores">
                        <th>#</th>
                        <th>Sujeto Excluido</th>
                        <th>Nombres</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <!-- ... -->
            </table>
        </div>
    </div>
</div>

<script>
    function modalProveedor(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "85^114^115",
                modalSize: "lg",
                modalTitle: frmData.tituloModal,
                modalForm: 'proveedor',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function modalProveedorN(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "85^114^115",
                modalSize: "lg",
                modalTitle: frmData.tituloModal,
                modalForm: 'proveedorN',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function modalProveedorExcluido(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "85^114^115",
                modalSize: "lg",
                modalTitle: frmData.tituloModal,
                modalForm: 'proveedorExcluido',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function modalContactoExcluido(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "85^114^115",
                modalSize: "lg",
                modalTitle: frmData.tituloModal,
                modalForm: 'proveedorcontactoSujetoExcluido',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function proveedorInactivo(frmData) {
        if(frmData.nuevoEstado == "Inactivo") {
            loadModal(
                "modal-container",
                {
                    modalDev: "-1",
                    modalSize: 'md',
                    modalTitle: frmData.tituloModal,
                    modalForm: 'proveedoresInactivos',
                    formData: frmData,
                    buttonAcceptShow: true,
                    buttonAcceptText: 'Cambiar estado',
                    buttonAcceptIcon: 'ban',
                    buttonAcceptClass: 'danger',
                    buttonCancelShow: true,
                    buttonCancelText: 'Cerrar'
                }
            );
        } else {
            mensaje_confirmacion(
            '¿Está seguro que desea cambiar el estado del proveedor?', 
            `Se cambiara el estado del proveedor a activo`, 
            `warning`,  
                (param) => {
                    asyncData(
                        "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                        frmData,
                        (data) => {
                            if(data == "success") {
                                mensaje_do_aceptar(
                                    "Operación completada:",
                                    "El proveedor pasó a estado activo",
                                    "success",
                                    () => {
                                        $(`#tblProveedores`).DataTable().ajax.reload(null, false);
                                        $(`#tblProveedoresInactivos`).DataTable().ajax.reload(null, false);
                                        $(`#tblProveedoresExcluidos`).DataTable().ajax.reload(null, false);
                                        $('#modal-container').modal("hide");
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
                "Sí, habilitar",
                "Cancelar"
            );
	    }
    }
    function contactoUbicacion(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "85^118",
                modalSize: 'lg',
                modalTitle: `Contactos de ubicación`,
                modalForm: 'proveedorContactosUbicacion',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function eliminarProveedor (frmData){
        mensaje_confirmacion(
            `¿Esta seguro que desea eliminar al sujeto excluido: ${frmData.nombreSujeto}?`,
            `Se eliminará del catálogo.`,
            `warning`,
            (param) => {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation/',
                    frmData,
                    (data) => {
                        if (data=="success") {
                            mensaje_do_aceptar(
                                `Operación completada`,
                                `Proveedor: ${frmData.nombreSujeto} eliminado con éxito`,
                                `success`,
                                () => {
                                $(`#tblProveedoresExcluidos`).DataTable().ajax.reload(null,false);
                            });
                        }else{
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

    function modalProveedorCuentasBanco(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "85^139",
                modalSize: 'lg',
                modalTitle: `Cuentas bancarias del proveedor: ${frmData.nombreProveedor}`,
                modalForm: 'proveedorCuentasBancarias',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    $(document).ready(function() {
        //Activos

        $("#btnLimpiarFiltros").click(function(e) {
            $("#filtroNrc").val('');
            $("#filtroNumDocumento").val('');
            $("#filtroNombreRazonSocial").val('');
            $('#tblProveedores').DataTable().ajax.reload(null, false);
        });
        $("#frmFiltros").validate({
            submitHandler: function(form) {
                $('#tblProveedores').DataTable().ajax.reload(null, false);
            }
        });

        let tblProveedores = $('#tblProveedores').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableProveedores",
                "data": function() { // En caso que se quiera enviar variable a la consulta
                    return {
                        "estadoProveedor": 'Activo',
                        "tipoProveedor" : 'Local',
                        "nrcProveedor": $("#filtroNrc").val(),
                        "numeroDocumento": $("#filtroNumDocumento").val(),
                        "nombreRazonSocial": $("#filtroNombreRazonSocial").val()
                    }
                }

            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "20%"},
                {"width": "30%"},
                {"width": "30%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3, 4] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });      
        //Inactivos
            $('#tblProveedoresInactivos thead tr#filterboxrow-tblProveedoresInactivos th').each(function(index) {
                if(index == 1  || index == 2 || index == 3){
                    var title = $('#tblProveedoresInactivos thead tr#filterboxrow-tblProveedoresInactivos th').eq($(this).index()).text();
                        $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}tblProveedoresInactivos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}tblProveedoresInactivos">Buscar</label></div>${title}`);
                        $(this).on('keyup change', function() {
                            tblProveedoresInactivos.column($(this).index()).search($(`#input${$(this).index()}tblProveedoresInactivos`).val()).draw();
                    });
                    document.querySelectorAll('.form-outline').forEach((formOutline) => {
                        new mdb.Input(formOutline).init();
                    });
                }else{
                }
            });
            let tblProveedoresInactivos = $('#tblProveedoresInactivos').DataTable({
                "dom": '<"top"lf>rt<"bottom"ip>',
                "ajax": {
                    "method": "POST",
                    "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableProveedores",
                    "data": {
                        "estadoProveedor": 'Activo',
                        "tipoProveedor" : 'Extranjero'
                    }
                },
                "rowReorder": true,
                "autoWidth": false,
                "columns": [
                    null,
                {"width": "20%"},
                {"width": "30%"},
                {"width": "30%"},
                null
                ],
                "columnDefs": [
                    { "orderable": false, "targets": [1, 2, 3, 4 ] }
                ],
                "language": {
                    "url": "../libraries/packages/js/spanish_dt.json"
                }
            });	
        //Excluidos
            $('#tblProveedoresExcluidos thead tr#filterboxrow-tblProveedoresExcluidos th').each(function(index) {
                if(index == 1  || index == 2 || index == 3){
                    var title = $('#tblProveedoresExcluidos thead tr#filterboxrow-tblProveedoresExcluidos th').eq($(this).index()).text();
                        $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}tblProveedoresExcluidos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}tblProveedoresExcluidos">Buscar</label></div>${title}`);
                        $(this).on('keyup change', function() {
                            tblProveedoresExcluidos.column($(this).index()).search($(`#input${$(this).index()}tblProveedoresExcluidos`).val()).draw();
                    });
                    document.querySelectorAll('.form-outline').forEach((formOutline) => {
                        new mdb.Input(formOutline).init();
                    });
                }else{
                }
            });
            let tblProveedoresExcluidos = $('#tblProveedoresExcluidos').DataTable({
                "dom": '<"top"lf>rt<"bottom"ip>',
                "ajax": {
                    "method": "POST",
                    "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableProveedoresExcluidos",
                    "data": {
                        "estadoProveedor": 'Activo',
                        "tipoProveedor" : 'Excluido'
                    }
                },
                "rowReorder": true,
                "autoWidth": false,
                "columns": [
                    null,
                {"width": "20%"},
                {"width": "30%"},
                {"width": "30%"},
                null
                ],
                "columnDefs": [
                    { "orderable": false, "targets": [1, 2, 3, 4] }
                ],
                "language": {
                    "url": "../libraries/packages/js/spanish_dt.json"
                }
            });	
    });
</script>