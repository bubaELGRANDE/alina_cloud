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
            $this->Image('../../../../../libraries/resources/images/logos/alina-logo.png', 15, 8, 40);

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
    }

    /*
        REQUEST:
        selectFiltroSucursal
        filtroSucursalDepartamento
        selectSucursalDepartamentos
    */

    $sucursalId = base64_decode(urldecode($_REQUEST['selectFiltroSucursal']));
    $filtroSucursalDepartamento = base64_decode(urldecode($_REQUEST['filtroSucursalDepartamento']));
    $sucursalDepartamentoIds = (isset($_REQUEST['selectSucursalDepartamentos']) ? base64_decode(urldecode($_REQUEST['selectSucursalDepartamentos'])) : '');

    $dataSucursal = $cloud->row("
        SELECT sucursal FROM cat_sucursales
        WHERE sucursalId = ? AND flgDelete = ?
    ", [$sucursalId, 0]);

    $tituloReporte = "Nómina de empleados";
    $subtituloReporte = "Sucursal: $dataSucursal->sucursal";
    
    $outputReporte = "Nomina de empleados - $dataSucursal->sucursal";

    if($filtroSucursalDepartamento == "Todos") {
        $whereSucursalDepartamento = "";
        $subtituloReporte .= " - Todos los departamentos";
        $outputReporte .= " - Todos los departamentos";
    } else {
        $whereSucursalDepartamento = "AND sucursalDepartamentoId IN ($sucursalDepartamentoIds)";
        $subtituloReporte .= " - Departamentos específicos";
        $outputReporte .= " - Departamentos específicos";
    }

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    $pdf->SetTitle(utf8_decode($outputReporte));

    $dataNominaEmpleados = $cloud->rows("
        SELECT 
            personaId,
            nombreCompleto,
            fechaNacimiento,
            departamentoPaisActual,
            municipioPaisActual,
            cargoPersona,
            departamentoSucursal,
            fechaInicioLabores,
            DATE_FORMAT(fechaInicioLabores, '%d/%m%/%Y') AS fechaInicioLaboresFormat,
            fechaInicio AS fechaInicioCargoExpediente,
            DATE_FORMAT(fechaInicio, '%d/%m/%Y') AS fechaInicioCargoExpedienteFormat
        FROM view_expedientes
        WHERE sucursalId = ? AND estadoPersona = ? AND estadoExpediente = ? $whereSucursalDepartamento
        ORDER BY apellido1, apellido2, nombre1, nombre2
    ", [$sucursalId, 'Activo', 'Activo']);

    $altura = 35;
    $pdf->AddPage();
    $n = 0;
    $registroPorPagina = 1;

    foreach ($dataNominaEmpleados as $nominaEmpleado) {
        $n++;

        if($registroPorPagina > 5) {
            $pdf->AddPage();
            $altura = 35;
            $registroPorPagina = 1;
        } else {
            // Aún caben en la página
        }

        $dataFotoPerfil = $cloud->row("
            SELECT prsAdjuntoId, urlPrsAdjunto FROM th_personas_adjuntos
            WHERE personaId = ? AND tipoPrsAdjunto = ? AND descripcionPrsAdjunto = ? AND flgDelete = ?
            LIMIT 1
        ", [$nominaEmpleado->personaId, 'Foto de empleado', 'Actual', 0]);

        if($dataFotoPerfil) {
            $urlPrsAdjunto = $dataFotoPerfil->urlPrsAdjunto;
        } else {
            $urlPrsAdjunto = 'mi-perfil/user-default.jpg';
        }

        $alturaImagen = 40;
        $anchoImagen = $alturaImagen;

        if($n % 2 == 0) {
            $posicionX = 108;
        } else {
            $posicionX = 15;
        }

        // Margen card
        $pdf->SetXY($posicionX - 1, $altura - 1);
        $pdf->Cell(93, $alturaImagen + 2, "", 1, 1, "L");
        // url, X, Y, Weight, Height
        $pdf->Image('../../../../../libraries/resources/images/' . $urlPrsAdjunto, $posicionX, $altura, $anchoImagen, $alturaImagen);
        $pdf->SetFont("Arial", "B", 11);
        $pdf->SetXY($posicionX + $anchoImagen, $altura + 2);
        $pdf->MultiCell(93 - $anchoImagen - 1, 4, utf8_decode($nominaEmpleado->nombreCompleto), 0, "L");

        $alturaTextCard = $pdf->GetY();

        $calcularEdad = date_diff(date_create($nominaEmpleado->fechaNacimiento), date_create(date("Y-m-d")));
        $pdf->SetXY($posicionX + $anchoImagen, $alturaTextCard);
        $pdf->CellHTML(93 - $anchoImagen - 1, 5, utf8_decode("<b>Edad:</b> " . $calcularEdad->format("%y") . " años"));

        $alturaTextCard += 5;

        $pdf->SetFont("Arial", "B", 11);
        $pdf->SetXY($posicionX + $anchoImagen, $alturaTextCard);
        $pdf->Cell(14, 5, utf8_decode("Cargo:"), 0, 0, "L");
        $pdf->SetFont("Arial", "", 11);
        $pdf->SetXY($posicionX + 14 + $anchoImagen, $alturaTextCard + 0.5);
        $pdf->MultiCell(93 - 14 - $anchoImagen - 1, 4, utf8_decode($nominaEmpleado->cargoPersona), 0, "L");

        $alturaTextCard = $pdf->GetY();

        $pdf->SetXY($posicionX + $anchoImagen, $alturaTextCard);
        $pdf->CellHTML(93 - $anchoImagen - 1, 5, utf8_decode("<b>Depto.:</b> $nominaEmpleado->departamentoSucursal"));

        $alturaTextCard += 5;

        if($nominaEmpleado->fechaInicioCargoExpedienteFormat == $nominaEmpleado->fechaInicioLaboresFormat) {
            $fechaCargo = "-";
            $espaciosFecha = "";
        } else {
            $fechaCargo = $nominaEmpleado->fechaInicioCargoExpedienteFormat;
            $espaciosFecha = "  ";
        }

        $pdf->SetXY($posicionX + $anchoImagen, $alturaTextCard);
        $pdf->CellHTML(93 - $anchoImagen - 1, 5, utf8_decode("<b>Nuevo cargo:</b> $fechaCargo"));

        $alturaTextCard += 5;

        $pdf->SetXY($posicionX + $anchoImagen, $alturaTextCard);
        $pdf->CellHTML(93 - $anchoImagen - 1, 5, utf8_decode("<b>Antigüedad:</b>$espaciosFecha $nominaEmpleado->fechaInicioLaboresFormat"));

        $alturaTextCard += 6;

        if($n % 2 == 0) {
            $altura += ($alturaImagen + 2);
            $registroPorPagina++;
        } else {
            // Primer registro de la columna
        }
    }

    $pdf->Output($outputReporte . '.pdf', "I");
?>