<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $n = 0;
    $capacitaciones = $cloud->rows('
        SELECT
            ecd.expedienteCapacitacionDetalleId as expedienteCapacitacionDetalleId, 
            ec.expedienteCapacitacionId as expedienteCapacitacionId, 
            ve.nombreCompleto as nombreCompleto,
            ve.cargoPersona as cargoPersona,
            ve.departamentoSucursal as departamentoSucursal,
            ve.sucursal as sucursal,
            ec.descripcionCapacitacion as descripcionCapacitacion,
            ec.nombreOrganizador as nombreOrganizador,
            ec.tipoFormacion as tipoFormacion,
            ec.tipoModalidad as tipoModalidad,
            ec.fechaIniCapacitacion as fechaIniCapacitacion,
            ec.fechaFinCapacitacion as fechaFinCapacitacion,
            ec.duracionCapacitacion as duracionCapacitacion,
            ec.costoInsaforp as costoInsaforp,
            ec.costoalina as costoalina,
            ve.personaId as personaId,
            ecd.prsAdjuntoId as prsAdjuntoId,
            CONCAT(ve.nombre1, ve.apellido1) AS carpetaEmpleado
        FROM th_expediente_capacitacion_detalle ecd
        JOIN th_expediente_capacitaciones ec ON ec.expedienteCapacitacionId = ecd.expedienteCapacitacionId
        JOIN view_expedientes ve ON ve.prsExpedienteId = ecd.prsExpedienteId
        WHERE ecd.flgDelete = ?
    ',[0]);
    foreach($capacitaciones as $capacitaciones){
        $n += 1;

        $jsonEditar = array(
            "typeOperation"             => "update",
            "expedienteCapacitacionId"  =>  $capacitaciones->expedienteCapacitacionId,
            "tituloModal"               =>  "Editar capacitación: $capacitaciones->descripcionCapacitacion"
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));

        $jsonEliminar = array(
            "typeOperation"                         => "delete",
            "operation"                             => "capacitaciones-detalle",
            "expedienteCapacitacionDetalleId"       => $capacitaciones->expedienteCapacitacionDetalleId,
            "nombreCompleto"                        => $capacitaciones->nombreCompleto,
            "descripcionCapacitacion"               => $capacitaciones->descripcionCapacitacion,
            "expedienteCapacitacionId"              => $capacitaciones->expedienteCapacitacionId
            
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));
        
        $empleado = '   <b><i class="fas fa-user"></i> Nombre completo:</b> '.$capacitaciones->nombreCompleto.'
                        <br><i class="fas fa-calendar"></i> <b>Cargo:</b> '.$capacitaciones->cargoPersona.'
                        <br><i class="fas fa-calendar"></i> <b>Departamento:</b> '.$capacitaciones->departamentoSucursal.'
                        <br><i class="fas fa-calendar"></i> <b>Sucursal:</b> '.$capacitaciones->sucursal.'
        ';

        $capacitacion = '   <b>Capacitación:</b> '.$capacitaciones->descripcionCapacitacion.'<br>
                            <b>Organizador:</b> '.$capacitaciones->nombreOrganizador.'<br>
                            <b>Tipo de formación:</b> '.$capacitaciones->tipoFormacion.'<br>
                            <b>Modalidad:</b> '.$capacitaciones->tipoModalidad.'<br>
        ';

        $duracion='
                    <b>Fecha de inicio:</b> '.date("d/m/Y", strtotime($capacitaciones->fechaIniCapacitacion)).'<br>
                    <b>Fecha de finalización:</b> '.date("d/m/Y",strtotime($capacitaciones->fechaFinCapacitacion)).'<br>
                    <b>Duración :</b> '.number_format($capacitaciones->duracionCapacitacion, 0, '.', ',').' Horas
        ';

        $costo='
                    <b>Costo Insaforp :</b> $ '.number_format($capacitaciones->costoInsaforp, 2, '.', ',').'<br>
                    <b>Costo empresa :</b>  $ '.number_format($capacitaciones->costoalina, 2, '.', ',').'
        ';

        if (is_null($capacitaciones->prsAdjuntoId) || $capacitaciones->prsAdjuntoId == ""){
            $jsonAdjunto = array(
                "expedienteCapacitacionDetalleId"       => $capacitaciones->expedienteCapacitacionDetalleId,
                "nombreCompleto"                        => $capacitaciones->nombreCompleto,
                "personaId"                             => $capacitaciones->personaId,
                "carpetaEmpleado"                       => $capacitaciones->carpetaEmpleado
            );

            $funcionAdjunto = htmlspecialchars(json_encode($jsonAdjunto));
            $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalCapacitacion('.$funcionEditar.');">
                            <i class="fas fa-pen"></i>
                            <span class="ttiptext">Editar</span>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm ttip" onClick="adjuntarArchivoCapacitacionInterna('.$funcionAdjunto.');">
                            <i class="fas fa-paperclip"></i>
                            <span class="ttiptext">Adjuntar</span>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm ttip" onClick="eliminarCapacitacion('.$funcionEliminar.')">
                            <i class="fas fa-trash-alt"></i>
                            <span class="ttiptext">Eliminar</span>
                        </button>
            ';
            }else{
                $jsonVista = array(
                    "prsAdjuntoId"     => $capacitaciones->prsAdjuntoId,
                    "nombreCompleto"    =>$capacitaciones->nombreCompleto
                );
                $funcionVista = htmlspecialchars(json_encode($jsonVista));
                $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalCapacitacion('.$funcionEditar.');">
                            <i class="fas fa-pen"></i>
                            <span class="ttiptext">Editar</span>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm ttip" onClick="verAdjuntoCapacitaciones('.$funcionVista.');">
                            <i class="fas fa-eye"></i>
                            <span class="ttiptext">Ver adjuntar</span>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm ttip" onClick="eliminarCapacitacion('.$funcionEliminar.')">
                            <i class="fas fa-trash-alt"></i>
                            <span class="ttiptext">Eliminar</span>
                        </button>
                ';
                } 

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $empleado,
            $capacitacion,
            $duracion,
            $costo,
            $acciones
        );
    }//forech

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }