<?php 
	@session_start();
    // tipoHistorial ^ tipoSolicitud
	$arrayFormData = explode("^", $_POST["arrayFormData"]);
?>
<div class="table-responsive">
	<table id="tblHistorialSolicitud" class="table table-hover" style="width: 100%;">
	    <thead>
	    	<tr id="filterboxrow-historial">
	    		<th>#</th>
		        <th>Detalle Solicitud</th>
		        <th>Fecha y Hora</th>
	    	</tr>
	    </thead>
	    <tbody>
        </tbody>
	</table>
</div>
<script>
    $(document).ready(function() {
        $('#tblHistorialSolicitud thead tr#filterboxrow-historial th').each(function(index) {
            if(index==1 || index==2) {
                var title = $('#tblHistorialSolicitud thead tr#filterboxrow-historial th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}historial" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}historial">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblHistorialSolicitud.column($(this).index()).search($(`#input${$(this).index()}historial`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblHistorialSolicitud = $('#tblHistorialSolicitud').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableHistorialSolicitudesAcceso",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoHistorial": '<?php echo $arrayFormData[0]; ?>',
                    "tipoSolicitud": '<?php echo $arrayFormData[1]; ?>'
                }
            },
            "columns": [
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1,2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>