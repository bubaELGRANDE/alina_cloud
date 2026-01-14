<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    // arrayFormData = prsExpedienteId
    $arrayFormData = explode('^', $_POST["arrayFormData"]);

    // agregar id de expediente cuando si existe :v

    $poseeExpediente = $cloud->row("
        SELECT prsExpedienteId FROM th_expediente_personas
        WHERE personaId = ? AND estadoExpediente = 'Activo' AND flgDelete = '0'
    ",[$arrayFormData[1]]);

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="expediente-procesar-baja">
<input type="hidden" id="personaId" name="personaId" value="<?php echo $arrayFormData[1]; ?>">
<input type="hidden" id="nombreCompleto" name="nombreCompleto" value="<?php echo $arrayFormData[0]; ?>">
<input type="hidden" id="flgPersona" name="flgPersona" value="<?php echo '1'; ?>">
<?php if(!empty($poseeExpediente)){
    echo '<input type="hidden" id="expedienteId" name="expedienteId" value="'.$poseeExpediente->prsExpedienteId.'">';
}
?>
<div class="row">
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <input type="date" id="fechaBaja" class="form-control" name="fechaBaja" required />
            <label class="form-label" for="fechaBaja">Fecha de baja</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select id="estadoBaja" name="estadoBaja" style="width: 100%;" required>
                <option></option>
                <?php 
                    $estadoBaja = array("Despido", "Renuncia", "Abandono", "Defunción", "Traslado", "Jubilado");
                    for ($i=0; $i < count($estadoBaja); $i++) { 
                        echo '<option value="'.$estadoBaja[$i].'">'.$estadoBaja[$i].'</option>';
                    }
                ?>
            </select>
        </div>     
    </div>
</div>
<div class="form-outline mb-4">
    <i class="fas fa-edit trailing"></i>
    <textarea class="form-control" id="justificacionBaja" name="justificacionBaja" rows="4" required></textarea>
    <label class="form-label" for="justificacionBaja">Justificación</label>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-check-validate mb-4">
            <label class="fw-bold">¿Re-contratable?</label>
            <br>
            <?php 
                $contratable = array("Sí", "No");
                for ($i=0; $i < count($contratable); $i++) { 
                    echo '
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="contratable" id="contratable'.$i.'" value="'.$contratable[$i].'" required>
                            <label class="form-check-label" for="contratable'.$i.'">'.$contratable[$i].'</label>
                        </div>                    
                    ';
                }
            ?>
        </div>        
    </div>
    <div class="col-md-6">
        <div class="form-check-validate mb-4">
            <label class="fw-bold">¿Adjuntar archivo?</label>
            <br>
            <?php 
                $flgAdjunto = array("Sí", "No");
                for ($i=0; $i < count($flgAdjunto); $i++) { 
                    echo '
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="flgAdjunto" id="flgAdjunto'.$i.'" value="'.$flgAdjunto[$i].'" required>
                            <label class="form-check-label" for="flgAdjunto'.$i.'">'.$flgAdjunto[$i].'</label>
                        </div>                    
                    ';
                }
            ?>
        </div>
    </div>
</div>
<!-- upload -->
<div id="divAdjunto">
    <label id="labelNombreArchivo" class="form-label">
        <b>Archivo seleccionado: </b> No se ha seleccionado un archivo.
    </label>
    <div class="file-upload mb-2">
        <button class="btn btn-primary btn-sm file-upload-btn" type="button" onclick="$('.file-upload-input').trigger( 'click' )"><i class="fas fa-upload"></i> Agregar archivo</button>

        <div class="image-upload-wrap">
            <input class="file-upload-input" id="adjunto" type='file' onchange="verificarArchivo();" accept="image/*, .pdf, .doc, docx, .xls, .xlsx" name="adjunto" required />
            <div class="drag-text">
                <h4><i class="fas fa-paperclip fa-2x"></i><br>
                Arrastre un archivo o seleccione Agregar archivo</h4>
            </div>
        </div>
    </div>
    <small>Tamaño maximo de archivo: <?php echo ini_get('upload_max_filesize'); ?></small>
</div>

<script>
    function verificarArchivo() {
        let imagen = document.getElementById("adjunto").value;
        let idxDot = imagen.lastIndexOf(".") + 1;
        let extFile = imagen.substr(idxDot, imagen.length).toLowerCase();
        if(extFile=="jpg" || extFile=="JPG" || extFile=="jpeg" || extFile=="JPEG" ||extFile=="PNG" || extFile=="png" || extFile=="pdf") {
            // Archivo valido
            $('#labelNombreArchivo').html(`<b>Archivo seleccionado: </b> <i class="fas fa-file-alt"></i> ${$('#adjunto').val().replace(/.*(\/|\\)/, '')} <i class="fas fa-check-circle text-success"></i>`);
        } else {
            mensaje(
                "AVISO - FORMULARIO",
                "El archivo seleccionado no coincide con un formato válido. Por favor vuelva a seleccionar un archivo con formato válido.",
                "warning"
            );
            $("#adjunto").val('');
            $('#labelNombreArchivo').html('<b>Archivo seleccionado: </b>No se ha seleccionado un archivo.');
        }   
    }

    $(document).ready(function() {
        $("#divAdjunto").hide();

        $("#estadoBaja").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Motivo'
        });

        $("[name='flgAdjunto']").on('change', function() { 
            if($('[name="flgAdjunto"]:checked').val() == "Sí") {
                $("#divAdjunto").show();
            } else {
                $("#divAdjunto").hide();
            }
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                mensaje_confirmacion(
                    '¿Está seguro que desea dar de baja al empleado <?php echo $arrayFormData[0]; ?>', 
                    `Se cambiará el estado del expediente a ${$("input[name='estadoBaja']:checked").val()} y el estado del empleado a Inactivo.`, 
                    `warning`, 
                    function(param) {
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
                                        'Baja de empleado realizada con éxito.',
                                        "success"
                                    );
                                    $('#tblEmpleados').DataTable().ajax.reload(null, false);
                                    $('#tblEmpleadosInactivos').DataTable().ajax.reload(null, false);
                                    
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
                    },
                    'Dar de Baja',
                    `Cancelar`
                );
            }
        });
    });
</script>