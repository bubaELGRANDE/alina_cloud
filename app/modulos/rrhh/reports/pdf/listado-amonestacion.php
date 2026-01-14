<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    require_once('../../../../../libraries/packages/php/vendor/fpdf/fpdf.php');

    class PDF extends FPDF {
        // Page header
        function Header() {
            global $tituloReporte;
            global $subtituloReporte;
            // url, X, Y, Weight
            $this->Image('../../../../../libraries/resources/images/logos/alina-logo.png', 10, 8, 40);

            $this->SetFont('Arial', 'B', 11);
            // Existe 216 de ancho en X
            $this->SetXY(54, 8);
            $this->Cell(114, 5, utf8_decode('Industrial La Palma S.A de C.V.'), 0, 0, 'C');
            $this->SetXY(54, 13);
            $this->Cell(114, 5, utf8_decode('Departamento de Recursos Humanos'), 0, 0, 'C');
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
            // Sin paginación porque son fichas independientes por empleado
            $this->Cell(0, 5, utf8_decode('Página '.$this->PageNo().'/{nb}'), 0, 0, 'C');
        }

        function encabezadosTabla($x = 15, $y = 35) {
            // 0 curso, 1 organizador, 2, modalidad, 3 tipo formacion, 4 fecha inicio, 5 fecha fin, 6 duracion
            global $txtEncabezado;
            $this->SetFont('Arial', 'B', 10);
            // Segun la pagina, establecer X y Y aca            
            $this->SetXY($x, $y);
            $this->CellHTML(186, 5, utf8_decode($txtEncabezado), 0, 0, "L");
            //$this->SetWidths(array(186));
            //$this->Row(array($x, $y, 5), array($txtEncabezado), array("", "No"), array("L"));
            $y += 5;
            $this->SetXY($x, $y);
            $this->SetFont('Arial', 'B', 9);
            $this->SetWidths(array(10, 50, 20,20));
            $this->Row(array($x, $y, 5), array("#", "Empleado", "Fecha", "Causa"), array(1, "No"));

            /*
            En este reporte no aplica, ya que son diferentes tablas y se debe manejar manualmente las alturas
            // Esto es para los registros de la tabla
            $this->SetXY(15, 70);
            $this->SetWidths(array(10, 54, 25, 25, 20, 26, 26));
            $this->SetFont('Arial', '', 7);
            */
        }
    }

    //Listado de amonestación de reportes 


    $anio = base64_decode(urldecode($_REQUEST['filtroAnio']));
    $expedienteAmonestacionId = base64_decode(urldecode($_REQUEST['amonestacionReporte']));
    $fechaInicio = base64_decode(urldecode($_REQUEST['fechaInicio']));
    $fechaFin = base64_decode(urldecode($_REQUEST['fechaFin']));
    $estadoAmonestacion = base64_decode(urldecode($_REQUEST['estadoAmonestacion']));

    $tituloReporte = 'Listado de amonestaciones';
    $subtituloReporte = "Del: " . date("d/m/Y", strtotime($fechaInicio)) . " al: " . date("d/m/Y", strtotime($fechaFin));
    
    $outputReporte = 'Listado de amonestaciones - Del ' . date("d-m-Y", strtotime($fechaInicio)) . " al " . date("d-m-Y", strtotime($fechaFin));

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    $pdf->SetTitle(utf8_decode($outputReporte));
    $pdf->AddPage();
    $x = 15;
    $altura = 35;

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, 40);
    $pdf->Cell(10, 5, utf8_decode("N°"), 'TB', 1, 'C');
    $pdf->SetXY(20, 40);
    $pdf->Cell(80, 5, utf8_decode("Empleado"), 'TB', 1, 'C');
    $pdf->SetXY(100, 40);
    $pdf->Cell(25, 5, utf8_decode("Fecha"), 'TB', 1, 'C');
    $pdf->SetXY(125, 40);
    $pdf->Cell(81, 5, utf8_decode("N° de amonestación y Causa"), 'TB', 1, 'C');
    //$pdf->Cell(196, 5, utf8_decode("Columna 1"), 1, 1, 'C');

    // Aqui va la consulta para generear el reporte
    $dataAmonestaciones = $cloud->rows("
        SELECT
            per.personaId as personaId, 
            exp.prsExpedienteId as expedienteId,
            CONCAT(

                IFNULL(per.nombre1, '-'),' ',
                IFNULL(per.nombre2, '-'),' ',
                IFNULL(per.apellido1, '-'),' ',
                IFNULL(per.apellido2, '-')
            ) AS nombreCompleto,
            am.expedienteAmonestacionId AS expedienteAmonestacionId,
            date_format(am.fechaAmonestacion, '%d/%m/%Y')  as fechaAmonestacion,
            date_format(am.fechaAmonestacion, '%Y')  as anioAmonestacion,
            am.tipoAmonestacion,
            am.causaFalta,
            am.descripcionFalta,
            am.descripcionOtroCausa AS descripcionOtroCausa,
            date_format(am.fechaSuspensionInicio, '%d/%m/%Y') as fechaSuspensionInicio,
            date_format(am.fechaSuspensionFin, '%d/%m/%Y') as fechaSuspensionFin
        FROM ((th_expediente_amonestaciones am
        JOIN th_expediente_personas exp ON am.expedienteId = exp.prsExpedienteId)
        JOIN th_personas per ON per.personaId = exp.personaId)
        WHERE am.flgDelete = ? AND am.estadoAmonestacion = ?  AND am.fechaAmonestacion BETWEEN ? AND ?
        ORDER BY apellido1, apellido2, nombre1, nombre2
    ",[0, $estadoAmonestacion, $fechaInicio, $fechaFin]);

    $pdf->SetFont('Arial', '', 11);
    $altura = 45;
    $n = 0;
    $correlativoPagina = 0;
    $alturaAnterior = 0; $altoCeldaBorde = 0;
    foreach($dataAmonestaciones as $dataAmonestaciones) {
        $n++;
        $correlativoPagina++;

        if($correlativoPagina > 42) {
            $pdf->AddPage();

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetXY(10, 40);
            $pdf->Cell(10, 5, utf8_decode("N°"), 'TB', 1, 'C');
            $pdf->SetXY(20, 40);
            $pdf->Cell(80, 5, utf8_decode("Empleado"), 'TB', 1, 'C');
            $pdf->SetXY(100, 40);
            $pdf->Cell(25, 5, utf8_decode("Fecha"), 'TB', 1, 'C');
            $pdf->SetXY(125, 40);
            $pdf->Cell(81, 5, utf8_decode("N° de amonestación y Causa"), 'TB', 1, 'C');
            // Reiniciar variables
            $altura = 45;
            $correlativoPagina = 1;
            $pdf->SetFont('Arial', '', 11);
        } else {
            // Todavia se puede dibujar en la pagina
        }

        $pdf->SetXY(10, $altura);
        $pdf->Cell(10, 5, $n, 0, 1, 'C');
        $pdf->SetXY(20, $altura);
        $pdf->Cell(80, 5, utf8_decode("$dataAmonestaciones->nombreCompleto"), 0, 1, 'L');
        $pdf->SetXY(100, $altura);
        $pdf->Cell(25, 5, utf8_decode("$dataAmonestaciones->fechaAmonestacion"), 0, 1, 'C');
        $pdf->SetXY(125, $altura);
        $pdf->MultiCell(81, 5, utf8_decode("N° $dataAmonestaciones->expedienteAmonestacionId: $dataAmonestaciones->causaFalta"), 0, 'L');

        $altura = $pdf->GetY();
        $altoCeldaBorde = $altura - $alturaAnterior;

        $pdf->SetXY(10, $alturaAnterior);
        $pdf->Cell(196, $altoCeldaBorde, '', 'B', 1, 'L');

        $alturaAnterior = $altura;
    }
    $pdf->Output(utf8_decode($outputReporte) . '.pdf', "I");
?>