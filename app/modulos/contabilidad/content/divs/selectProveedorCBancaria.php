<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    if(isset($_POST['tipoTransferencia'])) {
        if($_POST['tipoTransferencia'] == "Local") {
            $whereTipoTransferencia = "AND pc.nombreOrganizacionId = $_POST[bancoId]";
        } else {
            $whereTipoTransferencia = "AND pc.nombreOrganizacionId <> $_POST[bancoId]";
        }
    } else {
        $whereTipoTransferencia = "";
    }

    $dataProveedorCBancaria = $cloud->rows("
        SELECT
        	pc.proveedorCBancariaId AS proveedorCBancariaId, 
        	pc.nombreOrganizacionId AS nombreOrganizacionId, 
        	pc.numeroCuenta AS numeroCuenta, 
        	pc.descripcionCuenta AS descripcionCuenta,
            org.nombreOrganizacion AS nombreOrganizacion,
            org.abreviaturaOrganizacion AS abreviaturaOrganizacion
        FROM comp_proveedores_cbancaria pc
        JOIN cat_nombres_organizaciones org ON org.nombreOrganizacionId = pc.nombreOrganizacionId
        WHERE pc.proveedorId = ? AND pc.flgDelete = ? 
        $whereTipoTransferencia
        ORDER BY org.nombreOrganizacion
    ", [$_POST['proveedorId'], 0]);

    $n = 0;
    foreach($dataProveedorCBancaria as $proveedorCBancaria){
        $n += 1;
        $proveedores[] = array("id" => $proveedorCBancaria->proveedorCBancariaId, "valor" => "$proveedorCBancaria->numeroCuenta ($proveedorCBancaria->abreviaturaOrganizacion)");
    }
    
    if($n > 0) {
        echo json_encode($proveedores);
    } else {
        echo json_encode(array('data'=>''));
    }
?>