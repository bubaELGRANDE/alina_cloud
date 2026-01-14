<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="nueva-persona-sucursal">

<div class="form-select-control mb-4">
    <select class="form-select" id="selectSucursal" name="selectSucursal[]" multiple="multiple" style="width:100%;" required>
        <option></option>
            <?php
                $sucursales = $cloud->rows("
                    SELECT
                        sucursalId,
                        sucursal
                    FROM cat_sucursales
                    WHERE flgDelete = ? 
                ",[0]);
                foreach ($sucursales as $sucursales) {
                        echo '<option value='.$sucursales->sucursalId.'>'.$sucursales->sucursal.'</option>';
                }
            ?>
    </select>
</div>

<div class="form-select-control">
    <select class="form-select" id="selectPersonas" name="selectPersonas[]" multiple="multiple" style="width:100%;" required>
        <option></option>
        <?php
            $dataPersonaSucursal = $cloud->rows("
            SELECT 
                personaId AS personaId,
                CONCAT(apellido1, ' ', apellido2, ' ', nombre1, ' ', nombre2) AS nombreCompleto 
            FROM th_personas p
            WHERE estadoPersona = ? AND flgDelete = ?
            ORDER BY apellido1, apellido2, nombre1, nombre2
        ", ["Activo", 0]);
    
        foreach($dataPersonaSucursal as $dataPersonaSucursal){
            echo '<option value='.$dataPersonaSucursal->personaId.'>'.$dataPersonaSucursal->nombreCompleto.'</option>';
        }
        ?>
    </select>
</div>

<script>
$(document).ready(function() {
    $("#frmModal").validate({
        submitHandler: function(form) {
            button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
            mensaje_confirmacion(
                `¿Esta seguro que desea agregar las personas a la sucursal?`,
                `Se agregaran a la sucursal.`,
                `warning`,
                (param) => {
                    asyncData(
                        "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/", 
                        $("#frmModal").serialize(),
                        function(data) {
                            button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                            if(data == "success") {
                                mensaje_do_aceptar(
                                    "Operación completada",
                                    'Se agregaron con éxito.',
                                    "success",
                                    function() {
                                        $('#modal-container').modal("hide");
                                        $('#tblPersonaSucursal').DataTable().ajax.reload(null, false);
                                });			
                            } else {
                                mensaje(
                                    "Aviso:",
                                    data,
                                    "warning"
                                );
                            }
                        }
                    );
                },
                `Guardar`,
                `Cancelar`
            )
            
        }
    });
    $("#selectSucursal").select2({
        dropdownParent: $('#modal-container'),
        placeholder: 'Sucursal'
    });

    $("#selectPersonas").select2({
        dropdownParent: $('#modal-container'),
        placeholder: 'Personas'
    });


    /*$("#selectSucursal").change(function(e) {
        asyncSelect(
            `<?php //echo $_SESSION['currentRoute'];?>/content/divs/selectPersonasSucursales`,
            {
                id: $(this).val()
            },
            `selectPersonas`,
            function() {
                
            }
        );
    });*/
});
</script>
