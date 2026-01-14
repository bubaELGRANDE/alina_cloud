<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
	// arrayFormData = menuId ^ nombreMenu
	$arrayFormData = explode("^", $_POST["arrayFormData"]);

    // Validar si el permisoMenu es Dropdown y prevenir agregar si existen submenus
    $querySubMenus = "
        SELECT
            menuId
        FROM conf_menus
        WHERE menuSuperior = ? AND flgDelete = '0'
        ORDER BY numOrdenMenu                
    ";
    $existeSubMenu = $cloud->count($querySubMenus, [$arrayFormData[0]]);
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="menu-permiso">
<input type="hidden" id="menuId" name="menuId" value="<?php echo $arrayFormData[0]; ?>">
<input type="hidden" id="flgInsertUpdate" name="flgInsertUpdate" value="insert">
<input type="hidden" id="updateMenuPermisoId" name="updateMenuPermisoId" value="0">
<?php 
	if(in_array(12, $_SESSION["arrayPermisos"]) || in_array(39, $_SESSION["arrayPermisos"])) {
		if($existeSubMenu == 0) {
?>
			<div id="divBtnNuevoPermiso" class="row">
				<div class="col-lg-3 offset-lg-9">
					<button type="button" id="btnNuevoPermiso" class="btn btn-primary btn-block" onclick="showHideForm(1, 'insert');">
						<i class="fas fa-plus-circle"></i>
						Nuevo Permiso
					</button>
				</div>
			</div>
			<div id="divFrmNuevoPermiso" class="row">
				<div class="col-lg-10">
			        <div class="form-outline mb-4">
			            <i class="fas fa-lock trailing"></i>
			            <input type="text" id="permisoMenu" class="form-control" name="permisoMenu" required />
			            <label class="form-label" for="permisoMenu">Permiso</label>
			        </div>
				</div>
				<div class="col-lg-1">
					<button type="submit" class="btn btn-primary btn-block">
						<i class="fas fa-save"></i>
					</button>
				</div>
				<div class="col-lg-1">
					<button type="button" class="btn btn-secondary btn-block" onclick="showHideForm(0, 'insert');">
						<i class="fas fa-times-circle"></i>
					</button>		
				</div>
			</div>
<?php 
		} else {
?>
			<div class="row">
				<div class="col-lg-3 offset-lg-9">
					<button type="button" id="btnNuevoPermiso" class="btn btn-primary btn-block" disabled>
						<i class="fas fa-plus-circle"></i>
						Nuevo Permiso
					</button>
				</div>
			</div>
<?php 
		}
	} else {
		// Permiso
	}
?>
<div class="table-responsive">
	<table id="tblMenuPermiso" class="table table-hover" style="width: 100%;">
	    <thead>
	    	<tr id="filterboxrow-permisos">
	    		<th># (Id)</th>
		        <th>Permiso</th>
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
			$("#divBtnNuevoPermiso").show();
			$("#divFrmNuevoPermiso").hide();
			$("#permisoMenu").val('');
			$("#flgInsertUpdate").val("insert"); // regresar la flg para que al presionar los botones vuelvan a su función inicial "insert"
		} else { // show
			$("#divBtnNuevoPermiso").hide();
			$("#divFrmNuevoPermiso").show();
			$("#flgInsertUpdate").val(tipo); // para definir insert/update y validar en el submitHandler
		}
	}

	function editarMenuPermiso(tableData) {
		let arrayTblData = tableData.split("^");
		$("#updateMenuPermisoId").val(arrayTblData[0]);
		$("#permisoMenu").val(arrayTblData[1]);
        document.querySelectorAll('.form-outline').forEach((formOutline) => {
            new mdb.Input(formOutline).init();
        });
		showHideForm(1, "update");
	}

	function eliminarMenuPermiso(tableData) {
		let arrayTblData = tableData.split("^");
		mensaje_confirmacion(
			`¿Está seguro que desea eliminar el permiso ${arrayTblData[1]}?`, 
			`Se eliminará este permiso para los usuarios a quienes se les había asignado.`, 
			`warning`, 
			function(param) {
				asyncDoDataReturn(
					'<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
					{
						typeOperation: `delete`,
						operation: `eliminar-menu-permiso`,
						id: arrayTblData[0]
					},
					function(data) {
						if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                "Recuerde eliminar este permiso de las interfaces/fuente correspondiente.",
                                "success"
                            );
                            $('#tblMenuPermiso').DataTable().ajax.reload(null, false);
                            $('#tblMenus').DataTable().ajax.reload(null, false);
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
    	$("#divFrmNuevoPermiso").hide();

        $("#frmModal").validate({
            submitHandler: function(form) {
            	if($("#flgInsertUpdate").val() == "insert") {
	                asyncDoDataReturn(
	                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
	                    $("#frmModal").serialize(),
	                    function(data) {
	                        if(data == "success") {
	                            mensaje(
	                                "Operación completada:",
	                                'Permiso creado con éxito.<br>Para aplicar este permiso, debe asignarse a un usuario y colocarse en las interfaces/fuente correspondiente.',
	                                "success"
	                            );
	                            $('#tblMenuPermiso').DataTable().ajax.reload(null, false);
	                            $('#tblMenus').DataTable().ajax.reload(null, false);
	                            $('#permisoMenu').val('');
	                            showHideForm(0, "insert");
	                        } else {
	                            mensaje(
	                                "Aviso:",
	                                data,
	                                "warning"
	                            );
	                        }
	                    }
	                );
            	} else {
	                asyncDoDataReturn(
	                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
	                    {
							typeOperation: 'update',
							operation: 'menu-permiso',
							menuPermisoId: $("#updateMenuPermisoId").val(),
							permisoMenu: $("#permisoMenu").val()    	
	                    },
	                    function(data) {
	                        if(data == "success") {
	                            mensaje(
	                                "Operación completada:",
	                                'El nombre del permiso ha sido actualizado con éxito.',
	                                "success"
	                            );
	                            $('#tblMenuPermiso').DataTable().ajax.reload(null, false);
	                            $('#tblMenus').DataTable().ajax.reload(null, false);
	                            $('#permisoMenu').val('');
	                            showHideForm(0, "insert");
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
            }
        });

        $('#tblMenuPermiso thead tr#filterboxrow-permisos th').each(function(index) {
            if(index==1) {
                var title = $('#tblMenuPermiso thead tr#filterboxrow-permisos th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-permisos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-permisos">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblMenuPermiso.column($(this).index()).search($(`#input${$(this).index()}-permisos`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblMenuPermiso = $('#tblMenuPermiso').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableMenuPermisos",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $arrayFormData[0]; ?>'
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