<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $datTipoProd = $cloud->rows("
        SELECT
            tipoProductoId,
            nombreTipoProducto
        FROM cat_inventario_tipos_producto
        WHERE flgDelete = 0
    ");

    $n = 0;
    foreach ($datTipoProd as $datTipoProd) {
        $n += 1;
        $tipoProd = '<b><i class="fas fa-tag"></i> Tipo de producto: </b>' . $datTipoProd->nombreTipoProducto;
        
        $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalOpenForm(`update^'.$datTipoProd->tipoProductoId.'`,`Editar Tipo de producto`,`tipoDeProducto`,`md`);">
                    <i class="fas fa-pen"></i>
                    <span class="ttiptext">Editar</span>
                </button>
                <button type="button" class="btn btn-danger btn-sm ttip" onClick="eliminarTipoProducto(`'.$datTipoProd->tipoProductoId.'^'.$datTipoProd->nombreTipoProducto.'`);">
                    <i class="fas fa-trash-alt"></i>
                    <span class="ttiptext">Eliminar</span>
                </button>';
        
        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $tipoProd,
            $acciones
        );
    } // foreach

    if( $n > 0 ) {
        echo json_encode( $output );
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }