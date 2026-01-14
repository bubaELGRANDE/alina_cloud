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
			default:
				echo "No se encontró la operación.";
			break;
		}
    } else {
        header("Location: /alina-cloud/app/");
    }
?>