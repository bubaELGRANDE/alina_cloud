<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$productos = $cloud->rows("SELECT 
	p.productoId,
    p.codFabricante,
    p.codInterno,
    c.nombreCategoria AS categoriaPrincipal,
    p.nombreProducto,
    p.descripcionProducto,
    m.nombreMarca,
    udm.nombreUnidadMedida,
    udm.abreviaturaUnidadMedida,
    tp.nombreTipoProducto,
    p.estadoProducto,
    pa.abreviaturaPais,
    pa.pais,
    pa.iconBandera,
    abj.urlProductoAdjunto
FROM prod_productos p
LEFT JOIN cat_inventario_categorias c ON  c.inventarioCategoriaId = p.inventarioCategoriaPrincipalId
LEFT JOIN cat_unidades_medida udm ON udm.unidadMedidaId = p.unidadMedidaId
LEFT JOIN cat_inventario_marcas m ON m.marcaId = p.marcaId
LEFT JOIN cat_paises pa ON pa.paisId = p.paisIdOrigen
LEFT JOIN cat_inventario_tipos_producto tp ON tp.tipoProductoId = p.tipoProductoId
LEFT JOIN prod_productos_adjuntos abj ON abj.productoId = p.productoId AND abj.flgDelete = 0 AND abj.tipoProductoAdjunto = 'imagen_referencia'
WHERE p.flgDelete = 0");


$n = 0;
foreach ($productos as $p) {

    $especificacionData = $cloud->rows("SELECT es.valorEspecificacion,umd.abreviaturaUnidadMedida,epc.nombreProdEspecificacion
        FROM prod_productos_especificaciones es
        LEFT JOIN cat_unidades_medida umd ON umd.unidadMedidaId = es.unidadMedidaId
        LEFT JOIN cat_productos_especificaciones  epc ON epc.catProdEspecificacionId = es.catProdEspecificacionId
    WHERE es.catProdEspecificacionId IN (5,6) AND es.productoId = ?", [$p->productoId]);

    $n += 1;
    $sku = $p->codInterno ?? '';
    $nombre = $p->nombreProducto;
    $categoria = $p->categoriaPrincipal;
    $marca = $p->nombreMarca;
    $esp = '<b>Cod Interno:</b> ' . $p->productoId . '<br>';

    foreach ($especificacionData as $ps) {
        $esp .= '<b>' . $ps->nombreProdEspecificacion . ':</b> ' . $ps->valorEspecificacion . ' ' . $ps->abreviaturaUnidadMedida . '<br>';
        ;
    }

    $json = array("productoId" => $p->productoId);
    $funtion = htmlspecialchars(json_encode($json));
    $acciones = '<button type="button" class="btn btn-secondary btn-sm ttip" onClick="verAbjunto(' . $funtion . ');">
                    <i class="fas fa-file"></i>
                    <span class="ttiptext">Abjuntos</span>
                </button>
                <button type="button" class="btn btn-primary btn-sm ttip" onClick="verProducto(' . $funtion . ');">
                    <i class="fas fa-pencil"></i>
                    <span class="ttiptext">Editar</span>
                </button>';

    $output['data'][] = array(
        $n,
        $sku,
        $nombre,
        $categoria,
        $marca,
        $esp,
        $acciones
    );
} // foreach

if ($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data' => ''));
}

?>