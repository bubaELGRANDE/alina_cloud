<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
        POST:
        typeOperation
        tipoDevengo
        tituloModal
        catPlanillaDevengoId
        catPlanillaDevengoIdSuperior
        tblDevengo
    */
    if($_POST['typeOperation'] == "update") {
        $dataEditDevengo = $cloud->row("
            SELECT
                nombreDevengo, 
                codigoContable, 
                catPlanillaDevengoIdSuperior
            FROM cat_planilla_devengos
            WHERE catPlanillaDevengoId = ? AND flgDelete = ?
        ", [$_POST['catPlanillaDevengoId'], 0]);

        $txtSuccess = "Devengo actualizado con éxito.";
    } else {
        $txtSuccess = "Devengo agregado con éxito.";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
<input type="hidden" id="operation" name="operation" value="parametrizacion-devengo">
<input type="hidden" id="catPlanillaDevengoId" name="catPlanillaDevengoId" value="<?php echo $_POST['catPlanillaDevengoId']; ?>">
<input type="hidden" id="catPlanillaDevengoIdSuperior" name="catPlanillaDevengoIdSuperior" value="<?php echo $_POST['catPlanillaDevengoIdSuperior']; ?>">
<input type="hidden" id="tipoDevengo" name="tipoDevengo" value="<?php echo $_POST['tipoDevengo']; ?>">
<div class="row mb-4">
    <div class="col-md-8">
        <div class="form-outline">
            <i class="fas fa-money-check-alt trailing"></i>
            <input type="text" id="nombreDevengo" class="form-control" name="nombreDevengo" required />
            <label class="form-label" for="nombreDevengo">Nombre del devengo</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline">
            <i class="fas fa-list-ol trailing"></i>
            <input type="text" id="codigoContable" class="form-control" name="codigoContable" required />
            <label class="form-label" for="codigoContable">Código contable</label>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
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
                            $(`#<?php echo $_POST['tblDevengo']; ?>`).DataTable().ajax.reload(null, false);
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
            if($_POST['typeOperation'] == "update") {
        ?>
                $("#nombreDevengo").val(`<?php echo $dataEditDevengo->nombreDevengo; ?>`);
                $("#codigoContable").val(`<?php echo $dataEditDevengo->codigoContable; ?>`);
        <?php
            } else {
                // Ya se especifico el titulo de la modal
            }
        ?>
    });
</script>