<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$inventarioCategoriaId = $_POST["inventarioCategoriaId"] ?? 0;

$dataEspec = $cloud->rows("SELECT o.categoriaEspecificacionId,e.nombreProdEspecificacion,e.tipoEspecificacion,e.tipoMagnitud,o.esObligatoria FROM cat_categorias_especificaciones_obligatorias o
JOIN cat_productos_especificaciones e ON  o.catProdEspecificacionId = e.catProdEspecificacionId
WHERE o.inventarioCategoriaId = ? AND o.flgDelete = 0 AND e.flgDelete = 0", [$inventarioCategoriaId]);

$n = 0;
foreach ($dataEspec as $dat) {
    $n += 1;

    $esObligatoria = '';
    if($dat->esObligatoria){
        $esObligatoria = '<i class="fa fa-check-circle" aria-hidden="true"></i>';
    }
    $jsonEspecificacion = array("categoriaEspecificacionId" => $dat->categoriaEspecificacionId);
    $funtionEspec = htmlspecialchars(json_encode($jsonEspecificacion));
    $acciones = '<button type="button" class="btn btn-danger btn-sm ttip"
        onClick="eliminarCategoria(`' . $funtionEspec . ');">
        <i class="fas fa-trash-alt"></i><span class="ttiptext">Eliminar</span>
    </button>';

    $output['data'][] = array(
        $dat->tipoEspecificacion,
        $dat->nombreProdEspecificacion,
        $dat->tipoMagnitud,
        $esObligatoria,
        $acciones
    );
} // foreach

if ($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data' => ''));
}