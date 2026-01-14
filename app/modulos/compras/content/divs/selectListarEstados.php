<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

if (isset($_POST['pais'])){
    $paisId = $_POST['pais'];

    $sql_depto = $cloud->rows("SELECT paisDepartamentoId, departamentoPais FROM cat_paises_departamentos
        WHERE  paisId = ?", [$paisId]);
    $n = 0;

    foreach($sql_depto as $departamento) { $n += 1;
        $departamentos[] = array("id" => $departamento->paisDepartamentoId, "departamento" => $departamento->departamentoPais);
    }
    if ($n > 0) {
        echo json_encode($departamentos);
    }else{
        echo json_encode(array('data'=>''));
    }
    
}