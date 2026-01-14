<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $txtSuccess = "Sujeto agregado con éxito.";
    $sujetoExcluidoId = 0;

    if ($_POST['typeOperation'] == "update"){
        $sujetoExcluidoId = $_POST['sujetoExcluidoId'];

        $datProveedor = $cloud->row("
            SELECT
                se.sujetoExcluidoId AS sujetoExcluidoId, 
                se.tipoDocumentoMHId as tipoDocumentoMHId,
                se.numDocumento AS numDocumento,
                se.nombreSujeto as nombreSujeto,
                se.actividadEconomicaId as actividadEconomicaId,
                se.telefonoSujeto as telefonoSujeto,
                se.correoSujeto as correoSujeto,
                se.direccionSujeto as direccionSujeto,
                mu.paisDepartamentoId AS paisDepartamentoId,
                de.paisId AS paisId,
                se.paisMunicipioId AS paisMunicipioId
            FROM fel_sujeto_excluido se
            LEFT JOIN cat_paises_municipios mu ON mu.paisMunicipioId = se.paisMunicipioId
            LEFT JOIN cat_paises_departamentos de ON de.paisDepartamentoId = mu.paisDepartamentoId
            LEFT JOIN cat_paises pa ON pa.paisId = de.paisId
            LEFT JOIN mh_019_actividad_economica ae ON ae.actividadEconomicaId = se.actividadEconomicaId
            WHERE se.flgDelete = ? AND se.sujetoExcluidoId = ?
        ",[0, $_POST['sujetoExcluidoId']]);

        $txtSuccess = "Sujeto excluido actualizado con éxito";

        $tipoDoc = $datProveedor->tipoDocumentoMHId;

        if (is_null($datProveedor->tipoDocumentoMHId)){
            switch ($datProveedor->tipoDocumento){
                case 'NIT':
                    $tipoDoc = 1;
                break;
                case 'DUI':
                    $tipoDoc = 2;
                break;
                case 'Otro':
                    $tipoDocumento = 3;
                break;
            }
        } else {
            $tipoDoc = $datProveedor->tipoDocumentoMHId;
        }
    }

    $tipoDocumento = $cloud->rows("SELECT tipoDocumentoClienteId, tipoDocumentoCliente FROM mh_022_tipo_documento WHERE flgDelete = 0");

    // Esta modal se utiliza desde Quedan para crear nuevo proveedor
    $interfaz = isset($_POST['interfaz']) ? $_POST['interfaz'] : "proveedor";
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="sujetos-excluidos">
<input type="hidden" id="sujetoExcluidoId" name="sujetoExcluidoId" value="<?php echo $sujetoExcluidoId;?>">
<!--<input type="hidden" id="proveedorUbicacionId" name="proveedorUbicacionId" value=""> -->

<div class="row">
    <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select id="tipoDocumento" name="tipoDocumento" style="width: 100%;" required>
                <option></option>
                <?php 
                    foreach ($tipoDocumento as $documento) { 
                        echo '<option value="'.$documento->tipoDocumentoClienteId.'">'.$documento->tipoDocumentoCliente.'</option>';
                    }
                ?>
            </select>
            <input id="nombreDocumento" type="hidden" name="nombreDocumento" value="">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="text" id="numDocumento" class="form-control masked masked-numDocumento" name="numDocumento"  required />
            <label class="form-label" for="numDocumento">Número de documento</label>
            <div id="leyendaNumDocumento" class="form-helper"></div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-user trailing"></i>
            <input type="text" id="nombreProveedor" class="form-control" name="nombreProveedor" required />
            <label class="form-label" for="nombreProveedor">Nombre del proveedor</label>
        </div>
    </div>
   <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select id="actividadEconomicaId" name="actividadEconomicaId" style="width: 100%;">
                <option></option>
                    <?php
                        $dataActividadEconomica = $cloud->rows("
                            SELECT
                                actividadEconomicaId,
                                actividadEconomica,
                                codigoMh
                            FROM mh_019_actividad_economica
                            WHERE flgDelete = ?
                        ", [0]);
                        foreach ($dataActividadEconomica as $dataActividadEconomica) {
                            echo "<option value='$dataActividadEconomica->actividadEconomicaId'>($dataActividadEconomica->codigoMh) $dataActividadEconomica->actividadEconomica</option>";
                        }
                    ?>
            </select>
        </div>
    </div>
</div>

<hr>
<div class="row">
    <div class="col-md-12">
        <div class="form-outline mb-4">
            <i class="fas fa-route trailing"></i>
            <textarea id="direccionProveedorUbicacion" class="form-control" name="direccionProveedorUbicacion" required ></textarea>
            <label class="form-label" for="direccionProveedorUbicacion">Dirección del proveedor</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select class="form-select" id="paisId" name="paisId" style="width:100%;" required>
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
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select class="form-select" id="departamentoId" name="departamentoId" style="width:100%;" required>
                <option></option>
            </select>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select class="form-select" id="paisMunicipioId" name="paisMunicipioId" style="width:100%;" required>
                <option></option>
            </select>
        </div>
    </div>
</div>
<div class="row">
      <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="email" id="correo" class="form-control" name="correo" />
            <label class="form-label" for="correo">Correo</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="text" id="telefono" class="form-control masked masked-telefono" name="telefono" />
            <label class="form-label" for="telefono">Teléfono</label>
        </div>
    </div>
</div>


<script>
    
    $(document).ready(function() {
        Maska.create('#frmModal .masked');
        $("#divExtranjero").hide();
        $("#divLocal1").hide();
        $("#divLocal2").hide();

        $("#tipoDocumento").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de documento'
        });

        $("#actividadEconomicaId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Giro/Actividad económica'
        });
        $("#paisId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'País'
        });
        $("#departamentoId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Estado'
        });
        $("#paisMunicipioId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Ciudad'
        });

        $("#paisId").on("change", function() {
            var pais = $("#paisId").val();
            $.ajax({
                url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarEstados",
                type: "POST",
                dataType: "json",
                data: {pais: pais}
            }).done(function(data){
                //$("#municipio").html(data);
                var cant = data.length;
                $("#departamentoId").empty();
                $("#departamentoId").append("<option value='0' selected disabled>Estado</option>");

                $("#paisMunicipioId").empty();
                $("#paisMunicipioId").append("<option value='0' selected disabled>Ciudad</option>");
                for (var i = 0; i < cant; i++){
                    var id = data[i]['id'];
                    var depato = data[i]['departamento'];

                    $("#departamentoId").append("<option value='"+id+"'>"+depato+"</option>");
                }
                <?php 
                    // Validacion con php para prevenir error que no existe variable, sino se cumple simplemente no existe este script
                    if($_POST['typeOperation'] == "update") {
                ?>
                        $("#departamentoId").val('<?php echo $datProveedor->paisDepartamentoId; ?>').trigger('change');
                <?php 
                    } else { 

                    }
                ?>
                
            });
        });

        $("#departamentoId").on("change", function() {
            var depto = $("#departamentoId").val();
            $.ajax({
                url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarCiudades",
                type: "POST",
                dataType: "json",
                data: {depto: depto}
            }).done(function(data){
                //$("#municipio").html(data);
                var cant = data.length;
                $("#paisMunicipioId").empty();
                $("#paisMunicipioId").append("<option value='0' selected disabled>Ciudad</option>");
                for (var i = 0; i < cant; i++){
                    var id = data[i]['id'];
                    var muni = data[i]['municipio'];

                    $("#paisMunicipioId").append("<option value='"+id+"'>"+muni+"</option>");
                }
                <?php 
                    // Validacion con php para prevenir error que no existe variable, sino se cumple simplemente no existe este script
                    if($_POST['typeOperation'] == "update") {
                ?>
                        $("#paisMunicipioId").val('<?php echo $datProveedor->paisMunicipioId; ?>').trigger('change');
                <?php 
                    } else {
                    }
                ?>
                
            });
        });

        $("#tipoDocumento").change(function(e) {
            if($('#tipoDocumento').val() == "1") {
                $("#numDocumento").val('');
                $("#numDocumento").removeAttr("readonly");
                $("#leyendaNumDocumento").html("");
                Maska.create('.masked-numDocumento',{
                    mask: '####-######-###-#'
                });
                $("#numDocumento").attr("minlength", 17);
            } else if($('#tipoDocumento').val() == "2") {
                $("#numDocumento").val('');
                $("#numDocumento").removeAttr("readonly");
                $("#leyendaNumDocumento").html("");
                Maska.create('.masked-numDocumento', {
                    mask: '########-#'
                });
                $("#numDocumento").attr("minlength", 10);
            }else{
                $("#numDocumento").val('');
                $("#numDocumento").removeAttr("readonly");
                $("#leyendaNumDocumento").html("Digite con guiones");
                var mask = Maska.create('.masked-numDocumento');
                mask.destroy();
                $("#numDocumento").removeAttr("minlength");
            }
            var tipoDoc = $('#tipoDocumento option:selected').text();
            $("#nombreDocumento").val(tipoDoc);
        });

        $("#tipoDocumentoRL").change(function(e) {
            if($('#tipoDocumentoRL').val() == "NIT") {
                $("#numDocumentoRL").val('');
                $("#numDocumentoRL").removeAttr("readonly");
                $("#leyendaNumDocumentoRL").html("");
                Maska.create('.masked-numDocumentoRL',{
                    mask: '####-######-###-#'
                });
                $("#numDocumentoRL").attr("minlength", 17);
            } else if($('#tipoDocumentoRL').val() == "DUI") {
                $("#numDocumentoRL").val('');
                $("#numDocumentoRL").removeAttr("readonly");
                $("#leyendaNumDocumentoRL").html("");
                Maska.create('.masked-numDocumentoRL', {
                    mask: '########-#'
                });
                $("#numDocumentoRL").attr("minlength", 10);
            } else {
                $("#numDocumentoRL").val('');
                $("#numDocumentoRL").removeAttr("readonly");
                $("#leyendaNumDocumentoRL").html("Digite con guiones");
                var mask = Maska.create('.masked-numDocumentoRL');
                mask.destroy();
                $("#numDocumentoRL").removeAttr("minlength");
            }
        });

            // Aplicar la máscara al campo de teléfono
            Maska.create('.masked-telefono', {
            mask: '####-####'
        });

        $("#frmModal").validate({
            messages: {
                numDocumento: {
                    minlength: "Formato de documento incorrecto" // Se agrega automático o se quita si es Carné
                },
            },
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                '<?php echo $txtSuccess;?>',
                                "success"
                            );
                            $('#modal-container').modal("hide");
                            if(`<?php echo $interfaz; ?>` == "proveedor") {
                                $("#tblProveedoresExcluidos").DataTable().ajax.reload(null, false);
                            } else if(`<?php echo $interfaz; ?>` == "otra") {
                                // Para volver a cargar el select
                                cargarSelectProveedores();
                            } else {
                                // Otra interfaz
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

        <?php 
            if($_POST['typeOperation'] == "update") {
        ?>
                $("#typeOperation").val("update");
                $("#modalTitle").html('Editar sujeto excluido: <?php echo $datProveedor->nombreSujeto;?>');
                $("#tipoDocumento").val('<?php echo $tipoDoc; ?>').trigger('change');
                $("#numDocumento").val('<?php echo $datProveedor->numDocumento; ?>');
                $("#numDocumento").removeAttr("readonly");
                $("#nombreProveedor").val(`<?php echo $datProveedor->nombreSujeto; ?>`);
                $("#direccionProveedorUbicacion").val(`<?php echo $datProveedor->direccionSujeto; ?>`);
                $("#paisId").val('<?php echo $datProveedor->paisId; ?>').trigger('change');
                $("#telefono").val(`<?php echo $datProveedor->telefonoSujeto; ?>`);
                $("#correo").val(`<?php echo $datProveedor->correoSujeto; ?>`);
               
        <?php
            } else{
        ?> 
                $("#modalTitle").html('Nuevo sujeto excluido');
        <?php
            }
        ?>
    });
</script>