<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /* arrayFormData 
        Nuevo = nuevo
        Editar = editar ^ prsCargoId 
    */
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    if($arrayFormData[0] == "editar") {
        $dataEditCargo = $cloud->row("
            SELECT
                cargoPersona,
                descripcionCargoPersona,
                funcionCargoPersona,
                herramientasCargoPersona
            FROM cat_personas_cargos
            WHERE prsCargoId = ?
        ", [$arrayFormData[1]]);
        $txtSuccess = "Cargo actualizado con éxito.";
    } else {
        $txtSuccess = "Cargo agregado con éxito.";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation">
<input type="hidden" id="operation" name="operation" value="cargo-persona">
<div class="form-outline mb-4">
    <i class="fas fa-briefcase trailing"></i>
    <input type="text" id="cargoPersona" class="form-control" name="cargoPersona" required />
    <label class="form-label" for="cargoPersona">Nombre del cargo</label>
</div> 
<div class="form-outline mb-4">
    <i class="fas fa-edit trailing"></i>
    <textarea type="text" id="descripcionCargoPersona" class="form-control" name="descripcionCargoPersona" required></textarea>
    <label class="form-label" for="descripcionCargoPersona">Objetivo del cargo</label>
    <div class="form-helper text-end">
        De uso institucional
    </div>
</div>
<div class="form-outline mb-4">
    <i class="fas fa-edit trailing"></i>
    <textarea type="text" id="funcionCargoPersona" class="form-control" name="funcionCargoPersona" required></textarea>
    <label class="form-label" for="funcionCargoPersona">Funciones del cargo</label>
    <div class="form-helper text-end">
        De uso para contrato
    </div>
</div>
<div class="form-outline mb-4">
    <i class="fas fa-tools trailing"></i>
    <textarea type="text" id="herramientasCargoPersona" class="form-control" name="herramientasCargoPersona" required></textarea>
    <label class="form-label" for="herramientasCargoPersona">Herramientas/Materiales del cargo</label>
    <div class="form-helper text-end">
        De uso para contrato
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                '<?php echo $txtSuccess; ?>',
                                "success"
                            );
                            $('#tblCargosPersonas').DataTable().ajax.reload(null, false);
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
                $("#modalTitle").html(`Editar Cargo: <?php echo $dataEditCargo->cargoPersona; ?>`);
                $("#cargoPersona").val(`<?php echo $dataEditCargo->cargoPersona; ?>`);
                $("#descripcionCargoPersona").val(`<?php echo $dataEditCargo->descripcionCargoPersona; ?>`);
                $("#funcionCargoPersona").val(`<?php echo $dataEditCargo->funcionCargoPersona; ?>`);
                $("#herramientasCargoPersona").val(`<?php echo $dataEditCargo->herramientasCargoPersona; ?>`);
        <?php 
            } else {
        ?>
                $("#typeOperation").val('insert');
                $("#modalTitle").html('Nuevo Cargo');
        <?php 
            }
        ?>
    });
</script>
