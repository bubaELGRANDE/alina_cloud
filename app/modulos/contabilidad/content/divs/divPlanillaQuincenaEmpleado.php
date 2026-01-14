<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    // La vista ya lleva flgDelete
    $dataExpediente = $cloud->row("
        SELECT
            personaId,
            estadoExpediente,
            estadoPersona,
            fechaNacimiento, 
            fechaInicioLabores,
            cargoPersona,
            departamentoSucursal,
            sucursal,
            codEmpleado,
            nombreGastoSalario,
            nombreCompleto
        FROM view_expedientes
        WHERE prsExpedienteId = ?
    ", [$_POST["prsExpedienteId"]]);

    if($_POST["estadoQuincena"] == "Pendiente") {
        $divNotaEstadoQuincena = "
            <div class='alert alert-info mt-2' role='alert'>
                <b><i class='fas fa-info-circle'></i> Nota: </b> Los montos reflejados son una previsualización y se aplicarán cuando genere el cálculo de la quincena.
            </div>
        ";
        $divNotaEstadoDescuentos = "
            <div class='alert alert-info mt-2' role='alert'>
                <b><i class='fas fa-info-circle'></i> Nota: </b> Los descuentos que se agreguen quedarán como programados y se aplicarán cuando genere el cálculo de la quincena.
            </div>
        ";

        // Al crear el generado, enviar planillaId para que se redireccione a la tabla correspondiente
        $jsonOtrosDescuentos = array(
            "nombreCompleto"        => $dataExpediente->nombreCompleto,
            "prsExpedienteId"       => $_POST['prsExpedienteId'],
            "quincenaId"            => $_POST['quincenaId']
        );
        $jsonOtrosDescuentos = htmlspecialchars(json_encode($jsonOtrosDescuentos));

        $jsonDevengosGravados = array(
            "nombreCompleto"        => $dataExpediente->nombreCompleto,
            "prsExpedienteId"       => $_POST['prsExpedienteId'],
            "quincenaId"            => $_POST['quincenaId'],
            "tipoDevengo"           => "Gravado"
        );
        $jsonDevengosGravados = htmlspecialchars(json_encode($jsonDevengosGravados));

        $jsonDevengosNoGravados = array(
            "nombreCompleto"        => $dataExpediente->nombreCompleto,
            "prsExpedienteId"       => $_POST['prsExpedienteId'],
            "quincenaId"            => $_POST['quincenaId'],
            "tipoDevengo"           => "No Gravado"
        );
        $jsonDevengosNoGravados = htmlspecialchars(json_encode($jsonDevengosNoGravados));
    } else {
        // Agregar nota que la quincena ya fue generada
        $divNotaEstadoQuincena = "";
        $divNotaEstadoDescuentos = "";
    }
?>
<style>
    .hoverable-row:hover {
        background-color: #E0DFDF;
    }
</style>
<div class="perfil">
    <div class="row justify-content-between">
        <div class="col-md-3">
            <?php 
                $dataPersImg = $cloud->row("
                    SELECT COUNT(prsAdjuntoId) AS fotoPerfil 
                    FROM th_personas_adjuntos 
                    WHERE flgDelete = '0' AND descripcionPrsAdjunto = 'Actual' AND personaId = ?
                "
                , [$dataExpediente->personaId]) ;

                if($dataPersImg->fotoPerfil == 1) {
                    $dataUserImg = $cloud->row("
                        SELECT 
                            urlPrsAdjunto
                        FROM th_personas_adjuntos 
                        WHERE personaId = ? AND descripcionPrsAdjunto = 'Actual' AND flgDelete = '0'
                    ", [$dataExpediente->personaId]);

                    $fotoPerfil = $dataUserImg->urlPrsAdjunto;
                } else {
                    $fotoPerfil = "mi-perfil/user-default.jpg";
                }
            ?>
            <div class="user-pic" style="background-image: url('../libraries/resources/images/<?php echo $fotoPerfil; ?>'); width: 150px; height: 150px;">
            </div>
            <div class="info-emp">
                <?php 
                    $estadoExpediente = ($dataExpediente->estadoExpediente == "Activo" ? '<span class="text-success fw-bold">Activo</span>' : '<span class="text-danger fw-bold">Inactivo</span>');
                ?>
                <p>
                    <b><i class="fas fa-info-circle"></i> Estado:</b> <?php echo $estadoExpediente; ?><br>
                    <b><i class="fas fa-briefcase"></i> Cargo: </b> <?php echo $dataExpediente->cargoPersona; ?><br>
                    <b><i class="fas fa-user-tie"></i> Departamento: </b> <?php echo $dataExpediente->departamentoSucursal; ?><br>
                    <b><i class="fas fa-building"></i> Sucursal: </b> <?php echo $dataExpediente->sucursal; ?><br>
                    <b><i class="fas fa-calendar-day"></i> Inicio de labores:</b> <?php echo date("d/m/Y", strtotime($dataExpediente->fechaInicioLabores)); ?><br>
                    <b><i class="fas fa-business-time"></i> Antigüedad:</b> <?php echo diferenciaFechasYMD(strtotime(date("Y-m-d")), strtotime($dataExpediente->fechaInicioLabores)); ?><br>
                    <b><i class="fas fa-user-circle"></i> Edad:</b> <?php echo date_diff(date_create($dataExpediente->fechaNacimiento), date_create(date("Y-m-d")))->format('%y'); ?> años <br>
                    <b><i class="fas fa-calendar-day"></i> Fecha de nacimiento:</b> <?php echo date("d/m/Y", strtotime($dataExpediente->fechaNacimiento)); ?>
                </p>
            </div>
        </div>
        <div class="col-md-9">
            <div class="row">
                <div class="col-12">
                    <h3 class="display-6"><?php echo $dataExpediente->nombreCompleto; ?></h3>
                    <h5 class="display-9">Código: <?php echo $dataExpediente->codEmpleado; ?> Clasificación: <?php echo $dataExpediente->nombreGastoSalario; ?></h5>
                </div>
            </div>
            <hr>
            <div id="divCalculoQuincenaEmpleado"></div>
            <?php echo $divNotaEstadoQuincena; ?>
        </div>
    </div>
    <ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1" role="tab" aria-controls="ntab-content-1" aria-selected="true">
                Devengos
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2" role="tab" aria-controls="ntab-content-2" aria-selected="true">
                Otros Descuentos
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="ntab-3" data-mdb-toggle="pill" href="#ntab-content-3" role="tab" aria-controls="ntab-content-3" aria-selected="true">
                Días laborados
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="ntab-4" data-mdb-toggle="pill" href="#ntab-content-4" role="tab" aria-controls="ntab-content-4" aria-selected="true">
                Comisión
            </a>
        </li>
    </ul>
    <div class="tab-content" id="ntab-content">
        <div class="tab-pane fade show active" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">
            <div class="row">
                <div class="col-md-3">
                    <div class="nav flex-column nav-tabs text-center" id="v-tabs-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="gravados" data-mdb-toggle="tab" href="#gravados-tab" role="tab" aria-controls="gravados" aria-selected="true">Gravados</a>
                        <a class="nav-link" id="nogravados" data-mdb-toggle="tab" href="#nogravados-tab" role="tab" aria-controls="nogravados" aria-selected="false">No Gravados</a>
                        <a class="nav-link" id="horas-extras" data-mdb-toggle="tab" href="#horas-extras-tab" role="tab" aria-controls="horas-extras" aria-selected="false">Horas Extras</a>
                        <a class="nav-link" id="vacaciones" data-mdb-toggle="tab" href="#vacaciones-tab" role="tab" aria-controls="vacaciones" aria-selected="false">Vacaciones</a>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content" id="v-tabs-tabContent">
                        <div class="tab-pane fade show active" id="gravados-tab" role="tabpanel" aria-labelledby="gravados-tab">
                            <div class="text-end">
                                <button type="button" class="btn btn-primary ttip" onclick="modalDevengos(<?php echo $jsonDevengosGravados; ?>);">
                                    <i class="fas fa-plus-circle"></i> Devengo gravado
                                    <span class="ttiptext">Agregar devengo gravado</span>
                                </button>
                            </div>
                            <div id="divTblDevengoGravadoEmpleado" class="table-responsive">
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nogravados-tab" role="tabpanel" aria-labelledby="nogravados-tab">
                           <div class="text-end">
                                <button type="button" class="btn btn-primary ttip" onclick="modalDevengos(<?php echo $jsonDevengosNoGravados; ?>);">
                                    <i class="fas fa-plus-circle"></i> Devengo no gravado
                                    <span class="ttiptext">Agregar devengo no gravado</span>
                                </button>
                            </div>
                            <div id="divTblDevengoNoGravadoEmpleado" class="table-responsive">
                            </div>
                        </div>
                        <div class="tab-pane fade" id="horas-extras-tab" role="tabpanel" aria-labelledby="horas-extras-tab">
                            En desarrollo
                        </div>
                        <div class="tab-pane fade" id="vacaciones-tab" role="tabpanel" aria-labelledby="vacaciones-tab">
                            En desarrollo
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="ntab-content-2" role="tabpanel" aria-labelledby="ntab-2">
            <div class="text-end">
                <button type="button" class="btn btn-primary ttip" onclick="modalOtrosDescuentos(<?php echo $jsonOtrosDescuentos; ?>);">
                    <i class="fas fa-plus-circle"></i> Otros descuentos
                    <span class="ttiptext">Agregar otros descuentos</span>
                </button>
            </div>
            <div id="divTblDescuentosEmpleado" class="table-responsive">
            </div>
        </div>
        <div class="tab-pane fade" id="ntab-content-3" role="tabpanel" aria-labelledby="ntab-3">
                En desarrollo
        </div>
        <div class="tab-pane fade" id="ntab-content-4" role="tabpanel" aria-labelledby="ntab-4">
                En desarrollo
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        cargarCalculoEmpleado(<?php echo $_POST["prsExpedienteId"]; ?>, `<?php echo $dataExpediente->nombreCompleto; ?>`);
        cargarDescuentosEmpleado(<?php echo $_POST["prsExpedienteId"]; ?>, `<?php echo $dataExpediente->nombreCompleto; ?>`);
        cargarDevengosEmpleado(<?php echo $_POST["prsExpedienteId"]; ?>, `<?php echo $dataExpediente->nombreCompleto; ?>`, `Gravado`);
        cargarDevengosEmpleado(<?php echo $_POST["prsExpedienteId"]; ?>, `<?php echo $dataExpediente->nombreCompleto; ?>`, `No Gravado`);
    });
</script>
<?php 
    function diferenciaFechasYMD($fechaPublicacion,$fechaActual) {
        $diferencia = ($fechaPublicacion - $fechaActual);

        $anios = floor($diferencia / (365*60*60*24));
        $meses = floor(($diferencia - $anios * 365*60*60*24)/ (30*60*60*24) );
        $dias  = floor(($diferencia - $anios * 365*60*60*24 - $meses *30*60*60*24) / (60*60*24)+1);

        $txtAnio = ($anios == 0 ? "" : ($anios == 1 ? "Un año, " : $anios . " años, "));
        $txtMeses = ($meses == 0 ? "" : ($meses == 1 ? "Un mes, " : $meses . " meses, "));
        $txtDias = ($dias == 1 ? "Un día" : $dias . " días");

        $antiguiedad = $txtAnio . $txtMeses . $txtDias;

        if($anios >= 0) { // La fecha de publicacion era mayor que la actual (contratación a futuro)
            return $antiguiedad;
        } else {
            return "-";
        }
    }
?>