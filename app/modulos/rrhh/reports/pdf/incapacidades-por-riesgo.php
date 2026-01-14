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
            $this->SetXY(15, 8);
            $this->Cell(250, 5, utf8_decode('Industrial La Palma S.A de C.V.'), 0, 0, 'C');
            $this->SetXY(15, 13);
            $this->Cell(250, 5, utf8_decode('Departamento de Recursos Humanos'), 0, 0, 'C');
            $this->SetXY(15, 18);
            $this->Cell(250, 5, utf8_decode($tituloReporte), 0, 0, 'C');
            $this->SetXY(15, 23);
            $this->Cell(250, 5, utf8_decode($subtituloReporte), 0, 0, 'C');

            // Texto a la derecha del reporte
            $this->SetFont('Arial', '', 8);
            // Existe 216 de ancho en X
            $this->SetXY(225, 8);
            $this->Cell(40, 4, date("d-m-Y H:i:s"), 0, 0, 'R');
            $this->SetXY(225, 12);
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


        // Encabezados de la tabla
        function encabezadosTabla($x = 15, $y = 35) {
            global $txtEncabezado;
            $this->SetFont('Arial', 'B', 10);
            // Segun la pagina, establecer X y Y aca
            $this->SetXY($x, $y);
            $this->SetWidths(array(250));
            $this->Row(array($x, $y, 5), array($txtEncabezado), array(1, "No"), array("L"));
            $this->SetFont('Arial', 'B', 8);
            $this->SetWidths(array(10, 50, 30, 30, 30, 30, 20, 20, 30));
            $this->Row(array($x, $y, 30), array("#", "Empleado", "Cargo", "Departamento", "Sucursal", "Diagnóstico", "Fecha inicio", "Fecha fin", ""), array(1, "No"));

            $this->SetXY(235, 40);
            $this->SetWidths(array(30));
            $this->Row(array(235, 35, 4), array("Riesgo"), array(1, "No"));

            $this->SetXY(235, 44);
            $this->SetWidths(array(6, 6, 6, 6, 6));
            $this->Row(array(235, 35, 26), array("", "", "", "", ""), array(1, "No"));

            $this->SetFont('Arial', 'B', 6);
            $this->TextWithRotation(239, 69, utf8_decode('Enfermedad común'), 90, 0);
            $this->TextWithRotation(245, 69, utf8_decode('Enfermedad profesional'), 90, 0);
            $this->TextWithRotation(251, 69, utf8_decode('Accidente común'), 90, 0);
            $this->TextWithRotation(257, 69, utf8_decode('Accidente de trabajo'), 90, 0);
            $this->TextWithRotation(263, 69, utf8_decode('Maternidad'), 90, 0);

            // Esto es para los registros de la tabla
            $this->SetXY(15, 70);
            $this->SetWidths(array(10, 50, 30, 30, 30, 30, 20, 20, 6, 6, 6, 6, 6));
            $this->SetFont('Arial', '', 7);
        }
        function encabezadosTabla2($x = 15, $y = 35) {
            $this->SetFont('Arial', 'B', 8);
            $this->SetXY($x,$y);
            $this->SetWidths(array(250));
            $this->Row(array($x, $y, 30), array("Totales"), array(1, "No"));
        
            $this->SetXY(235, $y);
            $this->SetWidths(array(30));
            $this->Row(array(235, 35, 4), array("Riesgo"), array(1, "No"));
        
            $this->SetXY(235, $y + 4);
            $this->SetWidths(array(6, 6, 6, 6, 6));
            $this->Row(array(235, 35, 26), array("", "", "", "", ""), array(1, "No"));
        
            $this->SetFont('Arial', 'B', 6);
            $this->TextWithRotation(239, 64, utf8_decode('Enfermedad común'), 90, 0);
            $this->TextWithRotation(245, 64, utf8_decode('Enfermedad profesional'), 90, 0);
            $this->TextWithRotation(251, 64, utf8_decode('Accidente común'), 90, 0);
            $this->TextWithRotation(257, 64, utf8_decode('Accidente de trabajo'), 90, 0);
            $this->TextWithRotation(263, 64, utf8_decode('Maternidad'), 90, 0);
            $this->SetFont('Arial', 'B', 8);
            $this->SetWidths(array(220, 6, 6, 6, 6, 6));
        }
    }
    

    /*
        REQUEST:
        filtroIncapacidades
    */
    $filtroIncapacidades = base64_decode(urldecode($_REQUEST['filtroIncapacidades']));
    $filtroSucursales = (isset($_REQUEST['filtroSucursal']) ? base64_decode(urldecode($_REQUEST['filtroSucursal'])) : '');
    $sucursalesId = (isset($_REQUEST['selectSucursalesEspecificas']) ? base64_decode(urldecode($_REQUEST['selectSucursalesEspecificas'])) : '');

    $filtroEmpleados = (isset($_REQUEST['filtroEmpleado']) ? base64_decode(urldecode($_REQUEST['filtroEmpleado'])) : '');
    $expedientesId = (isset($_REQUEST['selectEmpleadosEspecificos']) ? base64_decode(urldecode($_REQUEST['selectEmpleadosEspecificos'])) : '');

    $fechaInicio = base64_decode(urldecode($_REQUEST['fechaInicio']));
    $fechaFin = base64_decode(urldecode($_REQUEST['fechaFin']));

    $estadoEmpleado = base64_decode(urldecode($_REQUEST['selectEstadoEmpleado']));
    
    $tituloReporte = "Registro de incapacidades por";
    $subtituloReporte = "salud y accidentes laborales";
    
    $outputReporte = "Registro de incapacidades por salud y accidentes laborales (Fecha inicio a Fecha fin)";

    $pdf = new PDF('L','mm','Letter');
    $pdf->AliasNbPages();
    $pdf->SetTitle($outputReporte);
    $whereSucursales = "";
    $whereEmpleados = "";

    $whereEstadoEmpleados = "";
    if($estadoEmpleado == "Activos") {
        $whereEstadoEmpleados = "AND exp.estadoExpediente = 'Activo' AND per.estadoPersona = 'Activo'";
    } else if($estadoEmpleado == "Inactivos") {
        $whereEstadoEmpleados = "AND per.estadoPersona = 'Inactivo' AND (exp.estadoExpediente =  'Renuncia' OR exp.estadoExpediente =  'Despido' OR exp.estadoExpediente =  'Inactivo' OR exp.estadoExpediente =  'Finalizado' OR exp.estadoExpediente =  'Jubilado' OR exp.estadoExpediente = 'Abandono' OR exp.estadoExpediente = 'Defunción' OR exp.estadoExpediente = 'Traslado')";
    } else {
        // Todos los estados
    }

    if($filtroIncapacidades == "Sucursales") {
        if ($filtroSucursales == 'Todas') {
            $whereSucursales = "";
        } else {
            // Especifico o Inactivos
            $whereSucursales = "AND sucursalId IN ($sucursalesId)";
        }
        $dataFiltroIncapacidades = $cloud->rows("
            SELECT 
                sucursalId,
                sucursal
            FROM cat_sucursales 
            WHERE flgDelete = ? $whereSucursales 
            ORDER BY sucursal
        ", [0]);
    } else {
        // Empleados
        if ($filtroEmpleados == 'Todos') {
            $whereEmpleados = "";
        }else{
            //Especificos o Inactivos
            $whereEmpleados = "AND inc.expedienteId IN ($expedientesId) ";
        }
        $dataFiltroIncapacidades = $cloud->rows("
            SELECT
                inc.expedienteId AS prsExpedienteId,
                CONCAT(
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-'),
                    ', ',
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-')
                ) AS nombreCompleto
            FROM th_expediente_incapacidades inc
            JOIN th_expediente_personas exp ON exp.prsExpedienteId = inc.expedienteId
            JOIN th_personas per ON per.personaId = exp.personaId
            WHERE per.prsTipoId = ? AND inc.flgDelete = ? AND inc.fechaInicio BETWEEN ? AND ? $whereEmpleados $whereEstadoEmpleados
            GROUP BY inc.expedienteId
            ORDER BY inc.fechaInicio DESC
        ",[1, 0, $fechaInicio, $fechaFin]);
    }

    $x = 15;
    $altura = 70;
    $arrayRiesgoSucursal = array(array("", "", "", "", ""), array(0,0,0,0,0),);
    $arrayRiesgoTotales = array();
    $arrayTotalGeneral = array(0,0,0,0,0);
    $correlativo = 0; $corrTotal = 0;
    foreach ($dataFiltroIncapacidades as $todasSucursales) {
        $pdf->AddPage();
        $corrTotal++;
        $arrayRiesgoSucursal[1] = array(0,0,0,0,0);

        if($filtroIncapacidades == "Sucursales") {
            $txtEncabezado = "Sucursal: $todasSucursales->sucursal";
            $whereIncapacidades = "AND sdep.sucursalId = $todasSucursales->sucursalId";
        } else {
            $txtEncabezado = "Empleado: $todasSucursales->nombreCompleto";
            $whereIncapacidades = "AND inc.expedienteId = $todasSucursales->prsExpedienteId";
        }

        $pdf->encabezadosTabla();
        $correlativo = 0;
        $dataIncapacidades =  $cloud->rows("
            SELECT 
                CONCAT(
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-'),
                    ', ',
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-')
                ) AS nombreCompleto,
                pc.cargoPersona as cargoPersona,
                sdep.departamentoSucursal as departamentoSucursal,
                s.sucursal AS sucursal,
                inc.motivoIncapacidad AS diagnostico,
                inc.fechaInicio AS fechaInicio,
                inc.fechaFin AS fechaFin,
                inc.riesgoIncapacidad AS riesgoIncapacidad
            FROM th_expediente_incapacidades inc
            JOIN th_expediente_personas exp ON exp.prsExpedienteId = inc.expedienteId
            JOIN th_personas per ON per.personaId = exp.personaId
            JOIN cat_sucursales_departamentos sdep ON sdep.sucursalDepartamentoId = exp.sucursalDepartamentoId
            JOIN cat_personas_cargos pc ON pc.prsCargoId = exp.prsCargoId
            JOIN cat_sucursales s ON s.sucursalId = sdep.sucursalId
            WHERE inc.flgDelete = ? AND  inc.fechaInicio BETWEEN ? AND ? $whereIncapacidades $whereEstadoEmpleados
            ORDER BY inc.fechaInicio DESC
        ", [0,$fechaInicio,$fechaFin]);
        foreach ($dataIncapacidades as $dataIncapacidades) {
            $arrayRiesgoSucursal[0] = array("", "", "", "", "");

            $riesgoIncapacidad = $dataIncapacidades->riesgoIncapacidad;
            
            switch($riesgoIncapacidad) {
                case 'Enfermedad común':
                    $arrayRiesgoSucursal[0][0] = "X";
                    $arrayRiesgoSucursal[1][0] += 1;
                break;
                
                case 'Enfermedad profesional':
                    $arrayRiesgoSucursal[0][1] = "X";
                    $arrayRiesgoSucursal[1][1] += 1;
                break;
    
                case 'Accidente común':
                    $arrayRiesgoSucursal[0][2] = "X";
                    $arrayRiesgoSucursal[1][2] += 1;
                break;
    
                case 'Accidente de trabajo':
                    $arrayRiesgoSucursal[0][3] = "X";
                    $arrayRiesgoSucursal[1][3] += 1;
                break;
    
                case 'Maternidad':
                    $arrayRiesgoSucursal[0][4] = "X";
                    $arrayRiesgoSucursal[1][4] += 1;
                break;
    
                default:
                    // No marcar ninguna X
                break;
            }
            $pdf->Row(array($x, $altura, 5), array($correlativo += 1 , $dataIncapacidades->nombreCompleto, $dataIncapacidades->cargoPersona, $dataIncapacidades->departamentoSucursal, $dataIncapacidades->sucursal,$dataIncapacidades->diagnostico, date("d-m-Y", strtotime($dataIncapacidades->fechaInicio)), date("d-m-Y", strtotime($dataIncapacidades->fechaFin)), $arrayRiesgoSucursal[0][0], $arrayRiesgoSucursal[0][1], $arrayRiesgoSucursal[0][2], $arrayRiesgoSucursal[0][3], $arrayRiesgoSucursal[0][4]), array(1, "Encabezados"), array("C", "L", "L", "L", "L", "L", "C", "C", "C", "C", "C", "C", "C"));
        }
        if ($correlativo == 0) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetWidths(array(250));
            $pdf->Row(array($x, $altura, 5), array("No se encontraron incapacidades con los filtros seleccionados"), array(1, "No"), array("C"));
        }else{
        }
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetWidths(array(220, 6, 6, 6, 6, 6));
        $pdf->Row(array($x, 40, 5), array("Totales",$arrayRiesgoSucursal[1][0],$arrayRiesgoSucursal[1][1],$arrayRiesgoSucursal[1][2],$arrayRiesgoSucursal[1][3],$arrayRiesgoSucursal[1][4]), 
        array(1, "No"), array("L", "C", "C", "C", "C", "C"));
        $arrayRiesgoTotales[] = $arrayRiesgoSucursal[1];
    }
    $pdf->AddPage();

    if ($corrTotal == 0) {
        $pdf->SetXY(15,35);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetWidths(array(250));
        $pdf->Row(array(15, 35, 5), array("No se encontraron incapacidades con los filtros seleccionados"), array(1, "No"), array("C"));
    }else{
        $pdf->encabezadosTabla2();
        $altura = 65;
        $pdf->SetXY($x,$altura);
        $pdf->SetFont('Arial', 'B', 8);
        $n = 0;
        foreach ($dataFiltroIncapacidades as $totalesSucursales) {
            if($filtroIncapacidades == "Sucursales") {
                $pdf->Row(array($x, $altura, 5), array("Total sucursal: $totalesSucursales->sucursal",$arrayRiesgoTotales[$n][0],$arrayRiesgoTotales[$n][1],$arrayRiesgoTotales[$n][2],$arrayRiesgoTotales[$n][3],$arrayRiesgoTotales[$n][4]),array(1, "Encabezados2"), array("L", "C", "C", "C", "C", "C"));
            } else {
                
                $pdf->Row(array($x, $altura, 5), array("Total empleado: $totalesSucursales->nombreCompleto",$arrayRiesgoTotales[$n][0],$arrayRiesgoTotales[$n][1],$arrayRiesgoTotales[$n][2],$arrayRiesgoTotales[$n][3],$arrayRiesgoTotales[$n][4]),array(1, "Encabezados2"), array("L", "C", "C", "C", "C", "C"));
            }
            $arrayTotalGeneral[0] += $arrayRiesgoTotales[$n][0];
            $arrayTotalGeneral[1] += $arrayRiesgoTotales[$n][1];
            $arrayTotalGeneral[2] += $arrayRiesgoTotales[$n][2];
            $arrayTotalGeneral[3] += $arrayRiesgoTotales[$n][3];
            $arrayTotalGeneral[4] += $arrayRiesgoTotales[$n][4];
            $n++;
        }

        $pdf->SetWidths(array(220, 6, 6, 6, 6, 6));
        $pdf->Row(array($x, $altura, 5), array("Total general",$arrayTotalGeneral[0],$arrayTotalGeneral[1],$arrayTotalGeneral[2],$arrayTotalGeneral[3],$arrayTotalGeneral[4]),array(1, "No"), array("L", "C", "C", "C", "C", "C"));
    }
    
    $pdf->Output($outputReporte . '.pdf', "I");
?>