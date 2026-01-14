<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $getClasificacion = $cloud->rows("SELECT clasifGastoSalarioId, nombreGastoSalario 
                                      FROM cat_clasificacion_gastos_salario 
                                      WHERE flgDelete = ?", 
                                    [0]);

    $n = 0;
    foreach ($getClasificacion as $getClasificacion){
        $n += 1;

        $empleadoGasto = array(
            'tituloModal'       => "Clasificación de empleados - $getClasificacion->nombreGastoSalario",
            'clasifGastoSalarioId'   => $getClasificacion->clasifGastoSalarioId
        );
        $empleadoGastoModal = htmlspecialchars(json_encode($empleadoGasto));

        $jsonEditar = array(
            "typeOperation"                     => "update",
            "tituloModal"                       => "$getClasificacion->nombreGastoSalario",
            "clasifGastoSalarioId"              => "$getClasificacion->clasifGastoSalarioId"
  
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));

        $jsonEliminar = array(
            "typeOperation"             => "delete",
            "operation"                 => "clasificacion-gasto",
            "clasifGastoSalarioId"      => $getClasificacion->clasifGastoSalarioId,
            "nombreGastoSalario"        => $getClasificacion->nombreGastoSalario
            
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $totalClasificacion = $cloud->count("
            SELECT prsExpedienteId FROM th_expediente_personas
            WHERE clasifGastoSalarioId = ? AND flgDelete = ?
        ", [$getClasificacion->clasifGastoSalarioId, 0]);
        
        $nombreGasto = $getClasificacion->nombreGastoSalario;

        $acciones   = '
                        <button type="button" class="btn btn-primary btn-sm ttip" onClick="modalEmpleadoGasto('.$empleadoGastoModal.');">
                            <span class="badge rounded-pill bg-light" style="color: black;">'.$totalClasificacion.'</span>
                            <i class="fas fa-user"></i>
                            <span class="ttiptext">Agregar empleado a clasificiación</span>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm ttip" onClick="modalClasificacion('.$funcionEditar.');">
                            <i class="fas fa-pen"></i>
                            <span class="ttiptext">Editar clasificación</span>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm ttip" onClick="eliminarClasificacion('.$funcionEliminar.');">
                            <i class="fas fa-trash-alt"></i>
                            <span class="ttiptext">Eliminar clasificación</span>
                        </button>
                        ';
        
        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $nombreGasto,
            $acciones
        );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }