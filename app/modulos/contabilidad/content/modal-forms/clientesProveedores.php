<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

    if ($_POST['typeOperation'] == "update"){
        $getCP = $cloud->row("SELECT clienteId, tipoRelacion, tipoRelacion, razonSocial, paisId
        FROM fel_clientes_relacion 
        WHERE clienteRelacionId = ?", [$_POST['clienteRelacionId']]);
    }
?>

    <input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
	<input type="hidden" id="operation" name="operation" value="datos-cliente-proveedor">
	<input type="hidden" id="idCliente" name="idCliente" value="<?php echo $_POST['idCliente']; ?>">
	<input type="hidden" id="tipoRelacion" name="tipoRelacion" value="<?php echo $_POST['tipoRelacion']; ?>">
	<input type="hidden" id="nombreCliente" name="nombreCliente" value="<?php echo $_POST['nombreCliente']; ?>">
	<input type="hidden" id="clienteRelacionId" name="clienteRelacionId" value="">

    <div class="row">
        <div class="col-md-6">
            <div class="form-outline">
                <i class="fas fa-user-tie trailing"></i>
                <input type="text" id="razonSocial" class="form-control" name="razonSocial" required>
                <label class="form-label" for="razonSocial">Razón social</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-select-control">
                <select id="pais" name="pais" style="width: 100%;" required>
                    <option></option>
                    <option value="61">El Salvador</option>
                    <?php 
                        $dataPaises = $cloud->rows("
                            SELECT
                                paisId,
                                pais
                            FROM cat_paises
                            WHERE flgDelete = '0' AND paisId <> '61' ORDER BY pais ASC
                        ");
                        foreach ($dataPaises as $dataPaises) {
                            echo '<option value="'.$dataPaises->paisId.'">'.$dataPaises->pais.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#pais").select2({
                dropdownParent: $('#modal-container'),
                placeholder: 'Nacionalidad', 
                allowClear: true
            });

            <?php if ($_POST['typeOperation'] == "update"){ ?>
                $("#razonSocial").val('<?php echo $getCP->razonSocial;?>');
                $("#pais").val('<?php echo $getCP->paisId;?>').trigger('change');
                $("#clienteRelacionId").val('<?php echo $_POST['clienteRelacionId'];?>');

            <?php } ?>

            $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation.php", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");

                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Cliente agregado con éxito.',
                                "success"
                            );
                            $('#tblClientesCliente').DataTable().ajax.reload(null, false);
                            $('#tblProveedoresCliente').DataTable().ajax.reload(null, false);
                            $('#modal-container').modal("hide");
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
        });
    </script>