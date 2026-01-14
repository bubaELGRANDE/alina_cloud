<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$max_upload = (int) (ini_get('upload_max_filesize'));
$max_post = (int) (ini_get('post_max_size'));
$memory_limit = (int) (ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);

$nombre_archivo = "plantillaProductos-".date("YmdHis");
?>
<div class="row">
    <div class="col-md-12 text-end">
        <button type="button" class="btn btn-primary ttip" id="btnDescargarExcel" onclick="">
            <i class="fas fa-plus-circle"></i> Descargar plantilla
            <span class="ttiptext">Descargar plantilla</span>
        </button>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-12">
        <div class="file-upload-content">
            <img class="file-upload-image" src="#" />
            <div class="image-title-wrap">
                <button type="button" onclick="removeUpload()" class="btn btn-danger btn-sm remove-image"><i
                        class="fas fa-minus-circle"></i> Eliminar <span class="image-title text-break">imagen
                        seleccionada</span></button>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <!-- upload -->
        <div class="file-upload mb-2">
            <button class="btn btn-primary btn-sm file-upload-btn" type="button"
                onclick="$('.file-upload-input').trigger( 'click' )"><i class="fas fa-upload"></i> Agregar
                archivo</button>

            <div class="image-upload-wrap">
                <input class="file-upload-input" id="adjunto" type='file' onchange="readURL(this);"
                    accept="image/*, .pdf, .doc, docx, .xls, .xlsx" name="adjunto" required />
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
            reader.onload = function (e) {

                let archivo = e.target.result;

                var file = $("#adjunto").val();
                var extension = file.substr((file.lastIndexOf('.') + 1));
                switch (extension) {
                    case 'xls':
                        $('.file-upload-image').attr('src', '../libraries/resources/images/icons/calculo.png');
                        break;
                    case 'xlsx':
                        $('.file-upload-image').attr('src', '../libraries/resources/images/icons/calculo.png');
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


    $(document).ready(function () {
        $("#btnDescargarExcel").click(function (e) {
            asyncXLS(
                "<?php echo $_SESSION['currentRoute']; ?>reports/xls/plantillaProducto",
                {
                    nombreArchivo: '<?php echo $nombre_archivo; ?>'
                },
                function (data) {
                    mensaje_do_aceptar(
                        `Operación completada:`,
                        `Plantilla de productos generada con éxito`,
                        `success`,
                        function () {
                            // Crea una URL para el archivo y lo descarga
                            var url = window.URL.createObjectURL(data);
                            var a = document.createElement("a");
                            a.href = url;
                            a.download = "<?php echo $nombre_archivo; ?>";
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url); // Liberar la URL
                        },
                        `Descargar archivo`
                    );
                }
            );
        });
        $("#frmModal").validate({
            submitHandler: function (form) {
                let form_data = new FormData($('#frmModal')[0]); // Para que envie los input file
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncFile(
                    "<?php echo $_SESSION['currentRoute']; ?>reports/xls/cargarProductosExcel",
                    form_data,
                    function (data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if (data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Productos cargados con exito.',
                                "success"
                            );
                            $('#modal-container').modal("hide");
                        } else {
                            console.log(data);
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