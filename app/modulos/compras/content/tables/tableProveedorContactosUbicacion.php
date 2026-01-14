<?php 
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataContactos = $cloud->rows("
    SELECT 
        sc.proveedorContactoId AS proveedorContactoId,
        sc.proveedorUbicacionId AS proveedorUbicacionId, 
        sc.tipoContactoId AS tipoContactoId,
        (SELECT tc.tipoContacto FROM cat_tipos_contacto tc WHERE tc.tipoContactoId = sc.tipoContactoId LIMIT 1) AS tipoContacto,
        sc.contactoProveedor AS contactoProveedor, 
        sc.descripcionProveedorContacto AS descripcionProveedorContacto
    FROM comp_proveedores_contactos sc
    WHERE sc.flgDelete = 0 AND sc.proveedorUbicacionId = ?
", [$_POST["proveedorUbicacionId"]]);
    
$n = 0;
foreach($dataContactos as $dataCon){
    $n += 1;
    $jsonEliminar = array(
        "typeOperation"           => 'delete',
        "operation"               => 'contacto-proveedor-ubicacion',
        "proveedorContactoId"   => $dataCon->proveedorContactoId,
        "contactoProveedor"     => $dataCon->contactoProveedor,
        "nombreProveedor"       => $_POST["nombreProveedor"]
    );
    $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));
    
    $acciones = '';
    if($_POST['estadoProveedorUbicacion'] == "Activo") {
        if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(120, $_SESSION["arrayPermisos"])) {
            $acciones .='<button type="button" class="btn btn-primary btn-sm ttip" onclick="editContactoUbicacion('. $dataCon->proveedorContactoId .');"><i class="fas fa-pencil-alt"></i><span class="ttiptext">Editar</span></button> ';
        } else {
            // No tiene permisos
        }
        if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(121, $_SESSION["arrayPermisos"])) {
            $acciones .= '<button type="button" class="btn btn-danger btn-sm ttip" onclick="delContactoUbicacion('. $funcionEliminar.')"><i class="fas fa-trash-alt"></i><span class="ttiptext">Eliminar</span></button>';
        } else {
            // No tiene permisos
        }
    } else {
        // Proveedor ubicacion inactivo
    }
    $contacto = '
        <b><i class="fas fa-tag"></i> Tipo de contacto: </b> '.$dataCon->tipoContacto.'<br>
        <b><i class="fas fa-address-book"></i> Contacto: </b> '.$dataCon->contactoProveedor.'<br>
        <b><i class="fas fa-edit"></i> Descripción de contacto: </b> '.$dataCon->descripcionProveedorContacto.'
    ';

    $output['data'][] = array(
        $n, // es #, se dibuja solo en el JS de datatable
        $contacto,
        $acciones
    );
}
if($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data'=>'')); 
}
