<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataAmonestaciones = $cloud->rows("
        SELECT
            per.personaId AS personaId, 
            exp.prsExpedienteId AS expedienteId,
            CONCAT(
                IFNULL(per.apellido1, '-'),
                ' ',
                IFNULL(per.apellido2, '-'),
                ', ',
                IFNULL(per.nombre1, '-'),
                ' ',
                IFNULL(per.nombre2, '-')
            ) AS nombreCompleto,
            am.expedienteAmonestacionId AS expedienteAmonestacionId,
            am.amonestacionAnteriorId AS amonestacionAnteriorId,
            am.expedienteIdJefe,
            am.expedienteId,
            DATE_FORMAT(am.fechaAmonestacion, '%d/%m/%Y') AS fechaAmonestacion,
            DATE_FORMAT(am.fechaAmonestacion, '%Y') AS anioAmonestacion,
            am.tipoAmonestacion,
            am.causaFalta,
            am.descripcionFalta,
            am.descripcionOtroCausa AS descripcionOtroCausa,
            am.consecuenciaFalta,
            am.descripcionConsecuencia,
            am.compromisoMejora,
            am.flgReincidencia,
            am.estadoAmonestacion,
            am.justificacionAnulada,
            DATE_FORMAT(am.fechaSuspensionInicio, '%d/%m/%Y') AS fechaSuspensionInicio,
            DATE_FORMAT(am.fechaSuspensionFin, '%d/%m/%Y') AS fechaSuspensionFin,
            DATE_FORMAT(amAnterior.fechaAmonestacion, '%d/%m/%Y') AS fechaAmonestacionAnterior
        FROM 
            th_expediente_amonestaciones am
        JOIN 
            th_expediente_personas exp ON am.expedienteId = exp.prsExpedienteId
        JOIN 
            th_personas per ON per.personaId = exp.personaId
        LEFT JOIN 
            th_expediente_amonestaciones amAnterior ON am.amonestacionAnteriorId = amAnterior.expedienteAmonestacionId
        WHERE 
            am.flgDelete = 0 
            AND am.estadoAmonestacion = ?
        ORDER BY 
            per.apellido1, per.apellido2, per.nombre1, per.nombre2
    ", [$_POST["estado"]]);
    $n = 0;
    foreach ($dataAmonestaciones as $amonestacion) {
        $n += 1;

        $persona = '<b><i class="fas fa-list-ul"></i> N° de amonestación: </b> '.str_pad($amonestacion->expedienteAmonestacionId, 3, '0', STR_PAD_LEFT).'<br>
                    <b><i class="fas fa-user"></i> Empleado: </b>' . $amonestacion->nombreCompleto . '</b>
                    <br><i class="fas fa-calendar"></i> <b>Fecha de amonestación:</b> ' . $amonestacion->fechaAmonestacion . '<br>
                    <b><i class="fas fa-user-times"></i> Tipo de amonestación:</b> ' . $amonestacion->tipoAmonestacion . '<br>
                    <b><i class="fas fa-user-times"></i> Descripción: </b> ' . $amonestacion->descripcionFalta . '<br>
                    ';

            if ($amonestacion->causaFalta == "Otros") {
            $causa = $amonestacion->causaFalta . ': ' . $amonestacion->descripcionOtroCausa;
            } else {
            $causa = $amonestacion->causaFalta;
            }

            if ($amonestacion->flgReincidencia == "Si") {
            $reincidencia = $amonestacion->flgReincidencia . ': ' . $amonestacion->amonestacionAnteriorId . ' - ' . $amonestacion->fechaAmonestacionAnterior;
            } else {
            $reincidencia = $amonestacion->flgReincidencia;
            }

        $infoAmonestacion = '<b>Causa: </b> ' . $causa . '<br>
                            <b><i class="fas fa-times-circle"></i> Reincidencia: </b>' . $reincidencia;

            if (!is_null($amonestacion->consecuenciaFalta)) {
            $infoAmonestacion .= '<br><b>Consecuencias: <span class="text-danger">' . $amonestacion->consecuenciaFalta . '</span></b><br>
                <b><i class="fas fa-user"></i> Descripción de la consecuencia: </b>' . $amonestacion->descripcionConsecuencia.'
            ';

            if (!empty($amonestacion->fechaSuspensionInicio) && !empty($amonestacion->fechaSuspensionFin)) {
                $infoAmonestacion .= '<br>
                                    <b><i class="fas fa-calendar-check"></i> Fecha de suspensión </b><br>
                                    Desde: ' . $amonestacion->fechaSuspensionInicio . ' Hasta: ' . $amonestacion->fechaSuspensionFin;
            }
            }
   //CP9
            if (!is_null($amonestacion->justificacionAnulada)) {
            $infoAmonestacion .= '<br>
                                <b>Justificación de anulación:</b><br>
                                ' . $amonestacion->justificacionAnulada;
            }

        
        $acciones = '

        ';
        if($amonestacion->estadoAmonestacion == 'Inactivo' || $amonestacion->estadoAmonestacion == 'Anulado'){
            $acciones .= '

             <button type="button" class="btn btn-primary btn-sm ttip" onClick="modalVerAmonestacion('.$amonestacion->expedienteAmonestacionId.');"  >
                <i class="fas fa-eye"></i>
                <span class="ttiptext">Ver detalles</span>
            </button>

                        ';
        } else {
            $acciones .= '

                        <button type="button" class="btn btn-primary btn-sm ttip" onClick="modalVerAmonestacion('.$amonestacion->expedienteAmonestacionId.');">
                            <i class="fas fa-eye"></i>
                            <span class="ttiptext">Ver detalles</span>
                        </button>
                        <button type="button" class="btn btn-info btn-sm ttip" onclick="modalReportesAmonestacion({expedienteAmonestacionId:'.$amonestacion->expedienteAmonestacionId.', expedienteId:'.$amonestacion->expedienteId.', anioAmonestacion:'.$amonestacion->anioAmonestacion.'});">
                            <i class="fas fa-print"></i>
                            <span class="ttiptext">Imprimir Amonestación</span>
                        </button>

                        <button type="button" class="btn btn-danger btn-sm ttip" onClick="modalAnularAmonestacion(`delete^'.$amonestacion->expedienteAmonestacionId.'^'.$amonestacion->expedienteId.'`);">
                                <i class="fas fa-times-circle"></i>
                                <span class="ttiptext">Anular amonestación</span>
                        </button>
                        ';
        }


        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $persona,
            $infoAmonestacion,
            $acciones
        );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }