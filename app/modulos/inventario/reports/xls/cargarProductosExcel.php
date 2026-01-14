<?php
require_once('../../../../../libraries/includes/logic/mgc/datos94.php');
require_once('../../../../../libraries/packages/php/vendor/autoload-spreadsheet.php');
require_once("../../../../../libraries/includes/logic/functions/funciones-inventario.php");

@session_start();
error_reporting(E_ALL & ~E_DEPRECATED);

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

if (isset($_FILES['adjunto'])) {
    try {
        $fileTmpPath = $_FILES['adjunto']['tmp_name'];
        $spreadsheet = IOFactory::load($fileTmpPath);
        $sheet = $spreadsheet->getActiveSheet();

        // Leer datos del Excel (desde la fila 2)
        $data = [];
        foreach ($sheet->getRowIterator(2) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $value = $cell->getCalculatedValue();
                if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                    $rowData[] = $value->getPlainText();
                } else {
                    $rowData[] = $value;
                }
            }

            // omitir filas totalmente vacías
            if (array_filter($rowData)) {
                $data[] = $rowData;
            }
        }

        $errores = [];
        $itemsValidos = [];
        $skusEnArchivo = [];

        // Validaciones similares al método de partidas
        if (!empty($data) && isset($data[0][0]) && trim($data[0][0]) !== '') {

            foreach ($data as $index => $row) {
                $filaExcel = $index + 2; // porque empezamos a leer en la fila 2

                // Mapear columnas (ajusta si tu plantilla cambia)
                $sku = isset($row[0]) ? trim((string) $row[0]) : '';   // SKU
                $nombre = isset($row[1]) ? trim((string) $row[1]) : '';   // NOMBRE
                $categoria = isset($row[2]) ? trim((string) $row[2]) : '';   // CATEGORIA
                $marca = isset($row[3]) ? trim((string) $row[3]) : '';   // MARCA

                $purezaValor = isset($row[4]) ? trim((string) $row[4]) : '';   // PUREZA
                $peso = isset($row[5]) && $row[5] !== '' ? floatval($row[5]) : 0; // PESO
                $ancho = isset($row[6]) && $row[6] !== '' ? floatval($row[6]) : 0; // ANCHO
                $largo = isset($row[7]) && $row[7] !== '' ? floatval($row[7]) : 0; // LARGO
                $diametro = isset($row[8]) && $row[8] !== '' ? floatval($row[8]) : 0; // DIAMETRO

                $descripcion = isset($row[9]) ? trim((string) $row[9]) : ''; // DESCRIPCION
                $cantidad = isset($row[10]) ? intval($row[10]) : 0;       // CANTIDAD
                $ubicacion = isset($row[11]) ? trim((string) $row[11]) : ''; // UBICACION (si luego la usás)
                $observaciones = isset($row[12]) ? trim((string) $row[12]) : ''; // OBSERVACIONES

                // -------- VALIDACIONES POR FILA --------

                // 1. SKU vacío
                if ($sku === '') {
                    $errores[] = "Fila #$filaExcel: el SKU está vacío.";
                    continue;
                }

                // 2. SKU repetido dentro del MISMO archivo
                if (in_array($sku, $skusEnArchivo)) {
                    $errores[] = "Fila #$filaExcel (SKU $sku): el SKU está repetido en el archivo Excel.";
                    continue;
                } else {
                    $skusEnArchivo[] = $sku;
                }

                // 3. Categoria y marca existentes
                $categoriaId = getCategoriaIdByName($categoria, $cloud);
                if (!$categoriaId) {
                    $errores[] = "Fila #$filaExcel (SKU $sku): la categoría '$categoria' no existe.";
                    continue;
                }

                $marcaId = getMarcaIdByName($marca, $cloud);
                if (!$marcaId) {
                    $errores[] = "Fila #$filaExcel (SKU $sku): la marca '$marca' no existe.";
                    continue;
                }

                // 4. SKU ya existe en la BD
                $skuExiste = $cloud->row(
                    "SELECT CodInterno FROM prod_productos WHERE CodInterno = ?",
                    [$sku]
                );

                if ($skuExiste) {
                    $errores[] = "Fila #$filaExcel (SKU $sku): el SKU ya está registrado en la base de datos.";
                    continue;
                }

                // Si pasa todas las validaciones, lo guardamos como item válido
                $itemsValidos[] = [
                    'filaExcel' => $filaExcel,
                    'sku' => $sku,
                    'nombre' => $nombre,
                    'categoriaId' => $categoriaId,
                    'marcaId' => $marcaId,
                    'purezaValor' => $purezaValor,
                    'peso' => $peso,
                    'ancho' => $ancho,
                    'largo' => $largo,
                    'diametro' => $diametro,
                    'descripcion' => $descripcion,
                    'cantidad' => $cantidad,
                    'ubicacion' => $ubicacion,
                    'observaciones' => $observaciones,
                ];
            }
        } else {
            $errores[] = 'El archivo no contiene datos o el primer ítem no tiene SKU.';
        }

        // Si hay errores, no insertamos nada (igual que en partidas)
        if (!empty($errores)) {
            echo 'El archivo no contiene datos válidos:<br>' . implode('<br>', $errores);
            exit;
        }

        if (empty($itemsValidos)) {
            echo 'El archivo no contiene filas válidas para insertar productos.';
            exit;
        }

        // -------- INSERCIÓN EN BD (solo si no hubo errores) --------
        $skusInsertados = [];

        foreach ($itemsValidos as $item) {

            // Insertar producto
            $insertProducto = [
                "CodInterno" => $item['sku'],
                "inventarioCategoriaPrincipalId" => $item['categoriaId'],
                "nombreProducto" => $item['nombre'],
                "obsEstadoProducto" => $item['observaciones'],
                "descripcionProducto" => $item['descripcion'] !== '' ? $item['descripcion'] : null,
                "marcaId" => $item['marcaId'],
                "unidadMedidaId" => 1,
                "tipoProductoId" => 1,
                "estadoProducto" => 'Activo'
            ];

            $productoId = $cloud->insert('prod_productos', $insertProducto);

            if (!$productoId) {
                // Si falla uno, avisamos y paramos
                echo 'Se produjo un error al insertar el producto de la fila #' . $item['filaExcel'] . ' (SKU ' . $item['sku'] . ').';
                if (!empty($skusInsertados)) {
                    echo '<br><br>SKUs que sí se insertaron antes del error (' . count($skusInsertados) . '):<br>' .
                        implode(', ', $skusInsertados);
                }
                exit;
            }

            // ESPECIFICACIONES

            // LARGO
            $cloud->insert("prod_productos_especificaciones", [
                "productoId" => $productoId,
                "catProdEspecificacionId" => 1,
                "valorEspecificacion" => $item['largo'],
                "unidadMedidaId" => 9 // mm
            ]);

            // PESO
            $cloud->insert("prod_productos_especificaciones", [
                "productoId" => $productoId,
                "catProdEspecificacionId" => 5,
                "valorEspecificacion" => $item['peso'],
                "unidadMedidaId" => 52 // ajusta si corresponde
            ]);

            // ANCHO
            $cloud->insert("prod_productos_especificaciones", [
                "productoId" => $productoId,
                "catProdEspecificacionId" => 2,
                "valorEspecificacion" => $item['ancho'],
                "unidadMedidaId" => 9  // mm
            ]);

            // DIÁMETRO
            $cloud->insert("prod_productos_especificaciones", [
                "productoId" => $productoId,
                "catProdEspecificacionId" => 4,
                "valorEspecificacion" => $item['diametro'],
                "unidadMedidaId" => 9  // mm
            ]);

            // PUREZA
            $cloud->insert("prod_productos_especificaciones", [
                "productoId" => $productoId,
                "catProdEspecificacionId" => 6,
                "valorEspecificacion" => $item['purezaValor'],
                "unidadMedidaId" => 67   // ajusta según tu catálogo
            ]);

            // UBICACIÓN Y CANTIDAD INICIAL
            // Si todavía no manejás la columna "ubicacion", se deja la ubicación por defecto 5
            $cloud->insert("inv_ubicaciones_productos", [
                "inventarioUbicacionId" => 5, // UBICACION POR DEFECTO
                "productoId" => $productoId,
                "existenciaProducto" => $item['cantidad'],
            ]);

            //PRECIO VENTA POR DEFECTO
// Último precio del oro
            $precioMetal = $cloud->row("
    SELECT 
        unidadMedidaId,
        precioBid,
        precioAsk,
        precioOpen,
        precioClose,
        precioHigh
    FROM bit_historial_precios_metal
    WHERE metal = ?
    ORDER BY fhRegistro DESC
    LIMIT 1
", ['ORO']);

            $precioPorGramoOro = 0;

            // Solo si hay precio registrado
            if ($precioMetal) {

                $unidadMedidaPrecio = (int) $precioMetal->unidadMedidaId; // ej: 50 = Onza troy
                $precioBase = (float) $precioMetal->precioAsk; // puedes usar Bid/Cose, etc.

                // 4 = Kilogramo, 52 = Gramo (según tu catálogo)
                // 1 unidad de medida del precio = X kg

                $eqMetalKg = $cloud->row("SELECT valorEquivalencia
                FROM cat_equivalencia_unidad_medida
                WHERE unidadMedidaId = ?
                AND unidadMedidaIdEquivalencia = 4
                AND flgDelete = 0
                LIMIT 1", [$unidadMedidaPrecio]);

                // 1 gramo = Y kg
                $eqGramoKg = $cloud->row("SELECT valorEquivalencia
                FROM cat_equivalencia_unidad_medida
                WHERE unidadMedidaId = 52
                AND unidadMedidaIdEquivalencia = 4
                AND flgDelete = 0
                LIMIT 1");

                if ($eqMetalKg && $eqGramoKg) {

                    $kgPorUnidadMetal = (float) $eqMetalKg->valorEquivalencia; // ej: 1 oz t = 0.03110 kg
                    $kgPorGramo = (float) $eqGramoKg->valorEquivalencia; // 1 g = 0.001 kg

                    // Precio por kg = precio por unidad / kg por unidad
                    // Precio por gramo = precio por kg * kgPorGramo
                    if ($kgPorUnidadMetal > 0 && $kgPorGramo > 0) {
                        $precioPorKgOro = $precioBase / $kgPorUnidadMetal;
                        $precioPorGramoOro = $precioPorKgOro * $kgPorGramo;
                    }

                } else {
                    // Fallback: si no hay conversiones en la tabla, usar factor fijo onza troy -> gramo
                    // 1 oz t = 31.10348 g
                    // OJO: solo aplica si la unidadMedidaId del precio es Onza troy (50)
                    if ($unidadMedidaPrecio === 50) {
                        $precioPorGramoOro = $precioBase / 31.10348;
                    }
                }
            }

            // Porcentaje de ganancia sobre el costo (ejemplo 30%)
            $porcentajeGanancia = 0.30;

            // IVA (ejemplo 13%)
            $porcentajeIVA = 0.13;

            $costoMaterial = 0.0;
            $precioVenta = 0.0;
            $precioVentaIVA = 0.0;

            // Solo calculamos si tenemos precio del oro por gramo y el producto tiene peso
            if ($precioPorGramoOro > 0 && $item['peso'] > 0) {

                // Pureza viene del Excel (columna PUREZA) y la guardaste como 'purezaValor'
                // Unidad de esa especificación es 67 = Quilates, así que asumimos que es un valor 10, 14, 18, 24, etc.
                $quilates = (float) $item['purezaValor'];

                if ($quilates > 0) {
                    // 24k = oro puro
                    $factorPureza = min($quilates, 24) / 24.0;
                } else {
                    // Si viene vacío o 0, asumimos puro (ajústalo a tu regla de negocio)
                    $factorPureza = 1.0;
                }

                // Costo del material de oro = peso en gramos * precio por gramo * factor de pureza
                $costoMaterial = $item['peso'] * $precioPorGramoOro * $factorPureza;

                // Precio de venta = costo + ganancia
                $precioVenta = $costoMaterial * (1 + $porcentajeGanancia);

                // Precio de venta con IVA
                $precioVentaIVA = $precioVenta * (1 + $porcentajeIVA);
            }

            // Insertar precio del producto
            $cloud->insert("prod_productos_precios", [
                "productoId" => $productoId,
                "personaId" => $_SESSION['personaId'],
                "precioVenta" => round($precioVenta, 6),
                "precioVentaIVA" => round($precioVentaIVA, 6),
                "costoUnitarioFOB" => round($costoMaterial, 6), // costo base del material
                "costoPromedio" => round($costoMaterial, 6), // al inicio igual al FOB
                "estadoPrecio" => 'Activo',
            ]);

            $skusInsertados[] = $item['sku'];
        }

        // -------- RESPUESTA FINAL --------
        echo 'success<br>';
        echo 'SKUs insertados correctamente (' . count($skusInsertados) . '):<br>' .
            implode(', ', $skusInsertados);

    } catch (Exception $e) {
        echo 'Error al leer el archivo Excel: ' . $e->getMessage();
    }
} else {
    echo 'No se ha subido ningún archivo Excel.';
}
?>