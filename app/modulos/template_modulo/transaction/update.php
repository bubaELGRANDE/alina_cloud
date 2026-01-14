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
			default:
				echo "No se encontró la operación.";
			break;
		}
    } else {
    	header("Location: /alina-cloud/app/");
    }
?>