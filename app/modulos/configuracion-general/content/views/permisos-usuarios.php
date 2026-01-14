<?php 
	@session_start();
?>
<h2>
    Gestión de Usuarios: Permisos de Módulo
</h2>
<hr>
<?php 
	if(in_array(10, $_SESSION["arrayPermisos"]) || in_array(50, $_SESSION["arrayPermisos"]) || in_array(51, $_SESSION["arrayPermisos"])) {
?>
		<div class="row">
			<div class="col-lg-3 offset-lg-9">
				<button type="button" id="btnAsignarPermisos" class="btn btn-primary btn-block">
					<i class="fas fa-user-lock"></i>
					Asignar Permisos
				</button>
			</div>
		</div>
<?php 
	} else {
		// Permiso
	}
?>
<div class="table-responsive">
	<table id="tblPermisos" class="table table-hover" style="width: 100%;">
	    <thead>
	    	<tr id="filterboxrow">
	    		<th>#</th>
		        <th>Permiso</th>
		        <th>Acciones</th>
	    	</tr>
	    </thead>
	    <tbody>
        </tbody>
	</table>
</div>
<script>
	function modalEditPermisos(tableData) {
		let arrayData = tableData.split("^");
	    loadModal(
	        "modal-container",
	        {
	        	modalDev: '10^52',
	            modalSize: 'md',
	            modalTitle: `Editar Permisos: ${arrayData[3]}`,
	            modalForm: 'editarPermisosUsuario',
	            formData: tableData,
	            buttonAcceptShow: true,
	            buttonAcceptText: 'Guardar',
	            buttonAcceptIcon: 'save',
	            buttonCancelShow: true,
	            buttonCancelText: 'Cancelar'
	        }
	    );
	}

	function eliminarPermiso(tableData) {
		// menuId ^ usuarioId ^ menu ^ persona ^ menuSuperior
		let arrayTblData = tableData.split("^");
		mensaje_confirmacion(
			`¿Está seguro que desea eliminar el menú ${arrayTblData[2]} asignado a ${arrayTblData[3]}?`, 
			`Se eliminarán todos los permisos asignados y esta persona no podrá visualizar este menú.`, 
			`warning`, 
			function(param) {
				asyncDoDataReturn(
					'<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
					{
						typeOperation: `delete`,
						operation: `menu-permisos-usuario`,
						menuId: arrayTblData[0],
						usuarioId: arrayTblData[1],
						menuSuperior: arrayTblData[4]
					},
					function(data) {
						let arrayData = data.split("^");
						if(arrayData[0] == "success") {
							mensaje_do_aceptar(`Operación completada:`, arrayData[1], `success`, function() {
								$(`#tblPermisos`).DataTable().ajax.reload(null, false);
							});
						} else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
						}
					}
				);
			},
			`Eliminar`,
			`Cancelar`
		);
	}

    $(document).ready(function() {
    	$("#btnAsignarPermisos").click(function() {
	        loadModal(
	            "modal-container",
	            {
	            	modalDev: '10^50^51',
	                modalSize: 'md',
	                modalTitle: `Asignar Permisos`,
	                modalForm: 'asignarPermisosUsuarios',
	                formData: '', // no se necesitan variables
	                buttonCancelShow: true,
	                buttonCancelText: 'Cancelar'
	            }
	        );
    	});

        $('#tblPermisos thead tr#filterboxrow th').each(function(index) {
            if(index==1) {
                var title = $('#tblPermisos thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblPermisos.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblPermisos = $('#tblPermisos').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePermisosUsuarios",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "x": ''
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                {"width": "20%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1,2] },
                //{ "className": "badge rounded-pill bg-primary", "targets": [3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>