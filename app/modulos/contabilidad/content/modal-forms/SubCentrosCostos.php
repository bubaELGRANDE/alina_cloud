<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
/*
        POST:
        typeOperation
        subCentroCostoId
        tbl = Son 2 tabs, por lo que son 2 datatable diferentes
    */
if ($_POST['typeOperation'] == "update") {
    $dataEditClasificacion = $cloud->row("
            SELECT codigoSubcentroCosto, nombreSubcentroCosto FROM conta_subcentros_costo
            WHERE subCentroCostoId = ? AND flgDelete = ?
        ", [$_POST['subCentroCostoId'], 0]);

    $txtSuccess = "Sub Centro de costo actualizado con éxito.";
} else {
    $txtSuccess = "Sub Centro de costo agregado con éxito.";
}

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
<input type="hidden" id="operation" name="operation" value="sub-centros-costos">
<input type="hidden" id="subCentroCostoId" name="subCentroCostoId" value="<?php echo $_POST['subCentroCostoId']; ?>">
<div class="row">
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-<?php echo $iconInput; ?> trailing"></i>
            <input type="text" id="codigoSubcentroCosto" class="form-control" name="codigoSubcentroCosto" required />
            <label class="form-label" for="tituloClasificacion">Codigo</label>
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-outline mb-4">
            <i class="fas fa-<?php echo $iconInput; ?> trailing"></i>
            <input type="text" id="nombreSubcentroCosto" class="form-control" name="nombreSubcentroCosto" required />
            <label class="form-label" for="tituloClasificacion">Nombre del Sub centro de costo </label>
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
                            $(`#<?php echo $_POST['tblsubCentroDetalle']; ?>`).DataTable().ajax.reload(null, false);

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
            $("#modalTitle").html(`Editar Centro de costo: <?php echo $dataEditClasificacion->nombreSubcentroCosto; ?>`);
            $("#codigoSubcentroCosto").val(`<?php echo $dataEditClasificacion->codigoSubcentroCosto; ?>`)
            $("#nombreSubcentroCosto").val(`<?php echo $dataEditClasificacion->nombreSubcentroCosto; ?>`);
        <?php
        } else {
        ?>
            $("#modalTitle").html(`Nuevo Sub Centro de costo`);
        <?php
        }
        ?>
    });
</script>