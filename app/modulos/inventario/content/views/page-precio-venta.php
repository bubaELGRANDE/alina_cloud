<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

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
        <h3 class="fw-bold">Actulizar precios de ventas</h3>
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
        <table id="tblPrecioVenta" class="table table-hover" style="width: 100%;">
            <thead>
                <tr id="filterboxrow">
                    <th>#</th>
                    <th>Código</th>
                    <th>Información</th>
                    <th>Características</th>
                    <th>Costos</th>
                    <th>Precio Venta <small class="text-muted">(sin IVA)</small></th>
                    <th>Precio con IVA</th>
                    <th>Sugerido <small class="text-muted">+1%</small></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script>
    // Función para validar que el precio no sea menor al mínimo (costo + 1%)
    function validarPrecioMinimo(input) {
        const precioMinimo = parseFloat(input.dataset.precioMinimo) || 0;
        const valorIngresado = parseFloat(input.value) || 0;
        
        if (valorIngresado < precioMinimo) {
            input.classList.add('is-invalid');
            Swal.fire({
                icon: 'warning',
                title: 'Precio muy bajo',
                html: `El precio de venta no puede ser menor al costo + 1% de utilidad.<br><b>Precio mínimo: $${precioMinimo.toFixed(2)}</b>`,
                confirmButtonText: 'Entendido'
            });
            input.value = precioMinimo.toFixed(2);
            calcularPrecioConIVA(input);
        } else {
            input.classList.remove('is-invalid');
        }
    }

    // Función para calcular y mostrar el precio con IVA en tiempo real
    function calcularPrecioConIVA(input) {
        const precioId = input.dataset.precioId;
        const precioSinIVA = parseFloat(input.value) || 0;
        const precioConIVA = precioSinIVA * 1.13;
        
        const spanIVA = document.getElementById('pv_iva_' + precioId);
        if (spanIVA) {
            spanIVA.textContent = '$' + precioConIVA.toFixed(2);
        }
    }

    // Función para actualizar el precio de venta
    function actualizarPrecioVenta(data) {
        const input = document.getElementById('pv_' + data.productoPrecioId);
        const precioVentaSinIVA = parseFloat(input.value) || 0;
        const precioMinimo = parseFloat(data.precioMinimo) || 0;
        
        // Validación final antes de enviar
        if (precioVentaSinIVA < precioMinimo) {
            Swal.fire({
                icon: 'error',
                title: 'Precio inválido',
                html: `El precio no puede ser menor a <b>$${precioMinimo.toFixed(2)}</b> (costo + 1% utilidad)`,
                confirmButtonText: 'Corregir'
            });
            return;
        }

        const precioVentaConIVA = precioVentaSinIVA * 1.13;

        Swal.fire({
            title: '¿Actualizar precio?',
            html: `<b>Precio sin IVA:</b> $${precioVentaSinIVA.toFixed(2)}<br><b>Precio con IVA:</b> $${precioVentaConIVA.toFixed(2)}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= $_SESSION['currentRoute']; ?>transaction/transaction.php',
                    method: 'POST',
                    data: {
                        hiddenFormData: 'update',
                        operation: 'actualizar-precio-venta',
                        productoPrecioId: data.productoPrecioId,
                        productoId: data.productoId,
                        precioVenta: precioVentaSinIVA,
                        precioVentaIVA: precioVentaConIVA
                    },
                    success: function (res) {
                        if (res.trim() === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Precio actualizado',
                                text: 'El precio se ha actualizado correctamente.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            $('#tblPrecioVenta').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo conectar con el servidor.'
                        });
                    }
                });
            }
        });
    }

    $(document).ready(function () {

        let tblPrecioVenta = $('#tblPrecioVenta').DataTable({
            dom: 'lrtip',
            ajax: {
                method: "POST",
                url: "<?= $_SESSION['currentRoute']; ?>content/tables/tableActualizarPreciosProductos.php",
                data: function (d) {
                    d.fSku = $('#fSku').val();
                    d.fNombre = $('#fNombre').val();
                    d.fCategoria = $('#fCategoria').val();
                    d.fMarca = $('#fMarca').val();
                }
            },
            autoWidth: false,
            columns: [
                null, // #
                null, // Código
                null, // Información
                null, // Características
                null, // Costos
                null, // Precio Venta (sin IVA)
                null, // Precio con IVA
                null, // Sugerido
                null  // Acciones
            ],
            columnDefs: [
                { orderable: false, targets: [ 6, 8] }
            ],
            language: {
                url: "../libraries/packages/js/spanish_dt.json"
            }
        });

        $('#fSku, #fNombre').on('keyup change', function () {
            tblPrecioVenta.ajax.reload();
        });

        $('#fCategoria, #fMarca').on('change', function () {
            tblPrecioVenta.ajax.reload();
        });

        $('#btnResetFiltros').on('click', function () {
            $('#fSku').val('');
            $('#fNombre').val('');
            $('#fCategoria').val('');
            $('#fMarca').val('');
            tblPrecioVenta.search('');
            tblPrecioVenta.ajax.reload();
        });
    });
</script>
