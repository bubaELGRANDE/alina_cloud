<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();
/*
    $dataCompra = $cloud->row("
        SELECT 
            m.abreviaturaMoneda AS abreviaturaMoneda
        FROM comp_compras ped
        JOIN cat_monedas m ON m.monedaId = ped.monedaId
        WHERE ped.compraId = ? AND ped.flgDelete = ?
    ", [$_POST['compraId'], 0]);

    $abreviaturaMoneda = $dataCompra->abreviaturaMoneda;
*/

    $dataFacturaDetalle = $cloud->rows("
    SELECT
    pd.facturaDetalleId AS facturaDetalleId, 
    pd.facturaId AS facturaId, 
    ped.fechaEmision AS fechaEmision,
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
    DATE_FORMAT(fr.fechaEmisionRelacionada, '%d/%m/%Y') AS fechaEmisionFormat,
    dte.tipoDTE AS tipoDTE,
    tg.tipoGeneracionDoc AS tipoGeneracionDoc,
    pd.facturaRelacionadaId AS facturaRelacionadaId
FROM 
    fel_factura_detalle pd
    JOIN fel_factura ped ON ped.facturaId = pd.facturaId
    JOIN inv_productos p ON p.productoId = pd.productoId
    JOIN fel_factura_relacionada fr ON fr.facturaRelacionadaId = pd.facturaRelacionadaId
    JOIN mh_002_tipo_dte dte ON dte.tipoDTEId = fr.tipoDTEId
    JOIN mh_007_tipo_generacion_documento tg ON tg.tipoGeneracionDocId = fr.tipoGeneracionDocId
    WHERE pd.facturaId = ? AND pd.flgDelete = ?
    ", [$_POST['facturaId'], 0]);

    $n = 0;

    $totalCostoUnitario = 0;
    $totalCantidad = 0;
    $totalCostoTotal = 0;
    $totalCostoTotalMoneda = 0;
    $totalIvaRetenido =0;

    foreach ($dataFacturaDetalle as $facturaDetalle){
        $n += 1;

        $columnaDTERelacionado = '<i class="fas fa-barcode"></i><b> Tipo de DTE: </b>'. $facturaDetalle->tipoDTE.'<br><i class="fas fa-calendar-alt"></i><b> Tipo de generación: </b>'. $facturaDetalle->tipoGeneracionDoc;
        $columnaNumDTE = '<i class="fas fa-list"></i><b> N° DTE: </b>'. $facturaDetalle->numeroDocumentoRelacionada.'<br><i class="fas fa-calendar-alt"></i><b> Fecha: </b>'. $facturaDetalle->fechaEmisionFormat;

        $columnaDescripcion = '<div class="text-end">'.$facturaDetalle->nombreProductoFactura.'</div>';

            $columnaMonto = "
                <div class='simbolo-moneda'>
                    <span>$_SESSION[monedaSimbolo]</span>
                    <div>
                        ". number_format($facturaDetalle->precioUnitario, 2, ".", ",") ."
                    </div>
                </div>
            ";

            $columnaIva = "
                <div class='simbolo-moneda'>
                    <span>$_SESSION[monedaSimbolo]</span>
                    <div>
                        ". number_format($facturaDetalle->ivaRetenidoDetalle, 2, ".", ",") ."
                    </div>
                </div>
            ";

        $jsonEditarFacturaDetalle = array(
            "facturaId"                 => $_POST["facturaId"],
            "typeOperation"             => "update",
            "operation"                 => "comprobanteRetencionDetalle",
            "tituloModal"               => "Editar comprobante ",
            "facturaDetalleId"          => $facturaDetalle->facturaDetalleId,
            "facturaRelacionadaId"      => $facturaDetalle->facturaRelacionadaId
        );

        $jsonEliminarCompraDetalle = array(
            "typeOperation"              => "delete",
            "operation"                  => "comprobante-detalle-delete",
            "facturaId"                   => $facturaDetalle->facturaId,
            "facturaDetalleId"            => $facturaDetalle->facturaDetalleId
        );
        $functionEliminarCompraDetalle = htmlspecialchars(json_encode($jsonEliminarCompraDetalle));

        $acciones = "
            <button type='button' class='btn btn-primary btn-sm ttip' onclick='modalEditarDocumento(".htmlspecialchars(json_encode($jsonEditarFacturaDetalle)).");' $_POST[disabledRetencion]>
                <i class='fas fa-pencil-alt'></i> 
                <span class='ttiptext'>Editar Comprobante</span>
             </button>
         
             <button type='button' class='btn btn-danger btn-sm ttip' onclick='eliminarComprobanteDetalle(".$functionEliminarCompraDetalle.");' $_POST[disabledRetencion]>
                 <i class='fas fa-trash-alt'></i>   
                 <span class='ttiptext'>Eliminar factura</span>
            </button>
        ";

        // Sumar los valores para el footer
        $totalCostoUnitario += $facturaDetalle->precioUnitario;
        $totalCantidad += $facturaDetalle->cantidadProducto;
        $totalCostoTotal += $facturaDetalle->totalDetalle;
        $totalIvaRetenido += $facturaDetalle->ivaRetenidoDetalle;
     

        $output['data'][] = array(
            $n , // es #, se dibuja solo en el JS de datatable
            $columnaDTERelacionado,
            $columnaNumDTE,
            $columnaDescripcion,
            $columnaMonto,
            $columnaIva,
            $acciones
        );
    }
    // Agregar el footer al resultado
 
        $output['footer'] = array(
            '', 
            '<b>Totales</b>',
            '',
            '',
            "<div class='simbolo-moneda fw-bold'>
                <span>$_SESSION[monedaSimbolo]</span>
                <div>
                    ". number_format($totalCostoTotal, 2, ".", ",") ."
                </div>
            </div>",
            "<div class='simbolo-moneda fw-bold'>
                <span>$_SESSION[monedaSimbolo]</span>
                <div>
                    ". number_format($totalIvaRetenido, 2, ".", ",") ."
                </div>
            </div>",
            ''
        );
    

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        $output['data'] = '';

        $output['footer'] = array(
            '', 
            '<b>Totales</b>',
            '',
            '',
            "<div class='simbolo-moneda fw-bold'>
                <span>$_SESSION[monedaSimbolo]</span>
                <div>
                    ". number_format($totalCostoTotal, 2, ".", ",") ."
                </div>
            </div>",
            "<div class='simbolo-moneda fw-bold'>
                <span>$_SESSION[monedaSimbolo]</span>
                <div>
                    ". number_format($totalIvaRetenido, 2, ".", ",") ."
                </div>
            </div>",
            ''
        );

        echo json_encode($output);    
    }
?>