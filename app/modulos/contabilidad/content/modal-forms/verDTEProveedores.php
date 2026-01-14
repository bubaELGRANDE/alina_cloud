<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

    $yearBD = $_POST['yearBD'];

    $dataDTEGeneral = $cloud->row("
            SELECT
            fe.sucursalId AS sucursalId,
            s.sucursal AS sucursal,
            f.identificacionAmbiente AS identificacionAmbiente,
            f.tipoDTEId AS tipoDTEId,
            cat002.codigoMH AS codDTEMH,
            cat002.tipoDTE AS tipoDTE,
            cat002.versionMH AS versionDTEMH,
            cat003.codigoMH AS codModeloMH,
            cat003.tipoModeloFacturacion AS tipoModeloFacturacion,
            f.fechaEmision AS fechaEmision,
            DATE_FORMAT(f.fechaEmision, '%d/%m/%Y') AS fechaEmisionFormat,
            f.horaEmision AS horaEmision,
            f.tipoMoneda AS tipoMoneda,
            mon.nombreMoneda AS nombreMoneda,
            mon.simboloMoneda AS simboloMoneda,
            fc.proveedorId AS proveedorId,
            CASE
                WHEN fc.nombreProveedor = '' OR fc.nombreProveedor = NULL THEN fc.nombreComercial
                ELSE fc.nombreProveedor
            END AS nombreProveedor,
            fc.nrcProveedor AS nrcProveedor,
            fc.tipoDocumento AS tipoDocumento,
            fc.numDocumento AS numDocumentoProveedor,
            f.proveedorUbicacionId AS proveedorUbicacionId,
            fcu.nombreProveedorUbicacion AS nombreProveedorUbicacion,
            cpdcliente.codigoMH AS codigoDepartamentoCliente,
            cpdcliente.departamentoPais AS departamentoCliente,
            cpmcliente.codigoMH AS codigoMunicipioCliente,
            cpmcliente.municipioPais AS municipioCliente,
            fcu.direccionProveedorUbicacion AS direccionProveedorUbicacion,
            f.vendedorId AS vendedorId,
            fv.tipoVendedor AS tipoVendedor,
            fv.mgCodVendedor AS mgCodVendedor,
            CASE
                WHEN fv.tipoVendedor = 'Empleado' THEN (
                    SELECT nombreCompleto FROM view_expedientes vexp
                    WHERE vexp.personaId = fv.personaId
                    LIMIT 1
                )
                ELSE fv.mgNombreVendedor
            END AS nombreVendedor,
            fv.mgNombreVendedor AS mgNombreVendedor,
            cat016.codigoMH AS codCondicionFacturaMH,
            cat016.condicionFactura AS condicionFactura,
            cat018.codigoMH AS codPlazoPagoMH,
            cat018.plazoPago AS plazoPago,
            f.periodoPlazo AS periodoPlazo,
            (
                SELECT CONCAT('(', cat017.codigoMH, ') ', cat017.formaPago) FROM fel_factura_pago$yearBD ffp 
                JOIN mh_017_forma_pago cat017 ON cat017.formaPagoId = ffp.formaPagoId
                WHERE ffp.facturaId = f.facturaId
                LIMIT 1
            ) AS formaPagoMH,
            (
                SELECT montoPago FROM fel_factura_pago$yearBD ffp2 
                WHERE ffp2.facturaId = f.facturaId
                LIMIT 1
            ) AS montoPagoMagic,
            f.porcentajeIVA AS porcentajeIVA,
            ffr.porcentajeIVARetenido AS porcentajeIVARetenido,
            ffr.ivaRetenido AS ivaRetenido,
            ffr.porcentajeIVAPercibido AS porcentajeIVAPercibido,
            ffr.ivaPercibido AS ivaPercibido,
            ffr.porcentajeRenta AS porcentajeRenta,
            ffr.rentaRetenido AS rentaRetenido,
            f.sujetoExcluidoId AS sujetoExcluidoId,
            cat019cliente.codigoMh AS codigoActividadClienteMH,
            cat019cliente.actividadEconomica AS actividadEconomicaCliente,
            f.tipoRemisionMHId AS tipoRemisionMHId
        FROM fel_factura$yearBD f
        JOIN fel_factura_emisor$yearBD fe ON fe.facturaId = f.facturaId
        JOIN cat_sucursales s ON s.sucursalId = fe.sucursalId
        JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = f.tipoDTEId
        JOIN mh_003_tipo_modelo cat003 ON cat003.tipoModeloMHId = f.tipoModeloMHId
        JOIN cat_monedas mon ON mon.abreviaturaMoneda = f.tipoMoneda
        LEFT JOIN comp_proveedores_ubicaciones fcu ON fcu.proveedorUbicacionId = f.proveedorUbicacionId
        LEFT JOIN comp_proveedores fc ON fc.proveedorId = fcu.proveedorId
        LEFT JOIN fel_vendedores fv ON fv.vendedorId = f.vendedorId
        JOIN mh_016_condicion_factura cat016 ON cat016.condicionFacturaId = f.condicionFacturaId
        JOIN mh_018_plazo_pago cat018 ON cat018.plazoPagoId = f.plazoPagoId
        JOIN fel_factura_retenciones$yearBD ffr ON ffr.facturaId = f.facturaId
        LEFT JOIN cat_paises_municipios cpmcliente ON cpmcliente.paisMunicipioId = fcu.paisMunicipioId
        LEFT JOIN cat_paises_departamentos cpdcliente ON cpdcliente.paisDepartamentoId = cpmcliente.paisDepartamentoId
        LEFT JOIN mh_019_actividad_economica cat019cliente ON cat019cliente.actividadEconomicaId = fc.actividadEconomicaId
        WHERE f.facturaId = ? AND f.flgDelete = ?
    ", [$_POST['facturaId'], 0]);
    // La columna formaPagoMH es un SELECT porque las formas de pago pueden ser múltiples, aunque ahorita por venir información de Magic es una sola forma de pago
    // Pero lo dejo así, a manera de recordatorio

    switch($dataDTEGeneral->identificacionAmbiente) {
        case '01':
            $ambienteDTE = "Modo producción";
        break;
        
        default:
            $ambienteDTE =  "Modo prueba";
        break;
    }

    $porcentajeIVA = $dataDTEGeneral->porcentajeIVA;
    $porcentajeIVARetenido = $dataDTEGeneral->porcentajeIVARetenido;
    $porcentajeIVAPercibido = $dataDTEGeneral->porcentajeIVAPercibido;
    $porcentajeRentaRetencion = $dataDTEGeneral->porcentajeRenta;
?>
<h4>Encabezado del DTE</h4>
<h5>Información FEL</h5>
<div class="row">
    <div class="col-4 mb-4">
        <b><i class="fas fa-laptop-code"></i> Ambiente: </b> <?php echo "($dataDTEGeneral->identificacionAmbiente) $ambienteDTE"; ?><br>
    </div>
    <div class="col-4 mb-4">
        <b><i class="fas fa-server"></i> Modelo: </b> <?php echo "($dataDTEGeneral->codModeloMH) $dataDTEGeneral->tipoModeloFacturacion"; ?><br>
    </div>
    <div class="col-4 mb-4">
        <b><i class="fas fa-money-bill-alt"></i> Moneda: </b> <?php echo "($dataDTEGeneral->tipoMoneda) $dataDTEGeneral->nombreMoneda"; ?><br>
    </div>
</div>
<div class="row">
    <div class="col-12 mb-4">
        <h5>Información del DTE</h5>
        <b><i class="fas fa-file-invoice-dollar"></i> Tipo: </b> <?php echo "($dataDTEGeneral->codDTEMH) $dataDTEGeneral->tipoDTE versión $dataDTEGeneral->versionDTEMH"; ?><br>
        <b><i class="fas fa-calendar-day"></i> Fecha y hora: </b> <?php echo "$dataDTEGeneral->fechaEmisionFormat $dataDTEGeneral->horaEmision"; ?><br>
        <?php 
            // Sujeto excluido no lleva cliente
            if($dataDTEGeneral->tipoDTEId == 10) {
                $dataSujetoExcluido = $cloud->row("
                    SELECT
                        fse.nombreSujeto AS nombreSujeto,
                        cat022se.codigoMH AS codigoTipoDocumentoPersonaMH,
                        cat022se.tipoDocumentoCliente AS tipoDocumentoPersona,
                        fse.numDocumento AS numDocumentoPersona,
                        cpdcliente.codigoMH AS codigoDepartamentoPersona,
                        cpdcliente.departamentoPais AS departamentoPersona,
                        cpmcliente.codigoMH AS codigoMunicipioPersona,
                        cpmcliente.municipioPais AS municipioPersona,
                        fse.direccionSujeto AS direccionSujeto,
                        cat019cliente.codigoMh AS codigoActividadPersonaMH,
                        cat019cliente.actividadEconomica AS actividadEconomicaPersona
                    FROM fel_sujeto_excluido fse
                    JOIN mh_022_tipo_documento cat022se ON cat022se.tipoDocumentoClienteId = fse.tipoDocumentoMHId
                    JOIN cat_paises_municipios cpmcliente ON cpmcliente.paisMunicipioId = fse.paisMunicipioId
                    JOIN cat_paises_departamentos cpdcliente ON cpdcliente.paisDepartamentoId = cpmcliente.paisDepartamentoId
                    JOIN mh_019_actividad_economica cat019cliente ON cat019cliente.actividadEconomicaId = fse.actividadEconomicaId
                    WHERE fse.sujetoExcluidoId = ? AND fse.flgDelete = ?
                ", [$dataDTEGeneral->sujetoExcluidoId, 0]);
        ?>
                <b><i class="fas fa-database"></i> Ref. Sujeto excluido: </b> <?php echo $dataDTEGeneral->sujetoExcluidoId; ?><br>
                <b><i class="fas fa-user-tie"></i> Persona: </b> <?php echo $dataSujetoExcluido->nombreSujeto; ?><br>
                <b><i class="fas fa-list-ul"></i> Actividad económica: </b> <?php echo "($dataSujetoExcluido->codigoActividadPersonaMH) $dataSujetoExcluido->actividadEconomicaPersona"; ?>
                <div class="row">
                    <div class="col-6">
                        <b><i class="fas fa-id-card"></i> NRC: </b> -<br>
                        <b><i class="fas fa-map"></i> Departamento: </b> <?php echo "($dataSujetoExcluido->codigoDepartamentoPersona) $dataSujetoExcluido->departamentoPersona"; ?>
                    </div>
                    <div class="col-6">
                        <b><i class="fas fa-id-card"></i> <?php echo "($dataSujetoExcluido->codigoTipoDocumentoPersonaMH) $dataSujetoExcluido->tipoDocumentoPersona"; ?>: </b> <?php echo $dataSujetoExcluido->numDocumentoPersona; ?><br>
                        <b><i class="fas fa-map-marked-alt"></i> Municipio: </b> <?php echo "($dataSujetoExcluido->codigoMunicipioPersona) $dataSujetoExcluido->municipioPersona"; ?>
                    </div>
                </div>
                <b><i class="fas fa-map-marked-alt"></i> Dirección: </b> <?php echo $dataSujetoExcluido->direccionSujeto; ?><br>
        <?php 
            } else {
        ?>
                <b><i class="fas fa-database"></i> Ref. Cliente - Ubicación: </b> <?php echo "$dataDTEGeneral->proveedorId - $dataDTEGeneral->proveedorUbicacionId"; ?><br>
                <b><i class="fas fa-user-tie"></i> Cliente: </b> <?php echo "$dataDTEGeneral->nombreProveedor ($dataDTEGeneral->nombreProveedorUbicacion)"; ?><br>
                <b><i class="fas fa-list-ul"></i> Actividad económica: </b> <?php echo "($dataDTEGeneral->codigoActividadClienteMH) $dataDTEGeneral->actividadEconomicaCliente"; ?>
                <div class="row">
                    <div class="col-6">
                        <b><i class="fas fa-id-card"></i> NRC: </b> <?php echo $dataDTEGeneral->nrcProveedor; ?><br>
                        <b><i class="fas fa-map"></i> Departamento: </b> <?php echo "($dataDTEGeneral->codigoDepartamentoCliente) $dataDTEGeneral->departamentoCliente"; ?>
                    </div>
                    <div class="col-6">
                        <b><i class="fas fa-id-card"></i> <?php echo "$dataDTEGeneral->tipoDocumento"; ?>: </b> <?php echo $dataDTEGeneral->numDocumentoProveedor; ?><br>
                        <b><i class="fas fa-map-marked-alt"></i> Municipio: </b> <?php echo "($dataDTEGeneral->codigoMunicipioCliente) $dataDTEGeneral->municipioCliente"; ?>
                    </div>
                </div>
                <b><i class="fas fa-map-marked-alt"></i> Dirección: </b> <?php echo $dataDTEGeneral->direccionProveedorUbicacion; ?><br>
        <?php 
            }
        ?>
        <b><i class="fas fa-user-tie"></i> Vendedor: </b> <?php echo ($dataDTEGeneral->nombreVendedor == "" ? "N/A" : $dataDTEGeneral->nombreVendedor); ?><br>
    </div>

</div>
<hr>
<h4>Detalle del DTE</h4>
<h5>Productos del DTE</h5>
<div class="table-responsive">
    <table id="tblDTEDetalle" class="table table-hover mt-4">
        <thead>
            <tr id="filterboxrow-detalle">
                <th>#</th>
                <th>Producto</th>
                <th>Precio unitario</th>
                <th>(=) Precio unit. facturado</th>
                <th>Cantidad</th>
                <th>(-) Descuento</th>
                <th>(+) IVA <?php echo $porcentajeIVA; ?>%</th>
                <th>(=) Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $dataDTEDetalle = $cloud->rows("
                    SELECT
                        fd.facturaDetalleId AS facturaDetalleId,
                        fd.facturaId AS facturaId,
                        fd.productoId AS productoId,
                        fd.codProductoFactura AS codProductoFactura,
                        prod.codInterno AS codProductoInterno,
                        fd.nombreProductoFactura AS nombreProductoFactura, 
                        m.abreviaturaMarca AS abreviaturaMarca,
                        m.nombreMarca AS nombreMarca,
                        fd.tipoItemMHId AS tipoItemMHId,
                        cat11.codigoMH AS codigoMHItem,
                        cat11.tipoItem AS tipoItem,
                        fd.costoPromedio AS costoPromedio,
                        fd.precioUnitario AS precioUnitario,
                        fd.precioUnitarioIVA AS precioUnitarioIVA,
                        fd.precioVenta AS precioVenta,
                        fd.precioVentaIVA AS precioVentaIVA,
                        fd.cantidadProducto AS cantidadProducto,
                        udm.abreviaturaUnidadMedida AS abreviaturaUnidadMedida,
                        udm.nombreUnidadMedida AS nombreUnidadMedida,
                        fd.ivaUnitario AS ivaUnitario,
                        fd.ivaTotal AS ivaTotal,
                        fd.porcentajeDescuento AS porcentajeDescuento,
                        fd.descuentoUnitario AS descuentoUnitario,
                        fd.descuentoTotal AS descuentoTotal, 
                        fd.subTotalDetalle AS subTotalDetalle,
                        fd.subTotalDetalleIVA AS subTotalDetalleIVA,
                        fd.totalDetalle AS totalDetalle,
                        fd.totalDetalleIVA AS totalDetalleIVA, 
                        fd.facturaRelacionadaId AS facturaRelacionadaId
                    FROM fel_factura_detalle$yearBD fd
                    LEFT JOIN inv_productos prod ON prod.productoId = fd.productoId
                    LEFT JOIN cat_inventario_marcas m ON m.marcaId = prod.marcaId
                    LEFT JOIN cat_unidades_medida udm ON udm.unidadMedidaId = prod.unidadMedidaId
                    JOIN mh_011_tipo_item cat11 ON cat11.tipoItemMHId = fd.tipoItemMHId
                    WHERE fd.facturaId = ? AND fd.flgDelete = ?
                ", [$_POST['facturaId'], 0]);
                $n = 0;
                $totalCantidadProducto = 0;
                $totalDescuento = 0;
                $totalIVA = 0;
                $totalDetalle = 0;
                $totalDetalleIVA = 0;
                foreach ($dataDTEDetalle as $dteDetalle) {
                    $n++;

                    echo "
                        <tr>
                            <td>$n</td>
                            <td>
                                <div class='row'>
                                    <div class='col-6'>
                                        <b>Ref. detalle: </b> $dteDetalle->facturaDetalleId<br>
                                        <b>Cód. Magic: </b> $dteDetalle->codProductoFactura<br>
                                        <b>Marca: </b> ($dteDetalle->abreviaturaMarca) $dteDetalle->nombreMarca
                                    </div>
                                    <div class='col-6'>
                                        <b>Ref. producto: </b> $dteDetalle->productoId<br>
                                        <b>Cód. interno: </b> $dteDetalle->codProductoInterno<br>
                                        <b>Tipo de item: </b> ($dteDetalle->codigoMHItem) $dteDetalle->tipoItem
                                    </div>
                                </div>
                                <b>Producto: </b> $dteDetalle->nombreProductoFactura<br>
                            </td>
                            <td>
                                Sin IVA: <br>
                                <div class='simbolo-moneda border-bottom'>
                                    <span>$dataDTEGeneral->simboloMoneda</span>
                                    <div>".number_format($dteDetalle->precioUnitario, 6, ".", ",")."</div>
                                </div>
                                Con IVA: <br>
                                <div class='simbolo-moneda'>
                                    <span>$dataDTEGeneral->simboloMoneda</span>
                                    <div>".number_format($dteDetalle->precioUnitarioIVA, 6, ".", ",")."</div>
                                </div>
                            </td>
                            <td>
                                Sin IVA: <br>
                                <div class='simbolo-moneda border-bottom'>
                                    <span>$dataDTEGeneral->simboloMoneda</span>
                                    <div>".number_format($dteDetalle->precioVenta, 6, ".", ",")."</div>
                                </div>
                                Con IVA: <br>
                                <div class='simbolo-moneda'>
                                    <span>$dataDTEGeneral->simboloMoneda</span>
                                    <div>".number_format($dteDetalle->precioVentaIVA, 6, ".", ",")."</div>
                                </div>
                            </td>
                            <td>$dteDetalle->cantidadProducto ".($dteDetalle->abreviaturaUnidadMedida == "" ? "u" : $dteDetalle->abreviaturaUnidadMedida)."</td>
                            <td>
                            " . number_format($dteDetalle->porcentajeDescuento ?? 0.0, 4, ".", ",") . "% unit.:<br>
                            <div class='simbolo-moneda border-bottom'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>" . number_format($dteDetalle->descuentoUnitario ?? 0.0, 6, ".", ",") . "</div>
                            </div>
                            Desc. total: <br>
                            <div class='simbolo-moneda'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>" . number_format($dteDetalle->descuentoTotal ?? 0.0, 6, ".", ",") . "</div>
                            </div>
                        </td>
                        <td>
                            IVA unitario: <br>
                            <div class='simbolo-moneda border-bottom'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>" . number_format($dteDetalle->ivaUnitario ?? 0.0, 6, ".", ",") . "</div>
                            </div>
                            IVA total: <br>
                            <div class='simbolo-moneda'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>" . number_format($dteDetalle->ivaTotal ?? 0.0, 6, ".", ",") . "</div>
                            </div>
                        </td>
                        <td>
                            Sin IVA: <br>
                            <div class='simbolo-moneda border-bottom'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>" . number_format($dteDetalle->totalDetalle ?? 0.0, 2, ".", ",") . "</div>
                            </div>
                            Con IVA: <br>
                            <div class='simbolo-moneda'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>" . number_format($dteDetalle->totalDetalleIVA ?? 0.0, 2, ".", ",") . "</div>
                            </div>
                        </td>
                    </tr>";
                    // de la linea 301 a la 335 parche el error de cuando viene null
                    $totalCantidadProducto += $dteDetalle->cantidadProducto;
                    $totalDescuento += $dteDetalle->descuentoTotal;
                    $totalIVA += $dteDetalle->ivaTotal;
                    $totalDetalle += $dteDetalle->totalDetalle;
                    $totalDetalleIVA += $dteDetalle->totalDetalleIVA;
                }
            ?>
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td></td>
                <td></td>
                <td></td>
                <td><b>Totales</b></td>
                <?php 
                    echo "
                        <td>
                            <div class='simbolo-moneda'>
                                <span></span>
                                <div>".number_format($totalCantidadProducto, 2, ".", ",")."</div>
                            </div>
                        </td>
                        <td>
                            <div class='simbolo-moneda'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>".number_format($totalDescuento, 6, ".", ",")."</div>
                            </div>
                        </td>
                        <td>
                            <div class='simbolo-moneda'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>".number_format($totalIVA, 6, ".", ",")."</div>
                            </div>
                        </td>
                        <td>
                            <div class='simbolo-moneda'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>".number_format(($totalDetalleIVA == 0 ? $totalDetalle : $totalDetalleIVA), 2, ".", ",")."</div>
                            </div>
                        </td>
                    ";
                ?>
            </tr>
        </tfoot>
    </table>
</div>
<h4>
    <?php 
        $flgFacturaRelacionada = "No";
        // Agregar con || los tipos de DTE que tienen factura relacionada
        // Notas de crédito = 4, Comprobante de retención = 6
        if($dataDTEGeneral->tipoDTEId == 4 || $dataDTEGeneral->tipoDTEId == 6) {
            $flgFacturaRelacionada = "Sí";
            echo "Factura relacionada";
        } else {
            echo "Cobro del DTE";
        }
    ?>
</h4>
<div class="row">
    <div class="col-6 mb-4">
        <?php 
            if($flgFacturaRelacionada == "Sí") {
                $dataFacturaRelacionada = $cloud->row("
                    SELECT 
                        frel.facturaRelacionadaId AS facturaRelacionadaId, 
                        frel.facturaIdRelacionada AS facturaIdRelacionada, 
                        frel.tipoGeneracionDocId AS tipoGeneracionDocId, 
                        cat007.codigoMH AS codigoRelacionadaMH,
                        cat007.tipoGeneracionDoc AS tipoGeneracionDoc,
                        frel.numeroDocumentoRelacionada AS numeroDocumentoRelacionada, 
                        frel.fechaEmisionRelacionada AS fechaEmisionRelacionada, 
                        DATE_FORMAT(frel.fechaEmisionRelacionada, '%d/%m/%Y') AS fechaEmisionRelacionadaFormat,
                        frel.horaEmisionRelacionada AS horaEmisionRelacionada
                    FROM fel_factura_relacionada$yearBD frel
                    JOIN mh_007_tipo_generacion_documento cat007 ON cat007.tipoGeneracionDocId = frel.tipoGeneracionDocId
                    WHERE frel.facturaId = ? AND frel.flgDelete = ?
                ", [$_POST['facturaId'], 0])
        ?>
                <h5>Información del DTE relacionado</h5>
                <b><i class="fas fa-file-signature"></i> Tipo de generación: </b> <?php echo "($dataFacturaRelacionada->codigoRelacionadaMH) $dataFacturaRelacionada->tipoGeneracionDoc"; ?><br>
                <b><i class="fas fa-list-ol"></i> Núm. crédito fiscal: </b> <?php echo $dataFacturaRelacionada->numeroDocumentoRelacionada; ?><br>
                <b><i class="fas fa-list-ol"></i> Fecha/Hora del crédito fiscal: </b> <?php echo "$dataFacturaRelacionada->fechaEmisionRelacionadaFormat $dataFacturaRelacionada->horaEmisionRelacionada"; ?><br>
        <?php 
            } else {
        ?>
                <h5>Información del DTE</h5>
                <b><i class="fas fa-credit-card"></i> Forma de pago: </b> <?php echo $dataDTEGeneral->formaPagoMH; ?><br>
                <b><i class="fas fa-money-check-alt"></i> Condición de pago: </b> <?php echo "($dataDTEGeneral->codCondicionFacturaMH) $dataDTEGeneral->condicionFactura" . ($dataDTEGeneral->codCondicionFacturaMH == 2 ? " de $dataDTEGeneral->periodoPlazo " . mb_strtolower($dataDTEGeneral->plazoPago) : ""); ?>
                <br><br>
                <h5>Información de Magic</h5>
                <b><i class="fas fa-credit-card"></i> Forma de pago: </b> <?php echo "($dataDTEGeneral->mgCodFormaPago) $dataDTEGeneral->mgNombreFormaPago"; ?><br>
                <b><i class="fas fa-money-check-alt"></i> Condición de pago: </b> <?php echo $dataDTEGeneral->mgCondicionPago . ($dataDTEGeneral->codCondicionFacturaMH == 2 ? " de $dataDTEGeneral->periodoPlazo " . mb_strtolower($dataDTEGeneral->plazoPago) : ""); ?>
        <?php 
            }
        ?>
    </div>
    <div class="col-6 mb-4">
        <?php 
            $dataTotalDTE = $cloud->row("
                SELECT 
                    SUM(subTotalDetalle) AS subTotal,
                    SUM(subTotalDetalleIVA) AS subTotalIVA,
                    SUM(descuentoTotal) AS descuentoTotal,
                    SUM(ivaTotal) AS ivaTotal,
                    SUM(totalDetalle) AS total,
                    SUM(totalDetalleIVA) AS totalIVA
                FROM fel_factura_detalle$yearBD
                WHERE facturaId = ? AND flgDelete = ?
            ", [$_POST['facturaId'], 0]);

            if($dataTotalDTE) {
                // Cambiar las columnas de IVA según el tipo de documento que aplique
                $subTotalDTE = $dataTotalDTE->subTotal;
                $descuentoDTE = $dataTotalDTE->descuentoTotal;
                $ivaDTE = $dataTotalDTE->ivaTotal;
                // Exportación, Sujeto excluido SIN IVA
                if($dataDTEGeneral->tipoDTEId == 6 || $dataDTEGeneral->tipoDTEId == 9 || $dataDTEGeneral->tipoDTEId == 10) {
                    $totalDTE = $dataTotalDTE->total;
                } else {
                    if($dataTotalDTE->totalIVA == 0) {
                        // Fue factura exenta
                        $totalDTE = $dataTotalDTE->total;
                    } else {
                        $totalDTE = $dataTotalDTE->totalIVA;
                    }
                }
            } else {
                // No había detalle (que no debería pasar) pero igual queda declaradas en cero para que no dé error
                $subTotalDTE = 0;
                $descuentoDTE = 0;
                $ivaDTE = 0;
                $totalDTE = 0;
            }

            $totalCalculado = $subTotalDTE - $descuentoDTE;

            if($dataDTEGeneral->ivaRetenido > 0) {
                $divIVARetenido = "
                    <div class='row border-bottom'>
                        <div class='col-6 fw-bold'>
                            (-) IVA $porcentajeIVARetenido% retenido
                        </div>
                        <div class='col-6'>
                            <div class='simbolo-moneda fw-bold'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>
                                    ". number_format($dataDTEGeneral->ivaRetenido, 2, ".", ",") ."
                                </div>
                            </div>
                        </div>
                    </div>
                ";
                if($dataDTEGeneral->tipoDTEId == 6) {
                    // Por alguna razón en este tipo de DTE se suma
                    $totalCalculado += $dataDTEGeneral->ivaRetenido;
                } else {
                    $totalCalculado -= $dataDTEGeneral->ivaRetenido;
                }
            } else {
                $divIVARetenido = "";
            }

            if($dataDTEGeneral->ivaPercibido > 0) {
                $divIVAPercibido = "
                    <div class='row border-bottom'>
                        <div class='col-6 fw-bold'>
                            (+) IVA $porcentajeIVAPercibido% percibido
                        </div>
                        <div class='col-6'>
                            <div class='simbolo-moneda fw-bold'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>
                                    ". number_format($dataDTEGeneral->ivaPercibido, 2, ".", ",") ."
                                </div>
                            </div>
                        </div>
                    </div>
                ";
                $totalCalculado += $dataDTEGeneral->ivaPercibido;
            } else {
                $divIVAPercibido = "";
            }

            if($dataDTEGeneral->rentaRetenido > 0) {
                $divRentaRetenido = "
                    <div class='row border-bottom'>
                        <div class='col-6 fw-bold'>
                            (-) Renta $dataDTEGeneral->porcentajeRenta%
                        </div>
                        <div class='col-6'>
                            <div class='simbolo-moneda fw-bold'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>
                                    ". number_format($dataDTEGeneral->rentaRetenido, 2, ".", ",") ."
                                </div>
                            </div>
                        </div>
                    </div>
                ";
                $totalCalculado -= $dataDTEGeneral->rentaRetenido;
            } else {
                $divRentaRetenido = "";
            }

            // Mantener este cálculo
            $totalCalculado += $ivaDTE;

            if($dataDTEGeneral->tipoDTEId == 6) {
                $totalCalculado = (($totalDTE + $dataDTEGeneral->ivaRetenido) + $dataDTEGeneral->ivaPercibido) - $dataDTEGeneral->rentaRetenido;
            } else {
                // Pero de momento sobreescribirlo para comparar con lo que hay en fel_factura_pago
                $totalCalculado = (($totalDTE - $dataDTEGeneral->ivaRetenido) + $dataDTEGeneral->ivaPercibido) - $dataDTEGeneral->rentaRetenido;
            }

            if(number_format($dataDTEGeneral->montoPagoMagic, 2, ".", ",") == number_format($totalCalculado, 2, ".", ",")) {
                $divDiferenciaDTE = "
                    <div class='row text-success border-bottom'>
                        <div class='col-6 fw-bold'>
                            Diferencia Cloud con Magic
                        </div>
                        <div class='col-6'>
                            <div class='simbolo-moneda fw-bold'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>
                                    ". number_format(0, 2, ".", ",") ."
                                </div>
                            </div>
                        </div>
                    </div>
                ";
            } else {
            /*    $divDiferenciaDTE = "
                    <div class='row text-danger border-bottom'>
                        <div class='col-6 fw-bold'>
                            Diferencia Cloud con Magic
                        </div>
                        <div class='col-6'>
                            <div class='simbolo-moneda fw-bold'>
                                <span>$dataDTEGeneral->simboloMoneda</span>
                                <div>
                                    ". number_format(($totalCalculado - $dataDTEGeneral->montoPagoMagic), 2, ".", ",") ."
                                </div>
                            </div>
                        </div>
                    </div>
                ";*/
            }

            echo "
                <div class='row border-bottom'>
                    <div class='col-6 fw-bold'>
                        (=) Subtotal
                    </div>
                    <div class='col-6'>
                        <div class='simbolo-moneda fw-bold'>
                            <span>$dataDTEGeneral->simboloMoneda</span>
                            <div>
                                ". number_format($subTotalDTE, 6, ".", ",") ."
                            </div>
                        </div>
                    </div>
                </div>
                <div class='row border-bottom'>
                    <div class='col-6 fw-bold'>
                        (-) Descuento
                    </div>
                    <div class='col-6'>
                        <div class='simbolo-moneda fw-bold'>
                            <span>$dataDTEGeneral->simboloMoneda</span>
                            <div>
                                ". number_format($descuentoDTE ?? 0.0, 6, ".", ",") ."
                            </div>
                        </div>
                    </div>
                </div>
                <div class='row border-bottom'>
                    <div class='col-6 fw-bold'>
                        (+) IVA $porcentajeIVA%
                    </div>
                    <div class='col-6'>
                        <div class='simbolo-moneda fw-bold'>
                            <span>$dataDTEGeneral->simboloMoneda</span>
                            <div>
                                ". number_format($ivaDTE ?? 0.0, 6, ".", ",") ."
                            </div>
                        </div>
                    </div>
                </div>
                $divIVARetenido
                $divIVAPercibido
                $divRentaRetenido
                <div class='row border-bottom'>
                    <div class='col-6 fw-bold'>
                        (=) Total
                    </div>
                    <div class='col-6'>
                        <div class='simbolo-moneda fw-bold'>
                            <span>$dataDTEGeneral->simboloMoneda</span>
                            <div>
                                ". number_format($totalCalculado, 2, ".", ",") ."
                            </div>
                        </div>
                    </div>
                </div>
                <div class='fw-bold text-center border-bottom'>Verificación de cálculo</div>
                <div class='row border-bottom'>
                    <div class='col-6 fw-bold'>
                        Total calculado
                    </div>
                    <div class='col-6'>
                        <div class='simbolo-moneda fw-bold'>
                            <span>$dataDTEGeneral->simboloMoneda</span>
                            <div>
                                ". number_format($totalCalculado, 2, ".", ",") ."
                            </div>
                        </div>
                    </div>
                </div>
            ";
        ?>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#tblDTEDetalle thead tr#filterboxrow-detalle th').each(function(index) {
            if(index == 1) {
                var title = $('#tblDTEDetalle thead tr#filterboxrow-detalle th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-detalle" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-detalle">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblDTEDetalle.column($(this).index()).search($(`#input${$(this).index()}-detalle`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblDTEDetalle = $('#tblDTEDetalle').DataTable({
            "dom": 'lrtip',
            "autoWidth": false,
            "columns": [
                null, null, null, null, null, null, null, null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3, 4, 5, 6, 7] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>