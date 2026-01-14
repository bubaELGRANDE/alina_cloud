<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>

<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="categoriasProducto">
<input type="hidden" id="operation" name="productoId" value="<?php echo $_POST["arrayFormData"];?>">

<div id="prodCat" class="row">
    <div class="col-md-12">
        <div class="form-select-control mb-2">
            <select class="form-select" id="categorias" name="categorias[]" style="width:100%;" multiple required>
                <option></option>
                <?php 
                $catsProd = $cloud->rows("
                    SELECT
                        inventarioCategoriaId, 
                        nombreCategoria
                    FROM cat_inventario_categorias
                    WHERE flgDelete = 0");
                    foreach ($catsProd as $categorias){
                        echo '<option value="'.$categorias->inventarioCategoriaId.'">'.$categorias->nombreCategoria.'</option>';
                    }
                    ?>
            </select>
        </div>
        <div class="text-end">
            <a id="toggleCat" class="mb-2 d-inline-block" href="#"><i class="fas fa-plus-circle"></i> Agregar nueva categoría</a>
        </div>
    </div>
</div>
<!-- insertar categoria -->
<div id="newCat" class="row" style="display:none">
    <div class="col-md-12">
        <div class="form-outline mb-4">
            <i class="fas fa-tags trailing"></i>
            <input type="text" id="nombreCategoria" class="form-control" name="nombreCategoria" required>
            <label class="form-label" for="nombreCategoria">Nombre Categoría</label>
        </div>
        <!--<button type="submit" class="btn btn-success btn-sm">
            <i class="fas fa-save"></i></i> Guardar
        </button>-->
        <button id="cancelCat" type="button" class="btn btn-secondary btn-sm">
            <i class="fas fa-times-circle"></i> Cancelar
        </button>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#categorias").select2({
            placeholder: "Seleccionar categorías",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $("#toggleCat, #cancelCat").on('click', function(){
            $("#prodCat").toggle();
            $("#newCat").toggle();

            $("#operation").val($("#operation").val() == 'categoriasProducto' ? 'categoria' : 'categoriasProducto');
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                var operation = $("#operation").val();
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                "Categorías agregadas correctamente.",
                                "success",
                                function() {
                                    if (operation == "categoria"){
                                        $("#operation").val('categoriasProducto');
                                        $('#nombreCategoria').val('');
                                        $("#prodCat").toggle();
                                        $("#newCat").toggle();
                                        $("#modal-container").modal("hide");
                                        modalCategorias('<?php echo $_POST["arrayFormData"]; ?>');
                                    } else{
                                        $('#modal-container').modal("hide");
                                        changePage('<?php echo $_SESSION["currentRoute"]; ?>', 'fichaTecnica', `productoId=<?php echo $_POST["arrayFormData"]; ?>`);
                                    }
                                }
                            );
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
    });
</script>