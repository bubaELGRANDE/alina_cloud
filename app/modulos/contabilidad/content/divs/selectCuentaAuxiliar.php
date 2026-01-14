<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $cuentaPadreId = $_POST['cuentaPadreId'];

    $padre = $cloud->row("SELECT numeroCuenta FROM conta_cuentas_contables WHERE cuentaContaId = ?", [$cuentaPadreId]);
    
    $numeroCuentaMayor = $padre->numeroCuenta;
    
    // Buscar el último auxiliar generado bajo esa cuenta mayor
    $lastAuxiliar = $cloud->row("
        SELECT MAX(CAST(SUBSTRING(numeroCuenta, LENGTH(?) + 1) AS UNSIGNED)) AS lastAux
        FROM conta_cuentas_contables
        WHERE cuentaPadreId = ? AND tipoCuenta = 'Auxiliar'
    ", [$numeroCuentaMayor, $cuentaPadreId]);
    
    $nextCorrelativo = ($lastAuxiliar && $lastAuxiliar->lastAux) ? $lastAuxiliar->lastAux + 1 : 1;
    
    // Concatenar como auxiliar: mayor + correlativo de 3 dígitos
    $numeroAuxiliar = $numeroCuentaMayor . str_pad($nextCorrelativo, 2, '0', STR_PAD_LEFT);
    
    echo $numeroAuxiliar;

    
    
?>