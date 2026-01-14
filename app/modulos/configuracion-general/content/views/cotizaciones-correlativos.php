<?php 
    @session_start();
?>
<h2>
    Cotizaciones: Correlativos
</h2>
<hr>
<div class="row">
    <div class="col text-end">
        <button id="btn" type="button" class="btn btn-primary" onclick="modalNuevaCotizacionCorrelativo()"><i class="fas fa-plus-circle"></i> 
            Nuevo Correlativo
        </button>
    </div>
</div>
<div class="table-responsive">
    <table id="tblCotizacionCorrelativo" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Tipo</th>
                <th>Origen</th>
                <th>año</th>
                <th>Correlativo actual</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
function modalNuevaCotizacionCorrelativo(frmData) {
    loadModal(
        "modal-container",
        {
            modalDev: "-1",
            modalSize: 'lg',
            modalTitle: "Nuevo correlativo",
            modalForm: 'NuevaCotizacionCorrelativo',
            formData: frmData,
            buttonAcceptShow: true,
            buttonAcceptText: 'Guardar',
            buttonAcceptIcon: 'save',
            buttonCancelShow: true,
            buttonCancelText: 'Cancelar'
        }
    );
}
function desactivarCotizacionCorrrelativo (frmData){
    mensaje_confirmacion(
        `¿Está seguro que desea desactivar la cotización?`,
        `Se desactivara la cotización.`,
        `warning`,
        (param) => {
            asyncData(
                '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
                frmData,
                (data) => {
                    if (data=="success") {
                        mensaje_do_aceptar(
                            `Operación completada`,
                            `Cotización desactivada con éxito`,
                            `success`,
                            () => {
                            $(`#tblCotizacionCorrelativo`).DataTable().ajax.reload(null,false);
                        });
                    }else{
                        mensaje(
                            "Aviso:",
                            data,
                            "warning"
                        );
                    }
                }
            );
        },
        `Desactivar`,
        `Cancelar`
    )
}
$(document).ready(function() {
    
    $('#tblCotizacionCorrelativo thead tr#filterboxrow th').each(function(index) {
        if(index==1) {
            var title = $('#tblCotizacionCorrelativo thead tr#filterboxrow th').eq($(this).index()).text();
            $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
            $(this).on('keyup change', function() {
                tblCotizacionCorrelativo.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
            });
            document.querySelectorAll('.form-outline').forEach((formOutline) => {
                new mdb.Input(formOutline).init();
            });
        } else {
        }
    });
    
    let tblCotizacionCorrelativo = $('#tblCotizacionCorrelativo').DataTable({
        "dom": 'lrtip',
        "ajax": {
            "method": "POST",
            "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableCotizacionCorrelativo",
            "data": {

            }
        },
        "autoWidth": false,
        "columns": [
            null,
            null,
            null,
            null,
            null,
            null
        ],
        "columnDefs": [
            { "orderable": false, "targets": [1, 2, 3, 4, 5] }
        ],
        "language": {
            "url": "../libraries/packages/js/spanish_dt.json"
        }
    });
});
</script>
