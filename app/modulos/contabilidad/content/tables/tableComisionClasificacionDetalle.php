<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
    	POST:
		comisionClasificacionId
		tipoClasificacion
    */

    $iconClasificacion = ''; $tblClasificacion = '';

    switch($_POST['tipoClasificacion']) {
        case 'Sucursal':
            $iconClasificacion = 'building';
            $tblClasificacion = 'tblClasifSucursales';
        break;

        default:
            $iconClasificacion = 'trademark';
            $tblClasificacion = 'tblClasifLineas';
        break;
    }

    $dataClasificacionDetalle = $cloud->rows("
        SELECT
            comisionClasificacionDetalleId,
            valorClasificacion
        FROM conta_comision_reporte_clasificacion_detalle
        WHERE comisionClasificacionId = ? AND flgDelete = ?
        ORDER BY valorClasificacion
    ", [$_POST['comisionClasificacionId'], 0]);
    $n = 0;
    foreach ($dataClasificacionDetalle as $dataClasificacionDetalle) {
        $n += 1;

        $clasificacion = '
            <b><i class="fas fa-'.$iconClasificacion.'"></i></b> '.$dataClasificacionDetalle->valorClasificacion.'
        ';

        $jsonEliminar = array(
            "typeOperation"                     		=> "delete",
            "operation"                         		=> "comision-clasificacion-detalle",
            "comisionClasificacionDetalleId" 			=> $dataClasificacionDetalle->comisionClasificacionDetalleId,
            "tblClasif"                         		=> $tblClasificacion
        );

        $acciones = '
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarClasificacion('.htmlspecialchars(json_encode($jsonEliminar)).');">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
        ';

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $clasificacion,
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