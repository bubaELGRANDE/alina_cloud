<?php 
@session_start();

//if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(34, $_SESSION["arrayPermisos"])) {

$jsonAgregar = array(
    "typeOperation" => "insert",
    "clienteId"     => $_POST['clienteId'],
    "nombreCliente" => $_POST["cliente"],
    "flgTipo"       => $_POST["flgTipo"],
    "tituloModal"   => "Personas politicamente expuesta"  
);
$funcionAgregar = htmlspecialchars(json_encode($jsonAgregar));

if($_POST["tipoPersona"] == "1") {
    $urlVariables = "naturales";
} else {
    $urlVariables = "juridicos";
}  
?>
<h2>
    Personas expuestas politicamente - Cliente: <?php echo $_POST["cliente"]; ?>
</h2>
<hr>
<div class="row mb-4">
    <div class="col-3"> 
        <button type="button" id="btnPageClientes"  class="btn btn-secondary btn-block">
            <i class="fas fa-chevron-circle-left"></i>
            Clientes
        </button>
    </div>
    
    <div class="col-3 offset-6">
        <button type="button" id="btnNuevaUbicacion" onclick="modalPEP(<?php echo $funcionAgregar; ?>);" class="btn btn-primary btn-block">
            <i class="fas fa-plus-circle"></i>
            Nueva PEP
        </button>
    </div>
</div>

<div class="table-responsive">
    <table id="tblPEP" class="table table-hover mt-3" style="width: 100%;">
    <thead>
        <tr id="filterboxrow-tblUbicaciones">
            <th>#</th>
            <th>Nombre</th>
            <th>Cargo</th>
            <th>Acciones</th>
        </tr>
    </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function modalPEP(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: "xl",
                modalTitle: frmData.tituloModal,
                modalForm: 'clientePEP',
                formData: frmData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function modalPEPFam(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: "lg",
                modalTitle: frmData.tituloModal,
                modalForm: 'PEPFam',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function modalPEPSoc(frmData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: "lg",
                modalTitle: frmData.tituloModal,
                modalForm: 'PEPSociedades',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    function delPEP(frmData){
        let title       = "Aviso:"
        let msj         = "¿Está seguro que quiere eliminar este registro?";
        let btnAccepTxt = "Confirmar";
        let msjDone     = "Se eliminó correctamente el registro.";
        
        mensaje_confirmacion(
			title, msj, `warning`, function(param) {
				asyncDoDataReturn(
					'<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
					frmData,
					function(data) {
						if(data == "success") {
							mensaje_do_aceptar(`Operación completada:`, msjDone, `success`, function() {
                                $('#tblPEP').DataTable().ajax.reload(null, false);
                                //$("#modal-container").modal("hide"); // para aprobar y rechazar se usa modal
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
        $("#btnPageClientes").click(function() {
            // id 12 de tbl menus, no se puede usar changePage porque es exclusiva para "page-"
            asyncPage(62, 'submenu', '<?php echo $urlVariables; ?>');
        });


        $('#tblPEP thead tr#filterboxrow-tblPEP th').each(function(index) {
            if(index==1 || index == 2) {
                var title = $('#tblPEP thead tr#filterboxrow-tblPEP th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-tblPEP" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblPEP.column($(this).index()).search($(`#input${$(this).index()}-tblPEP`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblPEP = $('#tblPEP').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableClientePEP",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $_POST["clienteId"]; ?>',
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "25%"},
                null,
                null
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