<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
        POST:
        comisionClasificacionId
        tipoClasificacion
        tituloModal
        tblClasif
    */

    $sqlParametrizacion = ""; $selectPlaceholder = "";
    switch($_POST['tipoClasificacion']) {
        case 'Sucursal':
            $sqlParametrizacion = "
                SELECT 
                    DISTINCT(sucursalFactura) AS valorClasificacion
                FROM conta_comision_pagar_calculo
                WHERE flgDelete = ?
                ORDER BY sucursalFactura
            ";
            $selectPlaceholder = "Sucursal(es)";
            $tblClasificacion = "tblClasifSucursales";
        break;
        
        default:
            // Linea
            // Traer del catalogo, ya que hay lineas que no se han facturado pero es necesario dejarlas parametrizadas
            $sqlParametrizacion = "
                SELECT 
                    CONCAT('(', abreviatura, ') ', linea) AS valorClasificacion
                FROM temp_cat_lineas
                WHERE flgDelete = ?
                ORDER BY abreviatura
            ";
            $selectPlaceholder = "Línea(s)";
            $tblClasificacion = "tblClasifLineas";
        break;
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operationDetalle" name="operation" value="comision-clasificacion-detalle">
<input type="hidden" id="comisionClasificacionId" name="comisionClasificacionId" value="<?php echo $_POST['comisionClasificacionId']; ?>">
<input type="hidden" id="tipoClasificacion" name="tipoClasificacion" value="<?php echo $_POST['tipoClasificacion']; ?>">
<div class="row justify-content-center">
    <div class="col-9">
        <div class="form-select-control mb-4">
            <select class="form-select" id="valorClasificacion" name="valorClasificacion[]" style="width:100%;" multiple="multiple" required>
                <option></option>
                <?php 
                    $dataParamDetalle = $cloud->rows($sqlParametrizacion, [0]);
                    foreach ($dataParamDetalle as $dataParamDetalle) {
                        echo '<option value="'.$dataParamDetalle->valorClasificacion.'">'.$dataParamDetalle->valorClasificacion.'</option>';
                    }
                ?>
            </select>
        </div>
    </div> 
    <div class="col-3">
        <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>
</div>

<div class="table-responsive">
    <table id="tblClasifDetalle" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-detalle">
                <th>#</th>
                <th>Clasificación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $("#valorClasificacion").select2({
            dropdownParent: $('#modal-container'),
            placeholder: '<?php echo $selectPlaceholder; ?>'
        });

        $('#tblClasifDetalle thead tr#filterboxrow-detalle th').each(function(index) {
            if(index==1) {
                var title = $('#tblClasifDetalle thead tr#filterboxrow-detalle th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-detalle" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-detalle">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblClasifDetalle.column($(this).index()).search($(`#input${$(this).index()}-detalle`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            } else {
            }
        });

        let tblClasifDetalle = $('#tblClasifDetalle').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableComisionClasificacionDetalle",
                "data": {
                    "comisionClasificacionId": '<?php echo $_POST["comisionClasificacionId"]; ?>',
                    "tipoClasificacion": '<?php echo $_POST["tipoClasificacion"]; ?>'
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
                                    $('#tblClasifDetalle').DataTable().ajax.reload(null, false);
                                    $(`#<?php echo $tblClasificacion; ?>`).DataTable().ajax.reload(null, false);
                                    $('#frmModal').trigger("reset");
                                    $('#valorClasificacion').val([]).trigger('change');
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