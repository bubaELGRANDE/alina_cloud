<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$whereProductos = (isset($_POST['busquedaSelect']) ? "AND (numeroCuenta LIKE '%$_POST[busquedaSelect]%' OR descripcionCuenta LIKE '%$_POST[busquedaSelect]%')" : "AND cuentaContaId = 0");

$dataCC = $cloud->rows("
        SELECT
            cuentaContaId,
            numeroCuenta,
            descripcionCuenta
        FROM conta_cuentas_contables
        WHERE flgDelete = ? $whereProductos
        ORDER by numeroCuenta ASC", [0]);

$n = 0;
foreach ($dataCC as $CC) {
    $n++;
    $valor = $CC->numeroCuenta . " - " . $CC->descripcionCuenta;
    $json[] = ["id" => $CC->cuentaContaId, "text" => $valor];
}

if ($n == 0) {
    $json[] = ['id' => '', 'text' => 'No se encontraron resultados...'];
    echo json_encode($json);
} else {
    echo json_encode($json);
}
?>