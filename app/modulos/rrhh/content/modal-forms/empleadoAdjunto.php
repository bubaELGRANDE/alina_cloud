<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    $dataPersona = $cloud->row("
    SELECT CONCAT(
        IFNULL(nombre1, '-'),
        IFNULL(apellido1, '-')
    ) AS nombreCompleto FROM th_personas WHERE flgDelete = 0 AND personaId =?", [$arrayFormData[0]]);

    $dataEstadoPersona = $cloud->row("
        SELECT
            estadoPersona
        FROM th_personas
        WHERE personaId = ?
    ",[$arrayFormData[0]]);

    $max_upload = (int)(ini_get('upload_max_filesize'));
    $max_post = (int)(ini_get('post_max_size'));
    $memory_limit = (int)(ini_get('memory_limit'));
    $upload_mb = min($max_upload, $max_post, $memory_limit);
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="adjuntoEmpleado">
<input type="hidden" id="personaId" name="personaId" value="<?php echo $arrayFormData[0]; ?>">
<input type="hidden" id="user" name="user" value="<?php echo $dataPersona->nombreCompleto; ?>">
<div class="row">
    <div class="col-md-6">
        <div class="file-upload-content">
            <img class="file-upload-image" src="#" />
            <div class="image-title-wrap">
                <button type="button" onclick="removeUpload()" class="btn btn-danger btn-sm remove-image"><i class="fas fa-minus-circle"></i> Eliminar <span class="image-title text-break">imagen seleccionada</span></button>
            </div>
        </div>
        <?php if ($arrayFormData[1] == "fotoPerfil"){ ?>

            <div class="form-outline mb-2">
                <label class="form-label" for="tipoAdjunto"><b><i class="fas fa-file-alt"></i> Tipo de adjunto:</b> </label><br>
                Foto de empleado
                <input type="hidden" id="tipoAdjunto" name="tipoAdjunto" value="Foto de empleado">
            </div>
            <div class="form-outline">
                <label class="form-label" for="descripcionAdjunto"><b><i class="fas fa-edit"></i> Descripción del archivo:</b> </label><br>
                Actual
                <input type="hidden" id="descripcionAdjunto" name="descripcionAdjunto" value="Actual">
            </div>
            <hr>
            <div class="alert alert-secondary" role="alert">
                Las imagenes de perfil deben ir en formato cuadrado, de preferencia 800x800px (alto y ancho) para que se visualicen correctamente en los perfiles y reportes.
            </div>
        <?php } else { ?>

        <div class="form-select-control mb-2">
            <select class="form-select" id="tipoAdjunto" name="tipoAdjunto" style="width:100%;" required>
                <option></option>
                <?php 
                    $tiposArchivos = array("Solvencia","Curriculum","Fotografia","Documento escaneado", "Imagen", "Contrato de trabajo", "Amonestación");
                    for ($i=0; $i < count($tiposArchivos); $i++) { 
                        echo '<option value="'.$tiposArchivos[$i].'">'.$tiposArchivos[$i].'</option>';
                    }
                ?>

            </select>
        </div>
        <div class="form-outline">
            <i class="fas fa-edit trailing"></i>
            <textarea id="descripcionAdjunto" class="form-control" name="descripcionAdjunto" required ></textarea>
            <label class="form-label" for="descripcionAdjunto">Descripción del archivo</label>
        </div>
        <?php } ?>
    </div>
    <div class="col-md-6">
        <!-- upload -->
        <div class="file-upload mb-2">
            <button class="btn btn-primary btn-sm file-upload-btn" type="button" onclick="$('.file-upload-input').trigger( 'click' )"><i class="fas fa-upload"></i> Agregar archivo</button>

            <div class="image-upload-wrap">
                <input class="file-upload-input" id="adjunto" type='file' onchange="readURL(this);" accept="image/*, .pdf, .doc, docx, .xls, .xlsx" name="adjunto" required />
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

<?php if ($arrayFormData[1] !== "fotoPerfil"){ ?>
    $("#tipoAdjunto").select2({
        placeholder: "Tipo de adjunto",
        dropdownParent: $('#modal-container'),
        allowClear: true
    });
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
                                'Se ha adjuntado con éxito el archivo.',
                                "success"
                            );
                            var tablaUpd = $("#operation").val();
                            //$("#tblSucursal").DataTable().ajax.reload(null, false);
                            $('#modal-container').modal("hide");

                            <?php if ($arrayFormData[1] == "fotoPerfil"){ ?>
                            changePage('<?php echo $_SESSION["currentRoute"]; ?>', 'perfil-empleado', `personaId=<?php echo $arrayFormData[0]; ?>&nombreCompleto=<?php echo $arrayFormData[2]; ?>`);
                            <?php }else { ?>
                            getAdjunto(<?php echo $arrayFormData[0]; ?>);
                            <?php } ?>

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
        if($dataEstadoPersona->estadoPersona == "Inactivo") {
    ?>
            $("#btnModalAccept").prop("disabled", true);
    <?php 
        } else {
            // No deshabilitar botón
        }
    ?>
});
</script>