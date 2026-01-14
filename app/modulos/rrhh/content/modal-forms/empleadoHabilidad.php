<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /* arrayFormData 
        Nuevo = nuevo ^ personaId ^ nombreCompleto ^ tipoHabilidad
        Editar = editar ^ prsHabilidadId ^ nombreCompleto ^ tipoHabilidad
    */
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    if($arrayFormData[0] == "editar") {
        $dataEditEmpleadoHabilidad = $cloud->row("
            SELECT
                personaId, 
                tipoHabilidad, 
                habilidadPersona, 
                nivelHabilidad
            FROM th_personas_habilidades
            WHERE prsHabilidadId = ?
        ", [$arrayFormData[1]]);
        $personaId = $dataEditEmpleadoHabilidad->personaId;
    } else {
        $personaId = $arrayFormData[1];
    }

    $dataEstadoPersona = $cloud->row("
        SELECT
            estadoPersona
        FROM th_personas
        WHERE personaId = ?
    ",[$personaId]);

    // Agrego aca el flgOtro para que no me dé problemas en idioma/otro por "isset"
    echo '
        <input type="hidden" id="typeOperation" name="typeOperation">
        <input type="hidden" id="operation" name="operation" value="empleado-habilidad">
        <input type="hidden" id="flgOtro" name="flgOtro" value="0">
        <input type="hidden" id="personaId" name="personaId" value="'.$personaId.'">
    ';

    if($arrayFormData[3] == "idioma") {
        $txtAccion = ($arrayFormData[0] == "editar") ? " actualizado" : "agregado";
        $txtSuccess = "Idioma " . $txtAccion . " con éxito.";
        $tableAjax = "tblEmpleadoHabIdiomas";
?>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-select-control mb-4">
                    <select id="habilidadPersona" name="habilidadPersona" style="width: 100%;" required>
                        <option></option>
                        <?php 
                            // Crear una table y obtener "fuente ct..."
                            $idiomas = array("Español","Inglés","Alemán","Portugués","Ruso","Árabe","Chino","Francés");
                            for ($i=0; $i < count($idiomas); $i++) { 
                                echo '<option value="'.$idiomas[$i].'">'.$idiomas[$i].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-select-control mb-4">
                    <select id="nivelHabilidad" name="nivelHabilidad" style="width: 100%;" required>
                        <option></option>
                        <?php 
                            $niveles = array("Nativo", "Básico", "Intermedio", "Avanzado");
                            for ($i=0; $i < count($niveles); $i++) { 
                                echo '<option value="'.$niveles[$i].'">'.$niveles[$i].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>    
        <script>
            $(document).ready(function() {
                $("#habilidadPersona").select2({
                    dropdownParent: $('#modal-container'),
                    placeholder: 'Idioma'
                });
                <?php 
                    if($arrayFormData[0] == "editar") {
                ?>
                        $("#modalTitle").html('Editar Habilidad: Idioma - <?php echo $dataEditEmpleadoHabilidad->habilidadPersona . " (".$dataEditEmpleadoHabilidad->nivelHabilidad.")"; ?>');
                        $("#habilidadPersona").val('<?php echo $dataEditEmpleadoHabilidad->habilidadPersona; ?>').trigger('change');
                <?php 
                    } else {
                ?>
                        $("#modalTitle").html('Nueva Habilidad: Idioma');
                <?php 
                    }
                ?>
            });
        </script>
<?php 
    } else if($arrayFormData[3] == "informática") {
        $txtAccion = ($arrayFormData[0] == "editar") ? " actualizado" : "agregado";
        $txtSuccess = "Conocimiento informático " . $txtAccion . " con éxito.";
        $tableAjax = "tblEmpleadoHabInformatico";
?>
        <div id="divSelectHabilidad" class="row">
            <div class="col-lg-12">
                <div class="form-select-control mb-4">
                    <select id="habilidadPersona" name="habilidadPersona" style="width: 100%;" required>
                        <option></option>
                        <?php 
                            $dataSoftware = $cloud->rows("
                                SELECT
                                    prsSoftwareId,
                                    nombreSoftware
                                FROM cat_personas_software
                                WHERE flgDelete = '0'
                            ");
                            foreach ($dataSoftware as $dataSoftware) {
                                echo '<option value="'.$dataSoftware->nombreSoftware.'">'.$dataSoftware->nombreSoftware.'</option>';
                            }
                        ?>
                    </select>
                    <div class="form-helper text-end">
                        <span class="badge rounded-pill bg-primary" style="cursor: pointer;" onclick="showHideOtro(1);">
                            <i class="fas fa-plus-circle"></i> Otro
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div id="divOtro" class="row">
            <div class="col-lg-12">
                <div class="form-outline form-hidden-update mb-4">
                    <i class="fas fa-laptop trailing"></i>
                    <input type="text" id="nombreOtro" class="form-control" name="nombreOtro" required />
                    <label class="form-label" for="nombreOtro">Conocimiento informático</label>
                    <div class="form-helper text-end">
                        <span class="badge rounded-pill bg-secondary" style="cursor: pointer;" onclick="showHideOtro(0);">
                            <i class="fas fa-times-circle"></i> Cancelar
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-select-control mb-4">
                    <select id="nivelHabilidad" name="nivelHabilidad" style="width: 100%;" required>
                        <option></option>
                        <?php 
                            $niveles = array("Básico", "Intermedio", "Avanzado");
                            for ($i=0; $i < count($niveles); $i++) { 
                                echo '<option value="'.$niveles[$i].'">'.$niveles[$i].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>    
        <script>
            function showHideOtro(tipo) {
                if(tipo == 1) {
                    $("#divOtro").show();
                    $("#divSelectHabilidad").hide();
                    $("#flgOtro").val(1);
                    document.querySelectorAll('.form-hidden-update').forEach((formOutline) => {
                        new mdb.Input(formOutline).update();
                    });
                } else {
                    $("#nombreOtro").val('');
                    $("#divOtro").hide();
                    $("#divSelectHabilidad").show();
                    $("#flgOtro").val(0);
                }
            }

            $(document).ready(function() {
                $("#divOtro").hide();
                $("#habilidadPersona").select2({
                    dropdownParent: $('#modal-container'),
                    placeholder: 'Conocimiento informático'
                });
                <?php 
                    if($arrayFormData[0] == "editar") {
                ?>
                        $("#modalTitle").html('Editar Habilidad: Conocimiento informático - <?php echo $dataEditEmpleadoHabilidad->habilidadPersona . " (".$dataEditEmpleadoHabilidad->nivelHabilidad.")"; ?>');
                        $("#habilidadPersona").val('<?php echo $dataEditEmpleadoHabilidad->habilidadPersona; ?>').trigger('change');
                <?php 
                    } else {
                ?>
                        $("#modalTitle").html('Nueva Habilidad: Conocimiento informático');
                <?php 
                    }
                ?>
            });
        </script>
<?php 
    } else if($arrayFormData[3] == "equipo") {
        $txtAccion = ($arrayFormData[0] == "editar") ? " actualizado" : "agregado";
        $txtSuccess = "Herramienta/Equipo " . $txtAccion . " con éxito.";
        $tableAjax = "tblEmpleadoHabEquipo";
?>
        <div id="divSelectHabilidad" class="row">
            <div class="col-lg-12">
                <div class="form-select-control mb-4">
                    <select id="habilidadPersona" name="habilidadPersona" style="width: 100%;" required>
                        <option></option>
                        <?php 
                            $dataHerrEquipo = $cloud->rows("
                                SELECT
                                    prsHerrEquipoId,
                                    nombreHerrEquipo
                                FROM cat_personas_herr_equipos
                                WHERE flgDelete = '0'
                            ");
                            foreach ($dataHerrEquipo as $dataHerrEquipo) {
                                echo '<option value="'.$dataHerrEquipo->nombreHerrEquipo.'">'.$dataHerrEquipo->nombreHerrEquipo.'</option>';
                            }
                        ?>
                    </select>
                    <div class="form-helper text-end">
                        <span class="badge rounded-pill bg-primary" style="cursor: pointer;" onclick="showHideOtro(1);">
                            <i class="fas fa-plus-circle"></i> Otro
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div id="divOtro" class="row">
            <div class="col-lg-12">
                <div class="form-outline form-hidden-update mb-4">
                    <i class="fas fa-hammer trailing"></i>
                    <input type="text" id="nombreOtro" class="form-control" name="nombreOtro" required />
                    <label class="form-label" for="nombreOtro">Herramienta/Equipo</label>
                    <div class="form-helper text-end">
                        <span class="badge rounded-pill bg-secondary" style="cursor: pointer;" onclick="showHideOtro(0);">
                            <i class="fas fa-times-circle"></i> Cancelar
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-select-control mb-4">
                    <select id="nivelHabilidad" name="nivelHabilidad" style="width: 100%;" required>
                        <option></option>
                        <?php 
                            $niveles = array("Básico", "Intermedio", "Avanzado");
                            for ($i=0; $i < count($niveles); $i++) { 
                                echo '<option value="'.$niveles[$i].'">'.$niveles[$i].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>    
        <script>
            function showHideOtro(tipo) {
                if(tipo == 1) {
                    $("#divOtro").show();
                    $("#divSelectHabilidad").hide();
                    $("#flgOtro").val(1);
                    document.querySelectorAll('.form-hidden-update').forEach((formOutline) => {
                        new mdb.Input(formOutline).update();
                    });
                } else {
                    $("#nombreOtro").val('');
                    $("#divOtro").hide();
                    $("#divSelectHabilidad").show();
                    $("#flgOtro").val(0);
                }
            }

            $(document).ready(function() {
                $("#divOtro").hide();
                $("#habilidadPersona").select2({
                    dropdownParent: $('#modal-container'),
                    placeholder: 'Herramienta/Equipo'
                });
                <?php 
                    if($arrayFormData[0] == "editar") {
                ?>
                        $("#modalTitle").html('Editar Habilidad: Herramienta/Equipo - <?php echo $dataEditEmpleadoHabilidad->habilidadPersona . " (".$dataEditEmpleadoHabilidad->nivelHabilidad.")"; ?>');
                        $("#habilidadPersona").val('<?php echo $dataEditEmpleadoHabilidad->habilidadPersona; ?>').trigger('change');
                <?php 
                    } else {
                ?>
                        $("#modalTitle").html('Nueva Habilidad: Herramienta/Equipo');
                <?php 
                    }
                ?>
            });
        </script>
<?php 
    } else { // Conocimiento/Habilidad
        $txtAccion = ($arrayFormData[0] == "editar") ? " actualizada" : "agregada";
        $txtSuccess = "Conocimiento/Habilidad " . $txtAccion . " con éxito.";
        $tableAjax = "tblEmpleadoHabHabilidad";
?>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-outline mb-4">
                    <i class="fas fa-star trailing"></i>
                    <input type="text" id="habilidadPersona" class="form-control" name="habilidadPersona" required />
                    <label class="form-label" for="habilidadPersona">Conocimiento/Habilidad</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-select-control mb-4">
                    <select id="nivelHabilidad" name="nivelHabilidad" style="width: 100%;" required>
                        <option></option>
                        <?php 
                            $niveles = array("Básico", "Intermedio", "Avanzado");
                            for ($i=0; $i < count($niveles); $i++) { 
                                echo '<option value="'.$niveles[$i].'">'.$niveles[$i].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div> 
        <script>
            $(document).ready(function() {
                <?php 
                    if($arrayFormData[0] == "editar") {
                ?>
                        $("#modalTitle").html('Editar Habilidad: Conocimiento/Habilidad - <?php echo $dataEditEmpleadoHabilidad->habilidadPersona . " (".$dataEditEmpleadoHabilidad->nivelHabilidad.")"; ?>');
                        $("#habilidadPersona").val('<?php echo $dataEditEmpleadoHabilidad->habilidadPersona; ?>');
                <?php 
                    } else {
                ?>
                        $("#modalTitle").html('Nueva Habilidad: Conocimiento/Habilidad');
                <?php 
                    }
                ?>
            });
        </script>
<?php 
    }
?>
<script>
    $(document).ready(function() {
        $("#nivelHabilidad").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Nivel'
        });

        $("#frmModal").validate({
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
                            $('#<?php echo $tableAjax; ?>').DataTable().ajax.reload(null, false);
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
                $("#nivelHabilidad").val('<?php echo $dataEditEmpleadoHabilidad->nivelHabilidad; ?>').trigger('change');
        <?php 
            } else {
        ?>
                $("#typeOperation").val('insert');
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