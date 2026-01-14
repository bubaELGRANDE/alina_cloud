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
    }


    $anio = base64_decode(urldecode($_REQUEST['filtroAnio']));
    $expedienteAmonestacionId = base64_decode(urldecode($_REQUEST['amonestacionReporte']));

    $tituloReporte = 'Formato de amonestación';
    $subtituloReporte = "";
    
    $outputReporte = 'Formato de amonestación - nombreCompleto';

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    $pdf->SetTitle(utf8_decode($outputReporte));

    // Aqui va la consulta para generear el reporte
    $dataAmonestaciones = $cloud->row("
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
            am.expedienteIdJefe,
            am.expedienteId,
            date_format(am.fechaAmonestacion, '%d/%m/%Y')  as fechaAmonestacion,
            date_format(am.fechaAmonestacion, '%Y')  as anioAmonestacion,
            am.tipoAmonestacion,
            am.causaFalta,
            am.descripcionFalta,
            am.descripcionOtroCausa AS descripcionOtroCausa,
            am.consecuenciaFalta,
            am.compromisoMejora,
            am.flgReincidencia,
            am.estadoAmonestacion,
            am.justificacionAnulada,
            date_format(am.fechaSuspensionInicio, '%d/%m/%Y') as fechaSuspensionInicio,
            date_format(am.fechaSuspensionFin, '%d/%m/%Y') as fechaSuspensionFin
        FROM ((th_expediente_amonestaciones am
        JOIN th_expediente_personas exp ON am.expedienteId = exp.prsExpedienteId)
        JOIN th_personas per ON per.personaId = exp.personaId)
        WHERE am.flgDelete = ? AND expedienteAmonestacionId = ?
        ORDER BY apellido1, apellido2, nombre1, nombre2
    ",[0, $expedienteAmonestacionId]);

    $dataEmpleadosACargo = $cloud->row("
        SELECT 
            ve.estadoExpediente as estadoExpediente,
            ve.estadoPersona as estadoPersona,
            ve.nombreCompleto as nombreCompleto,
            ve.nombreCompletoNA As nombreCompletoNA,
            ve.cargoPersona as cargoPersona,
            ve.departamentoSucursal as departamentoSucursal,
            ve.sucursal as sucursal,
            amon.expedienteAmonestacionId as expedienteAmonestacionId,
            jef.jefeId AS jefeId,
            pers.nombreCompletoNA AS nombreCompletoJefe
        FROM th_expediente_jefaturas jef
        JOIN view_expedientes ve ON ve.prsExpedienteId = jef.prsExpedienteId
        JOIN th_expediente_amonestaciones amon ON amon.expedienteId = ve.prsExpedienteId
        JOIN view_expedientes pers ON pers.prsExpedienteId = jef.jefeId
        WHERE amon.flgDelete = ? AND amon.expedienteAmonestacionId = ? 
        ORDER BY ve.nombre1, ve.nombre2, ve.apellido1, ve.apellido2
    ", [0, $expedienteAmonestacionId]);
    

    $flgLineas = 0;
    $pdf->AddPage();
    // Fuente, Negrita, I para cursiva o vacio (solo comillas) para normal, tamaño de letra
    $pdf->SetFont('Arial', 'B', 12);
    // X es movimiento lateral, Y es subir o bajar
    $pdf->SetXY(10, 50);
    // Ancho de la celda, Alto de la celda, Contenido de la celda (utf8_decode, number_format, o una cadena de texto en HTML, una variable), Márgenes 1 todos 0 ninguno y letras TBLR, "1", Alineación del texto
    $pdf->Cell(22, 5, utf8_decode("Empleado:"), $flgLineas, 1, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(32, 50);
    $pdf->Cell(136, 5, utf8_decode($dataAmonestaciones->nombreCompleto),'B', 0, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(168, 50);
    $pdf->Cell(14, 5, utf8_decode("Fecha: "), $flgLineas, 1, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(182, 50);
    $pdf->Cell(24, 5, utf8_decode($dataAmonestaciones->fechaAmonestacion),'B', 0, 'L');
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(10, 55);
    $pdf->Cell(13, 5, utf8_decode("Cargo:"), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(23, 55);
    $pdf->Cell(60, 5, utf8_decode($dataEmpleadosACargo->cargoPersona),'B', 0, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(83, 55);
    $pdf->Cell(13, 5, utf8_decode("Depto: "), $flgLineas, 1, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(96, 55);
    $pdf->Cell(40, 5, utf8_decode($dataEmpleadosACargo->departamentoSucursal),'B', 0, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(136, 55);
    $pdf->Cell(10, 5, utf8_decode("Jefe: "), $flgLineas, 1, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(146, 55);
    $pdf->Cell(60, 5, utf8_decode($dataEmpleadosACargo->nombreCompletoJefe),'B', 0, 'L');

    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(10, 60);
    $pdf->Cell(35, 5, utf8_decode("Tipo de sanción:"), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(45, 60);
    $pdf->Cell(35, 5, utf8_decode($dataAmonestaciones->tipoAmonestacion),'B', 0, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(80, 60);
    $pdf->Cell(30, 5, utf8_decode("Reincidencia:"), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(110, 60);
    $pdf->Cell(96, 5, utf8_decode($dataAmonestaciones->flgReincidencia),'B', 0, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(10, 65);
    $pdf->Cell(55, 5, utf8_decode("Causa/motivo de sanción:"), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(65, 65);
    $pdf->Cell(141, 5, utf8_decode($dataAmonestaciones->causaFalta), 'B', 0, 'L');
    
    if ($dataAmonestaciones->causaFalta == 'Otros') {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(10, 70);
        $pdf->Cell(1, 5, utf8_decode(" "), $flgLineas, 1, 'L');
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(11, 70);
        $pdf->MultiCell(151, 5, utf8_decode($dataAmonestaciones->descripcionOtroCausa), $flgLineas, 'L');
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(10, 75);
        $pdf->Cell(194, 5, utf8_decode("___________________________________________________________________________________"), $flgLineas, 1, 'L');
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(10, 80);
        $pdf->Cell(194, 5, utf8_decode("___________________________________________________________________________________"), $flgLineas, 1, 'L');
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(10, 80);
        $pdf->Cell(194, 5, utf8_decode("___________________________________________________________________________________"), $flgLineas, 1, 'L');
    }

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(10, 95);
    $pdf->Cell(53, 5, utf8_decode("Descripción de la causa:"), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(63, 95);
    $pdf->Cell(143, 5, utf8_decode($dataAmonestaciones->descripcionFalta), 'B', 0, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(10, 100);
    $pdf->Cell(194, 5, utf8_decode("___________________________________________________________________________________"), $flgLineas, 1, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(10, 105);
    $pdf->Cell(194, 5, utf8_decode("___________________________________________________________________________________"), $flgLineas, 1, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(10, 110);
    $pdf->Cell(194, 5, utf8_decode("___________________________________________________________________________________"), $flgLineas, 1, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(10, 115);
    $pdf->Cell(30, 5, utf8_decode("Consecuencia:"), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(40, 115);
    $pdf->Cell(166, 5, utf8_decode($dataAmonestaciones->consecuenciaFalta), 'B', 0, 'L');
    
    // Verifica si consecuenciaFalta es "Destitución"
    if ($dataAmonestaciones->consecuenciaFalta == 'Destitución') {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(10, 120);
        $pdf->Cell(45, 5, utf8_decode("Días de suspensión:"), $flgLineas, 1, 'L');
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(55, 120);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(8, 5, utf8_decode("De:"), $flgLineas, 0, 'L');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 5, utf8_decode($dataAmonestaciones->fechaSuspensionInicio), 'B', 0, 'L');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(13, 5, utf8_decode("Hasta:"), $flgLineas, 0, 'L');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(41, 5, utf8_decode($dataAmonestaciones->fechaSuspensionFin), 'B', 0, 'L');
    }

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(10, 125);
    $pdf->Cell(50, 5, utf8_decode("Compromiso de mejora:"), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(60, 125);
    $pdf->MultiCell(146, 5, utf8_decode($dataAmonestaciones->compromisoMejora), $flgLineas,  'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(10, 130);
    $pdf->Cell(194, 5, utf8_decode("___________________________________________________________________________________"), $flgLineas, 1, 'L');
    
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(10, 135);
    $pdf->Cell(194, 5, utf8_decode("___________________________________________________________________________________"), $flgLineas, 1, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(10, 140);
    $pdf->Cell(194, 5, utf8_decode("___________________________________________________________________________________"), $flgLineas, 1, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(15, 195);
    $pdf->Cell(55, 5, utf8_decode("F. ______________________________ "), $flgLineas, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(120, 195);
    $pdf->Cell(55, 5, utf8_decode("F. ______________________________ "), $flgLineas, 1, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(25, 200);
    $pdf->Cell(55, 5, utf8_decode("Jefe inmediato"), $flgLineas, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(130, 200);
    $pdf->Cell(55, 5, utf8_decode("Colaborador"), $flgLineas, 0, 'C');
    
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(25, 205);
    $pdf->Cell(55, 5, utf8_decode($dataEmpleadosACargo->nombreCompletoJefe), $flgLineas, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(130, 205);
    $pdf->Cell(55, 5, utf8_decode($dataAmonestaciones->nombreCompleto), $flgLineas, 0, 'C');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(65, 230);
    $pdf->Cell(55, 5, utf8_decode("F. ______________________________ "), $flgLineas, 1, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(80, 235);
    $pdf->Cell(55, 5, utf8_decode("RRHH"), $flgLineas, 1, 'C');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(80, 240);
    $pdf->Cell(55, 5, utf8_decode("Óscar Antonio Ochoa Velásquez"), $flgLineas, 1, 'C');

    // Ejemplo manejando altura dinámica
 /*   $altura = 60;

    for($i = 0; $i < 4; $i++) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(10, $altura);
        $pdf->Cell(100, 5, utf8_decode("Empleado"), $flgLineas, 1, 'L');
        $altura += 5;
    }
*/

    /*
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(35, 50);
    $pdf->Cell(150, 6, utf8_decode("Empleado:"), 1, 1, 'C');
    */

    $pdf->Output(utf8_decode($outputReporte) . '.pdf', "I");
?>