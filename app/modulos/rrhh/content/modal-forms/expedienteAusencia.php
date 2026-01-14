<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /* arrayFormData 
        Nuevo = nuevo
        Editar = editar ^ crudId 
    */
    $arrayFormData = explode("^",$_POST['arrayFormData']);
    if ($arrayFormData[0]=="editar") {
        $datSolicitud = $cloud->row("
            SELECT 
                expedienteAusenciaId,
                expedienteId,
                expedienteIdAutoriza,
                fechaAutorizacion,
                fhSolicitud,
                fechaAusencia,
                fechaFinAusencia,
                totalDias,
                horaAusenciaInicio,
                horaAusenciaFin,
                totalHoras,
                motivoAusencia,
                goceSueldo
            FROM th_expediente_ausencias
            WHERE expedienteAusenciaId = ?
        ",[$arrayFormData[1]]);

        $txtSuccess = "Solicitud editada con éxito.";
    }else{
        $txtSuccess = "Solicitud agregada con éxito";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation">
<input type="hidden" id="operation" name="operation" value="empleado-ausencia">
<input type="hidden" id="flgInsertCargo" name="flgInsertCargo" value="0">
<input type="hidden" id="expedienteAusenciaId" name="expedienteAusenciaId" value="0">
<input type="hidden" id="expedienteId" name="expedienteId" value="0">

<div class="row">
    <div class="col-md-8">
        <div class="form-select-control mb-4">
            <select class="persona" id="persona" name="persona" style="width:100%;" required>
                <option></option>
                <?php 
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
                    WHERE pers.prsTipoId = '1' AND pers.flgDelete = '0' AND pers.estadoPersona = 'Activo' AND exp.estadoExpediente = 'Activo' 
                    ORDER BY apellido1, apellido2, nombre1, nombre2
                    ");
                    foreach ($dataPersonas as $dataPersonas) {
                        echo '<option value="'.$dataPersonas->expedienteId.'">'.$dataPersonas->nombreCompleto.'</option>';
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
<div class="row">
    <div class="col-md-4">
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-calendar trailing"></i>
            <input type="text" id="fechaAu" class="form-control masked fecha" name="fechaAu" data-mask="##-##-####" minlength="10" required onChange="contarDias();" />
            <label class="form-label" for="fechaAu">Inicio de ausencia</label>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-calendar trailing"></i>
            <input type="text" id="fechaFinAu" class="form-control masked fecha" name="fechaFinAu" data-mask="##-##-####" minlength="10" required onChange="contarDias();" />
            <label class="form-label" for="fechaFinAu">Finalización de ausencia</label>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-calendar trailing"></i>
            <input type="number" id="totalDias" class="form-control" name="totalDias" required readonly />
            <label class="form-label" for="totalDias">Total de días</label>
        </div>
    </div>
    
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <input id="horaInicio" class="form-control horaInicio" type="time" name="horaInicio" onchange="contarDias();" required>
            <label class="form-label" for="horaInicio">Hora de inicio</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <input id="horaFin" class="form-control horaFin" type="time" name="horaFin" onchange="contarDias();" required>
            <label class="form-label" for="horaFin">Hora de finalización</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-calendar trailing"></i>
            <input type="text" id="totalHoras" class="form-control" name="totalHoras" readonly />
            <label class="form-label" for="totalHoras">Total de horas</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-align-justify trailing"></i>
            <textarea class="form-control" id="motivoAusencia" name="motivoAusencia" rows="3"></textarea>
            <label class="form-label" for="motivoAusencia">Motivo de ausencia</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col text-center mb-4">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="goceSueldo" id="inlineRadio1" value="Si" required>
            <label class="form-check-label" for="inlineRadio1">Con goce de sueldo</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="goceSueldo" id="inlineRadio2" value="No">
            <label class="form-check-label" for="inlineRadio2">Sin goce de sueldo</label>
        </div>
    </div>
</div>
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

    function contarDias(){
        let diaIni = $("#fechaAu").val();
        //$("#fechaFinAu").val(diaIni);
        let diaFin = $("#fechaFinAu").val();

        let fecha1 = moment(diaIni);
        let fecha2 = moment(diaFin);

        if (diaIni == "" || diaFin == "") {
            $("#totalDias").val(0);
            //$("#fechaFinAu").addClass("active"); 
            $("#totalDias").addClass("active");   
        }else if(fecha2.diff(fecha1,'days') < 0){
            mensaje(
                "AVISO",
                "La fecha inicial debe ser anterior a la final.",
                "warning"
            );
            $("#fechaAu").val('');
            $("#fechaFinAu").val('');
            $("#totalDias").val('0');
        }else{
            $("#totalDias").val(fecha2.diff(fecha1, 'days')+1);
            $("#fechaFinAu").addClass("active"); 
            $("#totalDias").addClass("active");
        }

        let horaIni = $("#horaInicio").val();
        let horaFin = $("#horaFin").val();

        let hora1   = horaIni;
        let hora2   = horaFin;

        let horas   = moment
            .duration(moment(hora2,"HH:mm")
            .diff(moment(hora1,"HH:mm"))
            ).asHours();

        if (horaIni == "" || horaFin == "") {
            $("#totalHoras").val(0);
            $("#totalHoras").addClass("active");
        }else if(horas < 0 ){
            mensaje(
                "AVISO",
                "La hora final debe ser mayor a la inicial.",
                "warning"
            );
            $("#horaInicio").val('');
            $("#horaFin").val('');
            $("#totalHoras").val(0);
        }else{
            $("#totalHoras").val(horas);
            $("#totalHoras").addClass("active");
        }
    }

    $(document).ready( () => {
        $("#persona").select2({
            placeholder: "Empleado",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#autorizadoPor").select2({
            placeholder: "Autorizado por:",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $('.fecha').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            calendarWeeks : false,
            clearBtn: true,
            disableTouchKeyboard: true,
            todayHighlight: true
        });

        $('.fecha').on('change', function() { 
            $(this).addClass("active"); 
        });

        $("#persona").change(function(e) {
            $.ajax({
                url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarJefaturasEmpleado",
                type: "POST",
                dataType: "json",
                data: {expedienteId: $("#persona").val()}
            }).done(function(data) {
                $("#autorizadoPor").empty();
                // Se selecciona el primer registro del append automáticamente, así que "simular" el efecto que no ha pasado nada
                // El disabled es para que el validate lo condicione siempre
                $("#autorizadoPor").append("<option value='0' selected disabled>Autorizado por</option>");
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
            submitHandler: (form) =>{
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    $("#frmModal").serialize(),
                    (data) => {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                '<?php echo $txtSuccess;?>',
                                "success"
                            );
                            //var tablaUpd = $("#operation").val();
                            $("#tblAusencia").DataTable().ajax.reload(null, false);
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
        })
    })

    <?php if ($arrayFormData[0]=="editar") { ?>
        $("#typeOperation").val('update');
        $("#modalTitle").html('Editar motivo de la ausencia');
        $("#expedienteAusenciaId").val('<?php echo $datSolicitud->expedienteAusenciaId?>');
        $("#expedienteId").val('<?php echo $datSolicitud->expedienteId?>');
        $("#persona").val('<?php echo $datSolicitud->expedienteId; ?>').trigger('change').attr("disabled","disabled");
        $("#fechaSolicitud").val('<?php echo $datSolicitud->fhSolicitud; ?>').attr("disabled","disabled");
        $("#fechaAu").val('<?php echo $datSolicitud->fechaAusencia; ?>').attr("disabled","disabled");
        $("#fechaFinAu").val('<?php echo $datSolicitud->fechaFinAusencia; ?>').attr("disabled","disabled");
        $("#totalDias").val('<?php echo $datSolicitud->totalDias; ?>');
        $("#horaInicio").val('<?php echo $datSolicitud->horaAusenciaInicio; ?>').attr("disabled","disabled");
        $("#horaFin").val('<?php echo $datSolicitud->horaAusenciaFin; ?>').attr("disabled","disabled");
        $("#totalHoras").val('<?php echo $datSolicitud->totalHoras; ?>').attr("disabled","disabled");
        $("#motivoAusencia").val('<?php echo $datSolicitud->motivoAusencia; ?>');
        $("#fechaApro").val('<?php echo $datSolicitud->fechaAutorizacion; ?>').attr("disabled","disabled");
        $("#autorizadoPor").val('<?php echo $datSolicitud->expedienteIdAutoriza;?>').trigger('change').attr("disabled","disabled");
        if (`<?php echo $datSolicitud->goceSueldo;?>`==`Si`) {
            $("#inlineRadio1").prop("checked", true).trigger("click").attr("disabled","disabled");
            $("#inlineRadio2").attr("disabled","disabled");
        }else{
            $("#inlineRadio2").prop("checked", true).trigger("click").attr("disabled","disabled");
            $("#inlineRadio1").attr("disabled","disabled");
        }

    <?php }else{ ?>
        $("#typeOperation").val('insert');
    <?php } ?>

</script>

