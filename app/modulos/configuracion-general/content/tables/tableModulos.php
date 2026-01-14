<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataModulos = $cloud->rows("
    	SELECT
    		moduloId,
    		modulo,
    		descripcionModulo,
    		iconModulo,
    		urlModulo,
    		badgeColor,
    		badgeText
    	FROM conf_modulos
    	WHERE flgDelete = '0'
    ", []);
    $n = 0;
    foreach ($dataModulos as $dataModulos) {
    	$n += 1;

    	$modulos = '
    		<b><i class="fas fa-folder-open"></i> Módulo: </b>' . $dataModulos->modulo . '<br>
    		<b><i class="fas fa-edit"></i> Descripción: </b>' . $dataModulos->descripcionModulo . '<br>
    		<b><i class="fas fa-folder"></i> Carpeta: </b>' . $dataModulos->urlModulo;	

    	// Validar si tiene badge
    	if(!(is_null($dataModulos->badgeColor) || is_null($dataModulos->badgeText))) {
    		$badge = '<span class="badge rounded-pill bg-'.$dataModulos->badgeColor.'">' . $dataModulos->badgeText . '</span>';
    	} else {
    		$badge = "Ninguno";
    	}

    	$iconos = '
    		<b><i class="fab fa-font-awesome-flag"></i> Icono: </b>
    		<a role="button">
    			<i class="fa fa-' . $dataModulos->iconModulo . ' icon-menu"></i>
    		</a>
    		<br>
    		<b><i class="fas fa-tag"></i> Badge: </b>' . $badge;

    	$totalMenus = $cloud->count("
    		SELECT
    			menuId
    		FROM conf_menus
    		WHERE moduloId = ? AND flgDelete = '0'
    	", [$dataModulos->moduloId]);

        if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(33, $_SESSION["arrayPermisos"])) {
            $btnEditar = '
                <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalEditModulo(`'.$dataModulos->moduloId.'^'.$dataModulos->modulo.'`);">
                    <i class="fas fa-pencil-alt"></i>
                    <span class="ttiptext">Editar</span>
                </button>
            ';
        } else {
            $btnEditar = '';
        }

        if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(34, $_SESSION["arrayPermisos"])) {
            $btnVerMenus = '
                <button type="button" class="btn btn-primary btn-sm ttip" onclick="changePage(`'.$_SESSION["currentRoute"].'`, `modulos-menu`, `moduloId='.$dataModulos->moduloId.'&modulo='.$dataModulos->modulo.'`);">
                    <span class="badge rounded-pill bg-light" style="color: black;">'.$totalMenus.'</span>
                    <i class="fas fa-list-ul"></i>
                    <span class="ttiptext">Admin. menús</span>
                </button>
            ';
        } else {
            $btnVerMenus = '';
        }

	    $acciones = '
			'.$btnEditar.'
			'.$btnVerMenus.'
	    ';

	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $modulos,
	        $iconos,
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