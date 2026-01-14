<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    // personaId ^ nombreCompleto ^ tab activo
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    $dataEstadoPersona = $cloud->row("
        SELECT estadoPersona, zonaResidenciaActual FROM th_personas
        WHERE personaId = ?
    ",[$arrayFormData[0]]);

    if($dataEstadoPersona->estadoPersona == "Inactivo") {
        $disabledInactivo = "disabled";
    } else {
        $disabledInactivo = "";
    }

    $arrayTabActivo = array(array("","",""), array("","",""));
    if(isset($arrayFormData[2])) {
        if($arrayFormData[2] == "tab-empresa") {
            $arrayTabActivo[1][0] = "active";
            $arrayTabActivo[1][1] = "true";
            $arrayTabActivo[1][2] = "show active";
        } else {
            $arrayTabActivo[0][0] = "active";
            $arrayTabActivo[0][1] = "true";
            $arrayTabActivo[0][2] = "show active";
        }
    } else {
        $arrayTabActivo[0][0] = "active";
        $arrayTabActivo[0][1] = "true";
        $arrayTabActivo[0][2] = "show active";
    }
?>
<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php echo $arrayTabActivo[0][0]; ?>" id="ntab-modal-1" data-mdb-toggle="pill" href="#ntab-modal-content-1" role="tab" aria-controls="ntab-modal-content-1" aria-selected="<?php echo $arrayTabActivo[0][1]; ?>">
            Familia
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php echo $arrayTabActivo[1][0]; ?>" id="ntab-modal-2" data-mdb-toggle="pill" href="#ntab-modal-content-2" role="tab" aria-controls="ntab-modal-content-2" aria-selected="<?php echo $arrayTabActivo[1][1]; ?>">
            Empresa
        </a>
    </li>
</ul>
<div class="tab-content" id="ntab-content">
    <div class="tab-pane fade <?php echo $arrayTabActivo[0][2]; ?>" id="ntab-modal-content-1" role="tabpanel" aria-labelledby="ntab-modal-1">
        <form id="frmNucleoFamilia">
            <input type="hidden" id="typeOperationFamilia" name="typeOperation" value="insert">
            <input type="hidden" id="operationFamilia" name="operation" value="nucleo-familiar-familia">
            <input type="hidden" id="personaId" name="personaId" value="<?php echo $arrayFormData[0]; ?>">
            <input type="hidden" id="prsFamiliaId" name="prsFamiliaId" value="0">
            <input type="hidden" id="nombreEmpleado" name="nombreEmpleado" value="<?php echo $arrayFormData[1]; ?>">

            <div id="divBtnForm" class="row">
                <div class="col-4 offset-8">
                    <button type="button" class="btn btn-primary btn-block" onclick="showHideForm(1, 'insert', 'Familia');" <?php echo $disabledInactivo; ?>>
                        <i class="fas fa-plus-circle"></i>
                        Nueva relación
                    </button>
                </div>
            </div>
            <div id="divFormModal">
                <div class="row justify-content-center">
                    <div class="col-4">
                        <div class="form-select-control mb-4">
                            <select class="form-select" id="parentesco" name="parentesco" style="width:100%;" required>
                                <option></option>
                                <?php 
                                    $dataRelacion = $cloud->rows("
                                        SELECT
                                            catPrsRelacionId, 
                                            tipoPrsRelacion
                                        FROM cat_personas_relacion
                                        WHERE flgDelete = ?
                                    ", ['0']);
                                    foreach ($dataRelacion as $dataRelacion) {
                                        echo '<option value="'.$dataRelacion->catPrsRelacionId.'">'.$dataRelacion->tipoPrsRelacion.'</option>';
                                    }
                                ?>
                            </select>
                        </div>
                    </div> 
                    <div class="col-4">
                        <div class="form-outline mb-4">
                            <i class="fas fa-user-circle trailing"></i>
                            <input type="text" id="nombreFamiliar" class="form-control masked" name="nombreFamiliar" required />
                            <label class="form-label" for="nombreFamiliar">Nombre del familiar</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-outline mb-4">
                            <i class="fas fa-user-circle trailing"></i>
                            <input type="text" id="apellidoFamiliar" class="form-control masked" name="apellidoFamiliar" required />
                            <label class="form-label" for="apellidoFamiliar">Apellido del familiar</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="form-outline mb-4">
                            <i class="fas fa-calendar trailing"></i>
                            <input type="date" id="fechaNacimiento" class="form-control" name="fechaNacimiento" required />
                            <label class="form-label" for="fechaNacimiento">Fecha de nacimiento</label>
                        </div>           
                    </div>
                    <div class="col-4">
                        <div class="form-select-control">
                            <select class="form-select" id="flgBeneficiario" name="flgBeneficiario" style="width:100%;" required>
                                <option></option>
                                <option value="Sí">Sí</option>
                                <option value="No" selected>No</option>
                            </select>
                        </div>
                        <div class="text-start mb-4">
                            <small>Beneficiario</small>
                        </div>
                    </div>
                    <div id="divPorcentajeBeneficiario" class="col-4">
                        <div class="form-outline mb-4">
                            <i class="fas fa-percentage trailing"></i>
                            <input type="number" id="porcentajeBeneficiario" class="form-control" name="porcentajeBeneficiario" min="0.01" max="100" step="0.01" required />
                            <label class="form-label" for="porcentajeBeneficiario">Porcentaje de beneficiario</label>
                        </div>           
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="form-select-control">
                            <select class="form-select" id="flgDependeEconomicamente" name="flgDependeEconomicamente" style="width:100%;" required>
                                <option></option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="text-start mb-4">
                            <small>Depende económicamente</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-select-control">
                            <select class="form-select" id="flgVivenJuntos" name="flgVivenJuntos" style="width:100%;" required>
                                <option></option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="text-start mb-4">
                            <small>Viven juntos</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-outline">
                            <i class="fas fa-map-marker-alt trailing"></i>
                            <textarea type="text" id="direccionVivenJuntos" class="form-control" name="direccionVivenJuntos" required ></textarea>
                            <label class="form-label" for="direccionVivenJuntos">Dirección</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3 offset-6">
                        <button type="submit" class="btn btn-primary btn-block" <?php echo $disabledInactivo; ?>>
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                    <div class="col-3">
                        <button type="button" class="btn btn-secondary btn-block" onclick="showHideForm(0, 'insert', 'Familia');">
                            <i class="fas fa-times-circle"></i> Cancelar
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tblNucleoFamilia" class="table table-hover" style="width: 100%;">
                    <thead>
                        <tr id="filterboxrow-familia">
                            <th>#</th>
                            <th>Relación familiar</th>
                            <th>Beneficiario</th>
                            <th>Depende económicamente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    <div class="tab-pane fade <?php echo $arrayTabActivo[1][2]; ?>" id="ntab-modal-content-2" role="tabpanel" aria-labelledby="ntab-modal-2">
        <form id="frmNucleoEmpresa">
            <input type="hidden" id="typeOperationEmpresa" name="typeOperation" value="insert">
            <input type="hidden" id="operationEmpresa" name="operation" value="nucleo-familiar-empresa">
            <input type="hidden" id="flgOtroPersona1" name="flgOtroPersona1" value="0">
            <input type="hidden" id="flgOtroPersona2" name="flgOtroPersona2" value="0">

            <div id="divBtnNuevaRel" class="row mb-4">
                <div class="col-lg-12 text-end">
                    <button type="button" id="btnNuevaRel" class="btn btn-primary" onclick="showHideForm(1, 'insert', 'Empresa');" <?php echo $disabledInactivo; ?>>
                        <i class="fas fa-plus-circle"></i>
                        Nueva relación
                    </button>
                </div>
            </div>
            <div id="newRela">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-outline mb-4">
                            <input type="text" id="nombrePersona" name="nombrePersona" class="form-control" readonly value="<?php echo $arrayFormData[1]; ?>">
                            <input type="hidden" name="persona1" readonly value="<?php echo $arrayFormData[0]; ?>">
                            <input type="hidden" id="relacionId1" name="relacionId1" value="0">
                        </div>
                        <div id="divSelectRelacion1" class="form-select-control">
                            <select class="relacion" id="relacion1" name="relacion1" style="width:100%;" required>
                                <option></option>
                                <?php 
                                    $dataRelacion = $cloud->rows("
                                        SELECT
                                            catPrsRelacionId, 
                                            tipoPrsRelacion
                                        FROM cat_personas_relacion
                                        WHERE flgDelete = ?
                                    ", ['0']);
                                    foreach ($dataRelacion as $dataRelacion) {
                                        echo '<option value="'.$dataRelacion->catPrsRelacionId.'">'.$dataRelacion->tipoPrsRelacion.'</option>';
                                    }
                                ?>
                            </select>
                            <div class="form-helper text-end">
                                <span class="badge rounded-pill bg-primary" style="cursor: pointer;" onclick="showHideOtro(1,1);" <?php echo $disabledInactivo; ?>>
                                    <i class="fas fa-plus-circle"></i> Otra
                                </span>
                            </div>
                        </div>
                        <div id="divOtroPersona1">
                            <div class="form-outline form-hidden-update mb-4">
                                <i class="fas fa-users trailing"></i>
                                <input type="text" id="nombreRelacionPersona1" class="form-control" name="nombreRelacionPersona1" required />
                                <label id="labelNombreRelacionPersona1" class="form-label" for="nombreRelacionPersona1">Nombre de relación</label>
                                <div class="form-helper text-end">
                                    <span class="badge rounded-pill bg-secondary" style="cursor: pointer;" onclick="showHideOtro(0,1);">
                                        <i class="fas fa-times-circle"></i> Cancelar
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-select-control mb-4">
                            <select class="persona" id="persona2" name="persona2" style="width:100%;" required>
                                <option></option>
                                <?php 
                                    $dataPersonas = $cloud->rows("
                                        SELECT
                                            personaId, 
                                            CONCAT(
                                                IFNULL(apellido1, '-'),
                                                ' ',
                                                IFNULL(apellido2, '-'),
                                                ', ',
                                                IFNULL(nombre1, '-'),
                                                ' ',
                                                IFNULL(nombre2, '-')
                                            ) AS nombreCompleto
                                        FROM th_personas
                                        WHERE personaId <> ? AND prsTipoId = '1' AND estadoPersona = 'Activo' AND flgDelete = '0'
                                        ORDER BY apellido1, apellido2, nombre1, nombre2
                                    ",[$arrayFormData[0]]);
                                    foreach ($dataPersonas as $dataPersonas) {
                                        echo '<option value="'.$dataPersonas->personaId.'">'.$dataPersonas->nombreCompleto.'</option>';
                                    }
                                ?>
                            </select>
                            <input type="hidden" id="relacionId2" name="relacionId2" value="0">
                        </div>
                        <div id="divSelectRelacion2" class="form-select-control">
                            <select class="relacion" id="relacion2" name="relacion2" style="width:100%;" required>
                                <option></option>
                                <?php 
                                    $dataRelacion = $cloud->rows("
                                        SELECT
                                        catPrsRelacionId, 
                                        tipoPrsRelacion
                                        FROM cat_personas_relacion
                                        WHERE flgDelete = ?
                                    ", ['0']);
                                    foreach ($dataRelacion as $dataRelacion) {
                                        echo '<option value="'.$dataRelacion->catPrsRelacionId.'">'.$dataRelacion->tipoPrsRelacion.'</option>';
                                    }
                                ?>
                            </select>
                            <div class="form-helper text-end">
                                <span class="badge rounded-pill bg-primary" style="cursor: pointer;" onclick="showHideOtro(1,2);" <?php echo $disabledInactivo; ?>>
                                    <i class="fas fa-plus-circle"></i> Otra
                                </span>
                            </div>
                        </div>
                        <div id="divOtroPersona2">
                            <div class="form-outline form-hidden-update mb-4">
                                <i class="fas fa-users trailing"></i>
                                <input type="text" id="nombreRelacionPersona2" class="form-control" name="nombreRelacionPersona2" required />
                                <label id="labelNombreRelacionPersona2" class="form-label" for="nombreRelacionPersona2">Nombre de relación</label>
                                <div class="form-helper text-end">
                                    <span class="badge rounded-pill bg-secondary" style="cursor: pointer;" onclick="showHideOtro(0,2);">
                                        <i class="fas fa-times-circle"></i> Cancelar
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <span class="nombrePers1"><?php echo $arrayFormData[1]; ?></span> es <span class="relacion1">_______________</span> de <span class="nombrePers2">_______________</span><br>
                        <span class="nombrePers2">_______________</span> es <span class="relacion2">_______________</span> de <span class="nombrePers1"><?php echo $arrayFormData[1]; ?></span>
                    </div>
                </div> 
                <hr>
                <div class="row">
                    <div class="col-lg-3 offset-lg-6">
                        <button type="submit" class="btn btn-primary btn-block" <?php echo $disabledInactivo; ?>>
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                    <div class="col-lg-3">
                        <button type="button" class="btn btn-secondary btn-block" onclick="showHideForm(0, 'insert', 'Empresa');">
                            <i class="fas fa-times-circle"></i> Cancelar
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="tblRelacionesEmpleados" class="table table-hover" style="width: 100%;">
                            <thead>
                                <tr id="filterboxrow-empresa">
                                    <th>#</th>
                                    <th>Empleado</th>
                                    <th>Tipo de relación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function showHideOtro(tipo, persona) {
        if(tipo == 1) {
            $(`#divOtroPersona${persona}`).show();
            $(`#divSelectRelacion${persona}`).hide();
            //$("#divSelectEnfermedad").hide();
            $(`#flgOtroPersona${persona}`).val(1);
            document.querySelectorAll('.form-hidden-update').forEach((formOutline) => {
                new mdb.Input(formOutline).update();
            });
        } else {
            //$("#tipoEnfermedad").val('').trigger('change');
            $(`#nombreRelacionPersona${persona}`).val('');
            $(`#divOtroPersona${persona}`).hide();
            $(`#divSelectRelacion${persona}`).show();
            //$("#divSelectEnfermedad").show();
            $(`#flgOtroPersona${persona}`).val(0);
        }
    }

    function showHideForm(flg, tipo, tab) {
        if(tab == "Familia") {
            if(flg == 0) { // hide
                $("#divBtnForm").show();
                $("#divFormModal").hide();
                $('#frmNucleoFamilia').trigger("reset");
                $("#typeOperationFamilia").val("insert");
                $('#parentesco').trigger('change');
                $('#flgBeneficiario').val("No").trigger("change");
                $('#divPorcentajeBeneficiario').hide();
            } else { // show
                $("#divBtnForm").hide();
                $("#divFormModal").show();
                $("#typeOperationFamilia").val(tipo);
            }
        } else {
            // Empresa
            if(flg == 0) { // hide
                $("#divBtnNuevaRel").show();
                $("#newRela").hide();
                $("#typeOperationEmpresa").val("insert");
                $('#frmNucleoEmpresa').trigger("reset");
                $("#persona2").trigger('change');
                $("#relacion1").trigger('change');
                $("#relacion2").trigger('change');
            } else { // show
                $("#typeOperationEmpresa").val("insert");
                $("#divBtnNuevaRel").hide();
                $("#newRela").show();
            }
        }
	}

    function editarNucleoFamilia(tableData) {
        $("#prsFamiliaId").val(tableData);
        asyncDoDataReturn(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getNucleoFamiliarFamilia", 
            $("#frmNucleoFamilia").serialize(),
            function(data) {
                let result = JSON.parse(data);

                $("#typeOperationFamilia").val("update");
                $("#parentesco").val(result.parentesco).trigger('change');
                $("#nombreFamiliar").val(result.nombreFamiliar);
                $("#apellidoFamiliar").val(result.apellidoFamiliar);
                $("#fechaNacimiento").val(result.fechaNacimiento);
                $("#flgBeneficiario").val(result.flgBeneficiario).trigger("change");
                $("#flgDependeEconomicamente").val(result.flgDependeEconomicamente).trigger("change");
                $("#flgVivenJuntos").val(result.flgVivenJuntos).trigger("change");

                if(result.flgBeneficiario == "Sí") {
                    $('#divPorcentajeBeneficiario').show();
                    $("#porcentajeBeneficiario").val(result.porcentajeBeneficiario);
                } else {
                    $('#divPorcentajeBeneficiario').hide();
                    $("#porcentajeBeneficiario").val(null);
                }

                if(result.flgVivenJuntos == "Sí") {
                    $("#direccionVivenJuntos").val(`<?php echo $dataEstadoPersona->zonaResidenciaActual; ?>`);
                } else {
                    $("#direccionVivenJuntos").val(result.direccionVivenJuntos);
                }

                $("#divBtnForm").hide();
                $("#divFormModal").show();

                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            }
        );
    }

    function delNucleoFamilia(id) {
        <?php 
            if($dataEstadoPersona->estadoPersona == "Inactivo") {
        ?>
                mensaje(
                    "Aviso:",
                    'No es posible eliminar la información de un empleado inactivo.',
                    "warning"
                );
        <?php 
            } else {
        ?>                
                mensaje_confirmacion(
                    "Aviso:", 
                    "¿Está seguro que quiere eliminar esta relación familiar?", 
                    `warning`, 
                    function(param) {
                        asyncDoDataReturn(
                            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                            {
                                typeOperation: 'delete',
                                operation: 'nucleo-familiar-familia',
                                prsFamiliaId: id,
                                nombreEmpleado: '<?php echo $arrayFormData[1]; ?>'
                            },
                            function(data) {
                                if(data == "success") {
                                    mensaje_do_aceptar(
                                        `Operación completada:`, 
                                        `Relación familiar eliminada con éxito.`, 
                                        `success`, 
                                        function() {
                                            $('#tblNucleoFamilia').DataTable().ajax.reload(null, false);
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
                    },
                    "Sí, eliminar",
                    `Cancelar`
                );
        <?php 
            }
        ?>
    }

    function editarNucleoEmpresa(idPersona1, idPersona2){
        asyncDoDataReturn(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getNucleoFamiliarEmpresa", 
            '&idPersona1=' + idPersona1 + '&idPersona2=' + idPersona2,
            function(data) {
                var result = JSON.parse(data);

                $("#typeOperationEmpresa").val("update");
                
                if (result.personaId1_0 == idPersona1){
                    $("#relacionId1").val(result.prsRelacionId_1);
                    $("#relacion1").val(result.relacionId_1);
                    $("#relacion1").trigger('change');

                    $("#relacionId2").val(result.prsRelacionId_0);
                    $("#persona2").val(result.personaId2_0);
                    $("#persona2").trigger('change');
                    $("#relacion2").val(result.relacionId_0);
                    $("#relacion2").trigger('change');
                } else{
                    $("#relacionId1").val(result.prsRelacionId_0);
                    $("#relacion1").val(result.relacionId_0);
                    $("#relacion1").trigger('change');

                    $("#relacionId2").val(result.prsRelacionId_1);
                    $("#persona2").val(result.personaId2_1);
                    $("#persona2").trigger('change');
                    $("#relacion2").val(result.relacionId_1);
                    $("#relacion2").trigger('change');

                }
                $("#divBtnNuevaRel").hide();
                $("#newRela").show();
            }
        );
    }

    $(document).ready(function() {
        // Tab: Familia
        $("#divFormModal").hide();
        $("#divPorcentajeBeneficiario").hide();

        $("#parentesco").select2({
            placeholder: "Parentesco",
            dropdownParent: $('#modal-container')
        });

        $("#flgBeneficiario").select2({
            placeholder: "Beneficiario",
            dropdownParent: $('#modal-container')
        });

        $("#flgDependeEconomicamente").select2({
            placeholder: "Depende económicamente",
            dropdownParent: $('#modal-container')
        });

        $("#flgVivenJuntos").select2({
            placeholder: "Viven juntos",
            dropdownParent: $('#modal-container')
        });

        $("#flgBeneficiario").change(function(e) {
            if($(this).val() == "Sí") {
                $("#divPorcentajeBeneficiario").show();
            } else {
                $("#divPorcentajeBeneficiario").hide();
            }
        });

        $("#flgVivenJuntos").change(function(e) {
            if($(this).val() == "Sí") {
                $("#direccionVivenJuntos").val(`<?php echo $dataEstadoPersona->zonaResidenciaActual; ?>`);
                $("#direccionVivenJuntos").addClass("active");
                $("#direccionVivenJuntos").prop("readonly", true);
            } else {
                // Se mantiene el valor que digitaron
                $("#direccionVivenJuntos").val(``);
                $("#direccionVivenJuntos").prop("readonly", false);
            }
        });

        $("#frmNucleoFamilia").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmNucleoFamilia").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Relación familiar del empleado guardada con éxito.',
                                "success"
                            );
                            $('#tblNucleoFamilia').DataTable().ajax.reload(null, false);
                            showHideForm(0, 'insert', 'Familia'); // para que se oculte
                            //$('#modal-container').modal("hide");
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

        $('#tblNucleoFamilia thead tr#filterboxrow-familia th').each(function(index) {
            if(index==1 || index==2) {
                var title = $('#tblNucleoFamilia thead tr#filterboxrow-familia th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-familia" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-familia">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblNucleoFamilia.column($(this).index()).search($(`#input${$(this).index()}-familia`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            } else {
            }
        });

        let tblNucleoFamilia = $('#tblNucleoFamilia').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoNucleoFamiliar",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $arrayFormData[0]; ?>',
                    "tipo": "Familia"
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null,
                null,
                {"width": "20%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1,2,3,4] },
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Empresa
        $("#newRela").hide();
        $("#divOtroPersona1").hide();
        $("#divOtroPersona2").hide();

        $(".persona").select2({
            placeholder: "Empleado",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $(".relacion").select2({
            placeholder: "Relación",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $("#relacion1").change(function() {
            if($("#relacion1 :selected").text() == "") {
                $(".relacion1").html('_______________');
            } else {
                $(".relacion1").html($("#relacion1 :selected").text());
            }
        });

        $("#relacion2").change(function() {
            if($("#relacion2 :selected").text() == "") {
                $(".relacion2").html('_______________');
            } else {
                $(".relacion2").html($("#relacion2 :selected").text());
            }
        });

        $("#persona2").change(function() {
            $(".nombrePers2").html($("#persona2 :selected").text());
        });

        $("#nombreRelacionPersona1").keyup(function() {
            $(".relacion1").html($(this).val());
        });

        $("#nombreRelacionPersona2").keyup(function() {
            $(".relacion2").html($(this).val());
        });

        $("#frmNucleoEmpresa").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmNucleoEmpresa").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:", 
                                'Se ha guardado con éxito la relación de empleados.', 
                                'success', 
                                function() {
                                    $('#tblRelacionesEmpleados').DataTable().ajax.reload(null, false);
                                    $('#frmNucleoEmpresa').trigger("reset");
                                    $("#typeOperationEmpresa").val("insert");
                                    $("#contactoSucursalHidden").val("");
                                    $('#tipoContacto').val('').trigger('change');

                                    if($("#flgOtroPersona1").val() == 0 && $("#flgOtroPersona2").val() == 0) {
                                        $('#flgOtroPersona1').val('0');
                                        $('#flgOtroPersona2').val('0');
                                        showHideForm(0, 'insert', 'Empresa'); // para que se oculte
                                    } else { // Volver a cargar la modal para que se refleje la relación agregada en el select de relaciones
                                        // Cerrarla primero, porque sino la pantalla queda a oscuras
                                        $('#modal-container').modal("hide");
                                        // Volver a abrir la modal
                                        modalRelacionEmpleado('<?php echo $_POST["arrayFormData"] . '^tab-empresa'; ?>');
                                    }  
                                    //$('#modal-container').modal("hide");
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

        $('#tblRelacionesEmpleados thead tr#filterboxrow-empresa th').each(function(index) {
            if(index==1 || index == 2) {
                var title = $('#tblRelacionesEmpleados thead tr#filterboxrow-empresa th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}empresa" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}empresa">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblRelacionesEmpleados.column($(this).index()).search($(`#input${$(this).index()}empresa`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblRelacionesEmpleados = $('#tblRelacionesEmpleados').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoNucleoFamiliar",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": "<?php echo $arrayFormData[0]; ?>",
                    "tipo": "Empresa"
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>