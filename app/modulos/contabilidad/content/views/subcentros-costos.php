<?php
@session_start();
?>
<h2>
    Sub centros de costos
</h2>
<hr>
<div class="row">
    <div class="col text-end">
        <button type="button" class="btn btn-primary ttip" onclick="modalNuevoSubCentroCosto({'typeOperation': 'insert', 'subCentroCostoId': 0, 'tblSubCentroCosto': 'tblSubCentroCosto'});">
            <i class="fas fa-plus-circle"></i>
            Nuevo Sub Centro de costo
            <span class="ttiptext">Agregar nuevo sub-centro de costo</span>
        </button>
    </div>
</div>
<div class="table-responsive">
    <table id="tblsubCentroDetalle" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-detalle">
                <th>#</th>
                <th>Sub centros de costos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>

        function modalNuevoSubCentroCosto(frmData) {
        loadModal(
            "modal-container", {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Sub centros de costos`,
                modalForm: 'SubCentrosCostos',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    $(document).ready(function() {

        $('#tblsubCentroDetalle thead tr#filterboxrow-detalle th').each(function(index) {
            if(index==1) {
                var title = $('#tblsubCentroDetalle thead tr#filterboxrow-detalle th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-detalle" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-detalle">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblsubCentroDetalle.column($(this).index()).search($(`#input${$(this).index()}-detalle`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            } else {
            }
        });

        let tblsubCentroDetalle = $('#tblsubCentroDetalle').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableMostrarSubcentroCostos",
                "data": {
                    "x": ''
                    
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1] },
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>


