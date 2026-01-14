<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    // id, nombreUsuario, tipoPersona
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    echo '
        <input type="hidden" id="typeOperation" name="typeOperation" value="update">
        <input type="hidden" id="operation" name="operation" value="editar-usuario">
        <input type="hidden" id="flgPersona" name="flgPersona" value="'.$arrayFormData[2].'">
    ';

    if($arrayFormData[2] == "Empleado") {
        $dataUsuario = $cloud->row("
            SELECT
                correo
            FROM conf_usuarios
            WHERE usuarioId = ?
        ", [$arrayFormData[0]]);
?>
        <div class="form-outline mb-4">
            <i class="fas fa-envelope trailing"></i>
            <input type="email" id="correo" class="form-control" name="correo" value="<?php echo $dataUsuario->correo; ?>" required />
            <label class="form-label" for="correo">Correo electrónico</label>
        </div>
        <script>
            $(document).ready(function() {
                $("#frmModal").validate({
                    submitHandler: function(form) {
                        let mailContent = $("#correo").val().toString().split("@");
                        if(mailContent[1] == "alina.jewelry") {
                            button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                            asyncDoDataReturn(
                                "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                                $("#frmModal").serialize(),
                                function(data) {
                                    button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                                    let arrayData = data.split("^");
                                    if(arrayData[0] == "success") {
                                        mensaje(
                                            "Operación completada:",
                                            arrayData[1],
                                            "success"
                                        );
                                        $('#tblUsuariosEmpleados').DataTable().ajax.reload(null, false);
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
                        } else {
                            mensaje("Aviso:", "El correo debe ser institucional (@alina.jewelry).", "warning");
                        }
                    }
                });
            });
        </script>
<?php 
    } else {
        $dataUsuario = $cloud->row("
            SELECT
                per.personaId AS personaId,
                per.nombre1 AS nombre1,
                per.nombre2 AS nombre2,
                per.apellido1 AS apellido1,
                per.apellido2 AS apellido2,
                per.sexo AS sexo,
                per.prsTipoId AS tipoPersonaId,
                per.dui AS dui,
                per.fechaNacimiento AS fechaNacimiento,
                us.correo AS correo
            FROM conf_usuarios us
            JOIN th_personas per ON per.personaId = us.personaId
            WHERE us.usuarioId = ?
        ", [$arrayFormData[0]]);
?>
        <input type="hidden" id="personaId" name="personaId" value="<?php echo $dataUsuario->personaId; ?>">
        <div class="row">
            <div class="col-lg-6">
                <div class="form-outline mb-4">
                    <i class="fas fa-user-alt trailing"></i>
                    <input type="text" id="nombre1" class="form-control" name="nombre1" value="<?php echo $dataUsuario->nombre1; ?>" required />
                    <label class="form-label" for="nombre1">Primer nombre</label>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-outline mb-4">
                    <i class="fas fa-user-alt trailing"></i>
                    <input type="text" id="nombre2" class="form-control" name="nombre2" value="<?php echo $dataUsuario->nombre2; ?>" />
                    <label class="form-label" for="nombre2">Segundo nombre</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-outline mb-4">
                    <i class="fas fa-male trailing"></i>
                    <input type="text" id="apellido1" class="form-control" name="apellido1" value="<?php echo $dataUsuario->apellido1; ?>" required />
                    <label class="form-label" for="apellido1">Apellido paterno</label>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-outline mb-4">
                    <i class="fas fa-female trailing"></i>
                    <input type="text" id="apellido2" class="form-control" name="apellido2" value="<?php echo $dataUsuario->apellido2; ?>" />
                    <label class="form-label" for="apellido2">Apellido materno</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-select-control mb-4">
                    <select id="sexo" name="sexo" style="width: 100%;" required>
                        <option></option>
                        <?php  
                            $arraySexoSelected = ($dataUsuario->sexo == "F") ? array("selected","") : array("", "selected");
                        ?>
                        <option value="F" <?php echo $arraySexoSelected[0]; ?>>Femenino</option>
                        <option value="M" <?php echo $arraySexoSelected[1]; ?>>Masculino</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-select-control mb-4">
                    <select id="tipoPersona" name="tipoPersona" style="width: 100%;" required>
                        <option></option>
                        <?php 
                            $dataTiposPersona = $cloud->rows("
                                SELECT
                                    prsTipoId,
                                    personaTipo
                                FROM cat_personas_tipo
                                WHERE flgDelete = '0'
                            ", []);
                            foreach ($dataTiposPersona as $dataTiposPersona) {
                                $disabled = ($dataTiposPersona->prsTipoId == 1) ? "disabled" : "";
                                $selected = ($dataTiposPersona->prsTipoId == 2) ? "selected" : "";
                                echo '<option value="'.$dataTiposPersona->prsTipoId.'" '.$selected.' '.$disabled.'>'.$dataTiposPersona->personaTipo.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-outline mb-4">
                    <i class="fas fa-address-card trailing"></i>
                    <input type="text" id="dui" class="form-control masked" name="dui" data-mask="########-#" data-rule-minlength="10" value="<?php echo $dataUsuario->dui; ?>" required />
                    <label class="form-label" for="dui">DUI</label>  
                </div>      
            </div>
            <div class="col-lg-6">
                <div class="form-outline mb-4 input-daterange"> 
                    <i class="fa fa-calendar trailing"></i> 
                    <input type="text" id="fechaNacimiento" name="fechaNacimiento" class="form-control text-start masked" data-mask="##-##-####" value="<?php echo date("d-m-Y", strtotime($dataUsuario->fechaNacimiento)); ?>" required /> 
                    <label class="form-label" id="start-p" for="fechaNacimiento">Fecha de nacimiento</label> 
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-outline mb-4">
                    <i class="fas fa-envelope trailing"></i>
                    <input type="email" id="correo" class="form-control" name="correo" value="<?php echo $dataUsuario->correo; ?>" required />
                    <label class="form-label" for="correo">Correo electrónico</label>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                Maska.create('#frmModal .masked');
                $("#sexo").select2({
                    dropdownParent: $('#modal-container'),
                    placeholder: 'Sexo'
                });

                $('#tipoPersona').select2({
                    dropdownParent: $('#modal-container'),
                    placeholder: 'Tipo Persona'
                });

                $('#fechaNacimiento').on('change', function() { 
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

                $("#frmModal").validate({
                    messages: {
                        dui: {
                            minlength: "Número de DUI no válido"
                        }
                    },
                    submitHandler: function(form) {
                        button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                        asyncDoDataReturn(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                            $("#frmModal").serialize(),
                            function(data) {
                                button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                                let arrayData = data.split("^");
                                if(arrayData[0] == "success") {
                                    mensaje(
                                        "Operación completada:",
                                        arrayData[1],
                                        "success"
                                    );
                                    $('#tblUsuariosExternos').DataTable().ajax.reload(null, false);
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
            });
        </script>
<?php 
    }
?>