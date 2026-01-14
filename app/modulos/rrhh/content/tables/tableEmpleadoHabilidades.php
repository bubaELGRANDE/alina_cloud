<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataEstadoPersona = $cloud->row("
        SELECT
            estadoPersona
        FROM th_personas
        WHERE personaId = ?
    ",[$_POST["id"]]);

    // Habilidades = Idioma, Informática, Habilidades
    if($_POST["tipoHabilidad"] == "Idioma") {
        $txtTipoHabilidad = '<i class="fas fa-language"></i> Idioma';
        $tHabilidad = "idioma";
    } else if($_POST['tipoHabilidad'] == "Informática") {
        $txtTipoHabilidad = '<i class="fas fa-laptop"></i> Conocimiento informático';
        $tHabilidad = "informática";
    } else if($_POST["tipoHabilidad"] == "Equipo") {
        $txtTipoHabilidad = '<i class="fas fa-tools"></i> Herramienta/Equipo';
        $tHabilidad = "equipo";
    } else {
        $txtTipoHabilidad = '<i class="fas fa-chalkboard-teacher"></i> Conocimiento/Habilidad';
        $tHabilidad = "habilidad";
    }

    // Pendiente agregar AJAX y las consultas abajo
    $dataEmpleadoHabilidades = $cloud->rows("
        SELECT
            prsHabilidadId, 
            personaId, 
            tipoHabilidad, 
            habilidadPersona, 
            nivelHabilidad
        FROM th_personas_habilidades
        WHERE personaId = ? AND tipoHabilidad = ? AND flgDelete = '0'
    ", [$_POST["id"], $_POST["tipoHabilidad"]]);
    $n = 0;
    foreach ($dataEmpleadoHabilidades as $dataEmpleadoHabilidades) {
        $n += 1;

        $habilidad = '
            <b>'.$txtTipoHabilidad.': </b> '.$dataEmpleadoHabilidades->habilidadPersona.'<br>
            <b><i class="fas fa-chart-line"></i> Nivel: </b> '.$dataEmpleadoHabilidades->nivelHabilidad.'
        ';

        if($dataEstadoPersona->estadoPersona == "Inactivo") {
            $disabledInactivo = "disabled";
        } else {
            $disabledInactivo = "";
        }

        $acciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalHabilidad(`editar`,`'.$tHabilidad.'`,`'.$dataEmpleadoHabilidades->prsHabilidadId.'`);" '.$disabledInactivo.'>
                <i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarHabilidad(`'.$dataEmpleadoHabilidades->prsHabilidadId.'^'.$tHabilidad.'`);" '.$disabledInactivo.'>
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
        ';

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $habilidad,
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