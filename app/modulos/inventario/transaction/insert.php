<?php
if (isset($_SESSION["usuarioId"]) && isset($operation)) {
  switch ($operation) {
    case "adjuntoProducto":
      $productoId = $_POST["productoId"];
      $tipoAdjunto = $_POST["tipoProductoAdjunto"];
      $descripcion = $_POST["descripcionProductoAdjunto"];
      $archivo = $_FILES['archivoAdjunto'];
      $nombreOriginal = $archivo['name'];
      $directorioBase = "adjuntos-productos/";
      $dirProducto = "../../../../libraries/resources/images/" . $directorioBase . $productoId . "/";
      if (!file_exists($dirProducto)) {
        mkdir($dirProducto, 0755, true);
      }
      $ubicacion = $dirProducto . $nombreOriginal;
      $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
      $permitidoImagen = ["jpg", "jpeg", "png"];
      $permitidoDocs = ["pdf"];
      $permitidoVideo = ["mp4"];
      $permitidoGeneral = array_merge($permitidoImagen, $permitidoDocs, $permitidoVideo);
      $flgSubir = 1;
      switch ($tipoAdjunto) {
        case "imagen_referencia":
        case "imagen_catalogo":
        case "foto_real":
          if (!in_array($ext, $permitidoImagen))
            $flgSubir = 0;
          break;
        case "certificado":
        case "manual":
        case "boleta_compra":
          if (!in_array($ext, $permitidoDocs))
            $flgSubir = 0;
          break;
        case "video":
          if (!in_array($ext, $permitidoVideo))
            $flgSubir = 0;
          break;
        default:
          if (!in_array($ext, $permitidoGeneral))
            $flgSubir = 0;
          break;
      }
      if ($flgSubir == 0) {
        echo json_encode([
          "ok" => false,
          "msg" => "El formato ($ext) no es válido para el tipo de adjunto seleccionado."
        ]);
        break;
      }
      $n = 1;
      $finalName = $nombreOriginal;
      while (file_exists($ubicacion)) {
        $finalName = "(" . $n . ")" . $nombreOriginal;
        $ubicacion = $dirProducto . $finalName;
        $n++;
      }
      if (move_uploaded_file($archivo['tmp_name'], $ubicacion)) {
        $insert = [
          'productoId' => $productoId,
          'tipoProductoAdjunto' => $tipoAdjunto,
          'descripcionProductoAdjunto' => $descripcion,
          'urlProductoAdjunto' => $directorioBase . $productoId . "/" . $finalName
        ];
        $adjuntoId = $cloud->insert('prod_productos_adjuntos', $insert);
        $dataProd = $cloud->row(
          "SELECT nombreProducto FROM prod_productos WHERE productoId = ? AND flgDelete = 0",
          [$productoId]
        );
        $cloud->writeBitacora(
          "movInsert",
          "(" . date("Y-m-d H:i:s") . ") Subió un adjunto ($tipoAdjunto) al producto " . $dataProd->nombreProducto
        );
        echo 'success';
      } else {
        echo 'Hubo un problema al subir el archivo.';
      }
      break;
    case 'marca':
      $queryExist = "SELECT nombreMarca FROM cat_inventario_marcas
      WHERE nombreMarca = ? AND abreviaturaMarca = ? AND flgDelete = 0";
      $existe = $cloud->count($queryExist, [$_POST["nombreMarca"], $_POST["abreviaturaMarca"]]);
      if ($existe == 0) {
        if ($_POST['adjuntarLogo'] == "Si") {
          $imagenNombre = $_FILES['adjunto']['name'];
          $ubicacion = "../../../../libraries/resources/images/logos/marcas/" . $imagenNombre;
          $filename = "../../../../libraries/resources/images/logos/marcas/";
          $flgSubir = 1;
          $imagenFormato = pathinfo($ubicacion, PATHINFO_EXTENSION);
          $formatosPermitidos = array("jpg", "jpeg", "png");
          if (!in_array(strtolower($imagenFormato), $formatosPermitidos)) {
            $flgSubir = 0;
          } else {
            $flgSubir = 1;
          }
          if ($flgSubir == 0) {
            echo "El archivo seleccionado no coincide con una imagen. Por favor vuelva a seleccionar una imagen con formato válido.";
          } else {
            $n = 1;
            $originalNombre = $imagenNombre;
            while ($n > 0) {
              if (file_exists($ubicacion)) {
                $imagenNombre = "(" . $n . ")" . $originalNombre;
                $ubicacion = $filename . "/" . $imagenNombre;
                $n += 1;
              } else {
                $n = 0;
              }
            }
            if (move_uploaded_file($_FILES['adjunto']['tmp_name'], $ubicacion)) {
              $insert = [
                'nombreMarca' => $_POST['nombreMarca'],
                'abreviaturaMarca' => $_POST['abreviaturaMarca'],
                'urlLogoMarca' => 'logos/marcas/' . $imagenNombre,
                'estadoMarca' => 'Activa'
              ];
              $cloud->insert('cat_inventario_marcas', $insert);
              $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva Marca: " . $_POST["nombreMarca"] . ", ");
              echo "success";
            } else {
              echo "Problema al cargar la imagen. Por favor comuniquese con el departamento de Informática.";
            }
          }
        } else {
          $insert = [
            'nombreMarca' => $_POST['nombreMarca'],
            'abreviaturaMarca' => $_POST['abreviaturaMarca'],
            'estadoMarca' => 'Activa'
          ];
          $cloud->insert('cat_inventario_marcas', $insert);
          $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva Marca: " . $_POST["nombreMarca"] . ", Sin logo");
          echo "success";
        }
      } else {
        echo "La Marca: " . $_POST["nombreMarca"] . " ya existe en el catálogo.";
      }
      break;
    case 'categoria':
      $queryExist = "SELECT nombreCategoria FROM cat_inventario_categorias WHERE nombreCategoria = ? AND flgDelete = 0";
      $existe = $cloud->count($queryExist, [$_POST["nombreCategoria"]]);
      if ($existe == 0) {
        $insert = [
          'nombreCategoria' => $_POST['nombreCategoria'],
          'abreviaturaCategoria' => $_POST['abreviaturaCategoria'],
          'flgPrincipal' => isset($_POST['flgPrincipal']) ? 1 : 0
        ];
        $cloud->insert('cat_inventario_categorias', $insert);
        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva Categoría: " . $_POST["nombreCategoria"] . ", ");
        echo "success";
      } else {
        echo "La Categoría: " . $_POST["nombreCategoria"] . " ya existe en el catálogo.";
      }
      break;
    case 'ubicacion':
      $queryExist = "SELECT nombreUbicacion,codigoUbicacion FROM inv_ubicaciones WHERE codigoUbicacion = ? AND sucursalId = ? AND flgDelete = 0";
      $existe = $cloud->count($queryExist, [$_POST["codigoUbicacion"], $_POST["sucursalId"]]);
      if ($existe == 0) {
        $insert = [
          'codigoUbicacion' => $_POST['codigoUbicacion'],
          'nombreUbicacion' => $_POST['nombreUbicacion'],
          'sucursalId' => $_POST['sucursalId']
        ];
        $cloud->insert('inv_ubicaciones', $insert);
        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva ubicaciones: " . $_POST["nombreUbicacion"] . ", " . $_POST["codigoUbicacion"] . ", En la sucursal ");
        echo "success";
      } else {
        echo "La Ubicacion: " . $_POST["nombreUbicacion"] . " ya existe en el catálogo.";
      }
      break;
    case 'unidadCategoriaCategoria':
      $insert = [
        'catProdEspecificacionId' => $_POST['catProdEspecificacionId'],
        'inventarioCategoriaId' => $_POST['inventarioCategoriaId'],
        'esObligatoria' => isset($_POST['esObligatoria']) ? 1 : 0
      ];
      $cloud->insert('cat_categorias_especificaciones_obligatorias', $insert);
      $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva categoria por especificación: " . $_POST["inventarioCategoriaId"] . ", ");
      echo "success";
      break;
    case 'unidad-medida':
      $queryExist = "SELECT nombreUnidadMedidaFROM cat_unidades_medida WHERE nombreUnidadMedida = ? AND abreviaturaUnidadMedida = ? AND tipoMagnitud = ? AND flgDelete = 0";
      $abreviatura = str_replace('&', '^', $_POST['abreviatura']);
      $existe = $cloud->count($queryExist, [$_POST["nombreUnidadMedida"], $_POST['abreviatura'], $_POST['tipoMagnitud']]);
      if ($existe == 0) {
        $existeCodigoMH = $cloud->count("SELECT nombreUnidadMedida
        FROM cat_unidades_medida
        WHERE codigoMH = ?
        AND flgDelete = ?", [$_POST['codigoMH'], 0]);
        if ($existeCodigoMH == 0) {
          $insert = [
            'nombreUnidadMedida' => $_POST['nombreUnidadMedida'],
            'abreviaturaUnidadMedida' => $abreviatura,
            'tipoMagnitud' => $_POST['tipoMagnitud'],
            'codigoMH' => $_POST['codigoMH']
          ];
          $cloud->insert('cat_unidades_medida', $insert);
          $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva Unidad de Medida: " . $_POST["nombreUnidadMedida"] . ", ");
          echo "success";
        } else {
          echo "El código de Hacienda: $_POST[codigoMH] ya fue creado en otra unidad de medida";
        }
      } else {
        echo "La Unidad de Medida: " . $_POST["nombreUnidadMedida"] . " de tipo de magnitud " . $_POST['tipoMagnitud'] . ", ya existe en el catálogo.";
      }
      break;
    case "especificacion":
      $tipoMagnitud = NULL;
      if (!empty($_POST["tipoMagnitud"])) {
        $tipoMagnitud = $_POST["tipoMagnitud"];
      }
      $queryExist = "SELECT nombreProdEspecificacion
      FROM cat_productos_especificaciones
      WHERE tipoEspecificacion = ? AND nombreProdEspecificacion = ? AND tipoMagnitud = ? AND flgDelete = 0";
      $existe = $cloud->count($queryExist, [$_POST["tipoEspecificacionN"], $_POST['nombreEsp'], $tipoMagnitud]);
      if ($existe == 0) {
        $insert = [
          'tipoEspecificacion' => $_POST["tipoEspecificacionN"],
          'nombreProdEspecificacion' => $_POST["nombreEsp"],
          'tipoMagnitud' => $tipoMagnitud,
        ];
        $cloud->insert('cat_productos_especificaciones', $insert);
        $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó la especificacion " . $_POST["tipoEspecificacionN"] . ": " . $_POST["nombreEsp"] . ", ");
        echo "success";
      } else {
        echo "La especificación: " . $_POST["nombreEsp"] . ", ya existe en el catálogo.";
      }
      break;
    case "producto":
      if (empty($_POST["sku"])) {
        echo "error: el SKU es obligatorio";
        break;
      }
      $sku = $_POST["sku"];
      $skuExiste = $cloud->row("SELECT CodInterno FROM prod_productos WHERE CodInterno = ?", [$sku]);
      if ($skuExiste) {
        echo "error: el SKU ya está registrado";
        break;
      }
      $insertProducto = [
        "codFabricante" => $_POST["codFabricante"] ?? null,
        "CodInterno" => $sku,
        "inventarioCategoriaPrincipalId" => $_POST["categoria"],
        "nombreProducto" => $_POST["nombre"],
        "descripcionProducto" => $_POST["descripcion"] ?? null,
        "marcaId" => $_POST["marcaId"],
        "unidadMedidaId" => $_POST["udm"],
        "tipoProductoId" => $_POST["tipo"],
        "estadoProducto" => 'Suspendido',
        "paisIdOrigen" => $_POST["pais"] ?? null
      ];
      $productoId = $cloud->insert('prod_productos', $insertProducto);
      if ($productoId <= 0) {
        echo "error: no se pudo insertar el producto";
        break;
      }
      $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó el producto " . $_POST["nombre"]);
      if (!empty($_POST["tags"])) {
        foreach ($_POST["tags"] as $t) {
          $cloud->insert("prod_productos_categorias", [
            "productoId" => $productoId,
            "inventarioCategoriaId" => $t
          ]);
        }
      }
      if (!empty($_POST["especificaciones"])) {
        foreach ($_POST["especificaciones"] as $esp) {
          if (empty($esp["nombre"]) || empty($esp["valor"])) {
            continue;
          }
          $cloud->insert("prod_productos_especificaciones", [
            "productoId" => $productoId,
            "catProdEspecificacionId" => $esp["id"],
            "valorEspecificacion" => $esp["valor"],
            "unidadMedidaId" => $esp["unidadMedida"] ?? null
          ]);
        }
      }
      if (!empty($_POST["sucursalId"]) && !empty($_POST["inventarioUbicacionId"])) {
        $cloud->insert("inventarioUbicacionId", [
          "inventarioUbicacionId" => $_POST["inventarioUbicacionId"],
          "productoId" => $productoId,
          "existenciaProducto" => 0,
        ]);
        $cloud->update("prod_productos", [
          "estadoProducto" => 'Activo',
        ], [
          "productoId" => $productoId
        ]);
      }
      echo "success";
      break;
    case 'traslado-interno':
      break;
    case 'precio-oro':

      $URL = "https://goldpricez.com/api/rates/currency/usd/measure/all";
      $apiKey = "ece41bb5c016b137d71cb874f895ab6aece41bb5";

      // 1) Llamar a la API
      $result = httpGet($URL, $apiKey);

      if ($result === null || $result === false) {
        echo "No se pudo obtener el precio del oro (sin respuesta de la API).";
        break;
      }

      // 2) Primer json_decode
      $decoded1 = json_decode($result, true);
      if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Error al decodificar la respuesta de la API (nivel 1): " . json_last_error_msg();
        break;
      }

      // 3) Segundo json_decode si todavía es string
      if (is_string($decoded1)) {
        $data = json_decode($decoded1, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
          echo "Error al decodificar la respuesta de la API (nivel 2): " . json_last_error_msg();
          break;
        }
      } else {
        if (!is_array($decoded1)) {
          echo "Formato inesperado en la respuesta de la API.";
          break;
        }
        $data = $decoded1;
      }

      // 4) Extraer datos en onzas
      $ounceAsk = isset($data['ounce_price_ask']) ? (float) $data['ounce_price_ask'] : null;
      $ounceBid = isset($data['ounce_price_bid']) ? (float) $data['ounce_price_bid'] : null;
      $ounceHigh = isset($data['ounce_price_usd_today_high']) ? (float) $data['ounce_price_usd_today_high'] : null;
      $ounceLow = isset($data['ounce_price_usd_today_low']) ? (float) $data['ounce_price_usd_today_low'] : null;
      $ounceLast = isset($data['ounce_price_usd']) ? (float) $data['ounce_price_usd'] : null;

      if ($ounceLast === null) {
        echo "La API no devolvió el campo ounce_price_usd.";
        break;
      }

      // Usamos el último precio como Open y Close
      $precioAskOz = $ounceAsk;
      $precioBidOz = $ounceBid;
      $precioHighOz = $ounceHigh;
      $precioLowOz = $ounceLow;
      $precioOpenOz = $ounceLast;
      $precioCloseOz = $ounceLast;

      // 5) Formatear a DECIMAL(18,6) (cuidando los null)
      $precioBidSql = $precioBidOz !== null ? number_format($precioBidOz, 6, '.', '') : null;
      $precioAskSql = $precioAskOz !== null ? number_format($precioAskOz, 6, '.', '') : null;
      $precioOpenSql = $precioOpenOz !== null ? number_format($precioOpenOz, 6, '.', '') : null;
      $precioCloseSql = $precioCloseOz !== null ? number_format($precioCloseOz, 6, '.', '') : null;
      $precioHighSql = $precioHighOz !== null ? number_format($precioHighOz, 6, '.', '') : null;
      $precioLowSql = $precioLowOz !== null ? number_format($precioLowOz, 6, '.', '') : null;

      // 6) Insertar en tu tabla
      $monedaUsdId = 1;  // id de USD en tu catálogo
      $unidadMedidaOnzaTroyId = 50; // id de "Onza troy" en tu catálogo de unidades

      $cloud->insert("bit_historial_precios_metal", [
        "metal" => "ORO",
        "precioBid" => $precioBidSql,
        "precioAsk" => $precioAskSql,
        "precioOpen" => $precioOpenSql,
        "precioClose" => $precioCloseSql,
        "precioHigh" => $precioHighSql,
        "precioLow" => $precioLowSql,
        "monedaId" => $monedaUsdId,
        "fuente" => "goldpricez",
        "unidadMedidaId" => $unidadMedidaOnzaTroyId,
      ]);

      echo 'success';
      break;
    default:
      echo "No se encontró la operación.";
      break;
  }
} else {
  header("Location: /alina-cloud/app/");
}
?>