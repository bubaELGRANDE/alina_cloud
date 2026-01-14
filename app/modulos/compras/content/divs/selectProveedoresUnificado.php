<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

if(isset($_POST['tipoProveedor'])) {
    if($_POST['tipoProveedor'] == "Extranjero") {
        $whereTipoProveedor = "AND (p.tipoProveedor = 'Empresa extranjera' OR p.tipoProveedor = 'Persona extranjera')";
    } else {
        // Local
        $whereTipoProveedor = "AND (p.tipoProveedor = 'Empresa local' OR p.tipoProveedor = 'Persona local')";
    }
} else {
    $whereTipoProveedor = "";
}

$getProveedor = $cloud->rows("
    SELECT 
        p.proveedorId AS proveedorId, 
        pu.proveedorUbicacionId AS proveedorUbicacionId,
        p.nrcProveedor AS nrcProveedor,
        p.nombreProveedor AS nombreProveedor,
        pu.nombreProveedorUbicacion AS nombreProveedorUbicacion
    FROM comp_proveedores p
    LEFT JOIN comp_proveedores_ubicaciones pu ON p.proveedorId = pu.proveedorId
    WHERE p.flgDelete = ? $whereTipoProveedor
    ORDER BY p.nombreProveedor
", [0]);

$proveedores = [];
foreach($getProveedor as $row){
    $nombreUbicacion = $row->nombreProveedorUbicacion ?? '';
    $texto = "({$row->nrcProveedor}) {$row->nombreProveedor}" . ($nombreUbicacion != '' ? " ({$nombreUbicacion})" : "");
    $proveedores[] = array("id" => $row->proveedorId, "valor" => $texto);
}

if (count($proveedores) > 0) {
    echo json_encode($proveedores);
} else {
    echo json_encode(array('data'=>''));
}
?>
