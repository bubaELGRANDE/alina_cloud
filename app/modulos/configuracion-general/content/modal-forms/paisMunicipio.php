<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $paisMunicipioId = 0;

    if ($_POST['typeOperation'] == "update") {
        $paisMunicipioId = $_POST['paisMunicipioId'];

        $dataMunicipio = $cloud->row("
            SELECT
            paisMunicipioId,municipioPais, codigoMH
            FROM cat_paises_municipios
            WHERE paisMunicipioId = ? AND flgDelete = ?
        ", [$_POST['paisMunicipioId'], 0]);
    } else {
        // Fue insert
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="municipio">
<input type="hidden" id="paisMunicipioId" name="paisMunicipioId" value="<?php echo $paisMunicipioId;?>">
<input type="hidden" id="paisDepartamentoId" name="paisDepartamentoId" value="<?php echo $_POST['paisDepartamentoId'];?>">
<div class="form-outline mb-4">
    <i class="fa fa-globe-americas trailing"></i>
    <input type="text" id="municipioPais" name="municipioPais" class="form-control" required>
    <label class="form-label" for="municipioPais">Nombre del municipio</label>
</div>
<div class="form-outline mb-4">
    <i class="fa fa-globe-americas trailing"></i>
    <input type="text" id="codigoMH" name="codigoMH" class="form-control" required>
    <label class="form-label" for="codigoMH">Cód. Hacienda</label>
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
                                `Municipio ${$("#typeOperation").val() == 'insert' ? 'agregado' : 'actualizado'} con éxito.`,
                                "success"
                            );
                            $(`#tblMunicipios`).DataTable().ajax.reload(null, false);
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
                $("#municipioPais").val('<?php echo $dataMunicipio->municipioPais; ?>'); 
                $("#codigoMH").val('<?php echo $dataMunicipio->codigoMH; ?>'); 

        <?php
            } else {
                // Fue insert, ya se especificó el título de la modal
            }
        ?>
    });
</script>
