<?php 
	@session_start();

	if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(34, $_SESSION["arrayPermisos"])) {
?>
		<h2>
		    Administración de Menús - Módulo: <?php echo $_POST["modulo"]; ?>
		</h2>
		<hr>
		<div class="row mb-4">
			<div class="col-lg-3">
				<button type="button" id="btnPageModulos" class="btn btn-secondary btn-block">
					<i class="fas fa-chevron-circle-left"></i>
					Módulos
				</button>
			</div>
			<?php 
				if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(35, $_SESSION["arrayPermisos"])) {
			?>
					<div class="col-lg-3 offset-lg-6">
						<button type="button" id="btnNuevoMenu" class="btn btn-primary btn-block">
							<i class="fas fa-plus-circle"></i>
							Nuevo Menú
						</button>
					</div>
			<?php 
				} else {
					// Permiso
				}
			?>
		</div>
		<div class="table-responsive">
			<table id="tblMenus" class="table table-hover" style="width: 100%;">
			    <thead>
			    	<tr id="filterboxrow">
			    		<th># (Orden)</th>
				        <th>Menú</th>
				        <th>Iconos Sidebar</th>
				        <th>Acciones</th>
			    	</tr>
			    </thead>
			    <tbody>
		        </tbody>
			</table>
		</div>
		<script>
			function modalEditMenu(tableData) {
				let arrayTblData = tableData.split("^");
			    loadModal(
			        "modal-container",
			        {
			        	modalDev: '12^36',
			            modalSize: 'lg',
			            modalTitle: `Editar Menú: ${arrayTblData[2]}`,
			            modalForm: 'editarMenu',
			            formData: tableData,
			            buttonAcceptShow: true,
			            buttonAcceptText: 'Guardar',
			            buttonAcceptIcon: 'save',
			            buttonCancelShow: true,
			            buttonCancelText: 'Cancelar'
			        }
			    );
			}

			function modalMenuPermisos(tableData) {
				let arrayTblData = tableData.split("^");
			    loadModal(
			        "modal-container",
			        {
			        	modalDev: '12^38',
			            modalSize: 'xl',
			            modalTitle: `Permisos - Menú: ${arrayTblData[1]}`,
			            modalForm: 'menuPermisos',
			            formData: tableData,
			            buttonCancelShow: true,
			            buttonCancelText: 'Cerrar'
			        }
			    );		
			}

			function eliminarMenu(tableData) {
				let arrayTblData = tableData.split("^");
				mensaje_confirmacion(
					`¿Está seguro que desea eliminar el menú ${arrayTblData[1]}?`, 
					`Se eliminarán todos los permisos incluyendo los permisos de los usuarios.`, 
					`warning`, 
					function(param) {
						asyncDoDataReturn(
							'<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
							{
								typeOperation: `delete`,
								operation: `eliminar-menu`,
								id: arrayTblData[0]
							},
							function(data) {
								let arrayData = data.split("^");
								if(arrayData[0] == "success") {
									mensaje_do_aceptar(`Operación completada:`, arrayData[1], `success`, function() {
										$(`#tblMenus`).DataTable().ajax.reload(null, false);
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
		    	$("#btnPageModulos").click(function() {
		    		// id 12 de tbl menus, no se puede usar changePage porque es exclusiva para "page-"
		    		asyncPage(12, 'submenu');
		    	});

		    	$("#btnNuevoMenu").click(function() {
			        loadModal(
			            "modal-container",
			            {
			            	modalDev: '12^35',
			                modalSize: 'lg',
			                modalTitle: `Nuevo Menú`,
			                modalForm: 'nuevoMenu',
			                formData: '<?php echo $_POST["moduloId"]; ?>',
			                buttonAcceptShow: true,
			                buttonAcceptText: 'Guardar',
			                buttonAcceptIcon: 'save',
			                buttonCancelShow: true,
			                buttonCancelText: 'Cancelar'
			            }
			        );
		    	});

		        $('#tblMenus thead tr#filterboxrow th').each(function(index) {
		            if(index==1) {
		                var title = $('#tblMenus thead tr#filterboxrow th').eq($(this).index()).text();
		                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
		                $(this).on('keyup change', function() {
		                    tblMenus.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
		                });
			            document.querySelectorAll('.form-outline').forEach((formOutline) => {
			                new mdb.Input(formOutline).init();
			            });
		            } else {
		            }
		        });

		        let tblMenus = $('#tblMenus').DataTable({
		            "dom": 'lrtip',
		            "bSort": false, // para respetar el order by de la consulta
		            "ajax": {
		            	"method": "POST",
		                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableMenus",
		                "data": { // En caso que se quiera enviar variable a la consulta
		                    "id": '<?php echo $_POST["moduloId"]; ?>'
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
		            ],
		            "language": {
		                "url": "../libraries/packages/js/spanish_dt.json"
		            }
		        });
		    });
		</script>
<?php 
	} else {
		// No tiene permisos
		// Bitacora
		$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y crear un nuevo módulo ".$_POST["modulo"]." (manejo desde consola), ");
		// Mostrar aviso
		include("../../../escritorio/content/views/403.php");
	}
?>
