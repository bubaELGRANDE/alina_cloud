<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    // $anio = $_POST['expedienteAmonestacionId']; // Obtén el año desde el POST

    $getAmonestacion = $cloud->rows("
    SELECT 
        tea.expedienteAmonestacionId AS expedienteAmonestacionId,
        date_format(tea.fechaAmonestacion, '%d/%m/%Y')  AS fechaAmonestacion,
        p.nombreCompleto AS nombreCompleto
    FROM th_expediente_amonestaciones tea
    JOIN view_expedientes p ON p.prsExpedienteId = tea.expedienteId
    WHERE tea.flgDelete = ?  AND tea.estadoAmonestacion = ? AND YEAR(tea.fechaAmonestacion) = ?
", [0, 'Activo',$_POST['anioAmonestacion']]);
$n = 0;
foreach($getAmonestacion as $amonestacion) {
    $n++;
    $selectReturn[] = array("id" => $amonestacion->expedienteAmonestacionId, "valor" =>  str_pad($amonestacion->expedienteAmonestacionId, 3, '0', STR_PAD_LEFT) . " - $amonestacion->fechaAmonestacion - $amonestacion->nombreCompleto");
}

    if($n > 0) {
        echo json_encode($selectReturn);
    }else{
        echo json_encode(array('data'=>''));
    }