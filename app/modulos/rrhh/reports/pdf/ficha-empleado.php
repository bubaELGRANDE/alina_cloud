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

    /*
        REQUEST:
        filtroEmpleados (radio)
        selectEmpleados (multiple)
        flgFirmaEmpleado
    */

    $filtroEmpleados = base64_decode(urldecode($_REQUEST['filtroEmpleados']));
    $arrayEmpleadosId = (isset($_REQUEST['selectEmpleados']) ? base64_decode(urldecode($_REQUEST['selectEmpleados'])) : '');
    $flgFirmaEmpleado = (isset($_REQUEST['flgFirmaEmpleado']) ? 'Sí' : 'No');
    $estadoPersona = ($filtroEmpleados == "Inactivos" ? 'Inactivo' : 'Activo');

    $tituloReporte = 'Ficha de empleado';
    $subtituloReporte = "";
    
    $outputReporte = 'Ficha de empleado (' . $filtroEmpleados . ')';

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    $pdf->SetTitle($outputReporte);

    if($filtroEmpleados == 'Todos') {
        // Todos los empleados
        $dataEmpleados = $cloud->rows("
            SELECT
                per.personaId AS personaId,
                CONCAT(
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-'),
                    ', ',
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-')
                ) AS nombreCompleto,
                per.docIdentidad AS docIdentidad,
                per.numIdentidad AS numIdentidad, 
                per.fechaExpiracionIdentidad AS fechaExpiracionIdentidad,
                expedimuni.paisDepartamentoId AS paisDepartamentoIdExpedicion,
                expedidepto.departamentoPais AS departamentoExpedicion,
                per.paisMunicipioIdExpedicion AS paisMunicipioIdExpedicion,
                expedimuni.municipioPais AS municipioExpedicion,
                per.fechaExpedicionIdentidad AS fechaExpedicionIdentidad,
                per.nit AS nit, 
                per.fechaNacimiento AS fechaNacimiento, 
                per.fechaInicioLabores AS fechaInicioLabores,
                per.sexo AS sexo, 
                per.estadoCivil AS estadoCivil, 
                per.nombreOrganizacionIdAFP AS nombreOrganizacionIdAFP,
                nameafp.nombreOrganizacion AS nombreOrganizacionAFP,
                nameafp.abreviaturaOrganizacion AS abreviaturaOrganizacionAFP,
                per.nup AS nup,
                per.nombreOrganizacionIdISSS AS nombreOrganizacionIdISSS,
                nameisss.nombreOrganizacion AS nombreOrganizacionISSS,
                nameisss.abreviaturaOrganizacion AS abreviaturaOrganizacionISSS,
                per.numISSS AS numISSS,
                per.paisId AS paisId, 
                pa.pais AS nacionalidad,
                pa.iconBandera AS iconBandera,
                per.paisMunicipioIdDUI AS paisMunicipioIdDUI, 
                pmdui.municipioPais AS municipioDUI,
                pddui.paisDepartamentoId AS paisDepartamentoIdDUI,
                pddui.departamentoPais AS departamentoDUI,
                per.zonaResidenciaDUI AS zonaResidenciaDUI, 
                per.paisMunicipioIdActual AS paisMunicipioIdActual, 
                pmactual.municipioPais AS municipioActual,
                pdactual.paisDepartamentoId AS paisDepartamentoIdActual,
                pdactual.departamentoPais AS departamentoActual,
                per.zonaResidenciaActual AS zonaResidenciaActual, 
                per.vehiculoPropio AS vehiculoPropio, 
                per.vehiculosPropios AS vehiculosPropios, 
                per.estadoPersona AS estadoPersona
            FROM th_personas per
            LEFT JOIN cat_paises pa ON pa.paisId = per.paisId
            LEFT JOIN cat_paises_municipios pmdui ON pmdui.paisMunicipioId = per.paisMunicipioIdDUI
            LEFT JOIN cat_paises_departamentos pddui ON pddui.paisDepartamentoId = pmdui.paisDepartamentoId
            LEFT JOIN cat_paises_municipios pmactual ON pmactual.paisMunicipioId = per.paisMunicipioIdActual
            LEFT JOIN cat_paises_departamentos pdactual ON pdactual.paisDepartamentoId = pmactual.paisDepartamentoId
            LEFT JOIN cat_nombres_organizaciones nameafp ON nameafp.nombreOrganizacionId = per.nombreOrganizacionIdAFP
            LEFT JOIN cat_nombres_organizaciones nameisss ON nameisss.nombreOrganizacionId = per.nombreOrganizacionIdISSS
            LEFT JOIN cat_paises_municipios expedimuni ON expedimuni.paisMunicipioId = per.paisMunicipioIdExpedicion
            LEFT JOIN cat_paises_departamentos expedidepto ON expedidepto.paisDepartamentoId = expedimuni.paisDepartamentoId
            WHERE per.prsTipoId = ? AND per.estadoPersona = ? AND per.flgDelete = ?
            ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
        ", ['1', $estadoPersona, '0']);
    } else {
        // Empleados especificos
        $dataEmpleados = $cloud->rows("
            SELECT
                per.personaId AS personaId,
                CONCAT(
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-'),
                    ', ',
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-')
                ) AS nombreCompleto,
                per.docIdentidad AS docIdentidad,
                per.numIdentidad AS numIdentidad, 
                per.fechaExpiracionIdentidad AS fechaExpiracionIdentidad,
                expedimuni.paisDepartamentoId AS paisDepartamentoIdExpedicion,
                expedidepto.departamentoPais AS departamentoExpedicion,
                per.paisMunicipioIdExpedicion AS paisMunicipioIdExpedicion,
                expedimuni.municipioPais AS municipioExpedicion,
                per.fechaExpedicionIdentidad AS fechaExpedicionIdentidad,
                per.nit AS nit, 
                per.fechaNacimiento AS fechaNacimiento, 
                per.fechaInicioLabores AS fechaInicioLabores,
                per.sexo AS sexo, 
                per.estadoCivil AS estadoCivil, 
                per.nombreOrganizacionIdAFP AS nombreOrganizacionIdAFP,
                nameafp.nombreOrganizacion AS nombreOrganizacionAFP,
                nameafp.abreviaturaOrganizacion AS abreviaturaOrganizacionAFP,
                per.nup AS nup,
                per.nombreOrganizacionIdISSS AS nombreOrganizacionIdISSS,
                nameisss.nombreOrganizacion AS nombreOrganizacionISSS,
                nameisss.abreviaturaOrganizacion AS abreviaturaOrganizacionISSS,
                per.numISSS AS numISSS,
                per.paisId AS paisId, 
                pa.pais AS nacionalidad,
                pa.iconBandera AS iconBandera,
                per.paisMunicipioIdDUI AS paisMunicipioIdDUI, 
                pmdui.municipioPais AS municipioDUI,
                pddui.paisDepartamentoId AS paisDepartamentoIdDUI,
                pddui.departamentoPais AS departamentoDUI,
                per.zonaResidenciaDUI AS zonaResidenciaDUI, 
                per.paisMunicipioIdActual AS paisMunicipioIdActual, 
                pmactual.municipioPais AS municipioActual,
                pdactual.paisDepartamentoId AS paisDepartamentoIdActual,
                pdactual.departamentoPais AS departamentoActual,
                per.zonaResidenciaActual AS zonaResidenciaActual, 
                per.vehiculoPropio AS vehiculoPropio, 
                per.vehiculosPropios AS vehiculosPropios, 
                per.estadoPersona AS estadoPersona
            FROM th_personas per
            LEFT JOIN cat_paises pa ON pa.paisId = per.paisId
            LEFT JOIN cat_paises_municipios pmdui ON pmdui.paisMunicipioId = per.paisMunicipioIdDUI
            LEFT JOIN cat_paises_departamentos pddui ON pddui.paisDepartamentoId = pmdui.paisDepartamentoId
            LEFT JOIN cat_paises_municipios pmactual ON pmactual.paisMunicipioId = per.paisMunicipioIdActual
            LEFT JOIN cat_paises_departamentos pdactual ON pdactual.paisDepartamentoId = pmactual.paisDepartamentoId
            LEFT JOIN cat_nombres_organizaciones nameafp ON nameafp.nombreOrganizacionId = per.nombreOrganizacionIdAFP
            LEFT JOIN cat_nombres_organizaciones nameisss ON nameisss.nombreOrganizacionId = per.nombreOrganizacionIdISSS
            LEFT JOIN cat_paises_municipios expedimuni ON expedimuni.paisMunicipioId = per.paisMunicipioIdExpedicion
            LEFT JOIN cat_paises_departamentos expedidepto ON expedidepto.paisDepartamentoId = expedimuni.paisDepartamentoId
            WHERE per.prsTipoId = ? AND per.estadoPersona = ? AND per.personaId IN ($arrayEmpleadosId) AND per.flgDelete = ?
            ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
        ", ['1', $estadoPersona, '0']);
    }

    $flgShowLineas = 0;

    foreach($dataEmpleados as $dataEmpleados) {
        $flgSegundaPagina = 0;
        $numInfoGeneral = 0;
        $pdf->AddPage();

        // Este if es porque estos campos son nuevos y puede dar error
        if($dataEmpleados->fechaExpedicionIdentidad == "") {
            $lugarFechaExpedicion = "-";
            $fechaExpedicionIdentidad = "--/--/----";
        } else {
            $fechaExpedicionIdentidad = date("d/m/Y", strtotime($dataEmpleados->fechaExpedicionIdentidad));
            $lugarFechaExpedicion = $dataEmpleados->departamentoExpedicion . ", " . $dataEmpleados->municipioExpedicion;
        }

        $existeFotoPerfil = $cloud->count("
            SELECT prsAdjuntoId, urlPrsAdjunto FROM th_personas_adjuntos
            WHERE personaId = ? AND tipoPrsAdjunto = ? AND descripcionPrsAdjunto = ? AND flgDelete = ?
        ", [$dataEmpleados->personaId, 'Foto de empleado', 'Actual', '0']);

        if($existeFotoPerfil == 0) {
            $urlPrsAdjunto = 'mi-perfil/user-default.jpg';
        } else {
            $dataFotoPerfil = $cloud->row("
                SELECT prsAdjuntoId, urlPrsAdjunto FROM th_personas_adjuntos
                WHERE personaId = ? AND tipoPrsAdjunto = ? AND descripcionPrsAdjunto = ? AND flgDelete = ?
                LIMIT 1
            ", [$dataEmpleados->personaId, 'Foto de empleado', 'Actual', '0']);
            $urlPrsAdjunto = $dataFotoPerfil->urlPrsAdjunto;
        }

        $vehiculosPropios = ''; $vehiculosPropiosMultiCell = '';
        if($dataEmpleados->vehiculoPropio == "No") {
            $vehiculosPropios = "No";
            $arrayVehiculosPropios = array();
        } else {
            $arrayVehiculosPropios = explode(",", $dataEmpleados->vehiculosPropios);
            $vehiculosPropios = $arrayVehiculosPropios[0];
            for ($i=1; $i < count($arrayVehiculosPropios) - 1; $i++) { 
                $vehiculosPropios .= ($i == 1 ? ',' : '');
                $vehiculosPropiosMultiCell .= $arrayVehiculosPropios[$i] . ($i == count($arrayVehiculosPropios) - 2 ? '' : ', ');
            }
        }

        // Foto de perfil
        // url, X, Y, Weight, Height
        $pdf->Image('../../../../../libraries/resources/images/' . $urlPrsAdjunto, 10, 30, 54, 60);

        // Nombre completo centrado
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetXY(67, 30);
        $pdf->Cell(138, 6, utf8_decode($dataEmpleados->nombreCompleto), 'B', 1, 'C');

        // Recuadro bajo foto de perfil
        $pdf->SetXY(10, 92);
        $pdf->Cell(55, 167, '', 0, 0, 'C'); 

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(10, 92);
        $pdf->Cell(55, 6, utf8_decode('Datos personales'), $flgShowLineas, $flgShowLineas, 'C'); 

        $pdf->SetFont('Arial', '', 9);

        $pdf->SetDrawColor(150,150,150);

        $altura = 98; $distanciaCeldas = 5; $ancho = 10;
        // Fecha de nacimiento
        $pdf->SetXY(10, $altura);
        $pdf->CellHTML(55, $distanciaCeldas, utf8_decode('<b>Fecha de nacimiento: </b> ' . date("d/m/Y", strtotime($dataEmpleados->fechaNacimiento))), $flgShowLineas, $flgShowLineas);
        //$pdf->Cell(55, $distanciaCeldas, utf8_decode("Fecha de nacimiento: " . date("d/m/Y", strtotime($dataEmpleados->fechaNacimiento))), $flgShowLineas, $flgShowLineas, 'L'); 
        $altura += $distanciaCeldas;
        // Edad, Sexo
        $pdf->SetXY(10, $altura);
        $pdf->CellHTML(27, $distanciaCeldas, utf8_decode("<b>Edad: </b>" . date_diff(date_create($dataEmpleados->fechaNacimiento), date_create(date("Y-m-d")))->format("%y") . " años"), $flgShowLineas, $flgShowLineas);
        $pdf->SetXY(37, $altura);
        $pdf->CellHTML(28, $distanciaCeldas, utf8_decode("<b>Sexo: </b>" . ($dataEmpleados->sexo == "M" ? 'Masculino': 'Femenino')), $flgShowLineas, $flgShowLineas);
        $altura += $distanciaCeldas;
        // Estado civil
        $pdf->SetXY(10, $altura);
        $pdf->CellHTML(55, $distanciaCeldas, utf8_decode("<b>Estado civil:</b> " . $dataEmpleados->estadoCivil), $flgShowLineas, $flgShowLineas); 
        $altura += $distanciaCeldas;
        // País de origen
        $pdf->SetXY(10, $altura);
        $pdf->CellHTML(55, $distanciaCeldas, utf8_decode("<b>País de origen:</b> " . $dataEmpleados->nacionalidad), $flgShowLineas, $flgShowLineas); 
        $altura += $distanciaCeldas;
        // Vehiculo propio
        $pdf->SetXY(10, $altura);
        $pdf->CellHTML(55, $distanciaCeldas, utf8_decode("<b>Vehículo propio:</b> " . $vehiculosPropios), ($dataEmpleados->vehiculoPropio == "No" || (count($arrayVehiculosPropios) - 1 == 1) ? 'B' : $flgShowLineas), $flgShowLineas);
        if((count($arrayVehiculosPropios) - 1) > 1) {
            $altura += $distanciaCeldas;
            $pdf->SetXY(10, $altura);
            $pdf->MultiCell(55, $distanciaCeldas, utf8_decode($vehiculosPropiosMultiCell), 'B', 'L');
            $altura = $pdf->GetY();
        } else {
            // Ya quedo dibujado
            $altura += $distanciaCeldas;
        }
        // Tipo de documento
        $pdf->SetXY(10, $altura);
        $pdf->CellHTML(55, $distanciaCeldas, utf8_decode("<b>Tipo de documento: </b>" . ($dataEmpleados->docIdentidad == "DUI" ? $dataEmpleados->docIdentidad : 'C. residencia')), $flgShowLineas, $flgShowLineas); 
        $altura += $distanciaCeldas;
        // Numero de documento, Fecha de expiracion documento
        $pdf->SetXY(10, $altura);
        $pdf->CellHTML(35, $distanciaCeldas, utf8_decode("<b>N°:</b>       " . $dataEmpleados->numIdentidad), $flgShowLineas, $flgShowLineas); 
        $pdf->SetXY(45, $altura);
        $pdf->CellHTML(20, $distanciaCeldas, utf8_decode('<b>Expi.:</b> ' . reducirMesAnio($dataEmpleados->fechaExpiracionIdentidad)), $flgShowLineas, $flgShowLineas); 
        $altura += $distanciaCeldas;
        $pdf->SetXY(10, $altura);
        $pdf->CellHTML(55, $distanciaCeldas, utf8_decode("<b>Expe.:</b> " . $lugarFechaExpedicion), $flgShowLineas, $flgShowLineas); 
        $altura += $distanciaCeldas;
        $pdf->SetXY(20, $altura);
        $pdf->CellHTML(55, $distanciaCeldas, utf8_decode("($fechaExpedicionIdentidad)"), $flgShowLineas, $flgShowLineas); 
        $altura += $distanciaCeldas;
        // NIT
        $pdf->SetXY(10, $altura);
        $pdf->CellHTML(55, $distanciaCeldas, utf8_decode("<b>NIT:</b>     " . ($dataEmpleados->nit == '' || is_null($dataEmpleados->nit) ? '0000-000000-000-0' : $dataEmpleados->nit)), $flgShowLineas, $flgShowLineas); 
        $altura += $distanciaCeldas;
        // AFP
        $pdf->SetXY(10, $altura);
        $pdf->CellHTML(55, $distanciaCeldas, utf8_decode('<b>NUP:</b>   ' . ($dataEmpleados->nup == '' || is_null($dataEmpleados->nup) ? '000000000000 (N/A)' : $dataEmpleados->nup . ' (' . $dataEmpleados->nombreOrganizacionAFP . ')')), $flgShowLineas, $flgShowLineas); 
        $altura += $distanciaCeldas;
        // ISSS
        $pdf->SetXY(10, $altura);
        $pdf->CellHTML(55, $distanciaCeldas, utf8_decode('<b>ISSS:</b>  ' . $dataEmpleados->numISSS), 'B', $flgShowLineas);
        $altura += $distanciaCeldas;

        // Contactos
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(10, $altura);
        $pdf->Cell(55, 6, utf8_decode('Contactos'), $flgShowLineas, $flgShowLineas, 'C'); 
        $altura += $distanciaCeldas;

        // Contactos normales
        $dataEmpleadoContactos = $cloud->rows("
            SELECT
                pc.prsContactoId AS prsContactoId, 
                pc.tipoContactoId AS tipoContactoId, 
                tc.tipoContacto AS tipoContacto,
                pc.contactoPersona AS contactoPersona, 
                pc.descripcionPrsContacto AS descripcionPrsContacto, 
                pc.visibilidadContacto AS visibilidadContacto,
                pc.estadoContacto AS estadoContacto,
                pc.flgContactoEmergencia AS flgContactoEmergencia
            FROM th_personas_contacto pc
            JOIN cat_tipos_contacto tc ON tc.tipoContactoId = pc.tipoContactoId
            WHERE pc.personaId = ? AND flgContactoEmergencia = ? AND pc.flgDelete = ?
        ", [$dataEmpleados->personaId, '0', '0']);

        $pdf->SetFont('Arial', '', 9);

        $corrContacto = 0;
        foreach ($dataEmpleadoContactos as $dataEmpleadoContactos) {
            $pdf->SetXY(10, $altura);
            $pdf->CellHTML(35, $distanciaCeldas, chr(149) . utf8_decode(' <b>'.$dataEmpleadoContactos->tipoContacto.':</b> '), $flgShowLineas, $flgShowLineas); 

            if(strlen($dataEmpleadoContactos->contactoPersona) > 10) {
                $altura += 4;
                $ancho = 12;
                $anchoCelda = 53;
            } else {
                // Se puede dibujar en el mismo
                $ancho = 45;
                $anchoCelda = 20;
            }
            $pdf->SetXY($ancho, $altura);
            $pdf->Cell($anchoCelda, $distanciaCeldas, utf8_decode($dataEmpleadoContactos->contactoPersona), $flgShowLineas, $flgShowLineas, 'L'); 

            $altura += $distanciaCeldas;
            $corrContacto += 1;
        }

        $pdf->SetXY(10, ($corrContacto == 0 ? $altura : $altura - $distanciaCeldas));
        $pdf->Cell(55, $distanciaCeldas, utf8_decode(($corrContacto == 0 ? 'Contactos no agregados' : '')), 'B', $flgShowLineas, 'C'); 
        $altura += ($corrContacto == 0 ? $distanciaCeldas : 0);

        // Contactos de emergencia
        $dataEmpleadoContactos = $cloud->rows("
            SELECT
                pc.prsContactoId AS prsContactoId, 
                pc.tipoContactoId AS tipoContactoId, 
                tc.tipoContacto AS tipoContacto,
                pc.contactoPersona AS contactoPersona, 
                pc.descripcionPrsContacto AS descripcionPrsContacto, 
                pc.visibilidadContacto AS visibilidadContacto,
                pc.estadoContacto AS estadoContacto,
                pc.flgContactoEmergencia AS flgContactoEmergencia
            FROM th_personas_contacto pc
            JOIN cat_tipos_contacto tc ON tc.tipoContactoId = pc.tipoContactoId
            WHERE pc.personaId = ? AND flgContactoEmergencia = ? AND pc.flgDelete = ?
        ", [$dataEmpleados->personaId, '1', '0']);

        $pdf->SetFont('Arial', 'B', 10);

        $pdf->SetXY(10, $altura);
        $pdf->Cell(55, $distanciaCeldas, utf8_decode('En caso de emergencia:'), $flgShowLineas, $flgShowLineas, 'L');
        $altura += $distanciaCeldas;

        $pdf->SetFont('Arial', '', 9);

        $corrEmergencia = 0;
        foreach ($dataEmpleadoContactos as $dataEmpleadoContactos) {
            $pdf->SetXY(10, $altura);
            $pdf->CellHTML(35, $distanciaCeldas, chr(149) . utf8_decode(' <b>'.$dataEmpleadoContactos->tipoContacto.':</b> '), $flgShowLineas, $flgShowLineas); 

            if(strlen($dataEmpleadoContactos->contactoPersona) > 10) {
                $altura += 4;
                $ancho = 12;
                $anchoCelda = 55;
            } else {
                // Se puede dibujar en el mismo
                $ancho = 45;
                $anchoCelda = 20;
            }
            $pdf->SetXY($ancho, $altura);
            $pdf->Cell($anchoCelda, $distanciaCeldas, utf8_decode($dataEmpleadoContactos->contactoPersona), $flgShowLineas, $flgShowLineas, 'L'); 

            $altura += $distanciaCeldas;
            $corrEmergencia += 1;
        }

        $pdf->SetXY(10, ($corrEmergencia == 0 ? $altura : $altura - $distanciaCeldas));
        $pdf->Cell(55, $distanciaCeldas, utf8_decode(($corrEmergencia == 0 ? 'Contactos no agregados' : '')), 'B', $flgShowLineas, 'C'); 

        $altura = 40; $ancho = 68;

        // Lugar de residencia
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(68, $altura);
        $pdf->Cell(138, $distanciaCeldas, utf8_decode('Lugar de residencia'), $flgShowLineas, $flgShowLineas, 'L'); 
        $altura += $distanciaCeldas;

        $pdf->SetFont('Arial', '', 10);
        // Lugar de residencia: Segun DUI
        $pdf->SetXY(68, $altura);
        $pdf->CellHTML(21, $distanciaCeldas, utf8_decode('<b>Según DUI:</b> '), $flgShowLineas, $flgShowLineas); 
        $pdf->SetXY(89, $altura + 1);
        $pdf->MultiCell(117, 4, utf8_decode($dataEmpleados->zonaResidenciaDUI . ', ' . $dataEmpleados->municipioDUI . ', ' . $dataEmpleados->departamentoDUI), $flgShowLineas, 'L');
        $altura = $pdf->GetY();

        // Lugar de residencia: Actual
        $pdf->SetXY(68, $altura);
        $pdf->CellHTML(21, $distanciaCeldas, utf8_decode('<b>Actual:</b> '), $flgShowLineas, $flgShowLineas); 
        $pdf->SetXY(89, $altura + 1);
        $pdf->MultiCell(117, 4, utf8_decode($dataEmpleados->zonaResidenciaActual . ', ' . $dataEmpleados->municipioActual . ', ' . $dataEmpleados->departamentoActual), $flgShowLineas, 'L');
        $altura = $pdf->GetY() + 5;

        // Cerar linea
        $pdf->SetXY(68, $altura - 3);
        $pdf->Cell(138, $distanciaCeldas, '', 'T', $flgShowLineas, 'L'); 

        // Estudios
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(68, $altura);
        $pdf->Cell(138, $distanciaCeldas, utf8_decode('Estudios'), $flgShowLineas, $flgShowLineas, 'L'); 
        $altura += $distanciaCeldas;

        $dataEmpleadoEstudios = $cloud->rows("
            SELECT
                pe.prsEducacionId AS prsEducacionId, 
                pe.centroEstudio AS centroEstudio, 
                pe.nivelEstudio AS nivelEstudio, 
                pe.prsArEstudioId AS prsArEstudioId, 
                ar.areaEstudio AS areaEstudio,
                pe.nombreCarrera AS nombreCarrera, 
                pe.paisId AS paisId, 
                p.pais AS pais,
                p.iconBandera AS iconBandera,
                pe.numMesInicio AS numMesInicio, 
                pe.mesInicio AS mesInicio, 
                pe.anioInicio AS anioInicio, 
                pe.numMesFinalizacion AS numMesFinalizacion, 
                pe.mesFinalizacion AS mesFinalizacion, 
                pe.anioFinalizacion AS anioFinalizacion, 
                pe.estadoEstudio AS estadoEstudio
            FROM th_personas_educacion pe
            LEFT JOIN cat_personas_ar_estudio ar ON ar.prsArEstudioId = pe.prsArEstudioId
            JOIN cat_paises p ON p.paisId = pe.paisId
            WHERE pe.personaId = ? AND pe.flgDelete = ?
        ", [$dataEmpleados->personaId, '0']);

        $corrEstudios = 0; $distanciaCeldas = 5;
        $pdf->SetFont('Arial', '', 10);
        foreach($dataEmpleadoEstudios as $dataEmpleadoEstudios) {
            $numInfoGeneral += 1;
            $corrEstudios += 1;

            // Simbolo de lista
            $pdf->SetXY(68, $altura);
            $pdf->Cell(4, $distanciaCeldas, chr(149), $flgShowLineas, $flgShowLineas, 'C');
            // Lugar de estudio
            $pdf->SetXY(72, $altura);
            $pdf->CellHTML(12, $distanciaCeldas,utf8_decode('<b>Lugar:</b> '), $flgShowLineas, $flgShowLineas);
            $pdf->SetXY(84, $altura + 0.5);
            $pdf->MultiCell(122, 4, utf8_decode($dataEmpleadoEstudios->centroEstudio), $flgShowLineas, 'L');
            $altura = $pdf->GetY();

            // Nivel y pais
            $pdf->SetXY(72, $altura);
            $pdf->CellHTML(67, $distanciaCeldas, utf8_decode('<b>Nivel:</b> ' . $dataEmpleadoEstudios->nivelEstudio), $flgShowLineas, $flgShowLineas);
            $pdf->SetXY(139, $altura);
            $pdf->CellHTML(67, $distanciaCeldas, utf8_decode('<b>País:</b> ' . $dataEmpleadoEstudios->pais), $flgShowLineas, $flgShowLineas);
            $altura += $distanciaCeldas;

            // Validacion de otros niveles de carrera
            if($dataEmpleadoEstudios->nivelEstudio == "Técnico/Profesional" || $dataEmpleadoEstudios->nivelEstudio == "Universidad" || $dataEmpleadoEstudios->nivelEstudio == "Postgrado" || $dataEmpleadoEstudios->nivelEstudio == "Diplomado" || $dataEmpleadoEstudios->nivelEstudio == "Curso" || $dataEmpleadoEstudios->nivelEstudio == "Curso - INSAFORP") {

                // Las distancias son variables, por eso en un if cada uno
                if($dataEmpleadoEstudios->nivelEstudio == "Diplomado") {
                    $pdf->SetXY(72, $altura);
                    $pdf->CellHTML(21, $distanciaCeldas,utf8_decode('<b>Diplomado:</b> '), $flgShowLineas, $flgShowLineas);
                    $pdf->SetXY(93, $altura + 0.5);
                    $pdf->MultiCell(113, 4, utf8_decode($dataEmpleadoEstudios->nombreCarrera), $flgShowLineas, 'L');
                } else if($dataEmpleadoEstudios->nivelEstudio == "Curso" || $dataEmpleadoEstudios->nivelEstudio == "Curso - INSAFORP") {
                    $pdf->SetXY(72, $altura);
                    $pdf->CellHTML(13, $distanciaCeldas,utf8_decode('<b>Curso:</b> '), $flgShowLineas, $flgShowLineas);
                    $pdf->SetXY(85, $altura + 0.5);
                    $pdf->MultiCell(121, 4, utf8_decode($dataEmpleadoEstudios->nombreCarrera), $flgShowLineas, 'L');
                } else {
                    $pdf->SetXY(72, $altura);
                    $pdf->CellHTML(15, $distanciaCeldas,utf8_decode('<b>Carrera:</b> '), $flgShowLineas, $flgShowLineas);
                    $pdf->SetXY(87, $altura + 0.5);
                    $pdf->MultiCell(119, 4, utf8_decode($dataEmpleadoEstudios->nombreCarrera), $flgShowLineas, 'L');
                }
                $altura = $pdf->GetY();

                // Area
                $pdf->SetXY(72, $altura);
                $pdf->CellHTML(134, $distanciaCeldas, utf8_decode('<b>Área:</b> ' . $dataEmpleadoEstudios->areaEstudio), $flgShowLineas, $flgShowLineas);
                $altura += $distanciaCeldas;
            } else {
                // Es educacion basica, secundaria, media
            }

            // Periodo
            if($dataEmpleadoEstudios->estadoEstudio == "Cursando" || $dataEmpleadoEstudios->estadoEstudio == "Incompleto") {
                $estadoFinalizacion = ' - ' . $dataEmpleadoEstudios->estadoEstudio;
            } else {
                $estadoFinalizacion = ' hasta ' . mb_strtolower($dataEmpleadoEstudios->mesFinalizacion) . ' - ' . $dataEmpleadoEstudios->anioFinalizacion;
            }

            $pdf->SetXY(72, $altura);
            $pdf->CellHTML(134, $distanciaCeldas, utf8_decode('<b>Periodo:</b> De ' . mb_strtolower($dataEmpleadoEstudios->mesInicio) . ' - ' . $dataEmpleadoEstudios->anioInicio . $estadoFinalizacion), $flgShowLineas, $flgShowLineas);
            $altura += $distanciaCeldas;
            $altura += 3;
        }

        if($corrEstudios == 0) {
            // Leyenda que no se han agregado estudios
            $pdf->SetXY(68, $altura);
            $pdf->Cell(138, $distanciaCeldas, utf8_decode('Estudios no agregados'), $flgShowLineas, $flgShowLineas, 'C'); 
            $altura += $distanciaCeldas + 3;
        } else {
            // Ya se dibujaron los estudios
            $altura += 3;
        }

        // Cerar linea
        $pdf->SetXY(68, $altura - 3);
        $pdf->Cell(138, $distanciaCeldas, '', 'T', $flgShowLineas, 'L'); 

        // Experiencia laboral
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(68, $altura);
        $pdf->Cell(138, $distanciaCeldas, utf8_decode('Experiencia laboral'), $flgShowLineas, $flgShowLineas, 'L'); 
        $altura += $distanciaCeldas + 1;

        $dataEmpleadoExpLaboral = $cloud->rows("
            SELECT
                exp.prsExpLaboralId AS prsExpLaboralId, 
                exp.personaId AS personaId, 
                exp.lugarTrabajo AS lugarTrabajo, 
                exp.paisId AS paisId, 
                p.pais AS pais,
                p.iconBandera AS iconBandera,
                exp.prsArExperienciaId prsArExperienciaId, 
                ar.areaExperiencia AS areaExperiencia,
                exp.cargoTrabajo AS cargoTrabajo, 
                exp.numMesInicio AS numMesInicio, 
                exp.mesInicio AS mesInicio, 
                exp.anioInicio AS anioInicio, 
                exp.numMesFinalizacion AS numMesFinalizacion, 
                exp.mesFinalizacion AS mesFinalizacion, 
                exp.anioFinalizacion AS anioFinalizacion,
                exp.motivoRetiro AS motivoRetiro
            FROM th_personas_exp_laboral exp
            JOIN cat_personas_ar_experiencia ar ON ar.prsArExperienciaId = exp.prsArExperienciaId
            JOIN cat_paises p ON p.paisId = exp.paisId
            WHERE exp.personaId = ? AND exp.flgDelete = ?
        ", [$dataEmpleados->personaId, '0']);

        $corrExpLaboral = 0; $distanciaCeldas = 5;
        $pdf->SetFont('Arial', '', 10);
        foreach($dataEmpleadoExpLaboral as $dataEmpleadoExpLaboral) {
            $numInfoGeneral += 1;
            $corrExpLaboral += 1;

            // 254 = altura maxima por pagina
            // 23 = Medida de toda la sección
            if(($altura + 23) >= 254) {
                $pdf->AddPage();
                $altura = 30;
                $flgSegundaPagina = 1;
            } else {
                // Todavia hay espacio
            }

            // Simbolo de lista
            $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
            $pdf->Cell(4, $distanciaCeldas, chr(149), $flgShowLineas, $flgShowLineas, 'C');
            // Lugar de trabajo
            $pdf->SetXY(($flgSegundaPagina == 1 ? 14 : 72), $altura);
            $pdf->CellHTML(12, $distanciaCeldas,utf8_decode('<b>Lugar:</b> '), $flgShowLineas, $flgShowLineas);
            $pdf->SetXY(($flgSegundaPagina == 1 ? 26 : 84), $altura + 0.5);
            $pdf->MultiCell(($flgSegundaPagina == 1 ? 180 : 122), 4, utf8_decode($dataEmpleadoExpLaboral->lugarTrabajo), $flgShowLineas, 'L');
            $altura = $pdf->GetY();

            // Cargo
            $pdf->SetXY(($flgSegundaPagina == 1 ? 14 : 72), $altura);
            $pdf->CellHTML(37, $distanciaCeldas,utf8_decode('<b>Cargo desempeñado:</b> '), $flgShowLineas, $flgShowLineas); 
            $pdf->SetXY(($flgSegundaPagina == 1 ? 51 : 109), $altura + 0.5);
            $pdf->MultiCell(($flgSegundaPagina == 1 ? 155 : 97), 4, utf8_decode($dataEmpleadoExpLaboral->cargoTrabajo), $flgShowLineas, 'L');
            $altura = $pdf->GetY();

            // Area y pais
            $pdf->SetXY(($flgSegundaPagina == 1 ? 14 : 72), $altura);
            $pdf->CellHTML(($flgSegundaPagina == 1 ? 96 : 67), $distanciaCeldas, utf8_decode('<b>Área:</b> ' . $dataEmpleadoExpLaboral->areaExperiencia), $flgShowLineas, $flgShowLineas);
            $pdf->SetXY(($flgSegundaPagina == 1 ? 110 : 139), $altura);
            $pdf->CellHTML(($flgSegundaPagina == 1 ? 96 : 67), $distanciaCeldas, utf8_decode('<b>País:</b> ' . $dataEmpleadoExpLaboral->pais), $flgShowLineas, $flgShowLineas);
            $altura += $distanciaCeldas;

            // Experiencia
            $numMesFinalizacion = $dataEmpleadoExpLaboral->numMesFinalizacion + 1;
            $anioFinalizacion = ($numMesFinalizacion == 13) ? $dataEmpleadoExpLaboral->anioFinalizacion + 1 : $dataEmpleadoExpLaboral->anioFinalizacion;
            $numMesFinalizacion = ($numMesFinalizacion == 13) ? 1 : $numMesFinalizacion;
            $fechaInicio = strtotime($dataEmpleadoExpLaboral->anioInicio . "-" . $dataEmpleadoExpLaboral->numMesInicio . "-1");
            $fechaFin = strtotime($anioFinalizacion . "-" . $numMesFinalizacion . "-1");

            $pdf->SetXY(($flgSegundaPagina == 1 ? 14 : 72), $altura);
            $pdf->CellHTML(($flgSegundaPagina == 1 ? 192 : 134), $distanciaCeldas,utf8_decode('<b>Periodo:</b> De ' . mb_strtolower($dataEmpleadoExpLaboral->mesInicio) . ' - ' . $dataEmpleadoExpLaboral->anioInicio . ' hasta ' . mb_strtolower($dataEmpleadoExpLaboral->mesFinalizacion) . ' - ' . $dataEmpleadoExpLaboral->anioFinalizacion . ' (' . diferenciaFechas($fechaInicio, $fechaFin) . ')'), $flgShowLineas, $flgShowLineas);
            $altura += $distanciaCeldas;
            $altura += 3;
        }

        if($corrExpLaboral == 0) {
            // Leyenda que no se han agregado experiencia laboral
            $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
            $pdf->Cell(($flgSegundaPagina == 1 ? 196 : 138), $distanciaCeldas, utf8_decode('Experiencia laboral no agregada '), $flgShowLineas, $flgShowLineas, 'C'); 
            $altura += $distanciaCeldas + 3;
        } else {
            // Ya se dibujo la exp laboral
            $altura += 3;
        }

        // 254 = altura maxima por pagina
        // 5 - 3 = Medida de toda la sección
        if(($altura + 2) >= 254) {
            $pdf->AddPage();
            $altura = 30;
            $flgSegundaPagina = 1;
        } else {
            // Todavia hay espacio
        }

        // Cerar linea
        $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura - 3);
        $pdf->Cell(($flgSegundaPagina == 1 ? 196 : 138), $distanciaCeldas, '', 'T', $flgShowLineas, 'L'); 

        // 254 = altura maxima por pagina
        // 13 = Medida de toda la sección
        if(($altura + 13) >= 254) {
            $pdf->AddPage();
            $altura = 30;
            $flgSegundaPagina = 1;
        } else {
            // Todavia hay espacio
        }

        // Habilidades
        // Idiomas y conocimientos informaticos
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
        $pdf->Cell(($flgSegundaPagina == 1 ? 98 : 69), $distanciaCeldas, utf8_decode('Idiomas'), $flgShowLineas, $flgShowLineas, 'L');   
        $pdf->SetXY(($flgSegundaPagina == 1 ? 108 : 137), $altura);
        $pdf->Cell(($flgSegundaPagina == 1 ? 98 : 69), $distanciaCeldas, utf8_decode('Conocimientos informáticos'), $flgShowLineas, $flgShowLineas, 'L');   
        $altura += $distanciaCeldas + 1;

        $dataIdiomasEmpleado = $cloud->rows("
            SELECT
                habilidadPersona, nivelHabilidad
            FROM th_personas_habilidades
            WHERE personaId = ? AND tipoHabilidad = ? AND flgDelete = ?
        ", [$dataEmpleados->personaId, 'Idioma', '0']);

        $pdf->SetFont('Arial', '', 10);
        $corrIdiomas = 0; $distanciaCeldas = 5;
        $alturaIdiomas = $altura;
        foreach ($dataIdiomasEmpleado as $dataIdiomasEmpleado) {
            $corrIdiomas += 1;

            // 254 = altura maxima por pagina
            // 5 = Medida de toda la sección
            if(($altura + 5) >= 254) {
                $pdf->AddPage();
                $altura = 30;
                $flgSegundaPagina = 1;
            } else {
                // Todavia hay espacio
            }

            // Simbolo de lista
            $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
            $pdf->Cell(4, $distanciaCeldas, chr(149), $flgShowLineas, $flgShowLineas, 'C');

            // Idioma
            $pdf->SetXY(($flgSegundaPagina == 1 ? 14 : 72), $altura);
            $pdf->CellHTML(($flgSegundaPagina == 1 ? 94 : 65), $distanciaCeldas, utf8_decode($dataIdiomasEmpleado->habilidadPersona . " (" . $dataIdiomasEmpleado->nivelHabilidad . ")"), $flgShowLineas, $flgShowLineas);
            $altura += $distanciaCeldas;
        }

        if($corrIdiomas == 0) {
            // Leyenda que no se han agregado idiomas
            $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
            $pdf->Cell(($flgSegundaPagina == 1 ? 98 : 69), $distanciaCeldas, utf8_decode('Idiomas no agregados'), $flgShowLineas, $flgShowLineas, 'L'); 
            $altura += $distanciaCeldas + 3;
        } else {
            // Ya se dibujaron los idiomas
            $altura += 3;
        }
        $alturaColumna1 = $altura;

        $dataInformaticaEmpleado = $cloud->rows("
            SELECT
                habilidadPersona, nivelHabilidad
            FROM th_personas_habilidades
            WHERE personaId = ? AND tipoHabilidad = ? AND flgDelete = ?
        ", [$dataEmpleados->personaId, 'Informática', '0']);

        $pdf->SetFont('Arial', '', 10);
        $corrInformatica = 0; $distanciaCeldas = 5;
        $altura = $alturaIdiomas; $alturaColumna2 = 0;
        foreach ($dataInformaticaEmpleado as $dataInformaticaEmpleado) {
            $corrInformatica += 1;

            // 254 = altura maxima por pagina
            // 5 = Medida de toda la sección
            if(($altura + 5) >= 254) {
                $pdf->AddPage();
                $altura = 30;
                $flgSegundaPagina = 1;
                $alturaIdiomas = $altura;
            } else {
                // Todavia hay espacio
            }

            // Simbolo de lista
            $pdf->SetXY(($flgSegundaPagina == 1 ? 108 : 137), $altura);
            $pdf->Cell(4, $distanciaCeldas, chr(149), $flgShowLineas, $flgShowLineas, 'C');

            // Conocimiento informatico
            $pdf->SetXY(($flgSegundaPagina == 1 ? 112 : 141), $altura + 0.5);
            $pdf->MultiCell(($flgSegundaPagina == 1 ? 94 : 65), 4, utf8_decode($dataInformaticaEmpleado->habilidadPersona . " (" . $dataInformaticaEmpleado->nivelHabilidad . ")"), $flgShowLineas, 'L');
            $altura = $pdf->GetY();
        }

        if($corrInformatica == 0) {
            // Leyenda que no se han agregado conocimientos informaticos
            $pdf->SetXY(($flgSegundaPagina == 1 ? 108 : 137), $altura);
            $pdf->Cell(($flgSegundaPagina == 1 ? 98 : 69), $distanciaCeldas, utf8_decode('Conoc. informáticos no agregados'), $flgShowLineas, $flgShowLineas, 'L'); 
            $altura += $distanciaCeldas + 3;
        } else {
            // Ya se dibujaron los conocimientos informaticos
            $altura += 3;
        }
        $alturaColumna2 = $altura;

        $altura = ($alturaColumna1 > $alturaColumna2 ? $alturaColumna1 : $alturaColumna2);

        // 254 = altura maxima por pagina
        // 13 = Medida de toda la sección
        if(($altura + 13) >= 254) {
            $pdf->AddPage();
            $altura = 30;
            $flgSegundaPagina = 1;
        } else {
            // Todavia hay espacio
        }

        // Conoocimientos-habilidades y Herramientas-equipos
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
        $pdf->Cell(($flgSegundaPagina == 1 ? 98 : 69), $distanciaCeldas, utf8_decode('Conocimientos/Habilidades'), $flgShowLineas, $flgShowLineas, 'L');   
        $pdf->SetXY(($flgSegundaPagina == 1 ? 108 : 137), $altura);
        $pdf->Cell(($flgSegundaPagina == 1 ? 98 : 69), $distanciaCeldas, utf8_decode('Herramientas/Equipo'), $flgShowLineas, $flgShowLineas, 'L');   
        $altura += $distanciaCeldas + 1;

        $dataConocimientosEmpleado = $cloud->rows("
            SELECT
                habilidadPersona, nivelHabilidad
            FROM th_personas_habilidades
            WHERE personaId = ? AND tipoHabilidad = ? AND flgDelete = ?
        ", [$dataEmpleados->personaId, 'Habilidad', '0']);

        $pdf->SetFont('Arial', '', 10);
        $corrConocimientos = 0; $distanciaCeldas = 5;
        $alturaConocimientos = $altura;
        foreach ($dataConocimientosEmpleado as $dataConocimientosEmpleado) {
            $corrConocimientos += 1;

            // 254 = altura maxima por pagina
            // 5 = Medida de toda la sección
            if(($altura + 5) >= 254) {
                $pdf->AddPage();
                $altura = 30;
                $flgSegundaPagina = 1;
                $alturaConocimientos = $altura;
            } else {
                // Todavia hay espacio
            }

            // Simbolo de lista
            $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
            $pdf->Cell(4, $distanciaCeldas, chr(149), $flgShowLineas, $flgShowLineas, 'C');

            // Habilidad
            $pdf->SetXY(($flgSegundaPagina == 1 ? 14 : 72), $altura + 0.5);
            $pdf->MultiCell(($flgSegundaPagina == 1 ? 94 : 65), 4, utf8_decode($dataConocimientosEmpleado->habilidadPersona . " (" . $dataConocimientosEmpleado->nivelHabilidad . ")"), $flgShowLineas, 'L');
            $altura = $pdf->GetY();
        }

        if($corrConocimientos == 0) {
            // Leyenda que no se han agregado idiomas
            $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
            //$pdf->SetFont('Arial', '', 10);
            $pdf->Cell(($flgSegundaPagina == 1 ? 98 : 69), $distanciaCeldas, utf8_decode('Conoc./Habilidades no agregados'), $flgShowLineas, $flgShowLineas, 'L'); 
            $altura += $distanciaCeldas + 3;
        } else {
            // Ya se dibujaron los idiomas
            $altura += 3;
        }
        $alturaColumna1 = $altura;

        $dataEquipoEmpleados = $cloud->rows("
            SELECT
                habilidadPersona, nivelHabilidad
            FROM th_personas_habilidades
            WHERE personaId = ? AND tipoHabilidad = ? AND flgDelete = ?
        ", [$dataEmpleados->personaId, 'Equipo', '0']);

        $pdf->SetFont('Arial', '', 10);
        $corrEquipos = 0; $distanciaCeldas = 5;
        $altura = $alturaConocimientos; $alturaColumna2 = 0;
        foreach ($dataEquipoEmpleados as $dataEquipoEmpleados) {
            $corrEquipos += 1;

            // 254 = altura maxima por pagina
            // 5 = Medida de toda la sección
            if(($altura + 5) >= 254) {
                $pdf->AddPage();
                $altura = 30;
                $flgSegundaPagina = 1;
            } else {
                // Todavia hay espacio
            }

            // Simbolo de lista
            $pdf->SetXY(($flgSegundaPagina == 1 ? 108 : 137), $altura);
            $pdf->Cell(4, $distanciaCeldas, chr(149), $flgShowLineas, $flgShowLineas, 'C');

            // Conocimiento informatico
            $pdf->SetXY(($flgSegundaPagina == 1 ? 112 : 141), $altura + 0.5);
            $pdf->MultiCell(($flgSegundaPagina == 1 ? 94 : 65), 4, utf8_decode($dataEquipoEmpleados->habilidadPersona . " (" . $dataEquipoEmpleados->nivelHabilidad . ")"), $flgShowLineas, 'L');
            $altura = $pdf->GetY();
        }

        if($corrEquipos == 0) {
            // Leyenda que no se han agregado conocimientos informaticos
            $pdf->SetXY(($flgSegundaPagina == 1 ? 108 : 137), $altura);
            $pdf->Cell(($flgSegundaPagina == 1 ? 98 : 69), $distanciaCeldas, utf8_decode('Herramientas/Equipo no agregados'), $flgShowLineas, $flgShowLineas, 'L'); 
            $altura += $distanciaCeldas + 3;
        } else {
            // Ya se dibujaron los conocimientos informaticos
            $altura += 3;
        }
        $alturaColumna2 = $altura;

        $altura = ($alturaColumna1 > $alturaColumna2 ? $alturaColumna1 : $alturaColumna2) + 3;

        // 254 = altura maxima por pagina
        // 5 - 3 = Medida de toda la sección
        if(($altura + 2) >= 254) {
            $pdf->AddPage();
            $altura = 30;
            $flgSegundaPagina = 1;
        } else {
            // Todavia hay espacio
        }

        // Cerar linea
        $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura - 3);
        $pdf->Cell(($flgSegundaPagina == 1 ? 196 : 138), $distanciaCeldas, '', 'T', $flgShowLineas, 'L'); 

        // 254 = altura maxima por pagina
        // 13 = Medida de toda la sección
        if(($altura + 13) >= 254) {
            $pdf->AddPage();
            $altura = 30;
            $flgSegundaPagina = 1;
        } else {
            // Todavia hay espacio
        }

        // Licencias de conducir
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
        $pdf->Cell(($flgSegundaPagina == 1 ? 196 : 138), $distanciaCeldas, utf8_decode('Licencias de conducir'), $flgShowLineas, $flgShowLineas, 'L'); 
        $altura += $distanciaCeldas + 1;

        $dataEmpleadoConducir = $cloud->rows("
            SELECT
                tipoLicencia, numLicencia, fechaExpiracionLicencia, descripcionLicencia
            FROM th_personas_licencias
            WHERE personaId = ? AND categoriaLicencia = ? AND flgDelete = ?
        ", [$dataEmpleados->personaId, 'Conducir', '0']);

        $corrLicenciaConducir = 0; $distanciaCeldas = 5;
        $pdf->SetFont('Arial', '', 10);

        foreach($dataEmpleadoConducir as $dataEmpleadoConducir) {
            $corrLicenciaConducir += 1;

            // 254 = altura maxima por pagina
            // 13 = Medida de toda la sección
            if(($altura + 5) >= 254) {
                $pdf->AddPage();
                $altura = 30;
                $flgSegundaPagina = 1;
            } else {
                // Todavia hay espacio
            }

            // Simbolo de lista
            $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
            $pdf->Cell(4, $distanciaCeldas, chr(149), $flgShowLineas, $flgShowLineas, 'C');
            // Tipo de licencia
            $pdf->SetXY(($flgSegundaPagina == 1 ? 14 : 72), $altura);
            $pdf->CellHTML(($flgSegundaPagina == 1 ? 70 : 50), $distanciaCeldas,utf8_decode('<b>Tipo:</b> ' . $dataEmpleadoConducir->tipoLicencia), $flgShowLineas, $flgShowLineas);
            // Numero de licencia
            $pdf->SetXY(($flgSegundaPagina == 1 ? 84 : 122), $altura);
            $pdf->CellHTML(($flgSegundaPagina == 1 ? 65 : 45), $distanciaCeldas,utf8_decode('<b>N°:</b> ' . $dataEmpleadoConducir->numLicencia), $flgShowLineas, $flgShowLineas);
            // Expiración
            $pdf->SetXY(($flgSegundaPagina == 1 ? 149 : 167), $altura);
            $pdf->CellHTML(($flgSegundaPagina == 1 ? 57 : 39), $distanciaCeldas,utf8_decode('<b>Expiración:</b> ' . reducirMesAnio($dataEmpleadoConducir->fechaExpiracionLicencia)), $flgShowLineas, $flgShowLineas);

            $altura += $distanciaCeldas;
        }

        if($corrLicenciaConducir == 0) {
            // Leyenda que no se han agregado licencias
            $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
            $pdf->Cell(($flgSegundaPagina == 1 ? 196 : 138), $distanciaCeldas, utf8_decode('Licencias de conducir no agregadas'), $flgShowLineas, $flgShowLineas, 'C'); 
            $altura += $distanciaCeldas + 3;
        } else {
            // Ya se dibujaron los estudios
            $altura += 3;
        }
        $altura += 3;
        
        // 254 = altura maxima por pagina
        // 13 = Medida de toda la sección
        if(($altura + 2) >= 254) {
            $pdf->AddPage();
            $altura = 30;
            $flgSegundaPagina = 1;
        } else {
            // Todavia hay espacio
        }

        // Cerar linea
        $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura - 3);
        $pdf->Cell(($flgSegundaPagina == 1 ? 196 : 138), $distanciaCeldas, '', 'T', $flgShowLineas, 'L'); 

        // Validar si existen licencias de arma, sino no dibujar nada
        $existeLicenciaArma = $cloud->count("
            SELECT
                tipoLicencia, numLicencia, fechaExpiracionLicencia, descripcionLicencia
            FROM th_personas_licencias
            WHERE personaId = ? AND categoriaLicencia = ? AND flgDelete = ?
        ", [$dataEmpleados->personaId, 'Arma', '0']);

        if($existeLicenciaArma > 0) {
            // 254 = altura maxima por pagina
            // 13 = Medida de toda la sección
            if(($altura + 13) >= 254) {
                $pdf->AddPage();
                $altura = 30;
                $flgSegundaPagina = 1;
            } else {
                // Todavia hay espacio
            }

            // Experiencia laboral
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
            $pdf->Cell(($flgSegundaPagina == 1 ? 196 : 138), $distanciaCeldas, utf8_decode('Licencias para el uso de armas de fuego'), $flgShowLineas, $flgShowLineas, 'L'); 
            $altura += $distanciaCeldas + 1;

            $dataEmpleadoArna = $cloud->rows("
                SELECT
                    tipoLicencia, numLicencia, fechaExpiracionLicencia, descripcionLicencia
                FROM th_personas_licencias
                WHERE personaId = ? AND categoriaLicencia = ? AND flgDelete = ?
            ", [$dataEmpleados->personaId, 'Arma', '0']);

            $corrLicenciaArma = 0; $distanciaCeldas = 5;
            $pdf->SetFont('Arial', '', 10);

            foreach($dataEmpleadoArna as $dataEmpleadoArna) {
                $corrLicenciaArma += 1;

                // 254 = altura maxima por pagina
                // 13 = Medida de toda la sección
                if(($altura + 5) >= 254) {
                    $pdf->AddPage();
                    $altura = 30;
                    $flgSegundaPagina = 1;
                } else {
                    // Todavia hay espacio
                }

                // Simbolo de lista
                $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
                $pdf->Cell(4, $distanciaCeldas, chr(149), $flgShowLineas, $flgShowLineas, 'C');
                // Tipo de licencia
                $pdf->SetXY(($flgSegundaPagina == 1 ? 14 : 72), $altura);
                $pdf->CellHTML(($flgSegundaPagina == 1 ? 70 : 50), $distanciaCeldas,utf8_decode('<b>Tipo:</b> Uso de armas de fuego'), $flgShowLineas, $flgShowLineas);
                // Numero de licencia
                $pdf->SetXY(($flgSegundaPagina == 1 ? 84 : 122), $altura);
                $pdf->CellHTML(($flgSegundaPagina == 1 ? 65 : 45), $distanciaCeldas,utf8_decode('<b>N°:</b> ' . $dataEmpleadoArna->numLicencia), $flgShowLineas, $flgShowLineas);
                // Expiración
                $pdf->SetXY(($flgSegundaPagina == 1 ? 149 : 167), $altura);
                $pdf->CellHTML(($flgSegundaPagina == 1 ? 57 : 39), $distanciaCeldas,utf8_decode('<b>Expiración:</b> ' . reducirMesAnio($dataEmpleadoArna->fechaExpiracionLicencia)), $flgShowLineas, $flgShowLineas);

                $altura += $distanciaCeldas;
            }
            $altura += 6;
            // No se muestra mensaje que no se han agregado porque si no posee no se muestra esta sección
            // 254 = altura maxima por pagina
            // 13 = Medida de toda la sección
            if(($altura + 2) >= 254) {
                $pdf->AddPage();
                $altura = 30;
                $flgSegundaPagina = 1;
            } else {
                // Todavia hay espacio
            }

            // Solo cerrar la línea
            // Cerar linea
            $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura - 3);
            $pdf->Cell(($flgSegundaPagina == 1 ? 196 : 138), $distanciaCeldas, '', 'T', $flgShowLineas, 'L'); 
        } else {
            // No dibujar esta sección
        }

        // 254 = altura maxima por pagina
        // 13 = Medida de toda la sección
        if(($altura + 13) >= 254) {
            $pdf->AddPage();
            $altura = 30;
            $flgSegundaPagina = 1;
        } else {
            // Todavia hay espacio
        }

        // Enfermedades y alergias
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
        $pdf->Cell(($flgSegundaPagina == 1 ? 98 : 69), $distanciaCeldas, utf8_decode('Enfermedades'), $flgShowLineas, $flgShowLineas, 'L');   
        $pdf->SetXY(($flgSegundaPagina == 1 ? 108 : 137), $altura);
        $pdf->Cell(($flgSegundaPagina == 1 ? 98 : 69), $distanciaCeldas, utf8_decode('Alergias'), $flgShowLineas, $flgShowLineas, 'L');   
        $altura += $distanciaCeldas + 1;

        $dataEnfermedadesEmpleado = $cloud->rows("
            SELECT
                cpe.nombreEnfermedad AS nombreEnfermedad
            FROM th_personas_enfermedades pe
            JOIN cat_personas_enfermedades cpe ON cpe.catPrsEnfermedadId = pe.catPrsEnfermedadId
            WHERE pe.personaId = ? AND cpe.tipoEnfermedad = ? AND pe.flgDelete = ?
        ", [$dataEmpleados->personaId, 'Enfermedad', '0']);

        $pdf->SetFont('Arial', '', 10);
        $corrEnfermedades = 0; $distanciaCeldas = 5;
        $alturaEnfermedades = $altura;
        foreach ($dataEnfermedadesEmpleado as $dataEnfermedadesEmpleado) {
            $corrEnfermedades += 1;

            // 254 = altura maxima por pagina
            // 13 = Medida de toda la sección
            if(($altura + 5) >= 254) {
                $pdf->AddPage();
                $altura = 30;
                $flgSegundaPagina = 1;
                $alturaEnfermedades = $altura;
            } else {
                // Todavia hay espacio
            }

            // Simbolo de lista
            $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
            $pdf->Cell(4, $distanciaCeldas, chr(149), $flgShowLineas, $flgShowLineas, 'C');

            // Enfermedad
            $pdf->SetXY(($flgSegundaPagina == 1 ? 14 : 72), $altura);
            $pdf->Cell(($flgSegundaPagina == 1 ? 94 : 65), $distanciaCeldas, utf8_decode($dataEnfermedadesEmpleado->nombreEnfermedad), $flgShowLineas, $flgShowLineas, 'L');
            $altura += $distanciaCeldas;
        }

        if($corrEnfermedades == 0) {
            // Leyenda que no se han agregado enfermedades
            $pdf->SetXY(($flgSegundaPagina == 1 ? 10 : 68), $altura);
            //$pdf->SetFont('Arial', '', 10);
            $pdf->Cell(($flgSegundaPagina == 1 ? 98 : 69), $distanciaCeldas, utf8_decode('Enfermedades no agregadas'), $flgShowLineas, $flgShowLineas, 'L'); 
            $altura += $distanciaCeldas + 3;
        } else {
            // Ya se dibujaron las enfermedades
            $altura += 3;
        }
        $alturaColumna1 = $altura;

        $altura = $alturaEnfermedades;
        $dataAlergiasEmpleado = $cloud->rows("
            SELECT
                cpe.nombreEnfermedad AS nombreEnfermedad
            FROM th_personas_enfermedades pe
            JOIN cat_personas_enfermedades cpe ON cpe.catPrsEnfermedadId = pe.catPrsEnfermedadId
            WHERE pe.personaId = ? AND cpe.tipoEnfermedad = ? AND pe.flgDelete = ?
        ", [$dataEmpleados->personaId, 'Alergia', '0']);

        $pdf->SetFont('Arial', '', 10);
        $corrAlergias = 0; $distanciaCeldas = 5;
        foreach ($dataAlergiasEmpleado as $dataAlergiasEmpleado) {
            $corrAlergias += 1;

            // 254 = altura maxima por pagina
            // 13 = Medida de toda la sección
            if(($altura + 5) >= 254) {
                $pdf->AddPage();
                $altura = 30;
                $flgSegundaPagina = 1;
            } else {
                // Todavia hay espacio
            }


            // Simbolo de lista
            $pdf->SetXY(($flgSegundaPagina == 1 ? 108 : 137), $altura);
            $pdf->Cell(4, $distanciaCeldas, chr(149), $flgShowLineas, $flgShowLineas, 'C');

            // Alergia
            $pdf->SetXY(($flgSegundaPagina == 1 ? 112 : 141), $altura);
            $pdf->Cell(($flgSegundaPagina == 1 ? 94 : 65), $distanciaCeldas, utf8_decode($dataAlergiasEmpleado->nombreEnfermedad), $flgShowLineas, $flgShowLineas, 'L');
            $altura += $distanciaCeldas;
        }

        if($corrAlergias == 0) {
            // Leyenda que no se han agregado alergias
            $pdf->SetXY(($flgSegundaPagina == 1 ? 108 : 137), $altura);
            //$pdf->SetFont('Arial', '', 10);
            $pdf->Cell(($flgSegundaPagina == 1 ? 98 : 69), $distanciaCeldas, utf8_decode('Alergias no agregadas'), $flgShowLineas, $flgShowLineas, 'L'); 
            $altura += $distanciaCeldas + 3;
        } else {
            // Ya se dibujaron las alergias
            $altura += 3;
        }
        $alturaColumna2 = $altura;

        $altura = ($alturaColumna1 > $alturaColumna2 ? $alturaColumna1 : $alturaColumna2) + 3;

        if(isset($_REQUEST['flgFirmaEmpleado'])) { 
            // Firma de validacion de datos del empleado

            // 254 = altura maxima por pagina
            // 25 = Medida de toda la sección
            if(($altura + 17) >= 254) {
                $pdf->AddPage();
                $altura = 30;
                $flgSegundaPagina = 1;
            } else {
                // Todavia hay espacio
                if($numInfoGeneral == 0) {
                    // Fix si no ha agregado nada de información para que no se monte la línea
                    $altura += 15;
                } else {
                    // Existe información, distancia normal
                    $altura += 3;
                }
            }

            $pdf->SetDrawColor(0,0,0);
            $pdf->SetXY(10, $altura);
            $pdf->Cell(196, $distanciaCeldas, utf8_decode('He leído y confirmo que la información proporcionada es correcta:'), $flgShowLineas, $flgShowLineas, 'C');
            $altura += $distanciaCeldas * 3;
            $pdf->SetXY(90, $altura);
            $pdf->Cell(50, $distanciaCeldas, utf8_decode('Empleado'), 'T', 0, 'C');
        } else {
            // Se dejó el switch apagado
        }
        
        unset($arrayVehiculosPropios);
        unset($arrayDireccion);
    }

    $pdf->Output($outputReporte . '.pdf', "I");

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