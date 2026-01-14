<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
if(isset($_POST["txtBuscar"])) {
    $filtroVendedor = $_POST['txtBuscar'];

    $checkVendedor = $cloud->rows("
        SELECT codVendedor 
        FROM conta_comision_compartida_parametrizacion_detalle 
        WHERE comisionCompartidaParamId = ?
        GROUP BY codVendedor
        ", [$_POST['idDParam']]);

    $notIn = '';
    $listaVendedores = '';
    if ($checkVendedor){
        foreach ($checkVendedor as $vendedor){
            $listaVendedores .= $vendedor->codVendedor . ', ';
        }

        $notIn = "AND codVendedor NOT IN (".substr($listaVendedores, 0, -2).")";
    }
    

    $dataVendedor = $cloud->rows("
       SELECT 
            comisionPagarCalculoId,
            codEmpleado,
            codVendedor,
            nombreEmpleado
        FROM conta_comision_pagar_calculo
        WHERE flgDelete = ? AND (codVendedor LIKE '%$filtroVendedor%' OR nombreEmpleado LIKE '%$filtroVendedor%') $notIn
        GROUP BY nombreEmpleado
        LIMIT 50
    ", [0]);
    $n = 0;
    foreach($dataVendedor as $vendedor) {
        $n += 1;
        $jsonVendedor[] = array("id" => $vendedor->comisionPagarCalculoId, "text" => "$vendedor->nombreEmpleado");
    }
    
    if($n > 0) {
        echo json_encode($jsonVendedor);
    }else{
        $json[] = ['id'=>'', 'text'=>'Ingrese NRC o nombre del cliente'];
        echo json_encode($json);
    }
} else {
    $json[] = ['id'=>'', 'text'=>'Ingrese NRC o nombre del cliente'];
    echo json_encode($json);
}

