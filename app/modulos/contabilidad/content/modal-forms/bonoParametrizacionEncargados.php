<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="bonos-encargados">
<div class="form-select-control mb-4">
    <select class="form-select" id="personaIdEncargado" name="personaIdEncargado[]" style="width:100%;" class="form-control" multiple="multiple" required>
        <option></option>
        <?php 
        	$dataEncargados = $cloud->rows("
        		SELECT 
        			exp.personaId AS personaId, 
        			exp.nombreCompleto AS nombreCompleto
        		FROM view_expedientes exp
        		WHERE exp.estadoPersona = ? AND exp.estadoExpediente = ? AND exp.personaId NOT IN (
        			SELECT bp.personaId FROM conf_bonos_personas bp
        			WHERE bp.personaId = exp.personaId AND bp.flgDelete = 0
        		)
        		ORDER BY exp.apellido1, exp.apellido2, exp.nombre1, exp.nombre2
        	", ["Activo", "Activo"]);

        	foreach($dataEncargados as $encargado) {
        		echo "<option value='$encargado->personaId'>$encargado->nombreCompleto</option>";
        	}
        ?>
    </select>
</div>
<script>
    $(document).ready(function() {
        $("#personaIdEncargado").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Encargado(s) para la asignación de bonos"
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data.respuesta == "success") {
                            mensaje(
                                "Operación completada:",
                                `Se asignaron ${data.registros} encargados con éxito.`,
                                "success"
                            );
                            $('#tblBonosPersonas').DataTable().ajax.reload(null, false);
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