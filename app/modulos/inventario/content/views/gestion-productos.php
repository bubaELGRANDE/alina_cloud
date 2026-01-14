<?php
@session_start();
?>
<style>
    /* Tarjetas */
    .inv-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        transition: transform .2s;
    }

    .inv-card:hover {
        transform: translateY(-3px);
    }

    .inv-img {
        width: 100%;
        height: 170px;
        object-fit: cover;
    }

    .inv-body {
        padding: 1rem;
    }

    /* Imágenes pequeñas de la tabla */
    .list-img {
        width: 55px;
        height: 55px;
        object-fit: cover;
        border-radius: 6px;
    }
</style>


<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Gestión de productos</h3>
        <!-- Botones de cambio de vista -->
        <button id="btn" type="button" class="ml-2 btn btn-primary"
            onclick='changePage(`<?php echo $_SESSION["currentRoute"]; ?>`, `gestion-productos`, `productoId=0`)'>
            <i class="fas fa-plus-circle"></i> Nuevo Producto</button>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <!-- Botones vista tarjetas/lista -->
        <div class="btn-group mb-2 mb-sm-0" role="group">
            <button class="btn btn-outline-primary active" id="btnCards">
                <i class="fa fa-th-large me-1"></i> Tarjetas
            </button>
            <button class="btn btn-outline-primary" id="btnList">
                <i class="fa fa-list me-1"></i> Lista
            </button>
        </div>

        <!-- Buscador tarjetas -->
        <div class="mt-2 mt-sm-0" style="max-width: 280px;">
            <input type="text" id="searchCards" class="form-control" placeholder="Buscar en tarjetas...">
        </div>
    </div>


    <!-- ====================== VISTA TARJETAS ======================= -->
    <div id="viewCards" class="row g-4 pt-4">
    </div>

    <!-- ====================== VISTA LISTA ======================= -->
    <div id="viewList" class="d-none pt-4">
        <div class="table-responsive">
            <table id="tblProducto" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow">
                        <th>#</th>
                        <th>SKU</th>
                        <th>Nombre</th>
                        <th>Categoria</th>
                        <th>Marca</th>
                        <th>Espeficicación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    // Función para cargar tarjetas (la dejo global igual que la tenías)
    function cargarTarjetas() {
        asyncData(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/divProductoTarjetas",
            null,
            function (data) {
                $("#viewCards").html(data);
            }
        );
    }

    // Función global para ver adjunto (igual que la tenías)
    function verAbjunto(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'fullscreen',
                modalTitle: 'Abjuntos',
                modalForm: 'abjuntosProductos',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    $(document).ready(function () {

        // ================== CAMBIO DE VISTA TARJETAS / LISTA ==================
        $('#btnCards').on('click', function () {
            $('#viewCards').removeClass('d-none');
            $('#viewList').addClass('d-none');

            $('#btnCards').addClass('active');
            $('#btnList').removeClass('active');
        });

        $('#btnList').on('click', function () {
            $('#viewList').removeClass('d-none');
            $('#viewCards').addClass('d-none');

            $('#btnList').addClass('active');
            $('#btnCards').removeClass('active');
        });

        // Cargar tarjetas al inicio
        cargarTarjetas();

        // ================== INPUTS DE BÚSQUEDA POR COLUMNA (DataTables) ==================
        $('#tblProducto thead th').each(function (i) {
            let title = $(this).text().trim();

            if (title !== '#' && title !== 'Acciones') {
                $(this).html(
                    title +
                    '<br><input type="text" class="form-control form-control-sm column-search" ' +
                    'data-index="' + i + '" placeholder="Buscar ' + title + '" />'
                );
            }
        });

        let tblProducto = $('#tblProducto').DataTable({
            dom: 'lrtip', // sin buscador general, solo por columnas
            ajax: {
                method: 'POST',
                url: "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablaProductos",
            },
            autoWidth: false,
            columns: [
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ],
            order: [[2, 'asc']],
            orderCellsTop: true,
            language: {
                url: '../libraries/packages/js/spanish_dt.json'
            }
        });

        // Evento para filtrar por columna
        $('#tblProducto thead').on('keyup change', 'input.column-search', function () {
            let index = $(this).data('index');
            tblProducto
                .column(index)
                .search(this.value)
                .draw();
        });

        // ================== BUSCADOR PARA LAS TARJETAS ==================
        $('#searchCards').on('keyup', function () {
            let term = $(this).val().toLowerCase();

            $('#viewCards .inv-card').each(function () {
                let texto = $(this).text().toLowerCase();
                let col = $(this).closest('.col-md-4, .col-lg-3');

                if (texto.indexOf(term) > -1) {
                    col.show();
                } else {
                    col.hide();
                }
            });
        });
    });
</script>