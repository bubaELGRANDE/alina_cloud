<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
        arrayFormData 
            Nuevo = nuevo ^ personaId ^ nombreCompleto
            Editar = editar ^ prsExpLaboralId ^ lugarTrabajo ^ nombreCompleto
    */
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    if($arrayFormData[0] == "editar") {
        $txtSuccess = "Experiencia laboral actualizada con éxito.";
        $dataEditEmpleadoExpLaboral = $cloud->row("
            SELECT
                personaId,
                lugarTrabajo, 
                paisId, 
                prsArExperienciaId, 
                cargoTrabajo, 
                numMesInicio, 
                mesInicio, 
                anioInicio, 
                numMesFinalizacion, 
                mesFinalizacion, 
                anioFinalizacion,
                motivoRetiro
            FROM th_personas_exp_laboral
            WHERE prsExpLaboralId = ?
        ", [$arrayFormData[1]]);
        $personaId = $dataEditEmpleadoExpLaboral->personaId;
    } else {
        $txtSuccess = "Experiencia laboral agregada con éxito.";
        $personaId = $arrayFormData[1];
    }
    
    $dataEstadoPersona = $cloud->row("
        SELECT
            estadoPersona
        FROM th_personas
        WHERE personaId = ?
    ",[$personaId]);
?>
<input type="hidden" id="typeOperation" name="typeOperation">
<input type="hidden" id="operation" name="operation" value="empleado-experiencia-laboral">
<div class="row">
    <div class="col-lg-8">
        <div class="form-outline mb-4">
            <i class="fas fa-building trailing"></i>
            <input type="text" id="lugarTrabajo" class="form-control" name="lugarTrabajo" required />
            <label class="form-label" for="lugarTrabajo">Lugar de trabajo</label>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select id="paisId" name="paisId" style="width: 100%;" required>
                <option></option>
                <option value="61">El Salvador</option>
                <?php 
                    $dataPaises = $cloud->rows("
                        SELECT
                            paisId,
                            pais
                        FROM cat_paises
                        WHERE flgDelete = '0' AND paisId <> '61'
                    ");
                    foreach ($dataPaises as $dataPaises) {
                        echo '<option value="'.$dataPaises->paisId.'">'.$dataPaises->pais.'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
</div>
<div id="divCarrera" class="row">
    <div class="col-lg-8">
        <div class="form-outline mb-4">
            <i class="fas fa-briefcase trailing"></i>
            <input type="text" id="cargoTrabajo" class="form-control" name="cargoTrabajo" required>
            <label class="form-label" for="cargoTrabajo">Cargo desempeñado</label>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select id="prsArExperienciaId" name="prsArExperienciaId" style="width: 100%;" required>
                <option></option>
                <?php 
                    $dataAreasExperiencia = $cloud->rows("
                        SELECT
                            prsArExperienciaId,
                            areaExperiencia
                        FROM cat_personas_ar_experiencia
                        WHERE flgDelete = '0'
                    ");
                    foreach ($dataAreasExperiencia as $dataAreasExperiencia) {
                        echo '<option value="'.$dataAreasExperiencia->prsArExperienciaId.'">'.$dataAreasExperiencia->areaExperiencia.'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-3">
        <div class="form-select-control mb-4">
            <select id="mesInicio" name="mesInicio" style="width: 100%;" required>
                <option></option>
                <?php 
                    $mesesAnio = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
                    for ($i=1; $i < count($mesesAnio); $i++) { 
                        echo '<option value="'.$i.'">'.$mesesAnio[$i].'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-select-control mb-4">
            <select id="anioInicio" name="anioInicio" style="width: 100%;" required>
                <option></option>
                <?php 
                    for ($i=date("Y"); $i >= 1920; $i--) { 
                        echo '<option value="'.$i.'">'.$i.'</option>';
                    }
                ?>
            </select>    
        </div>    
    </div> 
    <div class="col-lg-3">
        <div class="form-select-control mb-4">
            <select id="mesFinalizacion" name="mesFinalizacion" style="width: 100%;" required>
                <option></option>
                <?php 
                    $mesesAnio = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
                    for ($i=1; $i < count($mesesAnio); $i++) { 
                        echo '<option value="'.$i.'">'.$mesesAnio[$i].'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-select-control mb-4">
            <select id="anioFinalizacion" name="anioFinalizacion" style="width: 100%;" required>
                <option></option>
                <?php 
                    for ($i=date("Y"); $i >= 1920; $i--) { 
                        echo '<option value="'.$i.'">'.$i.'</option>';
                    }
                ?>
            </select>    
        </div>    
    </div> 
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="form-outline mb-4">
            <i class="fas fa-edit trailing"></i>
            <textarea type="text" id="motivoRetiro" class="form-control" name="motivoRetiro" required></textarea>
            <label class="form-label" for="motivoRetiro">Motivo de retiro</label>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#paisId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'País de trabajo'
        });
        $("#mesInicio").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Mes de inicio'
        });
        $("#anioInicio").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Año de inicio'
        });
        $("#mesFinalizacion").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Mes de finalización'
        });
        $("#anioFinalizacion").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Año de finalización'
        });
        $("#prsArExperienciaId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Área de trabajo'
        });

        $('#mesInicio').change(function(e) {
            $('#mesFinalizacion').attr("min", $("#mesInicio").val());
        });

        $('#anioInicio').change(function(e) {
            $('#anioFinalizacion').attr("min", $("#anioInicio").val());
        });

        $('#anioFinalizacion').change(function(e) {
            if($("#anioInicio").val() == $("#anioFinalizacion").val()) {
                $('#mesFinalizacion').attr("min", $("#mesInicio").val());
            } else {
                $('#mesFinalizacion').removeAttr("min");
            }
        });

        $("#frmModal").validate({
            messages: {
                mesFinalizacion: {
                    min: `Debe ser mayor o igual al mes de inicio`
                },
                anioFinalizacion: {
                    min: `Debe ser mayor o igual al año de inicio`
                }
            },
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Experiencia laboral agregada con éxito.',
                                "success"
                            );
                            $('#tblEmpleadoExpLaboral').DataTable().ajax.reload(null, false);
                            $('#modal-container').modal("hide");
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
                $("#typeOperation").val('update');
                $("#modalTitle").html('Editar Experiencia Laboral - Lugar de trabajo: <?php echo $dataEditEmpleadoExpLaboral->lugarTrabajo; ?>');
                $("#lugarTrabajo").val('<?php echo $dataEditEmpleadoExpLaboral->lugarTrabajo; ?>');
                $("#paisId").val('<?php echo $dataEditEmpleadoExpLaboral->paisId; ?>').trigger('change');
                $("#cargoTrabajo").val('<?php echo $dataEditEmpleadoExpLaboral->cargoTrabajo; ?>');
                $("#prsArExperienciaId").val('<?php echo $dataEditEmpleadoExpLaboral->prsArExperienciaId; ?>').trigger('change');
                $("#paisId").val('<?php echo $dataEditEmpleadoExpLaboral->paisId; ?>').trigger('change');
                $("#mesInicio").val('<?php echo $dataEditEmpleadoExpLaboral->numMesInicio; ?>').trigger('change');
                $("#anioInicio").val('<?php echo $dataEditEmpleadoExpLaboral->anioInicio; ?>').trigger('change');
                $("#mesFinalizacion").val('<?php echo $dataEditEmpleadoExpLaboral->numMesFinalizacion; ?>').trigger('change');
                $("#anioFinalizacion").val('<?php echo $dataEditEmpleadoExpLaboral->anioFinalizacion; ?>').trigger('change');
                $("#motivoRetiro").val('<?php echo $dataEditEmpleadoExpLaboral->motivoRetiro; ?>');
        <?php 
            } else {
        ?>
                $("#typeOperation").val('insert');
                $("#modalTitle").html('Nueva Experiencia Laboral');
        <?php 
            }
            if($dataEstadoPersona->estadoPersona == "Inactivo") {
        ?>
                $("#btnModalAccept").prop("disabled", true);
        <?php
            } else {
                // No deshabilitar botón
            }
        ?>
    });
</script>