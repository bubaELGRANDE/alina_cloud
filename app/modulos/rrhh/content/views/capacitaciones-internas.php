<?php 
    @session_start();


    $jsonAgregar = array(
        "typeOperation" => "insert",
        "tituloModal"   => "Nueva capacitación"  
    );
    $funcionAgregar = htmlspecialchars(json_encode($jsonAgregar));
?>
<h2>
    Capacitaciones Internas
</h2>
<hr>
<div class="text-end">
    <button type="button" class="btn btn-info ttip" onclick="reporteCapacitaciones()">
        <i class="fas fa-print"></i> Reportes
        <span class="ttiptext">Reportes de Capacitaciones</span>
    </button>
    <button type="button" class="btn btn-primary ttip" onclick="modalCapacitacion(<?php echo $funcionAgregar; ?>);">
        <i class="fas fa-plus-circle"></i> 
        Nueva Capacitación                
        <span class="ttiptext">Nueva capacitación</span>
    </button>
</div>
<div class="table-responsive">
    <table id="tblCapacitaciones" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-capacitaciones">
                <th>#</th>
                <th>Empleado</th>
                <th>Capacitación</th>
                <th>Duración</th>
                <th>Costo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    function reporteCapacitaciones(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'fullscreen',
                modalTitle: `Reporte de Capacitaciones`,
                modalForm: 'reporteCapacitaciones',
                formData: frmData,
                buttonResetShow: true,
                buttonAcceptShow: true,
                buttonAcceptText: 'Generar reporte',
                buttonAcceptIcon: 'print',
                buttonAcceptClass: 'primary',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
	}

    function adjuntarArchivoCapacitacionInterna(frmData){
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Adjuntar Archivo: ${frmData.nombreCompleto}`,
                modalForm: 'adjuntarArchivoCapacitacion',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function verAdjuntoCapacitaciones(frmData){
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: `Capacitación adjuntada: ${frmData.nombreCompleto}`,
                modalForm: 'adjuntoCapacitacion',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalCapacitacion(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: frmData.tituloModal,
                modalForm: 'capacitacionInterna',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function eliminarCapacitacion(frmData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar al empleado de esta capacitación?`, 
            `Ya no se reflejará al empleado en la capacitación.`, 
            `warning`, 
            function(param) {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    frmData,
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Empleado eliminado de la capacitación con éxito`, `success`, function() {
                                $("#tblCapacitaciones").DataTable().ajax.reload(null, false);
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
        $('#tblCapacitaciones thead tr#filterboxrow-capacitaciones th').each(function(index) {
            if(index == 1 || index == 2) {
                var title = $('#tblCapacitaciones thead tr#filterboxrow-capacitaciones th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}capacitaciones" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}capacitaciones">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblCapacitaciones.column($(this).index()).search($(`#input${$(this).index()}capacitaciones`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblCapacitaciones = $('#tblCapacitaciones').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableCapacitacionesInternas",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "estado": 'Activo'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                {"width": "20%"},
                {"width": "20%"},
                {"width": "15%"},
                {"width": "20%"},
                {"width": "20%"},
            ],
            "columnDefs": [
                { "orderable": false, "targets": [2, 3, 4, 5] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>