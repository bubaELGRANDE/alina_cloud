<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

if ($_POST['estado'] != 'Activo') {
    // Esta validación es para no listar expedientes de empleados en otro TAB si actualmente tienen un expediente ACTIVO, ya que saldrá 2 veces cuando en realidad su historial de expedientes estará en el expediente activo actual (tarjetitas de expediente con colores)
    if ($_POST['estado'] == "Pendiente") {
        $queryOtroEstado = " exp.fechaFinalizacion > curdate() and estadoExpediente != 'Activo'";
    } else {
        $queryOtroEstado = "
                AND exp.personaId NOT IN (
                    SELECT
                        personaId
                    FROM th_expediente_personas
                    WHERE estadoExpediente = 'Activo' AND flgDelete = '0'
                )
            ";
    }
} else {
    $queryOtroEstado = '';
}


if ($_POST['estado'] == 'Baja') {
    // Estos estados están en el update case = expediente-procesar-baja
    $dataExpedientes = $cloud->rows("
            SELECT 
                exp.prsExpedienteId as prsExpedienteId, 
                exp.personaId as personaId,
                exp.sucursalDepartamentoId as sucursalDepartamentoId, 
                exp.tipoContrato as tipoContrato, 
                exp.fechaInicio as fechaInicio, 
                exp.fechaFinalizacion as fechaFinalizacion, 
                exp.estadoExpediente as estadoExpediente,
                per.estadoPersona as estadoPersona,
                per.fechaInicioLabores as fechaInicioLabores,
                exp.justificacionEstado as justificacionEstado,
                CONCAT(
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-'),
                    ', ',
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-')
                ) AS nombreCompleto,
                car.cargoPersona as cargoPersona,
                dep.departamentoSucursal as departamentoSucursal,
                dep.sucursalId as sucursalId,
                s.sucursal as sucursal
            FROM th_expediente_personas exp
            LEFT JOIN th_personas per ON per.personaId = exp.personaId
            LEFT JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
            LEFT JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
            LEFT JOIN cat_sucursales s ON s.sucursalId = dep.sucursalId
            WHERE exp.flgDelete = ? AND (exp.estadoExpediente = 'Renuncia' OR exp.estadoExpediente = 'Despido' OR exp.estadoExpediente = 'Abandono' OR exp.estadoExpediente = 'Defunción' OR exp.estadoExpediente = 'Traslado') $queryOtroEstado
            ORDER BY exp.personaId, exp.prsExpedienteId DESC
        ", ['0']);
} elseif ($_POST['estado'] == 'Pendiente') {
    $dataExpedientes = $cloud->rows("
            SELECT 
                exp.prsExpedienteId as prsExpedienteId, 
                exp.personaId as personaId,
                exp.sucursalDepartamentoId as sucursalDepartamentoId, 
                exp.tipoContrato as tipoContrato, 
                exp.fechaInicio as fechaInicio, 
                exp.fechaFinalizacion as fechaFinalizacion, 
                exp.estadoExpediente as estadoExpediente,
                per.estadoPersona as estadoPersona,
                per.fechaInicioLabores as fechaInicioLabores,
                exp.justificacionEstado as justificacionEstado,
                CONCAT(
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-'),
                    ', ',
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-')
                ) AS nombreCompleto,
                car.cargoPersona as cargoPersona,
                dep.departamentoSucursal as departamentoSucursal,
                dep.sucursalId as sucursalId,
                s.sucursal as sucursal
            FROM th_expediente_personas exp
            LEFT JOIN th_personas per ON per.personaId = exp.personaId
            LEFT JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
            LEFT JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
            LEFT JOIN cat_sucursales s ON s.sucursalId = dep.sucursalId
            WHERE exp.flgDelete = 0 AND $queryOtroEstado
            ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
        ");
} else {
    $dataExpedientes = $cloud->rows("
            SELECT 
                exp.prsExpedienteId as prsExpedienteId, 
                exp.personaId as personaId,
                exp.sucursalDepartamentoId as sucursalDepartamentoId, 
                exp.tipoContrato as tipoContrato, 
                exp.fechaInicio as fechaInicio, 
                exp.fechaFinalizacion as fechaFinalizacion, 
                exp.estadoExpediente as estadoExpediente,
                per.estadoPersona as estadoPersona,
                per.fechaInicioLabores as fechaInicioLabores,
                exp.justificacionEstado as justificacionEstado,
                CONCAT(
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-'),
                    ', ',
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-')
                ) AS nombreCompleto,
                car.cargoPersona as cargoPersona,
                dep.departamentoSucursal as departamentoSucursal,
                dep.sucursalId as sucursalId,
                s.sucursal as sucursal
            FROM th_expediente_personas exp
            LEFT JOIN th_personas per ON per.personaId = exp.personaId
            LEFT JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
            LEFT JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
            LEFT JOIN cat_sucursales s ON s.sucursalId = dep.sucursalId
            WHERE exp.flgDelete = 0 AND exp.estadoExpediente = ? $queryOtroEstado
            ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
        ", [$_POST["estado"]]);
}
$n = 0;
$ultimoPersonaId = 0;
foreach ($dataExpedientes as $dataExpedientes) {
    $fechaInicio = ($dataExpedientes->fechaInicio == "" ? "-" : date("d/m/Y", strtotime($dataExpedientes->fechaInicio)));
    $fechaInicioLabores = ($dataExpedientes->fechaInicioLabores == "" ? "-" : date("d/m/Y", strtotime($dataExpedientes->fechaInicioLabores)));

    if ($dataExpedientes->fechaInicioLabores == "") {
        $antiguedad = "-";
    } else {
        $antiguedad = diferenciaFechasYMD(strtotime(date("Y-m-d")), strtotime($dataExpedientes->fechaInicioLabores));
    }

    if (!($_POST['estado'] == 'Baja' && $ultimoPersonaId == $dataExpedientes->personaId)) {
        $n += 1;

        $expediente = '
                <b><i class="fas fa-user-tie"></i> Nombre completo:</b> ' . $dataExpedientes->nombreCompleto . '<br>
                <b><i class="fas fa-briefcase"></i> Cargo:</b> ' . ($dataExpedientes->cargoPersona == "" ? '-' : $dataExpedientes->cargoPersona) . '<br>
                <b><i class="fas fa-building"></i> Sucursal:</b> ' . ($dataExpedientes->sucursal == "" ? '-' : $dataExpedientes->sucursal) . '<br>
                <b><i class="fas fa-building"></i> Departamento:</b> ' . ($dataExpedientes->departamentoSucursal == "" ? '-' : $dataExpedientes->departamentoSucursal) . '<br>
                <b><i class="fas fa-calendar"></i> Fecha de contratación:</b> ' . $fechaInicioLabores . '<br>
                <b><i class="fas fa-business-time"></i> Antigüedad:</b> ' . $antiguedad . '
            ';

        $fechaHoy = time();

        if (is_null($dataExpedientes->fechaFinalizacion)) {
            $fechaFin = 'Indefinido';
        } elseif (strtotime($dataExpedientes->fechaFinalizacion) > $fechaHoy) {

            $fechaFinal = strtotime($dataExpedientes->fechaFinalizacion);
            // echo $fechaFinal .' - '. $fechaHoy;
            $fechadif = $fechaFinal - $fechaHoy;

            $fechaFin = date("d/m/Y", strtotime($dataExpedientes->fechaFinalizacion)) . ' (Expira en ' . round($fechadif / (60 * 60 * 24), 1, PHP_ROUND_HALF_UP) . ' días).';
        } else {
            $fechaFin = date("d/m/Y", strtotime($dataExpedientes->fechaFinalizacion));
        }

        $estadoPersona = ($dataExpedientes->estadoPersona == "Activo") ? '<span class="text-success fw-bold">Activo</span>' : '<span class="text-danger fw-bold">' . $dataExpedientes->estadoPersona . '</span>';

        $justificacionEstado = "";
        $btnDarDeBaja = "";

        // Estos estados están en el update case = expediente-procesar-baja
        switch ($dataExpedientes->estadoExpediente) {
            case "Inactivo": // Este no debería suceder ya que se cambiarán automáticamente cuando se cambie de cargo
                $estadoExpediente = '<span class="text-secondary fw-bold">Inactivo</span>';
                break;

            case "Finalizado": // Alcanzó la fecha de finalización de su contrato
                $estadoExpediente = '<span class="text-warning fw-bold">Contrato finalizado</span>';
                $justificacionEstado = '<b><i class="fas fa-edit"></i> Justificación: </b> ' . $dataExpedientes->justificacionEstado;
                break;

            case "Jubilado":
                $estadoExpediente = '<span class="text-secondary fw-bold">Jubilado</span>';
                break;

            case "Despido":
                // Traer su última baja
                $dataMotivoBaja = $cloud->row("
                        SELECT
                            personaBajaId, 
                            prsExpedienteId, 
                            fechaBaja, 
                            contratable, 
                            justificacionBaja, 
                            estadoBaja
                        FROM bit_personas_bajas
                        WHERE prsExpedienteId = ? AND flgDelete = '0'
                        ORDER BY personaBajaId DESC
                        LIMIT 1
                    ", [$dataExpedientes->prsExpedienteId]);

                $contratable = ($dataMotivoBaja->contratable == "Sí") ? '<span class="text-success fw-bold">Sí</span>' : '<span class="text-danger fw-bold">No</span>';
                $estadoExpediente = '<span class="text-danger fw-bold">Despedido</span>';

                $justificacionEstado = '
                        <b><i class="fas fa-question-circle"></i> Contratable: </b> ' . $contratable . '<br>
                        <b><i class="fas fa-edit"></i> Justificación de despido: </b> ' . $dataExpedientes->justificacionEstado . '<br>
                        <b><i class="fas fa-calendar-times"></i> Fecha de despido: </b> ' . date("d/m/Y", strtotime($dataMotivoBaja->fechaBaja)) . '
                    ';
                break;

            case "Renuncia":
                // Traer su última baja
                $dataMotivoBaja = $cloud->row("
                        SELECT
                            personaBajaId, 
                            prsExpedienteId, 
                            fechaBaja, 
                            contratable, 
                            justificacionBaja, 
                            estadoBaja
                        FROM bit_personas_bajas
                        WHERE prsExpedienteId = ? AND flgDelete = '0'
                        ORDER BY personaBajaId DESC
                        LIMIT 1
                    ", [$dataExpedientes->prsExpedienteId]);

                $contratable = ($dataMotivoBaja->contratable == "Sí") ? '<span class="text-success fw-bold">Sí</span>' : '<span class="text-danger fw-bold">No</span>';
                $estadoExpediente = '<span class="text-danger fw-bold">Renuncia</span>';

                $justificacionEstado = '
                        <b><i class="fas fa-question-circle"></i> Contratable: </b> ' . $contratable . '<br>
                        <b><i class="fas fa-edit"></i> Justificación de renuncia: </b> ' . $dataExpedientes->justificacionEstado . '<br>
                        <b><i class="fas fa-calendar-times"></i> Fecha de renuncia: </b> ' . date("d/m/Y", strtotime($dataMotivoBaja->fechaBaja)) . '
                    ';
                break;

            case "Abandono":
                // Traer su última baja
                $dataMotivoBaja = $cloud->row("
                        SELECT
                            personaBajaId, 
                            prsExpedienteId, 
                            fechaBaja, 
                            contratable, 
                            justificacionBaja, 
                            estadoBaja
                        FROM bit_personas_bajas
                        WHERE prsExpedienteId = ? AND flgDelete = '0'
                        ORDER BY personaBajaId DESC
                        LIMIT 1
                    ", [$dataExpedientes->prsExpedienteId]);

                $contratable = ($dataMotivoBaja->contratable == "Sí") ? '<span class="text-success fw-bold">Sí</span>' : '<span class="text-danger fw-bold">No</span>';
                $estadoExpediente = '<span class="text-danger fw-bold">Abandono</span>';

                $justificacionEstado = '
                        <b><i class="fas fa-question-circle"></i> Contratable: </b> ' . $contratable . '<br>
                        <b><i class="fas fa-edit"></i> Justificación de abandono: </b> ' . $dataExpedientes->justificacionEstado . '<br>
                        <b><i class="fas fa-calendar-times"></i> Fecha de abandono: </b> ' . date("d/m/Y", strtotime($dataMotivoBaja->fechaBaja)) . '
                    ';
                break;

            case "Defunción":
                // Traer su última baja
                $dataMotivoBaja = $cloud->row("
                        SELECT
                            personaBajaId, 
                            prsExpedienteId, 
                            fechaBaja, 
                            contratable, 
                            justificacionBaja, 
                            estadoBaja
                        FROM bit_personas_bajas
                        WHERE prsExpedienteId = ? AND flgDelete = '0'
                        ORDER BY personaBajaId DESC
                        LIMIT 1
                    ", [$dataExpedientes->prsExpedienteId]);

                $contratable = ($dataMotivoBaja->contratable == "Sí") ? '<span class="text-success fw-bold">Sí</span>' : '<span class="text-danger fw-bold">No</span>';
                $estadoExpediente = '<span class="text-danger fw-bold">Defunción</span>';

                $justificacionEstado = '
                        <b><i class="fas fa-question-circle"></i> Contratable: </b> ' . $contratable . '<br>
                        <b><i class="fas fa-edit"></i> Justificación de defunción: </b> ' . $dataExpedientes->justificacionEstado . '<br>
                        <b><i class="fas fa-calendar-times"></i> Fecha de defunción: </b> ' . date("d/m/Y", strtotime($dataMotivoBaja->fechaBaja)) . '
                    ';
                break;

            case "Traslado":
                // Traer su última baja
                $dataMotivoBaja = $cloud->row("
                        SELECT
                            personaBajaId, 
                            prsExpedienteId, 
                            fechaBaja, 
                            contratable, 
                            justificacionBaja, 
                            estadoBaja
                        FROM bit_personas_bajas
                        WHERE prsExpedienteId = ? AND flgDelete = '0'
                        ORDER BY personaBajaId DESC
                        LIMIT 1
                    ", [$dataExpedientes->prsExpedienteId]);

                $contratable = ($dataMotivoBaja->contratable == "Sí") ? '<span class="text-success fw-bold">Sí</span>' : '<span class="text-danger fw-bold">No</span>';
                $estadoExpediente = '<span class="text-secondary fw-bold">Traslado</span>';

                $justificacionEstado = '
                        <b><i class="fas fa-question-circle"></i> Contratable: </b> ' . $contratable . '<br>
                        <b><i class="fas fa-edit"></i> Justificación de traslado: </b> ' . $dataExpedientes->justificacionEstado . '<br>
                        <b><i class="fas fa-calendar-times"></i> Fecha de traslado: </b> ' . date("d/m/Y", strtotime($dataMotivoBaja->fechaBaja)) . '
                    ';
                break;

            case "Pendiente":
                $estadoExpediente = '<span class="text-stihl fw-bold">Pendiente</span>';
                // Traer la justificación que se guardó en el expediente actual "Activo"
                // Esta justificación está en el "Activo" porque cuando se desactive desde el Cronjob, quedará reflejado en ese registro
                $dataJustificacion = $cloud->row("
                        SELECT
                            justificacionEstado
                        FROM th_expediente_personas
                        WHERE personaId = ? AND estadoExpediente = 'Pendiente' AND flgDelete = '0'
                    ", [$dataExpedientes->personaId]);
                $justificacionEstado = '
                        <b><i class="fas fa-edit"></i> Justificación del estado: </b> ' . $dataJustificacion->justificacionEstado . '
                    ';
                break;

            default:
                $estadoExpediente = '<span class="text-success fw-bold">Activo</span>';
                // Lleva aca el divider porque en los otros tabs no se muestra
                $btnDarDeBaja = '
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a role="button" class="dropdown-item link-danger" onclick="modalBajaExpediente(`' . $dataExpedientes->prsExpedienteId . '^' . $dataExpedientes->nombreCompleto . '^' . $dataExpedientes->cargoPersona . '`);">
                                <i class="fas fa-user-slash"></i> Dar de Baja
                            </a>
                        </li>
                    ';
                break;
        }

        $estado = '
                <b><i class="fas fa-calendar"></i> Fecha de inicio (nuevo cargo):</b> ' . $fechaInicio . '<br>
                <b><i class="fas fa-calendar-times"></i> Fecha de finalización:</b> ' . $fechaFin . '<br>
                <b><i class="fas fa-user-tie"></i> Empleado:</b> ' . $estadoPersona . '<br>
                <b><i class="fas fa-briefcase"></i> Expediente:</b> ' . $estadoExpediente . '<br>
                ' . $justificacionEstado . '
            ';

        $otrasAcciones = '
               <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-mdb-toggle="dropdown" aria-expanded="false" >
                    Acciones
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li>
                        <a role="button" class="dropdown-item" onclick="modalJefaturaEmpleado(`' . $dataExpedientes->prsExpedienteId . '`);">
                            <i class="fas fa-project-diagram"></i> Jefaturas
                        </a>
                    </li>
                    ' . $btnDarDeBaja . '
                </ul>
            ';

        $jsonUpdateFecha = array(
            "personaId" => $dataExpedientes->personaId
        );

        $funcionUpdateFecha = htmlspecialchars(json_encode($jsonUpdateFecha));

        $acciones = '
                <button type="button" class="btn btn-primary btn-sm ttip" onclick="changePage(`' . $_SESSION["currentRoute"] . '`, `expediente-empleado`, `personaId=' . $dataExpedientes->personaId . '&nombreCompleto=' . $dataExpedientes->nombreCompleto . '&estadoExpediente=' . $dataExpedientes->estadoExpediente . '`);">
                    <i class="fas fa-folder-open"></i>
                    <span class="ttiptext">Movimientos de expedientes</span>
                </button>
                ' . $otrasAcciones . '
                <button type="button" class="btn  btn-primary btn-sm ttip" onclick="modalFechaInicio(' . $funcionUpdateFecha . ');">
                    <i class="fas fa-calendar"></i> Fecha de contratación 
                </button>
               <button type="button" class="btn btn-primary btn-sm mt-1"
                    onclick="modalExpediente(\'modify^' . $dataExpedientes->personaId . '^' . $dataExpedientes->prsExpedienteId . '\')">
                    <i class="fas fa-edit"></i> Fecha inicio cargo
                </button>';

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $expediente,
            $estado,
            $acciones
        );

        $ultimoPersonaId = $dataExpedientes->personaId;
    } else {
        // Omitir para no mostrar expedientes anteriores, solamente el último que dejó cada empleado
    }
}

if ($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data' => ''));
}

function diferenciaFechasYMD($fechaPublicacion, $fechaActual)
{
    $diferencia = ($fechaPublicacion - $fechaActual);

    $anios = floor($diferencia / (365 * 60 * 60 * 24));
    $meses = floor(($diferencia - $anios * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
    $dias = floor(($diferencia - $anios * 365 * 60 * 60 * 24 - $meses * 30 * 60 * 60 * 24) / (60 * 60 * 24) + 1);

    $txtAnio = ($anios == 0 ? "" : ($anios == 1 ? "Un año, " : $anios . " años, "));
    $txtMeses = ($meses == 0 ? "" : ($meses == 1 ? "Un mes, " : $meses . " meses, "));
    $txtDias = ($dias == 1 ? "Un día" : $dias . " días");

    $antiguiedad = $txtAnio . $txtMeses . $txtDias;

    if ($fechaActual == "") {
        // Para los que no se les creó expediente
        return "-";
    } else {
        if ($anios >= 0) { // La fecha de publicacion era mayor que la actual (contratación a futuro)
            return $antiguiedad;
        } else {
            return "-";
        }
    }
}
?>