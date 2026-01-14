<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    // POST = tipoClasificacion
    $tipoClasificacion = $_POST['tipoClasificacion'];
    $iconClasificacion = ''; $tipoClasificacionPlural = ''; $tblClasificacion = '';

    switch($tipoClasificacion) {
        case 'Sucursal':
            $iconClasificacion = 'building';
            $tipoClasificacionPlural = 'Sucursales';
            $tblClasificacion = 'tblClasifSucursales';
        break;

        default:
            $iconClasificacion = 'trademark';
            $tipoClasificacionPlural = 'Líneas';
            $tblClasificacion = 'tblClasifLineas';
        break;
    }

    $dataClasificacion = $cloud->rows("
        SELECT
            comisionClasificacionId, tipoClasificacion, tituloClasificacion
        FROM conta_comision_reporte_clasificacion
        WHERE tipoClasificacion = ? AND flgDelete = ?
        ORDER BY tituloClasificacion
    ", [$tipoClasificacion, 0]);
    $n = 0;
    foreach ($dataClasificacion as $dataClasificacion) {
        $n += 1;

        $totalClasifDetalle = $cloud->count("
            SELECT comisionClasificacionId FROM conta_comision_reporte_clasificacion_detalle
            WHERE comisionClasificacionId = ? AND flgDelete = ?
        ", [$dataClasificacion->comisionClasificacionId, 0]);

        $clasificacion = '
            <b><i class="fas fa-'.$iconClasificacion.'"></i></b> '.$dataClasificacion->tituloClasificacion.'
        ';

        $jsonEditar = array(
            "typeOperation"                     => "update",
            "comisionClasificacionId"           => $dataClasificacion->comisionClasificacionId,
            "tipoClasificacion"                 => $tipoClasificacion,
            "tblClasif"                         => $tblClasificacion
        );

        $jsonEliminar = array(
            "typeOperation"                     => "delete",
            "operation"                         => "comision-clasificacion",
            "comisionClasificacionId"           => $dataClasificacion->comisionClasificacionId,
            "tblClasif"                         => $tblClasificacion
        );

        $jsonDetalle = array(
            "comisionClasificacionId"           => $dataClasificacion->comisionClasificacionId,
            "tipoClasificacion"                 => $tipoClasificacion,
            "tblClasif"                         => $tblClasificacion,
            "tituloModal"                       => "Clasificación de $tipoClasificacionPlural: $dataClasificacion->tituloClasificacion"
        );

        $acciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalClasificacion('.htmlspecialchars(json_encode($jsonEditar)).');">
                <i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarClasificacion('.htmlspecialchars(json_encode($jsonEliminar)).');">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalClasificacionDetalle('.htmlspecialchars(json_encode($jsonDetalle)).');">
                <span class="badge rounded-pill bg-light" style="color: black;">'.$totalClasifDetalle.'</span>
                <i class="fas fa-list-ul"></i>
                <span class="ttiptext">Clasificación de '.$tipoClasificacionPlural.'</span>
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