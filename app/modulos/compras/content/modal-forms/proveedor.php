<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
     arrayFormData 
        Nuevo  = insert
        editar = update ^ proveedorId
     */

    $nivel      = 1;
    $txtSuccess = "Proveedor agregado con éxito.";

    $proveedorId = 0;

    if ($_POST['typeOperation'] == "update"){
        $proveedorId = $_POST['proveedorId'];

        $datProveedor = $cloud->row("
            SELECT
                proveedorId, 
                tipoProveedor, 
                nrcProveedor,
                tipoDocumento, 
                numDocumento, 
                nombreProveedor,
                nombreComercial, 
                nombreCompletoRL, 
                tipoDocumentoRL, 
                numDocumentoRL, 
                estadoProveedor,
                actividadEconomicaId,
                descripcionExtranjero,
                codProveedorMagic,
                cuentaContable
            FROM comp_proveedores
            WHERE flgDelete = ? AND proveedorId = ?
        ",[0, $_POST['proveedorId']]);

        $txtSuccess = "Proveedor actualizado con éxito";
    } else {
        // Es insert
    }
    if(isset($_POST['tipoProveedor'])) {
        if($_POST['tipoProveedor'] == "Extranjero") {
            $tipoProveedor = array("Empresa extranjera", "Persona extranjera");
            $tipoDocumento = array("NRC","Otro");
        } else {
            // Local
            $tipoProveedor = array("Empresa local", "Persona local");
            $tipoDocumento = array("NIT","DUI","Otro");
        }
    } else {
        $tipoProveedor = array("Empresa local", "Persona local", "Empresa extranjera", "Persona extranjera");
        $tipoDocumento = array("NIT","DUI","Otro");
    }

    // Esta modal se utiliza desde Quedan para crear nuevo proveedor
    $interfaz = isset($_POST['interfaz']) ? $_POST['interfaz'] : "proveedor";
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="proveedor">
<input type="hidden" id="proveedorId" name="proveedorId" value="<?php echo $proveedorId;?>">
<div class="row">
    <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select id="tipoProveedor" name="tipoProveedor" style="width: 100%;" required>
                <option></option>
                <?php 
                    for ($i=0; $i < count($tipoProveedor); $i++) { 
                        echo '<option value="'.$tipoProveedor[$i].'">'.$tipoProveedor[$i].'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-id-card trailing"></i>
            <input type="text" id="nrcProveedor" class="form-control" name="nrcProveedor" required />
            <label class="form-label" for="nrcProveedor">NRC del proveedor</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select id="tipoDocumento" name="tipoDocumento" style="width: 100%;" required>
                <option></option>
                <?php 
                    for ($i=0; $i < count($tipoDocumento); $i++) { 
                        echo '<option value="'.$tipoDocumento[$i].'">'.$tipoDocumento[$i].'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="text" id="numDocumento" class="form-control masked masked-numDocumento" name="numDocumento" readonly required />
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
        <div class="form-outline mb-4">
            <i class="fas fa-user trailing"></i>
            <input type="text" id="nombreComercial" class="form-control" name="nombreComercial" required />
            <label class="form-label" for="nombreComercial">Nombre comercial</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <input type="number" id="codProveedorMagic" class="form-control" name="codProveedorMagic" >
            <label class="form-label" for="codProveedorMagic">Código de proveedor Magic</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-sort-numeric-up trailing"></i>
            <input type="text" id="cuentaContableProveedor" class="form-control" name="cuentaContableProveedor" />
            <label class="form-label" for="cuentaContableProveedor">Cuenta contable del proveedor</label>
        </div>
    </div>
</div>
<div id="divLocal1" class="row">
    <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select id="actividadEconomicaId" name="actividadEconomicaId" style="width: 100%;" required>
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
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-user trailing"></i>
            <input type="text" id="nombreCompletoRL" class="form-control" name="nombreCompletoRL" />
            <label class="form-label" for="nombreCompletoRL">Nombre del Representante Legal (RL)</label>
        </div>
    </div>
</div>

<div id="divLocal2" class="row">
    <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select id="tipoDocumentoRL" name="tipoDocumentoRL" style="width: 100%;">
                <option></option>
                <?php 
                    $tipoDocumentoRL = array("NIT","DUI","Pasaporte","Carnet de residente","Otro");
                    for ($i=0; $i < count($tipoDocumentoRL); $i++) { 
                        echo '<option value="'.$tipoDocumentoRL[$i].'">'.$tipoDocumentoRL[$i].'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="text" id="numDocumentoRL" class="form-control masked masked-numDocumentoRL" name="numDocumentoRL" readonly />
            <label class="form-label" for="numDocumentoRL">Número de documento RL</label>
            <div id="leyendaNumDocumentoRL" class="form-helper"></div>
        </div>
    </div>
</div>
<div id="divExtranjero" class="form-outline mb-4">
    <i class="fas fa-edit trailing"></i>
    <textarea type="text" id="descripcionExtranjero" class="form-control" name="descripcionExtranjero"></textarea>
    <label class="form-label" for="descripcionExtranjero">Descripción del proveedor</label>
</div>
<script>

    $(document).ready(function() {
        Maska.create('#frmModal .masked');
        $("#divExtranjero").hide();
        $("#divLocal1").hide();
        $("#divLocal2").hide();

        $("#tipoProveedor").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de proveedor'
        });
         
        $("#tipoDocumento").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de documento'
        });

        $("#tipoDocumentoRL").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de documento RL'
        });

        $("#actividadEconomicaId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Giro/Actividad económica'
        });
        
        $("#tipoProveedor").change(function(e) {
            if($(this).val() == "Empresa extranjera" || $(this).val() == "Persona extranjera") {
                $("#divLocal1").hide();
                $("#divLocal2").hide();
                $("#divExtranjero").show();
            } else {
                $("#divLocal1").show();
                $("#divLocal2").show();
                $("#divExtranjero").hide();
            }
        });

        $("#tipoDocumento").change(function(e) {
            if($('#tipoDocumento').val() == "NIT") {
                $("#numDocumento").val('');
                $("#numDocumento").removeAttr("readonly");
                $("#leyendaNumDocumento").html("");
                Maska.create('.masked-numDocumento',{
                    mask: '####-######-###-#'
                });
                $("#numDocumento").attr("minlength", 17);
            } else if($('#tipoDocumento').val() == "DUI") {
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
                                $("#tblProveedores").DataTable().ajax.reload(null, false);
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
                $("#modalTitle").html('Editar proveedor: <?php echo $datProveedor->nombreProveedor;?>');
                $("#tipoProveedor").val('<?php echo $datProveedor->tipoProveedor; ?>').trigger('change');
                $("#nrcProveedor").val(`<?php echo $datProveedor->nrcProveedor; ?>`);
                $("#tipoDocumento").val('<?php echo $datProveedor->tipoDocumento; ?>').trigger('change');
                $("#numDocumento").val('<?php echo $datProveedor->numDocumento; ?>');
                $("#numDocumento").removeAttr("readonly");
                $("#numDocumentoRL").removeAttr("readonly");
                $("#nombreProveedor").val(`<?php echo $datProveedor->nombreProveedor; ?>`);
                $("#nombreComercial").val(`<?php echo $datProveedor->nombreComercial; ?>`);

                if(`<?php echo $datProveedor->tipoProveedor; ?>` == "Empresa extranjera" || `<?php echo $datProveedor->tipoProveedor; ?>` == "Persona extranjera") {
                    $("#descripcionExtranjero").val(`<?php echo $datProveedor->descripcionExtranjero; ?>`);
                } else {
                    $("#nombreCompletoRL").val(`<?php echo $datProveedor->nombreCompletoRL; ?>`);
                    $("#actividadEconomicaId").val('<?php echo $datProveedor->actividadEconomicaId; ?>').trigger('change');
                    $("#tipoDocumentoRL").val('<?php echo $datProveedor->tipoDocumentoRL; ?>').trigger('change');
                    $("#numDocumentoRL").val('<?php echo $datProveedor->numDocumentoRL; ?>');
                }
                $("#codProveedorMagic").val('<?php echo $datProveedor->codProveedorMagic; ?>');
                $("#cuentaContableProveedor").val('<?php echo $datProveedor->cuentaContable; ?>');
        <?php
            } else{
        ?> 
                $("#modalTitle").html('Nuevo proveedor');
        <?php
            }
        ?>

    });
</script>