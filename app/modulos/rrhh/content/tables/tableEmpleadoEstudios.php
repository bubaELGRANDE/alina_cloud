<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataEstadoPersona = $cloud->row("
        SELECT
            estadoPersona
        FROM th_personas
        WHERE personaId = ?
    ",[$_POST["id"]]);

    $dataEmpleadoEstudios = $cloud->rows("
        SELECT
            pe.prsEducacionId AS prsEducacionId, 
            pe.centroEstudio AS centroEstudio, 
            pe.nivelEstudio AS nivelEstudio, 
            pe.prsArEstudioId AS prsArEstudioId, 
            ar.areaEstudio AS areaEstudio,
            pe.nombreCarrera AS nombreCarrera, 
            pe.paisId AS paisId, 
            p.pais AS pais,
            p.iconBandera AS iconBandera,
            pe.numMesInicio AS numMesInicio, 
            pe.mesInicio AS mesInicio, 
            pe.anioInicio AS anioInicio, 
            pe.numMesFinalizacion AS numMesFinalizacion, 
            pe.mesFinalizacion AS mesFinalizacion, 
            pe.anioFinalizacion AS anioFinalizacion, 
            pe.estadoEstudio AS estadoEstudio
        FROM th_personas_educacion pe
        LEFT JOIN cat_personas_ar_estudio ar ON ar.prsArEstudioId = pe.prsArEstudioId
        JOIN cat_paises p ON p.paisId = pe.paisId
        WHERE pe.personaId = ? AND pe.flgDelete = '0'
    ", [$_POST["id"]]);
    $n = 0;
    foreach ($dataEmpleadoEstudios as $dataEmpleadoEstudios) {
        $n += 1;

        if($dataEmpleadoEstudios->nivelEstudio == "Técnico/Profesional" || $dataEmpleadoEstudios->nivelEstudio == "Universidad" || $dataEmpleadoEstudios->nivelEstudio == "Postgrado" || $dataEmpleadoEstudios->nivelEstudio == "Diplomado" || $dataEmpleadoEstudios->nivelEstudio == "Curso" || $dataEmpleadoEstudios->nivelEstudio == "Curso - INSAFORP") {

            if($dataEmpleadoEstudios->nivelEstudio == "Diplomado") {
                $txtCarrera = "Nombre del diplomado";
            } else if($dataEmpleadoEstudios->nivelEstudio == "Curso" || $dataEmpleadoEstudios->nivelEstudio == "Curso - INSAFORP") {
                $txtCarrera = "Nombre del curso";
            } else {
                $txtCarrera = "Carrera";
            }

            $carreraNivel = '
                <br>
                <b><i class="fas fa-graduation-cap"></i> '.$txtCarrera.': </b> '.$dataEmpleadoEstudios->nombreCarrera.'<br>
                <b><i class="fas fa-chalkboard-teacher"></i> Área de estudio: </b> '.$dataEmpleadoEstudios->areaEstudio.'
            ';
        } else {
            $carreraNivel = '';
        }

        $estudio = '
            <b><i class="fas fa-user-graduate"></i> Lugar de estudio: </b> '.$dataEmpleadoEstudios->centroEstudio.'<br>
            <b><i class="fas fa-chart-bar"></i> Nivel de estudio: </b> '.$dataEmpleadoEstudios->nivelEstudio.'<br>
            <b><i class="fas fa-globe-americas"></i> País de estudio: </b>
            <img src="../libraries/resources/images/'.$dataEmpleadoEstudios->iconBandera.'" alt="'.$dataEmpleadoEstudios->pais.'">
            '.$dataEmpleadoEstudios->pais.'
            '.$carreraNivel.'
        ';  

        if($dataEmpleadoEstudios->estadoEstudio == "Cursando" || $dataEmpleadoEstudios->estadoEstudio == "Incompleto") {
            $estadoFinalizacion = $dataEmpleadoEstudios->estadoEstudio;
        } else {
            $estadoFinalizacion = $dataEmpleadoEstudios->mesFinalizacion . ' - ' . $dataEmpleadoEstudios->anioFinalizacion;
        }

        $periodo = '
            <b><i class="fas fa-calendar"></i> Inicio: </b> '.$dataEmpleadoEstudios->mesInicio.' - '.$dataEmpleadoEstudios->anioInicio.'<br>
            <b><i class="fas fa-calendar-check"></i> Finalización: </b> '.$estadoFinalizacion.'
        ';

        if($dataEstadoPersona->estadoPersona == "Inactivo") {
            $disabledInactivo = "disabled";
        } else {
            $disabledInactivo = "";
        }

        $acciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalEstudio(`editar`,`'.$dataEmpleadoEstudios->prsEducacionId.'`);" '.$disabledInactivo.'>
                <i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarEstudio(`'.$dataEmpleadoEstudios->prsEducacionId.'`);" '.$disabledInactivo.'>
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
        ';

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $estudio,
            $periodo,
            $acciones
        );
    } // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>