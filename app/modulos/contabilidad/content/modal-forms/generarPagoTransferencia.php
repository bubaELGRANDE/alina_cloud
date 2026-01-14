<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $bancoId = $_POST['bancoId'];
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="pagos-transferencias">
<div class="form-select-control mb-4">
    <select id="nombreOrganizacionId" name="nombreOrganizacionId" style="width: 100%;" required>
        <option></option>
        <?php 
            // Colocarlos separado por coma, como string, ya que es para usarlo en un IN de SQL
            $arrayBancosIndupal = "5";
            $dataBancosIndupal = $cloud->rows("
                SELECT 
                    nombreOrganizacionId,
                    nombreOrganizacion,
                    abreviaturaOrganizacion
                FROM cat_nombres_organizaciones
                WHERE nombreOrganizacionId IN ($arrayBancosIndupal) AND flgDelete = ?
            ", [0]);
            foreach ($dataBancosIndupal as $bancoIndupal) {
                echo "<option value='$bancoIndupal->nombreOrganizacionId' ".($bancoId == $bancoIndupal->nombreOrganizacionId ? "selected" : "").">$bancoIndupal->abreviaturaOrganizacion</option>";
            }
        ?>
    </select>
</div>
<div class="form-outline mb-4">
    <input type="date" id="fechaPagoTransferencia" name="fechaPagoTransferencia" class="form-control" required />
    <label class="form-label" for="fechaPagoTransferencia">Fecha de pago</label>
</div>
<div class="form-select-control mb-4">
    <select id="tipoTransferencia" name="tipoTransferencia" style="width: 100%;" required>
        <option></option>
        <?php 
            $arrayTiposTransferencia = array("Local", "Transfer365");
            for ($i=0; $i < count($arrayTiposTransferencia); $i++) { 
                echo "<option value='$arrayTiposTransferencia[$i]'>$arrayTiposTransferencia[$i]</option>";
            }
        ?>
    </select>
</div>
<script>
    $(document).ready(function() {
        $("#nombreOrganizacionId").select2({
        	dropdownParent: $('#modal-container'),
            placeholder: "Banco"
        });

        $("#tipoTransferencia").select2({
        	dropdownParent: $('#modal-container'),
            placeholder: "Tipo de transferencia"
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Generar pago", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Pago por transferencia generado con éxito. Por favor, agregue los Quedan y Otros pagos correspondientes.',
                                "success"
                            );
                            $("#filtroBancoId").val($("#nombreOrganizacionId").val()).trigger('change');
                            $('#tblTransferencias').DataTable().ajax.reload(null, false);
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