<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
?>
<input type="hidden" id="typeOperation" name="typeOperation"  value="insert">
<input type="hidden" id="operation" name="operation" value="invalidacion-extraordinaria">
<input type="hidden" id="cierreDeclaracionId" name="cierreDeclaracionId" value="<?php echo $_POST["idDeclaracion"];?>">

<div id="frmAnulacion" style="display: none;">
    <div class="row">
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select class="form-select" id="sucursalId" name="sucursalId[]" style="width:100%;" required>
                    <option></option>
                    <?php 
                        $dataSucursal = $cloud->rows("
                            SELECT
                            sucursalId,
                            sucursal
                            FROM cat_sucursales 
                            WHERE flgDelete = '0' 
                        ");
                        foreach ($dataSucursal as $datSucursal) {
                            echo '<option value="'.$datSucursal->sucursalId.'">'.$datSucursal->sucursal.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-4" >
            <div class="form-select-control mb-4">
                <select id="tipoDTEId" name="tipoDTEId" style="width:100%;" class="form-control" required>
                    <option></option>
                    <?php
                    $dataTipoDTE = $cloud->rows("
                        SELECT
                            tipoDTEId,
                            codigoMH,
                            tipoDTE
                        FROM mh_002_tipo_dte
                        WHERE flgDelete = ? AND tipoDTEId IN (1, 9)
                    ",[0]);
    
                    foreach ($dataTipoDTE as $dataTipoDTE) {
                        echo "<option value='$dataTipoDTE->tipoDTEId'>($dataTipoDTE->codigoMH) $dataTipoDTE->tipoDTE</option>";
                    }
                    ?>
                </select>  
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="yearBD" name="yearBD" style="width: 100%;" required>
                    <option></option>
                    <?php
                        for ($i=date("Y"); $i >= 2024; $i--) { 
                            echo "<option value='_$i' ".($i == date("Y") ? "selected" : "").">$i</option>";
                        }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div id="divEstacionesSucursal" class="form-select-control mb-4">
                <select id="listaDTE" name="listaDTE[]" style="width: 100%;" multiple="multiple" required>
                    <option></option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline">
                <input type="date" id="fechaPeriodo" class="form-control" name="fechaPeriodo" required />
                <label class="form-label" for="fechaPeriodo">Fecha del periodo que afectará</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline" data-mdb-input-init>
                <textarea name="motivoAnulacion" id="motivoAnulacion" class="form-control" required></textarea>
                <label class="form-label" for="motivoAnulacion">Motivo de anulación</label>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12 text-end">
            <button type="submit" id="agregar" class="btn btn-primary btn-sm">
                <i class="fas fa-save"></i> Guardar
            </button> 
            <button id="btnCancel" type="button" class="btn btn-secondary btn-sm">
                <i class="fas fa-times-circle"></i> Cancelar
            </button>
        </div>
    </div>
</div>
<div class="text-end" id="btn-nuevo">
    <button type="button" id="nuevaAnulacion" class="btn btn-primary btn-sm">
        <i class="fas fa-save"></i> Nueva invalidación
    </button>
</div>
<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table id="tblAnulacion" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-tblAnulacion">
                        <th>#</th>
                        <th>DTE</th>
                        <th>Motivo</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    function eliminarAnulacion(frmData){
        mensaje_confirmacion(
            `¿Esta seguro que desea eliminar esta anulación de periodo pasado?`,
            `Se eliminará de el registro.`,
            `warning`,
            (param) => {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
                    frmData,
                    (data) => {
                        if (data=="success") {
                            mensaje_do_aceptar(
                            `Operación completada`,
                            `Anulación eliminada con éxito`,
                            `success`,
                            () => {
                                $(`#tblAnulacion`).DataTable().ajax.reload(null,false);
                                $(`#tblDeclaraciones`).DataTable().ajax.reload(null,false);
                            });
                        }else{
                            mensaje(
                                "Aviso:",
                                data,
                                "warnig"
                            );
                        }
                    }
                );
            },
            `Eliminar`,
            `Cancelar`
        )
    }
    $(document).ready(function() {

        $("#nuevaAnulacion").click(function(){
            $("#frmAnulacion").show();
            $("#btn-nuevo").hide();
        });
        $("#btnCancel").click(function(){
            $("#frmAnulacion").hide();
            $("#btn-nuevo").show();

            $('#frmModal').trigger("reset");

            $('#frmModal').trigger("reset");
            $('#listaDTE').trigger('change');
            $('#sucursalId').trigger('change');
            $('#tipoDTEId').trigger('change');
            $("#yearBD").val("<?php echo '_' . date('Y'); ?>").trigger("change");
        });


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
                                `Anulación procesada con éxito.`,
                                "success",
                            );
                            // $('#modal-container').modal("hide");
                            $(`#tblAnulacion`).DataTable().ajax.reload(null,false);
                            $(`#tblDeclaraciones`).DataTable().ajax.reload(null,false);
                            $("#frmAnulacion").hide();
                            $("#btn-nuevo").show();

                            $('#frmModal').trigger("reset");
                            $('#listaDTE').trigger('change');
                            $('#sucursalId').trigger('change');
                            $('#tipoDTEId').trigger('change');
                            $("#yearBD").val("<?php echo '_' . date('Y'); ?>").trigger("change");
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
        
        $("#tipoDTEId").select2({
            placeholder: 'Tipo de DTE', 
            dropdownParent: $('#modal-container')
        });
        $("#sucursalId").select2({
            placeholder: 'Sucursal', 
            dropdownParent: $('#modal-container')
        });
        $("#yearBD").select2({
            placeholder: 'Año', 
            dropdownParent: $('#modal-container')
        });
        $("#listaDTE").select2({
            placeholder: 'DTE', 
            dropdownParent: $('#modal-container')
        });

        $("#cierreDeclaracionId").change(function(e) {
            asyncSelect(
                `<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListaDTEPeriodoPasado`,
                {
                    cierreDeclaracionId: $(this).val(),
                    sucursalId: $("#sucursalId").val(),
                    tipoDTEId: $("#tipoDTEId").val(),
                    yearBD: $("#yearBD").val()
                },
                `listaDTE`,
                function() {

                }
            );
        });

        $("#sucursalId").change(function(e) {
            asyncSelect(
                `<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListaDTEPeriodoPasado`,
                {
                    cierreDeclaracionId: $("#cierreDeclaracionId").val(),
                    sucursalId: $(this).val(),
                    tipoDTEId: $("#tipoDTEId").val(),
                    yearBD: $("#yearBD").val()
                },
                `listaDTE`,
                function() {

                }
            );
        });

        $("#tipoDTEId").change(function(e) {
            asyncSelect(
                `<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListaDTEPeriodoPasado`,
                {
                    cierreDeclaracionId: $("#cierreDeclaracionId").val(),
                    sucursalId: $("#sucursalId").val(),
                    tipoDTEId: $(this).val(),
                    yearBD: $("#yearBD").val()
                },
                `listaDTE`,
                function() {

                }
            );
        });

        $("#yearBD").change(function(e) {
            asyncSelect(
                `<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListaDTEPeriodoPasado`,
                {
                    cierreDeclaracionId: $("#cierreDeclaracionId").val(),
                    sucursalId: $("#sucursalId").val(),
                    tipoDTEId: $("#tipoDTEId").val(),
                    yearBD: $(this).val()
                },
                `listaDTE`,
                function() {

                }
            );
        });

        $('#tblAnulacion thead tr#filterboxrow th').each(function(index) {
            //if(index == 1) {
                var title = $('#tblAnulacion thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblAnulacion.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            //} else {
            //}
        });

        let tblAnulacion = $('#tblAnulacion').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableAnulacionPasada",
                "data": function() { // En caso que se quiera enviar variable a la consulta
                    return {
                        "filtroPeriodo": $("#cierreDeclaracionId").val()
                    }
                }
            },
            "autoWidth": false,
            "columns": [
            	null,
                {"width": "35%"},
                {"width": "25%"},
                {"width": "25%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3, 4] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

});
</script>