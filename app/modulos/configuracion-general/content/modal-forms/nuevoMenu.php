<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    // arrayFormData = moduloId
    $moduloId = $_POST["arrayFormData"];
    $existenMenus = $cloud->count("
        SELECT 
            numOrdenMenu
        FROM conf_menus
        WHERE moduloId = ? AND menuSuperior = '0' AND flgDelete = '0'
    ", [$moduloId]);
    if($existenMenus > 0) {
        $ultimoOrden = $cloud->row("
            SELECT 
                numOrdenMenu
            FROM conf_menus
            WHERE moduloId = ? AND menuSuperior = '0' AND flgDelete = '0'
            ORDER BY numOrdenMenu DESC
            LIMIT 1
        ", [$moduloId]);
        $ultimoMenu = $ultimoOrden->numOrdenMenu + 1;
    } else {
        $ultimoMenu = 1;
    }

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="nuevo-menu">
<input type="hidden" id="moduloId" name="moduloId" value="<?php echo $moduloId; ?>">
<input type="hidden" id="ordenRecomendado" name="ordenRecomendado" value="0">
<div class="row">
    <div class="col-lg-8">
        <div class="form-outline mb-4">
            <i class="fas fa-list-ul trailing"></i>
            <input type="text" id="menu" class="form-control" name="menu" required />
            <label class="form-label" for="menu">Menú</label>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select id="tipoMenu" name="tipoMenu" style="width: 100%;" onchange="getTipoMenu();" required>
                <option></option>
                <option value="unico">Único</option>
                <option value="submenu">Submenú</option>
            </select>
        </div>
    </div>
</div>
<div id="divTipoMenu" class="row">
    <div class="col-lg-12">
        <div class="form-select-control mb-4">
            <select id="menuSuperior" name="menuSuperior" style="width: 100%;" onchange="getUltimoOrden();">
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
                    ",[$moduloId]);
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
                        echo '<option value="' . $dataMenusPrincipales->menuId . '">' . $dataMenusPrincipales->menu . '</option>';
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
            <input type="text" id="icono" class="form-control" name="icono" value="fas fa-" required />
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
                        echo '<option value="' . $color . '">' . $color . '</option>';
                    }
                ?>
            </select>
        </div>
    </div>    
    <div id="divBadgeText" class="col-lg-4">
        <div class="form-outline mb-4">
            <i class="fas fa-quote-right trailing"></i>
            <input type="text" id="badgeText" class="form-control" name="badgeText" aria-describedby="eliminarBadge" />
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
            <input type="text" id="urlMenu" class="form-control" name="urlMenu" value="content/views/" required />
            <label id="labelURL" class="form-label" for="urlMenu">URL Menú</label>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-outline mb-4">
            <i class="fas fa-list-ol trailing"></i>
            <input type="number" id="ordenMenu" class="form-control" name="ordenMenu" value="<?php echo $ultimoMenu; ?>" min="1" required />
            <label id="labelOrden" class="form-label" for="ordenMenu">Orden Menú</label>
        </div>
    </div>
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

    function getTipoMenu() {
        if($("#tipoMenu").val() == "submenu") {
            getUltimoOrden();
            $("#divTipoMenu").show();
            $("#labelURL").html("URL Submenú");
            $("#labelOrden").html("Orden Submenú");
            $("#menuSuperior").attr('required', 'required');
        } else {
            $("#divTipoMenu").hide();
            $("#labelURL").html("URL Menú");
            $("#ordenMenu").val(<?php echo $ultimoMenu; ?>);
            $("#labelOrden").html("Orden Menú");
            $("#menuSuperior").removeAttr('required');
        }
        $("#ordenMenu").attr({
           "max": $("#ordenMenu").val()
        });
        $("#ordenRecomendado").val($("#ordenMenu").val());
        // Prevenir lineas montadas al asignar valores a input por javascript
        document.querySelectorAll('.form-outline').forEach((formOutline) => {
           new mdb.Input(formOutline).update();
        });
    }

    function getUltimoOrden() {
        let arrayOrden = <?php echo json_encode($arraySubMenuUltimoOrden); ?>;
        let orden = (arrayOrden[$("#menuSuperior").prop('selectedIndex')] != null) ? parseInt(arrayOrden[$("#menuSuperior").prop('selectedIndex')]) : 0;
        orden += 1;
        $("#ordenMenu").val(orden);
        $("#ordenMenu").attr({
           "max": $("#ordenMenu").val()
        });
        $("#ordenRecomendado").val($("#ordenMenu").val());
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