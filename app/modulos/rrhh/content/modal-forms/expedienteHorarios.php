<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
	// arrayFormData = prsExpedienteId
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    $dataHorarios = $cloud->rows("
        SELECT
        expedienteHorarioId, 
        prsExpedienteId, 
        diaInicio, 
        diaFin, 
        TIME_FORMAT(horaInicio, '%H:%i') as horaInicio, 
        TIME_FORMAT(horaFin, '%H:%i') as horaFin,
        horasLaborales
        FROM th_expediente_horarios
        WHERE prsExpedienteId = ? AND flgDelete = '0'
    ",[$arrayFormData[0]]);

    $dataExpediente = $cloud->row("
        SELECT 
            exp.prsExpedienteId as prsExpedienteId, 
            exp.personaId as personaId,
            exp.sucursalDepartamentoId as sucursalDepartamentoId, 
            exp.tipoContrato as tipoContrato, 
            exp.fechaInicio as fechaInicio, 
            exp.fechaFinalizacion as fechaFinalizacion, 
            exp.estadoExpediente as estadoExpediente,
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
            dep.departamentoSucursal as departamentoSucursal,
            dep.sucursalId as sucursalId,
            sal.tipoSalario as tipoSalario,
            sal.salario as salario
        FROM th_expediente_personas exp
        LEFT JOIN th_personas per ON per.personaId = exp.personaId
        LEFT JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
        LEFT JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
        LEFT JOIN th_expediente_salarios sal ON sal.prsExpedienteId = exp.prsExpedienteId
        WHERE exp.prsExpedienteId = ?
    ", [$arrayFormData[0]]);
?>

<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="horariosTrabajo">
<input type="hidden" id="personaId" name="personaId" value="<?php echo $arrayFormData[2]; ?>">
<input type="hidden" id="expedienteId" name="expedienteId" value="<?php echo $arrayFormData[0]; ?>">

<div class="mb-4 d-flex justify-content-between">
    <span class="badge rounded-pill bg-primary" style="cursor: pointer;" onclick="nuevoHorario();"><i class="far fa-clock"></i> Agregar horario</span>
</div>
<div id="horarios">

<?php 
$i = 1;
$totalHoras = 0;
foreach ($dataHorarios as $horarios) { ?>
    <div class="row fila-<?php echo $i; ?>">
        <div class="col-md-3 form-select-control">
            <select class="diaInicio" id="diaInicio-<?php echo $i; ?>" name="diaInicio[]" style="width:100%;" onchange="checkDias(<?php echo $i; ?>);" required>
                <option></option>
                <?php 
                    $diasSemana = array("Lunes", "Martes", "Miercóles", "Jueves", "Viernes", "Sábado", "Domingo");
                    
                    foreach ($diasSemana as $dias){
                        if ($dias == $horarios->diaInicio){
                            echo '<option value="'.$dias.'" selected>'.$dias.'</option>';
                        }else{
                            echo '<option value="'.$dias.'" >'.$dias.'</option>';
                        }
                    }
                ?>
            </select>
        </div>
        <div class="col-md-3 form-select-control">
            <select class="diaFin" id="diaFin-<?php echo $i; ?>" name="diaFin[]" style="width:100%;" onchange="checkDias(<?php echo $i; ?>);" required>
                <option></option>
                <?php                 
                    foreach ($diasSemana as $dias){
                        if ($dias == $horarios->diaFin){
                        echo '<option value="'.$dias.'" selected>'.$dias.'</option>';
                        }else{
                            echo '<option value="'.$dias.'">'.$dias.'</option>';
                        }
                    }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <div class="form-outline">
                <input id="horaInicio-<?php echo $i; ?>" class="form-control horaInicio" type="time" name="horaInicio[]" onchange="calculate(<?php echo $i; ?>);" required value="<?php echo $horarios->horaInicio; ?>">
                <label class="form-label" for="horaInicio">Hora de inicio</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-outline mb-2">
                <input id="horaFin-<?php echo $i; ?>" class="form-control horaFin" type="time" name="horaFin[]" onchange="calculate(<?php echo $i; ?>);" required value="<?php echo $horarios->horaFin; ?>">
                <label class="form-label" for="horaFin">Hora de finalización</label>
                <input class="totalH" type="hidden" id="totalH-<?php echo $i; ?>" name="totalH[]" value="<?php echo $horarios->horasLaborales;?>">
                <input class="horarioId" type="hidden" id="horarioId-<?php echo $i; ?>" name="horarioId[]" value="<?php echo $horarios->expedienteHorarioId;?>">
            </div>
            <span class="badge rounded-pill bg-danger mb-4" style="cursor: pointer;" onclick="eliminarWrapper(<?php echo $horarios->expedienteHorarioId.','. $i; ?>);"><i class="fas fa-times-circle"></i> eliminar horario</span>
        </div>
    </div>
    <?php 
        $totalHoras += $horarios->horasLaborales;
        $i++; 
    } ?>
</div>
<div>
    <div class="form-outline mt-4">
        <i class="fas fa-clock trailing"></i>
        <input type="number" id="horasSemanales" name="horasSemanales" class="form-control" readonly value="<?php echo $totalHoras; ?>">
        <label class="form-label" for="salario">Horas semanales</label>
    </div>
</div>

<script>
    var x = <?php echo $i; ?>, conteoFilas = <?php echo $i; ?>;
    function nuevoHorario() {
        if($(`#diaInicio-${x}`).val() != "" && $(`#diaFin-${x}`).val() != "" && $(`#horaInicio-${x}`).val() != "" && $(`#horaFin-${x}`).val() != "") {
            x++, conteoFilas++;
            $("#horarios").append(`
                <div class="row fila-`+x+` mt-4">
                <div class="col-md-3 form-select-control">
                    <select class="diaInicio" id="diaInicio-`+x+`" name="diaInicio[]" style="width:100%;" onchange="checkDias(`+x+`);" required>
                        <option></option>
                        <?php 
                            $diasSemana = array("Lunes", "Martes", "Miercóles", "Jueves", "Viernes", "Sábado", "Domingo");
                            
                            foreach ($diasSemana as $dias){
                                echo '<option value="'.$dias.'">'.$dias.'</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 form-select-control">
                    <select class="diaFin" id="diaFin-`+x+`" name="diaFin[]" style="width:100%;" onchange="checkDias(`+x+`);" required>
                        <option></option>
                        <?php                 
                            foreach ($diasSemana as $dias){
                                echo '<option value="'.$dias.'">'.$dias.'</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-outline">
                        <input id="horaInicio-`+x+`" class="form-control horaInicio" type="time" name="horaInicio[]" onchange="calculate(`+x+`);">
                        <label class="form-label" for="horaInicio">Hora de inicio</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-outline mb-2">
                        <input id="horaFin-`+x+`" class="form-control horaFin" type="time" name="horaFin[]" onchange="calculate(`+x+`);" required>
                        <label class="form-label" for="horaFin">Hora de finalización</label>
                        <input class="totalH" type="hidden" id="totalH-`+x+`" name="totalH[]" value="0">
                    </div>
                    <span class="badge rounded-pill bg-danger" style="cursor: pointer;" onclick="delHorario(`+x+`);" required><i class="fas fa-times-circle"></i> eliminar horario</span>
                </div>
            </div>
            `);
            $(".diaInicio").select2({
                placeholder: "Día de inicio",
                dropdownParent: $('#modal-container'),
                allowClear: true
            });
            $(".diaFin").select2({
                placeholder: "Día de finalización",
                dropdownParent: $('#modal-container'),
                allowClear: true
            });
            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                new mdb.Input(formOutline).init();
            });
        } else {
            mensaje("Aviso:", "Debe completar el horario anterior", "warning");
        }
    }   

    function delHorario(numero) {
        if(conteoFilas == 1) {
            mensaje("Aviso:", "Debe agregar al menos un horario", "warning");
        } else {
            id = '.fila-'+numero;
            $(id).remove();

            sumarHoras();
            conteoFilas--;
        }
    }
    function sumarHoras() {
        var sum = 0
        $('.totalH').each(function() {
            sum += parseInt(this.value);
        });

        $("#horasSemanales").val(sum);
    }
    function calculate(x) {

        var indexInicio = $("#diaInicio-"+x).prop('selectedIndex');
        var indexFin = $("#diaFin-"+x).prop('selectedIndex');

        if (indexInicio == indexFin) {
            var multiplicador = 1;
        } else {
            var multiplicador = indexFin - indexInicio + 1;
        }

        var hours = 0;

        if($(`#horaFin-${x}`).val() != "" && $(`#horaInicio-${x}`).val() != "") {
            hours = parseInt($("#horaFin-"+x).val().split(':')[0], 10) - parseInt($("#horaInicio-"+x).val().split(':')[0], 10);
        } else {
            // evitar warning
        }

        if(hours < 0) hours = 24 + hours;
        var totalHours = isNaN(hours * multiplicador) ? 0 : hours * multiplicador;

        $("#totalH-"+x).val(totalHours);

        sumarHoras();

        document.querySelectorAll('.form-outline').forEach((formOutline) => {
            new mdb.Input(formOutline).init();
        });
    }

    function checkDias(x) {
        var indexInicio = $("#diaInicio-"+x).prop('selectedIndex');
        var indexFin = $("#diaFin-"+x).prop('selectedIndex');

        if (indexFin < indexInicio && (indexInicio > 0 && indexFin > 0)) {
            mensaje(
                "Alerta:",
                'El día de inicio no puede ser anterior al día final.',
                "warning"
            );  
            $("#diaInicio-"+x).val(null).trigger('change');
            $("#diaFin-"+x).val(null).trigger('change');
        } else {
            calculate(x);
        }
    }

    function eliminarWrapper(id, correlativo) {
        if($("#horarios").val() == 1) { // Solo queda 1 condicion
            mensaje(
                "Aviso:",
                'Debe agregar al menos un horario.',
                "warning"
            );
        } else {
            mensaje_confirmacion(
                `Alerta`, 
                `¿Está seguro que desea eliminar este horario?`, 
                `warning`, 
                function(param) {
                    asyncDoDataReturn(
                        '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                        {
                            typeOperation: `delete`,
                            operation: `horariosTrabajo`,
                            id: id,
                            personaId : <?php echo $dataExpediente->personaId; ?>
                        },
                        function(data) {
                            if(data == "success") {
                                mensaje_do_aceptar(`Operación completada:`, `Condición eliminada con éxito.`, `success`, function() {
                                    $(`.fila-${correlativo}`).remove();
                                    //$("#conteoWrappers").val(parseInt($("#conteoWrappers").val()) - 1);
                                    changePage(`<?php echo $_SESSION['currentRoute']; ?>`, `expediente-empleado`, `personaId=<?php echo $dataExpediente->personaId; ?>&nombreCompleto=<?php echo $dataExpediente->nombreCompleto; ?>&estadoExpediente=<?php echo $dataExpediente->estadoExpediente; ?>`);
                                    sumarHoras();
                                });
                            } else {
                                mensaje(
                                    "Aviso:",
                                    data,
                                    "warning"
                                );
                            }
                        }
                    );
                },
                `Eliminar`,
                `Cancelar`
            );
        }
    }

    $(document).ready(function() {
        sumarHoras();

        $(".diaInicio").select2({
            placeholder: "Día de inicio",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $(".diaFin").select2({
            placeholder: "Día de finalización",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                // Esta validación es porque los input generados con append no son tomados en cuenta en la librería
                if($(`#diaInicio-${x}`).val() != "" && $(`#diaFin-${x}`).val() != "" && $(`#horaInicio-${x}`).val() != "" && $(`#horaFin-${x}`).val() != "") {
                    button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                    asyncDoDataReturn(
                        "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                        $("#frmModal").serialize(),
                        function(data) {
                            button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                            if(data == "success") {
                                mensaje(
                                    "Operación completada:",
                                    'Se actualizó con éxito el horario',
                                    "success"
                                );                      
                                changePage(`<?php echo $_SESSION['currentRoute']; ?>`, `expediente-empleado`, `personaId=<?php echo $dataExpediente->personaId; ?>&nombreCompleto=<?php echo $dataExpediente->nombreCompleto; ?>&estadoExpediente=<?php echo $dataExpediente->estadoExpediente; ?>`);
                                $('#modal-container').modal("hide");
                            } else {
                                mensaje(
                                    "Aviso:",
                                    data,
                                    "warning"
                                );
                            }
                        }
                    );
                } else {
                    mensaje("Aviso:", "Debe agregar al menos un horario", "warning");
                }
            }
        });

        $("#modalTitle").html("Horarios de trabajo del empleado: <?php echo $dataExpediente->nombreCompleto . ' (' . $dataExpediente->cargoPersona . ')'; ?> ");
    });
</script>