<?php 
	/*
        $insert = [
            'campo1'		=> "hola xd",
            'campo2'     => "hola 2222222",
        ];
        $cloud->insert('nombre_tabla', $insert);
	*/
    if(isset($_SESSION["usuarioId"]) && isset($operation)) {
		switch($operation) {
			case 'parametrizacion-ventas':
				/*
					POST:
					hiddenFormData
					typeOperation
					operation
					dashParamId
					tipoParametrizacion
					tituloParametrizacion
					colorParametrizacion
				*/
				$existeParametrizacion = $cloud->count("
					SELECT dashParamId FROM dash_parametrizacion
					WHERE tipoParametrizacion = ? AND tituloParametrizacion = ? AND flgDelete = ?
				", [ucfirst($_POST['tipoParametrizacion']), $_POST['tituloParametrizacion'], 0]);

				if($existeParametrizacion == 0) {
					$insert = [
						'tipoParametrizacion' 		=> ucfirst($_POST['tipoParametrizacion']),
						'tituloParametrizacion' 	=> $_POST['tituloParametrizacion'],
						'colorParametrizacion' 		=> $_POST['colorParametrizacion']
					];
					$cloud->insert("dash_parametrizacion", $insert);
					$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Insertó una nueva " . ucfirst($_POST['tipoParametrizacion']) . ": " . $_POST['tituloParametrizacion'] . " (Panel de ventas), ");

					echo "success";
				} else {
					echo 'La ' . $_POST['tipoParametrizacion'] . ': ' . $_POST['tituloParametrizacion'] . ' ya fue parametrizada.';
				}
			break;

			case 'parametrizacion-ventas-detalle':
				/*
					POST:
					hiddenFormData
					typeOperation
					operation
					dashParamId
					tipoParametrizacion
					valorParametrizacion (multiple)
				*/
				$n = 0;
				// Iterar la parametrizacion
				foreach ($_POST["valorParametrizacion"] as $valorParametrizacion) {
					$existeParamDetalle = $cloud->count("
						SELECT valorParametrizacion FROM dash_parametrizacion_detalle
						WHERE valorParametrizacion = ? AND dashParamId = ? AND flgDelete = ?
					", [$valorParametrizacion, $_POST['dashParamId'], 0]);

					if($existeParamDetalle == 0) {
						$n += 1;
						$insert = [
							'dashParamId' 				=> $_POST['dashParamId'],
							'valorParametrizacion'		=> $valorParametrizacion
						];
						$cloud->insert("dash_parametrizacion_detalle", $insert);
					} else {
						// Ya se agregó esta parametrización, omitirla para no duplicar
					}
				}
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Insertó ".$n." parametrizaciones para la ".$_POST['tipoParametrizacion']." ID: ".$_POST['dashParamId'].", ");
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