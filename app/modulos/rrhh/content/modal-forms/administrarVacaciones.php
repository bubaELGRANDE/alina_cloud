<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="cambiar-vacaciones">
<div class="row">
    <div class="col-md-12">
        <div class="form-select-control mb-4">
            <select id="empleadosVacacion" name="empleadosVacacion" style="width: 100%;" required>
                <option></option>
                <?php 
                    $dataEmpleadoExpediente = $cloud->rows("
                        SELECT
                            personaId,
                            nombreCompleto
                        FROM view_expedientes
                        WHERE estadoPersona = ? AND estadoExpediente = ?
                        ORDER BY nombreCompleto
                    ", ['Activo','Activo']);
                    foreach($dataEmpleadoExpediente as $dataEmpleadoExpediente) {
                        echo "<option value='$dataEmpleadoExpediente->personaId'>$dataEmpleadoExpediente->nombreCompleto</option>";
                    }
                ?>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div id="tipoVacacionActual" name="tipoVacacionActual">
            </p>Tipo de vacación actual :</p>
        </div>
    </div>
    <div class="col-md-6 mt-2">
        <input type="text" id="inputTipoVacacionActual" name="inputTipoVacacionActual" class="form-control" value="" disabled>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
            </p>Nuevo tipo de vacacion: </p>
    </div>
    <div class="col-md-6 mt-2">
            <input type="text" id="inputTipoVacacionesNuevo" name="inputTipoVacacionesNuevo" class="form-control" value="" disabled>
    </div>
</div>    
    <div id="diasDisponibles" class="row">
        <div class="col-md-6">
                </p>Dias disponibles: </p>
        </div>
        <div class="col-md-6 mt-2">
                <input type="number" id="inputDiasDisponibles" name="inputDiasDisponibles" class="form-control" min="0"  max="15" value="15">
        </div>
    </div>
<script>
    $(document).ready(function() {
        $("#diasDisponibles").hide();

        $("#empleadosVacacion").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Empleados'
            })

            $("#frmModal").validate({
                submitHandler: function(form) {
                    mensaje_confirmacion(
                        '¿Está seguro que desea cambiar las vacaciones del empleado?', 
                        `Se cambiará el plan de vacaciones del empleado seleccionado`, 
                        `warning`, 
                        function(param) {
                            button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                            asyncData(
                                "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                                $("#frmModal").serialize(),
                                function(data) {
                                    button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                                    if(data == "success") {
                                        mensaje_do_aceptar(
                                            "Operación completada:",
                                            "Se actualizaron las vacaciones del empleado",
                                            "success",
                                            function() {
                                                cargarVacaciones('Activo');
                                                cargarVacaciones('Inactivo');
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
                        },
                        'Sí, cambiar',
                        `Cancelar`
                    );
                }
            });
        
            $("#empleadosVacacion").change(function(e) {
            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>content/divs/divAdministrarVacacionesEmpleado", 
                {
                	personaId: $(this).val()
                },
                function(data) {
                   $("#inputTipoVacacionActual").val(data.tipoVacacionActual);
                   $("#inputTipoVacacionesNuevo").val(data.tipoVacacionNueva);

                   if (data.tipoVacacionNueva == "Individuales") {
                        $("#diasDisponibles").show();
                    } else{
                        $("#diasDisponibles").hide();
                    }
                }
            );
        });        

    });
</script>
