<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    // arrayFormData = menuId ^ moduloId ^ nombreMenu
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    $dataEditMenu = $cloud->row("
        SELECT
            iconMenu,
            urlMenu,
            numOrdenMenu,
            menuSuperior,
            badgeColor,
            badgeText,
            estadoMenu
        FROM conf_menus
        WHERE menuId = ? AND flgDelete = '0'
    ", [$arrayFormData[0]]);

    // Validar si es menuSuperior
    $selectedTipoMenu = array("","");
    if($dataEditMenu->menuSuperior == "0") {
        $selectedTipoMenu[0] = "selected";
    } else {
        $selectedTipoMenu[1] = "selected";
    }

    $flgBadge = 0;
    // Validar si tiene badge
    if(!(is_null($dataEditMenu->badgeColor) || is_null($dataEditMenu->badgeText))) {
        $flgBadge = 1;
    } else {
        $badge = "Ninguno";
    }

    $ultimoOrden = $cloud->row("
        SELECT 
            numOrdenMenu
        FROM conf_menus
        WHERE moduloId = ? AND menuSuperior = '0' AND flgDelete = '0'
        ORDER BY numOrdenMenu DESC
        LIMIT 1
    ", [$arrayFormData[1]]);

    // Validar si posee sub-menus para colocarlo en "Tipo"
    $querySubMenus = "
        SELECT
            menuId
        FROM conf_menus
        WHERE menuSuperior = ? AND flgDelete = '0'
        ORDER BY numOrdenMenu                
    ";
    $existeSubMenu = $cloud->count($querySubMenus, [$arrayFormData[0]]);

    $requiredURL = ($existeSubMenu == 0) ? "required" : "";

    // Validar si esta o no en mantenimiento
    $checkedDisponible = "checked";
    if($dataEditMenu->estadoMenu == "Mantenimiento") {
        $checkedDisponible = "";
    } else {
        // Interfaz disponible
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="editar-menu">
<input type="hidden" id="moduloId" name="moduloId" value="<?php echo $arrayFormData[1]; ?>">
<input type="hidden" id="ordenRecomendado" name="ordenRecomendado" value="<?php echo $ultimoOrden->numOrdenMenu; ?>">
<div class="row">
    <div class="col-lg-8">
        <div class="form-outline mb-4">
            <i class="fas fa-list-ul trailing"></i>
            <input type="text" id="menu" class="form-control" name="menu" value="<?php echo $arrayFormData[2]; ?>" required />
            <label class="form-label" for="menu">Menú</label>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select id="tipoMenu" name="tipoMenu" style="width: 100%;" onchange="getTipoMenu();" required>
                <option></option>
                <?php 
                    if($existeSubMenu == 0) {
                        echo '<option value ="unico" ' . $selectedTipoMenu[0] . '>Único</option>';
                        echo '<option value="submenu" ' . $selectedTipoMenu[1] . '>Submenú</option>';
                    } else {
                        echo '<option value ="unico" ' . $selectedTipoMenu[0] . '>Dropdown ('.$existeSubMenu.' submenús)</option>';
                    }
                ?>
            </select>
        </div>
    </div>
</div>
<div id="divTipoMenu" class="row">
    <div class="col-lg-12">
        <div class="form-select-control mb-4">
            <select id="menuSuperior" name="menuSuperior" style="width: 100%;" onchange="getUltimoOrden('submenu',1);">
                <option></option>
                <?php 
                    $dataMenusPrincipales = $cloud->rows("
                        SELECT
                            menuId,
                            menu,
                            numOrdenMenu
                        FROM conf_menus
                        WHERE moduloId = ? AND menuSuperior = '0' AND flgDelete = '0'
                        ORDER BY numOrdenMenu
                    ",[$arrayFormData[1]]);
                    $arraySubMenuUltimoOrden = array("0");
                    foreach($dataMenusPrincipales as $dataMenusPrincipales) {
                        $subMenuUltimoOrden = $cloud->row("
                            SELECT
                                numOrdenMenu
                            FROM conf_menus
                            WHERE menuSuperior = ? AND flgDelete = '0'
                            ORDER BY numOrdenMenu DESC
                            LIMIT 1
                        ",[$dataMenusPrincipales->menuId]);
                        $arraySubMenuUltimoOrden[] = $subMenuUltimoOrden->numOrdenMenu;

                        if($dataEditMenu->menuSuperior == $dataMenusPrincipales->menuId) {
                            echo '<option value="' . $dataMenusPrincipales->menuId . '" selected>' . $dataMenusPrincipales->menu . '</option>';
                        } else {
                            echo '<option value="' . $dataMenusPrincipales->menuId . '">' . $dataMenusPrincipales->menu . '</option>';
                        }
                    }
                ?>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-outline mb-4">
            <i class="fab fa-font-awesome-flag trailing"></i>
            <input type="text" id="icono" class="form-control" name="icono" value="<?php echo $dataEditMenu->iconMenu; ?>" required />
            <label class="form-label" for="icono">Icono</label>
        </div>
    </div>
    <div id="divAddBadge" class="col-lg-8">
        <button type="button" class="btn btn-primary btn-sm" onclick="showHideBadge(1);">
            <i class="fas fa-plus-circle"></i> Badge
        </button>
    </div>
    <input type="hidden" id="flgBadge" name="flgBadge" value="0">
    <div id="divBadgeColor" class="col-lg-4">
        <div class="form-select-control mb-4">
            <select id="badgeColor" name="badgeColor" style="width: 100%;">
                <option></option>
                <?php 
                    $arrayBadgeColors = array("primary", "secondary", "success", "danger", "warning", "info", "light", "dark");
                    foreach ($arrayBadgeColors as $color) {
                        if($flgBadge == 1) { // Se asigno badge, seleccionar el color
                            if($dataEditMenu->badgeColor == $color) {
                                echo '<option value="' . $color . '" selected>' . $color . '</option>';
                            } else {
                                echo '<option value="' . $color . '">' . $color . '</option>';
                            }
                        } else { // No se asigno badge, dibujar select normal con placeholder
                            echo '<option value="' . $color . '">' . $color . '</option>';
                        }
                    }
                ?>
            </select>
        </div>
    </div>    
    <div id="divBadgeText" class="col-lg-4">
        <div class="form-outline mb-4">
            <i class="fas fa-quote-right trailing"></i>
            <input type="text" id="badgeText" class="form-control" name="badgeText" aria-describedby="eliminarBadge" value="<?php echo $dataEditMenu->badgeText; ?>" />
            <label class="form-label" for="badgeText">Badge Text</label>
            <div class="form-helper text-end">
                <a role="button" onclick="showHideBadge(0);">
                    <i class="fas fa-trash-alt"></i> Eliminar Badge
                </a>
            </div>
        </div>
    </div>   
</div>
<div class="row">
    <div class="col-lg-8">
        <div class="form-outline mb-4">
            <i class="fas fa-link trailing"></i>
            <input type="text" id="urlMenu" class="form-control" name="urlMenu" value="<?php echo $dataEditMenu->urlMenu; ?>" <?php echo $requiredURL; ?> />
            <label id="labelURL" class="form-label" for="urlMenu">URL Menú</label>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-outline mb-4">
            <i class="fas fa-list-ol trailing"></i>
            <input type="number" id="ordenMenu" class="form-control" name="ordenMenu" value="<?php echo $dataEditMenu->numOrdenMenu; ?>" min="1" required />
            <label id="labelOrden" class="form-label" for="ordenMenu">Orden Menú</label>
        </div>
    </div>
</div>
<div class="d-flex">
    <label class="form-check-label me-2" for="flgMenuDisponible">Interfaz en mantenimiento</label>
        <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" id="flgMenuDisponible" name="flgMenuDisponible" value="Disponible" <?php echo $checkedDisponible; ?> />
        </div>
    <label class="form-check-label" for="flgMenuDisponible">Interfaz disponible</label>
</div>
<br>
<script>
    function showHideBadge(flg) {
        if(flg == 0) {
            $("#divAddBadge").show();
            $("#divBadgeColor").hide();
            $("#divBadgeText").hide();
            $("#flgBadge").val(0);
            $("#badgeColor").removeAttr('required');
            $("#badgeText").removeAttr('required');
        } else {
            $("#divAddBadge").hide();
            $("#divBadgeColor").show();
            $("#divBadgeText").show();
            $("#flgBadge").val(1);
            $("#badgeColor").attr('required', 'required');
            $("#badgeText").attr('required', 'required');
        }
    }

    function getTipoMenu(start = 1) {
        if($("#tipoMenu").val() == "submenu") {
            $("#divTipoMenu").show();
            $("#labelURL").html("URL Submenú");
            $("#labelOrden").html("Orden Submenú");
            $("#menuSuperior").attr('required', 'required');
            getUltimoOrden('submenu', start);
        } else {
            $("#divTipoMenu").hide();
            $("#labelURL").html("URL Menú");
            $("#labelOrden").html("Orden Menú");
            $("#menuSuperior").removeAttr('required');
            getUltimoOrden('unico', start);
        }
    }

    function getUltimoOrden(flg, start) {
        let orden = 0;
        if(flg == "submenu") {
            let arrayOrden = <?php echo json_encode($arraySubMenuUltimoOrden); ?>;
            orden = (arrayOrden[$("#menuSuperior").prop('selectedIndex')] != null) ? parseInt(arrayOrden[$("#menuSuperior").prop('selectedIndex')]) : 0;
        } else {
            orden = <?php echo $ultimoOrden->numOrdenMenu; ?>;
        }

        if(start > 0) {
            $("#ordenMenu").val(orden);
        } else {
        }

        $("#ordenMenu").attr({
           "max": orden
        });
        $("#ordenRecomendado").val(orden);
    }

    $(document).ready(function() {
        $("#divBadgeColor").hide();
        $("#divBadgeText").hide();
        $("#divTipoMenu").hide();

        $("#tipoMenu").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo'
        });
        $("#badgeColor").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Badge Color'
        });
        $("#menuSuperior").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Menú Principal'
        });

        getTipoMenu(0);
        showHideBadge(<?php echo $flgBadge; ?>);

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        let arrayData = data.split("^");
                        if(arrayData[0] == "success") {
                            mensaje(
                                "Operación completada:",
                                arrayData[1],
                                "success"
                            );
                            $('#tblMenus').DataTable().ajax.reload(null, false);
                            $('#modal-container').modal("hide");
                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            }
        });
    });
</script>