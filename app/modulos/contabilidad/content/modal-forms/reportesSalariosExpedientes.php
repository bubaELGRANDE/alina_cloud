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
                <option value="salariosExpedientes">Salarios de empleados</option>
            </select>
        </div>
        <div id="divFiltrosEmpleado" class="mb-4"> 
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroEmpleados" id="filtroEmpleadosTodos" value="Todos" checked/>
                <label class="form-check-label" for="filtroEmpleadosTodos">Todos los empleados</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroEmpleados" id="filtroEmpleadosEspecifico" value="Especifico" />
                <label class="form-check-label" for="filtroEmpleadosEspecifico">Empleados específicos</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroEmpleados" id="filtroEmpleadosInactivos" value="Inactivos" />
                <label class="form-check-label" for="filtroEmpleadosInactivos">Empleados inactivos</label>
            </div>
        </div>
        <div id="divSelectEmpleados" class="form-select-control mb-4">
            <select id="selectEmpleados" name="selectEmpleados[]" style="width: 100%;" multiple="multiple" required>
                <option></option>
            </select>
        </div>
        <div id="divFlgClasificacion" class="mb-4">
            <label>¿Clasificar empleados?</label>
            <div class="d-flex">
                <label class="form-check-label me-2" for="flgClasificacion" value="no">No</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="flgClasificacion" name="flgClasificacion">
                </div>
                <label class="form-check-label" for="flgClasificacion" value="si">Sí</label>
            </div>
        </div>
        <div id="divFiltroClasificacion" class="mb-4">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroClasificacion" id="filtroClasificacionTodos" value="Todos" checked/>
                <label class="form-check-label" for="filtroClasificacionTodos">Todas las clasificaciones</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroClasificacion" id="filtroClasificacionEspecifico" value="Especifico" />
                <label class="form-check-label" for="filtroClasificacionEspecifico">Clasificaciones específicas</label>
            </div>
        </div>
        <div id="divClasificacion" class="form-select-control mb-4">
            <select id="selectClasificacion" name="selectClasificacion[]" multiple="multiple" style="width: 100%;" required>
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
    <div id="divReporte" class="col-md-9">
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#divFiltrosEmpleado").hide();
        $("#divSelectEmpleados").hide();
        $("#divFlgClasificacion").hide();
        $("#divFiltroClasificacion").hide();
        $("#divClasificacion").hide();

        $("#file").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de reporte'
        });

        $("#selectEmpleados").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Empleado(s)'
        });

        $("#selectClasificacion").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Clasificación(es)'
        });

        $("#file").change(function(e) {
            if ($(this).val() == "salariosExpedientes") {
                $("#divFiltrosEmpleado").show();
                $("#divSelectEmpleados").hide();
                $("#divFlgClasificacion").show();
                $("#divFiltroClasificacion").hide();
                $("#divClasificacion").hide();
            } else {
                $("#divFiltrosEmpleado").hide();
                $("#divSelectEmpleados").hide();
                $("#divFlgClasificacion").hide();
                $("#divFiltroClasificacion").hide();
                $("#divClasificacion").hide();
            }
        });

        $("input[type=radio][name=filtroEmpleados]").change(function(e) {
            if ($(this).val() == "Especifico" || $(this).val() == "Inactivos") {
                $("#divSelectEmpleados").show();
                asyncSelect(
                    `<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarEmpleadosExpediente`,
                    {
                        estadoPersona: ($(this).val() == "Especifico" ? 'Activo' : 'Inactivo')
                    },
                    `selectEmpleados`
                );

                // Deshabilitar el checkbox 
                $("#flgClasificacion").prop("disabled", true);
                $("#flgClasificacion").prop("checked", false);
                $("#divFiltroClasificacion").hide();
                $("#divClasificacion").hide();
            } else {
                $("#divSelectEmpleados").hide();

                // Habilitar el checkbox
                $("#flgClasificacion").prop("disabled", false);
                $("#flgClasificacion").prop("checked", false);
                $("#divFiltroClasificacion").hide();
            }
        });

        $('#flgClasificacion').on('click', function() {
            $("#divFiltroClasificacion").toggle();
            $("#filtroClasificacionTodos").prop("checked", true);
            $("#filtroClasificacionEspecifico").prop("checked", false);
            $("#divClasificacion").hide();
        });

        $("input[type=radio][name=filtroClasificacion]").change(function(e) {
            if ($(this).val() == "Especifico") {
                $("#divClasificacion").show();
            } else {
                // Todos
                $("#divClasificacion").hide();
            }
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>reportes", 
                    $("#frmModal").serialize(),
                    function(data) {
                        // Mantener el botón disabled para prevenir que generen más de uno si no carga
                        button_icons("btnModalAccept", "fas fa-print", "Generar reporte", "enabled");
                        $("#divReporte").html(data);
                    }
                );
            }
        });

        // De momento solo hay un reporte, seleccionarlo para ahorrar un clic
        $("#file").val('salariosExpedientes').trigger('change');
    });
</script>
