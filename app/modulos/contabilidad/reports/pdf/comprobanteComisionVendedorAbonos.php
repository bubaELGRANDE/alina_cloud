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
            $this->Cell(40, 4, date("d-m-Y H:i:s"), 0, 0, 'R');
            $this->SetXY(165, 12);
            $this->Cell(40, 4, utf8_decode($_SESSION['usuario']), 0, 0, 'R');
        }

        // Page footer
        function Footer() {
            // Position at 1.5 cm from bottom
            $this->SetY(-15);
            // Arial italic 8
            $this->SetFont('Arial','I',7);
            $this->Cell(0, 5, utf8_decode('Nota: Los montos de abonos reflejados no incluyen IVA (si aplica).'), 0, 0, 'L');
            // Numeración de página
            //$this->Cell(0, 5, utf8_decode('Página '.$this->PageNo().'/{nb}'), 0, 0, 'C');
        }
    }


    /*
        REQUEST:
        filtroVendedores
        vendedorId = multiple
        comisionPagarPeriodoId
    */
   
    $comisionPagarPeriodoId = base64_decode(urldecode($_REQUEST['comisionPagarPeriodoId']));
    $filtroVendedores = base64_decode(urldecode($_REQUEST['filtroVendedores']));
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

    if($filtroVendedores == "Todos") {
        $filtroVendedores = "Todos los vendedores";
        $dataVendedores = $cloud->rows("
            SELECT
                nombreEmpleado,
                codTipoFactura,
                tipoFactura,
                correlativoFactura,
                fechaFactura,
                fechaAbono,
                nombreCliente,
                totalAbono,
                totalAbonoCalculo,
                tasaComisionAbono,
                comisionAbonoPagar,
                flgComisionEditar,
                comisionPagarEditar
            FROM conta_comision_pagar_calculo
            WHERE comisionPagarPeriodoId = ? AND flgIdentificador = 'A' AND flgDelete = '0'
            GROUP BY nombreEmpleado, correlativoFactura, tipoFactura, fechaAbono, totalAbono, flgRepetidoDiferente
            ORDER BY nombreEmpleado, fechaAbono
        ", [$comisionPagarPeriodoId]);
    } else {
        // Especifico
        $filtroVendedores = "Vendedores específicos";
        $vendedorId = base64_decode(urldecode($_REQUEST['vendedorId']));
        // Traer el nombre especifico de cada vendedor, ya que vendedorId trae comisionPagarCalculoId
        // Porque la información viene de magic y codEmpleado o codVendedor vienen en ceros repetidos
        $dataNombresVendedores = $cloud->rows("
            SELECT
                nombreEmpleado
            FROM conta_comision_pagar_calculo
            WHERE comisionPagarCalculoId IN (?)
            GROUP BY nombreEmpleado
            ORDER BY nombreEmpleado
        ", [$vendedorId]);
        $vendedores = "";
        foreach ($dataNombresVendedores as $dataNombresVendedores) {
            $vendedores .= "'" . $dataNombresVendedores->nombreEmpleado . "',";
        }

        // quitar la ultima coma
        $vendedores = substr($vendedores, 0, (strlen($vendedores) - 1));

        $dataVendedores = $cloud->rows("
            SELECT
                nombreEmpleado,
                codTipoFactura,
                tipoFactura,
                correlativoFactura,
                fechaFactura,
                fechaAbono,
                nombreCliente,
                totalAbono,
                totalAbonoCalculo,
                tasaComisionAbono,
                comisionAbonoPagar,
                flgComisionEditar,
                comisionPagarEditar
            FROM conta_comision_pagar_calculo
            WHERE comisionPagarPeriodoId = ? AND nombreEmpleado IN ($vendedores) AND flgIdentificador = 'A' AND flgDelete = '0'
            GROUP BY nombreEmpleado, correlativoFactura, tipoFactura, fechaAbono, totalAbono, flgRepetidoDiferente
            ORDER BY nombreEmpleado, fechaAbono
        ", [$comisionPagarPeriodoId]);
    }

    $tituloReporte = 'Comprobante de comisiones por vendedor';
    $subtituloReporte = 'Periodo: ' . $periodo . ' (Abonos)';

    $outputReporte = 'Comprobante de comisiones por vendedor (Abonos) - ' . $periodo . ' - ' . $filtroVendedores;

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();

    $pdf->SetTitle(utf8_decode($outputReporte));

    $ultimoVendedor = '';
    $n = 0; $conteo = 1; $conteoXVendedor = 1;

    // Por vendedor, general
    $arrayTotalAbono = array(0.00, 0.00);
    $arrayTotalComisiones = array(0.00, 0.00);

    $altura = 58;
    // Maximo por conteo = 54
    // dataVendedores se forma arriba en el if filtroVendedores
    foreach($dataVendedores as $dataVendedores) {
        if($ultimoVendedor == $dataVendedores->nombreEmpleado) {
            if($conteo > 54) {
                $drawEncabezados = "Completo";
            } else {
                // Cabe en la pagina
                 $drawEncabezados = "";
            }
        } else {
            if($n > 0) {
                // Dibujar los totales anteriores
                // Dibujar los totales de la linea ya que no cae en el if de arriba
                // Dibujar los totales de la linea anterior, ya se está iterando la nueva pero los totales se mantienen en los array
                // Verificar si cabe en la página
                if(($conteo + 1) > 54) {
                    $pdf->AddPage();
                    $conteo = 1;
                    $altura = 40;
                } else {
                    // Cabe en la pagina
                }

                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetXY(10, $altura);
                $pdf->Cell(120, 4, utf8_decode('Total Vendedor: ' . $ultimoVendedor), 0, 0, 'L'); // Celdas combinadas
                $pdf->Cell(25, 4, number_format($arrayTotalAbono[0], 2, ".", ","), 0, 0, 'R'); // Total abono
                $pdf->Cell(25, 4, '', 0, 0, 'R'); // Tasa
                $pdf->Cell(25, 4, number_format($arrayTotalComisiones[0], 2, ".", ","), 0, 0, 'R'); // Total comisiones

                // Borde
                $pdf->SetXY(10, $altura);
                $pdf->Cell(195, 4, '', 'B', 0, 'L'); // Celda completa para el margen

                // Simbolos de dolar
                $pdf->SetXY(10, $altura);
                $pdf->Cell(120, 4, '', 0, 0, 'L'); // Celdas combinadas
                $pdf->Cell(25, 4, utf8_decode('$'), 0, 0, 'L'); // Total abono
                $pdf->Cell(25, 4, '', 0, 0, 'R'); // Tasa
                $pdf->Cell(25, 4, utf8_decode('$'), 0, 0, 'L'); // Total comisiones                
            } else {
                // Es la primer iteracion
            }
            // Ya se está iterando otro vendedor
            $drawEncabezados = "Completo";
            $conteoXVendedor = 1;
            // Reiniciar totales por vendedor
            $arrayTotalAbono[0] = 0.00;
            $arrayTotalComisiones[0] = 0.00;
        }

        if($drawEncabezados == "Completo") {
            $conteo = 1;
            $altura = 40;
            $pdf->AddPage();
            // Ancho 195 ya con margen incluido
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(195, 4, utf8_decode('Vendedor: ' . $dataVendedores->nombreEmpleado), 0, 0, 'C');
            $altura += 4;
            // Columnas de la tabla
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(20, 4, utf8_decode('Documento'), 0, 0, 'C'); // Celda para Tipo de Documento
            $pdf->Cell(20, 4, utf8_decode('Correlativo'), 0, 0, 'C'); // Celda para correlativo
            $pdf->Cell(20, 4, utf8_decode('Fecha Factura'), 0, 0, 'C'); // Celda para fecha factura
            $pdf->Cell(20, 4, utf8_decode('Fecha Abono'), 0, 0, 'C'); // Celda para fecha abono
            $pdf->Cell(40, 4, utf8_decode('Cliente'), 0, 0, 'C'); // Celda para cliente
            $pdf->Cell(25, 4, utf8_decode('Abono'), 0, 0, 'C'); // Celda para abono
            $pdf->Cell(25, 4, utf8_decode('Tasa por abono'), 0, 0, 'C'); // Celda para tasa
            $pdf->Cell(25, 4, utf8_decode('Comisión'), 0, 0, 'C'); // Celda para comision
            // 195 exactos
            // Borde
            $pdf->SetXY(10, $altura);
            $pdf->Cell(195, 4, '', 'B', 0, 'C');
            
            $conteo += 2;
            $altura += 4;
        } else {
            // No dibujar encabezados
        }

        if($dataVendedores->flgComisionEditar == 0) {
            $comisionAbonoPagar = $dataVendedores->comisionAbonoPagar;
        } else {
            $comisionAbonoPagar = $dataVendedores->comisionPagarEditar;
        }

        $tipoFactura = checkTipoDocumento($dataVendedores->codTipoFactura, 0);
        $tasaComisionAbono = number_format($dataVendedores->tasaComisionAbono, 2, ".", ",");
        // Data general
        // Columnas de la tabla
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(10, $altura);
        $pdf->Cell(20, 4, utf8_decode($tipoFactura), 0, 0, 'C'); // Celda para Tipo de Documento
        $pdf->Cell(20, 4, utf8_decode($dataVendedores->correlativoFactura), 0, 0, 'R'); // Celda para correlativo
        $pdf->Cell(20, 4, utf8_decode(date("d/m/Y", strtotime($dataVendedores->fechaFactura))), 0, 0, 'C'); // Celda para fecha factura
        $pdf->Cell(20, 4, utf8_decode(date("d/m/Y", strtotime($dataVendedores->fechaAbono))), 0, 0, 'C'); // Celda para fecha abono
        $pdf->Cell(40, 4, utf8_decode($dataVendedores->nombreCliente), 0, 0, 'L'); // Celda para cliente
        $pdf->Cell(25, 4, number_format($dataVendedores->totalAbonoCalculo, 2, ".", ","), 0, 0, 'R'); // Celda para abono
        $pdf->Cell(25, 4, utf8_decode($tasaComisionAbono . '%'), 0, 0, 'R'); // Celda para tasa
        $pdf->Cell(25, 4, number_format($comisionAbonoPagar, 2, ".", ","), 0, 0, 'R'); // Celda para comision
        // 195 exactos
        // Borde
        $pdf->SetFont('Arial', '', 5);
        $pdf->SetXY(5, $altura);
        $pdf->Cell(5, 4, $conteoXVendedor, 0, 0, 'C'); 
        $pdf->SetXY(10, $altura);
        $pdf->Cell(195, 4, '', 'B', 0, 'C'); // Celda para Tipo de Documento / N° Factura

        // Símbolos de dólar
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(10, $altura);
        $pdf->Cell(20, 4, '', 0, 0, 'C'); // Celda para Tipo de Documento
        $pdf->Cell(20, 4, '', 0, 0, 'R'); // Celda para correlativo
        $pdf->Cell(20, 4, '', 0, 0, 'C'); // Celda para fecha factura
        $pdf->Cell(20, 4, '', 0, 0, 'C'); // Celda para fecha abono
        $pdf->Cell(40, 4, '', 0, 0, 'L'); // Celda para cliente
        $pdf->Cell(25, 4, utf8_decode('$'), 0, 0, 'L'); // Celda para abono
        $pdf->Cell(25, 4, '', 0, 0, 'R'); // Celda para tasa
        $pdf->Cell(25, 4, utf8_decode('$'), 0, 0, 'L'); // Celda para comision

        // Sumar totales
        $arrayTotalAbono[0] += $dataVendedores->totalAbonoCalculo;
        $arrayTotalAbono[1] += $dataVendedores->totalAbonoCalculo;

        $arrayTotalComisiones[0] += $comisionAbonoPagar;
        $arrayTotalComisiones[1] += $comisionAbonoPagar;

        // Variables para continuar reporte
        $ultimoVendedor = $dataVendedores->nombreEmpleado;
        $altura += 4;
        $n += 1;
        $conteo += 1;
        $conteoXVendedor += 1;
    }
    // Termina de iterar pero queda el ultimo vendedor sin su total
    // Mostrar el total del ultimo vendedor
    // Verificar si cabe en la página
    if(($conteo + 1) > 54) {
        $pdf->AddPage();
        $conteo = 1;
        $altura = 40;
    } else {
        // Cabe en la pagina
    }
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(120, 4, utf8_decode('Total Vendedor: ' . $ultimoVendedor), 0, 0, 'L'); // Celdas combinadas
    $pdf->Cell(25, 4, number_format($arrayTotalAbono[0], 2, ".", ","), 0, 0, 'R'); // Total abono
    $pdf->Cell(25, 4, '', 0, 0, 'R'); // Tasa
    $pdf->Cell(25, 4, number_format($arrayTotalComisiones[0], 2, ".", ","), 0, 0, 'R'); // Total comisiones

    // Borde
    $pdf->SetXY(10, $altura);
    $pdf->Cell(195, 4, '', 'B', 0, 'L'); // Celda completa para el margen

    // Simbolos de dolar
    $pdf->SetXY(10, $altura);
    $pdf->Cell(120, 4, '', 0, 0, 'L'); // Celdas combinadas
    $pdf->Cell(25, 4, utf8_decode('$'), 0, 0, 'L'); // Total abono
    $pdf->Cell(25, 4, '', 0, 0, 'R'); // Tasa
    $pdf->Cell(25, 4, utf8_decode('$'), 0, 0, 'L'); // Total comisiones    

    // Dibujar el total general en una nueva pagina
    $conteo = 1;
    $altura = 40;
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(195, 4, utf8_decode('Totales'), 0, 0, 'C');
    $altura += 4;
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(120, 4, utf8_decode('N° Total de abonos'), 0, 0, 'L'); // Celdas combinadas
    $pdf->Cell(25, 4, number_format($n, 0, ".", ","), 0, 0, 'R'); // Total abono
    $altura += 8;
    $pdf->SetXY(10, $altura);
    $pdf->Cell(120, 4, utf8_decode('Total General'), 0, 0, 'L'); // Celdas combinadas
    $pdf->Cell(25, 4, number_format($arrayTotalAbono[1], 2, ".", ","), 0, 0, 'R'); // Total abono
    $pdf->Cell(25, 4, '', 0, 0, 'R'); // Tasa
    $pdf->Cell(25, 4, number_format($arrayTotalComisiones[1], 2, ".", ","), 0, 0, 'R'); // Total comisiones

    // Borde
    $pdf->SetXY(10, $altura-8);
    $pdf->Cell(195, 4, '', 'B', 0, 'L'); // Celda completa para el margen
    $pdf->SetXY(10, $altura);
    $pdf->Cell(195, 4, '', 'B', 0, 'L'); // Celda completa para el margen

    // Simbolos de dolar
    $pdf->SetXY(10, $altura);
    $pdf->Cell(120, 4, '', 0, 0, 'L'); // Celdas combinadas
    $pdf->Cell(25, 4, utf8_decode('$'), 0, 0, 'L'); // Total abono
    $pdf->Cell(25, 4, '', 0, 0, 'R'); // Tasa
    $pdf->Cell(25, 4, utf8_decode('$'), 0, 0, 'L'); // Total comisiones  


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
                $tipoFactura = "EXPORTACIÓN";
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