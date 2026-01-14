<?php 
    ini_set('memory_limit', '-1');
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    require_once('../../../../../libraries/packages/php/vendor/fpdf/fpdf.php');
    include('../../../../../libraries/includes/logic/functions/funciones-generales.php');

    /*
        REQUEST:
        selectFiltroExpediente (select)
        filtroEmpleados (radio)
        selectEmpleados (multiple)
        selectApoderadoLegal (select)
        fechaContrato
        salarioContrato
    */

    $filtroEmpleados = (isset($_REQUEST['selectEmpleados']) ? base64_decode(urldecode($_REQUEST['filtroEmpleados'])) : 'Simple');
    if($filtroEmpleados == "Simple") {
        $arrayExpedientesId = base64_decode(urldecode($_REQUEST['selectEmpleadoExpediente']));
    } else {
        $arrayExpedientesId = (isset($_REQUEST['selectEmpleados']) ? base64_decode(urldecode($_REQUEST['selectEmpleados'])) : '');
    }
    $estadoPersona = ($filtroEmpleados == "Inactivos" ? 'Inactivo' : 'Activo');

    // Contrato
    $apoderadoLegal = base64_decode(urldecode($_REQUEST['selectApoderadoLegal']));
    $fechaContrato = base64_decode(urldecode($_REQUEST['fechaContrato']));
    
    $outputReporte = 'Contratos de trabajo (' . date("d-m-Y", strtotime($fechaContrato)) . ')';

    $arrayFechaContrato = explode("-", $fechaContrato);

    $whereEstadoExpediente = '';
    if($filtroEmpleados == "Simple") {
        $whereEstadoExpediente = "";
    } else {        
        if($estadoPersona == 'Activo') {
            $whereEstadoExpediente = "AND pers.estadoPersona = 'Activo' AND exp.estadoExpediente = 'Activo'";
        } else {
            // Inactivos, para expedientes son: Despido, Renuncia, Abandono, Defunción, Traslado, Jubilado
            // Indicados en empleadoDarDeBaja
            $whereEstadoExpediente = "AND pers.estadoPersona = 'Inactivo' AND (exp.estadoExpediente = 'Despido' OR exp.estadoExpediente = 'Renuncia' OR exp.estadoExpediente = 'Abandono' OR exp.estadoExpediente = 'Defunción' OR exp.estadoExpediente = 'Traslado' OR exp.estadoExpediente = 'Jubilado')";
        }
    }

    $pdf = new FPDF('P','mm','Letter');
    $pdf->AliasNbPages();
    $pdf->SetTitle($outputReporte);

    if($filtroEmpleados == 'Todos') {
        // Todos los empleados
        $dataExpedientes = $cloud->rows("
            SELECT
                exp.prsExpedienteId AS prsExpedienteId,
                CONCAT(
                    TRIM(pers.nombre1), ' ', TRIM(pers.nombre2), ' ', 
                    TRIM(pers.apellido1), ' ', TRIM(pers.apellido2)
                ) AS nombreCompleto,
                pers.docIdentidad AS docIdentidad,
                pers.numIdentidad AS numIdentidad,
                expedimuni.paisDepartamentoId AS paisDepartamentoIdExpedicion,
                expedidepto.departamentoPais AS departamentoExpedicion,
                pers.paisMunicipioIdExpedicion AS paisMunicipioIdExpedicion,
                expedimuni.municipioPais AS municipioExpedicion,
                pers.fechaExpedicionIdentidad AS fechaExpedicionIdentidad,
                pers.fechaNacimiento AS fechaNacimiento,
                pers.sexo AS sexo,
                pers.estadoCivil AS estadoCivil,
                pers.paisId AS paisId,
                pa.pais AS nacionalidad,
                pa.iconBandera AS iconBandera,
                pers.paisMunicipioIdDUI AS paisMunicipioIdDUI, 
                pmdui.municipioPais AS municipioDUI,
                pddui.paisDepartamentoId AS paisDepartamentoIdDUI,
                pddui.departamentoPais AS departamentoDUI,
                pers.zonaResidenciaDUI AS zonaResidenciaDUI, 
                pers.paisMunicipioIdActual AS paisMunicipioIdActual, 
                pmactual.municipioPais AS municipioActual,
                pdactual.paisDepartamentoId AS paisDepartamentoIdActual,
                pdactual.departamentoPais AS departamentoActual,
                pers.zonaResidenciaActual AS zonaResidenciaActual,
                car.cargoPersona as cargoPersona,
                car.funcionCargoPersona AS funcionCargoPersona,
                car.herramientasCargoPersona AS herramientasCargoPersona,
                exp.tipoContrato AS tipoContrato,
                pers.fechaInicioLabores AS fechaInicioLabores,
                exp.fechaInicio AS fechaInicioContrato
            FROM th_expediente_personas exp
            JOIN th_personas pers ON pers.personaId = exp.personaId
            JOIN cat_paises pa ON pa.paisId = pers.paisId
            JOIN cat_paises_municipios pmdui ON pmdui.paisMunicipioId = pers.paisMunicipioIdDUI
            JOIN cat_paises_departamentos pddui ON pddui.paisDepartamentoId = pmdui.paisDepartamentoId
            JOIN cat_paises_municipios pmactual ON pmactual.paisMunicipioId = pers.paisMunicipioIdActual
            JOIN cat_paises_departamentos pdactual ON pdactual.paisDepartamentoId = pmactual.paisDepartamentoId
            JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
            LEFT JOIN cat_paises_municipios expedimuni ON expedimuni.paisMunicipioId = pers.paisMunicipioIdExpedicion
            LEFT JOIN cat_paises_departamentos expedidepto ON expedidepto.paisDepartamentoId = expedimuni.paisDepartamentoId
            WHERE exp.flgDelete = ? $whereEstadoExpediente
        ", [0]);
    } else {
        // Empleados especificos         
        $dataExpedientes = $cloud->rows("
            SELECT
                exp.prsExpedienteId AS prsExpedienteId,
                CONCAT(
                    TRIM(pers.nombre1), ' ', TRIM(pers.nombre2), ' ', 
                    TRIM(pers.apellido1), ' ', TRIM(pers.apellido2)
                ) AS nombreCompleto,
                pers.docIdentidad AS docIdentidad,
                pers.numIdentidad AS numIdentidad,
                expedimuni.paisDepartamentoId AS paisDepartamentoIdExpedicion,
                expedidepto.departamentoPais AS departamentoExpedicion,
                pers.paisMunicipioIdExpedicion AS paisMunicipioIdExpedicion,
                expedimuni.municipioPais AS municipioExpedicion,
                pers.fechaExpedicionIdentidad AS fechaExpedicionIdentidad,
                pers.fechaNacimiento AS fechaNacimiento,
                pers.sexo AS sexo,
                pers.estadoCivil AS estadoCivil,
                pers.paisId AS paisId,
                pa.pais AS nacionalidad,
                pa.iconBandera AS iconBandera,
                pers.paisMunicipioIdDUI AS paisMunicipioIdDUI, 
                pmdui.municipioPais AS municipioDUI,
                pddui.paisDepartamentoId AS paisDepartamentoIdDUI,
                pddui.departamentoPais AS departamentoDUI,
                pers.zonaResidenciaDUI AS zonaResidenciaDUI, 
                pers.paisMunicipioIdActual AS paisMunicipioIdActual, 
                pmactual.municipioPais AS municipioActual,
                pdactual.paisDepartamentoId AS paisDepartamentoIdActual,
                pdactual.departamentoPais AS departamentoActual,
                pers.zonaResidenciaActual AS zonaResidenciaActual,
                car.cargoPersona as cargoPersona,
                car.funcionCargoPersona AS funcionCargoPersona,
                car.herramientasCargoPersona AS herramientasCargoPersona,
                exp.tipoContrato AS tipoContrato,
                pers.fechaInicioLabores AS fechaInicioLabores,
                exp.fechaInicio AS fechaInicioContrato
            FROM th_expediente_personas exp
            JOIN th_personas pers ON pers.personaId = exp.personaId
            JOIN cat_paises pa ON pa.paisId = pers.paisId
            JOIN cat_paises_municipios pmdui ON pmdui.paisMunicipioId = pers.paisMunicipioIdDUI
            JOIN cat_paises_departamentos pddui ON pddui.paisDepartamentoId = pmdui.paisDepartamentoId
            JOIN cat_paises_municipios pmactual ON pmactual.paisMunicipioId = pers.paisMunicipioIdActual
            JOIN cat_paises_departamentos pdactual ON pdactual.paisDepartamentoId = pmactual.paisDepartamentoId
            JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
            LEFT JOIN cat_paises_municipios expedimuni ON expedimuni.paisMunicipioId = pers.paisMunicipioIdExpedicion
            LEFT JOIN cat_paises_departamentos expedidepto ON expedidepto.paisDepartamentoId = expedimuni.paisDepartamentoId
            WHERE exp.flgDelete = ? AND exp.prsExpedienteId IN ($arrayExpedientesId) $whereEstadoExpediente
        ", [0]);
    }

    $dataContratantePatronal = $cloud->row("
        SELECT
            CONCAT(
                TRIM(pers.nombre1), ' ', TRIM(pers.nombre2), ' ', 
                TRIM(pers.apellido1), ' ', TRIM(pers.apellido2)
            ) AS nombreCompleto,
            pers.numIdentidad AS numIdentidad,
            expedimuni.paisDepartamentoId AS paisDepartamentoIdExpedicion,
            expedidepto.departamentoPais AS departamentoExpedicion,
            pers.paisMunicipioIdExpedicion AS paisMunicipioIdExpedicion,
            expedimuni.municipioPais AS municipioExpedicion,
            pers.fechaExpedicionIdentidad AS fechaExpedicionIdentidad,
            pers.fechaNacimiento AS fechaNacimiento,
            pers.sexo AS sexo,
            pers.estadoCivil AS estadoCivil,
            pers.paisId AS paisId,
            pa.pais AS nacionalidad,
            pa.iconBandera AS iconBandera,
            pers.paisMunicipioIdDUI AS paisMunicipioIdDUI, 
            pmdui.municipioPais AS municipioDUI,
            pddui.paisDepartamentoId AS paisDepartamentoIdDUI,
            pddui.departamentoPais AS departamentoDUI,
            pers.zonaResidenciaDUI AS zonaResidenciaDUI, 
            pers.paisMunicipioIdActual AS paisMunicipioIdActual, 
            pmactual.municipioPais AS municipioActual,
            pdactual.paisDepartamentoId AS paisDepartamentoIdActual,
            pdactual.departamentoPais AS departamentoActual,
            pers.zonaResidenciaActual AS zonaResidenciaActual
        FROM th_personas pers
        JOIN cat_paises pa ON pa.paisId = pers.paisId
        JOIN cat_paises_municipios pmdui ON pmdui.paisMunicipioId = pers.paisMunicipioIdDUI
        JOIN cat_paises_departamentos pddui ON pddui.paisDepartamentoId = pmdui.paisDepartamentoId
        JOIN cat_paises_municipios pmactual ON pmactual.paisMunicipioId = pers.paisMunicipioIdActual
        JOIN cat_paises_departamentos pdactual ON pdactual.paisDepartamentoId = pmactual.paisDepartamentoId
        LEFT JOIN cat_paises_municipios expedimuni ON expedimuni.paisMunicipioId = pers.paisMunicipioIdExpedicion
        LEFT JOIN cat_paises_departamentos expedidepto ON expedidepto.paisDepartamentoId = expedimuni.paisDepartamentoId
        WHERE pers.personaId = ? AND pers.flgDelete = ? 
    ", [$apoderadoLegal, 0]);

    $empresaRepresentacion = "Industrial La Palma, S.A. de C.V.";
    $nosotrosRepresentacion = "Por alina, S.A de C.V. - " . $dataContratantePatronal->nombreCompleto;
    // Consultar si aplica
    $trabajadorRepresentacion = "";
    // Solicitud 26-04-2024
    $direccionPrestacionServicios = "Boulevard Venezuela N° 1233, San Salvador";
    //$direccionPrestacionServicios = "Boulevard Venezuela N° 1233, San Salvador, o cualquier sucursal de la empresa";
    $personaFacultadaHorarios = "Gerente";
    $lugarPago = "San Salvador";
    $formaPago = "Planillas quincenales";

    $flgShowLineas = 0;
    $distanciaCeldas = 4;

    foreach($dataExpedientes as $dataExpedientes) {
        $pdf->AddPage();
        if($dataExpedientes->fechaExpedicionIdentidad == "") {
            $pdf->SetFont('Arial', '', 12);
            $pdf->SetXY(10, 10);
            $pdf->MultiCell(195, $distanciaCeldas, utf8_decode("La fecha de expedición no ha sido agregada, por lo que no es posible generar el contrato de $dataExpedientes->nombreCompleto"), $flgShowLineas, 'J');
        } else {
            if($dataContratantePatronal->fechaExpedicionIdentidad == "") {
                $pdf->SetFont('Arial', '', 12);
                $pdf->SetXY(10, 10);
                $pdf->MultiCell(195, $distanciaCeldas, utf8_decode("La fecha de expedición del patrono: $dataContratantePatronal->nombreCompleto no ha sido agregada, por lo que no es posible generar el contrato de $dataExpedientes->nombreCompleto"), $flgShowLineas, 'J');
            } else {
                // Información para rellenar contrato
                $cargoPersona = $dataExpedientes->cargoPersona;
                $funcionCargoPersona = $dataExpedientes->funcionCargoPersona;
                $herramientasCargoPersona = $dataExpedientes->herramientasCargoPersona;
                // Consultar
                // Si es por tiempo o plazo determinado indicar la razón
                // Concatenarlo a esta variable
                $tipoContrato = $dataExpedientes->tipoContrato;

                //$fechaInicioContrato = fechaLetras($dataExpedientes->fechaInicioContrato);
                $fechaInicioContrato = fechaLetras($dataExpedientes->fechaInicioLabores);
                // Consultar si es la misma fecha de inicio
                //$fechaComputarContrato = $fechaInicioContrato;
                $fechaComputarContrato = $fechaInicioContrato;
                //$trabajadorRepresentacion = $dataExpedientes->nombreCompleto;

                // Reemplazar después estas variables por el salario según planilla
                $filtroSalario = base64_decode(urldecode($_REQUEST['filtroSalario']));
                $salarioContrato = base64_decode(urldecode($filtroSalario == "Números" ? $_REQUEST['salarioContrato'] : $_REQUEST['salarioContratoLetra']));
                $tipoRemuneracionSalario = "Por tiempo";

                $pdf->SetFont('Arial', 'B', 18);
                $pdf->SetXY(10, 10);
                $pdf->Cell(196, $distanciaCeldas * 2, utf8_decode('CONTRATO INDIVIDUAL DE TRABAJO'), $flgShowLineas, $flgShowLineas, 'C');
                $pdf->SetFont('Arial', 'B', 11);
                $pdf->SetXY(10, 22);
                $pdf->Cell(98, $distanciaCeldas, utf8_decode('Generales del trabajador'), $flgShowLineas, $flgShowLineas, 'C');
                $pdf->SetXY(108, 22);
                $pdf->Cell(98, $distanciaCeldas, utf8_decode('Generales del contratante patronal'), $flgShowLineas, $flgShowLineas, 'C');

                $pdf->SetFont('Arial', '', 8);
                // Generales
                    for ($i=0; $i < 2; $i++) { 
                        if($i == 0) {
                            // Generales del trabajador
                            $margen = 10;
                            $altura = 29;

                            $nombreCompleto = $dataExpedientes->nombreCompleto;
                            $calcularEdad = date_diff(date_create($dataExpedientes->fechaNacimiento), date_create(date("Y-m-d")));
                            $sexoEmpleado = ($dataExpedientes->sexo == "M" ? 'Masculino' : 'Femenino');
                            $estadoCivil = estadoCivil($dataExpedientes->estadoCivil, $dataExpedientes->sexo);
                            $nacionalidad = nacionalidad($dataExpedientes->paisId, $dataExpedientes->sexo);
                            if($dataExpedientes->docIdentidad == "DUI") {
                                $numDUI = $dataExpedientes->numIdentidad;
                                $otrosDatosIdentificacion = "";
                            } else {
                                $numDUI = "-";
                                $otrosDatosIdentificacion = $dataExpedientes->docIdentidad . ": " . $dataExpedientes->numIdentidad;
                            }
                            
                            // Variables que no tenemos
                            $profesionOficio = "Empleado";
                            $domicilio = "El Salvador";
                            // Consultar si es residencia actual o segun DUI
                            $residencia = $dataExpedientes->zonaResidenciaDUI;
                            $duiFechaExpedicion = fechaLetras($dataExpedientes->fechaExpedicionIdentidad);
                            $duiLugarExpedicion = $dataExpedientes->municipioExpedicion . ", " . $dataExpedientes->departamentoExpedicion;
                        } else {
                            // Generales del contratante patronal
                            $margen = 110;
                            $altura = 29;

                            $nombreCompleto = $dataContratantePatronal->nombreCompleto;
                            $calcularEdad = date_diff(date_create($dataContratantePatronal->fechaNacimiento), date_create(date("Y-m-d")));
                            $sexoEmpleado = ($dataContratantePatronal->sexo == "M" ? 'Masculino' : 'Femenino');
                            $estadoCivil = estadoCivil($dataContratantePatronal->estadoCivil, $dataContratantePatronal->sexo);
                            $nacionalidad = nacionalidad($dataContratantePatronal->paisId, $dataContratantePatronal->sexo);
                            $numDUI = $dataContratantePatronal->numIdentidad;

                            // Variables que no tenemos
                            $profesionOficio = "Empleado";
                            $domicilio = "El Salvador";
                            // Consultar si es residencia actual o segun DUI
                            $residencia = $dataContratantePatronal->zonaResidenciaDUI;
                            $duiFechaExpedicion = fechaLetras($dataContratantePatronal->fechaExpedicionIdentidad);
                            $otrosDatosIdentificacion = "";

                            $duiLugarExpedicion = $dataContratantePatronal->municipioExpedicion . ", " . $dataContratantePatronal->departamentoExpedicion;
                        }

                        // Nombre 
                        // Son dos columnas de 196 / 2 = 98 pero para margen y separacion se usa 95
                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->SetXY($margen, $altura);
                        $pdf->Cell(13, $distanciaCeldas, utf8_decode('Nombre:'), $flgShowLineas, $flgShowLineas, 'L');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->Cell(82, $distanciaCeldas, utf8_decode($nombreCompleto), 'B', 0, 'L');
                        $altura += $distanciaCeldas;
                        // Edad, sexo, estado civil
                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->SetXY($margen, $altura);
                        $pdf->Cell(9, $distanciaCeldas, utf8_decode('Edad:'), $flgShowLineas, $flgShowLineas, 'L');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->Cell(14, $distanciaCeldas, utf8_decode($calcularEdad->format('%y') . ' años'), 'B', 0, 'L');
                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->Cell(9, $distanciaCeldas, utf8_decode('Sexo:'), $flgShowLineas, $flgShowLineas, 'L');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->Cell(17, $distanciaCeldas, utf8_decode($sexoEmpleado), 'B', 0, 'L');
                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->Cell(18, $distanciaCeldas, utf8_decode('Estado civil:'), $flgShowLineas, $flgShowLineas, 'L');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->Cell(28, $distanciaCeldas, utf8_decode($estadoCivil), 'B', 0, 'L');
                        $altura += $distanciaCeldas;

                        // Profesion u oficio
                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->SetXY($margen, $altura);
                        $pdf->Cell(26, $distanciaCeldas, utf8_decode('Profesión u oficio:'), $flgShowLineas, $flgShowLineas, 'L');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->Cell(69, $distanciaCeldas, utf8_decode($profesionOficio), 'B', 0, 'L');
                        $altura += $distanciaCeldas;

                        // Domicilio, nacionalidad
                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->SetXY($margen, $altura);
                        $pdf->Cell(15, $distanciaCeldas, utf8_decode('Domicilio:'), $flgShowLineas, $flgShowLineas, 'L');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->Cell(32, $distanciaCeldas, utf8_decode($domicilio), 'B', 0, 'L');
                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->Cell(20, $distanciaCeldas, utf8_decode('Nacionalidad:'), $flgShowLineas, $flgShowLineas, 'L');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->Cell(28, $distanciaCeldas, utf8_decode($nacionalidad), 'B', 0, 'L');
                        $altura += $distanciaCeldas;

                        // Residencia
                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->SetXY($margen, $altura);
                        $pdf->Cell(17, $distanciaCeldas, utf8_decode('Residencia:'), $flgShowLineas, $flgShowLineas, 'L');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->MultiCell(80, $distanciaCeldas, utf8_decode($residencia), 0, 'L');
                        // Tres lineas para residencia
                        $pdf->SetXY($margen + 17, $altura);
                        $pdf->Cell(78, $distanciaCeldas, '', 'B', 0, 'L');
                        $pdf->SetXY($margen + 17, $altura + 4);
                        $pdf->Cell(78, $distanciaCeldas, '', 'B', 0, 'L');
                        $pdf->SetXY($margen + 17, $altura + 8);
                        $pdf->Cell(78, $distanciaCeldas, '', 'B', 0, 'L');
                        $altura += (($distanciaCeldas * 3) + 1);

                        // DUI
                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->SetXY($margen, $altura);
                        $pdf->Cell(11, $distanciaCeldas, utf8_decode('DUI N°:'), $flgShowLineas, $flgShowLineas, 'L');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->Cell(84, $distanciaCeldas, utf8_decode($numDUI), 'B', 0, 'L');
                        $altura += $distanciaCeldas;

                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->SetXY($margen, $altura);
                        $pdf->Cell(19, $distanciaCeldas, utf8_decode('Expedida en:'), $flgShowLineas, $flgShowLineas, 'L');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->Cell(76, $distanciaCeldas, utf8_decode($duiLugarExpedicion), 'B', 0, 'L');
                        $altura += $distanciaCeldas;

                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->SetXY($margen, $altura);
                        $pdf->Cell(5, $distanciaCeldas, utf8_decode('el:'), $flgShowLineas, $flgShowLineas, 'L');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->Cell(90, $distanciaCeldas, utf8_decode($duiFechaExpedicion), 'B', 0, 'L');
                        $altura += $distanciaCeldas;

                        // Otros datos: otrosDatosIdentificacion
                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->SetXY($margen, $altura);
                        $pdf->Cell(41, $distanciaCeldas, utf8_decode('Otros datos de identificación:'), $flgShowLineas, $flgShowLineas, 'L');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->Cell(54, $distanciaCeldas, utf8_decode($otrosDatosIdentificacion), 'B', 0, 'L');
                        $altura += $distanciaCeldas;
                    }
                // Fin generales
                $altura += $distanciaCeldas;
                // En representacion
                    // 195 Ancho
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(31, $distanciaCeldas, utf8_decode('En representación de:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode($empresaRepresentacion), 0, 0, 'C');
                    // Linea
                    $pdf->SetXY(41, $altura);
                    $pdf->Cell(164, $distanciaCeldas, '', 'B', 0, 'C');
                    // Centrado
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(11, $altura + $distanciaCeldas);
                    $pdf->Cell(194, $distanciaCeldas, utf8_decode('(Razón social o nombre del patrono)'), $flgShowLineas, $flgShowLineas, 'C');
                    $altura += ($distanciaCeldas * 2);

                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(15, $distanciaCeldas, utf8_decode('Nosotros:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode($nosotrosRepresentacion), 0, 0, 'C');
                    $pdf->SetFont('Arial', '', 6);
                    // Linea
                    $pdf->SetXY(25, $altura);
                    $pdf->Cell(180, $distanciaCeldas, '', 'B', 0, 'C');
                    // Centrado
                    $pdf->SetXY(11, $altura + $distanciaCeldas);
                    $pdf->Cell(194, $distanciaCeldas, utf8_decode('(Nombre del contratante patronal)'), $flgShowLineas, $flgShowLineas, 'C');
                    $altura += ($distanciaCeldas * 2);

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(11, $altura);
                    $pdf->Cell(194, $distanciaCeldas, utf8_decode($trabajadorRepresentacion), 'B', 0, 'C');
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(11, $altura + $distanciaCeldas);
                    $pdf->Cell(194, $distanciaCeldas, utf8_decode('(Nombre del trabajador)'), $flgShowLineas, $flgShowLineas, 'C');
                    $altura += ($distanciaCeldas * 2);
                // Fin representacion
                $altura += 2;
                // Texto de las generales
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->MultiCell(195, $distanciaCeldas, utf8_decode('De las generales arriba indicadas y actuando en el carácter que aparece expresado, convenimos en celebrar el presente Contrato Individual de Trabajo sujeto a las estipulaciones siguientes:'), $flgShowLineas, 'J');
                // Fin texto de las generales
                $altura += ($distanciaCeldas * 3);
                // Literal a)
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('a) CLASE DE TRABAJO O SERVICIO:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('a) Clase de trabajo o servicio:'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(80, $distanciaCeldas, utf8_decode('El trabajador se obliga a prestar sus servicios al patrono como:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(115, $distanciaCeldas, utf8_decode($cargoPersona), 'B', 0, 'C');
                    $altura += $distanciaCeldas;

                    $pdf->SetXY(10, $altura);
                    $pdf->MultiCell(195, $distanciaCeldas, utf8_decode('Además de las obligaciones que impongan las leyes laborales y sus reglamentos, el Contrato Colectivo, si lo hubiere y el reglamento interno de trabajo, tendrá como obligaciones propias de su cargo las siguientes:'), $flgShowLineas, 'J');
                    $altura += (($distanciaCeldas * 2) + 1);

                    $pdf->SetXY(10, $altura);
                    $pdf->MultiCell(195, $distanciaCeldas, utf8_decode($funcionCargoPersona), $flgShowLineas, 'J');
                    // Lineas
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(195, $distanciaCeldas, '', 'B', 0, 'L');
                    $pdf->SetXY(10, $altura + $distanciaCeldas);
                    $pdf->Cell(195, $distanciaCeldas, '', 'B', 0, 'L');
                // Fin literal a)
                $altura += ($distanciaCeldas * 3);
                // Literal b)
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('b) DURACIÓN DEL CONTRATO Y TIEMPO DE SERVICIO:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('b) Duración del contrato y tiempo de servicio:'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(47, $distanciaCeldas, utf8_decode('El presente Contrato se celebra por:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(148, $distanciaCeldas, utf8_decode($tipoContrato), 'B', 0, 'C');
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(57, $altura + $distanciaCeldas);
                    $pdf->Cell(148, $distanciaCeldas, utf8_decode('(Tiempo indefinido, plazo u obra. Si es por tiempo o plazo determinado, indicar la razón que motiva tal plazo)'), $flgShowLineas, $flgShowLineas, 'C');
                    $altura += ($distanciaCeldas * 2);

                    $pdf->SetFont('Arial', '', 8);

                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(15, $distanciaCeldas, utf8_decode('a partir de:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(45, $distanciaCeldas, utf8_decode($fechaInicioContrato), 'B', 0, 'C');
                    $pdf->Cell(135, $distanciaCeldas, utf8_decode('cuando la iniciación del trabajo haya precedido a la celebración del presente Contrato, el tiempo de servicio'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(25, $altura);
                    $pdf->Cell(45, $distanciaCeldas, utf8_decode('(día, mes y año)'), 0, 0, 'C');
                    $altura += $distanciaCeldas;

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(34, $distanciaCeldas, utf8_decode('se computará a partir del:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(45, $distanciaCeldas, utf8_decode($fechaComputarContrato), 'B', 0, 'C');
                    $pdf->Cell(120, $distanciaCeldas, utf8_decode(' fecha desde la cual el trabajador presta servicios al patrono sin que la relación laboral se'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(40, $altura);
                    $pdf->Cell(45, $distanciaCeldas, utf8_decode('(día, mes y año)'), 0, 0, 'C');
                    $altura += $distanciaCeldas;

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->MultiCell(195, $distanciaCeldas, utf8_decode('haya disuelto. Queda estipulado para trabajadores de nuevo ingreso que los primeros treinta días serán de prueba y dentro de este término cualquiera de las partes podrá dar por terminado el Contrato, sin expresión de causa.'), 0, 'J');
                // Fin literal b)
                $altura += ($distanciaCeldas * 3);
                // Literal c)
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('c) LUGAR DE PRESTACIÓN DE SERVICIOS Y DE ALOJAMIENTO:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('c) Lugar de prestación de servicios y de alojamiento:'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(56, $distanciaCeldas, utf8_decode('El lugar de prestación de los servicios será:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->SetXY(66, $altura);
                    $pdf->Cell(139, $distanciaCeldas, utf8_decode($direccionPrestacionServicios), 'B', 0, 'C');
                // Fin literal c)
                $altura += ($distanciaCeldas * 2);
                // Literal d)
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('d) HORARIO DE TRABAJO:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('d) Horario de trabajo:'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));

                    // Horarios de trabajo
                    /*
                    Se cambió por una plantilla de horario predeterminada a solicitud de Óscar Ochoa
                    $dataHorariosExpediente = $cloud->rows("
                        SELECT
                            expedienteHorarioId, 
                            diaInicio, 
                            diaFin, 
                            horaInicio, 
                            horaFin, 
                            horasLaborales
                        FROM th_expediente_horarios 
                        WHERE prsExpedienteId = ? AND flgDelete = ?
                    ", [$dataExpedientes->prsExpedienteId, 0]);
                    $horasSemana = 0;
                    $pdf->SetFont('Arial', '', 8);
                    foreach($dataHorariosExpediente as $dataHorariosExpediente) {
                        $pdf->SetXY(20, $altura);
                        $pdf->Cell(13, $distanciaCeldas, chr(149) . utf8_decode(' Del día:'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(15, $distanciaCeldas, utf8_decode($dataHorariosExpediente->diaInicio), 'B', 0, 'C');
                        $pdf->Cell(11, $distanciaCeldas, utf8_decode(', al día:'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(15, $distanciaCeldas, utf8_decode($dataHorariosExpediente->diaFin), 'B', 0, 'C');
                        $pdf->Cell(8, $distanciaCeldas, utf8_decode(', de:'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(15, $distanciaCeldas, utf8_decode(formatoHora($dataHorariosExpediente->horaInicio)), 'B', 0, 'C');
                        $pdf->Cell(5, $distanciaCeldas, utf8_decode(' a:'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(15, $distanciaCeldas, utf8_decode(formatoHora($dataHorariosExpediente->horaFin)), 'B', 0, 'C');

                        $horasSemana += $dataHorariosExpediente->horasLaborales;
                        $altura += $distanciaCeldas;
                    }
                    $altura -= $distanciaCeldas;
                    */
                    $horariosExpediente = array(
                        array(
                            "diaInicio"             => "Lunes",
                            "diaFin"                => "Sábado",
                            "horaInicio"            => "08:00:00",
                            "horaFin"               => "12:00:00",
                            "horasLaborales"        => 24
                        ),
                        array(
                            "diaInicio"             => "Lunes",
                            "diaFin"                => "Viernes",
                            "horaInicio"            => "13:00:00",
                            "horaFin"               => "17:00:00",
                            "horasLaborales"        => 20
                        )
                    );
                    $horasSemana = 0;
                    $pdf->SetFont('Arial', '', 8);
                    foreach($horariosExpediente as $dataHorariosExpediente) {
                        $pdf->SetXY(20, $altura);
                        $pdf->Cell(13, $distanciaCeldas, chr(149) . utf8_decode(' Del día:'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(15, $distanciaCeldas, utf8_decode($dataHorariosExpediente["diaInicio"]), 'B', 0, 'C');
                        $pdf->Cell(11, $distanciaCeldas, utf8_decode(', al día:'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(15, $distanciaCeldas, utf8_decode($dataHorariosExpediente["diaFin"]), 'B', 0, 'C');
                        $pdf->Cell(8, $distanciaCeldas, utf8_decode(', de:'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(15, $distanciaCeldas, utf8_decode(formatoHora($dataHorariosExpediente["horaInicio"])), 'B', 0, 'C');
                        $pdf->Cell(5, $distanciaCeldas, utf8_decode(' a:'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(15, $distanciaCeldas, utf8_decode(formatoHora($dataHorariosExpediente["horaFin"])), 'B', 0, 'C');

                        $horasSemana += $dataHorariosExpediente["horasLaborales"];
                        $altura += $distanciaCeldas;
                    }
                    $altura -= $distanciaCeldas;

                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(158, $altura);
                    $pdf->Cell(23, $distanciaCeldas, utf8_decode('Semana laboral:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->Cell(15, $distanciaCeldas, utf8_decode($horasSemana), 'B', 0, 'C'); 
                    $pdf->Cell(9, $distanciaCeldas, utf8_decode('horas.'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += ($distanciaCeldas + 2);

                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(150, $distanciaCeldas, utf8_decode('Únicamente podrán ejecutarse trabajos extraordinarios cuando se reciba la orden de verificarlos dada por el patrono o:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    $pdf->Cell(45, $distanciaCeldas, utf8_decode($personaFacultadaHorarios), 'B', 0, 'C'); 
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(160, $altura);
                    $pdf->Cell(45, $distanciaCeldas, utf8_decode('(persona facultada)'), 0, 0, 'C'); 
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(150, $distanciaCeldas, utf8_decode('Previo acuerdo con el trabajador.'), $flgShowLineas, $flgShowLineas, 'L'); 
                // Fin literal d)
                $altura += ($distanciaCeldas * 2);
                // Literal e)
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('e) SALARIO: FORMA, PERÍODO Y LUGAR DEL PAGO:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('e) Salario: Forma, período y lugar del pago:'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(89, $distanciaCeldas, utf8_decode('El salario que recibirá el trabajador, por sus servicios será la suma de:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    if($filtroSalario == "Números") {
                        $pdf->Cell(106, $distanciaCeldas, utf8_decode(dineroLetras($salarioContrato, 'decimal') . ' ($ ' . number_format($salarioContrato, 2, '.', ',') . ')'), 'B', 0, 'C'); 
                    } else {
                        $pdf->Cell(106, $distanciaCeldas, utf8_decode($salarioContrato), 'B', 0, 'C');
                    }
                    $altura += $distanciaCeldas;
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode($tipoRemuneracionSalario), 'B', 0, 'C'); 
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('(Indicar la forma de remuneración: por tiempo, por unidad de obra, por sistema mixto, por tarea, por comisión, etc.)'), 0, 0, 'C'); 

                    // Siguiente página
                    $pdf->AddPage();
                    $altura = 20; 
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(21, $distanciaCeldas, utf8_decode('y se pagará en:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(59, $distanciaCeldas, utf8_decode($lugarPago), 'B', 0, 'C');
                    $pdf->Cell(56, $distanciaCeldas, utf8_decode('dicho pago se hará de la manera siguiente:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(59, $distanciaCeldas, utf8_decode($formaPago), 'B', 0, 'C');
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(31, $altura);
                    $pdf->Cell(59, $distanciaCeldas, utf8_decode('(Lugar de pago: ciudad)'), $flgShowLineas, $flgShowLineas, 'C');
                    $altura += $distanciaCeldas;
                    /*
                    Cambio por solicitud 26-04-2024
                    $pdf->MultiCell(195, $distanciaCeldas, utf8_decode('La operación del pago principiará y se continuará sin interrupción, a más tardar dentro de las dos horas siguientes a la terminación de la jornada de trabajo correspondiente a la fecha respectiva, y únicamente se admitirán reclamos después de pagada la planilla o el día:'), $flgShowLineas, 'J');
                    $altura += $distanciaCeldas;
                    $pdf->SetXY(164, $altura);
                    $pdf->Cell(16, $distanciaCeldas, utf8_decode('-----'), 'B', 0, 'C');
                    $pdf->SetXY(180, $altura);
                    $pdf->Cell(25, $distanciaCeldas, utf8_decode('siguiente.'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(164, $altura);
                    $pdf->Cell(16, $distanciaCeldas, utf8_decode('(Indicar el día)'), 0, 0, 'C');
                    */
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->MultiCell(195, $distanciaCeldas, utf8_decode('La operación del pago principiará y se continuará sin interrupción, a más tardar dentro de las dos horas siguientes a la terminación de la jornada de trabajo correspondiente a la fecha respectiva.'), $flgShowLineas, 'J');
                    $altura += ($distanciaCeldas * 2);
                // Fin literal e)
                // No va * 2 porque el anterior es un texto pequeño
                $altura += ($distanciaCeldas);
                // Literal f)
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('f) HERRAMIENTAS Y MATERIALES:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('f) Herramientas y materiales:'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(99, $distanciaCeldas, utf8_decode('El patrono suministrará al trabajador las herramientas y materiales siguientes:'), $flgShowLineas, $flgShowLineas, 'L');
                    /*
                    Solicitud 26-04-2024
                    $pdf->Cell(96, $distanciaCeldas, utf8_decode('---------------------------------------'), 'B', 0, 'C');
                    $altura += $distanciaCeldas;
                    $pdf->SetXY(11, $altura);
                    $pdf->Cell(194, $distanciaCeldas, utf8_decode('---------------------------------------'), 'B', 0, 'C');
                    $altura += $distanciaCeldas + 1;
                    */
                    $altura += $distanciaCeldas;
                    $pdf->SetXY(10, $altura);
                    $pdf->MultiCell(195, $distanciaCeldas, utf8_decode($herramientasCargoPersona), $flgShowLineas, 'J');
                    // Lineas
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(195, $distanciaCeldas, '', 'B', 0, 'L');
                    $pdf->SetXY(10, $altura + $distanciaCeldas);
                    $pdf->Cell(195, $distanciaCeldas, '', 'B', 0, 'L');

                    $altura += ($distanciaCeldas * 2) + 1;

                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(27, $distanciaCeldas, utf8_decode('que se entregan en:'), $flgShowLineas, $flgShowLineas, 'L');
                    // Solicitud 26-04-2024
                    $pdf->Cell(37, $distanciaCeldas, utf8_decode('Óptimas condiciones'), 'B', 0, 'C');
                    //$pdf->Cell(37, $distanciaCeldas, utf8_decode('------------'), 'B', 0, 'C');
                    $pdf->Cell(131, $distanciaCeldas, utf8_decode('y deben ser devueltos así por el trabajador cuando sea requerida al efecto por sus inmediatos, salvo la'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(37, $altura);
                    $pdf->Cell(37, $distanciaCeldas, utf8_decode('(Estado y calidad)'), 0, 0, 'C');
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('disminución o deterioro causados por caso fortuito o fuerza mayor, o por la acción del tiempo, o por el consumo y uso normal de los mismos.'), $flgShowLineas, $flgShowLineas, 'L');
                // Fin literal f)
                $altura += ($distanciaCeldas * 2);
                // Literal g)
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('g) OTRAS ESTIPULACIONES:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('g) Otras estipulaciones:'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(11, $altura);
                    $pdf->Cell(194, $distanciaCeldas, utf8_decode('------------------------------------------'), 'B', 0, 'C');
                // Fin literal g)
                $altura += ($distanciaCeldas * 2);
                // Literal h)
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('g) OTRAS ESTIPULACIONES:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->MultiCell(195, $distanciaCeldas, utf8_decode('h) Este Contrato sustituye cualquier otro Convenio Individual de Trabajo anterior, ya sea escrito o verbal, que haya estado vigente entre el patrono y el trabajador, pero no altera en manera alguna los derechos y prerrogativas del trabajador que emanen de su antigüedad en el servicio; ni se entenderá como negativo de mejores condiciones concedidas al trabajador en el Contrato anterior y que no consten en el presente.'), 0, 'J');
                // Fin literal h)
                // * 5 porque son 4 lineas más 1 de separación
                $altura += ($distanciaCeldas * 5);
                // En fe firmamos
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(85, $distanciaCeldas, utf8_decode('En fe de la cual firmamos el presente documento por triplicado en:'), $flgShowLineas, $flgShowLineas, 'L');  
                    $pdf->Cell(110, $distanciaCeldas, utf8_decode($lugarPago), 'B', 0, 'C'); 
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);   
                    $pdf->SetXY(95, $altura);  
                    $pdf->Cell(110, $distanciaCeldas, utf8_decode('(Ciudad)'), 0, 0, 'C');
                    $pdf->SetFont('Arial', '', 8);
                    $altura += ($distanciaCeldas * 2);
                    $pdf->SetXY(52, $altura);
                    $pdf->Cell(9, $distanciaCeldas, utf8_decode('A los:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(25, $distanciaCeldas, utf8_decode($arrayFechaContrato[2]), 'B', 0, 'C');
                    $pdf->Cell(22, $distanciaCeldas, utf8_decode('días del mes de:'), $flgShowLineas, $flgShowLineas, 'L');     
                    $pdf->Cell(25, $distanciaCeldas, utf8_decode(mesLetras($arrayFechaContrato[1])), 'B', 0, 'C');   
                    $pdf->Cell(6, $distanciaCeldas, utf8_decode('de:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(25, $distanciaCeldas, utf8_decode($arrayFechaContrato[0]), 'B', 0, 'C');   
                // Fin en fe firmamos
                $altura += ($distanciaCeldas * 12);
                // Firmas
                    // Esto es para cuadrar el centrado de las firmas
                    //$pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('F: ______________________________                                        F: ______________________________'), $flgShowLineas, $flgShowLineas, 'C'); 
                    $pdf->SetXY(40, $altura);
                    $pdf->Cell(5, $distanciaCeldas, utf8_decode('F:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    $pdf->Cell(47, $distanciaCeldas, '', 'B', 0, 'C');  
                    $pdf->SetXY(122, $altura);
                    $pdf->Cell(5, $distanciaCeldas, utf8_decode('F:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    $pdf->Cell(47, $distanciaCeldas, '', 'B', 0, 'C');  
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(45, $altura);
                    $pdf->Cell(47, $distanciaCeldas, utf8_decode('Patrono o Representante'), 0, 0, 'C');
                    $pdf->SetXY(127, $altura);
                    $pdf->Cell(47, $distanciaCeldas, utf8_decode('Trabajador'), 0, 0, 'C');
                    $altura += ($distanciaCeldas * 12);

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(40, $altura - (($distanciaCeldas * 7) + 1));
                    $pdf->Cell(52, $distanciaCeldas, utf8_decode('Si no puede el trabajador firmar:'), $flgShowLineas, $flgShowLineas, 'C'); 
                    $pdf->SetXY(40, $altura - ($distanciaCeldas * 6));
                    $pdf->Cell(25, 27, '', 1, 1, 'L'); 
                    $pdf->SetXY(67, $altura - ($distanciaCeldas * 6));
                    $pdf->Cell(25, 27, '', 1, 1, 'L'); 
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(40, $altura + $distanciaCeldas);
                    $pdf->Cell(52, $distanciaCeldas, utf8_decode('Huellas Digitales del Trabajador'), $flgShowLineas, $flgShowLineas, 'C'); 
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(122, $altura);
                    $pdf->Cell(5, $distanciaCeldas, utf8_decode('F:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    $pdf->Cell(47, $distanciaCeldas, '', 'B', 0, 'C');  
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(127, $altura);
                    $pdf->Cell(47, $distanciaCeldas, utf8_decode('A ruego del Trabajador'), 0, 0, 'C');
                // Fin firmas
            }
        }
    }

    $pdf->Output($outputReporte . '.pdf', "I");
?>