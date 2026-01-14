<?php 
	/*
        $update = [
            'campo1'		=> "hola :o",
            'campo2'     => "hola",
        ];
        $where = ['testId' => id]; // ids, soporta múltiple where
        
        $cloud->update('test', $update, $where);
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
				*/
				$existeParametrizacion = $cloud->count("
					SELECT dashParamId FROM dash_parametrizacion
					WHERE tituloParametrizacion = ? AND dashParamId <> ? AND flgDelete = ?
				", [$_POST['tituloParametrizacion'], $_POST['dashParamId'], 0]);

				if($existeParametrizacion == 0) {
					$update = [
						'tituloParametrizacion' 	=> $_POST['tituloParametrizacion'],
						'colorParametrizacion' 		=> $_POST['colorParametrizacion']
					];
					$where = ['dashParamId' => $_POST['dashParamId']];
					$cloud->update("dash_parametrizacion", $update, $where);

					echo "success";
				} else {
					echo 'La ' . $_POST['tipoParametrizacion'] . ': ' . $_POST['tituloParametrizacion'] . ' ya fue parametrizada.';
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