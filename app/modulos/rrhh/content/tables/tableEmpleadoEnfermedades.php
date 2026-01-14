<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataEstadoPersona = $cloud->row("
        SELECT
            estadoPersona
        FROM th_personas
        WHERE personaId = ?
    ",[$_POST["id"]]);

    $dataEmpleadoEnfermedades = $cloud->rows("
        SELECT
			pe.prsEnfermedadId AS prsEnfermedadId, 
			pe.catPrsEnfermedadId AS catPrsEnfermedadId,
			cpe.tipoEnfermedad AS tipoEnfermedad,
			cpe.nombreEnfermedad AS nombreEnfermedad
        FROM th_personas_enfermedades pe
        JOIN cat_personas_enfermedades cpe ON cpe.catPrsEnfermedadId = pe.catPrsEnfermedadId
        WHERE pe.personaId = ? AND pe.flgDelete = '0'
    ", [$_POST["id"]]);
    $n = 0;
    foreach ($dataEmpleadoEnfermedades as $dataEmpleadoEnfermedades) {
    	$n += 1;

    	$enfermedad = '
    		<b><i class="fas fa-syringe"></i> '.$dataEmpleadoEnfermedades->tipoEnfermedad.': </b> '.$dataEmpleadoEnfermedades->nombreEnfermedad.'
    	';

        if($dataEstadoPersona->estadoPersona == "Inactivo") {
            $disabledInactivo = "disabled";
        } else {
            $disabledInactivo = "";
        }

	    $acciones = '
			<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalEnfermedad(`editar`,`'.$dataEmpleadoEnfermedades->prsEnfermedadId.'`);" '.$disabledInactivo.'>
				<i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
			</button>
			<button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarEnfermedad(`'.$dataEmpleadoEnfermedades->prsEnfermedadId.'`);" '.$disabledInactivo.'>
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