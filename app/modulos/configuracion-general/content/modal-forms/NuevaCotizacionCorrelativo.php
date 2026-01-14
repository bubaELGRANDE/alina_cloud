<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

?>

<input type="hidden" id="typeOperation" name="typeOperation"  value="insert">
<input type="hidden" id="operation" name="operation" value="cotizacionesCorrelativo">

<div class="row">
    <div class="col-6">
        <div class="form-select-control mb-4">
            <select class="form-select" id="tipoCorrelativo" name="tipoCorrelativo" style="width:100%;" required>
                <option></option>
                <option value="Orden de venta">Orden de venta</option>
                <option value="Cotización">Cotización</option>
            </select>
        </div>
    </div> 
    <div class="col-6">
        <div class="form-select-control mb-4">
            <select class="form-select" id="origenCorrelativo" name="origenCorrelativo" style="width:100%;" required>
                <option></option>
                <option value="Sucursal">Sucursal</option>
            </select>
        </div>
    </div>
</div>
<div id="divSucursal">
    <div class="form-outline mb-4">
        <div class="form-select-control mb-4">
            <select class="form-select" id="sucursalId" name="sucursalId" style="width:100%;" required>
                <option></option>
                <?php
                    $sucursales = $cloud->rows("
                        SELECT 
                            sucursalId, 
                            sucursal 
                        FROM cat_sucursales
                        WHERE flgDelete = ?
                    ",[0]);    
                    foreach ($sucursales as $sucursales) {
                        echo "<option value='$sucursales->sucursalId'> $sucursales->sucursal</option>";
                    }
                ?>
            </select>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#tipoCorrelativo").select2({
        placeholder: "Tipo de correlativo",
        dropdownParent: $('#modal-container'),
        allowClear: true
    });
    $("#origenCorrelativo").select2({
        placeholder: "Origen del correlativo",
        dropdownParent: $('#modal-container'),
        allowClear: true
    });
    $("#sucursalId").select2({
        placeholder: "Sucursal",
        dropdownParent: $('#modal-container'),
        allowClear: true
    });

    $("#divSucursal").hide();

    $("#origenCorrelativo").change(function(e) {
        if($('#origenCorrelativo').val() == "Sucursal") {
            $("#divSucursal").show();
        } else {
            $("#divSucursal").hide();
        }
    });

    $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                'Se ha guardado el correlativo con éxito.',
                                "success"
                            );
                            $("#tblCotizacionCorrelativo").DataTable().ajax.reload(null, false);
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

