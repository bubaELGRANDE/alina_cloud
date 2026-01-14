<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $flgMostrarTabla = 1;
    if(in_array(10, $_SESSION["arrayPermisos"])) { // Dev - Todos sin excepcion
        $dataPermisosUsuario = $cloud->rows("
            SELECT
                prsr.permisoUsuarioId AS permisoUsuarioId,
                prsr.menuPermisoId AS menuPermisoId,
                prsr.usuarioId AS usuarioId,
                CONCAT(
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-'),
                    ', ',
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-')
                ) AS nombrePersona,
                md.moduloId AS moduloId,
                md.modulo AS modulo,
                mn.menuId AS menuId,
                mn.menu AS menu,
                mn.menuSuperior AS menuSuperior,
                mn.urlMenu AS urlMenu
            FROM conf_permisos_usuario prsr 
            JOIN conf_usuarios us ON us.usuarioId = prsr.usuarioId
            JOIN th_personas per ON per.personaId = us.personaId
            JOIN conf_menus_permisos mper ON mper.menuPermisoId = prsr.menuPermisoId
            JOIN conf_menus mn ON mn.menuId = mper.menuId
            JOIN conf_modulos md ON md.moduloId = mn.moduloId
            WHERE prsr.flgDelete = ? AND mper.flgDelete = ? AND mn.flgDelete = ? AND md.flgDelete = ?
            GROUP BY mn.menuId, us.usuarioId
            ORDER BY nombrePersona, md.moduloId, mn.menuId
        ", ['0','0','0','0']);
    } else if(in_array(50, $_SESSION["arrayPermisos"])) { // Todos pero solo los menús que tiene asignados
        $dataPermisosUsuario = $cloud->rows("
            SELECT
                prsr.permisoUsuarioId AS permisoUsuarioId,
                prsr.menuPermisoId AS menuPermisoId,
                prsr.usuarioId AS usuarioId,
                CONCAT(
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-'),
                    ', ',
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-')
                ) AS nombrePersona,
                md.moduloId AS moduloId,
                md.modulo AS modulo,
                mn.menuId AS menuId,
                mn.menu AS menu,
                mn.menuSuperior AS menuSuperior,
                mn.urlMenu AS urlMenu
            FROM conf_permisos_usuario prsr 
            JOIN conf_usuarios us ON us.usuarioId = prsr.usuarioId
            JOIN th_personas per ON per.personaId = us.personaId
            JOIN conf_menus_permisos mper ON mper.menuPermisoId = prsr.menuPermisoId
            JOIN conf_menus mn ON mn.menuId = mper.menuId
            JOIN conf_modulos md ON md.moduloId = mn.moduloId
            WHERE mper.menuId IN (
                SELECT mpsub.menuId FROM conf_permisos_usuario pmsub 
                JOIN conf_menus_permisos mpsub ON mpsub.menuPermisoId = pmsub.menuPermisoId
                WHERE pmsub.usuarioId = ?
            ) AND prsr.flgDelete = '0' AND mper.flgDelete = '0' AND mn.flgDelete = '0' AND md.flgDelete = '0'
            GROUP BY mn.menuId, us.usuarioId
            ORDER BY nombrePersona, md.moduloId, mn.menuId
        ", [$_SESSION['usuarioId']]);
    } else if(in_array(51, $_SESSION["arrayPermisos"])) { // Pendiente solo con menús que tiene asignados
        // Get usuarios a cargo
        // wherePermiso = us.personaId IN ($arrayPersonasACargo) AND
        $dataPermisosUsuario = $cloud->rows("
            SELECT
                prsr.permisoUsuarioId AS permisoUsuarioId,
                prsr.menuPermisoId AS menuPermisoId,
                prsr.usuarioId AS usuarioId,
                CONCAT(
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-'),
                    ', ',
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-')
                ) AS nombrePersona,
                md.moduloId AS moduloId,
                md.modulo AS modulo,
                mn.menuId AS menuId,
                mn.menu AS menu,
                mn.menuSuperior AS menuSuperior,
                mn.urlMenu AS urlMenu
            FROM conf_permisos_usuario prsr 
            JOIN conf_usuarios us ON us.usuarioId = prsr.usuarioId
            JOIN th_personas per ON per.personaId = us.personaId
            JOIN conf_menus_permisos mper ON mper.menuPermisoId = prsr.menuPermisoId
            JOIN conf_menus mn ON mn.menuId = mper.menuId
            JOIN conf_modulos md ON md.moduloId = mn.moduloId
            WHERE mper.menuId IN (
                SELECT mpsub.menuId FROM conf_permisos_usuario pmsub 
                JOIN conf_menus_permisos mpsub ON mpsub.menuPermisoId = pmsub.menuPermisoId
                WHERE pmsub.usuarioId = ?
            ) AND prsr.flgDelete = '0' AND mper.flgDelete = '0' AND mn.flgDelete = '0' AND md.flgDelete = '0'
            GROUP BY mn.menuId, us.usuarioId
            ORDER BY nombrePersona, md.moduloId, mn.menuId
        ", [$_SESSION['usuarioId']]);
    } else {
        // No se asignó ningún permiso
        $flgMostrarTabla = 0;
    }

    if($flgMostrarTabla == 1) {
        $n = 0;
        foreach ($dataPermisosUsuario as $dataPermisosUsuario) {
            $n += 1;

            $queryUsuariosPermiso = "
                SELECT
                    prs.permisoUsuarioId
                FROM conf_permisos_usuario prs
                JOIN conf_menus_permisos mpr ON mpr.menuPermisoId = prs.menuPermisoId
                JOIN conf_usuarios us ON us.usuarioId = prs.usuarioId
                WHERE prs.usuarioId = ? AND mpr.menuId = ? AND prs.flgDelete = '0' AND us.flgDelete = '0'
            ";

            $cantidadUsuariosPermiso = $cloud->count($queryUsuariosPermiso, [$dataPermisosUsuario->usuarioId, $dataPermisosUsuario->menuId]);

            // Validar si tiene sub-menús para asegurarse que es dropdown, menu normal o submenu
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
                WHERE menuId = ? AND flgDelete = '0'
                ORDER BY numOrdenMenu                
            ";
            $existeSubMenu = $cloud->count($querySubMenus, [$dataPermisosUsuario->menuSuperior]);

            $disabledAcciones = "";

            if(is_null($dataPermisosUsuario->urlMenu) || $dataPermisosUsuario->urlMenu == "") { 
                // Validar si todavía tiene submenús
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
                $todaviaExistenSubmenus = $cloud->count($querySubMenus, [$dataPermisosUsuario->menuId]);
                if($todaviaExistenSubmenus == 0) {
                    // Es padre - dropdown
                    $tipoMenu = "(URL No asignada)";
                } else {
                    // Es padre - dropdown
                    $tipoMenu = "(Dropdown [".$todaviaExistenSubmenus."])";
                    $disabledAcciones = "disabled";
                }
            } else { // Menú / submenú normal
                if($existeSubMenu == 0) {
                    $tipoMenu = "(Único)";
                } else {
                    $tipoMenu = "(Submenú)";
                }
            }

            $permisos = '
                <b><i class="fas fa-user"></i> Usuario: </b> ' . $dataPermisosUsuario->nombrePersona . '<br>
                <b><i class="fas fa-folder-open"></i> Módulo: </b> ' . $dataPermisosUsuario->modulo . '<br>
                <b><i class="fas fa-bars"></i> Menú: </b>' . $dataPermisosUsuario->menu . ' ' . $tipoMenu;

            if($disabledAcciones == "disabled") { // Es dropdown
                if(in_array(10, $_SESSION["arrayPermisos"]) || in_array(52, $_SESSION["arrayPermisos"])) {
                    $btnEditarPermisos = '
                        <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalEditPermisos(`'.$dataPermisosUsuario->menuId.'^'.$dataPermisosUsuario->usuarioId.'^'.$dataPermisosUsuario->nombrePersona.'^'.$dataPermisosUsuario->modulo.'^'.$dataPermisosUsuario->menu.'^dropdown`);">
                            <span class="badge rounded-pill bg-light" style="color: black;">'.$cantidadUsuariosPermiso.'</span>
                            <i class="fas fa-user-lock"></i>
                            <span class="ttiptext">Editar permisos</span>
                        </button>
                    ';
                } else {
                    $btnEditarPermisos = "";
                }

                if(in_array(10, $_SESSION["arrayPermisos"]) || in_array(53, $_SESSION["arrayPermisos"])) {
                    $btnEliminar = '
                        <button type="button" class="btn btn-danger btn-sm" disabled>
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                } else {
                    $btnEliminar = "";
                }
                $acciones = '
                    '.$btnEditarPermisos.'
                    '.$btnEliminar.'
                ';  
            } else {
                if(in_array(10, $_SESSION["arrayPermisos"]) || in_array(52, $_SESSION["arrayPermisos"])) {
                    $btnEditarPermisos = '
                        <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalEditPermisos(`'.$dataPermisosUsuario->menuId.'^'.$dataPermisosUsuario->usuarioId.'^'.$dataPermisosUsuario->nombrePersona.'^'.$dataPermisosUsuario->modulo.'^'.$dataPermisosUsuario->menu.'^menu`);">
                            <span class="badge rounded-pill bg-light" style="color: black;">'.$cantidadUsuariosPermiso.'</span>
                            <i class="fas fa-user-lock"></i>
                            <span class="ttiptext">Editar permisos</span>
                        </button>
                    ';
                } else {
                    $btnEditarPermisos = "";
                }

                if(in_array(10, $_SESSION["arrayPermisos"]) || in_array(53, $_SESSION["arrayPermisos"])) {
                    $btnEliminar = '
                        <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarPermiso(`'.$dataPermisosUsuario->menuId.'^'.$dataPermisosUsuario->usuarioId.'^'.$dataPermisosUsuario->menu.'^'.$dataPermisosUsuario->nombrePersona.'^'.$dataPermisosUsuario->menuSuperior.'`);">
                            <i class="fas fa-trash-alt"></i>
                            <span class="ttiptext">Eliminar permisos</span>
                        </button>
                    ';
                } else {
                    $btnEliminar = "";
                }
                $acciones = '
                    '.$btnEditarPermisos.'
                    '.$btnEliminar.'
                ';  
            }      

            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $permisos,
                $acciones
            );
        } // foreach
    } else {
        $n = 0;
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>