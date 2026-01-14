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
        f.estadoFactura AS estadoFactura,
        f.obsAnulacionInterna AS obsAnulacionInterna
    FROM
        fel_factura f
    JOIN
        fel_factura_emisor fe ON fe.facturaId = f.facturaId
    JOIN
        cat_sucursales s ON s.sucursalId = fe.sucursalId
    LEFT JOIN
        mh_002_tipo_dte cat002 ON cat002.tipoDTEId = f.tipoDTEId
    LEFT JOIN
        comp_proveedores_ubicaciones cpu ON cpu.proveedorUbicacionId = f.proveedorUbicacionId
    LEFT JOIN
        fel_clientes_ubicaciones fcu ON fcu.clienteUbicacionId = f.clienteUbicacionId
    LEFT JOIN
        comp_proveedores cp ON cp.proveedorId = cpu.proveedorId
    LEFT JOIN
        fel_factura_retenciones fr ON fr.facturaId = f.facturaId
    LEFT JOIN
        fel_clientes fc ON fc.clienteId = fcu.clienteId
    WHERE
        f.estadoFactura = ? AND
        f.flgDelete = ? AND
        f.tipoDTEId = 6
", ["Pendiente", 0]);

$n = 0;
foreach ($dataListaFacturas as $listarFactura) {
    $n += 1;

    $numeroDTE = '<b><i class="fas fa-list-ol"></i> Núm. DTE: </b> ' . $listarFactura->facturaId;
    $Proveedor = '<b><i class="fas fa-user-tie"></i> Proveedor: </b> ' . $listarFactura->nombreProveedor;
    $fecha = '<b><i class="fas fa-calendar-day"></i> Fecha: </b> ' . $listarFactura->fechaEmisionFormat;

    $jsonAnular = array(
        "facturaId" => $listarFactura->facturaId
    );
    $funcionAnular = htmlspecialchars(json_encode($jsonAnular));

    $acciones = '<button type="button" class="btn btn-primary btn-sm" onclick="changePage(`' . $_SESSION["currentRoute"] . '`, `comprobante-retencion`, `facturaId=' . $listarFactura->facturaId . '`);">
        <i class="fas fa-sync-alt"></i> Continuar DTE
    </button>
        <button type="button" class="btn btn-danger btn-sm" onclick="modalAnulacionInterna(' . $funcionAnular . ');">
        <i class="fas fa-ban"></i> Anular DTE
        </button>';

    $output['data'][] = array(
        $n, // es #, se dibuja solo en el JS de datatable
        $numeroDTE,
        $Proveedor,
        $fecha,
        $acciones
    );
}

if ($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data' => ''));
}
?>
