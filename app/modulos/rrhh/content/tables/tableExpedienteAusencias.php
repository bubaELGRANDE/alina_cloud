<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataAusencias = $cloud->rows("
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
        au.expedienteAusenciaId,
        au.expedienteIdAutoriza,
        au.expedienteId,
        date_format(au.fhSolicitud, '%d-%m-%Y')  as fechaSolicitud,
        au.flgIngresoSolicitud,
        date_format(au.fechaAusencia, '%d-%m-%Y') as fechaIni,
        date_format(au.fechaFinAusencia, '%d-%m-%Y') as fechaFin,
        au.totalDias,
        au.horaAusenciaInicio,
        au.horaAusenciaFin,
        au.totalHoras,
        au.motivoAusencia as motivo,
        au.goceSueldo,
        au.estadoSolicitudAu
        FROM ((th_expediente_ausencias au
        JOIN th_expediente_personas exp ON au.expedienteId = exp.prsExpedienteId)
        JOIN th_personas per ON per.personaId = exp.personaId)
        WHERE au.flgDelete = 0 
        ORDER BY apellido1, apellido2, nombre1, nombre2
    ");

    $n = 0;
    foreach ($dataAusencias as $ausencia) {
        $n += 1;

        $persona  = '<b><i class="fas fa-user"></i> ' . $ausencia->nombreCompleto .'</b>
                  <br><i class="fas fa-calendar"></i> <b>Fecha de solicitud:</b> ' . $ausencia->fechaSolicitud;

        $motivo   = '<b><i class="fas fa-align-justify"></i></b> '.$ausencia->motivo;
        $vigencia = '<i class="fas fa-calendar"></i> <b> Inicio:</b> ' . $ausencia->fechaIni . '<br><b><i class="fas fa-calendar"></i> Finalización:</b> ' . $ausencia->fechaFin;
        
        if ($ausencia->estadoSolicitudAu=="Anulada") {
            $acciones = "<b>Anulada</b>";
        }else{
            $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalSolicitud(`editar^'.$ausencia->expedienteAusenciaId.'`);">
                        <i class="fas fa-pen"></i>
                        <span class="ttiptext">Editar</span>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm ttip" onClick="anular(`update^'.$ausencia->expedienteAusenciaId.'^'.$ausencia->personaId.'^'.$ausencia->nombreCompleto.'`);">
                        <i class="fas fa-times-circle"></i>
                        <span class="ttiptext">Anular solicitud</span>
                    </button>';
        }
        

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