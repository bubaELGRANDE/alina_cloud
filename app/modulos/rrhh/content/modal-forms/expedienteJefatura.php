<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
        arrayFormData 
            Nuevo = nuevo ^ 0
            Editar = editar ^ jefeId
    */
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    $jefeId = 0;
    if($arrayFormData[0] == "editar") {
        $jefeId = $arrayFormData[1];
        $dataInfoJefe = $cloud->row("
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
            JOIN th_personas pers ON pers.personaId = exp.personaId
            JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
            JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
            JOIN cat_sucursales s ON s.sucursalId = dep.sucursalId
            WHERE exp.prsExpedienteId = ? AND exp.flgDelete = ?
        ", [$jefeId, 0]);
        $modalTitle = "Jefatura: " . $dataInfoJefe->nombreCompleto;

        $dataEmpleadosACargo = $cloud->rows("
            SELECT
                expedienteJefaturaId,
                prsExpedienteId,
                flgHeredarPersonal
            FROM th_expediente_jefaturas
            WHERE jefeId = ? AND flgDelete = ?
        ", [$jefeId, 0]);
        $empleadosACargo = "";
        foreach ($dataEmpleadosACargo as $dataEmpleadosACargo) {
            $empleadosACargo .= $dataEmpleadosACargo->prsExpedienteId . ",";
        }
        $empleadosACargo .= $jefeId;

        $whereEdit = " AND prsExpedienteId NOT IN ($empleadosACargo)";
    } else {
        $whereEdit = '';
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="expediente-jefatura">
<?php 
    if($arrayFormData[0] == "nuevo") {
?>
        <div class="form-select-control mb-4">
            <select id="jefeId" name="jefeId" style="width:100%;" required>
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
                        JOIN th_expediente_personas exp ON pers.personaId = exp.personaId
                        WHERE pers.prsTipoId = ? AND pers.flgDelete = ? AND pers.estadoPersona = ? AND exp.estadoExpediente = ? 
                        ORDER BY apellido1, apellido2, nombre1, nombre2
                    ", ['1', '0', 'Activo', 'Activo']);
                    foreach ($dataExpedientes as $dataExpedientes) {
                        echo '<option value="'.$dataExpedientes->expedienteId.'">'.$dataExpedientes->nombreCompleto.'</option>';
                    }
                ?>
            </select>
        </div>
<?php 
    } else {
        // Es editar, solo los input de abajo
        // Crear jefeId hidden
        echo '<input type="hidden" id="jefeId" name="jefeId" value="'.$jefeId.'">';
    }
?>
<div class="row">
    <div class="col-9">
        <div class="form-select-control mb-4">
            <select id="prsExpedienteId" name="prsExpedienteId[]" multiple="multiple" style="width:100%;" required>
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
                        JOIN th_expediente_personas exp ON pers.personaId = exp.personaId
                        WHERE pers.prsTipoId = ? AND pers.flgDelete = ? AND pers.estadoPersona = ? AND exp.estadoExpediente = ? $whereEdit
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
        <button type="submit" id="btnAsignarEmpleados" class="btn btn-primary btn-block ttip">
            <i class="fas fa-user-plus"></i> Asignar
            <span class="ttiptext">Asignar empleados</span>
        </button>
    </div>
</div>
<?php 
    if($arrayFormData[0] == "editar") {
?>
        <div id="divTblJefaturaEmpleados" class="table-responsive">
        </div>
<?php 
    } else {
        // Es nuevo, no mostrar la tabla
    }
?>
<script>
    function cargarJefaturaEmpleados() {
        asyncDoDataReturn(
            '<?php echo $_SESSION["currentRoute"]; ?>content/tables/tableJefaturasJefe',
            {
                id: <?php echo $jefeId; ?>
            },
            function(data) {
                $("#divTblJefaturaEmpleados").html(data);
            }
        );  
    }
    $(document).ready(function() {
        <?php 
            if($arrayFormData[0] == "nuevo") {
        ?>
                $("#jefeId").select2({
                    dropdownParent: $('#modal-container'),
                    placeholder: 'Jefe'
                });
        <?php 
            } else {
                // Es editar, se forma un hidden de jefeId
            }
        ?>
        $("#prsExpedienteId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Empleado(s) a cargo'
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnAsignarEmpleados", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnAsignarEmpleados", "fas fa-user-plus", "Asignar", "enabled");
                        if(data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                'Empleados asignados con éxito',
                                "success",
                                function() {
                                    $('#tblJefatura').DataTable().ajax.reload(null, false);
                                    <?php 
                                        if($arrayFormData[0] == "editar") {
                                            // Refrescar la tabla de empleados que tiene a cargo, sin cerrar la modal
                                    ?>
                                            $("#prsExpedienteId").val([]).trigger('change');
                                            cargarJefaturaEmpleados();
                                    <?php 
                                        } else {
                                            // Cerrar y volver a abrir la modal para cargar la datatable de los empleados que tiene a cargo y así se oculta el select de jefeId y pasa a ser un hidden
                                    ?>
                                            $('#modal-container').modal("hide");
                                            modalJefatura(`editar^${$("#jefeId").val()}`);
                                    <?php 
                                        }
                                    ?>
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
        <?php 
            if($arrayFormData[0] == "editar") {
        ?>
                $("#modalTitle").html('<?php echo $modalTitle; ?>');
                cargarJefaturaEmpleados();
        <?php
            } else {
        ?>
                $("#modalTitle").html('Nueva Jefatura');
        <?php 
            }
        ?>
    });
</script>