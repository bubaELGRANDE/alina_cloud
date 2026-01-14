<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

	if($_POST["tipoSolicitud"] == "Empleado" && (in_array(14, $_SESSION["arrayPermisos"]) || in_array(42, $_SESSION["arrayPermisos"]))) {
	    $flgMostrarTabla = 1;
	    if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(45, $_SESSION["arrayPermisos"])) { // Todos
	        $wherePermiso = ""; // Todos los usuarios
	    } else if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(44, $_SESSION["arrayPermisos"])) {
	        // Get usuarios-empleados a cargo
	        // wherePermiso = us.personaId IN ($arrayPersonasACargo) AND
	        $wherePermiso = ""; // Modificar al tener la tabla de empleados a cargo
	    } else {
	        // No se asignó ningún permiso
	        $flgMostrarTabla = 0;
	    }

	    if($flgMostrarTabla == 1) {
		    $dataSolicitudes = $cloud->rows("
		        SELECT
		            us.usuarioId AS usuarioId,
		            us.personaId AS personaId,
		            us.correo AS correo,
		            us.fhAdd AS fhSolicitud,
				    CONCAT(
				        IFNULL(per.apellido1, '-'),
				        ' ',
				        IFNULL(per.apellido2, '-'),
				        ', ',
				        IFNULL(per.nombre1, '-'),
				        ' ',
				        IFNULL(per.nombre2, '-')
				    ) AS nombrePersona,
				    CONCAT(per.nombre1, ' ', per.apellido1) AS sideName
		        FROM conf_usuarios us
		        JOIN th_personas per ON per.personaId = us.personaId
		        WHERE $wherePermiso us.estadoUsuario = ? AND per.estadoPersona = ? AND us.flgDelete = '0' AND per.flgDelete = '0'
		    ", ["Pendiente", "Activo"]);
		    $n = 0;
		    foreach ($dataSolicitudes as $dataSolicitudes) {
		    	$n += 1;

		    	if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(46, $_SESSION["arrayPermisos"])) {
		    		$btnAutorizar = '
						<button type="button" class="btn btn-success btn-sm ttip" onclick="procesarSolicitud(`autorizar`, `'.$dataSolicitudes->nombrePersona.'`, `'.$dataSolicitudes->usuarioId.'`, `'.$dataSolicitudes->personaId.'`, `'.$dataSolicitudes->sideName.'`, `'.$dataSolicitudes->correo.'`);">
							<i class="fas fa-user-check"></i>
							<span class="ttiptext">Autorizar solicitud</span>
						</button>
		    		';
		    	} else {
		    		$btnAutorizar = '';
		    	}

		    	if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(47, $_SESSION["arrayPermisos"])) {
		    		$btnRechazar = '
						<button type="button" class="btn btn-danger btn-sm ttip" onclick="modalSolicitudRechazo(`rechazar^'.$dataSolicitudes->nombrePersona.'^'.$dataSolicitudes->usuarioId.'^'.$dataSolicitudes->personaId.'^'.$dataSolicitudes->sideName.'^'.$dataSolicitudes->correo.'^Empleado`);">
							<i class="fas fa-user-times"></i>
							<span class="ttiptext">Rechazar solicitud</span>
						</button>
		    		';
		    	} else {
		    		$btnRechazar = '';
		    	}

		    	$solicitud = '
		    		<b><i class="fas fa-user"></i> Empleado: </b> '.$dataSolicitudes->nombrePersona.'<br>
		    		<b><i class="fas fa-envelope"></i> Correo electrónico: </b> '.$dataSolicitudes->correo.'<br>
		    		<b><i class="fas fa-user-clock"></i> Fecha y hora de la solicitud: </b> '.date("d/m/Y H:i:s", strtotime($dataSolicitudes->fhSolicitud)).'
		    	';

			    $controles = '
			    	'.$btnAutorizar.'
			    	'.$btnRechazar.'
			    ';

			    $output['data'][] = array(
			        $n, // es #, se dibuja solo en el JS de datatable
			        $solicitud,
			        $controles
			    );
			} // foreach
	    } else { // No cargar data en la tabla
	    	$n = 0;
	    }
	} else if($_POST["tipoSolicitud"] == "Externa" && (in_array(14, $_SESSION["arrayPermisos"]) || in_array(43, $_SESSION["arrayPermisos"]))) { // Externa
		// Acá no se puede validar 44 y 45 porque son solicitudes externas
	    $dataSolicitudes = $cloud->rows("
	        SELECT
	        	solicitudAccesoId, 
	        	nombreSolicitud,
	        	dui, 
	        	fechaNacimiento, 
	        	correo, 
	        	fhSolicitud
	        FROM bit_solicitudes_acceso
	        WHERE flgDelete = '0'
	    ", []);
	    $n = 0;
	    foreach ($dataSolicitudes as $dataSolicitudes) {
	    	$n += 1;

	    	$informacion = '
	    		<b><i class="fas fa-user"></i> Nombre del solicitante: </b>' . $dataSolicitudes->nombreSolicitud . '<br>
	    		<b><i class="fas fa-address-card"></i> DUI: </b>' . $dataSolicitudes->dui . '<br>
	    		<b><i class="fas fa-calendar"></i> Fecha de nacimiento: </b>' . date("d/m/Y", strtotime($dataSolicitudes->fechaNacimiento)) . '<br>
	    		<b><i class="fas fa-envelope"></i> Correo electrónico: </b> ' . $dataSolicitudes->correo . '<br>
	    		<b><i class="fas fa-user-clock"></i> Fecha y hora de la solicitud: </b> ' . date("d/m/Y H:i:s", strtotime($dataSolicitudes->fhSolicitud)) . '
	    	';

	    	if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(48, $_SESSION["arrayPermisos"])) {
	    		$btnAutorizar = '
					<button type="button" class="btn btn-success btn-sm ttip" onclick="modalSolicitudApExterna(`autorizar^'.$dataSolicitudes->nombreSolicitud.'^'.$dataSolicitudes->dui.'^'.$dataSolicitudes->fechaNacimiento.'^'.$dataSolicitudes->correo.'^'.$dataSolicitudes->solicitudAccesoId.'^Externa`);">
						<i class="fas fa-user-check"></i>
						<span class="ttiptext">Autorizar solicitud</span>
					</button>
	    		';
	    	} else {
	    		$btnAutorizar = '';
	    	}

	    	if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(49, $_SESSION["arrayPermisos"])) {
	    		$btnRechazar = '
					<button type="button" class="btn btn-danger btn-sm ttip" onclick="modalSolicitudRechazo(`rechazar^'.$dataSolicitudes->nombreSolicitud.'^'.$dataSolicitudes->dui.'^'.$dataSolicitudes->fechaNacimiento.'^'.$dataSolicitudes->correo.'^'.$dataSolicitudes->solicitudAccesoId.'^Externa`);">
						<i class="fas fa-user-times"></i>
						<span class="ttiptext">Rechazar solicitud</span>
					</button>
	    		';
	    	} else {
	    		$btnRechazar = '';
	    	}

		    $controles = '
		    	'.$btnAutorizar.'
		    	'.$btnRechazar.'
		    ';

		    $output['data'][] = array(
		        $n, // es #, se dibuja solo en el JS de datatable
		        $informacion,
		        $controles
		    );
		} // foreach
	} else { // No tiene permisos
		$n = 0;
	}

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>