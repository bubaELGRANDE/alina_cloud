<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$yearBD = $_POST['yearBD'];

$dataComplemento = $cloud->row("
    SELECT
        facturaId,
        complementoFactura
    FROM fel_factura_complementos$yearBD
    WHERE flgDelete = ? AND facturaComplementoId =  ?
    ", [0,$_POST["facturaComplementoId"]]);

$salida = array(
    "facturaComplementoId"    => $_POST["facturaComplementoId"],
    "facturaId"               => $dataComplemento->facturaId,
    "complementoFactura"      => $dataComplemento->complementoFactura
);
echo json_encode($salida);