<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataAmonestaciones = $cloud->row("
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
    CONCAT(
        IFNULL(nombre1, '-'),
        IFNULL(apellido1, '-')
    ) AS nombreUsuario,
    am.expedienteAmonestacionId,
    am.expedienteIdJefe,
    am.amonestacionAnteriorId AS amonestacionAnteriorId,
    am.expedienteId,
    date_format(am.fechaAmonestacion, '%d/%m/%Y')  as fechaAmonestacion,
    am.tipoAmonestacion,
    am.causaFalta,
    am.descripcionFalta AS descripcionFalta,
    am.consecuenciaFalta AS consecuenciaFalta,
    am.descripcionConsecuencia AS descripcionConsecuencia,
    am.compromisoMejora AS compromisoMejora,
    am.flgReincidencia,
    am.estadoAmonestacion,
    date_format(am.fechaSuspensionInicio, '%d/%m/%Y') as fechaSuspensionInicio,
    date_format(am.fechaSuspensionFin, '%d/%m/%Y') as fechaSuspensionFin,
    DATE_FORMAT(amAnterior.fechaAmonestacion, '%d/%m/%Y') AS fechaAmonestacionAnterior
    FROM ((th_expediente_amonestaciones am
    JOIN th_expediente_personas exp ON am.expedienteId = exp.prsExpedienteId)
    JOIN th_personas per ON per.personaId = exp.personaId)
    LEFT JOIN th_expediente_amonestaciones amAnterior ON am.amonestacionAnteriorId = amAnterior.expedienteAmonestacionId
    WHERE am.flgDelete = 0 and am.expedienteAmonestacionId = ?
    ", [$_POST["arrayFormData"]]);

?>

<div class="row">
    <div class="col-md-6">
        <i class="fas fa-user-tie"></i> <b>Empleado:</b> <?php echo $dataAmonestaciones->nombreCompleto; ?><br>
        <i class="fas fa-calendar"></i> <b>Fecha de amonestación:</b> <?php echo $dataAmonestaciones->fechaAmonestacion; ?><br>
        <b><i class="fas fa-times-circle"></i> Reincidencia: </b>
        
        <?php 
        
        if ($dataAmonestaciones->flgReincidencia == "Si") {
            $reincidencia = $dataAmonestaciones->flgReincidencia . ': ' . $dataAmonestaciones->amonestacionAnteriorId . ' - ' . $dataAmonestaciones->fechaAmonestacionAnterior;
            } else {
            $reincidencia = $dataAmonestaciones->flgReincidencia;
            };
        
            echo $reincidencia ?><br>
        <b><i class="fas fa-user-times"></i> Causa:</b> <?php echo $dataAmonestaciones->causaFalta; ?><br>
        <?php if (!empty($dataAmonestaciones->descripcionFalta)) : ?>
            <b><i class="fas fa-user-times"></i> Descripción:</b> <?php echo $dataAmonestaciones->descripcionFalta; ?>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <b><i class="fas fa-user-times"></i> Tipo de amonestación:</b> <?php echo $dataAmonestaciones->tipoAmonestacion; ?><br>
        <b>Consecuencias: <span class="text-danger"><?php echo $dataAmonestaciones->consecuenciaFalta; ?></span></b><br>
        <b><i class="fas fa-user-check"></i> Descripción de la consecuencia:</b> <?php echo $dataAmonestaciones->descripcionConsecuencia; ?><br>
        <b><i class="fas fa-user-times"></i> Estado: 
            <?php if ($dataAmonestaciones->estadoAmonestacion == "Activo"): ?>
                <span class="text-success"><?php echo $dataAmonestaciones->estadoAmonestacion; ?></span>
            <?php else: ?>
                <span class="text-danger"><?php echo $dataAmonestaciones->estadoAmonestacion; ?></span>
            <?php endif; ?>
        </b><br>
        <?php if ($dataAmonestaciones->fechaSuspensionInicio && $dataAmonestaciones->fechaSuspensionFin): ?>
            <b><i class="fas fa-calendar-check"></i> Fecha de la suspensión: </b><br>
            Desde: <?php echo $dataAmonestaciones->fechaSuspensionInicio; ?><br>
            Hasta: <?php echo $dataAmonestaciones->fechaSuspensionFin; ?><br>
        <?php endif; ?>
    </div>

</div>
