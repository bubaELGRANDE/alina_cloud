<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
$whereProductos = (isset($_POST['busquedaSelect']) && $_POST['busquedaSelect'] != "")
    ? "AND (cp.numeroControl LIKE '%$_POST[busquedaSelect]%' 
            OR cp.numFactura LIKE '%$_POST[busquedaSelect]%' 
            OR cpv.nombreProveedor LIKE '%$_POST[busquedaSelect]%')"
    : "";
$dataCC = $cloud->rows("
    SELECT
        cp.compraId,
        cp.numeroControl,
        cp.numFactura,
        cp.tipoDTEId,
        cpv.nombreProveedor
    FROM comp_compras_2025 cp
    LEFT JOIN comp_proveedores cpv ON cp.proveedorId = cpv.proveedorId
    WHERE 1=1 $whereProductos
    ORDER BY cp.numeroControl ASC
", []);
$json = [];
$json[] = ["id" => 0, "text" => "No documento", "tipoDTEId" => 0];
if ($dataCC) {
    foreach ($dataCC as $row) {
        if ($row->tipoDTEId == 12) {
            $numDoc = $row->numFactura ?: '';
        } else {
            $numDoc = $row->numeroControl ?: $row->numFactura;
        }
        $valor = $numDoc . " - " . ($row->nombreProveedor ?: 'N/A');
        $json[] = [
            "id" => $row->compraId,
            "text" => $valor,
            "tipoDTEId" => $row->tipoDTEId
        ];
    }
} else {
    $json[] = ['id' => '', 'text' => 'No se encontraron resultados...'];
}
echo json_encode($json);
