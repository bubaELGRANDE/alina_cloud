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

            $jsonEliminar = array(
                "typeOperation" => "delete",
                "operation" => "partida-contable-detalle",
                "id" => $dataPartidaDet->partidaContableDetalleId
            );
            $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));


            $jsonTrasladar = array(
                "partidaId" => $_POST['partidaContableId'],
                "detalleId" => $dataPartidaDet->partidaContableDetalleId
            );
            $funcionTraslado = htmlspecialchars(json_encode($jsonTrasladar));

            $centroDeCostoId = (int) $dataPartidaDet->centroCostoId ?? 0;
            $subCentroCostoId = (int) $dataPartidaDet->subCentroCostoId ?? 0;

            $jsonDuplicar = array(
                "typeOperation" => "insert",
                "operation" => "nueva-partida-contable-detalle",
                "partidaContableId" => $dataPartidaDet->partidaContableId,
                "centroCostoId" => $centroDeCostoId,
                "subCentroCostoId" => $subCentroCostoId,
                "documentoId" => $dataPartidaDet->documentoId,
                "partidaContaPeriodoId" => $dataPartidaDet->partidaContaPeriodoId,
                "cuentaId" => $dataPartidaDet->cuentaContaId,
                "descripcion" => $dataPartidaDet->descripcionPartidaDetalle,
                "cargos" => floatval(str_replace(',', '', $dataPartidaDet->cargos)),
                "abonos" => floatval(str_replace(',', '', $dataPartidaDet->abonos))
            );

            $funcionDuplicar = htmlspecialchars(json_encode($jsonDuplicar));
            $documento = '';

            if ($dataPartidaDet->tipoDTEId > 0 && $dataPartidaDet->numDocumento) {
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



            $centro = '';

            if ($dataPartidaDet->centroCosto) {
                $centro = '<b>Centro:</b> ' . $dataPartidaDet->centroCosto . '<br>' .
                    '<b>Subcentro: </b> ' . ($dataPartidaDet->subCentroCosto == '' ? '----' : $dataPartidaDet->subCentroCosto);
            } else {
                $centro = '<i>(No Aplica)</i>';
            }


            $desc = $dataPartidaDet->descripcionPartidaDetalle ?? '';

            if ($dataPartida->estadoPartidaContable === 'Pendiente') {
                $acciones = '
                <button type="button" onclick="event.stopPropagation(); duplicateItem(' . $funcionDuplicar . ');" class="btn btn-warning btn-sm ttip">
                    <i class="fas fa-copy"></i>
                    <span class="ttiptext">Duplicar</span>
                </button>
                <button type="button" onclick="event.stopPropagation(); deleteDetalle(' . $funcionEliminar . ');" class="btn btn-danger btn-sm ttip">
                    <i class="fas fa-trash-alt"></i>
                    <span class="ttiptext">Eliminar</span>
                </button>';
            } else {
                $acciones = '';
            }

            if (in_array(359, $_SESSION["arrayPermisos"])) {
                $acciones .= '
                <button type="button" onclick="event.stopPropagation(); traladarDetalle(' . $funcionTraslado . ');" class="mt-2 btn btn-info btn-sm ttip">
                    <i class="fas fa-paper-plane"></i>
                    <span class="ttiptext">Trasladar detalle</span>
                </button>';
            }

            if ($dataPartida->flgFilter == 0) {
                $output['data'][] = array(
                    $dataPartidaDet->partidaContableDetalleId, // es #, se dibuja solo en el JS de datatable
                    $n,
                    $cuenta,
                    $centro,
                    $documento,
                    $desc,
                    $cargos,
                    $abonos,
                    $acciones
                );
            } else {
                $output['data'][] = array(
                    $dataPartidaDet->partidaContableDetalleId, // es #, se dibuja solo en el JS de datatable
                    $n,
                    $cuenta,
                    $centro,
                    $documento,
                    $desc,
                    $dataPartidaDet->filtroBusqueda,
                    $cargos,
                    $abonos,
                    $acciones
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



