<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    $dataAdjunto = $cloud->row("SELECT prsAdjuntoId, personaId, tipoPrsAdjunto, descripcionPrsAdjunto, urlPrsAdjunto FROM th_personas_adjuntos WHERE prsAdjuntoId = ?", [$_POST["arrayFormData"]]);

    $ext = pathinfo(strtolower($dataAdjunto->urlPrsAdjunto), PATHINFO_EXTENSION);

    $dataEstadoPersona = $cloud->row("
        SELECT
            estadoPersona
        FROM th_personas
        WHERE personaId = ?
    ",[$dataAdjunto->personaId]);

    if($dataEstadoPersona->estadoPersona == "Inactivo" || $dataAdjunto->tipoPrsAdjunto == "Baja de empleado" || $dataAdjunto->tipoPrsAdjunto == "Incapacidad") {
        $disabledInactivo = "disabled";
    } else {
        $disabledInactivo = "";
    }

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

<div class="row">
    <div class="col-md-3">
        <div id="info-adjunto" class="mb-4" >
            <b><i class="fas fa-file-alt"></i> Tipo de adjunto:</b><br> 
            <?php echo $dataAdjunto->tipoPrsAdjunto; ?> <br><br>
            <b><i class="fas fa-edit"></i> Descripción:</b><br> 
            <?php echo $dataAdjunto->descripcionPrsAdjunto; ?>
        </div>
        <div id="edit-info" class="mb-4" style="display:none;">
            <input type="hidden" id="typeOperation" name="typeOperation" value="update">
            <input type="hidden" id="operation" name="operation" value="adjuntoEmpleado">
            <input type="hidden" id="adjuntoId" name="adjuntoId" value="<?php echo $_POST["arrayFormData"]; ?>">
            <input type="hidden" id="personaId" name="personaId" value="<?php echo $dataAdjunto->personaId; ?>">
            <div class="form-select-control mb-4">
                <select class="form-select" id="tipoAdjunto" name="tipoAdjunto" style="width:100%;" required>
                    <option></option>
                    <?php 
                        $tiposArchivos = array("Solvencia","Curriculum","Fotografia","Documento escaneado");
                        for ($i=0; $i < count($tiposArchivos); $i++) { 
                            echo '<option value="'.$tiposArchivos[$i].'">'.$tiposArchivos[$i].'</option>';
                        }
                        ?>
                </select>
            </div>
            <div class="form-outline form-update-direccion mb-4">
                <i class="fas fa-edit trailing"></i>
                <textarea type="text" id="direccionActual" class="form-control" name="descripcionAdjunto" required><?php echo $dataAdjunto->descripcionPrsAdjunto;?></textarea>
                <label class="form-label" for="direccionActual">Descripción del archivo</label>
            </div>
            <button type="submit" id="guardar" class="btn btn-primary btn-sm mb-2" <?php echo $disabledInactivo; ?>><i class="fas fa-save"></i> Guardar</button>
            <button type="button" id="cancelar" class="btn btn-danger btn-sm mb-2"><i class="fas fa-times-circle"></i> Cancelar</button>
        </div>
        <button type="button" id="edit-btn" class="btn btn-primary btn-block mb-2" <?php echo $disabledInactivo; ?>><i class="fas fa-pencil-alt"></i> Editar información</button>
        <hr>
        <a href="<?php echo '../libraries/resources/images/'. $dataAdjunto->urlPrsAdjunto; ?>" class="btn btn-success btn-block mb-2" download><i class="fas fa-download"></i> Descargar adjunto</a>
    </div>
    <div class="col-md-9">
        <!-- object class="img-fluid"  data="<?php echo $urlImagen;?>" <?php echo $altura; ?> -->
        <?php echo $output; ?>
    </div>
</div>
<script>

    $(document).ready(function() {

        $( "#edit-btn, #cancelar" ).click(function() {
            $("#edit-info").toggle();
            $("#info-adjunto").toggle();
            $("#edit-btn").toggle();
        });

        $("#tipoAdjunto").select2({
            placeholder: "Tipo de adjunto",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $('#tipoAdjunto').val('<?php echo $dataAdjunto->tipoPrsAdjunto; ?>').trigger('change');


        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("guardar", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("guardar", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Se actualizado la información del producto.',
                                "success"
                            );
                            var tablaUpd = $("#operation").val();
                            $('#modal-container').modal("hide");
                            getAdjunto(<?php echo $dataAdjunto->personaId; ?>);
                            verAdjuntoModal(<?php echo $dataAdjunto->prsAdjuntoId; ?>);
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