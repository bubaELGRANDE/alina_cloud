<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

	if($_POST["tipoSolicitud"] == "Empleado" && (in_array(14, $_SESSION["arrayPermisos"]) || in_array(42, $_SESSION["arrayPermisos"]))) {

	    $flgMostrarTabla = 1;
	    if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(64, $_SESSION["arrayPermisos"])) { // Todos
	        $wherePermiso = ""; // Todas las autorizaciones/rechazos
	    } else if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(65, $_SESSION["arrayPermisos"])) {
	        // Get solicitudes aprobadas/rechazadas por él mismo (usuario session)
	        $wherePermiso = "sh.usuarioIdAutoriza = '$_SESSION[usuarioId]' AND";
	    } else {
	        // No se asignó ningún permiso
	        $flgMostrarTabla = 0;
	    }

	    if($flgMostrarTabla == 1) {
		    $dataHistorialSolicitudes = $cloud->rows("
		        SELECT
		            us.usuarioId AS usuarioId,
		            us.personaId AS personaId,
		            us.fhAdd AS fhSolicitud,
		            us.correo AS correo,
		            sh.fhSolicitud AS fhSolicitudAutoriza,
		            sh.justificacionEstado AS justificacionEstado,
				    CONCAT(
				        IFNULL(per.apellido1, '-'),
				        ' ',
				        IFNULL(per.apellido2, '-'),
				        ', ',
				        IFNULL(per.nombre1, '-'),
				        ' ',
				        IFNULL(per.nombre2, '-')
				    ) AS nombrePersona,
				    CONCAT(
				        IFNULL(per2.apellido1, '-'),
				        ' ',
				        IFNULL(per2.apellido2, '-'),
				        ', ',
				        IFNULL(per2.nombre1, '-'),
				        ' ',
				        IFNULL(per2.nombre2, '-')
				    ) AS nombrePersonaAutoriza
				FROM bit_solicitudes_acc_historial sh
				JOIN conf_usuarios us ON us.usuarioId = sh.id
				JOIN th_personas per ON per.personaId = us.personaId
		        JOIN conf_usuarios us2 ON us2.usuarioId = sh.usuarioIdAutoriza
		        JOIN th_personas per2 ON per2.personaId = us2.personaId
		        WHERE $wherePermiso sh.estadoSolicitud = ? AND sh.tipoSolicitud = 'Empleado' AND sh.flgDelete = '0'
		    ", [$_POST["tipoHistorial"]]);
		    $n = 0;
		    foreach ($dataHistorialSolicitudes as $dataHistorialSolicitudes) {
		    	$n += 1;


		    	if($_POST["tipoHistorial"] == "Autorizada") {
		    		$justificacionMotivo = "";
		    	} else {
		    		$justificacionMotivo = '<br><b><i class="fas fa-edit"></i> Justificación/Motivo: </b> ' . $dataHistorialSolicitudes->justificacionEstado;
		    	}

		    	$detalleSolicitud = '
		    		<b><i class="fas fa-envelope-open-text"></i> Solicitud enviada por: </b>' . $dataHistorialSolicitudes->nombrePersona . ' (' . $dataHistorialSolicitudes->correo . ')<br>
		    		<b><i class="fas fa-envelope"></i> Solicitud ' . strtolower($_POST["tipoHistorial"]) . ' por: </b>' . $dataHistorialSolicitudes->nombrePersonaAutoriza . $justificacionMotivo;

		    	$fechaHora = '
		    		<b><i class="fas fa-user-clock"></i> Solicitud recibida: </b>' . date("d/m/Y H:i:s", strtotime($dataHistorialSolicitudes->fhSolicitud)) . '<br>
		    		<b><i class="fas fa-user-clock"></i> Solicitud ' . strtolower($_POST["tipoHistorial"]) . ': </b>' . date("d/m/Y H:i:s", strtotime($dataHistorialSolicitudes->fhSolicitudAutoriza)) . '
		    	';

			    $output['data'][] = array(
			        $n, // es #, se dibuja solo en el JS de datatable
			        $detalleSolicitud,
			        $fechaHora
			    );
			} // foreach
	    } else { // No mostrar data
	    	$n = 0;
	    }
	} else if($_POST["tipoSolicitud"] == "Externa" && (in_array(14, $_SESSION["arrayPermisos"]) || in_array(43, $_SESSION["arrayPermisos"]))) { // Externa
	    $flgMostrarTabla = 1;
	    if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(64, $_SESSION["arrayPermisos"])) { // Todos
	        $wherePermiso = ""; // Todas las autorizaciones/rechazos
	    } else if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(65, $_SESSION["arrayPermisos"])) {
	        // Get solicitudes aprobadas/rechazadas por él mismo (usuario session)
	        $wherePermiso = "sh.usuarioIdAutoriza = '$_SESSION[usuarioId]' AND";
	    } else {
	        // No se asignó ningún permiso
	        $flgMostrarTabla = 0;
	    }

	    if($flgMostrarTabla == 1) {
			// BUSCAR DATA EN solicitudAccesoId
		    $dataHistorialSolicitudes = $cloud->rows("
		        SELECT
		        	sa.nombreSolicitud AS nombrePersona,
		        	sa.dui AS dui,
		        	sa.fechaNacimiento AS fechaNacimiento,
		        	sa.correo AS correo,
		        	sa.fhSolicitud AS fhSolicitud,
		            sh.fhSolicitud AS fhSolicitudAutoriza,
		            sh.estadoSolicitud AS estadoSolicitud,
				    CONCAT(
				        IFNULL(per2.apellido1, '-'),
				        ' ',
				        IFNULL(per2.apellido2, '-'),
				        ', ',
				        IFNULL(per2.nombre1, '-'),
				        ' ',
				        IFNULL(per2.nombre2, '-')
				    ) AS nombrePersonaAutoriza,
				    sh.justificacionEstado AS justificacionEstado
				FROM bit_solicitudes_acc_historial sh
				JOIN bit_solicitudes_acceso sa ON sa.solicitudAccesoId = sh.id
		        JOIN conf_usuarios us2 ON us2.usuarioId = sh.usuarioIdAutoriza
		        JOIN th_personas per2 ON per2.personaId = us2.personaId
		        WHERE sh.estadoSolicitud = ? AND sh.tipoSolicitud = 'Externa' AND sh.flgDelete = '0'
		    ", [$_POST["tipoHistorial"]]);
		    $n = 0;
		    foreach ($dataHistorialSolicitudes as $dataHistorialSolicitudes) {
		    	$n += 1;

		    	if($dataHistorialSolicitudes->estadoSolicitud == "Autorizada") {
			    	$detalleSolicitud = '
			    		<b><i class="fas fa-envelope-open-text"></i> Solicitud enviada por: </b>' . $dataHistorialSolicitudes->nombrePersona . ' (' . $dataHistorialSolicitudes->correo . ')<br>
			    		<b><i class="fas fa-envelope"></i> Solicitud ' . strtolower($_POST["tipoHistorial"]) . ' por: </b>' . $dataHistorialSolicitudes->nombrePersonaAutoriza;
		    	} else {
			    	$detalleSolicitud = '
			    		<b><i class="fas fa-envelope-open-text"></i> Solicitud enviada por: </b>' . $dataHistorialSolicitudes->nombrePersona . ' (' . $dataHistorialSolicitudes->correo . ')<br>
			    		<b><i class="fas fa-envelope"></i> Solicitud ' . strtolower($_POST["tipoHistorial"]) . ' por: </b>' . $dataHistorialSolicitudes->nombrePersonaAutoriza . '<br>
			    		<b><i class="fas fa-edit"></i> Justificación/Motivo: </b>' . $dataHistorialSolicitudes->justificacionEstado . '
			    	';	    		
		    	}
		    	
		    	$fechaHora = '
		    		<b><i class="fas fa-user-clock"></i> Solicitud recibida: </b>' . date("d/m/Y H:i:s", strtotime($dataHistorialSolicitudes->fhSolicitud)) . '<br>
		    		<b><i class="fas fa-user-clock"></i> Solicitud ' . strtolower($_POST["tipoHistorial"]) . ': </b>' . date("d/m/Y H:i:s", strtotime($dataHistorialSolicitudes->fhSolicitudAutoriza)) . '
		    	';

			    $output['data'][] = array(
			        $n, // es #, se dibuja solo en el JS de datatable
			        $detalleSolicitud,
			        $fechaHora
			    );
			} // foreach
	    } else {
	    	$n = 0;
	    }
	} else { // No mostrar data
		$n = 0;
	}

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>