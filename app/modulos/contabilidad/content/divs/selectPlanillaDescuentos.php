<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataDescuentos = $cloud->rows("
        SELECT
        	catPlanillaDescuentoId, nombreDescuento, codigoContable
        FROM cat_planilla_descuentos
        WHERE catPlanillaDescuentoIdSuperior = ? AND flgDelete = ?
        ORDER BY codigoContable
    ", [$_POST['superiorId'], 0]);
    $n = 0;
    foreach($dataDescuentos as $descuento) {
        $output[] = array("id" => $descuento->catPlanillaDescuentoId, "valor" => "($descuento->codigoContable) $descuento->nombreDescuento");
        $n ++;
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>