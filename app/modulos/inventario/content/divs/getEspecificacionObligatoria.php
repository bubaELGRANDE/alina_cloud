<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$inventarioCategoriaId = $_POST["inventarioCategoriaId"] ?? 0;

$data = $cloud->rows("SELECT o.inventarioCategoriaId,o.catProdEspecificacionId,o.ordenSKU,o.esObligatoria,ep.tipoEspecificacion,ep.nombreProdEspecificacion,ep.tipoMagnitud
FROM cat_categorias_especificaciones_obligatorias o
LEFT JOIN cat_productos_especificaciones ep ON ep.catProdEspecificacionId = o.catProdEspecificacionId
WHERE o.flgDelete = ? AND O.inventarioCategoriaId = ?", [0, $inventarioCategoriaId]);

if (!empty($data)) {
    foreach ($data as $item) {
        $udm[] = array(
            "id" => $item->catProdEspecificacionId,
            "ordenSKU" => $item->ordenSKU,
            "esObligatoria" => $item->esObligatoria,
            "tipoEspecificacion" => $item->tipoEspecificacion,
            "nombreProdEspecificacion" => $item->nombreProdEspecificacion,
            "tipoMagnitud" => $item->tipoMagnitud,
        );
    }
    echo json_encode($udm);
} else {
    echo json_encode(array("id" => NULL, "text" => "Sin resultados"));
}
?>