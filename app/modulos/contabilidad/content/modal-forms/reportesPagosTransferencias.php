<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<input type="hidden" id="extension" name="extension" value="pdf">
<div class="row">
    <div class="col-md-3">
        <div class="form-select-control mb-4">
            <select id="file" name="file" style="width: 100%;" required>
                <option></option>
                <option value="pagos-transferencias-fecha">Pagos por transferencia</option>
            </select>
        </div>
        <div id="divTransferenciaFechaLista">
            <div class="form-outline mb-4">
                <input type="date" id="fechaPagoTransferenciaReporte" name="fechaPagoTransferenciaReporte" class="form-control" required />
                <label class="form-label" for="fechaPagoTransferenciaReporte">Fecha de pago</label>
            </div>
		    <div class="form-select-control mb-4">
		        <select id="pagoTransferenciaIdReporte" name="pagoTransferenciaIdReporte" style="width: 100%;" required>
		            <option></option>
		        </select>
		    </div>
        </div>
    </div>
    <div id="divReporte" class="col-md-9">
    </div>
</div>

<script>
    $(document).ready(function() {
    	$("#divTransferenciaFechaLista").hide();

        $("#file").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de reporte'
        });

        $("#pagoTransferenciaIdReporte").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Transferencia (Monto)'
        });

        $("#file").on("change", function(){
            if($(this).val() == "pagos-transferencias-fecha") {
            	$("#divTransferenciaFechaLista").show();
            } else {
            	$("#divTransferenciaFechaLista").hide();
            }
        });

        $("#fechaPagoTransferenciaReporte").change(function(e) {
            asyncSelect(
                "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectPagosTransferenciasXFecha",
                {
                	fechaPagoTransferencia: $(this).val()
                },
                "pagoTransferenciaIdReporte",
                function() {
			        <?php 
			        	if($_POST["file"] == "pagos-transferencias-fecha") { 
			        ?>
			        		$("#pagoTransferenciaIdReporte").val('<?php echo $_POST["pagoTransferenciaId"]; ?>').trigger('change');
			        		$("#frmModal").submit();
			        <?php 
			        	} else {
			        		// Clic desde Reportes en interfaz general
			        	}
			        ?>
                }
            );
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>reportes", 
                    $("#frmModal").serialize(),
                    function(data) {
                        // Mantener el botón disabled para prevenir que generen más de uno sino carga
                        button_icons("btnModalAccept", "fas fa-print", "Generar reporte", "enabled");
                        $("#divReporte").html(data);
                    }
                );
            }
        });

        <?php 
        	if($_POST["file"] == "pagos-transferencias-fecha") { 
        ?>
        		$("#file").val('<?php echo $_POST["file"]; ?>').trigger('change');
        		$("#fechaPagoTransferenciaReporte").val('<?php echo $_POST["fechaPagoTransferencia"]; ?>').trigger("change");
        <?php  
    		} else {
    			// Clic desde Reportes en interfaz general
    		}
    	?> 
    });
</script>