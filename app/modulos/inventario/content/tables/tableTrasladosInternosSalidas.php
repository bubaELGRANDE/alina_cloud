<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$yearBD     = $_POST['yearBD'];   // se recibe por si lo necesitas para filtrar por año
$sucursalId = $_POST['sucursalId']; // ahora este viene del select

$dataTrasladoInterno = $cloud->rows("
    SELECT 
        md.productoId AS productoId,
        v.nombreCompleto AS empleadoTraslado,
        ubentrada.nombreUbicacion AS nombreUbicacionEntrada,
        ubentrada.codigoUbicacion AS codigoUbicacionEntrada,
        ubsalida.nombreUbicacion AS nombreUbicacionSalida,
        ubsalida.codigoUbicacion AS codigoUbicacionSalida,
        m.fhSolicitud AS fhTraslado,
        udm.abreviaturaUnidadMedida AS abreviaturaUnidadMedida,
        p.nombreProducto AS nombreProducto,
        p.codInterno AS codInterno,
        md.cantidadRecibida AS cantidadTraslado,
        md.obsMovimientoDetalle AS obsTraslado,
        boSalida.codSucursalBodega AS codSucursalBodegaSalida,
        boSalida.bodegaSucursal  AS bodegaSucursalSalida,
        boEntrada.codSucursalBodega AS codSucursalBodegaEntrada,
        boEntrada.bodegaSucursal  AS bodegaSucursalEntrada
    FROM inv_movimiento_detalle md
    LEFT JOIN inv_movimientos m ON m.movimientoId = md.movimientoId
    LEFT JOIN inv_movimiento_persona mp ON mp.movimientoId = m.movimientoId
    LEFT JOIN view_expedientes v ON v.personaId = mp.personaId 
    LEFT JOIN inv_ubicaciones ubentrada ON ubentrada.inventarioUbicacionId = md.ubicacionProductoEntradaId
    LEFT JOIN cat_sucursales_bodegas boEntrada ON boEntrada.bodegaId = ubentrada.bodegaId
    LEFT JOIN inv_ubicaciones ubsalida ON ubsalida.inventarioUbicacionId = m.bodegaSalidaId
    LEFT JOIN cat_sucursales_bodegas boSalida ON boSalida.bodegaId = ubsalida.bodegaId 
    LEFT JOIN prod_productos p ON p.productoId = md.productoId
    LEFT JOIN cat_unidades_medida udm ON udm.unidadMedidaId = p.unidadMedidaId
    WHERE m.estadoMovimiento = ?
      AND boSalida.sucursalId = ?   -- usamos sucursalId en vez de bodegaId
      AND m.flgDelete = ?
", ['Finalizado', $sucursalId, 0]);

$output = ['data' => []];
$n = 0;

foreach ($dataTrasladoInterno as $dataTrasladoInterno) {
    $n++;

    $jsonProductoExistencias = array(
        "productoId"              => $dataTrasladoInterno->productoId,
        "codInterno"              => $dataTrasladoInterno->codInterno,
        "nombreProducto"          => $dataTrasladoInterno->nombreProducto,
        "abreviaturaUnidadMedida" => $dataTrasladoInterno->abreviaturaUnidadMedida
    );

    $productos = "<b></b>($dataTrasladoInterno->codInterno) $dataTrasladoInterno->nombreProducto";
    $cantidad  = "<div>$dataTrasladoInterno->cantidadTraslado $dataTrasladoInterno->abreviaturaUnidadMedida</div>";
    $ubicaciones = "
        <b><i class='fas fa-building'></i> Salida: </b>($dataTrasladoInterno->codSucursalBodegaSalida) $dataTrasladoInterno->bodegaSucursalSalida 
    ";
    $responsable = "<b><i class='fas fa-calendar-alt'></i> Fecha y hora: </b>" . date("d/m/Y H:i:s", strtotime($dataTrasladoInterno->fhTraslado)) . " <br> 
                    <b><i class='fas fa-user-tie'></i> Empleado: </b> $dataTrasladoInterno->empleadoTraslado";
    $justificacion = "<b><i class='fas fa-edit'></i> </b>$dataTrasladoInterno->obsTraslado";
    $acciones = '
        <button type="button" class="btn btn-primary btn-sm" onClick="modalProductoExistencias(' . htmlspecialchars(json_encode($jsonProductoExistencias)) . ');">
            <i class="fas fa-boxes"></i> Existencias
        </button>
    ';

    $output['data'][] = array(
        $n,
        $productos,
        $cantidad,
        $ubicaciones,
        $responsable,
        $justificacion,
        $acciones
    );
}

echo json_encode($output);
