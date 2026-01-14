<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    #$tipoUsuario = ($_POST["tipoUsuario"] == "Empleado") ? 1 : 2;

    $flgMostrarTabla = 1;
    /*if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(56, $_SESSION["arrayPermisos"])) { // Todos
        $wherePermiso = ""; // Todos los usuarios
    } else if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(57, $_SESSION["arrayPermisos"])) {
        // Get usuarios a cargo
        // wherePermiso = us.personaId IN ($arrayPersonasACargo) AND
        $wherePermiso = ""; // Modificar al tener la tabla de empleados a cargo
    } else {
        // No se asignó ningún permiso
        $flgMostrarTabla = 0;
    }*/

    if($flgMostrarTabla == 1) {
        $datCrud = $cloud->rows("
            SELECT
                ec.crudId,
                ec.nombreCrud,
                ec.descripcion,
                ec.flgDelete,
                cm.modulo
            FROM ejemplo_crud ec
            JOIN conf_modulos cm
            ON ec.moduloId = cm.moduloId
            WHERE ec.flgDelete = '0'
        ");
        $n = 0;
        foreach ($datCrud as $datCrud) {
            $n += 1;

            $nombreCrud = '
                <b><i class="fas fa-check"></i> Nombre CRUD:</b> '.$datCrud->nombreCrud.'<br>
                <b><i class="fas fa-stream"></i> Descripción:</b> '.$datCrud->descripcion.'<br>
            ';

            $modulo = '
                <b><i class="fas fa-archive"></i> Módulo:</b> '.$datCrud->modulo.'<br>
            ';
     
            $btnEditar = '
                <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalCrud(`editar`,`'.$datCrud->crudId.'`);">
                    <i class="fas fa-pencil-alt"></i>
                    <span class="ttiptext">Editar</span>
                </button>
            ';
            $btnEliminar = '
                <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarCrud(`'.$datCrud->crudId.'`);">
                    <i class="fas fa-trash-alt"></i>
                    <span class="ttiptext">Eliminar</span>
                </button>
            ';

            $acciones = '
                '.$btnEditar.'
                '.$btnEliminar.'
            ';

            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $nombreCrud,
                $modulo,
                $acciones
            );
        } // foreach
        if($n > 0) {
            echo json_encode($output);
        } else {
            // No retornar nada para evitar error "null"
            echo json_encode(array('data'=>'')); 
        }
    } else {
        echo json_encode(array('data'=>'')); 
    }
?>