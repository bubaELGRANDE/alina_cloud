<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
require_once("../../../../../libraries/includes/logic/functions/funciones-conta.php");

@session_start();


if ($_POST['partidaContableId'] > 0) {
    $dataPartida = $cloud->row("
    SELECT partidaContableId,estadoPartidaContable,tipoPartidaId,partidaContaPeriodoId,descripcionPartida,fechaPartida,numPartida,flgFilter
    FROM conta_partidas_contables
    WHERE partidaContableId = ? AND flgDelete = ?", [$_POST['partidaContableId'], 0]);

    if ($dataPartida) {
        $dataPartidasDet = $cloud->rows('
                SELECT 
                    cd.partidaContableDetalleId,
                    cd.partidaContableId,
                    cd.centroCostoId,
                    cd.subCentroCostoId,
                    cd.partidaContaPeriodoId,
                    cd.cuentaContaId,
                    cd.tipoDTEId,
                    cd.documentoId,
                    cd.numDocumento,
                    CONCAT(cc.nombreCentroCosto, " (", cc.codigoCentroCosto, ")") AS centroCosto,
                    CONCAT(su.nombreSubCentroCosto, " (", su.codigoSubCentroCosto, ")") AS subCentroCosto,
                    cd.descripcionPartidaDetalle,
                    cu.numeroCuenta,
                    cu.descripcionCuenta,
                    cd.cargos,
                    cd.abonos,
                    cd.filtroBusqueda
                FROM conta_partidas_contables_detalle cd
                LEFT JOIN conta_centros_costo cc ON cd.centroCostoId = cc.centroCostoId
                LEFT JOIN conta_cuentas_contables cu ON cd.cuentaContaId = cu.cuentaContaId
                LEFT JOIN conta_subcentros_costo su ON cd.subCentroCostoId = su.subCentroCostoId
                WHERE cd.partidaContableId = ? AND cd.flgDelete = ?', [$_POST['partidaContableId'], 0]);

        $n = 0;
        foreach ($dataPartidasDet as $dataPartidaDet) {
            $n += 1;

            /*$jsonEditar = array(
                "typeOperation" => "update",
                "partidaContableId" => $dataPartidas->partidaContableId

            );*/

            $documento = '';

            if ($dataPartidaDet->tipoDTEId > 0 && $dataPartidaDet->documentoId) {
                $documento = getDocumento($dataPartidaDet->tipoDTEId, $dataPartidaDet->documentoId, $dataPartidaDet->numDocumento, $cloud);
            } else {
                $documento = '<i>N/A</i>';
            }

            $cargos = '';
            $abonos = '';

            if ($dataPartidaDet->cargos == 0 && $dataPartidaDet->abonos == 0) {
                $cargos = '<span class="badge bg-danger">0</span>';
                $abonos = '<span class="badge bg-danger">0</span>';
            } else {
                $cargos = '$' . number_format($dataPartidaDet->cargos ?? 0, 2);
                $abonos = '$' . number_format($dataPartidaDet->abonos ?? 0, 2);
            }



            $cuenta = '';
            if ($dataPartidaDet->numeroCuenta) {
                $cuenta = '<b>Codigo :</b> ' . $dataPartidaDet->numeroCuenta . '<br><b>Descripción: </b> ' . $dataPartidaDet->descripcionCuenta;
            } else {
                $cuenta = '<span class="badge bg-danger">Cuenta no encontrada</span>';
            }



            $desc = $dataPartidaDet->descripcionPartidaDetalle ?? '';

           
            if ($dataPartida->flgFilter == 0) {
                $output['data'][] = array(
                    $dataPartidaDet->partidaContableDetalleId, // es #, se dibuja solo en el JS de datatable
                    $n,
                    $cuenta,
                    $documento,
                    $desc,
                    $cargos,
                    $abonos
                );
            } else {
                $output['data'][] = array(
                    $dataPartidaDet->partidaContableDetalleId, // es #, se dibuja solo en el JS de datatable
                    $n,
                    $cuenta,
                    $documento,
                    $desc,
                    $dataPartidaDet->filtroBusqueda,
                    $cargos,
                    $abonos
                );
            }
        } // foreach
        if ($n > 0) {
            echo json_encode($output);
        } else {
            // No retornar nada para evitar error "null"
            echo json_encode(array('data' => ''));
        }


    } else {
        echo json_encode(array('data' => ''));
    }
} else {
    echo json_encode(array('data' => ''));
}



