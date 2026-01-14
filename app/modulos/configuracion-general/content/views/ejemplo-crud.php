<?php 
	@session_start();
?>
<h2>
    Ejemplo de CRUD
</h2>
<hr>
<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
    <?php 
        if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(54, $_SESSION["arrayPermisos"])) {
    ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1" role="tab" aria-controls="ntab-content-1" aria-selected="true">
                    Empleados
                </a>
            </li>
    <?php 
        } else {
            // Permiso
        }
    ?>
      
</ul>
<hr>
<div class="tab-content" id="ntab-content">
    <?php 
        if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(54, $_SESSION["arrayPermisos"])) {
    ?>
        	<div class="tab-pane fade show active" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">
                <?php 
                    if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(58, $_SESSION["arrayPermisos"])) {
                ?>
                		<div class="row">
                			<div class="col-lg-3 offset-lg-9">
                				<button type="button" class="btn btn-primary btn-block" onclick="modalCrud('nuevo','N/A');">
                					<i class="fas fa-plus"></i> Nuevo CRUD
                				</button>
                			</div>
                		</div>
                <?php 
                    } else {
                        // Permiso
                    }
                ?>
        		<div class="table-responsive">
        			<table id="tblEjemploCrud" class="table table-hover" style="width: 100%;">
        			    <thead>
        			    	<tr id="filterboxrow">
        			    		<th>#</th>
        				        <th>Nombre</th>
        				        <th>Módulo</th>
        				        <th>Acciones</th>
        			    	</tr>
        			    </thead>
        			    <tbody>
        		        </tbody>
        			</table>
        		</div>
        	</div>
    <?php
        } else {
            // Permiso
        }
    ?>

</div>
<script>

    function modalCrud (tipo,tableData){
        let formData = "";
        if (tipo=="nuevo") {
            formData = "nuevo";
        }else{
            formData = "editar^"+tableData;
        }
        let size     = "md";
        //modalDev = -1 => se utiliza mientras se esta desarrollando
        let modalDev = "-1";
        loadModal (
            "modal-container",
            {
                modalDev: modalDev,
                modalSize: size,
                modalTitle: ``,
                modalForm: 'crudo',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarCrud (tableData){
        mensaje_confirmacion(
            `¿Esta seguro que desea eliminar este crud?`,
            `Se eliminará del catálogo.`,
            `warning`,
            (param) => {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
                    {
                        typeOperation:`delete`,
                        operation: `crud`,
                        id: tableData
                    },
                    (data) => {
                        if (data=="success") {
                            mensaje_do_aceptar(`Operación completada`,`Crud eliminado con éxito`,`success`,() => {
                                $(`#tblEjemploCrud`).DataTable().ajax.reload(null,false);
                            });
                        }else{
                            mensaje(
                                "Aviso:",
                                data,
                                "warnig"
                            );
                        }
                    }
                );
            },
            `Eliminar`,
            `Cancelar`
        )
    }

    $(document).ready(function() {
    	// Tab: Ejemplo CRUD, inputs de busqueda
        $('#tblEjemploCrud thead tr#filterboxrow th').each(function(index) {
            if(index==1 || index==2) {
                var title = $('#tblEjemploCrud thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEjemploCrud.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblEjemploCrud = $('#tblEjemploCrud').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEjemploCrud",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoUsuario": 'Empleado'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "40%"},
                {"width": "40%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1,2,3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>