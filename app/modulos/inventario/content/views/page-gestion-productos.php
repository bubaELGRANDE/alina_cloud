<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
?>
<style>
    body {
        background: #f5f6f7;
    }

    /* Leyenda */
    .legend-dot {
        width: 16px;
        height: 16px;
        border-left: 4px solid;
        margin-right: 8px;
    }

    /* Prioridades en borde izquierdo de cada card */
    .card-obligatorio {
        border-left: 4px solid #dc3545 !important;
    }

    /* rojo */
    .card-importante {
        border-left: 4px solid #fd7e14 !important;
    }

    /* naranja */
    .card-opcional {
        border-left: 4px solid #0d6efd !important;
    }

    /* azul */
    /* Títulos suaves por prioridad */
    .title-obligatorio,
    .title-importante,
    .title-opcional {
        border-radius: 6px;
        padding: 6px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* pastel azul */
    /* Ícono a la izquierda del texto del título */
    .title-label {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .title-icon {
        font-size: 1.1rem;
    }

    /* Tooltip y requerido */
    .tooltip-label {
        cursor: help;
        text-decoration: dotted underline;
    }

    .required:after {
        content: " *";
        color: #dc3545;
    }

    /* Animación colapsable */
    .collapse {
        transition: all 0.25s ease-in-out;
    }

    /* Botón regresar en título */
    .btn-back {
        font-size: .9rem;
        padding: 2px 10px;
    }

    /* Chips de tags */
    .tag-chip {
        background: #f1f1f1;
        border-radius: 20px;
        padding: 6px 12px;
        display: inline-flex;
        align-items: center;
        font-size: 0.9rem;
    }

    .tag-chip .btn-close {
        font-size: 0.65rem;
        margin-left: 6px;
    }
</style>
<div class="container py-4">
    <h2 class="mb-4 fw-bold">Crear Nuevo Producto</h2>
    <div class="alert shadow-sm mb-4" role="alert">
        <div class="d-flex justify-content-between flex-wrap gap-4">
            <!-- Obligatorio -->
            <div class="d-flex align-items-start" style="min-width: 250px;">
                <div class="legend-dot flex-shrink-0 mt-1" style="border-color:#dc3545;"></div>
                <div class="ms-2">
                    <span class="fw-semibold text-danger d-block">Obligatorio</span>
                    <small class="text-muted d-block">Datos necesarios para crear el producto y generar el SKU.</small>
                </div>
            </div>
            <!-- Importante -->
            <div class="d-flex align-items-start" style="min-width: 250px;">
                <div class="legend-dot flex-shrink-0 mt-1" style="border-color:#fd7e14;"></div>
                <div class="ms-2">
                    <span class="fw-semibold" style="color:#fd7e14;">Importante</span>
                    <small class="text-muted d-block">Altamente recomendado para descripción, control y
                        reportes.</small>
                </div>
            </div>
            <!-- Opcional -->
            <div class="d-flex align-items-start" style="min-width: 250px;">
                <div class="legend-dot flex-shrink-0 mt-1" style="border-color:#0d6efd;"></div>
                <div class="ms-2">
                    <span class="fw-semibold text-primary d-block">Opcional</span>
                    <small class="text-muted d-block">Información complementaria, no requerida.</small>
                </div>
            </div>
        </div>
    </div>
    <button class="btn btn-primary mb-2" id="generarSKU">generar SKU</button>
    <button class="btn btn-success mb-2" id="cargarExcel">
        <i class="fas fa-file-excel"></i> Cargar por excel</button>
    <form id="formProducto">
        <input type="hidden" id="typeOperation" name="typeOperation" value="insert">
        <input type="hidden" id="operation" name="operation" value="producto">

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
                        <!-- Nombre -->
                        <div class="col-md-8">
                            <label class="form-label required tooltip-label" data-bs-toggle="tooltip"
                                title="Escribe el nombre completo del producto">Nombre del Producto</label>
                            <input type="text" name="nombreProducto" id="nombreProducto"
                                class="form-control form-control-lg" required>
                        </div>
                        <!-- SKU (GENERADO AUTOMÁTICO) -->
                        <div class="col-md-4">
                            <label class="form-label">Código Interno (SKU)</label>
                            <div class="input-group">
                                <span class="input-group-text">SKU</span>
                                <input type="text" class="form-control" name="codInterno" id="codInterno" readonly>
                            </div>
                            <div class="form-text">Se generará automáticamente más adelante.</div>
                        </div>
                        <!-- Categoría principal -->
                        <div class="col-md-6">
                            <label class="form-label required">Categoría Principal</label>
                            <select id="categoriaPrincipal" name="inventarioCategoriaPrincipalId"
                                class="form-select form-select-lg" required>
                                <option value=""></option>
                            </select>
                        </div>
                        <!-- Marca -->
                        <div class="col-md-6">
                            <label class="form-label">Marca</label>
                            <select name="marcaId" id="marcaId" class="form-select form-select-lg">
                                <option>Seleccione Marca</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- ===================================================== -->
        <!-- ESPECIFICACIONES OBLIGATORIAS POR CATEGORÍA - IMPORTANTE -->
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
                        Estas especificaciones corresponden a la categoría principal seleccionada y se utilizarán
                        para generar el SKU automáticamente.
                        <br><strong>Por ahora están deshabilitadas, hasta implementar la lógica dinámica.</strong>
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
                            <tbody id="tablaEspecificacionesObligatorias">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- ===================================================== -->
        <!-- INFORMACIÓN SECUNDARIA - IMPORTANTE -->
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
                        <!-- Descripción -->
                        <div class="col-md-12">
                            <label class="form-label">Descripción del Producto</label>
                            <textarea id="descripcionProducto" name="descripcionProducto" class="form-control"
                                rows="2"></textarea>
                        </div>
                        <!-- Código Fabricante -->
                        <div class="col-md-4">
                            <label class="form-label">Código del Fabricante</label>
                            <input type="text" id="codFabricante" name="codFabricante" class="form-control">
                        </div>

                        <!-- Unidad de Medida -->
                        <div class="col-md-4">
                            <label class="form-label required">Unidad de Medida</label>
                            <?php
                            $unidades = $cloud->rows("
                                SELECT unidadMedidaId, nombreUnidadMedida, abreviaturaUnidadMedida
                                FROM cat_unidades_medida
                                WHERE flgDelete = 0
                                ORDER BY nombreUnidadMedida
                            ");
                            $unidadSeleccionada = 1;
                            ?>

                            <select id="unidadMedidaId" name="unidadMedidaId" class="form-select" required>
                                <option value="">Seleccione Unidad de Medida</option>

                                <?php foreach ($unidades as $u): ?>
                                    <option value="<?= $u->unidadMedidaId ?>" <?= ($u->unidadMedidaId == $unidadSeleccionada) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u->nombreUnidadMedida) ?>
                                        (<?= htmlspecialchars($u->abreviaturaUnidadMedida) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        </div>
                        <!-- País -->
                        <div class="col-md-4">
                            <label class="form-label">País de Origen</label>
                            <select id="paisId" name="paisIdOrigen" class="form-select">
                                <option value="">Seleccione País de Origen</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ===================================================== -->
        <!-- ORIGEN Y RELACIONES - IMPORTANTE -->
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
                        <!-- Tipo de Producto -->
                        <div class="col-md-4">
                            <label class="form-label required">Sucursal</label>
                            <select id="sucursalId" name="sucursalId" class="form-select form-select-lg" required>
                                <option value="">Seleccione la sucursal </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Ubicación</label>
                            <select id="inventarioUbicacionId" name="ubicacionId" class="form-select form-select-lg"
                                required>
                                <option value="">Seleccione la ubicación</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Tipo de Producto</label>
                            <?php
                            // Traes los tipos de producto
                            $data = $cloud->rows("
                                SELECT tipoProductoId, nombreTipoProducto
                                FROM cat_inventario_tipos_producto
                                WHERE flgDelete = 0
                            ");

                            $tipoProductoSeleccionado = $producto['tipoProductoId'] ?? 1;
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
                </div><!-- /collapse -->
            </div>
        </div>
        <!-- ===================================================== -->
        <!-- TAGS (HASHTAGS) - OPCIONAL -->
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
                    <p class="text-muted">Agrega etiquetas tipo hashtag para facilitar búsquedas internas. Escribe
                        una palabra y presiona <strong>Enter</strong>.</p>
                    <div class="mb-3">
                        <select class="d-flex flex-wrap gap-2" name="categorias[]" multiple="multiple"
                            id="categorias"></select>
                    </div>
                </div>
            </div>
        </div>
        <!-- ===================================================== -->
        <!-- ESTADO - OPCIONAL -->
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
                </div><!-- /collapse -->
            </div>
        </div>
        <!-- BOTÓN -->
        <div class="text-end mb-5">
            <button type="submit" class="btn btn-primary btn-lg px-5">Guardar Producto</button>
        </div>
    </form>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

    $(document).ready(function () {

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



        $("#generarSKU").on("click", generarSKU);

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


        $("#tipoProductoId").select2({
            width: "100%",
            placeholder: "Seleccione un tipo de producto",
        });

        $("#marcaId").select2({
            placeholder: "Seleccione una marca",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectMarcas",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        busquedaSelect: params.term,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
        $("#unidadMedidaId").select2({
            width: "100%",
            placeholder: "Seleccione Unidad de Medida",
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
                    return {
                        flgPrincipal: 1,
                        busquedaSelect: params.term,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        function initSelectUdm(idSelect, tipoMagnitud, nombreProdEspecificacion) {
            const $select = $(`#${idSelect}`);

            // 1) Inicializar Select2
            $select.select2({
                placeholder: "Unidad de medida",
                width: "100%",
                ajax: {
                    type: "POST",
                    url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectUnidadMedidaMagnitud",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            tipoMagnitud: tipoMagnitud,
                            busquedaSelect: params.term || null
                        };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });

            let defaultUdmId = null;

            /*
                Largo    -> UDM id 9
                Ancho    -> UDM id 9
                Altura   -> UDM id 9
                Diámetro -> UDM id 9
                Peso     -> UDM id 52
                Pureza   -> UDM id 67
            */

            switch (nombreProdEspecificacion) {
                case 'Largo':
                case 'Ancho':
                case 'Altura':
                case 'Diámetro':
                case 'Longitud':
                    defaultUdmId = 9;
                    break;
                case 'Peso':
                    defaultUdmId = 52;
                    break;
                case 'Pureza':
                    defaultUdmId = 67;
                    break;
                default:
                    defaultUdmId = null;
            }

            // 3) Si hay default, lo cargamos desde TU MISMO endpoint y lo seleccionamos
            if (defaultUdmId) {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectUnidadMedidaMagnitud",
                    dataType: "json",
                    data: { tipoMagnitud: tipoMagnitud }
                }).then(function (data) {
                    if (!Array.isArray(data)) return;

                    // Buscar la UDM con ese id dentro del resultado
                    const item = data.find(d => String(d.id) === String(defaultUdmId));

                    if (!item) return;

                    // Crear option seleccionado y disparar change para que Select2 lo muestre
                    const option = new Option(item.text, item.id, true, true);
                    $select.append(option).trigger('change');
                });
            } else {
                // Si no hay default para ese tipo, lo dejamos vacío
                $select.val(null).trigger('change');
            }
        }


        function obtenerEspec() {
            let especificaciones = [];
            $("#tablaEspecificacionesObligatorias tr").each(function () {
                const id = $(this).find(".especId").val();
                const nombre = $(this).find(".especNombre").val();
                const valor = $(this).find(".especCantidad").val();
                const unidadMedida = $(this).find("select").val();

                if (valor) {
                    especificaciones.push({
                        id,
                        nombre,
                        valor,
                        unidadMedida
                    })
                }
            });

            return especificaciones;
        }

        $('#categoriaPrincipal').on('select2:select', function (e) {

            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>content/divs/getEspecificacionObligatoria",
                { inventarioCategoriaId: $(this).val() },
                function (data) {
                    let html = '';
                    let count = 1;
                    data.forEach(item => {

                        html += `
                            <tr>
                            <input type="hidden" class="especId" value="${item.id}">
                                <td><input type="text" readonly class="form-control especNombre" value="${item.nombreProdEspecificacion}"></td>
                                <td><input type="text" value="${item.nombreProdEspecificacion === "Pureza" ? 14 : ''}" class="form-control especCantidad"></td>
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

                    // inicializar select2 DESPUÉS
                    count = 1;
                    data.forEach(item => {
                        initSelectUdm(`especificacionUdm${count}`, item.tipoMagnitud, item.nombreProdEspecificacion);
                        count++;
                    });

                }
            );

        });



        $("#sucursalId").select2({
            placeholder: "Seleccion la sucursal a usar",
            width: "100%",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectSucursales",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        busquedaSelect: params.term,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
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
                data: function (params) {
                    return {
                        busquedaSelect: params.term,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
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
                        busquedaSelect: params.term,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
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
                data: function (params) {
                    return {
                        busquedaSelect: params.term,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });


        function setDefaultPaisTurquia() {
            const $pais = $("#paisId");
            const defaultId = "225";
            const defaultText = "Turquía";

            // Solo si está vacío
            if (!$pais.val()) {
                const option = new Option(defaultText, defaultId, true, true);
                $pais.append(option).trigger("change");
            }
        }


        $("#paisId").select2({
            placeholder: "Seleccione País de Origen",
            width: "100%",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectPaises",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { busquedaSelect: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            }
        });

        // SOLO EN INSERT
        if ($("#typeOperation").val() === "insert") {
            setDefaultPaisTurquia();
        }




        function confirmarGuardarProducto(datos, onAceptar) {
            const html = `
                <div style="text-align:left">
                    <div><b>Nombre:</b> ${datos.nombre}</div>
                    <div><b>SKU nuevo:</b> <span class="text-primary">${datos.sku}</span></div>
                    <hr>
                    <div><b>Categoría:</b> ${datos.categoriaText || datos.categoria}</div>
                    <div><b>Marca:</b> ${datos.marcaText || datos.marcaId}</div>
                    <div><b>U. Medida:</b> ${datos.udmText || datos.udm}</div>
                    <div><b>País:</b> ${datos.paisText || (datos.pais || "—")}</div>
                </div>
            `;

            // Si tu helper ya tiene confirmación (Aceptar/Cancelar), usalo aquí.
            if (typeof mensaje_confirmar === "function") {
                mensaje_confirmar("Confirmar guardado", html, "question", onAceptar);
                return;
            }

            // Si solo tenés "aceptar", fallback a confirm()
            if (confirm(`Confirmar guardado\n\nNombre: ${datos.nombre}\nSKU: ${datos.sku}\n\n¿Guardar?`)) {
                onAceptar();
            }
        }

        $("#formProducto").validate({
            submitHandler: function (form) {

                // ESPECIFICACIONES: debe haber al menos una con valor
                let especificaciones = obtenerEspec();

                const tieneEspecificacion = especificaciones.some(e => e.valor && e.valor.trim() !== "");

                if (!tieneEspecificacion) {
                    mensaje(
                        "Datos incompletos:",
                        "agrega al menos una especificación del producto",
                        "warning"
                    );
                    return;
                }

                // BASICO
                let nombre = $("#nombreProducto");
                let categoria = $("#categoriaPrincipal");
                let tipo = $("#tipoProductoId");

                if (!nombre.val().trim()) {
                    mensaje("Datos incompletos:", "completa el nombre del producto", "warning");
                    return;
                }

                if (!categoria.val()) {
                    mensaje("Datos incompletos:", "selecciona la categoría principal", "warning");
                    return;
                }

                if (!tipo.val()) {
                    mensaje("Datos incompletos:", "selecciona el tipo de producto", "warning");
                    return;
                }

                // SECUNDARIO
                let marcaId = $("#marcaId");
                let udm = $("#unidadMedidaId");

                if (!marcaId.val()) {
                    mensaje("Datos incompletos:", "selecciona la marca", "warning");
                    return;
                }

                if (!udm.val()) {
                    mensaje("Datos incompletos:", "selecciona la unidad de medida", "warning");
                    return;
                }

                // ESTADO
                let estado = $("#estadoProducto");

                if (!estado.val()) {
                    mensaje("Datos incompletos:", "selecciona el estado del producto", "warning");
                    return;
                }

                generarSKU(function () {

                    const datos = {
                        typeOperation: 'insert',
                        operation: 'producto',
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

                        // textos (para mostrar bonito en el confirm)
                        categoriaText: $("#categoriaPrincipal").select2('data')?.[0]?.text,
                        marcaText: $("#marcaId").select2('data')?.[0]?.text,
                        udmText: $("#unidadMedidaId").select2('data')?.[0]?.text,
                        paisText: $("#paisId").select2('data')?.[0]?.text
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
                                if (resp == 'success') {
                                    mensaje_do_aceptar(
                                        `Operación completada`,
                                        `Se proceso la creación de la solicitud con éxito`,
                                        `success`,
                                        function () {
                                            asyncPage(173, 'submenu');
                                        });
                                }
                                else {
                                    mensaje(
                                        "Aviso:",
                                        resp,
                                        "warning"
                                    );
                                }
                            }
                        );

                    });
                });
            }
        });
    });
</script>