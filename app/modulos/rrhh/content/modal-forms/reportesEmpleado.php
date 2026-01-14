<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    // Empleados = nuevo^0
    // Expedientes = tipoReporte ^ data
    $arrayFormData = explode("^", $_POST['arrayFormData']);
?>
<!--  puede ser un select en un futuro, por si se necesita cambiar el formato -->
<input type="hidden" id="extension" name="extension" value="pdf">
<div class="row">
    <div class="col-md-3">
        <div class="form-select-control mb-4">
            <select id="file" name="file" style="width: 100%;" required>
                <option></option>
                <option value="ficha-actualizacion-datos">Ficha de actualización de datos</option>
                <option value="ficha-empleado">Ficha de empleado</option>
                <option value="listado-empleados">Listado de empleados</option>
                <option value="contrato-expediente">Contratos de trabajo</option>
                <option value="domicilios-empleados">Domicilios de empleados</option>
                <option value="nomina-empleados-fotos">Nómina de empleados con fotografía</option>
                <option value="mes-cumpleanios-laboral">Cumpleaños del mes: Laboral</option>
                <option value="mes-cumpleanios-personal">Cumpleaños del mes: Personal</option>
                <option value="empleados-con-hijos">Censo de empleados con hijos menores o igual a 4 años</option>
                <option value="empleados-beneficiarios">Lista de empelados con sus beneficiarios</option>
            </select>
        </div>
        <div id="divFiltroExpediente">
            <div class="form-select-control mb-4">
                <select id="selectFiltroExpediente" name="selectFiltroExpediente" style="width: 100%;" required>
                    <option></option>
                    <?php 
                        //$filtroExpediente = array("Empleados", "Cargos", "Sucursales");
                        $filtroExpediente = array("Empleados");
                        for ($i=0; $i < count($filtroExpediente); $i++) { 
                            echo '<option value="'.$filtroExpediente[$i].'">'.$filtroExpediente[$i].'</option>';
                        }
                    ?>
                </select>
            </div>
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
        <div id="divSelectEmpleadoSimple" class="form-select-control mb-4">
            <select id="selectEmpleadoSimple" name="selectEmpleadoSimple" style="width: 100%;"  required>
                <option></option>
            </select>
        </div>
        <div id="divSelectEmpleadoExpediente" class="form-select-control mb-4">
            <select id="selectEmpleadoExpediente" name="selectEmpleadoExpediente" style="width: 100%;"  required>
                <option></option>
            </select>
        </div>
        <div id="divFirmaEmpleado">
            <div class="d-flex">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="flgFirmaEmpleado" name="flgFirmaEmpleado" value="Sí" />
                </div>
                <label class="form-check-label" for="flgFirmaEmpleado">Firma de validación de datos del empleado</label>
            </div>
        </div>
        <div id="listaEmp">
            <div class="form-select-control mb-4">
                <select id="columnasDatos" name="columnasDatos[]" style="width: 100%;" required multiple>
                    <option></option>
                    <?php 
                        $columnasConsulta = array(
                            "Nombre completo" => "Nombre completo^nombreCompleto", 
                            "Fecha de nacimiento" => "Fecha de nacimiento^per.fechaNacimiento", 
                            "Sexo" => "Sexo^per.sexo",
                            "DUI" => "DUI^per.numIdentidad",
                            "Fecha de expiración DUI" => "Fecha de expiración DUI^per.fechaExpiracionIdentidad", 
                            "NIT" => "NIT^per.nit",
                            "ISSS" => "ISSS^per.numISSS",
                            "Número AFP" => "Número AFP^per.nup",
                            "Cuenta planillera" => "Cuenta planillera^numeroCuenta",
                            "Fecha de inicio de labores" => "Fecha de inicio de labores^per.fechaInicioLabores",
                            "Fecha de inicio en el cargo" => "Fecha de inicio en el cargo^fechaInicioCargo",
                            "Sucursal" => "Sucursal^sucursal",
                            "Cargo" => "Cargo^cargoPersona"
                            // "AFP" => "AFP^nameafp.nombreOrganizacion"
                        );

                        foreach ($columnasConsulta as $clave => $columna){
                            echo '<option value="'.$columna.'">'.$clave.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <div id="divComplementoContrato">
            <label>Contratante patronal:</label>
            <div class="form-select-control mb-4 mt-2">
                <select id="selectApoderadoLegal" name="selectApoderadoLegal" style="width: 100%;" required>
                    <option></option>
                    <?php 
                        $dataApoderadosLegales = $cloud->rows("
                            SELECT 
                                dir.personaId AS personaId,
                                CONCAT(
                                    IFNULL(pers.apellido1, '-'),
                                    ' ',
                                    IFNULL(pers.apellido2, '-'),
                                    ', ',
                                    IFNULL(pers.nombre1, '-'),
                                    ' ',
                                    IFNULL(pers.nombre2, '-')
                                ) AS nombreCompleto
                            FROM cat_directivos dir
                            JOIN th_personas pers ON pers.personaId = dir.personaId
                            WHERE dir.flgDelete = ?
                        ",[0]);

                        foreach($dataApoderadosLegales as $dataApoderadosLegales) {
                            echo '<option value="'.$dataApoderadosLegales->personaId.'" '.($dataApoderadosLegales->personaId == 3 ? "selected" : "").'>'.$dataApoderadosLegales->nombreCompleto.'</option>';
                        }
                    ?>
                </select>
            </div>
            <!-- Este es de momento, mientras se implementa la planilla -->
            <div class="row mb-4">
                <div class="col-4">
                    <label>Salario:</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" name="filtroSalario" id="filtroSalarioNumeros" value="Números" checked />
                        <label class="form-check-label" for="filtroSalarioNumeros">Números</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="filtroSalario" id="filtroSalarioLetras" value="Letras" />
                        <label class="form-check-label" for="filtroSalarioLetras">Letras</label>
                    </div>
                </div>
                <div id="divSalarioNumero" class="col-8 mt-4">
                    <div class="form-outline">
                        <i class="fas fa-dollar-sign trailing"></i>
                        <input type="number" id="salarioContrato" name="salarioContrato" class="form-control" onchange="($(this).val() == '' ? '' : $(this).val(parseFloat($(this).val()).toFixed(2)));" step="0.01" min="0.00" required />
                        <label class="form-label" for="salarioContrato">Salario</label>
                    </div>
                </div>
                <div id="divSalarioLetra" class="col-8">
                    <div class="form-outline">
                        <i class="fas fa-edit trailing"></i>
                        <textarea type="text" id="salarioContratoLetra" class="form-control" name="salarioContratoLetra" rows="3" required>Salario base ($ 0.00) más comisión generada por ventas</textarea>
                        <label class="form-label" for="salarioContratoLetra">Salario</label>
                    </div>
                </div>
            </div>
            <div class="form-outline mb-4 input-daterange">
                <input type="date" id="fechaContrato" class="form-control" name="fechaContrato" value="<?php echo date('Y-m-d'); ?>" required />
                <label class="form-label" for="fechaContrato">Fecha de impresión del contrato</label>
            </div>
        </div>
        <div id="divSucursales" class="form-select-control mb-4">
            <select id="selectFiltroSucursal" name="selectFiltroSucursal" style="width: 100%;" required>
                <option></option>
                <?php 
                    $dataSucursales = $cloud->rows("
                        SELECT
                            sucursalId, sucursal
                        FROM cat_sucursales
                        WHERE flgDelete = ? 
                        ORDER BY numOrdenSucursal
                    ", [0]);
                    foreach ($dataSucursales as $sucursal) {
                        echo "<option value='$sucursal->sucursalId'>$sucursal->sucursal</option>";
                    }
                ?>
            </select>
        </div>
        <div id="divFiltrosSucursalDepartamentos" class="mb-4"> 
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroSucursalDepartamento" id="filtroSucursalDepartamentoTodos" value="Todos" checked/>
                <label class="form-check-label" for="filtroSucursalDepartamentoTodos">Todos los departamentos</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroSucursalDepartamento" id="filtroSucursalDepartamentoEspecifico" value="Especifico" />
                <label class="form-check-label" for="filtroSucursalDepartamentoEspecifico">Departamento(s) específico(s)</label>
            </div>
        </div>
        <div id="divSelectSucursalDepartamentos" class="form-select-control mb-4">
            <select id="selectSucursalDepartamentos" name="selectSucursalDepartamentos[]" style="width: 100%;" multiple="multiple" required>
                <option></option>
            </select>
        </div>
        <div id="divFiltrosSucursalMultiple" class="mb-4"> 
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroSucursalMultiple" id="filtroSucursalMultipleTodos" value="Todos" checked/>
                <label class="form-check-label" for="filtroSucursalMultipleTodos">Todas las sucursales</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="filtroSucursalMultiple" id="filtroSucursalMultipleEspecifico" value="Especifico" />
                <label class="form-check-label" for="filtroSucursalMultipleEspecifico">Sucursal(es) específica(s)</label>
            </div>
        </div>
        <div id="divSelectSucursalMultiple" class="form-select-control mb-4">
            <select id="selectSucursalMultiple" name="selectSucursalMultiple[]" style="width: 100%;" multiple="multiple" required>
                <option></option>
                <?php 
                    $dataSucursales = $cloud->rows("
                        SELECT
                            sucursalId, sucursal
                        FROM cat_sucursales
                        WHERE flgDelete = ? 
                        ORDER BY numOrdenSucursal
                    ", [0]);
                    foreach ($dataSucursales as $sucursal) {
                        echo "<option value='$sucursal->sucursalId'>$sucursal->sucursal</option>";
                    }
                ?>
            </select>
        </div>
        <div id="divMesesAnio" class="form-select-control">
            <select id="selectMesAnio" name="selectMesAnio" style="width: 100%;" required>
                <option></option>
                <?php 
                    $arrayMeses = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                    for ($i=1; $i < count($arrayMeses); $i++) { 
                        echo "<option value='$i' ".($i == date("m") ? "selected" : "").">$arrayMeses[$i]</option>";
                    }
                ?>
            </select>
        </div>
        <div id="divFechaEdadInicio">
            <div class="form-outline input-daterange">
                <input type="date" id="fechaInicio" class="form-control" name="fechaInicio" value="<?php echo date('Y-m-d', strtotime('-5 years')); ?>" required />
                <label class="form-label" for="fechaInicio">Fecha de inicio</label>
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
        $("#divSelectEmpleadoSimple").hide();
        $("#divSelectEmpleadoExpediente").hide();
        $("#divFirmaEmpleado").hide();
        $("#listaEmp").hide();
        $("#divFiltroExpediente").hide();
        $("#divComplementoContrato").hide();
        $("#divSalarioLetra").hide();
        $("#divSucursales").hide();
        $("#divFiltrosSucursalDepartamentos").hide();
        $("#divSelectSucursalDepartamentos").hide();
        $("#divMesesAnio").hide();
        $("#divFiltrosSucursalMultiple").hide();
        $("#divSelectSucursalMultiple").hide();
        $("#divFechaEdadInicio").hide();

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

        $("#selectEmpleadoSimple").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Empleado'
        });

        $("#selectEmpleadoExpediente").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Cargos del empleado'
        });

        $("#selectFiltroExpediente").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Filtrar por'
        });

        $("#selectApoderadoLegal").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Contratante patronal'
        });

        $("#selectFiltroSucursal").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Sucursal'
        });

        $("#selectSucursalDepartamentos").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Departamento(s) de la sucursal'
        });

        $("#selectMesAnio").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Mes'
        });

        $("#selectSucursalMultiple").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Sucursal(es)'
        });

        $("input[type=radio][name=filtroEmpleados]").change(function(e) {
            let file = '';
            if($(this).val() == "Especifico" || $(this).val() == "Inactivos") {
                $("#divSelectEmpleados").show(); // Se reutiliza para contratos
                if($("#file").val() == "contrato-expediente") {
                    file = 'selectListarEmpleadosExpediente';
                } else {
                    // Empleados
                    file = 'selectListarEmpleados';
                }
                // Function en cloudjs para cargar selects
                asyncSelect(
                    `<?php echo $_SESSION['currentRoute']; ?>/content/divs/${file}`,
                    {
                        estadoPersona: ($(this).val() == "Especifico" ? 'Activo' : 'Inactivo'),
                        flgPersona: 'expedienteId'
                    },
                    `selectEmpleados`
                );
            } else {
                // Todos
                $("#divSelectEmpleados").hide();
            }
        });

        $("input[type=radio][name=filtroSucursalDepartamento]").change(function(e) {
            if($(this).val() == "Especifico") {
                $("#divSelectSucursalDepartamentos").show(); // Se reutiliza para contratos
                // Function en cloudjs para cargar selects
                asyncSelect(
                    `<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectSucursalDepartamentos`,
                    {
                        sucursal: $("#selectFiltroSucursal").val()
                    },
                    `selectSucursalDepartamentos`
                );
            } else {
                // Todos
                $("#divSelectSucursalDepartamentos").hide();
            }
        });

        $("#selectEmpleadoSimple").change(function(e) {
            asyncSelect(
                `<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectEmpleadoHistorialExpedientes`,
                {
                    personaId: $(this).val()
                },
                `selectEmpleadoExpediente`
            );
        });

        $("input[type=radio][name=filtroSalario]").change(function(e) {
            if($(this).val() == "Números") {
                $("#divSalarioNumero").show(); 
                $("#divSalarioLetra").hide();
                $("#divFechaEdadInicio").hide();
            } else {
                $("#divSalarioNumero").hide();
                $("#divSalarioLetra").show();
                $("#divFechaEdadInicio").hide();
            }
        });

        $("input[type=radio][name=filtroSucursalMultiple]").change(function(e) {
            if($(this).val() == "Todos") {
                $("#divSelectSucursalMultiple").hide();
                $("#divFechaEdadInicio").hide();
            } else {
                $("#divSelectSucursalMultiple").show();
                $("#divFechaEdadInicio").hide();
            }
        });

        $("#selectFiltroExpediente").change(function(e) {
            if($(this).val() !== "" && $(this).val() !== null) {
                $("#divFiltrosEmpleado").show(); // Reutilizado
                $("#divFechaEdadInicio").hide();
            } else {
                // No mostrar
                $("#divFiltrosEmpleado").hide();
                $("#divFechaEdadInicio").hide();
            }
        });

        $("#selectFiltroSucursal").change(function(e) {
            if($(this).val() > 0) {
                $("#divFiltrosSucursalDepartamentos").show();
                $("#filtroSucursalDepartamentoTodos").prop("checked", true);
                $("#filtroSucursalDepartamentoEspecifico").prop("checked", false);
                $("#divSelectSucursalDepartamentos").hide();
                $("#divFechaEdadInicio").hide();
            } else {
                $("#divFiltrosSucursalDepartamentos").hide();
                $("#divSelectSucursalDepartamentos").hide();
                $("#divFechaEdadInicio").hide();
            }
        });

        $("#file").on("change", function() {
            // Para este select en contrato-expediente se dibuja esta option
            // Pero para reutilizar este select en futuros reportes, al cambiar el tipo de reporte
            // Lo eliminamos para que quede solo los filtros generales para expedientes
            //$("#selectFiltroExpediente").find('option[value="Pendientes de firmar"]').remove();

            if($(this).val() == "ficha-actualizacion-datos") {
                $("#divFiltrosEmpleado").show();
                $("#divFirmaEmpleado").hide();
                $("#filtroEmpleadosTodos").prop('checked', true);
                $("#filtroEmpleadosEspecifico").prop('checked', false);
                $("#divSelectEmpleados").hide();

                // campos para listado-empleados
                $("#listaEmp").hide();
                // campos para contrato-expediente
                $("#divFiltroExpediente").hide();
                $("#divComplementoContrato").hide();
                $("#divSelectEmpleadoSimple").hide();
                $("#divSelectEmpleadoExpediente").hide();
                // campos para nomina-empleados-fotos
                $("#divSucursales").hide();
                $("#divFiltrosSucursalDepartamentos").hide();
                $("#divSelectSucursalDepartamentos").hide();
                // campos para mes-cumpleanios-laboral
                $("#divMesesAnio").hide();
                $("#divFiltrosSucursalMultiple").hide();
                $("#divSelectSucursalMultiple").hide();
                // Anexar los futuros divs que se vayan agregando
                $("#divFechaEdadInicio").hide();
            } else if($(this).val() == "ficha-empleado"){
                $("#divFiltrosEmpleado").show();
                $("#divFirmaEmpleado").show();
                $("#filtroEmpleadosTodos").prop('checked', true);
                $("#filtroEmpleadosEspecifico").prop('checked', false);
                $("#divSelectEmpleados").hide();

                // campos para listado-empleados
                $("#listaEmp").hide();
                // campos para contrato-expediente
                $("#divFiltroExpediente").hide();
                $("#divComplementoContrato").hide();
                $("#divSelectEmpleadoSimple").hide();
                $("#divSelectEmpleadoExpediente").hide();
                // campos para nomina-empleados-fotos
                $("#divSucursales").hide();
                $("#divFiltrosSucursalDepartamentos").hide();
                $("#divSelectSucursalDepartamentos").hide();
                // campos para mes-cumpleanios-laboral
                $("#divMesesAnio").hide();
                $("#divFiltrosSucursalMultiple").hide();
                $("#divSelectSucursalMultiple").hide();
                // Anexar los futuros divs que se vayan agregando
                $("#divFechaEdadInicio").hide();
            } else if ($(this).val() == "listado-empleados") {
                $("#listaEmp").show();

                // campos para ficha-empleado
                $("#divFiltrosEmpleado").hide();
                $("#divSelectEmpleados").hide();
                $("#divFirmaEmpleado").hide();
                // campos para contrato-expediente
                $("#divFiltroExpediente").hide();
                $("#divComplementoContrato").hide();
                $("#divSelectEmpleadoSimple").hide();
                $("#divSelectEmpleadoExpediente").hide();
                // campos para nomina-empleados-fotos
                $("#divSucursales").hide();
                $("#divFiltrosSucursalDepartamentos").hide();
                $("#divSelectSucursalDepartamentos").hide();
                // campos para mes-cumpleanios-laboral
                $("#divMesesAnio").hide();
                $("#divFiltrosSucursalMultiple").hide();
                $("#divSelectSucursalMultiple").hide();
                // Anexar los futuros divs que se vayan agregando
                $("#divFechaEdadInicio").hide();
            } else if($(this).val() == "contrato-expediente") {
                $("#divFiltroExpediente").hide();
                $("#divSelectEmpleadoSimple").show();
                $("#divComplementoContrato").show();

                asyncSelect(
                    `<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarEmpleadosExpediente`,
                    {
                        estadoPersona: 'Activo',
                        flgPersona: 'personaId'
                    },
                    `selectEmpleadoSimple`
                );
                $("#divSelectEmpleadoExpediente").show();

                // Los filtros de expedientes son generales para futuros reportes, para el caso de contratos
                // Se agrega una opción más para mostrar los contratos no firmados
                /*
                $("#selectFiltroExpediente").append($('<option>', {
                    value: 'Pendientes de firmar',
                    text: 'Pendientes de firmar'
                }));
                */
                // campos para ficha-empleado
                $("#divFiltrosEmpleado").hide(); // Reutilizado desde el change selectFiltrosExpediente
                $("#divSelectEmpleados").hide(); // Reutilizado desde el change filtroEmpleados
                $("#divFirmaEmpleado").hide();
                // campos para nomina-empleados-fotos
                $("#divSucursales").hide();
                // campos para listado-empleados
                $("#listaEmp").hide();
                // campos para nomina-empleados-fotos
                $("#divSucursales").hide();
                $("#divFiltrosSucursalDepartamentos").hide();
                $("#divSelectSucursalDepartamentos").hide();
                // campos para mes-cumpleanios-laboral
                $("#divMesesAnio").hide();
                $("#divFiltrosSucursalMultiple").hide();
                $("#divSelectSucursalMultiple").hide();

                $("#selectFiltroExpediente").trigger('change');
                // Anexar los futuros divs que se vayan agregando
                $("#divFechaEdadInicio").hide();
            } else if($(this).val() == "domicilios-empleados") {
                $("#listaEmp").hide();

                // campos para ficha-empleado
                $("#divFiltrosEmpleado").hide();
                $("#divSelectEmpleados").hide();
                $("#divFirmaEmpleado").hide();
                // campos para contrato-expediente
                $("#divFiltroExpediente").hide();
                $("#divComplementoContrato").hide();
                $("#divSelectEmpleadoSimple").hide();
                $("#divSelectEmpleadoExpediente").hide();
                // campos para nomina-empleados-fotos
                $("#divSucursales").hide();
                $("#divFiltrosSucursalDepartamentos").hide();
                $("#divSelectSucursalDepartamentos").hide();
                // campos para mes-cumpleanios-laboral
                $("#divMesesAnio").hide();
                $("#divFiltrosSucursalMultiple").hide();
                $("#divSelectSucursalMultiple").hide();
                // Anexar los futuros divs que se vayan agregando
                $("#divFechaEdadInicio").hide();
            } else if($(this).val() == "nomina-empleados-fotos") {
                $("#listaEmp").hide();

                // campos para ficha-empleado
                $("#divFiltrosEmpleado").hide();
                $("#divSelectEmpleados").hide();
                $("#divFirmaEmpleado").hide();
                // campos para contrato-expediente
                $("#divFiltroExpediente").hide();
                $("#divComplementoContrato").hide();
                $("#divSelectEmpleadoSimple").hide();
                $("#divSelectEmpleadoExpediente").hide();
                // campos para nomina-empleados-fotos
                $("#divSucursales").show();
                if($("#selectFiltroSucursal").val() > 0) {
                    $("#divFiltrosSucursalDepartamentos").show();
                    $("#selectFiltroSucursal").trigger("change");
                    $("#divFechaEdadInicio").hide();
                } else {
                    $("#divFiltrosSucursalDepartamentos").hide();
                    $("#divFechaEdadInicio").hide();
                }
                $("#divSelectSucursalDepartamentos").hide();
                // campos para mes-cumpleanios-laboral
                $("#divMesesAnio").hide();
                $("#divFiltrosSucursalMultiple").hide();
                $("#divSelectSucursalMultiple").hide();
                // Anexar los futuros divs que se vayan agregando
                $("#divFechaEdadInicio").hide();
            } else if($(this).val() == "mes-cumpleanios-laboral" || $(this).val() == "mes-cumpleanios-personal") {
                $("#listaEmp").hide();

                // campos para ficha-empleado
                $("#divFiltrosEmpleado").hide();
                $("#divSelectEmpleados").hide();
                $("#divFirmaEmpleado").hide();
                // campos para contrato-expediente
                $("#divFiltroExpediente").hide();
                $("#divComplementoContrato").hide();
                $("#divSelectEmpleadoSimple").hide();
                $("#divSelectEmpleadoExpediente").hide();
                // campos para nomina-empleados-fotos
                $("#divSucursales").hide();
                $("#divFiltrosSucursalDepartamentos").hide();
                $("#divSelectSucursalDepartamentos").hide();
                // campos para mes-cumpleanios-laboral
                $("#divMesesAnio").show();
                $("#divFiltrosSucursalMultiple").show();
                $("#divSelectSucursalMultiple").hide();
                // Anexar los futuros divs que se vayan agregando
                $("#divFechaEdadInicio").hide();
            } else if($(this).val() == "empleados-con-hijos"){
                $("#divFechaEdadInicio").show();

                $("#divFiltrosEmpleado").hide();
                $("#divSelectEmpleados").hide();
                $("#divFirmaEmpleado").hide();
                // campos para contrato-expediente
                $("#divFiltroExpediente").hide();
                $("#divComplementoContrato").hide();
                $("#divSelectEmpleadoSimple").hide();
                $("#divSelectEmpleadoExpediente").hide();
                // campos para nomina-empleados-fotos
                $("#divSucursales").hide();
                $("#divFiltrosSucursalDepartamentos").hide();
                $("#divSelectSucursalDepartamentos").hide();
                // campos para mes-cumpleanios-laboral
                $("#divMesesAnio").hide();
                $("#divFiltrosSucursalMultiple").hide();
                $("#divSelectSucursalMultiple").hide();
                $("#flgFirmaEmpleado").prop("checked", false).trigger('change');
            } else {
                // Reporte no definido
                $("#divFiltrosEmpleado").hide();
                $("#divSelectEmpleados").hide();
                $("#divSelectEmpleadoSimple").hide();
                $("#divSelectEmpleadoExpediente").hide();
                $("#divFirmaEmpleado").hide();
                $("#listaEmp").hide();
                $("#divFiltroExpediente").hide();
                $("#divComplementoContrato").hide();
                $("#divSalarioLetra").hide();
                $("#divSucursales").hide();
                $("#divFiltrosSucursalDepartamentos").hide();
                $("#divSelectSucursalDepartamentos").hide();
                $("#divMesesAnio").hide();
                $("#divFiltrosSucursalMultiple").hide();
                $("#divSelectSucursalMultiple").hide();
                $("#divFechaEdadInicio").hide();
            }
            $("#filtroEmpleadosEspecifico").prop("checked", false);
            $("#filtroEmpleadosInactivos").prop("checked", false);
            $("#filtroEmpleadosTodos").prop("checked", true).trigger('change');
            $("#filtroSucursalMultipleEspecifico").prop("checked", false);
            $("#filtroSucursalMultipleTodos").prop("checked", true);

            $("#selectSucursalMultiple").val([]).trigger("change");
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

        $("#btnModalReset").click(function(e) {
            if($("#file").val() == "ficha-empleado") {
                $("#flgFirmaEmpleado").prop("checked", false).trigger('change');
                // Al reutilizarse los filtros de empleados, abajo se resetean los otros
            } else if($("#file").val() == "listado-empleados") {
                $("#columnasDatos").val([]).trigger('change');
            } else if($("#file").val() == "contrato-expediente") {
                $("#selectFiltroExpediente").val(null).trigger('change');
                $("#selectEmpleadoSimple").val(null).trigger('change');
                $("#fechaContrato").val(`<?php echo date('Y-m-d'); ?>`);
                $("#selectApoderadoLegal").val(3).trigger('change');
                $("#salarioContrato").val(null);
                $("#salarioContratoLetra").val("Salario base ($ 0.00) más comisión generada por ventas");
            } else if($("#file").val() == "nomina-empleados-fotos") {
                $("#selectFiltroSucursal").val(null).trigger("change");
            } else if($("#file").val() == "mes-cumpleanios-laboral") {
                $("#selectMesAnio").val('<?php echo date("m"); ?>').trigger("change");
            } else {
                $("#divFiltrosEmpleado").hide();
                $("#divSelectEmpleados").hide();
                $("#divFirmaEmpleado").hide();
                $("#listaEmp").hide();
                $("#divFiltroExpediente").hide();
                $("#divComplementoContrato").hide();
                $("#frmModal").reset();
            }
            $("#filtroEmpleadosEspecifico").prop("checked", false);
            $("#filtroEmpleadosInactivos").prop("checked", false);
            $("#filtroEmpleadosTodos").prop("checked", true).trigger('change');

            $("#filtroSalarioLetras").prop("checked", false);
            $("#filtroSalarioNumeros").prop("checked", true).trigger('change');

            $("#filtroSucursalMultipleEspecifico").prop("checked", false);
            $("#filtroSucursalMultipleTodos").prop("checked", true);

            $("#selectSucursalMultiple").val([]).trigger("change");
        });
    });
</script>