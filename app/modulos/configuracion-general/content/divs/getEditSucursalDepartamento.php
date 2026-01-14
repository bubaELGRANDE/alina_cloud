<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataEditDepartamento = $cloud->row("
        SELECT
            codSucursalDepartamento,
            departamentoSucursal
        FROM cat_sucursales_departamentos
        WHERE sucursalDepartamentoId = ?
    ", [$_POST["sucursalDepartamentoId"]]);

    $output = array(
        "departamentoSucursal"      => $dataEditDepartamento->departamentoSucursal,
        "codSucursalDepartamento"   => $dataEditDepartamento->codSucursalDepartamento
    );
    echo json_encode($output);
?>