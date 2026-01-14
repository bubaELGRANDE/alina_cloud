<?php
/*
	DELETE ESPECIFICANDO CAMPOS (condiciones):
		$delete = ['columnas' => "hola xd"];
		$cloud->delete('test', $delete);
	DELETE POR ID:
		$cloud->deleteById('tabla', "columnaId", id);
	DELETE MULTIPLE ID:
		$cloud->deleteByIds('tabla', "columnaId", "2, 4, 6, N");
*/
if (isset($_SESSION["usuarioId"]) && isset($operation)) {
	switch ($operation) {
		case "especificacion":
			$queryExist = "SELECT prodEspecificacionId FROM prod_productos_especificaciones WHERE catProdEspecificacionId = ? AND flgDelete = 0";
			$existe = $cloud->count($queryExist, [$_POST["idEsp"]]);

			$queryEsp = "
					SELECT 
						nombreProdEspecificacion
					FROM cat_productos_especificaciones
					WHERE catProdEspecificacionId = ? 
				";
			if ($existe == 0) {

				$cloud->deleteById('cat_productos_especificaciones', 'catProdEspecificacionId', $_POST["idEsp"]);

				$esp = $cloud->row($queryEsp, [$_POST["idEsp"]]);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Eliminó la especificacion: " . $esp->nombreProdEspecificacion . ", ");

				echo "success";
			} else {
				$esp = $cloud->row($queryEsp, [$_POST["idEsp"]]);
				echo "La especificación: " . $esp->nombreProdEspecificacion . "  está asignada, por lo que no se puede eliminar.";
			}

			break;
		case 'marca':
			/*
				POST:
				typeOperation
				operation
				id: marcaId
			*/

			# Validación cuando marcaId ya se haya registrado en un producto
			$queryExist = "
					SELECT 
						nombreProducto
					FROM prod_productos
					WHERE marcaId = ? AND flgDelete = 0
				";
			$existe = $cloud->count($queryExist, [$_POST["id"]]);

			# Obtener los datos de la marca antes de hacer la validación
			$datMarca = $cloud->row("
					SELECT
						nombreMarca, urlLogoMarca
					FROM cat_inventario_marcas
					WHERE marcaId = ?
				", [$_POST['id']]);

			if ($existe == 0) {
				# Verificar si la URL del logo no es nula o vacía
				if (!empty($datMarca->urlLogoMarca)) {
					$oldUrl = "../../../../libraries/resources/images/" . $datMarca->urlLogoMarca;

					// logos/marcas/archivo.extension
					$arrayUrl = explode("/", $datMarca->urlLogoMarca);

					# Verificar que el array tenga suficientes partes antes de intentar acceder a índices
					if (count($arrayUrl) >= 3) {
						$newUrl = "../../../../libraries/resources/images/logos/" . $arrayUrl[1] . "/ (ELIMINADA " . date("d_m_Y H_i_s") . ") " . $arrayUrl[2];

						# Renombrar solo si los archivos y directorios son válidos
						if (file_exists($oldUrl)) {
							rename($oldUrl, $newUrl);
						} else {
							echo "Advertencia: El archivo original no existe.";
						}
					} else {
						echo "Advertencia: La URL del logo no es válida.";
					}
				} else {
					echo "";
				}

				# Eliminar la marca
				$cloud->deleteById('cat_inventario_marcas', 'marcaId', $_POST['id']);

				# Registrar en bitácora
				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó la Marca: " . $datMarca->nombreMarca);

				echo "success";
			} else {
				echo "La Marca: " . $datMarca->nombreMarca . " ya tiene productos asignados, por lo que no se puede eliminar.";
			}

			break;
		case 'categoria':
			/**
			 * tableData = inventarioCategoriaId^nombreCategoria
			 */
			$tableData = explode("^", $_POST["id"]);

			# Validación de si la categoría tiene productos asignados
			$queryExist = "
					SELECT 
						productoId
					FROM prod_productos_categorias
					WHERE inventarioCategoriaId = ? AND flgDelete = 0
				";
			$existe = $cloud->count($queryExist, [$tableData[0]]);

			if ($existe > 0) {
				# Si hay productos asignados, no se puede eliminar la categoría
				echo "No se puede eliminar la categoría: " . $tableData[1] . " porque tiene productos asignados.";
			} else {
				# Si no tiene productos, eliminar la categoría
				$cloud->deleteById('cat_inventario_categorias', 'inventarioCategoriaId', $tableData[0]);

				# Registrar en bitácora la eliminación
				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó la categoría: " . $tableData[1]);

				echo "success";
			}

			break;


		case 'unidad-medida':
			/**
			 * tableData = unidadMedidaId^nombreUnidadMedida
			 */
			$tableData = explode("^", $_POST["id"]);
			$queryExist = "
	        		SELECT 
	        			productoId
	        		FROM prod_productos
	        		WHERE unidadMedidaId = ? AND flgDelete = 0
	        	";
			$existe = $cloud->count($queryExist, [$tableData[0]]);
			if ($existe == 0) {
				$cloud->deleteById('cat_unidades_medida', 'unidadMedidaId', $tableData[0]);

				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó la Unidad de Medida: " . $tableData[1]);

				echo "success";
			} else {
				echo "La Unidad de Medida: " . $tableData[1] . " ya tiene productos asignados, por lo que no se puede eliminar.";
			}
			break;
		default:
			echo "No se encontró la operación.";
			break;
	}
} else {
	header("Location: /alina-cloud/app/");
}
?>