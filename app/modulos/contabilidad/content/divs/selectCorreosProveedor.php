<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataCorreosProveedor = $cloud->rows("
        SELECT
            contactoProveedor,
            flgContactoPrincipal
        FROM comp_proveedores_contactos
        WHERE tipoContactoId IN (1, 9, 13) AND proveedorUbicacionId = ? AND flgDelete = ?
        ORDER BY flgContactoPrincipal DESC
    ", [$_POST['proveedorUbicacionId'], 0]);

    $n = 0;
    foreach($dataCorreosProveedor as $correoProveedor){
        $n += 1;
        if($correoProveedor->flgContactoPrincipal == 1) {
            $selectFacturaCertificacion[] = array("id" => $correoProveedor->contactoProveedor, "valor" => "$correoProveedor->contactoProveedor (Principal)");
        } else {
            $selectFacturaCertificacion[] = array("id" => $correoProveedor->contactoProveedor, "valor" => "$correoProveedor->contactoProveedor");
        }
    }
    
    if ($n > 0) {
        echo json_encode($selectFacturaCertificacion);
    }else{
        echo json_encode(array('data'=>''));
    }
?>