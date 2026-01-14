<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $max_upload = (int)(ini_get('upload_max_filesize'));
    $max_post = (int)(ini_get('post_max_size'));
    $memory_limit = (int)(ini_get('memory_limit'));
    $upload_mb = min($max_upload, $max_post, $memory_limit);
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="calcular-comision">
<input type="hidden" id="flgRecalculo" name="flgRecalculo" value="0">
<div class="form-select-control mb-4">
    <select class="form-select" id="mes" name="mes" style="width:100%;" required>
        <option></option>
        <?php 
            $mesesAnio = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
            for ($i=1; $i < count($mesesAnio); $i++) { 
                echo '<option value="'.$i.'">'.$mesesAnio[$i].'</option>';
            }
        ?>
    </select>
</div>
<div class="form-select-control mb-4">
    <select id="anio" name="anio" style="width: 100%;" required>
        <option></option>
        <option value="<?php echo date('Y'); ?>"><?php echo date('Y'); ?></option>
        <option value="<?php echo date('Y') - 1; ?>"><?php echo date('Y') - 1; ?></option>
	    <?php 
            /*
            Quitar comentarios solo en caso de querer recalcular por pruebas
            for ($i=date("Y"); $i >= 2021; $i--) { 
                echo '<option value="'.$i.'">'.$i.'</option>';
            }
            */
        ?>   
    </select>    
</div>  
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
                Arrastre un archivo o seleccione Agregar archivo
            </h4>
        </div>
    </div>
</div>
<small>Tamaño máximo de archivo: <?php echo ini_get('upload_max_filesize'); ?></small>
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
                "AVISO - FORMULARIO",
                "El archivo seleccionado no coincide con el formato establecido. Por favor seleccione nuevamente el archivo.",
                "warning"
            );
            $("#adjunto").val('');
            $('#labelNombreArchivo').html('<b>Archivo seleccionado: </b>No se ha seleccionado un archivo.');
        }   
    }

    $(document).ready(function() {
        $("#mes").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Mes de la comisión'
        });

        $("#anio").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Año de la comisión'
        });

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
                                'Se ha realizado el cálculo de comisiones con éxito.',
                                "success"
                            );
                            // Seleccionar el mes y año de fondo en calculo-comisiones
                            $("#periodoMes").val($("#mes").val()).trigger('change');
                            $("#periodoAnio").val($("#anio").val()).trigger('change');
                            $('#modal-container').modal("hide");
                        } else if(data == "recalculo") {
                            mensaje_confirmacion(
                                `Las comisiones del periodo ${$("#periodoMes option:selected").text()} - ${$("#periodoAnio").val()} ya fueron calculadas. ¿Desea recalcular este periodo?`, 
                                `Se eliminará el cálculo de comisiones anterior y se recalculará nuevamente con la información del archivo y periodo seleccionado.`, 
                                `warning`, 
                                function(param) {
                                    $("#frmModal").submit();
                                },
                                `Sí, recalcular`,
                                `Cancelar`
                            );
                            $("#flgRecalculo").val(1);
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