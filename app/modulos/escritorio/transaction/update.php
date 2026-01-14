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
			case "cambiar-password":
				$nuevaPassword = password_hash(base64_encode(md5($_POST["passwordNew"])), PASSWORD_BCRYPT);
		        $update = [
		            'enLinea'	=> 0,
		            'pass'    	=> $nuevaPassword,
		        ];
		        $where = ['usuarioId' => $_SESSION["usuarioId"]];
		        $cloud->update('conf_usuarios', $update, $where);
		        // Bitácora de usuario final / jefes
		        $cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Actualizó su contraseña, ");
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