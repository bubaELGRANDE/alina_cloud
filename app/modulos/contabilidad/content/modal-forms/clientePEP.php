<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

    if ($_POST['typeOperation'] == "update"){
        $getPEP = $cloud->row("SELECT 
        clientePEPId, nombreCompletoPEP, cargoPublico, fechaNombramiento, fechaFinNombramiento, tipoDocumentoMHId,numDocumentoPEP, institucionCargoPublico, tipoPEP, tipoRelacionPEP
        FROM fel_clientes_pep 
        WHERE clientePEPId = ?", [$_POST['clienteId']]);
    }
?>

    <input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
	<input type="hidden" id="operation" name="operation" value="datos-cliente-PEP">
	<input type="hidden" id="idCliente" name="idCliente" value="<?php echo $_POST['clienteId']; ?>">
	<input type="hidden" id="flgTipo" name="flgTipo" value="<?php echo $_POST['flgTipo']; ?>">

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-outline">
                <i class="fas flist-ul trailing"></i>
                <input type="text" id="nombreCliente" class="form-control" name="nombreClientePEP" required>
                <label class="form-label" for="nombreCliente">Nombre completo</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-outline">
                <i class="fas flist-ul trailing"></i>
                <input type="text" id="cargoPublico" class="form-control" name="cargoPublico" required>
                <label class="form-label" for="cargoPublico">Cargo público que desempeña o ha desempeñado</label>
            </div>
        </div>
        
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-outline">
                <i class="fas flist-ul trailing"></i>
                <input type="text" id="institucion" class="form-control" name="institucion" required>
                <label class="form-label" for="institucion">Institución donde desempeña el cargo público</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline mb-4">
                <input type="date" id="fechaNombramiento" class="form-control" name="fechaNombramiento" required>
                <label class="form-label" for="fechaNombramiento">Fecha de nombramiento</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline mb-4">
                <input type="date" id="fechaNombramientoFin" class="form-control" name="fechaNombramientoFin">
                <label class="form-label" for="fechaNombramientoFin">Fecha de finalización</label>
            </div>
        </div>
        
    </div>
    <div class="row mb-3">
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
        <div class="col-md-4">
            <div class="form-select-control">
                <select id="tipoPEP" name="tipoPEP" style="width: 100%;" required>
                    <option></option>
                    <option value="Funcionario púlico">Funcionario público</option>
                    <option value="Persona relacionada">Persona relacionada</option>
                </select>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            $("#tipoDoc").select2({
                dropdownParent: $('#modal-container'),
                placeholder: 'Tipo de documento', 
                allowClear: true
            });
            $("#tipoPEP").select2({
                dropdownParent: $('#modal-container'),
                placeholder: 'Tipo de persona expuesta politicamente', 
                allowClear: true
            });
            $("#tipoRelacionPEP").select2({
                dropdownParent: $('#modal-container'),
                placeholder: 'Tipo de relación', 
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

            <?php if ($_POST['typeOperation'] == "update"){ ?>
                $("#nombreCliente").val('<?php echo $getPEP->nombreCompletoPEP;?>');
                $("#cargoPublico").val('<?php echo $getPEP->cargoPublico;?>');
                $("#institucion").val('<?php echo $getPEP->institucionCargoPublico;?>');
                $("#fechaNombramiento").val('<?php echo $getPEP->fechaNombramiento;?>');
                $("#fechaNombramientoFin").val('<?php echo $getPEP->fechaFinNombramiento;?>');
                $("#tipoDoc").val('<?php echo $getPEP->tipoDocumentoMHId;?>').trigger('change');
                $("#numeroDocumento").val('<?php echo $getPEP->numDocumentoPEP;?>');
                $("#tipoPEP").val('<?php echo $getPEP->tipoPEP;?>').trigger('change');
            <?php } ?>
            
            $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation.php", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");

                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Información de persona expuesta politicamente agregada correctamente.',
                                "success"
                            );
                            $('#tblPEP').DataTable().ajax.reload(null, false);
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
        });
    </script>