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

$dataPeriodo = $cloud->row("SELECT concat(mesNombre,' del ',anio) AS periodo FROM conta_partidas_contables_periodos WHERE partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);

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
        saldoFinalMayorizacion AS saldoFinal,
        tipoCuenta
    FROM conta_mayorizacion_2025
    WHERE nivelCuenta = ?
    AND partidaContaPeriodoId = ?
    AND flgDelete = ?
", [3, $contaPeriodo, 0]);

$fechaActual = date("d/m/Y h:i A");
$usuario = $_SESSION['usuario'];

// === ESTILOS CSS ===
$css = '
    <style>
        .header-img { width: 50%; }
        .header-center { vertical-align:middle; text-align: center; width: 60%; font-size: 9px; }
        .header-info { color:white; vertical-align:middle; text-align: left; width: 20%; font-size: 9px; }
        .header-table th,.header-table td {border: none;}
        table { width: 100%; border-collapse: collapse;  font-family: Arial, Helvetica, sans-serif; font-size: 9px; }
        th, td { padding: 1px 1px; border-bottom: 1px solid #ddd; }
        thead th { text-align: left; background-color: #013243; color: #fff; }
        td:nth-child(n+4) { text-align: right; }
        .separador { font-size: 3px; width: 100%; background-color: #174163ff; color: #174163ff }
    </style>
';

$html = '';

// === CUERPO ===
$html .= '
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

// Ordenar por número de cuenta
usort($detalle, function($a, $b) {
    return strcmp($a->numeroCuenta, $b->numeroCuenta);
});

$grupoActual = null;
$acum = ['saldoInicial'=>0,'cargo'=>0,'abono'=>0,'saldoFinal'=>0];

// Acumuladores generales
$totalGeneral = ['saldoInicial'=>0,'cargo'=>0,'abono'=>0,'saldoFinal'=>0];

foreach ($detalle as $cuenta) {
    $grupo = substr($cuenta->numeroCuenta, 0, 1);

    // Si cambio de grupo, imprimir total del anterior
    if ($grupoActual !== null && $grupo !== $grupoActual) {
        $html .= "
        <tr style='background:#c4c4c4; font-weight:bold;'>
            <td></td>
            <td style='text-align:right;'><b>Subtotal</b></td>
            <td><b>" . fmt($acum['saldoInicial']) . "</b></td>
            <td><b>" . fmt($acum['cargo']) . "</b></td>
            <td><b>" . fmt($acum['abono']) . "</b></td>
            <td><b>" . fmt($acum['saldoFinal']) . "</b></td>
        </tr>";

        // Acumular al total general
        $totalGeneral['saldoInicial'] += $acum['saldoInicial'];
        $totalGeneral['cargo'] += $acum['cargo'];
        $totalGeneral['abono'] += $acum['abono'];
        $totalGeneral['saldoFinal'] += $acum['saldoFinal'];

        // Reiniciar acumuladores
        $acum = ['saldoInicial'=>0,'cargo'=>0,'abono'=>0,'saldoFinal'=>0];
    }

    // Imprimir cuenta
    $html .= "
    <tr>
        <td>{$cuenta->numeroCuenta}</td>
        <td>{$cuenta->descripcionCuenta}</td>
        <td>" . fmt($cuenta->saldoInicial) . "</td>
        <td>" . fmt($cuenta->cargo) . "</td>
        <td>" . fmt($cuenta->abono) . "</td>
        <td>" . fmt($cuenta->saldoFinal) . "</td>
    </tr>";

    // Acumular al subtotal de grupo
    $acum['saldoInicial'] += floatval($cuenta->saldoInicial);
    $acum['cargo']        += floatval($cuenta->cargo);
    $acum['abono']        += floatval($cuenta->abono);
    $acum['saldoFinal']   += floatval($cuenta->saldoFinal);

    $grupoActual = $grupo;
}

// Al terminar, cerrar el último grupo
if ($grupoActual !== null) {
    $html .= "
    <tr style='background:#c4c4c4; font-weight:bold;'>
        <td></td>
        <td style='text-align:right;'><b>Subtotal</b></td>
        <td><b>" . fmt($acum['saldoInicial']) . "</b></td>
        <td><b>" . fmt($acum['cargo']) . "</b></td>
        <td><b>" . fmt($acum['abono']) . "</b></td>
        <td><b>" . fmt($acum['saldoFinal']) . "</b></td>
    </tr>";

    // Sumar al total general
    $totalGeneral['saldoInicial'] += $acum['saldoInicial'];
    $totalGeneral['cargo'] += $acum['cargo'];
    $totalGeneral['abono'] += $acum['abono'];
    $totalGeneral['saldoFinal'] += $acum['saldoFinal'];
}


$html .= "
<tr style='font-weight:bold;'>
    <td></td>
    <td style='text-align:right;'><b>Total General</b></td>
    <td><b>" . fmt($totalGeneral['saldoInicial']) . "</b></td>
    <td><b>" . fmt($totalGeneral['cargo']) . "</b></td>
    <td><b>" . fmt($totalGeneral['abono']) . "</b></td>
    <td><b>" . fmt($totalGeneral['saldoFinal']) . "</b></td>
</tr>";


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
    'margin_top' => 42,
    'margin_bottom' => 4,
    'setAutoTopMargin' => 'false',
    'autoMarginPadding' => 0,
    'shrink_tables_to_fit' => 1
]);
$mpdf->SetTitle("Mayor contable del periodo contable: {$dataPeriodo->periodo}");

$mpdf->SetHTMLHeader('<br><br>
    <table class="header-table">
    <tr>
        <td class="header-img">
            <img src="../../../../../libraries/resources/images/logos/indupal-logo.png" width="100">
            
        </td>
        <td class="header-center">
            <h2>Industrial La Palma, S.A. de C.V.</h2>
            <h4>Libro mayor contable de ' . $dataPeriodo->periodo . '</h4>
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
if (empty($html)) {
    $html = '<p>No hay datos para mostrar</p>';
}
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML(mb_convert_encoding($html, 'UTF-8', 'UTF-8'), \Mpdf\HTMLParserMode::HTML_BODY);
$mpdf->shrink_tables_to_fit = 1;
$mpdf->SetDisplayMode('fullpage', 'single');
$mpdf->Output("MayorContable{$dataPeriodo->periodo}.pdf", "I");
?>
