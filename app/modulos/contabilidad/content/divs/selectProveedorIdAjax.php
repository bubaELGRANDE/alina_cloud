<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    if(isset($_POST["txtBuscar"])) {
        $filtroProveedor = $_POST['txtBuscar'];

        if($_POST['tipoProveedor'] == "Local") {
            $whereTipoProveedor = "AND tipoProveedor IN ('Empresa local', 'Persona local')";
        } else {
            $whereTipoProveedor = "AND tipoProveedor IN ('Empresa extranjera', 'Persona extranjera')";
        }

        $dataProveedores = $cloud->rows("
            SELECT 
                proveedorId,
                nrcProveedor,
                numDocumento,
                nombreProveedor
            FROM comp_proveedores 
            WHERE estadoProveedor = ? AND flgDelete = ? AND (nrcProveedor LIKE '%$filtroProveedor%' OR numDocumento LIKE '%$filtroProveedor%' OR nombreProveedor LIKE '%$filtroProveedor%' OR nombreComercial LIKE '%$filtroProveedor%')
            ORDER BY nombreProveedor
            LIMIT 50
        ", ['Activo', 0]);
        $n = 0;
        foreach($dataProveedores as $proveedor) {
            $n += 1;
            $jsonProveedores[] = array("id" => $proveedor->proveedorId, "text" => "($proveedor->nrcProveedor) $proveedor->nombreProveedor [$proveedor->numDocumento]");
        }
        
        if($n > 0) {
            echo json_encode($jsonProveedores);
        }else{
            $json[] = ['id'=>'', 'text'=>'NRC, NIT o Nombre del proveedor'];
            echo json_encode($json);
        }
    } else {
        $json[] = ['id'=>'', 'text'=>'NRC, NIT o Nombre del proveedor'];
        echo json_encode($json);
    }
?>