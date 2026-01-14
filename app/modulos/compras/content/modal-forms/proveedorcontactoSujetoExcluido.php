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
                de.paisId AS paisId
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
<input type="hidden" id="operation" name="operation" value="contacto-sujetos-excluidos">
<input type="hidden" id="sujetoExcluidoId" name="sujetoExcluidoId" value="<?php echo $sujetoExcluidoId;?>">
<!--<input type="hidden" id="proveedorUbicacionId" name="proveedorUbicacionId" value=""> -->


<div class="row">
      <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="email" id="correo" class="form-control" name="correo" required />
            <label class="form-label" for="correo">Correo</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline mb-4">
            <i class="fas fa-address-card trailing"></i>
            <input type="text" id="telefono" class="form-control masked masked-telefono" name="telefono" required />
            <label class="form-label" for="telefono">Teléfono</label>
        </div>
    </div>

</div>


<script>
    
    $(document).ready(function() {
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