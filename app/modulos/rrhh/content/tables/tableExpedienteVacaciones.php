<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataVaca = $cloud->rows("
    SELECT
        vaca.expedienteVacacionesId,
        per.personaId as personaId, 
        vaca.expedienteId,
        CONCAT(
            IFNULL(per.apellido1, '-'),
            ' ',
            IFNULL(per.apellido2, '-'),
            ', ',
            IFNULL(per.nombre1, '-'),
            ' ',
            IFNULL(per.nombre2, '-')
        ) AS nombreCompleto,
        date_format(vaca.fhSolicitud, '%d-%m-%Y') AS fhSolicitud,
        vaca.periodoVacaciones,
        vaca.numDias,
        date_format(vaca.fechaInicio, '%d-%m-%Y') AS fechaInicio,
        date_format(vaca.fechaFin, '%d-%m-%Y') AS fechaFin,
        date_format(vaca.fhaprobacion, '%d-%m-%Y') AS fhaprobacion,
        vaca.estadoSolicitud
        FROM ((th_expedientes_vacaciones vaca
        JOIN th_expediente_personas exp ON vaca.expedienteId = exp.prsExpedienteId)
        JOIN th_personas per ON per.personaId = exp.personaId)
        WHERE vaca.flgDelete = 0 AND vaca.estadoSolicitud = 'Aprobado'
        ORDER BY apellido1, apellido2, nombre1, nombre2
    ");

    $n = 0;
    foreach ($dataVaca as $vacaciones) {
        $n += 1;

        $persona = '<b><i class="fas fa-user"></i> Empleado: </b> ' . $vacaciones->nombreCompleto .'<br>
                <i class="fas fa-calendar"></i> <b>Fecha de solicitud:</b> ' . $vacaciones->fhSolicitud . '<br>
                <i class="fas fa-calendar"></i> <b>Fecha de aprobación:</b> ' . $vacaciones->fhaprobacion . '<br>
                <i class="fas fa-calendar"></i> <b>Tipo de vacación:</b> ' . $vacaciones->periodoVacaciones . '<br>';
        
        $vigencia = '<i class="fas fa-umbrella-beach"></i> <b>Número de días:</b> ' . $vacaciones->numDias . '<br>
                    <i class="fas fa-calendar"></i> <b>Fecha de inicio:</b> ' . $vacaciones->fechaInicio . '<br>
                    <b><i class="fas fa-calendar"></i> Fecha de finalización:</b> ' . $vacaciones->fechaFin;
        
        $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalVacaciones(`update^'.$vacaciones->expedienteVacacionesId.'`);">
                    <i class="fas fa-pen"></i>
                    <span class="ttiptext">Editar periodo de vacaciones</span>
                </button>
                <button type="button" class="btn btn-danger btn-sm ttip" onClick="anularVacaciones(`update^'.$vacaciones->expedienteVacacionesId.'^'.$vacaciones->numDias.'^'.$vacaciones->personaId.'^'.$vacaciones->nombreCompleto.'`);">
                    <i class="fas fa-times-circle"></i>
                    <span class="ttiptext">Anular</span>
                </button>';


        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $persona,
            $vigencia,
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