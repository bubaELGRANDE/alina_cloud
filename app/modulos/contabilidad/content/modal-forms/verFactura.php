<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    // comisionPagarCalculoId ^ identificador ^ n (o viene el valor R que es desde Revisión)
    $arrayFormData = explode("^", $_POST['arrayFormData']);

    $dataInfoGeneralFactura = $cloud->row("
        SELECT
            nombreEmpleado,
            nombreCliente,
            tipoCliente,
            tipoFactura,
            correlativoFactura,
            fechaFactura,
            sucursalFactura,
            totalFactura,
            fechaAbono,
            totalAbono,
            totalAbonoCalculo,
            comisionPagarPeriodoId,
            tasaComisionAbono,
            comisionAbonoPagar,
            ivaPercibido,
            ivaRetenido,
            flgComisionEditar,
            comisionPagarEditar,
            flgRepetidoDiferente
        FROM conta_comision_pagar_calculo
        WHERE comisionPagarCalculoId = ? AND flgIdentificador = ?
    ", [$arrayFormData[0], $arrayFormData[1]]);
?>
<div class="row mb-4">
    <div class="col-6">
        <b>Cliente: </b> <?php echo $dataInfoGeneralFactura->nombreCliente . " (" . $dataInfoGeneralFactura->tipoCliente . ")"; ?><br>
        <b>Sucursal: </b> <?php echo $dataInfoGeneralFactura->sucursalFactura; ?>
    </div>
    <div class="col-6">
        <b>N° Factura: </b> <?php echo $dataInfoGeneralFactura->correlativoFactura . " (" . $dataInfoGeneralFactura->tipoFactura . ")"; ?> <br>
        <b>Fecha de la factura: </b> <?php echo date("d/m/Y", strtotime($dataInfoGeneralFactura->fechaFactura)); ?>
    </div>
</div>
<hr>
<div class="table-responsive">
    <table id="tblProductosFactura" class="table table-hover">
        <thead>
            <tr id="filterboxrow-detalle-productos">
                <th>#</th>
                <th>Productos</th>
                <th>Venta</th>
                <th>Comisión</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                // Todos esos filtros es porque es info de magic y son los únicos campos que hacen único estos registros
                // Son bastantes, pero mejor asegurar el filtrado ya que la información de magic es especial xd
                $dataProductosFactura = $cloud->rows("
                    SELECT 
                        codProductoFactura, 
                        nombreProducto, 
                        lineaProducto, 
                        precioUnitario, 
                        precioCosto, 
                        cantidadProducto, 
                        precioFacturado, 
                        totalVenta,
                        porcentajeDescuento,
                        paramLineaId,
                        paramRangoPorcentajeInicio,
                        paramRangoPorcentajeFin,
                        paramPorcentajePago,
                        tasaComisionAbono,
                        comisionPagar
                    FROM conta_comision_pagar_calculo
                    WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND nombreCliente = ? AND correlativoFactura = ? AND tipoFactura = ? AND fechaFactura = ? AND sucursalFactura = ? AND flgIdentificador = ? AND fechaAbono = ? AND totalAbono = ? AND flgRepetidoDiferente = ? AND flgDelete = '0'
                    ORDER BY lineaProducto, nombreProducto
                ",  [
                        $dataInfoGeneralFactura->comisionPagarPeriodoId, 
                        $dataInfoGeneralFactura->nombreEmpleado, 
                        $dataInfoGeneralFactura->nombreCliente,
                        $dataInfoGeneralFactura->correlativoFactura,
                        $dataInfoGeneralFactura->tipoFactura,
                        $dataInfoGeneralFactura->fechaFactura,
                        $dataInfoGeneralFactura->sucursalFactura,
                        $arrayFormData[1],
                        $dataInfoGeneralFactura->fechaAbono,
                        $dataInfoGeneralFactura->totalAbono,
                        $dataInfoGeneralFactura->flgRepetidoDiferente
                    ]
                );
                $n = 0; 
                $subtotal = 0.00;
                $totalComisionFactura = 0.00;
                foreach ($dataProductosFactura as $dataProductosFactura) {
                    $comisionPagar = $dataProductosFactura->comisionPagar;

                    $n += 1;
                    echo '
                        <tr>
                            <td>'.$n.'</td>
                            <td>
                                <b>Cód. producto: </b> '.$dataProductosFactura->codProductoFactura.'<br>
                                <b>Producto: </b> '.$dataProductosFactura->nombreProducto.'<br>
                                <b>Línea: </b> '.$dataProductosFactura->lineaProducto.'
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-5">
                                        Precio unitario:
                                    </div>
                                    <div class="col-7 text-end">
                                        $ '.number_format($dataProductosFactura->precioUnitario, 2, ".", ",").'
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-5">
                                        Precio facturado:
                                    </div>
                                    <div class="col-7 text-end">
                                        $ '.number_format($dataProductosFactura->precioFacturado, 2, ".", ",").'
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-5">
                                        Cantidad:
                                    </div>
                                    <div class="col-7 text-end">
                                        '.number_format($dataProductosFactura->cantidadProducto, 0, ".", ",").'
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-5">
                                        Total:
                                    </div>
                                    <div class="col-7 text-end fw-bold">
                                        $ '.number_format($dataProductosFactura->totalVenta, 2, ".", ",").'
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-5">
                                        Descuento:
                                    </div>
                                    <div class="col-7 text-end">
                                        '.number_format($dataProductosFactura->porcentajeDescuento, 2, ".", ",").'%
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-5">
                                        Condición:
                                    </div>
                                    <div class="col-7 text-end">
                                        '.number_format($dataProductosFactura->paramRangoPorcentajeInicio, 2, ".", ",").'% hasta '.number_format($dataProductosFactura->paramRangoPorcentajeFin, 2, ".", ",").'%
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-5">
                                        % Pago:
                                    </div>
                                    <div class="col-7 text-end">
                                        '.number_format($dataProductosFactura->paramPorcentajePago, 2, ".", ",").'%
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-5">
                                        Comisión:
                                    </div>
                                    <div class="col-7 text-end fw-bold">
                                        $ '.number_format($comisionPagar, 2, ".", ",").'
                                    </div>
                                </div>
                            </td>
                        </tr>
                    ';
                    $subtotal += $dataProductosFactura->totalVenta;
                    $totalComisionFactura += $dataProductosFactura->comisionPagar;
                }
            ?>
        </tbody>
        <tfoot class="text-end fw-bold">
            <tr>
                <td></td>
                <td>Subtotal</td>
                <td>$ <?php echo number_format($subtotal, 2, ".", ","); ?></td>
                <td></td>
            </tr>
            <?php 
                if($dataInfoGeneralFactura->tipoFactura == "CRÉDITO FISCAL") {
                    $ivaFactura = ($subtotal * 1.13) - $subtotal;
                    echo '
                        <tr>
                            <td></td>
                            <td>IVA 13%</td>
                            <td>$ '.number_format($ivaFactura, 2, ".", ",").'</td>
                            <td></td>
                        </tr>                    
                    ';
                } else {
                    // No mostrar IVA
                }
                if($dataInfoGeneralFactura->ivaPercibido > 0) {
                    echo '
                        <tr>
                            <td></td>
                            <td>IVA Percibido (+)</td>
                            <td>$ '.number_format($dataInfoGeneralFactura->ivaPercibido, 2, ".", ",").'</td>
                            <td></td>
                        </tr>                    
                    ';
                } else {
                    // No tenia IVA percibido
                }
                if($dataInfoGeneralFactura->ivaRetenido > 0) {
                    echo '
                        <tr>
                            <td></td>
                            <td>IVA Retenido (-)</td>
                            <td>$ '.number_format($dataInfoGeneralFactura->ivaRetenido, 2, ".", ",").'</td>
                            <td></td>
                        </tr>                    
                    ';
                } else {
                    // No tenia IVA retenido
                }
            ?>
            <tr>
                <td></td>
                <td>Total facturado</td>
                <td>$ <?php echo number_format($dataInfoGeneralFactura->totalFactura, 2, ".", ","); ?></td>
                <td></td>
            </tr>
            <?php 
                if($arrayFormData[1] == "F" && $dataInfoGeneralFactura->flgComisionEditar == "1") {
                    echo '
                        <tr>
                            <td></td>
                            <td>Total comisión: Calculada</td>
                            <td></td>
                            <td>$ '.number_format($totalComisionFactura, 2, ".", ",").'</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Total comisión: Editada</td>
                            <td></td>
                            <td>$ '.number_format($dataInfoGeneralFactura->comisionPagarEditar, 2, ".", ",").'</td>
                        </tr>
                    ';
                } else {
                    echo '
                        <tr>
                            <td></td>
                            <td>Total comisión: Calculada</td>
                            <td></td>
                            <td>$ '.number_format($totalComisionFactura, 2, ".", ",").'</td>
                        </tr>
                    ';                  
                }

                if($arrayFormData[1] == "A") {
                    if($dataInfoGeneralFactura->flgComisionEditar == "1") {
                        $divComisionPorAbono = '
                            <tr>
                                <td></td>
                                <td>Comisión por abono: Calculada</td>
                                <td></td>
                                <td>$ '.number_format($dataInfoGeneralFactura->comisionAbonoPagar, 2, ".", ",").'</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Comisión por abono: Editada</td>
                                <td></td>
                                <td>$ '.number_format($dataInfoGeneralFactura->comisionPagarEditar, 2, ".", ",").'</td>
                            </tr>
                        ';
                    } else {
                        $divComisionPorAbono = '
                            <tr>
                                <td></td>
                                <td>Comisión por abono</td>
                                <td></td>
                                <td>$ '.number_format($dataInfoGeneralFactura->comisionAbonoPagar, 2, ".", ",").'</td>
                            </tr>
                        ';
                    }
                    if($dataInfoGeneralFactura->tipoFactura == "FACTURA DE EXPORTACIÓN" || $dataInfoGeneralFactura->tipoFactura == "FACTURA EXENTA") {
                        echo '
                            <tr>
                                <td></td>
                                <td>Total abono</td>
                                <td>
                                    $ '.number_format($dataInfoGeneralFactura->totalAbonoCalculo, 2, ".", ",").'
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Tasa por abono</td>
                                <td>'.number_format($dataInfoGeneralFactura->tasaComisionAbono, 2, ".", ",").'%</td>
                                <td></td>
                            </tr>
                            '.$divComisionPorAbono.'
                        ';
                    } else {
                        echo '
                            <tr>
                                <td></td>
                                <td>Total abono</td>
                                <td>
                                    IVA: $ '.number_format($dataInfoGeneralFactura->totalAbono, 2, ".", ",").'<br>
                                    SIN IVA: $ '.number_format($dataInfoGeneralFactura->totalAbonoCalculo, 2, ".", ",").'
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Tasa por abono</td>
                                <td>'.number_format($dataInfoGeneralFactura->tasaComisionAbono, 2, ".", ",").'%</td>
                                <td></td>
                            </tr>
                            '.$divComisionPorAbono.'
                        ';
                    }
                } else {
                    // Fue factura
                }
            ?>
        </tfoot>
    </table>
</div>
<hr>
<?php 
    if($arrayFormData[1] == "F") {
        // Editar la comisión general por producto
        $editarComision = ($dataInfoGeneralFactura->flgComisionEditar == "1") ? $dataInfoGeneralFactura->comisionPagarEditar : $totalComisionFactura;
    } else {    
        // Editar la comisión del abono
        $editarComision = ($dataInfoGeneralFactura->flgComisionEditar == "1") ? $dataInfoGeneralFactura->comisionPagarEditar : $dataInfoGeneralFactura->comisionAbonoPagar;
    }
    if($dataInfoGeneralFactura->tipoFactura == "FACTURA DE CONSUMIDOR FINAL") {
        $subtotal /= 1.13;
    } else {
        // subtotal normal
    }
?>
<div id="divButtonEditarComision" class="text-end">
    <button type="button" class="btn btn-primary ttip" onclick="showHideEditarComision(1);">
        <i class="fas fa-edit"></i> Editar Comisión
        <span class="ttiptext">$ <?php echo number_format($editarComision, 2, ".", ","); ?></span>
    </button>
</div>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="comision-pagar">
<input type="hidden" id="comisionPagarCloud" name="comisionPagarCloud" value="<?php echo $editarComision; ?>">
<div id="divFormEditarComision">
    <div class="row">
        <div class="col-md-4">
            <div class="form-outline mb-4">
                <i class="fas fa-dollar-sign trailing"></i>
                <input type="number" id="comisionPagarNueva" class="form-control" name="comisionPagarNueva" min="0" step="0.01" value="<?php echo $editarComision; ?>" required>
                <label class="form-label" for="comisionPagarNueva">Comisión a pagar</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline mb-4">
                <i class="fas fa-edit trailing"></i>
                <textarea type="text" id="motivoEditar" class="form-control" name="motivoEditar" required>Cambio de porcentaje autorizado por gerencia</textarea>
                <label class="form-label" for="motivoEditar">Justificación/Motivo</label>
            </div>            
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar
            </button>
            <button type="button" class="btn btn-secondary" onclick="showHideEditarComision(0);">
                <i class="fas fa-times-circle"></i> Cancelar
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <input type="hidden" id="ventaNetaNoFormat" name="ventaNetaNoFormat" value="<?php echo $subtotal; ?>">
            <div class="form-outline mb-4">
                <i class="fas fa-dollar-sign trailing"></i>
                <input type="text" id="ventaNeta" class="form-control" name="ventaNeta" value="<?php echo number_format($subtotal, 2, '.', ','); ?>" readonly>
                <label class="form-label" for="ventaNeta">Venta neta</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline mb-4">
                <i class="fas fa-dollar-sign trailing"></i>
                <input type="number" id="porcentajeExtraordinario" class="form-control" name="porcentajeExtraordinario" min="0" step="0.01" onkeyup="calcularPorcentajeExtraordinario();">
                <label class="form-label" for="porcentajeExtraordinario">Porcentaje Extraordinario</label>
            </div>
        </div>
        <div class="col-md-4">
            <!--
            <button type="button" class="btn btn-primary" onclick="calcularPorcentajeExtraordinario();">
                <i class="fas fa-check-circle"></i> Trasladar
            </button>
            -->
        </div>
    </div>
</div>
<script>
    function calcularPorcentajeExtraordinario() {
        let nuevaComision = parseFloat($("#ventaNetaNoFormat").val()) * parseFloat(parseFloat($("#porcentajeExtraordinario").val()) / 100);
        $("#comisionPagarNueva").val(parseFloat(nuevaComision).toFixed(2));
    }

    function showHideEditarComision(tipo) {
        if(tipo == 1) { // Mostrar
            $('#divFormEditarComision').show();
            $('#divButtonEditarComision').hide();
            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                new mdb.Input(formOutline).update();
            });
        } else {
            $('#divFormEditarComision').hide();
            $('#divButtonEditarComision').show();            
        }
    }

    $(document).ready(function() {
        $("#modalTitle").html('Ver Factura - Vendedor: <?php echo $dataInfoGeneralFactura->nombreEmpleado; ?>');
        showHideEditarComision(0);

        $("#frmModal").validate({
            submitHandler: function(form) {
                mensaje_confirmacion(
                    `¿Está seguro que editará la comisión a pagar del vendedor?`, 
                    `Esta modificación se aplicará al cálculo de esta factura y al total general de comisiones.`, 
                    `warning`, 
                    function(param) {
                        button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                        asyncDoDataReturn(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                            $("#frmModal").serialize(),
                            function(data) {
                                button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                                if(data == "success") {
                                    if('<?php echo $arrayFormData[2]; ?>' == 'R') { // Desde revisión
                                        mensaje(
                                            "Operación completada:",
                                            'Se ha editado la comisión a pagar del vendedor en esta factura.',
                                            "success"
                                        );
                                        // Actualizar las datatable de page-comisiones-revision
                                        //$("#tblComisionesCero").DataTable().ajax.reload(null, false);
                                        //$("tblComisionesFacturasDescuento").DataTable().ajax.reload(null, false);
                                        //$("#tblComisionesEditadas").DataTable().ajax.reload(null, false);
                                        $('#modal-container').modal("hide");
                                    } else {
                                        mensaje_do_aceptar(
                                            `Operación completada:`, 
                                            'Se ha editado la comisión a pagar del vendedor en esta factura.', 
                                            `success`, 
                                            function() {
                                                // Cerrar modal actual, se volverá a abrir automáticamente con la function modalVerFactura
                                                $('#modal-container').modal("hide");
                                                // Estas son las funciones mostrarComisiones, cargarDetalleVendedor
                                                // pero no se abría el detalle del último vendedor visto, por lo que
                                                // mejor las puse en quemado su async especifico
                                                asyncDoDataReturn(
                                                    "<?php echo $_SESSION['currentRoute']; ?>content/divs/divMostrarComisiones", 
                                                    {
                                                        mes: $("#periodoMes").val(),
                                                        anio: $("#periodoAnio").val()
                                                    },
                                                    function(data) {
                                                        //$("#divMostrarComisiones").html(data);
                                                        //$(`#tdChevron<?php //echo $arrayFormData[2]; ?>`).html('<i class="fas fa-chevron-up"></i>');
                                                        // Cargar el detalle del vendedor
                                                        /*
                                                        asyncDoDataReturn(
                                                            "<?php echo $_SESSION['currentRoute']; ?>content/divs/divMostrarComisionesDetalle", 
                                                            {
                                                                comisionPagarPeriodoId: '<?php echo $dataInfoGeneralFactura->comisionPagarPeriodoId; ?>',
                                                                vendedor: '<?php echo $dataInfoGeneralFactura->nombreEmpleado; ?>',
                                                                n: <?php echo $arrayFormData[2]; ?>
                                                            },
                                                            function(data) {
                                                                $(`#divCollapseLoad<?php echo $arrayFormData[2]; ?>`).html(data);
                                                                $(`#trCollapse<?php echo $arrayFormData[2]; ?>`).show();
                                                                modalVerFactura(<?php echo $arrayFormData[0]; ?>, '<?php echo $arrayFormData[1]; ?>', <?php echo $arrayFormData[2]; ?>);
                                                            }
                                                        );
                                                        */
                                                        modalVerFactura(<?php echo $arrayFormData[0]; ?>, '<?php echo $arrayFormData[1]; ?>', <?php echo $arrayFormData[2]; ?>);
                                                    }
                                                );
                                            }
                                        );
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
                    },
                    `Sí, editar comisión`,
                    `Cancelar`
                );
            }
        });

        $('#tblProductosFactura thead tr#filterboxrow-detalle-productos th').each(function(index) {
            if(index == 1 || index == 2 || index == 3) {
                var title = $('#tblProductosFactura thead tr#filterboxrow-detalle-productos th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}detalle-productos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}detalle-productos">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblProductosFactura.column($(this).index()).search($(`#input${$(this).index()}detalle-productos`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblProductosFactura = $('#tblProductosFactura').DataTable({
            "dom": 'lrtip',
            "autoWidth": false,
            "columns": [
                {"width": "10%"},
                {"width": "30%"},
                {"width": "30%"},
                {"width": "30%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>