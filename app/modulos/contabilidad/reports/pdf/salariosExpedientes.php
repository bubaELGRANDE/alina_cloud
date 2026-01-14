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
            $this->Image('../../../../../libraries/resources/images/logos/indupal-logo.png', 15, 8, 40);

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
            $this->SetXY(160, 8);
            $this->Cell(40, 4, date("d-m-Y H:i:s"), 0, 0, 'R');
            $this->SetXY(160, 12);
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
        filtroEmpleados (radio)
        filtroClasificacion (radio)
        flgClasificacion (switch)
        selectEmpleados (multiple)
        selectClasificacion (multiple)
    */

    $filtroEmpleados = base64_decode(urldecode($_REQUEST['filtroEmpleados']));
    $arrayExpedientesId = (isset($_REQUEST['selectEmpleados']) ? base64_decode(urldecode($_REQUEST['selectEmpleados'])) : '');
    $flgClasificacion = (isset($_REQUEST['flgClasificacion']) ? 'Sí' : 'No');
    $filtroClasificacion = base64_decode(urldecode($_REQUEST['filtroClasificacion']));
    $arrayClasificacionId = (isset($_REQUEST['selectClasificacion']) ? base64_decode(urldecode($_REQUEST['selectClasificacion'])) : '');
    $estadoSalarioExpediente = ($filtroEmpleados == "Inactivos" ? 'Inactivo' : 'Activo');

    $tituloReporte = 'Salarios de Empleados';
    $subtituloReporte = "";
    
    $outputReporte = 'Salarios de Empleados (' . $filtroEmpleados . ')';

    $whereEstadoSalarioExpediente = '';
    if($estadoSalarioExpediente == 'Activo') {
        $whereEstadoSalarioExpediente = "AND p.estadoPersona = 'Activo' AND exp.estadoExpediente = 'Activo'";
    } else {
        // Inactivos, para expedientes son: Despido, Renuncia, Abandono, Defunción, Traslado, Jubilado
        // Indicados en empleadoDarDeBaja
        $whereEstadoSalarioExpediente = "AND p.estadoPersona = 'Inactivo' AND (exp.estadoExpediente = 'Despido' OR exp.estadoExpediente = 'Renuncia' OR exp.estadoExpediente = 'Abandono' OR exp.estadoExpediente = 'Defunción' OR exp.estadoExpediente = 'Traslado' OR exp.estadoExpediente = 'Jubilado')";
    }

    $pdf = new PDF('P','mm','Letter');
    $pdf->AliasNbPages();
    $pdf->SetTitle($outputReporte);
    $x = 15;
    $celdas = 5;

    $whereEmpleados = "";
    if ($filtroEmpleados == 'Todos') {
        $whereEmpleados = "";
    } else {
        // Especifico o Inactivos
        $whereEmpleados = "AND exp.prsExpedienteId IN ($arrayExpedientesId)";
    }

    $whereClasificacion = "";
    if($flgClasificacion == "Sí") {
        if ($filtroClasificacion == 'Todos') {
            $whereClasificacion = "";
        } else {
            // Especifico o Inactivos
            $whereClasificacion = "AND clasifGastoSalarioId IN ($arrayClasificacionId)";
        }
        $dataClasificacion = $cloud->rows("
            SELECT 
                clasifGastoSalarioId,
                nombreGastoSalario
            FROM cat_clasificacion_gastos_salario 
            WHERE flgDelete = ? $whereClasificacion
            ORDER BY nombreGastoSalario
        ", [0]);
    } else {
        // Para que se itere una sola vez, sin ningun filtro de clasificacion
        $dataClasificacion = array("");
    }
    $totalGeneral = 0;
    $arrayTotales = array();
    $posicionTotal = 0;
    foreach($dataClasificacion as $clasificacion) {
        if($flgClasificacion == "Sí") {
            $whereClasificacionExpediente = "AND exp.clasifGastoSalarioId = $clasificacion->clasifGastoSalarioId";
            
        } else {
            $whereClasificacionExpediente = "";
        }
        $dataExpedientes = $cloud->rows("
        SELECT 
            exp.prsExpedienteId AS prsExpedienteId,
            pc.cargoPersona AS cargoPersona,
            p.codEmpleado AS codEmpleado,
            CONCAT(
                IFNULL(p.apellido1, '-'),
                ' ',
                IFNULL(p.apellido2, '-'),
                ', ',
                IFNULL(p.nombre1, '-'),
                ' ',
                IFNULL(p.nombre2, '-')
            ) AS nombreCompleto
        FROM th_expediente_personas exp 
        JOIN th_personas p ON p.personaId = exp.personaId
        LEFT JOIN cat_personas_cargos pc ON pc.prsCargoId = exp.prsCargoId
        WHERE exp.flgDelete = ? $whereEmpleados $whereEstadoSalarioExpediente $whereClasificacionExpediente
        ORDER BY p.apellido1, p.apellido2, p.nombre1, p.nombre2
        ",[0]);
        
        $pdf->AddPage();
    
        $pdf->SetFont('Arial', '', 12);
        $x = 15;
        $altura = 30;
        $celdas = 5;
        $codigo = 0;
        $corrPagina = 1;
        $totales = 0 ;
        
        if($flgClasificacion == "Sí") {
            $pdf->SetXY($x, $altura);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(90, $celdas, utf8_decode('Clasificación: '.$clasificacion->nombreGastoSalario), 0, 0, 'L');
            $corrPagina++;
            $altura += $celdas;

        } else {
            // no dibujar clasificacioin
        }

        // Ancho total con margenes incluidos = 185
        $pdf->SetXY($x, $altura);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(12, $celdas, utf8_decode('Cód.'), 'B', 0, 'C');
        $pdf->Cell(80, $celdas, utf8_decode('Empleado'), 'B', 0, 'C');
        $pdf->Cell(62, $celdas, utf8_decode('Cargo'), 'B', 0, 'C');
        $pdf->Cell(31, $celdas, utf8_decode('Salario'), 'B', 0, 'C');   
        $altura += $celdas;

        $pdf->SetFont('Arial', '', 11);
        foreach($dataExpedientes as $expediente){
            $codigo = ($expediente->codEmpleado == "" ? "-" : $expediente->codEmpleado);
            $cargo             = ($expediente->cargoPersona == "" ? "-" : $expediente->cargoPersona);
            $nombreCompleto    = $expediente->nombreCompleto;
    
            $dataExpedienteSalarios = $cloud->row("
                SELECT
                    salario
                FROM th_expediente_salarios
                WHERE prsExpedienteId = ? AND flgDelete = ? AND estadoSalario = 'Activo'
            ", [$expediente->prsExpedienteId,0]);
    
            if (is_object($dataExpedienteSalarios)) {
                $salario = $dataExpedienteSalarios->salario;
            } else {
                $salario = 0;
            }
            $totales += $salario;
            $totalGeneral += $salario;
            if ($corrPagina > 44) {
                $pdf->AddPage();
    
                $pdf->SetFont('Arial', '', 12);
                $altura = 30;
                $corrPagina = 1;
                // Ancho total con margenes incluidos = 185
                $pdf->SetXY($x, $altura);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(12, $celdas, utf8_decode('Cód.'), 'B', 0, 'C');
                $pdf->Cell(80, $celdas, utf8_decode('Empleado'), 'B', 0, 'C');
                $pdf->Cell(62, $celdas, utf8_decode('Cargo'), 'B', 0, 'C');
                $pdf->Cell(31, $celdas, utf8_decode('Salario'), 'B', 0, 'C');    
                $altura += $celdas;
            }else{
                // todavia cabe en la pagina
            }
            $pdf->SetFont('Arial', '', 11);
            //Codigo del empleado
            $pdf->SetXY($x, $altura);
            $pdf->Cell(12, $celdas, utf8_decode($codigo), 'B', 0, 'C');
    
            //nombre completo del empleado  
            $pdf->Cell(80, $celdas, utf8_decode($nombreCompleto), 'B', 0, 'L');
            
             //cargo del empleado       
            $pdf->Cell(62, $celdas, utf8_decode($cargo), 'B', 0, 'L');
    
            //salario del empleado        
            $pdf->Cell(31, $celdas, number_format($salario, 2, '.', ','), 'B', 0, 'R');
            
    
            //Signo de dolar 
            $pdf->SetXY($x + 154, $altura);
            $pdf->Cell(31, $celdas, utf8_decode('$'), 0, 0, 'L');    
            $altura += $celdas;
            
            $corrPagina++;
            $salario = 0; $totalClasificacion = 0;
            
        }
        if($flgClasificacion == "Sí") {
            $corrPagina++;
            if ($corrPagina > 44) {
                $pdf->AddPage();
                $altura = 30;
                $corrPagina = 1;
            }else{
                // todavia cabe en la pagina, dejar un espacio más
                $altura += $celdas;
            }   
            $arrayTotales[$posicionTotal] = $totales;
            

            $pdf->SetXY($x, $altura);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(154, $celdas, utf8_decode('Total Clasificación: '.$clasificacion->nombreGastoSalario ), 'B', 0, 'L');
            $pdf->Cell(31, $celdas, number_format($totales, 2, '.', ','), 'B', 0, 'R');
        
            $pdf->SetXY($x + 154, $altura);
            $pdf->Cell(31, $celdas, utf8_decode('$'), 0, 0, 'L');  
            $altura += $celdas;
        } else {
            $whereClasificacionExpediente = "";
        }
        $posicionTotal++;
    }
    if ($flgClasificacion == "Sí") {
        $altura = 30;
        $pdf->AddPage();
        $posicionTotal = 0;
        foreach($dataClasificacion as $clasificacion) {
            $pdf->SetXY($x, $altura);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(154, $celdas, utf8_decode('Total Clasificación: '.$clasificacion->nombreGastoSalario ), 'B', 0, 'L');
            $pdf->Cell(31, $celdas, number_format($arrayTotales[$posicionTotal], 2, '.', ','), 'B', 0, 'R');

            $pdf->SetXY($x + 154, $altura);
            $pdf->Cell(31, $celdas, utf8_decode('$'), 0, 0, 'L');  
            $altura += $celdas;
            $posicionTotal++;
        }
        $altura += $celdas;
        $pdf->SetXY($x, $altura);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(154, $celdas, utf8_decode('Total General'), 'B', 0, 'L');
        $pdf->Cell(31, $celdas, number_format($totalGeneral, 2, '.', ','), 'B', 0, 'R');
    
        $pdf->SetXY($x + 154, $altura);
        $pdf->Cell(31, $celdas, utf8_decode('$'), 0, 0, 'L');  

    }else{
        $corrPagina++;
        if ($corrPagina > 44) {
            $pdf->AddPage();
            $x = 15;
            $altura = 30;
            $celdas = 5;
            $corrPagina = 1;
        }else{
            // todavia cabe en la pagina, dejar un espacio más
            $altura += $celdas;
        }
        
        $pdf->SetXY($x, $altura);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(154, $celdas, utf8_decode('Total General'), 'B', 0, 'L');
        $pdf->Cell(31, $celdas, number_format($totales, 2, '.', ','), 'B', 0, 'R');
    
        $pdf->SetXY($x + 154, $altura);
        $pdf->Cell(31, $celdas, utf8_decode('$'), 0, 0, 'L');    
        $altura += $celdas;

    }  
    
    $pdf->Output($outputReporte . '.pdf', "I");
?>