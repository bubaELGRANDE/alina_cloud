<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$arrayFormData = explode("^", $_POST["arrayFormData"]);
$dataExpediente = null;

if ($arrayFormData[0] == "modify") {
    $dataExpediente = $cloud->row("SELECT 
        prsCargoId,
        sucursalDepartamentoId,
        tipoContrato,
        fechaInicio,
        fechaFinalizacion,
        justificacionEstado,
        estadoExpediente,
        tipoVacacion,
        estadoVacacion
    FROM th_expediente_personas
    WHERE prsExpedienteId = ?  AND flgDelete = ?", [$arrayFormData[2], 0]);

    if (!$dataExpediente) {
        $dataExpediente = null;
    }
}

?>
<input type="hidden" id="flgInsertCargo" name="flgInsertCargo" value="0">
<input type="hidden" id="flgInsertRama" name="flgInsertRama" value="0">
<div class="row">
    <div class="col-md-12">
        <div class="form-select-control mb-4">
            <?php
            if ($arrayFormData[0] == "modify") {
                echo '<input type="hidden" id="typeOperation" name="typeOperation" value="update">';
                echo '<input type="hidden" name="prsExpedienteId" value="' . $arrayFormData[2] . '">';
            } else {
                echo ' <input type="hidden" id="typeOperation" name="typeOperation" value="insert">';
            }
            ?>
            <?php
            if ($arrayFormData[0] == "edit" || $arrayFormData[0] == "modify") {
                $dataPersonas = $cloud->row("
                         SELECT
                             personaId, 
                             CONCAT(
                                 IFNULL(apellido1, '-'),
                                 ' ',
                                 IFNULL(apellido2, '-'),
                                 ', ',
                                 IFNULL(nombre1, '-'),
                                 ' ',
                                 IFNULL(nombre2, '-')
                             ) AS nombreCompleto
                         FROM th_personas
                         WHERE prsTipoId = '1' AND flgDelete = '0' AND estadoPersona = 'Activo' AND personaId = ?
                    ", [$arrayFormData[1]]);
                ?>

                <input type="hidden" id="operation" name="operation" value="empleado-expediente">
                <input type="hidden" id="persona" name="persona" value="<?php echo $dataPersonas->personaId; ?>">
                <input type="hidden" id="expedienteId" name="expedienteId" value="<?php echo $arrayFormData[2]; ?>">
                <input type="hidden" id="accionExpediente" name="accionExpediente" value="<?php echo $arrayFormData[0]; ?>">
                <?php
            } else { ?>

                <input type="hidden" id="operation" name="operation" value="empleado-expediente">
                <select id="persona" name="persona" style="width:100%;" required>
                    <option></option>
                    <?php
                    $dataPersonas = $cloud->rows("
                                SELECT
                                    personaId, 
                                    CONCAT(
                                        IFNULL(apellido1, '-'),
                                        ' ',
                                        IFNULL(apellido2, '-'),
                                        ', ',
                                        IFNULL(nombre1, '-'),
                                        ' ',
                                        IFNULL(nombre2, '-')
                                    ) AS nombreCompleto
                                FROM th_personas
                                WHERE prsTipoId = '1' AND flgDelete = '0' AND estadoPersona = 'Activo' AND personaId NOT IN (
                                    SELECT
                                        personaId
                                    FROM th_expediente_personas
                                    WHERE estadoExpediente = 'Activo' AND flgDelete = '0'
                                )
                                ORDER BY apellido1, apellido2, nombre1, nombre2
                            ");
                    foreach ($dataPersonas as $dataPersonas) {
                        echo '<option value="' . $dataPersonas->personaId . '">' . $dataPersonas->nombreCompleto . '</option>';
                    }
                    ?>
                </select>
            <?php } ?>
        </div>
    </div>
</div>

<?php if ($arrayFormData[0] != "modify") { ?>

    <div class="row">
        <div id="divSelectCargo" class="col-md-4 form-select-control mb-4">
            <select id="cargo" name="cargo" style="width:100%;" required>
                <option></option>
                <?php
                $dataCargos = $cloud->rows("SELECT prsCargoId,cargoPersona FROM cat_personas_cargos WHERE flgDelete = '0'");
                foreach ($dataCargos as $dataCargo) {
                    $selected = '';
                    if ($dataExpediente && $dataExpediente->prsCargoId == $dataCargo->prsCargoId) {
                        $selected = 'selected';
                    }
                    echo '<option value="' . $dataCargo->prsCargoId . '" ' . $selected . '>' . $dataCargo->cargoPersona . '</option>';
                }
                ?>
            </select>
            <div class="form-helper text-end">
                <span class="badge rounded-pill bg-primary" style="cursor: pointer;" onclick="showHideCargo(1);">
                    <i class="fas fa-plus-circle"></i> Nuevo
                </span>
            </div>
        </div>
        <div id="divNuevoCargo" class="col-md-4">
            <div class="form-outline form-hidden-update mb-4">
                <i class="fas fa-briefcase trailing"></i>
                <input type="text" id="cargoPersona" class="form-control" name="cargoPersona" required />
                <label id="labelNombreEnfermedad" class="form-label" for="cargoPersona">Nombre del cargo</label>
                <div class="form-helper text-end">
                    <span class="badge rounded-pill bg-secondary" style="cursor: pointer;" onclick="showHideCargo(0);">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4 form-select-control mb-4">
            <select id="sucursal" name="sucursal" style="width:100%;" required>
                <option></option>
                <?php
                $dataSucursales = $cloud->rows("SELECT sucursalId,sucursal FROM cat_sucursales WHERE flgDelete = '0'");
                foreach ($dataSucursales as $sucursal) {
                    echo '<option value="' . $sucursal->sucursalId . '">' . $sucursal->sucursal . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-4 form-select-control mb-4">
            <select id="departamentoSuc" name="departamentoSuc" style="width:100%;" required>
                <option></option>
            </select>
        </div>
    </div>
    <div id="divNuevoCargoDescripcion" class="row">
        <div class="col-6">
            <div class="form-outline form-hidden-update mb-4 mt-4">
                <i class="fas fa-edit trailing"></i>
                <textarea type="text" id="descripcionCargoPersona" class="form-control" name="descripcionCargoPersona"
                    required></textarea>
                <label class="form-label" for="descripcionCargoPersona">Objetivo del Cargo</label>
                <div class="form-helper text-end">
                    De uso institucional
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="form-outline form-hidden-update mb-4 mt-4">
                <i class="fas fa-edit trailing"></i>
                <textarea type="text" id="funcionCargoPersona" class="form-control" name="funcionCargoPersona"
                    required></textarea>
                <label class="form-label" for="funcionCargoPersona">Funciones del Cargo</label>
                <div class="form-helper text-end">
                    De uso para contrato
                </div>
            </div>
        </div>
    </div>
    <hr>

<?php } ?>
<div class="row">
    <div class="col-md-12 form-select-control mb-4">
        <select id="tipoContrato" name="tipoContrato" style="width:100%;" required>
            <option></option>
            <?php
            $arrayContratos = array("Tiempo indefinido", "Periodo determinado", "Servicios profesionales", "Periodo de prueba", "Interinato");
            foreach ($arrayContratos as $contrato) {
                $selected = '';
                if ($dataExpediente && $dataExpediente->tipoContrato == $contrato) {
                    $selected = 'selected';
                }
                echo '<option value="' . $contrato . '" ' . $selected . '>' . $contrato . '</option>';
            }
            ?>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-outline mb-4 input-daterange">
            <input type="date" id="fechaContratacion" class="form-control" name="fechaContratacion" required
                value="<?= $dataExpediente && $dataExpediente->fechaInicio ? date('Y-m-d', strtotime($dataExpediente->fechaInicio)) : '' ?>" />
            <label class="form-label" for="fechaContratacion"> Fecha de inicio </label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline mb-4 input-daterange">
            <input type="date" id="fechaFinalizacion" class="form-control" name="fechaFinalizacion" required
                value="<?= $dataExpediente && $dataExpediente->fechaFinalizacion ? $dataExpediente->fechaFinalizacion : '' ?>" />
            <label class="form-label" for="fechaFinalizacion"> Fecha de finalización </label>
        </div>
    </div>
</div>
<?php if ($arrayFormData[0] != "modify") { ?>
    <hr>
    <div class="mb-4 d-flex justify-content-between">
        Horarios de trabajo
        <span class="badge rounded-pill bg-primary" style="cursor: pointer;" onclick="nuevoHorario();">
            <i class="far fa-clock"></i> Agregar horario
        </span>
    </div>
    <div id="horarios">
        <div class="row fila-1">
            <div class="col-md-3 form-select-control">
                <select class="diaInicio" id="diaInicio-1" name="diaInicio[]" style="width:100%;" onchange="checkDias(1);"
                    required>
                    <option></option>
                    <?php
                    $diasSemana = array("Lunes", "Martes", "Miercóles", "Jueves", "Viernes", "Sábado", "Domingo");
                    foreach ($diasSemana as $dias) {
                        echo '<option value="' . $dias . '">' . $dias . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 form-select-control">
                <select class="diaFin" id="diaFin-1" name="diaFin[]" style="width:100%;" onchange="checkDias(1);" required>
                    <option></option>
                    <?php
                    foreach ($diasSemana as $dias) {
                        echo '<option value="' . $dias . '">' . $dias . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-outline">
                    <input id="horaInicio-1" class="form-control horaInicio" type="time" name="horaInicio[]"
                        onchange="calculate(1);" required>
                    <label class="form-label" for="horaInicio">Hora de inicio</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-outline mb-2">
                    <input id="horaFin-1" class="form-control horaFin" type="time" name="horaFin[]" onchange="calculate(1);"
                        required>
                    <label class="form-label" for="horaFin">Hora de finalización</label>
                    <input class="totalH" type="hidden" id="totalH-1" name="totalH[]" value="0">
                </div>
                <span class="badge rounded-pill bg-danger" style="cursor: pointer;" onclick="delHorario(1);"><i
                        class="fas fa-times-circle"></i> Eliminar horario</span>
            </div>
        </div>
    </div>
    <div class="form-outline mt-4">
        <i class="fas fa-clock trailing"></i>
        <input type="number" id="horasSemanales" name="horasSemanales" class="form-control" readonly>
        <label class="form-label" for="horasSemanales">Horas semanales</label>
    </div>
<?php } ?>
<!-- <hr>
<div class="mb-4 d-flex justify-content-between">
    Salario
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-dollar-sign trailing"></i>
            <input type="number" id="salario" name="salario" class="form-control" step="0.01" min="0.01" required />
            <label class="form-label" for="salario">Salario</label>
        </div>
    </div>  
    <div class="col-md-6 form-select-control">
        <select id="tipoRemuneracion" name="tipoRemuneracion" style="width:100%;" required>
            <option></option>
            <?php
            /*
            $dataTipoRemuneracion = $cloud->rows("
                SELECT
                    salarioTipoRemuneracionId, 
                    tipoRemuneracion 
                FROM cat_salarios_tipo_remuneracion
                WHERE flgDelete = '0'
            ");
            foreach ($dataTipoRemuneracion as $TipoRemuneracion) {
                echo '<option value="'.$TipoRemuneracion->salarioTipoRemuneracionId.'">'.$TipoRemuneracion->tipoRemuneracion .'</option>';
            }
            */
            ?>
        </select>
    </div>
</div> -->
<hr>
<div class="row">
    <div class="col-md-6 form-select-control">
        <span class="mb-2 d-block">Vacaciones</span>
        <select id="tipoVacacion" name="tipoVacacion" style="width:100%;">
            <option></option>
            <option value="Colectivas" <?= ($dataExpediente && $dataExpediente->tipoVacacion == 'Colectivas') ? 'selected' : '' ?>>Colectivas</option>
            <option value="Individuales" <?= ($dataExpediente && $dataExpediente->tipoVacacion == 'Individuales') ? 'selected' : '' ?>>Individuales</option>
        </select>
    </div>
</div>
<?php
if ($arrayFormData[0] == "edit") {
    ?>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <div class="form-outline">
                <i class="fas fa-edit trailing"></i>
                <textarea type="text" id="justificacion" class="form-control" name="justificacion" required></textarea>
                <label class="form-label" for="justificacion">Justificación</label>
            </div>
        </div>
    </div>
    <?php
} else {
    // Nuevo expediente
}
?>

<script>
    function showHideCargo(tipo) {
        if (tipo == 1) {
            $("#divNuevoCargo").show();
            $("#divNuevoCargoDescripcion").show();
            $("#divSelectCargo").hide();
            $("#flgInsertCargo").val(1);
            document.querySelectorAll('.form-hidden-update').forEach((formOutline) => {
                new mdb.Input(formOutline).update();
            });
        } else {
            $("#divNuevoCargo").hide();
            $("#divNuevoCargoDescripcion").hide();
            $("#divSelectCargo").show();
            $("#flgInsertCargo").val(0);
            // reset inputs
            $("#cargoPersona").val('');
            $("#descripcionCargoPersona").val('');
        }
    }

    var x = 1, conteoFilas = 1;
    function nuevoHorario() {
        if ($(`#diaInicio-${x}`).val() != "" && $(`#diaFin-${x}`).val() != "" && $(`#horaInicio-${x}`).val() != "" && $(`#horaFin-${x}`).val() != "") {
            x++, conteoFilas++;
            $("#horarios").append(`
                <div class="row fila-`+ x + ` mt-4">
                <div class="col-md-3 form-select-control">
                    <select class="diaInicio" id="diaInicio-`+ x + `" name="diaInicio[]" style="width:100%;" onchange="checkDias(` + x + `);" required>
                        <option></option>
                        <?php
                        $diasSemana = array("Lunes", "Martes", "Miercóles", "Jueves", "Viernes", "Sábado", "Domingo");

                        foreach ($diasSemana as $dias) {
                            echo '<option value="' . $dias . '">' . $dias . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 form-select-control">
                    <select class="diaFin" id="diaFin-`+ x + `" name="diaFin[]" style="width:100%;" onchange="checkDias(` + x + `);" required>
                        <option></option>
                        <?php
                        foreach ($diasSemana as $dias) {
                            echo '<option value="' . $dias . '">' . $dias . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-outline">
                        <input id="horaInicio-`+ x + `" class="form-control horaInicio" type="time" name="horaInicio[]" onchange="calculate(` + x + `);">
                        <label class="form-label" for="horaInicio">Hora de inicio</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-outline mb-2">
                        <input id="horaFin-`+ x + `" class="form-control horaFin" type="time" name="horaFin[]" onchange="calculate(` + x + `);" required>
                        <label class="form-label" for="horaFin">Hora de finalización</label>
                        <input class="totalH" type="hidden" id="totalH-`+ x + `" name="totalH[]" value="0">
                    </div>
                    <span class="badge rounded-pill bg-danger" style="cursor: pointer;" onclick="delHorario(`+ x + `);" required><i class="fas fa-times-circle"></i> Eliminar horario</span>
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
        if (conteoFilas == 1) {
            mensaje("Aviso:", "Debe agregar al menos un horario", "warning");
        } else {
            id = '.fila-' + numero;
            $(id).remove();

            sumarHoras();
            conteoFilas--;
        }
    }
    function sumarHoras() {
        var sum = 0;
        $('.totalH').each(function () {
            sum += parseInt(this.value);
        });

        $("#horasSemanales").val(sum);
    }
    function calculate(x) {
        var indexInicio = $("#diaInicio-" + x).prop('selectedIndex');
        var indexFin = $("#diaFin-" + x).prop('selectedIndex');

        if (indexInicio == indexFin) {
            var multiplicador = 1;
        } else {
            var multiplicador = indexFin - indexInicio + 1;
        }

        var hours = 0;

        if ($(`#horaFin-${x}`).val() != "" && $(`#horaInicio-${x}`).val() != "") {
            hours = parseInt($("#horaFin-" + x).val().split(':')[0], 10) - parseInt($("#horaInicio-" + x).val().split(':')[0], 10);
        } else {
            // evitar warning
        }

        if (hours < 0) hours = 24 + hours;
        var totalHours = isNaN(hours * multiplicador) ? 0 : hours * multiplicador;

        $("#totalH-" + x).val(totalHours);

        sumarHoras();

        document.querySelectorAll('.form-outline').forEach((formOutline) => {
            new mdb.Input(formOutline).init();
        });
    }

    function checkDias(x) {
        var indexInicio = $("#diaInicio-" + x).prop('selectedIndex');
        var indexFin = $("#diaFin-" + x).prop('selectedIndex');

        if (indexFin < indexInicio && (indexInicio > 0 && indexFin > 0)) {
            mensaje(
                "Alerta:",
                'El día de inicio no puede ser anterior al día final.',
                "warning"
            );
            $("#diaInicio-" + x).val(null).trigger('change');
            $("#diaFin-" + x).val(null).trigger('change');
        } else {
            calculate(x);
        }
    }

    function validarFechas() {
        var fechaInicio = $("#fechaContratacion").val();
        var fechaFin = $("#fechaFinalizacion").val();

        var fecha1 = moment(fechaInicio);
        var fecha2 = moment(fechaFin);

        if (fechaInicio == "" || fechaFin == "" || $("#tipoContrato").val() == "Tiempo indefinido") {
            // No validar nada de momento
        } else {
            if (fecha2.diff(fecha1, 'days') < 0) {
                mensaje(
                    "AVISO",
                    "La fecha de inicio debe ser menor que la fecha de finalización.",
                    "warning"
                );
                $("#fechaContratacion").val('');
                $("#fechaFinalizacion").val('');
            } else {
                // La fecha de finalizacion es mayor, dejar pasar validación
            }
        }
    }

    $(document).ready(function () {
        //Maska.create('#frmModal .masked');

        $("#divNuevoCargo").hide();
        $("#divNuevoCargoDescripcion").hide();
        <?php
        if ($arrayFormData[0] == "nuevo") {
            ?>
            $("#persona").select2({
                placeholder: "Empleado",
                dropdownParent: $('#modal-container'),
                allowClear: true
            });
        <?php } ?>
        $("#cargo").select2({
            placeholder: "Cargo",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#sucursal").select2({
            placeholder: "Sucursal",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#departamentoSuc").select2({
            placeholder: "Departamento",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#tipoContrato").select2({
            placeholder: "Tipo de contrato",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
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
        $("#tipoRemuneracion").select2({
            placeholder: "Tipo de remuneracion",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#tipoVacacion").select2({
            placeholder: "Tipo de vacación",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $('#fechaContratacion, #fechaFinalizacion').on('change', function () {
            $(this).addClass("active");
        });

        /* $('.input-daterange').datepicker({
             format: 'yyyy-mm-dd',
             autoclose: true,
             calendarWeeks: false,
             clearBtn: true,
             disableTouchKeyboard: true,
             todayHighlight: true
         });*/

        $("#tipoContrato").change(function () {
            var contrato = $("#tipoContrato").val();
            if (contrato == "Tiempo indefinido") {
                $("#fechaFinalizacion").prop("disabled", true);
                $("#fechaFinalizacion").val(null);
            } else { // Solicitar fecha de finalización al resto de contratos
                $("#fechaFinalizacion").prop("disabled", false);
                $("#fechaFinalizacion").val(null);
            }
        });

        $("#sucursal").change(function () {
            var sucursal = $("#sucursal").val();
            $.ajax({
                url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarDepto",
                type: "POST",
                dataType: "json",
                data: { sucursal: sucursal }
            }).done(function (data) {
                //$("#municipio").html(data);
                var cant = data.length;
                $("#departamentoSuc").empty();
                $("#departamentoSuc").append("<option></option>");
                for (var i = 0; i < cant; i++) {
                    var id = data[i]['id'];
                    var depto = data[i]['departamento'];

                    $("#departamentoSuc").append("<option value='" + id + "'>" + depto + "</option>");
                }

            });
        });

        $("#frmModal").validate({
            messages: {
                salario: {
                    step: "El salario debe incluir solo dos decimales"
                }
            },
            submitHandler: function (form) {
                // Esta validación es porque los input generados con append no son tomados en cuenta en la librería
                if ($(`#diaInicio-${x}`).val() != "" && $(`#diaFin-${x}`).val() != "" && $(`#horaInicio-${x}`).val() != "" && $(`#horaFin-${x}`).val() != "") {
                    button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                    asyncDoDataReturn(
                        "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                        $("#frmModal").serialize(),
                        function (data) {
                            button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                            if (data == "success") {
                                mensaje(
                                    "Operación completada:",
                                    'Se guardó con éxito el expediente',
                                    "success"
                                );
                                <?php
                                if ($arrayFormData[0] == "nuevo" || $arrayFormData[0] == "modify") {
                                    ?>
                                    $("#tblExpedientesActivos").DataTable().ajax.reload(null, false);
                                    $("#tblExpedientesPendientes").DataTable().ajax.reload(null, false);

                                    <?php
                                } else { // Actualizar expediente
                                    ?>
                                    changePage(`<?php echo $_SESSION["currentRoute"]; ?>`, `expediente-empleado`, `personaId=<?php echo $dataPersonas->personaId; ?>&nombreCompleto=<?php echo $dataPersonas->nombreCompleto; ?>&estadoExpediente=Activo`);
                                    <?php
                                }

                                ?>
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

        <?php
        switch ($arrayFormData[0]) {
            case 'modify':
                echo " $('#modalTitle').html('Editar Expediente: {$dataPersonas->nombreCompleto}');";
                break;

            case 'edit':
                echo " $('#modalTitle').html('Actualizar Expediente: {$dataPersonas->nombreCompleto}');";
                break;

            default:
                echo " $('#modalTitle').html('Nuevo Expediente');";
                break;
        }
        ?>

    });
</script>