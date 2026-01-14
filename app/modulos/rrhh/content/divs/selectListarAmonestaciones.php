<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

if (isset($_POST['expedienteId'])){

    $sqlAmo = $cloud->rows("
    SELECT 
        expedienteAmonestacionId, 
        causaFalta, 
        FROM th_expediente_amonestaciones 
        WHERE expedienteId = ? AND flgDelete = 0 AND estadoAmonestacion = 'Activo'
        ",[$_POST["expedienteId"]]);

    // var_dump($sql_deptos);
    if (empty($sqlAmo)){
        $amonestaciones[] = array("id"=>"", "departamento" => "");
    } else {
        foreach($sql_deptos as $depto) {
            $amonestaciones[] = array("id" => $sqlAmo->expedienteAmonestacionId, "amonestacion" => $sqlAmo->departamentoScausaFaltaucursal);
        }
    }
    echo json_encode($amonestaciones);
}