<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
        POST:
        typeOperation
        tituloModal
        catPlanillaDescuentoId
        catPlanillaDescuentoIdSuperior
    */
    if($_POST['typeOperation'] == "update") {
        $dataEditDescuento = $cloud->row("
            SELECT
                nombreDescuento, 
                codigoContable, 
                catPlanillaDescuentoIdSuperior
            FROM cat_planilla_descuentos
            WHERE catPlanillaDescuentoId = ? AND flgDelete = ?
        ", [$_POST['catPlanillaDescuentoId'], 0]);

        $txtSuccess = "Descuento actualizado con éxito.";
    } else {
        $txtSuccess = "Descuento agregado con éxito.";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
<input type="hidden" id="operation" name="operation" value="parametrizacion-descuento">
<input type="hidden" id="catPlanillaDescuentoId" name="catPlanillaDescuentoId" value="<?php echo $_POST['catPlanillaDescuentoId']; ?>">
<input type="hidden" id="catPlanillaDescuentoIdSuperior" name="catPlanillaDescuentoIdSuperior" value="<?php echo $_POST['catPlanillaDescuentoIdSuperior']; ?>">
<div class="row mb-4">
    <div class="col-md-8">
        <div class="form-outline">
            <i class="fas fa-money-check-alt trailing"></i>
            <input type="text" id="nombreDescuento" class="form-control" name="nombreDescuento" required />
            <label class="form-label" for="nombreDescuento">Nombre del Descuento</label>
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
                            $(`#tblOtrosDescuentos`).DataTable().ajax.reload(null, false);
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
                $("#nombreDescuento").val(`<?php echo $dataEditDescuento->nombreDescuento; ?>`);
                $("#codigoContable").val(`<?php echo $dataEditDescuento->codigoContable; ?>`);
        <?php
            } else {
                // Ya se especifico el titulo de la modal
            }
        ?>
    });
</script>