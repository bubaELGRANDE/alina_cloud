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
        function encabezadoTabla($txtEncabezado) {
            $alturaTemp = $this->GetY();
            $this->SetFont('Arial', 'B', 10);
            $this->SetXY(10, $this->GetY());
            $this->Cell(196, 5, utf8_decode($txtEncabezado), 0, 0, 'C');
            $this->SetXY(10, $this->GetY() + 6);
            $this->SetFont('Arial', 'B', 9);
            $this->SetXY(10, $this->GetY());
            $this->Cell(11, 5, utf8_decode('#'), 'B', 0, 'C');
            $this->Cell(55, 5, utf8_decode('Concepto'), 'B', 0, 'C');
            $this->Cell(55, 5, utf8_decode('Proveedor'), 'B', 0, 'C');
            $this->Cell(15, 5, utf8_decode('Banco'), 'B', 0, 'C');
            $this->Cell(30, 5, utf8_decode('Cuenta'), 'B', 0, 'C');
            $this->Cell(30, 5, utf8_decode('Monto'), 'B', 0, 'C');

            $this->SetY($this->GetY() + 5);
        }

        // Renglones de la tabla
        function registrosTabla($n, $concepto, $proveedor, $banco, $cuenta, $monto) {
            $alturaTemp = $this->GetY();
            $this->SetFont('Arial', '', 9);
            $this->SetXY(10, $this->GetY());
            $this->Cell(11, 5, $n, 0, 0, 'C');
            $this->SetXY(21, $alturaTemp);
            $this->MultiCell(55, 4, utf8_decode($concepto), 0, 'L');
            $alturaCierre1 = $this->GetY();
            $anchoCelda1 = ($alturaCierre1 - $alturaTemp) + 1;
            
            $this->SetXY(76, $alturaTemp);
            $this->MultiCell(55, 4, utf8_decode($proveedor), 0, 'L');
            $alturaCierre2 = $this->GetY();
            $anchoCelda2 = ($alturaCierre2 - $alturaTemp) + 1;

            $this->SetXY(131, $alturaTemp);
            $this->Cell(15, 5, utf8_decode($banco), 0, 0, 'L');
            $this->Cell(30, 5, utf8_decode($cuenta), 0, 0, 'R');
            $this->Cell(30, 5, number_format($monto, 2, ".", ","), 0, 0, 'R');

            // Simbolos de dolar
            $this->SetXY(176, $alturaTemp);
            $this->Cell(30, 5, utf8_decode("$"), 0, 0, 'L');

            $anchoCelda = ($anchoCelda1 > $anchoCelda2 ? $anchoCelda1 : $anchoCelda2);

            $this->SetXY(10, $alturaTemp);
            $this->Cell(196, $anchoCelda, '', 'B', 0, 'C');

            $this->SetY($alturaTemp + $anchoCelda);
        }

        // Total general al final de la tabla
        function footerTabla($txtTotal, $totalGeneral) {
            $alturaTemp = $this->GetY();
            $this->SetFont('Arial', 'B', 10);
            $this->SetXY(10, $this->GetY());
            $this->Cell(166, 5, utf8_decode($txtTotal), 'B', 0, 'L');
            $this->Cell(30, 5, number_format($totalGeneral, 2), 'B', 0, 'R');

            // Simbolos de dolar
            $this->SetXY(176, $alturaTemp);
            $this->Cell(30, 5, utf8_decode("$"), 0, 0, 'L');

            $this->SetY($this->GetY() + 15);
        }
    }

    /*
        REQUEST:
		fechaPagoTransferenciaReporte
		pagoTransferenciaIdReporte
    */
    $fechaPagoTransferencia = base64_decode(urldecode($_REQUEST['fechaPagoTransferenciaReporte']));
    $fechaPagoTransferenciaFormat = date("d/m/Y", strtotime($fechaPagoTransferencia));
    $fechaPagoTransferenciaFormatOutput = date("d-m-Y", strtotime($fechaPagoTransferencia));
    $pagoTransferenciaId = base64_decode(urldecode($_REQUEST['pagoTransferenciaIdReporte']));

    $dataTransferencia = $cloud->row("
        SELECT
            cpt.pagoTransferenciaId AS pagoTransferenciaId,
            cpt.tipoTransferencia AS tipoTransferencia,
            org.abreviaturaOrganizacion AS abreviaturaOrganizacion,
            org.nombreOrganizacion AS nombreOrganizacion
        FROM conta_pagos_transferencias cpt
        JOIN cat_nombres_organizaciones org ON org.nombreOrganizacionId = cpt.nombreOrganizacionId
        WHERE cpt.pagoTransferenciaId = ? AND cpt.flgDelete = ?
    ", [$pagoTransferenciaId, 0]);

    $tituloReporte = "Pagos por transferencia: {$fechaPagoTransferenciaFormat}";
    $subtituloReporte = "{$dataTransferencia->abreviaturaOrganizacion}: {$dataTransferencia->tipoTransferencia}";

    $outputReporte = "Pagos por transferencia ({$fechaPagoTransferenciaFormatOutput} - {$dataTransferencia->abreviaturaOrganizacion} - {$dataTransferencia->tipoTransferencia})";

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    
    $pdf->SetTitle(utf8_decode($outputReporte));
    $pdf->AddPage();
    $pdf->SetY(35);

    $n = 0;
    $totalGeneral = 0;
    for ($x=1; $x <= 2; $x++) { 
        // + 10 para que quepa un registro más por lo menos
        if($pdf->GetY() + 10 > 252) {
            $pdf->AddPage();
            $pdf->SetY(35);
        } else {
            // Cabe en la página
        }

        if($x == 1) {
            $txtEncabezado = "Pagos por transferencia: Quedan";
            $txtTotal = "Total general: Quedan";
            $tablaDetalle = "comp_quedan";
        } else {
            $txtEncabezado = "Pagos por transferencia: Otros pagos";
            $txtTotal = "Total general: Otros pagos";
            $tablaDetalle = "conta_pagos_transferencias_detalle";
        }

        $dataTransferenciaDetalle = $cloud->rows("
            SELECT 
                pt.estadoPago AS estadoPago,
                ptd.pagoTransferenciaDetalleId AS pagoTransferenciaDetalleId, 
                ptd.proveedorCBancariaId AS proveedorCBancariaId, 
                ptd.conceptoTransferencia AS conceptoTransferencia,
                ptd.montoTransferencia AS montoTransferencia, 
                ptd.tablaDetalleId AS tablaDetalleId,
                p.nombreProveedor AS nombreProveedor,
                p.nombreComercial AS nombreComercial,
                org.abreviaturaOrganizacion AS abreviaturaOrganizacion,
                pcb.numeroCuenta AS numeroCuenta,
                pcb.tipoCuenta AS tipoCuenta
            FROM conta_pagos_transferencias_detalle ptd
            JOIN conta_pagos_transferencias pt ON pt.pagoTransferenciaId = ptd.pagoTransferenciaId
            JOIN comp_proveedores_cbancaria pcb ON pcb.proveedorCBancariaId = ptd.proveedorCBancariaId
            JOIN comp_proveedores p ON p.proveedorId = pcb.proveedorId
            JOIN cat_nombres_organizaciones org ON org.nombreOrganizacionId = pcb.nombreOrganizacionId
            WHERE ptd.pagoTransferenciaId = ? AND ptd.tablaDetalle = ? AND ptd.flgDelete = ?
            ORDER BY ptd.tablaDetalleId
        ", [$pagoTransferenciaId, $tablaDetalle, 0]);

        $pdf->encabezadoTabla($txtEncabezado);

        $totalGeneralPago = 0;
        foreach($dataTransferenciaDetalle as $transferenciaDetalle) { 
            $n++;
            $concepto = $transferenciaDetalle->conceptoTransferencia;
            $proveedor = $transferenciaDetalle->nombreComercial;
            $banco = $transferenciaDetalle->abreviaturaOrganizacion;
            $cuenta = $transferenciaDetalle->numeroCuenta;
            $monto = $transferenciaDetalle->montoTransferencia;

            if($pdf->GetY() > 252) {
                $pdf->AddPage();
                $pdf->SetY(35);
                $pdf->encabezadoTabla($txtEncabezado);
            } else {
                // Cabe en la página
            }

            $pdf->registrosTabla($n, $concepto, $proveedor, $banco, $cuenta, $monto);

            $totalGeneralPago += $monto;
        }

        if($pdf->GetY() > 252) {
            $pdf->AddPage();
            $pdf->SetY(35);
        } else {
            // Cabe en la página
        }

        $pdf->footerTabla($txtTotal, $totalGeneralPago);
        $totalGeneral += $totalGeneralPago;
    }

    if($pdf->GetY() > 252) {
        $pdf->AddPage();
        $pdf->SetY(35);
    } else {
        // Cabe en la página
    }

    $pdf->footerTabla("Total general", $totalGeneral);

    $pdf->Output(utf8_decode($outputReporte) . '.pdf', "I");
?>
