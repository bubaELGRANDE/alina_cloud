<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

	$arrayMeses = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

    $dataQuincenas = $cloud->rows("
        SELECT
        	quincenaId,
        	numQuincena,
        	mes
        FROM cat_quincenas
        WHERE anio = ? AND flgDelete = ?
        ORDER BY mes, numQuincena
    ", [$_POST['anio'], 0]);
    $n = 0;
    foreach($dataQuincenas as $quincenas) {
    	$mes = $arrayMeses[$quincenas->mes];
        $output[] = array("id" => $quincenas->quincenaId, "valor" => "Q$quincenas->numQuincena - $mes");
        $n ++;
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>