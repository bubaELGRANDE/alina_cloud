<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataCargosPersonas = $cloud->rows("
        SELECT
        	prsCargoId, 
        	cargoPersona, 
        	descripcionCargoPersona,
        	funcionCargoPersona,
        	herramientasCargoPersona
        FROM cat_personas_cargos
        WHERE flgDelete = ?
        ORDER BY cargoPersona
    ", ['0']);
    $n = 0;
    foreach ($dataCargosPersonas as $dataCargosPersonas) {
    	$n += 1;

    	$cargo = '
    		<b><i class="fas fa-briefcase"></i> Cargo: </b> '.$dataCargosPersonas->cargoPersona.'<br>
    		<b><i class="fas fa-edit"></i> Descripción del cargo: </b> '.$dataCargosPersonas->descripcionCargoPersona.'<br>
    		<b><i class="fas fa-edit"></i> Funciones del cargo: </b> '.$dataCargosPersonas->funcionCargoPersona.'<br>
    		<b><i class="fas fa-tools"></i> Herramientas/Materiales del cargo: </b> '.$dataCargosPersonas->herramientasCargoPersona.'
    	';

	    $acciones = '
			<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalCargoPersona(`editar`,`'.$dataCargosPersonas->prsCargoId.'`);">
				<i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
			</button>
			<button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarCargoPersona(`'.$dataCargosPersonas->prsCargoId.'`);">
				<i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
			</button>
	    ';

	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $cargo,
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