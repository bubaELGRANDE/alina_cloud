<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    require_once("../../../../../libraries/includes/logic/functions/funciones-planilla.php");
    @session_start();

    $dataExpediente = $cloud->row("
        SELECT
            tipoContrato,
            tipoVacacion,
            estadoExpediente
        FROM th_expediente_personas
        WHERE prsExpedienteId = ? AND flgDelete = ?
    ", [$_POST['prsExpedienteId'], 0]);

    $jsonSalario = [
        "prsExpedienteId"           => $_POST['prsExpedienteId'],
        "estadoExpediente"          => $dataExpediente->estadoExpediente,
        "nombreEmpleado"            => $_POST['nombreCompleto'],
        "flgPlanilla"               => 1
    ];

    $existeSalario = $cloud->count("
        SELECT
            tipoSalario,
            salario
        FROM th_expediente_salarios
        WHERE prsExpedienteId = ? AND estadoSalario = ? AND flgDelete = ?
    ", [$_POST['prsExpedienteId'], 'Activo', 0]);

    if($existeSalario == 0) {
?>
        <div class="alert alert-danger" role="alert">
            <b><i class="fas fa-exclamation-circle"></i> Aviso: </b> No se ha asignado el salario del empleado.
            <br>
            <button type="button" class="btn btn-outline-danger btn-sm mt-2" onclick='modalHistorialSalario(<?php echo json_encode($jsonSalario); ?>);'>
                <i class="fas fa-money-check-alt"></i> Asignar salario
            </button>
        </div>
<?php
    } else {
        $dataSalarioExpediente = $cloud->row("
            SELECT
                tipoSalario,
                salario
            FROM th_expediente_salarios
            WHERE prsExpedienteId = ? AND estadoSalario = ? AND flgDelete = ?
            LIMIT 1
        ", [$_POST['prsExpedienteId'], 'Activo', 0]);

        $salarioBase = $dataSalarioExpediente->salario;
        $salarioQuincena = $salarioBase / 2;
        $diasLaborados = 15;
        $arrayDevengosGravados = getDevengos($cloud, $_POST['quincenaId'], $_POST['prsExpedienteId'], 'Gravado');
        $arrayAFP = getDescuentoLey($cloud, $_POST['quincenaId'], $_POST['prsExpedienteId'], 'AFP');
        $arrayAFPPatronal = getDescuentoLey($cloud, $_POST['quincenaId'], $_POST['prsExpedienteId'], 'AFP', 'Patronal');
        $arrayISSS = getDescuentoLey($cloud, $_POST['quincenaId'], $_POST['prsExpedienteId'], 'ISSS');
        $arrayISSSPatronal = getDescuentoLey($cloud, $_POST['quincenaId'], $_POST['prsExpedienteId'], 'ISSS', 'Patronal');
        $arrayRenta = getDescuentoRenta($cloud, $_POST['quincenaId'], $_POST['prsExpedienteId']);
        $arrayOtrosDescuentos = getOtrosDescuentos($cloud, $_POST['quincenaId'], $_POST['prsExpedienteId']);
        $arrayDevengosNoGravados = getDevengos($cloud, $_POST['quincenaId'], $_POST['prsExpedienteId'], 'No Gravado');
        $arraySalarioLiquido = getSalarioLiquido($cloud, $_POST['quincenaId'], $_POST['prsExpedienteId']);
?>
        <div class="text-end mb-4">
            <button type="button" class="btn btn-secondary btn-sm" onclick='modalHistorialSalario(<?php echo json_encode($jsonSalario); ?>);'>
                <i class="fas fa-sync-alt"></i> Actualizar salario
            </button>
        </div>
        <div class="row mb-4">
            <div class="col-6">
                <div class="row hoverable-row">
                    <div class='col-8'>
                        <b><i class="fas fa-user-tie"></i> Salario mensual:</b>
                    </div>
                    <div class='col-4 text-end'>
                        $ <?php echo number_format($salarioBase, 2, '.', ','); ?>
                    </div>
                </div>
            </div>
            <div class="col-6 text-end">
                <button type="button" id="btnTipoDescuento" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-exchange-alt"></i> Patronal
                </button>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-6">
                <?php 
                    $arrayDevengos = array(
                        array(
                            '<i class="fas fa-coins"></i> Salario base', 
                            '<i class="fas fa-user-clock"></i> Horas extras', 
                            '<i class="fas fa-umbrella-beach"></i> Vacaciones', 
                            '<i class="fas fa-hand-holding-usd"></i> Dev. gravados', 
                            '<i class="fas fa-user-plus"></i> Total devengado'
                        ),
                        array(
                            $salarioQuincena, 
                            0, 
                            0, 
                            $arrayDevengosGravados["Calculo"], 
                            $salarioQuincena + $arrayDevengosGravados["Calculo"]
                        ),
                        array(
                            "Salario devengado",
                            "N horas extras",
                            "Vacaciones",
                            $arrayDevengosGravados["Concepto"],
                            "Suma total de devengos"
                        )
                    );
                    for ($i = 0; $i < 5; $i++) { 
                        echo "
                            ".($i == 4 ? '<hr>' : '')."
                            <div class='row hoverable-row'>
                                <div class='col-8 ttip'>
                                    <b>".$arrayDevengos[0][$i].":</b>
                                    <span class='ttiptext'>".$arrayDevengos[2][$i]."</span>
                                </div>
                                <div class='col-4 text-end ttip'>
                                    $ ".number_format($arrayDevengos[1][$i], 2, '.', ',')."
                                    <span class='ttiptext'>".$arrayDevengos[2][$i]."</span>
                                </div>
                            </div>
                        ";
                    }
                ?>
            </div>
            <div id="divDescuentosTrabajador" class="col-6">
                <?php 
                    $arrayDescuentos = array(
                        array(
                            '<i class="fas fa-university"></i> AFP', 
                            '<i class="fas fa-hospital-user"></i> ISSS', 
                            '<i class="fas fa-list-ol"></i>  Renta', 
                            '<i class="fas fa-folder-minus"></i> Otros descuentos', 
                            '<i class="fas fa-user-minus"></i> Total descuentos'
                        ),
                        array(
                            $arrayAFP["Calculo"], 
                            $arrayISSS["Calculo"], 
                            $arrayRenta["Calculo"], 
                            $arrayOtrosDescuentos["Calculo"], 
                            $arrayISSS["Calculo"] + $arrayAFP["Calculo"] + $arrayRenta["Calculo"] + $arrayOtrosDescuentos["Calculo"]
                        ),
                        array(
                            $arrayAFP["Concepto"], 
                            $arrayISSS["Concepto"], 
                            $arrayRenta["Concepto"], 
                            $arrayOtrosDescuentos["Concepto"], 
                            "Suma total de descuentos"
                        )
                    );
                    for ($i = 0; $i < 5; $i++) { 
                        echo "
                            ".($i == 4 ? '<hr>' : '')."
                            <div class='row hoverable-row'>
                                <div class='col-8 ttip'>
                                    <b>".$arrayDescuentos[0][$i].":</b>
                                    <span class='ttiptext'>".$arrayDescuentos[2][$i]."</span>
                                </div>
                                <div class='col-4 text-end ttip'>
                                    $ ".number_format($arrayDescuentos[1][$i], 2, '.', ',')."
                                    <span class='ttiptext'>".$arrayDescuentos[2][$i]."</span>
                                </div>
                                
                            </div>
                        ";
                    }
                ?>
            </div>
            <div id="divDescuentosPatronal" class="col-6">
                <?php 
                    $arrayDescuentos = array(
                        array(
                            '<i class="fas fa-university"></i> AFP', 
                            '<i class="fas fa-hospital-user"></i> ISSS', 
                            '<i class="fas fa-user-minus"></i> Total descuentos'
                        ),
                        array(
                            $arrayAFPPatronal["Calculo"], 
                            $arrayISSSPatronal["Calculo"], 
                            $arrayISSS["Calculo"] + $arrayAFP["Calculo"]
                        ),
                        array(
                            $arrayAFPPatronal["Concepto"],
                            $arrayISSSPatronal["Concepto"], 
                            "Suma total de descuentos"
                        )
                    );
                    for ($i = 0; $i < 3; $i++) { 
                        echo "
                            ".($i == 2 ? '<hr>' : '')."
                            <div class='row hoverable-row'>
                                <div class='col-8 ttip'>
                                    <b>".$arrayDescuentos[0][$i].":</b>
                                    <span class='ttiptext'>".$arrayDescuentos[2][$i]."</span>
                                </div>
                                <div class='col-4 text-end ttip'>
                                    $ ".number_format($arrayDescuentos[1][$i], 2, '.', ',')."
                                    <span class='ttiptext'>".$arrayDescuentos[2][$i]."</span>
                                </div>
                                
                            </div>
                        ";
                    }
                ?>
            </div>
        </div>
        <div class="row hoverable-row">
            <div class="col-8">
                <b><i class="fas fa-calendar-day"></i> Días laborados:</b>
            </div>
            <div class="col-4 text-end">
                <?php echo number_format($diasLaborados, 0, '.', ','); ?> días
            </div>
        </div>
        <div class="row hoverable-row">
            <div class="col-8">
                <b><i class="fas fa-user-plus"></i> Devengos no gravados:</b>
            </div>
            <div class="col-4 text-end ttip">
                $ <?php echo number_format($arrayDevengosNoGravados["Calculo"], 2, '.', ','); ?>
                <span class="ttiptext"><?php echo $arrayDevengosNoGravados["Concepto"]; ?></span>
            </div>
        </div>
        <div class="row hoverable-row">
            <div class="col-8 ttip">
                <b><i class="fas fa-coins"></i> Salario líquido:</b>
                <span class="ttiptext"><?php echo $arraySalarioLiquido["Concepto"]; ?></span>
            </div>
            <div class="col-4 text-end ttip">
                $ <?php echo number_format($arraySalarioLiquido["Calculo"], 2, '.', ','); ?>
                <span class="ttiptext"><?php echo $arraySalarioLiquido["Concepto"]; ?></span>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $("#divDescuentosPatronal").hide();

                $("#btnTipoDescuento").click(function(e) {
                    $("#divDescuentosPatronal").toggle();
                    $("#divDescuentosTrabajador").toggle();
                    if($("#divDescuentosTrabajador").is(":visible")) {
                        $(this).html('<i class="fas fa-exchange-alt"></i> Patronal');
                    } else {
                        $(this).html('<i class="fas fa-exchange-alt"></i> Trabajador');
                    }
                });
            });
        </script>
<?php 
    } // else existeSalario == 0
?>