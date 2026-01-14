<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
?>

<div class="table-responsive">
    <table id="tableCertificarComprobantes" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-comprobante">
                <th>#</th>
                <th>Número de DTE</th>
                <th>Proveedor</th>
                <th>Monto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        $('#tableCertificarComprobantes thead tr#filterboxrow-comprobante th').each(function(index) {
			if(index == 1 || index == 2 || index == 3) {
				var title = $('#tableCertificarComprobantes thead tr#filterboxrow-comprobante th').eq($(this).index()).text();
				$(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-comprobante" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-comprobante">Buscar</label></div>${title}`);
				$(this).on('keyup change', function() {
					tableCertificarComprobantes.column($(this).index()).search($(`#input${$(this).index()}-comprobante`).val()).draw();
				});
				document.querySelectorAll('.form-outline').forEach((formOutline) => {
					new mdb.Input(formOutline).init();
				});
			} else {
			}
		});

		let tableCertificarComprobantes = $('#tableCertificarComprobantes').DataTable({
			"dom": 'lrtip',
			"ajax": {
				"method": "POST",
				"url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableCertificarComprobantesPendientes",
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