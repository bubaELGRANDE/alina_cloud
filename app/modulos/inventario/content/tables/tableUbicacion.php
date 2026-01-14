<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$datCate = $cloud->rows("SELECT inventarioUbicacionId,nombreUbicacion,codigoUbicacion FROM inv_ubicaciones
WHERE sucursalId = ? AND flgDelete = 0", [$_POST["sucursalId"]]);

$n = 0;
foreach ($datCate as $dat) {
    $n += 1;
    $categoria = '<b><i class="fas fa-tag"></i> (' . $dat->nombreUbicacion . ')  </b> ' . $dat->codigoUbicacion;

    $jsonEspecificacion = array("inventarioUbicacionId" => $dat->inventarioUbicacionId);

    $funtionEspec = htmlspecialchars(json_encode($jsonEspecificacion));

    $acciones = '
                <button type="button" class="btn btn-danger btn-sm ttip">
                    <i class="fas fa-trash-alt"></i>
                    <span class="ttiptext">Eliminar</span>
                </button>';

    $output['data'][] = array(
        $n, // es #, se dibuja solo en el JS de datatable
        $categoria,
        $acciones
    );
} // foreach

if ($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data' => ''));
}