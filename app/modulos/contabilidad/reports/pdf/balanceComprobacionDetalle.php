<?php
@session_start();

ini_set('memory_limit', '-1');
ini_set("pcre.backtrack_limit", "10000000");
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
require_once('../../../../../libraries/packages/php/vendor/autoload.php');

$encodedFecha = $_GET['fechaInicio'] ?? '';
$contaPeriodo = (int) base64_decode($encodedFecha);

function fmt($n)
{
    return number_format((float) $n, 2, '.', ',');
}


$dataPeriodo = $cloud->row("SELECT concat(mesNombre,' DEL ',anio) AS periodo FROM conta_partidas_contables_periodos WHERE partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);

// === CONSULTA DE DETALLE ===
$detalle = $cloud->rows("
    SELECT
        cuentaContaId,
        cuentaPadreId,
        nivelCuenta,
        numeroCuentaMayorizacion AS numeroCuenta,
        descripcionCuentaMayorizacion AS descripcionCuenta,
        saldoInicialMayorizacion AS saldoInicial,
        cargoMayorizacion AS cargo,
        abonoMayorizacion AS abono,
        saldoFinalMayorizacion AS saldoFinal
    FROM conta_mayorizacion_2025
    WHERE (
        saldoInicialMayorizacion <> ?
        OR cargoMayorizacion <> ?
        OR abonoMayorizacion <> ?
        OR saldoFinalMayorizacion <> ?
    ) 
    AND partidaContaPeriodoId = ?
    AND flgDelete = ?
    ORDER BY LENGTH(numeroCuentaMayorizacion), numeroCuentaMayorizacion
", [0, 0, 0, 0, $contaPeriodo, 0]);

$fechaActual = date("d/m/Y h:i A");
$usuario = $_SESSION['usuario'];

// === ESTILOS CSS ===
// === CSS ===
$css = '
    <style>
        .header-table { margin-bottom: 20px; }
        .header-img { width: 20%; }
        .header-center { text-align: center; width: 60%; font-size: 12px; }
        .header-info { text-align: right; width: 20%; font-size: 10px; }

        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { padding: 4px 2px; border-bottom: 1px solid #ddd; }
        thead th { background-color: #013243; color: #fff; }
        td:nth-child(n+3) { text-align: right; }
    </style>
';
$html= '';
// === HEADER ===
$html .= '
<table class="header-table">
    <tr>
      <td class="header-img">
        <img src="../../../../../libraries/resources/images/logos/indupal-logo.png" width="100">
      </td>
      <td class="header-center">
        <h2>Industrial La Palma, S.A. de C.V.</h2>
        <h4>BALANCE DE COMPROBACIÓN (ANEXO) AL 31 DE ' . $dataPeriodo->periodo . '</h4>
        <p>(EN DÓLARES DE LOS ESTADOS UNIDOS DE NORTE AMÉRICA)</p>
      </td>
      <td class="header-info">
         <p><b>' . $fechaActual . '</b></p>
      <p><b>' . $usuario . '</b></p>
      </td>
    </tr>
</table>
';

// === CUERPO ===

$html .= '
<br>
<table>
    <thead>
        <tr>
            <th>Cuenta</th>
            <th>Descripción</th>
            <th>Saldo Inicial</th>
            <th>Cargo</th>
            <th>Abono</th>
            <th>Saldo Final</th>
        </tr>
    </thead>
    <tbody>';

$totalCargos = 0;
$totalAbonos = 0;

$cuentasById = [];
$hijosPorPadre = [];

foreach ($detalle as $cuenta) {
    $cuentasById[$cuenta->cuentaContaId] = $cuenta;
    $hijosPorPadre[$cuenta->cuentaPadreId ?? 0][] = $cuenta;
}

function construirArbolCuentas($padreId, $nivel, $cuentasById, $hijosPorPadre, &$html, &$totalInicial, &$totalCargos, &$totalAbonos, &$totalFinal, &$procesados)
{
    if (!isset($hijosPorPadre[$padreId]))
        return;

    foreach ($hijosPorPadre[$padreId] as $cuenta) {
        if (in_array($cuenta->cuentaContaId, $procesados))
            continue;
        if ($cuenta->cuentaContaId == $cuenta->cuentaPadreId)
            continue;

        $procesados[] = $cuenta->cuentaContaId;

        $numeroCuenta = $cuenta->numeroCuenta;
        $descripcionCuenta = $cuenta->descripcionCuenta;
        $saldoAnterior = floatval($cuenta->saldoInicial);
        $cargo = floatval($cuenta->cargo);
        $abono = floatval($cuenta->abono);
        $saldoFinal = floatval($cuenta->saldoFinal);

        $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $nivel);

        $html .= '
            <tr>
                <td>' . $indent . $numeroCuenta . '</td>
                <td>' . $descripcionCuenta . '</td>
                <td>' . fmt($saldoAnterior) . '</td>
                <td style="background-color: #f5f5f5;">' . fmt($cargo) . '</td>
                <td style="background-color: #f5f5f5;">' . fmt($abono) . '</td>
                <td>' . fmt($saldoFinal) . '</td>
            </tr>';

        // Totales generales


        if ($cuenta->nivelCuenta == 1) {
            $totalInicial += $saldoAnterior;
            $totalCargos += $cargo;
            $totalAbonos += $abono;
            $totalFinal += $saldoFinal;
            construirSubArbol($cuenta->cuentaContaId, $nivel + 1, $cuentasById, $hijosPorPadre, $html, $procesados);
        } else {

            construirArbolCuentas($cuenta->cuentaContaId, $nivel + 1, $cuentasById, $hijosPorPadre, $html, $totalInicial, $totalCargos, $totalAbonos, $totalFinal, $procesados);
        }
    }
}

function construirSubArbol($padreId, $nivel, $cuentasById, $hijosPorPadre, &$html, &$procesados)
{
    if (!isset($hijosPorPadre[$padreId]))
        return;

    foreach ($hijosPorPadre[$padreId] as $cuenta) {
        if (in_array($cuenta->cuentaContaId, $procesados))
            continue;
        if ($cuenta->cuentaContaId == $cuenta->cuentaPadreId)
            continue;

        $procesados[] = $cuenta->cuentaContaId;

        $numeroCuenta = $cuenta->numeroCuenta;
        $descripcionCuenta = $cuenta->descripcionCuenta;
        $saldoAnterior = floatval($cuenta->saldoInicial);
        $cargo = floatval($cuenta->cargo);
        $abono = floatval($cuenta->abono);
        $saldoFinal = floatval($cuenta->saldoFinal);

        $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $nivel);

        $html .= '
            <tr>
                <td>' . $indent . $numeroCuenta . '</td>
                <td>' . $descripcionCuenta . '</td>
                <td>' . fmt($saldoAnterior) . '</td>
                <td style="padding: 4px 2px; background-color: #f5f5f5;">' . fmt($cargo) . '</td>
                <td style="padding: 4px 2px; background-color: #f5f5f5;">' . fmt($abono) . '</td>
                <td>' . fmt($saldoFinal) . '</td>
            </tr>';
        construirSubArbol(
            $cuenta->cuentaContaId,
            $nivel + 1,
            $cuentasById,
            $hijosPorPadre,
            $html,
            $procesados
        );
    }
}


$procesados = [];
$totalInicial = 0;
$totalCargos = 0;
$totalAbonos = 0;
$totalFinal = 0;

construirArbolCuentas(0, 0, $cuentasById, $hijosPorPadre, $html, $totalInicial, $totalCargos, $totalAbonos, $totalFinal, $procesados);

$html .= '
<tr style=" background-color: #c4c4c4;">
<td></td>
<td style="padding: 4px; font-weight: bold; text-align: right;">Total:</td>
<td style="padding: 4px; font-weight: bold;">' . fmt($totalInicial) . '</td>
<td style="padding: 4px; font-weight: bold;">' . fmt($totalCargos) . '</td>
<td style="padding: 4px; font-weight: bold;">' . fmt($totalAbonos) . '</td>
<td style="padding: 4px; font-weight: bold;">' . fmt($totalFinal) . '</td>
</tr>';

$html .= '</tbody></table>
<br><br><br>
    <table style="border: none; width: 60%; margin: 0 auto;">
        <tbody> 
            <tr> 
                <td></td>
                <td></td>
            </tr>
            <tr style="border: none;">
                <td style="text-align: center;"><b>Representante Legal</b></td>
                <td style="text-align: center;"><b>Contador</b></td>
            </tr>
        </tbody>
    </table>';

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


$mpdf->SetTitle("Balance de comprobación al detalle del periodo contable: {$dataPeriodo->periodo}");

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
$mpdf->shrink_tables_to_fit = 1;
$mpdf->SetDisplayMode('fullpage', 'single');
$mpdf->Output("BalanceComprobacionAnexo{$dataPeriodo->periodo}.pdf", "I");

?>