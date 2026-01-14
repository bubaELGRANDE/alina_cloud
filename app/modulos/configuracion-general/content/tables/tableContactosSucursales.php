<?php 
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataContactos = $cloud->rows("
    SELECT 
        sc.sucursalContactoId AS sucursalContactoId, 
        sc.tipoContactoId AS tipoContactoId,
        tc.tipoContacto AS tipoContacto,
        sc.contactoSucursal AS contactoSucursal, 
        sc.descripcionCSucursal AS descripcionCSucursal
    FROM cat_sucursales_contacto sc
    JOIN cat_tipos_contacto tc ON tc.tipoContactoId = sc.tipoContactoId 
    WHERE sc.flgDelete = 0 AND sc.sucursalId = ?
", [$_POST["idSucursal"]]);
    
$n = 0;
foreach($dataContactos as $dataCon){
    $n += 1;
    
    $controles = '';
    if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(25, $_SESSION["arrayPermisos"])) { // edit contacto sucursal
        $controles .='<button type="button" class="btn btn-primary btn-sm ttip" onclick="editContactoSucursal(`'. $dataCon->sucursalContactoId .'`)"><i class="fas fa-pencil-alt"></i><span class="ttiptext">Editar</span></button> ';
    }
    if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(26, $_SESSION["arrayPermisos"])) { // del contacto sucursal
        $controles .= '<button type="button" class="btn btn-danger btn-sm ttip" onclick="delContactoSucursal(`'. $dataCon->sucursalContactoId .'`)"><i class="fas fa-trash-alt"></i><span class="ttiptext">Eliminar</span></button>';
    }
    
    $contacto = '
        <b><i class="fas fa-tag"></i> Tipo de contacto: </b> '.$dataCon->tipoContacto.'<br>
        <b><i class="fas fa-address-book"></i> Contacto: </b> '.$dataCon->contactoSucursal.'<br>
        <b><i class="fas fa-edit"></i> Descripción de contacto: </b> '.$dataCon->descripcionCSucursal.'
    ';

    $output['data'][] = array(
        $n, // es #, se dibuja solo en el JS de datatable
        $contacto,
        $controles
    );
}
if($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data'=>'')); 
}
