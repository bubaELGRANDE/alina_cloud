<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>

<div class="table-responsive">
    <table id="tblPeriodos" class="table table-striped table-hover mt-3" style="width: 100%;">
    <thead>
        <tr id="filterboxrow-tblUbicaciones">
            <th>#</th>
            <th>Sucursal</th>
            <th>Ventas contribuyente</th>
            <th>Ventas consumidores</th>
            <th>Ventas consumidores exentos</th>
            <th>Ventas exportaciones</th>
            <th>Notas de credito</th>
            <th>Notas de remision</th>
            <th>Comprobantes de retencion</th>
            <th>Sujetos excluidos</th>
            <th>Invalidaciones</th>
            <th>IVA</th>
        </tr>
    </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        // Tab: Cheques Emitidos
        $('#tblPeriodos thead tr#filterboxrow-tblPeriodos th').each(function(index) {
            if(index == 1 || index == 2){
                var title = $('#tblPeriodos thead tr#filterboxrow-tblPeriodos th').eq($(this).index()).text();
                    $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}tblPeriodos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}tblChequesEmitidos">Buscar</label></div>${title}`);
                    $(this).on('keyup change', function() {
                        tblPeriodos.column($(this).index()).search($(`#input${$(this).index()}tblPeriodos`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            }else{

            }
           
        });
        
        let tblPeriodos = $('#tblPeriodos').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePeriodoDeclarado",
                "data": {
                    "idDeclaracion" : <?php echo $_POST['idDeclaracion']; ?>
                }
            },
            "rowReorder": true,
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
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>