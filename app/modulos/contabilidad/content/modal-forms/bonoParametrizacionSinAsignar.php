<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
?>
<div class="table-responsive">
    <table id="tableEmpleadosSinAsignar" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-sin-asignar">
                <th>#</th>
                <th>Empleado</th>
                <th>Cargo actual</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        $('#tableEmpleadosSinAsignar thead tr#filterboxrow-sin-asignar th').each(function(index) {
			if(index == 1 || index == 2) {
				var title = $('#tableEmpleadosSinAsignar thead tr#filterboxrow-sin-asignar th').eq($(this).index()).text();
				$(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-sin-asignar" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-sin-asignar">Buscar</label></div>${title}`);
				$(this).on('keyup change', function() {
					tableEmpleadosSinAsignar.column($(this).index()).search($(`#input${$(this).index()}-sin-asignar`).val()).draw();
				});
				document.querySelectorAll('.form-outline').forEach((formOutline) => {
					new mdb.Input(formOutline).init();
				});
			} else {
			}
		});

		let tableEmpleadosSinAsignar = $('#tableEmpleadosSinAsignar').DataTable({
			"dom": 'lrtip',
			"ajax": {
				"method": "POST",
				"url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableBonosParametrizacionSinAsignar",
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
				{ "orderable": false, "targets": [1, 2] }
			],
			"language": {
				"url": "../libraries/packages/js/spanish_dt.json"
			}
		});
    });
</script>