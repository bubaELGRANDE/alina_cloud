<?php

if (isset($_SESSION["usuarioId"]) && isset($operation)) {
  switch ($operation) {
    case 'marca':
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
          $dataUrlArchivo = $cloud->row("
							SELECT
								urlLogoMarca
							FROM cat_inventario_marcas
							WHERE marcaId = ?
						", [$_POST["marcaId"]]);

          $oldUrl = "../../../../libraries/resources/images/" . $dataUrlArchivo->urlLogoMarca;

          if ($dataUrlArchivo->urlLogoMarca == "" || is_null($dataUrlArchivo->urlLogoMarca)) {

          } else {

            $arrayUrl = explode("/", $dataUrlArchivo->urlLogoMarca);

            $newUrl = "../../../../libraries/resources/images/logos/" . $arrayUrl[1] . "/ (REEMPLAZADA " . date("d_m_Y H_i_s") . ") " . $arrayUrl[2];

            rename($oldUrl, $newUrl);
          }


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

            $update = [
              'nombreMarca' => $_POST['nombreMarca'],
              'abreviaturaMarca' => $_POST['abreviaturaMarca'],
              'estadoMarca' => $_POST['estado'],
              'urlLogoMarca' => 'logos/marcas/' . $imagenNombre
            ];

            $where = ['marcaId' => $_POST['marcaId']];

            $cloud->update('cat_inventario_marcas', $update, $where);
            $cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la Marca: " . $_POST["nombreMarca"] . ", Cambió el logo por: " . $imagenNombre);

            echo "success";
          } else {
            echo "Problema al cargar la imagen. Por favor comuniquese con el departamento de Informática.";
          }
        }
      } else {
        $update = [
          'nombreMarca' => $_POST['nombreMarca'],
          'abreviaturaMarca' => $_POST['abreviaturaMarca'],
          'estadoMarca' => $_POST['estado']
        ];

        $where = ['marcaId' => $_POST['marcaId']];

        $cloud->update('cat_inventario_marcas', $update, $where);
        $cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la Marca: " . $_POST["nombreMarca"] . ", Sin cambiar logo.");

        echo "success";
      }
      break;

    case 'categoria':
      $queryExist = "
	        		SELECT 
	        			nombreCategoria
	        		FROM cat_inventario_categorias
	        		WHERE nombreCategoria = ? AND inventarioCategoriaId != ? AND flgDelete = 0
	        	";
      $existe = $cloud->count($queryExist, [$_POST["nombreCategoria"], $_POST['inventarioCategoriaId']]);
      if ($existe == 0) {
        $update = [
          'nombreCategoria' => $_POST['nombreCategoria'],
        ];
        $where = ['inventarioCategoriaId' => $_POST['inventarioCategoriaId']];

        $cloud->update('cat_inventario_categorias', $update, $where);
        $cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la Categoría: " . $_POST["nombreCategoria"] . ", ");

        echo "success";
      } else {
        echo "La Categoría: " . $_POST["nombreCategoria"] . " ya existe en el catálogo.";
      }
      break;

    case 'unidad-medida':

      $queryExist = "
	        		SELECT 
	        			nombreUnidadMedida
	        		FROM cat_unidades_medida
	        		WHERE nombreUnidadMedida = ? AND abreviaturaUnidadMedida = ? AND tipoMagnitud = ? AND unidadMedidaId != ? AND flgDelete = 0
	        	";
      $existe = $cloud->count($queryExist, [$_POST["nombreUnidadMedida"], $_POST['abreviatura'], $_POST['tipoMagnitud'], $_POST['unidadMedidaId']]);
      if ($existe == 0) {
        $existeCodigoMH = $cloud->count("
	        			SELECT nombreUnidadMedida FROM cat_unidades_medida
	        			WHERE codigoMH = ? AND unidadMedidaId <> ? AND flgDelete = ?
	        		", [$_POST['codigoMH'], $_POST['unidadMedidaId'], 0]);
        if ($existeCodigoMH == 0) {
          $update = [
            'nombreUnidadMedida' => $_POST['nombreUnidadMedida'],
            'abreviaturaUnidadMedida' => $_POST['abreviatura'],
            'tipoMagnitud' => $_POST['tipoMagnitud'],
            'codigoMH' => $_POST['codigoMH']
          ];
          $where = ['unidadMedidaId' => $_POST['unidadMedidaId']];

          $cloud->update('cat_unidades_medida', $update, $where);
          $cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Modificó la Unidad de Medida: " . $_POST["nombreUnidadMedida"] . ", ");

          echo "success";
        } else {
          echo "El código de Hacienda: $_POST[codigoMH] ya fue creado en otra unidad de medida";
        }
      } else {
        echo "La Unidad de Medida: " . $_POST["nombreUnidadMedida"] . " de tipo de magnitud " . $_POST['tipoMagnitud'] . ", ya existe en el catálogo.";
      }
      break;

    case 'habilitar-marca':

      $tableDataArray = explode('^', $_POST['id']);
      $update = [
        'estadoMarca' => 'Activa'
      ];
      $where = ['marcaId' => $tableDataArray[0]];

      $cloud->update('cat_inventario_marcas', $update, $where);
      $cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Habilitó la Marca: " . $tableDataArray[1] . ", ");

      echo "success";
      break;

    case 'equivalenciasUDM':
      $update = [
        'unidadMedidaId' => $_POST['udmId'],
        'unidadMedidaIdEquivalencia' => $_POST['udmEq'],
        'valorEquivalencia' => $_POST['valorEq'],
      ];
      $where = ['equivalenciaUnidadMedidaId' => $_POST['eqID']];

      $cloud->update('cat_equivalencia_unidad_medida', $update, $where);

      $data = $cloud->row("
				SELECT 
					eq.equivalenciaUnidadMedidaId,
					udm.nombreUnidadMedida AS unidadMedida,
					udm2.nombreUnidadMedida AS equivalenciaUDM,
					eq.valorEquivalencia,
					udm2.abreviaturaUnidadMedida
				FROM ((cat_equivalencia_unidad_medida eq
				JOIN cat_unidades_medida udm ON eq.unidadMedidaId = udm.unidadMedidaId)
				JOIN cat_unidades_medida udm2 ON eq.unidadMedidaIdEquivalencia = udm2.unidadMedidaId)
				WHERE eq.flgDelete = 0 AND eq.equivalenciaUnidadMedidaId = ?",
        [$_POST['eqID']]
      );

      $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Se editó la equivalencia de unidad de medida: (1 " . $data->unidadMedida . " = " . $data->valorEquivalencia . " " . $data->abreviaturaUnidadMedida . ", ");

      echo 'success';

      break;

    case "adjuntoProducto":


      $update = [
        'tipoProductoAdjunto' => $_POST["tipoAdjunto"],
        'descripcionProductoAdjunto' => $_POST["descripcionAdjunto"]
      ];
      $where = ['productoAdjuntoId' => $_POST["adjuntoProdId"]];
      $cloud->update('prod_productos_adjuntos', $update, $where);

      $dataProd = $cloud->row("SELECT nombreProducto FROM prod_productos WHERE flgDelete = 0 AND productoId = ? ", [$_POST["productoId"]]);
      $cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó la información del archivo: " . $_POST["tipoAdjunto"] . " para el producto " . $dataProd->nombreProducto . ", ");
      echo "success";

      break;

    case "especificacion":



      $tipoMagnitud = NULL;
      if (!empty($_POST["tipoMagnitud"])) {
        $tipoMagnitud = $_POST["tipoMagnitud"];
      }

      $update = [
        'tipoEspecificacion' => $_POST["tipoEspecificacionN"],
        'nombreProdEspecificacion' => $_POST["nombreEsp"],
        'tipoMagnitud' => $tipoMagnitud,
      ];
      $where = ['catProdEspecificacionId' => $_POST["especificacionId"]];
      $cloud->update('cat_productos_especificaciones', $update, $where);

      $cloud->writeBitacora("movInsert", "(" . $fhActual . ") Actualizó la especificacion: " . $_POST["nombreEsp"] . " (" . $_POST["tipoEspecificacionN"] . "), ");

      echo "success";
      break;
    case 'actualizar-costo-oro':

      // 1) Obtener último precio del oro
      $dataPrecioOro = $cloud->row("SELECT precioBid, precioAsk, fhRegistro
        FROM bit_historial_precios_metal
        WHERE metal = ? AND monedaId = ? AND flgDelete = ?
        ORDER BY fhRegistro DESC
        LIMIT 1", ['ORO', 1, 0]);

      if (!$dataPrecioOro) {
        echo "error: no se encontró el precio del oro";
        break;
      }

      $precioPorOnzaTroy = (float) $dataPrecioOro->precioAsk;
      $FACTOR_ONZA_A_GRAMO = 31.1035;
      $PUREZA_14K = 14 / 24;
      $alpha = 0.2;

      // 2) Obtener productos con peso (catProdEspecificacionId = 5) y su precio activo actual
      $dataProductos = $cloud->rows("SELECT
          p.productoId,
          p.nombreProducto,
          ep.valorEspecificacion AS pesoValor,
          ep.unidadMedidaId,
          pp.productoPrecioId,
          pp.costoPromedio AS costoPrevProm,
          pp.costoUnitario AS costoPrevUnit,
          pp.precioVenta,
          pp.precioVentaIVA
        FROM prod_productos p
        JOIN prod_productos_especificaciones ep
          ON ep.productoId = p.productoId
          AND ep.catProdEspecificacionId = ?
          AND ep.flgDelete = ?
        LEFT JOIN prod_productos_precios pp
          ON pp.productoId = p.productoId
          AND pp.estadoPrecio = 'Activo'
          AND pp.flgDelete = ?
        WHERE p.flgDelete = ?", [5, 0, 0, 0]);

      if (!$dataProductos) {
        echo "ok: no hay productos para actualizar";
        break;
      }

      $precioPorGramoPuro = $precioPorOnzaTroy / $FACTOR_ONZA_A_GRAMO;
      $productosActualizados = 0;

      try {
        foreach ($dataProductos as $dp) {

          // Solo procesar si la unidad de medida es gramos (unidadMedidaId = 52)
          if ((int) $dp->unidadMedidaId !== 52) {
            continue;
          }

          $costoNuevo = $precioPorGramoPuro * (float) $dp->pesoValor * $PUREZA_14K;
          $costoPrev = (float) ($dp->costoPrevProm ?? 0);

          if ($costoPrev <= 0) {
            $costoPrev = (float) ($dp->costoPrevUnit ?? 0);
          }

          // Suavizado exponencial para evitar cambios bruscos
          $costoSuav = ($costoPrev > 0)
            ? ($alpha * $costoNuevo + (1 - $alpha) * $costoPrev)
            : $costoNuevo;

          // Piso: nunca bajar del costo anterior
          $piso = $costoPrev > 0 ? $costoPrev : 0;
          $costoFinal = max($piso, $costoSuav);

          // 3) Desactivar el precio actual (si existe) para mantener historial
          if (!empty($dp->productoPrecioId)) {
            $cloud->update(
              'prod_productos_precios',
              [
                'estadoPrecio' => 'Inactivo',
                'userEdit' => $_SESSION['usuario'] ?? null,
                'fhEdit' => $fhActual
              ],
              ['productoPrecioId' => $dp->productoPrecioId]
            );
          }

          // 4) Insertar nuevo precio como Activo
          $cloud->insert(
            'prod_productos_precios',
            [
              'productoId' => $dp->productoId,
              'personaId' => $_SESSION["personaId"] ?? null,
              'precioVenta' => $dp->precioVenta ?? 0,
              'precioVentaIVA' => $dp->precioVentaIVA ?? 0,
              'costoUnitario' => round($costoFinal, 8),
              'costoPromedio' => round($costoFinal, 8),
              'estadoPrecio' => 'Activo',
              'obsPrecio' => 'Actualizado automáticamente según precio del oro',
              'userAdd' => $_SESSION['usuario'] ?? null,
              'fhAdd' => $fhActual
            ]
          );

          $productosActualizados++;
        }

        $cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó costos de $productosActualizados productos de oro según precio: $" . number_format($precioPorOnzaTroy, 2) . "/oz troy");
        echo 'success';

      } catch (Throwable $e) {
        echo "error: " . $e->getMessage();
      }
      break;

    case 'actualizar-precio-venta':
      // Actualización individual de precio de venta desde la tabla
      // Recibe: productoPrecioId, productoId, precioVenta (sin IVA), precioVentaIVA

      if (empty($_POST['productoPrecioId']) || empty($_POST['productoId'])) {
        echo "error: faltan datos requeridos";
        break;
      }

      $productoPrecioId = (int) $_POST['productoPrecioId'];
      $productoId = (int) $_POST['productoId'];
      $precioVenta = (float) $_POST['precioVenta'];
      $precioVentaIVA = (float) $_POST['precioVentaIVA'];

      // Obtener el precio actual para copiar los costos
      $precioActual = $cloud->row("
        SELECT costoUnitarioFOB, costoUnitario, costoPromedio, obsPrecio
        FROM prod_productos_precios
        WHERE productoPrecioId = ? AND flgDelete = 0
      ", [$productoPrecioId]);

      if (!$precioActual) {
        echo "error: no se encontró el precio actual";
        break;
      }

      // Validar que el precio no sea menor al costo + 1%
      $costoBase = (float) $precioActual->costoPromedio > 0 
        ? (float) $precioActual->costoPromedio 
        : (float) $precioActual->costoUnitario;
      $precioMinimo = $costoBase * 1.01;

      if ($precioVenta < $precioMinimo) {
        echo "error: el precio de venta no puede ser menor a $" . number_format($precioMinimo, 2) . " (costo + 1%)";
        break;
      }

      try {
        // 1) Desactivar el precio actual
        $cloud->update(
          'prod_productos_precios',
          [
            'estadoPrecio' => 'Inactivo',
            'userEdit' => $_SESSION['usuario'] ?? null,
            'fhEdit' => $fhActual
          ],
          ['productoPrecioId' => $productoPrecioId]
        );

        // 2) Insertar nuevo precio como Activo
        $cloud->insert(
          'prod_productos_precios',
          [
            'productoId' => $productoId,
            'personaId' => $_SESSION['personaId'] ?? null,
            'precioVenta' => round($precioVenta, 8),
            'precioVentaIVA' => round($precioVentaIVA, 8),
            'costoUnitarioFOB' => $precioActual->costoUnitarioFOB,
            'costoUnitario' => $precioActual->costoUnitario,
            'costoPromedio' => $precioActual->costoPromedio,
            'estadoPrecio' => 'Activo',
            'obsPrecio' => 'Actualización manual de precio de venta',
            'userAdd' => $_SESSION['usuario'] ?? null,
            'fhAdd' => $fhActual
          ]
        );

        // Obtener nombre del producto para bitácora
        $dataProd = $cloud->row("SELECT nombreProducto FROM prod_productos WHERE productoId = ?", [$productoId]);
        $nombreProd = $dataProd->nombreProducto ?? 'ID:' . $productoId;

        $cloud->writeBitacora(
          "movUpdate",
          "(" . $fhActual . ") Actualizó precio de venta del producto: " . $nombreProd . 
          " - Nuevo precio: $" . number_format($precioVenta, 2) . " (con IVA: $" . number_format($precioVentaIVA, 2) . ")"
        );

        echo 'success';

      } catch (Throwable $e) {
        echo "error: " . $e->getMessage();
      }
      break;

    case "producto":

      if (($_POST["typeOperation"] ?? "") !== "update") {
        echo "error: typeOperation inválido";
        break;
      }

      if (empty($_POST["productoId"])) {
        echo "error: productoId es obligatorio";
        break;
      }

      $productoId = (int) $_POST["productoId"];


      $prod = $cloud->row(
        "SELECT productoId, codInterno 
     FROM prod_productos 
     WHERE productoId = ? AND flgDelete = 0",
        [$productoId]
      );

      if (!$prod) {
        echo "error: producto no existe";
        break;
      }


      if (empty($_POST["sku"])) {
        echo "error: el SKU es obligatorio";
        break;
      }

      $sku = trim($_POST["sku"]);


      $skuExiste = $cloud->row(
        "SELECT productoId 
     FROM prod_productos 
     WHERE codInterno = ? AND productoId <> ? AND flgDelete = 0",
        [$sku, $productoId]
      );

      if ($skuExiste) {
        echo "error: el SKU ya está registrado";
        break;
      }


      if (empty($_POST["nombre"])) {
        echo "error: el nombre es obligatorio";
        break;
      }
      if (empty($_POST["categoria"])) {
        echo "error: la categoría es obligatoria";
        break;
      }
      if (empty($_POST["marcaId"])) {
        echo "error: la marca es obligatoria";
        break;
      }
      if (empty($_POST["udm"])) {
        echo "error: la unidad de medida es obligatoria";
        break;
      }
      if (empty($_POST["tipo"])) {
        echo "error: el tipo de producto es obligatorio";
        break;
      }


      $updateProducto = [
        "codFabricante" => $_POST["codFabricante"] ?? null,
        "codInterno" => $sku,
        "inventarioCategoriaPrincipalId" => $_POST["categoria"],
        "nombreProducto" => $_POST["nombre"],
        "descripcionProducto" => $_POST["descripcion"] ?? null,
        "marcaId" => $_POST["marcaId"],
        "unidadMedidaId" => $_POST["udm"],
        "tipoProductoId" => $_POST["tipo"],
        "paisIdOrigen" => $_POST["pais"] ?? null,
        "obsEstadoProducto" => $_POST["obs"] ?? null,


        "userEdit" => $_SESSION["usuario"] ?? null,
        "fhEdit" => $fhActual,
      ];


      if (!empty($_POST["estado"])) {
        $updateProducto["estadoProducto"] = $_POST["estado"];
      }

      $ok = $cloud->update("prod_productos", $updateProducto, ["productoId" => $productoId]);
      if (!$ok) {
        echo "error: no se pudo actualizar el producto";
        break;
      }

      $cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó el producto " . ($_POST["nombre"] ?? ""));


      $tags = $_POST["tags"] ?? [];
      if (!is_array($tags))
        $tags = [];

      $tagsDb = $cloud->rows(
        "SELECT inventarioCategoriaId
     FROM prod_productos_categorias
     WHERE productoId = ? AND flgDelete = 0",
        [$productoId]
      );

      $setDb = [];
      foreach ($tagsDb as $r)
        $setDb[(string) $r->inventarioCategoriaId] = true;

      $setIn = [];
      foreach ($tags as $t)
        $setIn[(string) $t] = true;


      foreach ($setDb as $catId => $_) {
        if (!isset($setIn[$catId])) {
          $cloud->update("prod_productos_categorias", [
            "flgDelete" => 1,
            "userDelete" => $_SESSION["usuario"] ?? null,
            "fhDelete" => $fhActual
          ], [
            "productoId" => $productoId,
            "inventarioCategoriaId" => $catId
          ]);
        }
      }


      foreach ($setIn as $catId => $_) {


        $exSoft = $cloud->row(
          "SELECT productoId 
       FROM prod_productos_categorias 
       WHERE productoId = ? AND inventarioCategoriaId = ?",
          [$productoId, $catId]
        );

        if ($exSoft) {
          $cloud->update("prod_productos_categorias", [
            "flgDelete" => 0,
            "userEdit" => $_SESSION["usuario"] ?? null,
            "fhEdit" => $fhActual
          ], [
            "productoId" => $productoId,
            "inventarioCategoriaId" => $catId
          ]);
        } else {
          $cloud->insert("prod_productos_categorias", [
            "productoId" => $productoId,
            "inventarioCategoriaId" => $catId
          ]);
        }
      }


      $esps = $_POST["especificaciones"] ?? [];
      if (!is_array($esps))
        $esps = [];

      $dbEsps = $cloud->rows(
        "SELECT catProdEspecificacionId
     FROM prod_productos_especificaciones
     WHERE productoId = ? AND flgDelete = 0",
        [$productoId]
      );

      $mapDb = [];
      foreach ($dbEsps as $r)
        $mapDb[(string) $r->catProdEspecificacionId] = true;

      $mapIn = [];

      foreach ($esps as $esp) {
        if (empty($esp["id"]))
          continue;

        $espId = (string) $esp["id"];
        $valor = $esp["valor"] ?? null;
        $udmEsp = $esp["unidadMedida"] ?? null;


        if ($valor === null || trim((string) $valor) === "") {
          continue;
        }

        $mapIn[$espId] = true;


        $exEsp = $cloud->row(
          "SELECT productoId 
       FROM prod_productos_especificaciones
       WHERE productoId = ? AND catProdEspecificacionId = ?",
          [$productoId, $espId]
        );

        if ($exEsp) {
          $cloud->update("prod_productos_especificaciones", [
            "valorEspecificacion" => $valor,
            "unidadMedidaId" => $udmEsp,
            "flgDelete" => 0,
            "userEdit" => $_SESSION["usuario"] ?? null,
            "fhEdit" => $fhActual
          ], [
            "productoId" => $productoId,
            "catProdEspecificacionId" => $espId
          ]);
        } else {
          $cloud->insert("prod_productos_especificaciones", [
            "productoId" => $productoId,
            "catProdEspecificacionId" => $espId,
            "valorEspecificacion" => $valor,
            "unidadMedidaId" => $udmEsp
          ]);
        }
      }


      foreach ($mapDb as $espId => $_) {
        if (!isset($mapIn[$espId])) {
          $cloud->update("prod_productos_especificaciones", [
            "flgDelete" => 1,
            "userDelete" => $_SESSION["usuario"] ?? null,
            "fhDelete" => $fhActual
          ], [
            "productoId" => $productoId,
            "catProdEspecificacionId" => $espId
          ]);
        }
      }
      echo "success";
      break;

    default:
      echo "No se encontró la operación.";
      break;
  }
} else {
  header("Location: /alina-cloud/app/");
}
?>