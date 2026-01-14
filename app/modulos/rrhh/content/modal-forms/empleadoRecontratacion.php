<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
        arrayFormData = personaId
    */  
   
    $dataEmpleado = $cloud->row("
        SELECT
            per.nombre1 AS nombre1, 
            per.nombre2 AS nombre2, 
            per.nombre3 AS nombre3, 
            per.apellido1 AS apellido1, 
            per.apellido2 AS apellido2, 
            per.apellido3 AS apellido3, 
            per.docIdentidad AS docIdentidad,
            per.numIdentidad AS numIdentidad, 
            per.fechaExpiracionIdentidad AS fechaExpiracionIdentidad,
            per.nit AS nit, 
            per.nombreOrganizacionIdAFP AS nombreOrganizacionIdAFP,
            per.nup AS nup,
            per.nombreOrganizacionIdISSS AS nombreOrganizacionIdISSS,
            per.numISSS AS numISSS,
            per.fechaNacimiento AS fechaNacimiento, 
            per.sexo AS sexo, 
            per.estadoCivil AS estadoCivil, 
            per.paisId AS paisId, 
            pa.pais AS nacionalidad,
            pa.iconBandera AS iconBandera,
            per.paisMunicipioIdDUI AS paisMunicipioIdDUI, 
            pmdui.municipioPais AS municipioDUI,
            pddui.paisDepartamentoId AS paisDepartamentoIdDUI,
            pddui.departamentoPais AS departamentoDUI,
            per.zonaResidenciaDUI AS zonaResidenciaDUI, 
            per.paisMunicipioIdActual AS paisMunicipioIdActual, 
            pmactual.municipioPais AS municipioActual,
            pdactual.paisDepartamentoId AS paisDepartamentoIdActual,
            pdactual.departamentoPais AS departamentoActual,
            per.zonaResidenciaActual AS zonaResidenciaActual, 
            per.vehiculoPropio AS vehiculoPropio, 
            per.vehiculosPropios AS vehiculosPropios, 
            per.estadoPersona AS estadoPersona
        FROM th_personas per
        LEFT JOIN cat_paises pa ON pa.paisId = per.paisId
        LEFT JOIN cat_paises_municipios pmdui ON pmdui.paisMunicipioId = per.paisMunicipioIdDUI
        LEFT JOIN cat_paises_departamentos pddui ON pddui.paisDepartamentoId = pmdui.paisDepartamentoId
        LEFT JOIN cat_paises_municipios pmactual ON pmactual.paisMunicipioId = per.paisMunicipioIdActual
        LEFT JOIN cat_paises_departamentos pdactual ON pdactual.paisDepartamentoId = pmactual.paisDepartamentoId
        WHERE personaId = ?
    ", [$_POST['arrayFormData']]);
    $personaId = $_POST['arrayFormData'];
    $txtSuccess = "Recontratación del empleado realizada con éxito. Recuerde crear un nuevo expediente.";
?>
<input type="hidden" id="typeOperation" name="typeOperation">
<input type="hidden" id="operation" name="operation" value="empleado-recontratacion">
<input type="hidden" id="personaId" name="personaId" value="<?php echo $personaId; ?>">
<input type="hidden" id="flgMunicipio" name="flgMunicipio" value="0">

<label class="mb-2">Nombres</label>
<div class="row">
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-user trailing"></i>
            <input type="text" id="nombre1" class="form-control" name="nombre1" required />
            <label class="form-label" for="nombre1">Nombre 1</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-user trailing"></i>
            <input type="text" id="nombre2" class="form-control" name="nombre2" />
            <label class="form-label" for="nombre2">Nombre 2</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline">
            <i class="fas fa-user trailing"></i>
            <input type="text" id="nombre3" class="form-control" name="nombre3" />
            <label class="form-label" for="nombre3">Nombre 3</label>
        </div>
    </div>
</div>
<label class="mb-2">Apellidos</label>
<div class="row">
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-user trailing"></i>
            <input type="text" id="apellido1" class="form-control" name="apellido1" required />
            <label class="form-label" for="apellido1">Apellido 1</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-user trailing"></i>
            <input type="text" id="apellido2" class="form-control" name="apellido2" />
            <label class="form-label" for="apellido2">Apellido 2</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline">
            <i class="fas fa-user trailing"></i>
            <input type="text" id="apellido3" class="form-control" name="apellido3" />
            <label class="form-label" for="apellido3">Apellido 3</label>
        </div>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-6">
        <label class="mb-2">Datos personales</label>
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-user trailing"></i>
            <input type="text" id="fechaNac" class="form-control masked" name="fechaNac" data-mask="##-##-####" minlength="10" required />
            <label class="form-label" for="fechaNac">Fecha de nacimiento</label>
        </div>
        <div class="form-select-control mb-4">
            <select class="form-select" id="sexo" name="sexo" style="width:100%;" required>
                <option></option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
            </select>
        </div>
        <div class="form-select-control mb-4">
            <select class="form-select" id="estCivil" name="estCivil" style="width:100%;" required>
                <option></option>
                <option value="Casado">Casado/a</option>
                <option value="Divorciado/a">Divorciado/a</option>
                <option value="Viudo/a">Viudo/a</option>
                <option value="Soltero/a">Soltero/a</option>
            </select>
        </div>
        <div class="form-select-control mb-4">
            <select class="form-select" id="pais" name="pais" style="width:100%;" required>
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
    <div class="col-md-6">
        <label class="mb-2">Documentos</label>
        <div class="form-check-validate mb-4">
            <?php 
                $docIdentidad = array("DUI", "Carné de residencia");
                for ($i=0; $i < count($docIdentidad); $i++) { 
                    echo '
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="docIdentidad" id="docIdentidad'.$i.'" value="'.$docIdentidad[$i].'" required>
                            <label class="form-check-label" for="docIdentidad'.$i.'">'.$docIdentidad[$i].'</label>
                        </div>                    
                    ';
                }
            ?>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-outline mb-4">
                    <i class="fas fa-address-card trailing"></i>
                    <input type="text" id="numIdentidad" class="form-control masked masked-numIdentidad" name="numIdentidad" required readonly />
                    <label id="labelNumIdentidad" class="form-label" for="numIdentidad">Número de Identidad</label>
                    <div id="leyendaNumIdentidad" class="form-helper"></div>
                </div>                
            </div>
            <div class="col-md-6">
                <div class="form-outline mb-4 input-daterange">
                    <i class="fas fa-calendar trailing"></i>
                    <input type="text" id="fechaExpiracionIdentidad" class="form-control masked" name="fechaExpiracionIdentidad" data-mask="##-####" minlength="7" required />
                    <label class="form-label" for="fechaExpiracionIdentidad">Fecha de expiración</label>
                </div>         
            </div>
        </div>
        <div class="form-outline mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="text" id="nit" class="form-control masked" name="nit" required data-mask="####-######-###-#" minlength="17" />
            <label class="form-label" for="nit">NIT</label>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="nombreOrganizacionIdAFP" name="nombreOrganizacionIdAFP" style="width:100%;" required>
                        <option></option>
                        <?php 
                            $dataNombresAFP = $cloud->rows("
                                SELECT 
                                    nombreOrganizacionId, 
                                    abreviaturaOrganizacion
                                FROM cat_nombres_organizaciones 
                                WHERE flgDelete = 0 AND tipoOrganizacion = ?
                            ",['AFP']);
                            foreach($dataNombresAFP as $dataNombresAFP){
                                echo '<option value="'. $dataNombresAFP->nombreOrganizacionId .'">' . $dataNombresAFP->abreviaturaOrganizacion . '</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-outline mb-4">
                    <i class="fas fa-address-card trailing"></i>
                    <input type="text" id="nup" class="form-control masked" name="nup" minlength="12" data-mask="############" required />
                    <label class="form-label" for="nup">NUP</label>
                </div>
            </div>
        </div>
        <div class="form-outline mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="text" id="numISSS" class="form-control masked" name="numISSS" minlength="9" data-mask="#########" required />
            <label class="form-label" for="numISSS">Número de ISSS</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" value="Si" id="vehiculo" name="vehiculo">
            <label class="form-check-label" for="vehiculo">Vehículo propio<label>
        </div>
        <div id="tipos-vehiculos" style="display:none;">
        <label for="">Tipo de vehiculo:</label>
            <?php
                $tiposVehiculos = array("4 puertas", "2 puertas", "Pick-up", "4x4", "Microbus", "Camioneta", "Motocicleta");

                foreach($tiposVehiculos as $item){
                    echo '
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="' . $item . '" value="' . $item .'" name="listaVehiculos[]">
                            <label class="form-check-label" for="' . $item . '">' . $item . '</label>
                        </div>
                    ';
                }
            ?>
            
        </div>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="row mb-2">
            <div class="col-md-8">
                Lugar de residencia: Según DUI
            </div>
            <div class="col-md-4 text-end">
                <span id="btnCopiarSegunActual" class="badge rounded-pill bg-primary" style="cursor: pointer;">
                    <i class="fas fa-exchange-alt"></i> Copiar residencia actual
                </span>
            </div>
        </div>
        <div class="row justify-content-md-center">
            <div class="col-md-6">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="departamentoDUI" name="departamentoDUI" style="width:100%;" required>
                        <option></option>
                        <?php $dataDep = $cloud->rows("
                            SELECT paisDepartamentoId, departamentoPais FROM cat_paises_departamentos WHERE flgDelete = 0 AND paisId = 61
                        ");
                            foreach($dataDep as $depto){
                                echo '<option value="'. $depto->paisDepartamentoId .'">' . $depto->departamentoPais . '</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="municipioDUI" name="municipioDUI" style="width:100%;" required>
                        <option></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-outline">
            <i class="fas fa-map-marker-alt trailing"></i>
            <textarea type="text" id="direccionDUI" class="form-control" name="direccionDUI" required ></textarea>
            <label class="form-label" for="direccionDUI">Dirección según DUI</label>
        </div>
    </div>   
    <div class="col-lg-6 mb-4">
        <div class="row mb-2">
            <div class="col-md-8">
                Lugar de residencia: Actual
            </div>
            <div class="col-md-4 text-end">
                <span id="btnCopiarSegunDUI" class="badge rounded-pill bg-primary" style="cursor: pointer;">
                    <i class="fas fa-exchange-alt"></i> Copiar residencia DUI
                </span>
            </div>
        </div>
        <div class="row justify-content-md-center">
            <div class="col-md-6">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="departamentoActual" name="departamentoActual" style="width:100%;" required>
                        <option></option>
                        <?php $dataDep = $cloud->rows("
                            SELECT paisDepartamentoId, departamentoPais FROM cat_paises_departamentos WHERE flgDelete = 0 AND paisId = 61
                        ");
                            foreach($dataDep as $depto){
                                echo '<option value="'. $depto->paisDepartamentoId .'">' . $depto->departamentoPais . '</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="municipioActual" name="municipioActual" style="width:100%;" required>
                        <option></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-outline form-update-direccion">
            <i class="fas fa-map-marker-alt trailing"></i>
            <textarea type="text" id="direccionActual" class="form-control" name="direccionActual" required ></textarea>
            <label class="form-label" for="direccionActual">Dirección actual</label>
        </div>
    </div>   
</div>
<hr>
<div class="row">
    <label class="mb-2">Recontratación</label>
    <div class="col-md-6">
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-calendar-check trailing"></i>
            <input type="text" id="fechaRecontratacion" class="form-control masked" name="fechaRecontratacion" data-mask="##-##-####" required />
            <label class="form-label" for="fechaRecontratacion">Fecha de recontratación</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-edit trailing"></i>
            <textarea class="form-control" id="justificacionRecontratacion" name="justificacionRecontratacion" rows="4" required></textarea>
            <label class="form-label" for="justificacionRecontratacion">Justificación</label>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        Maska.create('#frmModal .masked');
        $("#pais").select2({
            placeholder: "Nacionalidad",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#departamentoDUI").select2({
            placeholder: "Seleccionar departamento",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#municipioDUI").select2({
            placeholder: "Seleccionar municipio",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#departamentoActual").select2({
            placeholder: "Seleccionar departamento",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#municipioActual").select2({
            placeholder: "Seleccionar municipio",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#estCivil").select2({
            placeholder: "Estado civil",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#sexo").select2({
            placeholder: "Sexo",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#nombreOrganizacionIdAFP").select2({
            placeholder: "AFP",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $('#fechaNac').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            calendarWeeks : false,
            clearBtn: true,
            disableTouchKeyboard: true,
            todayHighlight: true
        });

        $('#fechaNac').on('change', function() { 
            $(this).addClass("active"); 
        });

        $('#fechaExpiracionIdentidad').datepicker({
            format: "mm-yyyy",
            viewMode: "months", 
            minViewMode: "months",
            autoclose: true,
            calendarWeeks : false,
            clearBtn: true,
            disableTouchKeyboard: true,
            todayHighlight: true
        });

        $('#vehiculo').on('change', function() { 
            $("#tipos-vehiculos").toggle(); 
        });

        $('#fechaExpiracionIdentidad').on('change', function() { 
            $(this).addClass("active"); 
        });

        $('#fechaRecontratacion').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            calendarWeeks : false,
            clearBtn: true,
            disableTouchKeyboard: true,
            todayHighlight: true
        });

        $('#fechaRecontratacion').on('change', function() { 
            $(this).addClass("active"); 
        });

        $("#departamentoDUI").on("change", function() {
            asyncSelect(
                "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarMunicipios",
                {
                    depto: $(this).val()
                },
                `municipioDUI`,
                function() {
                    $("#municipioDUI").val('<?php echo $dataEmpleado->paisMunicipioIdDUI; ?>').trigger('change');
                    if($("#flgMunicipio").val() == "1") {
                        $("#municipioDUI").val($("#municipioActual").val()).trigger('change');
                    } else {
                    }
                }
            );
        });

        $("#departamentoActual").on("change", function() {
            asyncSelect(
                "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarMunicipios",
                {
                    depto: $(this).val()
                },
                `municipioActual`,
                function() {
                    $("#municipioActual").val('<?php echo $dataEmpleado->paisMunicipioIdActual; ?>').trigger('change');
                    if($("#flgMunicipio").val() == "1") {
                        $("#municipioActual").val($("#municipioDUI").val()).trigger('change');
                    } else {
                    }
                }
            );
        });

        $("#btnCopiarSegunDUI").click(function(event) {
            $("#departamentoActual").val($("#departamentoDUI").val()).trigger('change');
            $("#flgMunicipio").val(1);
            $("#direccionActual").val($("#direccionDUI").val());
            document.querySelectorAll('.form-update-direccion').forEach((formOutline) => {
                new mdb.Input(formOutline).update();
            });
        });

        $("#btnCopiarSegunActual").click(function(event) {
            $("#departamentoDUI").val($("#departamentoActual").val()).trigger('change');
            $("#flgMunicipio").val(1);
            $("#direccionDUI").val($("#direccionActual").val());
            document.querySelectorAll('.form-update-direccion').forEach((formOutline) => {
                new mdb.Input(formOutline).update();
            });
        });

        $("[name='docIdentidad']").change(function(e) {
            if($('[name="docIdentidad"]:checked').val() == "DUI") {
                $("#numIdentidad").removeAttr("readonly");
                $("#labelNumIdentidad").html("DUI");
                $("#numIdentidad").val('');
                $("#leyendaNumIdentidad").html("");
                Maska.create('.masked-numIdentidad', {
                    mask: '########-#'
                });
                $("#numIdentidad").attr("minlength", 10);
            } else {
                $("#numIdentidad").removeAttr("readonly");
                $("#labelNumIdentidad").html("Carné de residencia");
                $("#numIdentidad").val('');
                $("#leyendaNumIdentidad").html("Digite con guiones");
                var mask = Maska.create('.masked-numIdentidad');
                mask.destroy();
                $("#numIdentidad").removeAttr("minlength");
            }
        });

        $("#frmModal").validate({
            messages: {
                fechaNac: {
                    minlength: "Formato de fecha no válido"
                },
                numIdentidad: {
                    minlength: "Debe contener 9 digitos" // Se agrega automático o se quita si es Carné
                },
                nup: {
                    minlength: "Debe contener 12 dígitos"
                },
                numISSS: {
                    minlength: "Debe contener 9 dígitos"
                },
                fechaExpiracionIdentidad: {
                    minlength: "Formato de fecha no válido"
                },
                nit: {
                    minlength: "Debe contener 14 digitos"
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
                            $("#tblEmpleados").DataTable().ajax.reload(null, false);
                            $("#tblEmpleadosInactivos").DataTable().ajax.reload(null, false);

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

        $("#typeOperation").val('update');
        $("#modalTitle").html('Recontratación de Empleado: <?php echo $dataEmpleado->apellido1 . " " . $dataEmpleado->apellido2 . ", " . $dataEmpleado->nombre1 . " " .$dataEmpleado->nombre2; ?>');

        $("#nombre1").val('<?php echo $dataEmpleado->nombre1; ?>');
        $("#nombre2").val('<?php echo $dataEmpleado->nombre2; ?>');
        $("#nombre3").val('<?php echo $dataEmpleado->nombre3; ?>');
        $("#apellido1").val('<?php echo $dataEmpleado->apellido1; ?>');
        $("#apellido2").val('<?php echo $dataEmpleado->apellido2; ?>');
        $("#apellido3").val('<?php echo $dataEmpleado->apellido3; ?>');
        $("#fechaNac").val('<?php echo date("d-m-Y", strtotime($dataEmpleado->fechaNacimiento)); ?>');
        $("#sexo").val('<?php echo $dataEmpleado->sexo; ?>').trigger('change');
        $("#estCivil").val('<?php echo $dataEmpleado->estadoCivil; ?>').trigger('change');
        $("#pais").val('<?php echo $dataEmpleado->paisId; ?>').trigger('change');
        $("input[name='docIdentidad'][value='<?php echo $dataEmpleado->docIdentidad; ?>']").prop("checked",true).trigger('change');
        $("#numIdentidad").val('<?php echo $dataEmpleado->numIdentidad; ?>');
        $("#fechaExpiracionIdentidad").val('<?php echo $dataEmpleado->fechaExpiracionIdentidad; ?>');

        $("#nit").val('<?php echo $dataEmpleado->nit; ?>');
        $("#nombreOrganizacionIdAFP").val('<?php echo $dataEmpleado->nombreOrganizacionIdAFP; ?>').trigger('change');
        $("#nup").val('<?php echo $dataEmpleado->nup; ?>');
        $("#numISSS").val('<?php echo $dataEmpleado->numISSS; ?>');
        if('<?php echo $dataEmpleado->vehiculoPropio; ?>' == 'Si'){
            $("#vehiculo").prop("checked", true).trigger('change');
        }
        <?php 
        $arrayVehiculos = explode(",", $dataEmpleado->vehiculosPropios);
        foreach($arrayVehiculos as $item){
            //echo '$("#'.$item.'").prop("checked", true).trigger("change");';
        ?>
            $("input[value='<?php echo $item; ?>']").prop("checked", true).trigger("change");
        <?php } ?>
        $("#departamentoDUI").val('<?php echo $dataEmpleado->paisDepartamentoIdDUI; ?>').trigger('change');
        $("#direccionDUI").val('<?php echo $dataEmpleado->zonaResidenciaDUI; ?>');
        $("#departamentoActual").val('<?php echo $dataEmpleado->paisDepartamentoIdActual; ?>').trigger('change');
        $("#direccionActual").val('<?php echo $dataEmpleado->zonaResidenciaActual; ?>');
        
    });
</script>
