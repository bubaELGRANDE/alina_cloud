<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataEstadoProveedor = $cloud->row("
        SELECT estadoProveedor FROM comp_proveedores
        WHERE proveedorId = ? AND flgDelete = ?
    ",[$_POST['proveedorId'], 0]);

    if($dataEstadoProveedor->estadoProveedor == "Inactivo") {
        $disabledInactivo = "disabled";
    } else {
        $disabledInactivo = "";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="proveedor-cuenta-bancaria">
<input type="hidden" id="proveedorId" name="proveedorId" value="<?php echo $_POST['proveedorId']; ?>">
<input type="hidden" id="proveedorCBancariaId" name="proveedorCBancariaId" value="0">
<input type="hidden" id="nombreProveedor" name="nombreProveedor" value="<?php echo $_POST['nombreProveedor']; ?>">
<?php 
    if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(140, $_SESSION["arrayPermisos"])) {
?>
        <div id="divBtnForm" class="text-end">
        	<button type="button" class="btn btn-primary" onclick="showHideForm(1, 'insert');" <?php echo $disabledInactivo; ?>>
        		<i class="fas fa-plus-circle"></i>
        		Nueva Cuenta
        	</button>
        </div>
        <div id="divFormModal">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="form-select-control mb-4">
                        <select id="nombreOrganizacionId" name="nombreOrganizacionId" style="width: 100%;" required>
                            <option></option>
                            <?php 
                                $dataBancos = $cloud->rows("
                                    SELECT
                                        nombreOrganizacionId, nombreOrganizacion, abreviaturaOrganizacion
                                    FROM cat_nombres_organizaciones
                                    WHERE tipoOrganizacion = ? AND flgDelete = ?
                                    ORDER BY nombreOrganizacion
                                ", ['Banco', '0']);
                                foreach ($dataBancos as $bancos) {
                                    echo '<option value="'.$bancos->nombreOrganizacionId.'">'.$bancos->abreviaturaOrganizacion.'</option>';
                                }
                            ?>
                        </select>
                    </div>
                </div> 
                <div class="col-md-6">
                    <div class="form-outline mb-4">
                        <i class="fas fa-money-check-alt trailing"></i>
                        <input type="text" id="numeroCuenta" class="form-control masked" name="numeroCuenta" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required />
                        <label class="form-label" for="numeroCuenta">Número de cuenta</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-select-control mb-4">
                        <select id="tipoCuenta" name="tipoCuenta" style="width: 100%;" required>
                            <option></option>
                            <?php 
                                $arrayTipoCuenta = array("Corriente", "Ahorro");
                                foreach($arrayTipoCuenta as $tipoCuenta) {
                                    echo "<option value='$tipoCuenta'>$tipoCuenta</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-outline mb-4">
                        <i class="fas fa-edit trailing"></i>
                        <textarea type="text" id="descripcionCuenta" class="form-control" name="descripcionCuenta"></textarea>
                        <label class="form-label" for="descripcionCuenta">Descripción</label>
                    </div>            
                </div>
            </div>


            <div class="row">
            	<div class="col-md-3 offset-md-6">
            		<button type="submit" class="btn btn-primary btn-block" <?php echo $disabledInactivo; ?>>
            			<i class="fas fa-save"></i> Guardar
            		</button>
            	</div>
            	<div class="col-md-3">
            		<button type="button" class="btn btn-secondary btn-block" onclick="showHideForm(0, 'insert');">
            			<i class="fas fa-times-circle"></i> Cancelar
            		</button>
            	</div>
            </div>
        </div>
<?php 
    } else {
        // No tiene permiso de nueva cuenta bancaria
    }
?>
<div class="table-responsive">
	<table id="tblProveedorCuentasBanco" class="table table-hover" style="width: 100%;">
	    <thead>
	    	<tr id="filterboxrow-cuentasbanco">
	    		<th>#</th>
		        <th>Cuenta</th>
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
			$("#divBtnForm").show();
			$("#divFormModal").hide();
            $('#frmModal').trigger("reset");
            $("#typeOperation").val("insert");
            $('#nombreOrganizacionId').trigger('change');
            $('#tipoCuenta').trigger('change');
		} else { // show
			$("#divBtnForm").hide();
			$("#divFormModal").show();
            $("#typeOperation").val(tipo);
		}
	}

	function editarProveedorCBancaria(id) {
        $("#proveedorCBancariaId").val(id);
		asyncData(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getInfoProveedorCuentaBancaria", 
            $("#frmModal").serialize(),
            function(data) {
                $("#typeOperation").val("update");
                $("#nombreOrganizacionId").val(data.nombreOrganizacionId).trigger('change');
                $("#numeroCuenta").val(data.numeroCuenta);
                $("#tipoCuenta").val(data.tipoCuenta).trigger('change');
                $("#descripcionCuenta").val(data.descripcionCuenta);
				$("#divBtnForm").hide();
				$("#divFormModal").show();

                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            }
        );
	}

	function eliminarProveedorCBancaria(id) {
        <?php 
            if($dataEstadoProveedor->estadoProveedor == "Inactivo") {
        ?>
                mensaje(
                    "Aviso:",
                    'No es posible eliminar la información de un proveedor inactivo.',
                    "warning"
                );
        <?php 
            } else {
        ?>                
                mensaje_confirmacion(
                    "¿Está seguro que desea eliminar esta cuenta bancaria?", 
                    "Se eliminará la cuenta bancaria asociada al proveedor", 
                    `warning`, 
                    function(param) {
                        asyncDoDataReturn(
                            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                            {
                                typeOperation: 'delete',
                                operation: 'proveedor-cuenta-bancaria',
                                proveedorCBancariaId: id,
                                nombreProveedor: '<?php echo $_POST['nombreProveedor']; ?>'
                            },
                            function(data) {
                                if(data == "success") {
                                    mensaje_do_aceptar(
                                        `Operación completada:`, 
                                        `Cuenta bancaria eliminada con éxito.`, 
                                        `success`, 
                                        function() {
                                            showHideForm(0, 'insert'); // para que se oculte
                                            $('#tblProveedorCuentasBanco').DataTable().ajax.reload(null, false);
                                            $(`#tblProveedores`).DataTable().ajax.reload(null, false);
                                            $(`#tblProveedoresInactivos`).DataTable().ajax.reload(null, false);
                                        }
                                    );
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
                    "Sí, eliminar",
                    `Cancelar`
                );
        <?php 
            }
        ?>
    }
	

    $(document).ready(function() {
    	$("#divFormModal").hide();

        $("#nombreOrganizacionId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Banco'
        });

        $("#tipoCuenta").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de cuenta'
        });

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
                                `Cuenta bancaria ${$("#typeOperation").val() == "update" ? "actualizada" : "agregada"} con éxito.`,
                                "success"
                            );
                            $('#tblProveedorCuentasBanco').DataTable().ajax.reload(null, false);
                            $(`#tblProveedores`).DataTable().ajax.reload(null, false);
                            $(`#tblProveedoresInactivos`).DataTable().ajax.reload(null, false);
                            showHideForm(0, 'insert'); // para que se oculte
                            //$('#modal-container').modal("hide");
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

        $('#tblProveedorCuentasBanco thead tr#filterboxrow-cuentasbanco th').each(function(index) {
            if(index==1) {
                var title = $('#tblProveedorCuentasBanco thead tr#filterboxrow-cuentasbanco th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-cuentasbanco" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-cuentasbanco">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblProveedorCuentasBanco.column($(this).index()).search($(`#input${$(this).index()}-cuentasbanco`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).update();
	            });
            } else {
            }
        });

        let tblProveedorCuentasBanco = $('#tblProveedorCuentasBanco').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableProveedorCuentasBancarias",
                "data": {
                    "proveedorId": '<?php echo $_POST["proveedorId"]; ?>'
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