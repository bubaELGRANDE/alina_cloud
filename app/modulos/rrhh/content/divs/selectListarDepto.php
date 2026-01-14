<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

if (isset($_POST['sucursal'])){

    $sql_deptos = $cloud->rows("SELECT sucursalDepartamentoId, departamentoSucursal FROM cat_sucursales_departamentos 
    WHERE sucursalId = ? AND flgDelete = 0",[$_POST["sucursal"]]);

    // var_dump($sql_deptos);
    if (empty($sql_deptos)){
        $departamentos[] = array("id"=>"", "departamento" => "");
    } else {
        foreach($sql_deptos as $depto) {
            $departamentos[] = array("id" => $depto->sucursalDepartamentoId, "departamento" => $depto->departamentoSucursal);
        }
    }
    echo json_encode($departamentos);
}