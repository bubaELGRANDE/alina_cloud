<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
        POST:
        typeOperation
        comisionClasificacionId
        tipoClasificacion
        tbl = Son 2 tabs, por lo que son 2 datatable diferentes
    */
    if($_POST['typeOperation'] == "update") {
        $dataEditClasificacion = $cloud->row("
            SELECT tituloClasificacion FROM conta_comision_reporte_clasificacion
            WHERE comisionClasificacionId = ? AND flgDelete = ?
        ", [$_POST['comisionClasificacionId'], 0]);

        $txtSuccess = "Clasificación actualizada con éxito.";
    } else {
        $txtSuccess = "Clasificación agregada con éxito.";
    }
    $iconInput = ($_POST['tipoClasificacion'] == "Línea" ? 'trademark' : 'building');
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
<input type="hidden" id="operation" name="operation" value="comision-clasificacion">
<input type="hidden" id="comisionClasificacionId" name="comisionClasificacionId" value="<?php echo $_POST['comisionClasificacionId']; ?>">
<input type="hidden" id="tipoClasificacion" name="tipoClasificacion" value="<?php echo $_POST['tipoClasificacion']; ?>">
<div class="form-outline mb-4">
    <i class="fas fa-<?php echo $iconInput; ?> trailing"></i>
    <input type="text" id="tituloClasificacion" class="form-control" name="tituloClasificacion" required />
    <label class="form-label" for="tituloClasificacion">Nombre de la <?php echo $_POST['tipoClasificacion']; ?></label>
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
                            $(`#<?php echo $_POST['tblClasif']; ?>`).DataTable().ajax.reload(null, false);
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
                $("#modalTitle").html(`Editar clasificación - <?php echo $_POST['tipoClasificacion']; ?>: <?php echo $dataEditClasificacion->tituloClasificacion; ?>`);
                $("#tituloClasificacion").val(`<?php echo $dataEditClasificacion->tituloClasificacion; ?>`);
        <?php
            } else {
        ?>
                $("#modalTitle").html(`Nueva clasificación: <?php echo $_POST['tipoClasificacion']; ?>`);
        <?php 
            }
        ?>
    });
</script>