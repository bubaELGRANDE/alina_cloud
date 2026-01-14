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
            $this->SetXY(161, 8);
            $this->Cell(40, 4, date("d-m-Y H:i:s"), 0, 0, 'R');
            $this->SetXY(161, 12);
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

        function encabezadosTabla($x = 15, $y = 35, $arrayDatos) {
            $tempY = $y;
            // 0 curso, 1 organizador, 2, modalidad, 3 tipo formacion, 4 fecha inicio, 5 fecha fin, 6 duracion
            $this->SetFont('Arial', 'B', 10);
            // Segun la pagina, establecer X y Y aca
            $this->SetXY($x, $y);
            $this->CellHTML(13, 5, utf8_decode("<b>Curso:</b>"), 0, 0, "L");
            $this->MultiCell(110, 5, utf8_decode($arrayDatos[0]), 0, "J");
            $y = $this->GetY();
            $this->SetXY(139, $tempY);
            $this->CellHTML(62, 5, utf8_decode($arrayDatos[2]), 0, 0, "L");
            //$this->SetWidths(array(186));
            //$this->SetWidths(array(124, 62));
            //$this->Row(array($x, $y, 5), array($arrayDatos[0],$arrayDatos[2]), array(0, "No"), array("L", "L"));
            $this->SetXY($x, $y);
            $this->CellHTML(124, 5, utf8_decode($arrayDatos[1]), 0, 0, "L");
            $this->CellHTML(62, 5, utf8_decode($arrayDatos[3]), 0, 0, "L");
            $y += 5;
            //$this->SetWidths(array(124, 62));
            //$this->Row(array($x, $y, 5), array($arrayDatos[1],$arrayDatos[3]), array(0, "No"), array("L", "L"));
            $this->SetXY($x, $y);
            $this->CellHTML(62, 5, utf8_decode($arrayDatos[4]), 0, 0, "L");
            $this->CellHTML(62, 5, utf8_decode($arrayDatos[5]), 0, 0, "L");
            $this->CellHTML(56, 5, utf8_decode($arrayDatos[6]), 0, 0, "L");
            $y += 5;
            $this->SetXY($x, $y);
            $this->SetFont('Arial', 'B', 9);
            $this->SetWidths(array(10, 100, 38, 38));
            $this->Row(array($x, $y, 5), array("#", "Empleado", "Costo Insaforp", "Costo Empresa"), array(1, "No"));
        }
    }
    $filtroCursos = (isset($_REQUEST['filtroCapacitacionesCursos']) ? base64_decode(urldecode($_REQUEST['filtroCapacitacionesCursos'])) : '');
    $cursosId = (isset($_REQUEST['selectCursosEspecificos']) ? base64_decode(urldecode($_REQUEST['selectCursosEspecificos'])) : '');

    $fechaInicio = base64_decode(urldecode($_REQUEST['fechaInicio']));
    $fechaFin = base64_decode(urldecode($_REQUEST['fechaFin']));
    
    $outputReporte = "Capacitaciones internas - Cursos";
    $tituloReporte = "Capacitaciones internas: Cursos";

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    $pdf->SetTitle($outputReporte);

    if($filtroCursos == "Todos"){
        $whereCursos = "";
    }else{
        $whereCursos = "AND expedienteCapacitacionId IN($cursosId)";
    }

    $total1 = 0;
    $total2 = 0;
    $totalGeneral1 = 0;
    $totalGeneral2 = 0;

        $x = 15;
        $altura = 35;

        $pdf->AddPage();

        $dataCursos = $cloud->rows("
            SELECT 
                expedienteCapacitacionId,
                descripcionCapacitacion,
                nombreOrganizador,
                tipoModalidad,
                tipoFormacion,
                fechaIniCapacitacion,
                fechaFinCapacitacion,
                duracionCapacitacion,
                costoInsaforp,
                costoalina
            FROM th_expediente_capacitaciones
            WHERE flgDelete = ? AND fechaIniCapacitacion BETWEEN ? AND ? $whereCursos
        ", [0, $fechaInicio, $fechaFin]);

        foreach($dataCursos as $dataCursos) {
            $arrayDatos = array(
                $dataCursos->descripcionCapacitacion,
                "<b>Organizador:</b> $dataCursos->nombreOrganizador",
                "<b>Modalidad:</b> $dataCursos->tipoModalidad",
                "<b>Tipo de formación:</b> $dataCursos->tipoFormacion",
                '<b>Fecha inicio:</b> '.date("d/m/Y", strtotime($dataCursos->fechaIniCapacitacion)).'',
                '<b>Fecha fin:</b> '.date("d/m/Y", strtotime($dataCursos->fechaFinCapacitacion)).'',
                "<b>Duración:</b> ".number_format($dataCursos->duracionCapacitacion, 0, '.', ',')." horas"
            );
            if($altura + 22 > 250) {
                $altura = 35;
                $pdf->AddPage();
            } else {
                // Cabe en la página
            }
            $pdf->encabezadosTabla($x, $altura, $arrayDatos);
            $altura = $pdf->GetY();
            $corr = 0; 

            $dataEmpleados = $cloud->rows("
                SELECT 	
                    ve.nombreCompleto as nombreCompleto
                FROM th_expediente_capacitacion_detalle ecd
                JOIN view_expedientes ve ON ve.prsExpedienteId = ecd.prsExpedienteId
                WHERE ecd.flgDelete = ? AND ecd.expedienteCapacitacionId = ?
            ",[0, $dataCursos->expedienteCapacitacionId]); 
        
            foreach($dataEmpleados as $dataEmpleados){
                if($altura + 5 > 250) {
                    $altura = 35;
                    $pdf->AddPage();
                    $pdf->encabezadosTabla($x, $altura, $arrayDatos);
                    $altura = $pdf->GetY();
                } else {
                    // Cabe en la página
                }
                $corr += 1;

                $pdf->SetFont('Arial', '', 9);
                $pdf->SetXY($x, $altura);
                $pdf->SetWidths(array(10, 100, 38, 38));
                $pdf->Row(array($x, $altura, 5), array($corr, $dataEmpleados->nombreCompleto, number_format($dataCursos->costoInsaforp, 2, '.', ','), number_format($dataCursos->costoalina, 2, '.', ',')), array(1, "No"), 
                array("L", "L", "R","R"));

                $pdf->SetFont('Arial', '', 9);
                $pdf->SetXY($x, $altura);
                $pdf->SetWidths(array(10, 100, 38, 38));
                $pdf->Row(array($x, $altura, 5), array("", "", "$", "$"), array(1, "No"),array("", "", "L","L"));

                $altura += 5;
                $total1 += $dataCursos->costoInsaforp;
                $total2 += $dataCursos->costoalina;

            }
            
            if($altura + 22 > 250) {
                $altura = 35;
                $pdf->AddPage();
                $pdf->encabezadosTabla($x, $altura, $arrayDatos);
                $altura = $pdf->GetY();
            } else {
                // Cabe en la página
            }
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY($x, $altura);
            $pdf->SetWidths(array(110, 38, 38));
            $pdf->Row(array($x, $altura, 5), array("Total del curso", number_format($total1, 2, '.', ','), number_format($total2, 2, '.', ',')), array(1, "No"),array("L", "R", "R"));

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY($x, $altura);
            $pdf->SetWidths(array(110, 38, 38));
            $pdf->Row(array($x, $altura, 5), array("", "$", "$"), array(1, "No"),array("", "L","L"));
    
            $altura += 10;
            $totalGeneral1 += $total1;
            $totalGeneral2 += $total2;
            $total1 = 0;
            $total2 = 0;
        }

        if($altura + 5 > 250) {
            $altura = 35;
            $pdf->AddPage();
            $altura = $pdf->GetY();
        } else {
            // Cabe en la página
        }
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($x, $altura);
        $pdf->SetWidths(array(110, 38, 38));
        $pdf->Row(array($x, $altura, 5), array("Total general", number_format($totalGeneral1, 2, '.', ','), number_format($totalGeneral2, 2, '.', ',')), array(1, "No"),array("L", "R", "R"));

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($x, $altura);
        $pdf->SetWidths(array(110, 38, 38));
        $pdf->Row(array($x, $altura, 5), array("", "$", "$"), array(1, "No"),array("", "L","L"));

        $altura += 10;

    $pdf->Output($outputReporte. '.pdf', "I");
?>