<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    // arrayFormData = comisionPagarPeriodoId
?>
<!-- extension puede ser un select en un futuro, por si se necesita cambiar el formato -->
<input type="hidden" id="extension" name="extension" value="pdf">
<input type="hidden" id="comisionPagarPeriodoId" name="comisionPagarPeriodoId" value="<?php echo $_POST['arrayFormData']; ?>">
<div class="row">
    <div class="col-md-3">
        <div class="form-select-control mb-4">
            <select id="file" name="file" style="width: 100%;" required>
                <option></option>
                <option value="parametrizacionComisiones">Parametrización de comisiones</option>
            </select>
        </div>
        <div id="divFiltroLineas" class="form-select-control mb-4">
            <select id="filtroLineas" name="filtroLineas" style="width: 100%;" required>
                <option></option>
                <option value="Todos">Todas las líneas</option>
                <option value="Especifico">Línea(s) específica(s)</option>
            </select>
        </div>
        <div id="divLineaId" class="form-select-control mb-4">
            <select id="lineaId" name="lineaId[]" style="width: 100%;" multiple="multiple" required>
                <option></option>
                <?php 
                    $dataLineas = $cloud->rows("
                        SELECT 
                            l.lineaId AS lineaId,
                            CONCAT('(', l.abreviatura, ') ', l.linea) AS nombreLinea
                        FROM conta_comision_porcentaje_lineas pl
                        JOIN temp_cat_lineas l ON pl.lineaId = l.lineaId
                        WHERE pl.flgDelete = ?
                        GROUP BY pl.lineaId
                        ORDER BY l.abreviatura
                    ", ['0']);
                    foreach ($dataLineas as $dataLineas) {
                        echo '<option value="'.$dataLineas->lineaId.'">'.$dataLineas->nombreLinea.'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
	<div id="divReporte" class="col-md-9">
	</div>
</div>
<script>
	$(document).ready(function() {
        $("#divFiltroLineas").hide();
        $("#divLineaId").hide();

        $("#file").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de reporte'
        });

        $("#filtroLineas").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Filtro por línea'
        });

        $("#lineaId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Línea(s)'
        });

        $("#file").change(function(e) {
            if($(this).val() == "parametrizacionComisiones") {
                $("#divFiltroLineas").show();
            } else {
                $("#divFiltroLineas").hide();
                $("#divLineaId").hide();
            }
        });

        $("#filtroLineas").change(function(e) {
            if($(this).val() == "Especifico") {
                $("#divLineaId").show();
            } else {
                $("#divLineaId").hide();
            }
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
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

        // De momento solo hay un reporte, seleccionarlo para ahorrar un clic
        $("#file").val('parametrizacionComisiones').trigger('change');
	});
</script>