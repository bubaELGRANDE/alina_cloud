<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$idCuentaDetalle = $_POST['partidaContableDetalleId'];

if (!$idCuentaDetalle) {
    echo json_encode(null);
    exit;
}

 
$rows = $cloud->row("
SELECT 
    p.partidaContableDetalleId,
    p.centroCostoId,
    p.SubCentroCostoId,
    p.descripcionPartidaDetalle,
    p.cuentaContaId,
    p.abonos,
    p.cargos,
    p.documentoId,
    cc.descripcionCuenta,
    cc.numeroCuenta,
    c.numeroControl,
    c.numFactura,
    c.tipoDTEId,
    cpv.nombreProveedor
FROM conta_partidas_contables_detalle p
LEFT JOIN conta_cuentas_contables cc ON p.cuentaContaId = cc.cuentaContaId
LEFT JOIN comp_compras_2025 c ON p.documentoId = c.compraId
LEFT JOIN comp_proveedores cpv ON c.proveedorId = cpv.proveedorId
WHERE p.partidaContableDetalleId = ? AND p.flgDelete = ?", [$idCuentaDetalle, 0]);
if ($rows) {
    echo json_encode($rows);
}
