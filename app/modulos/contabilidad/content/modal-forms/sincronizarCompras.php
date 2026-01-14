<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<input type="hidden" id="typeOperationModal" name="typeOperation" value="insert">
<input type="hidden" id="operationModal" name="operation" value="sincronizacion-magic-sincronizar">
<input type="hidden" id="bitExportacionMagicId" name="bitExportacionMagicId" value="<?php echo $_POST['bitExportacionMagicId']; ?>">
<input type="hidden" id="descripcionExportacion" name="descripcionExportacion" value="<?php echo $_POST['descripcionExportacion']; ?>">
<input type="hidden" id="estadoExportacion" name="estadoExportacion" value="<?php echo $_POST['estadoExportacion']; ?>">
<div class="mb-4">
	<b>Nota: </b> Las Compras que agregó serán enviadas y consolidadas en la base de datos de Magic. Una vez realizada, cualquier modificación o edición, deberá realizarse en dicho sistema.
</div>
<!-- Esta modal tenía estos inputs, pero por modificaciones en los formularios ya no aplican -->
<!-- De momento, no se usa esta modal, se cambió por una sweet alert de confirmación -->
<!--  
<div class="form-outline mb-4">
    <input type="date" id="fechaDeclaracionMagic" name="fechaDeclaracionMagic" class="form-control" required />
    <label class="form-label" for="fechaDeclaracionMagic">Fecha de declaración</label>
</div>
<div class="form-outline mb-4">
    <i class="fas fa-calendar-day trailing"></i>
    <input type="number" id="semanaContabilidadMagic" class="form-control" name="semanaContabilidadMagic" min="0" step="1" required />
    <label class="form-label" for="semanaContabilidadMagic">Semana de declaración</label>
</div>
-->
<script>
	$(document).ready(function() {
        $("#frmModal").validate({
            submitHandler: function(form) {
            	button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    $("#frmModal").serialize(),
                    function(data) {
                    	button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje_do_aceptar(
                        		`Operación completada:`, 
                        		`Compras sincronizadas en Magic con éxito`, 
                        		`success`, 
                        		function() {
                                	$('#tblSincronizacionCompras').DataTable().ajax.reload(null, false);
                                	$('#modal-container').modal("hide");
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