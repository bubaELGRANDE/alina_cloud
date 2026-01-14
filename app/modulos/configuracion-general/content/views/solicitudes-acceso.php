<?php 
	@session_start();
?>
<h2>
    Solicitudes de Acceso
</h2>
<hr>
<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
	<?php 
		if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(42, $_SESSION["arrayPermisos"])) {
	?>
			<li class="nav-item" role="presentation">
				<a class="nav-link active" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1" role="tab" aria-controls="ntab-content-1" aria-selected="true" onclick="getButtonsHistorial('Empleado');">
					Solicitudes Empleados
				</a>
			</li>
	<?php 
		} else {
			// Permiso
		}
		if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(43, $_SESSION["arrayPermisos"])) {
	?>
			<li class="nav-item" role="presentation">
				<a class="nav-link" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2" role="tab" aria-controls="ntab-content-2" aria-selected="false" onclick="getButtonsHistorial('Externa');">
					Solicitudes Externas
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
		if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(42, $_SESSION["arrayPermisos"])) {
	?>
			<div class="tab-pane fade show active" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">
				<div id="divButtonsHistorial">
				</div>
				<div class="table-responsive">
					<table id="tblSolicitudes" class="table table-hover" style="width: 100%;">
					    <thead>
					    	<tr id="filterboxrow">
					    		<th>#</th>
						        <th>Solicitud</th>
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
		if(in_array(14, $_SESSION["arrayPermisos"]) || in_array(43, $_SESSION["arrayPermisos"])) {
	?>	
			<div class="tab-pane fade" id="ntab-content-2" role="tabpanel" aria-labelledby="ntab-2">
				<div class="row" id="divButtonsHistorialExterna">
				</div>
				<div class="table-responsive">
					<table id="tblSolicitudesExterna" class="table table-hover" style="width: 100%;">
					    <thead>
					    	<tr id="filterboxrow-externa">
					    		<th>#</th>
						        <th>Solicitud</th>
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
	function modalSolicitudRechazo(formData) {
		let arrayData = formData.split("^");
		let modalDev = (arrayData[6] == 'Empleado') ? '14^47' : '14^49';
        loadModal(
            "modal-container",
            {
            	modalDev: modalDev,
                modalSize: 'md',
                modalTitle: `Rechazar solicitud de acceso: ${arrayData[1]}`,
                modalForm: 'rechazarSolicitudAcceso',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Rechazar solicitud',
                buttonAcceptIcon: 'user-times',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
	}

	function modalSolicitudApExterna(formData) {
		let arrayData = formData.split("^");
        loadModal(
            "modal-container",
            {
            	modalDev: '14^48',
                modalSize: 'lg',
                modalTitle: `Autorizar solicitud de acceso: ${arrayData[1]}`,
                modalForm: 'autorizarSolicitudAccesoExterna',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Autorizar solicitud',
                buttonAcceptIcon: 'user-check',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
	}

	function procesarSolicitud(tipoSolicitud, nombrePersona, id, pId, sideName, correoPersona, justificacionEstado = 'N/A') {
		let title, msj, msjDone, btnAccepTxt;

		if(tipoSolicitud == "autorizar") {
			title = "¿Está seguro que desea autorizar esta solicitud?";
			msj = `Se enviará un correo a <b>${nombrePersona}</b> con sus credenciales adjuntas.`;
			msjDone = `Las credenciales de <b>${nombrePersona}</b> han sido habilitadas y enviadas por correo electrónico.`;
			btnAccepTxt = 'Autorizar';
		} else {
			title = "¿Está seguro que desea rechazar esta solicitud?";
			msj = `Se eliminará la solicitud recibida de <b>${nombrePersona}</b>.`;
			msjDone = `Se ha eliminado la solicitud de <b>${nombrePersona}</b>.`;
			btnAccepTxt = 'Rechazar';
		}

		mensaje_confirmacion(
			title, msj, `warning`, function(param) {
				asyncDoDataReturn(
					'<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
					{
						typeOperation: `update`,
						operation: `solicitud-acceso-empleado`,
						solicitud: tipoSolicitud,
						id: id,
						pId: pId,
						nombrePersona: nombrePersona,
						sideName: sideName,
						correoPersona: correoPersona,
						justificacionEstado: justificacionEstado
					},
					function(data) {
						if(data == "success") {
							mensaje_do_aceptar(`Operación completada:`, msjDone, `success`, function() {
								getButtonsHistorial('Empleado');
								$(`#tblSolicitudes`).DataTable().ajax.reload(null, false);
								if(tipoSolicitud == "rechazar") {
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

	function procesarSolicitudExterna(tipoSolicitud, nombrePersona, dui, fechaNacimiento, correo, solicitudAccesoId, justificacionEstado = 'N/A') {
		// justificacionEstado en autorizar = string ^ para hacer array en update
		let title, msj, msjDone, btnAccepTxt;

		if(tipoSolicitud == "autorizar") {
			title = "¿Está seguro que desea autorizar esta solicitud?";
			msj = `Al presionar <b>Autorizar</b> se enviará un correo a <b>${nombrePersona}</b> con sus credenciales adjuntas.`;
			msjDone = `Las credenciales de <b>${nombrePersona}</b> han sido habilitadas y enviadas por correo electrónico.`;
			btnAccepTxt = 'Autorizar';
		} else {
			title = "¿Está seguro que desea rechazar esta solicitud?";
			msj = `Al presionar <b>Rechazar</b> se eliminará la solicitud recibida de <b>${nombrePersona}</b>.`;
			msjDone = `Se ha eliminado la solicitud de <b>${nombrePersona}</b>.`;
			btnAccepTxt = 'Rechazar';
		}

		mensaje_confirmacion(
			title, msj, `warning`, function(param) {
				asyncDoDataReturn(
					'<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
					{
						typeOperation: `update`,
						operation: `solicitud-acceso-externa`,
						solicitud: tipoSolicitud,
						nombrePersona: nombrePersona,
						dui: dui,
						fechaNacimiento: fechaNacimiento,
						correo: correo,
						id: solicitudAccesoId,
						justificacionEstado: justificacionEstado
					},
					function(data) {
						if(data == "success") {
							mensaje_do_aceptar(`Operación completada:`, msjDone, `success`, function() {
								getButtonsHistorial('Externa');
								$(`#tblSolicitudesExterna`).DataTable().ajax.reload(null, false);
								$("#modal-container").modal("hide"); // para aprobar y rechazar se usa modal
							});
						} else if(data == "ya-existe") {
							mensaje_do_aceptar(`Aviso:`, `Esta persona ya tiene credenciales asignadas, por lo que esta solicitud ha sido rechazada automáticamente`, `warning`, function() {
								getButtonsHistorial('Externa');
								$(`#tblSolicitudesExterna`).DataTable().ajax.reload(null, false);
								$("#modal-container").modal("hide"); // para aprobar y rechazar se usa modal
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

	function getButtonsHistorial(tipoSolicitud) {
    	asyncDoDataReturn(
    		'<?php echo $_SESSION["currentRoute"]; ?>content/divs/buttonsSolicitudesAcceso', 
    		{tipoSolicitud: tipoSolicitud}, 
    		function(data) {
    			if(tipoSolicitud == "Empleado") { // Al reutilizar chocan los buttonId y se duplican los script, asi que se limpia un div, para colocar los otros btn
    				$("#divButtonsHistorialExterna").html("");
    				$("#divButtonsHistorial").html(data);
    			} else { // Al reutilizar chocan los buttonId y se duplican los script, asi que se limpia un div, para colocar los otros btn
    				$("#divButtonsHistorialExterna").html(data);
    				$("#divButtonsHistorial").html("");    				
    			}
    		}
    	); 
	}

    $(document).ready(function() {
    	getButtonsHistorial('Empleado');

    	// Tab: Empleado
        $('#tblSolicitudes thead tr#filterboxrow th').each(function(index) {
            if(index==1) {
                var title = $('#tblSolicitudes thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblSolicitudes.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblSolicitudes = $('#tblSolicitudes').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableSolicitudesAcceso",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoSolicitud": 'Empleado'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                {"width": "20%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1,2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

	    // Tab: Externa
        $('#tblSolicitudesExterna thead tr#filterboxrow-externa th').each(function(index) {
            if(index==1) {
                var title = $('#tblSolicitudesExterna thead tr#filterboxrow-externa th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}ext" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}ext">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblSolicitudesExterna.column($(this).index()).search($(`#input${$(this).index()}ext`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblSolicitudesExterna = $('#tblSolicitudesExterna').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableSolicitudesAcceso",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoSolicitud": 'Externa'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                {"width": "20%"}
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