<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
$urlVariables = $_POST["estadoExpediente"]; // Para redirigir al tab correspondiente
$personaId = $_POST["personaId"];

$dataExpedientes = $cloud->rows("
        SELECT 
            exp.prsExpedienteId as prsExpedienteId, 
            exp.personaId as personaId,
            exp.sucursalDepartamentoId as sucursalDepartamentoId, 
            exp.tipoContrato as tipoContrato, 
            exp.fechaInicio as fechaInicio, 
            exp.fechaFinalizacion as fechaFinalizacion, 
            exp.justificacionEstado as justificacionEstado,
            exp.estadoExpediente as estadoExpediente,
            exp.tipoVacacion as tipoVacacion,
            per.estadoPersona as estadoPersona,
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
            car.prsCargoId as prsCargoId,
            dep.departamentoSucursal as departamentoSucursal,
            dep.sucursalId as sucursalId,
            exp.fhEdit as fhEdit
        FROM th_expediente_personas exp
        LEFT JOIN th_personas per ON per.personaId = exp.personaId
        LEFT JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
        LEFT JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
        WHERE exp.flgDelete = 0 AND exp.personaId = ?
        ORDER BY prsExpedienteId DESC
    ", [$personaId]);
?>
<h2>
    Movimientos de expedientes: <?php echo $_POST["nombreCompleto"]; ?>
</h2>
<hr>
<div class="row mb-4">
    <div class="col-lg-3">
        <button type="button" id="btnPageExpedientes" class="btn btn-secondary btn-block ttip"
            onclick="asyncPage(15, 'submenu', '<?php echo $urlVariables; ?>');">
            <i class="fas fa-chevron-circle-left"></i>
            Expedientes
            <span class="ttiptext">Volver a Expedientes</span>
        </button>
    </div>
</div>
<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1" role="tab"
            aria-controls="ntab-content-1" aria-selected="true">
            General
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2" role="tab"
            aria-controls="ntab-content-2" aria-selected="false">
            Solicitudes
        </a>
    </li>
    <!--<li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-3" data-mdb-toggle="pill" href="#ntab-content-3" role="tab" aria-controls="ntab-content-3" aria-selected="false">
            Vacaciones
        </a>
    </li>-->
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-4" data-mdb-toggle="pill" href="#ntab-content-4" role="tab"
            aria-controls="ntab-content-4" aria-selected="false">
            Amonestaciones
        </a>
    </li>
</ul>
<hr>
<div class="tab-content" id="ntab-content">
    <div class="tab-pane fade show active" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">
        <?php
        $n = 0;
        foreach ($dataExpedientes as $dataExpedientes) {
            $n += 1;

            $sucursalId = $dataExpedientes->sucursalId;
            $dataSucursal = $cloud->row("SELECT sucursal FROM cat_sucursales WHERE sucursalId = ?", [$sucursalId]);

            if (is_null($dataExpedientes->fechaFinalizacion)) {
                $fechaFin = 'Indefinido';
            } else {
                $fechaFin = date("d/m/Y", strtotime($dataExpedientes->fechaFinalizacion));
            }

            $estadoPersona = ($dataExpedientes->estadoPersona == "Activo") ? '<span class="text-success fw-bold">Activo</span>' : '<span class="text-danger fw-bold">' . $dataExpedientes->estadoPersona . '</span>';
            $drawJustificacionBaja = '';
            switch ($dataExpedientes->estadoExpediente) {
                case "Inactivo": // Se le cambió el cargo
                    $estadoExpediente = '<span class="text-secondary fw-bold">Inactivo</span>';
                    $expedienteColor = 'secundario';
                    $drawJustificacionBaja = '
                            <i class="fas fa-edit"></i> <b>Justificación del estado:</b> ' . $dataExpedientes->justificacionEstado . '<br>
                            <i class="fas fa-calendar-times"></i> <b>Fecha del cambio de estado: </b> ' . date("d/m/Y", strtotime($dataExpedientes->fhEdit)) . '
                        ';
                    break;

                case "Finalizado": // Alcanzó la fecha de finalización de su contrato
                    $estadoExpediente = '<span class="text-warning fw-bold">Contrato finalizado</span>';
                    $expedienteColor = 'advertencia';
                    break;

                case "Jubilado":
                    $estadoExpediente = '<span class="text-secondary fw-bold">Jubilado</span>';
                    $expedienteColor = 'secundario';
                    break;

                case "Despido":
                    $estadoExpediente = '<span class="text-danger fw-bold">Despido</span>';
                    $expedienteColor = 'alerta';

                    // Acá no aplica el estadoBaja ya que se itera específicamente cada expediente
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
                        ", [$dataExpedientes->prsExpedienteId]);

                    $drawJustificacionBaja = '
                            <i class="fas fa-edit"></i> <b>Justificación de despido:</b> ' . $dataExpedientes->justificacionEstado . '<br>
                            <i class="fas fa-calendar-times"></i> <b>Fecha de despido: </b> ' . date("d/m/Y", strtotime($dataMotivoBaja->fechaBaja)) . '
                        ';
                    break;

                case "Renuncia":
                    $estadoExpediente = '<span class="text-danger fw-bold">Renuncia</span>';
                    $expedienteColor = 'alerta';

                    // Acá no aplica el estadoBaja ya que se itera específicamente cada expediente
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
                        ", [$dataExpedientes->prsExpedienteId]);

                    $drawJustificacionBaja = '
                            <i class="fas fa-edit"></i> <b>Justificación de renuncia:</b> ' . $dataExpedientes->justificacionEstado . '<br>
                            <i class="fas fa-calendar-times"></i> <b>Fecha de renuncia: </b> ' . date("d/m/Y", strtotime($dataMotivoBaja->fechaBaja)) . '
                        ';
                    break;

                case "Abandono":
                    $estadoExpediente = '<span class="text-danger fw-bold">Abandono</span>';
                    $expedienteColor = 'alerta';

                    // Acá no aplica el estadoBaja ya que se itera específicamente cada expediente
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
                        ", [$dataExpedientes->prsExpedienteId]);

                    $drawJustificacionBaja = '
                            <i class="fas fa-edit"></i> <b>Justificación de abandono:</b> ' . $dataExpedientes->justificacionEstado . '<br>
                            <i class="fas fa-calendar-times"></i> <b>Fecha de abandono: </b> ' . date("d/m/Y", strtotime($dataMotivoBaja->fechaBaja)) . '
                        ';
                    break;

                case "Defunción":
                    $estadoExpediente = '<span class="text-danger fw-bold">Defunción</span>';
                    $expedienteColor = 'alerta';

                    // Acá no aplica el estadoBaja ya que se itera específicamente cada expediente
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
                        ", [$dataExpedientes->prsExpedienteId]);

                    $drawJustificacionBaja = '
                            <i class="fas fa-edit"></i> <b>Justificación de defunción:</b> ' . $dataExpedientes->justificacionEstado . '<br>
                            <i class="fas fa-calendar-times"></i> <b>Fecha de defunción: </b> ' . date("d/m/Y", strtotime($dataMotivoBaja->fechaBaja)) . '
                        ';
                    break;

                case "Traslado":
                    $estadoExpediente = '<span class="text-secondary fw-bold">Traslado</span>';
                    $expedienteColor = 'alerta';

                    // Acá no aplica el estadoBaja ya que se itera específicamente cada expediente
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
                        ", [$dataExpedientes->prsExpedienteId]);

                    $drawJustificacionBaja = '
                            <i class="fas fa-edit"></i> <b>Justificación de traslado:</b> ' . $dataExpedientes->justificacionEstado . '<br>
                            <i class="fas fa-calendar-times"></i> <b>Fecha de traslado: </b> ' . date("d/m/Y", strtotime($dataMotivoBaja->fechaBaja)) . '
                        ';
                    break;

                case "Pendiente": // Fecha a futuro
                    $estadoExpediente = '<span class="text-stihl fw-bold">Pendiente (expira en x días</span>';
                    $expedienteColor = 'advertencia';

                    // Traer la justificación que se guardó en el expediente actual "Activo"
                    // Esta justificación está en el "Activo" porque cuando se desactive desde el Cronjob, quedará reflejado en ese registro
                    $dataJustificacion = $cloud->row("
                            SELECT
                                justificacionEstado
                            FROM th_expediente_personas
                            WHERE personaId = ? AND estadoExpediente = 'Activo' AND flgDelete = '0'
                        ", [$dataExpedientes->personaId]);
                    $drawJustificacionBaja = '
                            <i class="fas fa-edit"></i> <b>Justificación del estado:</b> ' . $dataJustificacion->justificacionEstado . '
                        ';
                    break;

                default:
                    $estadoExpediente = '<span class="text-success fw-bold">Activo</span>';
                    $expedienteColor = 'activo';
                    break;
            }
            ?>
            <div class="exp-card <?php echo $expedienteColor; ?>">
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <i class="fas fa-user-tie"></i> <b>Nombre:</b> <?php echo $dataExpedientes->nombreCompleto; ?><br>
                        <i class="fas fa-briefcase"></i> <b>Cargo:</b>
                        <?php echo ($dataExpedientes->cargoPersona == "" ? '-' : $dataExpedientes->cargoPersona); ?><br>
                        <i class="fas fa-building"></i> <b>Sucursal:</b>
                        <?php echo (isset($dataSucursal->sucursal) ? $dataSucursal->sucursal : '-'); ?><br>
                        <i class="far fa-building"></i> <b>Departamento:</b>
                        <?php echo ($dataExpedientes->departamentoSucursal == "" ? '-' : $dataExpedientes->departamentoSucursal); ?><br>
                    </div>
                    <div class="col-md-3 mb-4">
                        <i class="fas fa-calendar-check"></i> <b>Fecha de inicio:</b>
                        <?php echo date("d/m/Y", strtotime($dataExpedientes->fechaInicio)); ?><br>
                        <i class="fas fa-calendar-times"></i> <b>Fecha de finalización:</b> <?php echo $fechaFin; ?><br>
                        <i class="fas fa-user"></i> <b>Empleado:</b> <?php echo $estadoPersona; ?><br>
                        <i class="fas fa-briefcase"></i> <b>Expediente:</b> <?php echo $estadoExpediente; ?><br>
                        <?php echo $drawJustificacionBaja; ?>
                    </div>
                    <div class="col-md-3 mb-4">
                        <!-- <b><i class="fas fa-money-check-alt"></i> Tipo de salario: </b> <?php //echo $dataExpedientes->tipoSalario; ?><br>
                            <b><i class="fas fa-money-bill-wave"></i> Salario: </b> <?php //echo '$ ' . $dataExpedientes->salario; ?><br>
                            <b><i class="fas fa-file-invoice-dollar"></i> Forma de remuneración: </b> <?php //echo $dataExpedientes->tipoRemuneracion; ?> -->
                        <b><i class="fas fa-business-time"></i> Horario de trabajo:</b><br>
                        <?php
                        $dataHorarios = $cloud->rows("
                                    SELECT
                                    expedienteHorarioId, 
                                    prsExpedienteId, 
                                    diaInicio, 
                                    diaFin, 
                                    TIME_FORMAT(horaInicio, '%h:%i %p') as horaInicio, 
                                    TIME_FORMAT(horaFin, '%h:%i %p') as horaFin,
                                    horasLaborales
                                    FROM th_expediente_horarios
                                    WHERE prsExpedienteId = ? AND flgDelete = '0'
                                ", [$dataExpedientes->prsExpedienteId]);
                        $n = 0;
                        foreach ($dataHorarios as $horarios) {
                            $n++;
                            if ($horarios->diaInicio == $horarios->diaFin) {
                                echo '
                                        <i class="fas fa-calendar"></i> El día ' . $horarios->diaInicio . '<br>
                                        <i class="fas fa-clock"></i> de ' . $horarios->horaInicio . ' a ' . $horarios->horaFin . '<br>
                                        ';
                            } else {
                                echo '
                                        <i class="fas fa-calendar"></i> Del día ' . $horarios->diaInicio . ' al día ' . $horarios->diaFin . '<br>
                                        <i class="fas fa-clock"></i> de ' . $horarios->horaInicio . ' a ' . $horarios->horaFin . '<br>
                                        ';
                            }
                        }
                        echo ($n == 0 ? 'No se encontraron registros' : '');
                        ?>
                    </div>
                    <div class="col-md-3 mb-4">
                        <b><i class="fas fa-umbrella-beach"></i> Vacaciones:</b>
                        <?php echo ($dataExpedientes->tipoVacacion == "" ? '-' : $dataExpedientes->tipoVacacion); ?>
                    </div>
                </div>
                <?php
                if ($dataExpedientes->estadoExpediente == "Activo") {
                    $jsonCambioSucursal = array(
                        "nombreCompleto" => $dataExpedientes->nombreCompleto,
                        "personaId" => $dataExpedientes->personaId,
                        "prsExpedienteId" => $dataExpedientes->prsExpedienteId,
                        "sucursalDepartamentoId" => $dataExpedientes->sucursalDepartamentoId,
                        "sucursal" => $dataSucursal->sucursal,
                        "departamentoSucursal" => $dataExpedientes->departamentoSucursal,
                        "estadoExpediente" => $dataExpedientes->estadoExpediente
                    );

                    ?>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-outline-success btn-sm"
                                onclick="modalExpediente('edit^<?php echo $personaId . '^' . $dataExpedientes->prsExpedienteId; ?>');"><i
                                    class="fas fa-sync-alt"></i> Cambio de cargo</button>
                            <!-- <button type="button" class="btn btn-outline-success btn-sm" onclick="modalHistorialSalario(`<?php //echo $dataExpedientes->prsExpedienteId; ?>^<?php //echo $dataExpedientes->estadoExpediente; ?>`);"><i class="fas fa-pen"></i> Historial de salarios</button> -->
                            <button type="button" class="btn btn-outline-success btn-sm"
                                onclick="modalHorarios(`<?php echo $dataExpedientes->prsExpedienteId . '^' . $dataExpedientes->estadoExpediente . '^' . $personaId; ?>`);"><i
                                    class="fas fa-business-time"></i> Editar horarios</button>
                            <button type="button" class="btn btn-outline-success btn-sm"
                                onclick="modalCambioSucursal(<?php echo htmlspecialchars(json_encode($jsonCambioSucursal)); ?>);">
                                <i class="fas fa-building"></i> Gestión de sucursal/departamento
                            </button>
                            <!--<button type="button" class="btn btn-outline-success btn-sm"
                                onclick="modalExpediente('modify^<?php echo $personaId . '^' . $dataExpedientes->prsExpedienteId; ?>');"><i
                                    class="fas fa-edit"></i> Editar
                            </button>-->
                        </div>
                    </div>
                    <?php
                } else {
                    // Expediente inactivo, sin acciones
                }
                ?>
            </div>

            <?php
        } // foreach dataExpediente
        ?>
    </div>
    <div class="tab-pane fade" id="ntab-content-2" role="tabpanel" aria-labelledby="ntab-2">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="nav flex-column nav-tabs text-center" id="v-tabs-tab" role="tablist"
                    aria-orientation="vertical">
                    <!--<a class="nav-link active" id="v-tabs-1-tab" data-mdb-toggle="tab" href="#v-tabs-1" role="tab" aria-controls="v-tabs-1" aria-selected="true">Permisos</a>-->
                    <a class="nav-link active" id="v-tabs-2-tab" data-mdb-toggle="tab" href="#v-tabs-2" role="tab"
                        aria-controls="v-tabs-2" aria-selected="false">Incapacidades</a>
                    <a class="nav-link" id="v-tabs-3-tab" data-mdb-toggle="tab" href="#v-tabs-3" role="tab"
                        aria-controls="v-tabs-3" aria-selected="false">Ausencias</a>
                    <a class="nav-link" id="v-tabs-4-tab" data-mdb-toggle="tab" href="#v-tabs-4" role="tab"
                        aria-controls="v-tabs-4" aria-selected="false">Vacaciones</a>
                </div>
            </div>
            <div class="col-lg-9 mb-4">
                <div class="tab-content" id="vtabs-mainContent">
                    <!--<div class="tab-pane fade show active" id="v-tabs-1" role="tabpanel" aria-labelledby="v-tabs-1-tab">
                        Permisos...
                    </div>-->
                    <div class="tab-pane fade show active" id="v-tabs-2" role="tabpanel" aria-labelledby="v-tabs-2-tab">
                        <?php
                        $dataIncapacidades = $cloud->rows("
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
                                date_format(inca.fechaInicio, '%d-%m-%Y') as fechaIni,
                                date_format(inca.fechaFin, '%d-%m-%Y') as fechaFin,
                                date_format(inca.fechaExpedicion, '%d-%m-%Y') as fechaExp,
                                inca.motivoIncapacidad as motivo,
                                inca.incapacidadSubsidio as subsidio,
                                inca.prsAdjuntoId as adjuntoId,
                                inca.riesgoIncapacidad as riesgoIncapacidad,
                                inca.expedienteIncapacidadId as incapacidadId
                                FROM ((th_expediente_incapacidades inca
                                JOIN th_expediente_personas exp ON inca.expedienteId = exp.prsExpedienteId)
                                JOIN th_personas per ON per.personaId = exp.personaId)
                                WHERE inca.flgDelete = 0 AND per.personaId = ? 
                                ORDER BY fechaExp DESC
                            ", [$personaId]);

                        $n = 0;
                        foreach ($dataIncapacidades as $incapacidad) {
                            $n += 1;
                            echo '<div class="exp-card activo">';
                            echo '<i class="fas fa-calendar"></i> <b>Fecha de expedición:</b> ' . $incapacidad->fechaExp;
                            echo '<br><i class="fas fa-align-left"></i> <b>Riesgo:</b> ' . $incapacidad->riesgoIncapacidad;
                            echo '<br><i class="fas fa-align-left"></i> <b>Motivo:</b> ' . $incapacidad->motivo;
                            echo '<br><i class="fas fa-calendar"></i> <b>Fecha de inicio:</b> ' . $incapacidad->fechaIni . '<br><b><i class="fas fa-calendar"></i> Fecha de finalización:</b> ' . $incapacidad->fechaFin;
                            echo '</div>';
                        } // foreach
                        
                        if ($n == 0) {
                            echo "No se encontraron resultados";
                        }
                        ?>
                    </div>
                    <div class="tab-pane fade" id="v-tabs-3" role="tabpanel" aria-labelledby="v-tabs-3-tab">
                        <?php
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
                                WHERE au.flgDelete = 0 AND per.personaId = ?
                                ORDER BY fechaSolicitud DESC
                            ", [$personaId]);

                        $n = 0;
                        foreach ($dataAusencias as $ausencia) {
                            $n += 1;

                            echo '<div class="exp-card activo">';
                            echo '<i class="fas fa-calendar"></i> <b>Fecha de solicitud:</b> ' . $ausencia->fechaSolicitud;
                            echo '<br><b><i class="fas fa-align-left"></i></b> ' . $ausencia->motivo;
                            echo '<br><i class="fas fa-calendar"></i> <b> Inicio:</b> ' . $ausencia->fechaIni . '<br><b><i class="fas fa-calendar"></i> Finalización:</b> ' . $ausencia->fechaFin;
                            if ($ausencia->estadoSolicitudAu == "Anulada") {
                                echo "<br><b>Anulada</b>";
                            } else {
                                echo "";
                            }
                            echo "</div>";

                        } // foreach
                        
                        if ($n == 0) {
                            echo "No se encontraron resultados";
                        }
                        ?>
                    </div>
                    <div class="tab-pane fade" id="v-tabs-4" role="tabpanel" aria-labelledby="v-tabs-4-tab">
                        <?php
                        $dataVaca = $cloud->rows("
                            SELECT
                                vaca.expedienteVacacionesId,
                                per.personaId as personaId, 
                                vaca.expedienteId,
                                CONCAT(
                                    IFNULL(per.apellido1, '-'),
                                    ' ',
                                    IFNULL(per.apellido2, '-'),
                                    ', ',
                                    IFNULL(per.nombre1, '-'),
                                    ' ',
                                    IFNULL(per.nombre2, '-')
                                ) AS nombreCompleto,
                                date_format(vaca.fhSolicitud, '%d-%m-%Y') AS fhSolicitud,
                                vaca.periodoVacaciones,
                                vaca.numDias,
                                date_format(vaca.fechaInicio, '%d-%m-%Y') AS fechaInicio,
                                date_format(vaca.fechaFin, '%d-%m-%Y') AS fechaFin,
                                date_format(vaca.fhaprobacion, '%d-%m-%Y') AS fhaprobacion,
                                vaca.estadoSolicitud
                                FROM ((th_expedientes_vacaciones vaca
                                JOIN th_expediente_personas exp ON vaca.expedienteId = exp.prsExpedienteId)
                                JOIN th_personas per ON per.personaId = exp.personaId)
                                WHERE vaca.flgDelete = 0 AND vaca.estadoSolicitud = 'Aprobado' AND per.personaId = ?
                                ORDER BY fhSolicitud DESC
                            ", [$personaId]);

                        $n = 0;
                        foreach ($dataVaca as $vacaciones) {
                            $n += 1;
                            echo '<div class="exp-card activo">';
                            echo '<i class="fas fa-calendar"></i> <b>Fecha de solicitud:</b> ' . $vacaciones->fhSolicitud . '<br>
                                        <i class="fas fa-calendar"></i> <b>Fecha de Aprobación:</b> ' . $vacaciones->fhaprobacion . '<br>
                                        <i class="fas fa-calendar"></i> <b>Tipo de vacación:</b> ' . $vacaciones->periodoVacaciones . '<br>';

                            echo '<i class="fas fa-umbrella-beach"></i> <b>Número de días:</b> ' . $vacaciones->numDias . '<br>
                                        <i class="fas fa-calendar"></i> <b>Fecha de inicio:</b> ' . $vacaciones->fechaInicio . '<br>
                                        <b><i class="fas fa-calendar"></i> Fecha de finalización:</b> ' . $vacaciones->fechaFin;
                            echo "</div>";
                        } // foreach
                        
                        if ($n == 0) {
                            echo "No se encontraron resultados";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--<div class="tab-pane fade" id="ntab-content-3" role="tabpanel" aria-labelledby="ntab-3">
        En desarrollo...
    </div>-->
    <div class="tab-pane fade" id="ntab-content-4" role="tabpanel" aria-labelledby="ntab-4">
        <?php
        $dataAmonestaciones = $cloud->rows("
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
            am.expedienteAmonestacionId,
            am.expedienteIdJefe,
            am.expedienteId,
            date_format(am.fhAmonestacion, '%d-%m-%Y')  as fhAmonestacion,
            am.tipoAmonestacion,
            am.suspension,
            am.totalDiasSuspension,
            am.causaFalta,
            am.descripcionFalta,
            am.consecuenciaFalta,
            am.advertenciaSiguienteFalta,
            am.compromisoMejora,
            am.flgReincidencia,
            am.estadoAmonestacion,
            am.justificacionAnulada,
            date_format(am.fechaVigenciaInicio, '%d-%m-%Y') as fechaVigenciaInicio,
            date_format(am.fechaVigenciaFin, '%d-%m-%Y') as fechaVigenciaFin,
            date_format(am.fechaSuspensionInicio, '%d-%m-%Y') as fechaSuspensionInicio,
            date_format(am.fechaSuspensionFin, '%d-%m-%Y') as fechaSuspensionFin
            FROM ((th_expediente_amonestaciones am
            JOIN th_expediente_personas exp ON am.expedienteId = exp.prsExpedienteId)
            JOIN th_personas per ON per.personaId = exp.personaId)
            WHERE am.flgDelete = 0 AND per.personaId = ?
            ORDER BY fhAmonestacion DESC
        ", [$personaId]);
        $n = 0;
        foreach ($dataAmonestaciones as $amonestacion) {
            $n += 1;

            $persona = '<i class="fas fa-calendar"></i> <b>Fecha de amonestación:</b> ' . $amonestacion->fhAmonestacion . '<br>
                      <b><i class="fas fa-user-times"></i> Tipo de amonestación:</b> ' . $amonestacion->tipoAmonestacion . '<br>
                      ';

            if ($amonestacion->estadoAmonestacion == "Activo") {
                $persona .= '<b><i class="fas fa-user-times"></i> Amonestación: <span class="text-success">' . $amonestacion->estadoAmonestacion . '</span></b>';
            } else {
                $persona .= '<b><i class="fas fa-user-times"></i> Amonestación: <span class="text-danger">' . $amonestacion->estadoAmonestacion . '</span></b>';
            }

            $infoAmonestacion = '<br><b>Causa de la amonestación: </b> ' . $amonestacion->causaFalta . '<br>
                                <b><i class="fas fa-calendar-check"></i> Fecha de vigencia </b><br>
                                Desde: ' . $amonestacion->fechaVigenciaInicio . ', Hasta: ' . $amonestacion->fechaVigenciaFin . '<br>
                                <b><i class="fas fa-times-circle"></i> Reincidencia: </b>' . $amonestacion->flgReincidencia;
            if (!is_null($amonestacion->suspension)) {
                $infoAmonestacion .= '<br>
                                    <b>Estado: <span class="text-danger">' . $amonestacion->suspension . '</span></b><br>
                                    <b><i class="fas fa-calendar-times"></i> Duración de suspensión: </b>' . $amonestacion->totalDiasSuspension . ' días<br>
                                    Desde: ' . $amonestacion->fechaSuspensionInicio . ', Hasta: ' . $amonestacion->fechaSuspensionFin . '
                                    ';
            }
            if (!is_null($amonestacion->justificacionAnulada)) {
                $infoAmonestacion .= '<br>
                                    <b>Justificación de anulación:</b><br>
                                    ' . $amonestacion->justificacionAnulada;
            }
            echo '<div class="exp-card activo">';
            echo $persona;
            echo $infoAmonestacion;
            echo "</div>";
        }

        if ($n == 0) {
            echo "No se encontraron resultados";
        }
        ?>
    </div>
</div>
<script>



    /*
    function modalHistorialSalario(id, estado) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: `Historial de Salario`,
                modalForm: 'expedienteSalarios',
                formData: id,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalHorarios(id, estado) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Horarios de trabajo`,
                modalForm: 'expedienteHorarios',
                formData: id,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function modalCambioSucursal(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: `Cambio de sucursal/departamento - Empleado: ${frmData.nombreCompleto}`,
                modalForm: 'expedienteCambioSucursal',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    */

    $(document).ready(function () {
        /* $("#btnPageExpedientes").click(function() {
            // id 13 de tbl menus, no se puede usar changePage porque es exclusiva para "page-"
            asyncPage(15, 'submenu', '<?php echo $urlVariables; ?>');
        console.log('prueba');
    }); */

    // Tab: Estudios
    $('#tblEmpleadoEstudios thead tr#filterboxrow-estudio th').each(function (index) {
        if (index == 1 || index == 2) {
            var title = $('#tblEmpleadoEstudios thead tr#filterboxrow-estudio th').eq($(this).index()).text();
            $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}estudio" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}estudio">Buscar</label></div>${title}`);
            $(this).on('keyup change', function () {
                tblEmpleadoEstudios.column($(this).index()).search($(`#input${$(this).index()}estudio`).val()).draw();
            });
            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                new mdb.Input(formOutline).init();
            });
        } else {
        }
    });

    let tblEmpleadoEstudios = $('#tblEmpleadoEstudios').DataTable({
        "dom": 'lrtip',
        "ajax": {
            "method": "POST",
            "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoEstudios",
            "data": { // En caso que se quiera enviar variable a la consulta
                "id": '<?php echo $_POST["personaId"]; ?>'
            }
        },
        "autoWidth": false,
        "columns": [
            null,
            null,
            null,
            null
        ],
        "columnDefs": [
            { "orderable": false, "targets": [1, 2, 3] }
        ],
        "language": {
            "url": "../libraries/packages/js/spanish_dt.json"
        }
    });

    // Tab: Experiencia Laboral
    $('#tblEmpleadoExpLaboral thead tr#filterboxrow-explab th').each(function (index) {
        if (index == 1 || index == 2) {
            var title = $('#tblEmpleadoExpLaboral thead tr#filterboxrow-explab th').eq($(this).index()).text();
            $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}explab" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}explab">Buscar</label></div>${title}`);
            $(this).on('keyup change', function () {
                tblEmpleadoExpLaboral.column($(this).index()).search($(`#input${$(this).index()}explab`).val()).draw();
            });
            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                new mdb.Input(formOutline).init();
            });
        } else {
        }
    });

    let tblEmpleadoExpLaboral = $('#tblEmpleadoExpLaboral').DataTable({
        "dom": 'lrtip',
        "autoWidth": false,
        "ajax": {
            "method": "POST",
            "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoExpLaboral",
            "data": { // En caso que se quiera enviar variable a la consulta
                "id": '<?php echo $_POST["personaId"]; ?>'
            }
        },
        "columns": [
            null,
            null,
            null,
            null
        ],
        "columnDefs": [
            { "orderable": false, "targets": [1, 2, 3] }
        ],
        "language": {
            "url": "../libraries/packages/js/spanish_dt.json"
        }
    });

    // Tab: Habilidades
    // Idioma
    $('#tblEmpleadoHabIdiomas thead tr#filterboxrow-hab-idioma th').each(function (index) {
        if (index == 1) {
            var title = $('#tblEmpleadoHabIdiomas thead tr#filterboxrow-hab-idioma th').eq($(this).index()).text();
            $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}hab-idioma" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}hab-idioma">Buscar</label></div>${title}`);
            $(this).on('keyup change', function () {
                tblEmpleadoHabIdiomas.column($(this).index()).search($(`#input${$(this).index()}hab-idioma`).val()).draw();
            });
            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                new mdb.Input(formOutline).init();
            });
        } else {
        }
    });

    let tblEmpleadoHabIdiomas = $('#tblEmpleadoHabIdiomas').DataTable({
        "dom": 'lrtip',
        "ajax": {
            "method": "POST",
            "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoHabilidades",
            "data": { // En caso que se quiera enviar variable a la consulta
                "id": '<?php echo $_POST["personaId"]; ?>',
                "tipoHabilidad": 'Idioma'
            }
        },
        "autoWidth": false,
        "columns": [
            null,
            null,
            null
        ],
        "columnDefs": [
            { "orderable": false, "targets": [1, 2] }
        ],
        "language": {
            "url": "../libraries/packages/js/spanish_dt.json"
        }
    });

    // Informática
    $('#tblEmpleadoHabInformatico thead tr#filterboxrow-hab-informatico th').each(function (index) {
        if (index == 1) {
            var title = $('#tblEmpleadoHabInformatico thead tr#filterboxrow-hab-informatico th').eq($(this).index()).text();
            $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}hab-informatico" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}hab-informatico">Buscar</label></div>${title}`);
            $(this).on('keyup change', function () {
                tblEmpleadoHabInformatico.column($(this).index()).search($(`#input${$(this).index()}hab-informatico`).val()).draw();
            });
            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                new mdb.Input(formOutline).init();
            });
        } else {
        }
    });

    let tblEmpleadoHabInformatico = $('#tblEmpleadoHabInformatico').DataTable({
        "dom": 'lrtip',
        "ajax": {
            "method": "POST",
            "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoHabilidades",
            "data": { // En caso que se quiera enviar variable a la consulta
                "id": '<?php echo $_POST["personaId"]; ?>',
                "tipoHabilidad": 'Informática'
            }
        },
        "autoWidth": false,
        "columns": [
            null,
            null,
            null
        ],
        "columnDefs": [
            { "orderable": false, "targets": [1, 2] }
        ],
        "language": {
            "url": "../libraries/packages/js/spanish_dt.json"
        }
    });

    // Habilidad
    $('#tblEmpleadoHabHabilidad thead tr#filterboxrow-hab-habilidad th').each(function (index) {
        if (index == 1) {
            var title = $('#tblEmpleadoHabHabilidad thead tr#filterboxrow-hab-habilidad th').eq($(this).index()).text();
            $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}hab-habilidad" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}hab-habilidad">Buscar</label></div>${title}`);
            $(this).on('keyup change', function () {
                tblEmpleadoHabHabilidad.column($(this).index()).search($(`#input${$(this).index()}hab-habilidad`).val()).draw();
            });
            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                new mdb.Input(formOutline).init();
            });
        } else {
        }
    });

    let tblEmpleadoHabHabilidad = $('#tblEmpleadoHabHabilidad').DataTable({
        "dom": 'lrtip',
        "ajax": {
            "method": "POST",
            "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoHabilidades",
            "data": { // En caso que se quiera enviar variable a la consulta
                "id": '<?php echo $_POST["personaId"]; ?>',
                "tipoHabilidad": 'Habilidad'
            }
        },
        "autoWidth": false,
        "columns": [
            null,
            null,
            null
        ],
        "columnDefs": [
            { "orderable": false, "targets": [1, 2] }
        ],
        "language": {
            "url": "../libraries/packages/js/spanish_dt.json"
        }
    });
    // Herramienta-Equipo
    $('#tblEmpleadoHabEquipo thead tr#filterboxrow-hab-equipo th').each(function (index) {
        if (index == 1) {
            var title = $('#tblEmpleadoHabEquipo thead tr#filterboxrow-hab-equipo th').eq($(this).index()).text();
            $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}hab-equipo" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}hab-equipo">Buscar</label></div>${title}`);
            $(this).on('keyup change', function () {
                tblEmpleadoHabEquipo.column($(this).index()).search($(`#input${$(this).index()}hab-equipo`).val()).draw();
            });
            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                new mdb.Input(formOutline).init();
            });
        } else {
        }
    });

    let tblEmpleadoHabEquipo = $('#tblEmpleadoHabEquipo').DataTable({
        "dom": 'lrtip',
        "ajax": {
            "method": "POST",
            "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoHabilidades",
            "data": { // En caso que se quiera enviar variable a la consulta
                "id": '<?php echo $_POST["personaId"]; ?>',
                "tipoHabilidad": 'Equipo'
            }
        },
        "autoWidth": false,
        "columns": [
            null,
            null,
            null
        ],
        "columnDefs": [
            { "orderable": false, "targets": [1, 2] }
        ],
        "language": {
            "url": "../libraries/packages/js/spanish_dt.json"
        }
    });

    // Tab: Licencias
    // Licencia de Conducir
    $('#tblEmpleadoLicConducir thead tr#filterboxrow-lic-conducir th').each(function (index) {
        if (index == 1) {
            var title = $('#tblEmpleadoLicConducir thead tr#filterboxrow-lic-conducir th').eq($(this).index()).text();
            $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}lic-conducir" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}lic-conducir">Buscar</label></div>${title}`);
            $(this).on('keyup change', function () {
                tblEmpleadoLicConducir.column($(this).index()).search($(`#input${$(this).index()}lic-conducir`).val()).draw();
            });
            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                new mdb.Input(formOutline).init();
            });
        } else {
        }
    });

    let tblEmpleadoLicConducir = $('#tblEmpleadoLicConducir').DataTable({
        "dom": 'lrtip',
        "ajax": {
            "method": "POST",
            "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoLicencias",
            "data": { // En caso que se quiera enviar variable a la consulta
                "id": '<?php echo $_POST["personaId"]; ?>',
                "categoriaLicencia": 'Conducir'
            }
        },
        "autoWidth": false,
        "columns": [
            null,
            null,
            null
        ],
        "columnDefs": [
            { "orderable": false, "targets": [1, 2] }
        ],
        "language": {
            "url": "../libraries/packages/js/spanish_dt.json"
        }
    });

    // Licencia de armas
    $('#tblEmpleadoLicArma thead tr#filterboxrow-lic-arma th').each(function (index) {
        if (index == 1) {
            var title = $('#tblEmpleadoLicArma thead tr#filterboxrow-lic-arma th').eq($(this).index()).text();
            $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}lic-arma" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}lic-arma">Buscar</label></div>${title}`);
            $(this).on('keyup change', function () {
                tblEmpleadoLicArma.column($(this).index()).search($(`#input${$(this).index()}lic-arma`).val()).draw();
            });
            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                new mdb.Input(formOutline).init();
            });
        } else {
        }
    });

    let tblEmpleadoLicArma = $('#tblEmpleadoLicArma').DataTable({
        "dom": 'lrtip',
        "ajax": {
            "method": "POST",
            "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoLicencias",
            "data": { // En caso que se quiera enviar variable a la consulta
                "id": '<?php echo $_POST["personaId"]; ?>',
                "categoriaLicencia": 'Arma'
            }
        },
        "autoWidth": false,
        "columns": [
            null,
            null,
            null
        ],
        "columnDefs": [
            { "orderable": false, "targets": [1, 2] }
        ],
        "language": {
            "url": "../libraries/packages/js/spanish_dt.json"
        }
    });

    // Tab: Enfermedades/Alergias
    $('#tblEmpleadoEnfermedades thead tr#filterboxrow-enfermedad th').each(function (index) {
        if (index == 1 || index == 2) {
            var title = $('#tblEmpleadoEnfermedades thead tr#filterboxrow-enfermedad th').eq($(this).index()).text();
            $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}enfermedad" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}enfermedad">Buscar</label></div>${title}`);
            $(this).on('keyup change', function () {
                tblEmpleadoEnfermedades.column($(this).index()).search($(`#input${$(this).index()}enfermedad`).val()).draw();
            });
            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                new mdb.Input(formOutline).init();
            });
        } else {
        }
    });

    let tblEmpleadoEnfermedades = $('#tblEmpleadoEnfermedades').DataTable({
        "dom": 'lrtip',
        "autoWidth": false,
        "ajax": {
            "method": "POST",
            "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoEnfermedades",
            "data": { // En caso que se quiera enviar variable a la consulta
                "id": '<?php echo $_POST["personaId"]; ?>'
            }
        },
        "columns": [
            null,
            { "width": "70%" },
            null
        ],
        "columnDefs": [
            { "orderable": false, "targets": [1, 2] }
        ],
        "language": {
            "url": "../libraries/packages/js/spanish_dt.json"
        }
    });
    });

</script>