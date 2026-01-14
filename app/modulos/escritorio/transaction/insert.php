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
			default:
				echo "No se encontró la operación.";
			break;
		}
    } else {
        header("Location: /alina-cloud/app/");
    }
?>