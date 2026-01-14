<?php 
    @session_start();

    // 0 = "m" + moduloId + "-" + menuId
    // 1 = modulo
    // 2 = menuSuperiorId
    $arrayActive = $_SESSION["pageActive"];
    $arrayActive = explode("^", $arrayActive);
    $activeClass = "";
?>
<ul>
    <li class="header-menu">
        <span>Principal</span>
    </li>
    <?php 
        $dataMPrincipal = $cloud->rows("
            SELECT
                menuId,
                menu,
                iconMenu,
                urlMenu,
                menuSuperior,
                badgeColor,
                badgeText
            FROM conf_menus
            WHERE moduloId = ? AND flgDelete='0'
            ORDER BY numOrdenMenu, menuSuperior
        ", [1]);
        foreach ($dataMPrincipal as $dataMPrincipal) {
            $idMenu = "m1-" . $dataMPrincipal->menuId;
            $activeClass = ($arrayActive[0] == $idMenu) ? "active" : "";
    ?>
            <li class="sidebar-item <?php echo $activeClass; ?>">
                <a id="<?php echo $idMenu; ?>" role="button" class="menu-click" onclick="asyncPage(<?php echo $dataMPrincipal->menuId; ?>,'principal');">
                    <i class="fa fa-<?php echo $dataMPrincipal->iconMenu; ?>"></i>
                    <span><?php echo $dataMPrincipal->menu; ?></span>
                    <?php 
                        // Validar si tiene badges
                        if(!(is_null($dataMPrincipal->badgeColor) || is_null($dataMPrincipal->badgeText))) {
                    ?>
                            <span class="badge rounded-pill bg-<?php echo $dataMPrincipal->badgeColor; ?>"><?php echo $dataMPrincipal->badgeText; ?></span>
                    <?php 
                        } else {
                        }
                    ?>
                </a>
            </li>
    <?php 
            $activeClass = "";
        } // foreach dataMPrincipal
    ?>
    <li class="header-menu">
        <span>Módulos</span>
    </li>
    <?php 
        $n = 0;
        // Escritorio, Institucional, Reportes
        $modulosNoMostrar = "1, 2, 8";
        $dataModulos = $cloud->rows("
            SELECT
                md.moduloId AS moduloId,
                md.modulo AS modulo,
                md.descripcionModulo AS descripcionModulo,
                md.iconModulo AS iconModulo,
                md.urlModulo AS urlModulo,
                md.badgeColor AS badgeColor,
                md.badgeText AS badgeText
            FROM conf_permisos_usuario perm
            JOIN conf_menus_permisos mp ON mp.menuPermisoId = perm.menuPermisoId
            JOIN conf_menus m ON m.menuId = mp.menuId
            JOIN conf_modulos md ON md.moduloId = m.moduloId
            WHERE md.moduloId NOT IN ($modulosNoMostrar) AND perm.usuarioId = ? AND
            (perm.flgDelete = '0' AND mp.flgDelete = '0' AND m.flgDelete = '0' AND md.flgDelete = '0')
            GROUP BY md.moduloId
        ",[$_SESSION["usuarioId"]]);
        foreach($dataModulos as $dataModulos) {
            $n += 1;
            $queryGetMenuPadres = "
                SELECT
                    m.menuId AS menuId,
                    m.menu AS menu,
                    m.iconMenu AS iconMenu,
                    m.urlMenu AS urlMenu,
                    m.menuSuperior AS menuSuperior,
                    m.badgeColor AS badgeColor,
                    m.badgeText AS badgeText
                FROM conf_permisos_usuario perm
                JOIN conf_menus_permisos mp ON mp.menuPermisoId = perm.menuPermisoId
                JOIN conf_menus m ON m.menuId = mp.menuId
                JOIN conf_modulos md ON md.moduloId = m.moduloId
                WHERE m.moduloId = ? AND perm.usuarioId = ? AND m.menuSuperior = '0' AND
                (perm.flgDelete = '0' AND mp.flgDelete = '0' AND m.flgDelete = '0' AND md.flgDelete = '0')
                GROUP BY m.menuId
                ORDER BY m.numOrdenMenu, m.menuSuperior
            ";
            // AGREGAR JOINS PARA PERMISOS, revisar consulta 
            $countMenus = $cloud->count($queryGetMenuPadres, [$dataModulos->moduloId, $_SESSION["usuarioId"]]);
            if($countMenus > 0) {
                // 0 = "m" + moduloId + "-" + menuId
                // 1 = modulo
                // 2 = menuSuperior
                $idModulo = "m" . $dataModulos->moduloId;
                $activeClass = ($arrayActive[1] == $dataModulos->moduloId) ? "active" : "";
    ?>
                <li class="sidebar-dropdown <?php echo $activeClass; ?>">
                    <a id="<?php echo $idModulo; ?>" role="button">
                        <i class="fa fa-<?php echo $dataModulos->iconModulo; ?>"></i>
                        <span><?php echo $dataModulos->modulo; ?></span>
                        <?php 
                            // Validar si tiene badges
                            if(!(is_null($dataModulos->badgeColor) || is_null($dataModulos->badgeText))) {
                        ?>
                                <span class="badge rounded-pill bg-<?php echo $dataModulos->badgeColor; ?>"><?php echo $dataModulos->badgeText; ?></span>
                        <?php 
                            } else {
                            }
                        ?>
                    </a>
                    <div class="sidebar-submenu">
                        <ul>
                            <?php 
                                $dataMenusPadres = $cloud->rows($queryGetMenuPadres, [$dataModulos->moduloId, $_SESSION["usuarioId"]]);

                                foreach ($dataMenusPadres as $dataMenusPadres) {
                                    $n += 1;
                                    if(is_null($dataMenusPadres->urlMenu)) { // Tiene sub-submenu
                                        $idSubMenuSuperior = "m" . $dataModulos->moduloId . "-" . $dataMenusPadres->menuId;
                                        $formarIdArray = "m" . $arrayActive[1] . "-" . $arrayActive[2];
                                        $activeClass = ($formarIdArray == $idSubMenuSuperior) ? "active" : "";
                            ?>
                                        <li class="sidebar-sub-dropdown <?php echo $activeClass; ?>">
                                            <a id="<?php echo $idSubMenuSuperior; ?>" role="button">
                                                <i class="fas fa-<?php echo $dataMenusPadres->iconMenu; ?>"></i>
                                                <span><?php echo $dataMenusPadres->menu; ?></span>
                                                <?php 
                                                    // Validar menú badges
                                                    if((!(is_null($dataMenusPadres->badgeColor) || is_null($dataMenusPadres->badgeText)))) {
                                                ?>
                                                        <span class="badge rounded-pill bg-<?php echo $dataMenusPadres->badgeColor; ?>"><?php echo $dataMenusPadres->badgeText; ?></span>
                                                <?php 
                                                    } else {
                                                    }
                                                ?>
                                            </a>
                                            <div class="sidebar-sub-submenu">
                                                <ul>
                                                    <?php 
                                                        // Traer los sub-submenu
                                                        $dataSubSubMenus = $cloud->rows("
                                                            SELECT
                                                                m.menuId AS menuId,
                                                                m.menu AS menu,
                                                                m.iconMenu AS iconMenu,
                                                                m.urlMenu AS urlMenu,
                                                                m.menuSuperior AS menuSuperior,
                                                                m.badgeColor AS badgeColor,
                                                                m.badgeText AS badgeText
                                                            FROM conf_permisos_usuario perm
                                                            JOIN conf_menus_permisos mp ON mp.menuPermisoId = perm.menuPermisoId
                                                            JOIN conf_menus m ON m.menuId = mp.menuId
                                                            JOIN conf_modulos md ON md.moduloId = m.moduloId
                                                            WHERE m.moduloId = ? AND perm.usuarioId = ? AND m.menuSuperior = ? AND
                                                            (perm.flgDelete = '0' AND mp.flgDelete = '0' AND m.flgDelete = '0' AND md.flgDelete = '0')
                                                            GROUP BY m.menuId
                                                            ORDER BY m.numOrdenMenu, m.menuSuperior
                                                        ", [$dataModulos->moduloId, $_SESSION["usuarioId"], $dataMenusPadres->menuId]);
                                                        foreach ($dataSubSubMenus as $dataSubSubMenus) {
                                                            $n += 1;
                                                            $idMenu = "m" . $dataModulos->moduloId . "-" . $dataSubSubMenus->menuId;
                                                            $activeClass = ($arrayActive[0] == $idMenu) ? "active" : ""; 
                                                    ?>
                                                            <li class="sidebar-item <?php echo $activeClass; ?>">
                                                                <a id="<?php echo $idMenu; ?>" role="button" onclick="asyncPage(<?php echo $dataSubSubMenus->menuId; ?>, 'sub-submenu');">
                                                                    <i class="fa fa-<?php echo $dataSubSubMenus->iconMenu; ?>"></i>
                                                                    <span><?php echo $dataSubSubMenus->menu; ?></span>
                                                                    <?php 
                                                                        // Validar si tiene badges
                                                                        if(!(is_null($dataSubSubMenus->badgeColor) || is_null($dataSubSubMenus->badgeText))) {
                                                                    ?>
                                                                            <span class="badge rounded-pill bg-<?php echo $dataSubSubMenus->badgeColor; ?>"><?php echo $dataSubSubMenus->badgeText; ?></span>
                                                                    <?php 
                                                                        } else {
                                                                        }
                                                                    ?>
                                                                </a>
                                                            </li>
                                                    <?php 
                                                        } // foreach dataSubSubMenus
                                                    ?>
                                                </ul>
                                            </div>
                                        </li>
                            <?php 
                                    } else { // es menú único, sin sub-submenu
                                        $idMenu = "m" . $dataModulos->moduloId . "-" . $dataMenusPadres->menuId;
                                        $activeClass = ($arrayActive[0] == $idMenu) ? "active" : "";
                            ?>
                                        <li class="sidebar-item <?php echo $activeClass; ?>">
                                            <a id="<?php echo $idMenu; ?>" role="button" onclick="asyncPage(<?php echo $dataMenusPadres->menuId; ?>, 'submenu');">
                                                <i class="fas fa-<?php echo $dataMenusPadres->iconMenu; ?>"></i>
                                                <span><?php echo $dataMenusPadres->menu; ?></span>
                                                <?php 
                                                    // Validar menú badges
                                                    if((!(is_null($dataMenusPadres->badgeColor) || is_null($dataMenusPadres->badgeText)))) {
                                                ?>
                                                        <span class="badge rounded-pill bg-<?php echo $dataMenusPadres->badgeColor; ?>"><?php echo $dataMenusPadres->badgeText; ?></span>
                                                <?php 
                                                    } else {
                                                    }
                                                ?>
                                            </a>
                                        </li>
                            <?php 
                                    } // if null urlMenu
                                } // foreach dataMenusPadres
                            ?>
                        </ul>
                    </div>
                </li>
    <?php 
            } else { // no tiene menús, será un acceso directo desde módulos (no debe de suceder)
                $idMenu = "m" . $dataModulos->moduloId;
                $activeClass = ($arrayActive[0] == $idMenu) ? "active" : "";
    ?>
                <li class="sidebar-item">
                    <a id="<?php echo $idMenu; ?>" role="button" class="menu-click" onclick="asyncPage(<?php echo $dataModulos->moduloId; ?>, 'modulo');">
                        <i class="fa fa-<?php echo $dataModulos->iconModulo; ?>"></i>
                        <span><?php echo $dataModulos->modulo; ?></span>
                        <?php 
                            // Validar si tiene badges
                            if(!(is_null($dataModulos->badgeColor) || is_null($dataModulos->badgeText))) {
                        ?>
                                <span class="badge rounded-pill bg-<?php echo $dataModulos->badgeColor; ?>"><?php echo $dataModulos->badgeText; ?></span>
                        <?php 
                            } else {
                            }
                        ?>
                    </a>
                </li>
    <?php 
            } // if countMenus > 0
            $activeClass = "";
        } // foreach dataModulos
        if($n == 0) {
            echo '
                <li class="sidebar-item">
                    <a role="button" class="menu-click">No se han asignado permisos</a>
                </li>
            ';
        } else {

        }

        $n = 0;
        // Apartado REPORTES de la sidebar
        $dataModulos = $cloud->rows("
            SELECT
                md.moduloId AS moduloId,
                md.modulo AS modulo,
                md.descripcionModulo AS descripcionModulo,
                md.iconModulo AS iconModulo,
                md.urlModulo AS urlModulo,
                md.badgeColor AS badgeColor,
                md.badgeText AS badgeText
            FROM conf_permisos_usuario perm
            JOIN conf_menus_permisos mp ON mp.menuPermisoId = perm.menuPermisoId
            JOIN conf_menus m ON m.menuId = mp.menuId
            JOIN conf_modulos md ON md.moduloId = m.moduloId
            WHERE md.moduloId = ? AND perm.usuarioId = ? AND
            (perm.flgDelete = '0' AND mp.flgDelete = '0' AND m.flgDelete = '0' AND md.flgDelete = '0')
            GROUP BY md.moduloId
        ",[8, $_SESSION["usuarioId"]]);
        foreach($dataModulos as $dataModulos) {
            if($n == 0) {
                echo '
                    <li class="header-menu">
                        <span>Reportes</span>
                    </li>
                ';
            } else {
                // Ya se dibujo la etiqueta
            }
            $n += 1;
            $queryGetMenuPadres = "
                SELECT
                    m.menuId AS menuId,
                    m.menu AS menu,
                    m.iconMenu AS iconMenu,
                    m.urlMenu AS urlMenu,
                    m.menuSuperior AS menuSuperior,
                    m.badgeColor AS badgeColor,
                    m.badgeText AS badgeText
                FROM conf_permisos_usuario perm
                JOIN conf_menus_permisos mp ON mp.menuPermisoId = perm.menuPermisoId
                JOIN conf_menus m ON m.menuId = mp.menuId
                JOIN conf_modulos md ON md.moduloId = m.moduloId
                WHERE m.moduloId = ? AND perm.usuarioId = ? AND m.menuSuperior = '0' AND
                (perm.flgDelete = '0' AND mp.flgDelete = '0' AND m.flgDelete = '0' AND md.flgDelete = '0')
                GROUP BY m.menuId
                ORDER BY m.numOrdenMenu, m.menuSuperior
            ";
            // AGREGAR JOINS PARA PERMISOS, revisar consulta 
            $countMenus = $cloud->count($queryGetMenuPadres, [$dataModulos->moduloId, $_SESSION["usuarioId"]]);
            if($countMenus > 0) {
                // 0 = "m" + moduloId + "-" + menuId
                // 1 = modulo
                // 2 = menuSuperior
                $idModulo = "m" . $dataModulos->moduloId;
                $activeClass = ($arrayActive[1] == $dataModulos->moduloId) ? "active" : "";

                $dataMenusPadres = $cloud->rows($queryGetMenuPadres, [$dataModulos->moduloId, $_SESSION["usuarioId"]]);

                foreach ($dataMenusPadres as $dataMenusPadres) {
                    $n += 1;
                    $idSubMenuSuperior = "m" . $dataModulos->moduloId . "-" . $dataMenusPadres->menuId;
                    $formarIdArray = "m" . $arrayActive[1] . "-" . $arrayActive[2];
                    $activeClass = ($formarIdArray == $idSubMenuSuperior) ? "active" : "";
    ?>
                    <li class="sidebar-dropdown <?php echo $activeClass; ?>">
                        <a id="<?php echo $idSubMenuSuperior; ?>" role="button">
                            <i class="fas fa-<?php echo $dataMenusPadres->iconMenu; ?>"></i>
                            <span><?php echo $dataMenusPadres->menu; ?></span>
                            <?php 
                                // Validar menú badges
                                if((!(is_null($dataMenusPadres->badgeColor) || is_null($dataMenusPadres->badgeText)))) {
                            ?>
                                    <span class="badge rounded-pill bg-<?php echo $dataMenusPadres->badgeColor; ?>"><?php echo $dataMenusPadres->badgeText; ?></span>
                            <?php 
                                } else {
                                }
                            ?>
                        </a>
                        <div class="sidebar-submenu" <?php echo ($activeClass == "active" ? 'style="display: block;"' : ''); ?>>
                            <ul>
                                <?php 
                                    // Traer los sub-submenu
                                    $dataSubSubMenus = $cloud->rows("
                                        SELECT
                                            m.menuId AS menuId,
                                            m.menu AS menu,
                                            m.iconMenu AS iconMenu,
                                            m.urlMenu AS urlMenu,
                                            m.menuSuperior AS menuSuperior,
                                            m.badgeColor AS badgeColor,
                                            m.badgeText AS badgeText
                                        FROM conf_permisos_usuario perm
                                        JOIN conf_menus_permisos mp ON mp.menuPermisoId = perm.menuPermisoId
                                        JOIN conf_menus m ON m.menuId = mp.menuId
                                        JOIN conf_modulos md ON md.moduloId = m.moduloId
                                        WHERE m.moduloId = ? AND perm.usuarioId = ? AND m.menuSuperior = ? AND
                                        (perm.flgDelete = '0' AND mp.flgDelete = '0' AND m.flgDelete = '0' AND md.flgDelete = '0')
                                        GROUP BY m.menuId
                                        ORDER BY m.numOrdenMenu, m.menuSuperior
                                    ", [$dataModulos->moduloId, $_SESSION["usuarioId"], $dataMenusPadres->menuId]);
                                    foreach ($dataSubSubMenus as $dataSubSubMenus) {
                                        $n += 1;
                                        $idMenu = "m" . $dataModulos->moduloId . "-" . $dataSubSubMenus->menuId;
                                        $activeClass = ($arrayActive[0] == $idMenu) ? "active" : ""; 
                                ?>
                                        <li class="sidebar-item <?php echo $activeClass; ?>">
                                            <a id="<?php echo $idMenu; ?>" role="button" onclick="asyncPage(<?php echo $dataSubSubMenus->menuId; ?>, 'sub-submenu');">
                                                <i class="fa fa-<?php echo $dataSubSubMenus->iconMenu; ?>"></i>
                                                <span><?php echo $dataSubSubMenus->menu; ?></span>
                                                <?php 
                                                    // Validar si tiene badges
                                                    if(!(is_null($dataSubSubMenus->badgeColor) || is_null($dataSubSubMenus->badgeText))) {
                                                ?>
                                                        <span class="badge rounded-pill bg-<?php echo $dataSubSubMenus->badgeColor; ?>"><?php echo $dataSubSubMenus->badgeText; ?></span>
                                                <?php 
                                                    } else {
                                                    }
                                                ?>
                                            </a>
                                        </li>
                                <?php 
                                    } // foreach dataSubSubMenus
                                ?>
                            </ul>
                        </div>
                    </li>
    <?php 
                } // foreach dataMenusPadre
            } else { // no tiene menús, será un acceso directo desde módulos (no debe de suceder)
                $idMenu = "m" . $dataModulos->moduloId;
                $activeClass = ($arrayActive[0] == $idMenu) ? "active" : "";
    ?>
                <li class="sidebar-item">
                    <a id="<?php echo $idMenu; ?>" role="button" class="menu-click" onclick="asyncPage(<?php echo $dataModulos->moduloId; ?>, 'modulo');">
                        <i class="fa fa-<?php echo $dataModulos->iconModulo; ?>"></i>
                        <span><?php echo $dataModulos->modulo; ?></span>
                        <?php 
                            // Validar si tiene badges
                            if(!(is_null($dataModulos->badgeColor) || is_null($dataModulos->badgeText))) {
                        ?>
                                <span class="badge rounded-pill bg-<?php echo $dataModulos->badgeColor; ?>"><?php echo $dataModulos->badgeText; ?></span>
                        <?php 
                            } else {
                            }
                        ?>
                    </a>
                </li>
    <?php 
            } // if countMenus > 0
            $activeClass = "";
        } // foreach dataModulos
    ?>
    <li class="header-menu">
        <span>Institucional</span>
    </li>
    <?php 
        $dataMPrincipal = $cloud->rows("
            SELECT
                menuId,
                menu,
                iconMenu,
                urlMenu,
                menuSuperior,
                badgeColor,
                badgeText
            FROM conf_menus
            WHERE moduloId = ? AND flgDelete='0'
            ORDER BY numOrdenMenu, menuSuperior
        ", [2]);
        foreach ($dataMPrincipal as $dataMPrincipal) {
            $idMenu = "m2-" . $dataMPrincipal->menuId;
            $activeClass = ($arrayActive[0] == $idMenu) ? "active" : "";
    ?>
            <li class="sidebar-item <?php echo $activeClass ?>">
                <a id="<?php echo $idMenu; ?>" role="button" class="menu-click" onclick="asyncPage(<?php echo $dataMPrincipal->menuId; ?>,'institucional');">
                    <i class="fa fa-<?php echo $dataMPrincipal->iconMenu; ?>"></i>
                    <span><?php echo $dataMPrincipal->menu; ?></span>
                    <?php 
                        // Validar si tiene badges
                        if(!(is_null($dataMPrincipal->badgeColor) || is_null($dataMPrincipal->badgeText))) {
                    ?>
                            <span class="badge rounded-pill bg-<?php echo $dataMPrincipal->badgeColor; ?>"><?php echo $dataMPrincipal->badgeText; ?></span>
                    <?php 
                        } else {
                        }
                    ?>
                </a>
            </li>
    <?php 
            $activeClass = "";
        } // foreach dataMPrincipal
    ?>
</ul>