<?php
require_once("../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$funtionObtenerOro = htmlspecialchars(json_encode(
    array(
        "typeOperation" => "insert",
        "operation" => "precio-oro"
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
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Precios de venta</h3>
        <button id="btn" type="button" class="ml-2 btn btn-primary" onclick='calcularCosto(<?= $funtionObtenerOro ?>)'>
            <i class="fas fa-plus-circle"></i> Actualizar Costo Oro
        </button>
    </div>

    <!-- FILTROS -->
    <div class="row mb-3">
        <div class="col-md-3 mb-2">
            <label for="fSku" class="form-label mb-1">SKU / Código</label>
            <input type="text" id="fSku" class="form-control form-control-sm" placeholder="Buscar por código">
        </div>
        <div class="col-md-3 mb-2">
            <label for="fNombre" class="form-label mb-1">Nombre producto</label>
            <input type="text" id="fNombre" class="form-control form-control-sm" placeholder="Buscar por nombre">
        </div>
        <div class="col-md-3 mb-2">
            <label for="fCategoria" class="form-label mb-1">Categoría principal</label>
            <select id="fCategoria" class="form-control form-control-sm">
                <option value="">Todas</option>
                <?php foreach ($categorias as $c) { ?>
                    <option value="<?= $c->inventarioCategoriaId; ?>">
                        <?= htmlspecialchars($c->nombreCategoria); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-3 mb-2">
            <label for="fMarca" class="form-label mb-1">Marca</label>
            <select id="fMarca" class="form-control form-control-sm">
                <option value="">Todas</option>
                <?php foreach ($marcas as $m) { ?>
                    <option value="<?= $m->marcaId; ?>">
                        <?= htmlspecialchars($m->nombreMarca); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <!-- Botón reset -->
        <div class="col-12 mt-2 text-end">
            <button type="button" id="btnResetFiltros" class="btn btn-sm btn-secondary">
                <i class="fas fa-eraser"></i> Limpiar filtros
            </button>
        </div>
    </div>


    <div class="table-responsive">
        <table id="tblPrecio" class="table table-hover" style="width: 100%;">
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
    function calcularCosto(frmData) {
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
            columns: [
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ],
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

            // opcional: limpiar búsqueda interna de DT (si en algún momento activas search global)
            tblPrecio.search('');

            // recargar tabla desde la página 1 sin filtros
            tblPrecio.ajax.reload();
        });
    });
</script>