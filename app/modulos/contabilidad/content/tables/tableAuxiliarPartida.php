<?php
require_once ('../../../../../libraries/includes/logic/mgc/datos94.php');
require_once ('../../../../../libraries/includes/logic/functions/funciones-conta.php');
@session_start();

ini_set('memory_limit', '-1');

function isFechaYmd($s)
{
    if (!$s)
        return false;
    $dt = DateTime::createFromFormat('Y-m-d', $s);
    return $dt && $dt->format('Y-m-d') === $s;
}

$cuentaIdInicio = isset($_POST['cuentaIdInicio']) ? (int) $_POST['cuentaIdInicio'] : 0;
$fechaInicio = $_POST['fechaInicio'] ?? '';
$fechaFin = $_POST['fechaFin'] ?? '';
$condiciones = ['cd.flgDelete = 0 AND pc.flgDelete = 0'];
$agruparPorDia = isset($_POST['agruparPorDia']) ? (int) $_POST['agruparPorDia'] : 0;

$params = [];
if ($cuentaIdInicio > 0) {
    $condiciones[] = 'cu.numeroCuenta = ?';
    $params[] = $cuentaIdInicio;
    $fiOk = isFechaYmd($fechaInicio);
    $ffOk = isFechaYmd($fechaFin);
    if ($fiOk && $ffOk) {
        $condiciones[] = 'pc.fechaPartida BETWEEN ? AND ?';
        $params[] = $fechaInicio;
        $params[] = $fechaFin;
    } elseif ($fiOk) {
        $condiciones[] = 'pc.fechaPartida >= ?';
        $params[] = $fechaInicio;
    } elseif ($ffOk) {
        $condiciones[] = 'pc.fechaPartida <= ?';
        $params[] = $fechaFin;
    }
    $whereSQL = implode(' AND ', $condiciones);

    if ($agruparPorDia === 1) {
        $sql = "SELECT 
                pc.fechaPartida,
                SUM(cd.cargos) AS totalCargos,
                SUM(cd.abonos) AS totalAbonos
            FROM conta_partidas_contables_detalle cd
            JOIN conta_partidas_contables pc 
                 ON cd.partidaContableId = pc.partidaContableId
            JOIN conta_cuentas_contables cu 
                 ON cd.cuentaContaId = cu.cuentaContaId
            WHERE {$whereSQL}
            GROUP BY pc.fechaPartida
            ORDER BY pc.fechaPartida ASC";

        $rows = $cloud->rows($sql, $params);

        $n = 0;
        $op = (float) ($rSaldo->v ?? 0.0);
        $output = ['data' => []];

        foreach ($rows as $row) {
            $saldoI = '$' . number_format($op, 2);
            $op += ($row->totalCargos - $row->totalAbonos);
            $saldoR = '$' . number_format($op, 2);

            $n++;
            $output['data'][] = [
                $n,
                '<b>Día:</b> ' . $row->fechaPartida,
                '-',
                '-',
                '-',
                $row->fechaPartida,
                '-',
                'Resumen diario',
                $saldoI,
                '$' . number_format($row->totalCargos, 2),
                '$' . number_format($row->totalAbonos, 2),
                $saldoR,
                '-'
            ];
        }

        echo json_encode($output);
        exit;
    }

    $sql = "SELECT 
    cd.partidaContableDetalleId,
    pc.tipoPartidaId,
    pc.numPartida,
    t.descripcionPartida AS tipoPartida,
    pc.partidaContableId,
    pc.fechaPartida,
    cd.partidaContableDetalleId,
    pc.numPartida,
    cd.descripcionPartidaDetalle,
    cd.cargos,
    cd.abonos,
    cu.tipoMayoreo,
    cd.partidaContaPeriodoId,
    cu.saldoFinal,
    cu.categoriaCuenta,
    pc.fechaPartida,
    cd.tipoDTEId,
    cd.documentoId,
    cd.numDocumento,
    CONCAT(cp.mesNombre, ' ', cp.anio) AS periodo
FROM
    conta_partidas_contables_detalle cd
        LEFT JOIN
    conta_centros_costo cc ON cd.centroCostoId = cc.centroCostoId
        LEFT JOIN
    conta_partidas_contables pc ON cd.partidaContableId = pc.partidaContableId
        LEFT JOIN
    conta_partidas_contables_periodos cp ON cd.partidaContaPeriodoId = cp.partidaContaPeriodoId
        LEFT JOIN
    conta_cuentas_contables cu ON cd.cuentaContaId = cu.cuentaContaId
        LEFT JOIN
    conta_subcentros_costo su ON cd.subCentroCostoId = su.subCentroCostoId
        LEFT JOIN
    cat_tipo_partida_contable t ON t.tipoPartidaId = pc.tipoPartidaId
    WHERE {$whereSQL}
    ORDER BY pc.fechaPartida, cd.partidaContableDetalleId ASC";
    $dataDetalle = $cloud->rows($sql, $params);
    $periodoIdParaSaldo = 0;
    if ($fiOk) {
        $r = $cloud->row(
            'SELECT pc.partidaContaPeriodoId AS v
           FROM conta_partidas_contables pc
          WHERE pc.fechaPartida <= ?
          ORDER BY pc.fechaPartida DESC
          LIMIT 1',
            [$fechaInicio]
        );
        $periodoIdParaSaldo = (int) ($r->v ?? 0);
        if ($periodoIdParaSaldo === 0) {
            $r = $cloud->row(
                'SELECT pc.partidaContaPeriodoId AS v
               FROM conta_partidas_contables pc
              WHERE pc.fechaPartida >= ?
              ORDER BY pc.fechaPartida ASC
              LIMIT 1',
                [$fechaInicio]
            );
            $periodoIdParaSaldo = (int) ($r->v ?? 0);
        }
    }
    $rSaldo = $cloud->row(
        'SELECT saldoInicialMayorizacion AS v
       FROM conta_mayorizacion_2025
      WHERE partidaContaPeriodoId = ?
        AND numeroCuentaMayorizacion = ?',
        [$periodoIdParaSaldo, $cuentaIdInicio]
    );
    $n = 0;
    $op = (float) ($rSaldo->v ?? 0.0);
    $output = ['data' => []];
    foreach ($dataDetalle as $data) {
        $periodo = $data->periodo;
        $desc = limitarTexto($data->descripcionPartidaDetalle, 50);
        $saldoI = '$' . number_format($op, 2);

        if (($data->cargos ?? 0) != 0 || ($data->abonos ?? 0) != 0) {
            switch ($data->categoriaCuenta) {
                case 'Activo':
                case 'Gasto':
                    $op += ($data->cargos - $data->abonos);
                    break;

                case 'Pasivo':
                case 'Capital':
                case 'Ingreso':
                case 'Resultado':
                    $op += ($data->abonos - $data->cargos);
                    break;

                default:
                    $op += ($data->cargos - $data->abonos);
                    break;
            }
        }

        $saldoR = '$' . number_format(abs($op), 2);
        $cargos = '$' . number_format($data->cargos ?? 0, 2);
        $abonos = '$' . number_format($data->abonos ?? 0, 2);
        $jsonDetalle = array(
            'tituloModal' => 'Detalle de partida contable',
            'partidaContableId' => $data->partidaContableId,
            'partidaContableDetalleId' => $data->partidaContableDetalleId,
            'tipoPartidaId' => $data->tipoPartidaId,
        );
        $jsonImprimir = array(
            'tituloModal' => 'Reportes: Partida contable - ' . $data->numPartida,
            'partidaContableId' => $data->partidaContableId,
            'tipoPartidaId' => $data->tipoPartidaId,
        );
        $n++;

        $partida = '<b>N° partida: </b>' . $data->numPartida . '<br><b>Tipo partida: </b>' . $data->tipoPartida;
        $documento = '';

        if ($data->tipoDTEId > 0 && $data->documentoId) {
            $documento = getDocumento($data->tipoDTEId, $data->documentoId, $data->numDocumento, $cloud, 2);
        } else {
            $documento = '<i>N/A</i>';
        }

        $acciones = '
        <button type="button" class="btn btn-info btn-sm ttip" 
         onclick=\'modalPartida(' . htmlspecialchars(json_encode($jsonDetalle)) . ')\'>
             <i class="fas fa-eye"></i>
            <span class="ttiptext">Ver Partida</span>
        </button>
        <button type="button" class="btn btn-primary btn-sm ttip" 
         onclick=\'modalImprimirPartida(' . htmlspecialchars(json_encode($jsonImprimir)) . ')\'>
             <i class="fas fa-print"></i>
            <span class="ttiptext">Imprimir Partida</span>
        </button>';
        $output['data'][] = array(
            $n,
            $partida,
            $data->numPartida,
            $data->tipoPartida,
            $periodo,
            $data->fechaPartida,
            $documento,
            $desc,
            $saldoI,
            $cargos,
            $abonos,
            $saldoR,
            $acciones
        );
    }
    echo json_encode($n > 0 ? $output : array('data' => ''));
} else {
    echo json_encode(array('data' => ''));
}
