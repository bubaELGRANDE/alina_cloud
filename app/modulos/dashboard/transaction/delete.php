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
	if(isset($_SESSION["usuarioId"]) && isset($operation)) {
		switch($operation) {
			case "parametrizacion-ventas":
                /*
					POST:
					typeOperation
					operation
					id
	        	*/
                $cloud->deleteById("dash_parametrizacion", "dashParamId", $_POST["id"]);

                $dataParametrizacion = $cloud->row("
                	SELECT tituloParametrizacion FROM dash_parametrizacion
                	WHERE dashParamId = ?
                ", [$_POST["id"]]);

				$existeParametrizacion = $cloud->count("
					SELECT dashParamDetalleId FROM dash_parametrizacion_detalle
					WHERE dashParamId = ?
				", [$_POST['id']]);

				if($existeParametrizacion == 0) {
					$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó la parametrización: ".$dataParametrizacion->tituloParametrizacion.", ");
				} else {
					// Tiene detalles
					$totalEliminados = $cloud->deleteById("dash_parametrizacion_detalle", "dashParamId", $_POST["id"]);
					$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó la parametrización (panel de ventas): ".$dataParametrizacion->tituloParametrizacion." con un total de ".$totalEliminados." detalles de parametrización, ");
				}

                echo "success";
			break;

			case "parametrizacion-ventas-detalle":
                /*
					POST:
					typeOperation
					operation
					id
	        	*/
                $cloud->deleteById("dash_parametrizacion_detalle", "dashParamDetalleId", $_POST["id"]);

                $dataParamDetalle = $cloud->row("
                	SELECT valorParametrizacion FROM dash_parametrizacion_detalle
                	WHERE dashParamDetalleId = ?
                ", [$_POST["id"]]);

   				$cloud->writeBitacora("movDelete", "(" . $fhActual . ") Eliminó la parametrización detalle (panel de ventas): ".$dataParamDetalle->valorParametrizacion.", ");
   				
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