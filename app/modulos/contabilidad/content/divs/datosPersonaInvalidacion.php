<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataPersona = $cloud->row("
    SELECT personaId, docIdentidad, numIdentidad FROM view_expedientes
    WHERE estadoPersona = 'Activo' AND estadoExpediente = 'Activo' AND personaId = ?
    ", [$_POST['id']]);

        $selectPersona = array(
            "id" => $dataPersona->personaId, 
            "docIdentidad" => $dataPersona->docIdentidad, 
            "numIdentidad" => $dataPersona->numIdentidad
        );
    
        echo json_encode($selectPersona);
?>