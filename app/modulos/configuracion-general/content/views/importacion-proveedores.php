<?php 
	@session_start();

    $max_upload = (int)(ini_get('upload_max_filesize'));
    $max_post = (int)(ini_get('post_max_size'));
    $memory_limit = (int)(ini_get('memory_limit'));
    $upload_mb = min($max_upload, $max_post, $memory_limit);
?>
<h2>
    Importación de proveedores
</h2>
<hr>
<!-- <div class="row">
    <div class="col text-end">
        <button id="btnNuevaArea" type="button" class="btn btn-primary" onclick="nuevaSuc();"><i class="fas fa-file-upload"></i> Cargar archivo</button>
    </div>
</div> -->

<div class="row justify-content-center">
    <div class="col-md-6">
        <form id="frmModal">
            <input type="hidden" id="typeOperation" name="typeOperation" value="insert">
            <input type="hidden" id="operation" name="operation" value="proveedores-carga">
            <label id="labelNombreArchivo" class="form-label">
                <b>Archivo seleccionado: </b> No se ha seleccionado un archivo.
            </label>
            <div class="file-upload mb-2">
                <button class="btn btn-primary btn-sm file-upload-btn" type="button" onclick="$('.file-upload-input').trigger( 'click' )"><i class="fas fa-upload"></i> Agregar archivo</button>
        
                <div class="image-upload-wrap">
                    <input type='file' class="file-upload-input" id="adjunto" name="adjunto" onchange="verificarArchivo();" accept=".txt" required />
                    <div class="drag-text">
                        <h4>
                            <i class="fas fa-paperclip fa-2x"></i><br>
                            Arrastre un archivo o seleccione agregar archivo
                        </h4>
                    </div>
                </div>
            </div>
            <small>Tamaño máximo de archivo: <?php echo ini_get('upload_max_filesize'); ?></small>
            <hr>
            <div class="text-end">
                <button id="btnImportDist" type="submit" class="btn btn-success"><i class="fas fa-file-upload"></i> Cargar archivo</button>
            </div>
        </form>
    </div>
</div>

<script>
    function verificarArchivo() {
        let archivo = document.getElementById("adjunto").value;
        let idxDot = archivo.lastIndexOf(".") + 1;
        let extFile = archivo.substr(idxDot, archivo.length).toLowerCase();
        if(extFile == "txt") {
            // Archivo valido
            $('#labelNombreArchivo').html(`<b>Archivo seleccionado: </b> <i class="fas fa-file-alt"></i> ${$('#adjunto').val().replace(/.*(\/|\\)/, '')} <i class="fas fa-check-circle text-success"></i>`);
        } else {
            mensaje(
                "Aviso:",
                "El archivo seleccionado no coincide con el formato establecido. Por favor seleccione nuevamente el archivo.",
                "warning"
            );
            $("#adjunto").val('');
            $('#labelNombreArchivo').html('<b>Archivo seleccionado: </b>No se ha seleccionado un archivo.');
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
                            mensaje_do_aceptar(
                                "Operación completada:",
                                'Proveedores cargados con éxito.',
                                "success", 
                                function() {
                                    // $('#tblValidacionPendientes').DataTable().ajax.reload(null, false);
                                    // $('#modal-container').modal("hide");
                                    
                                }
                            );
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