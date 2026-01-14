<?php 
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

    $flgMostrarButtons = 1;
    if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(64, $_SESSION["arrayPermisos"])) { // Todos
        $wherePermiso = ""; // Todas las autorizaciones/rechazos
    } else if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(65, $_SESSION["arrayPermisos"])) {
        // Get solicitudes aprobadas/rechazadas por él mismo (usuario session)
        $wherePermiso = "usuarioIdAutoriza = '$_SESSION[usuarioId]' AND";
    } else {
        // No se asignó ningún permiso
        $flgMostrarButtons = 0;
    }

    // WHERE usuarioIdAutoriza = ? AND estadoSolicitud = ? AND tipoSolicitud = ? AND flgDelete = '0'
	$dataHistorialAutorizadas = $cloud->row("
		SELECT
			COUNT(solicitudAccHistorialId) AS autorizadas
		FROM bit_solicitudes_acc_historial
		WHERE $wherePermiso estadoSolicitud = ? AND tipoSolicitud = ? AND flgDelete = '0'
	", ["Autorizada", $_POST["tipoSolicitud"]]);

    // WHERE usuarioIdAutoriza = ? AND estadoSolicitud = ? AND tipoSolicitud = ? AND flgDelete = '0'
    // PARAMS: $_SESSION usuarioId
	$dataHistorialRechazadas = $cloud->row("
		SELECT
			COUNT(solicitudAccHistorialId) AS rechazadas
		FROM bit_solicitudes_acc_historial
		WHERE $wherePermiso estadoSolicitud = ? AND tipoSolicitud = ? AND flgDelete = '0'
	", ["Rechazada", $_POST["tipoSolicitud"]]);

    if($flgMostrarButtons == 1) {
?>
        <div class="text-end mb-4">
            <button type="button" id="btnAutorizadas" class="btn btn-success">
                <span class="badge rounded-pill bg-light" style="color: black;"><?php echo $dataHistorialAutorizadas->autorizadas; ?></span>
                Autorizadas
            </button>
            <button type="button" id="btnRechazadas" class="btn btn-danger">
                <span class="badge rounded-pill bg-light" style="color: black;"><?php echo $dataHistorialRechazadas->rechazadas; ?></span>
                Rechazadas
            </button>
        </div>
        <script>
            $(document).ready(function() {
                $("#btnAutorizadas").click(function(event) {
                    loadModal(
                        "modal-container",
                        {
                            modalDev: '14^64^65',
                            modalSize: 'xl',
                            modalTitle: 'Solicitudes de acceso: Autorizadas',
                            modalForm: 'historialSolicitudesAcceso',
                            formData: 'Autorizada^<?php echo $_POST["tipoSolicitud"]; ?>',
                            buttonCancelShow: true,
                            buttonCancelText: 'Cerrar'
                        }
                    );
                });
                $("#btnRechazadas").click(function(event) {
                    loadModal(
                        "modal-container",
                        {
                            modalDev: '14^64^65',
                            modalSize: 'xl',
                            modalTitle: 'Solicitudes de acceso: Rechazadas',
                            modalForm: 'historialSolicitudesAcceso',
                            formData: 'Rechazada^<?php echo $_POST["tipoSolicitud"]; ?>',
                            buttonCancelShow: true,
                            buttonCancelText: 'Cerrar'
                        }
                    );
                });
            });
        </script>
<?php 
    } else {
        // No se asigno permisos de "Historial de solicitudes"
    }
?>