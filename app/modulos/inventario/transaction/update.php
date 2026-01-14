<?php
/*
      $update = [
          'campo1'		=> "hola :o",
          'campo2'     => "hola",
      ];
      $where = ['testId' => id]; // ids, soporta múltiple where

      $cloud->update('test', $update, $where);
*/
if (isset($_SESSION["usuarioId"]) && isset($operation)) {
  switch ($operation) {
    case 'marca':
      if ($_POST['adjuntarLogo'] == "Si") {
        /*
         * POST:
          hiddenFormData: update
          typeOperation
          operation
        */
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
          // Validación de formato nuevamente por si se evade la de Javascript
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
            // No se habia subido imagen anteriormente
          } else {
            // logos/marcas/archivo.extension
            $arrayUrl = explode("/", $dataUrlArchivo->urlLogoMarca);

            $newUrl = "../../../../libraries/resources/images/logos/" . $arrayUrl[1] . "/ (REEMPLAZADA " . date("d_m_Y H_i_s") . ") " . $arrayUrl[2];

            rename($oldUrl, $newUrl);
          }

          //verificar si existe la imagen
          $n = 1;
          $originalNombre = $imagenNombre;
          while ($n > 0) {
            if (file_exists($ubicacion)) {

              $imagenNombre = "(" . $n . ")" . $originalNombre;
              $ubicacion = $filename . "/" . $imagenNombre;
              $n += 1;
            } else {
              // No existe, se mantiene el flujo normal
              $n = 0;
            }
          }
          /* Upload file */
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
      /*
         POST:
        hiddenFormData: update
        typeOperation
        operation
        nombreUnidadMedida
        abreviatura
        tipoMagnitud
        codigoMH
      */
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
      /**
       * $_POST['id'] => marcaId^nombreMarca
       */
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
      /*
        POST
        hiddenFormData
        hiddenFormData
        operation
        adjuntoId
        personaId
        tipoAdjunto
        descripcionAdjunto
      */

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
      /*
        hiddenFormData
        typeOperation
        operation
        productoId
        nombreEsp
        tipoEspecificacionN
        tipoMagnitud
      */


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
    default:
      echo "No se encontró la operación.";
      break;
  }
} else {
  header("Location: /alina-cloud/app/");
}
?>