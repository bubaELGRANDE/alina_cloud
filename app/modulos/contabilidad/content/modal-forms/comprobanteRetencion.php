<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
?>

<div class="table-responsive">
    <table id="tableComprobantes" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-comprobante">
                <th>#</th>
                <th>Número de DTE</th>
                <th>Proveedor</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        $('#tableComprobantes thead tr#filterboxrow-comprobante th').each(function(index) {
			if(index == 1 || index == 2 || index == 3) {
				var title = $('#tableComprobantes thead tr#filterboxrow-comprobante th').eq($(this).index()).text();
				$(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-comprobante" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-comprobante">Buscar</label></div>${title}`);
				$(this).on('keyup change', function() {
					tableComprobantes.column($(this).index()).search($(`#input${$(this).index()}-comprobante`).val()).draw();
				});
				document.querySelectorAll('.form-outline').forEach((formOutline) => {
					new mdb.Input(formOutline).init();
				});
			} else {
			}
		});

		let tableComprobantes = $('#tableComprobantes').DataTable({
			"dom": 'lrtip',
			"ajax": {
				"method": "POST",
				"url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableComprobantesPendientes",
			},
			"autoWidth": false,
			"columns": [
				null,
				null,
				null,
                null,
				null
			],
			"columnDefs": [
				{ "orderable": false, "targets": [1, 2, 3] }
			],
			"language": {
				"url": "../libraries/packages/js/spanish_dt.json"
			}
		});
    });
</script>