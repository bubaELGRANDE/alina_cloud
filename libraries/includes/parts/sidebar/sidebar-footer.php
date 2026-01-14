<div class="dropdown dropup"></div>
<div class="dropdown dropup">
<!--     <a class="dropdown-toggle dropdown-toggle-split" role="button" role="button" id="dropdownMenuLink" data-mdb-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-bell"></i>
        <span class="badge rounded-pill bg-warning badge-notification">3</span>
    </a>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
        <li><a class="dropdown-item" role="button">Opción 1</a></li>
        <li><a class="dropdown-item" role="button">Opción 2</a></li>
        <li><a class="dropdown-item" role="button">Opción 3</a></li>
    </ul> -->
</div>
<div class="dropdown dropup">
    <!-- <a class="dropdown-toggle dropdown-toggle-split" role="button" role="button" id="dropdownMenuLink" data-mdb-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-envelope"></i>
        <span class="badge rounded-pill bg-success badge-notification">7</span>
    </a>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
        <li><a class="dropdown-item" role="button">Opción 4</a></li>
        <li><a class="dropdown-item" role="button">Opción 5</a></li>
        <li><a class="dropdown-item" role="button">Opción 6</a></li>
    </ul> -->
</div>
<div class="dropdown dropup">
    <a class="dropdown-toggle dropdown-toggle-split ttip" role="button" role="button" id="dropdownMenuLink" data-mdb-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-user-circle"></i>
        <!-- <span class="badge bg-danger badge-dot"></span> -->
        <span class="ttiptext">Mi Perfil</span>
    </a>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
        <!-- <li><a class="dropdown-item" role="button">Opción 7</a></li>
        <li><a class="dropdown-item" role="button">Opción 8</a></li>
        <li><a class="dropdown-item" role="button">Opción 7</a></li>
        <li><a class="dropdown-item" role="button">Opción 8</a></li>-->
        <li><a class="dropdown-item" role="button" onclick="changePage(`../app/modulos/mi-perfil/`, `perfil.php`, ``);"><i class="fas fa-user"></i> Ver perfil</a></li>
        <li><hr class="dropdown-divider" /></li> 
        <li>
            <?php
                $queryTema = "
                    SELECT 
                        custom
                    FROM mip_perfil_custom 
                    WHERE usuarioId = ? AND tipoCustom = 'Tema' AND flgDelete = '0'
                ";
                $dataUserTema = $cloud->row($queryTema, [$_SESSION["usuarioId"]]);
                if($dataUserTema->custom == "dark") {
                    echo '<a class="dropdown-item drk-toggle" role="button" onclick="asyncTheme();">Modo claro <span class="badge rounded-pill bg-info text-dark"><i class="fas fa-sun fa-lg"></i></a></span>';
                } else{
                    echo '<a class="dropdown-item drk-toggle" role="button" onclick="asyncTheme();">Modo oscuro <span class="badge rounded-pill bg-dark"><i class="fas fa-moon fa-lg"></i></span></a>';
                }
            ?>
        </li>
    </ul>
</div>
<div>
    <a role="button" id="cerrarSesion" class="ttip">
        <i class="fa fa-power-off"></i>
        <span class="ttiptext">Cerrar sesión</span>
    </a>
</div>
<div class="pinned-footer">
    <a role="button">
        <i class="fas fa-ellipsis-h"></i>
    </a>
</div>