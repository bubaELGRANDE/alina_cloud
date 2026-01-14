<?php
@session_start();
ini_set('memory_limit', '-1');
ini_set("pcre.backtrack_limit", "10000000");
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
require_once('../../../../../libraries/packages/php/vendor/autoload.php');

$encodedFecha = $_GET['fechaInicio'] ?? '';
$contaPeriodo = (int) base64_decode($encodedFecha);
$dataPeriodo = $cloud->row("SELECT concat(mesNombre,' ',anio) AS periodo FROM conta_partidas_contables_periodos WHERE partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);
$fechaActual = date("d/m/Y h:i A");
$usuario = $_SESSION['usuario'];

function fmt($n)
{
    return number_format((float) $n, 2, '.', ',');
}

// TODOS: Datos generales de formularios 
$css = '
    <style>
        .header-img { width: 50%; }
        .header-center { vertical-align:middle; text-align: center; width: 60%; font-size: 8px; }
        .header-info { color:white; vertical-align:middle; text-align: left; width: 20%; font-size: 8px; }
        .header-table th,.header-table td {border: none;}
        table { width: 100%; border-collapse: collapse;  font-family: Arial, Helvetica, sans-serif; font-size: 8px; }
        th, td { padding: 1px 1px; border-bottom: 1px solid #ddd; }
        thead th { text-align: left; background-color: #ebebebff; }
        td:nth-child(n+4) { text-align: right; }
        .separador { font-size: 3px; width: 100%; background-color: #174163ff; color: #174163ff }
    </style>
';

// === HTML HEADER ===
$html = '';

$dataEncabezados = $cloud->rows('
SELECT
    p.partidaContableId,
    p.tipoPartidaId,
    p.numPartida,
    p.descripcionPartida,
    p.fechaPartida,
    t.descripcionPartida AS tipoPartidaNombre
FROM
    conta_partidas_contables p
    LEFT JOIN cat_tipo_partida_contable t ON t.tipoPartidaId = p.tipoPartidaId
    WHERE p.partidaContaPeriodoId = ? AND p.flgDelete = ?
', [$contaPeriodo, 0]);

$totalGranCargos = 0;
$totalGranAbonos = 0;

foreach ($dataEncabezados as $encabezado) {
    $html .= '
    <div class="separador">INICIO DE PARTIDA</div>
    <table>
        <tbody>
            <tr>
                <td><strong>N° Partida:</strong>' . $encabezado->numPartida . '</td>
                <td><strong>Fecha:</strong> ' . $encabezado->fechaPartida . '</td>
            </tr>
            <tr>
                <td><strong>Tipo:</strong>' . $encabezado->tipoPartidaNombre . '</td>
                <td><strong>Concepto:</strong>' . $encabezado->descripcionPartida . '</td>
            </tr>
        </tbody>
    </table>
    ';

    $dataCuerpo = $cloud->rows('
        SELECT  
        cu.numeroCuenta,
        cu.descripcionCuenta,
        MIN(cd.descripcionPartidaDetalle) AS descripcionPartidaDetalle,
        SUM(cd.cargos) AS totalCargos, 
        SUM(cd.abonos) AS totalAbonos
        FROM conta_partidas_contables_detalle cd
        JOIN conta_cuentas_contables cu ON cu.cuentaContaId = cd.cuentaContaId
        WHERE cd.partidaContableId = ? AND cd.flgDelete = ?
        GROUP BY cd.cuentaContaId, cu.numeroCuenta, cu.descripcionCuenta, cu.nivelCuenta
        ', [$encabezado->partidaContableId, 0]);

    $html .= '<table>
    <thead>
        <tr>
            <th>N° Cuenta</th>
            <th>Cuenta</th>
            <th>Concepto Movimiento</th>
            <th>Cargo</th>
            <th>Abono</th>
        </tr>
    </thead>
    <tbody>';

    $subCargos = 0;
    $subAbonos = 0;

    foreach ($dataCuerpo as $cuerpo) {
        $subCargos += $cuerpo->totalCargos;
        $subAbonos += $cuerpo->totalAbonos;
        $html .= '
            <tr>
                <td>' . $cuerpo->numeroCuenta . '&nbsp;</td>
                <td>' . $cuerpo->descripcionCuenta . '</td>
                <td>' . $cuerpo->descripcionPartidaDetalle . '</td>
                <td>' . fmt($cuerpo->totalCargos) . '</td>
                <td>' . fmt($cuerpo->totalAbonos) . '</td>
            </tr>
        ';
    }

    $html .= '
    <tr style=" background-color: #c4c4c4;">
        <td></td>
        <td></td>
        <td style="padding: 4px; font-weight: bold; text-align: right;">Total:</td>
        <td style="padding: 4px; font-weight: bold;">' . fmt($subCargos) . '</td>
        <td style="padding: 4px; font-weight: bold;">' . fmt($subAbonos) . '</td>
    </tr></tbody></table>';

    $totalGranCargos += $subCargos;
    $totalGranAbonos += $subAbonos;
}

$html .= '<br><table style="border: none; width: 100%; margin: 0 auto;"><tbody> <tr style=" background-color: #c4c4c4;">
        <td></td>
        <td style="width: 600px"></td>
        <td style="padding: 4px; font-weight: bold; text-align: right;">Total:</td>
        <td style="padding: 4px; font-weight: bold;">' . fmt($totalGranCargos) . '</td>
        <td style="padding: 4px; font-weight: bold;">' . fmt($totalGranAbonos) . '</td>
    </tr></tbody></table>
<br><br><br>
<table style="border: none; width: 75%; margin: 0 auto;">
        <tbody> 
            <tr> 
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr style="border: none;"> 
                <td style="text-align: center;"><b>Representante Legal</b></td>
                <td style="text-align: center;"><b>Auditor Externo</b></td>
                <td style="text-align: center;"><b>Contador</b></td>
            </tr>
        </tbody>
    </table>
';

$mpdf = new \Mpdf\Mpdf([
    'format' => 'Letter',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 42,
    'margin_bottom' => 4,
    'setAutoTopMargin' => 'false',
    'autoMarginPadding' => 0,
    'shrink_tables_to_fit' => 1
]);

$mpdf->SetTitle("Libro diario general de {$dataPeriodo->periodo}");

$mpdf->SetHTMLHeader('<br><br>
    <table class="header-table">
    <tr>
        <td class="header-img">
            <img src="../../../../../libraries/resources/images/logos/indupal-logo.png" width="100">
            
        </td>
        <td class="header-center">
            <h2>Industrial La Palma, S.A. de C.V.</h2>
            <h4>Libro diario general de ' . $dataPeriodo->periodo . '</h4>
            <p>(EN DÓLARES DE LOS ESTADOS UNIDOS DE NORTE AMÉRICA)</p>
            <p><b>' . $fechaActual . '</b></p>
            <P>Página {PAGENO} de {nb}</P>
        </td>
        <td class="header-info">
            <p><b>' . $fechaActual . '</b></p>
            <p><b>' . $usuario . '</b></p>
            <P>Página {PAGENO} de {nb}</P>
        </td>
    </tr>
</table>
');
$mpdf->debug = false;
$mpdf->showImageErrors = false;
$mpdf->useSubstitutions = false;
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
if (empty($html)) {
    $html = '<p>No hay datos para mostrar</p>';
}

$mpdf->WriteHTML(mb_convert_encoding($html, 'UTF-8', 'UTF-8'), \Mpdf\HTMLParserMode::HTML_BODY);
$mpdf->shrink_tables_to_fit = 1;
$mpdf->SetDisplayMode('fullpage', 'single');


$mpdf->Output("LibroDiario{$dataPeriodo->periodo}.pdf", "I");


?>