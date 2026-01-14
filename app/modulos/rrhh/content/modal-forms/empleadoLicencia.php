<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /* arrayFormData 
        Nuevo = nuevo ^ personaId ^ nombreCompleto ^ categoriaLicencia
        Editar = editar ^ prsLicenciaId ^ nombreCompleto ^ categoriaLicencia
    */
    $arrayFormData = explode("^", $_POST["arrayFormData"]);
    $numLicencia = "";

    if($arrayFormData[0] == "editar") {
        $dataEditEmpleadoLicencia = $cloud->row("
            SELECT
                personaId, 
                categoriaLicencia, 
                tipoLicencia, 
                numLicencia, 
                fechaExpiracionLicencia,
                descripcionLicencia
            FROM th_personas_licencias
            WHERE prsLicenciaId = ?
        ", [$arrayFormData[1]]);
        $personaId = $dataEditEmpleadoLicencia->personaId;

        $numLicencia = $dataEditEmpleadoLicencia->numLicencia;
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
        <input type="hidden" id="operation" name="operation" value="empleado-licencia">
        <input type="hidden" id="personaId" name="personaId" value="'.$personaId.'">
    ';

    if($arrayFormData[3] == "conducir") {
        $txtAccion = ($arrayFormData[0] == "editar") ? " actualizada" : "agregada";
        $txtSuccess = "Licencia de Conducir " . $txtAccion . " con éxito.";
        $tableAjax = "tblEmpleadoLicConducir";
        $dataNITPersona = $cloud->row("
            SELECT nit, numIdentidad FROM th_personas
            WHERE personaId = ?
        ",[$personaId]);

        $selectedDuiNit = array("", "");

        if($arrayFormData[0] == "editar") {
            if(strlen($dataEditEmpleadoLicencia->numLicencia) > 10) {
                $selectedDuiNit[1] = "checked";
            } else {
                $selectedDuiNit[0] = "checked";
            }
            $numLicencia = $dataEditEmpleadoLicencia->numLicencia;
        } else {
            $numLicencia = $dataNITPersona->numIdentidad;
            $selectedDuiNit[0] = "checked";
        }
?>
        <div class="form-select-control mb-4">
            <select class="form-select" id="tipoLicencia" name="tipoLicencia" style="width:100%;" required>
                <option></option>
                <?php 
                    $arrayLicencias = array("Licencia Motociclistas", "Licencia Particular", "Licencia Liviana", "Licencia Pesada", "Licencia Pesada-T");
                    // "Licencia Juvenil Vehículos Particulares", "Licencia Juvenil Motociclistas"
                    for ($i=0; $i < count($arrayLicencias); $i++) { 
                        echo '<option value="'.$arrayLicencias[$i].'">'.$arrayLicencias[$i].'</option>';
                    }
                ?>
            </select>
        </div>
        <div class="form-check-validate mb-4">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="duiNit" id="duiNit1" value="DUI" required <?php echo $selectedDuiNit[0]; ?>>
                <label class="form-check-label" for="duiNit1">DUI</label>
            </div>     
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="duiNit" id="duiNit2" value="NIT" required <?php echo $selectedDuiNit[1]; ?>>
                <label class="form-check-label" for="duiNit2">NIT</label>
            </div>   
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="duiNit" id="duiNit3" value="Carnet" required>
                <label class="form-check-label" for="duiNit3">Carnet de residencia</label>
            </div>   
        </div>
        <div class="form-outline form-update-licencia mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="text" id="numLicenciaDUI" class="form-control masked" name="numLicenciaDUI" data-mask="########-#" minlength="10" required />
            <input type="text" id="numLicenciaNIT" class="form-control masked" name="numLicenciaNIT" data-mask="####-######-###-#" minlength="17" required />
            <input type="text" id="numLicenciaCarnet" class="form-control" name="numLicenciaCarnet" required style="display: none;" />
            <label class="form-label" for="numLicencia">Número de licencia</label>
        </div>
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-calendar trailing"></i>
            <input type="text" id="fechaExpiracionLicencia" class="form-control masked" name="fechaExpiracionLicencia" data-mask="##-####" minlength="7" required />
            <label class="form-label" for="fechaExpiracionLicencia">Fecha de expiración</label>
        </div>  
        <script>
            $(document).ready(function() {
                // Si coinciden los campos moverlos al document ready de abajo
                $("#tipoLicencia").select2({
                    placeholder: "Tipo de licencia",
                    dropdownParent: $('#modal-container'),
                    allowClear: true
                });

                $('#fechaExpiracionLicencia').on('change', function() { 
                    $(this).addClass("active"); 
                });

                $("[name='duiNit']").change(function(e) {
            let selected = $('[name="duiNit"]:checked').val();
            
                    if (selected === "DUI") {
                        $("#numLicenciaNIT, #numLicenciaCarnet").hide();
                        $("#numLicenciaDUI").show().val('<?php echo $dataNITPersona->numIdentidad; ?>');
                    } else if (selected === "NIT") {
                        $("#numLicenciaDUI, #numLicenciaCarnet").hide();
                        $("#numLicenciaNIT").show().val('<?php echo $dataNITPersona->nit; ?>');
                    } else if (selected === "Carnet") {
                        $("#numLicenciaDUI, #numLicenciaNIT").hide();
                        $("#numLicenciaCarnet").show().val('').removeAttr("data-mask");
                    }
                    document.querySelectorAll('.form-update-licencia').forEach((formOutline) => {
                        new mdb.Input(formOutline).update();
                    });
                });

                <?php 
                    // Los campos que coincidan en editar con arma moverlos al if de abajo
                    if($arrayFormData[0] == "editar") {
                        if($selectedDuiNit[0] == "checked") {
                            echo '$("#numLicenciaNIT").hide();';
                        } else {
                            echo '$("#numLicenciaDUI").hide();';
                        }
                ?>
                        $("#modalTitle").html('Editar Licencia: Conducir - <?php echo $dataEditEmpleadoLicencia->tipoLicencia . " (".$dataEditEmpleadoLicencia->numLicencia.")"; ?>');
                        $("#tipoLicencia").val('<?php echo $dataEditEmpleadoLicencia->tipoLicencia; ?>').trigger('change');
                        $("#fechaExpiracionLicencia").val('<?php echo $dataEditEmpleadoLicencia->fechaExpiracionLicencia; ?>');
                <?php 
                    } else {
                ?>
                        $("#numLicenciaNIT").hide();
                        $("#modalTitle").html('Nueva Licencia: Conducir');
                <?php 
                    }
                ?>
                $("[name='duiNit']").trigger("change");
            });
        </script>
<?php 
    } else { // Arma
        $txtAccion = ($arrayFormData[0] == "editar") ? " actualizada" : "agregada";
        $txtSuccess = "Licencia de Arma " . $txtAccion . " con éxito.";
        $tableAjax = "tblEmpleadoLicArma";
?>
        <div class="form-outline form-update-licencia mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="text" id="numLicenciaArma" class="form-control masked" name="numLicenciaArma" required />
            <label class="form-label" for="numLicencia">Número de licencia</label>
        </div>
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-calendar trailing"></i>
            <input type="text" id="fechaExpiracionLicencia" class="form-control masked" name="fechaExpiracionLicencia" data-mask="##-####" minlength="7" required />
            <label class="form-label" for="fechaExpiracionLicencia">Fecha de expiración</label>
        </div>  
        <div class="form-outline">
            <i class="fas fa-edit trailing"></i>
            <textarea type="text" id="descripcionLicencia" class="form-control" name="descripcionLicencia"></textarea>
            <label class="form-label" for="descripcionLicencia">Descripción</label>
        </div>
        <script>
            $(document).ready(function() {
                <?php 
                    if($arrayFormData[0] == "editar") {
                ?>
                        $("#modalTitle").html('Editar Licencia: Arma - <?php echo $dataEditEmpleadoLicencia->tipoLicencia . " (".$dataEditEmpleadoLicencia->numLicencia.")"; ?>');
                        $("#numLicenciaArma").val('<?php echo $dataEditEmpleadoLicencia->numLicencia; ?>');
                        $("#fechaExpiracionLicencia").val('<?php echo $dataEditEmpleadoLicencia->fechaExpiracionLicencia; ?>');
                        $("#descripcionLicencia").val('<?php echo $dataEditEmpleadoLicencia->descripcionLicencia; ?>');
                <?php 
                    } else {
                ?>
                        $("#modalTitle").html('Nueva Licencia: Arma');
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
        Maska.create('#frmModal .masked');

        $('#fechaExpiracionLicencia').datepicker({
            format: "mm-yyyy",
            viewMode: "months", 
            minViewMode: "months",
            autoclose: true,
            calendarWeeks : false,
            clearBtn: true,
            disableTouchKeyboard: true,
            todayHighlight: true
        });

        $("#frmModal").validate({
            <?php 
                // Reglas para la Licencia de conducir
                if($arrayFormData[3] == "conducir") {
            ?>
                    messages: {
                        fechaExpiracionLicencia: {
                            minlength: "Formato de fecha no válido"
                        }
                    },
            <?php
                // Reglas para la licencia de arma 
                } else {
            ?>
            <?php 
                }
            ?>
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
            // Los campos que coincidan en editar de conducir moverlos a este apartado
            if($arrayFormData[0] == "editar") {
        ?>
                $("#typeOperation").val('update');
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