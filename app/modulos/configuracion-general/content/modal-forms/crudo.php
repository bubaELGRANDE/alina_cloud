<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /* arrayFormData 
        Nuevo = nuevo
        Editar = editar ^ crudId 
    */
    $arrayFormData = explode("^",$_POST['arrayFormData']);
    if ($arrayFormData[0]=="editar") {
        $datCrud = $cloud->row("
            SELECT 
                nombreCrud,
                moduloId,
                descripcion
            FROM ejemplo_crud
            WHERE crudId = ?
        ",[$arrayFormData[1]]);

        $txtSuccess = "Crud actualizado con éxito.";
    }else{
        $txtSuccess = "Crud agregado con éxito";
    }

?>
<input type="hidden" id="typeOperation" name="typeOperation">
<input type="hidden" id="operation" name="operation" value="crud">
<!--<input type="hidden" id="numOrdenSucursal" name="numOrdenSucursal" value="<?php #echo $numOrden; ?>">-->
<div class="form-outline mb-4">
    <i class="fas fa-align-left trailing"></i>
    <input type="text" id="nombreCrud" class="form-control" name="nombreCrud" required />
    <label class="form-label" for="nombreCrud">Nombre CRUD</label>
</div>
<div class="row justify-content-md-center">
    <div class="col-12">
        <div class="form-select-control mb-4">
            <select class="form-select" id="moduloId" name="moduloId" style="width:100%;" required>
                <option></option>
                <?php $dataModulo = $cloud->rows("
                    SELECT moduloId, modulo FROM conf_modulos WHERE flgDelete = 0
                ");
                    foreach($dataModulo as $dataModulo){
                        echo '<option value="'. $dataModulo->moduloId .'">' . $dataModulo->modulo . '</option>';
                    }
                ?>
            </select>
        </div>
    </div> 

</div>
<div class="form-outline mb-4">
    <i class="fas flist-ul trailing"></i>
    <textarea type="text" id="descripcion" class="form-control" name="descripcion" required ></textarea>
    <label class="form-label" for="descripcion">Descripción</label>
</div>

<script>

    $(document).ready(function() {
        $("#moduloId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: "Módulo"
        });
        
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
                            //var tablaUpd = $("#operation").val();
                            $("#tblEjemploCrud").DataTable().ajax.reload(null, false);
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
    });

    <?php if ($arrayFormData[0]=="editar") { ?>
        $("#modalTitle").html('Editar CRUD: <?php echo $datCrud->nombreCrud;?>');
        $("#typeOperation").val('update');
        $("#nombreCrud").val('<?php echo $datCrud->nombreCrud;?>');
        $("#descripcion").val('<?php echo $datCrud->descripcion?>');
        $("#moduloId").val('<?php echo $datCrud->moduloId; ?>').trigger('change');

    <?php }else{ ?>
        $("#typeOperation").val('insert');
        $("#modalTitle").html('Nuevo CRUD');
    <?php } ?>
</script>

