<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /* arrayFormData 
        Nuevo = nuevo
        Editar = editar ^ catPrsRelacionId 
    */
    $arrayFormData = explode("^", $_POST["arrayFormData"]);
    if($arrayFormData[0] == "editar") {
        $dataEditRelacion = $cloud->row("
        SELECT
        tipoPrsRelacion FROM cat_personas_relacion WHERE catPrsRelacionId = ?
        ", [$arrayFormData[1]]);
        $txtSuccess = "La relación " . $dataEditRelacion->tipoPrsRelacion . " ha sido actualizada con éxito";
        $idRelacion = $arrayFormData[1];
    } else {
        $txtSuccess = "Relación agregada con éxito.";
        $idRelacion = 0;
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation">
<input type="hidden" id="operation" name="operation" value="tipoRelacion">
<input type="hidden" id="idRelacion" name="idRelacion" value="<?php echo $idRelacion; ?>">

<div class="form-outline form-hidden-update mb-4">
    <i class="fas fa-users trailing"></i>
    <input type="text" id="nombreRelacion" class="form-control" name="nombreRelacion" required />
    <label id="labelNombreRelacion" class="form-label" for="nombreRelacion">Nombre de relación</label>
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
                            $('#tblRelaciones').DataTable().ajax.reload(null, false);
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
                $("#modalTitle").html('Editar relación: <?php echo $dataEditRelacion->tipoPrsRelacion; ?>');
                $("#nombreRelacion").val('<?php echo $dataEditRelacion->tipoPrsRelacion; ?>');
        <?php 
            } else {
        ?>
                $("#typeOperation").val('insert');
                $("#modalTitle").html('Nueva Relación');
        <?php 
            }
        ?>
    });
</script>
