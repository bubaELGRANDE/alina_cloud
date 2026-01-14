<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $arrayFormData = explode("^", $_POST["arrayFormData"]);


    $fechaExpedicion = "";
    $fechaInicio     = "";
    $fechaFin        = "";
    $diagnostico     = "";

    if (!empty($arrayFormData[1])){
        $dataIncapacidad = $cloud->row("
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
            inca.fechaInicio as fechaIni,
            inca.fechaFin as fechaFin,
            inca.fechaExpedicion as fechaExp,
            inca.motivoIncapacidad as motivo,
            inca.prsAdjuntoId as adjuntoId,
            inca.incapacidadSubsidio as subsidio,
            inca.tipoIncapacidad as tipoIncapacidad,
            inca.riesgoIncapacidad as riesgoIncapacidad,
            inca.expedienteIncapacidadId as incapacidadId
            FROM ((th_expediente_incapacidades inca
            JOIN th_expediente_personas exp ON inca.expedienteId = exp.prsExpedienteId)
            JOIN th_personas per ON per.personaId = exp.personaId)
            WHERE inca.flgDelete = 0 AND inca.expedienteIncapacidadId =  ?
        ", [$arrayFormData[1]]);

            $fechaExpedicion = 'value="' . $dataIncapacidad->fechaExp . '" disabled';
            $fechaInicio = 'value="' . $dataIncapacidad->fechaIni . '" disabled';
            $fechaFin = 'value="' . $dataIncapacidad->fechaFin . '" disabled';
            $diagnostico = $dataIncapacidad->motivo;

    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $arrayFormData[0]; ?>">
<input type="hidden" id="operation" name="operation" value="incapacidad">
<?php if (!empty($arrayFormData[1])){ ?>
<input type="hidden" id="incapacidadId" name="expedienteIncapacidadId" value="<?php echo $dataIncapacidad->incapacidadId;?>">
<input type="hidden" id="personaId" name="personaId" value="<?php echo $dataIncapacidad->personaId;?>">
<?php } ?>

<div class="row">
    <div class="col-md-8">
        <div class="form-select-control mb-4">
            <select class="persona" id="persona" name="persona" style="width:100%;" required>
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
            <input type="text" id="fechaExpedicion" class="form-control masked fecha" name="fechaExpedicion" data-mask="##-##-####" minlength="10" <?php echo $fechaExpedicion;?> required >
            <label class="form-label" for="fechaExpedicion">Fecha de expedición</label>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-12 mb-2">Duración de incapacidad:</div>
    <div class="col-md-4">
        <div class="form-outline input-daterange">
            <i class="fas fa-calendar trailing"></i>
            <input type="text" id="fechaIni" class="form-control masked fecha" name="fechaIni" data-mask="##-##-####" minlength="10" required onChange="contarDias();" <?php echo $fechaInicio;?>>
            <label class="form-label" for="fechaIni">Fecha de inicio</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline input-daterange">
            <i class="fas fa-calendar trailing"></i>
            <input type="text" id="fechaFin" class="form-control masked fecha" name="fechaFin" data-mask="##-##-####" minlength="10" required onChange="contarDias();" <?php echo $fechaFin;?>>
            <label class="form-label" for="fechaFin">Fecha de finalización</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-calendar trailing"></i>
            <input type="text" id="totalDias" class="form-control" name="totalDias" minlength="10" required disabled />
            <label class="form-label" for="totalDias">Total de días</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-align-justify trailing"></i>
            <textarea class="form-control" id="motivoIncapacidad" name="motivoIncapacidad" rows="3"><?php echo $diagnostico;?></textarea>
            <label class="form-label" for="motivoIncapacidad">Diagnostico/Motivo de incapacidad</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <select class="tipoIncapacidad" id="tipoIncapacidad" name="tipoIncapacidad" style="width:100%;" required>
            <option></option>
            <?php $arrayOptions = array("Inicial", "Prórroga");
                foreach($arrayOptions as $option){
                    echo '<option value="'. $option .'">'. $option .'</option>';
                }
            ?>
        </select>
    </div>
    <div class="col-md-4">
        <select class="riesgo" id="riesgo" name="riesgo" style="width:100%;" required>
            <option></option>
            <?php $arrayOptions = array("Enfermedad común", "Enfermedad profesional", "Accidente común", "Accidente de trabajo", "Maternidad");
                foreach($arrayOptions as $option){
                    echo '<option value="'. $option .'">'. $option .'</option>';
                }
            ?>
        </select>
    </div>
    <div class="col-md-4">
    <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="subsidio" id="genera" value="Si">
            <label class="form-check-label" for="genera">Genera subsidio</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="subsidio" id="noGenera" value="No">
            <label class="form-check-label" for="noGenera">No genera subsidios</label>
        </div>
    </div>
</div>
<?php if (empty($arrayFormData[1])){ ?>
<hr>
<div class="form-check form-check-inline mb-4">
    <input class="form-check-input" type="radio" name="flgAdjuntar" id="flgAdjuntarSi" value="Sí" />
    <label class="form-check-label" for="flgAdjuntarSi">Adjuntar incapacidad</label>
</div>
<div class="form-check form-check-inline mb-4">
    <input class="form-check-input" type="radio" name="flgAdjuntar" id="flgAdjuntarNo" value="No" checked />
    <label class="form-check-label" for="flgAdjuntarNo">No adjuntar incapacidad</label>
</div>
<div id="divAdjuntar" class="row">
    <div class="col-md-12">
        <div class="file-upload-content">
            <img class="file-upload-image" src="#" />
            <div class="image-title-wrap">
                <button type="button" onclick="removeUpload()" class="btn btn-danger btn-sm remove-image"><i class="fas fa-minus-circle"></i> Eliminar <span class="image-title text-break">imagen seleccionada</span></button>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <!-- upload -->
        <div class="file-upload mb-2">
            <button class="btn btn-primary btn-sm file-upload-btn" type="button" onclick="$('.file-upload-input').trigger( 'click' )"><i class="fas fa-upload"></i> Agregar archivo</button>

            <div class="image-upload-wrap">
                <input class="file-upload-input" id="adjunto" type='file' onchange="readURL(this);" accept="image/*, .pdf" name="adjunto" required />
                <div class="drag-text">
                    <h4><i class="fas fa-paperclip fa-2x"></i><br>
                    Arrastre un archivo o seleccione Agregar archivo</h4>
                </div>
            </div>
        </div>
        <small>Tamaño maximo de archivo: <?php echo ini_get('upload_max_filesize'); ?></small>
    </div>
</div>
<?php } ?>

<script>

    function contarDias() {
        var diaIni = $("#fechaIni").val();
        var diaFin = $("#fechaFin").val();

        var fecha1 = moment(diaIni);
        var fecha2 = moment(diaFin);

        if (diaIni == "" || diaFin == ""){
            $("#totalDias").val('0');
            $("#totalDias").addClass("active"); 
        } else if (fecha2.diff(fecha1, 'days') < 0){
            mensaje(
                "AVISO",
                "La fecha inicial debe ser anterior a la final.",
                "warning"
            );
            $("#fechaIni").val('');
            $("#fechaFin").val('');
            $("#totalDias").val('0');
        }  else {
            $("#totalDias").val(fecha2.diff(fecha1, 'days')+1);
            $("#totalDias").addClass("active"); 
        }

    }

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {

                let archivo = e.target.result;

                var file = $("#adjunto").val();
                var extension = file.substr((file.lastIndexOf('.') +1));
                switch (extension) {
                    case 'jpg': 
                        $('.file-upload-image').attr('src',archivo);
                        break;
                    case 'jpeg': 
                        $('.file-upload-image').attr('src',archivo);
                        break;
                    case 'png': 
                        $('.file-upload-image').attr('src', archivo);
                        break;
                    case 'bmp': 
                        $('.file-upload-image').attr('src', archivo);
                        break;
                    case 'pdf': 
                        $('.file-upload-image').attr('src', '../libraries/resources/images/icons/pdf.png');
                        break
                    case 'doc': 
                        $('.file-upload-image').attr('src', '../libraries/resources/images/icons/texto.png');
                        break;
                    case 'docx': 
                        $('.file-upload-image').attr('src', '../libraries/resources/images/icons/texto.png');
                        break;
                    case 'xls': 
                        $('.file-upload-image').attr('src', '../libraries/resources/images/icons/calculo.png');
                        break;
                    case 'xlsx': 
                        $('.file-upload-image').attr('src', '../libraries/resources/images/icons/calculo.png');
                        break;
                    case 'ai': 
                        $('.file-upload-image').attr('src', '../libraries/resources/images/icons/ai.png');
                        break;
                    case 'eps': 
                        $('.file-upload-image').attr('src', '../libraries/resources/images/icons/ai.png');
                        break;
                    case 'psd': 
                        $('.file-upload-image').attr('src', '../libraries/resources/images/icons/psd.png');
                        break;
            }
                $('.file-upload-content').show();
                $('.image-title').html(input.files[0].name);
            };
        reader.readAsDataURL(input.files[0]);
        } else {
            removeUpload();
        }
    }

    function removeUpload() {
        $('.file-upload-input').replaceWith($('.file-upload-input').clone());
        $('.file-upload-content').hide();
        $('.image-upload-wrap').show();
    }
    $('.image-upload-wrap').bind('dragover', function () {
            $('.image-upload-wrap').addClass('image-dropping');
        });
    $('.image-upload-wrap').bind('dragleave', function () {
        $('.image-upload-wrap').removeClass('image-dropping');
    });

function verificarImagen() {
    let imagen = document.getElementById("adjunto").value;
    let idxDot = imagen.lastIndexOf(".") + 1;
    let extFile = imagen.substr(idxDot, imagen.length).toLowerCase();
    if(extFile=="jpg" || extFile=="JPG" || extFile=="jpeg" || extFile=="JPEG" ||extFile=="PNG" || extFile=="png" || extFile=="pdf") {
        // Imagen valida
    } else {
        mensaje(
            "AVISO - FORMULARIO",
            "El archivo seleccionado no coincide con un formato válido. Por favor vuelva a seleccionar un archivo con formato válido.",
            "warning"
        );
        $("#adjunto").val('');
    }   
}


    $(document).ready(function() {
        $("#persona").select2({
            placeholder: "Empleado",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#tipoIncapacidad").select2({
            placeholder: "Tipo de incapacidad",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#riesgo").select2({
            placeholder: "Riesgo",
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

        $("#divAdjuntar").hide();

        $("input[type=radio][name=flgAdjuntar]").change(function(e) {
            if($(this).val() == "Sí") {
                $("#divAdjuntar").show();
            } else {
                $("#divAdjuntar").hide();
            }
        });

        <?php if (!empty($arrayFormData[1])){ ?>
            $('#persona').val(<?php echo $dataIncapacidad->expedienteId;?>).trigger('change');
            $('#tipoIncapacidad').val(`<?php echo $dataIncapacidad->tipoIncapacidad;?>`).trigger('change');
            $('#riesgo').val(`<?php echo $dataIncapacidad->riesgoIncapacidad;?>`).trigger('change');
            if (`<?php echo $dataIncapacidad->subsidio;?>` == `No`){
                $("#noGenera").prop("checked", true).trigger("click");
            }else{
                $("#genera").prop("checked", true).trigger("click");
            }
            $("#persona").select2({disabled: 'true'});
            $("#tipoIncapacidad").select2({disabled: 'true'});
            $("#riesgo").select2({disabled: 'true'});
            contarDias();
        <?php } ?>

        $("#frmModal").validate({
            submitHandler: function(form) {
                let form_data = new FormData($('#frmModal')[0]); // Para que envie los input file
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncFile(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    form_data,
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Se ha creado con éxito la incapacidad.',
                                "success"
                            );
                            var tablaUpd = $("#operation").val();
                            $("#tblIncapacidad").DataTable().ajax.reload(null, false);
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
        <?php 
            if(!empty($arrayFormData[1])) {
        ?>
                $("#modalTitle").html('Editar diagnostico/motivo de la incapacidad');
        <?php 
            }
        ?>

    });
</script>