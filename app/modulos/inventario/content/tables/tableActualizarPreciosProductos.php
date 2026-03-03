<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$fSku = isset($_POST['fSku']) ? trim($_POST['fSku']) : '';
$fNombre = isset($_POST['fNombre']) ? trim($_POST['fNombre']) : '';
$fCategoria = isset($_POST['fCategoria']) ? trim($_POST['fCategoria']) : '';
$fMarca = isset($_POST['fMarca']) ? trim($_POST['fMarca']) : '';

$where = " WHERE pe.estadoPrecio = :estado AND pe.flgDelete = 0 ";
$params = [':estado' => 'Activo'];

if ($fSku !== '') {
    $where .= " AND p.codInterno LIKE :sku ";
    $params[':sku'] = "%{$fSku}%";
}

if ($fNombre !== '') {
    $where .= " AND p.nombreProducto LIKE :nombre ";
    $params[':nombre'] = "%{$fNombre}%";
}

if ($fCategoria !== '') {
    $where .= " AND p.inventarioCategoriaPrincipalId = :cat ";
    $params[':cat'] = $fCategoria;
}

if ($fMarca !== '') {
    $where .= " AND p.marcaId = :marca ";
    $params[':marca'] = $fMarca;
}

$sql = "
  SELECT 
    p.productoId,
    p.nombreProducto,
    p.codInterno,
    c.nombreCategoria,
    m.nombreMarca,
    CONCAT(per.nombre1, ' ', per.apellido1) AS nombrePersona,
    pe.productoPrecioId,
    pe.precioVenta,
    pe.precioVentaIVA,
    pe.costoUnitarioFOB,
    pe.costoUnitario,
    pe.costoPromedio,
    pe.fhAdd
  FROM prod_productos_precios pe
  LEFT JOIN prod_productos p ON p.productoId = pe.productoId
  LEFT JOIN th_personas per ON per.personaId = pe.personaId
  LEFT JOIN cat_inventario_categorias c ON c.inventarioCategoriaId = p.inventarioCategoriaPrincipalId
  LEFT JOIN cat_inventario_marcas m ON m.marcaId = p.marcaId
  $where
";

$dataPrecio = $cloud->rows($sql, $params);

$n = 0;
$output = ['data' => []];

foreach ($dataPrecio as $p) {
    $n++;

    $productoId = (int) $p->productoId;
    $precioId = (int) $p->productoPrecioId;

    // Obtener especificaciones del producto (peso, material, quilate)
    // catProdEspecificacionId: 5 = Peso, 6 = otra especificación
    $especificaciones = $cloud->rows("
        SELECT 
            es.catProdEspecificacionId,
            es.valorEspecificacion,
            umd.abreviaturaUnidadMedida,
            epc.nombreProdEspecificacion
        FROM prod_productos_especificaciones es
        LEFT JOIN cat_unidades_medida umd ON umd.unidadMedidaId = es.unidadMedidaId
        LEFT JOIN cat_productos_especificaciones epc ON epc.catProdEspecificacionId = es.catProdEspecificacionId
        WHERE es.productoId = ? AND es.flgDelete = 0
    ", [$productoId]);

    // Extraer peso (catProdEspecificacionId = 5)
    $pesoValor = '';
    $pesoUnidad = '';
    // TODO: Agregar material y quilate cuando estén definidos
    // $materialValor = '';
    // $quilateValor = '';
    
    foreach ($especificaciones as $esp) {
        if ((int)$esp->catProdEspecificacionId === 5) {
            $pesoValor = $esp->valorEspecificacion;
            $pesoUnidad = $esp->abreviaturaUnidadMedida ?? '';
        }
        // TODO: Agregar más especificaciones aquí
        // if ((int)$esp->catProdEspecificacionId === X) { // Material
        //     $materialValor = $esp->valorEspecificacion;
        // }
        // if ((int)$esp->catProdEspecificacionId === Y) { // Quilate
        //     $quilateValor = $esp->valorEspecificacion;
        // }
    }

    // Col 1: Código
    $codigo = '<b><i class="fas fa-barcode"></i> Código:</b> ' . htmlspecialchars($p->codInterno ?? '') .
        '<br><b>ID:</b> ' . $productoId;

    // Col 2: Información general + Categoría + Marca
    $info = '<b>Descripción:</b> ' . htmlspecialchars($p->nombreProducto ?? '') .
        '<br><b>Categoría:</b> ' . htmlspecialchars($p->nombreCategoria ?: 'Sin categoría') .
        ' | <b>Marca:</b> ' . htmlspecialchars($p->nombreMarca ?: 'Sin marca');

    // Col 3: Características (Peso, Material, Quilate)
    $caracteristicas = '';
    if ($pesoValor !== '') {
        $caracteristicas .= '<b>Peso:</b> ' . htmlspecialchars($pesoValor) . ' ' . htmlspecialchars($pesoUnidad);
    } else {
        $caracteristicas .= '<span class="text-muted">Sin peso</span>';
    }
    // TODO: Agregar material y quilate
    // $caracteristicas .= '<br><b>Material:</b> ' . ($materialValor ?: '-');
    // $caracteristicas .= '<br><b>Quilate:</b> ' . ($quilateValor ?: '-');

    // Col 4: Costos
    $costoProm = (float) $p->costoPromedio;
    $costoUnit = (float) $p->costoUnitario;
    $costoBase = $costoProm > 0 ? $costoProm : $costoUnit;
    
    $costo = '<b>Costo promedio:</b> $' . number_format($costoProm, 2) .
        '<br><b>Último costo:</b> $' . number_format($costoUnit, 2);

    // Calcular precio mínimo (costo + 1% utilidad)
    $precioMinimo = $costoBase * 1.01;
    $precioMinimoFormateado = number_format($precioMinimo, 2, '.', '');

    // Col 5: Precio venta Actual SIN IVA (INPUT editable)
    $precioActualSinIVA = (float) $p->precioVenta;
    $inputPrecio = '
        <div class="input-group input-group-sm">
            <span class="input-group-text">$</span>
            <input type="number"
                class="form-control form-control-sm input-precio-venta"
                id="pv_' . $precioId . '"
                data-precio-id="' . $precioId . '"
                data-producto-id="' . $productoId . '"
                data-costo="' . number_format($costoBase, 2, '.', '') . '"
                data-precio-minimo="' . $precioMinimoFormateado . '"
                step="0.01"
                min="' . $precioMinimoFormateado . '"
                value="' . number_format($precioActualSinIVA, 2, '.', '') . '"
                onchange="validarPrecioMinimo(this)"
                onkeyup="calcularPrecioConIVA(this)">
        </div>
        <small class="text-muted">Mín: $' . $precioMinimoFormateado . '</small>';

    // Col 6: Precio con IVA (calculado automáticamente)
    $precioConIVA = $precioActualSinIVA * 1.13;
    $precioIVAHtml = '<span id="pv_iva_' . $precioId . '" class="fw-bold text-success">$' . number_format($precioConIVA, 2) . '</span>' .
        '<br><small class="text-muted">IVA 13%</small>';

    // Col 7: Precio sugerido +1% utilidad
    $sugeridoSinIva = $costoBase * 1.01;
    $sugeridoConIva = $sugeridoSinIva * 1.13;
    $precioSugerido = '<b>$' . number_format($sugeridoSinIva, 2) . '</b>' .
        '<br><small>Con IVA: $' . number_format($sugeridoConIva, 2) . '</small>';

    // Acciones: botón actualizar + histórico
    $payload = htmlspecialchars(json_encode([
        'productoPrecioId' => $precioId,
        'productoId' => $productoId,
        'costoBase' => $costoBase,
        'precioMinimo' => $precioMinimo
    ]));

    $acciones = '
    <button type="button" class="btn btn-success btn-sm ttip" onclick="actualizarPrecioVenta(' . $payload . ')">
      <i class="fas fa-save"></i>
      <span class="ttiptext">Actualizar</span>
    </button>
    <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalCategoria(' . $payload . ')">
      <i class="fas fa-clock"></i>
      <span class="ttiptext">Histórico</span>
    </button>
  ';

    // 9 columnas
    $output['data'][] = [
        $n,               // # (0)
        $codigo,          // Código (1)
        $info,            // Información general (2)
        $caracteristicas, // Características: Peso, Material, Quilate (3)
        $costo,           // Costos (4)
        $inputPrecio,     // Precio venta SIN IVA - INPUT (5)
        $precioIVAHtml,   // Precio CON IVA calculado (6)
        $precioSugerido,  // Precio sugerido +1% (7)
        $acciones         // Acciones (8)
    ];
}

echo json_encode($output);
