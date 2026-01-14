<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataListaFacturas = $cloud->rows("
    SELECT
    f.facturaId AS facturaId,
    fe.sucursalId AS sucursalId,
    s.sucursal AS sucursal,
    f.tipoDTEId AS tipoDTEId,
    cat002.codigoMH AS codTipoDTEMH,
    cat002.tipoDTE AS tipoDTE,
    f.fechaEmision AS fechaEmision,
    DATE_FORMAT(f.fechaEmision, '%d/%m/%Y') AS fechaEmisionFormat,
    f.horaEmision AS horaEmision,
    fc.clienteId AS clienteId,
    CASE
        WHEN fc.nombreCliente = '' OR fc.nombreCliente IS NULL THEN fc.nombreComercialCliente
        ELSE fc.nombreCliente
    END AS nombreCliente,
    cp.proveedorId AS proveedorId,
    CASE
        WHEN cp.nombreProveedor = '' OR cp.nombreProveedor IS NULL THEN cp.nombreComercial
        ELSE cp.nombreProveedor
    END AS nombreProveedor,
    cp.nrcProveedor AS nrcProveedor,
    cp.tipoDocumento AS tipoDocumento,
    cp.numDocumento AS numDocumento,
    cpu.proveedorUbicacionId AS proveedorUbicacionId,
    fcu.nombreClienteUbicacion AS nombreClienteUbicacion,
    fcu.clienteUbicacionId AS clienteUbicacionId,
    cpu.nombreProveedorUbicacion AS nombreProveedorUbicacion,
    fr.ivaRetenido AS ivaRetenido,
    fr.ivaRetenido AS totalIvaRetenido,
    f.estadoFactura AS estadoFactura,
    f.obsAnulacionInterna AS obsAnulacionInterna,
    (
        SELECT SUM(ffd.totalDetalle) FROM fel_factura_detalle ffd
        WHERE ffd.facturaId = f.facturaId AND ffd.flgDelete = 0
    ) AS total
FROM fel_factura f
JOIN fel_factura_emisor fe ON fe.facturaId = f.facturaId
JOIN cat_sucursales s ON s.sucursalId = fe.sucursalId
LEFT JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = f.tipoDTEId
LEFT JOIN comp_proveedores_ubicaciones cpu ON cpu.proveedorUbicacionId = f.proveedorUbicacionId
LEFT JOIN fel_clientes_ubicaciones fcu ON fcu.clienteUbicacionId = f.clienteUbicacionId
LEFT JOIN comp_proveedores cp ON cp.proveedorId = cpu.proveedorId
LEFT JOIN fel_factura_retenciones fr ON fr.facturaId = f.facturaId
LEFT JOIN fel_clientes fc ON fc.clienteId = fcu.clienteId
WHERE f.estadoFactura = ? AND f.flgDelete = ? AND f.tipoDTEId = 6 AND NOT EXISTS (
    SELECT 1 FROM fel_factura_certificacion ffc
    WHERE ffc.facturaId = f.facturaId AND ffc.flgDelete = 0 AND ffc.estadoCertificacion = 'Certificado'    
 )
 GROUP BY f.facturaId, fe.sucursalId, fr.ivaRetenido
", ["Finalizado", 0]);

//mostrar los campos en el bucle 

$n = 0;
foreach ($dataListaFacturas as $listarFactura) {
    $n += 1;

    $numeroDTE = '<b><i class="fas fa-file-alt"></i> Núm. DTE: </b> '.$listarFactura->facturaId.'<br>'.'<b><i class="fas fa-calendar-day"></i>   Fecha: </b> '.$listarFactura->fechaEmisionFormat ;
    $Proveedor = '<b><i class="fas fa-user-tie"></i> Proveedor: </b> '.$listarFactura->nombreProveedor.'<br>'.'<b><i class="fas fa-list-ol"> </i> NRC: </b> '.$listarFactura->nrcProveedor.'<br>'.'<b>'.$listarFactura->tipoDocumento.': </b> '. $listarFactura->numDocumento;
    $fecha = '<b><i class="fas fa-coins"></i>  Total: </b>$ '.number_format($listarFactura->total, 2, '.',',').'<br>'.'<b>(-) IVA retenido: </b>$ '.number_format($listarFactura->totalIvaRetenido, 2, '.', ',');

    $jsonCertificar = array(
        "typeOperation"              => "certificacion",
        "operation"                  => "certificar-comprobante-retencion",
        "facturaId" 		         => $listarFactura->facturaId,
        "tipoDTEId" 				 => $listarFactura->tipoDTEId,
        "tipoDTE" 					 => $listarFactura->tipoDTE
    );
    $funcionCertificar = htmlspecialchars(json_encode($jsonCertificar));

    // Acciones que va a tener la table 
    $acciones = '
    <button type="button" class="btn btn-primary btn-sm" onclick="certificarDTE('.$funcionCertificar.');">
    <i class="fas fa-file-signature"></i></i> certificar DTE
    </button>
                ';

    $output['data'][] = array(
        $n, // es #, se dibuja solo en el JS de datatable
        $numeroDTE,
        $Proveedor,
        $fecha,
        $acciones
    );
}


if($n > 0) {
echo json_encode($output);
} else {
// No retornar nada para evitar error "null"
echo json_encode(array('data'=>'')); 
}

