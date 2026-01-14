<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $getBodegasEmpleado = $cloud->rows("
        SELECT 
            s.bodegaId AS bodegaId, 
            s.sucursalId AS sucursalId,
            s.codSucursalBodega AS codSucursalBodega,
            s.bodegaSucursal AS bodegaSucursal,
            cps.personaSucursalId AS personaSucursalId,
            cps.personaId AS personaId,
            cs.sucursal AS sucursal,
            cs.sucursalId AS sucursalId
        FROM cat_sucursales_bodegas s
        JOIN cat_sucursales cs ON cs.sucursalId = s.sucursalId
        JOIN conf_personas_sucursales cps ON cps.sucursalId = cs.sucursalId
        WHERE cps.personaId = ? AND s.flgDelete = ? AND cps.flgDelete = ? AND s.bodegaId NOT IN (
            SELECT cpb.bodegaId 
            FROM conf_personas_sucursales_bodegas cpb
            WHERE cpb.personaSucursalId = cps.personaSucursalId 
              AND cpb.flgDelete = 0
        )
        ORDER BY cs.sucursalId, s.bodegaId
    ", [$_POST["personaId"], 0, 0]);

$n = 0;
foreach($getBodegasEmpleado as $getBodegasEmpleado){
    $n += 1;
    $bodegas[] = array("id" => $getBodegasEmpleado->bodegaId, "valor" =>($getBodegasEmpleado->sucursal) ." - ".  $getBodegasEmpleado->bodegaSucursal);
}

if ($n > 0) {
    echo json_encode($bodegas);
}else{
    echo json_encode(array('data'=>''));
}
?>