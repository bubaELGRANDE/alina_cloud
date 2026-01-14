<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$ordenSuc = $cloud->row("SELECT MAX(numOrdenSucursal) AS ultimo FROM cat_sucursales");
$numOrden = $ordenSuc->ultimo + 1;
?>

<!-- ==================  SECCIÓN: NUEVA SUCURSAL  ================== -->
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="nuevaSucursal">
<input type="hidden" id="numOrdenSucursal" name="numOrdenSucursal" value="<?php echo $numOrden; ?>">

<!-- Nombre -->
<div class="form-outline mb-4">
    <i class="fas fa-building trailing"></i>
    <input type="text" id="nombreSucursal" class="form-control" name="nombreSucursal" required />
    <label class="form-label" for="nombreSucursal">Nombre de sucursal <span class="text-danger">*</span></label>
</div>

<!-- Fila de selects -->
<div class="row mb-4">
    <div class="col-md-6">
        <label class="form-label">Departamento <span class="text-danger">*</span></label>
        <select class="form-select" id="departamento" name="departamento" required style="width: 100%;">
            <option disabled selected>Seleccione un departamento</option>
            <?php
            $dataDep = $cloud->rows("
                            SELECT paisDepartamentoId, departamentoPais 
                            FROM cat_paises_departamentos 
                            WHERE flgDelete = 0 AND paisId = 61
                        ");
            foreach ($dataDep as $depto) {
                echo '<option value="' . $depto->paisDepartamentoId . '">' . $depto->departamentoPais . '</option>';
            }
            ?>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Municipio <span class="text-danger">*</span></label>
        <select class="form-select" id="municipio" name="municipio" required style="width: 100%;">
            <option disabled selected>Municipio</option>
        </select>
    </div>
</div>

<!-- Dirección -->
<div class="form-outline mb-4">
    <i class="fas fa-map-marker-alt trailing"></i>
    <textarea id="direccion" class="form-control" name="direccion" required></textarea>
    <label class="form-label" for="direccion">Dirección <span class="text-danger">*</span></label>
</div>
<div class="mb-3">
    <label class="form-label">Logo de sucursal</label>

    <div id="dropLogo" class="border border-primary rounded p-4 text-center bg-light mb-2"
        ondragover="this.classList.add('bg-primary','text-white')"
        ondragleave="this.classList.remove('bg-primary','text-white')" ondrop="dropImagen(event)">
        <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
        <p class="mb-0">Arrastre el archivo aquí o haga clic para seleccionar</p>
        <input type="file" id="subirLogo" name="subirLogo" class="d-none" accept=".jpg,.jpeg,.png"
            onchange="previewImagen();">
    </div>

    <img id="imgPreview" class="img-thumbnail d-none" style="max-height: 120px;">
</div>

<script>
    document.getElementById("dropLogo").onclick = function () {
        document.getElementById("subirLogo").click();
    };

    function dropImagen(e) {
        e.preventDefault();
        document.getElementById("dropLogo").classList.remove("bg-primary", "text-white");

        let file = e.dataTransfer.files[0];
        document.getElementById("subirLogo").files = e.dataTransfer.files;
        previewImagen();
    }

    function previewImagen() {
        let input = document.getElementById("subirLogo");
        let file = input.files[0];
        if (!file) return;

        let ext = file.name.split('.').pop().toLowerCase();
        if (!["jpg", "jpeg", "png"].includes(ext)) {
            mensaje("AVISO", "El archivo no es una imagen válida.", "warning");
            input.value = "";
            return;
        }

        let reader = new FileReader();
        reader.onload = function (e) {
            let img = document.getElementById("imgPreview");
            img.src = e.target.result;
            img.classList.remove("d-none");
        };
        reader.readAsDataURL(file);
    }
</script>


<!-- ==================  SCRIPTS  ================== -->
<script>
    function verificarImagen() {
        let imagen = document.getElementById("subirLogo").value;
        let idxDot = imagen.lastIndexOf(".") + 1;
        let extFile = imagen.substr(idxDot).toLowerCase();

        if (extFile == "jpg" || extFile == "jpeg" || extFile == "png") {
            return;
        }

        mensaje(
            "AVISO - FORMULARIO",
            "El archivo seleccionado no coincide con una imagen válida (jpg, jpeg, png).",
            "warning"
        );
        $("#subirLogo").val('');
    }

    $(document).ready(function () {

        /* Select2 + modal parent */
        $("#departamento").select2({ dropdownParent: $('#modal-container') });
        $("#municipio").select2({ dropdownParent: $('#modal-container') });

        /* Listado de municipios */
        $("#departamento").on("change", function () {
            $.ajax({
                url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarMunicipios",
                type: "POST",
                dataType: "json",
                data: { depto: $(this).val() }
            }).done(function (data) {

                $("#municipio").empty()
                    .append("<option value='' disabled selected>Seleccione municipio...</option>");

                data.forEach(m => {
                    $("#municipio").append(`<option value="${m.id}">${m.municipio}</option>`);
                });

            });
        });

        /* Validación + envío */
        $("#frmModal").validate({
            submitHandler: function (form) {

                let form_data = new FormData($('#frmModal')[0]);

                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");

                asyncFile(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    form_data,
                    function (data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");

                        if (data == "success") {
                            mensaje("Operación completada:", "Sucursal creada con éxito.", "success");
                            $("#tblSucursal").DataTable().ajax.reload(null, false);
                            $('#modal-container').modal("hide");
                        } else {
                            mensaje("Aviso:", data, "warning");
                        }
                    }
                );
            }
        });

    });
</script>