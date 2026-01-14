<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$datCate = $cloud->rows("
        SELECT
            inventarioCategoriaId,
            nombreCategoria
        FROM cat_inventario_categorias
        WHERE flgDelete = 0
    ");

$n = 0;
foreach ($datCate as $datCate) {
    $n += 1;
    $categoria = '<b><i class="fas fa-tag"></i> Categoría: </b>' . $datCate->nombreCategoria;

    $jsonEspecificacion = array("inventarioCategoriaId" => $datCate->inventarioCategoriaId);

    $funtionEspec = htmlspecialchars(json_encode($jsonEspecificacion));

    $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalCategoria(`update^' . $datCate->inventarioCategoriaId . '`);">
                    <i class="fas fa-pen"></i>
                    <span class="ttiptext">Editar</span>
                </button>
                <button type="button" class="btn btn-primary btn-sm ttip" onClick="modalEsp(' . $funtionEspec . ');">
                    <i class="fas fa-list"></i>
                    <span class="ttiptext">Especificaciones</span>
                </button>
                <button type="button" class="btn btn-danger btn-sm ttip" onClick="eliminarCategoria(`' . $datCate->inventarioCategoriaId . '^' . $datCate->nombreCategoria . '`);">
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