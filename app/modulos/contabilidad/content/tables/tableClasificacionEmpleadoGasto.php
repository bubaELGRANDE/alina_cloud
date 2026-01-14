<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $getClasificacion = $cloud->rows("SELECT 
    exp.prsExpedienteId as prsExpedienteId, 
    CONCAT(
        IFNULL(per.apellido1, '-'),
        ' ',
        IFNULL(per.apellido2, '-'),
        ', ',
        IFNULL(per.nombre1, '-'),
        ' ',
        IFNULL(per.nombre2, '-')
    ) AS nombreCompleto,
    car.cargoPersona as cargoPersona
    FROM th_expediente_personas exp
        JOIN th_personas per ON per.personaId = exp.personaId
        JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
    WHERE exp.flgDelete = ? AND exp.estadoExpediente = ? AND exp.clasifGastoSalarioId = ?
    ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
    ", [0, 'Activo', $_POST["clasifGastoSalarioId"]]);

    $n = 0;
    foreach ($getClasificacion as $getClasificacion){
        $n += 1;
        $persona = "<b><i class='fas fa-user-tie trailing'></i> Empleado: </b>". $getClasificacion->nombreCompleto .'<br>'. 
                   "<b><i class='fas fa-briefcase trailing'></i> Cargo: </b>". $getClasificacion->cargoPersona;

        $jsonEditar = array(
            "typeOperation"                     => "delete",
            "operation"                         => "clasificacion-gasto-empleado",
            "prsExpedienteId"                   => $getClasificacion->prsExpedienteId
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));

        $acciones = '<button type="button" class="btn btn-danger btn-sm ttip" onClick=eliminarClasificacionEmpleados('.$funcionEditar.')>
                        <i class="fas fa-trash-alt"></i>
                        <span class="ttiptext">Eliminar clasificación</span>
                    </button>';

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $persona,
            $acciones
        );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }