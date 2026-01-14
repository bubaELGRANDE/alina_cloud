<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
/*
        POST:
        typeOperation
        centroCostoId
        tbl = Son 2 tabs, por lo que son 2 datatable diferentes
    */
if ($_POST['typeOperation'] == "update") {
    $dataEditClasificacion = $cloud->row("
            SELECT codigoCentroCosto, nombreCentroCosto FROM conta_centros_costo
            WHERE centroCostoId = ? AND flgDelete = ?
        ", [$_POST['centroCostoId'], 0]);

    $txtSuccess = "Centro de costo actualizado con éxito.";
} else {
    $txtSuccess = "Centro de costo agregado con éxito.";
}

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
<input type="hidden" id="operation" name="operation" value="centros-costos">
<input type="hidden" id="centroCostoId" name="centroCostoId" value="<?php echo $_POST['centroCostoId']; ?>">
<div class="row">
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-<?php echo $iconInput; ?> trailing"></i>
            <input type="text" id="codigoCentroCosto" class="form-control" name="codigoCentroCosto" required />
            <label class="form-label" for="tituloClasificacion">Codigo</label>
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-outline mb-4">
            <i class="fas fa-<?php echo $iconInput; ?> trailing"></i>
            <input type="text" id="nombreCentroCosto" class="form-control" name="nombreCentroCosto" required />
            <label class="form-label" for="tituloClasificacion">Nombre de centro de costo </label>
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
                                '<?php echo $txtSuccess; ?>',
                                "success"
                            );
                            $('#modal-container').modal("hide");
                            $(`#<?php echo $_POST['tblCentroCosto']; ?>`).DataTable().ajax.reload(null, false);

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
            $("#modalTitle").html(`Editar Centro de costo: <?php echo $dataEditClasificacion->nombreCentroCosto; ?>`);
            $("#codigoCentroCosto").val(`<?php echo $dataEditClasificacion->codigoCentroCosto; ?>`)
            $("#nombreCentroCosto").val(`<?php echo $dataEditClasificacion->nombreCentroCosto; ?>`);
        <?php
        } else {
        ?>
            $("#modalTitle").html(`Nuevo Centro de costo`);
        <?php
        }
        ?>
    });
</script>