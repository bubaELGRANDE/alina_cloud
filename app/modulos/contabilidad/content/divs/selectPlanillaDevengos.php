<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataDevengos = $cloud->rows("
        SELECT
            catPlanillaDevengoId, nombreDevengo, codigoContable
        FROM cat_planilla_devengos
        WHERE catPlanillaDevengoIdSuperior = ? AND tipoDevengo = ? AND flgDelete = ?
        ORDER BY codigoContable
    ", [$_POST['superiorId'], $_POST['tipoDevengo'], 0]);
    $n = 0;
    foreach($dataDevengos as $devengo) {
        $output[] = array("id" => $devengo->catPlanillaDevengoId, "valor" => "($devengo->codigoContable) $devengo->nombreDevengo");
        $n ++;
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>