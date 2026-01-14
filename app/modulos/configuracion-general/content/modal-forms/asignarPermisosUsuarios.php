<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="menu-permisos-usuario">
<input type="hidden" id="flgStep" name="flgStep" value="step1">
<input type="hidden" id="totalContinuar" name="totalContinuar" value="0">
<input type="hidden" id="flgContinuarActual" name="flgContinuarActual" value="0">
<ul class="nav nav-tabs nav-justified mb-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1" role="tab" aria-controls="ntab-content-1" aria-selected="true" >
            <span class="step-form">1</span> Seleccionar menús
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link disabled" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2" role="tab" aria-controls="ntab-content-2" aria-selected="false">
            <span class="step-form">2</span> Asignar permisos
        </a>
    </li>
</ul>
<div class="tab-content" id="ntab-content">
    <div class="tab-pane fade active show" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="form-select-control mb-4">
                    <select id="usuarioId" name="usuarioId[]" style="width: 100%;" multiple="multiple" required>
                        <option></option>
                        <?php 
                            if(in_array(10, $_SESSION["arrayPermisos"]) || in_array(50, $_SESSION["arrayPermisos"])) { // Todos
                                $wherePermisoNoDev = (in_array(10, $_SESSION["arrayPermisos"])) ? '' : "us.usuarioId<>'$_SESSION[usuarioId]' AND";
                                $dataUsuarios = $cloud->rows("
                                    SELECT
                                        us.usuarioId AS usuarioId,
                                        CONCAT(
                                            IFNULL(per.apellido1, '-'),
                                            ' ',
                                            IFNULL(per.apellido2, '-'),
                                            ', ',
                                            IFNULL(per.nombre1, '-'),
                                            ' ',
                                            IFNULL(per.nombre2, '-')
                                        ) AS nombrePersona
                                    FROM conf_usuarios us
                                    JOIN th_personas per ON per.personaId = us.personaId
                                    WHERE $wherePermisoNoDev us.estadoUsuario = 'Activo' AND us.flgDelete = ? AND per.flgDelete = ?
                                    ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
                                ",['0','0']);
                            } else if(in_array(10, $_SESSION["arrayPermisos"]) || in_array(51, $_SESSION["arrayPermisos"])) {
                                // Get usuarios a cargo
                                // wherePermiso = us.personaId IN ($arrayPersonasACargo) AND
                                $wherePermisoNoDev = (in_array(10, $_SESSION["arrayPermisos"])) ? '' : "us.usuarioId<>'$_SESSION[usuarioId]' AND";
                                $dataUsuarios = $cloud->rows("
                                    SELECT
                                        us.usuarioId AS usuarioId,
                                        CONCAT(
                                            IFNULL(per.apellido1, '-'),
                                            ' ',
                                            IFNULL(per.apellido2, '-'),
                                            ', ',
                                            IFNULL(per.nombre1, '-'),
                                            ' ',
                                            IFNULL(per.nombre2, '-')
                                        ) AS nombrePersona
                                    FROM conf_usuarios us
                                    JOIN th_personas per ON per.personaId = us.personaId
                                    WHERE $wherePermisoNoDev us.estadoUsuario = 'Activo' AND us.flgDelete = ? AND per.flgDelete = ?
                                    ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
                                ",['0','0']);
                            } else {
                                // No se asignó ningún permiso
                            }

                            foreach($dataUsuarios as $dataUsuarios) {
                                echo '<option value="' . $dataUsuarios->usuarioId . '">' . $dataUsuarios->nombrePersona . '</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="form-select-control mb-4">
                    <select id="moduloId" name="moduloId" style="width: 100%;" onchange="getModulosMenu();" required>
                        <option></option>
                        <?php 
                            if(in_array(10, $_SESSION["arrayPermisos"])) { // Mostrar todos los módulos a Desarrollo
                                $dataModulos = $cloud->rows("
                                    SELECT
                                        moduloId,
                                        modulo
                                    FROM conf_modulos 
                                    WHERE moduloId > 2 AND flgDelete = ?
                                ",['0']);
                            } else { // No permitir que un usuario seleccione un módulo al que no tiene acceso
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
                                    WHERE md.moduloId >= ? AND perm.usuarioId = ? AND
                                    (perm.flgDelete = '0' AND mp.flgDelete = '0' AND m.flgDelete = '0' AND md.flgDelete = '0')
                                    GROUP BY md.moduloId
                                ",[3, $_SESSION["usuarioId"]]);
                            }

                            foreach($dataModulos as $dataModulos) {
                                echo '<option value="' . $dataModulos->moduloId . '">' . $dataModulos->modulo . '</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-lg-12">
                <div id="divSelectMenus" class="form-select-control mb-4">
                    <select id="tempMenu" name="tempMenu" style="width: 100%;">
                        <option></option>
                    </select>
                </div>
            </div>
        </div>
        <hr>
        <div class="row mb-4">
            <div class="col-lg-4 offset-lg-8">
                <button type="button" id="btnSiguiente" class="btn btn-primary btn-block">
                    Siguiente <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-content-2" role="tabpanel" aria-labelledby="ntab-2">
        <div class="row mb-4">
            <div id="div2Usuarios" class="col-lg-12">
            </div>
        </div>
        <div class="row mb-4">
            <div id="div2Modulo" class="col-lg-12">
            </div>
        </div>
        <div class="row mb-4">
            <div id="div2MenusPermisos" class="col-lg-12">
            </div>
        </div>
        <hr>
        <div class="row mb-4">
            <div class="col-lg-4">
                <button type="button" id="btnRegresar" class="btn btn-secondary btn-block">
                    <i class="fas fa-chevron-left"></i> Regresar
                </button>                
            </div>
            <div class="col-lg-2 offset-lg-2">
                <button type="button" id="btnRegresarPermiso" class="btn btn-secondary btn-block ttip">
                    <i class="fas fa-chevron-left"></i>
                    <span class="ttiptext">Menú anterior</span>
                </button>
            </div>
            <div class="col-lg-4">
                <button type="button" id="btnContinuarPermiso" class="btn btn-primary btn-block ttip">
                    <span class="badge rounded-pill bg-light" style="color: black;">0/0</span>
                    <span class="ttiptext">Menú siguiente</span>
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    function getModulosMenu() {
        asyncDoDataReturn(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/divSelectModuloxMenu", 
            {moduloId: $("#moduloId").val()}, 
            function(data) {
                $("#divSelectMenus").html(data);
            }
        );
    }
	$(document).ready(function() {
		$("#usuarioId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Usuario(s)'
		});
		$("#moduloId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Módulo'
		});
        $("#tempMenu").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Menú(s)'
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                if($("#flgStep").val() == "step1") { // Clic en "Siguiente", cambiar clases
                    $("#ntab-2").removeClass('disabled');
                    $("#ntab-1").removeClass('active');
                    $("#ntab-2").addClass('active');
                    $("#ntab-content-1").removeClass('active show');
                    $("#ntab-content-2").addClass('active show');
                    $("#ntab-1").addClass('disabled');
                    $("#flgStep").val("step2"); // Cambiar flg para que al proximo clic el submit caiga acá

                    let txtUsuarios = ""; n = 0;
                    $("#usuarioId option:selected").each(function() {
                        n += 1;
                        txtUsuarios += `${n}. ${$(this).text()}<br>`;                
                    });
                    $("#div2Usuarios").html(`<b><i class="fas fa-users"></i> Usuarios: </b><br>${txtUsuarios}`);
                    $("#div2Modulo").html(`<b><i class="fas fa-folder-open"></i> Módulo: </b>${$("#moduloId option:selected").text()}`);

                    n = 0;
                    $("#menuId option:selected").each(function() {
                        n += 1;              
                    });

                    $("#btnRegresarPermiso").attr("disabled", true);
                    if(n == 1) { // Solo seleccionó un menú, mostrar guardar
                        $("#btnContinuarPermiso").html(`<span class="badge rounded-pill bg-light" style="color: black;">1/${n}</span> <i class="fas fa-save"></i><span class="ttiptext">Guardar permisos</span>`);
                    } else { // Es más de 1 menú
                        $("#btnContinuarPermiso").html(`<span class="badge rounded-pill bg-light" style="color: black;">1/${n}</span> <i class="fas fa-chevron-right"></i><span class="ttiptext">Menú siguiente</span>`);
                    }
                    $("#flgContinuarActual").val(1);
                    $("#totalContinuar").val(n);
                    asyncDoDataReturn(
                        "<?php echo $_SESSION['currentRoute']; ?>content/divs/divMenusPermisos", 
                        $("#frmModal").serialize(),
                        function(data) {
                            $("#div2MenusPermisos").html(data);
                        }
                    );
                } else { // step 2
                    asyncDoDataReturn(
                        "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                        $("#frmModal").serialize(),
                        function(data) {
                            let arrayData = data.split("^");
                            if(arrayData[0] == "success") {
                                mensaje(
                                    "Operación completada:",
                                    arrayData[1],
                                    "success"
                                );
                                $('#tblPermisos').DataTable().ajax.reload(null, false);
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
            }
        });

        $("#btnSiguiente").click(function(e) {
            e.preventDefault();
            $('#frmModal').submit(); // trigger form-validate
        });

        $("#btnRegresar").click(function(e) {
            e.preventDefault();
            $("#ntab-1").removeClass('disabled');
            $("#ntab-2").removeClass('active');
            $("#ntab-1").addClass('active');
            $("#ntab-content-2").removeClass('active show');
            $("#ntab-content-1").addClass('active show');
            $("#ntab-2").addClass('disabled');
            $("#flgStep").val("step1");
        });

        $("#btnContinuarPermiso").click(function(e) {
            e.preventDefault();
            if($('#flgContinuarActual').val() == $('#totalContinuar').val()) { // se dió clic en el último
                // validar si se han marcado permisos en el último
                if($(`input[name="checkPermisos${$("#flgContinuarActual").val()}[]"]`).is(":checked")) {
                    // enviar solicitud
                    $('#frmModal').submit(); // trigger form-validate
                } else { // no ha marcado ningún permiso
                    mensaje("Aviso:", "Seleccione al menos 1 permiso", "warning");
                }
            } else { // pasar al siguiente 
                // validar antes si se seleccionaron permisos
                // por "name" ya que los id están dinámicos para evitar warning de navegador
                if($(`input[name="checkPermisos${$("#flgContinuarActual").val()}[]"]`).is(":checked")) {
                    $(`#divPM${$("#flgContinuarActual").val()}`).hide();
                    // aumentar en 1, para mostrar siguiente div
                    $("#flgContinuarActual").val(parseInt($("#flgContinuarActual").val()) + 1);
                    $(`#divPM${$("#flgContinuarActual").val()}`).show();
                    // habilitar volver del formulario de checkbox
                    $(`#btnRegresarPermiso`).removeAttr("disabled");

                    // validar si se debe cambiar el icono de guardado y re-dibujar continuar
                    if($("#flgContinuarActual").val() == $('#totalContinuar').val()) { // se llegó al último, cambiar valores e icono
                        $("#btnContinuarPermiso").html(`<span class="badge rounded-pill bg-light" style="color: black;">${$("#flgContinuarActual").val()}/${$("#totalContinuar").val()}</span> <i class="fas fa-save"></i><span class="ttiptext">Guardar permisos</span>`);
                    } else { // redibujar badge con los nuevos valores
                        $("#btnContinuarPermiso").html(`<span class="badge rounded-pill bg-light" style="color: black;">${$("#flgContinuarActual").val()}/${$("#totalContinuar").val()}</span> <i class="fas fa-chevron-right"></i><span class="ttiptext">Menú siguiente</span>`);
                    }
                } else { // no ha marcado ningún permiso
                    mensaje("Aviso:", "Seleccione al menos 1 permiso", "warning");
                }
            }
        });

        $("#btnRegresarPermiso").click(function(e) {
            e.preventDefault();
            $(`#divPM${$("#flgContinuarActual").val()}`).hide();
            $("#flgContinuarActual").val(parseInt($("#flgContinuarActual").val()) - 1);
            $(`#divPM${$("#flgContinuarActual").val()}`).show();

            // actualizar botón continuar, si tenia el botón de guardar acá se le quita automático
            $("#btnContinuarPermiso").html(`<span class="badge rounded-pill bg-light" style="color: black;">${$("#flgContinuarActual").val()}/${$("#totalContinuar").val()}</span> <i class="fas fa-chevron-right"></i><span class="ttiptext">Menú siguiente</span>`);
            if($("#flgContinuarActual").val() == 1) { // evitar que se retroceda al llegar a 1, el Continuar lo vuelve a habilitar
                $("#btnRegresarPermiso").attr("disabled", true);
            } else {
            }
        });
	});
</script>