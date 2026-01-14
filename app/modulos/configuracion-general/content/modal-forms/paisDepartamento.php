<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $paisDepartamentoId = 0;

    if ($_POST['typeOperation'] == "update") {
        $paisDepartamentoId = $_POST['paisDepartamentoId'];

        $dataDepartamento = $cloud->row("
            SELECT
            paisDepartamentoId,departamentoPais, codigoMH
            FROM cat_paises_departamentos
            WHERE paisDepartamentoId = ? AND flgDelete = ?
        ", [$_POST['paisDepartamentoId'], 0]);
    } else {
        // Fue insert
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="departamento">
<input type="hidden" id="paisDepartamentoId" name="paisDepartamentoId" value="<?php echo $paisDepartamentoId;?>">
<input type="hidden" id="paisId" name="paisId" value="<?php echo $_POST['paisId'];?>">
<div class="row">
    <div class="form-outline mb-4">
        <i class="fa fa-globe-americas trailing"></i>
        <input type="text" id="departamentoPais" name="departamentoPais" class="form-control" required>
        <label class="form-label" for="departamentoPais">Nombre del departamento</label>
    </div>
    <div class="form-outline mb-4">
        <i class="fa fa-globe-americas trailing"></i>
        <input type="num" id="codigoMH" name="codigoMH" class="form-control" required>
        <label class="form-label" for="codigoMH">Cod. MH </label>
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
                                `Departamento ${$("#typeOperation").val() == 'insert' ? 'agregado' : 'actualizado'} con éxito.`,
                                "success"
                            );
                            $(`#tblDepartamentos`).DataTable().ajax.reload(null, false);
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
                $("#departamentoPais").val('<?php echo $dataDepartamento->departamentoPais; ?>'); 
                $("#codigoMH").val('<?php echo $dataDepartamento->codigoMH; ?>'); 


        <?php
            } else {
                // Fue insert, ya se especificó el título de la modal
            }
        ?>
    });
</script>
