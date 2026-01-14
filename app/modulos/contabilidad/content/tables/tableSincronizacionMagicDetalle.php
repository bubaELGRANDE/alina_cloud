<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    // Este archivo permite generar las mismas columnas para cualquier tipo de sincronización o exportación a Magic

    $dataSincronizacionDetalle = $cloud->rows("
    	SELECT
    		bitExportacionMagicDetalleId, tablaExportacion, tablaExportacionId
    	FROM bit_exportaciones_magic_detalle
    	WHERE bitExportacionMagicId = ? AND flgDelete = ?
    ", [$_POST["bitExportacionMagicId"],  0]);

    $n = 0;
    foreach ($dataSincronizacionDetalle as $sincronizacionDetalle) {
        $n++;

        $tablaExportacion = $sincronizacionDetalle->tablaExportacion;

        switch($_POST['descripcionExportacion']) {
            case "comp_compras":
                // Últimos 5 caracteres para saber el año
                $yearBDDetalle = substr($tablaExportacion, -5);
                // No se validó con tablaExportacion, porque los JOIN, LEFT que se van a utilizar serán diferentes y la idea es reutilizar estos archivos para más exportaciones
                $dataCompra = $cloud->row("
                    SELECT 
                        tdte.codigoMH AS codigoMHDTE,
                        tdte.tipoDTE AS tipoDTE,
                        tgd.codigoMH AS codigoMHGeneracion,
                        tgd.tipoGeneracionDoc AS tipoGeneracionDoc,
                        c.numFactura AS numFactura,
                        p.nrcProveedor AS nrcProveedor,
                        p.tipoDocumento AS tipoDocumentoProveedor,
                        p.numDocumento AS numDocumentoProveedor,
                        p.nombreProveedor AS nombreProveedor,
                        c.fechaFactura AS fechaFactura,
                        (
                            SELECT SUM(cd.costoDetalleTotalIVA) FROM comp_compras_detalle$yearBDDetalle cd
                            WHERE cd.compraId = c.compraId AND cd.flgDelete = 0
                        ) AS totalFacturaIVA
                    FROM $tablaExportacion c
                    JOIN mh_002_tipo_dte tdte ON tdte.tipoDTEId = c.tipoDTEId
                    JOIN mh_007_tipo_generacion_documento tgd ON tgd.tipoGeneracionDocId = c.tipoGeneracionDocId
                    JOIN comp_proveedores_ubicaciones pu ON pu.proveedorUbicacionId = c.proveedorUbicacionId
                    JOIN comp_proveedores p ON p.proveedorId = pu.proveedorId
                    WHERE c.compraId = ? AND c.estadoCompra = ? AND c.flgDelete = ?
                ", [$sincronizacionDetalle->tablaExportacionId, "Finalizado", 0]);

            	$columna2 = "
                    <b><i class='fas fa-file-alt'></i> Tipo: </b> ({$dataCompra->codigoMHDTE}) {$dataCompra->tipoDTE}<br>
                    <b><i class='fas fa-sync-alt'></i> Generación: </b> ({$dataCompra->codigoMHGeneracion}) {$dataCompra->tipoGeneracionDoc}<br>
                    <b><i class='fas fa-list-ol'></i> Documento: </b> {$dataCompra->numFactura}<br>
                ";
            	$columna3 = "
                    <b><i class='fas fa-id-card'></i> NRC: </b> {$dataCompra->nrcProveedor}<br>
                    <b><i class='fas fa-address-card'></i> {$dataCompra->tipoDocumentoProveedor}: </b> {$dataCompra->nrcProveedor}<br>
                    <b><i class='fas fa-user-tie'></i> Proveedor: </b> {$dataCompra->nombreProveedor}
                ";
            	$columna4 = date("d/m/Y", strtotime($dataCompra->fechaFactura));
            	$columna5 = "
                    <div class='simbolo-moneda fw-bold'>
                        <span>{$_SESSION['monedaSimbolo']}</span>
                        <div>
                            ". number_format((float)$dataCompra->totalFacturaIVA, 2, ".", ",") ."
                        </div>
                    </div>
                ";

            	if($_POST['estadoExportacion'] == "Pendiente") {
                    $jsonSincronizacionDetalle = array(
                        "typeOperation"                             => "delete",
                        "operation"                                 => "sincronizacion-magic-compras-detalle",
                        "bitExportacionMagicDetalleId"              => $sincronizacionDetalle->bitExportacionMagicDetalleId,
                        "tablaExportacion"                          => $sincronizacionDetalle->tablaExportacion,
                        "tablaExportacionId"                        => $sincronizacionDetalle->tablaExportacionId
                    );

            		$acciones = "
                        <button type='button' class='btn btn-danger btn-sm ttip' onclick='eliminarSincronizacionDetalle(".htmlspecialchars(json_encode($jsonSincronizacionDetalle)).");'>
                            <i class='fas fa-trash-alt'></i>
                            <span class='ttiptext'>Eliminar compra de la sincronización</span>
                        </button>
                    ";
            	} else {
            		$acciones = "
                        <button type='button' class='btn btn-danger btn-sm ttip' disabled>
                            <i class='fas fa-trash-alt'></i>
                            <span class='ttiptext'>Eliminar compra de la sincronización</span>
                        </button>
                    ";
            	}

            break;

            default:
                $acciones = "";
            break;
        }

        $output['data'][] = array(
            $n,
            $columna2,
            $columna3,
            $columna4,
            $columna5,
            $acciones
        );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>