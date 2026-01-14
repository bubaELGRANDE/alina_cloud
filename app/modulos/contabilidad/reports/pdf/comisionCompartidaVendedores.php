<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    require_once('../../../../../libraries/packages/php/vendor/fpdf/fpdf.php');

    class PDF extends FPDF {
        // Page header
        function Header() {
            global $tituloReporte;
            global $subtituloReporte;
            // url, X, Y, Weight
            $this->Image('../../../../../libraries/resources/images/logos/indupal-logo.png', 10, 8, 40);

            $this->SetFont('Arial', 'B', 11);
            // Existe 216 de ancho en X
            $this->SetXY(54, 8);
            $this->Cell(114, 5, utf8_decode('Industrial La Palma S.A de C.V.'), 0, 0, 'C');
            $this->SetXY(54, 13);
            $this->Cell(114, 5, utf8_decode('Departamento de Contabilidad'), 0, 0, 'C');
            $this->SetXY(54, 18);
            $this->Cell(114, 5, utf8_decode($tituloReporte), 0, 0, 'C');
            $this->SetXY(54, 23);
            $this->Cell(114, 5, utf8_decode($subtituloReporte), 0, 0, 'C');

            // Texto a la derecha del reporte
            $this->SetFont('Arial', '', 8);
            // Existe 216 de ancho en X
            $this->SetXY(165, 8);
            $this->Cell(40, 4, date("d/m/Y H:i:s"), 0, 0, 'R');
            $this->SetXY(165, 12);
            $this->Cell(40, 4, utf8_decode($_SESSION['usuario']), 0, 0, 'R');
        }

        // Page footer
        function Footer() {
            // Position at 1.5 cm from bottom
            $this->SetY(-15);
            // Arial italic 8
            $this->SetFont('Arial','I',7);
            //$this->Cell(0, 5, utf8_decode('Nota: Los montos de abonos reflejados no incluyen IVA (si aplica).'), 0, 0, 'L');
            // Numeración de página
            $this->Cell(0, 5, utf8_decode('Página '.$this->PageNo().'/{nb}'), 0, 0, 'C');
        }
    }

    /*
        REQUEST:
        comisionPagarPeriodoId
    */
   
    $comisionPagarPeriodoId = base64_decode(urldecode($_REQUEST['comisionPagarPeriodoId']));
    $parametrizacionIVA = 1.13;

    $dataPeriodo = $cloud->row("
        SELECT
            numMes,
            mes,
            anio
        FROM conta_comision_pagar_periodo
        WHERE comisionPagarPeriodoId = ?
    ", [$comisionPagarPeriodoId]);

    $periodo = $dataPeriodo->mes . " - " . $dataPeriodo->anio;

    $tituloReporte = 'Comisiones compartidas entre vendedores';
    $subtituloReporte = 'Periodo: ' . $periodo;

    $outputReporte = 'Comisiones compartidas entre vendedores - ' . $periodo;

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();

    $pdf->SetTitle(utf8_decode($outputReporte));

    $dataComisionesCompartidas = $cloud->rows("
        SELECT 
            cc.codTipoFactura AS codTipoFactura,
            cc.tipoFactura AS tipoFactura,
            cc.correlativoFactura AS correlativoFactura,
            cc.sucursalFactura AS sucursalFactura,
            cc.fechaFactura AS fechaFactura,
            DATE_FORMAT(cc.fechaFactura, '%d/%m/%Y') AS fechaFacturaFormat,
            cc.fechaAbono AS fechaAbono,
            DATE_FORMAT(cc.fechaAbono, '%d/%m/%Y') AS fechaAbonoFormat,
            cc.nombreCliente AS nombreCliente,
            cparamd.codVendedor AS codVendedor,
            cparamd.nombreEmpleado AS vendedor,
            cc.flgIdentificador AS flgIdentificador,
            cc.comisionPagar AS comisionItem,
            cc.comisionAbonoPagar AS comisionAbonoPagar,
            cparamd.porcentajeComisionCompartida AS porcentajeComisionCompartida,
            ccomp.comisionCompartidaPagar AS comisionCompartidaPagar
        FROM conta_comision_compartida_calculo ccomp
        JOIN conta_comision_pagar_calculo cc ON cc.comisionPagarCalculoId = ccomp.comisionPagarCalculoId
        JOIN conta_comision_compartida_parametrizacion_detalle cparamd ON cparamd.comisionCompartidaParamDetalleId = ccomp.comisionCompartidaParamDetalleId
        WHERE cc.comisionPagarPeriodoId = ? AND ccomp.flgDelete = ?
        ORDER BY cparamd.codVendedor DESC
    ", [$comisionPagarPeriodoId, 0]);

    $conteoXVendedor = 0;
    $nPagina = 0;
    $codVendedorActual = "N/A";
    $nGeneral = 0;

    $comisionTotalVendedor = 0;
    $comisionCompartidaVendedor = 0;
    foreach ($dataComisionesCompartidas as $comisionesCompartidas) {
        $conteoXVendedor++;
        $nPagina++;
        $nGeneral++;
        
        if($comisionesCompartidas->flgIdentificador == "F") {
            // Consulta SUM para sacar la comision total de esta factura
            $dataComisionVendedorVenta = $cloud->row("
                SELECT 
                    SUM(comisionPagar) AS totalVendedorComision 
                FROM conta_comision_pagar_calculo
                WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND nombreCliente = ? AND correlativoFactura = ? AND tipoFactura = ? AND fechaFactura = ? AND sucursalFactura = ? AND flgIdentificador = ? AND fechaAbono = ? AND flgDelete = '0'
            ", [$comisionPagarPeriodoId, $comisionesCompartidas->vendedor, $comisionesCompartidas->nombreCliente, $comisionesCompartidas->correlativoFactura, $comisionesCompartidas->tipoFactura, $comisionesCompartidas->fechaFactura, $comisionesCompartidas->sucursalFactura, 'F', $comisionesCompartidas->fechaAbono]);
            $comisionTotal = $dataComisionVendedorVenta->totalVendedorComision;
            $fechaAbono = "-";
        } else {
            // Abono
            $comisionTotal = $comisionesCompartidas->comisionAbonoPagar;
            $fechaAbono = $comisionesCompartidas->fechaAbonoFormat;
        }

        if($nPagina > 27 || !($codVendedorActual == $comisionesCompartidas->codVendedor)) {
            if($nGeneral > 1) {
                // Dibujar totales antes de agregar la siguiente página
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY(10, $altura);
                $pdf->Cell(120, 8, utf8_decode("Total del vendedor"), 0, 0, 'L');
                $pdf->Cell(25, 8, number_format($comisionTotalVendedor, 2, '.', ','), 0, 0, 'R'); 
                $pdf->Cell(25, 8, '', 0, 0, 'R'); 
                $pdf->Cell(25, 8, number_format($comisionCompartidaVendedor, 2, '.', ','), 0, 0, 'R'); 

                $pdf->SetXY(10, $altura);
                $pdf->Cell(120, 8, '', 0, 0, 'L');
                $pdf->Cell(25, 8, '$', 0, 0, 'L');
                $pdf->Cell(25, 8, '', 0, 0, 'L');
                $pdf->Cell(25, 8, '$', 0, 0, 'L');

                $pdf->SetXY(10, $altura);
                $pdf->Cell(195, 8, '', 'B', 0, 'C');

                $comisionTotalVendedor = 0;
                $comisionCompartidaVendedor = 0;
            } else {
                // Primer registro, no dibujar totales
            }

            $nPagina = 1;
            $pdf->AddPage();
            $altura = 35;

            if(!($codVendedorActual == $comisionesCompartidas->codVendedor)) {
                $codVendedorActual = $comisionesCompartidas->codVendedor;
                $conteoXVendedor = 1;

                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetXY(10, $altura);
                $pdf->Cell(195, 8, utf8_decode("Vendedor: $comisionesCompartidas->vendedor"), 0, 0, 'C'); 

                $altura += 8;
                $nPagina++;
            } else {
                // Mismo vendedor, 27 registros
            }
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(20, 8, utf8_decode('Documento'), 0, 0, 'C'); 
            $pdf->Cell(20, 8, utf8_decode('Correlativo'), 0, 0, 'C'); 
            $pdf->Cell(20, 8, utf8_decode('Fecha factura'), 0, 0, 'C');
            $pdf->Cell(20, 8, utf8_decode('Fecha abono'), 0, 0, 'C');
            $pdf->Cell(40, 8, utf8_decode('Cliente'), 0, 0, 'C'); 
            $pdf->Cell(25, 4, utf8_decode('Comisión'), 0, 0, 'C'); 
            $pdf->Cell(25, 4, utf8_decode('Porcentaje'), 0, 0, 'C'); 
            $pdf->Cell(25, 4, utf8_decode('Comisión'), 0, 0, 'C'); 

            $pdf->SetXY(10, $altura + 4);
            $pdf->Cell(120, 4, '', 0, 0, 'C'); 
            $pdf->Cell(25, 4, utf8_decode('total'), 0, 0, 'C'); 
            $pdf->Cell(25, 4, utf8_decode('compartido'), 0, 0, 'C'); 
            $pdf->Cell(25, 4, utf8_decode('compartida'), 0, 0, 'C');
            // 195 exactos
            // Borde
            $pdf->SetXY(10, $altura);
            $pdf->Cell(195, 8, '', 'B', 0, 'C');

            $altura += 8;
        } else {
            // Todavia cabe en la pagina
        }
        $pdf->SetFont('Arial', '', 5);
        $pdf->SetXY(5, $altura);
        $pdf->Cell(5, 8, $conteoXVendedor, 0, 0, 'R'); 

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(10, $altura);
        $pdf->Cell(20, 8, utf8_decode(checkTipoDocumento($comisionesCompartidas->codTipoFactura, 0)), 0, 0, 'L'); 
        $pdf->Cell(20, 8, utf8_decode($comisionesCompartidas->correlativoFactura), 0, 0, 'L'); 
        $pdf->Cell(20, 8, utf8_decode($comisionesCompartidas->fechaFacturaFormat), 0, 0, 'C');
        $pdf->Cell(20, 8, utf8_decode($fechaAbono), 0, 0, 'C');
        $pdf->Cell(40, 8, utf8_decode($comisionesCompartidas->nombreCliente), 0, 0, 'L'); 
        $pdf->Cell(25, 8, number_format($comisionTotal, 2, '.', ','), 0, 0, 'R'); 
        $pdf->Cell(25, 8, number_format($comisionesCompartidas->porcentajeComisionCompartida, 2, '.', ',') . ' %', 0, 0, 'R'); 
        $pdf->Cell(25, 8, number_format($comisionesCompartidas->comisionCompartidaPagar, 2, '.', ','), 0, 0, 'R'); 

        $pdf->SetXY(10, $altura);
        $pdf->Cell(120, 8, '', 0, 0, 'L');
        $pdf->Cell(25, 8, '$', 0, 0, 'L');
        $pdf->Cell(25, 8, '', 0, 0, 'L');
        $pdf->Cell(25, 8, '$', 0, 0, 'L');
        // 195 exactos
        // Borde
        $pdf->SetXY(10, $altura);
        $pdf->Cell(195, 8, '', 'B', 0, 'C');

        $comisionTotalVendedor += $comisionTotal;
        $comisionCompartidaVendedor += $comisionesCompartidas->comisionCompartidaPagar;

        $altura += 8;
    }

    // Totales por ser última iteración
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(120, 8, utf8_decode("Total del vendedor"), 0, 0, 'L');
    $pdf->Cell(25, 8, number_format($comisionTotalVendedor, 2, '.', ','), 0, 0, 'R'); 
    $pdf->Cell(25, 8, '', 0, 0, 'R'); 
    $pdf->Cell(25, 8, number_format($comisionCompartidaVendedor, 2, '.', ','), 0, 0, 'R'); 

    $pdf->SetXY(10, $altura);
    $pdf->Cell(120, 8, '', 0, 0, 'L');
    $pdf->Cell(25, 8, '$', 0, 0, 'L');
    $pdf->Cell(25, 8, '', 0, 0, 'L');
    $pdf->Cell(25, 8, '$', 0, 0, 'L');

    $pdf->SetXY(10, $altura);
    $pdf->Cell(195, 8, '', 'B', 0, 'C');

    $pdf->Output(utf8_decode($outputReporte) . '.pdf', "I");

    function checkTipoDocumento($tipoDocumento, $tipoReturn) {
        // tipoReturn: 0 = tipoFactura, 1 = flgTipoCalculo
        switch ($tipoDocumento) {
            case '1':
                $tipoFactura = "C. FINAL";
                $flgTipoCalculo = "IVA"; // Precios CON IVA
            break;
            
            case '2':
                $tipoFactura = "C. FISCAL";
                $flgTipoCalculo = "NIVA"; // Precio SIN IVA
            break;

            case '3':
                $tipoFactura = "EXPORT.";
                $flgTipoCalculo = "NIVA"; // Precio SIN IVA
            break;

            case '4':
                $tipoFactura = "EXENTA";
                $flgTipoCalculo = "NIVA"; // Precio SIN IVA
            break;

            case '5':
                $tipoFactura = "N. DÉBITO";
                $flgTipoCalculo = "NIVA"; // Precio SIN IVA
            break;

            case '8':
                $tipoFactura = "TICKET";
                $flgTipoCalculo = "IVA"; // Precios CON IVA
            break;

            default:
                $tipoFactura = "N/A";
                $flgTipoCalculo = "N/A";
            break;
        }
        if($tipoReturn == 0) {
            return $tipoFactura;
        } else {
            return $flgTipoCalculo;
        }
    }
?>