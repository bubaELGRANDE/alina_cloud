<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="empleado-organigrama">
<input type="hidden" id="idExpOrg" name="idExpOrg" value="<?php echo $_POST["arrayFormData"]; ?>">

<div class="masEmpleados">
    <div class="row">
        <div class="col-md-12">
            <div class="form-select-control">
                <select id="rama" name="rama" style="width:100%;" required>
                    <option></option>
                    <?php 
                        $dataOrganigrama = $cloud->rows("
                        SELECT 
                            organigramaRamaId,
                            organigramaRama
                        FROM cat_organigrama_ramas
                        WHERE flgDelete = '0'
                        ");

                        foreach ($dataOrganigrama as $ramaOrganigrama) {
                        echo '<option value="'.$ramaOrganigrama->organigramaRamaId.'">'.$ramaOrganigrama->organigramaRama .'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
    </div>
</div>


<script>

    $(document).ready(function() {
        Maska.create('#frmModal .masked');
        $("#rama").select2({
            placeholder: "Nueva rama",
            dropdownParent: $('#modal-container'),
            allowClear: true
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
                                "Empleados agregados exitosamente.",
                                "success"
                            );
                            $('#tblOrganigramaRama').DataTable().ajax.reload(null, false);
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
</script>