<?php

function limitarTexto($texto, $max = 25)
{
    $texto = trim($texto);
    return mb_strlen($texto) > $max
        ? mb_substr($texto, 0, $max) . '...'
        : $texto;
}

function getToteranciaPartidas()
{
    return 0.01;  // ! Acepta diferencias de hasta 1 centavo
}

function numeroPartida($tipoPartida, $periodo, $cloud): int
{
    $last = $cloud->row(
        '
    SELECT MAX(numPartida) AS ultimo
    FROM conta_partidas_contables
    WHERE partidaContaPeriodoId = ? AND tipoPartidaId = ? AND flgDelete = ?',
        [$periodo, $tipoPartida, 0]
    );

    $ultimoNum = $last && $last->ultimo ? (int) $last->ultimo : 0;
    $siguiente = str_pad($ultimoNum + 1, 8, '0', STR_PAD_LEFT);
    return $siguiente;
}

function crearPartida($tipoPartida, $periodo, $numero, $desc, $cloud, $flgFilter = 0, $fechaPartida = null): int
{
    /*
     * $partida:
     *     numPartida: 00000003
     *     periodoPartidas: 5
     *     tipoPartidas: 1
     *     fechaPartida: 2025-05-21
     *     descripcionPartida: ESTE ES UNA PARTIDA DE PRUEBA
     */

    $fhActual = date('Y-m-d');
    $insert = [
        'tipoPartidaId' => $tipoPartida,
        'partidaContaPeriodoId' => $periodo,
        'numPartida' => $numero,
        'estadoPartidaContable' => 'Pendiente',
        'descripcionPartida' => $desc,
        'fechaPartida' => $fechaPartida ?? $fhActual,
        'flgFilter' => $flgFilter ?? 0
    ];
    $partidaContableId = $cloud->insert('conta_partidas_contables', $insert);

    if ($partidaContableId > 0) {
        $cloud->writeBitacora('movInsert', "($fhActual) Insertó una nueva partida contable");
        return $partidaContableId;
    } else {
        return 0;
    }
}

function agregarDetalle($insert, $partida, $cloud)
{
    $partidaContableId = $cloud->insert('conta_partidas_contables_detalle', $insert);

    if ($partidaContableId) {
        $result = $cloud->row('SELECT sum(cargos) AS cargos,sum(abonos) AS abonos FROM conta_partidas_contables_detalle WHERE partidaContableId =  ?  AND flgDelete = ?', [$partida, 0]);

        if ($result) {
            $params = [
                'cargoPartida' => floatval(str_replace(',', '', $result->cargos)),
                'abonoPartida' => floatval(str_replace(',', '', $result->abonos))
            ];
            $whereP = ['partidaContableId' => $partida];
            $cloud->update('conta_partidas_contables', $params, $whereP);
            return true;
        } else {
            return false;
        }
    }
}

// TODOS: si existe, retorna contaPartidaId ? 0
function existPatidaMensual($tipoParitida, $periodo, $cloud): int
{
    $data = $cloud->row(
        'SELECT partidaContableId 
         FROM conta_partidas_contables 
         WHERE tipoPartidaId = ? 
           AND partidaContaPeriodoId = ? 
           AND flgDelete = ?',
        [$tipoParitida, $periodo, 0]
    );

    return ($data && !empty($data->partidaContableId)) ? (int) $data->partidaContableId : 0;
}

function existPartidaDiaria($tipoPartida, $periodo, $cloud): int
{
    $fechaHoy = date('Y-m-d');

    $data = $cloud->row(
        'SELECT partidaContableId 
         FROM conta_partidas_contables 
         WHERE tipoPartidaId = ? 
           AND partidaContaPeriodoId = ? 
           AND fechaPartida = ?
           AND flgDelete = ? 
         LIMIT 1',
        [$tipoPartida, $periodo, $fechaHoy, 0]
    );

    return ($data && !empty($data->partidaContableId)) ? (int) $data->partidaContableId : 0;
}

function existPartidaByFecha($tipoPartida, $fecha, $cloud): int
{
    $data = $cloud->row(
        'SELECT partidaContableId 
         FROM conta_partidas_contables 
         WHERE tipoPartidaId = ? 
           AND fechaPartida = ?
           AND flgDelete = ? 
         LIMIT 1',
        [$tipoPartida, $fecha, 0]
    );

    return ($data && !empty($data->partidaContableId)) ? (int) $data->partidaContableId : 0;
}

function buscarPeriodoActual($cloud): int
{
    $mes = date('m');
    $anio = date('Y');

    $periodoData = $cloud->row('SELECT partidaContaPeriodoId FROM conta_partidas_contables_periodos WHERE mes = ? AND anio = ? AND flgDelete = ?', [$mes, $anio, 0]);

    if ($periodoData->partidaContaPeriodoId) {
        return $periodoData->partidaContaPeriodoId;
    } else {
        return 0;
    }
}

function buscarPeriodoFecha($fecha, $cloud): int
{
    $mes = date('m', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));

    $periodoData = $cloud->row(
        'SELECT partidaContaPeriodoId 
         FROM conta_partidas_contables_periodos 
         WHERE mes = ? AND anio = ? AND flgDelete = ?',
        [$mes, $anio, 0]
    );

    if ($periodoData && $periodoData->partidaContaPeriodoId) {
        return (int) $periodoData->partidaContaPeriodoId;
    } else {
        return 0;
    }
}

function estaFinalizada($partidaConta, $cloud)
{
    $data = $cloud->row(
        'SELECT estadoPartidaContable FROM conta_partidas_contables WHERE partidaContableId = ? AND flgDelete = ?',
        [$partidaConta, 0]
    );

    return !empty($data->estadoPartidaContable) && $data->estadoPartidaContable === 'Finalizada';
}

function getCentroCosComprasExterior($udnId): int
{
    $cuenta = 0;
    switch ($udnId) {
        case 1:  // STIHL
            $cuenta = 5523;  // COMPRAS STIHL
            break;
        case 2:  // Kärcher
            $cuenta = 5519;  // COMPRAS KARCHER
            break;
        case 3:  // Hidropal
            $cuenta = 5518;  // COMPRAS BOMBAS
            break;
        case 4:  // Agropal
            $cuenta = 5524;  // COMPRAS LINEA AGRICOLA
            break;
        case 6:  // Industrial
            $cuenta = 5520;  // COMPRAS EMPAQUETADURAS
            break;
        default:
            $cuenta = 0;  // No encontrado
    }

    return $cuenta;
}


function buscarIdCuenta($numPartida, $cloud): int
{
    $data = $cloud->row(
        'SELECT cuentaContaId FROM conta_cuentas_contables WHERE numeroCuenta = ? AND flgDelete = ?',
        [$numPartida, 0]
    );

    return $data->cuentaContaId ?? 0;
}


// ! Partidas tipoDTEId

/*
 * 1 RETACEOS
 * 2 FACTURAS
 * 3 COSTOS DE RETACEOS
 * 4 COMPRAS
 * 5 DUCAS
 * 6 DOCUMENTO DE LIQUIDACION
 * 7 EXEL
 */

function getTablaDoc($tipoDTEId): string
{
    $yearBD = '_' . date('Y');
    $db = '';
    switch ($tipoDTEId) {
        case 16:  // Retaceos
            $db = "comp_retaceo$yearBD";
            break;
        case 17:  // Compras (DUCA,IMPORTES,ETC.)
            $db = "comp_compras$yearBD";
            break;
        case 9:  // Documento contable de liquidacion (DUCA,IMPORTES,ETC.)
            $db = "comp_compras_dcl$yearBD";
            break;
        default:
            $db = '';
            break;
    }
    return $db;
}

function getDocumento($tipoDTEId, $documentoId, $numDocumento, $cloud, $print = 1): string
{
    $yearBD = '_' . date('Y');
    $doc = '';

    if ($documentoId == 0) {
        $doc = "<b>DOC:</b> $numDocumento";
    } else {
        // $print FORMATO DE IMPRESION 1:TABLA 2:REPORTE IMPRESO 3:EXCEL
        switch ($tipoDTEId) {
            /*case 16:  // Retaceos
                $dataDocumento = $cloud->row("
                SELECT r.numRetaceo,p.numPedido,pr.nombreProveedor 
                FROM comp_retaceo$yearBD r
                LEFT JOIN comp_retaceo_detalle$yearBD rd ON r.retaceoId = rd.retaceoId
                LEFT JOIN comp_compras$yearBD c ON rd.compraId = c.compraId
                LEFT JOIN comp_compras_detalle$yearBD cd ON cd.compraId = c.compraId
                LEFT JOIN comp_pedidos$yearBD p ON cd.pedidoId = p.pedidoId
                LEFT JOIN comp_proveedores pr ON pr.proveedorId = c.proveedorId
                WHERE r.retaceoId = ?
                LIMIT 1", [$documentoId]);

                $doc = "<b>REC:</b> {$dataDocumento->numRetaceo} 
                    <b>PED:</b> {$dataDocumento->numPedido}<br>
                    <b>Proveedor:</b> " . limitarTexto($dataDocumento->nombreProveedor);

                if ($print == 1) {
                    $doc = "<b>REC:</b> {$dataDocumento->numRetaceo} 
                    <b>PED:</b> {$dataDocumento->numPedido}<br>
                    <b>Proveedor:</b> " . limitarTexto($dataDocumento->nombreProveedor);
                } else {
                    $doc = "<b>REC:</b> {$dataDocumento->numRetaceo}<br><b>PED:</b> {$dataDocumento->numPedido}";
                }
                break;*/

            case 17:  // Compras
                $dataDocumento = $cloud->row("
                SELECT cp.compraId,cp.numeroControl,cp.fechaFactura,
                       cp.totalCompra AS total,cpv.nombreProveedor
                FROM comp_compras$yearBD cp
                JOIN comp_proveedores cpv ON cp.proveedorId = cpv.proveedorId
                WHERE cp.flgDelete = 0 AND cp.compraId = ?", [$documentoId]);

                if ($print == 1) {
                    $doc = "<b>CF:</b> {$dataDocumento->numeroControl}<br>
                        <b>Fecha:</b> {$dataDocumento->fechaFactura} 
                        <b>Monto:</b> \$" . number_format($dataDocumento->total ?? 0, 2) . '<br>
                        <b>Proveedor:</b> ' . limitarTexto($dataDocumento->nombreProveedor);
                } else {
                    $doc = "{$dataDocumento->numeroControl}<br>
                        <b>Proveedor:</b> " . limitarTexto($dataDocumento->nombreProveedor);
                }
                break;
            case 1: // FACTURA
                $dataDocumento = $cloud->row('SELECT f.facturaId,m.mgCorrelativoFactura,f.totalFactura
                FROM fel_factura f
                LEFT JOIN fel_factura_magic m ON m.facturaId = f.facturaId
                WHERE f.facturaId = ?', [$documentoId]);

                if ($print == 1) {
                    $doc = "<b>FAC interno: </b>{$dataDocumento->facturaId}<br>
                        <b>FAC Magic: </b>{$dataDocumento->mgCorrelativoFactura} <br>
                        <b>Monto:</b> \$" . number_format($dataDocumento->totalFactura ?? 0, 2);
                } else {
                    $doc = "<b>" . getPrefijoDoc($tipoDTEId) . ":</b> $dataDocumento->nombreProveedor <br>
                    <b>Magic</b>: $dataDocumento->mgCorrelativoFactura";
                }
                break;
            case 2: // CREDITO FISCAL
                $dataDocumento = $cloud->row('SELECT f.facturaId,m.mgCorrelativoFactura,f.totalFactura
                FROM fel_factura f
                LEFT JOIN fel_factura_magic m ON m.facturaId = f.facturaId
                WHERE f.facturaId = ?', [$documentoId]);

                if ($print == 1) {
                    $doc = "<b>CCF interno: </b>{$dataDocumento->facturaId}<br>
                        <b>CCF Magic: </b>{$dataDocumento->mgCorrelativoFactura} <br>
                        <b>Monto:</b> \$" . number_format($dataDocumento->totalFactura ?? 0, 2);
                } else {
                    $doc = "<b>" . getPrefijoDoc($tipoDTEId) . ":</b> $dataDocumento->nombreProveedor <br>
                    <b>Magic</b>: $dataDocumento->mgCorrelativoFactura";
                }
                break;
            case 9: // FACTURA DE EXPORTACION
                $dataDocumento = $cloud->row('SELECT f.facturaId,m.mgCorrelativoFactura,f.totalFactura
                FROM fel_factura f
                LEFT JOIN fel_factura_magic m ON m.facturaId = f.facturaId
                WHERE f.facturaId = ?', [$documentoId]);

                if ($print == 1) {
                    $doc = "<b>FEXP interno: </b>{$dataDocumento->facturaId}<br>
                        <b>FEXP Magic: </b>{$dataDocumento->mgCorrelativoFactura} <br>
                        <b>Monto:</b> \$" . number_format($dataDocumento->totalFactura ?? 0, 2);
                } else {
                    $doc = "<b>" . getPrefijoDoc($tipoDTEId) . ":</b> $dataDocumento->nombreProveedor <br>
                    <b>Magic</b>: $dataDocumento->mgCorrelativoFactura";
                }
                break;
            case 4: // NOTA CREDITO

                $dataDocumento = $cloud->row('SELECT f.facturaId,m.mgCorrelativoFactura,f.totalFactura
                FROM fel_factura f
                LEFT JOIN fel_factura_magic m ON m.facturaId = f.facturaId
                WHERE f.facturaId = ?', [$documentoId]);

                        $dataRelacionado = $cloud->row('SELECT f.facturaIdRelacionada,m.mgCorrelativoFactura
                FROM fel_factura_relacionada f
                LEFT JOIN fel_factura_magic m ON m.facturaId = f.facturaIdRelacionada
                WHERE f.facturaId = ?', [$documentoId]);

            
                if (empty($dataDocumento) || empty($dataRelacionado)) {
                    $doc = "<b>" . getPrefijoDoc($tipoDTEId) . ":</b>$numDocumento";
                    break;
                }

                $facturaId           = $dataDocumento->facturaId ?? '';
                $mgFactura           = $dataDocumento->mgCorrelativoFactura ?? '';
                $facturaRelacionada  = $dataRelacionado->facturaIdRelacionada ?? '';
                $mgRelacionada       = $dataRelacionado->mgCorrelativoFactura ?? '';

                if ($print == 1) {
                    $doc = "<b>NC interno: </b>{$facturaId}<br>
                <b>NC Magic: </b>{$mgFactura}<hr>
                <b>CCF interno: </b>{$facturaRelacionada}<br>
                <b>CCF Magic: </b>{$mgRelacionada}";
                } else {
                    $doc = "<b>" . getPrefijoDoc($tipoDTEId) . ":</b> $facturaId <br>
                <b>Magic: </b> $mgFactura";
                }

                break;

            case 18:  // Compras DUCA
                $dataDocumento = $cloud->row('
                SELECT cp.compraId,cp.numFactura,cp.fechaFactura,
                       cp.totalCompra AS total,cpv.nombreProveedor
                FROM comp_compras_2025 cp
                JOIN comp_proveedores cpv ON cp.proveedorId = cpv.proveedorId
                WHERE cp.flgDelete = 0 AND cp.compraId = ?', [$documentoId]);

                if ($print == 1) {
                    $doc = "<b>DUCA:</b> {$dataDocumento->numFactura}<br>
                        <b>Fecha:</b> {$dataDocumento->fechaFactura} 
                        <b>Monto:</b> \$" . number_format($dataDocumento->total ?? 0, 2) . '<br>
                        <b>Proveedor:</b> ' . limitarTexto($dataDocumento->nombreProveedor);
                } else {
                    $doc = "{$dataDocumento->numFactura}<br>
                        <b>Proveedor:</b> " . limitarTexto($dataDocumento->nombreProveedor);
                }
                break;

            default:
                $doc = "<b>" . getPrefijoDoc($tipoDTEId) . ":</b>$numDocumento";
                break;
        }
    }
    return $doc;
}

function getPrefijoDoc($tipoDTEId): string
{
    switch ($tipoDTEId) {
        case 1:  // 01 Factura  
            return 'FAC';

        case 2:  // 03 Comprobante de crédito fiscal 
            return 'CCF';

        case 3:  // 04 Nota de remisión
            return 'NR';

        case 4:  // 05 Nota de crédito
            return 'NC';

        case 5:  // 06 Nota de débito
            return 'ND';

        case 6:  // 07 Comprobante de retención
            return 'CR';

        case 7:  // 08 Comprobante de liquidación
            return 'CL';

        case 8:  // 09 Documento contable de liquidación
            return 'DCL';

        case 9: // 11 Facturas de exportación
            return 'FEXP';

        case 10: // 14 Factura de sujeto excluido
            return 'FSE';

        case 12: // 15 Comprobante de donación
            return 'CDON';

        case 16:  // Retaceos
            return 'RET';

        case 17:  // COMPRA
            return 'COMP';
        case 18:  // DUCA
            return 'DUCA';
        case 19:  // DUCA
            return 'ABONO';
        default:
            return 'DOC: ';
    }
}


// TODOS: ------------------------------------------------ funciones de generacion de partida--------------------------------------------------------------------------------------

function buscarComprasDiariasPartida($fecha, $cloud): int
{
    $partidaConta = $cloud->row('SELECT partidaContableId FROM conta_partidas_contables WHERE tipoPartidaId = ? AND fechaPartida = ? AND flgDelete = ?', [28, $fecha, 0]);
    if (!empty($partidaConta)) {
        if ($partidaConta->partidaContableId) {
            return $partidaConta->partidaContableId;
        } else {
            return 0;
        }
    } else {
        return 0;
    }
}

function buscarComprasDiariasPartidaFinalizada($fecha, $cloud)
{
    $partidaConta = $cloud->row("SELECT partidaContableId FROM conta_partidas_contables WHERE estadoPartidaContable = 'Finalizada' AND tipoPartidaId = ? AND fechaPartida = ? AND flgDelete = ?", [28, $fecha, 0]);
    if (!empty($partidaConta)) {
        if ($partidaConta->partidaContableId) {
            return 'success';
        } else {
            return 'No es posible generar la liquidación porque existe una partida de compras diarias pendiente. Por favor, finalice la partida de compras diarias antes de continuar con la liquidación.';
        }
    } else {
        return 'No es posible generar la liquidación porque existe una partida de compras diarias pendiente. Por favor, finalice la partida de compras diarias antes de continuar con la liquidación.';
    }
}

// TODOS: ================================================= funciones de partidas automaticas ====================================================================================

// ? Función para liquidar quedan

function liquidacionDeComprasQuedan($pagoTransferenciaId, $cloud)
{
    // numeroControl -- numero a mostrar
    $tipoPartida = 37;  // ! ID tipoPartidaId = LIQUIDACIÓN DE COMPRAS QUEDAN 37
    $periodoId = buscarPeriodoActual($cloud);
    $yearBD = '_' . date('Y');

    $lunesSemanaActual = date('Y-m-d', strtotime('Monday this week'));
    $viernesSemanaActual = date('Y-m-d', strtotime('Friday this week', strtotime('Monday this week')));
    $concepto = "LIQUIDACIÓN DE QUEDAN DEL $lunesSemanaActual AL $viernesSemanaActual";

    if ($periodoId == 0) {
        return 'Lo sentimos, ocurrió un error al obtener los registros del quedan. Por favor, inténtelo nuevamente más tarde.';
    } else {
        $partidaContaId = existPartidaByFecha($tipoPartida, $viernesSemanaActual, $cloud);
        if ($partidaContaId > 0) {
            // ? verificamos si la partida ya esta finalizada
            if (estaFinalizada($partidaContaId, $cloud)) {
                return 'No es posible agregar más detalles a la partida, ya que la partida actual se encuentra finalizada. Por favor, comuníquese con su supervisor para cualquier modificación.';
            }
        } else {
            // ? crea una nueva partida de COMPRAS DE MOTORISTAS
            $numeroPartida = numeroPartida($tipoPartida, $periodoId, $cloud);
            $partidaContaId = crearPartida($tipoPartida, $periodoId, $numeroPartida, $concepto, $cloud, 0, $viernesSemanaActual);
            if ($partidaContaId == 0) {
                return 'Lo sentimos, ocurrió un error al generar la partida de liquidación de quedan. Por favor, inténtelo nuevamente más tarde.';
            }
        }

        // buscar partida de quedan
        $partidaDiaria = existPartidaByFecha(34, $viernesSemanaActual, $cloud);

        $dataTransfer = $cloud->rows(
            'SELECT proveedorCBancariaId,tablaDetalle,tablaDetalleId,montoTransferencia FROM desarrollo_cloud.conta_pagos_transferencias_detalle WHERE pagoTransferenciaId = ? AND flgDelete =?',
            [$pagoTransferenciaId, 0]
        );

        if ($dataTransfer) {
            foreach ($dataTransfer as $trasnferencia) {
                if ($trasnferencia->tablaDetalle == 'comp_quedan') {
                    // ? ES UN QUEDAN
                    $dataQuedan = $cloud->rows('SELECT numDocumentoCompra,fechaDocumentoCompra,totalDocumentoCompra FROM desarrollo_cloud.comp_quedan_detalle WHERE quedanId = ? AND flgDelete = ?', [$trasnferencia->tablaDetalleId, 0]);
                    foreach ($dataQuedan as $quedan) {
                        $dataCompra = $cloud->row(
                            'SELECT compraId,numeroControl FROM desarrollo_cloud.comp_compras_2025 WHERE numeroControl LIKE "%?" AND totalCompra = ? AND fechaDeclaracion = ? AND flgDelete = ?',
                            [$quedan->numDocumentoCompra, $quedan->fechaDocumentoCompra, $quedan->totalDocumentoCompra, 0]
                        );
                        if ($dataCompra) {
                            // $detallePartida = $cloud->row("", );
                        }
                    }
                } else {
                    // ? Otro pago
                }
            }
        } else {
            return 'Lo sentimos, ocurrió un error al generar la partida de compras. Por favor, inténtelo nuevamente más tarde.';
        }
    }
}

// ? Función para crear partidas de vales de combustibles
function comprasValesCombustible($compraId, $cloud, $fecha)
{
    // numeroControl -- numero a mostrar
    $tipoPartida = 15;  // ! ID tipoPartidaId = COMBUSTIBLES VALES 14
    $periodoId = buscarPeriodoFecha($fecha, $cloud);
    $yearBD = '_' . date('Y', strtotime($fecha));

    // Calcular el lunes y viernes de la semana de la fecha dada
    $diaSemana = date('N', strtotime($fecha));  // 1 (lunes) a 7 (domingo)
    $lunesSemana = date('Y-m-d', strtotime($fecha . ' -' . ($diaSemana - 1) . ' days'));
    $viernesSemana = date('Y-m-d', strtotime($lunesSemana . ' +4 days'));

    $concepto = "PARA CONTABILIZAR VALES COMBUSTIBLES DEL $lunesSemana AL $viernesSemana";

    if ($periodoId == 0) {
        return 'Lo sentimos, ocurrió un error al obtener los registros de compra. Por favor, inténtelo nuevamente más tarde.';
    } else {
        $partidaContaId = existPartidaByFecha($tipoPartida, $viernesSemana, $cloud);
        if ($partidaContaId > 0) {
            // ? verificamos si la partida ya esta finalizada
            if (estaFinalizada($partidaContaId, $cloud)) {
                return 'No es posible agregar más detalles a la partida, ya que la partida actual se encuentra finalizada. Por favor, comuníquese con su supervisor para cualquier modificación.';
            }
        } else {
            // ? crea una nueva partida de COMPRAS DE MOTORISTAS
            $numeroPartida = numeroPartida($tipoPartida, $periodoId, $cloud);
            $partidaContaId = crearPartida($tipoPartida, $periodoId, $numeroPartida, $concepto, $cloud, 1, $viernesSemana);
            if ($partidaContaId == 0) {
                return 'Lo sentimos, ocurrió un error al generar la partida de compras. Por favor, inténtelo nuevamente más tarde.';
            }
        }

        $dataCompra = $cloud->row("SELECT numeroControl AS numFactura  FROM comp_compras$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);
        $dataItemCompra = $cloud->rows("SELECT cuentaContableId,concepto,valorAbono as abono, valorCargo as cargo, fechaPago
            FROM comp_compras_contabilidad$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);

        foreach ($dataItemCompra as $item) {
            $insert = [
                'partidaContableId' => $partidaContaId,
                'partidaContaPeriodoId' => $periodoId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => 17,  // TIPO DE COMPRAS
                'documentoId' => $compraId,
                'numDocumento' => $dataCompra->numFactura ?? 0,
                'cuentaContaId' => $item->cuentaContableId ?? null,
                'filtroBusqueda' => (string) $item->fechaPago ?? null,
                'descripcionPartidaDetalle' => $item->concepto ?? '',
                'cargos' => $item->cargo ?? 0,
                'abonos' => $item->abono ?? 0
            ];

            agregarDetalle($insert, $partidaContaId, $cloud);
        }

        return json_encode([
            'status' => 'success',
            'partidaContableId' => $partidaContaId,
            'tipoPartidaId' => $tipoPartida,
        ]);
    }
}

// ? Función para crear partidas de combustibles a motoristas
function comprasMotoriastaCombustibles($compraId, $cloud, $fecha)
{
    $tipoPartida = 14;  // ID tipoPartidaId = COMBUSTIBLES MOTORISTAS 14
    $periodoId = buscarPeriodoFecha($fecha, $cloud);
    $yearBD = '_' . date('Y', strtotime($fecha));

    // Calcular el lunes y viernes de la semana de la fecha dada
    $diaSemana = date('N', strtotime($fecha));  // 1 (lunes) a 7 (domingo)
    $lunesSemana = date('Y-m-d', strtotime($fecha . ' -' . ($diaSemana - 1) . ' days'));
    $viernesSemana = date('Y-m-d', strtotime($lunesSemana . ' +4 days'));

    $concepto = "PARA CONTABILIZAR COMBUSTIBLE DE MOTORISTAS DEL $lunesSemana AL $viernesSemana";

    if ($periodoId == 0) {
        return 'Lo sentimos, ocurrió un error al obtener los registros de compra. Por favor, inténtelo nuevamente más tarde.';
    } else {
        $partidaContaId = existPartidaByFecha($tipoPartida, $viernesSemana, $cloud);
        if ($partidaContaId > 0) {
            // ? verificamos si la partida ya esta finalizada
            if (estaFinalizada($partidaContaId, $cloud)) {
                return 'No es posible agregar más detalles a la partida, ya que la partida actual se encuentra finalizada. Por favor, comuníquese con su supervisor para cualquier modificación.';
            }
        } else {
            // ? crea una nueva partida de COMPRAS DE MOTORISTAS
            $numeroPartida = numeroPartida($tipoPartida, $periodoId, $cloud);
            $partidaContaId = crearPartida($tipoPartida, $periodoId, $numeroPartida, $concepto, $cloud, 1, $viernesSemana);
            if ($partidaContaId == 0) {
                return 'Lo sentimos, ocurrió un error al generar la partida de compras. Por favor, inténtelo nuevamente más tarde.';
            }
        }

        $dataCompra = $cloud->row("SELECT numeroControl AS numFactura  FROM comp_compras$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);
        $dataItemCompra = $cloud->rows("SELECT cuentaContableId,concepto,valorAbono as abono, valorCargo as cargo, fechaPago
        FROM comp_compras_contabilidad$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);

        foreach ($dataItemCompra as $item) {
            $insert = [
                'partidaContableId' => $partidaContaId,
                'partidaContaPeriodoId' => $periodoId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => 17,  // TIPO DE COMPRAS
                'documentoId' => $compraId,
                'numDocumento' => $dataCompra->numFactura ?? 0,
                'cuentaContaId' => $item->cuentaContableId ?? null,
                'descripcionPartidaDetalle' => $item->concepto ?? '',
                'cargos' => $item->cargo ?? 0,
                'abonos' => $item->abono ?? 0
            ];
            agregarDetalle($insert, $partidaContaId, $cloud);
        }
        return json_encode([
            'status' => 'success',
            'partidaContableId' => $partidaContaId,
            'tipoPartidaId' => $tipoPartida,
        ]);
    }
}

// ? Función para crear partidas de compras diarias
function comprasDiariasPartida($compraId, $cloud, $fecha)
{
    // numeroControl -- numero a mostrar
    $tipoPartida = 28;  // ! ID tipoPartidaId = COMPRAS DIARIAS 10
    $periodoId = buscarPeriodoFecha($fecha, $cloud);
    $yearBD = '_' . date('Y');

    $concepto = "REGISTRO DE COMPRAS DEL DÍA $fecha";

    if ($periodoId == 0) {
        return 'Lo sentimos, ocurrió un error al obtener los registros de compra. Por favor, inténtelo nuevamente más tarde.';
    } else {
        $partidaContaId = existPartidaByFecha($tipoPartida, $fecha, $cloud);
        if ($partidaContaId > 0) {
            // ? verificamos si la partida ya esta finalizada
            if (estaFinalizada($partidaContaId, $cloud)) {
                return 'No es posible agregar más detalles a la partida de diario, ya que la partida actual se encuentra finalizada. Por favor, comuníquese con su supervisor para cualquier modificación.';
            }
        } else {
            // ? crea una nueva partida de liquidacion
            $numeroPartida = numeroPartida($tipoPartida, $periodoId, $cloud);
            $partidaContaId = crearPartida($tipoPartida, $periodoId, $numeroPartida, $concepto, $cloud, 1, $fecha);
            if ($partidaContaId == 0) {
                return 'Lo sentimos, ocurrió un error al generar la partida de compras. Por favor, inténtelo nuevamente más tarde.';
            }
        }

        $dataCompra = $cloud->row("SELECT numeroControl AS numFactura  FROM comp_compras$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);
        $dataItemCompra = $cloud->rows("SELECT cuentaContableId,concepto,valorAbono as abono, valorCargo as cargo, fechaPago
        FROM comp_compras_contabilidad$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);

        foreach ($dataItemCompra as $item) {
            $insert = [
                'partidaContableId' => $partidaContaId,
                'partidaContaPeriodoId' => $periodoId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => 17,  // TIPO DE COMPRAS
                'documentoId' => $compraId,
                'numDocumento' => $dataCompra->numFactura ?? 0,
                'cuentaContaId' => $item->cuentaContableId ?? null,
                'descripcionPartidaDetalle' => $item->concepto ?? '',
                'filtroBusqueda' => (string) $item->fechaPago ?? null,
                'cargos' => $item->cargo ?? 0,
                'abonos' => $item->abono ?? 0
            ];

            agregarDetalle($insert, $partidaContaId, $cloud);
        }
        return json_encode([
            'status' => 'success',
            'partidaContableId' => $partidaContaId,
            'tipoPartidaId' => $tipoPartida,
        ]);
    }
}

// ? Función para crear partidas de compras diarias quedan
function comprasDiariasQuedan($compraId, $cloud, $fecha)
{
    // numeroControl -- numero a mostrar
    $tipoPartida = 34;  // ! ID tipoPartidaId = COMPRAS DIARIAS 10
    $periodoId = buscarPeriodoFecha($fecha, $cloud);
    $yearBD = '_' . date('Y');

    $fechaHoy = $fecha;
    $concepto = "REGISTRO DE COMPRAS QUEDAN DEL DÍA $fechaHoy";

    if ($periodoId == 0) {
        return 'Lo sentimos, ocurrió un error al obtener los registros de compra. Por favor, inténtelo nuevamente más tarde.';
    } else {
        $partidaContaId = existPartidaByFecha($tipoPartida, $fecha, $cloud);
        if ($partidaContaId > 0) {
            // ? verificamos si la partida ya esta finalizada
            if (estaFinalizada($partidaContaId, $cloud)) {
                return 'No es posible agregar más detalles a la partida de diario, ya que la partida actual se encuentra finalizada. Por favor, comuníquese con su supervisor para cualquier modificación.';
            }
        } else {
            // ? crea una nueva partida de liquidacion
            $numeroPartida = numeroPartida($tipoPartida, $periodoId, $cloud);
            $partidaContaId = crearPartida($tipoPartida, $periodoId, $numeroPartida, $concepto, $cloud, 1, $fecha);
            if ($partidaContaId == 0) {
                return 'Lo sentimos, ocurrió un error al generar la partida de compras. Por favor, inténtelo nuevamente más tarde.';
            }
        }

        $dataCompra = $cloud->row("SELECT numeroControl AS numFactura  FROM comp_compras$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);
        $dataItemCompra = $cloud->rows("SELECT cuentaContableId,concepto,valorAbono as abono, valorCargo as cargo, fechaPago
        FROM comp_compras_contabilidad$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);

        foreach ($dataItemCompra as $item) {
            $insert = [
                'partidaContableId' => $partidaContaId,
                'partidaContaPeriodoId' => $periodoId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => 17,  // TIPO DE COMPRAS
                'documentoId' => $compraId,
                'numDocumento' => $dataCompra->numFactura ?? 0,
                'cuentaContaId' => $item->cuentaContableId ?? null,
                'descripcionPartidaDetalle' => $item->concepto ?? '',
                'filtroBusqueda' => (string) $item->fechaPago ?? null,
                'cargos' => $item->cargo ?? 0,
                'abonos' => $item->abono ?? 0
            ];

            agregarDetalle($insert, $partidaContaId, $cloud);
        }
        return json_encode([
            'status' => 'success',
            'partidaContableId' => $partidaContaId,
            'tipoPartidaId' => $tipoPartida,
        ]);
    }
}

// ? Función para crear partidas de comisiones bancarias
function comisionesBancarias($compraId, $cloud)
{
    // numeroControl -- numero a mostrar
    $tipoPartida = 32;  // ! ID tipoPartidaId = COMPRAS DIARIAS 10
    $periodoId = buscarPeriodoActual($cloud);
    $yearBD = '_' . date('Y');

    $fechaHoy = date('Y-m-d');
    $concepto = "REGISTRO DE COMISION BANCARIA $fechaHoy";

    if ($periodoId == 0) {
        return 'Lo sentimos, ocurrió un error al obtener los registros de comision. Por favor, inténtelo nuevamente más tarde.';
    } else {
        $partidaContaId = existPartidaDiaria($tipoPartida, $periodoId, $cloud);
        if ($partidaContaId > 0) {
            // ? verificamos si la partida ya esta finalizada
            if (estaFinalizada($partidaContaId, $cloud)) {
                return 'No es posible agregar más detalles a la partida de diario, ya que la partida actual se encuentra finalizada. Por favor, comuníquese con su supervisor para cualquier modificación.';
            }
        } else {
            // ? crea una nueva partida de liquidacion
            $numeroPartida = numeroPartida($tipoPartida, $periodoId, $cloud);
            $partidaContaId = crearPartida($tipoPartida, $periodoId, $numeroPartida, $concepto, $cloud, 1);
            if ($partidaContaId == 0) {
                return 'Lo sentimos, ocurrió un error al generar la partida de compras. Por favor, inténtelo nuevamente más tarde.';
            }
        }

        $dataCompra = $cloud->row("SELECT numeroControl AS numFactura  FROM comp_compras$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);
        $dataItemCompra = $cloud->rows("SELECT cuentaContableId,concepto,valorAbono as abono, valorCargo as cargo, fechaPago
        FROM comp_compras_contabilidad$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);

        foreach ($dataItemCompra as $item) {
            $insert = [
                'partidaContableId' => $partidaContaId,
                'partidaContaPeriodoId' => $periodoId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => 17,  // TIPO DE COMPRAS
                'documentoId' => $compraId,
                'numDocumento' => $dataCompra->numFactura ?? 0,
                'cuentaContaId' => $item->cuentaContableId ?? null,
                'descripcionPartidaDetalle' => $item->concepto ?? '',
                'filtroBusqueda' => (string) $item->fechaPago ?? null,
                'cargos' => $item->cargo ?? 0,
                'abonos' => $item->abono ?? 0
            ];

            agregarDetalle($insert, $partidaContaId, $cloud);
        }
        return json_encode([
            'status' => 'success',
            'partidaContableId' => $partidaContaId,
            'tipoPartidaId' => $tipoPartida,
        ]);
    }
}

// ? Función para crear partidas de comisiones bancarias

function generlaPartidaUnitaria($compraId, $cloud)
{
    // numeroControl -- numero a mostrar
    $tipoPartida = 33;  // ! ID tipoPartidaId = DUCAS 33
    $periodoId = buscarPeriodoActual($cloud);
    $yearBD = '_' . date('Y');

    $fechaHoy = date('Y-m-d');
    $concepto = "REGISTRO DE DUCAS $fechaHoy";

    if ($periodoId == 0) {
        return 'Lo sentimos, ocurrió un error al obtener los registros de comision. Por favor, inténtelo nuevamente más tarde.';
    } else {
        $partidaContaId = existPartidaDiaria($tipoPartida, $periodoId, $cloud);
        if ($partidaContaId > 0) {
            // ? verificamos si la partida ya esta finalizada
            if (estaFinalizada($partidaContaId, $cloud)) {
                return 'No es posible agregar más detalles a la partida de diario, ya que la partida actual se encuentra finalizada. Por favor, comuníquese con su supervisor para cualquier modificación.';
            }
        } else {
            // ? crea una nueva partida de liquidacion
            $numeroPartida = numeroPartida($tipoPartida, $periodoId, $cloud);
            $partidaContaId = crearPartida($tipoPartida, $periodoId, $numeroPartida, $concepto, $cloud, 1);
            if ($partidaContaId == 0) {
                return 'Lo sentimos, ocurrió un error al generar la partida de compras. Por favor, inténtelo nuevamente más tarde.';
            }
        }

        $dataCompra = $cloud->row("SELECT numeroControl AS numFactura  FROM comp_compras$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);

        $dataItemCompra = $cloud->rows("SELECT cuentaContableId,concepto,valorAbono as abono, valorCargo as cargo, fechaPago
        FROM comp_compras_contabilidad$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);

        foreach ($dataItemCompra as $item) {
            $insert = [
                'partidaContableId' => $partidaContaId,
                'partidaContaPeriodoId' => $periodoId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => 18,  // TIPO DE COMPRAS
                'documentoId' => $compraId,
                'numDocumento' => $dataCompra->numFactura ?? 0,
                'cuentaContaId' => $item->cuentaContableId ?? null,
                'descripcionPartidaDetalle' => $item->concepto ?? '',
                'filtroBusqueda' => (string) $item->fechaPago ?? null,
                'cargos' => $item->cargo ?? 0,
                'abonos' => $item->abono ?? 0
            ];

            agregarDetalle($insert, $partidaContaId, $cloud);
        }
        return json_encode([
            'status' => 'success',
            'partidaContableId' => $partidaContaId,
            'tipoPartidaId' => $tipoPartida,
        ]);
    }
}

// ? Funcion para liquidar compras diarias
function liquidacionComprasDiariasPartida($compraDiariaPartida, $cuentaPago, $cloud)
{
    if (estaFinalizada($compraDiariaPartida, $cloud)) {
        $dataCompraDiario = $cloud->row('SELECT fechaPartida,partidaContaPeriodoId,estadoPartidaContable FROM conta_partidas_contables WHERE partidaContableId = ? AND flgDelete = ?', [$compraDiariaPartida, 0]);

        $dataCompraDiariaDet = $cloud->rows('SELECT 
        cuentaContaId,
        partidaContaPeriodoId,
        tipoDTEId,
        documentoId,
        numDocumento,
        descripcionPartidaDetalle,
        cargos,
        filtroBusqueda,
        abonos
    FROM
        conta_partidas_contables_detalle
    WHERE
        partidaContableId = ? AND flgDelete = ?', [$compraDiariaPartida, 0]);

        if ($dataCompraDiariaDet) {
            $tipoPartida = 18;  // ! ID tipoPartidaId = LIQUIDACIÓN DE COMPRAS DIARIAS 18
            $periodoId = $dataCompraDiario->partidaContaPeriodoId;
            $fechaHoy = $dataCompraDiario->fechaPartida;
            $concepto = "LIQUIDACIÓN DE COMPRAS DEL DÍA $fechaHoy";

            $partidaContaId = existPartidaByFecha($tipoPartida, $fechaHoy, $cloud);
            if ($partidaContaId == 0) {
                // ? crea una nueva partida de liquidacion
                $numeroPartida = numeroPartida($tipoPartida, $periodoId, $cloud);
                $partidaContaId = crearPartida($tipoPartida, $periodoId, $numeroPartida, $concepto, $cloud, 1, $fechaHoy);
                if ($partidaContaId == 0) {
                    return 'Lo sentimos, ocurrió un error al generar la partida de liquidación. Por favor, inténtelo nuevamente más tarde.';
                }

                $sumaTotal = 0;
                foreach ($dataCompraDiariaDet as $item) {
                    if ($item->abonos > 0) {
                        $sumaTotal += $item->abonos;
                        $insert = [
                            'partidaContableId' => $partidaContaId,
                            'partidaContaPeriodoId' => $periodoId,
                            'centroCostoId' => 0,
                            'subCentroCostoId' => 0,
                            'tipoDTEId' => $item->tipoDTEId,
                            'documentoId' => $item->documentoId,
                            'numDocumento' => $item->numDocumento ?? 0,
                            'cuentaContaId' => $item->cuentaContaId ?? null,
                            'filtroBusqueda' => (string) $item->filtroBusqueda ?? null,
                            'descripcionPartidaDetalle' => $item->descripcionPartidaDetalle ?? '',
                            'cargos' => $item->abonos ?? 0,
                            'abonos' => 0
                        ];

                        agregarDetalle($insert, $partidaContaId, $cloud);
                    }
                }

                if ($sumaTotal > 0) {
                    $insertP = [
                        'partidaContableId' => $partidaContaId,
                        'partidaContaPeriodoId' => $periodoId,
                        'centroCostoId' => 0,
                        'subCentroCostoId' => 0,
                        'tipoDTEId' => $item->tipoDTEId,
                        'documentoId' => $item->documentoId,
                        'numDocumento' => $item->numDocumento ?? 0,
                        'cuentaContaId' => $cuentaPago,
                        'descripcionPartidaDetalle' => $concepto,
                        'cargos' => 0,
                        'abonos' => $sumaTotal
                    ];

                    agregarDetalle($insertP, $partidaContaId, $cloud);
                    return json_encode([
                        'status' => 'success',
                        'partidaContableId' => $partidaContaId,
                        'tipoPartidaId' => $tipoPartida,
                    ]);
                }
            } else {
                return 'Le informamos que la partida de la fecha seleccionada ya se encuentra liquidada.';
            }
        } else {
            return 'Lo sentimos, no se encontró la partida del registro de compras diarias.';
        }
    } else {
        return 'No es posible liquidar la partida de la fecha seleccionada, ya que la partida actual no está finalizada. Por favor, contabilice todas las compras diarias y finalice la partida antes de continuar.';
    }
}

// ? Función para generar gastos de retaceos
function comprasGastosPedidosExteriorPartida($compraId, $duca, $cloud)
{
    $tipoPartida = 27;  // ! ID tipoPartidaId = GASTOS PEDIDOS DEL EXTERIOR 27
    $periodoId = buscarPeriodoActual($cloud);
    $yearBD = '_' . date('Y');

    $concepto = 'PARA CONTABILIZAR GASTOS DE PEDIDOS DEL EXTERIOR';

    if ($periodoId == 0) {
        return 'Lo sentimos, ocurrió un error al generar la partida de liquidación. Por favor, inténtelo nuevamente más tarde.';
    } else {
        $partidaContaId = existPatidaMensual($tipoPartida, $periodoId, $cloud);
        if ($partidaContaId == 0) {
            // ? crea una nueva partida de liquidacion
            $numeroPartida = numeroPartida($tipoPartida, $periodoId, $cloud);
            $partidaContaId = crearPartida($tipoPartida, $periodoId, $numeroPartida, $concepto, $cloud, 1);
            if ($partidaContaId == 0) {
                return 'Lo sentimos, ocurrió un error al generar la partida de liquidación. Por favor, inténtelo nuevamente más tarde.';
            }
        }

        $dataCompra = $cloud->row("SELECT numeroControl,tipoDTEId,numFactura  FROM comp_compras$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);

        if ($dataCompra->tipoDTEId == 12) {
            $tipoDTEId = 17;
            $documento = $dataCompra->numFactura;
        } else {
            $tipoDTEId = 17;
            $documento = $dataCompra->numeroControl;
        }

        $dataItemCompra = $cloud->rows("SELECT cuentaContableId,concepto,valorAbono as abono, valorCargo as cargo, fechaPago
        FROM comp_compras_contabilidad$yearBD WHERE compraId = ? AND flgDelete = ?", [$compraId, 0]);
        foreach ($dataItemCompra as $item) {
            $insert = [
                'partidaContableId' => $partidaContaId,
                'partidaContaPeriodoId' => $periodoId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => $tipoDTEId,  // Tipo de retaceo
                'documentoId' => $compraId,
                'numDocumento' => $documento,
                'cuentaContaId' => $item->cuentaContableId ?? null,
                'descripcionPartidaDetalle' => $item->concepto ?? '',
                'filtroBusqueda' => (string) $item->fechaPago ?? null,
                'cargos' => $item->cargo ?? 0,
                'abonos' => $item->abono ?? 0
            ];

            agregarDetalle($insert, $partidaContaId, $cloud);
        }

        return json_encode([
            'status' => 'success',
            'partidaContableId' => $partidaContaId,
            'tipoPartidaId' => $tipoPartida,
        ]);
    }
}

// ? Función para generar liquidacion de retaceos
function liquidacionRetaceoPartida($retaceoId, $cloud, $fecha)
{
    // ! ID tipoPartidaId = LIQUIDACIÓN RETACEO 26

    $periodoId = buscarPeriodoFecha($fecha, $cloud);
    $yearBD = '_' . date('Y');

    if ($periodoId == 0) {
        return 'Lo sentimos, ocurrió un error al generar la partida d  ---------' . $fecha;
    } else {
        $partidaContaId = existPatidaMensual(26, $periodoId, $cloud);

        if ($partidaContaId == 0) {
            // ? crea una nueva partida de liquidacion
            $partidaContaId = crearPartida(26, $periodoId, 1, 'LIQUIDACION DE RETACEOS POR MERCADERIA RECIBIDA EN BODEGA', $cloud);
            if ($partidaContaId == 0) {
                return 'Periodo';
            }
        }

        $retaceo = $cloud->row("SELECT totalGasto,costoTotal,totalImporte,totalDAI,totalFlete,numRetaceo FROM comp_retaceo$yearBD WHERE retaceoId = ?", [$retaceoId]);

        if (!$retaceo) {
            return 'rety';
        }

        $dataCuentas = $cloud->row("SELECT p.cuentaContaId_transitoria,p.udnId,p.nombreComercial FROM comp_retaceo_detalle$yearBD r
        JOIN comp_compras$yearBD c ON c.compraId = r.compraId JOIN comp_proveedores p ON p.proveedorId = c.proveedorId
        WHERE r.retaceoId = ? ORDER BY r.compraId ASC LIMIT 1", [$retaceoId]);

        $dataDocumento = $cloud->row('SELECT r.numRetaceo,p.numPedido,pr.nombreProveedor FROM 
        comp_retaceo_2025 r
        LEFT JOIN comp_retaceo_detalle_2025 rd ON r.retaceoId = rd.retaceoId
        LEFT JOIN comp_compras_2025 c ON rd.compraId = c.compraId
        LEFT JOIN comp_compras_detalle_2025 cd ON cd.compraId = c.compraId
        LEFT JOIN comp_pedidos_2025 p ON cd.pedidoId = p.pedidoId
        LEFT JOIN comp_proveedores pr ON pr.proveedorId = c.proveedorId
        WHERE r.retaceoId = ?
        limit 1', [$retaceoId]);

        $gastosData = $cloud->rows('SELECT numDocumento,conceptoCosto,monto FROM comp_retaceo_costos_2025 WHERE retaceoId = ? AND flgDelete = ?', [$retaceoId, 0]);

        $insertAbono = [];

        if ($gastosData) {
            foreach ($gastosData as $gasto) {
                $insertAbono[] = [
                    'partidaContableId' => $partidaContaId,
                    'centroCostoId' => 0,
                    'subCentroCostoId' => 0,
                    'tipoDTEId' => 16,
                    'documentoId' => $retaceoId,
                    'numDocumento' => $retaceo->numRetaceo,
                    'partidaContaPeriodoId' => $periodoId,
                    'cuentaContaId' => $dataCuentas->cuentaContaId_transitoria,
                    'descripcionPartidaDetalle' => $gasto->conceptoCosto . ' DOC: ' . $gasto->numDocumento,
                    'cargos' => 0,
                    'abonos' => $gasto->monto
                ];
            }
        }

        // insert de importe
        if ($retaceo->totalImporte > 0) {
            $insertAbono[] = [
                'partidaContableId' => $partidaContaId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => 16,
                'documentoId' => $retaceoId,
                'numDocumento' => $retaceo->numRetaceo,
                'partidaContaPeriodoId' => $periodoId,
                'cuentaContaId' => $dataCuentas->cuentaContaId_transitoria,
                'descripcionPartidaDetalle' => 'IMPORTE RET.' . $dataDocumento->numRetaceo,
                'cargos' => 0,
                'abonos' => $retaceo->totalImporte
            ];
        }

        // insert de DAI
        if ($retaceo->totalDAI > 0) {
            $insertAbono[] = [
                'partidaContableId' => $partidaContaId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => 16,
                'documentoId' => $retaceoId,
                'numDocumento' => $retaceo->numRetaceo,
                'partidaContaPeriodoId' => $periodoId,
                'cuentaContaId' => $dataCuentas->cuentaContaId_transitoria,
                'descripcionPartidaDetalle' => 'DAI RET.' . $dataDocumento->numRetaceo,
                'cargos' => 0,
                'abonos' => $retaceo->totalDAI
            ];
        }

        // insert de flete
        if ($retaceo->totalFlete > 0) {
            $insertAbono[] = [
                'partidaContableId' => $partidaContaId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => 16,
                'documentoId' => $retaceoId,
                'numDocumento' => $retaceo->numRetaceo,
                'partidaContaPeriodoId' => $periodoId,
                'cuentaContaId' => $dataCuentas->cuentaContaId_transitoria,
                'descripcionPartidaDetalle' => 'IMPORTE  RET.' . $dataDocumento->numRetaceo,
                'cargos' => 0,
                'abonos' => $retaceo->totalFlete
            ];
        }

        foreach ($insertAbono as $insert) {
            agregarDetalle($insert, $partidaContaId, $cloud);
        }

        $cuentaGastos = getCentroCosComprasExterior($dataCuentas->udnId);
        if ($cuentaGastos > 0) {
            $insertCompras = [
                'partidaContableId' => $partidaContaId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => 16,
                'documentoId' => $retaceoId,
                'numDocumento' => $retaceo->numRetaceo,
                'partidaContaPeriodoId' => $periodoId,
                'cuentaContaId' => $cuentaGastos,
                'descripcionPartidaDetalle' => strtoupper($dataCuentas->nombreComercial) . ' MERCADERÍA RECIBIDA EN BODEGA',
                'cargos' => $retaceo->costoTotal,
                'abonos' => 0
            ];

            agregarDetalle($insertCompras, $partidaContaId, $cloud);
        }
        return 'success';
    }
}

function gastosPedidosExterior($retaceoId, $cloud): bool
{
    $tipoPartida = 27;  // ! ID tipoPartidaId = LIQUIDACIÓN RETACEO 27
    $periodoId = buscarPeriodoActual($cloud);
    $yearBD = '_' . date('Y');

    if ($periodoId == 0) {
        return false;
    } else {
        $partidaContaId = existPatidaMensual($tipoPartida, $periodoId, $cloud);

        if ($partidaContaId == 0) {
            // ? crea una nueva partida de liquidacion
            $partidaContaId = crearPartida($tipoPartida, $periodoId, 1, 'PARA CONTABILIZAR GASTOS DE PEDIDOS DEL EXTERIOR', $cloud);
            if ($partidaContaId == 0) {
                return false;
            }
        }

        $cuentaPago = 25;  // ? numCuenta: 1110200101 desc : BANCOAGRICOLA
        $cuentaIva = 4444;  // ? numCuenta: 11205001 desc : IVA CREDITO FISCAL
        $gastos = $cloud->rows('SELECT retaceoCostoId,numDocumento,monto,montoIVA,montoMoneda,conceptoCosto,tipoCosto FROM comp_retaceo_costos_2025 WHERE retaceoId = ? AND flgDelete = ?', [$retaceoId, 0]);
        $retaceo = $cloud->row("SELECT numRetaceo FROM comp_retaceo$yearBD WHERE retaceoId = ?", [$retaceoId]);

        if ($gastos) {
            foreach ($gastos as $gasto) {
                $insertGasto = [
                    'partidaContableId' => $partidaContaId,
                    'centroCostoId' => 0,
                    'subCentroCostoId' => 0,
                    'tipoDTEId' => 0,  // Tipo de gastos de retaceo
                    'documentoId' => 0,
                    'numDocumento' => $gasto->numDocumento,
                    'partidaContaPeriodoId' => $periodoId,
                    'cuentaContaId' => $cuentaPago,
                    'descripcionPartidaDetalle' => strtoupper($gasto->conceptoCosto) . ' RET: ' . $retaceo->numRetaceo,
                    'cargos' => $gasto->monto,
                    'abonos' => 0
                ];

                agregarDetalle($insertGasto, $partidaContaId, $cloud);

                if ($gasto->montoIVA > 0) {
                    $insertIva = [
                        'partidaContableId' => $partidaContaId,
                        'centroCostoId' => 0,
                        'subCentroCostoId' => 0,
                        'tipoDTEId' => 0,  // Tipo de gastos de retaceo
                        'documentoId' => 0,
                        'numDocumento' => $gasto->numDocumento,
                        'partidaContaPeriodoId' => $periodoId,
                        'cuentaContaId' => $cuentaIva,
                        'descripcionPartidaDetalle' => strtoupper($gasto->conceptoCosto) . ' RET: ' . $retaceo->numRetaceo,
                        'cargos' => $gasto->montoIVA,
                        'abonos' => 0
                    ];
                    agregarDetalle($insertIva, $partidaContaId, $cloud);
                }
            }

            return true;
        } else {
            return false;
        }
    }
}


function partidasRepetitivas($cloud, $fecha)
{
    // Normalizar la fecha
    $fechaBase = date('Y-m-d', strtotime($fecha));

    // Mes y año según la fecha que pasamos
    $mes = date('m', strtotime($fechaBase));
    $anio = date('Y', strtotime($fechaBase));

    // Buscar el periodo correspondiente a la fecha
    $periodoData = $cloud->row('
        SELECT partidaContaPeriodoId 
        FROM conta_partidas_contables_periodos 
        WHERE mes = ? AND anio = ? AND flgDelete = 0
        LIMIT 1
    ', [$mes, $anio]);

    if (!$periodoData || !$periodoData->partidaContaPeriodoId) {
        return 'error: no existe el periodo para la fecha seleccionada';
    }

    $periodoId = $periodoData->partidaContaPeriodoId;

    // Primer y último día del mes solicitado
    $primerDia = date('Y-m-01', strtotime($fechaBase));
    $ultimoDia = date('Y-m-t', strtotime($fechaBase));

    // Rango del mes anterior para buscar partidas base
    $primerDiaAnterior = date('Y-m-01', strtotime("$fechaBase -1 month"));
    $primerDiaMes = $primerDia;

    // Tipos de partidas que se repiten
    $tipos = [
        19 => 'PARA CONTABILIZAR DEPRECIACION DE MOBILIARIO Y EQUIPO DEL MES',
        21 => 'CAPITALIZACION DE INTERESES DEL MES',
        23 => 'CUOTA MENSUAL ESTIMADA DE VACACIONES',
        24 => 'CUOTA ESTIMADA DE GRATIFICACION ANUAL',
        25 => 'AMORTIZACION CUOTA DE SEGUROS',
        29 => 'AMORTIZACION MENSUAL DE CLOUD Y ALCALDIA MUNICIPAL S.S.'
    ];

    foreach ($tipos as $tipoId => $concepto) {
        // Buscar partida del mes anterior
        $row = $cloud->row('
            SELECT partidaContableId 
            FROM conta_partidas_contables
            WHERE tipoPartidaId = ?
            AND fechaPartida >= ?
            AND fechaPartida < ?
            LIMIT 1
        ', [$tipoId, $primerDiaAnterior, $primerDiaMes]);

        if ($row && $row->partidaContableId > 0) {
            // Crear nueva partida en el periodo correspondiente
            $numeroPartida = numeroPartida($tipoId, $periodoId, $cloud);
            $partidaContaId = crearPartida($tipoId, $periodoId, $numeroPartida, $concepto, $cloud, 0, $ultimoDia);

            // Duplicar detalles usando el nuevo periodo
            $cloud->run('CALL DuplicarPartidaDetalle(?, ?, ?)', [
                $row->partidaContableId,
                $partidaContaId,
                $periodoId
            ]);
        } else {
            return 'error: no existe partidas del tipo ->' . $tipoId . ' ' . $concepto;
        }
    }

    return 'success';
}


function getCuentaSucursalVentas($sucursal): string
{
    switch ($sucursal) {
        case 1:  // Indupal Casa Matriz
            return '61101001';
        case 2:  // STIHL Center (colocar valor correcto)
            return '61101002';
        case 3:  // Agropal Santa Ana
            return '61101002';
        case 4:  // Indupal Santa Ana
            return '61101002';
        case 12: // Karcher Santa Ana 
            return '61101002';
        case 13: // Indupal San Miguel
            return '61101003';
        case 8:  // Kärcher San Miguel
            return '61101007';
        case 5:  // Hidropal
            return '61101005';
        case 6:  // Indupal División Agrícola
            return '61101006';
        case 7:  // Kärcher Center
            return '61101004';
        default:
            return '00000000';
    }
}

function getCCVentas($udnId): string
{
    switch ($udnId) {
        case 1:
            return '07'; // STIHL
        case 2:
            return '02'; // Karcher
        case 3:
            return '01'; // Hidropal
        case 4:
            return '06'; // Agropal
        case 5:
            return '06'; // Call Center
        case 6:
            return '04'; // Industrial
        case 7:
            return '04'; // Mayoreo
        case 8:
            return '04'; // Promotoria
        case 9:
            return '04'; // Varios
        case 10:
            return '04'; // Chesterton (corrijo ID duplicado)
        default:
            return '05'; // General
    }
}

function buildCuentaContable(string $sucursal, string $cc, string $tipo): string
{
    return $sucursal . $cc . $tipo;
}

// ? Liquidacion de comision por pos
function liquidacionDeComisionPosPartida($cloud, $fecha)
{
    $tipoPartida = 5;  // ! ID tipoPartidaId = VENTAS CONTADO  5
    $periodoId = buscarPeriodoFecha($fecha, $cloud);
    $yearBD = '_' . date('Y');

    if ($periodoId == 0) {
        return 'Lo sentimos, ocurrió un error al obtener los registros del quedan. Por favor, inténtelo nuevamente más tarde.';
    } else {
        $partidaContaId = existPartidaByFecha($tipoPartida, $fecha, $cloud);
        if ($partidaContaId == 0) {
            return 'Lo sentimos, no pudimos encontrar la partida da ventas contado en la fecha ingresada. Por favor, inténtelo nuevamente más tarde.';
        } else {
            $compraData = $cloud->rows(
                'SELECT
                c.compraId,c.numeroControl,c.proveedorId,p.nombreComercial,c.fechaDeclaracion,c.subTotal,c.totalCompra,c.totalIVA 
                FROM comp_compras_2025 c
                LEFT JOIN comp_proveedores p ON p.proveedorId = c.proveedorId
                WHERE c.compraClaseDocumentoId = ?
                AND c.compraClasificacionDetalleId = ?
                AND c.compraCuentaContableId = ?
                AND c.estadoCompra = ?
                AND c.fechaDeclaracion = ?
                AND c.flgDelete = ?',
                [4, 3, 1, 'Finalizado', $fecha, 0]
            );

            if ($compraData) {
                $responseCompra = [];
                $cuentaCaja = 5;
                $total = 0;
                $insert = [];

                foreach ($compraData as $compra) {
                    $dclData = $cloud->row(
                        "SELECT compraDCLId,numeroControl,compraAdjuntoId,subtotal,ivaPercibido,ivaComision,iva,codLiquidacion,periodoLiquidacionInicio FROM comp_compras_dcl$yearBD WHERE compraId = ?",
                        [$compra->compraId]
                    );

                    $cuentaIvaRetinido = 4447;  // ? numCuenta: 11205004 desc : IVA RETENIDO
                    $cuentaGastos = 5961;  // ? numCuenta: 541010010641 desc : COMISIONES A TERCEROS
                    $cuentaIva = 4444;  // ? numCuenta: 11205001 desc : IVA CREDITO FISCAL

                    if ($dclData) {
                        $total += floatval($compra->subTotal)
                            + floatval($compra->totalIVA)
                            + floatval($dclData->ivaPercibido);

                        // ? Insert de la compra
                        $insert[] = [
                            'partidaContableId' => $partidaContaId,
                            'centroCostoId' => 0,
                            'subCentroCostoId' => 0,
                            'tipoDTEId' => 17,  // Tipo de compra
                            'documentoId' => $compra->compraId,
                            'numDocumento' => $compra->numeroControl,
                            'partidaContaPeriodoId' => $periodoId,
                            'cuentaContaId' => $cuentaGastos,
                            'descripcionPartidaDetalle' => $compra->nombreComercial . ' COMISION USO POST',
                            'cargos' => $compra->subTotal,
                            'abonos' => 0
                        ];

                        // ? Insert de la iva compra
                        $insert[] = [
                            'partidaContableId' => $partidaContaId,
                            'centroCostoId' => 0,
                            'subCentroCostoId' => 0,
                            'tipoDTEId' => 17,  // Tipo de compra
                            'documentoId' => $compra->compraId,
                            'numDocumento' => $compra->numeroControl,
                            'partidaContaPeriodoId' => $periodoId,
                            'cuentaContaId' => $cuentaIva,
                            'descripcionPartidaDetalle' => $compra->nombreComercial . ' COMISION USO POST',
                            'cargos' => $compra->totalIVA,
                            'abonos' => 0
                        ];

                        // ? Insert de la iva retenida documento contable de liquidación
                        $insert[] = [
                            'partidaContableId' => $partidaContaId,
                            'centroCostoId' => 0,
                            'subCentroCostoId' => 0,
                            'tipoDTEId' => 9,  // Tipo de compra
                            'documentoId' => $dclData->compraDCLId,
                            'numDocumento' => $dclData->numeroControl,
                            'partidaContaPeriodoId' => $periodoId,
                            'cuentaContaId' => $cuentaIvaRetinido,
                            'descripcionPartidaDetalle' => $compra->nombreComercial . ' IVA RETENIDO',
                            'cargos' => $dclData->ivaPercibido,
                            'abonos' => 0
                        ];
                    } else {
                        $responseCompra[] = 'La compra con el codigo Interno: ' . $compra->compraId . ' , No tiene documento de liquidación.';
                    }
                }

                if ($total > 0) {
                    foreach ($insert as $in) {
                        agregarDetalle($in, $partidaContaId, $cloud);
                    }

                    // ? Inserto Total

                    $insertTotal = [
                        'partidaContableId' => $partidaContaId,
                        'centroCostoId' => 0,
                        'subCentroCostoId' => 0,
                        'tipoDTEId' => 0,  // Tipo de compra
                        'documentoId' => null,
                        'numDocumento' => null,
                        'partidaContaPeriodoId' => $periodoId,
                        'cuentaContaId' => $cuentaCaja,
                        'descripcionPartidaDetalle' => 'COMISION Y RETENCION IVA USO POST',
                        'cargos' => 0,
                        'abonos' => $total
                    ];

                    agregarDetalle($insertTotal, $partidaContaId, $cloud);

                    /* return json_encode([
                        'status' => 'success',
                        'partidaContableId' => $partidaContaId,
                        'tipoPartidaId' => $tipoPartida,
                    ]); */
                } else {
                    return 'Lo sentimos, no se puedo agregar los documentos contables de liquidación.';
                }
            } else {
                return 'Lo sentimos, no pudimos encontrar la partida da ventas contado en la fecha ingresada. Por favor, inténtelo nuevamente más tarde.';
            }
        }
    }
}


// ? Liquidacion de comision por pos
function partidaAutomaticasVentasContado($cloud, $fecha)
{
    $tipoPartida = 5;  // VENTAS CONTADO
    $periodoId = buscarPeriodoFecha($fecha, $cloud);
    $yearBD = '_' . date('Y');

    if ($periodoId == 0) {
        return 'error: Ocurrió un error al obtener el período contable.';
    }

    // Verificar si ya existe partida
    $partidaContaId = existPartidaByFecha($tipoPartida, $fecha, $cloud);
    if ($partidaContaId > 0) {
        return 'error: La partida de ventas contado ya se encuentra generada.';
    }

    // Crear partida
    $numeroPartida = numeroPartida($tipoPartida, $periodoId, $cloud);
    $partidaContaId = crearPartida(
        $tipoPartida,
        $periodoId,
        $numeroPartida,
        'PARA CONTABILIZAR VENTAS AL CONTADO DEL ' . $fecha,
        $cloud,
        0,
        $fecha
    );

    if ($partidaContaId == 0) {
        return 'error: No se pudo crear la partida contable.';
    }

    // =======================================================================================
    // VENTAS CONTADO
    // =======================================================================================

    $dataVentas = $cloud->rows(
        'SELECT 
            f.facturaId,
            f.totalIVA,
            f.subTotal,
            r.ivaRetenido,
            r.ivaPercibido,
            r.rentaRetenido,
            f.tipoDTEId,
            f.totalFactura,
            f.totalTributos,
            c.nombreCliente,
            f.condicionFacturaId,
            e.sucursalId,
            s.sucursal
        FROM fel_factura f
        LEFT JOIN fel_clientes c ON f.clienteId = c.clienteId
        LEFT JOIN fel_factura_emisor e ON f.facturaId = e.facturaId
        LEFT JOIN fel_factura_retenciones r ON f.facturaId = r.facturaId
        LEFT JOIN cat_sucursales s ON e.sucursalId = s.sucursalId
        WHERE f.fechaEmision = ?
            AND f.condicionFacturaId = 1
            AND f.tipoDTEId IN (1,2,5,9)
            AND f.estadoFactura = "Finalizado"
            AND f.flgDelete = 0
        ORDER BY f.horaEmision ASC',
        [$fecha]
    );

    $insert = [];

    foreach ($dataVentas as $venta) {

        $descripcion = 'VENTA A ' . ($venta->nombreCliente ?? '');

        $dataDetVenta = $cloud->rows(
            'SELECT 
                u.udnId,
                SUM(fd.subTotalDetalle) AS subTotal,
                SUM(fd.subTotalDetalleIVA) AS subTotalIVA,
                SUM(fd.totalDetalle) AS totalDetalle
            FROM fel_factura_detalle fd
            LEFT JOIN inv_productos p ON fd.productoId = p.productoId AND p.flgDelete = 0
            LEFT JOIN fel_udn_marcas_detalle md ON p.marcaId = md.marcaId AND md.flgDelete = 0
            LEFT JOIN cat_unidad_negocio u ON md.udnId = u.udnId AND u.flgDelete = 0
            WHERE fd.facturaId = ?
              AND fd.flgDelete = 0
            GROUP BY u.udnId',
            [$venta->facturaId]
        );

        // DETALLES DE VENTAS
        foreach ($dataDetVenta as $det) {

            $numeroCuenta = buildCuentaContable(
                getCuentaSucursalVentas($venta->sucursalId),
                getCCVentas($det->udnId),
                '01'
            );

            $cuentaVentas = buscarIdCuenta($numeroCuenta, $cloud);

            $insert[] = [
                'partidaContableId' => $partidaContaId,
                'partidaContaPeriodoId' => $periodoId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => $venta->tipoDTEId,
                'documentoId' => $venta->facturaId,
                'numDocumento' => $venta->facturaId,
                'cuentaContaId' => $cuentaVentas ?? null,
                'descripcionPartidaDetalle' => $descripcion,
                'cargos' => 0,
                'abonos' => $det->subTotal ?? 0
            ];
        }

        // IVA
        if ($venta->totalIVA > 0) {

            $cuentaIva = ($venta->tipoDTEId == 1) ? 5383 : 5382;

            $insert[] = [
                'partidaContableId' => $partidaContaId,
                'partidaContaPeriodoId' => $periodoId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => $venta->tipoDTEId,
                'documentoId' => $venta->facturaId,
                'numDocumento' => $venta->facturaId,
                'cuentaContaId' => $cuentaIva,
                'descripcionPartidaDetalle' => $descripcion,
                'cargos' => 0,
                'abonos' => $venta->totalIVA
            ];
        }

        // IVA PERCIBIDO
        if ($venta->ivaPercibido > 0) {
            $insert[] = [
                'partidaContableId' => $partidaContaId,
                'partidaContaPeriodoId' => $periodoId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => $venta->tipoDTEId,
                'documentoId' => $venta->facturaId,
                'numDocumento' => $venta->facturaId,
                'cuentaContaId' => 5386,
                'descripcionPartidaDetalle' => $descripcion,
                'cargos' => 0,
                'abonos' => $venta->ivaPercibido
            ];
        }

        // RENTA RETENIDO
        if ($venta->rentaRetenido > 0) {
            $insert[] = [
                'partidaContableId' => $partidaContaId,
                'partidaContaPeriodoId' => $periodoId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => $venta->tipoDTEId,
                'documentoId' => $venta->facturaId,
                'numDocumento' => $venta->facturaId,
                'cuentaContaId' => 5385,
                'descripcionPartidaDetalle' => $descripcion,
                'cargos' => 0,
                'abonos' => $venta->rentaRetenido
            ];
        }

        // TOTAL FACTURA (CARGO)
        $insert[] = [
            'partidaContableId' => $partidaContaId,
            'partidaContaPeriodoId' => $periodoId,
            'centroCostoId' => 0,
            'subCentroCostoId' => 0,
            'tipoDTEId' => $venta->tipoDTEId,
            'documentoId' => $venta->facturaId,
            'numDocumento' => $venta->facturaId,
            'cuentaContaId' => 5,
            'descripcionPartidaDetalle' => $descripcion,
            'cargos' => $venta->totalFactura,
            'abonos' => 0
        ];
    }

    // INSERTAR DETALLES DE VENTAS
    foreach ($insert as $in) {
        agregarDetalle($in, $partidaContaId, $cloud);
    }

    // =======================================================================================
    // NOTAS DE CRÉDITO
    // =======================================================================================

    $dataNotaCredito = $cloud->rows(
        'SELECT 
            f.facturaId,
            f.totalIVA,
            f.subTotal,
            r.ivaRetenido,
            r.ivaPercibido,
            r.rentaRetenido,
            f.tipoDTEId,
            f.totalFactura,
            f.totalTributos,
            c.nombreCliente,
            f.condicionFacturaId,
            e.sucursalId,
            s.sucursal
        FROM fel_factura f
        LEFT JOIN fel_clientes c ON f.clienteId = c.clienteId
        LEFT JOIN fel_factura_emisor e ON f.facturaId = e.facturaId
        LEFT JOIN fel_factura_retenciones r ON f.facturaId = r.facturaId
        LEFT JOIN cat_sucursales s ON e.sucursalId = s.sucursalId
        WHERE f.fechaEmision = ?
            AND f.tipoDTEId = 4
            AND f.estadoFactura = "Finalizado"
            AND f.flgDelete = 0
        ORDER BY f.horaEmision ASC',
        [$fecha]
    );

    $insertNotas = [];

    foreach ($dataNotaCredito as $nota) {

        $descripcion = 'NOTA DE CREDITO A ' . ($nota->nombreCliente ?? '');

        $tipo = $cloud->row(
            "SELECT e.facturaId,e.tipoDTEId,f.condicionFacturaId
             FROM fel_factura_relacionada e
             LEFT JOIN fel_factura f ON e.facturaIdRelacionada = f.facturaId
             WHERE e.facturaId = ?",
            [$nota->facturaId]
        );

        if ($tipo->condicionFacturaId == 1) {

            $dataDetnota = $cloud->rows(
                'SELECT 
                    u.udnId,
                    SUM(fd.subTotalDetalle) AS subTotal,
                    SUM(fd.subTotalDetalleIVA) AS subTotalIVA,
                    SUM(fd.totalDetalle) AS totalDetalle
                FROM fel_factura_detalle fd
                LEFT JOIN inv_productos p ON fd.productoId = p.productoId AND p.flgDelete = 0
                LEFT JOIN fel_udn_marcas_detalle md ON p.marcaId = md.marcaId AND md.flgDelete = 0
                LEFT JOIN cat_unidad_negocio u ON md.udnId = u.udnId AND u.flgDelete = 0
                WHERE fd.facturaId = ?
                  AND fd.flgDelete = 0
                GROUP BY u.udnId',
                [$nota->facturaId]
            );

            // DETALLES NOTA DE CREDITO
            foreach ($dataDetnota as $det) {

                $numeroCuenta = buildCuentaContable(
                    getCuentaSucursalVentas($nota->sucursalId),
                    getCCVentas($det->udnId),
                    '01'
                );

                $cuentaNotas = buscarIdCuenta($numeroCuenta, $cloud);

                $insertNotas[] = [
                    'partidaContableId' => $partidaContaId,
                    'partidaContaPeriodoId' => $periodoId,
                    'centroCostoId' => 0,
                    'subCentroCostoId' => 0,
                    'tipoDTEId' => 4,
                    'documentoId' => $nota->facturaId,
                    'numDocumento' => $nota->facturaId,
                    'cuentaContaId' => $cuentaNotas ?? null,
                    'descripcionPartidaDetalle' => $descripcion,
                    'cargos' => $det->subTotal,
                    'abonos' => 0
                ];
            }

            // IVA NOTA CREDITO
            if ($nota->totalIVA > 0) {

                $cuentaIva = ($nota->tipoDTEId == 1) ? 5383 : 5382;

                $insertNotas[] = [
                    'partidaContableId' => $partidaContaId,
                    'partidaContaPeriodoId' => $periodoId,
                    'centroCostoId' => 0,
                    'subCentroCostoId' => 0,
                    'tipoDTEId' => 4,
                    'documentoId' => $nota->facturaId,
                    'numDocumento' => $nota->facturaId,
                    'cuentaContaId' => $cuentaIva,
                    'descripcionPartidaDetalle' => $descripcion,
                    'cargos' => $nota->totalIVA,
                    'abonos' => 0
                ];
            }

            // IVA PERCIBIDO
            if ($nota->ivaPercibido > 0) {
                $insertNotas[] = [
                    'partidaContableId' => $partidaContaId,
                    'partidaContaPeriodoId' => $periodoId,
                    'centroCostoId' => 0,
                    'subCentroCostoId' => 0,
                    'tipoDTEId' => 4,
                    'documentoId' => $nota->facturaId,
                    'numDocumento' => $nota->facturaId,
                    'cuentaContaId' => 5386,
                    'descripcionPartidaDetalle' => $descripcion,
                    'cargos' => $nota->ivaPercibido,
                    'abonos' => 0
                ];
            }

            // RENTA RETENIDO
            if ($nota->rentaRetenido > 0) {
                $insertNotas[] = [
                    'partidaContableId' => $partidaContaId,
                    'partidaContaPeriodoId' => $periodoId,
                    'centroCostoId' => 0,
                    'subCentroCostoId' => 0,
                    'tipoDTEId' => 4,
                    'documentoId' => $nota->facturaId,
                    'numDocumento' => $nota->facturaId,
                    'cuentaContaId' => 5385,
                    'descripcionPartidaDetalle' => $descripcion,
                    'cargos' => $nota->rentaRetenido,
                    'abonos' => 0
                ];
            }

            // TOTAL FACTURA NOTA
            $insertNotas[] = [
                'partidaContableId' => $partidaContaId,
                'partidaContaPeriodoId' => $periodoId,
                'centroCostoId' => 0,
                'subCentroCostoId' => 0,
                'tipoDTEId' => 4,
                'documentoId' => $nota->facturaId,
                'numDocumento' => $nota->facturaId,
                'cuentaContaId' => 5,
                'descripcionPartidaDetalle' => $descripcion,
                'cargos' => 0,
                'abonos' => $nota->totalFactura
            ];
        }
    }

    foreach ($insertNotas as $in) {
        agregarDetalle($in, $partidaContaId, $cloud);
    }

    // =======================================================================================
    // NOTAS DE ABONO
    // =======================================================================================

    $dataNotasAbono = $cloud->rows(
        "SELECT 
            f.notaAbonoId,
            f.nombreCliente,
            f.totalAbono,
            f.corrNotaAbono,
            cu.cuentaContaId
        FROM cred_notas_abono f
        LEFT JOIN fel_clientes c ON c.nombreCliente = f.nombreCliente
        JOIN conta_cuentas_contables cu ON cu.numeroCuenta = c.codContableCliente
        WHERE f.fechaNotaAbono = ?
          AND f.flgDelete = 0",
        [$fecha]
    );

    foreach ($dataNotasAbono as $abono) {

        $descripcionS = $abono->nombreCliente . " SEGÚN NOTA DE ABONO";

        // ABONO (ABONOS)
        agregarDetalle([
            'partidaContableId' => $partidaContaId,
            'partidaContaPeriodoId' => $periodoId,
            'centroCostoId' => 0,
            'subCentroCostoId' => 0,
            'tipoDTEId' => 19,
            'documentoId' => $abono->notaAbonoId,
            'numDocumento' => $abono->corrNotaAbono,
            'cuentaContaId' => $abono->cuentaContaId ?? 0,
            'descripcionPartidaDetalle' => $descripcionS,
            'cargos' => 0,
            'abonos' => $abono->totalAbono
        ], $partidaContaId, $cloud);

        // CARGO
        agregarDetalle([
            'partidaContableId' => $partidaContaId,
            'partidaContaPeriodoId' => $periodoId,
            'centroCostoId' => 0,
            'subCentroCostoId' => 0,
            'tipoDTEId' => 19,
            'documentoId' => $abono->notaAbonoId,
            'numDocumento' => $abono->corrNotaAbono,
            'cuentaContaId' => 5,
            'descripcionPartidaDetalle' => $descripcionS,
            'cargos' => $abono->totalAbono,
            'abonos' => 0
        ], $partidaContaId, $cloud);
    }

    // =======================================================================================
    // COMPRAS POST
    // =======================================================================================

    $compraData = $cloud->rows(
        'SELECT
            c.compraId,
            c.numeroControl,
            c.proveedorId,
            p.nombreComercial,
            c.fechaDeclaracion,
            c.subTotal,
            c.totalCompra,
            c.totalIVA 
        FROM comp_compras_2025 c
        LEFT JOIN comp_proveedores p ON p.proveedorId = c.proveedorId
        WHERE c.compraClaseDocumentoId = ?
            AND c.compraClasificacionDetalleId = ?
            AND c.compraCuentaContableId = ?
            AND c.estadoCompra = ?
            AND c.fechaDeclaracion = ?
            AND c.flgDelete = 0',
        [4, 3, 1, 'Finalizado', $fecha]
    );

    if ($compraData) {

        $cuentaCaja = 5;
        $insertCompra = [];
        $total = 0;

        foreach ($compraData as $compra) {

            $dclData = $cloud->row(
                "SELECT compraDCLId,numeroControl,subtotal,ivaPercibido,ivaComision,iva
                 FROM comp_compras_dcl$yearBD 
                 WHERE compraId = ?",
                [$compra->compraId]
            );

            $cuentaIvaRetenido = 4447;
            $cuentaGastos = 5961;
            $cuentaIva = 4444;

            if ($dclData) {

                $total += floatval($compra->subTotal)
                    + floatval($compra->totalIVA)
                    + floatval($dclData->ivaPercibido);

                // Compra (Gastos)
                $insertCompra[] = [
                    'partidaContableId' => $partidaContaId,
                    'centroCostoId' => 0,
                    'subCentroCostoId' => 0,
                    'tipoDTEId' => 17,
                    'documentoId' => $compra->compraId,
                    'numDocumento' => $compra->numeroControl,
                    'partidaContaPeriodoId' => $periodoId,
                    'cuentaContaId' => $cuentaGastos,
                    'descripcionPartidaDetalle' => $compra->nombreComercial . ' COMISION USO POST',
                    'cargos' => $compra->subTotal,
                    'abonos' => 0
                ];

                // IVA compra
                $insertCompra[] = [
                    'partidaContableId' => $partidaContaId,
                    'centroCostoId' => 0,
                    'subCentroCostoId' => 0,
                    'tipoDTEId' => 17,
                    'documentoId' => $compra->compraId,
                    'numDocumento' => $compra->numeroControl,
                    'partidaContaPeriodoId' => $periodoId,
                    'cuentaContaId' => $cuentaIva,
                    'descripcionPartidaDetalle' => $compra->nombreComercial . ' COMISION USO POST',
                    'cargos' => $compra->totalIVA,
                    'abonos' => 0
                ];

                // IVA retenido
                $insertCompra[] = [
                    'partidaContableId' => $partidaContaId,
                    'centroCostoId' => 0,
                    'subCentroCostoId' => 0,
                    'tipoDTEId' => 9,
                    'documentoId' => $dclData->compraDCLId,
                    'numDocumento' => $dclData->numeroControl,
                    'partidaContaPeriodoId' => $periodoId,
                    'cuentaContaId' => $cuentaIvaRetenido,
                    'descripcionPartidaDetalle' => $compra->nombreComercial . ' IVA RETENIDO',
                    'cargos' => $dclData->ivaPercibido,
                    'abonos' => 0
                ];
            }
        }

        // Insertar compras
        foreach ($insertCompra as $in) {
            agregarDetalle($in, $partidaContaId, $cloud);
        }

        // TOTAL
        agregarDetalle([
            'partidaContableId' => $partidaContaId,
            'centroCostoId' => 0,
            'subCentroCostoId' => 0,
            'tipoDTEId' => 0,
            'documentoId' => null,
            'numDocumento' => null,
            'partidaContaPeriodoId' => $periodoId,
            'cuentaContaId' => $cuentaCaja,
            'descripcionPartidaDetalle' => 'COMISION Y RETENCION IVA USO POST',
            'cargos' => 0,
            'abonos' => $total
        ], $partidaContaId, $cloud);
    }

    // =======================================================================================
    // RETORNO FINAL
    // =======================================================================================

    return json_encode([
        'status' => 'success',
        'partidaContableId' => $partidaContaId,
        'tipoPartidaId' => $tipoPartida
    ]);
}



function partidaAutomaticasVentasCredito($cloud, $fecha)
{
    $tipoPartida = 1;  // ! ID tipoPartidaId = VENTAS CREDITO  1
    $periodoId = buscarPeriodoFecha($fecha, $cloud);
    $yearBD = '_' . date('Y');

    if ($periodoId == 0) {
        return 'Lo sentimos, ocurrió un error al obtener los registros del quedan. Por favor, inténtelo nuevamente más tarde.';
    } else {
        $partidaContaId = existPartidaByFecha($tipoPartida, $fecha, $cloud);
        if ($partidaContaId > 0) {
            return 'La partida de ventas contado ya se encuentra generada. (Si necesita recalcular porfavor .............)';
        } else {
            $numeroPartida = numeroPartida($tipoPartida, $periodoId, $cloud);
            $partidaContaId = crearPartida($tipoPartida, $periodoId, $numeroPartida, 'PARA CONTABILIZAR VENTAS AL CREDITO DEl ' . $fecha, $cloud, 0, $fecha);
            if ($partidaContaId == 0) {
                return 'Lo sentimos, ocurrió un error al obtener los registros del quedan. Por favor, inténtelo nuevamente más tarde.';
            }
            $dataVentas = $cloud->rows('SELECT 
                f.facturaId,
                f.totalIVA,
                f.subTotal,
                r.ivaRetenido,
                r.ivaPercibido,
                r.rentaRetenido,
                f.tipoDTEId,
                f.totalFactura,
                f.totalTributos,
                c.nombreCliente,
                c.clienteId,
                c.codContableCliente,
                cu.cuentaContaId,
                f.condicionFacturaId,
                e.sucursalId,
                s.sucursal
            FROM
                fel_factura f
                    LEFT JOIN
                fel_clientes c ON f.clienteId = c.clienteId
                    LEFT JOIN
                fel_factura_emisor e ON f.facturaId = e.facturaId
                    LEFT JOIN
                fel_factura_retenciones r ON f.facturaId = r.facturaId
                    LEFT JOIN
                cat_sucursales s ON e.sucursalId = s.sucursalId
                    LEFT JOIN
                conta_cuentas_contables cu ON cu.numeroCuenta = c.codContableCliente
            WHERE
                f.fechaEmision = ?
                AND f.condicionFacturaId = ?
                AND tipoDTEId IN (1,2,5,9)
                AND f.estadoFactura = ?
                AND f.flgDelete = ?
            ORDER BY f.horaEmision ASC', [$fecha, 2, 'Finalizado', 0]);

            $insert = [];
            foreach ($dataVentas as $venta) {

                $descripcion = 'VENTA A ' . $venta->nombreCliente ?? '';

                $dataDetVenta = $cloud->rows('SELECT 
                    u.udnId,
                    u.nombreUDN,
                    u.abreviaturaUDN,
                    SUM(fd.subTotalDetalle)      AS subTotal,
                    SUM(fd.subTotalDetalleIVA)   AS subTotalIVA,
                    SUM(fd.totalDetalle)         AS totalDetalle

                FROM fel_factura_detalle fd
                LEFT JOIN inv_productos p 
                    ON fd.productoId = p.productoId 
                    AND p.flgDelete = 0
                LEFT JOIN fel_udn_marcas_detalle md 
                    ON p.marcaId = md.marcaId 
                    AND md.flgDelete = 0
                LEFT JOIN cat_unidad_negocio u 
                    ON md.udnId = u.udnId 
                    AND u.flgDelete = 0

                WHERE fd.facturaId = ?
                AND fd.flgDelete = 0

                GROUP BY u.udnId', [$venta->facturaId]);


                foreach ($dataDetVenta as $det) {
                    $numeroCuenta = buildCuentaContable(getCuentaSucursalVentas($venta->sucursalId), getCCVentas($det->udnId), '02');
                    $cuentaVentas = buscarIdCuenta($numeroCuenta, $cloud);

                    $insert[] = [
                        'partidaContableId' => $partidaContaId,
                        'partidaContaPeriodoId' => $periodoId,
                        'centroCostoId' => 0,
                        'subCentroCostoId' => 0,
                        'tipoDTEId' => $venta->tipoDTEId,  // TIPO DE FACTURA O CREDITO FISCAL
                        'documentoId' => $venta->facturaId,
                        'numDocumento' => $venta->facturaId,
                        'cuentaContaId' => $cuentaVentas ?? null,
                        'descripcionPartidaDetalle' => $descripcion,
                        'cargos' => 0,
                        'abonos' => $det->subTotal ?? 0
                    ];
                }

                if ($venta->totalIVA > 0) {
                    //? AGREGAR EL IVA
                    if ($venta->tipoDTEId == 1) {
                        $cuentaIva = 5383; //! IVA DEBITO FISCAL CONSUMIDOR
                    } else {
                        $cuentaIva = 5382; //! IVA DEBITO FISCAL CONTRIB.
                    }

                    $insert[] = [
                        'partidaContableId' => $partidaContaId,
                        'partidaContaPeriodoId' => $periodoId,
                        'centroCostoId' => 0,
                        'subCentroCostoId' => 0,
                        'tipoDTEId' => $venta->tipoDTEId,  // TIPO DE FACTURA O CREDITO FISCAL
                        'documentoId' => $venta->facturaId,
                        'numDocumento' => $venta->facturaId,
                        'cuentaContaId' => $cuentaIva,
                        'descripcionPartidaDetalle' => $descripcion,
                        'cargos' => 0,
                        'abonos' => $venta->totalIVA ?? 0
                    ];
                }

                //? IVA PERCIBIDO
                if ($venta->ivaPercibido > 0) {
                    $insert[] = [
                        'partidaContableId' => $partidaContaId,
                        'partidaContaPeriodoId' => $periodoId,
                        'centroCostoId' => 0,
                        'subCentroCostoId' => 0,
                        'tipoDTEId' => $venta->tipoDTEId,  // TIPO DE FACTURA O CREDITO FISCAL
                        'documentoId' => $venta->facturaId,
                        'numDocumento' => $venta->facturaId,
                        'cuentaContaId' => 5386,
                        'descripcionPartidaDetalle' => $descripcion,
                        'cargos' => 0,
                        'abonos' => $venta->ivaPercibido ?? 0
                    ];
                }

                //? RENTA RETENIDO
                if ($venta->rentaRetenido > 0) {
                    $insert[] = [
                        'partidaContableId' => $partidaContaId,
                        'partidaContaPeriodoId' => $periodoId,
                        'centroCostoId' => 0,
                        'subCentroCostoId' => 0,
                        'tipoDTEId' => $venta->tipoDTEId,  // TIPO DE FACTURA O CREDITO FISCAL
                        'documentoId' => $venta->facturaId,
                        'numDocumento' => $venta->facturaId,
                        'cuentaContaId' => 5385,
                        'descripcionPartidaDetalle' => $descripcion,
                        'cargos' => 0,
                        'abonos' => $venta->rentaRetenido ?? 0
                    ];
                }


                //? SUBTOTAL

                $insert[] = [
                    'partidaContableId' => $partidaContaId,
                    'partidaContaPeriodoId' => $periodoId,
                    'centroCostoId' => 0,
                    'subCentroCostoId' => 0,
                    'tipoDTEId' => $venta->tipoDTEId,  // TIPO DE FACTURA O CREDITO FISCAL
                    'documentoId' => $venta->facturaId,
                    'numDocumento' => $venta->facturaId,
                    'cuentaContaId' => $venta->cuentaContaId,
                    'descripcionPartidaDetalle' => $descripcion,
                    'cargos' => $venta->totalFactura,
                    'abonos' => 0
                ];
            }


            $dataNotaCredito = $cloud->rows('SELECT 
                f.facturaId,
                f.totalIVA,
                f.subTotal,
                r.ivaRetenido,
                r.ivaPercibido,
                r.rentaRetenido,
                f.tipoDTEId,
                f.totalFactura,
                f.totalTributos,
                c.nombreCliente,
                c.clienteId,
                c.codContableCliente,
                cu.cuentaContaId,
                f.condicionFacturaId,
                e.sucursalId,
                s.sucursal
            FROM
                fel_factura f
                    LEFT JOIN
                fel_clientes c ON f.clienteId = c.clienteId
                    LEFT JOIN
                fel_factura_emisor e ON f.facturaId = e.facturaId
                    LEFT JOIN
                fel_factura_retenciones r ON f.facturaId = r.facturaId
                    LEFT JOIN
                cat_sucursales s ON e.sucursalId = s.sucursalId
                    LEFT JOIN
                conta_cuentas_contables cu ON cu.numeroCuenta = c.codContableCliente
            WHERE
                f.fechaEmision = ?
                AND tipoDTEId IN (4)
                AND f.estadoFactura = ?
                AND f.flgDelete = ?
            ORDER BY f.horaEmision ASC', [$fecha, 'Finalizado', 0]);

            foreach ($dataNotaCredito as $nota) {

                $descripcion = 'NOTA DE CREDITO A ' . $nota->nombreCliente ?? '';
                $tipo = $cloud->row("SELECT 
                    e.facturaId,
                    e.tipoDTEId,
                    f.condicionFacturaId
                FROM fel_factura_relacionada e
                LEFT JOIN fel_factura f ON e.facturaIdRelacionada = f.facturaId
                WHERE e.facturaId = ?", [$nota->facturaId]);

                if ($tipo->condicionFacturaId == 2) {

                    $dataDetNota = $cloud->rows('SELECT 
                    u.udnId,
                    u.nombreUDN,
                    u.abreviaturaUDN,
                    SUM(fd.subTotalDetalle)      AS subTotal,
                    SUM(fd.subTotalDetalleIVA)   AS subTotalIVA,
                    SUM(fd.totalDetalle)         AS totalDetalle

                    FROM fel_factura_detalle fd
                        LEFT JOIN inv_productos p 
                            ON fd.productoId = p.productoId 
                            AND p.flgDelete = 0
                        LEFT JOIN fel_udn_marcas_detalle md 
                            ON p.marcaId = md.marcaId 
                            AND md.flgDelete = 0
                        LEFT JOIN cat_unidad_negocio u 
                            ON md.udnId = u.udnId 
                            AND u.flgDelete = 0

                        WHERE fd.facturaId = ?
                        AND fd.flgDelete = 0

                        GROUP BY u.udnId', [$venta->facturaId]);


                    foreach ($dataDetNota as $det) {
                        $numeroCuenta = buildCuentaContable(getCuentaSucursalVentas($venta->sucursalId), getCCVentas($det->udnId), '02');
                        $cuentaVentas = buscarIdCuenta($numeroCuenta, $cloud);

                        $insert[] = [
                            'partidaContableId' => $partidaContaId,
                            'partidaContaPeriodoId' => $periodoId,
                            'centroCostoId' => 0,
                            'subCentroCostoId' => 0,
                            'tipoDTEId' => 4,  // TIPO DE FACTURA O CREDITO FISCAL
                            'documentoId' => $nota->facturaId,
                            'numDocumento' => $nota->facturaId,
                            'cuentaContaId' => $cuentaVentas ?? null,
                            'descripcionPartidaDetalle' => $descripcion,
                            'cargos' => $det->subTotal ?? 0,
                            'abonos' => 0
                        ];
                    }

                    if ($venta->totalIVA > 0) {
                        //? AGREGAR EL IVA
                        if ($nota->tipoDTEId = 1) {
                            $cuentaIva = 5383; //! IVA DEBITO FISCAL CONSUMIDOR
                        } else {
                            $cuentaIva = 5382; //! IVA DEBITO FISCAL CONTRIB.
                        }

                        $insert[] = [
                            'partidaContableId' => $partidaContaId,
                            'partidaContaPeriodoId' => $periodoId,
                            'centroCostoId' => 0,
                            'subCentroCostoId' => 0,
                            'tipoDTEId' => 4,  // TIPO DE FACTURA O CREDITO FISCAL
                            'documentoId' => $nota->facturaId,
                            'numDocumento' => $nota->facturaId,
                            'cuentaContaId' => $cuentaIva,
                            'descripcionPartidaDetalle' => $descripcion,
                            'cargos' => $nota->totalIVA ?? 0,
                            'abonos' => 0
                        ];
                    }

                    //? IVA PERCIBIDO
                    if ($nota->ivaPercibido > 0) {
                        $insert[] = [
                            'partidaContableId' => $partidaContaId,
                            'partidaContaPeriodoId' => $periodoId,
                            'centroCostoId' => 0,
                            'subCentroCostoId' => 0,
                            'tipoDTEId' => 4,  // TIPO DE FACTURA O CREDITO FISCAL
                            'documentoId' => $nota->facturaId,
                            'numDocumento' => $nota->facturaId,
                            'cuentaContaId' => 5386,
                            'descripcionPartidaDetalle' => $descripcion,
                            'cargos' => $nota->ivaPercibido ?? 0,
                            'abonos' => 0
                        ];
                    }

                    //? RENTA RETENIDO
                    if ($nota->rentaRetenido > 0) {
                        $insert[] = [
                            'partidaContableId' => $partidaContaId,
                            'partidaContaPeriodoId' => $periodoId,
                            'centroCostoId' => 0,
                            'subCentroCostoId' => 0,
                            'tipoDTEId' => 4,  // TIPO DE FACTURA O CREDITO FISCAL
                            'documentoId' => $nota->facturaId,
                            'numDocumento' => $nota->facturaId,
                            'cuentaContaId' => 5385,
                            'descripcionPartidaDetalle' => $descripcion,
                            'cargos' => $nota->rentaRetenido ?? 0,
                            'abonos' => 0
                        ];
                    }


                    //? SUBTOTAL

                    $insert[] = [
                        'partidaContableId' => $partidaContaId,
                        'partidaContaPeriodoId' => $periodoId,
                        'centroCostoId' => 0,
                        'subCentroCostoId' => 0,
                        'tipoDTEId' => 4,  // TIPO DE FACTURA O CREDITO FISCAL
                        'documentoId' => $nota->facturaId,
                        'numDocumento' => $nota->facturaId,
                        'cuentaContaId' => $nota->cuentaContaId,
                        'descripcionPartidaDetalle' => $descripcion,
                        'cargos' => 0,
                        'abonos' => $nota->totalFactura
                    ];
                }
            }

            foreach ($insert as $in) {
                agregarDetalle($in, $partidaContaId, $cloud);
            }

            return json_encode([
                'status' => 'success',
                'partidaContableId' => $partidaContaId,
                'tipoPartidaId' => $tipoPartida,
            ]);
        }
    }
}


function obtenerTotalesVentasPorFecha($cloud, $fecha)
{
    $totales = [
        'ventasContado' => [
            'subTotal' => 0,
            'iva' => 0,
            'ivaPercibido' => 0,
            'rentaRetenido' => 0,
            'totalFactura' => 0,
            'cantidad' => 0
        ],
        'ventasCredito' => [
            'subTotal' => 0,
            'iva' => 0,
            'ivaPercibido' => 0,
            'rentaRetenido' => 0,
            'totalFactura' => 0,
            'cantidad' => 0
        ],
        'notasCredito' => [
            'subTotal' => 0,
            'iva' => 0,
            'ivaPercibido' => 0,
            'rentaRetenido' => 0,
            'totalFactura' => 0,
            'cantidad' => 0
        ],
        'notasAbono' => [
            'totalAbonos' => 0,
            'cantidad' => 0
        ],
        'comprasPost' => [
            'subtotal' => 0,
            'iva' => 0,
            'ivaPercibido' => 0,
            'total' => 0,
            'cantidad' => 0
        ]
    ];

    // =====================================================================
    // VENTAS CONTADO
    // =====================================================================
    $ventasContado = $cloud->rows(
        'SELECT f.totalIVA, f.subTotal, r.ivaRetenido, r.ivaPercibido, r.rentaRetenido, f.totalFactura
         FROM fel_factura f
         LEFT JOIN fel_factura_retenciones r ON f.facturaId = r.facturaId
         WHERE f.fechaEmision = ?
           AND f.condicionFacturaId = 1
           AND f.tipoDTEId IN (1,2,9)
           AND f.estadoFactura = "Finalizado"
           AND f.flgDelete = 0',
        [$fecha]
    );

    foreach ($ventasContado as $v) {
        $totales['ventasContado']['subTotal'] += $v->subTotal;
        $totales['ventasContado']['iva'] += $v->totalIVA;
        $totales['ventasContado']['ivaPercibido'] += $v->ivaPercibido;
        $totales['ventasContado']['rentaRetenido'] += $v->rentaRetenido;
        $totales['ventasContado']['totalFactura'] += $v->totalFactura;
        $totales['ventasContado']['cantidad']++;
    }

    // =====================================================================
    // VENTAS CRÉDITO
    // =====================================================================
    $ventasCredito = $cloud->rows(
        'SELECT f.totalIVA, f.subTotal, r.ivaRetenido, r.ivaPercibido, r.rentaRetenido, f.totalFactura
         FROM fel_factura f
         LEFT JOIN fel_factura_retenciones r ON f.facturaId = r.facturaId
         WHERE f.fechaEmision = ?
           AND f.condicionFacturaId = 2
           AND f.tipoDTEId IN (1,2,5,9)
           AND f.estadoFactura = "Finalizado"
           AND f.flgDelete = 0',
        [$fecha]
    );

    foreach ($ventasCredito as $v) {
        $totales['ventasCredito']['subTotal'] += $v->subTotal;
        $totales['ventasCredito']['iva'] += $v->totalIVA;
        $totales['ventasCredito']['ivaPercibido'] += $v->ivaPercibido;
        $totales['ventasCredito']['rentaRetenido'] += $v->rentaRetenido;
        $totales['ventasCredito']['totalFactura'] += $v->totalFactura;
        $totales['ventasCredito']['cantidad']++;
    }

    // =====================================================================
    // NOTAS DE CRÉDITO
    // =====================================================================
    $notasCredito = $cloud->rows(
        'SELECT f.totalIVA, f.subTotal, r.ivaRetenido, r.ivaPercibido, r.rentaRetenido, f.totalFactura
         FROM fel_factura f
         LEFT JOIN fel_factura_retenciones r ON f.facturaId = r.facturaId
         WHERE f.fechaEmision = ?
           AND f.tipoDTEId = 4
           AND f.estadoFactura = "Finalizado"
           AND f.flgDelete = 0',
        [$fecha]
    );

    foreach ($notasCredito as $v) {
        $totales['notasCredito']['subTotal'] += $v->subTotal;
        $totales['notasCredito']['iva'] += $v->totalIVA;
        $totales['notasCredito']['ivaPercibido'] += $v->ivaPercibido;
        $totales['notasCredito']['rentaRetenido'] += $v->rentaRetenido;
        $totales['notasCredito']['totalFactura'] += $v->totalFactura;
        $totales['notasCredito']['cantidad']++;
    }

    // =====================================================================
    // NOTAS DE ABONO
    // =====================================================================
    $notasAbono = $cloud->rows(
        "SELECT totalAbono
         FROM cred_notas_abono
         WHERE fechaNotaAbono = ?
           AND flgDelete = 0",
        [$fecha]
    );

    foreach ($notasAbono as $n) {
        $totales['notasAbono']['totalAbonos'] += $n->totalAbono;
        $totales['notasAbono']['cantidad']++;
    }

    // =====================================================================
    // COMPRAS POST
    // =====================================================================
    $comprasPost = $cloud->rows(
        'SELECT subTotal, totalIVA
         FROM comp_compras_2025
         WHERE compraClaseDocumentoId = 4
           AND compraClasificacionDetalleId = 3
           AND compraCuentaContableId = 1
           AND estadoCompra = "Finalizado"
           AND fechaDeclaracion = ?
           AND flgDelete = 0',
        [$fecha]
    );

    foreach ($comprasPost as $c) {
        $totales['comprasPost']['subtotal'] += $c->subTotal;
        $totales['comprasPost']['iva'] += $c->totalIVA;
        $totales['comprasPost']['total'] += ($c->subTotal + $c->totalIVA);
        $totales['comprasPost']['cantidad']++;
    }

    return json_encode([
        'status' => 'success',
        'fecha' => $fecha,
        'totales' => $totales
    ]);
}
