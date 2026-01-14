<?php 
    @session_start();
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="nuevo-modulo">
<div class="row">
    <div class="col-lg-8">
        <div class="form-outline mb-4">
            <i class="fas fa-folder trailing"></i>
            <input type="text" id="modulo" class="form-control" name="modulo" required />
            <label class="form-label" for="modulo">Módulo</label>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select id="carpeta" name="carpeta" style="width: 100%;" required>
                <option></option>
                <?php 
                    $cdir = scandir("../../../");
                    foreach($cdir as $key => $value) {
                        if(!in_array($value,array(".",".."))) {
                            echo '<option value="' . $value . '">' . $value . '</option>';
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
    <div class="col-lg-12">
        <div class="form-outline mb-4">
            <i class="fas fa-edit trailing"></i>
            <textarea class="form-control" id="descripcionModulo" name="descripcionModulo" rows="4" required></textarea>
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
        $("#divBadgeColor").hide();
        $("#divBadgeText").hide();

        $("#carpeta").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Carpeta'
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
                            mensaje(
                                "Operación completada:",
                                'Módulo creado con éxito.<br>Pasos para habilitar el módulo:<br>1. Agregar menús.<br>2. Asignar permisos a los menús.<br>3. Asignar estos permisos a los usuarios.',
                                "success"
                            );
                            $('#tblModulos').DataTable().ajax.reload(null, false);
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