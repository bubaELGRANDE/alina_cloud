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
            $this->SetWidths(array(10, 58, 23, 23, 20, 26, 26));
            $this->Row(array($x, $y, 5), array("#", "Cursos", "Fecha inicio", "Fecha fin","Duración","Costo Insaforp","Costo Empresa"), array(1, "No"));

            /*
            En este reporte no aplica, ya que son diferentes tablas y se debe manejar manualmente las alturas
            // Esto es para los registros de la tabla
            $this->SetXY(15, 70);
            $this->SetWidths(array(10, 54, 25, 25, 20, 26, 26));
            $this->SetFont('Arial', '', 7);
            */
        }
        
    }
    $filtroEmpleados = (isset($_REQUEST['filtroCapacitacionesEmpleados']) ? base64_decode(urldecode($_REQUEST['filtroCapacitacionesEmpleados'])) : '');
    $empleadosId = (isset($_REQUEST['selectEmpleadosEspecificos']) ? base64_decode(urldecode($_REQUEST['selectEmpleadosEspecificos'])) : '');

    $fechaInicio = base64_decode(urldecode($_REQUEST['fechaInicio']));
    $fechaFin = base64_decode(urldecode($_REQUEST['fechaFin']));

    $outputReporte = "Capacitaciones internas - Empleados";
    $tituloReporte = "Capacitaciones internas: Empleados";

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    $pdf->SetTitle($outputReporte);

        $x = 15;
        $altura = 35;
        $total1 = 0;
        $total2 = 0;
        $totalGeneral1 = 0;
        $totalGeneral2 = 0;

        $pdf->AddPage();
        if($filtroEmpleados == "Todos"){
            $where = "";
        }else{
            $where = "AND ecd.prsExpedienteId IN($empleadosId)";
        }

        $dataEmpleados = $cloud->rows("
            SELECT
                ecd.expedienteCapacitacionId as expedienteCapacitacionId,
                ecd.prsExpedienteId as prsExpedienteId,
                ve.nombreCompleto as nombreCompleto
            FROM th_expediente_capacitacion_detalle ecd
            JOIN view_expedientes ve ON ve.prsExpedienteId = ecd.prsExpedienteId
            JOIN th_expediente_capacitaciones ec ON ec.expedienteCapacitacionId = ecd.expedienteCapacitacionId
            WHERE ecd.flgDelete = ? AND ve.estadoPersona = ? AND ve.estadoExpediente = ? AND ec.fechaIniCapacitacion BETWEEN ? AND ? $where
            GROUP BY ecd.prsExpedienteId
            ORDER BY ve.nombreCompleto
        ",[0, 'Activo','Activo',$fechaInicio,$fechaFin]);
        foreach($dataEmpleados as $dataEmpleados) {
            if($altura + 10 > 250) {
                $altura = 35;
                $pdf->AddPage();  
            } else {
                // Cabe en la página
            }
            $txtEncabezado = "<b>Empleado:</b> $dataEmpleados->nombreCompleto";
            $pdf->encabezadosTabla($x, $altura);
            $altura += 10;
            $corr = 0;

            $dataCursos = $cloud->rows("
                SELECT 
                    ec.descripcionCapacitacion,
                    ec.fechaIniCapacitacion,
                    ec.fechaFinCapacitacion,
                    ec.duracionCapacitacion,
                    ec.costoInsaforp,
                    ec.costoalina
                FROM th_expediente_capacitacion_detalle ecd
                JOIN th_expediente_capacitaciones ec ON ec.expedienteCapacitacionId = ecd.expedienteCapacitacionId
                WHERE ecd.flgDelete = ? AND ecd.prsExpedienteId = ?           
            ",[0,$dataEmpleados->prsExpedienteId]);

            foreach($dataCursos as $dataCursos){
                if($altura + 5 > 250) {
                    $altura = 35;
                    $pdf->AddPage();
                    $txtEncabezado = "<b>Empleado:</b> $dataEmpleados->nombreCompleto";
                    $pdf->encabezadosTabla($x, $altura);
                    $altura += 10;
                } else {
                    // Cabe en la página
                }
                $corr += 1;
                $pdf->SetFont('Arial', '', 9);

                $pdf->SetXY($x, $altura);
                $pdf->SetWidths(array(10, 58, 23, 23, 20, 26, 26));
                $pdf->Row(array($x, $altura, 5), array("", "", "", "", "", "$", "$"), array("", "No"),array("", "", "", "", "", "L","L"));

                $pdf->SetXY($x, $altura);
                $pdf->SetWidths(array(10, 58, 23, 23, 20, 26, 26));
                $pdf->Row(array($x, $altura, 5), array($corr, $dataCursos->descripcionCapacitacion, date("d/m/Y", strtotime($dataCursos->fechaIniCapacitacion)), date("d/m/Y", strtotime($dataCursos->fechaFinCapacitacion)),  number_format($dataCursos->duracionCapacitacion, 0, '.', ',') . " horas", number_format($dataCursos->costoInsaforp, 2, '.', ','), number_format($dataCursos->costoalina, 2, '.', ',')), array(1, "No"), 
                array("L", "L", "C", "C", "R", "R", "R"));

                $altura = $pdf->GetY();

                $total1 += $dataCursos->costoInsaforp;
                $total2 += $dataCursos->costoalina;
            }
            if($altura > 250) {
                $altura = 35;
                $pdf->AddPage();
                $txtEncabezado = "<b>Empleado:</b> $dataEmpleados->nombreCompleto";
                $pdf->encabezadosTabla($x, $altura);
                $altura += 10;
            } else {
                // Cabe en la página
            }
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY($x, $altura);
            $pdf->SetWidths(array(134, 26, 26));
            $pdf->Row(array($x, $altura, 5), array("Total del empleado", number_format($total1, 2, '.', ','), number_format($total2, 2, '.', ',')), array(1, "No"),array("L", "R", "R"));

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY($x, $altura);
            $pdf->SetWidths(array(134, 26, 26));
            $pdf->Row(array($x, $altura, 5), array("", "$", "$"), array(1, "No"),array("", "L","L"));
    
            $altura += 10;
            $totalGeneral1 += $total1;
            $totalGeneral2 += $total2;
            $total1 = 0;
            $total2 = 0;
        }
        
        if($altura > 250) {
            $altura = 35;
            $pdf->AddPage();
            $txtEncabezado = "<b>Empleado:</b> $dataEmpleados->nombreCompleto";
            $pdf->encabezadosTabla($x, $altura);
            $altura += 10;
        } else {
            // Cabe en la página
        }
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($x, $altura);
        $pdf->SetWidths(array(134, 26, 26));
        $pdf->Row(array($x, $altura, 5), array("Total general", number_format($totalGeneral1, 2, '.', ','), number_format($totalGeneral2, 2, '.', ',')), array(1, "No"),array("L", "R", "R"));

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($x, $altura);
        $pdf->SetWidths(array(134, 26, 26));
        $pdf->Row(array($x, $altura, 5), array("", "$", "$"), array(1, "No"),array("", "L","L"));

        $altura += 10;
        

    $pdf->Output($outputReporte. '.pdf', "I");
?>