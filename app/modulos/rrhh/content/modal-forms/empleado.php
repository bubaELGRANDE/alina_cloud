<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
        arrayFormData
            Nuevo = nuevo ^ 0
            Editar = editar ^ personaId
    */  
    $arrayFormData = explode("^", $_POST["arrayFormData"]);
    $fechaExpiracionIdentidad = "";
    $flgAFPHomologacion = "No";

    if($arrayFormData[0] == "editar") {
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
                expedimuni.paisDepartamentoId AS paisDepartamentoIdExpedicion,
                per.paisMunicipioIdExpedicion AS paisMunicipioIdExpedicion,
                per.fechaExpedicionIdentidad AS fechaExpedicionIdentidad,
                per.nit AS nit, 
                per.nombreOrganizacionIdAFP AS nombreOrganizacionIdAFP,
                per.nup AS nup,
                per.nombreOrganizacionIdISSS AS nombreOrganizacionIdISSS,
                per.numISSS AS numISSS,
                per.fechaNacimiento AS fechaNacimiento, 
                per.sexo AS sexo, 
                per.estadoCivil AS estadoCivil, 
                per.tipoSangre AS tipoSangre,
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
            LEFT JOIN cat_paises_municipios expedimuni ON expedimuni.paisMunicipioId = per.paisMunicipioIdExpedicion
            WHERE personaId = ?
        ", [$arrayFormData[1]]);
        $personaId = $arrayFormData[1];
        $txtSuccess = "Se ha actualizado con éxito los datos de empleado.";

        // mm-YYYY pasa a YYYY-mm porque el input month lo recibe de esa manera
        $arrayFechaExpiracion = explode("-", $dataEmpleado->fechaExpiracionIdentidad);
        $fechaExpiracionIdentidad = $arrayFechaExpiracion[1] . "-" . $arrayFechaExpiracion[0];
        
        // Este if es porque estos campos son nuevos y puede dar error
        if($dataEmpleado->fechaExpedicionIdentidad == "") {
            $fechaExpedicionIdentidad = "";
        } else {
            $fechaExpedicionIdentidad = $dataEmpleado->fechaExpedicionIdentidad;
        }

        // Validar si el NUP ha sido homologado
        if(strlen($dataEmpleado->nup) == "10") {
            // Homologado a DUI
            $flgAFPHomologacion = "No";
        } else {
            // NUP
            $flgAFPHomologacion = "Sí";
        }
    } else {
        $personaId = 0;
        $txtSuccess = "Se ha creado con éxito el nuevo empleado.";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation">
<input type="hidden" id="operation" name="operation" value="empleado">
<input type="hidden" id="personaId" name="personaId" value="<?php echo $personaId; ?>">
<input type="hidden" id="flgMunicipio" name="flgMunicipio" value="0">
<input type="hidden" id="flgAFPHomologacion" name="flgAFPHomologacion" value="<?php echo $flgAFPHomologacion; ?>">
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
            <input type="date" id="fechaNac" class="form-control" name="fechaNac" required />
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
                <option value="Acompañado/a">Acompañado/a</option>
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
        <div class="form-outline mb-4">
            <i class="fas fa-syringe trailing"></i>
            <input type="text" id="tipoSangre" class="form-control" name="tipoSangre" />
            <label class="form-label" for="tipoSangre">Tipo de sangre</label>
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
                    <input type="month" id="fechaExpiracionIdentidad" class="form-control" name="fechaExpiracionIdentidad" required />
                    <label class="form-label" for="fechaExpiracionIdentidad">Fecha de expiración</label>
                </div>         
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-select-control mb-4">
                    <select id="departamentoExpedicion" name="departamentoExpedicion" style="width: 100%;" required>
                        <option></option>
                        <?php 
                            $dataDep = $cloud->rows("
                                SELECT 
                                    paisDepartamentoId, departamentoPais 
                                FROM cat_paises_departamentos 
                                WHERE paisId = ? AND flgDelete = ? 
                            ", [61, 0]);
                            foreach($dataDep as $depto){
                                echo '<option value="'. $depto->paisDepartamentoId .'">' . $depto->departamentoPais . '</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-select-control mb-4">
                    <select id="paisMunicipioIdExpedicion" name="paisMunicipioIdExpedicion" style="width: 100%;" required>
                        <option></option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-outline mb-4 input-daterange">
                    <input type="date" id="fechaExpedicionIdentidad" class="form-control" name="fechaExpedicionIdentidad" required />
                    <label class="form-label" for="fechaExpedicionIdentidad">Fecha de expedición</label>
                </div> 
            </div>
        </div>
        <div class="form-outline mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="text" id="nit" class="form-control masked" name="nit" data-mask="####-######-###-#" minlength="17" />
            <label class="form-label" for="nit">NIT</label>
            <div class="form-helper text-end">
                No completar si aplica homologación
            </div>
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
                    <i class="fas fa-money-check-alt trailing"></i>
                    <input type="text" id="nup" class="form-control masked masked-nup" name="nup" minlength="12" data-mask="############" required />
                    <label id="labelNUP" class="form-label" for="nup">NUP</label>
                    <div class="form-helper text-end">
                        <span id="labelAFPHomologacion" class="badge rounded-pill bg-secondary" style="cursor: pointer;" onclick="homologacionAFP();">
                            <i class="fas fa-sync-alt"></i> Cambiar a Homologación
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-outline mb-4">
            <i class="fas fa-ambulance trailing"></i>
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

<script>
    function homologacionAFP() {
        var mask = Maska.create('.masked-nup');
        mask.destroy();
        $("#nup").removeAttr("minlength");

        if($("#flgAFPHomologacion").val() == "No") {
            // DUI en NUP
            $("#flgAFPHomologacion").val("Sí");
            $("#labelNUP").html("DUI");
            $("#labelAFPHomologacion").html('<i class="fas fa-sync-alt"></i> Cambiar a NUP');
            Maska.create('.masked-nup', {
                mask: '########-#'
            });
            $("#nup").attr("minlength", 10);
            $("#nup").val($("#numIdentidad").val());
            $("#nup").addClass("active");
        } else {
            // NUP
            $("#flgAFPHomologacion").val("No");
            $("#labelNUP").html("NUP");
            $("#labelAFPHomologacion").html('<i class="fas fa-sync-alt"></i> Cambiar a Homologación');
            Maska.create('.masked-nup', {
                mask: '############'
            });
            $("#nup").attr("minlength", 12);
            $("#nup").val('');
        }
    }

    $(document).ready(function() {
        Maska.create('#frmModal .masked');
        $("#pais").select2({
            placeholder: "Nacionalidad",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#departamentoDUI").select2({
            placeholder: "Departamento",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#municipioDUI").select2({
            placeholder: "Municipio",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#departamentoActual").select2({
            placeholder: "Departamento",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#municipioActual").select2({
            placeholder: "Municipio",
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
        $("#departamentoExpedicion").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Departamento expedición'
        });
        $("#paisMunicipioIdExpedicion").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Municipio expedición'
        });

        $("#fechaExpiracionIdentidad").addClass('active');

        $('#vehiculo').on('change', function() { 
            $("#tipos-vehiculos").toggle(); 
        });

        $("#departamentoDUI").on("change", function() {
            asyncSelect(
                "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarMunicipios",
                {
                    depto: $(this).val()
                },
                `municipioDUI`,
                function() {
                    <?php 
                        // Validacion con php para prevenir error que no existe variable, sino se cumple simplemente no existe este script
                        if($arrayFormData[0] == "editar") {
                    ?>
                            $("#municipioDUI").val('<?php echo $dataEmpleado->paisMunicipioIdDUI; ?>').trigger('change');
                    <?php 
                        } else {
                        }
                    ?>
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
                    <?php 
                        // Validacion con php para prevenir error que no existe variable, sino se cumple simplemente no existe este script
                        if($arrayFormData[0] == "editar") {
                    ?>
                            $("#municipioActual").val('<?php echo $dataEmpleado->paisMunicipioIdActual; ?>').trigger('change');
                    <?php 
                        } else {
                        }
                    ?>
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

        $("#departamentoExpedicion").change(function(e) {
            asyncSelect(
                "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarMunicipios",
                {
                    depto: $(this).val()
                },
                `paisMunicipioIdExpedicion`,
                function() {
                    <?php 
                        // Validacion con php para prevenir error que no existe variable, sino se cumple simplemente no existe este script
                        if($arrayFormData[0] == "editar") {
                    ?>
                            $("#paisMunicipioIdExpedicion").val('<?php echo $dataEmpleado->paisMunicipioIdExpedicion; ?>').trigger('change');
                    <?php 
                        } else {
                        }
                    ?>
                }
            );
        });

        $("#frmModal").validate({
            messages: {
                numIdentidad: {
                    minlength: "Debe contener 9 digitos" // Se agrega automático o se quita si es Carné
                },
                numISSS: {
                    minlength: "Debe contener 9 dígitos"
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
                            <?php 
                                // Validacion con php para prevenir error que no existe funcion, sino se cumple simplemente no existe este script
                                if($arrayFormData[0] == "editar") {
                            ?>
                                    changePage('<?php echo $_SESSION["currentRoute"]; ?>', 'perfil-empleado', `personaId=<?php echo $arrayFormData[1]; ?>&nombreCompleto=${$("#apellido1").val()} ${$("#apellido2").val()}, ${$("#nombre1").val()} ${$("#nombre2").val()}`);
                            <?php 
                                } else {
                            ?>
                                    $("#tblEmpleados").DataTable().ajax.reload(null, false);
                            <?php 
                                }
                            ?>
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
                $("#modalTitle").html('Editar Empleado: <?php echo $dataEmpleado->apellido1 . " " . $dataEmpleado->apellido2 . ", " . $dataEmpleado->nombre1 . " " .$dataEmpleado->nombre2; ?>');

                $("#nombre1").val('<?php echo $dataEmpleado->nombre1; ?>');
                $("#nombre2").val('<?php echo $dataEmpleado->nombre2; ?>');
                $("#nombre3").val('<?php echo $dataEmpleado->nombre3; ?>');
                $("#apellido1").val('<?php echo $dataEmpleado->apellido1; ?>');
                $("#apellido2").val('<?php echo $dataEmpleado->apellido2; ?>');
                $("#apellido3").val('<?php echo $dataEmpleado->apellido3; ?>');
                $("#fechaNac").val('<?php echo $dataEmpleado->fechaNacimiento; ?>');
                $("#sexo").val('<?php echo $dataEmpleado->sexo; ?>').trigger('change');
                $("#estCivil").val('<?php echo $dataEmpleado->estadoCivil; ?>').trigger('change');
                $("#tipoSangre").val('<?php echo $dataEmpleado->tipoSangre; ?>');
                $("#pais").val('<?php echo $dataEmpleado->paisId; ?>').trigger('change');
                $("input[name='docIdentidad'][value='<?php echo $dataEmpleado->docIdentidad; ?>']").prop("checked",true).trigger('change');
                $("#numIdentidad").val('<?php echo $dataEmpleado->numIdentidad; ?>');
                $("#fechaExpiracionIdentidad").val('<?php echo $fechaExpiracionIdentidad; ?>');
                $("#fechaExpedicionIdentidad").val('<?php echo $fechaExpedicionIdentidad; ?>');
                $("#departamentoExpedicion").val('<?php echo $dataEmpleado->paisDepartamentoIdExpedicion; ?>').trigger('change');
                $("#nit").val('<?php echo $dataEmpleado->nit; ?>');
                $("#nombreOrganizacionIdAFP").val('<?php echo $dataEmpleado->nombreOrganizacionIdAFP; ?>').trigger('change');
                if('<?php echo $flgAFPHomologacion; ?>' == 'No') {
                    // Correr function para que cambien los caracteres del input
                    homologacionAFP();
                } else {
                    // Ya quedo default no, no es necesario correr la function
                    $("#nup").val('<?php echo $dataEmpleado->nup; ?>');
                }
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
        <?php 
                if($dataEmpleado->estadoPersona == "Inactivo") {
        ?>
                    $("#btnModalAccept").prop("disabled", true);
        <?php 
                } else {
                    // No deshabilitar botón
                }
            } else {
        ?>
                $("#typeOperation").val('insert');
                $("#modalTitle").html('Nuevo Empleado');
        <?php 
            }
        ?>
        
    });
</script>
