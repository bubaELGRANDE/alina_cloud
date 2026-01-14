<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<input type="hidden" id="extension" name="extension" value="pdf">
<div class="row">
    <div class="col-md-3">
        <div class="form-select-control mb-4" id="divIncapacidades">
            <select id="file" name="file" style="width: 100%;" required>
                <option></option>
                <option value="incapacidades-por-riesgo">Incapacidades por riesgo</option>
            </select>
        </div>
        <div class="form-select-control mb-4" id="divFiltroIncapacidades">
            <select id="filtroIncapacidades" name="filtroIncapacidades" style="width: 100%;" required>
                <option></option>
                <option value="Sucursales">Sucursales</option>
                <option value="Empleados">Empleado</option>
            </select>
        </div>
        <div id="divInputRadios" class="form-select-control mb-4">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroSucursal" id="filtroSucursalTodas" value="Todas" checked/>
                <label class="form-check-label" for="filtroSucursalTodas">Todas las sucursales</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroSucursal" id="filtroSucursalEspecificas" value="Especificas" />
                <label class="form-check-label" for="filtroSucursalEspecificas">Sucursales especificas</label>
            </div>
        </div>
        <div id="divInputRadioEmpleados" class="form-select-control mb-4">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroEmpleado" id="filtroTodosfiltroEmpleado" value="Todos" checked/>
                <label class="form-check-label" for="filtroTodosfiltroEmpleado">Todos los Empleados</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroEmpleado" id="filtroEspecificoEmpleado" value="Especificos" />
                <label class="form-check-label" for="filtroEspecificoEmpleado">Empleados Especificos</label>
            </div>            
        </div>
        <div id="divClasificacion" class="form-select-control mb-4">
            <select id="selectSucursalesEspecificas" name="selectSucursalesEspecificas[]" multiple="multiple" style="width: 100%;" required>
                <?php 
                    $dataSucursalesespecificas = $cloud->rows("
                        SELECT
                            sucursalId,
                            sucursal
                        FROM cat_sucursales
                        WHERE flgDelete = ?
                    ", [0]);
                    foreach($dataSucursalesespecificas as $SucursalesEspecificas) {
                        echo "<option value='$SucursalesEspecificas->sucursalId'>$SucursalesEspecificas->sucursal</option>";
                    }
                ?>
            </select>
        </div>
        <div id="divClasificacionEmpleados" class="form-select-control mb-4">
            <select id="selectEmpleadosEspecificos" name="selectEmpleadosEspecificos[]" multiple="multiple" style="width: 100%;" required>
                <?php 
                    $dataEmpleadosEspecificos = $cloud->rows("
                        SELECT
                            inc.expedienteId AS expedienteId,
                            CONCAT(
                                IFNULL(pers.apellido1, '-'),
                                ' ',
                                IFNULL(pers.apellido2, '-'),
                                ', ',
                                IFNULL(pers.nombre1, '-'),
                                ' ',
                                IFNULL(pers.nombre2, '-')
                            ) AS nombreCompleto
                        FROM th_expediente_incapacidades inc
                        JOIN th_expediente_personas exp ON exp.prsExpedienteId = inc.expedienteId
                        JOIN th_personas pers ON pers.personaId = exp.personaId
                        WHERE pers.prsTipoId = ? AND inc.flgDelete = ? AND exp.estadoExpediente = ? AND pers.estadoPersona = ?
                        GROUP BY inc.expedienteId
                        ORDER BY apellido1, apellido2, nombre1, nombre2
                    ", [1, 0, 'Activo', 'Activo']);
                    foreach($dataEmpleadosEspecificos as $EmpleadosEspecificos) {
                        echo "<option value='$EmpleadosEspecificos->expedienteId'>$EmpleadosEspecificos->nombreCompleto</option>";
                    }
                ?>
            </select>
        </div>
        <div id="divInputFechas" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-outline input-daterange">
                        <i class="fas fa-calendar trailing"></i>
                        <input type="text" id="fechaInicio" class="form-control masked" name="fechaInicio" data-mask="####-##-##" onchange="validarFechas();" value="<?php echo date('Y-m-d'); ?>" required />
                        <label class="form-label" for="fechaInicio">Fecha inicio</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-outline input-daterange">
                        <i class="fas fa-calendar trailing"></i>
                        <input type="text" id="fechaFin" class="form-control masked" name="fechaFin" data-mask="####-##-##" onchange="validarFechas();" value="<?php echo date('Y-m-d'); ?>" required />
                        <label class="form-label" for="fechaFin">Fecha fin</label>
                    </div>
                </div>               
            </div>
        </div>
        <div class="form-select-control mb-4" id="divEstadoEmpleados">
            <select id="selectEstadoEmpleado" name="selectEstadoEmpleado" style="width: 100%;" required>
                <option></option>
                <option value="Activos" selected>Empleados activos</option>
                <option value="Inactivos">Empleados inactivos</option>
                <option value="Todos">Todos los empleados</option>
            </select>
        </div>
    </div>
    <div id="divReporte" class="col-md-9">
    </div>
</div>
<script>
    function validarFechas() {
        var fechaInicio = $("#fechaInicio").val();
        var fechaFin = $("#fechaFin").val();

        var fecha1 = moment(fechaInicio);
        var fecha2 = moment(fechaFin);

        if(fechaInicio == "" || fechaFin == "") {
            // No validar nada de momento
        } else {
            if(fecha2.diff(fecha1, 'days') < 0) {
                mensaje(
                    "AVISO",
                    "La fecha de inicio debe ser menor que la fecha de finalización.",
                    "warning"
                );
                $("#fechaInicio").val('');
                $("#fechaFin").val('');
            } else {
                // La fecha de finalizacion es mayor, dejar pasar validación
            }
        } 
    }
    $(document).ready(function() {
        Maska.create('#frmModal .masked');

        $("#divClasificacionEmpleados").hide();
        $("#divFiltroIncapacidades").hide();
        $("#divClasificacion").hide();
        $("#divInputRadios").hide();
        $("#divInputRadioEmpleados").hide();
        $("#divInputFechas").hide();
        $("#divEstadoEmpleados").hide();

        $("#file").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de reporte'
        });
        $("#filtroIncapacidades").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Filtrar'
        });
        $("#selectSucursalesEspecificas").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Sucursales especificas'
        });
        $("#selectEmpleadosEspecificos").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Empleados especificos'
        })
        $("#selectEstadoEmpleado").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Estado de empleados'
        })

        $("#file").change(function(e){
            if ($(this).val() == "incapacidades-por-riesgo"){
                $("#divFiltroIncapacidades").show();
                $("#divInputFechas").show();
                $("#divFlgSaltarPagina").show();
                $("#divInputRadioEmpleados").hide();
                $("#divEstadoEmpleados").show();
            }else{
                $("#divFiltroIncapacidades").hide();
                $("#divInputFechas").hide();
                $("#divFlgSaltarPagina").hide();
                $("#divInputRadios").hide();
                $("#divClasificacion").hide();
                $("#divInputRadioEmpleados").hide();
                $("#divEstadoEmpleados").hide();
                $("#filtroIncapacidades").val(null).trigger("change");

            }
        })
        
        $("#filtroIncapacidades").change(function(e) {
            if ($(this).val() == "Sucursales") {
                $("#divInputRadios").show();
                $("#filtroSucursalTodas").prop("checked", true);
                $("#divInputRadioEmpleados").hide();
                $("#divClasificacionEmpleados").hide();
            } else if($(this).val() == "Empleados") {
                $("#divInputRadios").hide();
                $("#divClasificacion").hide();
                $("#divInputRadioEmpleados").show();
                $("#filtroTodosfiltroEmpleado").prop("checked", true);
                $("#divClasificacionEmpleados").hide();
            } else {
                $("#divInputRadios").hide();
                $("#divClasificacion").hide();
                $("#divInputRadioEmpleados").hide();
                $("#divClasificacionEmpleados").hide();                
            }
        });

        $("input[type=radio][name=filtroSucursal]").change(function(e) {
            if ($(this).val() == "Especificas") {
                $("#divClasificacion").show();
            }else{
                $("#divClasificacion").hide();
            }
        });

        
        $("input[type=radio][name=filtroEmpleado]").change(function(e) {
            if ($(this).val() == "Especificos") {
                $("#divClasificacionEmpleados").show();
                $("#selectEstadoEmpleado").val("Activos").trigger("change");
            }else{
                $("#divClasificacionEmpleados").hide();
            }
        });
        

        $('#fechaInicio, #fechaFin').on('change', function() { 
            $(this).addClass("active"); 
        });

        $('.input-daterange').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            calendarWeeks : false,
            clearBtn: true,
            disableTouchKeyboard: true,
            todayHighlight: true
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
        $("#file").val('incapacidades-por-riesgo').trigger('change');
    });
</script>
