<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

            // Traer del catalogo, ya que hay lineas que no se han facturado pero es necesario dejarlas parametrizadas
            $sqlParametrizacion = "
                SELECT 
                    CONCAT('(', codigoSubcentroCosto, ') ', nombreSubcentroCosto) AS ValorSubCentro,
                    subCentroCostoId
                FROM conta_subcentros_costo
                WHERE flgDelete = ?
                ORDER BY codigoSubcentroCosto
            ";
            $selectPlaceholder = "Sub centro(s)";
            $tblClasificacion = "tblClasifLineas";


?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operationDetalle" name="operation" value="subcentros-costos-detalle">
<input type="hidden" id="centroCostoId" name="centroCostoId" value="<?php echo $_POST['centroCostoId']; ?>">

<div class="row justify-content-center">
    <div class="col-9">
        <div class="form-select-control mb-4">
            <select class="form-select" id="subCentroCostoId" name="subCentroCostoId" style="width:100%;"  required>
                <option></option>
                <?php 
                    $dataParamDetalle = $cloud->rows($sqlParametrizacion, [0]);
                    foreach ($dataParamDetalle as $data) {
                        echo '<option value="'.$data->subCentroCostoId.'">'.$data->ValorSubCentro.'</option>';
                    }
                ?>
            </select>
        </div>
    </div> 
    <input type="hidden" name="valorSubCentroTexto" id="valorSubCentroTexto">

    <div class="col-3">
        <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>
</div>

<div class="table-responsive">
    <table id="tblsubCentroDetalle" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-detalle">
                <th>#</th>
                <th>Sub centros de costos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $("#subCentroCostoId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: '<?php echo $selectPlaceholder; ?>'
        });

        $("#subCentroCostoId").on("change", function () {
            let selectedText = $(this).find("option:selected").text();
            $("#valorSubCentroTexto").val(selectedText);
        });

        $('#tblsubCentroDetalle thead tr#filterboxrow-detalle th').each(function(index) {
            if(index==1) {
                var title = $('#tblsubCentroDetalle thead tr#filterboxrow-detalle th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-detalle" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-detalle">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblsubCentroDetalle.column($(this).index()).search($(`#input${$(this).index()}-detalle`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            } else {
            }
        });

        let tblsubCentroDetalle = $('#tblsubCentroDetalle').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableSubcentroCostoDetalle",
                "data": {
                    "centroCostoId": '<?php echo $_POST["centroCostoId"]; ?>'
                    
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                {"width": "20%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1,2] },
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:", 
                                'Clasificación agregada con éxito.', 
                                'success', 
                                function() {
                                    $('#tblsubCentroDetalle').DataTable().ajax.reload(null, false);
                                    $(`#<?php echo $tblClasificacion; ?>`).DataTable().ajax.reload(null, false);
                                    $('#frmModal').trigger("reset");
                                    $('#subCentroCostoId').val([]).trigger('change');
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
    });
</script>