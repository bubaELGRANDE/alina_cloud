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
                exp.fechaInicio AS fechaInicioContrato,
                exp.personaId AS personaId
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
                exp.fechaInicio AS fechaInicioContrato,
                exp.personaId AS personaId
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
    $nosotrosRepresentacion = "Industrial La Palma, S.A. de C.V. - " . $dataContratantePatronal->nombreCompleto;
    // Consultar si aplica
    $trabajadorRepresentacion = "----------";
    // Solicitud 26-04-2024
    $direccionPrestacionServicios = "Boulevard Venezuela N° 1233, San Salvador, San Salvador";
    //$direccionPrestacionServicios = "Boulevard Venezuela N° 1233, San Salvador, o cualquier sucursal de la empresa";
    $personaFacultadaHorarios = "Gerente";
    $lugarPago = "San Salvador";
    $formaPago = "Planillas quincenales";
    $formaPagoTiempo = "Por tiempo";
    $pagoEfectuadoMedio = "Transferencia bancaria";

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
                $pdf->Cell(98, $distanciaCeldas, utf8_decode('Generales del contratante patronal'), $flgShowLineas, $flgShowLineas, 'C');
                $pdf->SetXY(108, 22);
                $pdf->Cell(98, $distanciaCeldas, utf8_decode('Generales del trabajador'), $flgShowLineas, $flgShowLineas, 'C');

                $pdf->SetFont('Arial', '', 8);
                // Generales

                // Generales del contratante patronal
                $margen = 10;
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
                $residencia = $dataContratantePatronal->zonaResidenciaDUI.",".$dataContratantePatronal->municipioDUI.",".$dataContratantePatronal->departamentoDUI;
                $duiFechaExpedicion = fechaLetras($dataContratantePatronal->fechaExpedicionIdentidad);
                $otrosDatosIdentificacion = "";

                $duiLugarExpedicion = $dataContratantePatronal->municipioExpedicion . ", " . $dataContratantePatronal->departamentoExpedicion;

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
                $pdf->Cell(9, $distanciaCeldas, utf8_decode('Sexo:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(86, $distanciaCeldas, utf8_decode($sexoEmpleado), 'B', 0, 'L');
                $altura += $distanciaCeldas;

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(9, $distanciaCeldas, utf8_decode('Edad:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(86, $distanciaCeldas, utf8_decode($calcularEdad->format('%y') . ' años'), 'B', 0, 'L');
                $altura += $distanciaCeldas;
                
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(18, $distanciaCeldas, utf8_decode('Estado civil:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(77, $distanciaCeldas, utf8_decode($estadoCivil), 'B', 0, 'L');
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
                $pdf->Cell(80, $distanciaCeldas, utf8_decode($domicilio), 'B', 0, 'L');
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
                $altura += (($distanciaCeldas * 2) + 1);

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(20, $distanciaCeldas, utf8_decode('Nacionalidad:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(75, $distanciaCeldas, utf8_decode($nacionalidad), 'B', 0, 'L');
                $altura += $distanciaCeldas;
                
                // DUI
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(11, $distanciaCeldas, utf8_decode('DUI:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetXY($margen + 7, $altura);
                $pdf->Cell(88, $distanciaCeldas, utf8_decode($numDUI), 'B', 0, 'L');
                $altura += $distanciaCeldas;

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(19, $distanciaCeldas, utf8_decode('Expedido en:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(76, $distanciaCeldas, utf8_decode($duiLugarExpedicion), 'B', 0, 'L');
                $altura += $distanciaCeldas;

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(5, $distanciaCeldas, utf8_decode('el:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(90, $distanciaCeldas, utf8_decode($duiFechaExpedicion), 'B', 0, 'L');
                $altura += $distanciaCeldas;

                // En representación de (Razón Social)
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(51, $distanciaCeldas, utf8_decode('En representación de (Razón Social):'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(44, $distanciaCeldas, utf8_decode($empresaRepresentacion), "B", 0, 'L');
                $altura += ($distanciaCeldas * 1);
                
                // NIT
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(6, $distanciaCeldas, utf8_decode('NIT:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(89, $distanciaCeldas, utf8_decode("0614-010184-002-2"), "B", 0, 'L');
                $altura += $distanciaCeldas;

                // Actividad Económica de la Empresa:
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(51, $distanciaCeldas, utf8_decode('Actividad Económica de la Empresa:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->MultiCell(50, $distanciaCeldas, utf8_decode("Venta al por mayor de maquinaria"), 0, 'L');
                $pdf->SetXY($margen, ($altura + 4));
                $pdf->MultiCell(80, $distanciaCeldas, utf8_decode("y equipo agropecuario, accesorios, partes y suministros"), 0, 'L');
                $pdf->SetXY(61, $altura);
                $pdf->Cell(44, $distanciaCeldas, utf8_decode(""), "B", 0, 'L');
                $pdf->SetXY($margen + 1, $altura + 4);
                $pdf->Cell(94, $distanciaCeldas, '', 'B', 0, 'L');
                $altura += ($distanciaCeldas * 2);
                // Generales del trabajador
                $margen = 110;
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
                $residencia = $dataExpedientes->zonaResidenciaDUI.",".$dataExpedientes->municipioDUI.",".$dataExpedientes->departamentoDUI;
                $duiFechaExpedicion = fechaLetras($dataExpedientes->fechaExpedicionIdentidad);
                $duiLugarExpedicion = $dataExpedientes->municipioExpedicion . ", " . $dataExpedientes->departamentoExpedicion;

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
                $pdf->Cell(9, $distanciaCeldas, utf8_decode('Sexo:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(86, $distanciaCeldas, utf8_decode($sexoEmpleado), 'B', 0, 'L');
                
                $altura += $distanciaCeldas;

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(9, $distanciaCeldas, utf8_decode('Edad:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(86, $distanciaCeldas, utf8_decode($calcularEdad->format('%y') . ' años'), 'B', 0, 'L');
                $altura += $distanciaCeldas;

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(18, $distanciaCeldas, utf8_decode('Estado civil:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(77, $distanciaCeldas, utf8_decode($estadoCivil), 'B', 0, 'L');
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
                $pdf->Cell(80, $distanciaCeldas, utf8_decode($domicilio), 'B', 0, 'L');
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
                $altura += (($distanciaCeldas * 2) + 1);

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(20, $distanciaCeldas, utf8_decode('Nacionalidad:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(75, $distanciaCeldas, utf8_decode($nacionalidad), 'B', 0, 'L');
                $altura += $distanciaCeldas;
                // DUI
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(11, $distanciaCeldas, utf8_decode('DUI:'), $flgShowLineas, $flgShowLineas, 'L');
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetXY($margen + 7, $altura);
                $pdf->Cell(88, $distanciaCeldas, utf8_decode($numDUI), 'B', 0, 'L');
                $altura += $distanciaCeldas;

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($margen, $altura);
                $pdf->Cell(19, $distanciaCeldas, utf8_decode('Expedido en:'), $flgShowLineas, $flgShowLineas, 'L');
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
                $altura += ($distanciaCeldas * 2);


                // Fin generales
                    $altura += ($distanciaCeldas * 3);

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(15, $distanciaCeldas, utf8_decode('NOSOTROS:'), $flgShowLineas, $flgShowLineas, 'L');
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
                    $pdf->SetXY(15, $altura);
                    $pdf->Cell(190, $distanciaCeldas, utf8_decode($trabajadorRepresentacion), 'B', 0, 'C');
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(5, $distanciaCeldas, utf8_decode("y"), 0, 0, 'R');
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

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(19, $distanciaCeldas, utf8_decode('Desempeñando las funciones de:'), $flgShowLineas, $flgShowLineas, 'L');

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(53, $altura);
                    $pdf->Cell(153, $distanciaCeldas, utf8_decode(substr($funcionCargoPersona, 0, 115)), $flgShowLineas, $flgShowLineas, 'J');
                    // Lineas
                    $pdf->SetXY(52, $altura);
                    $pdf->Cell(153, $distanciaCeldas, '', 'B', 0, 'L');

                    $pdf->SetXY(10, $altura + 4);
                    $pdf->MultiCell(195, $distanciaCeldas, utf8_decode(substr($funcionCargoPersona, 116)), $flgShowLineas, 'J');
                     // Lineas
                    $pdf->SetXY(11, $altura + $distanciaCeldas);
                    $pdf->Cell(194, $distanciaCeldas, '', "B", 0, 'L');
                    $pdf->SetXY(11, $altura + ($distanciaCeldas * 2));
                    $pdf->Cell(194, $distanciaCeldas, '', 'B', 0, 'L');
                // Fin literal a)

                $altura += ($distanciaCeldas * 3.5);
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
                    $pdf->Cell(75, $distanciaCeldas, utf8_decode($fechaInicioContrato), 'B', 0, 'C');
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(25, $altura);
                    $pdf->Cell(75, $distanciaCeldas, utf8_decode('(día, mes y año)'), 0, 0, 'C');
                    $altura += $distanciaCeldas;

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->CellHTML(195, $distanciaCeldas, utf8_decode('Queda estipulado para los trabajadores de nuevo ingreso que los primeros <b>treinta días serán de prueba</b> y dentro de ese término cualquiera de las partes podrá dar por terminado el contrato sin expresión de causa.'), 0, 0, 'J');
                    // Fin literal b)
                $altura += ($distanciaCeldas * 2.5);
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
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(56, $distanciaCeldas, utf8_decode('y el trabajador habitará en:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->SetXY(45, $altura);
                    $pdf->Cell(160, $distanciaCeldas, utf8_decode("----------------"), 'B', 0, 'C');
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->CellHTML(195, $distanciaCeldas, utf8_decode('Dado que la empresa <b>(sí)</b> o <b>(no)</b> le proporciona alojamiento'), 0, 'J');

                    /*  $pdf->SetXY(10, $altura + $distanciaCeldas);
                        $pdf->Cell(195, $distanciaCeldas, utf8_decode($direccionPrestacionServicios), 'B', 0, 'L');
                    */
                // Fin literal c)
                $altura += ($distanciaCeldas * 1.5);
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

                   /*$horariosExpediente = array(
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
                    );*/
                    //$horasSemana = 0;
                    $pdf->SetFont('Arial', '', 8);

                        $pdf->SetXY(20, $altura);
                        $pdf->Cell(13, $distanciaCeldas, chr(149) . utf8_decode(' Del día'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(30, $distanciaCeldas, utf8_decode("Lunes"), 'B', 0, 'C');
                        $pdf->Cell(11, $distanciaCeldas, utf8_decode(', al día'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(30, $distanciaCeldas, utf8_decode("Viernes"), 'B', 0, 'C');
                        $pdf->Cell(8, $distanciaCeldas, utf8_decode(', de'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(30, $distanciaCeldas, utf8_decode("8:00 A.M."), 'B', 0, 'C');
                        $pdf->Cell(5, $distanciaCeldas, utf8_decode(' a'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(30, $distanciaCeldas, utf8_decode("5:00 P.M."), 'B', 0, 'C');

                        //$horasSemana += $dataHorariosExpediente["horasLaborales"];
                        $altura += $distanciaCeldas;

                        /*$pdf->SetXY(20, $altura);
                        $pdf->Cell(13, $distanciaCeldas, chr(149) . utf8_decode(' Y del'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(30, $distanciaCeldas, utf8_decode("----"), 'B', 0, 'C');
                        $pdf->Cell(11, $distanciaCeldas, utf8_decode(',    a'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(30, $distanciaCeldas, utf8_decode("----"), 'B', 0, 'C');

                        $altura += $distanciaCeldas;*/

                        $pdf->SetXY(20, $altura);
                        $pdf->Cell(13, $distanciaCeldas, chr(149) . utf8_decode(' El día'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(30, $distanciaCeldas, utf8_decode("Sabado"), 'B', 0, 'C');
                        $pdf->Cell(11, $distanciaCeldas, utf8_decode(',    de'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(30, $distanciaCeldas, utf8_decode("8:00 A.M."), 'B', 0, 'C');
                        $pdf->Cell(8, $distanciaCeldas, utf8_decode(', a'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(30, $distanciaCeldas, utf8_decode("12:00 M.D."), 'B', 0, 'C');

                        $altura += $distanciaCeldas;

                        $pdf->SetXY(20, $altura);
                        $pdf->Cell(13, $distanciaCeldas, chr(149) . utf8_decode(' De las'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(30, $distanciaCeldas, utf8_decode("12:00 M.D."), 'B', 0, 'C');
                        $pdf->Cell(11, $distanciaCeldas, utf8_decode(', a las'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(30, $distanciaCeldas, utf8_decode("1:00 P.M."), 'B', 0, 'C');
                        $pdf->Cell(36, $distanciaCeldas, utf8_decode(", para la toma de alimentos."), 0, 0, 'C');

                        $altura += $distanciaCeldas;

                        $pdf->SetXY(20, $altura);
                        $pdf->Cell(13, $distanciaCeldas, chr(149) . utf8_decode(' Cumpliendo con la semana Laboral'), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->Cell(41, $distanciaCeldas, utf8_decode(''), $flgShowLineas, $flgShowLineas, 'L'); 
                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->Cell(30, $distanciaCeldas, utf8_decode("44"), 'B', 0, 'C');
                        $pdf->SetFont('Arial', '', 8);
                        $pdf->Cell(10, $distanciaCeldas, utf8_decode("horas."), 0, 0, 'C');

                        $altura += ($distanciaCeldas * 1.5);

                        $pdf->SetXY(10, $altura);
                        $pdf->CellHTML(195, $distanciaCeldas, utf8_decode('alina S.A de C.V. acuerda con el trabajador fijar como día de descanso remunerado, día <b>DOMINGO</b>.'), 0, 'J');
                        $altura += ($distanciaCeldas * 1.5);

                // Fin literal d)


                // Literal e)
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('e) SALARIO: FORMA, PERÍODO Y LUGAR DEL PAGO:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('e) Salario: Forma, período y lugar del pago:'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(89, $distanciaCeldas, utf8_decode('El salario que recibirá el trabajador, por sus servicios, será la suma de:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    if($filtroSalario == "Números") {
                        $pdf->Cell(106, $distanciaCeldas, utf8_decode(dineroLetras($salarioContrato, 'decimal') . ' ($ ' . number_format($salarioContrato, 2, '.', ',') . ')'), 'B', 0, 'C'); 
                    } else {
                        $pdf->Cell(106, $distanciaCeldas, utf8_decode($salarioContrato), 'B', 0, 'C');
                    }
                    $altura += $distanciaCeldas;
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(100, $distanciaCeldas, utf8_decode('Se pagará en dólares de los Estados Unidos de América de la siguiente forma:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    $pdf->Cell(95, $distanciaCeldas, utf8_decode($formaPagoTiempo), 'B', 0, 'C');

                    $altura += $distanciaCeldas;

                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(47, $distanciaCeldas, utf8_decode('El pago se efectuará por medio de:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    $pdf->Cell(148, $distanciaCeldas, utf8_decode($pagoEfectuadoMedio), 'B', 0, 'C');

                    /*$pdf->SetXY(10, $altura);
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode($tipoRemuneracionSalario), 'B', 0, 'C'); 
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('(Indicar la forma de remuneración: por tiempo, por unidad de obra, por sistema mixto, por tarea, por comisión, etc.)'), 0, 0, 'C'); 
                    */
                    // Siguiente página
                    $pdf->AddPage();
                    $altura = 5; 
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(21, $distanciaCeldas, utf8_decode('En la Dirección:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    $pdf->Cell(173, $distanciaCeldas, utf8_decode($direccionPrestacionServicios), 'B', 0, 'C');

                    $altura += $distanciaCeldas;
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(61, $distanciaCeldas, utf8_decode('Dicho pago se efectuará de la manera siguiente:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    $pdf->Cell(133, $distanciaCeldas, utf8_decode($formaPago), 'B', 0, 'C');

                    $altura += ($distanciaCeldas + 1);

                    $pdf->SetXY(10, $altura);
                    $pdf->MultiCell(195, $distanciaCeldas, utf8_decode('La operación del pago principiará y se continuará sin interrupción, a más tardar a la Terminación de la jornada de trabajo correspondiente a la respectiva fecha, en caso de reclamo de la persona trabajadora, se estará a lo dispuesto en el artículo 613 del Código de Trabajo. '), $flgShowLineas, 'J');

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
                // Fin literal e)
                // No va * 2 porque el anterior es un texto pequeño
            
                $altura += ($distanciaCeldas * 2.5);
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
                    $pdf->Cell(131, $distanciaCeldas, utf8_decode('y deben ser devueltos así por el trabajador, cuando sea requerida al efecto por sus jefes inmediatos,'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(37, $altura);
                    $pdf->Cell(37, $distanciaCeldas, utf8_decode('(Estado y calidad)'), 0, 0, 'C');
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('salvo la disminución o deterioro causados por caso fortuito o fuerza mayor, o por la acción del tiempo o por el consumo y uso normal de los mismos.'), $flgShowLineas, $flgShowLineas, 'L');
                // Fin literal f)
                    $altura += ($distanciaCeldas * 2);
                //literal g)
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('f) HERRAMIENTAS Y MATERIALES:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('g) Personas Que Dependen Económicamente Del Trabajador:'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));

                    $familiaDepende = $cloud->rows("
                        SELECT 
                            nombreFamiliar,
                            apellidoFamiliar,
                            fechaNacimiento,
                            direccionVivenJuntos,
                            flgDependeEconomicamente
                        FROM th_personas_familia 
                        WHERE flgDelete = ? AND personaId = ? AND flgDependeEconomicamente = ?
                    ", [0, $dataExpedientes->personaId, "Sí"]);

                    
                    if($familiaDepende){
                        foreach($familiaDepende AS $familiaDepende){
                            if ($familiaDepende->direccionVivenJuntos) {
                                $direccionDependeFamiliar = $dataExpedientes->zonaResidenciaDUI . ", " . $dataExpedientes->municipioDUI . ", " . $dataExpedientes->departamentoDUI;
                            } else {
                                $direccionDependeFamiliar = $familiaDepende->direccionVivenJuntos;
                            }
                            /*$nombreCompleto = explode(' ', $familiaDepende->nombreFamiliar);
                            $nombres = $nombreCompleto[0] . ' ' . $nombreCompleto[1];
                            $apellidos =  implode(' ', array_slice($nombreCompleto, 2));*/
    
                            $anioActual = date('Y');
                            
                            $anioNacimiento = date("Y", strtotime($familiaDepende->fechaNacimiento));
    
                            $edad = $anioActual - $anioNacimiento;
                            
                            $pdf->SetFont('Arial', '', 8.5);
                            $pdf->SetXY(10, $altura);
                            $pdf->Cell(32, $distanciaCeldas, utf8_decode($familiaDepende->nombreFamiliar), 0, 0, 'C');
                            $pdf->SetXY(10, $altura);
                            $pdf->Cell(195, $distanciaCeldas, '', 'B', 0, 'L');
    
                            $pdf->SetXY(42, $altura);
                            $pdf->Cell(40, $distanciaCeldas, utf8_decode($familiaDepende->apellidoFamiliar), 0, 0, 'C');
                            $pdf->SetXY(45, $altura);
                            $pdf->Cell(195, $distanciaCeldas, '', 0, 0, 'L');
    
                            $pdf->SetXY(82, $altura);
                            $pdf->Cell(20, $distanciaCeldas, utf8_decode($edad), 0, 0, 'C');
                            $pdf->SetXY(95, $altura);
                            $pdf->Cell(195, $distanciaCeldas, '', 0, 0, 'L');
    
                            $pdf->SetFont('Arial', '', 7.5);
                            $pdf->SetXY(102, $altura);
                            $pdf->Cell(103, $distanciaCeldas, utf8_decode($direccionDependeFamiliar), 0, 0, 'C');
                            $pdf->SetXY(110, $altura);
                            $pdf->Cell(195, $distanciaCeldas, '', 0, 0, 'L');
    
                            $pdf->SetFont('Arial', '', 8.5);
                            $altura += $distanciaCeldas;
                            $pdf->SetFont('Arial', 'B', 7);
                            $pdf->SetXY(10, $altura);
                            $pdf->Cell(32, $distanciaCeldas, utf8_decode('Nombre'), 0, 0, 'C');
                            
                            $pdf->SetXY(42, $altura);
                            $pdf->Cell(40, $distanciaCeldas, utf8_decode('Apellido'), 0, 0, 'C');
        
                            $pdf->SetXY(82, $altura);
                            $pdf->Cell(20, $distanciaCeldas, utf8_decode('Edad'), 0, 0, 'C');
        
                            $pdf->SetXY(102, $altura);
                            $pdf->Cell(103, $distanciaCeldas, utf8_decode('Dirección'), 0, 0, 'C');
                            
                            $altura += ($distanciaCeldas * 2);
        
                        }
                    }else{
                        $pdf->SetFont('Arial', '', 8.5);
                        $pdf->SetXY(10, $altura);
                        $pdf->Cell(195, $distanciaCeldas, utf8_decode('El trabajador declara que no tiene personas a su cargo económicamente.'), "B", 0, 'C');
                        $altura += ($distanciaCeldas * 2);
                    }
                    
                //fin de literal g)
                
                // Literal h)
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('g) OTRAS ESTIPULACIONES:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(195, $distanciaCeldas, utf8_decode('h) Otras estipulaciones:'), $flgShowLineas, $flgShowLineas, 'L');
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(11, $altura);
                    $pdf->Cell(194, $distanciaCeldas, utf8_decode('------------------------------------------'), 'B', 0, 'C');
                    $altura += ($distanciaCeldas + ($distanciaCeldas / 2));
                    $pdf->SetXY(11, $altura);
                    $pdf->Cell(194, $distanciaCeldas, utf8_decode('------------------------------------------'), 'B', 0, 'C');

                // Fin literal g)
                $altura += ($distanciaCeldas * 2);
                // Literal h)
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('g) OTRAS ESTIPULACIONES:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->MultiCell(195, $distanciaCeldas, utf8_decode('i) En el presente Contrato Individual de Trabajo se entenderán incluidos, según el caso, los derechos y deberes laborales establecidos por las Leyes y Reglamentos de Trabajo pertinentes, por el Reglamento Interno de Trabajo y por el o los Contratos Colectivos de Trabajo que celebre el patrono; los reconocidos en las sentencias que resuelvan conflictos colectivos de trabajo en la empresa, y los consagrados por la costumbre.'), 0, 'J');
                // Fin literal h)
                // * 5 porque son 4 lineas más 1 de separación
                $altura += ($distanciaCeldas * 5);

                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('g) OTRAS ESTIPULACIONES:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->MultiCell(195, $distanciaCeldas, utf8_decode('j) Este contrato sustituye cualquier otro Convenio Individual de Trabajo anterior, ya sea escrito o verbal, que haya estado vigente entre el patrono y el trabajador, pero no altera en manera alguna los derechos y prerrogativas del trabajador que emanen de su antigüedad en el servicio, ni se entenderá como negativa de mejores condiciones concedidas al trabajador en el Contrato anterior y que no consten el presente.'), 0, 'J');
                    $altura += ($distanciaCeldas * 4);
                // En fe firmamos

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(85, $distanciaCeldas, utf8_decode('En fe de lo cual firmamos el presente documento por triplicado en:'), $flgShowLineas, $flgShowLineas, 'L');  
                    $pdf->Cell(110, $distanciaCeldas, utf8_decode($lugarPago), 'B', 0, 'C'); 
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);   
                    $pdf->SetXY(95, $altura);  
                    $pdf->Cell(110, $distanciaCeldas, utf8_decode('(Ciudad)'), 0, 0, 'C');
                    $pdf->SetFont('Arial', '', 8);
                    $altura += ($distanciaCeldas * 2);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(9, $distanciaCeldas, utf8_decode('A los:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(25, $distanciaCeldas, utf8_decode($arrayFechaContrato[2]), 'B', 0, 'C');
                    $pdf->Cell(22, $distanciaCeldas, utf8_decode('días del mes de:'), $flgShowLineas, $flgShowLineas, 'L');     
                    $pdf->Cell(25, $distanciaCeldas, utf8_decode(mesLetras($arrayFechaContrato[1])), 'B', 0, 'C');   
                    $pdf->Cell(6, $distanciaCeldas, utf8_decode('de:'), $flgShowLineas, $flgShowLineas, 'L');
                    $pdf->Cell(25, $distanciaCeldas, utf8_decode($arrayFechaContrato[0]), 'B', 0, 'C');   
                // Fin en fe firmamos
                $altura += ($distanciaCeldas * 3);
                // Firmas
                    // Esto es para cuadrar el centrado de las firmas
                    //$pdf->SetXY(10, $altura);
                    //$pdf->Cell(195, $distanciaCeldas, utf8_decode('F: ______________________________                                        F: ______________________________'), $flgShowLineas, $flgShowLineas, 'C'); 
                    $pdf->SetXY(10, $altura + 10);
                    $pdf->Cell(5, $distanciaCeldas, utf8_decode('F:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    $pdf->Cell(47, $distanciaCeldas, '', 'B', 0, 'C');  
                    $pdf->SetXY(140, $altura + 10);
                    $pdf->Cell(5, $distanciaCeldas, utf8_decode('F:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    $pdf->Cell(47, $distanciaCeldas, '', 'B', 0, 'C');  
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', 'B', 6);
                    $pdf->SetXY(15, $altura + 10);
                    $pdf->Cell(47, $distanciaCeldas, utf8_decode('Patrono o Representante'), 0, 0, 'C');
                    $pdf->SetFont('Arial', 'B', 6);
                    $pdf->SetXY(145, $altura + 10);
                    $pdf->Cell(47, $distanciaCeldas, utf8_decode('Trabajador'), 0, 0, 'C');
                    $altura += ($distanciaCeldas * 12);


                    $pdf->SetXY(144, $altura - ($distanciaCeldas * 6));
                    $pdf->Cell(25, 27, '', 1, 1, 'L'); 
                    $pdf->SetXY(171, $altura - ($distanciaCeldas * 6));
                    $pdf->Cell(25, 27, '', 1, 1, 'L'); 
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(144, $altura + $distanciaCeldas);
                    $pdf->Cell(52, $distanciaCeldas, utf8_decode('Huellas Digitales del Trabajador'), $flgShowLineas, $flgShowLineas, 'C');
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(144, $altura + ($distanciaCeldas + 2));
                    $pdf->Cell(52, $distanciaCeldas, utf8_decode('Si no puede firmar'), $flgShowLineas, $flgShowLineas, 'C');
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY(10, $altura);
                    $pdf->Cell(5, $distanciaCeldas, utf8_decode('F:'), $flgShowLineas, $flgShowLineas, 'L'); 
                    $pdf->Cell(47, $distanciaCeldas, '', 'B', 0, 'C');  
                    $altura += $distanciaCeldas;
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(15, $altura);
                    $pdf->Cell(47, $distanciaCeldas, utf8_decode('A ruego del Trabajador'), 0, 0, 'C');
                // Fin firmas

            }
        }
    }

    $pdf->Output($outputReporte . '.pdf', "I");
?>