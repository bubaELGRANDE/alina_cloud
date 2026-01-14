<?php 
	@session_start();
?>
<h2>Programa de vacaciones individuales</h2>
<hr>
<div class="text-end mb-4">
    <button type="button" class="btn btn-info ttip" onclick="reporteVacaciones();">
        <i class="fas fa-print"></i> Reportes
        <span class="ttiptext">Reportes de vacaciones</span>
    </button>
    <button class="btn btn-primary ttip" onclick="administrarVacaciones();">
    	<i class="fas fa-user-cog"></i> Cambiar plan de vacaciones
        <span class="ttiptext">Cambiar plan de vacaciones de un empleado</span>
    </button>
</div>
<div class="row">
    <div class="col-6 offset-3">
        <div class="form-outline mb-4">
            <i class="fas fa-search trailing"></i>
            <input type="text" id="buscarVacacion" class="form-control" name="buscarVacacion" required />
            <label class="form-label" for="buscarVacacion">Buscar</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="tab-content" id="v-tabs-tabContent">
            <ul class="nav nav-tabs nav-justified mb-3" id="ex1" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="activos-tab" data-mdb-toggle="tab" href="#activos" role="tab" aria-controls="activos" aria-selected="true">
                        Individuales
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="inactivos-tab" data-mdb-toggle="tab" href="#inactivos" role="tab" aria-controls="inactivos" aria-selected="false">
                        Individuales: Remunerados
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="ex1-content">
                <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
                    <div id="divVacacionesActivos" class="row mb-4 mt-4">
                    </div>
                </div>
                <div class="tab-pane fade" id="inactivos" role="tabpanel" aria-labelledby="inactivos-tab">
                    <div id="divVacacionesInactivos" class="row mb-4 mt-4">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function reporteVacaciones(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'fullscreen',
                modalTitle: `Reporte de vacaciones`,
                modalForm: 'reporteVacaciones',
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

    function administrarVacaciones(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Administrar vacaciones`,
                modalForm: 'administrarVacaciones',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Cambiar plan',
                buttonAcceptClass: 'primary',
                buttonAcceptIcon: 'retweet',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
	}
    function cargarVacaciones(estadoVacacion) {
        asyncData(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/divVacacionesEmpleados", 
            {
                estadoVacacion: estadoVacacion
            },
            function(data) {
                if(estadoVacacion == "Activo") {
                    $("#divVacacionesActivos").html(data);
                } else {
                    $("#divVacacionesInactivos").html(data);
                }
            }
        );
    }

    function verDiasSolicitados(expedienteId) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: `Ver días solicitados`,
                modalForm: 'verDiasSolicitados',
                formData: {
                    prsExpedienteId: expedienteId
                },
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function cambiarEstadoVacacion(data) {
        mensaje_confirmacion(
            '¿Está seguro que desea cambiar el estado de las vacaciones?', 
            `Se cambiará el estado de las vacaciones del empleado a ${data.estadoVacacion}`, 
            `warning`, 
            function(param) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    data,
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                "Se actualizaron las vacaciones del empleado",
                                "success",
                                function() {
                                    cargarVacaciones('Activo');
                                    cargarVacaciones('Inactivo');
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
            'Sí, cambiar',
            `Cancelar`
        );
    }

    function eliminarPeriodoVacacion(frmData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar el periodo de vacaciones del año ${frmData.anio} de ${frmData.nombreCompleto}?`, 
            `Se eliminará su periodo de vacaciones y restarán los días disponibles de vacación.`, 
            `warning`, 
            function() {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    frmData,
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                "Periodo de vacaciones del empleado eliminado con éxito",
                                "success",
                                function() {
                                    cargarVacaciones('Activo');
                                    cargarVacaciones('Inactivo');
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
            `Sí, eliminar periodo`,
            `Cancelar`
        );
    }

    $(document).ready(function() {
        cargarVacaciones('Activo');
        cargarVacaciones('Inactivo');

        $("#buscarVacacion").on("keyup", function() {
            $(".buscarVacacion").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf($("#buscarVacacion").val().toLowerCase()) > -1)
            });
        });
    });
</script>