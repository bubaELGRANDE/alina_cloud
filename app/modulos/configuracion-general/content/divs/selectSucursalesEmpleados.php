<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $getSucursalEmpleado = $cloud->rows("
    SELECT 
        s.sucursalId AS sucursalId, 
        s.sucursal AS sucursal,
        s.paisMunicipioId AS paisMunicipioId,
        s.direccionSucursal AS direccionSucursal
    FROM cat_sucursales s
    WHERE s.flgDelete = ? AND s.sucursalId NOT IN (
        SELECT cps.sucursalId FROM conf_personas_sucursales cps
        WHERE cps.sucursalId = s.sucursalId AND cps.personaId = ? AND cps.flgDelete = ?
    )
    ORDER BY sucursal
", [0, $_POST["personaId"], 0]);

$n = 0;
foreach($getSucursalEmpleado as $getSucursalEmpleado){
    $n += 1;
    $sucursales[] = array("id" => $getSucursalEmpleado->sucursalId, "valor" => $getSucursalEmpleado->sucursal);
}

if ($n > 0) {
    echo json_encode($sucursales);
}else{
    echo json_encode(array('data'=>''));
}
?>