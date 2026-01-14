<?php 
	@session_start();
?>
<h2>
    Tipos de contacto
</h2>
<hr>
<div class="row">
    <div class="col text-end">
        <?php if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(18, $_SESSION["arrayPermisos"])) { ?>
        <button id="btnNuevaArea" type="button" class="btn btn-primary" onclick="nuevoTipoCon();"><i class="fas fa-plus-circle"></i> Nuevo tipo de contacto</button>
        <?php } ?>
    </div>
</div>
<div class="table-responsive">
    <table id="tblTipoCont" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Tipo de contacto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function nuevoTipoCon(tipoCon) {
        loadModal(
            "modal-container",
            {
                modalDev: "9^18",
                modalSize: 'lg',
                modalTitle: `Nuevo tipo de contacto`,
                modalForm: 'nuevoTipoContacto',
                formData: tipoCon,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function modTipoCon(idTipoCon){
        loadModal(
            "modal-container",
            {
                modalDev: "9^19",
                modalSize: 'md',
                modalTitle: `Editar tipo de contacto`,
                modalForm: 'editarTipoContacto',
                formData: idTipoCon,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        )
    }
    function delTipoCon(idTipoCon){
        let title = "Aviso:"
        let msj = "¿Está seguro que quiere eliminar este registro?";
        let btnAccepTxt = "Confirmar";
        let msjDone = "Se eliminó correctamente el registro.";
        
        mensaje_confirmacion(
			title, msj, `warning`, function(param) {
				asyncDoDataReturn(
					'<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
					{
						typeOperation: 'delete',
						operation: 'delTipoCon',
						idTipoCon: idTipoCon,
					},
					function(data) {
						if(data == "success") {
							mensaje_do_aceptar(`Operación completada:`, msjDone, `success`, function() {
								

                        $("#tblTipoCont").DataTable().ajax.reload(null, false);
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
    $(document).ready(function() {
    
        $('#tblTipoCont thead tr#filterboxrow th').each(function(index) {
            if(index==1) {
                var title = $('#tblTipoCont thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEstudio.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblEstudio = $('#tblTipoCont').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableTipoContacto",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoSolicitud": 'Estudio'
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