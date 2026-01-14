<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    if($_POST['yearBD'] == "") {
        // FEL actual
        $anioTxt = date("Y");
    } else {
        $anioTxt = str_replace("_", "", $_POST['yearBD']);
    }

    $dataCorreosEnviados = $cloud->rows("
        SELECT tipoEnvio, correo, DATE_FORMAT(fhAdd, '%d/%m/%Y %H:%i:%s') AS fhEnvio FROM bit_fel_correos
        WHERE facturaId = ? AND anio = ? AND flgDelete = ?
    ", [$_POST['facturaId'], $anioTxt, 0]);
    $n = 0;
    foreach ($dataCorreosEnviados as $correoEnviado) {
        $n++;
    
        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $correoEnviado->tipoEnvio,
            $correoEnviado->fhEnvio,
            $correoEnviado->correo
        );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>