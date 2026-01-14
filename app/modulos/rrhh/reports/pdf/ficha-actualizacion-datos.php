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
            //$this->Image('../../../../../libraries/resources/images/logos/alina-logo.png', 10, 8, 40);

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
            /*
            $this->SetXY(165, 8);
            $this->Cell(40, 4, date("d-m-Y H:i:s"), 0, 0, 'R');
            $this->SetXY(165, 12);
            $this->Cell(40, 4, utf8_decode($_SESSION['usuario']), 0, 0, 'R');
            */
        }

        // Page footer
        function Footer() {
            // Position at 1.5 cm from bottom
            $this->SetFont('Arial','',10);
            $this->SetXY(10, -15);
            $this->Cell(196, 5, utf8_decode("F. _______________________"), 0, 0, 'R');
            $this->SetFont('Arial','',9);
            $this->SetXY(10, -15);
            $this->MultiCell(150, 3, utf8_decode("La información anterior es solicitada con el fin de actualizar nuestros registros de Personal, y dar cumplimiento legal a requerimientos del Ministerio de Trabajo."), 0, 'L');
            //$this->Cell(0, 5, utf8_decode(''), 0, 0, 'L');
            // Numeración de página
            // Sin paginación porque son fichas independientes por empleado
            //$this->Cell(0, 5, utf8_decode('Página '.$this->PageNo().'/{nb}'), 0, 0, 'C');
        }
    }

    /*
        REQUEST:
        filtroEmpleados (radio)
        selectEmpleados (multiple)
    */

    $filtroEmpleados = base64_decode(urldecode($_REQUEST['filtroEmpleados']));
    $arrayEmpleadosId = (isset($_REQUEST['selectEmpleados']) ? base64_decode(urldecode($_REQUEST['selectEmpleados'])) : '');
    $estadoPersona = ($filtroEmpleados == "Inactivos" ? 'Inactivo' : 'Activo');

    $tituloReporte = 'Ficha de actualización de datos';
    $subtituloReporte = "";
    
    $outputReporte = 'Ficha de actualización de datos (' . $filtroEmpleados . ')';

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    $pdf->SetTitle(utf8_decode($outputReporte));

    if($filtroEmpleados == 'Todos') {
        // Todos los empleados
        $dataEmpleados = $cloud->rows("
            SELECT
                per.personaId AS personaId,
                CONCAT(
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-'),
                    ' ',
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-')
                ) AS nombreCompleto,
                per.numIdentidad AS numIdentidad, 
                per.fechaExpiracionIdentidad AS fechaExpiracionIdentidad,
                per.paisMunicipioIdActual AS paisMunicipioIdActual, 
                pmactual.municipioPais AS municipioActual,
                pdactual.paisDepartamentoId AS paisDepartamentoIdActual,
                pdactual.departamentoPais AS departamentoActual,
                per.zonaResidenciaActual AS zonaResidenciaActual, 
                per.tipoSangre AS tipoSangre,
                per.estadoCivil AS estadoCivil, 
                per.estadoPersona AS estadoPersona
            FROM th_personas per
            LEFT JOIN cat_paises pa ON pa.paisId = per.paisId
            LEFT JOIN cat_paises_municipios pmactual ON pmactual.paisMunicipioId = per.paisMunicipioIdActual
            LEFT JOIN cat_paises_departamentos pdactual ON pdactual.paisDepartamentoId = pmactual.paisDepartamentoId
            WHERE per.prsTipoId = ? AND per.estadoPersona = ? AND per.flgDelete = ?
            ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
        ", ['1', $estadoPersona, '0']);
    } else {
        // Empleados especificos
        $dataEmpleados = $cloud->rows("
            SELECT
                per.personaId AS personaId,
                CONCAT(
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-'),
                    ' ',
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-')
                ) AS nombreCompleto,
                per.numIdentidad AS numIdentidad, 
                per.fechaExpiracionIdentidad AS fechaExpiracionIdentidad,
                per.paisMunicipioIdActual AS paisMunicipioIdActual, 
                pmactual.municipioPais AS municipioActual,
                pdactual.paisDepartamentoId AS paisDepartamentoIdActual,
                pdactual.departamentoPais AS departamentoActual,
                per.zonaResidenciaActual AS zonaResidenciaActual, 
                per.tipoSangre AS tipoSangre,
                per.estadoCivil AS estadoCivil, 
                per.estadoPersona AS estadoPersona
            FROM th_personas per
            LEFT JOIN cat_paises pa ON pa.paisId = per.paisId
            LEFT JOIN cat_paises_municipios pmactual ON pmactual.paisMunicipioId = per.paisMunicipioIdActual
            LEFT JOIN cat_paises_departamentos pdactual ON pdactual.paisDepartamentoId = pmactual.paisDepartamentoId
            WHERE per.prsTipoId = ? AND per.estadoPersona = ? AND per.personaId IN ($arrayEmpleadosId) AND per.flgDelete = ?
            ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
        ", ['1', $estadoPersona, '0']);
    }

    $flgShowLineas = 0;

    foreach($dataEmpleados as $empleado) {
        $pdf->AddPage();

        $altura = 25;
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY(10, $altura);
        $pdf->MultiCell(196, 4, utf8_decode("La información detallada en este documento corresponde a la actualización de datos del personal 2024. En caso de modificaciones, favor de informarlas y registrarlas en este mismo formato."), $flgShowLineas, 'L');
        $altura += 10;

        //$pdf->SetXY(10, $altura);
        //$pdf->MultiCell(196, 4, utf8_decode("Dado el carácter relevante de esta información, le solicitamos que complete este formato utilizando una letra legible."), $flgShowLineas, 'L');
        //$altura += 6;

        // Nombre completo
            $pdf->SetXY(10, $altura);
            $pdf->Cell(196, 9, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(196, 4, utf8_decode("Nombre completo"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(10, $altura + 4);
            $pdf->Cell(196, 5, utf8_decode($empleado->nombreCompleto), $flgShowLineas, 0, 'L');
            $altura += 9;

        // Teléfono, Celular, Correo
            $pdf->SetXY(10, $altura);
            $pdf->Cell(66, 9, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(66, 4, utf8_decode("Teléfono"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            // No mostrar teléfono del empleado
            $pdf->SetXY(10, $altura + 4);
            $pdf->Cell(66, 5, utf8_decode(""), $flgShowLineas, 0, 'L');

            $pdf->SetXY(76, $altura);
            $pdf->Cell(65, 9, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(76, $altura);
            $pdf->Cell(65, 4, utf8_decode("Celular"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            // No mostrar celular del empleado
            $pdf->SetXY(76, $altura + 4);
            $pdf->Cell(65, 5, utf8_decode(""), $flgShowLineas, 0, 'L');


            $dataCorreoInstitucional = $cloud->row("
                SELECT contactoPersona FROM th_personas_contacto
                WHERE personaId = ? AND tipoContactoId = ? AND estadoContacto = ? AND flgDelete = ?
            ", [$empleado->personaId, 1, 'Activo', 0]);

            if($dataCorreoInstitucional) {
                $correoInstitucional = $dataCorreoInstitucional->contactoPersona;
            } else { 
                $correoInstitucional = "";
            }

            $pdf->SetXY(141, $altura);
            $pdf->Cell(65, 9, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(141, $altura);
            $pdf->Cell(65, 4, utf8_decode("Correo institucional"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(141, $altura + 4);
            $pdf->Cell(65, 5, utf8_decode($correoInstitucional), $flgShowLineas, 0, 'L');
            $altura += 9;

        // Dirección de residencia actual
            $pdf->SetXY(10, $altura);
            $pdf->Cell(66, 9, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(66, 4, utf8_decode("Departamento de residencia actual"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(10, $altura + 4);
            $pdf->Cell(66, 5, utf8_decode($empleado->departamentoActual), $flgShowLineas, 0, 'L');

            $pdf->SetXY(10, $altura + 9);
            $pdf->Cell(66, 9, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura + 9);
            $pdf->Cell(66, 4, utf8_decode("Municipio de residencia actual"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(10, $altura + 9 + 4);
            $pdf->Cell(66, 5, utf8_decode($empleado->municipioActual), $flgShowLineas, 0, 'L');

            $pdf->SetXY(76, $altura);
            $pdf->Cell(130, 18, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(76, $altura);
            $pdf->Cell(130, 4, utf8_decode("Dirección de residencia actual"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(76, $altura + 4);
            $pdf->MultiCell(130, 4, utf8_decode($empleado->zonaResidenciaActual), 0, 'L');
            $altura += 18;

        // Documento único de identidad
            $pdf->SetXY(10, $altura);
            $pdf->Cell(66, 9, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(66, 4, utf8_decode("Documento Único de Identidad (DUI)"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(10, $altura + 4);
            $pdf->Cell(66, 5, utf8_decode($empleado->numIdentidad), $flgShowLineas, 0, 'L');

            $pdf->SetXY(76, $altura);
            $pdf->Cell(65, 9, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(76, $altura);
            $pdf->Cell(65, 4, utf8_decode("Fecha de vencimiento (DUI)"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(76, $altura + 4);
            $pdf->Cell(65, 5, utf8_decode($empleado->fechaExpiracionIdentidad), $flgShowLineas, 0, 'L');

            $pdf->SetXY(141, $altura);
            $pdf->Cell(65, 9, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(141, $altura);
            $pdf->Cell(65, 4, utf8_decode("Tipo de sangre"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(141, $altura + 4);
            $pdf->Cell(65, 5, utf8_decode($empleado->tipoSangre), $flgShowLineas, 0, 'L');
            $altura += 9;

        // Licencia de conducir
            $pdf->SetXY(10, $altura);
            $pdf->Cell(66, 14, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(66, 4, utf8_decode("Licencia de conducir"), $flgShowLineas, 0, 'C');

            // Verificar Licencia de Moto
            $dataLicenciaMotocicleta = $cloud->row("
                SELECT tipoLicencia, numLicencia, fechaExpiracionLicencia FROM th_personas_licencias
                WHERE categoriaLicencia = ? AND tipoLicencia = ? AND personaId = ? AND flgDelete = ?
                ORDER BY fhAdd DESC
                LIMIT 1
            ", ['Conducir', 'Licencia Motociclistas', $empleado->personaId, 0]);

            if($dataLicenciaMotocicleta) {
                $checkLicenciaMoto = "X";
                $numLicenciaMoto = $dataLicenciaMotocicleta->numLicencia;
                $tipoLicenciaPrincipal = $dataLicenciaMotocicleta->tipoLicencia;
                $fechaExpiracionLicencia = $dataLicenciaMotocicleta->fechaExpiracionLicencia;
            } else {
                $checkLicenciaMoto = "";
                $numLicenciaMoto = "";
                $tipoLicenciaPrincipal = "";
                $fechaExpiracionLicencia = "";
            }

            // Verificar Licencia de Auto
            $dataLicenciaAutomovil = $cloud->row("
                SELECT tipoLicencia, numLicencia, fechaExpiracionLicencia FROM th_personas_licencias
                WHERE categoriaLicencia = ? AND tipoLicencia <> ? AND personaId = ? AND flgDelete = ?
                ORDER BY fhAdd DESC
                LIMIT 1
            ", ['Conducir', 'Licencia Motociclistas', $empleado->personaId, 0]);

            if($dataLicenciaAutomovil) {
                $checkLicenciaAutomovil = "X";
                $numLicenciaAutomovil = $dataLicenciaAutomovil->numLicencia;
                $tipoLicenciaPrincipal = $dataLicenciaAutomovil->tipoLicencia;
                $fechaExpiracionLicencia = $dataLicenciaAutomovil->fechaExpiracionLicencia;
            } else {
                $checkLicenciaAutomovil = "";
                $numLicenciaAutomovil = "";
                $tipoLicenciaPrincipal = "";
                $fechaExpiracionLicencia = "";
            }

            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(12, $altura + 4);
            $pdf->Cell(5, 4, utf8_decode($checkLicenciaMoto), 1, 1, 'C');
            $pdf->SetXY(18, $altura + 4);
            $pdf->Cell(66, 4, utf8_decode("Motocicleta"), $flgShowLineas, 0, 'L');
            $pdf->SetXY(10, $altura + 4);
            $pdf->Cell(66, 4, utf8_decode($numLicenciaMoto), $flgShowLineas, 0, 'R');

            $pdf->SetXY(12, $altura + 9);
            $pdf->Cell(5, 4, utf8_decode($checkLicenciaAutomovil), 1, 1, 'C');
            $pdf->SetXY(18, $altura + 9);
            $pdf->Cell(66, 4, utf8_decode("Automóvil"), $flgShowLineas, 0, 'L');
            $pdf->SetXY(10, $altura + 9);
            $pdf->Cell(66, 4, utf8_decode($numLicenciaAutomovil), $flgShowLineas, 0, 'R');

            $pdf->SetXY(76, $altura);
            $pdf->Cell(65, 14, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(76, $altura);
            $pdf->Cell(65, 4, utf8_decode("Tipo de licencia"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(76, $altura + 4);
            $pdf->Cell(65, 5, utf8_decode($tipoLicenciaPrincipal), $flgShowLineas, 0, 'L');

            $pdf->SetXY(141, $altura);
            $pdf->Cell(65, 14, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(141, $altura);
            $pdf->Cell(65, 4, utf8_decode("Fecha de vencimiento (Licencia)"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(141, $altura + 4);
            $pdf->Cell(65, 5, utf8_decode($fechaExpiracionLicencia), $flgShowLineas, 0, 'L');
            $altura += 14;

        // Estado civil
            /*
                [0] = Soltero/a
                [1] = Casado/a
                [2] = Acompañado/a
                [3] = Viudo/a
            */
            $arrayCheckEstadosCiviles = array("", "", "", "");

            if($empleado->estadoCivil == "Soltero/a") {
                $arrayCheckEstadosCiviles[0] = "X";
            } else if($empleado->estadoCivil == "Casado") {
                $arrayCheckEstadosCiviles[1] = "X";
            } else if($empleado->estadoCivil == "Acompañado/a") {
                $arrayCheckEstadosCiviles[2] = "X";
            } else if($empleado->estadoCivil == "Viudo/a") {
                $arrayCheckEstadosCiviles[3] = "X";
            } else {
                // Divorciado, que por alguna razón no se reflejó en este formato
            }

            $pdf->SetXY(10, $altura);
            $pdf->Cell(196, 10, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(196, 4, utf8_decode("Estado civil"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(12, $altura + 5);
            $pdf->Cell(5, 4, utf8_decode($arrayCheckEstadosCiviles[0]), 1, 1, 'C');
            $pdf->SetXY(18, $altura + 5);
            $pdf->Cell(41, 4, utf8_decode("Soltero(a)"), $flgShowLineas, 0, 'L');

            $pdf->SetXY(61, $altura + 5);
            $pdf->Cell(5, 4, utf8_decode($arrayCheckEstadosCiviles[1]), 1, 1, 'C');
            $pdf->SetXY(67, $altura + 5);
            $pdf->Cell(41, 4, utf8_decode("Casado(a)"), $flgShowLineas, 0, 'L');

            $pdf->SetXY(110, $altura + 5);
            $pdf->Cell(5, 4, utf8_decode($arrayCheckEstadosCiviles[2]), 1, 1, 'C');
            $pdf->SetXY(116, $altura + 5);
            $pdf->Cell(41, 4, utf8_decode("Acompañado(a)"), $flgShowLineas, 0, 'L');

            $pdf->SetXY(159, $altura + 5);
            $pdf->Cell(5, 4, utf8_decode($arrayCheckEstadosCiviles[3]), 1, 1, 'C');
            $pdf->SetXY(164, $altura + 5);
            $pdf->Cell(41, 4, utf8_decode("Viudo(a)"), $flgShowLineas, 0, 'L');
            $altura += 10;

        // Nombre del Cónyuge
            $pdf->SetXY(10, $altura);
            $pdf->Cell(115, 9, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(115, 4, utf8_decode("Nombre completo del Cónyuge"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            // No mostrar nombre del cónyuge
            $pdf->SetXY(10, $altura + 4);
            $pdf->Cell(115, 5, utf8_decode(""), $flgShowLineas, 0, 'L');

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(125, $altura);
            $pdf->Cell(51, 4, utf8_decode("Fecha de nacimiento (Cónyuge)"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            // No mostrar fecha de nacimiento
            $pdf->SetXY(125, $altura + 4);
            $pdf->Cell(51, 5, utf8_decode(""), $flgShowLineas, 0, 'L');

            $pdf->SetXY(176, $altura);
            $pdf->Cell(30, 9, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(176, $altura);
            $pdf->Cell(30, 4, utf8_decode("Número de Hijos"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            // No mostrar número de hijos
            $pdf->SetXY(176, $altura + 4);
            $pdf->Cell(30, 5, utf8_decode(""), $flgShowLineas, 0, 'L');
            $altura += 9;

        // En caso de Emergencia
            $pdf->SetXY(10, $altura);
            $pdf->Cell(131, 14, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(131, 4, utf8_decode("En caso de Emergencia"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura + 4);
            $pdf->Cell(131, 4, utf8_decode("Avisar a:"), $flgShowLineas, 0, 'L');
            // No mostrar nombre de emergencia
            $pdf->SetXY(10, $altura + 4);
            $pdf->Cell(131, 4, utf8_decode(""), $flgShowLineas, 0, 'R');

            $pdf->SetXY(10, $altura + 9);
            $pdf->Cell(131, 4, utf8_decode("Parentesco:"), $flgShowLineas, 0, 'L');
            // No mostrar parentesco de emergencia
            $pdf->SetXY(10, $altura + 9);
            $pdf->Cell(131, 4, utf8_decode(""), $flgShowLineas, 0, 'R');

            $pdf->SetXY(141, $altura);
            $pdf->Cell(65, 14, '', 1, 1, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(141, $altura);
            $pdf->Cell(65, 4, utf8_decode("Número de contacto (Emergencia)"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            // No mostrar número de emergencia
            $pdf->SetXY(141, $altura + 4);
            $pdf->Cell(65, 5, utf8_decode(""), $flgShowLineas, 0, 'L');
            $altura += 16;

        // Niveles académicos título
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(196, 4, utf8_decode("Niveles académicos obtenidos"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetXY(10, $altura + 4);
            $pdf->MultiCell(196, 4, utf8_decode("Si esta información ha cambiado en el último año, le pedimos registrarla en este formato y anexar una copia del título obtenido, o entregarlo posteriormente a Recursos Humanos."), $flgShowLineas, 'L');
            $altura += 14;

        // Cuadro niveles académicos
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(49, 4, utf8_decode("Nivel"), 1, 0, 'C');
            $pdf->SetXY(59, $altura);
            $pdf->Cell(49, 4, utf8_decode("Título obtenido"), 1, 0, 'C');
            $pdf->SetXY(108, $altura);
            $pdf->Cell(49, 4, utf8_decode("Lugar de estudio"), 1, 0, 'C');
            $pdf->SetXY(157, $altura);
            $pdf->Cell(49, 4, utf8_decode("Periodo"), 1, 0, 'C');
            $altura += 4;

        // Niveles académicos
            $arrayNivelesEstudioBD = array("Educación media","Técnico/Profesional","Universidad","Postgrado", "Otros");
            $arrayNivelesAcademicos = array("Bachillerato", "Técnico", "Universitario", "Postgrado", "Otros");
            $whereNotINEstudios = "AND nivelEstudio NOT IN (";
            for ($i=0; $i < 5; $i++) { 
                $checkNivelAcademico = "";

                if($i < 4) {
                    $dataNivelAcademico = $cloud->row("
                        SELECT 
                            nivelEstudio,
                            nombreCarrera,
                            centroEstudio,
                            mesInicio, 
                            anioInicio,
                            mesFinalizacion,
                            anioFinalizacion,
                            estadoEstudio
                        FROM th_personas_educacion
                        WHERE nivelEstudio = ? AND personaId = ? AND flgDelete = ?
                        ORDER BY fhAdd DESC
                        LIMIT 1
                    ", [$arrayNivelesEstudioBD[$i], $empleado->personaId, 0]);
                } else {
                    // Es "Otros" y este lleva más validaciones por Diplomado, Curso, etc
                    // Omitir todos los anteriores, para encontrar "Otros"
                    $dataNivelAcademico = $cloud->row("
                        SELECT 
                            nivelEstudio,
                            nombreCarrera,
                            centroEstudio,
                            mesInicio, 
                            anioInicio,
                            mesFinalizacion,
                            anioFinalizacion,
                            estadoEstudio
                        FROM th_personas_educacion
                        WHERE personaId = ? AND flgDelete = ? AND nivelEstudio NOT IN ('Educación media', 'Técnico/Profesional', 'Universidad' ,'Postgrado')
                        ORDER BY fhAdd DESC
                        LIMIT 1
                    ", [$empleado->personaId, 0]);
                }

                if($dataNivelAcademico) {
                    $checkNivelAcademico = "X";

                    if($dataNivelAcademico->nombreCarrera == "" || is_null($dataNivelAcademico->nombreCarrera)) {
                        $tituloEstudio = $dataNivelAcademico->nivelEstudio;
                    } else {
                        $tituloEstudio = $dataNivelAcademico->nombreCarrera;
                    }

                    $lugarEstudio = $dataNivelAcademico->centroEstudio;

                    if($dataNivelAcademico->estadoEstudio == "Cursando" || $dataNivelAcademico->estadoEstudio == "Incompleto") {
                        $estadoFinalizacionEstudio = ' - ' . $dataNivelAcademico->estadoEstudio;
                    } else {
                        $estadoFinalizacionEstudio = ' hasta ' . mb_strtolower($dataNivelAcademico->mesFinalizacion) . ' - ' . $dataNivelAcademico->anioFinalizacion;
                    }
                    $periodoEstudio = "De " . mb_strtolower($dataNivelAcademico->mesInicio) . " - " . $dataNivelAcademico->anioInicio . $estadoFinalizacionEstudio;
                } else {
                    $checkNivelAcademico = "";
                    $tituloEstudio = "";
                    $lugarEstudio = "";
                    $periodoEstudio = "";
                }

                $pdf->SetFont('Arial', '', 9);
                $pdf->SetXY(10, $altura);
                $pdf->Cell(49, 6, '', 1, 0, 'L');
                $pdf->SetXY(12, $altura + 1);
                $pdf->Cell(5, 4, utf8_decode($checkNivelAcademico), 1, 1, 'C');
                $pdf->SetXY(18, $altura);
                $pdf->Cell(41, 6, utf8_decode($arrayNivelesAcademicos[$i]), 0, 0, 'L');

                $pdf->SetFont('Arial', '', 7.5);
                $pdf->SetXY(59, $altura);
                $pdf->Cell(49, 6, '', 1, 0, 'L');
                $pdf->SetXY(59, $altura + 0.5);
                $pdf->MultiCell(49, 2.75, utf8_decode($tituloEstudio), 0, 'L');
                $pdf->SetXY(108, $altura);
                $pdf->Cell(49, 6, '', 1, 0, 'L');
                $pdf->SetXY(108, $altura + 0.5);
                $pdf->MultiCell(49, 2.75, utf8_decode($lugarEstudio), 0, 'L');
                $pdf->SetXY(157, $altura);
                $pdf->Cell(49, 6, '', 1, 0, 'L');
                $pdf->SetXY(157, $altura + 0.5);
                $pdf->MultiCell(49, 2.75, utf8_decode($periodoEstudio), 0, 'L');
                $altura += 6;
            }
            $altura += 2;

        // Personas que dependan título
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(196, 4, utf8_decode("Personas que dependan económicamente del Trabajador"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetXY(10, $altura + 4); 
            $pdf->MultiCell(196, 4, utf8_decode('- Información requerida por el Ministerio de Trabajo.'), $flgShowLineas, 'L');
            $pdf->SetXY(10, $altura + 8);
            $pdf->MultiCell(196, 4, utf8_decode('- Si su respuesta es "No", especificar el lugar de domicilio de la persona.'), $flgShowLineas, 'L');
            $altura += 14;

        // Cuadro personas que dependan
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(60, 8, utf8_decode("Nombre completo"), 1, 0, 'C');
            $pdf->SetXY(70, $altura);
            $pdf->Cell(35, 8, utf8_decode("Parentesco"), 1, 0, 'C');
            $pdf->SetXY(105, $altura);
            $pdf->Cell(30, 8, '', 1, 0, 'C');
            $pdf->SetXY(105, $altura - 2);
            $pdf->Cell(30, 8, utf8_decode("Fecha de"), 0, 0, 'C');
            $pdf->SetXY(105, $altura + 2);
            $pdf->Cell(30, 8, utf8_decode("nacimiento"), 0, 0, 'C');
            $pdf->SetXY(135, $altura);
            $pdf->Cell(24, 8, '', 1, 0, 'C');
            $pdf->SetXY(135, $altura - 2);
            $pdf->Cell(24, 8, utf8_decode("Vive con usted"), 0, 0, 'C');
            $pdf->SetXY(135, $altura + 4);
            $pdf->Cell(12, 4, utf8_decode("Sí"), 1, 0, 'C');
            $pdf->SetXY(147, $altura + 4);
            $pdf->Cell(12, 4, utf8_decode("No"), 1, 0, 'C');
            $pdf->SetXY(159, $altura);
            $pdf->Cell(47, 8, utf8_decode("Dirección"), 1, 0, 'C');
            $altura += 8;

        // Personas que dependan
            $pdf->SetFont('Arial', '', 9);
            for ($i=0; $i < 5; $i++) { 
                // No mostrar personas que dependen económicamente
                $pdf->SetXY(10, $altura);
                $pdf->Cell(60, 6, utf8_decode(""), 1, 0, 'L');
                $pdf->SetXY(70, $altura);
                $pdf->Cell(35, 6, utf8_decode(""), 1, 0, 'L');
                $pdf->SetXY(105, $altura);
                $pdf->Cell(30, 6, utf8_decode(""), 1, 0, 'L');
                $pdf->SetXY(135, $altura);
                $pdf->Cell(24, 6, '', 1, 0, 'C');
                $pdf->SetXY(135, $altura);
                $pdf->Cell(12, 6, '', 1, 0, 'C');
                $pdf->SetXY(147, $altura);
                $pdf->Cell(12, 6, '', 1, 0, 'C');
                $pdf->SetXY(159, $altura);
                $pdf->Cell(47, 6, utf8_decode(""), 1, 0, 'L');
                $altura += 6;
            }
            $altura += 2;

        // Condiciones Médicas título
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(196, 4, utf8_decode("Condiciones médicas"), $flgShowLineas, 0, 'C');
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetXY(10, $altura + 4); 
            $pdf->MultiCell(196, 4, utf8_decode('Si presenta alguna de estas condiciones o cualquier otra que considere relevante para su seguridad en el trabajo, por favor marque la opción correspondiente o seleccione "Otros" para especificar.'), $flgShowLineas, 'L');
            $altura += 14;

        // Condiciones Médicas
            $pdf->SetFont('Arial', '', 9);
            $arrayCondicionesMedicas = array("Diabetes Mellitus", "Hipertensión Arterial", "Asma o Enfermedades respiratorias", "Enfermedades cardiovasculares");
            $x = 10;
            for ($i=0; $i < 4; $i++) { 
                $pdf->SetXY($x, $altura);
                $pdf->Cell(49, 6, '', 0, 0, 'L');
                $x += 2;
                $pdf->SetXY($x, $altura + 1);
                $pdf->Cell(5, 4, '', 1, 1, 'C');
                $x += 6;
                $pdf->SetXY($x, $altura + 1);
                $pdf->MultiCell(41, 3, utf8_decode($arrayCondicionesMedicas[$i]), 0, 'L');
                $x += 41;
            }
            $altura += 6;

            $pdf->SetXY(10, $altura);
            $pdf->Cell(49, 6, '', 0, 0, 'L');
            $pdf->SetXY(12, $altura + 1);
            $pdf->Cell(5, 4, '', 1, 1, 'C');
            $pdf->SetXY(18, $altura);
            $pdf->Cell(41, 6, utf8_decode("Otros:"), 0, 0, 'L');

            $pdf->SetXY(30, $altura);
            $pdf->Cell(176, 6, '', 'B', 0, 'L');
    }

    $pdf->Output(utf8_decode($outputReporte) . '.pdf', "I");

    function reducirMesAnio($mesAnio) {
        if($mesAnio == '') {
            $fechaReducida = '--/--';
        } else {
            $arrayMesAnio = explode("-", $mesAnio);
            $fechaReducida = $arrayMesAnio[0] . "/" . substr($arrayMesAnio[1], 2, 4);
        }
        return $fechaReducida;
    }

    function diferenciaFechas($fechaInicio,$fechaFin) {
        $diff = '';
        $diferencia = ($fechaFin - $fechaInicio)/60/60/24;
        if($diferencia == 0) {
            $diff = 'Ninguno';
        } else if($diferencia == 1) {
            $diff = '1 día';
        } else if($diferencia < 31) {
            if($diferencia < 7) {
                $diff = $diferencia . ' días';
            } else if($diferencia < 14) {
                $diff = '1 semana';
            } else if($diferencia < 21) {
                $diff = '2 semanas';
            } else {
                $diff = '3 semanas';
            }
        } else if($diferencia < 365) {
            if($diferencia < 62) {
                $diff = '1 mes';
            } else {
                $diff = round($diferencia / 31) . ' meses';
            }
        } else {
            if($diferencia < 730) {
                $diff = '1 año';
            } else {
                $diff = round($diferencia / 365) . ' años';
            }
        }

        return $diff;
    }
?>