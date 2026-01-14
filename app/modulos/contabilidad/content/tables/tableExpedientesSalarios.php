<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    if($_POST['clasifGastoSalarioId'] == "Todos") {
        $whereClasificacion = "";
    } else {
        $whereClasificacion = "AND exp.clasifGastoSalarioId = '$_POST[clasifGastoSalarioId]'";
    }

    if($_POST['estadoExpediente'] == "Activo") {
        $whereEstadoExpediente = "AND per.estadoPersona = 'Activo' AND exp.estadoExpediente = 'Activo'";
    } else {
        // where con los estados de bajas de empleado
        $whereEstadoExpediente = "AND per.estadoPersona = 'Inactivo' AND (exp.estadoExpediente =  'Renuncia' OR exp.estadoExpediente =  'Despido' OR exp.estadoExpediente =  'Inactivo' OR exp.estadoExpediente =  'Finalizado'
                                 OR exp.estadoExpediente =  'Jubilado' OR exp.estadoExpediente = 'Abandono' OR exp.estadoExpediente = 'Defunción' OR exp.estadoExpediente = 'Traslado')";
    }

    $dataExpedientes = $cloud->rows("
        SELECT 
            exp.prsExpedienteId as prsExpedienteId, 
            exp.personaId as personaId,
            exp.sucursalDepartamentoId as sucursalDepartamentoId, 
            exp.tipoContrato as tipoContrato, 
            exp.fechaInicio as fechaInicio, 
            exp.fechaFinalizacion as fechaFinalizacion, 
            exp.estadoExpediente as estadoExpediente,
            per.estadoPersona as estadoPersona,
            per.fechaInicioLabores as fechaInicioLabores,
            exp.justificacionEstado as justificacionEstado,
            exp.clasifGastoSalarioId as clasifGastoSalarioId,
            per.codEmpleado as codEmpleado,
            CONCAT(
                IFNULL(per.apellido1, '-'),
                ' ',
                IFNULL(per.apellido2, '-'),
                ', ',
                IFNULL(per.nombre1, '-'),
                ' ',
                IFNULL(per.nombre2, '-')
            ) AS nombreCompleto,
            car.cargoPersona as cargoPersona,
            dep.departamentoSucursal as departamentoSucursal,
            dep.sucursalId as sucursalId,
            s.sucursal as sucursal,
            cgs.nombreGastoSalario as nombreGastoSalario
        FROM th_expediente_personas exp
        LEFT JOIN th_personas per ON per.personaId = exp.personaId
        LEFT JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
        LEFT JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
        LEFT JOIN cat_sucursales s ON s.sucursalId = dep.sucursalId
        LEFT JOIN cat_clasificacion_gastos_salario cgs ON cgs.clasifGastoSalarioId = exp.clasifGastoSalarioId
        WHERE exp.flgDelete = ? $whereEstadoExpediente $whereClasificacion
        ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
    ", [0]);
    $n = 0;

    foreach ($dataExpedientes as $dataExpedientes) {

        $dataExpedienteSalarios = $cloud->row("
        SELECT
                tes.salario,
                str.tipoRemuneracion
        FROM th_expediente_salarios tes
        INNER JOIN  cat_salarios_tipo_remuneracion str ON str.salarioTipoRemuneracionId = tes.salarioTipoRemuneracionId  
        WHERE tes.prsExpedienteId = ? AND tes.flgDelete = ? AND tes.estadoSalario = 'Activo'
        ", [$dataExpedientes->prsExpedienteId,0]);
        
        $n += 1;
        $expediente = '
            <b><i class="fas fa-user-tie"></i> Código de empleado:</b> '.($dataExpedientes->codEmpleado == "" ? '-' : $dataExpedientes->codEmpleado).'<br>
            <b><i class="fas fa-user-tie"></i> Nombre completo:</b> '.$dataExpedientes->nombreCompleto.'<br>
            <b><i class="fas fa-briefcase"></i> Cargo:</b> '.($dataExpedientes->cargoPersona == "" ? '-' : $dataExpedientes->cargoPersona).'<br>
            <b><i class="fas fa-building"></i> Sucursal:</b> '.($dataExpedientes->sucursal == "" ? '-' : $dataExpedientes->sucursal).'<br>
            <b><i class="fas fa-building"></i> Departamento:</b> '.($dataExpedientes->departamentoSucursal == "" ? '-' : $dataExpedientes->departamentoSucursal).'<br>
        ';
        
        if (is_object($dataExpedienteSalarios)) {
            $salario = $dataExpedienteSalarios->salario;
            $remuneracion = $dataExpedienteSalarios-> tipoRemuneracion;
        } else {
            $salario = 0;
            $remuneracion = "-";
        }

        if($dataExpedientes->clasifGastoSalarioId == 0){
            $Clasificacion = "-";
        } else{
            $Clasificacion = $dataExpedientes->nombreGastoSalario;
        }

        $columnaSalario = '
            <b><i class="fas fa-dollar-sign"></i> Salario:</b> $ '.number_format($salario, 2 , '.' , ',').'<br>
            <b><i class="fas fa-list-ul"></i> T. remuneración: </b> '.$remuneracion.'<br>
            <b><i class="fas fa-clipboard-list"></i> Clasificacion: </b> '.$Clasificacion.' 
        ';

        $jsonSalario = [
            "prsExpedienteId"           => $dataExpedientes->prsExpedienteId,
            "estadoExpediente"          => $dataExpedientes->estadoExpediente,
            "nombreEmpleado"            => $dataExpedientes->nombreCompleto
        ];
        $funcionSalario = htmlspecialchars(json_encode($jsonSalario));

        $jsonNombre = [
            "codEmpleado"               => $dataExpedientes->codEmpleado,
            "nombreEmpleado"            => $dataExpedientes->nombreCompleto,
            "clasificacion"             => $dataExpedientes->nombreGastoSalario,
            "prsExpedienteId"           => $dataExpedientes->prsExpedienteId,
            "personaId"                 => $dataExpedientes->personaId,
            "clasifGastoSalarioId"      => $dataExpedientes->clasifGastoSalarioId
        ];
        $funcionNombre = htmlspecialchars(json_encode($jsonNombre));


        if($whereEstadoExpediente == "AND per.estadoPersona = 'Activo' AND exp.estadoExpediente = 'Activo'"){
            $acciones = '
            <button type="button" class="btn btn-primary btn-sm" onclick="modalHistorialSalario('.$funcionSalario.');">
                <i class="fas fa-money-check-alt"></i> Salario
            </button>

            <button type="button" class="btn btn-primary btn-sm" onclick="modalEditar('.$funcionNombre.');">
                <i class="fas fa-edit"></i> Clasificación
            </button>
        ';
        }else{
            $acciones = '
            <button type="button" class="btn btn-primary btn-sm" onclick="modalHistorialSalario('.$funcionSalario.');">
                <i class="fas fa-money-check-alt"></i> Salario
            </button>
        ';
        }

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $expediente,
            $columnaSalario,
            $acciones
        );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>''));
    }

?>