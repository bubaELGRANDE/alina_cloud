<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $sql_deptos = $cloud->rows("SELECT sucursalDepartamentoId, departamentoSucursal FROM cat_sucursales_departamentos 
    WHERE sucursalId = ? AND flgDelete = 0",[$_POST["sucursal"]]);

    // var_dump($sql_deptos);
    if (empty($sql_deptos)){
        $departamentos[] = array("id"=>"", "valor" => "");
    } else {
        foreach($sql_deptos as $depto) {
            $departamentos[] = array("id" => $depto->sucursalDepartamentoId, "valor" => $depto->departamentoSucursal);
        }
    }
    echo json_encode($departamentos);
?>