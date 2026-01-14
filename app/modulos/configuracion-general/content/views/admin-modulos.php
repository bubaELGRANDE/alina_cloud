<?php 
	@session_start();
?>
<h2>
    Administración de Módulos
</h2>
<hr>
<?php 
	if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(32, $_SESSION["arrayPermisos"])) {
?>
		<div class="row">
			<div class="col-lg-3 offset-lg-9">
				<button type="button" id="btnNuevoModulo" class="btn btn-primary btn-block">
					<i class="fas fa-plus-circle"></i>
					Nuevo Módulo
				</button>
			</div>
		</div>
<?php 
	} else {
		// Permiso
	}
?>
<div class="table-responsive">
	<table id="tblModulos" class="table table-hover" style="width: 100%;">
	    <thead>
	    	<tr id="filterboxrow">
	    		<th>#</th>
		        <th>Módulo</th>
		        <th>Iconos Sidebar</th>
		        <th>Acciones</th>
	    	</tr>
	    </thead>
	    <tbody>
        </tbody>
	</table>
</div>
<script>
	function modalEditModulo(tableData) {
		let arrayData = tableData.split("^");
	    loadModal(
	        "modal-container",
	        {
                modalDev: '12^33',
	            modalSize: 'lg',
	            modalTitle: `Editar Módulo: ${arrayData[1]}`,
	            modalForm: 'editarModulo',
	            formData: tableData,
	            buttonAcceptShow: true,
	            buttonAcceptText: 'Guardar',
	            buttonAcceptIcon: 'save',
	            buttonCancelShow: true,
	            buttonCancelText: 'Cancelar'
	        }
	    );
	}

    $(document).ready(function() {
    	$("#btnNuevoModulo").click(function() {
	        loadModal(
	            "modal-container",
	            {
	            	modalDev: '12^32',
	                modalSize: 'lg',
	                modalTitle: `Nuevo Módulo`,
	                modalForm: 'nuevoModulo',
	                formData: '', // no se necesitan variables
	                buttonAcceptShow: true,
	                buttonAcceptText: 'Guardar',
	                buttonAcceptIcon: 'save',
	                buttonCancelShow: true,
	                buttonCancelText: 'Cancelar'
	            }
	        );
    	});

        $('#tblModulos thead tr#filterboxrow th').each(function(index) {
            if(index==1) {
                var title = $('#tblModulos thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblModulos.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblModulos = $('#tblModulos').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableModulos",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "x": ''
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null,
                {"width": "20%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1,2,3] },
                //{ "className": "badge rounded-pill bg-primary", "targets": [3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>