<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

	$getPeriodos = $cloud->rows('
		SELECT cierreDeclaracionId, mesNombre, anio, obsCierreDelaracion 
		FROM fel_cierre_declaracion 
		WHERE flgDelete = ?
		ORDER BY anio DESC, mesNumero DESC
	', [0]);

	$n = 0;
	foreach ($getPeriodos as $pr){
		$n += 1;
		$periodo = $pr->mesNombre . ' ' . $pr->anio;
		$observaciones = $pr->obsCierreDelaracion;

		$jsonDecl = array(
			"tituloModal"   	=> "Periodo declarado: " . $periodo,
			"idDeclaracion"   	=> $pr->cierreDeclaracionId
		);
		$funcionDeclaracion = htmlspecialchars(json_encode($jsonDecl));

		$jsonInvalidacion = array(
			"tituloModal"   	=> "Invalidación extraordinaria: " . $periodo,
			"idDeclaracion"   	=> $pr->cierreDeclaracionId
		);
		$funcionInvalidacion = htmlspecialchars(json_encode($jsonInvalidacion));

		$jsonDel = array(
			"typeOperation" 	=> "delete",
			"operation"       	=> "eliminar-periodo-declarado",
			"periodo"			=> $periodo,
			"idDeclaracion"   	=> $pr->cierreDeclaracionId
		);
		$funcionDel = htmlspecialchars(json_encode($jsonDel));

		$contadorAnulaciones = $cloud->count("
			SELECT cierreAnulacionId 
			FROM fel_cierre_declaracion_anulacion
			 WHERE cierreDeclaracionId = ? AND flgDelete = 0
		", [$pr->cierreDeclaracionId]);

		/*
			<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalDeclaracion('.$funcionDeclaracion.');">
				<i class="fas fa-lock"></i>
				<span class="ttiptext">Ver Declaración</span>
			</button>
		*/

		$acciones = '
			<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalInvalidar('.$funcionInvalidacion.');">
				<span class="badge bg-light text-dark">'.$contadorAnulaciones.'</span>
				<i class="fas fa-lock"></i>
				<span class="ttiptext">Invalidación de DTE extraordinaria</span>
			</button>
			<button type="button" class="btn btn-danger btn-sm ttip" onclick="delDeclaracion('.$funcionDel.');">
				<i class="fas fa-trash-alt"></i> 
				<span class="ttiptext">Eliminar Declaración</span>
			</button>
		';

		$output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $periodo,
            $observaciones,
            $acciones
        );
	}
    
	if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>''));
    }