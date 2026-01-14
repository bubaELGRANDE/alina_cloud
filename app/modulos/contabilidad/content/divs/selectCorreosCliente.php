<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataCorreosCliente = $cloud->rows("
        SELECT
            contactoCliente,
            flgContactoPrincipal
        FROM fel_clientes_contactos
        WHERE tipoContactoId IN (1, 9, 13) AND clienteUbicacionId = ? AND flgDelete = ?
        ORDER BY flgContactoPrincipal DESC
    ", [$_POST['proveedorUbicacionId'], 0]);

    $n = 0;
    foreach($dataCorreosCliente as $correoCliente){
        $n += 1;
        if($correoCliente->flgContactoPrincipal == 1) {
            $selectFacturaCertificacion[] = array("id" => $correoCliente->contactoCliente, "valor" => "$correoCliente->contactoCliente (Principal)");
        } else {
            $selectFacturaCertificacion[] = array("id" => $correoCliente->contactoCliente, "valor" => "$correoCliente->contactoCliente");
        }
    }
    
    if ($n > 0) {
        echo json_encode($selectFacturaCertificacion);
    }else{
        echo json_encode(array('data'=>''));
    }
?>