<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="empleado-organigrama">

<div class="masEmpleados">
    <div class="row">
        <div class="col-md-12">
            <div class="form-select-control mb-4">
                <select id="persona" name="persona[]" style="width:100%;" multiple="multiple" required>
                    <option></option>
                    <?php 
                        $dataPersonas = $cloud->rows("
                        SELECT
                        exp.prsExpedienteId,
                            per.personaId, 
                            CONCAT(
                                IFNULL(per.apellido1, '-'),
                                ' ',
                                IFNULL(per.apellido2, '-'),
                                ', ',
                                IFNULL(per.nombre1, '-'),
                                ' ',
                                IFNULL(per.nombre2, '-')
                            ) AS nombreCompleto
                        FROM th_expediente_personas exp
                        JOIN th_personas per ON exp.personaId = per.personaId
                        WHERE exp.flgDelete = '0' AND exp.estadoExpediente = 'Activo' AND exp.prsExpedienteId NOT IN (
                            SELECT
                                prsExpedienteId
                            FROM th_expediente_organigrama
                            WHERE flgDelete = '0'
                        )
                        ORDER BY apellido1, apellido2, nombre1, nombre2
                        ");
                        foreach ($dataPersonas as $dataPersonas) {
                            echo '<option value="'.$dataPersonas->prsExpedienteId.'">'.$dataPersonas->nombreCompleto.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-6"></div>
    </div>
</div>


<script>

    $(document).ready(function() {
        Maska.create('#frmModal .masked');
        $("#persona").select2({
            placeholder: "Empleados",
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