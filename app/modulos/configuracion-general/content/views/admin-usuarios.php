<?php 
	@session_start();
?>
<h2>
    Administración de Usuarios
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

        if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(55, $_SESSION["arrayPermisos"])) {
    ?>
        	<li class="nav-item" role="presentation">
        		<a class="nav-link" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2" role="tab" aria-controls="ntab-content-2" aria-selected="false">
        			Externos
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
                				<button type="button" class="btn btn-primary btn-block" onclick="modalNuevoUsuario('Empleado');">
                					<i class="fas fa-plus"></i> Nuevo Usuario
                				</button>
                			</div>
                		</div>
                <?php 
                    } else {
                        // Permiso
                    }
                ?>
        		<div class="table-responsive">
        			<table id="tblUsuariosEmpleados" class="table table-hover" style="width: 100%;">
        			    <thead>
        			    	<tr id="filterboxrow">
        			    		<th>#</th>
        				        <th>Usuario</th>
        				        <th>Estado</th>
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

        if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(55, $_SESSION["arrayPermisos"])) {
    ?>
        	<div class="tab-pane fade" id="ntab-content-2" role="tabpanel" aria-labelledby="ntab-2">
                <?php 
                    if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(61, $_SESSION["arrayPermisos"])) {
                ?>
                		<div class="row">
                			<div class="col-lg-3 offset-lg-9">
                				<button type="button" class="btn btn-primary btn-block" onclick="modalNuevoUsuario('Distribuidor');">
                					<i class="fas fa-plus"></i> Nuevo Usuario
                				</button>
                			</div>
                		</div>
                <?php 
                    } else {
                        // Permiso
                    }
                ?>
        		<div class="table-responsive">
        			<table id="tblUsuariosExternos" class="table table-hover" style="width: 100%;">
        			    <thead>
        			    	<tr id="filterboxrow-externo">
        			    		<th>#</th>
        				        <th>Usuario</th>
        				        <th>Estado</th>
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
	function modalNuevoUsuario(tipoUsuario) {
        let size = (tipoUsuario == "Empleado") ? "md" : "lg";
        let modalDev = (tipoUsuario == "Empleado") ? "9^58" : "9^61";
        loadModal(
            "modal-container",
            {
                modalDev: modalDev,
                modalSize: size,
                modalTitle: `Nuevo Usuario - Tipo: ${tipoUsuario}`,
                modalForm: 'nuevoUsuario',
                formData: tipoUsuario,
                buttonAcceptShow: true,
                buttonAcceptText: 'Crear Usuario',
                buttonAcceptIcon: 'user-plus',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
	}

    function modalEditarUsuario(tableData) {
        // id, nombreUsuario, tipoPersona
        let arrayData = tableData.split("^");
        let size = (arrayData[2] == "Empleado") ? 'md' : 'lg';
        let modalDev = (arrayData[2] == "Empleado") ? "9^59" : "9^62";
        loadModal(
            "modal-container",
            {
                modalDev: modalDev,
                modalSize: size,
                modalTitle: `Editar usuario: ${arrayData[1]}`,
                modalForm: 'editarUsuario',
                formData: tableData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );        
    }

    function modalSuspenderAcceso(tableData) {
        // id, usuario, tipoPersona
        let arrayData = tableData.split("^");
        let modalDev = (arrayData[2] == "Empleado") ? "9^60" : "9^63";
        loadModal(
            "modal-container",
            {
                modalDev: modalDev,
                modalSize: 'md',
                modalTitle: `Suspender acceso - Usuario: ${arrayData[1]}`,
                modalForm: 'suspenderAccesoUsuario',
                formData: tableData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Suspender acceso',
                buttonAcceptIcon: 'user-lock',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function procesarEstado(id, nombreUsuario, tipoEstado, tipoPersona, justificacionEstado = "N/A") {
        let title, msj, msjDone, btnAccepTxt;

        if(tipoEstado == "activar") {
            title = `¿Está seguro que desea volver a activar estas credenciales?`;
            msj = `Se enviará un correo a <b>${nombreUsuario}</b> con sus credenciales adjuntas.`;
            msjDone = `Las credenciales de <b>${nombreUsuario}</b> han sido activadas y enviadas por correo electrónico.`;
            btnAccepTxt = 'Activar';
        } else { // Suspender
            title = `¿Está seguro que desea suspender estas credenciales?`;
            msj = `${nombreUsuario} no podrá volver a iniciar sesión.`;
            msjDone = `Se han suspendido las credenciales de <b>${nombreUsuario}</b>.`;
            btnAccepTxt = 'Suspender';
        }

        mensaje_confirmacion(
            title, msj, `warning`, function(param) {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    {
                        typeOperation: `update`,
                        operation: `estado-credenciales`,
                        tipoEstado: tipoEstado,
                        tipoPersona: tipoPersona,
                        id: id,
                        nombreUsuario: nombreUsuario,
                        justificacionEstado: justificacionEstado
                    },
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, msjDone, `success`, function() {
                                if(tipoPersona == "Empleado") {
                                    $(`#tblUsuariosEmpleados`).DataTable().ajax.reload(null, false);
                                } else {
                                    $(`#tblUsuariosExternos`).DataTable().ajax.reload(null, false);
                                }
                                if(tipoEstado == "suspender") {
                                    $("#modal-container").modal("hide");
                                } else {
                                }
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
            btnAccepTxt,
            `Cancelar`
        );
    }

    $(document).ready(function() {
    	// Tab: Empleado
        $('#tblUsuariosEmpleados thead tr#filterboxrow th').each(function(index) {
            if(index==1 || index==2) {
                var title = $('#tblUsuariosEmpleados thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblUsuariosEmpleados.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblUsuariosEmpleados = $('#tblUsuariosEmpleados').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableUsuarios",
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

	    // Tab: Externo
        $('#tblUsuariosExternos thead tr#filterboxrow-externo th').each(function(index) {
            if(index==1 || index==2) {
                var title = $('#tblUsuariosExternos thead tr#filterboxrow-externo th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}ext" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}ext">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblUsuariosExternos.column($(this).index()).search($(`#input${$(this).index()}ext`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblUsuariosExternos = $('#tblUsuariosExternos').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableUsuarios",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoUsuario": 'Externo'
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