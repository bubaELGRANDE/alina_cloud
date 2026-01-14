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
                <option value="vacaciones-individuales">Programa de vacaciones individuales</option>
            </select>
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
        <div id="divListadoEmpleados" class="form-select-control mb-4">
            <select id="selectEmpleadosEspecificos" name="selectEmpleadosEspecificos[]" multiple="multiple" style="width: 100%;" required>
                <option></option>
                <?php 
                    $dataEmpleadosEspecificos = $cloud->rows("
                        SELECT prsExpedienteId,
                            nombreCompleto
                        FROM view_expedientes 
                        WHERE estadoPersona = ? AND estadoExpediente = ? AND tipoVacacion = ? AND estadoVacacion = ? 
                        ORDER BY apellido1, apellido2, nombre1, nombre2
                    ",['Activo','Activo','Individuales','Activo']);
                    foreach($dataEmpleadosEspecificos as $EmpleadosEspecificos) {
                        echo "<option value='$EmpleadosEspecificos->prsExpedienteId'>$EmpleadosEspecificos->nombreCompleto</option>";
                    }
                ?>
            </select>
        </div>
        <div class="row" id="fechaReporteVacaciones">
            <div class="col-6">
                <div class="form-select-control mb-4">
                    <select id="anioDesde" name="anioDesde" style="width: 100%;" onchange="validarDesdeHasta();" required>
                        <option></option>
                        <?php 
                            for ($i=date("Y"); $i >= 2019; $i--) { 
                                echo "<option value='$i'>$i</option>";
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-6">
                <div class="form-select-control mb-4">
                    <select id="anioHasta" name="anioHasta" style="width: 100%;" onchange="validarDesdeHasta();" required>
                        <option></option>
                        <?php 
                            for ($i=date("Y"); $i >= 2019; $i--) { 
                                echo "<option value='$i'>$i</option>";
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div id="divReporte" class="col-md-9">
    </div>
</div>
<script>
    function validarDesdeHasta() {
        var indexInicio = $("#anioDesde").prop('selectedIndex');
        var indexFin = $("#anioHasta").prop('selectedIndex');

        if (indexFin > indexInicio && (indexInicio > 0 && indexFin > 0)) {
            mensaje(
                "Alerta:",
                'El año de inicio debe ser menor que el año fin.',
                "warning"
            );  
            $("#anioDesde").val(null).trigger('change');
            $("#anioHasta").val(null).trigger('change');
        } else {
            // Dejar pasar, el año inicio es menor que el fin
        }
    }

    $(document).ready(function() {
        $("#divListadoEmpleados").hide();
        $("#divInputRadioEmpleados").hide();
        $("#fechaReporteVacaciones").hide();

        $("#file").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de reporte'
        });

        $("#selectEmpleadosEspecificos").select2({
            dropdownParent: $('#modal-container'),
            placeholder:'Empleado especifico'
        });

        $("#anioDesde").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Año inicio'
        });

        $("#anioHasta").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Año fin'
        });
        
        $("#file").change(function(e){
            if($(this).val() == "vacaciones-individuales"){
                $("#divInputRadioEmpleados").show();
                $("#fechaReporteVacaciones").show();
                $("#divListadoEmpleados").hide();
            }else{
                $("#divInputRadioEmpleados").hide();
                $("#fechaReporteVacaciones").hide();
                $("#divListadoEmpleados").hide();
            }
        });

        $("input[type=radio][name=filtroEmpleado]").change(function(e){
            if($(this).val() == "Especificos"){
                $("#divListadoEmpleados").show();
            }else{
                $("#divListadoEmpleados").hide();
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
        //$("#file").val('vacaciones-individuales').trigger('change');
    });
</script>
