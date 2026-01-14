<?php 
    ini_set('memory_limit', '-1');
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    require_once('../../../../../libraries/packages/php/vendor/fpdf/fpdf.php');

    class PDF extends FPDF {
        // Page header
        function Header() {
            global $tituloReporte;
            global $subtituloReporte;
            // url, X, Y, Weight
            $this->Image('../../../../../libraries/resources/images/logos/indupal-logo.png', 15, 8, 40);

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
            $this->SetXY(160, 8);
            $this->Cell(40, 4, date("d-m-Y H:i:s"), 0, 0, 'R');
            $this->SetXY(160, 12);
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
        filtroEmpleados (radio)
        filtroClasificacion (radio)
        flgClasificacion (switch)
        selectEmpleados (multiple)
        selectClasificacion (multiple)
    */

    $filtroEmpleados = base64_decode(urldecode($_REQUEST['filtroEmpleados']));
    $arrayExpedientesId = (isset($_REQUEST['selectEmpleados']) ? base64_decode(urldecode($_REQUEST['selectEmpleados'])) : '');
    $flgClasificacion = (isset($_REQUEST['flgClasificacion']) ? 'Sí' : 'No');
    $filtroClasificacion = base64_decode(urldecode($_REQUEST['filtroClasificacion']));
    $arrayClasificacionId = (isset($_REQUEST['selectClasificacion']) ? base64_decode(urldecode($_REQUEST['selectClasificacion'])) : '');

    $tituloReporte = 'Salarios de Empleados';
    $subtituloReporte = "";
    
    $outputReporte = 'Salarios de Empleados (' . $filtroEmpleados . ')';

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    $pdf->SetTitle($outputReporte);
    
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $x = 15;
    $altura = 30;
    $celdas = 5;
    /*
    $pdf->SetXY($x, $altura);
    // Ancho total con margenes incluidos = 185
    // ancho de la celda, alto de la celda, texto a mostrar, borde (1 completo, L, R, T, B), relleno, alineación (L, C, R)
    $pdf->Cell(37, $celdas, utf8_decode('Celda 1'), 1, 0, 'C');
    $pdf->Cell(37, $celdas, utf8_decode('Celda 1'), 1, 0, 'C');
    $pdf->Cell(37, $celdas, utf8_decode('Celda 1'), 1, 0, 'C');
    $pdf->Cell(37, $celdas, utf8_decode('Celda 1'), 1, 0, 'C');
    $pdf->Cell(37, $celdas, utf8_decode('Celda 1'), 1, 0, 'C');
    $altura += $celdas;

    $pdf->SetXY($x, $altura);
    // ancho de la celda, alto de la celda, texto a mostrar y etiquetas html, borde, relleno, alineación
    $pdf->CellHTML(74, $celdas, utf8_decode('<b>Celda 1 HTML: David Ernesto Rivas Lazo</b>'), 1);
    $altura += $celdas;

    $pdf->SetXY($x, $altura);
    // ancho de la celda, distancia del texto, texto a mostrar, borde, alineación (L,C,R,J)
    $pdf->MultiCell(74, 4, utf8_decode('Celda 1 MultiCell: David Ernesto Rivas Lazo, Analista Programador de Indupal S.A de C.V.'), 1, 'L');
    // GetY se utiliza con MultiCell cuando no sabemos cuántas lineas de texto fueron y desconocemos la última altura
    $altura = $pdf->GetY();

    $pdf->SetXY($x, $altura);
    // Ancho total con margenes incluidos = 185
    // ancho de la celda, alto de la celda, texto a mostrar, borde (1 completo, L, R, T, B), relleno, alineación (L, C, R)
    $pdf->Cell(37, $celdas, utf8_decode('Celda 1'), 1, 0, 'C');
    $pdf->Cell(37, $celdas, utf8_decode('Celda 1'), 1, 0, 'C');
    $pdf->Cell(37, $celdas, utf8_decode('Celda 1'), 1, 0, 'C');
    $pdf->Cell(37, $celdas, utf8_decode('Celda 1'), 1, 0, 'C');
    $pdf->Cell(37, $celdas, utf8_decode('Celda 1'), 1, 0, 'C');    
    $altura += $celdas + 5;
    */

    $pdf->SetXY($x, $altura);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(130, $celdas, utf8_decode('Empleado'), 1, 0, 'C');
    $pdf->Cell(55, $celdas, utf8_decode('Salario'), 1, 0, 'C');    
    $altura += $celdas;

    $pdf->SetFont('Arial', '', 10);
    $corrPagina = 1;
    $totalGeneral = 0; $salario = 0; $totalClasificacion = 0;
    for ($a=1; $a < 5; $a++) { 
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY($x, $altura);
        $pdf->CellHTML(185, $celdas, utf8_decode("<b>Clasificación: </b> $a"), 1, 0, 'C'); 
        $pdf->SetFont('Arial', '', 10);
        $altura += $celdas;

        for ($i=1; $i < 100; $i++) { 
            if($corrPagina == 41) {
                $corrPagina = 1;
                $pdf->AddPage();
                $altura = 30;
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetXY($x, $altura);
                $pdf->Cell(130, $celdas, utf8_decode('Empleado'), 1, 0, 'C');
                $pdf->Cell(55, $celdas, utf8_decode('Salario'), 1, 0, 'C');  
                $altura += $celdas; 
                $pdf->SetFont('Arial', '', 10);
            } else {
                // Todavia cabe otro en la página
            }
            $salario = rand(0, 1000);
            $totalClasificacion += $salario;
            $totalGeneral += $salario;
            $pdf->SetXY($x, $altura);
            $pdf->Cell(130, $celdas, utf8_decode("Empleado $i"), 1, 0, 'L');
            $pdf->Cell(55, $celdas, number_format($salario, 2, '.', ','), 1, 0, 'R'); 
    
            $pdf->SetXY($x + 130, $altura);
            $pdf->Cell(55, $celdas, utf8_decode('$'), 0, 0, 'L');    
            $altura += $celdas;
            $corrPagina++;
        }
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($x, $altura);
        $pdf->Cell(130, $celdas, utf8_decode("Total: Clasificación $a"), 1, 0, 'L');
        $pdf->Cell(55, $celdas, number_format($totalClasificacion, 2, '.', ','), 1, 0, 'R');  
        $pdf->SetXY($x + 130, $altura);
        $pdf->Cell(55, $celdas, utf8_decode('$'), 0, 0, 'L'); 
        $altura += $celdas * 2;
        $totalClasificacion = 0;
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY($x, $altura);
    $pdf->Cell(130, $celdas, utf8_decode('Total general'), 1, 0, 'L');
    $pdf->Cell(55, $celdas, number_format($totalGeneral, 2, '.', ','), 1, 0, 'R');  
    $pdf->SetXY($x + 130, $altura);
    $pdf->Cell(55, $celdas, utf8_decode('$'), 0, 0, 'L'); 

    $pdf->Output($outputReporte . '.pdf', "I");
?>