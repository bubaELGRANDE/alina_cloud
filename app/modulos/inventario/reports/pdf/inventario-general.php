<?php
@session_start();
ini_set('memory_limit', '-1');
ini_set("pcre.backtrack_limit", "10000000");

// ============================================================================
// 1. CONFIGURACIÓN E INICIALIZACIÓN
// ============================================================================
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
require_once('../../../../../libraries/packages/php/vendor/autoload.php');

$fechaActual = date("d/m/Y h:i A");
$usuario = $_SESSION['usuario'] ?? 'Sistema';

// ============================================================================
// 2. CAPA DE ACCESO A DATOS (Consultas SQL)
// ============================================================================
$sqlCatalogo = "
    SELECT 
        p.codFabricante,
        p.codInterno,
        p.nombreProducto,
        p.estadoProducto,
        c.nombreCategoria,
        t.nombreTipoProducto,
        IFNULL(m.nombreMarca, 'N/A') AS nombreMarca,
        u.nombreUnidadMedida,
        u.abreviaturaUnidadMedida
    FROM prod_productos p
    INNER JOIN cat_inventario_categorias c 
        ON p.inventarioCategoriaPrincipalId = c.inventarioCategoriaId
    INNER JOIN cat_inventario_tipos_producto t 
        ON p.tipoProductoId = t.tipoProductoId
    LEFT JOIN cat_inventario_marcas m 
        ON p.marcaId = m.marcaId
    INNER JOIN cat_unidades_medida u 
        ON p.unidadMedidaId = u.unidadMedidaId
    WHERE p.flgDelete = 0 
      AND c.flgDelete = 0 
      AND t.flgDelete = 0 
      AND u.flgDelete = 0
    ORDER BY c.nombreCategoria ASC, p.nombreProducto ASC
";

$dataProductos = $cloud->rows($sqlCatalogo, []);
// ============================================================================
// 3. CAPA DE PRESENTACIÓN (Lógica de vista y HTML)
// ============================================================================
$css = '
    <style>
        .header-img { width: 25%; text-align: left; vertical-align: middle; }
        .header-center { vertical-align: middle; text-align: center; width: 50%; font-size: 10px; color: #000000; }
        .header-info { color: #333333; vertical-align: middle; text-align: right; width: 25%; font-size: 9px; }
        
        /* Línea separadora principal en gris oscuro (sin rojo) */
        .header-table { width: 100%; border-bottom: 1px solid #333333; padding-bottom: 10px; margin-bottom: 15px; }
        .header-table th, .header-table td { border: none; }
        
        table { width: 100%; border-collapse: collapse; font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 9px; }
        /* Bordes sutiles en gris claro para la lectura formal */
        th, td { padding: 5px; border-bottom: 1px solid #cccccc; } 
        
        /* Encabezados de tabla: Fondo gris, texto negro, borde gris oscuro */
        thead th { text-align: left; background-color: #e6e6e6; color: #000000; font-weight: bold; border-bottom: 2px solid #333333; }
        
        /* Separadores de categoría recuperados: Fondo oscuro, texto blanco */
        .separador { font-size: 11px; font-weight: bold; width: 100%; background-color: #2c2c2c; color: #ffffff; padding: 6px; margin-top: 15px; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; }
        
        .text-center { text-align: center; }
        
        /* Estados en grises y negro para mantener la formalidad */
        .badge-activo { color: #000000; font-weight: bold; } 
        .badge-inactivo { color: #666666; font-weight: bold; } 
        
        .total-row { background-color: #e6e6e6; border-top: 2px solid #333333; }
    </style>
';

$html = '';

if (empty($dataProductos)) {
    $html = '<p style="text-align: center; font-family: sans-serif; margin-top: 20px;">No se encontraron productos registrados en el catálogo.</p>';
} else {
    $categoriaActual = '';
    $contadorTotal = 0;

    foreach ($dataProductos as $prod) {
        $estadoClase = (strtolower($prod->estadoProducto) === 'activo') ? 'badge-activo' : 'badge-inactivo';

        if ($categoriaActual !== $prod->nombreCategoria) {
            if ($categoriaActual !== '') {
                $html .= '</tbody></table>';
            }
            $categoriaActual = $prod->nombreCategoria;
            
            // Aquí se pinta el separador de categorías recuperado
            $html .= '<div class="separador">CATEGORÍA: ' . mb_strtoupper($categoriaActual, 'UTF-8') . '</div>';
            $html .= '<table>
                        <thead>
                            <tr>
                                <th style="width: 12%;">Cód. Interno</th>
                                <th style="width: 12%;">Cód. Fabricante</th>
                                <th style="width: 31%;">Nombre del Producto</th>
                                <th style="width: 15%;">Tipo</th>
                                <th style="width: 12%;">Marca</th>
                                <th style="width: 8%;">U.M.</th>
                                <th style="width: 10%;" class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>';
        }

        $html .= '<tr>
                    <td>' . $prod->codInterno . '</td>
                    <td>' . $prod->codFabricante . '</td>
                    <td>' . $prod->nombreProducto . '</td>
                    <td>' . $prod->nombreTipoProducto . '</td>
                    <td>' . $prod->nombreMarca . '</td>
                    <td>' . $prod->abreviaturaUnidadMedida . '</td>
                    <td class="text-center ' . $estadoClase . '">' . $prod->estadoProducto . '</td>
                  </tr>';
                  
        $contadorTotal++;
    }
    
    if ($categoriaActual !== '') {
        $html .= '</tbody></table>';
    }

    $html .= '<br><br>
              <table style="border: none; width: 100%;">
                <tr class="total-row">
                    <td style="padding: 8px; font-weight: bold; text-align: right; border: none;">Total de Códigos en Catálogo:</td>
                    <td style="padding: 8px; font-weight: bold; width: 10%; border: none;">' . $contadorTotal . '</td>
                </tr>
              </table>';
}

// ============================================================================
// 4. GENERACIÓN DEL PDF (Mpdf)
// ============================================================================
$mpdf = new \Mpdf\Mpdf([
    'format' => 'Letter',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 30,       
    'margin_bottom' => 15,
    'setAutoTopMargin' => 'stretch', 
    'autoMarginPadding' => 5, // Asegura un espacio limpio entre el encabezado y el contenido
    'shrink_tables_to_fit' => 1
]);

$mpdf->SetTitle("Catálogo General - Alina D'or");

// Header con altura de imagen bloqueada estrictamente a 55px (height="55")
// El único texto con color rojo (#8A151B) es el nombre de la marca.
$headerHtml = '
<table class="header-table">
    <tr>
        <td class="header-img">
            <img src="../../../../../libraries/resources/images/logos/alina-logo.png" height="55">
        </td>
        <td class="header-center">
            <h2 style="margin: 0; color: #8A151B; letter-spacing: 2px; text-transform: uppercase;">ALINA D\'OR</h2>
            <h3 style="margin: 5px 0 0 0; color: #2c2c2c; font-weight: normal;">Catálogo General de Productos</h3>
        </td>
        <td class="header-info">
            <p style="margin: 0;"><b>Fecha:</b> ' . $fechaActual . '</p>
            <p style="margin: 2px 0;"><b>Usuario:</b> ' . $usuario . '</p>
            <p style="margin: 0;">Página {PAGENO} de {nb}</p>
        </td>
    </tr>
</table>';

$mpdf->SetHTMLHeader($headerHtml);
$mpdf->debug = false;
$mpdf->showImageErrors = false;

// Escritura de contenido
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML(mb_convert_encoding($html, 'UTF-8', 'UTF-8'), \Mpdf\HTMLParserMode::HTML_BODY);

$mpdf->shrink_tables_to_fit = 1;
$mpdf->SetDisplayMode('fullpage', 'single');

$nombreArchivo = "Catalogo_AlinaDor_" . date('Ymd_Hi') . ".pdf";
$mpdf->Output($nombreArchivo, "I");
?>