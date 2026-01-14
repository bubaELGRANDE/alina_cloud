<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<!-- extension puede ser un select en un futuro, por si se necesita cambiar el formato -->
<input type="hidden" id="extension" name="extension" value="pdf">
<div class="row">
    <div class="col-md-3">
        <div class="form-select-control mb-4">
            <select id="file" name="file" style="width: 100%;" required>
                <option></option>
                <option value="ficha-empleado">Ficha de empleado</option>
                <option value="listado-empleados">Listado de empleados</option>
            </select>
        </div>
        <div id="divFiltrosEmpleado" class="mb-3"> 
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
        <div id="divFirmaEmpleado" class="form-check mb-2">
            <!--  
            En desarrollo
            <input class="form-check-input" type="checkbox" value="Si" id="flgFirmaEmpleado" name="flgFirmaEmpleado">
            <label class="form-check-label" for="flgFirmaEmpleado">Incluir firma de validación de datos del empleado<label>
            -->
        </div>
        <div id="listaEmp" style="display:none;">
            <div class="form-select-control mb-4">
                <select id="columnasDatos" name="columnasDatos[]" style="width: 100%;" required multiple>
                    <option></option>
                    <?php 
                        $columnasConsulta = array(
                            "Nombre completo" => "Nombre completo^nombreCompleto", 
                            "Fecha de nacimiento" => "Fecha de nacimiento^per.fechaNacimiento", 
                            "DUI" => "DUI^per.numIdentidad",
                            "Fecha de expiración DUI" => "Fecha de expiración DUI^per.fechaExpiracionIdentidad", 
                            "NIT" => "NIT^per.nit",
                            "ISSS" => "ISSS^per.numISSS",
                            "Número AFP" => "Número AFP^per.nup",
                            "Cuenta planillera" => "Cuenta planillera^numeroCuenta",
                            // "AFP" => "AFP^nameafp.nombreOrganizacion"
                        );

                        foreach ($columnasConsulta as $clave => $columna){
                            echo '<option value="'.$columna.'">'.$clave.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div id="divReporte" class="col-md-9">
    </div>
</div>


<script>
	$(document).ready(function() {
        $("#divFiltrosEmpleado").hide();
        $("#divSelectEmpleados").hide();
        $("#divFirmaEmpleado").hide();

        $("#file").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de reporte'
        });

        $("#columnasDatos").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Columnas de datos'
        });

        $("#selectEmpleados").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Empleado(s)'
        });

        $("input[type=radio][name=filtroEmpleados]").change(function(e) {
            if($(this).val() == "Especifico" || $(this).val() == "Inactivos") {
                $("#divSelectEmpleados").show();
                $.ajax({
                    url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarEmpleados",
                    type: "POST",
                    dataType: "json",
                    data: {estadoPersona: ($(this).val() == "Especifico" ? 'Activo' : 'Inactivo')}
                }).done(function(data){
                    $("#selectEmpleados").empty();
                    for (let i = 0; i < data.length; i++){
                        $("#selectEmpleados").append(`<option value="${data[i]['personaId']}">${data[i]['nombreCompleto']}</option>`);
                    }                
                });
            } else {
                // Todos
                $("#divSelectEmpleados").hide();
            }
        });

        $("#file").on("change", function(){
            if($(this).val() == "ficha-empleado"){
                $("#divFiltrosEmpleado").show();
                $("#divFirmaEmpleado").show();
                $("#listaEmp").hide();
                $("#filtroEmpleadosTodos").prop('checked', true);
                $("#filtroEmpleadosEspecifico").prop('checked', false);
                $("#divSelectEmpleados").hide();
            } else if ($(this).val() == "listado-empleados") {
                $("#listaEmp").show();
                // campos para ficha-empleado
                $("#divFiltrosEmpleado").hide();
                $("#divSelectEmpleados").hide();
                $("#divFirmaEmpleado").hide();
            } else {
                // Reporte no definido
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
    });
</script>