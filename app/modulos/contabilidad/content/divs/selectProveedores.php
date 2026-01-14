<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    if(isset($_POST['tipoProveedor'])) {
        if($_POST['tipoProveedor'] == "Extranjero") {
            $whereTipoProveedor = "AND (tipoProveedor = 'Empresa extranjera' OR tipoProveedor = 'Persona extranjera')";
        } else {
            // Local
            $whereTipoProveedor = "AND (tipoProveedor = 'Empresa local' OR tipoProveedor = 'Persona local')";
        }
    } else {
        $whereTipoProveedor = "";
    }

    $getProveedor = $cloud->rows("
        SELECT proveedorId, nombreProveedor FROM comp_proveedores 
        WHERE flgDelete = ? $whereTipoProveedor
        ORDER BY nombreProveedor
    ", [0]);

    $n = 0;
    foreach($getProveedor as $getProveedor){
        $n += 1;
        $proveedores[] = array("id" => $getProveedor->proveedorId, "valor" => $getProveedor->nombreProveedor);
    }
    
    if ($n > 0) {
        echo json_encode($proveedores);
    }else{
        echo json_encode(array('data'=>''));
    }
?>