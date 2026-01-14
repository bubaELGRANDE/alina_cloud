<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataMenuPermisos = $cloud->rows("
    	SELECT
    		menuPermisoId,
            menuId,
            permisoMenu 
    	FROM conf_menus_permisos
    	WHERE menuId = ? AND flgDelete = '0'
    ", [$_POST["id"]]);
    $n = 0;
    foreach ($dataMenuPermisos as $dataMenuPermisos) {
    	$n += 1;

    	$permisos = $dataMenuPermisos->permisoMenu;	

        $queryUsuariosPermiso = "
            SELECT
                prs.permisoUsuarioId,
                CONCAT(
                    IFNULL(pr.apellido1, '-'),
                    ' ',
                    IFNULL(pr.apellido2, '-'),
                    ', ',
                    IFNULL(pr.nombre1, '-'),
                    ' ',
                    IFNULL(pr.nombre2, '-')
                ) AS nombrePersona
            FROM conf_permisos_usuario prs
            JOIN conf_usuarios us ON us.usuarioId = prs.usuarioId
            JOIN th_personas pr ON pr.personaId = us.personaId
            WHERE prs.menuPermisoId = ? AND prs.flgDelete = '0' AND us.flgDelete = '0'
        ";

        $cantidadUsuariosPermiso = $cloud->count($queryUsuariosPermiso, [$dataMenuPermisos->menuPermisoId]);

        $getNombresUsuario = $cloud->rows($queryUsuariosPermiso, [$dataMenuPermisos->menuPermisoId]);
        $nombresUsuario = '';

        $i = 0;
        foreach ($getNombresUsuario as $getNombresUsuario) {
            $i += 1;
            $nombresUsuario .= $i . ". " . $getNombresUsuario->nombrePersona . "<br>";
        }

        if($i == 0) {
            $nombresUsuario = 'No se encontraron usuarios con este permiso asignado.';
        } else {
        }

        // Validar si el permisoMenu es Dropdown y prevenir eliminar si todavia existen submenus
        $querySubMenus = "
            SELECT
                moduloId,
                menuId,
                menu,
                iconMenu,
                urlMenu,
                numOrdenMenu,
                menuSuperior,
                badgeColor,
                badgeText
            FROM conf_menus
            WHERE menuSuperior = ? AND flgDelete = '0'
            ORDER BY numOrdenMenu                
        ";
        $existeSubMenu = $cloud->count($querySubMenus, [$dataMenuPermisos->menuId]);

        if($existeSubMenu == 0) { // Ya no tiene submenus, permitir acciones
            if($dataMenuPermisos->permisoMenu == "Desarrollo") { // Es el permiso "Desarrollo", no se puede editar ni eliminar
                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(40, $_SESSION["arrayPermisos"])) {
                    $btnEditar = '
                        <button type="button" class="btn btn-primary btn-sm" disabled>
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    ';
                } else {
                    $btnEditar = '';
                }

                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(41, $_SESSION["arrayPermisos"])) {
                    $btnEliminar = '
                        <button type="button" class="btn btn-danger btn-sm" disabled>
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                } else {
                    $btnEliminar = '';
                }
            } else { // Es otro permiso, se puede editar/eliminar
                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(40, $_SESSION["arrayPermisos"])) {
                    $btnEditar = '
                        <button type="button" class="btn btn-primary btn-sm ttip" onclick="editarMenuPermiso(`'.$dataMenuPermisos->menuPermisoId.'^'.$dataMenuPermisos->permisoMenu.'`);">
                            <i class="fas fa-pencil-alt"></i>
                            <span class="ttiptext">Editar</span>
                        </button>
                    ';
                } else {
                    $btnEditar = '';
                }

                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(41, $_SESSION["arrayPermisos"])) {
                    $btnEliminar = '
                        <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarMenuPermiso(`'.$dataMenuPermisos->menuPermisoId.'^'.$dataMenuPermisos->permisoMenu.'`);">
                            <i class="fas fa-trash-alt"></i>
                            <span class="ttiptext">Eliminar</span>
                        </button>
                    ';
                } else {
                    $btnEliminar = '';
                }
            }
            if($cantidadUsuariosPermiso == 0) {
                $acciones = '
                    '.$btnEditar.'
                    <button type="button" class="btn btn-primary btn-sm ttip" onclick="mensaje(`Permiso: '.$dataMenuPermisos->permisoMenu.'`, `Usuarios: <br>No se han asignado usuarios.`, `info`, `Cerrar`);">
                        <span class="badge rounded-pill bg-light" style="color: black;">'.$cantidadUsuariosPermiso.'</span>
                        <i class="fas fa-user-lock"></i>
                        <span class="ttiptext">Usuarios</span>
                    </button>
                    '.$btnEliminar.'
                ';
            } else {
                // El eliminar se crea arriba pero ya tiene usuarios, se necesita poner disabled (arriba se crea con ids), validar su permiso
                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(41, $_SESSION["arrayPermisos"])) {
                    $btnEliminar = '
                        <button type="button" class="btn btn-danger btn-sm" disabled>
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                } else {
                    $btnEliminar = '';
                }                
                $acciones = '
                    '.$btnEditar.'
                    <button type="button" class="btn btn-primary btn-sm ttip" onclick="mensaje(`Permiso: '.$dataMenuPermisos->permisoMenu.'`, `Usuarios: <br>'.$nombresUsuario.'`, `info`, `Cerrar`);">
                        <span class="badge rounded-pill bg-light" style="color: black;">'.$cantidadUsuariosPermiso.'</span>
                        <i class="fas fa-user-lock"></i>
                        <span class="ttiptext">Usuarios</span>
                    </button>
                    '.$btnEliminar.'
                ';
            }
        } else {
            // Es Dropdown y todavia tiene submenus, prevenir acciones con este permiso automatico
            // Validar sus permisos aunque los buttons vayan en disabled
            if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(40, $_SESSION["arrayPermisos"])) {
                $btnEditar = '
                    <button type="button" class="btn btn-primary btn-sm" disabled>
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                ';
            } else {
                $btnEditar = '';
            }

            if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(41, $_SESSION["arrayPermisos"])) {
                $btnEliminar = '
                    <button type="button" class="btn btn-danger btn-sm" disabled>
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            } else {
                $btnEliminar = '';
            }
            $acciones = '
                '.$btnEditar.'
                <button type="button" class="btn btn-primary btn-sm ttip" onclick="mensaje(`Permiso: '.$dataMenuPermisos->permisoMenu.'`, `Usuarios: <br>'.$nombresUsuario.'`, `info`, `Cerrar`);">
                    <span class="badge rounded-pill bg-light" style="color: black;">'.$cantidadUsuariosPermiso.'</span>
                    <i class="fas fa-user-lock"></i>
                    <span class="ttiptext">Usuarios</span>
                </button>
                '.$btnEliminar.'
            ';
        }

	    $output['data'][] = array(
	        $n . " (" . $dataMenuPermisos->menuPermisoId . ")", // es #, se dibuja solo en el JS de datatable
	        $permisos,
	        $acciones
	    );
	} // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>