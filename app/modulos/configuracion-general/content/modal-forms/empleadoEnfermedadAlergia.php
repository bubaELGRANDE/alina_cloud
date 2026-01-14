<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /* arrayFormData 
        Nuevo = nuevo
        Editar = editar ^ catPrsEnfermedadId 
    */
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    if($arrayFormData[0] == "editar") {
        $dataEditEnfermedad = $cloud->row("
            SELECT
                tipoEnfermedad,
                nombreEnfermedad
            FROM cat_personas_enfermedades
            WHERE catPrsEnfermedadId = ?
        ", [$arrayFormData[1]]);
        $txtSuccess = "La " . $dataEditEnfermedad->tipoEnfermedad . " ha sido actualizada con éxito";
    } else {
        $txtSuccess = "Enfermedad/Alergia agregada con éxito.";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation">
<input type="hidden" id="operation" name="operation" value="enfermedad-alergia">
<div class="form-select-control mb-4">
    <select id="tipoEnfermedad" name="tipoEnfermedad" style="width: 100%;" required>
        <option></option>
        <?php 
            $tipoEnfermedades = array("Alergia","Enfermedad");
            for ($i=0; $i < count($tipoEnfermedades); $i++) { 
                echo '<option value="'.$tipoEnfermedades[$i].'">'.$tipoEnfermedades[$i].'</option>';
            }
        ?>
    </select>
</div>
<div class="form-outline form-hidden-update mb-4">
    <i class="fas fa-syringe trailing"></i>
    <input type="text" id="nombreEnfermedad" class="form-control" name="nombreEnfermedad" readonly required />
    <label id="labelNombreEnfermedad" class="form-label" for="nombreEnfermedad">Nombre de la enfermedad/alergia</label>
    <div class="form-helper text-end">
        <span class="badge rounded-pill bg-secondary" style="cursor: pointer;" onclick="showHideOtro(0);">
            <i class="fas fa-times-circle"></i> Cancelar
        </span>
    </div>
</div> 
<script>
    $(document).ready(function() {
        $("#tipoEnfermedad").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo'
        });

        $("#tipoEnfermedad").change(function(e) {
            if($("#tipoEnfermedad").val() == "Alergia") {
                $("#labelNombreEnfermedad").html("Nombre de la alergia");
                $("#nombreEnfermedad").removeAttr("readonly");
            } else if($("#tipoEnfermedad").val() == "Enfermedad") {
                $("#labelNombreEnfermedad").html("Nombre de la enfermedad");
                $("#nombreEnfermedad").removeAttr("readonly");
            } else {
                $("#labelNombreEnfermedad").html("Nombre de la enfermedad/alergia");
                $("#nombreEnfermedad").prop("readonly", true);
            }
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                '<?php echo $txtSuccess; ?>',
                                "success"
                            );
                            $('#tblEnfermedades').DataTable().ajax.reload(null, false);
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
            if($arrayFormData[0] == "editar") {
        ?>
                $("#typeOperation").val('update');
                $("#modalTitle").html('Editar <?php echo $dataEditEnfermedad->tipoEnfermedad; ?>: <?php echo $dataEditEnfermedad->nombreEnfermedad; ?>');
                $("#tipoEnfermedad").val('<?php echo $dataEditEnfermedad->tipoEnfermedad; ?>').trigger('change');
                $("#nombreEnfermedad").val('<?php echo $dataEditEnfermedad->nombreEnfermedad; ?>');
        <?php 
            } else {
        ?>
                $("#typeOperation").val('insert');
                $("#modalTitle").html('Nueva Enfermedad/Alergia');
        <?php 
            }
        ?>
    });
</script>
