<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
        arrayFormData 
        prsExpedienteId
    */
    $prsExpedienteId = $_POST['arrayFormData'];

    $dataInfoEmpleado = $cloud->row("
        SELECT
            CONCAT(
                IFNULL(pers.apellido1, '-'),
                ' ',
                IFNULL(pers.apellido2, '-'),
                ', ',
                IFNULL(pers.nombre1, '-'),
                ' ',
                IFNULL(pers.nombre2, '-')
            ) AS nombreCompleto,
            car.cargoPersona as cargoPersona,
            dep.departamentoSucursal as departamentoSucursal,
            s.sucursal as sucursal
        FROM th_expediente_personas exp
        LEFT JOIN th_personas pers ON pers.personaId = exp.personaId
        LEFT JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
        LEFT JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
        LEFT JOIN cat_sucursales s ON s.sucursalId = dep.sucursalId
        WHERE exp.prsExpedienteId = ? AND exp.flgDelete = ?
    ", [$prsExpedienteId, 0]);
    $modalTitle = "Jefaturas del empleado: " . $dataInfoEmpleado->nombreCompleto;
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="expediente-jefatura-empleado">
<input type="hidden" id="prsExpedienteId" name="prsExpedienteId" value="<?php echo $prsExpedienteId; ?>">
<div class="row">
    <div class="col-9">
        <div class="form-select-control mb-4">
            <select id="jefeId" name="jefeId[]" multiple="multiple" style="width:100%;" required>
                <option></option>
                <?php // falta join con expediente
                    $dataExpedientes = $cloud->rows("
                        SELECT
                        pers.personaId as personaId, 
                        exp.prsExpedienteId as expedienteId,
                        CONCAT(
                            IFNULL(pers.apellido1, '-'),
                            ' ',
                            IFNULL(pers.apellido2, '-'),
                            ', ',
                            IFNULL(pers.nombre1, '-'),
                            ' ',
                            IFNULL(pers.nombre2, '-')
                        ) AS nombreCompleto
                        FROM th_personas pers
                        LEFT JOIN th_expediente_personas exp ON pers.personaId = exp.personaId
                        WHERE pers.prsTipoId = ? AND pers.flgDelete = ? AND pers.estadoPersona = ? AND exp.estadoExpediente = ?
                        ORDER BY apellido1, apellido2, nombre1, nombre2
                    ", ['1', '0', 'Activo', 'Activo']);
                    foreach ($dataExpedientes as $dataExpedientes) {
                        echo '<option value="'.$dataExpedientes->expedienteId.'">'.$dataExpedientes->nombreCompleto.'</option>';
                    }
                ?>
            </select>
        </div>
        
    </div>
    <div class="col-3">
        <button type="submit" id="btnAsignarJefes" class="btn btn-primary btn-block ttip">
            <i class="fas fa-user-plus"></i> Asignar
            <span class="ttiptext">Asignar jefaturas</span>
        </button>
    </div>
</div>

<div id="divTblJefaturasEmpleado" class="table-responsive">
</div>

<script>
    function cargarJefaturasEmpleado() {
        asyncDoDataReturn(
            '<?php echo $_SESSION["currentRoute"]; ?>content/tables/tableJefaturasEmpleado',
            {
                id: <?php echo $prsExpedienteId; ?>
            },
            function(data) {
                $("#divTblJefaturasEmpleado").html(data);
            }
        );  
    }
    $(document).ready(function() {
        $("#modalTitle").html('<?php echo $modalTitle; ?>');
        cargarJefaturasEmpleado();

        $("#jefeId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Jefatura(s)'
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnAsignarJefes", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnAsignarJefes", "fas fa-user-plus", "Asignar", "enabled");
                        if(data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                'Jefaturas asignadas con éxito',
                                "success",
                                function() {
                                    $("#jefeId").val([]).trigger('change');
                                    cargarJefaturasEmpleado();
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
            }
        });
    });
</script>