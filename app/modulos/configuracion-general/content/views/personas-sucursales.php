<?php 
    @session_start();

    $jsonPersonaSucursal = array(
        "tituloModal"   => "Nuevo traslado"
    );
    $funcionPersonaSucursal = htmlspecialchars(json_encode($jsonPersonaSucursal));
    
?>
<h2>
    Permisos operativos de usuarios
</h2>
<hr>
<div class="tab-content mt-3" id="ntab-content">
    <div class="tab-pane fade show active" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">  
        <table id="tblPersonaSucursal" class="table table-hover" style="width: 100%;">
            <thead>
                <tr id="filterboxrow">
                    <th>#</th>
                    <th>Empleado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<script>
    function nuevaPersona(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: "Nueva persona",
                modalForm: 'nuevaPersonaSucursal',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function permisosDTE(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: "Permisos de DTE",
                modalForm: 'permisosDTE',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function sucursalesDTE(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: frmData.tituloModal,
                modalForm: 'sucursalesEmpleado',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function bodegasEmpleado(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: frmData.tituloModal,
                modalForm: 'bodegasEmpleado',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function eliminarPersonaSucursal (frmData){
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar a la persona: ${frmData.nombrePersona} de la sucursal: ${frmData.sucursal}?`,
            `Se eliminará del catalogo.`,
            `warning`,
            (param) => {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
                    frmData,
                    (data) => {
                        if (data=="success") {
                            mensaje_do_aceptar(
                                `Operación completada`,
                                `Persona eliminada con éxito`,
                                `success`,
                                () => {
                                $(`#tblPersonaSucursal`).DataTable().ajax.reload(null,false);
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

    $(document).ready(function() {
        //SOLICITUD POR ENVIAR
        $('#tblPersonaSucursal thead tr#filterboxrow th').each(function(index) {
            if(index==1 || index==2) {
                var title = $('#tblPersonaSucursal thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblPersonaSucursal.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblPersonaSucursal = $('#tblPersonaSucursal').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePersonasSucursales",
                "data": { 
                    
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "10%"},
                null,
                null
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