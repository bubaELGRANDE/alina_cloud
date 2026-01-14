<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataMenus = $cloud->rows("
    	SELECT
            moduloId,
    		menuId,
    		menu,
    		iconMenu,
    		urlMenu,
    		numOrdenMenu,
    		menuSuperior,
            badgeColor,
            badgeText,
            estadoMenu
    	FROM conf_menus
    	WHERE moduloId = ? AND menuSuperior = '0' AND flgDelete = '0'
        ORDER BY numOrdenMenu
    ", [$_POST["id"]]);
    $n = 0;
    foreach ($dataMenus as $dataMenus) {
    	$n += 1;

        $orden = $n . " (" . $dataMenus->numOrdenMenu . ")";

        // Validar si posee sub-menus para colocarlo en "Tipo"
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
                badgeText,
                estadoMenu
            FROM conf_menus
            WHERE menuSuperior = ? AND flgDelete = '0'
            ORDER BY numOrdenMenu                
        ";
        $existeSubMenu = $cloud->count($querySubMenus, [$dataMenus->menuId]);

        $cantidadMenuPermisos = $cloud->count("
            SELECT
                menuPermisoId
            FROM conf_menus_permisos
            WHERE menuId = ? AND flgDelete = '0'
        ", [$dataMenus->menuId]);

        $badgeInterfaz = "";
        if($existeSubMenu == 0) {
            if($dataMenus->estadoMenu == "Mantenimiento") {
                $badgeInterfaz = '<b>Estado interfaz:</b> <span class="badge rounded-pill bg-warning">En mantenimiento</span>';
            } else {
                $badgeInterfaz = '<b>Estado interfaz:</b> <span class="badge rounded-pill bg-success">Disponible</span>';
            }

            $menus = '
                <b><i class="fas fa-bars"></i> Menú: </b>' . $dataMenus->menu . '<br>
                <b><i class="fas fa-grip-lines"></i> Tipo: </b> Único<br>
                <b><i class="fas fa-link"></i> URL: </b>' . $dataMenus->urlMenu . '
            ';

            if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(36, $_SESSION["arrayPermisos"])) {
                $btnEditarMenu = '
                    <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalEditMenu(`'.$dataMenus->menuId.'^'.$dataMenus->moduloId.'^'.$dataMenus->menu.'`);">
                        <i class="fas fa-pencil-alt"></i>
                        <span class="ttiptext">Editar</span>
                    </button>
                ';
            } else {
                $btnEditarMenu = '';
            }

            if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(37, $_SESSION["arrayPermisos"])) {
                $btnEliminarMenu = '
                    <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarMenu(`'.$dataMenus->menuId.'^'.$dataMenus->menu.'`);">
                        <i class="fas fa-trash-alt"></i>
                        <span class="ttiptext">Eliminar</span>
                    </button>
                ';
            } else {
                $btnEliminarMenu = '';
            }

            if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(38, $_SESSION["arrayPermisos"])) {
                $btnVerPermisos = '
                    <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalMenuPermisos(`'.$dataMenus->menuId.'^'.$dataMenus->menu.'`);">
                        <span class="badge rounded-pill bg-light" style="color: black;">'.$cantidadMenuPermisos.'</span>
                        <i class="fas fa-lock"></i>
                        <span class="ttiptext">Permisos</span>
                    </button>
                ';
            } else {
                $btnVerPermisos = '';
            }

            $acciones = '
                '.$btnEditarMenu.'
                '.$btnVerPermisos.'
                '.$btnEliminarMenu.'
            ';
        } else {
            $menus = '
                <b><i class="fas fa-bars"></i> Menú: </b>' . $dataMenus->menu . '<br>
                <b><i class="fas fa-grip-lines"></i> Tipo: </b> Dropdown (' . $existeSubMenu . ')<br>
                <b><i class="fas fa-link"></i> URL: </b> No aplica
            ';

            if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(36, $_SESSION["arrayPermisos"])) {
                $btnEditarMenu = '
                    <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalEditMenu(`'.$dataMenus->menuId.'^'.$dataMenus->moduloId.'^'.$dataMenus->menu.'`);">
                        <i class="fas fa-pencil-alt"></i>
                        <span class="ttiptext">Editar</span>
                    </button>
                ';
            } else {
                $btnEditarMenu = '';
            }

            if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(37, $_SESSION["arrayPermisos"])) {
                $btnEliminarMenu = '
                    <button type="button" class="btn btn-danger btn-sm" disabled>
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            } else {
                $btnEliminarMenu = '';
            }

            if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(38, $_SESSION["arrayPermisos"])) {
                $btnVerPermisos = '
                    <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalMenuPermisos(`'.$dataMenus->menuId.'^'.$dataMenus->menu.'`);">
                        <span class="badge rounded-pill bg-light" style="color: black;">'.$cantidadMenuPermisos.'</span>
                        <i class="fas fa-lock"></i>
                        <span class="ttiptext">Permisos</span>
                    </button>
                ';
            } else {
                $btnVerPermisos = '';
            }

            $acciones = '
                '.$btnEditarMenu.'
                '.$btnVerPermisos.'
                '.$btnEliminarMenu.'
            ';
        }

    	// Validar si tiene badge
    	if(!(is_null($dataMenus->badgeColor) || is_null($dataMenus->badgeText))) {
    		$badge = '<span class="badge rounded-pill bg-'.$dataMenus->badgeColor.'">' . $dataMenus->badgeText . '</span>';
    	} else {
    		$badge = "Ninguno";
    	}

    	$iconos = '
    		<b><i class="fab fa-font-awesome-flag"></i> Icono: </b>
    		<a role="button">
    			<i class="fa fa-' . $dataMenus->iconMenu . ' icon-menu"></i>
    		</a>
    		<br>
    		<b><i class="fas fa-tag"></i> Badge: </b>' . $badge . '<br>
            '.$badgeInterfaz.'
        ';

	    $output['data'][] = array(
	        $orden, // es #, se dibuja solo en el JS de datatable
	        $menus,
	        $iconos,
	        $acciones
	    );

        // Validar si tiene sub-menus después de haber dibujado su registro para dibujarlos abajo del "padre"
        if($existeSubMenu > 0) { // Existe, dibujar data de sus "hijos"
            $dataSubMenus = $cloud->rows($querySubMenus, [$dataMenus->menuId]);
            $badgeInterfaz = "";
            foreach ($dataSubMenus as $dataSubMenus) {
                $n += 1;
                
                if($dataSubMenus->estadoMenu == "Mantenimiento") {
                    $badgeInterfaz = '<b>Estado interfaz:</b> <span class="badge rounded-pill bg-warning">En mantenimiento</span>';
                } else {
                    $badgeInterfaz = '<b>Estado interfaz:</b> <span class="badge rounded-pill bg-success">Disponible</span>';
                }

                $cantidadSubMenuPermisos = $cloud->count("
                    SELECT
                        menuPermisoId
                    FROM conf_menus_permisos
                    WHERE menuId = ? AND flgDelete = '0'
                ", [$dataSubMenus->menuId]);

                $orden = $n . " (" . $dataMenus->numOrdenMenu . ") (" . $dataSubMenus->numOrdenMenu . ")";
                $menus = '
                    <b><i class="fas fa-bars"></i> Menú: </b>' . $dataSubMenus->menu . '<br>
                    <b><i class="fas fa-grip-lines"></i> Tipo: </b> Submenú<br>
                    <b><i class="fas fa-link"></i> URL: </b>' . $dataSubMenus->urlMenu . '
                ';

                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(36, $_SESSION["arrayPermisos"])) {
                    $btnEditarMenu = '
                        <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalEditMenu(`'.$dataSubMenus->menuId.'^'.$dataSubMenus->moduloId.'^'.$dataSubMenus->menu.'`);">
                            <i class="fas fa-pencil-alt"></i>
                            <span class="ttiptext">Editar</span>
                        </button>
                    ';
                } else {
                    $btnEditarMenu = '';
                }                

                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(37, $_SESSION["arrayPermisos"])) {
                    $btnEliminarMenu = '
                        <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarMenu(`'.$dataSubMenus->menuId.'^'.$dataSubMenus->menu.'`);">
                            <i class="fas fa-trash-alt"></i>
                            <span class="ttiptext">Eliminar</span>
                        </button>
                    ';
                } else {
                    $btnEliminarMenu = '';
                }

                if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(38, $_SESSION["arrayPermisos"])) {
                    $btnVerPermisos = '
                        <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalMenuPermisos(`'.$dataSubMenus->menuId.'^'.$dataSubMenus->menu.'`);">
                            <span class="badge rounded-pill bg-light" style="color: black;">'.$cantidadSubMenuPermisos.'</span>
                            <i class="fas fa-lock"></i>
                            <span class="ttiptext">Permisos</span>
                        </button>
                    ';
                } else {
                    $btnVerPermisos = '';
                }

                $acciones = '
                    '.$btnEditarMenu.'
                    '.$btnVerPermisos.'
                    '.$btnEliminarMenu.'
                ';
                // Validar si tiene badge
                if(!(is_null($dataSubMenus->badgeColor) || is_null($dataSubMenus->badgeText))) {
                    $badge = '<span class="badge rounded-pill bg-'.$dataSubMenus->badgeColor.'">' . $dataSubMenus->badgeText . '</span>';
                } else {
                    $badge = "Ninguno";
                }

                $iconos = '
                    <b><i class="fab fa-font-awesome-flag"></i> Icono: </b>
                    <a role="button">
                        <i class="fa fa-' . $dataSubMenus->iconMenu . ' icon-menu"></i>
                    </a>
                    <br>
                    <b><i class="fas fa-tag"></i> Badge: </b>' . $badge . '<br>
                    '.$badgeInterfaz. '
                ';

                $output['data'][] = array(
                    $orden, // es #, se dibuja solo en el JS de datatable
                    $menus,
                    $iconos,
                    $acciones
                );
            }
        } else {
        }
	} // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>