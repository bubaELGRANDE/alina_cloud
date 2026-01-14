<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    require_once('../../../../../libraries/packages/php/vendor/fpdf/fpdf.php');
    @session_start();

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
            $this->Cell(40, 4, date("d-m-Y H:i:s"), 0, 0, 'R');
            $this->SetXY(165, 12);
            $this->Cell(40, 4, utf8_decode($_SESSION['usuario']), 0, 0, 'R');
        }

        // Page footer
        function Footer() {
            // Position at 1.5 cm from bottom
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            // Numeración de página
            $this->Cell(0, 5, utf8_decode('Página '.$this->PageNo().'/{nb}'), 0, 0, 'C');
        }

        // Encabezado de la tabla
        function encabezadoTabla($altura) {
            $this->SetFont('Arial', 'B', 9);
            // 196
            $this->SetXY(10, $altura);
            $this->Cell(10, 10, utf8_decode('#'), 'B', 0, 'C');
            $this->Cell(50, 10, utf8_decode('Empleado'), 'B', 0, 'C');
            $this->Cell(18, 5, utf8_decode('Código:'), 0, 0, 'C');
            $this->Cell(18, 5, utf8_decode('Código:'), 0, 0, 'C');
            $this->Cell(25, 5, utf8_decode('Comisión:'), 0, 0, 'C');
            $this->Cell(25, 5, utf8_decode('Comisión:'), 0, 0, 'C');
            $this->Cell(25, 5, utf8_decode('Comisión:'), 0, 0, 'C');
            $this->Cell(25, 5, utf8_decode('Total'), 0, 0, 'C');
            $altura += 5;

            $this->SetXY(70, $altura);
            $this->Cell(18, 5, utf8_decode('Empleado'), 'B', 0, 'C');
            $this->Cell(18, 5, utf8_decode('Vendedor'), 'B', 0, 'C');
            $this->Cell(25, 5, utf8_decode('Contado'), 'B', 0, 'C');
            $this->Cell(25, 5, utf8_decode('Abono'), 'B', 0, 'C');
            $this->Cell(25, 5, utf8_decode('Compartida'), 'B', 0, 'C');
            $this->Cell(25, 5, utf8_decode('Comisión'), 'B', 0, 'C');
            $altura += 5;

            $this->SetY($altura);
        }

        // Renglones de la tabla
        function registrosTabla($n, $empleado, $codEmpleado, $codVendedor, $contado, $abono, $compartida, $total) {
            $alturaTemp = $this->GetY();
            $this->SetXY(10, $this->GetY());
            $this->SetFont('Arial', '', 9);
            $this->Cell(10, 5, $n, 'B', 0, 'C');
            $this->SetFont('Arial', '', 8);
            $this->Cell(50, 5, utf8_decode($empleado), 'B', 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(18, 5, $codEmpleado, 'B', 0, 'C');
            $this->Cell(18, 5, $codVendedor, 'B', 0, 'C');
            $this->Cell(25, 5, number_format($contado, 2, ".", ","), 'B', 0, 'R');
            $this->Cell(25, 5, number_format($abono, 2, ".", ","), 'B', 0, 'R');
            $this->Cell(25, 5, number_format($compartida, 2, ".", ","), 'B', 0, 'R');
            $this->Cell(25, 5, number_format($total, 2, ".", ","), 'B', 0, 'R');

            // Simbolos de dolar
            $this->SetXY(106, $alturaTemp);
            $this->Cell(25, 5, utf8_decode("$"), 0, 0, 'L');
            $this->Cell(25, 5, utf8_decode("$"), 0, 0, 'L');
            $this->Cell(25, 5, utf8_decode("$"), 0, 0, 'L');
            $this->Cell(25, 5, utf8_decode("$"), 0, 0, 'L');

            $this->SetY($this->GetY() + 5);
        }

        // Total general al final de la tabla
        function TableFooter($totalContado, $totalAbono, $totalCompartida, $totalGeneral) {
            $alturaTemp = $this->GetY();
            $this->SetFont('Arial', 'B', 10);
            $this->SetXY(10, $this->GetY());
            $this->Cell(96, 5, 'Total general', 'B', 0, 'L');
            $this->Cell(25, 5, number_format($totalContado, 2), 'B', 0, 'R');
            $this->Cell(25, 5, number_format($totalAbono, 2), 'B', 0, 'R');
            $this->Cell(25, 5, number_format($totalCompartida, 2), 'B', 0, 'R');
            $this->Cell(25, 5, number_format($totalGeneral, 2), 'B', 0, 'R');

            // Simbolos de dolar
            $this->SetXY(106, $alturaTemp);
            $this->Cell(25, 5, utf8_decode("$"), 0, 0, 'L');
            $this->Cell(25, 5, utf8_decode("$"), 0, 0, 'L');
            $this->Cell(25, 5, utf8_decode("$"), 0, 0, 'L');
            $this->Cell(25, 5, utf8_decode("$"), 0, 0, 'L');
        }
    }

    /*
        REQUEST:
        filtroVendedores
        vendedorId = multiple
        comisionPagarPeriodoId
        txtPeriodo
    */
    $parametrizacionIVA = 1.13;
    $comisionPagarPeriodoId = base64_decode(urldecode($_REQUEST['comisionPagarPeriodoId']));
    $filtroVendedores = base64_decode(urldecode($_REQUEST['filtroVendedores']));
    $txtPeriodo = base64_decode(urldecode($_REQUEST['txtPeriodo']));

    $periodo = $txtPeriodo;

    $tituloReporte = 'Consolidado de comisiones por vendedor';
    $subtituloReporte = 'Periodo: ' . $periodo;

    $outputReporte = 'Consolidado de comisiones por vendedor - ' . $periodo;

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    
    $pdf->SetTitle(utf8_decode($outputReporte));
    $pdf->AddPage();
    $altura = 35;
    $pdf->encabezadoTabla($altura);

    if($filtroVendedores == "Todos") {
        // Todos los vendedores
        $whereVendedores = "";
    } else {
        // Especifico
        $vendedorId = base64_decode(urldecode($_REQUEST['vendedorId']));
        $whereVendedores = "AND comisionPagarCalculoId IN ($vendedorId)";
    }

    $dataVendedoresComision = $cloud->rows("
        SELECT 
            codEmpleado,
            codVendedor,
            nombreEmpleado
        FROM conta_comision_pagar_calculo
        WHERE comisionPagarPeriodoId = ? AND flgDelete = '0'
        $whereVendedores
        GROUP BY nombreEmpleado
        ORDER BY nombreEmpleado
    ", [$comisionPagarPeriodoId]);

    $n = 0;
    $totalGeneralContado = 0;
    $totalGeneralAbono = 0;
    $totalGeneralCompartida = 0;
    $totalGeneral = 0;
    $nPagina = 0;
    foreach ($dataVendedoresComision as $data) {
        $n++;
        $nPagina++;

        // Verificar quiebre de página después de cada fila si es necesario
        if ($nPagina > 42) {
            $pdf->AddPage();
            $altura = 35;
            $nPagina = 1;
            $pdf->encabezadoTabla($altura);
        } else {
            // Cabe en la pagina
        }

        $totalVendedorComision = 0;

        // Sumar comisiones de contado
        $dataComisionVendedorVenta = $cloud->row("
            SELECT 
                SUM(
                    CASE 
                        WHEN flgComisionEditar = '1' THEN comisionPagarEditar 
                        ELSE comisionPagar 
                    END
                ) AS totalVendedorComision
            FROM conta_comision_pagar_calculo
            WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = 'F' AND flgDelete = '0'
        ", [$comisionPagarPeriodoId, $data->nombreEmpleado]);

        $totalVendedorComision = $dataComisionVendedorVenta->totalVendedorComision ?? 0;

        // Sumar comisiones de abono
        $totalVendedorComisionAbono = 0;

        $dataComisionVendedorAbono = $cloud->rows("
            SELECT 
                comisionAbonoPagar,
                flgComisionEditar,
                comisionPagarEditar
            FROM conta_comision_pagar_calculo
            WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = 'A' AND flgDelete = '0'
            GROUP BY nombreEmpleado, correlativoFactura, tipoFactura, fechaAbono, totalAbono, flgRepetidoDiferente
        ", [$comisionPagarPeriodoId, $data->nombreEmpleado]);
        
        foreach ($dataComisionVendedorAbono as $abono) {
            $totalVendedorComisionAbono += ($abono->flgComisionEditar == '1') ? $abono->comisionPagarEditar : $abono->comisionAbonoPagar;
        }

        $totalVendedorComisionCompartida = 0;

        $dataComisionesCompartidas = $cloud->row("
            SELECT 
                SUM(ccomp.comisionCompartidaPagar) AS comisionCompartidaPagar
            FROM conta_comision_compartida_calculo ccomp
            JOIN conta_comision_pagar_calculo cc ON cc.comisionPagarCalculoId = ccomp.comisionPagarCalculoId
            JOIN conta_comision_compartida_parametrizacion_detalle cparamd ON cparamd.comisionCompartidaParamDetalleId = ccomp.comisionCompartidaParamDetalleId
            WHERE cc.comisionPagarPeriodoId = ? AND cparamd.nombreEmpleado = ? AND ccomp.flgDelete = ?
        ", [$comisionPagarPeriodoId, $data->nombreEmpleado, 0]);

        $totalVendedorComisionCompartida = $dataComisionesCompartidas->comisionCompartidaPagar;

        $totalComisionVendedor = $totalVendedorComision + $totalVendedorComisionAbono + $totalVendedorComisionCompartida;

        $pdf->registrosTabla($n, $data->nombreEmpleado, $data->codEmpleado, $data->codVendedor, $totalVendedorComision, $totalVendedorComisionAbono, $totalVendedorComisionCompartida, $totalComisionVendedor);

        $totalGeneralContado += $totalVendedorComision;
        $totalGeneralAbono += $totalVendedorComisionAbono;
        $totalGeneralCompartida += $totalVendedorComisionCompartida;
        $totalGeneral += $totalComisionVendedor;
    }

    // Pie de la tabla con totales generales
    $pdf->TableFooter($totalGeneralContado, $totalGeneralAbono, $totalGeneralCompartida, $totalGeneral);

    $pdf->Output(utf8_decode($outputReporte) . '.pdf', "I");
?>
