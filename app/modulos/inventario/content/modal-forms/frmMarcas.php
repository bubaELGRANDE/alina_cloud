<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$array = explode("^", $_POST["arrayFormData"]);
$operacion = $array[0];
$idMarca = $array[1] ?? "";

$editMode = !empty($idMarca);

$nombreMarca = "";
$abreviatura = "";
$logoActual = "";
$txtSuccess = $editMode ? "Marca editada con éxito." : "Marca agregada con éxito.";
$txtImg = $editMode ? "Reemplazar logo" : "Adjuntar logo";

if ($editMode) {
    $marca = $cloud->row("
        SELECT marcaId,nombreMarca,abreviaturaMarca,urlLogoMarca,estadoMarca
        FROM cat_inventario_marcas
        WHERE flgDelete = 0 AND marcaId = $idMarca
    ");

    $nombreMarca = $marca->nombreMarca;
    $abreviatura = $marca->abreviaturaMarca;

    if (!empty($marca->urlLogoMarca)) {
        $logoActual = "../libraries/resources/images/" . $marca->urlLogoMarca;
    }
}
?>

<!-- Operaciones -->
<input type="hidden" name="typeOperation" value="<?= $operacion ?>">
<input type="hidden" name="operation" value="marca">
<?php if ($editMode) { ?>
    <input type="hidden" name="marcaId" value="<?= $marca->marcaId ?>">
<?php } ?>

<!-- LOGO ACTUAL -->
<?php if ($editMode && $logoActual) { ?>
    <div class="text-center mb-4">
        <label class="fw-bold d-block mb-2">Logo actual</label>
        <img src="<?= $logoActual ?>" class="img-fluid rounded shadow-sm" style="max-height:200px;">
    </div>
<?php } ?>

<div class="row">
    <div class="col-md-8">
        <div class="form-outline mb-4">
            <i class="fas fa-trademark trailing"></i>
            <input type="text" class="form-control" name="nombreMarca" value="<?= $nombreMarca ?>" required>
            <label class="form-label">Nombre de marca</label>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-thumbtack trailing"></i>
            <input type="text" class="form-control" name="abreviaturaMarca" value="<?= $abreviatura ?>" required>
            <label class="form-label">Abreviatura</label>
        </div>
    </div>
</div>

<!-- ADJUNTAR LOGO -->
<div class="row mb-4">
    <div class="col-md-8">
        <label class="fw-bold"><?= $txtImg ?></label><br>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="adjuntarLogo" value="Si" <?= !$editMode || empty($logoActual) ? "checked" : "" ?>>
            <label class="form-check-label">Sí</label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="adjuntarLogo" value="No" <?= $editMode ? "checked" : "" ?>>
            <label class="form-check-label">No</label>
        </div>
    </div>

    <?php if ($editMode) { ?>
        <div class="col-md-4">
            <label class="form-label">Estado</label>
            <select class="form-select" id="estado" name="estado" required>
                <option></option>
                <option value="Activa">Activa</option>
                <option value="Inactiva">Inactiva</option>
            </select>
        </div>
    <?php } ?>
</div>

<!-- UPLOAD -->
<div id="divAdjuntar" class="mt-3">
    <hr>
    <div class="mb-3">

        <label class="form-label fw-bold">Archivo de logo</label>

        <div class="input-group mb-2">
            <label class="input-group-text bg-primary text-white" for="adjunto">
                <i class="fas fa-upload"></i>
            </label>
            <input type="file" class="form-control" id="adjunto" name="adjunto" accept="image/*"
                onchange="previewLogo(event)">
        </div>

        <img id="previewLogoImg" class="img-thumbnail d-none mb-2" style="max-height:200px;">

        <small class="text-muted">Tamaño máximo permitido: <?= ini_get('upload_max_filesize') ?></small>
    </div>
</div>



<script>

    function previewLogo(event) {
        let file = event.target.files[0];
        if (!file) return;

        let ext = file.name.split('.').pop().toLowerCase();
        let valid = ["jpg", "jpeg", "png", "bmp"];

        if (!valid.includes(ext)) {
            mensaje("AVISO", "El archivo no es una imagen válida.", "warning");
            event.target.value = "";
            return;
        }

        let reader = new FileReader();
        reader.onload = function (e) {
            $("#previewLogoImg")
                .attr("src", e.target.result)
                .removeClass("d-none");
        }
        reader.readAsDataURL(file);
    }


    $(document).ready(function () {

        // Mostrar / ocultar uploader
        $("[name='adjuntarLogo']").change(function () {
            if ($(this).val() === "Si") {
                $("#divAdjuntar").fadeIn();
            } else {
                $("#divAdjuntar").fadeOut();
            }
        });

        // Estado (solo edición)
        <?php if ($editMode) { ?>
            $("#estado").select2({
                placeholder: "Estado",
                dropdownParent: $('#modal-container'),
                allowClear: true
            });
            $("#estado").val("<?= $marca->estadoMarca ?>").trigger("change");
            $("#divAdjuntar").hide();
        <?php } ?>

        // VALIDACIÓN
        $("#frmModal").validate({
            submitHandler: function () {
                let form_data = new FormData($('#frmModal')[0]);

                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");

                asyncFile(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    form_data,
                    function (data) {

                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");

                        if (data === "success") {
                            mensaje("Éxito", "<?= $txtSuccess ?>", "success");
                            $("#tblMarca").DataTable().ajax.reload(null, false);
                            $("#modal-container").modal("hide");
                        } else {
                            mensaje("Aviso:", data, "warning");
                        }
                    }
                );
            }
        });

        <?php if ($editMode) { ?>
            $("#modalTitle").html("Editar Marca: <?= $marca->nombreMarca ?>");
        <?php } ?>

    });
</script>