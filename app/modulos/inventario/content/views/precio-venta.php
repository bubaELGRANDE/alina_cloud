<?php
require_once("../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$funtionObtenerOro = htmlspecialchars(json_encode(
    array(
        "typeOperation" => "insert",
        "operation" => "precio-oro"
    )
));

$funcionCostos = htmlspecialchars(json_encode(
    array(
        "typeOperation" => "update",
        "operation" => "updatate-costo"
    )
));

// categorías principales
$categorias = $cloud->rows("
    SELECT inventarioCategoriaId, nombreCategoria
    FROM cat_inventario_categorias
    WHERE flgDelete = 0
");

// marcas
$marcas = $cloud->rows("
    SELECT marcaId, nombreMarca
    FROM cat_inventario_marcas
    WHERE flgDelete = 0
");
?>

<div class="container py-4">

    <!-- Encabezado + acciones -->
    <div class="row align-items-center g-2 mb-3">
        <div class="col-12 col-lg">
            <h3 class="fw-bold mb-0">Precios de venta</h3>
        </div>

        <div class="col-12 col-lg-auto">
            <!-- En móvil: stack / En escritorio: inline -->
            <div class="d-grid d-lg-flex gap-2">
                <button type="button" class="btn btn-outline-warning" onclick="modalPrecioOro();">
                    <i class="fas fa-coins me-1"></i> Ver Precio Actual
                </button>
                <!--<button type="button" class="btn btn-primary" onclick='ActualizarValorOro(<?= $funtionObtenerOro ?>)'>
                    <i class="fas fa-sync-alt me-1"></i> Actualizar Costo Oro
                </button>-->
                <button type="button" class="btn btn-primary" onclick='changePage(`<?php echo $_SESSION["currentRoute"]; ?>`, `precio-venta`, ``)'>
                    <i class="fas fa-sync-alt me-1"></i> Actualizar Precios de venta
                </button>
                <button type="button" class="btn btn-outline-primary" onclick='updateCostos(<?= $funcionCostos ?>)'>
                    <i class="fas fa-money me-1"></i> Actualizar costos productos
                </button>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6 col-lg-3">
            <label for="fSku" class="form-label mb-1">SKU / Código</label>
            <input type="text" id="fSku" class="form-control form-control-sm" placeholder="Buscar por código">
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <label for="fNombre" class="form-label mb-1">Nombre producto</label>
            <input type="text" id="fNombre" class="form-control form-control-sm" placeholder="Buscar por nombre">
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <label for="fCategoria" class="form-label mb-1">Categoría principal</label>
            <select id="fCategoria" class="form-select form-select-sm">
                <option value="">Todas</option>
                <?php foreach ($categorias as $c) { ?>
                    <option value="<?= $c->inventarioCategoriaId; ?>">
                        <?= htmlspecialchars($c->nombreCategoria); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <label for="fMarca" class="form-label mb-1">Marca</label>
            <select id="fMarca" class="form-select form-select-sm">
                <option value="">Todas</option>
                <?php foreach ($marcas as $m) { ?>
                    <option value="<?= $m->marcaId; ?>">
                        <?= htmlspecialchars($m->nombreMarca); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <!-- Botón reset -->
        <div class="col-12">
            <div class="d-grid d-sm-flex justify-content-sm-end">
                <button type="button" id="btnResetFiltros" class="btn btn-sm btn-secondary">
                    <i class="fas fa-eraser me-1"></i> Limpiar filtros
                </button>
            </div>
        </div>
    </div>

    <!-- TABLA (DataTable normal) -->
    <div class="table-responsive">
        <table id="tblPrecio" class="table table-hover table-sm align-middle w-100">
            <thead>
                <tr id="filterboxrow">
                    <th>#</th>
                    <th>Codigo</th>
                    <th>Productos</th>
                    <th>Costos</th>
                    <th>Precio Vigente</th>
                    <th>Datos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</div>

<script>
    function modalPrecioOro() {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: 'Precio Actual del Oro',
                modalForm: 'precioMetalActual',
                formData: 'view',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar',
                buttonCancelIcon: 'times'
            }
        );
    }

    function ActualizarValorOro(frmData) {
        asyncData("<?= $_SESSION['currentRoute']; ?>transaction/operation/", frmData, function (resp) {
            const t = (typeof resp === 'string') ? resp.trim() : (resp?.toString?.().trim() || '');
            if (t === 'success') mensaje('Éxito', 'Operación completada.', 'success');
            else mensaje('Error', t || 'Ocurrió un error.', 'error');
        });
    }

    function updateCostos(frmData) {
        asyncData("<?= $_SESSION['currentRoute']; ?>transaction/operation/", frmData, function (resp) {
            const t = (typeof resp === 'string') ? resp.trim() : (resp?.toString?.().trim() || '');
            if (t === 'success') mensaje('Éxito', 'Operación completada.', 'success');
            else mensaje('Error', t || 'Ocurrió un error.', 'error');
        });
    }

    $(document).ready(function () {

        let tblPrecio = $('#tblPrecio').DataTable({
            dom: 'lrtip',
            ajax: {
                method: "POST",
                url: "<?= $_SESSION['currentRoute']; ?>content/tables/tablePreciosProductos",
                data: function (d) {
                    d.fSku = $('#fSku').val();
                    d.fNombre = $('#fNombre').val();
                    d.fCategoria = $('#fCategoria').val();
                    d.fMarca = $('#fMarca').val();
                }
            },
            autoWidth: false,
            columns: [null, null, null, null, null, null, null],
            columnDefs: [
                { orderable: false, targets: [1, 2, 6] }
            ],
            language: {
                url: "../libraries/packages/js/spanish_dt.json"
            }
        });

        // recargar al cambiar filtros
        $('#fSku, #fNombre').on('keyup change', function () {
            tblPrecio.ajax.reload();
        });

        $('#fCategoria, #fMarca').on('change', function () {
            tblPrecio.ajax.reload();
        });

        // botón: limpiar filtros
        $('#btnResetFiltros').on('click', function () {
            $('#fSku').val('');
            $('#fNombre').val('');
            $('#fCategoria').val('');
            $('#fMarca').val('');
            tblPrecio.search('');
            tblPrecio.ajax.reload();
        });
    });
</script>