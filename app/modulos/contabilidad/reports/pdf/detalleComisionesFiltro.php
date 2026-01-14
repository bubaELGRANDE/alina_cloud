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
            $this->Cell(0, 5, utf8_decode('Nota: Se detallan precios y totales sin IVA de cada factura (IVA Percibido y Retenido no son tomados en cuenta).'), 0, 0, 'L');
            // Numeración de página
            //$this->Cell(0, 5, utf8_decode('Página '.$this->PageNo().'/{nb}'), 0, 0, 'C');
        }
    }


    /*
        REQUEST:
        comisionPagarPeriodoId
        filtroVendedores
        vendedorId = multiple
        filtroLineas
        lineaId = multiple
        filtroPorcentajeComision
        porcentajeComision
        porcentajeComisionR1
        porcentajeComisionR2
    */
   
    $comisionPagarPeriodoId = base64_decode(urldecode($_REQUEST['comisionPagarPeriodoId']));
    $filtroClientes = base64_decode(urldecode($_REQUEST['filtroClientes']));
    $filtroVendedores = base64_decode(urldecode($_REQUEST['filtroVendedores']));
    $filtroLineas = base64_decode(urldecode($_REQUEST['filtroLineas']));
    $filtroPorcentajeComision = base64_decode(urldecode($_REQUEST['filtroPorcentajeComision']));
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

    $whereClientes = ''; $whereVendedores = ''; $whereLineas = ''; $wherePorcentajeComision = '';
    $filtroReporte = "";

    if($filtroClientes == "Especifico") {
        $filtroReporte = "Clientes específicos";
        $clienteId = base64_decode(urldecode($_REQUEST['clienteId']));
        // Traer el nombre especifico de cada vendedor, ya que vendedorId trae comisionPagarCalculoId
        // Porque la información viene de magic y codEmpleado o codVendedor vienen en ceros repetidos
        $dataNombresClientes = $cloud->rows("
            SELECT
                nombreCliente
            FROM conta_comision_pagar_calculo
            WHERE comisionPagarCalculoId IN ($clienteId) AND flgDelete = ?
            GROUP BY nombreCliente
            ORDER BY nombreCliente
        ", ['0']);
        $clientes = "";
        foreach ($dataNombresClientes as $dataNombresClientes) {
            $clientes .= "'" . $dataNombresClientes->nombreCliente . "',";
        }

        // quitar la ultima coma
        $clientes = substr($clientes, 0, (strlen($clientes) - 1));

        $whereClientes = "AND nombreCliente IN ($clientes)";
    } else {
        // Todos los vendedores
    }

    if($filtroVendedores == "Especifico") {
        $filtroReporte .= ($filtroReporte == "" ? "Vendedores específicos" : ", vendedores específicos");
        $vendedorId = base64_decode(urldecode($_REQUEST['vendedorId']));
        // Traer el nombre especifico de cada vendedor, ya que vendedorId trae comisionPagarCalculoId
        // Porque la información viene de magic y codEmpleado o codVendedor vienen en ceros repetidos
        $dataNombresVendedores = $cloud->rows("
            SELECT
                nombreEmpleado
            FROM conta_comision_pagar_calculo
            WHERE comisionPagarCalculoId IN ($vendedorId) AND flgDelete = ?
            GROUP BY nombreEmpleado
            ORDER BY nombreEmpleado
        ", ['0']);
        $vendedores = "";
        foreach ($dataNombresVendedores as $dataNombresVendedores) {
            $vendedores .= "'" . $dataNombresVendedores->nombreEmpleado . "',";
        }

        // quitar la ultima coma
        $vendedores = substr($vendedores, 0, (strlen($vendedores) - 1));

        $whereVendedores = "AND nombreEmpleado IN ($vendedores)";
    } else {
        // Todos los vendedores
    }

    if($filtroLineas == "Especifico") {
        $filtroReporte .= ($filtroReporte == "" ? "Líneas específicas" : ", líneas específicas");
        $lineaId = base64_decode(urldecode($_REQUEST['lineaId']));
        // Traer el nombre especifico de cada vendedor, ya que vendedorId trae comisionPagarCalculoId
        // Porque la información viene de magic y codEmpleado o codVendedor vienen en ceros repetidos
        $dataNombresLineas = $cloud->rows("
            SELECT abreviatura, linea FROM temp_cat_lineas
            WHERE lineaId IN ($lineaId) AND flgDelete = ?
        ", ['0']);
        $lineas = "";
        foreach ($dataNombresLineas as $dataNombresLineas) {
            $lineas .= "'" . $dataNombresLineas->abreviatura . "',";
        }

        // quitar la ultima coma
        $lineas = substr($lineas, 0, (strlen($lineas) - 1));

        $whereLineas = "AND lineaProducto IN ($lineas)";
    } else {
        // Todas las lineas
    }

    if($filtroPorcentajeComision == "Especifico") {
        $filtroReporte .= ($filtroReporte == "" ? "Porcentaje de comisión específico" : " y porcentaje de comisión específico");
        $wherePorcentajeComision = "AND paramPorcentajePago = '" . base64_decode(urldecode($_REQUEST['porcentajeComision'])) . "'";
    } else if($filtroPorcentajeComision == "Rango") {
        $filtroReporte .= ($filtroReporte == "" ? "Porcentaje de comisión por rango" : " y porcentaje de comisión por rango");
        $wherePorcentajeComision = "AND (paramPorcentajePago BETWEEN '" . base64_decode(urldecode($_REQUEST['porcentajeComisionR1'])) . "' AND '" . base64_decode(urldecode($_REQUEST['porcentajeComisionR2'])) . "')";
    } else {
        // No especificar porcentaje de comision
    }

    $dataVendedores = $cloud->rows("
        SELECT
            nombreEmpleado,
            codTipoFactura,
            tipoFactura,
            correlativoFactura,
            fechaFactura,
            nombreCliente,
            lineaProducto,
            codProductoFactura,
            precioUnitario,
            precioFacturado,
            cantidadProducto,
            totalVenta,
            porcentajeDescuento,
            paramRangoPorcentajeInicio,
            paramRangoPorcentajeFin,
            paramPorcentajePago,
            comisionPagar,
            flgComisionEditar,
            comisionPagarEditar
        FROM conta_comision_pagar_calculo
        WHERE comisionPagarPeriodoId = ? AND flgDelete = '0' $whereClientes $whereVendedores $whereLineas $wherePorcentajeComision
        ORDER BY nombreEmpleado, lineaProducto, fechaFactura
    ", [$comisionPagarPeriodoId]);

    $dataTotalGeneralPorLinea = $cloud->rows("
        SELECT
            nombreEmpleado,
            codTipoFactura,
            tipoFactura,
            correlativoFactura,
            fechaFactura,
            nombreCliente,
            lineaProducto,
            codProductoFactura,
            precioUnitario,
            precioFacturado,
            cantidadProducto,
            totalVenta,
            porcentajeDescuento,
            paramRangoPorcentajeInicio,
            paramRangoPorcentajeFin,
            paramPorcentajePago,
            comisionPagar,
            flgComisionEditar,
            comisionPagarEditar
        FROM conta_comision_pagar_calculo
        WHERE comisionPagarPeriodoId = ? AND flgDelete = '0' $whereClientes $whereVendedores $whereLineas $wherePorcentajeComision
        ORDER BY lineaProducto 
    ", [$comisionPagarPeriodoId]);

    $tituloReporte = 'Detalle de comisiones por filtro';
    $subtituloReporte = 'Periodo: ' . $periodo . ' (Contado y Abonos)';

    $outputReporte = 'Detalle de comisiones por filtro (Contado y Abonos) - ' . $periodo . ' - ' . $filtroReporte;

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();

    $pdf->SetTitle(utf8_decode($outputReporte));

    $ultimoVendedor = ''; $ultimaLinea = '';
    $n = 0; $conteo = 1; $conteoXLinea = 1;

    // Por linea, por vendedor, total general
    $arrayTotalesCantidadVenta = array(0.00, 0.00, 0.00);
    $arrayTotalesVenta = array(0.00, 0.00, 0.00);
    $arrayTotalesComision = array(0.00, 0.00, 0.00);

    $altura = 58;
    // Maximo por conteo = 36
    // dataVendedores se forma arriba en el if filtroVendedores
    foreach($dataVendedores as $dataVendedores) {
        if($ultimoVendedor == $dataVendedores->nombreEmpleado) {
            // Continuar iteracion en la misma página
            // Misma pagina
            if($ultimaLinea == $dataVendedores->lineaProducto) {
                // Misma linea
                if($conteo > 36) { // Solo caben 18 filas en cada página
                    $drawEncabezados = "Linea";
                    $pdf->AddPage();
                    $conteo = 1;
                    $altura = 40;
                } else {
                    $drawEncabezados = "";
                }
            } else {
                // Dibujar los totales de la linea anterior, ya se está iterando la nueva pero los totales se mantienen en los array
                // Verificar si cabe en la página
                if(($conteo + 1) > 36) {
                    $pdf->AddPage();
                    $conteo = 1;
                    $altura = 40;
                } else {
                    // Cabe en la pagina
                }

                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetXY(10, $altura);
                $pdf->Cell(195, 6, '', 'B', 0, 'L'); // Celda completa para el margen
                $pdf->SetXY(10, $altura);
                $pdf->Cell(115, 6, utf8_decode('Total Línea: ' . $ultimaLinea), 0, 0, 'L');
                $pdf->Cell(25, 6, '', 0, 0, 'L'); // Celda para Cantidad / Total Venta
                $pdf->Cell(15, 6, '', 0, 0, 'C'); // % Descuento
                $pdf->Cell(20, 6, '', 0, 0, 'C'); // Celda para condición / % pago
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(20, 6, number_format($arrayTotalesComision[0], 2, ".", ","), 0, 0, 'R');

                // Celdas dobles
                $pdf->SetXY(125, $altura);
                $pdf->Cell(25, 3, number_format($arrayTotalesCantidadVenta[0], 2, ".", ","), 0, 0, 'R');
                $pdf->SetXY(125, $altura+3);
                $pdf->Cell(25, 3, number_format($arrayTotalesVenta[0], 2, ".", ","), 0, 0, 'R');

                // Simbolos de dolar
                $pdf->SetXY(125, $altura+3);
                $pdf->Cell(25, 3, utf8_decode('$'), 0, 0, 'L');
                $pdf->SetXY(185, $altura);
                $pdf->Cell(20, 6, utf8_decode('$'), 0, 0, 'L');

                // Dejar un espacio
                $conteo += 1;
                $altura += 6;

                // Es otra linea, validar si hay espacio para dibujar 3 conteos más (espacio, linea, 1 valor)
                if(($conteo + 3) > 36) {
                    // La tabla de la próxima línea no cabe, hacer salto
                    $drawEncabezados = "Linea";
                    $pdf->AddPage();
                    $conteo = 1;
                    $altura = 40;
                } else {   
                    // Todavía cabe en la página
                    $drawEncabezados = "Linea";
                    $altura += 6; // Un espacio en blanco
                }
                $conteoXLinea = 1;
            }
        } else {
            if($n > 0) {
                // Dibujar los totales anteriores
                // Dibujar los totales de la linea ya que no cae en el if de arriba
                // Dibujar los totales de la linea anterior, ya se está iterando la nueva pero los totales se mantienen en los array
                // Verificar si cabe en la página
                if(($conteo + 1) > 36) {
                    $pdf->AddPage();
                    $conteo = 1;
                    $altura = 40;
                } else {
                    // Cabe en la pagina
                }

                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetXY(10, $altura);
                $pdf->Cell(195, 6, '', 'B', 0, 'L'); // Celda completa para el margen
                $pdf->SetXY(10, $altura);
                $pdf->Cell(115, 6, utf8_decode('Total Línea: ' . $ultimaLinea), 0, 0, 'L');
                $pdf->Cell(25, 6, '', 0, 0, 'L'); // Celda para Cantidad / Total Venta
                $pdf->Cell(15, 6, '', 0, 0, 'C'); // % Descuento
                $pdf->Cell(20, 6, '', 0, 0, 'C'); // Celda para condición / % pago
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(20, 6, number_format($arrayTotalesComision[0], 2, ".", ","), 0, 0, 'R');

                // Celdas dobles
                $pdf->SetXY(125, $altura);
                $pdf->Cell(25, 3, number_format($arrayTotalesCantidadVenta[0], 2, ".", ","), 0, 0, 'R');
                $pdf->SetXY(125, $altura+3);
                $pdf->Cell(25, 3, number_format($arrayTotalesVenta[0], 2, ".", ","), 0, 0, 'R');

                // Simbolos de dolar
                $pdf->SetXY(125, $altura+3);
                $pdf->Cell(25, 3, utf8_decode('$'), 0, 0, 'L');
                $pdf->SetXY(185, $altura);
                $pdf->Cell(20, 6, utf8_decode('$'), 0, 0, 'L');

                // Dejar un espacio
                $conteo += 2;
                $altura += 12;

                // Dibujar los totales del vendedor
                if(($conteo + 1) > 36) {
                    $pdf->AddPage();
                    $conteo = 1;
                    $altura = 40;
                } else {

                }

                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetXY(10, $altura);
                $pdf->Cell(195, 6, '', 'B', 0, 'L'); // Celda completa para el margen
                $pdf->SetXY(10, $altura);
                $pdf->Cell(115, 6, utf8_decode('Total Vendedor: ' . $ultimoVendedor), 0, 0, 'L');
                $pdf->Cell(25, 6, '', 0, 0, 'L'); // Celda para Cantidad / Total Venta
                $pdf->Cell(15, 6, '', 0, 0, 'C'); // % Descuento
                $pdf->Cell(20, 6, '', 0, 0, 'C'); // Celda para condición / % pago
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(20, 6, number_format($arrayTotalesComision[1], 2, ".", ","), 0, 0, 'R');

                // Celdas dobles
                $pdf->SetXY(125, $altura);
                $pdf->Cell(25, 3, number_format($arrayTotalesCantidadVenta[1], 2, ".", ","), 0, 0, 'R');
                $pdf->SetXY(125, $altura+3);
                $pdf->Cell(25, 3, number_format($arrayTotalesVenta[1], 2, ".", ","), 0, 0, 'R');

                // Simbolos de dolar
                $pdf->SetXY(125, $altura+3);
                $pdf->Cell(25, 3, utf8_decode('$'), 0, 0, 'L');
                $pdf->SetXY(185, $altura);
                $pdf->Cell(20, 6, utf8_decode('$'), 0, 0, 'L');
            } else {
                // Es la primer iteracion
            }
            // Ya se está iterando otro vendedor
            $drawEncabezados = "Completo";
            $conteoXLinea = 1;
        }

        if($drawEncabezados == "Completo") {
            $conteo = 1;
            $altura = 40;
            $pdf->AddPage();
            // Ancho 195 ya con margen incluido
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(195, 6, utf8_decode('Vendedor: ' . $dataVendedores->nombreEmpleado), 0, 0, 'C');
            $altura += 6;
            $pdf->SetXY(10, $altura);
            $pdf->Cell(195, 6, utf8_decode('Línea: ' . $dataVendedores->lineaProducto), 0, 0, 'L');
            $altura += 6;
            // Columnas de la tabla
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(15, 6, '', 0, 0, 'C'); // Celda para Tipo de Documento / N° Factura
            $pdf->Cell(15, 6, utf8_decode('Fecha F.'), 0, 0, 'C');
            $pdf->Cell(35, 6, utf8_decode('Cliente'), 0, 0, 'C');
            $pdf->Cell(25, 6, utf8_decode('Producto'), 0, 0, 'C');
            $pdf->Cell(25, 6, '', 0, 0, 'C'); // Celda para precio unitario / precio venta
            $pdf->Cell(25, 6, '', 0, 0, 'C'); // Celda para cantidad / total venta
            $pdf->Cell(15, 6, utf8_decode('% Desc.'), 0, 0, 'C');
            $pdf->Cell(20, 6, '', 0, 0, 'C'); // Celda para condición / % pago
            $pdf->Cell(20, 6, utf8_decode('Comisión'), 0, 0, 'C');
            // 195 exactos
            // Espacios personalizados para hacer las dobles columnas
            $pdf->SetXY(10, $altura);
            $pdf->Cell(15, 3, utf8_decode('Documento'), 0, 0, 'C');
            $pdf->SetXY(10,$altura+3);
            $pdf->Cell(15, 3, utf8_decode('N° Factura'), 0, 0, 'C');
            $pdf->SetXY(100, $altura);
            $pdf->Cell(25, 3, utf8_decode('Precio Unitario'), 0, 0, 'C');
            $pdf->SetXY(100,$altura+3);
            $pdf->Cell(25, 3, utf8_decode('Precio de Venta'), 0, 0, 'C');
            $pdf->SetXY(125, $altura);
            $pdf->Cell(25, 3, utf8_decode('Cantidad'), 0, 0, 'C');
            $pdf->SetXY(125,$altura+3);
            $pdf->Cell(25, 3, utf8_decode('Total Venta'), 0, 0, 'C');
            $pdf->SetXY(165, $altura);
            $pdf->Cell(20, 3, utf8_decode('Condición'), 0, 0, 'C');
            $pdf->SetXY(165,$altura+3);
            $pdf->Cell(20, 3, utf8_decode('% Pagar'), 0, 0, 'C');

            // Solo el margen de abajo
            $pdf->SetXY(10, $altura);
            $pdf->Cell(195, 6, utf8_decode(''), 'B', 0, 'L');

            $conteo += 3;
            $altura += 6;
        } else if($drawEncabezados == "Linea") {
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(195, 6, utf8_decode('Línea: ' . $dataVendedores->lineaProducto), 0, 0, 'L');
            $altura += 6;
            // Columnas de la tabla
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(15, 6, '', 0, 0, 'C'); // Celda para Tipo de Documento / N° Factura
            $pdf->Cell(15, 6, utf8_decode('Fecha F.'), 0, 0, 'C');
            $pdf->Cell(35, 6, utf8_decode('Cliente'), 0, 0, 'C');
            $pdf->Cell(25, 6, utf8_decode('Producto'), 0, 0, 'C');
            $pdf->Cell(25, 6, '', 0, 0, 'C'); // Celda para precio unitario / precio venta
            $pdf->Cell(25, 6, '', 0, 0, 'C'); // Celda para cantidad / total venta
            $pdf->Cell(15, 6, utf8_decode('% Desc.'), 0, 0, 'C');
            $pdf->Cell(20, 6, '', 0, 0, 'C'); // Celda para condición / % pago
            $pdf->Cell(20, 6, utf8_decode('Comisión'), 0, 0, 'C');
            // 195 exactos
            // Espacios personalizados para hacer las dobles columnas
            $pdf->SetXY(10, $altura);
            $pdf->Cell(15, 3, utf8_decode('Documento'), 0, 0, 'C');
            $pdf->SetXY(10,$altura+3);
            $pdf->Cell(15, 3, utf8_decode('N° Factura'), 0, 0, 'C');
            $pdf->SetXY(100, $altura);
            $pdf->Cell(25, 3, utf8_decode('Precio Unitario'), 0, 0, 'C');
            $pdf->SetXY(100,$altura+3);
            $pdf->Cell(25, 3, utf8_decode('Precio de Venta'), 0, 0, 'C');
            $pdf->SetXY(125, $altura);
            $pdf->Cell(25, 3, utf8_decode('Cantidad'), 0, 0, 'C');
            $pdf->SetXY(125,$altura+3);
            $pdf->Cell(25, 3, utf8_decode('Total Venta'), 0, 0, 'C');
            $pdf->SetXY(165, $altura);
            $pdf->Cell(20, 3, utf8_decode('Condición'), 0, 0, 'C');
            $pdf->SetXY(165,$altura+3);
            $pdf->Cell(20, 3, utf8_decode('% Pagar'), 0, 0, 'C');

            // Solo el margen de abajo
            $pdf->SetXY(10, $altura);
            $pdf->Cell(195, 6, utf8_decode(''), 'B', 0, 'L');

            $conteo += 3;
            $altura += 6;
        } else {
            // No dibujar encabezados
        }

        $tipoFactura = checkTipoDocumento($dataVendedores->codTipoFactura, 0);
        $flgTipoCalculo = checkTipoDocumento($dataVendedores->codTipoFactura, 1);  

        if($flgTipoCalculo == "IVA") { // CONSUMIDOR FINAL
            // Quitar el IVA
            $precioUnitario = round(($dataVendedores->precioUnitario / $parametrizacionIVA), 2);
            //$precioUnitario = $calculoPrecioUnitario;
            $precioFacturado = round(($dataVendedores->precioFacturado / $parametrizacionIVA), 2);
            $totalVenta = round(($dataVendedores->totalVenta / $parametrizacionIVA), 2);
        } else if($flgTipoCalculo == "NIVA") { // CRÉDITO FISCAL, EXPORTACIÓN, EXENTA
            // Los precios ya vienen sin iva
            $precioUnitario = $dataVendedores->precioUnitario;
            $totalVenta = $dataVendedores->totalVenta;
            $precioFacturado = $dataVendedores->precioFacturado;
        } else { 
            // N/A = Otro tipo de documento no especificado
            $precioUnitario = 0.00;
            $precioFacturado = 0.00;
            $totalVenta = 0.00;
        }
        $porcentajeDescuento = number_format($dataVendedores->porcentajeDescuento, 2, ".", ",");
        $condicionInicio = number_format($dataVendedores->paramRangoPorcentajeInicio, 2, ".", ",");
        $condicionFin = number_format($dataVendedores->paramRangoPorcentajeFin, 2, ".", ",");
        $porcentajePago = number_format($dataVendedores->paramPorcentajePago, 2, ".", ",");

        if($dataVendedores->flgComisionEditar == 0) {
            $comisionPagar = $dataVendedores->comisionPagar;
        } else {
            $comisionPagar = $dataVendedores->comisionPagarEditar;
        }

        // Data general
        // Columnas de la tabla
        $pdf->SetFont('Arial', '', 5);
        $pdf->SetXY(5, $altura);
        $pdf->Cell(5, 3, $conteoXLinea, 0, 0, 'R'); 
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(15, 6, '', 0, 0, 'C'); // Celda para Tipo de Documento / N° Factura
        $pdf->Cell(15, 6, utf8_decode(date("d/m/Y", strtotime($dataVendedores->fechaFactura))), 0, 0, 'C');
        $pdf->Cell(35, 6, utf8_decode($dataVendedores->nombreCliente), 0, 0, 'L');
        $pdf->Cell(25, 6, utf8_decode($dataVendedores->codProductoFactura), 0, 0, 'L');
        $pdf->Cell(25, 6, '', 0, 0, 'C'); // Celda para precio unitario / precio venta
        $pdf->Cell(25, 6, '', 0, 0, 'C'); // Celda para cantidad / total venta
        $pdf->Cell(15, 6, utf8_decode($porcentajeDescuento . '%'), 0, 0, 'R');
        $pdf->Cell(20, 6, '', 0, 0, 'C'); // Celda para condición / % pago
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(20, 6, number_format($comisionPagar, 2, ".", ","), 0, 0, 'R');
        // 195 exactos
        
        // Espacios personalizados para hacer las dobles columnas
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(10, $altura);
        $pdf->Cell(15, 3, utf8_decode($tipoFactura), 0, 0, 'C');
        $pdf->SetXY(10,$altura+3);
        $pdf->Cell(15, 3, utf8_decode($dataVendedores->correlativoFactura), 0, 0, 'R');
        $pdf->SetXY(100, $altura);
        $pdf->Cell(25, 3, number_format($precioUnitario, 2, ".", ","), 0, 0, 'R');
        $pdf->SetXY(100,$altura+3);
        $pdf->Cell(25, 3, number_format($precioFacturado, 2, ".", ","), 0, 0, 'R');
        $pdf->SetXY(125, $altura);
        $pdf->Cell(25, 3, number_format($dataVendedores->cantidadProducto, 2, ".", ","), 0, 0, 'R');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(125,$altura+3);
        $pdf->Cell(25, 3, number_format($totalVenta, 2, ".", ","), 0, 0, 'R');
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(165, $altura);
        $pdf->Cell(20, 3, utf8_decode($condicionInicio . '% - ' . $condicionFin . '%'), 0, 0, 'C');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(165,$altura+3);
        $pdf->Cell(20, 3, utf8_decode($porcentajePago . '%'), 0, 0, 'C');

        $pdf->SetFont('Arial', '', 7);
        // Simbolos de dolar
        $pdf->SetXY(100, $altura);
        $pdf->Cell(25, 3, utf8_decode('$'), 0, 0, 'L');
        $pdf->SetXY(100,$altura+3);
        $pdf->Cell(25, 3, utf8_decode('$'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(125,$altura+3);
        $pdf->Cell(25, 3, utf8_decode('$'), 0, 0, 'L');
        $pdf->SetXY(185, $altura);
        $pdf->Cell(20, 6, utf8_decode('$'), 0, 0, 'L');

        // Solo el margen de abajo
        $pdf->SetXY(10, $altura);
        $pdf->Cell(195, 6, utf8_decode(''), 'B', 0, 'L');

        // Sumar totales
        if($dataVendedores->lineaProducto == $ultimaLinea) {
            $arrayTotalesCantidadVenta[0] += $dataVendedores->cantidadProducto; // Por linea - Se reinicia al cambiar de linea
            $arrayTotalesVenta[0] += $totalVenta; // Por linea - Se reinicia al cambiar de linea
            $arrayTotalesComision[0] += $comisionPagar; // Por linea - Se reinicia al cambiar de linea
        } else {
            $arrayTotalesCantidadVenta[0] = $dataVendedores->cantidadProducto; // Por linea - Se reinicia al cambiar de linea
            $arrayTotalesVenta[0] = $totalVenta; // Por linea - Se reinicia al cambiar de linea
            $arrayTotalesComision[0] = $comisionPagar; // Por linea - Se reinicia al cambiar de linea
        }

        if($dataVendedores->nombreEmpleado == $ultimoVendedor) {
            $arrayTotalesCantidadVenta[1] += $dataVendedores->cantidadProducto; // Por vendedor - Se reinicia al cambiar de vendedor
            $arrayTotalesVenta[1] += $totalVenta; // Por vendedor - Se reinicia al cambiar de vendedor
            $arrayTotalesComision[1] += $comisionPagar; // Por vendedor - Se reinicia al cambiar de vendedor
        } else {
            $arrayTotalesCantidadVenta[1] = $dataVendedores->cantidadProducto; // Por vendedor - Se reinicia al cambiar de vendedor
            $arrayTotalesVenta[1] = $totalVenta; // Por vendedor - Se reinicia al cambiar de vendedor
            $arrayTotalesComision[1] = $comisionPagar; // Por vendedor - Se reinicia al cambiar de vendedor
        }
        
        $arrayTotalesCantidadVenta[2] += $dataVendedores->cantidadProducto; // Total General
        $arrayTotalesVenta[2] += $totalVenta; // Total General        
        $arrayTotalesComision[2] += $comisionPagar; // Total General

        // Variables para continuar reporte
        $ultimaLinea = $dataVendedores->lineaProducto;
        $ultimoVendedor = $dataVendedores->nombreEmpleado;
        $altura += 6;
        $n += 1;
        $conteo += 1;
        $conteoXLinea += 1;
    }
    // Termina de iterar pero ya no dibuja los totales por línea y vendedor del último, así que los agrego acá
    // Dibujar los totales anteriores
    // Dibujar los totales de la linea ya que no cae en el if de arriba
    // Dibujar los totales de la linea anterior, ya se está iterando la nueva pero los totales se mantienen en los array
    // Verificar si cabe en la página
    if(($conteo + 1) > 36) {
        $pdf->AddPage();
        $conteo = 1;
        $altura = 40;
    } else {
        // Cabe en la pagina
    }

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(195, 6, '', 'B', 0, 'L'); // Celda completa para el margen
    $pdf->SetXY(10, $altura);
    $pdf->Cell(115, 6, utf8_decode('Total Línea: ' . $ultimaLinea), 0, 0, 'L');
    $pdf->Cell(25, 6, '', 0, 0, 'L'); // Celda para Cantidad / Total Venta
    $pdf->Cell(15, 6, '', 0, 0, 'C'); // % Descuento
    $pdf->Cell(20, 6, '', 0, 0, 'C'); // Celda para condición / % pago
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 6, number_format($arrayTotalesComision[0], 2, ".", ","), 0, 0, 'R');

    // Celdas dobles
    $pdf->SetXY(125, $altura);
    $pdf->Cell(25, 3, number_format($arrayTotalesCantidadVenta[0], 2, ".", ","), 0, 0, 'R');
    $pdf->SetXY(125, $altura+3);
    $pdf->Cell(25, 3, number_format($arrayTotalesVenta[0], 2, ".", ","), 0, 0, 'R');

    // Simbolos de dolar
    $pdf->SetXY(125, $altura+3);
    $pdf->Cell(25, 3, utf8_decode('$'), 0, 0, 'L');
    $pdf->SetXY(185, $altura);
    $pdf->Cell(20, 6, utf8_decode('$'), 0, 0, 'L');

    // Dejar un espacio
    $conteo += 2;
    $altura += 12;

    // Dibujar los totales del vendedor
    if(($conteo + 1) > 36) {
        $pdf->AddPage();
        $conteo = 1;
        $altura = 40;
    } else {
        // Cabe en la página
    }

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(195, 6, '', 'B', 0, 'L'); // Celda completa para el margen
    $pdf->SetXY(10, $altura);
    $pdf->Cell(115, 6, utf8_decode('Total Vendedor: ' . $ultimoVendedor), 0, 0, 'L');
    $pdf->Cell(25, 6, '', 0, 0, 'L'); // Celda para Cantidad / Total Venta
    $pdf->Cell(15, 6, '', 0, 0, 'C'); // % Descuento
    $pdf->Cell(20, 6, '', 0, 0, 'C'); // Celda para condición / % pago
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 6, number_format($arrayTotalesComision[1], 2, ".", ","), 0, 0, 'R');

    // Celdas dobles
    $pdf->SetXY(125, $altura);
    $pdf->Cell(25, 3, number_format($arrayTotalesCantidadVenta[1], 2, ".", ","), 0, 0, 'R');
    $pdf->SetXY(125, $altura+3);
    $pdf->Cell(25, 3, number_format($arrayTotalesVenta[1], 2, ".", ","), 0, 0, 'R');

    // Simbolos de dolar
    $pdf->SetXY(125, $altura+3);
    $pdf->Cell(25, 3, utf8_decode('$'), 0, 0, 'L');
    $pdf->SetXY(185, $altura);
    $pdf->Cell(20, 6, utf8_decode('$'), 0, 0, 'L');

    // Total general
    $conteo = 1;
    $altura = 40;
    $pdf->AddPage();
    
    // Ancho 195 ya con margen incluido
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(195, 6, utf8_decode('Totales'), 0, 0, 'C');
    $altura += 6;

    // Cantidad, Venta, Comisión
    $arrayTotalesPorLinea = array(0.00, 0.00, 0.00);
    $ultimaLinea = ''; $ultimaFactura = ''; $n = 0;
    foreach ($dataTotalGeneralPorLinea as $dataTotalGeneralPorLinea) {
        if($dataTotalGeneralPorLinea->flgComisionEditar == 0) {
            $comisionPagar = $dataTotalGeneralPorLinea->comisionPagar;
        } else {
            $comisionPagar = $dataTotalGeneralPorLinea->comisionPagarEditar;
        }

        if($ultimaLinea == $dataTotalGeneralPorLinea->lineaProducto || $n == 0) {

            $tipoFactura = checkTipoDocumento($dataTotalGeneralPorLinea->codTipoFactura, 0);
            $flgTipoCalculo = checkTipoDocumento($dataTotalGeneralPorLinea->codTipoFactura, 1);  

            $arrayTotalesPorLinea[0] += $dataTotalGeneralPorLinea->cantidadProducto;
            if($flgTipoCalculo == "IVA") { // CONSUMIDOR FINAL
                // Quitar el IVA
                $arrayTotalesPorLinea[1] += round(($dataTotalGeneralPorLinea->totalVenta / $parametrizacionIVA), 2);
            } else if($flgTipoCalculo == "NIVA") { // CRÉDITO FISCAL, EXPORTACIÓN, EXENTA
                // Los precios ya vienen sin iva
                $arrayTotalesPorLinea[1] += $dataTotalGeneralPorLinea->totalVenta;
            } else { 
                // N/A = Otro tipo de documento no especificado
                $arrayTotalesPorLinea[1] += 0.00;
            }

            $arrayTotalesPorLinea[2] += $comisionPagar;

        } else {
            if($conteo > 35) {
                // Total general
                $conteo = 1;
                $altura = 40;
                $pdf->AddPage();
                
                // Ancho 195 ya con margen incluido
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetXY(10, $altura);
                $pdf->Cell(195, 6, utf8_decode('Totales'), 0, 0, 'C');
                $altura += 6;
            } else {
                // Cabe en la página
            }
            // Dibujar los últimos totales y reiniciar variables
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(195, 6, '', 'B', 0, 'L'); // Celda completa para el margen
            $pdf->SetXY(10, $altura);
            $pdf->Cell(115, 6, utf8_decode('Total por Línea: ' . $ultimaLinea), 0, 0, 'L');
            $pdf->Cell(25, 6, '', 0, 0, 'L'); // Celda para Cantidad / Total Venta
            $pdf->Cell(15, 6, '', 0, 0, 'C'); // % Descuento
            $pdf->Cell(20, 6, '', 0, 0, 'C'); // Celda para condición / % pago
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(20, 6, number_format($arrayTotalesPorLinea[2], 2, ".", ","), 0, 0, 'R'); // Comision

            // Celdas dobles
            $pdf->SetXY(125, $altura);
            $pdf->Cell(25, 3, number_format($arrayTotalesPorLinea[0], 2, ".", ","), 0, 0, 'R'); // Cantidad
            $pdf->SetXY(125, $altura+3);
            $pdf->Cell(25, 3, number_format($arrayTotalesPorLinea[1], 2, ".", ","), 0, 0, 'R'); // Venta

            // Simbolos de dolar
            $pdf->SetXY(125, $altura+3);
            $pdf->Cell(25, 3, utf8_decode('$'), 0, 0, 'L');
            $pdf->SetXY(185, $altura);
            $pdf->Cell(20, 6, utf8_decode('$'), 0, 0, 'L');

            $altura += 6;
            $conteo += 1;
            // Reiniciar variables con las nuevas de la nueva linea que se esta iterando   
            $tipoFactura = checkTipoDocumento($dataTotalGeneralPorLinea->codTipoFactura, 0);
            $flgTipoCalculo = checkTipoDocumento($dataTotalGeneralPorLinea->codTipoFactura, 1);         

            $arrayTotalesPorLinea[0] = $dataTotalGeneralPorLinea->cantidadProducto;
            if($flgTipoCalculo == "IVA") { // CONSUMIDOR FINAL
                // Quitar el IVA
                $arrayTotalesPorLinea[1] = round(($dataTotalGeneralPorLinea->totalVenta / $parametrizacionIVA), 2);
            } else if($flgTipoCalculo == "NIVA") { // CRÉDITO FISCAL, EXPORTACIÓN, EXENTA
                // Los precios ya vienen sin iva
                $arrayTotalesPorLinea[1] = $dataTotalGeneralPorLinea->totalVenta;
            } else { 
                // N/A = Otro tipo de documento no especificado
                $arrayTotalesPorLinea[1] = 0.00;
            }
            $arrayTotalesPorLinea[2] = $comisionPagar;
        }
        $ultimaLinea = $dataTotalGeneralPorLinea->lineaProducto;
        $ultimaFactura = $dataTotalGeneralPorLinea->correlativoFactura;
        $n += 1;
    }

    // Dibujar la ultima linea que no se itera
    if($conteo > 35) {
        // Total general
        $conteo = 1;
        $altura = 40;
        $pdf->AddPage();
        
        // Ancho 195 ya con margen incluido
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY(10, $altura);
        $pdf->Cell(195, 6, utf8_decode('Totales'), 0, 0, 'C');  
        $altura += 6;              
    } else {
        // Cabe en la página
    }
    // Dibujar los últimos totales y reiniciar variables
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(195, 6, '', 'B', 0, 'L'); // Celda completa para el margen
    $pdf->SetXY(10, $altura);
    $pdf->Cell(115, 6, utf8_decode('Total por Línea: ' . $ultimaLinea), 0, 0, 'L');
    $pdf->Cell(25, 6, '', 0, 0, 'L'); // Celda para Cantidad / Total Venta
    $pdf->Cell(15, 6, '', 0, 0, 'C'); // % Descuento
    $pdf->Cell(20, 6, '', 0, 0, 'C'); // Celda para condición / % pago
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(20, 6, number_format($arrayTotalesPorLinea[2], 2, ".", ","), 0, 0, 'R'); // Comision

    // Celdas dobles
    $pdf->SetXY(125, $altura);
    $pdf->Cell(25, 3, number_format($arrayTotalesPorLinea[0], 2, ".", ","), 0, 0, 'R'); // Cantidad
    $pdf->SetXY(125, $altura+3);
    $pdf->Cell(25, 3, number_format($arrayTotalesPorLinea[1], 2, ".", ","), 0, 0, 'R'); // Venta

    // Simbolos de dolar
    $pdf->SetXY(125, $altura+3);
    $pdf->Cell(25, 3, utf8_decode('$'), 0, 0, 'L');
    $pdf->SetXY(185, $altura);
    $pdf->Cell(20, 6, utf8_decode('$'), 0, 0, 'L');
    $altura += 12;
    $conteo += 1;

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(195, 6, '', 'B', 0, 'L'); // Celda completa para el margen
    $pdf->SetXY(10, $altura);
    $pdf->Cell(115, 6, utf8_decode('Total General'), 0, 0, 'L');
    $pdf->Cell(25, 6, '', 0, 0, 'L'); // Celda para Cantidad / Total Venta
    $pdf->Cell(15, 6, '', 0, 0, 'C'); // % Descuento
    $pdf->Cell(20, 6, '', 0, 0, 'C'); // Celda para condición / % pago
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 6, number_format($arrayTotalesComision[2], 2, ".", ","), 0, 0, 'R');

    // Celdas dobles
    $pdf->SetXY(125, $altura);
    $pdf->Cell(25, 3, number_format($arrayTotalesCantidadVenta[2], 2, ".", ","), 0, 0, 'R');
    $pdf->SetXY(125, $altura+3);
    $pdf->Cell(25, 3, number_format($arrayTotalesVenta[2], 2, ".", ","), 0, 0, 'R');

    // Simbolos de dolar
    $pdf->SetXY(125, $altura+3);
    $pdf->Cell(25, 3, utf8_decode('$'), 0, 0, 'L');
    $pdf->SetXY(185, $altura);
    $pdf->Cell(20, 6, utf8_decode('$'), 0, 0, 'L');

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