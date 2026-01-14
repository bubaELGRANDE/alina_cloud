<?php 
	@session_start();
?>
<h2>
    Bitácoras de usuarios
</h2>
<hr>

<!-- Tabs navs -->
<ul class="nav nav-tabs mb-3" id="ex1" role="tablist">
    <?php if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(27, $_SESSION["arrayPermisos"])) { ?>
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="ex1-tab-1" data-mdb-toggle="tab" href="#ex1-tabs-1" role="tab" aria-controls="ex1-tabs-1" aria-selected="true">
            Bitácora de usuarios
        </a>
    </li>
    <?php } ?>
    <?php if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(28, $_SESSION["arrayPermisos"])) { ?>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ex1-tab-2" data-mdb-toggle="tab" href="#ex1-tabs-2" role="tab" aria-controls="ex1-tabs-2" aria-selected="false">
            Bitacora inactivos
        </a>
    </li>
    <?php } ?>
    <?php if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(29, $_SESSION["arrayPermisos"])) { ?>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ex1-tab-3" data-mdb-toggle="tab" href="#ex1-tabs-3" role="tab" aria-controls="ex1-tabs-3" aria-selected="false">
            Bitácora de solicitudes externas
        </a>
    </li>
    <?php } ?>
    </ul>
<!-- Tabs navs -->

<!-- Tabs content -->
<div class="tab-content" id="ex1-content">
    <?php if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(27, $_SESSION["arrayPermisos"])) { ?>
    <div class="tab-pane fade show active" id="ex1-tabs-1" role="tabpanel" aria-labelledby="ex1-tab-1">
        <form id="bitUsers">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" id="selectEmpleado" name="selectEmpleado[]" style="width: 100%;" multiple required>
                        <option value="0" disabled>Seleccione los usuarios</option>
                        <?php 
                        if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(30, $_SESSION["arrayPermisos"])) {  // Ver todos los usuarios
                            $listEmpleados = $cloud->rows ("
                                SELECT
                                    us.usuarioId AS usuarioId,
                                    pers.nombre1 AS nombre1,
                                    pers.nombre2 AS nombre2,
                                    pers.apellido1 AS apellido1,
                                    pers.apellido2 AS apellido2
                                FROM conf_usuarios us 
                                JOIN th_personas pers ON pers.personaId=us.personaId
                                WHERE us.usuarioId NOT IN (1, 2, 5)
                                ORDER BY pers.apellido1, pers.apellido2, pers.nombre1, pers.nombre2
                            ");
                            foreach($listEmpleados as $empleado) {
                                $nombreCompleto = ""; // CONCAT retornaba null por empleados que no tienen datos completos
                                $nombreCompleto .= ($empleado->apellido1 != "") ? $empleado->apellido1 : "";
                                $nombreCompleto .= ($empleado->apellido2 != "") ? " " . $empleado->apellido2 : "";
                                $nombreCompleto .= ($empleado->nombre1 != "") ? ", ". $empleado->nombre1 : "";
                                $nombreCompleto .= ($empleado->nombre2 != "") ? " " . $empleado->nombre2 : "";

                                echo '<option value="'.$empleado->usuarioId.'"> '.$nombreCompleto.'</option>';
                        
                            }
                        } else { // ver usuarios asignados (consulta pendiente)
                            $listEmpleados = $cloud->rows ("
                                SELECT
                                    us.usuarioId AS usuarioId,
                                    pers.nombre1 AS nombre1,
                                    pers.nombre2 AS nombre2,
                                    pers.apellido1 AS apellido1,
                                    pers.apellido2 AS apellido2
                                FROM conf_usuarios us 
                                JOIN th_personas pers ON pers.personaId=us.personaId
                                WHERE us.usuarioId NOT IN (1, 2, 5)
                                ORDER BY pers.apellido1, pers.apellido2, pers.nombre1, pers.nombre2
                            ");
                            foreach($listEmpleados as $empleado) {
                                $nombreCompleto = ""; // CONCAT retornaba null por empleados que no tienen datos completos
                                $nombreCompleto .= ($empleado->apellido1 != "") ? $empleado->apellido1 : "";
                                $nombreCompleto .= ($empleado->apellido2 != "") ? " " . $empleado->apellido2 : "";
                                $nombreCompleto .= ($empleado->nombre1 != "") ? ", ". $empleado->nombre1 : "";
                                $nombreCompleto .= ($empleado->nombre2 != "") ? " " . $empleado->nombre2 : "";

                                echo '<option value="'.$empleado->usuarioId.'"> '.$nombreCompleto.'</option>';
                        
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="selectMov" name="selectMod[]" style="width: 100%;" multiple required>
                        <option value="0" disabled>Seleccione los movimientos</option>
                        <option value="movInterfaces">Movimientos en interfaces</option>
                        <option value="movInsert">Ingreso de registros</option>
                        <option value="movUpdate">Modificación de registros</option>
                        <option value="movDelete">Eliminación de registros</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-outline mb-4 input-daterange"> 
                        <i class="fa fa-calendar trailing"></i> 
                        <input type="text" id="fechaInicioUsers" name="fechaInicio" class="form-control text-start masked fechaInicio" data-mask="##-##-####" required onchange="compararFechas();" value="<?php echo date('d-m-Y'); ?>"> 
                        <label class="form-label" id="start-p" for="fechaInicio">Fecha de inicio:</label> 
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-outline mb-4 input-daterange"> 
                        <i class="fa fa-calendar trailing"></i> 
                        <input type="text" id="fechaFinUsers" name="fechaFin" class="form-control text-start masked fechaFin" data-mask="##-##-####" required onchange="compararFechas();" value="<?php echo date('d-m-Y'); ?>"> 
                        <label class="form-label" id="start-p" for="fechaFin">Fecha de finalización:</label> 
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 offset-md-9">
                    <button type="button" class="btn btn-primary btn-block" onclick="getBitacora('bitUsuarios')"><i class="fas fa-list-alt"></i> Ver movimientos</button>
                </div>
            </div>
        </form>
    </div>
    <?php } ?>
    <?php if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(27, $_SESSION["arrayPermisos"])) { ?>
    <div class="tab-pane fade" id="ex1-tabs-2" role="tabpanel" aria-labelledby="ex1-tab-2">
        <form id="bitInactivo">
            <div class="row">
                
                <div class="col-md-3">
                    <div class="form-outline mb-4 input-daterange"> 
                        <i class="fa fa-calendar trailing"></i> 
                        <input type="text" id="fechaInicioInactivo" name="fechaInicio" class="form-control text-start masked fechaInicio" data-mask="##-##-####" required onchange="compararFechas();" value="<?php echo date('d-m-Y'); ?>"> 
                        <label class="form-label" id="start-p" for="fechaInicio">Fecha de inicio:</label> 
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-outline mb-4 input-daterange"> 
                        <i class="fa fa-calendar trailing"></i> 
                        <input type="text" id="fechaFinInactivo" name="fechaFin" class="form-control text-start masked fechaFin" data-mask="##-##-####" required onchange="compararFechas();" value="<?php echo date('d-m-Y'); ?>"> 
                        <label class="form-label" id="start-p" for="fechaFin">Fecha de finalización:</label> 
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary btn-block" onclick="getBitacora('bitInactivos')"><i class="fas fa-list-alt"></i> Ver movimientos</button>
                </div>
            </div>
        </form>
    </div>
    <?php } ?>
    <?php if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(29, $_SESSION["arrayPermisos"])) { ?>
    <div class="tab-pane fade" id="ex1-tabs-3" role="tabpanel" aria-labelledby="ex1-tab-3">
        <form id="bitExterno">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-outline mb-4 input-daterange"> 
                        <i class="fa fa-calendar trailing"></i> 
                        <input type="text" id="fechaInicioExterno" name="fechaInicio" class="form-control text-start masked fechaInicio" data-mask="##-##-####" required onchange="compararFechas();" value="<?php echo date('d-m-Y'); ?>"> 
                        <label class="form-label" id="start-p" for="fechaInicio">Fecha de inicio:</label> 
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-outline mb-4 input-daterange"> 
                        <i class="fa fa-calendar trailing"></i> 
                        <input type="text" id="fechaFinExterno" name="fechaFin" class="form-control text-start masked fechaFin" data-mask="##-##-####" required onchange="compararFechas();" value="<?php echo date('d-m-Y'); ?>"> 
                        <label class="form-label" id="start-p" for="fechaFin">Fecha de finalización:</label> 
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary btn-block" onclick="getBitacora('bitExterno')"><i class="fas fa-list-alt"></i> Ver movimientos</button>
                </div>
            </div>
        </form>
    </div>
    <?php } ?>
</div>
<!-- Tabs content -->
<div id="tablaBit"></div>



<script>
    function compararFechas() {
        if($(".fechaInicio").val() != "" && $(".fechaFin").val() != "") {
            let fechaInicio = new Date($(".fechaInicio").val());
            let fechaFin = new Date($(".fechaFin").val()); 

            if(fechaInicio > fechaFin) {
                mensaje(
                    "AVISO - FORMULARIO",
                    "La fecha de inicio debe de ser menor o igual que la fecha fin",
                    "warning"
                );
                $(".fechaFin").val("");
            } else {
            }
        } else {
        }
    }
    
    function getBitacora(tipoBit){
        asyncDoDataReturn(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/divTableBitacoras", 
            $("#bitUsers").serialize()+ '&tipoBit=' + tipoBit,
            function(data) {
                $("#tablaBit").html(data);
            }
        );
    }
    
    $(document).ready(function() {
        $(".nav-link").on("click", function(){
            $("#tablaBit").empty();
            $('#bitExterno, #bitUsers, #bitInactivo').trigger("reset");
            $('#selectEmpleado').val(null).trigger('change');
            $('#selectMov').val(null).trigger('change');
            $('.fechaInicio, .fechaFin').addClass("active"); 
        });
        $("#selectEmpleado").select2({
            placeholder: "Seleccionar empleado(s)",
            allowClear: true
        });
        $("#selectMov").select2({
            placeholder: "Seleccionar movimiento(s)",
            allowClear: true
        });
        $('#fechaInicio, #fechaFin').on('change', function() { 
           $(this).addClass("active"); 
        });
        $('.input-daterange').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            calendarWeeks : false,
            clearBtn: true,
            disableTouchKeyboard: true,
            todayHighlight: true
        });
        
        $("#bitUsers").submit(function(e){
            asyncDoDataReturn(
                "<?php echo $_SESSION['currentRoute']; ?>content/divs/divTableBitacoras", 
                $("#bitUsers").serialize(),
                function(data) {
                    $("#tablaBit").html(data);
                    
                }
            );
            e.preventDefault();
        });
        
    });
</script>