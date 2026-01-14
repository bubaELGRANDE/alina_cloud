<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $operation = "insert";
    $organigramaRama = "";
    $mensaje = "Rama creada exitosamente.";

    if ($_POST["arrayFormData"] != 0){
        $dataRamas = $cloud->row("SELECT 
                organigramaRamaId,
                organigramaRama,
                organigramaRamaDescripcion,
                ramaSuperiorId
            FROM cat_organigrama_ramas
            WHERE flgDelete = '0' AND organigramaRamaId =?", [$_POST["arrayFormData"]]);

            $operation = "update";
            $organigramaRama = $dataRamas->organigramaRamaId;
            $mensaje = "Rama actualizada exitosamente.";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $operation; ?>">
<input type="hidden" id="operation" name="operation" value="organigrama-rama">
<input type="hidden" id="operation" name="organigramaRamaId" value="<?php echo $organigramaRama; ?>">

<div class="row">
    <div class="col-md-6">
        <div class="form-outline">
            <i class="fas fa-code-branch trailing"></i>
            <input type="text" id="nombreRama" name="nombreRama" class="form-control" required />
            <label class="form-label" for="salario">Nombre de la rama</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select id="ramaSuperior" name="ramaSuperior" style="width:100%;" required>
                <option></option>
                <?php 
                    $dataOrganigrama = $cloud->rows("
                    SELECT 
                        organigramaRamaId,
                        organigramaRama
                    FROM cat_organigrama_ramas
                    WHERE flgDelete = '0'
                    ");

                    foreach ($dataOrganigrama as $ramaOrganigrama) {
                    echo '<option value="'.$ramaOrganigrama->organigramaRamaId.'">'.$ramaOrganigrama->organigramaRama .'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-outline form-hidden-update mb-4">
            <i class="fas fa-edit trailing"></i>
            <textarea type="text" id="organigramaRamaDescripcion" class="form-control" name="organigramaRamaDescripcion" required></textarea>
            <label class="form-label" for="organigramaRamaDescripcion">Descripción de la Rama</label>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#ramaSuperior").select2({
            placeholder: "Rama superior",
            dropdownParent: $('#modal-container'),
            allowClear: true
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
                                "<?php echo $mensaje; ?>",
                                "success"
                            );
                            $('#tblOrganigrama').DataTable().ajax.reload(null, false);
                            $('#modal-container').modal("hide");
                            getOrganigrama(0);
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

        <?php if ($_POST["arrayFormData"] != 0){ ?>
            $("#nombreRama").val("<?php echo $dataRamas->organigramaRama; ?>");
            $("#ramaSuperior").val("<?php echo $dataRamas->ramaSuperiorId; ?>").trigger('change');
            $("#organigramaRamaDescripcion").val("<?php echo $dataRamas->organigramaRamaDescripcion; ?>");
        <?php } ?>
    });
</script>