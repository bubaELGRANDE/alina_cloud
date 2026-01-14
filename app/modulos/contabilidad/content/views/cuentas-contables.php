<?php
@session_start();
?>
<h2>Cátalogo de Cuentas Contables</h2>
<hr>
<div class="row mb-4">
    <div class="col-md-12 text-end">
        <button class="btn btn-primary" onclick="modalNuevaCuenta();">
            <i class="fas fa-plus-circle"></i> Nueva cuenta
        </button>
    </div>
</div>
<div class="table-responsive">
    <table id="tblCuentasContables" class="table table-hover mt-4" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Número de cuenta</th>
                <th>Descripción</th>
                <th>Cuenta Mayor</th>
                <th>Tipo cuenta</th>
                <th>Aplica movimiento</th>
                <th>Centro de costo</th>
                <th>Mayoreo</th>
                <th>Categoría</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    function modalNuevaCuenta(frmData) {
        loadModal(
            "modal-container", {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Cuentas contables`,
                modalForm: 'nuevaCuentaContable',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'success',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    $(document).ready(function() {

        $('#tblCuentasContables thead tr#filterboxrow th').each(function(index) {
            if (index == 1 || index == 2) {
                var title = $('#tblCuentasContables thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblCuentasContables.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {}
        });

        let tblCuentasContables = $('#tblCuentasContables').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableCuentasContables",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "x": ""
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ],
            "columnDefs": [{
                "orderable": false,
                "targets": [3, 4, 5, 6, 7]
            }],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>