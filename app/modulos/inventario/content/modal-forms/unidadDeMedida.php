<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /**
     arrayFormData 
        Nuevo  = insert
        editar = update ^ categoriaId
     */

    $arrayFormData   = explode("^", $_POST["arrayFormData"]);

    $nombreUnidadMedida = "";
    $txtSuccess         = "Unidad de Medida agregada con éxito.";
    $abreviatura        = "";
    $codigoMH           = "";

    if (!empty($arrayFormData[1])){
        $dataUnidadMedida = $cloud->row("
        SELECT 
            unidadMedidaId,
            codigoMH,
            nombreUnidadMedida,
            abreviaturaUnidadMedida,
            tipoMagnitud
        FROM cat_unidades_medida
        WHERE unidadMedidaId = $arrayFormData[1] AND flgDelete = 0
    ");

        $nombreUnidadMedida = 'value="'.$dataUnidadMedida->nombreUnidadMedida.'"';
        $txtSuccess         = "Unidad de Medida editada con éxito";
        $abreviatura        = 'value="'.$dataUnidadMedida->abreviaturaUnidadMedida.'"';
        $codigoMH           = 'value="'.$dataUnidadMedida->codigoMH.'"';
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $arrayFormData[0]; ?>">
<input type="hidden" id="operation" name="operation" value="unidad-medida">
<?php if (!empty($arrayFormData[1])){ ?>
<input type="hidden" id="unidadMedidaId" name="unidadMedidaId" value="<?php echo $dataUnidadMedida->unidadMedidaId;?>">
<?php } ?>
<div class="row">
    <label for="">Para agregar la abreviatura de la unidad de medida con caracteres especiales, dé clic en el botón respectivo.</label><p>
    <div class="col-md-12">
        <button type="button" id="boton1" class="btn btn-info btn-sm ttip" onClick="copiarValue(this.value)" value="²"><sup>2</sup><span class="ttiptext">Superindice</span></button>
        <button type="button" id="boton2" class="btn btn-info btn-sm ttip" onClick="copiarValue(this.value)" value="³"><sup>3</sup><span class="ttiptext">Superindice</span></button>
        <button type="button" id="boton3" class="btn btn-info btn-sm ttip" onClick="copiarValue(this.value)" value="º">º<span class="ttiptext">Grados</span></button>
        <button type="button" id="boton3" class="btn btn-info btn-sm ttip" onClick="copiarValue(this.value)" value="·">·<span class="ttiptext">·</span></button>
        <button type="button" id="boton3" class="btn btn-info btn-sm ttip" onClick="copiarValue(this.value)" value="Ω">Ω<span class="ttiptext">omega</span></button>
        <button type="button" id="boton3" class="btn btn-info btn-sm ttip" onClick="copiarValue(this.value)" value="Φ">Φ<span class="ttiptext">Fi</span></button>
        <button type="button" id="boton3" class="btn btn-info btn-sm ttip" onClick="copiarValue(this.value)" value="ρ">ρ<span class="ttiptext">rho</span></button>
        <button type="button" id="boton3" class="btn btn-info btn-sm ttip" onClick="copiarValue(this.value)" value="π">π<span class="ttiptext">pi</span></button>
        <button type="button" id="boton3" class="btn btn-info btn-sm ttip" onClick="copiarValue(this.value)" value="η">η<span class="ttiptext">eta</span></button>
        <button type="button" id="boton3" class="btn btn-info btn-sm ttip" onClick="copiarValue(this.value)" value="λ">λ<span class="ttiptext">lambda</span></button>

    </div> 
    <!--<p>
    <div class="col-md-12">
        
        <button type="button" id="boton3" class="btn btn-info btn-sm ttip" onClick="copiarValue(this.value)" value="μ">μ<span class="ttiptext">mu</span></button>            
    </div>-->
    
</div>
<hr>
<div class="row">
    <div class="col-md-8">
        <div class="form-outline mb-4">
            <i class="fas fa-ruler-combined trailing"></i>
            <input type="text" id="nombreUnidadMedida" class="form-control" name="nombreUnidadMedida" <?php echo $nombreUnidadMedida;?> required>
            <label class="form-label" for="nombreUnidadMedida">Nombre Unidad medida</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-thumbtack trailing"></i>
            <input type="text" id="abreviatura" class="form-control" name="abreviatura" <?php echo $abreviatura;?> required>
            <label class="form-label" for="abreviatura">Abreviatura</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="form-select-control mb-4">
            <select id="tipoMagnitud" name="tipoMagnitud" style="width:100%;" required>
                <option></option> 
                <?php 
                $arrayOptions = array("Longitud","Material","Masa","Tiempo","Velocidad","Aceleración","Área","Fuerza","Superficie","Volumen","Temperatura","Presión","Trabajo/Energía","Potencia","Cantidad","Carga Eléctrica","Potencial Eléctrico","Frecuencia","Conductancia Eléctrica","Intensidad de corriente eléctrica","Actividad Radiactiva","Resistencia eléctrica","Cantidad de sustancia","Intensidad luminosa","Carga Magnética","Flujo Magnético","Intensidad del Flujo Magnético","Flujo Luminoso","Capacidad eléctrica","Iluminancia","Radiación Ionizante","Dosis de Radiación","No aplica");
                    foreach($arrayOptions as $option){
                        echo '<option value="'. $option .'">'. $option .'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-list-ol trailing"></i>
            <input type="text" id="codigoMH" class="form-control" name="codigoMH" <?php echo $codigoMH;?> required>
            <label class="form-label" for="codigoMH">Código Hacienda</label>
        </div>
    </div>
</div>

<script>

    function copiarValue (valor){
        $("#abreviatura").addClass("active") ;
        document.getElementById('abreviatura').value += valor;
        //console.log(valor);
    }

    $(document).ready(function() {

        $("#tipoMagnitud").select2({
            placeholder: "Tipo de Magnitud",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        <?php if (!empty($arrayFormData[1])){ ?>
            $('#tipoMagnitud').val('<?php echo $dataUnidadMedida->tipoMagnitud;?>').trigger('change');
        <?php } ?>

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                '<?php echo $txtSuccess;?>',
                                "success"
                            );
                            var tblUnidadMedida = $("#operation").val();
                            $("#tblUnidadMedida").DataTable().ajax.reload(null, false);
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
            if(!empty($arrayFormData[1])) {
        ?>
            $("#modalTitle").html('Editar Unidad de Medida: <?php echo $dataUnidadMedida->nombreUnidadMedida; ?>');
        <?php 
            } 
        ?>

    });
</script>