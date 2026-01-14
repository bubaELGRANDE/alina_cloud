<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

   
    $dataIvaRetenido = $cloud->row("
    SELECT
        parametrizacionId,
        tipoParametrizacion,
        descripcionParametrizacion,
        parametro
    FROM conf_parametrizacion
    WHERE parametrizacionId = ? 
",[2]);
$ivaRetenido = $dataIvaRetenido->parametro * 0.01;


if ($_POST['typeOperation'] == "update") {
    $facturaDetalleId = $_POST['facturaDetalleId'];
    $facturaRelacionadaId = $_POST['facturaRelacionadaId'];

        $dataComprobanteDetalle = $cloud->row("
        SELECT
        pd.facturaDetalleId AS facturaDetalleId, 
        pd.facturaId AS facturaId, 
        pd.productoId AS productoId, 
        pd.codProductoFactura AS codProductoFactura,
        pd.nombreProductoFactura AS nombreProductoFactura,
        pd.costoPromedio AS costoPromedio, 
        pd.precioUnitario AS precioUnitario, 
        pd.precioUnitarioIVA AS precioUnitarioIVA, 
        pd.precioVenta AS precioVenta, 
        pd.cantidadProducto AS cantidadProducto, 
        pd.ivaUnitario AS ivaUnitario, 
        pd.ivaTotal AS ivaTotal, 
        pd.porcentajeDescuento AS porcentajeDescuento, 
        pd.descuentoUnitario AS descuentoUnitario, 
        pd.descuentoTotal AS descuentoTotal, 
        pd.subTotalDetalle AS subTotalDetalle, 
        pd.subTotalDetalleIVA AS subTotalDetalleIVA, 
        pd.totalDetalle AS totalDetalle, 
        pd.totalDetalleIVA AS totalDetalleIVA, 
        pd.ivaRetenidoDetalle AS ivaRetenidoDetalle, 
        pd.ivaPercibidoDetalle AS ivaPercibidoDetalle,
        fr.tipoDTEId AS tipoDTEId,
        fr.tipoGeneracionDocId AS tipoGeneracionDocId,
        fr.numeroDocumentoRelacionada AS numeroDocumentoRelacionada,
        fr.fechaEmisionRelacionada AS fechaEmisionRelacionada,
        fr.facturaRelacionadaId As facturaRelacionadaId
    FROM fel_factura_detalle pd
    JOIN fel_factura ped ON ped.facturaId = pd.facturaId
    JOIN inv_productos p ON p.productoId = pd.productoId
    JOIN fel_factura_relacionada fr ON fr.facturaRelacionadaId = pd.facturaRelacionadaId
    WHERE pd.facturaDetalleId = ? AND pd.flgDelete = ?
    ", [$_POST['facturaDetalleId'], 0]);

} else {
    // Fue insert 
    $facturaDetalleId = 0;
}
?>
<input type="hidden" id="MdtypeOperation" name="typeOperation" value="insert">
<input type="hidden" id="Mdoperation" name="operation" value="agregar-documento-retencion">
<input type="hidden" id="MdFacturaId" name="facturaId" value="<?php echo $_POST['facturaId'];?>">
<input type="hidden" id="MdFacturaDetalleId" name="facturaDetalleId" value="<?php echo $facturaDetalleId; ?>">
<input type="hidden" id="MdfacturaRelacionadaId" name="facturaRelacionadaId" value="<?php echo $facturaRelacionadaId; ?>">
<div class="row">
    <div class="col-md-4" >
        <div class="form-select-control mb-4">
            <select id="MdtipoDTEId" name="tipoDTEId" style="width:100%;" class="form-control" required>
                <option></option>
                <?php
                $dataTipoDTE = $cloud->rows("
                    SELECT
                        tipoDTEId,
                        codigoMH,
                        tipoDTE
                    FROM mh_002_tipo_dte
                    WHERE (tipoDTEId = ? OR tipoDTEId = ?) AND flgDelete = ?
                ",[1, 2, 0]);

                foreach ($dataTipoDTE as $dataTipoDTE) {
                    echo "<option value='$dataTipoDTE->tipoDTEId'>($dataTipoDTE->codigoMH) $dataTipoDTE->tipoDTE</option>";
                }
                ?>
            </select>  
        </div>
    </div>
    <div class="col-md-4" >
        <div class="form-select-control mb-4">
            <select id="MdtipoGeneracionDocId" name="tipoGeneracionDocId" style="width:100%;" class="form-control" required>
                <option></option>
                <?php
                $dataTipoDTE = $cloud->rows("
                    SELECT
                        tipoGeneracionDocId,
                        codigoMH,
                        tipoGeneracionDoc
                    FROM mh_007_tipo_generacion_documento
                    WHERE tipoGeneracionDocId = ? OR tipoGeneracionDocId = ? AND flgDelete = ?
                ",[1, 2, 0]);

                foreach ($dataTipoDTE as $dataTipoDTE) {
                    echo "<option value='$dataTipoDTE->tipoGeneracionDocId'>($dataTipoDTE->codigoMH) $dataTipoDTE->tipoGeneracionDoc</option>";
                }
                ?>
            </select>  
        </div>
    </div>
    <div class="col-md-4">
            <div class="form-outline">
                <input type="date" id="fechaEmisionRelacionada" class="form-control" name="fechaEmisionRelacionada" required  />
                <label class="form-label" for="fechaEmisionRelacionada">Fecha de emisión</label>
            </div>
        </div>

</div>
<div class="row">
    <div class="form-outline mb-3">
        <div id="inputContainer" class="form-outline mb-4">
            <i class="fa fa-tag trailing"></i>
            <input type="text" id="numDoc" name="numDoc" class="form-control maskDTE" required>
            <label class="form-label" id="inputLabel" for="numDoc">Número de documento</label>
        </div>
    </div>
</div>
<div class="row" >
    <div class="col-md-8">
        <div class="form-outline mb-3">
            <i class="fas fa-edit trailing"></i>
            <textarea type="text" id="descripcion" class="form-control" name="descripcion" rows= 3 required></textarea>
            <label class="form-label" for="descripcion">Descripción</label>
        </div>  
    </div>
    <div class="col-md-4">
            <div class="form-outline mb-3">
                <i class="fas fa-dollar-sign trailing"></i>
                <input type="number" id="valorNeto" class="form-control" name="valorNeto" min="0.00" step="0.01" required  />
                <label class="form-label" for="valorNeto">Valor neto</label>
            </div>
            <div class="form-outline">
                <i class="fas fa-dollar-sign trailing"></i>
                <input type="number" id="ivaRetenido" class="form-control" name="ivaRetenido" value="0.00" readonly  />
                <label class="form-label" for="ivaRetenido">Iva retenido</label>
            </div>
        </div>
</div> 
<script>
    $(document).ready(function() {
        $("#MdtipoDTEId").select2({
            placeholder: "Tipo DTE",
            dropdownParent: $('#modal-container')
        });
        $("#MdtipoGeneracionDocId").select2({
            placeholder: "Tipo de generación",
            dropdownParent: $('#modal-container')
        });
        // Evento change para el select
        $("#MdtipoGeneracionDocId").change(function() {
            var selectedValue = $(this).val();
            if (selectedValue == 2) {
                // Si es 2, cambia el label a "Código de generación"
                $("#inputLabel").text("Código de generación");
                Maska.create('.maskDTE',{
                    mask: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'
                    //F638F291-72D3-4B67-8CC9-252731DCC7A3
                });
                $("#numDoc").attr("minlength", 36);
            } else  {
                // Si es 1, cambia el label a "Número de documento"
                $("#inputLabel").text("Número de documento");
                var mask = Maska.create('.maskDTE');
                mask.destroy();
                $("#numDoc").removeAttr("minlength");;
            }
        });

        $("#numDoc").keyup(function(e) {
            if($("#MdtipoGeneracionDocId").val() == 2) {
                $(this).val($(this).val().toUpperCase());
            } else {
                // No pasa nada
            }
        });

        $("#valorNeto").keyup(function(e) {
            $("#ivaRetenido").val(parseFloat($(this).val() * <?php echo $ivaRetenido; ?>).toFixed(2));            
        });
        $("#frmModal").validate({
            messages: {
                numDoc: {
                    minlength: "Debe ingresar los 36 carácteres del código de generación del DTE"
                }
            },
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if (data == "success") {
                            mensaje(
                                "Operación completada:",
                                `Documentos ${$("#MdtypeOperation").val() == 'insert' ? 'agregado' : 'actualizado'} en el comprobante con éxito.`,
                                "success"
                            );
                            $(`#tblImportacionDetalle`).DataTable().ajax.reload(null, false);
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
            if ($_POST['typeOperation'] == "update") {
        ?>
                $("#MdtypeOperation").val("update");
                $("#MdtipoDTEId").val('<?php echo $dataComprobanteDetalle->tipoDTEId; ?>').trigger("change"); 
                $("#MdtipoGeneracionDocId").val('<?php echo $dataComprobanteDetalle->tipoGeneracionDocId; ?>').trigger("change"); 
                $("#fechaEmisionRelacionada").val('<?php echo $dataComprobanteDetalle->fechaEmisionRelacionada; ?>').trigger("change");
                $("#numDoc").val('<?php echo $dataComprobanteDetalle->numeroDocumentoRelacionada; ?>').trigger("change"); 
                $("#descripcion").val('<?php echo $dataComprobanteDetalle->nombreProductoFactura; ?>').trigger("change"); 
                $("#valorNeto").val('<?php echo number_format($dataComprobanteDetalle->precioUnitario, 2, '.', ''); ?>').trigger("keyup"); 
        <?php
            } else {
                // Fue insert, ya se especificó el título de la modal
            }
        ?>
    });

</script>
