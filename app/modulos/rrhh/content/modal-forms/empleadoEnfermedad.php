<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /* arrayFormData 
        Nuevo = nuevo ^ personaId ^ nombreCompleto
        Editar = editar ^ prsEnfermedadId ^ nombreCompleto
    */
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    if($arrayFormData[0] == "editar") {
        $dataEditEmpleadoEnfermedad = $cloud->row("
            SELECT
                pe.personaId AS personaId, 
                pe.catPrsEnfermedadId AS catPrsEnfermedadId,
                cpe.tipoEnfermedad AS tipoEnfermedad,
                cpe.nombreEnfermedad AS nombreEnfermedad
            FROM th_personas_enfermedades pe
            JOIN cat_personas_enfermedades cpe ON cpe.catPrsEnfermedadId = pe.catPrsEnfermedadId
            WHERE pe.prsEnfermedadId = ? AND pe.flgDelete = '0'
        ", [$arrayFormData[1]]);
        $personaId = $dataEditEmpleadoEnfermedad->personaId;
        $txtSuccess = "La " . $dataEditEmpleadoEnfermedad->tipoEnfermedad . " ha sido actualizada en el perfil del empleado";
    } else {
        $personaId = $arrayFormData[1];
        $txtSuccess = "La enfermedad/alergia ha sido agregada al perfil del empleado.";
    }
    
    $dataEstadoPersona = $cloud->row("
        SELECT
            estadoPersona
        FROM th_personas
        WHERE personaId = ?
    ",[$personaId]);
?>
<input type="hidden" id="typeOperation" name="typeOperation">
<input type="hidden" id="operation" name="operation" value="empleado-enfermedad">
<input type="hidden" id="flgOtro" name="flgOtro" value="0">
<input type="hidden" id="personaId" name="personaId" value="<?php echo $personaId; ?>">
<div id="divSelectEnfermedad">
    <div class="form-select-control mb-4">
        <select id="catPrsEnfermedadId" name="catPrsEnfermedadId" style="width: 100%;" required>
            <option></option>
            <?php 
                $dataEnfermedades = $cloud->rows("
                    SELECT
                        catPrsEnfermedadId, 
                        tipoEnfermedad, 
                        nombreEnfermedad
                    FROM cat_personas_enfermedades
                    WHERE flgDelete = '0'
                    ORDER BY tipoEnfermedad
                ");
                foreach ($dataEnfermedades as $dataEnfermedades) {
                    echo '<option value="'.$dataEnfermedades->catPrsEnfermedadId.'">'.$dataEnfermedades->nombreEnfermedad.' ('.$dataEnfermedades->tipoEnfermedad.')</option>';
                }
            ?>
        </select>
        <div class="form-helper text-end">
            <span class="badge rounded-pill bg-primary" style="cursor: pointer;" onclick="showHideOtro(1);">
                <i class="fas fa-plus-circle"></i> Otro
            </span>
        </div>
    </div>
</div>
<div id="divOtro">
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
</div>  
<script>
    function showHideOtro(tipo) {
        if(tipo == 1) {
            $("#divOtro").show();
            $("#divSelectEnfermedad").hide();
            $("#flgOtro").val(1);
            document.querySelectorAll('.form-hidden-update').forEach((formOutline) => {
                new mdb.Input(formOutline).update();
            });
        } else {
            $("#tipoEnfermedad").val('').trigger('change');
            $("#nombreEnfermedad").val('');
            $("#divOtro").hide();
            $("#divSelectEnfermedad").show();
            $("#flgOtro").val(0);
        }
    }

    $(document).ready(function() {
        $("#divOtro").hide();
        $("#catPrsEnfermedadId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Enfermedad/Alergia'
        });
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
                            $('#tblEmpleadoEnfermedades').DataTable().ajax.reload(null, false);
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
                $("#modalTitle").html('Editar <?php echo $dataEditEmpleadoEnfermedad->tipoEnfermedad; ?>: <?php echo $dataEditEmpleadoEnfermedad->nombreEnfermedad; ?>');
                $("#catPrsEnfermedadId").val('<?php echo $dataEditEmpleadoEnfermedad->catPrsEnfermedadId; ?>').trigger('change');
        <?php 
            } else {
        ?>
                $("#typeOperation").val('insert');
                $("#modalTitle").html('Nueva Enfermedad/Alergia');
        <?php 
            }
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
