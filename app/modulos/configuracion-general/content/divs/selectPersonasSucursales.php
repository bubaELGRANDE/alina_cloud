<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    
    $dataPersonaSucursal = $cloud->rows("
        SELECT 
            p.personaId AS personaId,
            CONCAT(p.apellido1, ' ', p.apellido2, ' ', p.nombre1, ' ', p.nombre2) AS nombreCompleto 
        FROM th_personas p
        WHERE p.estadoPersona = ? AND p.flgDelete = ? AND NOT EXISTS (
            SELECT 1 FROM conf_personas_sucursales cps
            WHERE cps.personaId = p.personaId AND cps.flgDelete = 0 AND cps.sucursalId = $_POST[id]
        )
        ORDER BY p.apellido1, p.apellido2, p.nombre1, p.nombre2
    ", ["Activo", 0]);

    $n = 0;
    foreach($dataPersonaSucursal as $dataPersonaSucursal){
        $n += 1;
        $selectPersonaSucursal[] = array("id" => $dataPersonaSucursal->personaId, "valor" => $dataPersonaSucursal->nombreCompleto);
    }
    
    if ($n > 0) {
        echo json_encode($selectPersonaSucursal);
    }else{
        echo json_encode(array('data'=>''));
    }
?>