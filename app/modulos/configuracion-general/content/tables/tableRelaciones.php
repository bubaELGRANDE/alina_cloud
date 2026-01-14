<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataRelacion = $cloud->rows("
        SELECT
        catPrsRelacionId, 
        tipoPrsRelacion
        FROM cat_personas_relacion
        WHERE flgDelete = 0
    ");
    $n = 0;
    foreach ($dataRelacion as $dataRelacion) {
        $n += 1;
        $controles = '';
        if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(16, $_SESSION["arrayPermisos"])) { 
            $controles .= '<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalRelacion(`editar`, `'.$dataRelacion->catPrsRelacionId .'`)"><i class="fas fa-pencil-alt"></i><span class="ttiptext">Editar</span></button> ';
        }
        if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(17, $_SESSION["arrayPermisos"])) { 
            $controles .= '<button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarRelacion(`'. $dataRelacion->catPrsRelacionId .'`)"><i class="fas fa-trash-alt"></i><span class="ttiptext">Eliminar</span></button>';
        }

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $dataRelacion->tipoPrsRelacion,
            $controles
        );
    }
    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }