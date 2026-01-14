<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
#date_format(inca.fechaInicio, '%d-%m-%Y')
    $dataIncapacidades = $cloud->rows("
    SELECT
        per.personaId as personaId, 
        exp.prsExpedienteId as expedienteId,
        CONCAT(
            IFNULL(per.apellido1, '-'),
            ' ',
            IFNULL(per.apellido2, '-'),
            ', ',
            IFNULL(per.nombre1, '-'),
            ' ',
            IFNULL(per.nombre2, '-')
        ) AS nombreCompleto,
        date_format(inca.fechaInicio, '%d-%m-%Y') as fechaIni,
        date_format(inca.fechaFin, '%d-%m-%Y') as fechaFin,
        date_format(inca.fechaExpedicion, '%d-%m-%Y') as fechaExp,
        inca.motivoIncapacidad as motivo,
        inca.incapacidadSubsidio as subsidio,
        inca.prsAdjuntoId as adjuntoId,
        inca.expedienteIncapacidadId as incapacidadId,
        inca.riesgoIncapacidad as riesgoIncapacidad
        FROM ((th_expediente_incapacidades inca
        JOIN th_expediente_personas exp ON inca.expedienteId = exp.prsExpedienteId)
        JOIN th_personas per ON per.personaId = exp.personaId)
        WHERE inca.flgDelete = 0 
        ORDER BY inca.fechaInicio DESC
    ");

    $n = 0;
    foreach ($dataIncapacidades as $incapacidad) {
        $n += 1;

        $persona = '<b><i class="fas fa-user"></i> Empleado: </b> ' . $incapacidad->nombreCompleto .'
                <br><i class="fas fa-calendar"></i> <b>Fecha de expedición:</b> ' . $incapacidad->fechaExp;
        $motivo = '<b>Riesgo:</b> ' . $incapacidad->riesgoIncapacidad . '<br>
                    <b>Diagnostico:</b> '. $incapacidad->motivo;
        $vigencia = '<i class="fas fa-calendar"></i> <b>Fecha de inicio:</b> ' . $incapacidad->fechaIni . '<br><b><i class="fas fa-calendar"></i> Fecha de finalización:</b> ' . $incapacidad->fechaFin;
        $onclickIncapacidad = "";
        if($incapacidad->adjuntoId == 0) {
            // Cargar modal para una nueva incapacidad que haga update a este campo y le quite el cero
            $onclickIncapacidad = 'onClick="mensaje(`En desarrollo`, `Esta función estará disponible próximamente`, `info`);"';
        } else {
            // Mostrar desde la modal normal de ver adjuntos
            $onclickIncapacidad = 'onClick="verAdjuntoModal('.$incapacidad->adjuntoId.');"';
        }
        $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" '.$onclickIncapacidad.'>
                        <i class="fas fa-paperclip"></i>
                        <span class="ttiptext">Ver adjunto</span>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm ttip" onClick="modalIncapacidad(`update^'.$incapacidad->incapacidadId.'`);">
                        <i class="fas fa-pen"></i>
                        <span class="ttiptext">Editar incapacidad</span>
                    </button>';

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $persona,
            $motivo,
            $vigencia,
            $acciones
        );
    } // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }