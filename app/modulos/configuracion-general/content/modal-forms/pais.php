<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $paisId = 0;

    if ($_POST['typeOperation'] == "update") {
        $paisId = $_POST['paisId'];

        $dataPais = $cloud->row("
            SELECT
                paisId, pais, abreviaturaPais, telefonoCodPais, iconBandera, codigoMH
            FROM cat_paises
            WHERE paisId = ? AND flgDelete = ?
        ", [$_POST['paisId'], 0]);
    } else {
        // Fue insert
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="pais">
<input type="hidden" id="paisId" name="paisId" value="<?php echo $paisId;?>">
<div class="row">
    <div class="col-md-8">
        <div class="form-outline mb-4">
            <i class="fa fa-globe-americas trailing"></i>
            <input type="text" id="pais" name="pais" class="form-control" required>
            <label class="form-label" for="pais">Nombre del país</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fa fa-tag trailing"></i>
            <input type="text" id="abreviaturaPais" name="abreviaturaPais" class="form-control" required>
            <label class="form-label" for="abreviaturaPais">Abreviatura</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fa fa-link trailing"></i>
            <input type="text" id="iconBandera" name="iconBandera" class="form-control">
            <label class="form-label" for="iconBandera">URL bandera</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fa fa-phone-alt trailing"></i>
            <input type="text" id="telefonoCodPais" name="telefonoCodPais" class="form-control" required>
            <label class="form-label" for="telefonoCodPais">Cód. teléfono</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fa fa-phone-alt trailing"></i>
            <input type="num" id="codigoMH" name="codigoMH" class="form-control" required>
            <label class="form-label" for="codigoMH">Cód. MH</label>
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
                        if (data == "success") {
                            mensaje(
                                "Operación completada:",
                                `País ${$("#typeOperation").val() == 'insert' ? 'agregado' : 'actualizado'} con éxito.`,
                                "success"
                            );
                            $(`#tblPaises`).DataTable().ajax.reload(null, false);
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
            if ($_POST['typeOperation'] == "update") {
        ?>
                $("#typeOperation").val("update");
                $("#pais").val('<?php echo $dataPais->pais; ?>');
                $("#abreviaturaPais").val('<?php echo $dataPais->abreviaturaPais; ?>');
                $("#iconBandera").val('<?php echo $dataPais->iconBandera; ?>');
                $("#telefonoCodPais").val('<?php echo $dataPais->telefonoCodPais; ?>');
                $("#codigoMH").val('<?php echo $dataPais->codigoMH; ?>');
        <?php
            } else {
                // Fue insert, ya se especificó el título de la modal
            }
        ?>
    });
</script>
