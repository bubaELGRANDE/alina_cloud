<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
if(isset($_POST["txtBuscar"])) {
    $filtroProducto = $_POST['txtBuscar'];

    $dataCliente = $cloud->rows("
        SELECT 
            comisionPagarCalculoId,
            numRegistroCliente,
            nombreCliente
        FROM conta_comision_pagar_calculo
        WHERE flgDelete = ? AND (numRegistroCliente LIKE '%$filtroProducto%' OR nombreCliente LIKE '%$filtroProducto%')
        GROUP BY nombreCliente
        LIMIT 50
    ", [0]);
    $n = 0;
    foreach($dataCliente as $cliente) {
        $n += 1;
        $jsonCliente[] = array("id" => $cliente->comisionPagarCalculoId, "text" => "($cliente->numRegistroCliente) $cliente->nombreCliente");
    }
    
    if($n > 0) {
        echo json_encode($jsonCliente);
    }else{
        $json[] = ['id'=>'', 'text'=>'Ingrese NRC o nombre del cliente'];
        echo json_encode($json);
    }
} else {
    $json[] = ['id'=>'', 'text'=>'Ingrese NRC o nombre del cliente'];
    echo json_encode($json);
}

