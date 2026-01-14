<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

	/*
		POST:
		bonoPersonaId
		cuentaBonoId
		nombreCompleto
	*/
?>
<input type="hidden" id="typeOperationModal" name="typeOperation" value="update">
<input type="hidden" id="operationModal" name="operation" value="bonos-encargados-cuenta">
<input type="hidden" id="bonoPersonaIdModal" name="bonoPersonaId" value="<?php echo $_POST['bonoPersonaId']; ?>">
<input type="hidden" id="nombreCompletoModal" name="nombreCompleto" value="<?php echo $_POST['nombreCompleto']; ?>">
<div class="form-select-control">
    <select class="form-select" id="cuentaBonoIdModal" name="cuentaBonoId" style="width:100%;" class="form-control" required>
        <option></option>
        <?php 
        	$dataCuentas = $cloud->rows("
        		SELECT
        			cuentaBonoId, 
        			numCuentaContable, 
        			nombreCuentaContable, 
        			obsCuentaContable
        		FROM conta_cuentas_bonos
        		WHERE flgDelete = ?
        		ORDER BY obsCuentaContable
        	", [0]);

        	foreach($dataCuentas as $cuenta) {
        		echo "<option value='$cuenta->cuentaBonoId' ".($_POST['cuentaBonoId'] == $cuenta->cuentaBonoId ? "selected" : "").">$cuenta->obsCuentaContable</option>";
        	}
        ?>
    </select>
</div>
<script>
    $(document).ready(function() {
        $("#cuentaBonoIdModal").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Cuenta/División"
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
                                `Cuenta asignada al encargado como principal con éxito.`,
                                "success"
                            );
                            $('#tblBonosPersonas').DataTable().ajax.reload(null, false);
                            $("#cuentaBonoIdModal").val('').trigger("change");
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