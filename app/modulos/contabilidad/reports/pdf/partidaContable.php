<?php
@session_start();
ini_set('memory_limit', '-1');
ini_set("pcre.backtrack_limit", "10000000");
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
require_once('../../../../../libraries/packages/php/vendor/autoload.php');
require_once("../../../../../libraries/includes/logic/functions/funciones-conta.php");

$encodedId = $_GET['partidaContableId'] ?? '';
$partidaContableId = (int) base64_decode($encodedId);
$fechaActual = date("d/m/Y h:i A");
$usuario = $_SESSION['usuario'];

function fmt($n) 
{
    return number_format((float) $n, 2, '.', ',');
}

// TODOS: Datos generales de formularios 
$css = '
    <style>
        .header-table { margin-bottom: 20px; }
        .header-img { width: 20%; }
        .header-center {text-align: center;  width: 60%; font-size: 12px; }
        .header-info { text-align: right; width: 20%; font-size: 10px; }

        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { padding: 4px; border-bottom: 1px solid #ddd; }
        thead th { text-align: left; background-color: #013243; color: #fff; }
    </style>
';

$data = $cloud->row("
    SELECT 
        p.partidaContableId,
        p.numPartida,
        p.fechaPartida,
        p.descripcionPartida,
        p.estadoPartidaContable,
        t.descripcionPartida AS nombreTipoPartida,
        concat(pr.mesNombre,' ',pr.anio) AS nombrePeriodoContable
    FROM conta_partidas_contables p
    LEFT JOIN cat_tipo_partida_contable t ON t.tipoPartidaId = p.tipoPartidaId
    LEFT JOIN conta_partidas_contables_periodos pr ON pr.partidaContaPeriodoId = p.partidaContaPeriodoId
    WHERE p.partidaContableId = ? AND p.flgDelete = ?
", [$partidaContableId, 0]);

// === CONSULTA DE DETALLE ===
$detalle = $cloud->rows('SELECT 
    cd.tipoDTEId,
    cd.numDocumento,
    cc.nombreCentroCosto AS centroCosto,
    su.nombreSubCentroCosto AS subCentroCosto,
    cd.descripcionPartidaDetalle,
    cu.numeroCuenta,
    cd.documentoId,
    cu.descripcionCuenta,
    cd.cargos,
    cd.abonos
FROM conta_partidas_contables_detalle cd
LEFT JOIN conta_centros_costo cc ON cd.centroCostoId = cc.centroCostoId
LEFT JOIN conta_cuentas_contables cu ON cd.cuentaContaId = cu.cuentaContaId
LEFT JOIN conta_subcentros_costo su ON cd.subCentroCostoId = su.subCentroCostoId
WHERE cd.partidaContableId = ? AND cd.flgDelete = ?', [$partidaContableId, 0]);


$html = '
<table class="header-table">
    <tr>
        <td class="header-img">
            <img src="../../../../../libraries/resources/images/logos/indupal-logo.png" width="100">
        </td>
        <td class="header-center">
            <h2>Industrial La Palma, S.A. de C.V.</h2>
            <p>(EN DÓLARES DE LOS ESTADOS UNIDOS DE NORTE AMÉRICA)</p>
        </td>
        <td class="header-info">
            <p><b>' . $fechaActual . '</b></p>
            <p><b>' . $usuario . '</b></p>
        </td>
    </tr>
</table>
<br>';

// === CUERPO ===

$html .= '
    <table>
        <tbody>
            <tr>
                <td><strong>N° Partida: </strong>' . str_pad($data->numPartida, 8, '0', STR_PAD_LEFT) . '</td>
                <td><strong>Periodo: </strong>' . $data->nombrePeriodoContable . '</td>
                <td><strong>Fecha: </strong> ' . date("d/m/Y", strtotime($data->fechaPartida)) . '</td>
            </tr>
            <tr>
                <td><strong>Tipo:</strong>' . $data->nombreTipoPartida . '</td>
                <td><strong>Concepto:</strong>' . $data->descripcionPartida . '</td>
            </tr>
        </tbody>
    </table>
    <br>';

$html .= '
<table>
    <thead>
        <tr>
            <th>Cuenta</th>
            <th>Documento</th>
            <th>Concepto</th>
            <th class="right">Cargos</th>
            <th class="right">Abonos</th>
      </tr>
    </thead>
    <tbody>';

$totalCargos = 0;
$totalAbonos = 0;

foreach ($detalle as $item) {

    $documento = '';

    if ($item->numDocumento) {
        $documento = getDocumento($item->tipoDTEId, $item->documentoId, $item->numDocumento, $cloud,2);
    } else {
        $documento = '<i>N/A</i>';
    }

    $html .= '
        <tr>
        <td><b>' . $item->numeroCuenta . '</b> ' . $item->descripcionCuenta . '</td>
        <td>' . $documento . '</td>
        <td>' . ($item->descripcionPartidaDetalle ?? '-') . '</td>
        <td style=" background-color: #f5f5f5; text-align: right;">$' . fmt($item->cargos) . '</td>
        <td style=" background-color: #f5f5f5; text-align: right;">$' . fmt($item->abonos) . '</td>
        </tr>';
    $totalCargos += floatval($item->cargos);
    $totalAbonos += floatval($item->abonos);
}

$html .= '
    <tr style=" background-color: #c4c4c4;">
        <td></td>
    
        <td></td>
        <td style="padding: 4px; font-weight: bold; text-align: right;">Total:</td>
        <td style="padding: 4px; font-weight: bold;">$' . fmt($totalCargos) . '</td>
        <td style="padding: 4px; font-weight: bold;">$' . fmt($totalAbonos) . '</td>
    </tr></tbody></table>';

// Mpdf

$mpdf = new \Mpdf\Mpdf([
    'format' => 'Letter',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 5,
    'margin_bottom' => 20,
    'setAutoTopMargin' => 'false',
    'autoMarginPadding' => 0,
    'shrink_tables_to_fit' => 1
]);


$mpdf->SetTitle("Reporte de Partida Contable #{$data->numPartida}");

$mpdf->SetHTMLFooter('
    <hr>
    <div style="text-align: center; font-size: 10px;">
        Página {PAGENO} de {nb}
    </div>
');

$mpdf->debug = false;
$mpdf->showImageErrors = false;
$mpdf->useSubstitutions = false;
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
$mpdf->SetDisplayMode('fullpage', 'single');
$mpdf->Output("PartidaContable_{$data->numPartida}.pdf", "I");
