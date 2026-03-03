<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$term = isset($_POST['busquedaSelect']) ? trim($_POST['busquedaSelect']) : '';
$where = "";
$params = [];

if($term !== '') {
    $where = "AND (
        p.nombreProducto LIKE ? OR
        p.codInterno LIKE ? OR
        p.codFabricante LIKE ?
    )";
    $params = ["%$term%", "%$term%", "%$term%"];
}

$data = $cloud->rows(
    "SELECT
        p.productoId,
        p.codInterno,
        p.codFabricante,
        p.nombreProducto,
        m.nombreMarca,
        c.nombreCategoria,
        udm.abreviaturaUnidadMedida,
        pp.precioVenta,
        pp.precioVentaIVA,
        pp.costoPromedio
    FROM prod_productos p
    LEFT JOIN cat_inventario_marcas m ON m.marcaId = p.marcaId
    LEFT JOIN cat_inventario_categorias c ON c.inventarioCategoriaId = p.inventarioCategoriaPrincipalId
    LEFT JOIN cat_unidades_medida udm ON udm.unidadMedidaId = p.unidadMedidaId
    LEFT JOIN prod_productos_precios pp
        ON pp.productoId = p.productoId
        AND pp.estadoPrecio = 'Activo'
        AND pp.flgDelete = 0
    WHERE p.flgDelete = 0 $where
    ORDER BY p.nombreProducto ASC
    LIMIT 50",
    $params
);

if($data) {
    $json = [];
    foreach($data as $p) {
        $sku = $p->codInterno ?? ($p->codFabricante ?? '');
        $text = '(' . $sku . ') ' . ($p->nombreProducto ?? 'Producto');
        if(!is_null($p->nombreMarca) && $p->nombreMarca !== '') {
            $text .= ' - ' . $p->nombreMarca;
        }

        $json[] = [
            'id' => (int) $p->productoId,
            'text' => $text,
            'codInterno' => $p->codInterno,
            'nombreProducto' => $p->nombreProducto,
            'abreviaturaUnidadMedida' => $p->abreviaturaUnidadMedida,
            'precioVenta' => (float) ($p->precioVenta ?? 0),
            'precioVentaIVA' => (float) ($p->precioVentaIVA ?? 0),
            'costoPromedio' => (float) ($p->costoPromedio ?? 0)
        ];
    }
    echo json_encode($json);
} else {
    echo json_encode([['id' => null, 'text' => 'Sin resultados']]);
}
