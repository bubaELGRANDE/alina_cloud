<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    // tipoSolicitud ^ nombrePersona ^ dui ^ fechaNacimiento ^ correo ^ solicitudAccesoId ^ Externa
    $arrayFormData = explode("^", $_POST["arrayFormData"]);
    $fechaNacimiento = new DateTime($arrayFormData[3]);
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
                        echo '<option value="'.$dataTiposPersona->prsTipoId.'" '.$disabled.'>'.$dataTiposPersona->personaTipo.'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="form-outline mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="text" id="dui" class="form-control masked" name="dui" data-mask="########-#" data-rule-minlength="10" value="<?php echo $arrayFormData[2]; ?>" required />
            <label class="form-label" for="dui">DUI</label>  
        </div>      
    </div>
    <div class="col-lg-6">
        <div class="form-outline mb-4 input-daterange"> 
            <i class="fa fa-calendar trailing"></i> 
            <input type="text" id="fechaNacimiento" name="fechaNacimiento" class="form-control text-start masked" data-mask="##-##-####" value="<?php echo $fechaNacimiento->format('d-m-Y'); ?>" required /> 
            <label class="form-label" id="start-p" for="fechaNacimiento">Fecha de nacimiento</label> 
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="form-outline mb-4">
            <i class="fas fa-envelope trailing"></i>
            <input type="email" id="mailRegister" class="form-control" name="mailRegister" value="<?php echo $arrayFormData[4]; ?>" required />
            <label class="form-label" for="mailRegister">Correo electrónico</label>
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
                procesarSolicitudExterna(
                    '<?php echo $arrayFormData[0]; ?>', 
                    '<?php echo $arrayFormData[1]; ?>', 
                    '<?php echo $arrayFormData[2]; ?>', 
                    '<?php echo $arrayFormData[3]; ?>', 
                    '<?php echo $arrayFormData[4]; ?>', 
                    '<?php echo $arrayFormData[5]; ?>', 
                    $("#frmModal").serialize()
                );
            }
        });
    });
</script>