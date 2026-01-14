<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

	$jefaturasEmpleado = array();

	// ID del empleado para el cual deseas obtener los jefes
	$prsExpedienteId = $_POST['expedienteId'];

	$dataJefesEmpleado = $cloud->rows("
	        SELECT
	        	jef.jefeId AS jefeId,
	            CONCAT(
	                IFNULL(pers.apellido1, '-'),
	                ' ',
	                IFNULL(pers.apellido2, '-'),
	                ', ',
	                IFNULL(pers.nombre1, '-'),
	                ' ',
	                IFNULL(pers.nombre2, '-')
	            ) AS nombreCompleto
	        FROM th_expediente_jefaturas jef
	        JOIN th_expediente_personas exp ON exp.prsExpedienteId = jef.jefeId
	        JOIN th_personas pers ON pers.personaId = exp.personaId
	        WHERE jef.prsExpedienteId = ? AND jef.flgDelete = ?
	", [$prsExpedienteId, 0]);

	foreach ($dataJefesEmpleado as $dataJefesEmpleado) {
		$jefaturasEmpleado[] = array(
			"id" 			=> $dataJefesEmpleado->jefeId,
			"valor" 	=> $dataJefesEmpleado->nombreCompleto
		);

		$flgRepetir = true;
		$prsExpedienteId = $dataJefesEmpleado->jefeId;
		$numJefe = 1;
		// Buscar jefes de los jefes que hereden personal
		while($flgRepetir) {
			// En la primera vuelta no se hereda, ya que es el jefe del empleado para verificar
			// Si tiene otro jefe y en la siguiente vuelta se heredarán
			$whereHeredar = ($numJefe == 1 ? '' : " AND jef.flgHeredarPersonal = 'Sí' ");

			$sqlJefe = "
		        SELECT
		        	jef.jefeId AS jefeId,
		            CONCAT(
		                IFNULL(pers.apellido1, '-'),
		                ' ',
		                IFNULL(pers.apellido2, '-'),
		                ', ',
		                IFNULL(pers.nombre1, '-'),
		                ' ',
		                IFNULL(pers.nombre2, '-')
		            ) AS nombreCompleto
		        FROM th_expediente_jefaturas jef
		        JOIN th_expediente_personas exp ON exp.prsExpedienteId = jef.jefeId
		        JOIN th_personas pers ON pers.personaId = exp.personaId
		        WHERE jef.prsExpedienteId = ? AND jef.flgDelete = ? $whereHeredar
		    ";
		    $existeJefeHereda = $cloud->count($sqlJefe, [$prsExpedienteId, 0]);

		    // Verificar si se encontró un jefe para el empleado actual
		    if($existeJefeHereda > 0) {
		    	$dataJefeHereda = $cloud->row($sqlJefe, [$prsExpedienteId, 0]);

				$jefaturasEmpleado[] = array(
					"id" 			=> $dataJefeHereda->jefeId,
					"valor" 	=> $dataJefeHereda->nombreCompleto
				);
				$prsExpedienteId = $dataJefeHereda->jefeId;
		    } else {
		        // No se encontró un jefe, por lo que se termina el bucle
		        $flgRepetir = false;
		    }
		    $numJefe += 1;
		}
	}


	if(count($jefaturasEmpleado) == 0) {
		// No se agregaron jefes
		$jefaturasEmpleado[] = array(
			"id" 			=> "0",
			"valor" 	=> "No se han asignado jefaturas al empleado"
		);
	} else {
		// Se agregaron jefes
	}

	// Por si las jefaturas se vuelven una cadena interminable heredando por todos lados
	$jefaturasEmpleado = eliminarDuplicados($jefaturasEmpleado, 'id');
	echo json_encode($jefaturasEmpleado);

	// Función para eliminar registros duplicados basados en el "id"
	function eliminarDuplicados($array, $clave) {
	    $tempArray = array();
	    $resultado = array();

	    foreach ($array as $elemento) {
	        if (!isset($tempArray[$elemento[$clave]])) {
	            $tempArray[$elemento[$clave]] = true;
	            $resultado[] = $elemento;
	        }
	    }

	    return $resultado;
	}
?>