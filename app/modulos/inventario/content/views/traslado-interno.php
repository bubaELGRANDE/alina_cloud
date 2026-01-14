<?php
require_once("../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$sucursales = $cloud->rows("SELECT sucursalId, sucursal FROM cat_sucursales WHERE flgDelete = 0");
?>

<h4 class="mb-3">Traslado interno</h4>
<hr>

<div class="mb-4">
    <label for="sucursalId" class="form-label fw-bold">Seleccione una sucursal</label>
    <select id="sucursalId" name="sucursalId" class="form-select">
        <option value="">Seleccione una sucursal</option>
        <?php foreach ($sucursales as $suc) { ?>
            <option value="<?= $suc->sucursalId ?>"><?= $suc->sucursal ?></option>
        <?php } ?>
    </select>
</div>

<div class="text-end mb-4">
    <button id="btnNuevaUbicacion" class="btn btn-primary" disabled onclick="nuevoTraslado();">
        <i class="fas fa-plus-circle"></i> Nuevo Traslado
    </button>
</div>

<!-- Placeholder -->
<div id="placeholder" class="alert alert-secondary text-center py-4">
    Seleccione una sucursal para ver o agregar ubicaciones.
</div>

<div class="table-responsive" id="contenedorTabla" style="display:none;">
    <table id="tblUbicaciones" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>MOVI</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>


<script>
    function nuevoTraslado(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: "Nuevo traslado",
                modalForm: 'nuevoTraslado',
                formData: {
                    sucursalId: $("#sucursalId").val()
                },
                buttonAcceptShow: true,
                buttonAcceptText: 'Trasladar Producto',
                buttonAcceptIcon: 'fas fa-sync-alt',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    let tblUbicaciones;

    $(document).ready(function () {

        $("#sucursalId").select2({
            width: "100%",
            placeholder: "Seleccione una sucursal"
        });

        $("#sucursalId").on("change", function () {
            const sucursal = $(this).val();

            if (!sucursal) {
                $("#btnNuevaUbicacion").prop("disabled", true);
                $("#placeholder").show();
                $("#contenedorTabla").hide();
                return;
            }

            $("#btnNuevaUbicacion").prop("disabled", false);
            cargarUbicaciones(sucursal);
        });

        // Inicializar DataTable vacío
        tblUbicaciones = $('#tblUbicaciones').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableUbicacion",
                "data": function (d) {
                    d.sucursalId = $("#sucursalId").val();
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                { "width": "15%" }
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        function cargarUbicaciones(sucursalId) {
            $("#placeholder").hide();
            $("#contenedorTabla").show();

            // Recargar DataTable con la sucursal seleccionada
            tblUbicaciones.ajax.reload();
        }
    });
</script>