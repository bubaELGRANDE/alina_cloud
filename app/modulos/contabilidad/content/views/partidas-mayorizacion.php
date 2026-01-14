<?php
@session_start();
?>
<h2>Mayorización</h2>
<hr>
<div class="row mb-4">
    <div class="col-md-12 text-end">
        <button class="btn btn-primary" onclick="modal();">
            <i class="fas fa-plus-circle"></i> Mayorizar
        </button>
    </div>
</div>
<div class="table-responsive">
    <table id="tblMayorizacion" class="table table-hover mt-4" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Fecha Mayorización</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    function modal() {
        loadModal(
            "modal-container", {
            modalDev: "-1",
            modalSize: 'lg',
            modalTitle: `Nueva Mayorización`,
            modalForm: 'mayorizacion',
            formData: null ,
            buttonAcceptShow: true,
            buttonAcceptText: 'Guardar',
            buttonAcceptIcon: 'success',
            buttonCancelShow: true,
            buttonCancelText: 'Cancelar'
        }
        );
    }

    $(document).ready(function () {

        $('#tblMayorizacion thead tr#filterboxrow th').each(function (index) {
            if (index == 1 || index == 2) {
                var title = $('#tblMayorizacion thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function () {
                    tblCuentasContables.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else { }
        });

        let tblCuentasContables = $('#tblMayorizacion').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableMayorizacion",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "x": ""
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null,
                null
            ],
            "columnDefs": [{
                "orderable": false,
                "targets": [null]
            }],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>