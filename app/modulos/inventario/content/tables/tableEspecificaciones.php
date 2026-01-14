<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataEsp = $cloud->rows("
    SELECT catProdEspecificacionId, tipoEspecificacion, nombreProdEspecificacion, tipoMagnitud FROM cat_productos_especificaciones WHERE flgDelete = 0 
    ORDER BY nombreProdEspecificacion");

    $n = 0;
    foreach ($dataEsp as $esp) {
        $n += 1;
        $especificacion = '<b>' . $esp->nombreProdEspecificacion .'</b>';
        $equivalencia = (empty($esp->tipoMagnitud)) ? ("N/A") : ($esp->tipoMagnitud);
        $tipoEsp = $esp->tipoEspecificacion;
        $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalEspecificaciones(`update^'.$esp->catProdEspecificacionId.'`);">
                    <i class="fas fa-pen"></i>
                    <span class="ttiptext">Editar</span>
                </button>
                <button type="button" class="btn btn-danger btn-sm ttip" onClick="eliminarEsp(`'.$esp->catProdEspecificacionId.'`);">
                    <i class="fas fa-trash-alt"></i>
                    <span class="ttiptext">Eliminar</span>
                </button>';
        
        
        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $especificacion,
            $tipoEsp,
            $equivalencia,
            $acciones
        );
    } // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }