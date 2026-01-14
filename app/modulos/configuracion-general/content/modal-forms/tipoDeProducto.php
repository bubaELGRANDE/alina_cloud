<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /**
     arrayFormData 
        Nuevo  = insert
        editar = update ^ categoriaId
     */

    $arrayFormData   = explode("^", $_POST["arrayFormData"]);

    $nombreTipoProducto = "";
    $txtSuccess         = "Tipo de producto agregado con éxito.";

    if (!empty($arrayFormData[1])){
        $datTipoProd = $cloud->row("
            SELECT
                tipoProductoId,
                nombreTipoProducto
            FROM cat_inventario_tipos_producto
            WHERE tipoProductoId = $arrayFormData[1] AND flgDelete = 0
        ");

        $nombreTipoProducto = 'value="'.$datTipoProd->nombreTipoProducto.'"';
        $txtSuccess         = "Tipo de producto editado con éxito";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $arrayFormData[0]; ?>">
<input type="hidden" id="operation" name="operation" value="tipo-producto">
<?php if (!empty($arrayFormData[1])){ ?>
<input type="hidden" id="tipoProductoId" name="tipoProductoId" value="<?php echo $datTipoProd->tipoProductoId;?>">
<?php } ?>

<div class="row">
    <div class="col-md-12">
        <div class="form-outline mb-4">
            <i class="fas fa-tags trailing"></i>
            <input type="text" id="nombreTipoProducto" class="form-control" name="nombreTipoProducto" <?php echo $nombreTipoProducto;?> required>
            <label class="form-label" for="nombreTipoProducto">Nombre tipo de producto</label>
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
                                '<?php echo $txtSuccess;?>',
                                "success"
                            );
                            var tblTipoProducto = $("#operation").val();
                            $("#tblTipoProducto").DataTable().ajax.reload(null, false);
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
            if(!empty($arrayFormData[1])) {
        ?>
            $("#modalTitle").html('Editar Tipo de producto: <?php echo $datTipoProd->nombreTipoProducto; ?>');
        <?php 
            } 
        ?>

    });
</script>