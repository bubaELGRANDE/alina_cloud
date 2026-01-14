<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataEstadoPersona = $cloud->row("
        SELECT
            estadoPersona
        FROM th_personas
        WHERE personaId = ?
    ",[$_POST["id"]]);

    $dataEmpleadoExpLaboral = $cloud->rows("
        SELECT
            exp.prsExpLaboralId AS prsExpLaboralId, 
            exp.personaId AS personaId, 
            exp.lugarTrabajo AS lugarTrabajo, 
            exp.paisId AS paisId, 
            p.pais AS pais,
            p.iconBandera AS iconBandera,
            exp.prsArExperienciaId prsArExperienciaId, 
            ar.areaExperiencia AS areaExperiencia,
            exp.cargoTrabajo AS cargoTrabajo, 
            exp.numMesInicio AS numMesInicio, 
            exp.mesInicio AS mesInicio, 
            exp.anioInicio AS anioInicio, 
            exp.numMesFinalizacion AS numMesFinalizacion, 
            exp.mesFinalizacion AS mesFinalizacion, 
            exp.anioFinalizacion AS anioFinalizacion,
            exp.motivoRetiro AS motivoRetiro
        FROM th_personas_exp_laboral exp
        JOIN cat_personas_ar_experiencia ar ON ar.prsArExperienciaId = exp.prsArExperienciaId
        JOIN cat_paises p ON p.paisId = exp.paisId
        WHERE exp.personaId = ? AND exp.flgDelete = '0'
    ", [$_POST["id"]]);
    $n = 0;
    foreach ($dataEmpleadoExpLaboral as $dataEmpleadoExpLaboral) {
        $n += 1;

        $expLaboral = '
            <b><i class="fas fa-building"></i> Lugar de trabajo: </b> '.$dataEmpleadoExpLaboral->lugarTrabajo.'<br>
            <b><i class="fas fa-briefcase"></i> Cargo desempeñado: </b> '.$dataEmpleadoExpLaboral->cargoTrabajo.'<br>
            <b><i class="fas fa-user-tie"></i> Área de trabajo: </b> '.$dataEmpleadoExpLaboral->areaExperiencia.'<br>
            <b><i class="fas fa-globe-americas"></i> País de trabajo: </b>
            <img src="../libraries/resources/images/'.$dataEmpleadoExpLaboral->iconBandera.'" alt="'.$dataEmpleadoExpLaboral->pais.'">
            '.$dataEmpleadoExpLaboral->pais.'
        ';  

        $numMesFinalizacion = $dataEmpleadoExpLaboral->numMesFinalizacion + 1;
        $anioFinalizacion = ($numMesFinalizacion == 13) ? $dataEmpleadoExpLaboral->anioFinalizacion + 1 : $dataEmpleadoExpLaboral->anioFinalizacion;
        $numMesFinalizacion = ($numMesFinalizacion == 13) ? 1 : $numMesFinalizacion;
        $fechaInicio = strtotime($dataEmpleadoExpLaboral->anioInicio . "-" . $dataEmpleadoExpLaboral->numMesInicio . "-1");
        $fechaFin = strtotime($anioFinalizacion . "-" . $numMesFinalizacion . "-1");

        $periodo = '
            <b><i class="fas fa-calendar"></i> Inicio: </b> '.$dataEmpleadoExpLaboral->mesInicio.' - '.$dataEmpleadoExpLaboral->anioInicio.'<br>
            <b><i class="fas fa-calendar-times"></i> Finalización: </b> '.$dataEmpleadoExpLaboral->mesFinalizacion.' - '.$dataEmpleadoExpLaboral->anioFinalizacion.'<br>
            <b><i class="fas fa-user-clock"></i> Tiempo de experiencia: </b> '.diferenciaFechas($fechaInicio, $fechaFin).'<br>
            <b><i class="fas fa-edit"></i> Motivo de retiro: </b> '.$dataEmpleadoExpLaboral->motivoRetiro.'
        ';

        if($dataEstadoPersona->estadoPersona == "Inactivo") {
            $disabledInactivo = "disabled";
        } else {
            $disabledInactivo = "";
        }

        $acciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalExpLaboral(`editar`,`'.$dataEmpleadoExpLaboral->prsExpLaboralId.'`);" '.$disabledInactivo.'>
                <i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarExpLaboral(`'.$dataEmpleadoExpLaboral->prsExpLaboralId.'`);" '.$disabledInactivo.'>
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
        ';

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $expLaboral,
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

    function diferenciaFechas($fechaInicio,$fechaFin) {
        $diff = '';
        $diferencia = ($fechaFin - $fechaInicio)/60/60/24;
        if($diferencia == 0) {
            $diff = 'Ninguno';
        } else if($diferencia == 1) {
            $diff = '1 día';
        } else if($diferencia < 31) {
            if($diferencia < 7) {
                $diff = $diferencia . ' días';
            } else if($diferencia < 14) {
                $diff = '1 semana';
            } else if($diferencia < 21) {
                $diff = '2 semanas';
            } else {
                $diff = '3 semanas';
            }
        } else if($diferencia < 365) {
            if($diferencia < 62) {
                $diff = '1 mes';
            } else {
                $diff = round($diferencia / 31) . ' meses';
            }
        } else {
            if($diferencia < 730) {
                $diff = '1 año';
            } else {
                $diff = round($diferencia / 365) . ' años';
            }
        }

        return $diff;
    }
?>