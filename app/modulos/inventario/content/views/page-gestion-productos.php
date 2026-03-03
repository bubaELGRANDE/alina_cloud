<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

/*
  EDIT MODE:
  - Abrí esta página con ?productoId=123 para editar.
  - Sin productoId => insert.
*/
$productoId = isset($_POST["productoId"]) ? (int) $_POST["productoId"] : 0;
$typeOperation = $productoId > 0 ? "update" : "insert";
?>
<style>
    body {
        background: #f5f6f7;
    }

    .legend-dot {
        width: 16px;
        height: 16px;
        border-left: 4px solid;
        margin-right: 8px;
    }

    .card-obligatorio {
        border-left: 4px solid #dc3545 !important;
    }

    .card-importante {
        border-left: 4px solid #fd7e14 !important;
    }

    .card-opcional {
        border-left: 4px solid #0d6efd !important;
    }

    .title-obligatorio,
    .title-importante,
    .title-opcional {
        border-radius: 6px;
        padding: 6px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .title-label {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .title-icon {
        font-size: 1.1rem;
    }

    .tooltip-label {
        cursor: help;
        text-decoration: dotted underline;
    }

    .required:after {
        content: " *";
        color: #dc3545;
    }

    .collapse {
        transition: all .25s ease-in-out;
    }

    .btn-back {
        font-size: .9rem;
        padding: 2px 10px;
    }

    .tag-chip {
        background: #f1f1f1;
        border-radius: 20px;
        padding: 6px 12px;
        display: inline-flex;
        align-items: center;
        font-size: .9rem;
    }

    .tag-chip .btn-close {
        font-size: .65rem;
        margin-left: 6px;
    }
</style>

<div class="container py-4">
    <h2 class="mb-4 fw-bold">
        <?= $typeOperation === "update" ? "Editar Producto" : "Crear Nuevo Producto" ?>
    </h2>

    <div class="alert shadow-sm mb-4" role="alert">
        <div class="d-flex justify-content-between flex-wrap gap-4">
            <div class="d-flex align-items-start" style="min-width: 250px;">
                <div class="legend-dot flex-shrink-0 mt-1" style="border-color:#dc3545;"></div>
                <div class="ms-2">
                    <span class="fw-semibold text-danger d-block">Obligatorio</span>
                    <small class="text-muted d-block">Datos necesarios para crear el producto y generar el SKU.</small>
                </div>
            </div>
            <div class="d-flex align-items-start" style="min-width: 250px;">
                <div class="legend-dot flex-shrink-0 mt-1" style="border-color:#fd7e14;"></div>
                <div class="ms-2">
                    <span class="fw-semibold" style="color:#fd7e14;">Importante</span>
                    <small class="text-muted d-block">Altamente recomendado para descripción, control y
                        reportes.</small>
                </div>
            </div>
            <div class="d-flex align-items-start" style="min-width: 250px;">
                <div class="legend-dot flex-shrink-0 mt-1" style="border-color:#0d6efd;"></div>
                <div class="ms-2">
                    <span class="fw-semibold text-primary d-block">Opcional</span>
                    <small class="text-muted d-block">Información complementaria, no requerida.</small>
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary mb-2" id="generarSKU" type="button">generar SKU</button>
    <button class="btn btn-success mb-2" id="cargarExcel" type="button">
        <i class="fas fa-file-excel"></i> Cargar por excel
    </button>

    <form id="formProducto">
        <input type="hidden" id="typeOperation" name="typeOperation" value="<?= $typeOperation ?>">
        <input type="hidden" id="operation" name="operation" value="producto">
        <?php if ($typeOperation === "update"): ?>
            <input type="hidden" id="productoId" name="productoId" value="<?= (int) $productoId ?>">
        <?php endif; ?>

        <!-- ===================================================== -->
        <!-- SECCIÓN 1: DATOS OBLIGATORIOS (PARA SKU) - OBLIGATORIO -->
        <!-- ===================================================== -->
        <div class="card mb-4 shadow-sm card-obligatorio">
            <div class="card-body">
                <h5 class="card-title mb-3 title-obligatorio section-toggle" data-bs-toggle="collapse"
                    data-bs-target="#secDatosPrincipales">
                    <span class="title-label">
                        <i class="fa-solid fa-circle-exclamation text-danger title-icon"></i>
                        <span>Datos principales (necesarios para el SKU)</span>
                    </span>
                </h5>

                <div id="secDatosPrincipales" class="collapse show">
                    <div class="row g-4">
                        <div class="col-md-8">
                            <label class="form-label required tooltip-label" data-bs-toggle="tooltip"
                                title="Escribe el nombre completo del producto">
                                Nombre del Producto
                            </label>
                            <input type="text" name="nombreProducto" id="nombreProducto"
                                class="form-control form-control-lg" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Código Interno (SKU)</label>
                            <div class="input-group">
                                <span class="input-group-text">SKU</span>
                                <input type="text" class="form-control" name="codInterno" id="codInterno" readonly>
                            </div>
                            <div class="form-text">Se generará automáticamente más adelante.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Categoría Principal</label>
                            <select id="categoriaPrincipal" name="inventarioCategoriaPrincipalId"
                                class="form-select form-select-lg" required>
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Marca</label>
                            <select name="marcaId" id="marcaId" class="form-select form-select-lg">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- ESPECIFICACIONES OBLIGATORIAS POR CATEGORÍA -->
        <!-- ===================================================== -->
        <div class="card mb-4 shadow-sm card-obligatorio">
            <div class="card-body">
                <h5 class="card-title mb-3 title-obligatorio section-toggle" data-bs-toggle="collapse"
                    data-bs-target="#secEspecificaciones">
                    <span class="title-label">
                        <i class="fa-solid fa-triangle-exclamation text-warning title-icon"></i>
                        <span>Especificaciones obligatorias según categoría</span>
                    </span>
                </h5>

                <div id="secEspecificaciones" class="collapse show">
                    <p class="text-muted">
                        Estas especificaciones corresponden a la categoría principal seleccionada y se utilizarán para
                        generar el SKU automáticamente.
                    </p>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%">Especificación</th>
                                    <th style="width: 30%">Valor</th>
                                    <th style="width: 20%">Unidad</th>
                                    <th style="width: 10%">Obligatoria</th>
                                </tr>
                            </thead>
                            <tbody id="tablaEspecificacionesObligatorias"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- INFORMACIÓN SECUNDARIA -->
        <!-- ===================================================== -->
        <div class="card mb-4 shadow-sm card-importante">
            <div class="card-body">
                <h5 class="card-title mb-3 title-importante section-toggle" data-bs-toggle="collapse"
                    data-bs-target="#secInfoSecundaria">
                    <span class="title-label">
                        <i class="fa-solid fa-triangle-exclamation text-warning title-icon"></i>
                        <span>Información secundaria</span>
                    </span>
                </h5>

                <div id="secInfoSecundaria" class="collapse show">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label">Descripción del Producto</label>
                            <textarea id="descripcionProducto" name="descripcionProducto" class="form-control"
                                rows="2"></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Código del Fabricante</label>
                            <input type="text" id="codFabricante" name="codFabricante" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">Unidad de Medida</label>
                            <select id="unidadMedidaId" name="unidadMedidaId" class="form-select" required>
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">País de Origen</label>
                            <select id="paisId" name="paisIdOrigen" class="form-select">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- ORIGEN Y RELACIONES -->
        <!-- ===================================================== -->
        <div class="card mb-4 shadow-sm card-importante">
            <div class="card-body">
                <h5 class="card-title mb-3 title-importante section-toggle" data-bs-toggle="collapse"
                    data-bs-target="#secOrigen">
                    <span class="title-label">
                        <i class="fa-solid fa-triangle-exclamation text-warning title-icon"></i>
                        <span>Datos de ubicación y inventario</span>
                    </span>
                </h5>

                <div id="secOrigen" class="collapse show">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label required">Sucursal</label>
                            <select id="sucursalId" name="sucursalId" class="form-select form-select-lg" required>
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">Ubicación</label>
                            <select id="inventarioUbicacionId" name="ubicacionId" class="form-select form-select-lg"
                                required>
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">Tipo de Producto</label>
                            <?php
                            $data = $cloud->rows("
                SELECT tipoProductoId, nombreTipoProducto
                FROM cat_inventario_tipos_producto
                WHERE flgDelete = 0
              ");
                            $tipoProductoSeleccionado = 1;
                            ?>
                            <select id="tipoProductoId" name="tipoProductoId" class="form-select form-select-lg"
                                required>
                                <option value="">Seleccione categoría...</option>
                                <?php foreach ($data as $row): ?>
                                    <option value="<?= $row->tipoProductoId ?>"
                                        <?= ($row->tipoProductoId == $tipoProductoSeleccionado) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row->nombreTipoProducto) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- TAGS -->
        <!-- ===================================================== -->
        <div class="card mb-4 shadow-sm card-opcional">
            <div class="card-body">
                <h5 class="card-title mb-3 title-opcional section-toggle" data-bs-toggle="collapse"
                    data-bs-target="#secTags">
                    <span class="title-label">
                        <i class="fa-solid fa-circle-info text-primary title-icon"></i>
                        <span>Etiquetas (Tags) del Producto</span>
                    </span>
                </h5>

                <div id="secTags" class="collapse show">
                    <p class="text-muted">
                        Agrega etiquetas tipo hashtag para facilitar búsquedas internas. Escribe una palabra y presiona
                        <strong>Enter</strong>.
                    </p>
                    <div class="mb-3">
                        <select class="d-flex flex-wrap gap-2" name="categorias[]" multiple="multiple"
                            id="categorias"></select>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- ESTADO -->
        <!-- ===================================================== -->
        <div class="card mb-4 shadow-sm card-opcional">
            <div class="card-body">
                <h5 class="card-title mb-3 title-opcional section-toggle" data-bs-toggle="collapse"
                    data-bs-target="#secEstado">
                    <span class="title-label">
                        <i class="fa-solid fa-circle-info text-primary title-icon"></i>
                        <span>Estado del Producto</span>
                    </span>
                </h5>

                <div id="secEstado" class="collapse show">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select id="estadoProducto" name="estadoProducto" disabled class="form-select">
                                <option value="Activo" selected>Activo</option>
                                <option value="Suspendido">Suspendido</option>
                                <option value="Descontinuado">Descontinuado</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Observaciones</label>
                            <textarea id="obsEstadoProducto" name="obsEstadoProducto" class="form-control"
                                rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-5">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <?= $typeOperation === "update" ? "Actualizar Producto" : "Guardar Producto" ?>
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function () {

        const isUpdate = $("#typeOperation").val() === "update";

        function setSelect2Value($select, id, text) {
            if (!id) return;
            const option = new Option(text || ("ID " + id), id, true, true);
            $select.append(option).trigger("change");
        }

        function obtenerEspec() {
            let especificaciones = [];
            $("#tablaEspecificacionesObligatorias tr").each(function () {
                const id = $(this).find(".especId").val();
                const nombre = $(this).find(".especNombre").val();
                const valor = $(this).find(".especCantidad").val();
                const unidadMedida = $(this).find("select").val();

                if (valor) {
                    especificaciones.push({ id, nombre, valor, unidadMedida });
                }
            });
            return especificaciones;
        }

        function generarSKU(done) {
            const categoria = $('#categoriaPrincipal').val() ?? 0;
            const marca = $('#marcaId').val() ?? 0;

            let pureza = "", peso = "", diametro = "";
            const especificaciones = obtenerEspec();

            especificaciones.forEach(e => {
                if (e.nombre === "Peso") peso = e.valor;
                if (e.nombre === "Diámetro") diametro = e.valor;
                if (e.nombre === "Pureza") pureza = e.valor;
            });

            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>content/divs/getSkuDatos",
                { categoria, marca },
                function (data) {
                    let SKU = data.categoria + "0" + data.marca;
                    SKU += pureza ? pureza + data.id : data.id;
                    if (peso) SKU += "-" + Math.round(Number(peso));
                    if (diametro) SKU += Math.round(Number(diametro));

                    $("#codInterno").val(SKU);
                    if (typeof done === "function") done(SKU);
                }
            );
        }

        function prepararSKU(cb) {
            // update: si ya hay SKU, no lo regeneres a la fuerza
            if (isUpdate && $("#codInterno").val().trim() !== "") {
                cb();
                return;
            }
            generarSKU(cb);
        }

        $("#generarSKU").on("click", function () {
            generarSKU();
        });

        $("#cargarExcel").on("click", cargarExel);
        function cargarExel() {
            loadModal(
                "modal-container",
                {
                    modalDev: "-1",
                    modalSize: 'lg',
                    modalTitle: `Cargar productos desde Excel`,
                    modalForm: 'cargarProductosExcel',
                    buttonAcceptShow: true,
                    buttonAcceptText: 'Guardar',
                    buttonAcceptIcon: 'save',
                    buttonCancelShow: true,
                    buttonCancelText: 'Cancelar'
                }
            );
        }

        // =============== Select2 init ===============
        $("#tipoProductoId").select2({ width: "100%", placeholder: "Seleccione un tipo de producto" });

        $("#marcaId").select2({
            placeholder: "Seleccione una marca",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectMarcas",
                dataType: 'json',
                delay: 250,
                data: function (params) { return { busquedaSelect: params.term }; },
                processResults: function (data) { return { results: data }; },
                cache: true
            }
        });

        $("#categoriaPrincipal").select2({
            placeholder: "Seleccione Categoría Principal",
            width: "100%",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectCategoriaInvetario",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { flgPrincipal: 1, busquedaSelect: params.term };
                },
                processResults: function (data) { return { results: data }; },
                cache: true
            }
        });

        $("#sucursalId").select2({
            placeholder: "Seleccion la sucursal a usar",
            width: "100%",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectSucursales",
                dataType: 'json',
                delay: 250,
                data: function (params) { return { busquedaSelect: params.term }; },
                processResults: function (data) { return { results: data }; },
                cache: true
            }
        });

        $("#inventarioUbicacionId").select2({
            placeholder: "Seleccione la ubicacion a guardar el producto",
            width: "100%",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectUbicacionesSucursal",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        sucursalId: $("#sucursalId").val(),
                        busquedaSelect: params.term
                    };
                },
                processResults: function (data) { return { results: data }; },
                cache: true
            }
        });

        $("#unidadMedidaId").select2({
            placeholder: "Seleccione Unidad de Medida",
            width: "100%",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectUnidadMedida",
                dataType: 'json',
                delay: 250,
                data: function (params) { return { busquedaSelect: params.term }; },
                processResults: function (data) { return { results: data }; },
                cache: true
            }
        });

        $("#categorias").select2({
            placeholder: "Escribe un tag y presiona Enter...",
            width: "100%",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectCategoriaInvetario",
                dataType: 'json',
                delay: 250,
                data: function (params) { return { busquedaSelect: params.term }; },
                processResults: function (data) { return { results: data }; },
                cache: true
            }
        });

        $("#paisId").select2({
            placeholder: "Seleccione País de Origen",
            width: "100%",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectPaises",
                dataType: 'json',
                delay: 250,
                data: function (params) { return { busquedaSelect: params.term }; },
                processResults: function (data) { return { results: data }; },
                cache: true
            }
        });

        function setDefaultPaisTurquia() {
            const $pais = $("#paisId");
            const defaultId = "225";
            const defaultText = "Turquía";
            if (!$pais.val()) {
                const option = new Option(defaultText, defaultId, true, true);
                $pais.append(option).trigger("change");
            }
        }
        if (!isUpdate) setDefaultPaisTurquia();

        // =============== Especificaciones ===============
        function initSelectUdm(idSelect, tipoMagnitud, nombreProdEspecificacion, overrideUdmId = null) {
            const $select = $(`#${idSelect}`);

            $select.select2({
                placeholder: "Unidad de medida",
                width: "100%",
                ajax: {
                    type: "POST",
                    url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectUnidadMedidaMagnitud",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { tipoMagnitud: tipoMagnitud, busquedaSelect: params.term || null };
                    },
                    processResults: function (data) { return { results: data }; },
                    cache: true
                }
            });

            let defaultUdmId = overrideUdmId;

            if (!defaultUdmId) {
                switch (nombreProdEspecificacion) {
                    case 'Largo':
                    case 'Ancho':
                    case 'Altura':
                    case 'Diámetro':
                    case 'Longitud':
                        defaultUdmId = 9; break;
                    case 'Peso':
                        defaultUdmId = 52; break;
                    case 'Pureza':
                        defaultUdmId = 67; break;
                    default:
                        defaultUdmId = null;
                }
            }

            if (defaultUdmId) {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectUnidadMedidaMagnitud",
                    dataType: "json",
                    data: { tipoMagnitud: tipoMagnitud }
                }).then(function (data) {
                    if (!Array.isArray(data)) return;
                    const item = data.find(d => String(d.id) === String(defaultUdmId));
                    if (!item) return;
                    const option = new Option(item.text, item.id, true, true);
                    $select.append(option).trigger('change');
                });
            } else {
                $select.val(null).trigger('change');
            }
        }

        function renderEspecificaciones(data, mapaGuardadas = {}) {
            let html = '';
            let count = 1;

            data.forEach(item => {
                const guardada = mapaGuardadas[String(item.id)] || null;
                const valorGuardado = guardada ? (guardada.valor ?? '') : (item.nombreProdEspecificacion === "Pureza" ? 14 : '');

                html += `
          <tr>
            <input type="hidden" class="especId" value="${item.id}">
            <td><input type="text" readonly class="form-control especNombre" value="${item.nombreProdEspecificacion}"></td>
            <td><input type="text" value="${valorGuardado}" class="form-control especCantidad"></td>
            <td>
              <select readonly id="especificacionUdm${count}" class="form-select" style="width: 100%;">
                <option value="">Unidad</option>
              </select>
            </td>
            <td class="text-center">
              <input type="checkbox" ${item.esObligatoria == 1 ? 'checked' : ''} disabled>
            </td>
          </tr>
        `;
                count++;
            });

            $("#tablaEspecificacionesObligatorias").html(html);

            count = 1;
            data.forEach(item => {
                const guardada = mapaGuardadas[String(item.id)] || null;
                const udmOverride = guardada ? (guardada.unidadMedidaId ?? null) : null;
                initSelectUdm(`especificacionUdm${count}`, item.tipoMagnitud, item.nombreProdEspecificacion, udmOverride);
                count++;
            });
        }

        function cargarEspecificacionesCategoria(categoriaId, especGuardadasArr = []) {
            const mapa = {};
            especGuardadasArr.forEach(e => {
                mapa[String(e.catProdEspecificacionId)] = {
                    valor: e.valorEspecificacion,
                    unidadMedidaId: e.unidadMedidaId
                };
            });

            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>content/divs/getEspecificacionObligatoria",
                { inventarioCategoriaId: categoriaId },
                function (data) {
                    renderEspecificaciones(data, mapa);
                }
            );
        }

        // cuando el usuario cambia categoría manualmente (insert o update)
        $('#categoriaPrincipal').on('select2:select', function () {
            cargarEspecificacionesCategoria($(this).val(), []);
        });

        // =============== Cargar datos en UPDATE ===============
        function cargarProductoUpdate() {
            const productoId = $("#productoId").val();
            if (!productoId) return;

            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>content/divs/getProductoEditar",
                { productoId: productoId },
                function (p) {

                    $("#nombreProducto").val(p.nombreProducto || "");
                    $("#codInterno").val(p.codInterno || "");
                    $("#descripcionProducto").val(p.descripcionProducto || "");
                    $("#codFabricante").val(p.codFabricante || "");
                    $("#obsEstadoProducto").val(p.obsEstadoProducto || "");

                    setSelect2Value($("#categoriaPrincipal"), p.inventarioCategoriaPrincipalId, p.categoriaText);
                    setSelect2Value($("#marcaId"), p.marcaId, p.marcaText);
                    setSelect2Value($("#unidadMedidaId"), p.unidadMedidaId, p.udmText);
                    setSelect2Value($("#paisId"), p.paisIdOrigen, p.paisText);

                    setSelect2Value($("#sucursalId"), p.sucursalId, p.sucursalText);
                    setSelect2Value($("#inventarioUbicacionId"), p.inventarioUbicacionId, p.ubicacionText);

                    if (p.tipoProductoId) $("#tipoProductoId").val(String(p.tipoProductoId)).trigger("change");

                    if (Array.isArray(p.tags)) {
                        p.tags.forEach(t => {
                            const opt = new Option(t.text, t.id, true, true);
                            $("#categorias").append(opt);
                        });
                        $("#categorias").trigger("change");
                    }

                    cargarEspecificacionesCategoria(p.inventarioCategoriaPrincipalId, p.especificaciones || []);
                }
            );
        }

        if (isUpdate) {
            cargarProductoUpdate();
        }

        // =============== Confirm (insert/update) ===============
        function confirmarGuardarProducto(datos, onAceptar) {
            const html = `
        <div style="text-align:left">
          <div><b>Nombre:</b> ${datos.nombre}</div>
          <div><b>SKU:</b> <span class="text-primary">${datos.sku}</span></div>
          <hr>
          <div><b>Categoría:</b> ${datos.categoriaText || datos.categoria}</div>
          <div><b>Marca:</b> ${datos.marcaText || datos.marcaId}</div>
          <div><b>U. Medida:</b> ${datos.udmText || datos.udm}</div>
          <div><b>País:</b> ${datos.paisText || (datos.pais || "—")}</div>
        </div>
      `;

            if (typeof mensaje_confirmar === "function") {
                mensaje_confirmar(isUpdate ? "Confirmar actualización" : "Confirmar guardado", html, "question", onAceptar);
                return;
            }

            if (confirm(`${isUpdate ? "Confirmar actualización" : "Confirmar guardado"}\n\nNombre: ${datos.nombre}\nSKU: ${datos.sku}\n\n¿Continuar?`)) {
                onAceptar();
            }
        }

        // =============== Submit ===============
        $("#formProducto").validate({
            submitHandler: function () {

                // specs: al menos una con valor
                let especificaciones = obtenerEspec();
                const tieneEspecificacion = especificaciones.some(e => e.valor && e.valor.trim() !== "");
                if (!tieneEspecificacion) {
                    mensaje("Datos incompletos:", "agrega al menos una especificación del producto", "warning");
                    return;
                }

                // básicos
                if (!$("#nombreProducto").val().trim()) { mensaje("Datos incompletos:", "completa el nombre del producto", "warning"); return; }
                if (!$("#categoriaPrincipal").val()) { mensaje("Datos incompletos:", "selecciona la categoría principal", "warning"); return; }
                if (!$("#tipoProductoId").val()) { mensaje("Datos incompletos:", "selecciona el tipo de producto", "warning"); return; }

                if (!$("#marcaId").val()) { mensaje("Datos incompletos:", "selecciona la marca", "warning"); return; }
                if (!$("#unidadMedidaId").val()) { mensaje("Datos incompletos:", "selecciona la unidad de medida", "warning"); return; }

                prepararSKU(function () {

                    const datos = {
                        typeOperation: $("#typeOperation").val(), // insert | update
                        operation: "producto",
                        productoId: isUpdate ? $("#productoId").val() : null,

                        nombre: $("#nombreProducto").val().trim(),
                        sku: $("#codInterno").val().trim(),
                        categoria: $("#categoriaPrincipal").val(),
                        tipo: $("#tipoProductoId").val(),
                        descripcion: $("#descripcionProducto").val().trim(),
                        codFabricante: $("#codFabricante").val().trim(),
                        marcaId: $("#marcaId").val(),
                        udm: $("#unidadMedidaId").val(),
                        tags: $("#categorias").val(),
                        pais: $("#paisId").val(),
                        estado: $("#estadoProducto").val(),
                        obs: $("#obsEstadoProducto").val().trim(),
                        especificaciones: obtenerEspec(),

                        sucursalId: $("#sucursalId").val(),
                        inventarioUbicacionId: $("#inventarioUbicacionId").val(),

                        categoriaText: $("#categoriaPrincipal").select2("data")?.[0]?.text,
                        marcaText: $("#marcaId").select2("data")?.[0]?.text,
                        udmText: $("#unidadMedidaId").select2("data")?.[0]?.text,
                        paisText: $("#paisId").select2("data")?.[0]?.text
                    };

                    if (!datos.sku) {
                        mensaje("Datos incompletos:", "no se pudo generar el SKU", "warning");
                        return;
                    }

                    confirmarGuardarProducto(datos, function () {
                        asyncData(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/",
                            datos,
                            function (resp) {
                                if (resp === "success") {
                                    mensaje_do_aceptar(
                                        "Operación completada",
                                        isUpdate ? "Se actualizó el producto con éxito" : "Se creó el producto con éxito",
                                        "success",
                                        function () { asyncPage(173, "submenu"); }
                                    );
                                } else {
                                    mensaje("Aviso:", resp, "warning");
                                }
                            }
                        );
                    });
                });
            }
        });

    });
</script>