<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $nombreCliente = $_POST['nombreCliente'];
    $yearBD = $_POST['yearBD'];

    if ($_POST["tipoDTE"] == '1'){
        $tiempoMax = '90';
    } else {
        $tiempoMax = '1';
    }

    $listaDTE = $cloud->rows("SELECT 
    fel.facturaId, dte.numeroControl, fel.fechaEmision, dte.codigoGeneracion, fc.clienteId
    FROM fel_factura$yearBD fel 
    JOIN fel_factura_certificacion$yearBD dte ON dte.facturaId = fel.facturaId
    LEFT JOIN fel_clientes_ubicaciones fcu ON fcu.clienteUbicacionId = fel.clienteUbicacionId
    LEFT JOIN fel_clientes fc ON fc.clienteId = fcu.clienteId
    WHERE fel.estadoFactura = ? AND fel.tipoDTEId = ? AND fel.flgDelete = ? AND DATEDIFF(CURDATE(), fel.fechaEmision) <= ?  AND (fc.clienteId = ? OR fc.nombreCliente LIKE '$nombreCliente%' OR fc.nombreComercialCliente LIKE '$nombreCliente%') AND dte.facturaCertificacionId = (
            SELECT ffcex.facturaCertificacionId 
            FROM fel_factura_certificacion$yearBD ffcex 
            WHERE (ffcex.estadoCertificacion = 'Certificado' OR ffcex.descripcionMsg LIKE 'RECIBIDO%') AND ffcex.facturaId = fel.facturaId 
            ORDER BY ffcex.facturaCertificacionId DESC 
            LIMIT 1
    )", ["Finalizado", $_POST["tipoDTE"], '0', $tiempoMax,$_POST['clienteId']]);

    $n = 0;
    foreach($listaDTE as $DTE){
        $n += 1;
        $selectFactura[] = array("id" => $DTE->facturaId, "valor" => $DTE->numeroControl);
    }
    
    if ($n > 0) {
        echo json_encode($selectFactura);
    }else{
        echo json_encode(array('data'=>''));
    }