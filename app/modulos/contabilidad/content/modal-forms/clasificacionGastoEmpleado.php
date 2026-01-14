<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
        POST:
        typeOperation
        tituloModal
        clasifGastoSalarioId
    */
    if($_POST['typeOperation'] == "update") {
        $dataEditClasificacionGastosEmpleado = $cloud->row("
            SELECT
            nombreGastoSalario 
            FROM cat_clasificacion_gastos_salario
            WHERE clasifGastoSalarioId = ? AND flgDelete = ?
        ", [$_POST['clasifGastoSalarioId'], 0]);

        $txtSuccess = "Clasificacion empleado actualizado con éxito.";
    } else {
        $txtSuccess = "Clasificacion empleado agregado con éxito.";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="clasificacion-gasto">
<input type="hidden" id="clasifGastoSalarioId" name="clasifGastoSalarioId" value="<?php echo $_POST['clasifGastoSalarioId']; ?>">

<div class="form-outline mb-4">
    <i class="fas fa-tag trailing"></i>
    <input type="text" id="descGasto" class="form-control" name="descGasto" value="" required/>
    <label class="form-label" for="descGasto">Descripción de gasto</label>
</div>

<script>
    $(document).ready(function() {
        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                `Clasificación ${$("#typeOperation").val() == 'insert' ? 'agregada' : 'actualizada'} con éxito.`,
                                "success"
                            );
                            $("#tblClasiEmp").DataTable().ajax.reload(null, false);
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
                $("#typeOperation").val("update");
                $("#descGasto").val(`<?php echo $dataEditClasificacionGastosEmpleado->nombreGastoSalario; ?>`);
        <?php
            } else {
                // Ya se especifico el titulo de la modal
            }
        ?>
    });
</script>