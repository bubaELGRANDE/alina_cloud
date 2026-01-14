<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

if (isset($_POST['depto'])){
    $id_depto = $_POST['depto'];

    $checkSV = $cloud->row("SELECT paisId FROM cat_paises_departamentos WHERE paisDepartamentoId = ?", [$_POST['depto']]);
    
    $actual = "No";
    if ($checkSV->paisId == 61){
        $actual = "Si";
    }

    $sql_municipio = $cloud->rows("SELECT paisMunicipioId, municipioPais, versionActual FROM cat_paises_municipios
        WHERE paisDepartamentoId = ?", [$id_depto]);
    
    $n = 0;
    foreach($sql_municipio as $municipio) { 
        $n += 1;
        $municipios[] = array("id" => $municipio->paisMunicipioId, "valor" => $municipio->municipioPais);
    }
    if ($n > 0) {
        echo json_encode($municipios);
    }else{
        echo json_encode(array('data'=>''));
    }
}