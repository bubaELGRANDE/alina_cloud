<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    $tipoPersona = $_POST["arrayFormData"];

    echo '
        <input type="hidden" id="typeOperation" name="typeOperation" value="insert">
        <input type="hidden" id="operation" name="operation" value="nuevo-usuario">
        <input type="hidden" id="flgPersona" name="flgPersona" value="'.$tipoPersona.'">
    ';

    if($tipoPersona == "Empleado") {
?>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-select-control mb-4">
                    <select id="personaId" name="personaId" style="width: 100%;" required>
                        <option></option>
                        <?php 
                            $dataPersonasNoUser = $cloud->rows("
                                SELECT
                                    p.personaId AS personaId,
                                    CONCAT(
                                        IFNULL(p.apellido1, '-'),
                                        ' ',
                                        IFNULL(p.apellido2, '-'),
                                        ', ',
                                        IFNULL(p.nombre1, '-'),
                                        ' ',
                                        IFNULL(p.nombre2, '-')
                                    ) AS nombrePersona,
                                    us.estadoUsuario AS estadoUsuario
                                FROM th_personas p
                                LEFT JOIN conf_usuarios us ON us.personaId = p.personaId
                                WHERE us.usuarioId IS NULL OR us.estadoUsuario = 'Pendiente' AND p.estadoPersona = 'Activo' AND p.flgDelete = '0'
                            ");
                            foreach ($dataPersonasNoUser as $dataPersonasNoUser) {
                                if($dataPersonasNoUser->estadoUsuario == "Pendiente") { // Se debe seguir el proceso de aprobar la solicitud, por lo que no se podrá seleccionar
                                    $disabledSolicitud = "disabled";
                                    $txtSolicitud = " (Solicitud de acceso)";
                                } else {
                                    $disabledSolicitud = ""; $txtSolicitud = "";
                                }
                                echo '<option value="'.$dataPersonasNoUser->personaId.'" '.$disabledSolicitud.'>'.$dataPersonasNoUser->nombrePersona.$txtSolicitud.'</option>';
                            }
                        ?>
                    </select>
                </div>                
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-outline mb-4">
                    <i class="fas fa-envelope trailing"></i>
                    <input type="email" id="correo" class="form-control" name="correo" required />
                    <label class="form-label" for="correo">Correo electrónico</label>
                    <input type="hidden" id="flgInsertCorreo" name="flgInsertCorreo" value="1">
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $("#personaId").select2({
                    dropdownParent: $('#modal-container'),
                    placeholder: 'Empleado'                    
                });

                $("#personaId").change(function(e) {
                    e.preventDefault();
                    asyncDoDataReturn(
                        "<?php echo $_SESSION['currentRoute']; ?>content/divs/getCorreoPersona", 
                        {personaId: $(this).val()},
                        function(data) {
                            if(data == "") {
                                $("#flgInsertCorreo").val(1);
                            } else {
                                $("#flgInsertCorreo").val(0);
                            }
                            $("#correo").val(data);
                            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                                new mdb.Input(formOutline).init();
                            });
                        }
                    );
                });

                $("#frmModal").validate({
                    submitHandler: function(form) {
                        let mailContent = $("#correo").val().toString().split("@");
                        if(mailContent[1] == "alina.jewelry") {
                            button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                            asyncDoDataReturn(
                                "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                                $("#frmModal").serialize(),
                                function(data) {
                                    button_icons("btnModalAccept", "fas fa-user-plus", "Crear Usuario", "enabled");
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
?>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-outline mb-4">
                    <i class="fas fa-user-alt trailing"></i>
                    <input type="text" id="nombre1" class="form-control" name="nombre1" required />
                    <label class="form-label" for="nombre1">Primer nombre</label>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-outline mb-4">
                    <i class="fas fa-user-alt trailing"></i>
                    <input type="text" id="nombre2" class="form-control" name="nombre2" />
                    <label class="form-label" for="nombre2">Segundo nombre</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-outline mb-4">
                    <i class="fas fa-male trailing"></i>
                    <input type="text" id="apellido1" class="form-control" name="apellido1" required />
                    <label class="form-label" for="apellido1">Apellido paterno</label>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-outline mb-4">
                    <i class="fas fa-female trailing"></i>
                    <input type="text" id="apellido2" class="form-control" name="apellido2" />
                    <label class="form-label" for="apellido2">Apellido materno</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-select-control mb-4">
                    <select id="sexo" name="sexo" style="width: 100%;" required>
                        <option></option>
                        <option value="F">Femenino</option>
                        <option value="M">Masculino</option>
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
                    <input type="text" id="dui" class="form-control masked" name="dui" data-mask="########-#" data-rule-minlength="10" required />
                    <label class="form-label" for="dui">DUI</label>  
                </div>      
            </div>
            <div class="col-lg-6">
                <div class="form-outline mb-4 input-daterange"> 
                    <i class="fa fa-calendar trailing"></i> 
                    <input type="text" id="fechaNacimiento" name="fechaNacimiento" class="form-control text-start masked" data-mask="##-##-####" required /> 
                    <label class="form-label" id="start-p" for="fechaNacimiento">Fecha de nacimiento</label> 
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-outline mb-4">
                    <i class="fas fa-envelope trailing"></i>
                    <input type="email" id="correo" class="form-control" name="correo" required />
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
                                button_icons("btnModalAccept", "fas fa-user-plus", "Crear Usuario", "enabled");
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