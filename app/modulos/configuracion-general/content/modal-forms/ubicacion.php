<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /**
     arrayFormData 
        Nuevo  = insert ^ bodegId
        Nuevo  = insert ^ bodegId ^ ubicacionSuperiorId => inventarioUbicacionId
        editar = update ^ bodegId ^ inventarioUbicacionId
     */

    $arrayFormData      = explode("^", $_POST["arrayFormData"]);

    $maxOrdenUbicacion = $cloud->row("
        SELECT MAX(orden) AS orden
        FROM inv_ubicaciones
        WHERE bodegaId = $arrayFormData[1] AND flgDelete = 0
    ");

    if(isset($maxOrdenUbicacion->orden)){
        $orden = str_pad($maxOrdenUbicacion->orden+1,2,"0",STR_PAD_LEFT);
    }else{
        $orden = "01";
    }

    $nivel           = 1;
    $txtSuccess      = "Ubicación agregada con éxito.";
    $codigoUbicacion = "";
    $nombreUbicacion = "";

    if (!empty($arrayFormData[2]) AND $arrayFormData[0]=="update"){
        $datUbicacion = $cloud->row("
            SELECT
                inventarioUbicacionId,
                nombreUbicacion,
                codigoUbicacion,
                ubicacionSuperiorId
            FROM inv_ubicaciones
            WHERE inventarioUbicacionId = $arrayFormData[2] AND flgDelete = 0
        ");

        $codigoUbicacion = 'value="'.$datUbicacion->codigoUbicacion.'"';
        $nombreUbicacion = 'value="'.$datUbicacion->nombreUbicacion.'"';
        $txtSuccess      = "Ubicación editada con éxito";
    }

    if (!empty($arrayFormData[2]) AND $arrayFormData[0]=="insert") {
        $ubicacionSuperiorId = $arrayFormData[2];

        $datUbicacionSuperior = $cloud->row("
            SELECT
                inventarioUbicacionId,
                nombreUbicacion,
                codigoUbicacion,
                ubicacionSuperiorId,
                orden,
                nivel
            FROM inv_ubicaciones
            WHERE inventarioUbicacionId = $ubicacionSuperiorId AND flgDelete = 0
        ");

        $maxOrdenUbicacion = $cloud->row("
        SELECT MAX(orden) AS orden
            FROM inv_ubicaciones
            WHERE bodegaId = $arrayFormData[1] AND ubicacionSuperiorId = $ubicacionSuperiorId AND flgDelete = 0
        ");

        if(isset($maxOrdenUbicacion->orden)){
            $numSubNiveles = substr($maxOrdenUbicacion->orden, -2);
            $orden         = $datUbicacionSuperior->orden."".str_pad($numSubNiveles+1,2,"0",STR_PAD_LEFT);
        }else{

            $orden = $datUbicacionSuperior->orden."".str_pad(0+1,2,"0",STR_PAD_LEFT);
        }

        $nivel = $datUbicacionSuperior->nivel+1;
        
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $arrayFormData[0]; ?>">
<input type="hidden" id="operation" name="operation" value="ubicacion-bodega">
<input type="hidden" id="bodegaId" name="bodegaId" value="<?php echo $arrayFormData[1];?>">
<input type="hidden" id="orden" name="orden" value="<?php echo $orden;?>">
<input type="hidden" id="nivel" name="nivel" value="<?php echo $nivel;?>">
<?php if (!empty($arrayFormData[2]) AND $arrayFormData[0]=="update"){ ?>
<input type="hidden" id="inventarioUbicacionId" name="inventarioUbicacionId" value="<?php echo $datUbicacion->inventarioUbicacionId;?>">
<?php } ?>
<?php if (!empty($arrayFormData[2]) AND $arrayFormData[0]=="insert"){ ?>
<input type="hidden" id="ubicacionSuperiorId" name="ubicacionSuperiorId" value="<?php echo $ubicacionSuperiorId;?>">
<?php } ?>
<div class="row">
    <div class="col-lg-12">
        <div class="form-outline mb-4">
            <i class="fas fa-qrcode trailing"></i>
            <input type="text" id="codigoUbicacion" class="form-control" name="codigoUbicacion" <?php echo $codigoUbicacion;?> required />
            <label class="form-label" for="codigoUbicacion">Código</label>
        </div>
    </div>
    
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="form-outline mb-4">
            <i class="fas fa-building trailing"></i>
            <input type="text" id="nombreUbicacion" class="form-control" name="nombreUbicacion" <?php echo $nombreUbicacion;?> required />
            <label class="form-label" for="nombreUbicacion">Ubicación</label>
        </div>
    </div>

</div>
<br>

<script>

    $(document).ready(function() {

        $("#frmModal").validate({
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
                                '<?php echo $txtSuccess;?>',
                                "success"
                            );
                            var tblUbicaciones = $("#operation").val();
                            $("#tblUbicaciones").DataTable().ajax.reload(null, false);
                            $('#modal-container').modal("hide");
                            getUbicacionesBodega(<?php echo $arrayFormData[1]; ?>);
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
            if(!empty($arrayFormData[2]) AND $arrayFormData[0]=="update") {
        ?>
            $("#modalTitle").html('Editar Ubicación: <?php echo $datUbicacion->nombreUbicacion; ?>');
        <?php 
            } elseif (!empty($arrayFormData[2]) AND $arrayFormData[0]=="insert"){
        ?>
            $("#modalTitle").html('Nuevo subnivel en: <?php echo $datUbicacionSuperior->nombreUbicacion; ?>');
        <?php
            } else{
        ?> 
            //$("#modalTitle").html('Nueva Ubicación');
        <?php
            }
        ?>

    });
</script>