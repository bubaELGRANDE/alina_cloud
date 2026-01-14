<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataEmpleadoCuentaBanco = $cloud->row("
        SELECT 
            nombreOrganizacionId, numeroCuenta, descripcionCuenta, flgCuentaPlanilla
        FROM th_personas_cbancaria
        WHERE prsCBancariaId = ?
    ", [$_POST["prsCBancariaId"]]);

    $salida = array(
        "nombreOrganizacionId"          => $dataEmpleadoCuentaBanco->nombreOrganizacionId,
        "numeroCuenta"                  => $dataEmpleadoCuentaBanco->numeroCuenta,
        "descripcionCuenta"             => $dataEmpleadoCuentaBanco->descripcionCuenta,
        "flgCuentaPlanilla"             => $dataEmpleadoCuentaBanco->flgCuentaPlanilla,
    );
    echo json_encode($salida);
?>