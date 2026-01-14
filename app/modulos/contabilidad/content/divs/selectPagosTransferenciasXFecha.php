<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataFechaTransferencias = $cloud->rows("
        SELECT
        	cpt.pagoTransferenciaId AS pagoTransferenciaId,
        	cpt.tipoTransferencia AS tipoTransferencia,
        	org.abreviaturaOrganizacion AS abreviaturaOrganizacion,
        	org.nombreOrganizacion AS nombreOrganizacion,
        	(
        		SELECT 
        			SUM(cptd.montoTransferencia)
        		FROM conta_pagos_transferencias_detalle cptd
        		WHERE cptd.pagoTransferenciaId = cpt.pagoTransferenciaId AND cptd.flgDelete = 0
        	) AS totalTransferencia
        FROM conta_pagos_transferencias cpt
        JOIN cat_nombres_organizaciones org ON org.nombreOrganizacionId = cpt.nombreOrganizacionId
        WHERE cpt.estadoPago = ? AND cpt.fechaPagoTransferencia = ? AND cpt.flgDelete = ?
        ORDER BY cpt.nombreOrganizacionId, cpt.tipoTransferencia
    ", ["Finalizado", $_POST['fechaPagoTransferencia'], 0]);

    $n = 0;
    foreach($dataFechaTransferencias as $transferenciaFecha) {
        $n++;
        $selectTransferencias[] = array(
        	"id" => $transferenciaFecha->pagoTransferenciaId, 
        	"valor" => "{$transferenciaFecha->abreviaturaOrganizacion} - {$transferenciaFecha->tipoTransferencia} ({$_SESSION['monedaSimbolo']} ".number_format($transferenciaFecha->totalTransferencia, 2, ".", ",").")"
       	);
    }
    
    if ($n > 0) {
        echo json_encode($selectTransferencias);
    }else{
        echo json_encode(array('data'=>''));
    }
?>