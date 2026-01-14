<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    
    if ($_POST["typeOperation"] == "update"){
        $dataCliente = $cloud->row("SELECT 
        c.clienteId, 
        c.tipoDocumentoMHId, 
        c.nombreCliente, 
        c.actividadEconomicaId,
        c.actividadEconomicaIdSecundaria,
        c.tipoPersonaMHId, 
        c.numDocumento, 
        c.nombreComercialCliente, 
        c.nrcCliente, 
        c.tipoDocumentoRL, 
        c.categoriaCliente, 
        c.nombreCompletoRL, 
        c.numDocumentoRL,
        c.estadoCivilRL,
        c.sexoRL,
        c.fechaNacimientoRL,
        c.profesionRL,
        p.paisId,
        d.paisDepartamentoId,
        c.paisMunicipioIdNacimientoNat,
        c.paisMunicipioIdNacimientoRL,
        rld.paisDepartamentoId AS paisDepartamentoRL,
        rlp.paisId AS paisRL,
        c.flgAPNFD,
        c.flgPEP,
        c.flgPEPFamiliar,
        c.flgPEPAccionista,
        c.estadoCivilNat,
        c.sexoNat,
        c.fechaNacimientoNat,
        c.profesionNat
        FROM fel_clientes c
        LEFT JOIN cat_paises_municipios m ON m.paisMunicipioId = c.paisMunicipioIdNacimientoNat
		LEFT JOIN cat_paises_departamentos d ON d.paisDepartamentoId = m.paisDepartamentoId
		LEFT JOIN cat_paises p ON p.paisId = d.paisId
        LEFT JOIN cat_paises_municipios rlm ON rlm.paisMunicipioId = c.paisMunicipioIdNacimientoRL
        LEFT JOIN cat_paises_departamentos rld ON rld.paisDepartamentoId = rlm.paisDepartamentoId
        LEFT JOIN cat_paises rlp ON rlp.paisId = rld.paisId
        WHERE c.flgDelete = 0 AND c.clienteId =  ?", [$_POST["clienteId"]]);
    }
?>

<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
	<input type="hidden" id="operation" name="operation" value="datos-cliente">
	<input type="hidden" id="idClienteUPDT" name="idCliente" value="<?php echo $_POST['clienteId']; ?>">
	
    <div class="row">
        <div class="col-md-4">
            <div class="form-select-control mb-3">
                <select id="tipoPersona" name="tipoPersona" style="width: 100%;" required>
                    <option></option>
                    <?php 
                    $getTipoDoc = $cloud->rows("SELECT tipoPersonaId, tipoPersona
                    FROM mh_029_tipo_persona WHERE flgDelete = 0
                    ");
                    foreach ($getTipoDoc as $getTipoDoc){
                        echo '<option value="'.$getTipoDoc->tipoPersonaId.'">'.$getTipoDoc->tipoPersona.'</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline">
                <i class="fas flist-ul trailing"></i>
                <input type="text" id="nombreCliente" class="form-control" name="nombreCliente" required>
                <label class="form-label" for="nombreCliente">Nombre completo</label>
            </div>
        </div>
        <div class="col-md-4" id="nombreC" style="display:none;">
            <div class="form-outline" >
                <i class="fas flist-ul trailing"></i>
                <input type="text" id="nombreComercial" class="form-control" name="nombreComercial" required>
                <label class="form-label" for="nombreComercial">Nombre comercial</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline">
                <i class="fas fa-id-card trailing"></i>
                <input type="text" id="nrc" class="form-control" name="nrc">
                <label class="form-label" for="nrc">Número de registro (NRC)</label>
            </div>
        </div>
    </div>
    
<div id="pNatural" style="display:none;">
    <hr>
    <div class="row">
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="estadoCivil" name="estadoCivil" style="width: 100%;" required>
                <option></option>
                <option value="Casado">Casado/a</option>
                <option value="Acompañado/a">Acompañado/a</option>
                <option value="Divorciado/a">Divorciado/a</option>
                <option value="Viudo/a">Viudo/a</option>
                <option value="Soltero/a">Soltero/a</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="sexo" name="sexo" style="width: 100%;" required>
                    <option></option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline mb-4">
                <input type="date" id="fechaNacimientoNat" class="form-control" name="fechaNacimientoNat">
                <label class="form-label" for="profesion">Fecha de nacimiento</label>
            </div>
        </div>
        
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="paisId" name="nacionalidad" style="width: 100%;" required>
                    <option></option>
                    <option value="61">El Salvador</option>
                    <?php 
                        $dataPaises = $cloud->rows("
                            SELECT
                                paisId,
                                pais
                            FROM cat_paises
                            WHERE flgDelete = '0' AND paisId <> '61' ORDER BY pais ASC
                        ");
                        foreach ($dataPaises as $dataPaises) {
                            echo '<option value="'.$dataPaises->paisId.'">'.$dataPaises->pais.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="departamentoId" name="deptoNacimiento" style="width: 100%;" required>
                    <option></option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="municipioId" name="municipioId" style="width: 100%;" required>
                    <option></option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-outline mb-4">
                <i class="fas fa-id-card trailing"></i>
                <input type="text" id="profesion" class="form-control" name="profesion">
                <label class="form-label" for="profesion">Profesión</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-select-control">
                <select id="tipoDoc" name="tipoDoc" style="width: 100%;" required>
                    <option></option>
                    <?php 
                    $getTipoDoc = $cloud->rows("SELECT tipoDocumentoClienteId, codigoMH, tipoDocumentoCliente
                    FROM mh_022_tipo_documento WHERE flgDelete = 0
                    ");
                    foreach ($getTipoDoc as $getTipoDoc){
                        echo '<option value="'.$getTipoDoc->tipoDocumentoClienteId.'">'.$getTipoDoc->tipoDocumentoCliente.'</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline">
                <i class="fas fa-id-card trailing"></i>
                <input type="text" id="numeroDocumento" class="form-control masked masked-doc" name="numeroDocumento" required>
                <label class="form-label" for="numeroDocumento">Número de documento</label>
            </div>
            <div id="txtNumDoc" class="form-text" style="display:none;">
                Digite el número con guiones.
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <!-- Default checkbox -->
            <div class="form-check">
                <input class="form-check-input" id="pep" type="checkbox" value="Si" id="pep" name="pepPN" />
                <label class="form-check-label" for="pep">¿Ha desempeñado algún cargo como Persona Expuesta Politicamente?</label>
            </div>

            <!-- Checked checkbox -->
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Si" id="pepFam" name="pepPNFam"/>
                <label class="form-check-label" for="pepFam">¿Tiene algún familiar en primer o segundo grado de consanguinidad que desempeñe o ha desempeñado un cargo como Persona Expuesta Politicamente?</label>
            </div>
            <div class="alert alert-secondary mt-3 d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-circle fa-lg me-3"></i>
                <div>
                    Por persona expuesta politicamente habrá de entenderse todo aquel sujeto que esté comprendido en los artículos 236 y 239 de la Constitución de 
                    la República o en los literales a), b) y c) del artículo 2 de  la Convención de las Naciones Unidos Contra la Corrupción.
                </div>
            </div>
        </div>
    </div>
</div>

<div id="pJuridica" style="display:none;">
    <hr>
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="form-outline mb-4">
                <i class="fas fa-id-card trailing"></i>
                <input type="text" id="nitPJ" class="form-control masked-nit masked" name="nitPJ">
                <label class="form-label" for="numeroderegistro">NIT</label>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-select-control mb-4">
                <select id="categoria" name="categoria" style="width: 100%;" required>
                    <option></option>
                    <option value="Pequeño contribuyente">Pequeño contribuyente</option>
                    <option value="Mediano contribuyente">Mediano contribuyente</option>
                    <option value="Gran contribuyente">Gran contribuyente</option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-select-control mb-4">
                <select id="giro" name="giro" style="width: 100%;" required>
                    <option></option>
                    <?php 
                    $getGiro = $cloud->rows("SELECT actividadEconomicaId, codigoMh, actividadEconomica
                    FROM mh_019_actividad_economica WHERE flgDelete = 0
                    ");
                    foreach ($getGiro as $getGiro){
                        echo '<option value="'.$getGiro->actividadEconomicaId.'">('.$getGiro->codigoMh.') '.$getGiro->actividadEconomica.'</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-select-control mb-4">
                <select id="giroSec" name="giroSec" style="width: 100%;" required>
                    <option></option>
                    <?php 
                        $getGiro = $cloud->rows("SELECT actividadEconomicaId, codigoMh, actividadEconomica
                        FROM mh_019_actividad_economica WHERE flgDelete = 0
                        ");
                        foreach ($getGiro as $getGiro){
                            echo '<option value="'.$getGiro->actividadEconomicaId.'">('.$getGiro->codigoMh.') '.$getGiro->actividadEconomica.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Si" id="apnfd" name="apnfd" />
                <label class="form-check-label" for="apnfd">
                    ¿Las actividades economicas que realiza  han sido catalogada como una APNFD? (Actividades y Profesiones No Financieras Designadas) <br>
                    (Casinos, casas de juego, comercializadora de metales y piedras preciosas, empresas e intermediarios de bienes y raices, proveedores de servicios societarios y Fideicomisos)
                </label>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <!-- Default checkbox -->
            <div class="form-check">
                <input class="form-check-input" id="pepPJ" type="checkbox" value="Si" id="pepPJ" name="pepPJ" />
                <label class="form-check-label" for="pepPJ">¿El Representante Legal, desempeña o ha desempeñado algún cargo como Persona Expuesta Politicamente?</label>
            </div>

            <!-- Checked checkbox -->
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Si" id="pepPJAc" name="pepPJAc"/>
                <label class="form-check-label" for="pepPJAc">¿Tiene algún accionista que desempeñe cargo como Persona Expuesta Politicamente; que posea el 25% o más del capital accionario o participación en el patrimonio?</label>
            </div>
            <div class="alert alert-secondary mt-3 d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-circle fa-lg me-3"></i>
                <div>
                    Por persona expuesta politicamente habrá de entenderse todo aquel sujeto que esté comprendido en los artículos 236 y 239 de la Constitución de 
                    la República o en los literales a), b) y c) del artículo 2 de  la Convención de las Naciones Unidos Contra la Corrupción.
                </div>
            </div>
        </div>
    </div>
    <h4>Información de representante legal</h4>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <div class="form-outline mb-3">
                <i class="fas flist-ul trailing"></i>
                <input type="text" id="nombreRL" class="form-control" name="nombreRL" required>
                <label class="form-label" for="nombreRL">Nombre de representante legal</label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="estadoCivilRL" name="estadoCivilRL" style="width: 100%;" required>
                    <option></option>
                    <option value="Casado">Casado/a</option>
                    <option value="Acompañado/a">Acompañado/a</option>
                    <option value="Divorciado/a">Divorciado/a</option>
                    <option value="Viudo/a">Viudo/a</option>
                    <option value="Soltero/a">Soltero/a</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select class="form-select" id="sexoRL" name="sexoRL" style="width:100%;" required>
                    <option></option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline mb-4">
                <input type="date" id="fechaNacimientoRL" class="form-control" name="fechaNacimientoRL">
                <label class="form-label" for="fechaNacimientoRL">Fecha de nacimiento de representante legal</label>
            </div>
        </div>
        
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="paisIdRL" name="nacionalidadRL" style="width: 100%;" required>
                    <option></option>
                    <option value="61">El Salvador</option>
                    <?php 
                        $dataPaises = $cloud->rows("
                            SELECT
                                paisId,
                                pais
                            FROM cat_paises
                            WHERE flgDelete = '0' AND paisId <> '61' ORDER BY pais ASC
                        ");
                        foreach ($dataPaises as $dataPaises) {
                            echo '<option value="'.$dataPaises->paisId.'">'.$dataPaises->pais.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="departamentoIdRL" name="deptoNacimientoRL" style="width: 100%;" required>
                    <option></option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="municipioIdRL" name="muniNacimientoRL" style="width: 100%;" required>
                    <option></option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-select-control">
                <select id="tipoDocRL" name="tipoDocRL" style="width: 100%;" required>
                    <option></option>
                    <?php 
                    $getTipoDoc = $cloud->rows("SELECT tipoDocumentoClienteId, codigoMH, tipoDocumentoCliente
                    FROM mh_022_tipo_documento WHERE flgDelete = 0
                    ");
                    foreach ($getTipoDoc as $getTipoDoc){
                        echo '<option value="'.$getTipoDoc->tipoDocumentoClienteId.'">'.$getTipoDoc->tipoDocumentoCliente.'</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline">
                <i class="fas fa-id-card trailing"></i>
                <input type="text" id="numeroDocumentoRL" class="form-control masked masked-docRL" name="numeroDocumentoRL" required>
                <label class="form-label" for="numeroDocumentoRL">Número de documento</label>
            </div>
            <div id="txtNumDoc" class="form-text" style="display:none;">
                Digite el número con guiones.
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline">
                <i class="fas flist-ul trailing"></i>
                <input type="text" id="profesionRL" class="form-control" name="profesionRL" required>
                <label class="form-label" for="profesionRL">Profesión de representante legal</label>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        Maska.create('#infGeneral .masked');
        Maska.create('#contactos .masked');
        Maska.create('.masked-nit',{
                    mask: '####-######-###-#'
                });
        $("#categoria").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Categoría de contribuyente', 
            allowClear: true
        });
        $("#tipoDoc").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de documento', 
            allowClear: true
        });
        $("#tipoDocRL").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de documento de Representante Legal', 
            allowClear: true
        });
        $("#giro").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Giro/Actividad económica', 
            allowClear: true
        });
        $("#giroSec").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Giro/Actividad económica secundaria', 
            allowClear: true
        });
        $("#tipoPersona").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de cliente', 
            allowClear: true
        });
        $("#paisId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Nacionalidad', 
            allowClear: true
        });
        $("#departamentoId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Departamento de nacimiento', 
            allowClear: true
        });
        $("#municipioId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Municipio de nacimiento', 
            allowClear: true
        });
        $("#paisIdRL").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Nacionalidad del representante legal', 
            allowClear: true
        });
        $("#departamentoIdRL").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Departamento de nacimiento del representante legal', 
            allowClear: true
        });
        $("#municipioIdRL").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Municipio de nacimiento del representante legal', 
            allowClear: true
        });
        
        $("#estadoCivil").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Estado civil', 
            allowClear: true
        });
        $("#estadoCivilRL").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Estado civil de representante legal', 
            allowClear: true
        });
        $("#sexo").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Sexo', 
            allowClear: true
        });
        $("#sexoRL").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Sexo de representante legal', 
            allowClear: true
        });

        $("#tipoDoc").change(function(e) {
            if($('#tipoDoc').val() == "1") {
                $("#numeroDocumento").val('');
                Maska.create('.masked-doc',{
                    mask: '####-######-###-#'
                });
                $("#numeroDocumento").attr("minlength", 17);
				$("#txtNumDoc").hide();
            } else if($('#tipoDoc').val() == "2") {
                $("#numeroDocumento").val('');
                Maska.create('.masked-doc', {
                    mask: '########-#'
                });
                $("#numeroDocumento").attr("minlength", 10);
				$("#txtNumDoc").hide();
            }else{
                $("#numeroDocumento").val('');
                var mask = Maska.create('.masked-doc');
                mask.destroy();
                $("#numeroDocumento").removeAttr("minlength");
				$("#txtNumDoc").show();
            }
        });
		$("#tipoDocRL").change(function(e) {
            if($('#tipoDocRL').val() == "1") {
                $("#duiRL").val('');
                Maska.create('.masked-docRL',{
                    mask: '####-######-###-#'
                });
                $("#duiRL").attr("minlength", 17);
				$("#txtNumDocRL").hide();

				$("#labelduiRL").html("Número de NIT de Representante Legal");
            } else if($('#tipoDocRL').val() == "2") {
                $("#duiRL").val('');
                Maska.create('.masked-docRL', {
                    mask: '########-#'
                });
                $("#duiRL").attr("minlength", 10);
				$("#txtNumDocRL").hide();

				$("#labelduiRL").html("Número de DUI de Representante Legal");
            } else if($('#tipoDocRL').val() == "5"){
				$("#labelduiRL").html("Número de Carnet de residente de Representante Legal");
				$("#duiRL").val('');
                var mask = Maska.create('.masked-docRL');
                mask.destroy();
                $("#duiRL").removeAttr("minlength");
				$("#txtNumDocRL").show();
            } else if($('#tipoDocRL').val() == "4"){
				$("#labelduiRL").html("Número de pasaporte de Representante Legal");
				$("#duiRL").val('');
                var mask = Maska.create('.masked-docRL');
                mask.destroy();
                $("#duiRL").removeAttr("minlength");
				$("#txtNumDocRL").show();
			} else{
                $("#duiRL").val('');
                var mask = Maska.create('.masked-docRL');
                mask.destroy();
                $("#duiRL").removeAttr("minlength");
				$("#txtNumDocRL").show();
				$("#labelduiRL").html("Número de documento de Representante Legal");
            }
        });
        
        $("#tipoPersona").change(function(e){
			let persona = $("#tipoPersona").val();
            if (persona == "1"){
                $("#pNatural").show();
                $("#pJuridica").hide();
                $("#nombreC").hide();
            } else {
                $("#pNatural").hide();
                $("#pJuridica").show();
                $("#nombreC").show();
            }
		});


        $("#paisId").on("change", function() {
                var pais = $("#paisId").val();
                $.ajax({
                    url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarEstados.php/",
                    type: "POST",
                    dataType: "json",
                    data: {pais: pais}
                }).done(function(data){
                    //$("#municipio").html(data);
                    var cant = data.length;
                    $("#departamentoId").empty();
                    $("#departamentoId").append("<option value='0' selected disabled>Estado</option>");

                    $("#municipioId").empty();
                    $("#municipioId").append("<option value='0' selected disabled>Ciudad</option>");
                    for (var i = 0; i < cant; i++){
                        var id = data[i]['id'];
                        var depato = data[i]['departamento'];

                        $("#departamentoId").append("<option value='"+id+"'>"+depato+"</option>");
                    }
                    <?php if ($_POST['typeOperation'] == 'update'){ ?>
                        $("#departamentoId").val(<?php echo $dataCliente->paisDepartamentoId;?>).trigger('change');
                    <?php } ?>
                });
            });

            $("#departamentoId").on("change", function() {
                var depto = $("#departamentoId").val();
                $.ajax({
                    url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarCiudades.php/",
                    type: "POST",
                    dataType: "json",
                    data: {depto: depto}
                }).done(function(data){
                    //$("#municipio").html(data);
                    var cant = data.length;
                    $("#municipioId").empty();
                    $("#municipioId").append("<option value='0' selected disabled>Ciudad</option>");
                    for (var i = 0; i < cant; i++){
                        var id = data[i]['id'];
                        var muni = data[i]['municipio'];

                        $("#municipioId").append("<option value='"+id+"'>"+muni+"</option>");
                    }
                    <?php if ($_POST['typeOperation'] == 'update'){ ?>
                        $("#municipioId").val(<?php echo $dataCliente->paisMunicipioIdNacimientoNat;?>).trigger('change');
                    <?php } ?>
                });
            });
        $("#paisIdRL").on("change", function() {
                var pais = $("#paisIdRL").val();
                $.ajax({
                    url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarEstados.php/",
                    type: "POST",
                    dataType: "json",
                    data: {pais: pais}
                }).done(function(data){
                    //$("#municipio").html(data);
                    var cant = data.length;
                    $("#departamentoIdRL").empty();
                    $("#departamentoIdRL").append("<option value='0' selected disabled>Estado</option>");

                    $("#municipioIdRL").empty();
                    $("#municipioIdRL").append("<option value='0' selected disabled>Ciudad</option>");
                    for (var i = 0; i < cant; i++){
                        var id = data[i]['id'];
                        var depato = data[i]['departamento'];

                        $("#departamentoIdRL").append("<option value='"+id+"'>"+depato+"</option>");
                    }
                    <?php if ($_POST['typeOperation'] == 'update'){
                    if ($dataCliente->tipoPersonaMHId == 2){ ?>
                        $("#departamentoIdRL").val(<?php echo $dataCliente->paisDepartamentoRL;?>).trigger('change');
                    <?php }} ?>
                });
            });

            $("#departamentoIdRL").on("change", function() {
                var depto = $("#departamentoIdRL").val();
                $.ajax({
                    url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarCiudades.php/",
                    type: "POST",
                    dataType: "json",
                    data: {depto: depto}
                }).done(function(data){
                    //$("#municipio").html(data);
                    var cant = data.length;
                    $("#municipioIdRL").empty();
                    $("#municipioIdRL").append("<option value='0' selected disabled>Ciudad</option>");
                    for (var i = 0; i < cant; i++){
                        var id = data[i]['id'];
                        var muni = data[i]['municipio'];

                        $("#municipioIdRL").append("<option value='"+id+"'>"+muni+"</option>");
                    }
                    <?php if ($_POST['typeOperation'] == 'update'){
                    if ($dataCliente->tipoPersonaMHId == 2){ ?>
                        $("#municipioIdRL").val(<?php echo $dataCliente->paisMunicipioIdNacimientoRL;?>).trigger('change');
                    <?php }} ?>
                });
            });
        <?php if ($_POST["typeOperation"] == "update"){ ?>
            $("#tipoPersona").val('<?php echo $dataCliente->tipoPersonaMHId;?>');
			$("#tipoPersona").trigger('change');
			$("#tipoDoc").val('<?php echo $dataCliente->tipoDocumentoMHId;?>');
			$("#tipoDoc").trigger('change');
            <?php if ($dataCliente->tipoPersonaMHId == 1){ 
            echo ($dataCliente->flgPEP == 'Si') ? '$("#pep").prop( "checked", true );' : '';
            ?>
			$("#numeroDocumento").val('<?php echo $dataCliente->numDocumento;?>');
			$("#numeroDocumento").addClass('active');
            <?php } else { 
                echo ($dataCliente->flgPEP == 'Si') ? '$("#pepPJ").prop( "checked", true );' : '';
            ?>
            $("#nitPJ").val('<?php echo $dataCliente->numDocumento;?>');
			$("#nitPJ").addClass('active');
            <?php } ?>
			$("#nombreCliente").val('<?php echo $dataCliente->nombreCliente;?>');
			$("#nombreCliente").addClass('active');
			$("#nombreComercial").val('<?php echo $dataCliente->nombreComercialCliente;?>');
			$("#nombreComercial").addClass('active');
			$("#nrc").val('<?php echo $dataCliente->nrcCliente;?>');
			$("#nrc").addClass('active');
			$("#giro").val('<?php echo $dataCliente->actividadEconomicaId;?>').trigger('change');
			$("#giroSec").val('<?php echo $dataCliente->actividadEconomicaIdSecundaria;?>').trigger('change');
			$("#categoria").val('<?php echo $dataCliente->categoriaCliente;?>').trigger('change');
			
            $("#nombreRL").val('<?php echo $dataCliente->nombreCompletoRL;?>');
			$("#nombreRL").addClass('active');
			$("#tipoDocRL").val('<?php echo $dataCliente->tipoDocumentoRL;?>').trigger('change');
			$("#numeroDocumentoRL").val('<?php echo $dataCliente->numDocumentoRL;?>');
			$("#numeroDocumentoRL").addClass('active');
			$("#estadoCivilRL").val('<?php echo $dataCliente->estadoCivilRL;?>').trigger('change');
			$("#sexoRL").val('<?php echo $dataCliente->sexoRL;?>').trigger('change');
			$("#fechaNacimientoRL").val('<?php echo $dataCliente->fechaNacimientoRL;?>');
			$("#profesionRL").val('<?php echo $dataCliente->profesionRL;?>');
            $("#profesionRL").addClass('active');
			
            $("#estadoCivil").val('<?php echo $dataCliente->estadoCivilNat;?>').trigger('change');
			$("#sexo").val('<?php echo $dataCliente->sexoNat;?>').trigger('change');
			$("#fechaNacimientoNat").val('<?php echo $dataCliente->fechaNacimientoNat;?>');
			$("#profesion").val('<?php echo $dataCliente->profesionNat;?>');
			$("#paisId").val('<?php echo $dataCliente->paisId;?>').trigger('change');
			$("#paisIdRL").val('<?php echo $dataCliente->paisRL;?>').trigger('change');

        <?php 
            echo ($dataCliente->flgAPNFD == 'Si') ? '$("#apnfd").prop( "checked", true );' : '';
            echo ($dataCliente->flgPEP == 'Si') ? '$("#pep").prop( "checked", true );' : '';
            echo ($dataCliente->flgPEPFamiliar == 'Si') ? '$("#pepFam").prop( "checked", true );' : '';
            echo ($dataCliente->flgPEPAccionista == 'Si') ? '$("#pepPJAc").prop( "checked", true );' : '';
        } 
        ?>

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation.php", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        let tipoPersona = $("#tipoPersona").val();
                        if(data.resultado == "success") {
                            mensaje(
                                "Operación completada:",
                                'Cliente agregado con éxito.',
                                "success"
                            );
                            $('#tblCliente').DataTable().ajax.reload(null, false);
                            $('#tblClienteJ').DataTable().ajax.reload(null, false);
                            $('#modal-container').modal("hide");

                            if (tipoPersona == 1) {
                                $('#ex1 a[href="#ex1-tabs-1"]').tab('show');
                            } else {
                                $('#ex1 a[href="#ex1-tabs-2"]').tab('show');
                            }
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