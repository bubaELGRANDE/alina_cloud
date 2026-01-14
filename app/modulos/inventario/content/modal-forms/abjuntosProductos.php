<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
?>

<style>
    .required:after {
        content: " *";
        color: red;
    }

    #imgPreview {
        width: auto;
        max-width: 100%;
        border-radius: 10px;
    }

    .card-clean {
        border-radius: 14px;
        border: 1px solid #e9ecef;
    }

    .card-header-clean {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: 14px;
        border-top-right-radius: 14px;
    }

    .tag-type {
        font-size: .75rem;
        padding: 2px 6px;
        background: #eef2ff;
        border-radius: 4px;
        color: #495057;
    }
</style>


<input type="hidden" name="typeOperation" value="insert">
<input type="hidden" name="operation" value="adjuntoProducto">
<input type="hidden" name="productoId" value="<?= $_POST["productoId"] ?? 0 ?>">

<div class="card mb-4 shadow-sm card-opcional">
    <div class="card-body">

        <p class="text-muted mb-4">
            Subí imágenes, videos o documentos relacionados al producto.
        </p>

        <div class="row g-3">

            <!-- Tipo -->
            <div class="col-md-4">
                <label class="form-label required">Tipo de Adjunto</label>
                <select name="tipoProductoAdjunto" id="tipoProductoAdjunto" class="form-select" required>
                    <option value="">Seleccione tipo</option>
                    <option value="imagen_referencia">Imagen de Referencia</option>
                    <option value="imagen_catalogo">Imagen de Catálogo</option>
                    <option value="foto_real">Foto Real</option>
                    <option value="certificado">Certificado</option>
                    <option value="boleta_compra">Boleta / Factura</option>
                    <option value="video">Video</option>
                    <option value="manual">Manual / PDF</option>
                    <option value="otro">Otro</option>
                </select>
            </div>

            <!-- Archivo -->
            <div class="col-md-8">
                <label class="form-label required">Archivo</label>
                <input type="file" name="archivoAdjunto" id="archivoAdjunto" class="form-control" required>
                <div class="form-text">Formatos: jpg, png, pdf, mp4…</div>
            </div>

            <!-- Descripción -->
            <div class="col-md-12">
                <label class="form-label">Descripción</label>
                <textarea name="descripcionProductoAdjunto" id="descripcionProductoAdjunto" class="form-control"
                    rows="2"></textarea>
            </div>
        </div>

        <!-- Vista previa -->
        <div class="col-md-12 d-none mt-4" id="previewContainer">
            <label class="form-label">Vista previa</label>
            <img id="imgPreview" class="rounded border shadow-sm" style="max-height:200px;">
        </div>

        <div class="text-end mt-3">
            <button id="guardar" type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Guardar
            </button>
        </div>
    </div>
</div>

<h5 class="card-title mb-3 title-opcional section-toggle" data-bs-toggle="collapse" data-bs-target="#secEstado">
    <span class="title-label">
        <i class="fa-solid fa-circle-info text-primary title-icon"></i>
        <span>Archivos cargados</span>
    </span>
</h5>
<div id="listaAdjuntos" class="row g-3"></div>



<script>

    document.getElementById("archivoAdjunto").addEventListener("change", function () {
        const file = this.files[0];
        if (!file) return;

        if (file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById("imgPreview").src = e.target.result;
                document.getElementById("previewContainer").classList.remove("d-none");
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById("previewContainer").classList.add("d-none");
        }
    });


    function cargarAdjuntos() {
        asyncData(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/divAdjuntoProductos", {
                productoId: <?= $_POST["productoId"] ?>
            },
            function (data) {
                $("#listaAdjuntos").html(data);
            }
        );
    }


    $(document).ready(function () {

        cargarAdjuntos();

        $("#frmModal").validate({
            submitHandler: function () {
                let form_data = new FormData($("#frmModal")[0]);
                button_icons("guardar", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncFile(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    form_data,
                    function (data) {
                        button_icons("guardar", "fas fa-save", "Guardar", "enabled");
                        if (data == "success") {
                            mensaje("Completado", "El archivo se guardó correctamente.", "success");
                            cargarAdjuntos();   
                        } else {
                            mensaje("Aviso", data, "warning");
                        }
                    }
                );
            }
        });
    });
</script>