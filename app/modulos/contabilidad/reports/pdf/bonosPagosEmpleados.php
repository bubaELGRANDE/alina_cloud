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
            $this->SetFont('Arial', 'B', 11);
            $this->SetXY(10, $this->GetY());
            $this->Cell(196, 5, utf8_decode($txtEncabezado), 0, 0, 'L');
            $this->SetXY(10, $this->GetY() + 6);
            $this->SetFont('Arial', 'B', 10);
            $this->SetXY(10, $this->GetY());
            $this->Cell(11, 5, utf8_decode('#'), 'B', 0, 'C');
            $this->Cell(145, 5, utf8_decode('Empleado'), 'B', 0, 'C');
            $this->Cell(40, 5, utf8_decode('Bonificación'), 'B', 0, 'C');

            $this->SetY($this->GetY() + 5);
        }

        // Renglones de la tabla
        function registrosTabla($n, $empleado, $bono) {
            $alturaTemp = $this->GetY();
            $this->SetXY(10, $this->GetY());
            $this->SetFont('Arial', '', 10);
            $this->SetXY(10, $this->GetY());
            $this->Cell(11, 5, $n, 'B', 0, 'L');
            $this->Cell(145, 5, utf8_decode($empleado), 'B', 0, 'L');
            $this->Cell(40, 5, number_format($bono, 2, ".", ","), 'B', 0, 'R');

            // Simbolo de dolar
            $this->SetXY(166, $alturaTemp);
            $this->Cell(40, 5, utf8_decode("$"), 0, 0, 'L');

            $this->SetY($this->GetY() + 5);
        }

        // Total general al final de la tabla
        function footerTabla($txtTotal, $totalGeneral) {
            $alturaTemp = $this->GetY() + 5;
            $this->SetFont('Arial', 'B', 10);
            $this->SetXY(10, $this->GetY() + 5);
            $this->Cell(156, 5, utf8_decode($txtTotal), 'B', 0, 'L');
            $this->Cell(40, 5, number_format($totalGeneral, 2), 'B', 0, 'R');

            // Simbolos de dolar
            $this->SetXY(166, $alturaTemp);
            $this->Cell(40, 5, utf8_decode("$"), 0, 0, 'L');

            $this->SetY($this->GetY() + 5);
        }
    }

    /*
        REQUEST:
		periodoBonoId
		txtPeriodo
        fechaPagoBono
    */
    $periodoBonoId = base64_decode(urldecode($_REQUEST['periodoBonoId']));
    $txtPeriodo = base64_decode(urldecode($_REQUEST['txtPeriodo']));

    $tituloReporte = "Pago de bonificaciones a empleados";
    $subtituloReporte = "Periodo: {$txtPeriodo}";

    $outputReporte = "Pago de bonificaciones a empleados - {$txtPeriodo}";

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    
    $pdf->SetTitle(utf8_decode($outputReporte));

    for ($i=0; $i < 2; $i++) { 
        $pdf->AddPage();
        $pdf->SetY(35);
        
        if($i == 0) {
            $txtEncabezado = "Bonificaciones aplicadas";

            $dataBonosEmpleado = $cloud->rows("
                SELECT 
                    bpd.personaId AS personaId,
                    exp.nombreCompleto AS nombreCompleto,
                    SUM(pb.montoBono) AS totalMontoBono
                FROM conta_planilla_bonos pb
                JOIN conf_bonos_personas_detalle bpd ON bpd.bonoPersonaDetalleId = pb.bonoPersonaDetalleId
                JOIN view_expedientes exp ON exp.prsExpedienteId = pb.prsExpedienteId
                WHERE pb.periodoBonoId = ? AND pb.flgDelete = ?
                GROUP BY bpd.personaId, exp.nombreCompleto
                ORDER BY exp.apellido1, exp.apellido2, exp.nombre1, exp.nombre2
            ", [$periodoBonoId, 0]);
        } else {
            $txtEncabezado = "Bonificaciones no aplicadas";

            $dataBonosEmpleado = $cloud->rows("
                SELECT 
                    bpd.personaId AS personaId,
                    exp.nombreCompleto AS nombreCompleto,
                    exp.prsExpedienteId AS prsExpedienteId
                FROM conf_bonos_personas_detalle bpd
                JOIN view_expedientes exp ON exp.personaId = bpd.personaId
                WHERE bpd.flgDelete = ? AND exp.estadoPersona = ? AND exp.estadoExpediente = ?
                ORDER BY exp.apellido1, exp.apellido2, exp.nombre1, exp.nombre2
            ", [0, "Activo", "Activo"]);
        }
        $pdf->encabezadoTabla($txtEncabezado);

        $n = 0; $nPagina = 0;
        $totalGeneral = 0;
        foreach($dataBonosEmpleado as $bonoEmpleado) {
            if($i == 1) {
                // Verificar si tiene bonos aplicados por cualquier gerencia
                $existeBonoOtroEncargado = $cloud->count("
                    SELECT pb.planillaBonoId 
                    FROM conta_planilla_bonos pb
                    JOIN view_expedientes exp ON exp.prsExpedienteId = pb.prsExpedienteId
                    WHERE pb.periodoBonoId = ? AND exp.personaId = ? AND pb.flgDelete = ?
                ", [$periodoBonoId, $bonoEmpleado->personaId, 0]);

                if($existeBonoOtroEncargado > 0) {
                    $flgMostrarRegistro = 1;
                } else {
                    $flgMostrarRegistro = 0;
                }
            } else {
                $flgMostrarRegistro = 0;
            }

            if($flgMostrarRegistro == 0) {
                $nPagina++;
                $n++;
                // Al no ser un reporte dinámico de alturas entre lineas, se puede controlar por registros
                if($nPagina > 42) {
                    $nPagina = 1;
                    $pdf->AddPage();
                    $pdf->SetY(35);
                    $pdf->encabezadoTabla($txtEncabezado);
                } else {
                    // Cabe en la página
                }

                $montoBono = ($i == 0 ? $bonoEmpleado->totalMontoBono : 0);
                $totalGeneral += $montoBono;
                $pdf->registrosTabla($n, $bonoEmpleado->nombreCompleto, $montoBono);
            } else {
                // Validación por bono duplicado o aplicado por otro jefe
            }
        }

        if($i == 0) {
            if($nPagina >= 42) {
                $nPagina = 1;
                $pdf->AddPage();
                $pdf->SetY(35);
            } else {
                // Cabe en la página    
            }
            if($n == 0) {
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetXY(10, $pdf->GetY());
                $pdf->Cell(196, 5, utf8_decode("No se encontraron bonificaciones aplicadas en este periodo"), 'B', 0, 'C');
            } else {
                // Se ingresaron y dibujaron registros
            }
            $pdf->footerTabla("Total general", $totalGeneral);
        } else {
            // No mostrar total general
            if($n == 0) {
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetXY(10, $pdf->GetY());
                $pdf->Cell(196, 5, utf8_decode("No se encontraron bonificaciones no aplicadas en este periodo"), 'B', 0, 'C');
            } else {
                // Se ingresaron y dibujaron registros
            }
        }
    }

    $pdf->Output(utf8_decode($outputReporte) . '.pdf', "I");
?>
