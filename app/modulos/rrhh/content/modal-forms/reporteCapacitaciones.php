<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<input type="hidden" id="extension" name="extension" value="pdf">
<div class="row">
    <div class="col-md-3">
        <div class="form-select-control mb-4" id="divCapacitaciones">
            <select id="file" name="file" style="width: 100%;" required>
                <option></option>
                <option value="capacitaciones-por-cursos">Capacitación interna por cursos</option>
                <option value="capacitaciones-por-empleado">Capacitación interna por empleado</option>
            </select>
        </div>
        <div id="divInputRadiosCursos" class="form-select-control mb-4">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroCapacitacionesCursos" id="filtroCapacitacionesTodosCursos" value="Todos" checked/>
                <label class="form-check-label" for="filtroCapacitacionesTodosCursos">Todos los cursos</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroCapacitacionesCursos" id="filtroCapacitacionCursosEspecificos" value="Especificos" />
                <label class="form-check-label" for="filtroCapacitacionCursosEspecificos">Cursos específicos</label>
            </div>
        </div>
        <div id="divInputRadiosEmpleados" class="form-select-control mb-4">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroCapacitacionesEmpleados" id="filtroCapacitacionTodosEmpleados" value="Todos" checked/>
                <label class="form-check-label" for="filtroCapacitacionTodosEmpleados">Todos los empleados</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroCapacitacionesEmpleados" id="filtroCapacitacionEmpleadosEspecificos" value="Especificos" />
                <label class="form-check-label" for="filtroCapacitacionEmpleadosEspecificos">Empleados específicos</label>
            </div>
        </div>
        <div id="divClasificacionCursos" class="form-select-control mb-4">
            <select id="selectCursosEspecificos" name="selectCursosEspecificos[]" multiple="multiple" style="width: 100%;" required>
                <?php 
                    $dataCursosEspecificos = $cloud->rows("
                        SELECT
                            expedienteCapacitacionId,
                            descripcionCapacitacion
                        FROM th_expediente_capacitaciones
                        WHERE flgDelete = ? 
                    ", [0]);
                    foreach($dataCursosEspecificos as $dataCursosEspecificos) {
                        echo "<option value='$dataCursosEspecificos->expedienteCapacitacionId'>$dataCursosEspecificos->descripcionCapacitacion</option>";
                    }
                ?>
            </select>
        </div>
        <div id="divClasificacionEmpleados" class="form-select-control mb-4">
            <select id="selectEmpleadosEspecificos" name="selectEmpleadosEspecificos[]" multiple="multiple" style="width: 100%;" required>
                <?php 
                    $dataEmpleadosEspecificos = $cloud->rows("
                    SELECT
                        ecd.prsExpedienteId as prsExpedienteId,
                        ve.nombreCompleto as nombreCompleto
                    FROM th_expediente_capacitacion_detalle ecd
                    JOIN view_expedientes ve ON ve.prsExpedienteId = ecd.prsExpedienteId
                    WHERE ve.estadoPersona = ? AND ve.estadoExpediente = ? AND ecd.flgDelete = ? $where
                    GROUP BY ecd.prsExpedienteId
                    ORDER BY ve.nombreCompleto
                ",['Activo','Activo', 0]);
                    foreach($dataEmpleadosEspecificos as $dataEmpleadosEspecificos) {
                        echo "<option value='$dataEmpleadosEspecificos->prsExpedienteId'>$dataEmpleadosEspecificos->nombreCompleto</option>";
                    }
                ?>
            </select>
        </div>
        <div id="divInputFechas" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-outline input-daterange">
                        <input type="date" id="fechaInicio" class="form-control " name="fechaInicio" onchange="validarFechas();" value="<?php echo date('Y-m-d'); ?>" required />
                        <label class="form-label" for="fechaInicio">Fecha inicio</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-outline input-daterange">
                        <input type="date" id="fechaFin" class="form-control " name="fechaFin" onchange="validarFechas();" value="<?php echo date('Y-m-d'); ?>" required />
                        <label class="form-label" for="fechaFin">Fecha fin</label>
                    </div>
                </div>               
            </div>
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
        $("#divInputFechas").hide();
        $("#divInputRadiosCursos").hide();
        $("#divInputRadiosEmpleados").hide();
        $("#divClasificacionCursos").hide();
        $("#divClasificacionEmpleados").hide();

        $("#file").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de reporte'
        });

        $("#selectCursosEspecificos").select2({
            dropdownParent: $('#modal-container'),
            placeholder : 'Curso(s)'
        });

        $("#selectEmpleadosEspecificos").select2({
            dropdownParent: $('#modal-container'),
            placeholder : 'Empleado(s) '
        });

        $("#file").change(function(e){
            if($(this).val() == "capacitaciones-por-cursos"){
                $("#divInputRadiosCursos").show();
                $("#divInputRadiosEmpleados").hide();
                $("#divClasificacionEmpleados").hide();
                $("#filtroCapacitacionesTodosCursos").prop("checked", true);
                $("#divInputFechas").show();
            }else{
                $("#divInputRadiosCursos").hide();
                
            }
        });
        $("input[type=radio][name=filtroCapacitacionesCursos]").change(function(e){
            if($(this).val() == "Especificos"){
                $("#divClasificacionCursos").show();
                $("#divClasificacionEmpleados").hide();
                $("#divInputRadiosEmpleados").hide();
                $("#divInputFechas").show();
            }else{
                $("#divClasificacionCursos").hide();
                
            }
        });

        $("#file").change(function(e){
            if($(this).val() == "capacitaciones-por-empleado"){
                $("#divInputRadiosEmpleados").show();
                $("#divInputRadiosCursos").hide();
                $("#divClasificacionCursos").hide();
                $("#filtroCapacitacionTodosEmpleados").prop("checked", true);
                $("#divInputFechas").show();
            }else{
                $("#divInputRadiosEmpleados").hide();
                
            }
        });

        $("input[type=radio][name=filtroCapacitacionesEmpleados]").change(function(e){
            if($(this).val() == "Especificos"){
                $("#divClasificacionEmpleados").show();
                $("#divClasificacionCursos").hide();
                $("#divInputRadiosCursos").hide();
                $("#divInputFechas").show();
            }else{
                $("#divClasificacionEmpleados").hide();
                
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
        //$("#file").val('incapacidades-por-riesgo').trigger('change');
    });
</script>