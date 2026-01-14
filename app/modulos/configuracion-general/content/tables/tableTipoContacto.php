<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataTipoCon = $cloud->rows("
    SELECT tipoContactoId, tipoContacto, formatoContacto FROM cat_tipos_contacto WHERE flgDelete = 0
");
$n = 0;
foreach ($dataTipoCon as $dataTipo) {
    $n += 1;

    $tipoContacto = '
        <b><i class="fas fa-address-book"></i> Tipo de contacto: </b> '.$dataTipo->tipoContacto.'<br>
        <b><i class="fas fa-mask"></i> Máscara: </b> '.$dataTipo->formatoContacto.'
    ';

    $controles = '';
    if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(19, $_SESSION["arrayPermisos"])) { // edit tipo con
        $controles .= '<button type="button" class="btn btn-primary btn-sm ttip" onclick="modTipoCon(`'. $dataTipo->tipoContactoId .'`)"><i class="fas fa-pencil-alt"></i><span class="ttiptext">Editar</span></button> ';
    }
    if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(20, $_SESSION["arrayPermisos"])) { // del tipo con
        $controles .= '<button type="button" class="btn btn-danger btn-sm ttip" onclick="delTipoCon(`'. $dataTipo->tipoContactoId .'`)"><i class="fas fa-trash-alt"></i><span class="ttiptext">Eliminar</span></button>';
    }

    $output['data'][] = array(
        $n, // es #, se dibuja solo en el JS de datatable
        $tipoContacto,
        $controles
    );
}
if($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data'=>'')); 
}