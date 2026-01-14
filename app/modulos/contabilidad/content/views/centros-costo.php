<?php
@session_start();
?>
<h2>
    Centros de costos
</h2>
<hr>
<div class="row">
    <div class="col text-end">
        <button type="button" class="btn btn-primary ttip" onclick="modalNuevoCentroCosto({'typeOperation': 'insert', 'centroCostoId': 0, 'tblCentroCosto': 'tblCentroCosto'});">
            <i class="fas fa-plus-circle"></i>
            Nuevo Centro de costo
            <span class="ttiptext">Agregar nuevo centro de costo</span>
        </button>
    </div>
</div>
<div class="table-responsive">
    <table id="tblCentroCosto" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-centro-costo">
                <th>#</th>
                <th>Centro de costo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>


    function modalNuevoCentroCosto(frmData) {
        loadModal(
            "modal-container", {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Sub centros de costos`,
                modalForm: 'centrosCostos',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function modalNuevoCentroCostoDetalle(frmData) {
        loadModal(
            "modal-container", {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `${frmData.tituloModal}`,
                modalForm: 'centroCostoDetalle',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    $(document).ready(function() {
        //Activos
        $('#tblCentroCosto thead tr#filterboxrow th').each(function(index) {
            if (index == 1 || index == 2) {
                var title = $('#tblCentroCosto thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblCentroCosto.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {}
        });

        let tblCentroCosto = $('#tblCentroCosto').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableCentrosCostos",
                "data": {
                    "x": ''
                }
            },
            "autoWidth": false,
            "columns": [{
                    "width": "10%"
                },
                null,
                {
                    "width": "30%"
                }
            ],
            "columnDefs": [{
                "orderable": false,
                "targets": [1, 2]
            }],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

    });
</script>