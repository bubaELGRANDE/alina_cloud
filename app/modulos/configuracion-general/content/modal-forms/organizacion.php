<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /* arrayFormData 
        Nuevo = nuevo
        Editar = editar ^ nombreOrganizacionId 
    */
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    if($arrayFormData[0] == "editar") {
        $dataEditOrganizacion = $cloud->row("
            SELECT
                tipoOrganizacion, 
                nombreOrganizacion, 
                abreviaturaOrganizacion
            FROM cat_nombres_organizaciones
            WHERE nombreOrganizacionId = ?
        ", [$arrayFormData[1]]);
        $txtSuccess = "Organización actualizada con éxito";
    } else {
        $txtSuccess = "Organización agregada con éxito.";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation">
<input type="hidden" id="operation" name="operation" value="organizacion">
<input type="hidden" id="tipoOrganizacionHidden" name="tipoOrganizacionHidden" value="0">
<div class="form-select-control mb-4">
    <select id="tipoOrganizacion" name="tipoOrganizacion" style="width: 100%;" required>
        <option></option>
        <?php 
            $tipoOrganizacion = array("AFP","Seguro médico","Banco");
            for ($i=0; $i < count($tipoOrganizacion); $i++) { 
                echo '<option value="'.$tipoOrganizacion[$i].'">'.$tipoOrganizacion[$i].'</option>';
            }
        ?>
    </select>
</div>
<div class="form-outline form-hidden-update mb-4">
    <i class="fas fa-university trailing"></i>
    <input type="text" id="nombreOrganizacion" class="form-control" name="nombreOrganizacion" required />
    <label class="form-label" for="nombreOrganizacion">Nombre de la organización</label>
</div> 
<div class="form-outline form-hidden-update mb-4">
    <i class="fas fa-tag trailing"></i>
    <input type="text" id="abreviaturaOrganizacion" class="form-control" name="abreviaturaOrganizacion" required />
    <label class="form-label" for="abreviaturaOrganizacion">Abreviatura de la organización</label>
</div> 
<script>
    $(document).ready(function() {
        $("#tipoOrganizacion").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de organización'
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
                                '<?php echo $txtSuccess; ?>',
                                "success"
                            );
                            $('#tblOrganizaciones').DataTable().ajax.reload(null, false);
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
                $("#modalTitle").html('Editar Organización: <?php echo $dataEditOrganizacion->nombreOrganizacion . " (" . $dataEditOrganizacion->abreviaturaOrganizacion . ")"; ?>');
                $("#tipoOrganizacion").val('<?php echo $dataEditOrganizacion->tipoOrganizacion; ?>').trigger('change');
                $("#tipoOrganizacionHidden").val($("#tipoOrganizacion").val());
                $("#tipoOrganizacion").prop("disabled", true);
                $("#nombreOrganizacion").val('<?php echo $dataEditOrganizacion->nombreOrganizacion; ?>');
                $("#abreviaturaOrganizacion").val('<?php echo $dataEditOrganizacion->abreviaturaOrganizacion; ?>');
        <?php 
            } else {
        ?>
                $("#typeOperation").val('insert');
                $("#modalTitle").html('Nueva Organización');
        <?php 
            }
        ?>
    });
</script>
