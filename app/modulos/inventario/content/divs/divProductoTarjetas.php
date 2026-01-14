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
WHERE p.flgDelete = 0")

    ?>

<?php foreach ($productos as $p) {

    $jsonProducto = array("productoId" => $p->productoId);
    $funtionPro = htmlspecialchars(json_encode($jsonProducto));

    $especificacionData = $cloud->rows("SELECT es.valorEspecificacion,umd.abreviaturaUnidadMedida,epc.nombreProdEspecificacion
        FROM prod_productos_especificaciones es
        LEFT JOIN cat_unidades_medida umd ON umd.unidadMedidaId = es.unidadMedidaId
        LEFT JOIN cat_productos_especificaciones  epc ON epc.catProdEspecificacionId = es.catProdEspecificacionId
    WHERE es.catProdEspecificacionId IN (5,6) AND es.productoId = ?", [$p->productoId]);
    
    ?>
    <div class="col-md-4 col-lg-3">
        <div class="inv-card shadow-sm">
            <img src="../libraries/resources/images/<?= ($p->urlProductoAdjunto) ? $p->urlProductoAdjunto : 'adjuntos-productos/default.jpg' ?>"
                class="inv-img" alt="<?= $p->nombreProducto ?>">
            <div class="inv-body">
                <h6 class="fw-bold mb-0"><?= $p->nombreProducto ?></h6>
                <label class="mb-1 fst-italic"><?= $p->codInterno ?? '' ?></label><br>
                <small class="text-muted"><?= $p->categoriaPrincipal ?> · <?= $p->nombreMarca ?></small>

                <div class="mt-3 small">
                    <?php foreach ($especificacionData as $espec) { ?>
                        <div><strong><?= $espec->nombreProdEspecificacion ?>:</strong>
                            <?= $espec->valorEspecificacion . ' ' . $espec->abreviaturaUnidadMedida ?></div>
                        <?php
                    } ?>
                </div>
                <button class="btn btn-sm w-100 btn-info mt-3" onclick="verAbjunto(<?= $funtionPro ?>)">
                    Ver abjuntos
                </button>
                <button
                    onclick="changePage(`<?php echo $_SESSION['currentRoute']; ?>`, `gestion-productos`, `productoId=<?= $p->productoId ?>`)"
                    class="btn btn-sm w-100 btn-outline-primary mt-2">
                    Ver detalle
                </button>
            </div>
        </div>
    </div>
<?php } ?>