<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataRamas = $cloud->row("SELECT 
        organigramaRamaId,
        organigramaRama,
        organigramaRamaDescripcion,
        ramaSuperiorId
    FROM cat_organigrama_ramas
    WHERE flgDelete = '0' AND organigramaRamaId =?", [$_POST["ramaId"]]);
?>
<h2>
    Área: <?php echo $_POST["rama"]; ?>
</h2>
<p class="lead"><?php echo $dataRamas->organigramaRamaDescripcion; ?></p>
<hr>
<div class="row mb-4 justify-content-between">
    <div class="col-md-3">
        <button type="button" id="btnPageModulos" class="btn btn-secondary btn-block ttip">
            <i class="fas fa-chevron-circle-left"></i>
            Empleados
            <span class="ttiptext">Volver a Organigrama</span>
        </button>
    </div>
    <div class="col-md-3 text-end">
        <button type="button" id="btnPageModulos" class="btn btn-primary" onclick="modalEmpleadoOrganigrama(`<?php echo $_POST["ramaId"]; ?>`, `<?php echo $_POST["rama"];?>`)">
            <i class="fas fa-plus-circle"></i>
            Agregar empleados
        </button>
    </div>
</div>
<div id="jefeArea"></div>
<div class="table-responsive">
    <table id="tblOrganigramaRama" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-renuncias">
                <th>#</th>
                <th>Empleado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function getJefe(ramaId, expOrganigramaId) {
        asyncDoDataReturn(
            '<?php echo $_SESSION["currentRoute"]; ?>content/divs/getJefeArea',
            {
                ramaId: ramaId,
                expOrganigramaId: expOrganigramaId
            },
            function(data) {
                $("#jefeArea").html(data);
            }
        );  
    }
    function modalEmpleadoOrganigrama(id,rama) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Rama: `+rama,
                modalForm: 'empleadoOrganigrama',
                formData: id,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalEditEmpleadoOrganigrama(id, nombre) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: `Cambiar empleado: `+nombre,
                modalForm: 'editEmpleadoOrganigrama',
                formData: id,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function eliminarEmpleadoOrganigrama(idExpOrg, nombreEmp) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar a ${nombreEmp} del área?`, 
            `Se eliminará del área de <?php echo $_POST["rama"]; ?>.`, 
            `warning`, 
            function(param) {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    {
                        typeOperation: `delete`,
                        operation: `empleado-organigrama`,
                        nombreEmpleado: nombreEmp,
                        idExpOrg: idExpOrg
                    },
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Se ha eliminado a : ${nombreEmp} con éxito del área.`, `success`, function() {
                                $(`#tblOrganigramaRama`).DataTable().ajax.reload(null, false);
                                getJefe(<?php echo $_POST["ramaId"]; ?>, 0);
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
        getJefe(<?php echo $_POST["ramaId"]; ?>, 0);
        
        $("#btnPageModulos").click(function() {
            // id 13 de tbl menus, no se puede usar changePage porque es exclusiva para "page-"
            asyncPage(24, 'submenu');
        });

        $('#tblOrganigramaRama thead tr#filterboxrow-activos th').each(function(index) {
            if(index == 1 || index == 2) {
                var title = $('#tblOrganigramaRama thead tr#filterboxrow-activos th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}activos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}activos">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblOrganigramaRama.column($(this).index()).search($(`#input${$(this).index()}activos`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblOrganigramaRama = $('#tblOrganigramaRama').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableOrganigramaEmpleado",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $_POST["ramaId"];?>'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                {"width": "60%"},
                {"width": "30%"},
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        
    });
</script>