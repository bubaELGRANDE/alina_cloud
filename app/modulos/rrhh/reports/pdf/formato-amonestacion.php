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
            //$this->Cell(0, 5, utf8_decode('Página '.$this->PageNo().'/{nb}'), 0, 0, 'C');
        }
    }

    $anio = base64_decode(urldecode($_REQUEST['filtroAnio']));
    $expedienteAmonestacionId = base64_decode(urldecode($_REQUEST['amonestacionReporte']));

    $dataAmonestacion = $cloud->row("
        SELECT
            am.expedienteId AS expedienteId,
            emp.nombreCompletoNA AS nombreCompletoFormatEmpleado,
            emp.departamentoSucursal AS departamentoSucursalEmpleado,
            emp.cargoPersona AS cargoPersonaEmpleado,
            jefe.nombreCompletoNA AS nombreCompletoFormatJefe,
            jefe.departamentoSucursal AS departamentoSucursalJefe,
            jefe.cargoPersona AS cargoPersonaJefe,
            am.fechaAmonestacion AS fechaAmonestacion,
            DATE_FORMAT(am.fechaAmonestacion, '%d/%m/%Y') AS fechaAmonestacionFormat,
            am.tipoAmonestacion AS tipoAmonestacion,
            am.flgReincidencia AS flgReincidencia,
            am.amonestacionAnteriorId AS amonestacionAnteriorId,
            am.fechaAmonestacion AS fechaAmonestacionAnterior,
            DATE_FORMAT(am.fechaAmonestacion, '%d/%m/%Y') AS fechaAmonestacionFormatAnterior,
            am.causaFalta AS causaFalta,
            am.descripcionOtroCausa AS descripcionOtroCausa,
            am.descripcionFalta AS descripcionFalta,
            am.consecuenciaFalta AS consecuenciaFalta,
            am.fechaSuspensionInicio AS fechaSuspensionInicio,
            DATE_FORMAT(am.fechaSuspensionInicio, '%d/%m/%Y') AS fechaSuspensionInicioFormat,
            am.fechaSuspensionFin AS fechaSuspensionFin,
            DATE_FORMAT(am.fechaSuspensionFin, '%d/%m/%Y') AS fechaSuspensionFinFormat,
            am.descripcionConsecuencia AS descripcionConsecuencia,
            am.compromisoMejora AS compromisoMejora,
            am.estadoAmonestacion AS estadoAmonestacion,
            am.justificacionAnulada AS justificacionAnulada
        FROM th_expediente_amonestaciones am
        JOIN view_expedientes emp ON emp.prsExpedienteId = am.expedienteId
        LEFT JOIN view_expedientes jefe ON jefe.prsExpedienteId = am.expedienteIdJefe
        LEFT JOIN th_expediente_amonestaciones amr ON amr.expedienteAmonestacionId = am.amonestacionAnteriorId
        WHERE am.expedienteAmonestacionId = ? AND am.flgDelete = ?
    ", [$expedienteAmonestacionId, 0]);

    // Validación para tipo de sanción
    $arrayMarcaTipoSancion = array("", "", "");
    switch ($dataAmonestacion->tipoAmonestacion) {
        case 'Verbal':
            $arrayMarcaTipoSancion[0] = "X";
        break;
        
        case 'Escrita':
            $arrayMarcaTipoSancion[1] = "X";
        break;

        default:
            // Verbal y Escrita
            $arrayMarcaTipoSancion[2] = "X";
        break;
    }

    // Validación para reincidencia
    $arrayMarcaReincidencia = array("", "");
    $amonestacionAnteriorTexto = ""; $amonestacionAnteriorFecha = "";

    if($dataAmonestacion->flgReincidencia == "Si") {
        $arrayMarcaReincidencia[0] = "X";
        $amonestacionAnteriorTexto = "N° $dataAmonestacion->amonestacionAnteriorId";
        $amonestacionAnteriorFecha = $dataAmonestacion->fechaAmonestacionFormatAnterior;
    } else {
        // No
        $arrayMarcaReincidencia[1] = "X";
        $amonestacionAnteriorTexto = "-";
        $amonestacionAnteriorFecha = "-";
    }

    $tituloReporte = 'Amonestación de personal';
    $subtituloReporte = "";
    
    $outputReporte = "Amonestación de personal N° ".str_pad($expedienteAmonestacionId, 3, '0', STR_PAD_LEFT)." - $dataAmonestacion->nombreCompletoFormatEmpleado";

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    $pdf->SetTitle(utf8_decode($outputReporte));

    $flgLineas = 0;

    $pdf->AddPage();

    $altura = 35;

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, $altura);
    $pdf->CellHTML(98, 5, utf8_decode("<b>N° de amonestación:</b> " . str_pad($expedienteAmonestacionId, 3, '0', STR_PAD_LEFT)), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(108, $altura);
    $pdf->Cell(78, 5, utf8_decode("Fecha:"), $flgLineas, 1, 'R');
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetXY(186, $altura);
    $pdf->Cell(20, 5, utf8_decode($dataAmonestacion->fechaAmonestacionFormat), $flgLineas, 1, 'C');
    $altura += 5;

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(196, 5, utf8_decode("Información del empleado"), 0, 1, 'C');
    $altura += 10;

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(26, 5, utf8_decode("Colaborador:"), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetXY(36, $altura);
    $pdf->Cell(170, 5, utf8_decode($dataAmonestacion->nombreCompletoFormatEmpleado), 'B', 1, 'L');
    $altura += 7;

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(14, 5, utf8_decode("Cargo:"), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetXY(24, $altura);
    $pdf->Cell(111, 5, utf8_decode($dataAmonestacion->cargoPersonaEmpleado), 'B', 1, 'L');

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(135, $altura);
    $pdf->Cell(29, 5, utf8_decode("Departamento:"), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetXY(164, $altura);
    $pdf->Cell(42, 5, utf8_decode($dataAmonestacion->departamentoSucursalEmpleado), 'B', 1, 'L');
    $altura += 7;

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(30, 5, utf8_decode("Jefe inmediato:"), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetXY(40, $altura);
    $pdf->Cell(166, 5, utf8_decode($dataAmonestacion->nombreCompletoFormatJefe), 'B', 1, 'L');
    $altura += 10;

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(196, 5, utf8_decode("Descripción de la amonestación"), $flgLineas, 1, 'C');
    $altura += 10;

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(98, 5, utf8_decode("Tipo de sanción:"), $flgLineas, 1, 'C');
    $pdf->SetXY(108, $altura);
    $pdf->Cell(98, 5, utf8_decode("Reincidencia:"), $flgLineas, 1, 'C');
    $altura += 7;

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(8, 5, $arrayMarcaTipoSancion[0], 1, 1, 'C');
    $pdf->SetXY(18, $altura);
    $pdf->Cell(90, 5, utf8_decode("Verbal"), $flgLineas, 1, 'L');

    $pdf->SetXY(108, $altura);
    $pdf->Cell(8, 5, $arrayMarcaReincidencia[0], 1, 1, 'C');
    $pdf->SetXY(116, $altura);
    $pdf->Cell(90, 5, utf8_decode("Sí"), $flgLineas, 1, 'L'); 
    $altura += 7;

    $pdf->SetXY(10, $altura);
    $pdf->Cell(8, 5, $arrayMarcaTipoSancion[1], 1, 1, 'C');
    $pdf->SetXY(18, $altura);
    $pdf->Cell(90, 5, utf8_decode("Escrita"), $flgLineas, 1, 'L');
    $pdf->SetXY(108, $altura);
    $pdf->Cell(8, 5, $arrayMarcaReincidencia[1], 1, 1, 'C');
    $pdf->SetXY(116, $altura);
    $pdf->Cell(90, 5, utf8_decode("No"), $flgLineas, 1, 'L'); 
    $altura += 7;

    $pdf->SetXY(10, $altura);
    $pdf->Cell(8, 5, $arrayMarcaTipoSancion[2], 1, 1, 'C');
    $pdf->SetXY(18, $altura);
    $pdf->Cell(90, 5, utf8_decode("Verbal y Escrita"), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetXY(108, $altura);
    $pdf->Cell(38, 5, utf8_decode("Amonestación anterior:"), $flgLineas, 1, 'L'); 
    $pdf->SetXY(146, $altura);
    $pdf->Cell(23, 5, utf8_decode($amonestacionAnteriorTexto), 'B', 1, 'C'); 
    $pdf->SetXY(169, $altura);
    $pdf->Cell(12, 5, utf8_decode("Fecha:"), $flgLineas, 1, 'L'); 
    $pdf->SetXY(181, $altura);
    $pdf->Cell(25, 5, utf8_decode($amonestacionAnteriorFecha), 'B', 1, 'C');
    $altura += 12;

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(196, 5, utf8_decode("Causa o motivo de la sanción:"), $flgLineas, 1, 'C');
    $altura += 10;

    $causasDeSancionColumna1 = array("Faltas repetidas e injustificadas de asistencia o puntualidad al trabajo", "Indisciplina o desobediencia en el trabajo", "Transgresión de la buena fe contractual, así como el abuso de confianza en el desempeño del trabajo", "Disminución continuada y voluntaria en el rendimiento de sus funciones", "Presentarse en estado de embriaguez o en efectos de sustancias tóxicas", "Incumplimiento en marcar asistencia (física o virtual)");
    $causasDeSancionColumna2 = array("Conductas de irrespeto o intolerancia hacia su jefe y/o compañeros", "Robo o hurto a la empresa o a otros empleados", "Abandono del puesto de trabajo sin motivos o autorización del jefe inmediato", "Incumplimiento con la política de seguridad de la empresa", "Acoso o abuso laboral (ideológico, sexual, religión, entre otros)","Falta de cumplimiento en el uso del uniforme institucional");
    
    $alturaOld = $altura;
    // "Otros"
    $pdf->SetFont('Arial', '', 9);
    for ($i=0; $i < count($causasDeSancionColumna1); $i++) { 
        $pdf->SetXY(10, $altura);

        if($dataAmonestacion->causaFalta == $causasDeSancionColumna1[$i]) {
            $pdf->Cell(8, 5, "X", 1, 1, 'C');
        } else {
            $pdf->Cell(8, 5, "", 1, 1, 'C');
        }

        $pdf->SetXY(18, $altura);
        $pdf->MultiCell(90, 4, utf8_decode($causasDeSancionColumna1[$i]), $flgLineas, 'L');
        $altura += 9;
    }

    // Para regresar a la altura anterior
    $altura = $alturaOld;
    for ($i=0; $i < count($causasDeSancionColumna2); $i++) { 
        $pdf->SetXY(108, $altura);

        if($dataAmonestacion->causaFalta == $causasDeSancionColumna2[$i]) {
            $pdf->Cell(8, 5, "X", 1, 1, 'C');
        } else {
            $pdf->Cell(8, 5, "", 1, 1, 'C');
        }

        $pdf->SetXY(116, $altura);
        $pdf->MultiCell(90, 4, utf8_decode($causasDeSancionColumna2[$i]), $flgLineas, 'L');
        $altura += 9;
    }

    $pdf->SetXY(10, $altura);

    $otrosTexto = "";
    if($dataAmonestacion->causaFalta == "Otros") {
        $pdf->Cell(8, 5, "X", 1, 1, 'C');
        $otrosTexto = utf8_decode($dataAmonestacion->descripcionOtroCausa);
    } else {
        $pdf->Cell(8, 5, "", 1, 1, 'C');
    }
    $pdf->SetXY(18, $altura);
    $pdf->Cell(11, 5, utf8_decode("Otros:"), $flgLineas, 1, 'L');

    $alturaOld = $altura;
    for ($i=0; $i < 4; $i++) { 
        $pdf->SetXY(29, $altura);
        $pdf->Cell(177, 5, "", 'B', 1, 'L');
        $altura += 5;
    }
    $altura = $alturaOld;

    $pdf->SetXY(29, $altura);
    $pdf->MultiCell(177, 5, $otrosTexto, 0, 'L');
    $altura += (5 * 6);

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(196, 5, utf8_decode("Descripción de la causa:"), $flgLineas, 1, 'L');
    $altura += 6;

    $alturaOld = $altura;
    for ($i=0; $i < 8; $i++) { 
        $pdf->SetXY(10, $altura);
        $pdf->Cell(196, 5, "", 'B', 1, 'L');
        $altura += 5;
    }
    $altura = $alturaOld;

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetXY(10, $altura);
    $pdf->MultiCell(196, 5, utf8_decode($dataAmonestacion->descripcionFalta), 0, 'L');

    $pdf->AddPage();

    $altura = 35;

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(196, 5, utf8_decode("Consecuencia de la falta:"), $flgLineas, 1, 'C');
    $altura += 10;

    $consecuenciasColumna1 = array("Descuento del día", "Descuento de día y séptimo", "Descuento de séptimo", "Descuento total o parcial de bono");
    $consecuenciasColumna2 = array("Descuento del acumulativo de minutos en concepto de llegadas tardes", "Llamado de atención verbal y escrito", "Destitución");
    
    $alturaOld = $altura;
    // "Otros"
    $pdf->SetFont('Arial', '', 9);
    for ($i=0; $i < count($consecuenciasColumna1); $i++) { 
        $pdf->SetXY(10, $altura);

        if($dataAmonestacion->consecuenciaFalta == $consecuenciasColumna1[$i]) {
            $pdf->Cell(8, 5, "X", 1, 1, 'C');
        } else {
            $pdf->Cell(8, 5, "", 1, 1, 'C');
        }

        $pdf->SetXY(18, $altura);
        $pdf->MultiCell(90, 4, utf8_decode($consecuenciasColumna1[$i]), $flgLineas, 'L');
        $altura += 9;
    }

    // Para regresar a la altura anterior
    $altura = $alturaOld;
    for ($i=0; $i < count($consecuenciasColumna2); $i++) { 
        $pdf->SetXY(108, $altura);

        if($dataAmonestacion->consecuenciaFalta == $consecuenciasColumna2[$i]) {
            $pdf->Cell(8, 5, "X", 1, 1, 'C');
        } else {
            $pdf->Cell(8, 5, "", 1, 1, 'C');
        }

        $pdf->SetXY(116, $altura);
        $pdf->MultiCell(90, 4, utf8_decode($consecuenciasColumna2[$i]), $flgLineas, 'L');
        $altura += 9;
    }
    $altura += 9;

    $pdf->SetXY(10, $altura);
    $fechaInicioSuspension = ""; $fechaFinSuspension = "";
    if($dataAmonestacion->consecuenciaFalta == "Suspensión de labores sin goce de sueldo") {
        $pdf->Cell(8, 5, "X", 1, 1, 'C');
        $fechaInicioSuspension = $dataAmonestacion->fechaSuspensionInicioFormat;
        $fechaFinSuspension = $dataAmonestacion->fechaSuspensionFinFormat;
    } else {
        $pdf->Cell(8, 5, "", 1, 1, 'C');
        $fechaInicioSuspension = "-";
        $fechaFinSuspension = "-";
    }
    $pdf->SetXY(18, $altura);
    $pdf->MultiCell(90, 4, utf8_decode("Suspensión de labores sin goce de sueldo"), $flgLineas, 'L');
    $pdf->SetXY(108, $altura - 0.5);
    $pdf->Cell(46, 5, utf8_decode("Vigencia de la suspensión. Del:"), $flgLineas, 1, 'L');
    $pdf->SetXY(154, $altura - 0.5);
    $pdf->Cell(23, 5, $fechaInicioSuspension, 'B', 1, 'C');
    $pdf->SetXY(177, $altura - 0.5);
    $pdf->Cell(5, 5, utf8_decode("al:"), $flgLineas, 1, 'C');
    $pdf->SetXY(182, $altura - 0.5);
    $pdf->Cell(24, 5, $fechaFinSuspension, 'B', 1, 'C');
    $altura += 14;

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(196, 5, utf8_decode("Descripción de la consecuencia:"), $flgLineas, 1, 'L');
    $altura += 6;

    $alturaOld = $altura;
    for ($i=0; $i < 3; $i++) { 
        $pdf->SetXY(10, $altura);
        $pdf->Cell(196, 5, "", 'B', 1, 'L');
        $altura += 5;
    }

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetXY(10, $alturaOld);
    $pdf->MultiCell(196, 5, utf8_decode($dataAmonestacion->descripcionConsecuencia), 0, 'L');

    $altura += 14;

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY(10, $altura);
    $pdf->Cell(196, 5, utf8_decode("Compromiso de mejora:"), $flgLineas, 1, 'L');
    $altura += 6;

    $alturaOld = $altura;
    for ($i=0; $i < 10; $i++) { 
        $pdf->SetXY(10, $altura);
        $pdf->Cell(196, 5, "", 'B', 1, 'L');
        $altura += 5;
    }
    $altura = $alturaOld;

    $pdf->SetFont('Arial', '', 10);
    /*
    $pdf->SetXY(10, $altura);
    $pdf->MultiCell(196, 5, utf8_decode($dataAmonestacion->compromisoMejora), 0, 'L');
    */
    $altura += (9 * 11);

    $pdf->SetXY(10, $altura);
    $pdf->Cell(5, 5, utf8_decode('F:'), $flgLineas, 0, 'L'); 
    $pdf->Cell(47, 5, '', 'B', 0, 'C');  
    $pdf->SetXY(82, $altura);
    $pdf->Cell(5, 5, utf8_decode('F:'), $flgLineas, 0, 'L'); 
    $pdf->Cell(47, 5, '', 'B', 0, 'C');  
    $pdf->SetXY(154, $altura);
    $pdf->Cell(5, 5, utf8_decode('F:'), $flgLineas, 0, 'L'); 
    $pdf->Cell(47, 5, '', 'B', 0, 'C');  
    $altura += 5;
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY(15, $altura);
    $pdf->Cell(47, 5, utf8_decode('Colaborador'), 0, 0, 'C');
    $pdf->SetXY(15, $altura + 4);
    $pdf->Cell(47, 5, utf8_decode('(Firma de conformidad)'), 0, 0, 'C');
    $pdf->SetXY(87, $altura);
    $pdf->Cell(47, 5, utf8_decode('Jefe inmediato'), 0, 0, 'C');
    $pdf->SetXY(159, $altura);
    $pdf->Cell(47, 5, utf8_decode('Encargado de Recursos Humanos'), 0, 0, 'C');

    $pdf->Output(utf8_decode($outputReporte) . '.pdf', "I");
?>