<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataRelacion = $cloud->rows("
    SELECT prsRelacionId, personaId1, personaId2, catPrsRelacionId 
    FROM th_personas_relacion 
    WHERE flgDelete = 0 AND personaId2 = ? and personaId1 = ? or personaId2 = ? and personaId1 = ?
    ", [$_POST["idPersona1"], $_POST["idPersona2"], $_POST["idPersona2"], $_POST["idPersona1"]]);

$x=0;
$salida = array();
foreach($dataRelacion as $dataRelacion) {
    
    $salida["prsRelacionId_$x"] = $dataRelacion->prsRelacionId;
    $salida["personaId1_$x"] = $dataRelacion->personaId1;
    $salida["personaId2_$x"] = $dataRelacion->personaId2;
    $salida["relacionId_$x"] = $dataRelacion->catPrsRelacionId;

    $x++;
}

echo json_encode($salida);