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
            //$this->Cell(0, 5, utf8_decode(''), 0, 0, 'L');
            // Numeración de página
            $this->Cell(0, 5, utf8_decode('Página '.$this->PageNo().'/{nb}'), 0, 0, 'C');
        }
    }


    /*
        REQUEST:
        filtroLineas
        lineaId = multiple
    */
   
    $filtroLineas = base64_decode(urldecode($_REQUEST['filtroLineas']));

    if($filtroLineas == "Todos") {
        $filtroLineas = "Todas las líneas";
        $dataParametrizacion = $cloud->rows("
            SELECT 
                pl.lineaId AS lineaId,
                CONCAT('(', l.abreviatura, ') ', l.linea) AS nombreLinea,
                pl.rangoPorcentajeInicio AS rangoPorcentajeInicio,
                pl.rangoPorcentajeFin AS rangoPorcentajeFin,
                pl.porcentajePago AS porcentajePago
            FROM conta_comision_porcentaje_lineas pl
            JOIN temp_cat_lineas l ON l.lineaId = pl.lineaId
            WHERE pl.flgDelete = ?
            ORDER BY l.abreviatura, rangoPorcentajeInicio
        ", ['0']);
    } else {
        // Especifico
        $filtroLineas = "Líneas específicas";
        $lineaId = base64_decode(urldecode($_REQUEST['lineaId']));

        $dataParametrizacion = $cloud->rows("
            SELECT 
                pl.lineaId AS lineaId,
                CONCAT('(', l.abreviatura, ') ', l.linea) AS nombreLinea,
                pl.rangoPorcentajeInicio AS rangoPorcentajeInicio,
                pl.rangoPorcentajeFin AS rangoPorcentajeFin,
                pl.porcentajePago AS porcentajePago
            FROM conta_comision_porcentaje_lineas pl
            JOIN temp_cat_lineas l ON l.lineaId = pl.lineaId
            WHERE pl.lineaId IN ($lineaId) AND pl.flgDelete = ?
            ORDER BY l.abreviatura, rangoPorcentajeInicio
        ", ['0']);
    }

    $tituloReporte = 'Parametrización de comisiones';
    $subtituloReporte = $filtroLineas;

    $outputReporte = 'Parametrización de comisiones - ' . $filtroLineas;

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();

    $pdf->SetTitle(utf8_decode($outputReporte));

    $n = 0; $conteo = 1; $flgColumna = 1; $flgDibujar = 0;
    $ultimaLinea = '';

    $arrayDibujar = array();

    $pdf->AddPage();
    $ancho = 10; $altura = 40;
    // Maximo por conteo = 36
    // dataParametrizacion se forma arriba en el if filtroLineas
    foreach($dataParametrizacion as $dataParametrizacion) {
        if($ultimaLinea == $dataParametrizacion->lineaId) {
            // Guardar en un array la parametrización
            $arrayDibujar[] = array($dataParametrizacion->nombreLinea, $dataParametrizacion->rangoPorcentajeInicio, $dataParametrizacion->rangoPorcentajeFin, $dataParametrizacion->porcentajePago);
            $flgDibujar = 1;
        } else {
            // Es otra línea
            if($flgDibujar == 0) {
                // Fue la primer iteración 
                $arrayDibujar[] = array($dataParametrizacion->nombreLinea, $dataParametrizacion->rangoPorcentajeInicio, $dataParametrizacion->rangoPorcentajeFin, $dataParametrizacion->porcentajePago);
            } else {
                // Verificar si hay espacio para dibujar el array anterior
                $numCeldasArray = count($arrayDibujar);
                // + 2 porque lleva el nombre de la línea y los encabezados de la tabla
                if(($conteo + $numCeldasArray + 2) > 54) {
                    $conteo = 1;
                    $altura = 40;
                    if($flgColumna == 1) {
                        $flgColumna = 2;
                        // Mover ancho hacia la posición de la 2da columna
                        $ancho = 115;
                    } else {
                        $flgColumna = 1;
                        $pdf->AddPage();
                        // Regresar el ancho a la columna 1
                        $ancho = 10;
                    }
                } else {
                    // Todavía cabe en la página y columna
                }

                // Dibujar el array
                for ($i=0; $i < count($arrayDibujar); $i++) { 
                    if($i == 0) { // Dibujar el titulo
                        $pdf->SetFont('Arial', 'B', 7);
                        $pdf->SetXY($ancho, $altura);
                        $pdf->Cell(90, 4, utf8_decode($arrayDibujar[$i][0]), 0, 0, 'L');
                        $altura += 4;
                        $conteo += 1;

                        $pdf->SetXY($ancho, $altura);
                        $pdf->Cell(9, 4, utf8_decode('#'), 1, 0, 'C');
                        $pdf->Cell(54, 4, utf8_decode('Condición'), 1, 0, 'C');
                        $pdf->Cell(27, 4, utf8_decode('Porcentaje de pago'), 1, 0, 'C');
                        $altura += 4;
                        $conteo += 1;
                    } else {
                        // Solo dibujar la celda
                    }
                    $rangoPorcentajeInicio = number_format($arrayDibujar[$i][1], 2, ".", ",");
                    $rangoPorcentajeFin = number_format($arrayDibujar[$i][2], 2, ".", ",");
                    $porcentajePago = number_format($arrayDibujar[$i][3], 2, ".", ",");

                    if($rangoPorcentajeInicio == "0.00" || $rangoPorcentajeInicio == 0.00) {
                        $rangoPorcentajeInicio = "Precio de Lista";
                    } else {
                        $rangoPorcentajeInicio .= '%';
                    }

                    $pdf->SetFont('Arial', '', 7);
                    $pdf->SetXY($ancho, $altura);
                    $pdf->Cell(9, 4, number_format(($i + 1), 0, ".", ","), 1, 0, 'C');
                    $pdf->Cell(54, 4, utf8_decode($rangoPorcentajeInicio . ' - ' . $rangoPorcentajeFin . '% de descuento'), 1, 0, 'C');
                    $pdf->Cell(27, 4, utf8_decode($porcentajePago . '% sobre venta'), 1, 0, 'C');
                    $altura += 4;
                    $conteo += 1;
                }

                // Simular que se deja un espacio de por medio
                $altura += 4;
                $conteo += 1;

                // Reiniciar el array
                unset($arrayDibujar);
                $arrayDibujar = array(); 

                // Setear los nuevos valores que se están iterando
                $arrayDibujar[] = array($dataParametrizacion->nombreLinea, $dataParametrizacion->rangoPorcentajeInicio, $dataParametrizacion->rangoPorcentajeFin, $dataParametrizacion->porcentajePago);
            }
        }

        $ultimaLinea = $dataParametrizacion->lineaId;
        $n += 1;
    }

    // La última línea no se muestra ya que el bucle termina, dibujarla acá
    // Verificar si hay espacio para dibujar el array anterior
    $numCeldasArray = count($arrayDibujar);
    // + 2 porque lleva el nombre de la línea y los encabezados de la tabla
    if(($conteo + $numCeldasArray + 2) > 54) {
        $conteo = 1;
        $altura = 40;
        if($flgColumna == 1) {
            $flgColumna = 2;
            // Mover ancho hacia la posición de la 2da columna
            $ancho = 115;
        } else {
            $flgColumna = 1;
            $pdf->AddPage();
            // Regresar el ancho a la columna 1
            $ancho = 10;
        }
    } else {
        // Todavía cabe en la página y columna
    }

    // Dibujar el array
    for ($i=0; $i < count($arrayDibujar); $i++) { 
        if($i == 0) { // Dibujar el titulo
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetXY($ancho, $altura);
            $pdf->Cell(90, 4, utf8_decode($arrayDibujar[$i][0]), 0, 0, 'L');
            $altura += 4;
            $conteo += 1;

            $pdf->SetXY($ancho, $altura);
            $pdf->Cell(9, 4, utf8_decode('#'), 1, 0, 'C');
            $pdf->Cell(54, 4, utf8_decode('Condición'), 1, 0, 'C');
            $pdf->Cell(27, 4, utf8_decode('Porcentaje de pago'), 1, 0, 'C');
            $altura += 4;
            $conteo += 1;
        } else {
            // Solo dibujar la celda
        }
        $rangoPorcentajeInicio = number_format($arrayDibujar[$i][1], 2, ".", ",");
        $rangoPorcentajeFin = number_format($arrayDibujar[$i][2], 2, ".", ",");
        $porcentajePago = number_format($arrayDibujar[$i][3], 2, ".", ",");

        if($rangoPorcentajeInicio == "0.00" || $rangoPorcentajeInicio == 0.00) {
            $rangoPorcentajeInicio = "Precio de Lista";
        } else {
            $rangoPorcentajeInicio .= '%';
        }

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY($ancho, $altura);
        $pdf->Cell(9, 4, number_format(($i + 1), 0, ".", ","), 1, 0, 'C');
        $pdf->Cell(54, 4, utf8_decode($rangoPorcentajeInicio . ' - ' . $rangoPorcentajeFin . '% de descuento'), 1, 0, 'C');
        $pdf->Cell(27, 4, utf8_decode($porcentajePago . '% sobre venta'), 1, 0, 'C');
        $altura += 4;
        $conteo += 1;
    }

    $pdf->Output(utf8_decode($outputReporte) . '.pdf', "I");
?>