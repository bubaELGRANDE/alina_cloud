<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $tipoUsuario = ($_POST["tipoUsuario"] == "Empleado") ? 1 : 2;

    $flgMostrarTabla = 1;
    if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(56, $_SESSION["arrayPermisos"])) { // Todos
        $wherePermiso = ""; // Todos los usuarios
    } else if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(57, $_SESSION["arrayPermisos"])) {
        // Get usuarios a cargo
        // wherePermiso = us.personaId IN ($arrayPersonasACargo) AND
        $wherePermiso = ""; // Modificar al tener la tabla de empleados a cargo
    } else {
        // No se asignó ningún permiso
        $flgMostrarTabla = 0;
    }

    if($flgMostrarTabla == 1) {
        $dataUsuarios = $cloud->rows("
            SELECT
                us.usuarioId AS usuarioId,
                us.personaId AS personaId,
                us.correo AS correo,
                us.estadoUsuario AS estadoUsuario,
                us.numLogin AS numLogin,
                us.fhUltimoLogin AS fhUltimoLogin,
                us.enLinea AS enLinea,
                us.justificacionEstado AS justificacionEstado,
                per.estadoPersona AS estadoPersona,
                CONCAT(
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-'),
                    ', ',
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-')
                ) AS nombrePersona
            FROM conf_usuarios us
            JOIN th_personas per ON per.personaId = us.personaId
            WHERE $wherePermiso per.prsTipoId = ? AND us.estadoUsuario <> 'Pendiente' AND us.flgDelete = '0' AND per.flgDelete = '0'
        ", [$tipoUsuario]);
        $n = 0;
        foreach ($dataUsuarios as $dataUsuarios) {
            $n += 1;
     
            if($dataUsuarios->numLogin == 1) {
                $veces = "vez";
            } else {
                $veces = "veces";
            }

            $tipoPersona = ($_POST["tipoUsuario"] == "Empleado") ? "Empleado" : "Distribuidor";

            $usuario = '
                <b><i class="fas fa-user-tie"></i> '.$tipoPersona.': </b>' . $dataUsuarios->nombrePersona . '<br>
                <b><i class="fas fa-envelope"></i> Correo electrónico: </b>' . $dataUsuarios->correo . '<br>
                <b><i class="fas fa-sign-in-alt"></i> Inicios de sesión: </b>' . $dataUsuarios->numLogin . ' ' . $veces;   

            if($dataUsuarios->enLinea == 0) {
                if(is_null($dataUsuarios->fhUltimoLogin)) {
                    $fhUltimoLogin = "-";
                } else {
                    $fhUltimoLogin = date("d/m/Y H:i:s", strtotime($dataUsuarios->fhUltimoLogin));
                }
                $enlinea = '<span class="text-secondary"><b><i class="fas fa-toggle-off"></i> Desconectado</b></span><br><b><i class="fas fa-user-clock"></i> Últ. vez: </b>' . $fhUltimoLogin;
            } else {
                // Validar si ya pasó 1 día y su estado quedó enLinea = 1
                $fhHoy = strtotime(date("Y-m-d"));
                $soloFecha = explode(" ", $dataUsuarios->fhUltimoLogin);
                $fhUltimoLogin = strtotime($soloFecha[0]);
                    
                if($fhHoy == $fhUltimoLogin) {
                    $enlinea = '<span class="text-success"><b><i class="fas fa-toggle-on"></i> En línea</b></span>';
                } else {
                    $enlinea = '<span class="text-secondary"><b><i class="fas fa-toggle-off"></i> Desconectado</b></span><br><b><i class="fas fa-user-clock"></i> Últ. vez: </b>' . date("d/m/Y H:i:s", strtotime($dataUsuarios->fhUltimoLogin));
                }
            }

            /*
                ESTADOS USUARIOS: 
                Activo = Persona activa, usuario activo, todo bien
                Inhabilitado = RRHH
                Suspendido = Solicitud a Informática de restringir acceso
                Bloqueado = Alcanzó los 5 intentos
                Pendiente = Solicitud, no se muestra

                ESTADOS PERSONA: -NO CONFUNDIR CON EXPEDIENTE-
                Activo = Activo, su expediente sigue con un estado "activo"
                Inactivo = Se le dió de baja, el verdadero estado/motivo está en EXPEDIENTE
            */
           
            if($dataUsuarios->estadoUsuario == "Activo") {
                $estadoUsuario = '<span class="text-success"><b>Activo</b></span>';
                $btnEstado = '
                    <button type="button" class="btn btn-danger btn-sm ttip" onclick="modalSuspenderAcceso(`'.$dataUsuarios->usuarioId.'^'.$dataUsuarios->nombrePersona.'^'.$tipoPersona.'`);">
                        <i class="fas fa-user-lock"></i>
                        <span class="ttiptext">Suspender acceso</span>
                    </button>
                ';
            } else if($dataUsuarios->estadoUsuario == "Inhabilitado") { // Dado de bajar por RRHH
                // El estado "Inhabilitado" solo sucederá si en RRHH le dan de baja al Empleado
                $estadoUsuario = '<span class="text-danger"><b>Inhabilitado</b></span><br><span><b>Justificación/Motivo:</b> Dado de baja en RRHH.</span>';
                if($dataUsuarios->estadoPersona == "Activo") { // Si está activo se interpreta como que regresó a trabajar / RRHH cambió nuevamente el Empleado
                    // Por lo que, se debe volver a activar su usuario manualmente
                    $btnEstado = '
                        <button type="button" class="btn btn-success btn-sm ttip" onclick="procesarEstado(`'.$dataUsuarios->usuarioId.'`, `'.$dataUsuarios->nombrePersona.'`, `activar`, `'.$tipoPersona.'`);">
                            <i class="fas fa-user-check"></i>
                            <span class="ttiptext">Activar acceso</span>
                        </button>
                    ';
                } else { // Se encuentra dado de baja, no permitir volver a activar su usuario
                    $btnEstado = '
                        <button type="button" class="btn btn-success btn-sm" disabled>
                            <i class="fas fa-user-check"></i>
                        </button>
                    ';
                }
            } else if($dataUsuarios->estadoUsuario == "Suspendido") { // Dado de baja por Informática / Interfaz
                $estadoUsuario = '<span class="text-danger"><b>Suspendido</b></span><br><span><b>Justificación/Motivo:</b> '.$dataUsuarios->justificacionEstado.'</span>';
                $btnEstado = '
                    <button type="button" class="btn btn-success btn-sm ttip" onclick="procesarEstado(`'.$dataUsuarios->usuarioId.'`, `'.$dataUsuarios->nombrePersona.'`, `activar`, `'.$tipoPersona.'`);">
                        <i class="fas fa-user-check"></i>
                        <span class="ttiptext">Activar acceso</span>
                    </button>
                ';
            } else { // Bloqueado - Límite de intentos
                $estadoUsuario = '<span class="text-secondary"><b>Bloqueado</b></span><br><span><b>Justificación/Motivo:</b> Límite de intentos al iniciar sesión.';
                $btnEstado = '
                    <button type="button" class="btn btn-success btn-sm ttip" onclick="procesarEstado(`'.$dataUsuarios->usuarioId.'`, `'.$dataUsuarios->nombrePersona.'`, `activar`, `'.$tipoPersona.'`);">
                        <i class="fas fa-user-check"></i>
                        <span class="ttiptext">Activar acceso</span>
                    </button>
                ';
            }

            $estadoPersona = ($dataUsuarios->estadoPersona == "Activo") ? '<span class="text-success"><b>Activo</b></span>' : '<span class="text-danger"><b>Inactivo</b></span>';

            if($tipoPersona == "Empleado") {
                $drawEstadoEmpleado = '<b><i class="fas fa-user-tie"></i> Empleado: </b> ' . $estadoPersona . '<br>';
            } else {
                $drawEstadoEmpleado = '';
            }

            $estado = '
                <b><i class="fas fa-user"></i> Usuario: </b> ' . $estadoUsuario . '<br>
                '.$drawEstadoEmpleado.'
                <b><i class="fas fa-laptop"></i> Cloud: </b>' . $enlinea;


            if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(59, $_SESSION["arrayPermisos"]) || in_array(62, $_SESSION["arrayPermisos"])) { // interno or externo, mismas funciones
                $btnEditar = '
                    <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalEditarUsuario(`'.$dataUsuarios->usuarioId.'^'.$dataUsuarios->nombrePersona.'^'.$tipoPersona.'`);">
                        <i class="fas fa-pencil-alt"></i>
                        <span class="ttiptext">Editar</span>
                    </button>
                ';
            } else {
                $btnEditar = "";
            }

            if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(60, $_SESSION["arrayPermisos"]) || in_array(63, $_SESSION["arrayPermisos"])) { // interno or externo, mismas funciones
                $btnHabilitarSuspender = $btnEstado;
            } else {
                $btnHabilitarSuspender = "";
            }

            $acciones = '
                '.$btnEditar.'
                '.$btnHabilitarSuspender.'
            ';

            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $usuario,
                $estado,
                $acciones
            );
        } // foreach
        if($n > 0) {
            echo json_encode($output);
        } else {
            // No retornar nada para evitar error "null"
            echo json_encode(array('data'=>'')); 
        }
    } else {
        echo json_encode(array('data'=>'')); 
    }
?>