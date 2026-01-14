<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
        arrayFormData 
            Nuevo = nuevo ^ personaId ^ nombreCompleto 
            Editar = editar ^ prsEducacionId ^ nombreCompleto
    */
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    if($arrayFormData[0] == "editar") {
        $txtSuccess = "Estudio actualizado con éxito.";
        $dataEditEmpleadoEstudio = $cloud->row("
            SELECT
                personaId,
                centroEstudio, 
                nivelEstudio, 
                prsArEstudioId, 
                nombreCarrera, 
                paisId, 
                numMesInicio,
                mesInicio, 
                anioInicio, 
                numMesFinalizacion, 
                mesFinalizacion, 
                anioFinalizacion, 
                estadoEstudio
            FROM th_personas_educacion
            WHERE prsEducacionId = ?
        ", [$arrayFormData[1]]);
        $txtSuccess = "Se ha actualizado con éxito los datos de empleado.";
        $personaId = $dataEditEmpleadoEstudio->personaId;
    } else {
        $txtSuccess = "Estudio agregado con éxito.";
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
<input type="hidden" id="operation" name="operation" value="empleado-estudio">
<div class="row">
    <div class="col-lg-8">
        <div class="form-outline form-update-insaforp mb-4">
            <i class="fas fa-user-graduate trailing"></i>
            <input type="text" id="centroEstudio" class="form-control" name="centroEstudio" required />
            <label class="form-label" for="centroEstudio">Lugar de estudio</label>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select id="nivelEstudio" name="nivelEstudio" style="width: 100%;" required>
                <option></option>
                <?php 
                    $nivelesEstudio = array("Básica primaria","Básica secundaria","Educación media","Técnico/Profesional","Universidad","Postgrado","Diplomado", "Curso", "Curso - INSAFORP");
                    for ($i=0; $i < count($nivelesEstudio); $i++) { 
                        echo '<option value="'.$nivelesEstudio[$i].'">'.$nivelesEstudio[$i].'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
</div>
<div id="divCarrera" class="row">
    <div class="col-lg-8">
        <div class="form-outline mb-4">
            <i class="fas fa-graduation-cap trailing"></i>
            <input type="text" id="nombreCarrera" class="form-control" name="nombreCarrera">
            <label id="labelCarrera" class="form-label" for="nombreCarrera">Carrera</label>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select id="prsArEstudioId" name="prsArEstudioId" style="width: 100%;">
                <option></option>
                <?php 
                    $dataAreasEstudio = $cloud->rows("
                        SELECT
                            prsArEstudioId,
                            areaEstudio
                        FROM cat_personas_ar_estudio
                        WHERE flgDelete = '0'
                    ");
                    foreach ($dataAreasEstudio as $dataAreasEstudio) {
                        echo '<option value="'.$dataAreasEstudio->prsArEstudioId.'">'.$dataAreasEstudio->areaEstudio.'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
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
    <div class="col-lg-6">
        <div class="form-check-validate">
            <?php 
                $estadosEstudios = array("Finalizado", "Cursando", "Incompleto");
                for ($i=0; $i < count($estadosEstudios); $i++) { 
                    echo '
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="estadoEstudio" id="estadoEstudio'.$i.'" value="'.$estadosEstudios[$i].'" required>
                            <label class="form-check-label" for="estadoEstudio'.$i.'">'.$estadosEstudios[$i].'</label>
                        </div>                    
                    ';
                }
            ?>
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
    <div id="divMesFinalizacion" class="col-lg-3">
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
    <div id="divAnioFinalizacion" class="col-lg-3">
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
    <div id="divActualmente" class="col-lg-6">
        <div class="form-outline form-hidden-update mb-4">
            <input type="text" id="actualmente" class="form-control" name="actualmente" readonly />
            <label class="form-label" for="actualmente">Mes/Año de finalización</label>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#divActualmente").hide();
        $("#divCarrera").hide();
        $('#divMesFinalizacion').hide();
        $('#divAnioFinalizacion').hide();

        $("#nivelEstudio").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Nivel de estudio'
        });
        $("#paisId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'País de estudio'
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
        $("#prsArEstudioId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Área de estudio'
        });

        $('#nivelEstudio').change(function(e) {
            if($('#nivelEstudio').val() == "Técnico/Profesional" || $('#nivelEstudio').val() == "Universidad" || $('#nivelEstudio').val() == "Postgrado" || $('#nivelEstudio').val() == "Diplomado" || $('#nivelEstudio').val() == "Curso" || $('#nivelEstudio').val() == "Curso - INSAFORP") {
                $('#nombreCarrera').prop("required", true);
                $('#prsArEstudioId').prop("required", true);    
                $('#divCarrera').show();

                // Cambiar nombre label
                if($('#nivelEstudio').val() == "Diplomado") {
                    $('#labelCarrera').html("Nombre del diplomado");
                    $('#centroEstudio').removeAttr("readonly");
                } else if($('#nivelEstudio').val() == "Curso" || $('#nivelEstudio').val() == "Curso - INSAFORP") {
                    $('#labelCarrera').html("Nombre del curso");
                    if($('#nivelEstudio').val() == "Curso - INSAFORP") {
                        $('#centroEstudio').val('Instituto Salvadoreño de Formación Profesional (INSAFORP)');
                        $('#centroEstudio').prop("readonly", true);
                        document.querySelectorAll('.form-update-insaforp').forEach((formOutline) => {
                            new mdb.Input(formOutline).update();
                        });
                    } else {
                        $('#centroEstudio').removeAttr("readonly");
                    }
                } else {
                    $('#labelCarrera').html("Carrera");
                    $('#centroEstudio').removeAttr("readonly");
                }
            } else {
                $('#nombreCarrera').removeAttr("required");
                $('#prsArEstudioId').removeAttr("required");  
                $('#divCarrera').hide();
                $('#centroEstudio').removeAttr("readonly");
            }
        });

        $("[name='estadoEstudio']").change(function(e) {
            if($('[name="estadoEstudio"]:checked').val() == "Cursando") {
                $('#divMesFinalizacion').hide();
                $('#divAnioFinalizacion').hide();
                $('#actualmente').val("Actualmente");
                $('#divActualmente').show();
                $('#mesFinalizacion').removeAttr("required");
                $('#anioFinalizacion').removeAttr("required");
            } else if($('[name="estadoEstudio"]:checked').val() == "Incompleto") {
                $('#divMesFinalizacion').hide();
                $('#divAnioFinalizacion').hide();
                $('#actualmente').val("Incompleto");
                $('#divActualmente').show();
                $('#mesFinalizacion').removeAttr("required");
                $('#anioFinalizacion').removeAttr("required");
            } else {
                $('#actualmente').val("");
                $('#divActualmente').hide();
                $('#divMesFinalizacion').show();
                $('#divAnioFinalizacion').show();
                $('#mesFinalizacion').prop("required", true);
                $('#anioFinalizacion').prop("required", true);   
            }
            document.querySelectorAll('.form-hidden-update').forEach((formOutline) => {
                new mdb.Input(formOutline).update();
            });
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
                                '<?php echo $txtSuccess; ?>',
                                "success"
                            );
                            $('#tblEmpleadoEstudios').DataTable().ajax.reload(null, false);
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
                $("#modalTitle").html('Editar Estudio - Centro de estudio: <?php echo $dataEditEmpleadoEstudio->centroEstudio; ?>');
                $("#centroEstudio").val('<?php echo $dataEditEmpleadoEstudio->centroEstudio; ?>');
                $("#nivelEstudio").val('<?php echo $dataEditEmpleadoEstudio->nivelEstudio; ?>').trigger('change');
                $("#nombreCarrera").val('<?php echo $dataEditEmpleadoEstudio->nombreCarrera; ?>');
                $("#prsArEstudioId").val('<?php echo $dataEditEmpleadoEstudio->prsArEstudioId; ?>').trigger('change');
                $("#paisId").val('<?php echo $dataEditEmpleadoEstudio->paisId; ?>').trigger('change');
                $("#nombreCarrera").val('<?php echo $dataEditEmpleadoEstudio->nombreCarrera; ?>');
                $("input[name='estadoEstudio'][value='<?php echo $dataEditEmpleadoEstudio->estadoEstudio; ?>']").prop("checked",true).trigger('change');
                $("#mesInicio").val('<?php echo $dataEditEmpleadoEstudio->numMesInicio; ?>').trigger('change');
                $("#anioInicio").val('<?php echo $dataEditEmpleadoEstudio->anioInicio; ?>').trigger('change');
                $("#mesFinalizacion").val('<?php echo $dataEditEmpleadoEstudio->numMesFinalizacion; ?>').trigger('change');
                $("#anioFinalizacion").val('<?php echo $dataEditEmpleadoEstudio->anioFinalizacion; ?>').trigger('change');
        <?php
            } else {
        ?>
                $("#typeOperation").val('insert');
                $("#modalTitle").html('Nuevo Estudio');
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