<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
        POST:
            descuentoLeyId, 
            nombreDescuentoLey, 
            tipoDescuento, 
            tipoValorDescuento,
            montoMaximo,
            cuotaExcesoMaximo,
            valorDescuento,
            estadoDescuentoLey
    */
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
<input type="hidden" id="operation" name="operation" value="parametrizacion-desc-ley">
<input type="hidden" id="descuentoLeyId" name="descuentoLeyId" value="<?php echo $_POST['descuentoLeyId']; ?>">
<input type="hidden" id="tipoDescuento" name="tipoDescuento" value="<?php echo $_POST['tipoDescuento']; ?>">
<div class="row mb-4">
    <div class="col-md-12">
        <div class="form-outline">
            <i class="fas fa-user-minus trailing"></i>
            <input type="text" id="nombreDescuentoLey" class="form-control" name="nombreDescuentoLey" required />
            <label class="form-label" for="nombreDescuentoLey">Nombre del descuento: <?php echo $_POST['tipoDescuento']; ?></label>
        </div>
    </div>
</div>    
<div class="row mb-4">    
    <label>Tipo Valor</label>
    <div class="col-md-2">    
        <div class="form-check">
            <input class="form-check-input" type="radio" name="tipoValorDescuento" id="filtroValorPorcentaje" value="Porcentaje" checked />
            <label class="form-check-label" for="filtroValorPorcentaje">Porcentaje</label>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="tipoValorDescuento" id="filtroValorMonto" value="Monto" />
            <label class="form-check-label" for="filtroValorMonto">Monto</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline">
            <i id="iconoValorDescuento" class="fas fa-percent trailing"></i>
            <input type="number" id="valorDescuento" class="form-control" name="valorDescuento" min="0.00" max="100.00" required />
            <label class="form-label" for="valorDescuento">Descuento</label>
        </div>
    </div>
    <div class="col-md-4">
        <label>¿Máximo Descuento?</label>
        <div class="d-flex">
                <label class="form-check-label me-2" for="estadoDescuentoLey">No</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="estadoDescuentoLey" name="estadoDescuentoLey">
                    </div>
                <label class="form-check-label" for="estadoDescuentoLey">Sí</label>
        </div>
    </div>
</div>
<div class="row" id="descuentosMaximos" style="display:none;">
    <div class="col-md-4">
        <div class="form-outline">
            <i class="fas fa-dollar-sign trailing"></i>
            <input type="number" id="montoMaximo" class="form-control" name="montoMaximo" min="0.00" step="0.01" required />
            <label class="form-label" for="montoMaximo">Monto máximo</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline">
            <i class="fas fa-dollar-sign trailing"></i>
            <input type="number" id="cuotaExcesoMaximo" class="form-control" name="cuotaExcesoMaximo" min="0.00" step="0.01" required />
            <label class="form-label" for="cuotaExcesoMaximo">Cuota exceso máximo</label>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#rangoInicio").keyup(function(e) {
            $("#rangoFin").prop("min", $(this).val());
        });

        $("input[type=radio][name=tipoValorDescuento]").change(function(e) {
            if($(this).val() == "Porcentaje") {
                $("#iconoValorDescuento").removeClass("fas fa-dollar-sign trailing");
                $("#iconoValorDescuento").addClass("fas fa-percent trailing");
            } else {
                $("#iconoValorDescuento").removeClass("fas fa-percent trailing");
                $("#iconoValorDescuento").addClass("fas fa-dollar-sign trailing");
            }            
        });
        $('#estadoDescuentoLey').on('click', function() { 
            $("#descuentosMaximos").toggle();
        });
        $("#frmModal").validate({
            messages: {
                rangoInicio: {
                    step: "Ingrese un numero de 2 decimales",
                },
                porcentajeDescuento:{
                    max:"Debe ingresar un valor mayor o igual a 0 y menor o igual que 100"
                },
                montoExceso:{
                    step:"Ingrese un numero de 2 decimales"
                },
                cuotaFija:{
                    step:"Ingrese un numero de 2 decimales"
                }
            },
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Descuento agregado con exito',
                                "success"
                            );
                            $(`#<?php echo $_POST['tblDescuentoLey']; ?>`).DataTable().ajax.reload(null, false);
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