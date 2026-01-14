<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    // POST = tipoParametrizacion
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

    $dataParametrizacion = $cloud->rows("
        SELECT
            dashParamId, tipoParametrizacion, tituloParametrizacion, colorParametrizacion
        FROM dash_parametrizacion
        WHERE tipoParametrizacion = ? AND flgDelete = ?
        ORDER BY tituloParametrizacion
    ", [$tipoParametrizacion, 0]);
    $n = 0;
    foreach ($dataParametrizacion as $dataParametrizacion) {
        $n += 1;

        $totalParamDetalle = $cloud->count("
            SELECT dashParamDetalleId FROM dash_parametrizacion_detalle
            WHERE dashParamId = ? AND flgDelete = ?
        ", [$dataParametrizacion->dashParamId, 0]);

        $parametrizacion = '
            <div class="row">
                <div class="col-9">
                    <b><i class="fas fa-'.$iconParametrizacion.'"></i></b> '.$dataParametrizacion->tituloParametrizacion.'
                </div>
                <div class="col-3">
                    <input type="color" class="form-control form-control-color" value="'.$dataParametrizacion->colorParametrizacion.'" disabled>
                </div>
            </div>
        ';

        $acciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalParametrizacion(`editar^'.$dataParametrizacion->dashParamId.'^'.$tipoParametrizacionUpdate.'`);">
                <i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarParametrizacion(`'.$dataParametrizacion->dashParamId.'`, `'.$tblParam.'`, `parametrizacion-ventas`);">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalParametrizacionDetalle(`'.$dataParametrizacion->dashParamId.'^'.$tipoParametrizacionUpdate.'`, `'.$tipoParametrizacionPlural.': '.$dataParametrizacion->tituloParametrizacion.'`);">
                <span class="badge rounded-pill bg-light" style="color: black;">'.$totalParamDetalle.'</span>
                <i class="fas fa-list-ul"></i>
                <span class="ttiptext">Parametrización de '.$tipoParametrizacionPlural.'</span>
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