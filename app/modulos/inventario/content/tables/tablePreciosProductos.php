<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

// Filtros recibidos por POST
$fSku = isset($_POST['fSku']) ? trim($_POST['fSku']) : '';
$fNombre = isset($_POST['fNombre']) ? trim($_POST['fNombre']) : '';
$fCategoria = isset($_POST['fCategoria']) ? trim($_POST['fCategoria']) : '';
$fMarca = isset($_POST['fMarca']) ? trim($_POST['fMarca']) : '';

$where = " WHERE pe.estadoPrecio = 'Activo' AND pe.flgDelete = 0 ";
$params = [];

// SKU (codInterno)
if ($fSku !== '') {
    $where .= " AND p.codInterno LIKE ? ";
    $params[] = "%" . $fSku . "%";
}

// Nombre / descripción del producto
if ($fNombre !== '') {
    $where .= " AND p.nombreProducto LIKE ? ";
    $params[] = "%" . $fNombre . "%";
}

// Categoría principal (por ID)
if ($fCategoria !== '') {
    $where .= " AND p.inventarioCategoriaPrincipalId = ? ";
    $params[] = $fCategoria;
}

// Marca (por ID)
if ($fMarca !== '') {
    $where .= " AND p.marcaId = ? ";
    $params[] = $fMarca;
}

$sql = "
    SELECT 
        p.productoId,
        p.nombreProducto,
        p.codInterno,
        c.inventarioCategoriaId,
        c.nombreCategoria,
        m.marcaId,
        m.nombreMarca,
        CONCAT(per.nombre1, ' ', per.apellido1) AS nombrePersona,
        pe.estadoPrecio,
        pe.precioVenta,
        pe.precioVentaIVA,
        pe.costoUnitarioFOB,
        pe.costoUnitario,
        pe.costoPromedio,
        pe.fhAdd
    FROM prod_productos_precios pe
        LEFT JOIN prod_productos p 
            ON p.productoId = pe.productoId
        LEFT JOIN th_personas per 
            ON per.personaId = pe.personaId
        LEFT JOIN cat_inventario_categorias c 
            ON c.inventarioCategoriaId = p.inventarioCategoriaPrincipalId
        LEFT JOIN cat_inventario_marcas m 
            ON m.marcaId = p.marcaId
    $where
";

$dataPrecio = $cloud->rows($sql, $params);

$n = 0;
$output = ['data' => []];

foreach ($dataPrecio as $p) {
    $n++;

    $codigo = '<b><i class="fas fa-barcode"></i> Código: </b>' . $p->codInterno;

    // TODO: aquí va en UNA SOLA LÍNEA: NOMBRE – CATEGORÍA – MARCA
    $producto = '<b>Descripción: </b>' . $p->nombreProducto
        . '<br><b>Categoria: </b>' . ($p->nombreCategoria ?: 'Sin categoría')
        . '     <b>Marca: </b>' . ($p->nombreMarca ?: 'Sin marca');

    $costo = '<b>Costo promedio: </b>$' . number_format($p->costoPromedio, 2)
        . '<br><b>Último costo FOB: </b>$' . number_format($p->costoUnitarioFOB, 2)
        . '<br><b>Último costo: </b>$' . number_format($p->costoUnitario, 2);

    $precio = '<b>Sin IVA: </b>$' . number_format($p->precioVenta, 2)
        . '<br><b>Con IVA: </b>$' . number_format($p->precioVentaIVA, 2);

    $datos = '<b>Actualizado: </b>' . $p->fhAdd
        . '<br><b>Responsable: </b>' . ($p->nombrePersona ?: 'Sistema');

    $jsonEspecificacion = array("productoId" => $p->productoId);
    $funtionEspec = htmlspecialchars(json_encode($jsonEspecificacion));

    $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalCategoria(' . $funtionEspec . ');">
                    <i class="fas fa-pen"></i>
                    <span class="ttiptext">Historico</span>
                </button>';

    $output['data'][] = array(
        $n,         // 0 #
        $codigo,    // 1 Código
        $producto,  // 2 Producto (nombre – categoría – marca)
        $costo,     // 3 Costos
        $precio,    // 4 Precio
        $datos,     // 5 Datos
        $acciones   // 6 Acciones
    );
}

echo json_encode($output);
