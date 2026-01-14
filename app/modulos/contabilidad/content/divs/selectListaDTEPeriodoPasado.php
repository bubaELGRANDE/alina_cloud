<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    if($_POST['cierreDeclaracionId'] == "" || $_POST['sucursalId'] == "" || $_POST['tipoDTEId'] == "" || $_POST['yearBD'] == "") {
        $n = 0;
    } else {
        $dataCierre = $cloud->row("
            SELECT mesNumero FROM fel_cierre_declaracion
            WHERE cierreDeclaracionId = ? AND flgDelete = ?
        ", [$_POST["cierreDeclaracionId"], 0]);

        $anioActual = "_" . date("Y");
        $yearBD = $_POST['yearBD'];
        
        if ($yearBD == $anioActual ){
            $yearBD = '';
        } 

        $dataListarDTE = $cloud->rows("
            SELECT
                finv.facturaId AS facturaId,
                DATE_FORMAT(f.fechaEmision, '%d/%m/%Y') AS fechaEmision,
                ffp.montoPago AS montoPago
            FROM view_factura_invalidada$yearBD finv
            JOIN fel_factura$yearBD f ON f.facturaId = finv.facturaId
            JOIN fel_factura_emisor$yearBD ffe ON ffe.facturaId = finv.facturaId
            JOIN fel_factura_pago$yearBD ffp ON ffp.facturaId = finv.facturaId
            WHERE MONTH(f.fechaEmision) = ? AND ffe.sucursalId = ? AND f.tipoDTEId = ? AND f.estadoFactura = ? AND f.flgDelete = ? AND NOT EXISTS (
                SELECT 1 FROM fel_cierre_declaracion_anulacion fca 
                WHERE fca.facturaId = f.facturaId AND fca.flgDelete = 0
            ) 
        ", [$dataCierre->mesNumero, $_POST['sucursalId'], $_POST['tipoDTEId'], "Anulado", 0]);

        $n = 0;
        foreach($dataListarDTE as $dataListarDTE){
            $n += 1;
            $selectReturn[] = array("id" => $dataListarDTE->facturaId, "valor" => "($dataListarDTE->facturaId) - $dataListarDTE->fechaEmision - $$dataListarDTE->montoPago");
        }
    }

    if ($n > 0) {
        echo json_encode($selectReturn);
    } else {
        echo json_encode(array('data'=>''));
    }
?>