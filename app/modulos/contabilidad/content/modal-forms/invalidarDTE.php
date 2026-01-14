<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

    $yearBD = $_POST['yearBD'];
?>


<input type="hidden" id="typeOperation" name="typeOperation"  value="<?php echo $_POST['typeOperation']; ?>">
<input type="hidden" id="operation" name="operation" value="invalidar-dte">
<input type="hidden" id="facturaId" name="facturaId" value="<?php echo $_POST['facturaId']; ?>">
<input type="hidden" id="codigoGeneracion" name="codigoGeneracion" value="<?php echo $_POST['codigoGeneracion']; ?>">
<input type="hidden" id="proveedorId" name="proveedorId" value="<?php echo $_POST['proveedorId']; ?>">
<input type="hidden" id="yearBDModal" name="yearBD" value="<?php echo $yearBD; ?>">
<h4>Documento a invalidar</h4>
<div class="row mb-3">
    <div class="col-md-6"><?php echo $_POST["tipoDTE"]; ?></div>
    <div class="col-md-6"><?php echo $_POST["numeroControl"]; ?></div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-select-control">
            <select id="tipoAnulacion" name="tipoAnulacion" style="width: 100%;" required>
            <option></option>
                <?php
                    $dataTipoAnulacion = $cloud->rows("
                        SELECT 
                            tipoAnulacionId, 
                            codigoMH, 
                            tipoAnulacion
                        FROM mh_024_tipo_anulacion
                        WHERE tipoAnulacionId IN(2)");    
                    foreach ($dataTipoAnulacion as $dataTipoAnulacion) {
                        echo "<option value='$dataTipoAnulacion->codigoMH'> $dataTipoAnulacion->tipoAnulacion</option>";
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline" data-mdb-input-init>
            <textarea name="motivoAnulacion" id="motivoAnulacion" class="form-control"></textarea>
            <label class="form-label" for="motivoAnulacion">Motivo de anulación</label>
        </div>
    </div>
</div>
<div id="divDTESustituye">
    <h4>Documento que sustituye</h4>
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="form-select-control">
                <select id="tipoGenDoc" name="tipoGenDoc" style="width: 100%;" required>
                <option></option>
                    <?php
                        $tipoGenDoc = $cloud->rows('SELECT codigoMH, tipoGeneracionDoc FROM mh_007_tipo_generacion_documento WHERE flgDelete = ?', ['0']);

                        foreach ($tipoGenDoc as $tipoGenDoc){
                            echo '<option value="'.$tipoGenDoc->codigoMH.'">'.$tipoGenDoc->tipoGeneracionDoc.'</option>';
                        }
                     ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-select-control">
                <select id="tipoDoc" name="tipoDoc" style="width: 100%;" required>
                <option></option>
                    <?php
                        $tipoDoc = $cloud->rows('SELECT tipoDTEId, tipoDTE FROM mh_002_tipo_dte WHERE flgDelete = ?', ['0']);

                        foreach ($tipoDoc as $tipoDoc){
                            echo '<option value="'.$tipoDoc->tipoDTEId.'">'.$tipoDoc->tipoDTE.'</option>';
                        }
                     ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-select-control">
                <select id="listaDTE" name="listaDTE" style="width: 100%;" required>
                <option></option>
                    
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-select-control">
            <select id="personaAnulacion" name="personaAnulacion" style="width: 100%;" required>
            <option></option>
            <?php
                    $personaAnulacion = $cloud->rows("
                        SELECT personaId, nombreCompleto FROM view_expedientes
                        WHERE estadoPersona = 'Activo' AND estadoExpediente = 'Activo'
                        ORDER BY nombreCompleto");    
                    foreach ($personaAnulacion as $personaAnulacion) {
                        echo "<option value='$personaAnulacion->personaId'> $personaAnulacion->nombreCompleto</option>";
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-3">
            <input type="text" id="tipoDocumento" class="form-control" name="tipoDocumento" required readonly>
            <label class="form-label" for="tipoDocumento">Tipo de documento</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-3">
            <input type="text" id="numDocumento" class="form-control" name="numDocumento" required readonly>
            <label class="form-label" for="numDocumento">Número de documento</label>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#divDTESustituye").hide();

        $("#tipoAnulacion").select2({
            placeholder: 'Tipo de anulación', 
            dropdownParent: $('#modal-container')
        });

        $("#personaAnulacion").select2({
            placeholder: 'Persona que anula', 
            dropdownParent: $('#modal-container')
        });

        $("#tipoGenDoc").select2({
            placeholder: 'Tipo de generación del documento', 
            dropdownParent: $('#modal-container')
        });

        $("#tipoDoc").select2({
            placeholder: 'Tipo de documento que sustituye', 
            dropdownParent: $('#modal-container')
        });

        $("#listaDTE").select2({
            placeholder: 'Documento que sustituye', 
            dropdownParent: $('#modal-container')
        });
        
        $("#personaAnulacion").change(function(e) {
            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>content/divs/datosPersonaInvalidacion",
                {
                    id: $(this).val()
                },
                function(data) {
                    $("#tipoDocumento").val(data.docIdentidad).addClass('active');
                    $("#numDocumento").val(data.numIdentidad).addClass('active');
                }
            );
        });

        $("#tipoDoc").change(function(e) {
            asyncSelect(
                `<?php echo $_SESSION['currentRoute']; ?>/content/divs/listarDTEtipo`,
                {
                    tipoDTE: $(this).val(),
                    proveedorId: <?php echo $_POST['proveedorId']; ?>,
                    nombreProveedor: '<?php echo $_POST["nombreProveedor"]; ?>',
                    yearBD: '<?php echo $yearBD; ?>'
                },
                `listaDTE`
            );
        });
        $("#tipoAnulacion").change(function(e) {
            if($(this).val() == "2") {
                $("#divDTESustituye").hide();
            } else {
                $("#divDTESustituye").show();
            }
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data.respuesta == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `DTE ${data.facturaId} invalidado con estado ${data.estado}. ${(data.estado == "PROCESADO" ? 'Puede visualizarlo desde el menú "Documentos tributarios electrónicos".' : 'Por favor, verifique el error y vuelva a invalidar el DTE.')}<br>
                                Sello de recibido: ${data.selloRecibido}<br>
                                Código generación: ${data.codigoGeneracion}
                                `,
                                `${(data.estado == "PROCESADO" ? 'success' : 'warning')}`,
                                function() {                                    
                                    $("#modal-container").modal("hide");
                                    location.reload();
                                }
                            );
                        } else {
                            mensaje("Aviso:", data.respuesta, "warning");
                        }
                    }
                );
            }
        });
    });
</script>