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

$dataPeriodo = $cloud->row("SELECT concat(mesNombre,' ',anio) AS periodo FROM conta_partidas_contables_periodos WHERE partidaContaPeriodoId = ? AND flgDelete = ?", [$contaPeriodo, 0]);

// === CSS ===
$css = '
    <style>
        .header-table { margin-bottom: 20px; }
        .header-img { width: 20%; }
        .header-center { text-align: center; width: 60%; font-size: 12px; }
        .header-info { text-align: right; width: 20%; font-size: 10px; }
        .total {border-bottom: 1px solid black}
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { padding: 2px 2px;}
        thead th { background-color: #013243; color: #fff; }
        .border-bottom  { border-bottom: 1px solid #bbb;}
    </style>
';

$fechaActual = date("d/m/Y h:i A");
$usuario = $_SESSION['usuario'];

// === HEADER ===
$html = '
<table class="header-table">
    <tr>
      <td class="header-img">
        <img src="../../../../../libraries/resources/images/logos/indupal-logo.png" width="100">
      </td>
      <td class="header-center">
        <h2>Industrial La Palma, S.A. de C.V.</h2>
        <h4>Balance general al 31 de ' . $dataPeriodo->periodo . '</h4>
        <p>(EN DÓLARES DE LOS ESTADOS UNIDOS DE NORTE AMÉRICA)</p>
      </td>
      <td class="header-info">
         <p><b>' . $fechaActual . '</b></p>
      <p><b>' . $usuario . '</b></p>
      </td>
    </tr>
</table><br>
';

// === Datos de cuentas

$cuentas = $cloud->rows("
SELECT numeroCuentaMayorizacion, descripcionCuentaMayorizacion, saldoFinalMayorizacion AS saldo
FROM desarrollo_cloud.conta_mayorizacion_2025
WHERE partidaContaPeriodoId = ?
  AND numeroCuentaMayorizacion IN (
    '1','11','111','11101','11102','11103','112','11201','11203','11205','11202',
    '11302','114','116','11601','11603','119','118','12','121','122','127','12101','12102','12103','12104','12105','12106',
    '2','21','211','21101','21102','21106','21109','21110','21113','21114','21111','21108','21107',
    '22','221','22105',
    '3','31','33','33101','33102'
  )
ORDER BY numeroCuentaMayorizacion;
", [$contaPeriodo]);

$cuenta_1 = 0; // ACTIVOS
$cuenta_11 = 0; // ACTIVOS CORRIENTE

$cuenta_111 = 0; // EFECTIVO Y EQUIVALENTES
$cuenta_11101 = 0; // CAJA
$cuenta_11102 = 0; // BANCOS (EFECTIVO)
$cuenta_11103 = 0; // BANCOS (INVERSIONES)

$cuenta_112 = 0; //CUENTAS POR COBRAR
$cuenta_11201 = 0;
$cuenta_11202 = 0;
$cuenta_11203 = 0;
$cuenta_11205 = 0;
$cuenta_114 = 0;
$cuenta_116 = 0;
$cuenta_11601 = 0;
$cuenta_11603 = 0;
$cuenta_118 = 0;
$cuenta_119 = 0;
$cuenta_12 = 0;
$cuenta_121 = 0;
$cuenta_12101 = 0;
$cuenta_12102 = 0;
$cuenta_12103 = 0;
$cuenta_12104 = 0;
$cuenta_12105 = 0;
$cuenta_12106 = 0;
$cuenta_122 = 0;
$cuenta_127 = 0;
$cuenta_11302 = 0;

$cuenta_2 = 0;
$cuenta_21 = 0; //PASIVO CORRIENTE
$cuenta_211 = 0; // DEUDAS A CORTO PLAZO
$cuenta_21101 = 0; // PROVE. LOCAL (SUMA CUENTAS POR PAGAR)
$cuenta_21102 = 0; // PROVE. EXTRAN (SUMA CUENTAS POR PAGAR)
$cuenta_21106 = 0; // PROVISIONES (SUMA CUENTAS POR PAGAR)
$cuenta_21107 = 0;
$cuenta_21108 = 0;
$cuenta_21109 = 0;
$cuenta_21110 = 0;
$cuenta_21111 = 0;
$cuenta_21113 = 0;
$cuenta_21114 = 0; //CUENTAS EN SUSPENSO
$cuenta_22 = 0;
$cuenta_22105 = 0;

$cuenta_3 = 0;
$cuenta_31 = 0;
$cuenta_33 = 0;
$cuenta_33101 = 0;
$cuenta_33102 = 0;

// Lista blanca de variables válidas
$variablesPermitidas = [
    'cuenta_1',
    'cuenta_11',
    'cuenta_111',
    'cuenta_11101',
    'cuenta_11102',
    'cuenta_11103',
    'cuenta_112',
    'cuenta_11201',
    'cuenta_11203',
    'cuenta_11205',
    'cuenta_11202',
    'cuenta_114',
    'cuenta_116',
    'cuenta_11601',
    'cuenta_11603',
    'cuenta_119',
    'cuenta_118',
    'cuenta_12',
    'cuenta_121',
    'cuenta_122',
    'cuenta_127',
    'cuenta_12101',
    'cuenta_12102',
    'cuenta_12103',
    'cuenta_12104',
    'cuenta_12105',
    'cuenta_12106',
    'cuenta_11302',
    'cuenta_2',
    'cuenta_21',
    'cuenta_211',
    'cuenta_21101',
    'cuenta_21102',
    'cuenta_21109',
    'cuenta_21106',
    'cuenta_21111',
    'cuenta_21112',
    'cuenta_21116',
    'cuenta_21113',
    'cuenta_21114',
    'cuenta_21110',
    'cuenta_21108',
    'cuenta_21107',
    'cuenta_22',
    'cuenta_221',
    'cuenta_22105',
    'cuenta_3',
    'cuenta_31',
    'cuenta_33',
    'cuenta_33101',
    'cuenta_33102'
];
// Llenar variables si existen en los resultados
foreach ($cuentas as $cuenta) {
    $varName = 'cuenta_' . $cuenta->numeroCuentaMayorizacion;
    if (in_array($varName, $variablesPermitidas)) {
        $$varName = $cuenta->saldo;
    }
}


if ($cuenta_1) {
    $html .= '
    <table>
        <thead>
            <tr class="border-bottom">
                <th colspan="3">ACTIVO</th>
                <th colspan="3">PASIVO</th>
            </tr>
        </thead>
        <tbody>
            <tr class="border-bottom">
                <td colspan="3"><strong>ACTIVOS CORRIENTES</strong></td>
                <td colspan="3"><strong>PASIVOS CORRIENTES</strong></td>
            </tr>
            <tr>
                <td><b>EFECTIVO EN CAJA Y BANCOS</b></td>
                <td></td>
                <td><b>$ ' . fmt($cuenta_111) . '</b></td>
                <td><b>OBLIGACIONES A CORTO PLAZO (Sin Garantía Real)</b></td>
                <td></td>
                <td><b>$ ' . fmt($cuenta_211) . '</b></td>
            </tr>
            <tr>
                <td>Caja</td>
                <td>$ ' . fmt($cuenta_11101) . '</td>
                <td></td>
                <td>Cuentas Por Pagar</td>
                <td>$ ' . fmt(($cuenta_21101 + $cuenta_21102 + $cuenta_21106)) . '</td>
                <td></td>
            </tr>
            <tr>
                <td>Bancos</td>
                <td class="border-bottom">$ ' . fmt(($cuenta_11102 + $cuenta_11103)) . '</td>
                <td class="border-bottom"></td>
                <td>Retenciones al Personal</td>
                <td>$ ' . fmt($cuenta_21107) . '</td>
                <td></td>
            </tr>
            <tr>
                <td><b>DEUDORES A CORTO PLAZO<br>(Sin Garantía Real)</b></td>
                <td></td>
                <td><b>$' . fmt($cuenta_112 + $cuenta_11302) . '</b></td>
                <td>Impuestos Por Pagar</td>
                <td>$ ' . fmt(($cuenta_21110 + $cuenta_21109)) . '</td>
                <td></td>
            </tr>

            <tr>
                <td>Clientes</td>
                <td>$ ' . fmt($cuenta_11201) . '</td>
                <td></td>
                <td>Beneficios a Empleados por Pagar</td>
                <td>$ ' . fmt($cuenta_21108) . '</td>
                <td></td>
            </tr>
            <tr>
                <td>Otros Deudores</td>
                <td>$ ' . fmt($cuenta_11203) . '</td>
                <td></td>
                <td>Dividendos Por Pagar</td>
                <td>$ ' . fmt($cuenta_21111) . '</td>
                <td></td>
            </tr>
            <tr>
                <td>Impuestos por Recuperar</td>
                <td>$ ' . fmt($cuenta_11205) . '</td>
                <td></td>
                <td>Otras Cuentas por Pagar</td>
                <td >$ ' . fmt($cuenta_21113) . '</td>
                <td ></td>
            </tr>
            <tr>
                <td>(Menos) Rva. P/Cuentas Incobrables</td>
                <td >$ ' . fmt($cuenta_11302) . '</td>
                <td ></td>
                <td >Cuentas en suspenso</td>
                <td class="border-bottom">$ ' . fmt($cuenta_21114) . '</td>
                <td class="border-bottom"></td>
            </tr>
            <tr>
                <td><b>PARTES RELACIONADAS</b></td>
                <td class="border-bottom"></td>
                <td class="border-bottom"><b>$ ' . fmt($cuenta_114) . '</b></td>
                <td ><b>TOTAL PASIVO CORRIENTES</b></td>
                <td></td>
                <td><b>$ ' . fmt($cuenta_21) . '</b></td>
            </tr>
            <tr>
                <td><b>INVENTARIOS</b></td>
                <td></td>
                <td><b>$ ' . fmt($cuenta_116) . '</b></td>
                <td><b>PASIVO NO CORRIENTES</b></td>
                <td></td>
                <td></td>
            </tr>

            <tr>
                <td>Inventario de Mercaderías</td>
                <td>$ ' . fmt($cuenta_11601) . '</td>
                <td></td>
                <td><b>OBLIGACIONES A LARGO PLAZO</b></td>
                <td></td>
                <td><b>$ ' . fmt($cuenta_221) . '</b></td>
            </tr>

            <tr>
                <td>Mercadería en Tránsito</td>
                <td class="border-bottom">$ ' . fmt($cuenta_11603) . '</td>
                <td class="border-bottom"></td>
                <td>Dividendos Por Pagar</td>
                <td class="border-bottom">$ ' . fmt($cuenta_22105) . '</td>
                <td class="border-bottom"></td>
            </tr>
            <tr>
                <td><b>PAGOS ANTICIPADOS</b></td>
                <td></td>
                <td><b>$' . fmt($cuenta_119) . '</b></td>
                <td><b>TOTAL PASIVO NO CORRIENTES</b></td>
                <td></td>
                <td><b>$ ' . fmt($cuenta_22) . '</b></td>
            </tr>
            <tr>
                <td>Pagos Anticipados</td>
                <td class="border-bottom"> $' . fmt($cuenta_119) . '</td>
                <td class="border-bottom"></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><b>DEPÓSITOS EN GARANTÍA</b></td>
                <td></td>
                <td><b>$ ' . fmt($cuenta_118) . '</b></td>
                <td><b>PATRIMONIO DE LOS ACCIONISTAS</b></td>
                <td></td>
                <td><b>$ ' . fmt($cuenta_3) . '</b></td>
            </tr>
            <tr>
                <td>Depósitos en Garantía</td>
                <td class="border-bottom">$ ' . fmt($cuenta_118) . '</td>
                <td class="border-bottom"></td>
                <td>Capital Social</td>
                <td>$ ' . fmt($cuenta_31) . '</td>
                <td></td>
            </tr>

            <tr>
                <td><b>TOTAL ACTIVOS CORRIENTES</b></td>
                <td></td>
                <td><b>$ ' . fmt($cuenta_11) . '</b></td>
                <td colspan="2">Capital Social (Mínimo $ 24,000.00)</td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td colspan="2">Capital Social (Variable $ 2,576,004.00)</td>
                <td></td>
            </tr>
            <tr>
                <td><b>ACTIVOS NO CORRIENTES</b></td>
                <td></td>
                <td></td>
                <td colspan="2">216667 Acc. Comunes de $12.00 C/U</td>
                <td></td>
            </tr>
            <tr>
                <td>Inversiones Permanentes</td>
                <td></td>
                <td>$' . fmt($cuenta_122) . '</td>
                <td>Autorizadas, emitidas y pagadas.</td>
                <td class="border-bottom"></td>
                <td class="border-bottom"></td>
            </tr>
            <tr>
                <td>Dep. En Garantía Largo Plazo</td>
                <td></td>
                <td>$ ' . fmt($cuenta_127) . '</td>
                <td><b>UTILIDADES ACUMULADAS</b></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Propiedad, Planta y Equipo</td>
                <td></td>
                <td>$ ' . fmt(($cuenta_12101 + $cuenta_12102 + $cuenta_12103 + $cuenta_12104 + $cuenta_12105)) . '</td>
                <td>Reserva Legal</td>
                <td>$ ' . fmt($cuenta_33101) . '</td>
                <td></td>
            </tr>
            <tr>
                <td>(Menos) Depreciación Acumulada</td>
                <td class="border-bottom"></td>
                <td class="border-bottom">$ ' . fmt($cuenta_12106) . '</td>
                <td>Superávit del Ejercicio Anteriores</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><b>TOTAL ACTIVOS NO CORRIENTES</b></td>
                <td></td>
                <td><b>$' . fmt($cuenta_12) . '</b></td>
                <td>Superávit del Presente Ejercicio</td>
                <td class="border-bottom">$ ' . fmt($cuenta_33102) . '</td>
                <td class="border-bottom"></td>
            </tr>
            <tr>
                <td><b>TOTAL ACTIVO</b></td>
                <td></td>
                <td class="border-bottom"><b>' . fmt($cuenta_1) . '</b></td>
                <td><b>TOTAL PASIVO Y PATRIMONIO</b></td>
                <td></td>
                <td class="border-bottom"><b>$ ' . fmt(($cuenta_2 + $cuenta_3)) . '</b></td>
            </tr>
        </tbody>
    </table>
    <br><br><br>
    <table style="width: 80%; margin: 0 auto;">
        <tbody> 
            <tr style="border-bottom: 1px solid #ddd;"> 
                <td><hr></td>
                <td><hr></td>
                <td><hr></td>
            </tr>
            <tr style="border: none;"> 
                <td style="text-align: center;"><b>Eckart Kurt Hoffmann</b></td>
                <td style="text-align: center;"><b>Auditor Externo</b></td>
                <td style="text-align: center;"><b>Lic. Willian Leandro Aquino Sanchez</b></td>
            </tr>
            <tr style="border: none;">
                <td style="text-align: center;">Representante Legal</td>
                <td style="text-align: center;"></td>
                <td style="text-align: center;">Contador General</td>
            </tr>
        </tbody>
    </table>
';
}

// Mpdf

$mpdf = new \Mpdf\Mpdf([
    'setAutoTopMargin' => 'false',
    'autoMarginPadding' => 2,
    'margin_top' => 5,
    'format' => 'Letter',
    'shrink_tables_to_fit' => 1
]);

$mpdf->SetTitle("Balance de general del periodo contable: {$dataPeriodo->periodo}");

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
$mpdf->Output("BalanceGeneral{$dataPeriodo->periodo}.pdf", "I");

?>