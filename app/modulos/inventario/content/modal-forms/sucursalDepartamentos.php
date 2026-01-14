<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
	// arrayFormData = sucursalId
    $dataNombreSucursal = $cloud->row("
        SELECT
            sucursal
        FROM cat_sucursales
        WHERE sucursalId = ?
    ", [$_POST['arrayFormData']]); 
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="sucursal-departamento">
<input type="hidden" id="sucursalDepartamentoId" name="sucursalDepartamentoId" value="0">
<div id="divBtnNuevoDepartamento" class="text-end">
	<button type="button" class="btn btn-primary" onclick="showHideForm(1, 'insert');">
		<i class="fas fa-plus-circle"></i>
		Nuevo Departamento
	</button>
</div>
<div id="divFrmNuevoDepartamento">
    <div class="row">
        <div class="col-md-8">
            <div class="form-outline mb-4">
                <i class="fas fa-building trailing"></i>
                <input type="text" id="departamentoSucursal" class="form-control" name="departamentoSucursal" required />
                <label class="form-label" for="departamentoSucursal">Nombre del Departamento</label>
            </div>
        </div> 
        <div class="col-md-4">
            <div class="form-outline mb-4">
                <i class="fas fa-tag trailing"></i>
                <input type="text" id="codSucursalDepartamento" class="form-control" name="codSucursalDepartamento" required />
                <label class="form-label" for="codSucursalDepartamento">Código del Departamento</label>
            </div>
        </div>
    </div>
    <div class="text-end">
		<button type="submit" class="btn btn-primary">
			<i class="fas fa-save"></i> Guardar
		</button>
		<button type="button" class="btn btn-secondary" onclick="showHideForm(0, 'insert');">
			<i class="fas fa-times-circle"></i> Cancelar
		</button>
    </div>
</div>
<div class="table-responsive">
	<table id="tblSucursalDepartamentos" class="table table-hover" style="width: 100%;">
	    <thead>
	    	<tr id="filterboxrow-sucursal-depto">
	    		<th>#</th>
		        <th>Departamento</th>
		        <th>Acciones</th>
	    	</tr>
	    </thead>
	    <tbody>
        </tbody>
	</table>
</div>
<script>
	function showHideForm(flg, tipo) {
		if(flg == 0) { // hide
			$("#divBtnNuevoDepartamento").show();
			$("#divFrmNuevoDepartamento").hide();
			$("#typeOperation").val("insert"); // regresar la flg para que al presionar los botones vuelvan a su función inicial "insert"
            $('#frmModal').trigger("reset");
		} else { // show
			$("#divBtnNuevoDepartamento").hide();
			$("#divFrmNuevoDepartamento").show();
			$("#typeOperation").val(tipo); // para definir insert/update y validar en el submitHandler
		}
	}

	function editDepartamento(tableData) {
        $("#sucursalDepartamentoId").val(tableData);
		asyncDoDataReturn(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getEditSucursalDepartamento", 
            $("#frmModal").serialize(),
            function(data) {
                var result = JSON.parse(data);
                $("#typeOperation").val("update");
                $("#departamentoSucursal").val(result.departamentoSucursal);
                $("#codSucursalDepartamento").val(result.codSucursalDepartamento);

				$("#divBtnNuevoDepartamento").hide();
				$("#divFrmNuevoDepartamento").show();

                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            }
        );
	}

    function eliminarDepartamento(tableData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar este departamento?`, 
            `Se eliminará de la sucursal.`, 
            `warning`, 
            function(param) {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    {
                        typeOperation: `delete`,
                        operation: `sucursal-departamento`,
                        id: tableData
                    },
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Departamento eliminado con éxito`, `success`, function() {
                                $(`#tblSucursalDepartamentos`).DataTable().ajax.reload(null, false);
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
    	$("#divFrmNuevoDepartamento").hide();

        $("#modalTitle").html("Departamentos - Sucursal: <?php echo $dataNombreSucursal->sucursal; ?>");

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Se ha guardado con éxito la información del departamento departamento.',
                                "success"
                            );
                            $('#tblSucursalDepartamentos').DataTable().ajax.reload(null, false);
                            $('#frmModal').trigger("reset");
                            $("#typeOperation").val("insert");
                            showHideForm(0, 'insert'); // para que se oculte
                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            }
        });

        $('#tblSucursalDepartamentos thead tr#filterboxrow-sucursal-depto th').each(function(index) {
            if(index==1) {
                var title = $('#tblSucursalDepartamentos thead tr#filterboxrow-sucursal-depto th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-sucursal-depto" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-sucursal-depto">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblSucursalDepartamentos.column($(this).index()).search($(`#input${$(this).index()}-sucursal-depto`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).update();
	            });
            } else {
            }
        });

        let tblSucursalDepartamentos = $('#tblSucursalDepartamentos').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableSucursalesDepartamentos",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $_POST["arrayFormData"]; ?>'
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
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>