<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataEnfermedades = $cloud->rows("
        SELECT
        	catPrsEnfermedadId,
			tipoEnfermedad,
			nombreEnfermedad
        FROM cat_personas_enfermedades
        WHERE flgDelete = ?
        ORDER BY tipoEnfermedad
    ", ['0']);
    $n = 0;
    foreach ($dataEnfermedades as $dataEnfermedades) {
    	$n += 1;

    	$enfermedad = '
    		'.$dataEnfermedades->nombreEnfermedad.' ('.$dataEnfermedades->tipoEnfermedad.')
    	';

	    $acciones = '
			<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalEnfermedad(`editar`,`'.$dataEnfermedades->catPrsEnfermedadId.'`);">
				<i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
			</button>
			<button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarEnfermedad(`'.$dataEnfermedades->catPrsEnfermedadId.'`);">
				<i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
			</button>
	    ';

	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $enfermedad,
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