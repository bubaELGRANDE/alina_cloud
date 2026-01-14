<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $numeroCuenta = $_POST['numeroCuenta'];

    $existe = $cloud->row("SELECT cuentaContaId FROM conta_cuentas_contables WHERE numeroCuenta = ?", [$numeroCuenta]);
    
    echo $existe ? "exists" : "ok";
    
    
?>