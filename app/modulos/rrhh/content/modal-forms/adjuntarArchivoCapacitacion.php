<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="adjuntoCapacitacion">
<input type="hidden" id="expedienteCapacitacionDetalleId" name="expedienteCapacitacionDetalleId" value="<?php echo $_POST["expedienteCapacitacionDetalleId"]; ?>">
<input type="hidden" id="personaId" name="personaId" value="<?php echo $_POST["personaId"]; ?>">
<input type="hidden" id="nombreCompleto" name="nombreCompleto" value="<?php echo $_POST["nombreCompleto"]; ?>">
<input type="hidden" id="carpetaEmpleado" name="carpetaEmpleado" value="<?php echo $_POST['carpetaEmpleado']; ?>">
<div class="row">
    <div class="col-md-12">
        <div class="file-upload-content">
            <img class="file-upload-image" src="#" />
            <div class="image-title-wrap">
                <button type="button" onclick="removeUpload()" class="btn btn-danger btn-sm remove-image"><i class="fas fa-minus-circle"></i> Eliminar <span class="image-title text-break">imagen seleccionada</span></button>
            </div>
        </div>
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
                            // var tablaUpd = $("#operation").val();
                            $("#tblCapacitaciones").DataTable().ajax.reload(null, false);
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