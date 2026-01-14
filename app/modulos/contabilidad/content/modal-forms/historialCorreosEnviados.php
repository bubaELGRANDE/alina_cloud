<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

	$yearBD = $_POST['yearBD'];
?>
<b><i class="fas fa-user-tie"></i> Cliente: </b> <?php echo $_POST['nombreCliente']; ?><br>
<b><i class="fas fa-file-invoice-dollar"></i> DTE: </b> <?php echo $_POST['tipoDTE'] . " - " . $_POST['facturaId']; ?>
<br><br>
<div class="table-responsive">
    <table id="tableCorreosEnviados" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-correos">
                <th>#</th>
                <th>Tipo de envío</th>
                <th>Fecha y hora de envío</th>
                <th>Correo receptor</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        $('#tableCorreosEnviados thead tr#filterboxrow-correos th').each(function(index) {
			if(index == 1 || index == 2 || index == 3) {
				var title = $('#tableCorreosEnviados thead tr#filterboxrow-correos th').eq($(this).index()).text();
				$(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-correos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-correos">Buscar</label></div>${title}`);
				$(this).on('keyup change', function() {
					tableCorreosEnviados.column($(this).index()).search($(`#input${$(this).index()}-correos`).val()).draw();
				});
				document.querySelectorAll('.form-outline').forEach((formOutline) => {
					new mdb.Input(formOutline).init();
				});
			} else {
			}
		});

		let tableCorreosEnviados = $('#tableCorreosEnviados').DataTable({
			"dom": 'lrtip',
			"ajax": {
				"method": "POST",
				"url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableCorreosEnviados",
				"data": { 
					"facturaId": <?php echo $_POST['facturaId']; ?>,
					"yearBD": '<?php echo $yearBD; ?>'
				}
			},
			"autoWidth": false,
			"columns": [
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