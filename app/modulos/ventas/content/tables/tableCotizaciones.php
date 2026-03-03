<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$data = $cloud->rows(
    "SELECT
        c.cotizacionId,
        c.fechaEmision,
        DATE_FORMAT(c.fechaEmision, '%d/%m/%Y') AS fechaEmisionFormat,
        c.totalCotizacion,
        c.estadoCotizacion,
        s.sucursal,
        c.nombreClienteSala,
        cl.nombreCliente,
        cl.nombreComercialCliente,
        cl.nrcCliente,
        cl.numDocumento
    FROM fel_cotizacion c
    LEFT JOIN cat_sucursales s ON s.sucursalId = c.sucursalId
    LEFT JOIN fel_clientes cl ON cl.clienteId = c.clienteId
    WHERE c.flgDelete = 0
    ORDER BY c.cotizacionId DESC",
    []
);

$n = 0;
$output = ['data' => []];
foreach($data as $row) {
    $n++;

    // Cliente registrado o cliente en sala
    $clienteNombre = $row->nombreCliente;
    if(($clienteNombre == '' || is_null($clienteNombre)) && !is_null($row->nombreComercialCliente) && $row->nombreComercialCliente !== '') {
        $clienteNombre = $row->nombreComercialCliente;
    }
    if(($clienteNombre == '' || is_null($clienteNombre)) && !is_null($row->nombreClienteSala) && $row->nombreClienteSala !== '') {
        $clienteNombre = $row->nombreClienteSala;
    }

    $colId = '<b><i class="fas fa-hashtag"></i> Cotización: </b> ' . $row->cotizacionId;

    $colCliente = '<b><i class="fas fa-user"></i> Cliente: </b> ' . ($clienteNombre ?? 'N/A');
    if(!is_null($row->nrcCliente) && $row->nrcCliente !== '') {
        $colCliente .= '<br><b><i class="fas fa-list-ol"></i> NRC: </b> ' . $row->nrcCliente;
    }
    if(!is_null($row->numDocumento) && $row->numDocumento !== '') {
        $colCliente .= '<br><b><i class="fas fa-id-card"></i> Doc: </b> ' . $row->numDocumento;
    }

    $colSucursal = '<b><i class="fas fa-store"></i> Sucursal: </b> ' . ($row->sucursal ?? 'N/A');

    $colFecha = '<b><i class="fas fa-calendar-day"></i> Fecha: </b> ' . ($row->fechaEmisionFormat ?? '');

    $colTotal = '<b><i class="fas fa-coins"></i> Total: </b> ' . ($_SESSION['monedaSimbolo'] ?? '$') . ' ' . number_format((float)$row->totalCotizacion, 2, '.', ',');

    $badge = 'secondary';
    if($row->estadoCotizacion == 'Emitida') $badge = 'success';
    if($row->estadoCotizacion == 'Anulada') $badge = 'danger';
    if($row->estadoCotizacion == 'Borrador') $badge = 'warning';

    $colEstado = '<span class="badge bg-' . $badge . '">' . ($row->estadoCotizacion ?? 'N/A') . '</span>';

    $jsonOpen = htmlspecialchars(json_encode([
        'cotizacionId' => $row->cotizacionId
    ]));

    $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onclick="abrirCotizacion(' . $jsonOpen . ')">
        <i class="fas fa-folder-open"></i>
        <span class="ttiptext">Abrir</span>
    </button>';

    if($row->estadoCotizacion !== 'Anulada') {
        $jsonAnular = htmlspecialchars(json_encode([
            'cotizacionId' => $row->cotizacionId
        ]));

        $acciones .= ' <button type="button" class="btn btn-danger btn-sm ttip" onclick="anularCotizacion(' . $jsonAnular . ')">
            <i class="fas fa-ban"></i>
            <span class="ttiptext">Anular</span>
        </button>';
    }

    $output['data'][] = [
        $n,
        $colId,
        $colCliente,
        $colSucursal,
        $colFecha,
        $colTotal,
        $colEstado,
        $acciones
    ];
}

if($n > 0) {
    echo json_encode($output);
} else {
    echo json_encode(['data' => '']);
}
