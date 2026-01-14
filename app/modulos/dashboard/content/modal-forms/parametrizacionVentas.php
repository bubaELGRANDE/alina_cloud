<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    // arrayFormData = typeOperation ^ dashParamId ^ tipoParametrizacion
    $arrayFormData = explode("^", $_POST['arrayFormData']);
    $dashParamId = $arrayFormData[1];
    $tipoParametrizacion = ($arrayFormData[2] == "udn" ? "unidad de negocio" : $arrayFormData[2]);
    $tblParam = "tblParam" . ($arrayFormData[2] == "udn" ? strtoupper($arrayFormData[2]) : ucfirst($arrayFormData[2]));

    if($arrayFormData[0] == "editar") {
        $dataEditParametrizacion = $cloud->row("
            SELECT tituloParametrizacion, colorParametrizacion FROM dash_parametrizacion
            WHERE dashParamId = ? AND flgDelete = ?
        ", [$dashParamId, 0]);

        $txtSuccess = "Parametrización actualizada con éxito.";
    } else {
        $txtSuccess = "Parametrización agregada con éxito.";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation">
<input type="hidden" id="operation" name="operation" value="parametrizacion-ventas">
<input type="hidden" id="dashParamId" name="dashParamId" value="<?php echo $dashParamId; ?>">
<input type="hidden" id="tipoParametrizacion" name="tipoParametrizacion" value="<?php echo $tipoParametrizacion; ?>">
<div class="row">
    <div class="col-9">
        <div class="form-outline mb-4">
            <i class="fas fa-users-cog trailing"></i>
            <input type="text" id="tituloParametrizacion" class="form-control" name="tituloParametrizacion" required />
            <label class="form-label" for="tituloParametrizacion">Nombre de la <?php echo strtolower($tipoParametrizacion); ?></label>
        </div>
    </div>
    <div class="col-3">
        <div class="form-outline mb-4">
            <i class="fas fa-palette trailing"></i>
            <input type="color" id="colorParametrizacion" class="form-control form-control-color" name="colorParametrizacion" value="#003561" required />
            <label class="form-label" for="colorParametrizacion">Color</label>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
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
                                '<?php echo $txtSuccess; ?>',
                                "success"
                            );
                            $(`#<?php echo $tblParam; ?>`).DataTable().ajax.reload(null, false);
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
                $("#modalTitle").html(`Editar parametrización - <?php echo ucfirst($tipoParametrizacion); ?>: <?php echo $dataEditParametrizacion->tituloParametrizacion; ?>`);
                $("#tituloParametrizacion").val('<?php echo $dataEditParametrizacion->tituloParametrizacion; ?>');
                $("#colorParametrizacion").val('<?php echo $dataEditParametrizacion->colorParametrizacion; ?>');
        <?php
            } else {
        ?>
                $("#typeOperation").val('insert');
                $("#modalTitle").html(`Nueva parametrización: <?php echo ucfirst($tipoParametrizacion); ?>`);
        <?php 
            }
        ?>
    });
</script>