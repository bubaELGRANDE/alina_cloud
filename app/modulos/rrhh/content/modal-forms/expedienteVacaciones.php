<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $arrayFormData = explode("^", $_POST["arrayFormData"]);
    $txtSuccess = "Vacación del empleado registrada con éxito";

    if ($arrayFormData[0]=="update") {
        $dataVaca = $cloud->row("
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
            vaca.fhSolicitud,
            vaca.periodoVacaciones,
            vaca.numDias,
            vaca.fechaInicio,
            vaca.fechaFin,
            vaca.fhaprobacion,
            vaca.expedienteJefeId,
            vaca.estadoSolicitud
            FROM ((th_expedientes_vacaciones vaca
            JOIN th_expediente_personas exp ON vaca.expedienteId = exp.prsExpedienteId)
            JOIN th_personas per ON per.personaId = exp.personaId)
            WHERE vaca.flgDelete = 0 AND vaca.expedienteVacacionesId = ?
        ", [$arrayFormData[1]]);
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $arrayFormData[0]; ?>">
<input type="hidden" id="operation" name="operation" value="vacaciones">
<?php if ($arrayFormData[0]=="update") { ?>
<input type="hidden" id="expedienteVacaId" name="expedienteVacaId" value="<?php echo $dataVaca->expedienteVacacionesId; ?>">
<input type="hidden" id="expId" name="expId" value="">
<?php } ?>
<div class="row">
    <div class="col-md-8">
        <div class="form-select-control mb-4">
            <select class="expedienteId" id="expedienteId" name="expedienteId" style="width:100%;" required>
                <option></option>
                <?php // falta join con expediente
                    $dataPersonas = $cloud->rows("
                    SELECT
                    pers.personaId as personaId, 
                    exp.prsExpedienteId as expedienteId,
                    CONCAT(
                        IFNULL(pers.apellido1, '-'),
                        ' ',
                        IFNULL(pers.apellido2, '-'),
                        ', ',
                        IFNULL(pers.nombre1, '-'),
                        ' ',
                        IFNULL(pers.nombre2, '-')
                    ) AS nombreCompleto
                    FROM th_personas pers
                    JOIN th_expediente_personas exp ON pers.personaId = exp.personaId
                    WHERE pers.prsTipoId = '1' AND pers.estadoPersona = 'Activo' AND exp.estadoExpediente = 'Activo' AND pers.flgDelete = '0' AND exp.flgDelete = '0' AND exp.tipoVacacion = 'Individuales'  AND pers.fechaInicioLabores <= DATE_SUB(NOW(),INTERVAL 1 YEAR) AND exp.estadoVacacion = ?
                    ORDER BY apellido1, apellido2, nombre1, nombre2
                    ", ['Activo']);
                    foreach ($dataPersonas as $dataPersonas) {
                        $contarDiasVaca = $cloud->row("
                            SELECT SUM(diasRestantesVacacion) as totalDias 
                            FROM ctrl_persona_vacaciones 
                            WHERE personaId = ? AND flgDelete = ?
                        ", [$dataPersonas->personaId, '0']);
                        if(is_null($contarDiasVaca->totalDias)){
                            $totalDias = 0;
                        }else{
                            $totalDias = $contarDiasVaca->totalDias;
                        }

                        echo '<option value="'.$dataPersonas->expedienteId.'">'.$dataPersonas->nombreCompleto.' ('.$totalDias.' días disponibles)</option>';
                    }
                ?>
            </select>

        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-calendar trailing"></i>
            <input type="text" id="fechaSolicitud" class="form-control masked fecha" name="fechaSolicitud" data-mask="##-##-####" minlength="10" required />
            <label class="form-label" for="fechaSolicitud">Fecha de solicitud</label>
        </div>
    </div>
</div>
<div class="row align-items-center">
    <div class="col-md-8 text-center mb-4">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="tipoVaca" id="consecutivos" value="Consecutivos" checked>
            <label class="form-check-label" for="consecutivos">15 días consecutivos</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="tipoVaca" id="proporcionales" value="Proporcionales">
            <label class="form-check-label" for="proporcionales">Proporcionales</label>
        </div>
    </div>
    <div class="col-md-4">
        <div id="numDiasDiv" class="form-outline mb-4" style="display:none;">
            <i class="fas fa-calendar trailing"></i>
            <input type="number" id="numDias" class="form-control" name="numDias" maxlength="10" data-mask="##" required onChange="sumarDias();">
            <label class="form-label" for="numDias">Número de días</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-calendar trailing"></i>
            <input type="text" id="fechaIni" class="form-control masked fecha" name="fechaIni" data-mask="##-##-####" minlength="10" required onChange="sumarDias();">
            <label class="form-label" for="fechaIni">Fecha de inicio</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-calendar trailing"></i>
            <input type="text" id="fechaFin" class="form-control" name="fechaFin" readonly/>
            <label class="form-label" for="fechaFin">Fecha de finalización</label>
        </div>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select id="autorizadoPor" name="autorizadoPor" style="width:100%;" required>
                <option></option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-calendar trailing"></i>
            <input type="text" id="fechaApro" class="form-control masked fecha" name="fechaApro" data-mask="##-##-####" minlength="10" required />
            <label class="form-label" for="fechaApro">Fecha de aprobación</label>
        </div>
    </div>
</div>
<script>
    function sumarDias() {
        var diaIni = $("#fechaIni").val();

        if (diaIni !== ""){

            var fecha1 = moment(diaIni);

            if ($('#proporcionales').is(':checked')){
                var numeroDias = $("#numDias").val();
            } else {
                var numeroDias = Number(15);
            }
            // Internamente se resta uno para reflejar la fecha correcta
            numeroDias -= 1;

            var fecha2 = moment(diaIni).add(numeroDias, 'days').format('YYYY-MM-DD');
                                      
            $("#fechaFin").val(fecha2);
            $("#fechaFin").addClass("active");
        }
    }

    $(document).ready(function() {
        $("#expedienteId").select2({
            placeholder: "Empleado",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $("#autorizadoPor").select2({
            placeholder: "Autorizado por",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $('.masked').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            calendarWeeks : false,
            clearBtn: true,
            disableTouchKeyboard: true,
            todayHighlight: true
        });

        $('.masked').on('change', function() { 
            $(this).addClass("active"); 
        });

        $('#consecutivos').on('change', function() { 
            $("#numDiasDiv").toggle(); 
            $("#fechaIni, #fechaFin, #numDias").val("");
            $("#fechaIni, #fechaFin, #numDias").removeClass("active");
        });

        $('#proporcionales').on('change', function() { 
            $("#numDiasDiv").toggle(); 
            $("#fechaIni, #fechaFin, #numDias").val("");
            $("#fechaIni, #fechaFin, #numDias").removeClass("active");
        });

        $("#expedienteId").change(function(e) {
            $.ajax({
                url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarJefaturasEmpleado",
                type: "POST",
                dataType: "json",
                data: {expedienteId: $("#expedienteId").val()}
            }).done(function(data) {
                $("#autorizadoPor").empty();
                // Se selecciona el primer registro del append automáticamente, así que "simular" el efecto que no ha pasado nada
                // El disabled es para que el validate lo condicione siempre
                //$("#autorizadoPor").append("<option value='0' selected disabled>Autorizado por</option>");
                for(let i = 0; i < data.length; i++) {
                    if(data[i]['id'] === "0") { 
                        // No se encontraron jefes, el disabled permitirá validar el form
                        $("#autorizadoPor").append(`<option value="${data[i]['id']}" disabled>${data[i]['nombreJefe']}</option>`);
                    } else {
                        $("#autorizadoPor").append(`<option value="${data[i]['id']}">${data[i]['nombreJefe']}</option>`);
                    }
                }
                <?php 
                    // Validacion con php para prevenir error que no existe variable, sino se cumple simplemente no existe este script
                    if($arrayFormData[0] == "editar") {
                ?>
                        // asignar el .val con personaId de php para activar el evento
                        //$("#persona").val('').trigger('change');
                <?php 
                    } else {
                    }
                ?>
            });
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                '<?php echo $txtSuccess; ?>',
                                "success"
                            );
                            $('#tblVacacion').DataTable().ajax.reload(null, false);
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
            }
        });

        <?php if ($arrayFormData[0]=="update") { ?>
            $("#modalTitle").html('Editar solicitud de vacaciones');
            $("#expedienteId").val('<?php echo $dataVaca->expedienteId; ?>').trigger('change').attr("disabled","disabled");
            $("#expId").val('<?php echo $dataVaca->expedienteId; ?>').trigger('change');
            $("#autorizadoPor").val('<?php echo $dataVaca->expedienteJefeId; ?>').trigger('change');
            $("#fechaSolicitud").val('<?php echo $dataVaca->fhSolicitud; ?>');
            $("#fechaIni").val('<?php echo $dataVaca->fechaInicio; ?>');
            $("#fechaFin").val('<?php echo $dataVaca->fechaFin; ?>');
            $("#fechaApro").val('<?php echo $dataVaca->fhaprobacion; ?>');
            if (`<?php echo $dataVaca->periodoVacaciones;?>`==`Proporcionales`) {
                $("#proporcionales").prop("checked", true).trigger("click");
                $("#numDiasDiv").toggle(); 
                $("#numDias").val('<?php echo $dataVaca->numDias; ?>');
            } else {
                $("#consecutivos").prop("checked", true).trigger("click");
            }

        <?php } ?>
    });
</script>