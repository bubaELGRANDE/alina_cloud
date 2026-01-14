<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

        $dataEditarExpediente = $cloud->row("
        SELECT 
            p.personaId,p.codEmpleado, cgs.clasifGastoSalarioId, cgs.nombreGastoSalario
            FROM th_expediente_personas exp
            JOIN th_personas p ON p.personaId = exp.personaId
            LEFT JOIN cat_clasificacion_gastos_salario cgs ON cgs.clasifGastoSalarioId = exp.clasifGastoSalarioId
        WHERE exp.flgDelete = ? AND p.personaId = ? 
        ", [0,$_POST["personaId"]]);

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="planilla-info-empleado">
<input type="hidden" id="prsExpedienteId" name="prsExpedienteId" value="<?php echo $_POST['prsExpedienteId']; ?>">
<input type="hidden" id="personaId" name="personaId" value="<?php echo $dataEditarExpediente->personaId; ?>">
<div class="row mb-4">
    <div class="col-md-6">
        <div class="form-outline">
            <i class="fas fa-list-ol trailing"></i>
            <input type="text" id="codEmpleado" class="form-control" name="codEmpleado" required />
            <label class="form-label" for="codEmpleado">Código de empleado</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-select-control">
            <select id="selectClasificacion" name="selectClasificacion" style="width: 100%;" required>
            <option></option>
                <?php 
                    $dataClasificacionGasto = $cloud->rows("
                        SELECT
                            clasifGastoSalarioId,
                            nombreGastoSalario
                        FROM cat_clasificacion_gastos_salario
                        WHERE flgDelete = ?
                    ", [0]);
                    foreach($dataClasificacionGasto as $clasificacionGasto) {
                        echo "<option value='$clasificacionGasto->clasifGastoSalarioId'>$clasificacionGasto->nombreGastoSalario</option>";
                    }
                ?>
            </select>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#selectClasificacion").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Clasificación'
        });
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
                                'Informacion del empleado actualizada con exito',
                                "success"
                            );
                            $(`#tblExpedientesActivos`).DataTable().ajax.reload(null, false);
                            $(`#tblExpedientesInactivos`).DataTable().ajax.reload(null, false);
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

                $("#codEmpleado").val(`<?php echo $dataEditarExpediente->codEmpleado; ?>`);
                $("#selectClasificacion").val(`<?php echo $dataEditarExpediente->clasifGastoSalarioId; ?>`).trigger("change");
    });
</script>