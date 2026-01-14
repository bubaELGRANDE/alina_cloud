<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataSucursales = $cloud->rows("
    SELECT 
        cps.personaSucursalId AS personaSucursalId,
        cps.personaId AS personaId,
        cps.sucursalId AS sucursalId,
        s.sucursal AS sucursal,
        s.paisMunicipioId AS paisMunicipioId,
        s.direccionSucursal AS direccionSucursal
    FROM conf_personas_sucursales cps
    JOIN cat_sucursales s ON cps.sucursalId = s.sucursalId
    WHERE cps.flgDelete = ? AND cps.personaId = ?
    ORDER BY s.sucursal
", [0, $_POST["personaId"]]);
    $n = 0;
    foreach ($dataSucursales as $dataSucursales) {
    	$n += 1;
        $jsonEliminar = array(
            'typeOperation'         => "delete",
            'operation'             => "eliminar-sucursal-persona",
            'sucursalId'            => $dataSucursales->sucursalId,
            'personaId'             => $_POST["personaId"],
            'personaSucursalId'     => $dataSucursales->personaSucursalId
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

    	$departamento = '
    		<b><i class="fas fa-tag"></i> Sucursal: </b> '.$dataSucursales->sucursal.'
    	';

	    $acciones = '
			<button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarSucursalPersona('.$funcionEliminar.');">
				<i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
			</button>
	    ';

	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $departamento,
	        $acciones
	    );
	} // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>