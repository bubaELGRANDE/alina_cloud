<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$productoId = (int) ($_POST["productoId"] ?? 0);
if ($productoId <= 0) {
    echo json_encode(["error" => "productoId inválido"]);
    exit;
}

// Ajustá joins si tus tablas se llaman diferente
$p = $cloud->row("
  SELECT
    p.productoId,
    p.codFabricante,
    p.codInterno,
    p.inventarioCategoriaPrincipalId,
    p.nombreProducto,
    p.descripcionProducto,
    p.marcaId,
    p.unidadMedidaId,
    p.tipoProductoId,
    p.paisIdOrigen,
    p.obsEstadoProducto,

    c.nombreCategoria AS categoriaText,
    m.nombreMarca AS marcaText,
    u.nombreUnidadMedida AS udmText,
    pa.pais AS paisText
  FROM prod_productos p
  LEFT JOIN cat_inventario_categorias c ON c.inventarioCategoriaId = p.inventarioCategoriaPrincipalId
  LEFT JOIN cat_inventario_marcas m ON m.marcaId = p.marcaId
  LEFT JOIN cat_unidades_medida u ON u.unidadMedidaId = p.unidadMedidaId
  LEFT JOIN cat_paises pa ON pa.paisId = p.paisIdOrigen
  WHERE p.productoId = ? AND p.flgDelete = 0
", [$productoId]);

if (!$p) {
    echo json_encode(["error" => "producto no existe"]);
    exit;
}

// tags
$tags = $cloud->rows("
  SELECT pc.inventarioCategoriaId AS id, ci.nombreCategoria AS text
  FROM prod_productos_categorias pc
  LEFT JOIN cat_inventario_categorias ci ON ci.inventarioCategoriaId = pc.inventarioCategoriaId
  WHERE pc.productoId = ? AND pc.flgDelete = 0
", [$productoId]);

// especificaciones guardadas
$esps = $cloud->rows("
  SELECT
    pe.catProdEspecificacionId,
    pe.valorEspecificacion,
    pe.unidadMedidaId
  FROM prod_productos_especificaciones pe
  WHERE pe.productoId = ? AND pe.flgDelete = 0
", [$productoId]);

echo json_encode([
    "productoId" => $p->productoId,
    "codFabricante" => $p->codFabricante,
    "codInterno" => $p->codInterno,
    "inventarioCategoriaPrincipalId" => $p->inventarioCategoriaPrincipalId,
    "categoriaText" => $p->categoriaText ?? null,

    "nombreProducto" => $p->nombreProducto,
    "descripcionProducto" => $p->descripcionProducto,
    "marcaId" => $p->marcaId,
    "marcaText" => $p->marcaText ?? null,

    "unidadMedidaId" => $p->unidadMedidaId,
    "udmText" => $p->udmText ?? null,

    "tipoProductoId" => $p->tipoProductoId,
    "paisIdOrigen" => $p->paisIdOrigen,
    "paisText" => $p->paisText ?? null,

    "obsEstadoProducto" => $p->obsEstadoProducto,

    // si tenés relación de sucursal/ubicación, devolvelas aquí
    "sucursalId" => null,
    "sucursalText" => null,
    "inventarioUbicacionId" => null,
    "ubicacionText" => null,

    "tags" => $tags ?: [],
    "especificaciones" => $esps ?: []
]);
 