<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $idProveedor = $_POST['id'];

    $getProveedor = $cloud->rows("SELECT 
                                proveedorUbicacionId, nombreProveedorUbicacion 
                                FROM comp_proveedores_ubicaciones 
                                WHERE proveedorId = ? AND flgDelete = ?", [$idProveedor, 0]);

    $n = 0;
    foreach($getProveedor as $getProveedor){
        $n += 1;
        $proveedores[] = array("id" => $getProveedor->proveedorUbicacionId, "valor" => $getProveedor->nombreProveedorUbicacion);
    }
    
    if ($n > 0) {
        echo json_encode($proveedores);
    }else{
        echo json_encode(array('data'=>''));
    }
?>