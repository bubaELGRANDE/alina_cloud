<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    // arrayFormData = moduloId ^ nombreModulo
    $arrayFormData = explode("^", $_POST["arrayFormData"]);
    $dataEditModulo = $cloud->row("
        SELECT
            modulo,
            descripcionModulo,
            iconModulo,
            urlModulo,
            badgeColor,
            badgeText
        FROM conf_modulos
        WHERE moduloId = ? AND flgDelete = '0'
    ", [$arrayFormData[0]]);
    $flgBadge = 0;
    // Validar si tiene badge
    if(!(is_null($dataEditModulo->badgeColor) || is_null($dataEditModulo->badgeText))) {
        $flgBadge = 1;
    } else {
        $badge = "Ninguno";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="editar-modulo">
<input type="hidden" id="moduloId" name="moduloId" value="<?php echo $arrayFormData[0]; ?>">
<div class="row">
    <div class="col-lg-8">
        <div class="form-outline mb-4">
            <i class="fas fa-folder trailing"></i>
            <input type="text" id="modulo" class="form-control" name="modulo" value="<?php echo $dataEditModulo->modulo; ?>" required />
            <label class="form-label" for="modulo">Módulo</label>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select id="carpeta" name="carpeta" style="width: 100%;" required>
                <option value="<?php echo $dataEditModulo->urlModulo; ?>" selected><?php echo $dataEditModulo->urlModulo; ?></option>
                <?php 
                    $cdir = scandir("../../../");
                    foreach($cdir as $key => $value) {
                        if(!in_array($value,array(".",".."))) {
                            if($value != $dataEditModulo->urlModulo) {
                                echo '<option value="' . $value . '">' . $value . '</option>';
                            } else {
                                // Omitir la carpeta que ya está asignada
                            }
                        } else {
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
            <input type="text" id="icono" class="form-control" name="icono" value="<?php echo $dataEditModulo->iconModulo; ?>" required />
            <label class="form-label" for="icono">Icono</label>
        </div>
    </div>
    <div id="divAddBadge" class="col-lg-8">
        <button type="button" class="btn btn-primary btn-sm" onclick="showHideBadge(1);">
            <i class="fas fa-plus-circle"></i> Badge
        </button>
    </div>
    <input type="hidden" id="flgBadge" name="flgBadge" value="<?php echo $flgBadge; ?>">
    <div id="divBadgeColor" class="col-lg-4">
        <div class="form-select-control mb-4">
            <select id="badgeColor" name="badgeColor" style="width: 100%;">
                <option></option>
                <?php 
                    $arrayBadgeColors = array("primary", "secondary", "success", "danger", "warning", "info", "light", "dark");
                    foreach ($arrayBadgeColors as $color) {
                        if($flgBadge == 1) { // Se asigno badge, seleccionar el color
                            if($dataEditModulo->badgeColor == $color) {
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
            <input type="text" id="badgeText" class="form-control" name="badgeText" aria-describedby="eliminarBadge" value="<?php echo $dataEditModulo->badgeText; ?>" />
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
    <div class="col-lg-12">
        <div class="form-outline mb-4">
            <i class="fas fa-edit trailing"></i>
            <textarea class="form-control" id="descripcionModulo" name="descripcionModulo" rows="4" required><?php echo $dataEditModulo->descripcionModulo; ?></textarea>
            <label class="form-label" for="descripcionModulo">Descripción</label>
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

    $(document).ready(function() {
        showHideBadge(<?php echo $flgBadge; ?>);

        $("#carpeta").select2({
            dropdownParent: $('#modal-container')
        });
        $("#badgeColor").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Badge Color'
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:", 
                                "Información del módulo actualizada con éxito. Se volverá a cargar la página para ver los cambios reflejados en la barra lateral.", 
                                "success", 
                                function() {
                                    location.reload();
                                }, 
                                "Aceptar"
                            );  
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