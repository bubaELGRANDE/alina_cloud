<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    // POST = id, tipoParametrizacion
    $dashParamId = $_POST['id'];
    $tipoParametrizacion = $_POST['tipoParametrizacion'];
    $tipoParametrizacionUpdate = ($tipoParametrizacion == "Unidad de negocio" ? 'udn' : strtolower($tipoParametrizacion));
    $iconParametrizacion = ''; $tipoParametrizacionPlural = ''; $tblParam = '';

    switch ($tipoParametrizacionUpdate) {
        case 'marca':
            $iconParametrizacion = 'trademark';
            $tipoParametrizacionPlural = 'Marcas';
            $tblParam = 'tblParamMarca';
        break;
        
        case 'udn':
            $iconParametrizacion = 'briefcase';
            $tipoParametrizacionPlural = 'Unidades de negocios';
            $tblParam = 'tblParamUDN';
        break;

        default:
            $iconParametrizacion = 'building';
            $tipoParametrizacionPlural = 'Sucursales';
            $tblParam = 'tblParamSucursal';
        break;
    }

    $dataParametrizacionDetalle = $cloud->rows("
        SELECT
            dashParamDetalleId,
            valorParametrizacion
        FROM dash_parametrizacion_detalle
        WHERE dashParamId = ? AND flgDelete = ?
        ORDER BY valorParametrizacion
    ", [$dashParamId, 0]);
    $n = 0;
    foreach ($dataParametrizacionDetalle as $dataParametrizacionDetalle) {
        $n += 1;

        $parametrizacion = '
            <b><i class="fas fa-'.$iconParametrizacion.'"></i></b> '.$dataParametrizacionDetalle->valorParametrizacion.'
        ';

        $acciones = '
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarParametrizacion(`'.$dataParametrizacionDetalle->dashParamDetalleId.'`, `'.$tblParam.'`, `parametrizacion-ventas-detalle`);">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
        ';

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $parametrizacion,
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