<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataEstadoPersona = $cloud->row("
        SELECT
            estadoPersona
        FROM th_personas
        WHERE personaId = ?
    ",[$_POST["id"]]);

    // categoriaLicencia = Conducir, Arma
    if($_POST["categoriaLicencia"] == "Conducir") {
        $tLicencia = "conducir";
    } else {
        $tLicencia = "arma";
    }

    // Pendiente agregar AJAX y las consultas abajo
    $dataEmpleadoLicencias = $cloud->rows("
        SELECT
            prsLicenciaId, 
            personaId, 
            categoriaLicencia, 
            tipoLicencia, 
            numLicencia, 
            fechaExpiracionLicencia,
            descripcionLicencia
        FROM th_personas_licencias
        WHERE personaId = ? AND categoriaLicencia = ? AND flgDelete = '0'
    ", [$_POST["id"], $_POST["categoriaLicencia"]]);
    $n = 0;
    foreach ($dataEmpleadoLicencias as $dataEmpleadoLicencias) {
        $n += 1;

        $descripcionLicencia = "";

        if($tLicencia == "arma") {
            if($dataEmpleadoLicencias->descripcionLicencia == "" || is_null($dataEmpleadoLicencias->descripcionLicencia)) {
                $descripcionLicencia = '<br><b><i class="fas fa-edit"></i> Descripción de licencia: </b> -';
            } else {
                $descripcionLicencia = '<br><b><i class="fas fa-edit"></i> Descripción de licencia: </b>' . $dataEmpleadoLicencias->descripcionLicencia;
            }
        } else {
            // conducir
        }

        $licencia = '
            <b><i class="fas fa-list-ul"></i> Tipo de licencia: </b> '.$dataEmpleadoLicencias->tipoLicencia.'<br>
            <b><i class="fas fa-address-card"></i> Número de licencia: </b> '.$dataEmpleadoLicencias->numLicencia.' (Expiración: '.$dataEmpleadoLicencias->fechaExpiracionLicencia.')
            '.$descripcionLicencia.'
        ';

        if($dataEstadoPersona->estadoPersona == "Inactivo") {
            $disabledInactivo = "disabled";
        } else {
            $disabledInactivo = "";
        }

        $acciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalLicencia(`editar`,`'.$tLicencia.'`,`'.$dataEmpleadoLicencias->prsLicenciaId.'`);" '.$disabledInactivo.'>
                <i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarLicencia(`'.$dataEmpleadoLicencias->prsLicenciaId.'^'.$tLicencia.'`);" '.$disabledInactivo.'>
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
        ';

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $licencia,
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