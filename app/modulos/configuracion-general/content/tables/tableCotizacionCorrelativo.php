<?php 
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataCotizacionCorrelativo = $cloud->rows("
    SELECT
        cc.correlativoCotizacionId AS correlativoCotizacionId, 
        cc.tipoCorrelativo AS tipoCorrelativo,
        cc.origenCorrelativo AS origenCorrelativo,
        cs.sucursal AS sucursal,
        cc.anio AS anio,
        cc.correlativoActual AS correlativoActual,
        cc.estadoCorrelativo AS estadoCorrelativo
    FROM fel_correlativo_cotizacion cc
    JOIN cat_sucursales cs ON cs.sucursalId = cc.origenCorrelativoId
    WHERE cc.flgDelete = ?
", [0]);
    
$n = 0;
foreach($dataCotizacionCorrelativo as $cotizacionCorrelativo){
    $n += 1;
    
    $tipo = '
        <b><i class="fas fa-file-alt"></i> Tipo: </b> '.$cotizacionCorrelativo->tipoCorrelativo.'<br>
    ';
    
    $origen = '
        <b><i class="fas fa-list-ul"></i> Origen: </b> '.$cotizacionCorrelativo->origenCorrelativo.'<br>
        <b><i class="fas fa-edit"></i> Valor: </b> '.$cotizacionCorrelativo->sucursal.'<br>
    ';

    $año = '
        <b><i class="fas fa-calendar-alt"></i> Año: </b> '.$cotizacionCorrelativo->anio.'<br>    
    ';

    $correlativoActual = '
            <b><i class="fas fa-list-ol"></i> Correlativo actual: </b> '.$cotizacionCorrelativo->correlativoActual.'<br>
    ';
    
    $jsonEstadoCorrelativo = array(
        "operation"                 => "cotizacionesCorrelativo",
        "typeOperation"             => "update",
        "correlativoCotizacionId"   =>  $cotizacionCorrelativo->correlativoCotizacionId
    );

    if($cotizacionCorrelativo->estadoCorrelativo == "Activo"){
        $correlativoActual .= '
            <b><i class="fas fa-business-time"></i> Estado: </b> <span class="badge badge-success">'.$cotizacionCorrelativo->estadoCorrelativo.'</span><br>
        ';
        $acciones = '
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="desactivarCotizacionCorrrelativo('.htmlspecialchars(json_encode($jsonEstadoCorrelativo)).')">
                <i class="fas fa-ban"></i>
                <span class="ttiptext">Desactivar</span>
            </button>
        ';
    }else{
        $correlativoActual .= '
            <b><i class="fas fa-business-time"></i> Estado: </b> <span class="badge badge-danger">'.$cotizacionCorrelativo->estadoCorrelativo.'</span><br>
        ';

        $acciones = '';
    }

    $output['data'][] = array(
        $n, // es #, se dibuja solo en el JS de datatable
        $tipo,
        $origen,
        $año,
        $correlativoActual,
        $acciones
    );
}
if($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data'=>'')); 
}
