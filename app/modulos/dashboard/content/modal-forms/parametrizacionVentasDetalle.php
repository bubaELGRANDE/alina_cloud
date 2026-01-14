<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    // arrayFormData = dashParamId ^ tipoParametrizacion
    $arrayFormData = explode("^", $_POST['arrayFormData']);
    $dashParamId = $arrayFormData[0];
    $tipoParametrizacion = ($arrayFormData[1] == "udn" ? "Unidad de negocio" : ucfirst($arrayFormData[1]));
    $tblParam = "tblParam" . ($arrayFormData[1] == "udn" ? strtoupper($arrayFormData[1]) : ucfirst($arrayFormData[1]));

    $sqlParametrizacion = ""; $selectPlaceholder = "";
    switch ($tipoParametrizacion) {
        case 'Marca':
            $sqlParametrizacion = "
                SELECT 
                    DISTINCT(pc.lineaProducto) AS lineaProducto,
                    CONCAT('(', pc.lineaProducto, ') ', li.linea) AS valorParametrizacion
                FROM conta_comision_pagar_calculo pc
                JOIN temp_cat_lineas li ON li.abreviatura = pc.lineaProducto
                WHERE pc.flgDelete = ?
                ORDER BY pc.lineaProducto
            ";
            $selectPlaceholder = "Marca(s)";
        break;
        
        case 'Unidad de negocio':
            $sqlParametrizacion = "
                SELECT 
                    DISTINCT(nombreEmpleado) AS valorParametrizacion
                FROM conta_comision_pagar_calculo
                WHERE flgDelete = ?
                ORDER BY nombreEmpleado
            ";
            $selectPlaceholder = "Empleado(s)";
        break;

        default:
            // Sucursal
            $sqlParametrizacion = "
                SELECT 
                    DISTINCT(sucursalFactura) AS valorParametrizacion
                FROM conta_comision_pagar_calculo
                WHERE flgDelete = ?
                ORDER BY sucursalFactura
            ";
            $selectPlaceholder = "Sucursal(es)";
        break;
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operationDetalle" name="operation" value="parametrizacion-ventas-detalle">
<input type="hidden" id="dashParamId" name="dashParamId" value="<?php echo $dashParamId; ?>">
<input type="hidden" id="tipoParametrizacion" name="tipoParametrizacion" value="<?php echo $tipoParametrizacion; ?>">

<div id="divBtnForm" class="row">
    <div class="col-4 offset-8">
        <button type="button" class="btn btn-primary btn-block" onclick="showHideForm(1);">
            <i class="fas fa-plus-circle"></i>
            Parametrización
        </button>
    </div>
</div>
<div id="divFormModal">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="form-select-control mb-4">
                <select class="form-select" id="valorParametrizacion" name="valorParametrizacion[]" style="width:100%;" multiple="multiple" required>
                    <option></option>
                    <?php 
                        $dataParamDetalle = $cloud->rows($sqlParametrizacion, [0]);
                        foreach ($dataParamDetalle as $dataParamDetalle) {
                            echo '<option value="'.$dataParamDetalle->valorParametrizacion.'">'.$dataParamDetalle->valorParametrizacion.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div> 
    </div>
    <div class="row">
        <div class="col-3 offset-6">
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
        <div class="col-3">
            <button type="button" class="btn btn-secondary btn-block" onclick="showHideForm(0);">
                <i class="fas fa-times-circle"></i> Cancelar
            </button>
        </div>
    </div>
</div>
<div class="table-responsive">
    <table id="tblParamDetalle" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-detalle">
                <th>#</th>
                <th>Parametrización</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function showHideForm(flg) {
        if(flg == 0) { // hide
            $("#divBtnForm").show();
            $("#divFormModal").hide();
            $('#frmModal').trigger("reset");
        } else { // show
            $("#divBtnForm").hide();
            $("#divFormModal").show();
        }
    }

    $(document).ready(function() {
        $("#divFormModal").hide();

        $("#valorParametrizacion").select2({
            dropdownParent: $('#modal-container'),
            placeholder: '<?php echo $selectPlaceholder; ?>'
        });

        $('#tblParamDetalle thead tr#filterboxrow-detalle th').each(function(index) {
            if(index==1 || index==2) {
                var title = $('#tblParamDetalle thead tr#filterboxrow-detalle th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-detalle" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-detalle">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblParamDetalle.column($(this).index()).search($(`#input${$(this).index()}-detalle`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            } else {
            }
        });

        let tblParamDetalle = $('#tblParamDetalle').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableParametrizacionVentasDetalle",
                "data": {
                    "id": '<?php echo $dashParamId; ?>',
                    "tipoParametrizacion": '<?php echo $tipoParametrizacion; ?>'
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
                                'Parametrización agregada con éxito.', 
                                'success', 
                                function() {
                                    $('#tblParamDetalle').DataTable().ajax.reload(null, false);
                                    $(`#<?php echo $tblParam; ?>`).DataTable().ajax.reload(null, false);
                                    $('#frmModal').trigger("reset");
                                    $('#valorParametrizacion').val([]).trigger('change');
                                    showHideForm(0); // para que se oculte
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