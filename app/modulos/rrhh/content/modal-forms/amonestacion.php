<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();


    $dataAmonestaciones = $cloud->row("
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
    CONCAT(
        IFNULL(nombre1, '-'),
        IFNULL(apellido1, '-')
    ) AS nombreUsuario,
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
    am.prsAdjuntoId,
    date_format(am.fechaVigenciaInicio, '%d-%m-%Y') as fechaVigenciaInicio,
    date_format(am.fechaVigenciaFin, '%d-%m-%Y') as fechaVigenciaFin,
    date_format(am.fechaSuspensionInicio, '%d-%m-%Y') as fechaSuspensionInicio,
    date_format(am.fechaSuspensionFin, '%d-%m-%Y') as fechaSuspensionFin
    FROM ((th_expediente_amonestaciones am
    JOIN th_expediente_personas exp ON am.expedienteId = exp.prsExpedienteId)
    JOIN th_personas per ON per.personaId = exp.personaId)
    WHERE am.flgDelete = 0 and am.expedienteAmonestacionId = ?
    ", [$_POST["arrayFormData"]]);

    ?>

<div class="row">
    <div class="col-md-6">
        <p>
            <i class="fas fa-calendar"></i> <b>Fecha de amonestación:</b> <?php echo $dataAmonestaciones->fhAmonestacion; ?><br>
            <b><i class="fas fa-user-times"></i> Tipo de amonestación:</b> <?php echo $dataAmonestaciones->tipoAmonestacion; ?><br>
            <b><i class="fas fa-user-times"></i> Estado: <span class="text-success"><?php echo $dataAmonestaciones->estadoAmonestacion; ?></span></b>
        </p>
    </div>
    <div class="col-md-6">
        <b><i class="fas fa-calendar-check"></i> Vigencia de amonestación: </b><br>
        Desde: <?php echo $dataAmonestaciones->fechaVigenciaInicio; ?>, Hasta: <?php echo $dataAmonestaciones->fechaVigenciaFin; ?><br>
        <b><i class="fas fa-times-circle"></i> Reincidencia: </b><?php echo $dataAmonestaciones->flgReincidencia; ?>
        <?php if(!is_null($dataAmonestaciones->suspension)){ ?>
            <br>
            <b>Estado: <span class="text-danger"><?php echo $dataAmonestaciones->suspension; ?></span></b><br>
            <b><i class="fas fa-calendar-times"></i> Duración de suspensión: </b><?php echo $dataAmonestaciones->totalDiasSuspension; ?> días<br>
            Desde: <?php echo $dataAmonestaciones->fechaSuspensionInicio; ?>, Hasta: <?php echo $dataAmonestaciones->fechaSuspensionFin; ?>
        <?php } ?>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-6">
        <h3><?php echo $dataAmonestaciones->causaFalta; ?></h3>
        <hr>
        <h4>Descripción de la falta:</h4>
        <p><?php echo $dataAmonestaciones->descripcionFalta; ?></p>
        <h4>Consecuencias de la falta:</h4>
        <p><?php echo $dataAmonestaciones->consecuenciaFalta; ?></p>
        <h4>Advertencia futuras faltas:</h4>
        <p><?php echo $dataAmonestaciones->advertenciaSiguienteFalta; ?></p>
        <h4>Compromiso de mejora:</h4>
        <p><?php echo $dataAmonestaciones->compromisoMejora; ?></p>
    </div>
    <?php if ($dataAmonestaciones->prsAdjuntoId == NULL){ ?>
    <div class="col-md-6">
        <h3>Amonestación adjunta:</h3>
        <hr>
        <input type="hidden" id="typeOperation" name="typeOperation" value="insert">
        <input type="hidden" id="operation" name="operation" value="adjuntoEmpleado">
        <input type="hidden" id="personaId" name="personaId" value="<?php echo $dataAmonestaciones->personaId; ?>">
        <input type="hidden" id="user" name="user" value="<?php echo $dataAmonestaciones->nombreUsuario; ?>">
        <input type="hidden" id="amonestacionId" name="amonestacionId" value="<?php echo $_POST["arrayFormData"]; ?>">

        <div class="file-upload-content">
            <img class="file-upload-image" src="#" />
            <div class="image-title-wrap">
                <button type="button" onclick="removeUpload()" class="btn btn-danger btn-sm remove-image"><i class="fas fa-minus-circle"></i> Eliminar <span class="image-title text-break">imagen seleccionada</span></button>
            </div>
        </div>
        <div class="form-outline mb-2">
            <label class="form-label" for="tipoAdjunto"><b><i class="fas fa-file-alt"></i> Tipo de adjunto:</b> </label><br>
            Amonestación
            <input type="hidden" id="tipoAdjunto" name="tipoAdjunto" value="Amonestación">
        </div>
        <div class="form-outline">
            <i class="fas fa-edit trailing"></i>
            <textarea id="descripcionAdjunto" class="form-control" name="descripcionAdjunto" required ></textarea>
            <label class="form-label" for="descripcionAdjunto">Descripción del archivo</label>
        </div>
        
        <!-- upload -->
        <div class="file-upload mt-4">
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
<script>
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
                                'Se ha adjuntado con éxito el archivo.',
                                "success"
                            );
                            var tablaUpd = $("#operation").val();
                            //$("#tblSucursal").DataTable().ajax.reload(null, false);
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
});
</script>

<?php } else { 

    $dataAdjunto = $cloud->row("SELECT prsAdjuntoId, personaId, tipoPrsAdjunto, descripcionPrsAdjunto, urlPrsAdjunto FROM th_personas_adjuntos WHERE prsAdjuntoId = ?", [$dataAmonestaciones->prsAdjuntoId]);

    $ext = pathinfo(strtolower($dataAdjunto->urlPrsAdjunto), PATHINFO_EXTENSION);
    
    $defaultOutput = '
        <h4 class="text-center mt-5">
            <i class="far fa-eye-slash fa-2x"></i><br>
            Vista previa no disponible
        </h4>
    ';
    switch ($ext){
        case "pdf":
            //$urlImagen = '../libraries/resources/images/icons/pdf.png';
            $urlImagen = '../libraries/resources/images/'. $dataAdjunto->urlPrsAdjunto;
            $altura = 'style="height: 80vh; width: 100%;"';
            $output = '<object class="img-fluid"  data="'.$urlImagen.'" '.$altura.' >';
            break;
        case "doc":
            $output = $defaultOutput;
            break;
        case "docx":
            $output = $defaultOutput;
            break;
        case "xls":
            $output = $defaultOutput;
            break;
        case "xlsx":
            $output = $defaultOutput;
            break;
        default:
            $urlImagen = '../libraries/resources/images/'. $dataAdjunto->urlPrsAdjunto;
            $altura ='style="margin: 0 auto; display: block;"';
            $output = '<object class="img-fluid"  data="'.$urlImagen.'" '.$altura.' >';

            break;
    }  
?>

    <div class="col-md-6">
        <?php echo $output; ?>
    </div>

<?php } ?>
</div>