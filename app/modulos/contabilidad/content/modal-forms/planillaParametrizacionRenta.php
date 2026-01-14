<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
        POST:
        tituloModal
        descuentoRentaId
    */
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
<input type="hidden" id="operation" name="operation" value="parametrizacion-renta">
<input type="hidden" id="descuentoRentaId" name="descuentoRentaId" value="<?php echo $_POST['descuentoRentaId']; ?>">
<input type="hidden" id="flgEnAdelante" name="flgEnAdelante" value="No">
<div class="row mb-4">
    <div class="col-md-4">
        <div class="form-outline">
            <i class="fas fa-edit trailing"></i>
            <input type="text" id="tramoRenta" class="form-control" name="tramoRenta" required />
            <label class="form-label" for="tramoRenta">Tramo</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline">
            <i class="fas fa-dollar-sign trailing"></i>
            <input type="number" id="rangoInicio" class="form-control" name="rangoInicio" min="0.00" step="0.01" required />
            <label class="form-label" for="rangoInicio">Desde</label>
        </div>
    </div>
    <div id="divHasta" class="col-md-4">
        <div class="form-outline">
            <i class="fas fa-dollar-sign trailing"></i>
            <input type="number" id="rangoFin" class="form-control" name="rangoFin" step="0.01" required />
            <label class="form-label" for="rangoFin">Hasta</label>
        </div>
        <div class="form-helper text-end">
            <span id="btnFlgEnAdelante" class="badge rounded-pill bg-secondary" style="cursor: pointer;">
                <i class="fas fa-greater-than"></i> En adelante
            </span>
        </div>
    </div>
    <div id="divEnAdelante" class="col-md-4">
        <div class="form-outline">
            <i class="fas fa-dollar-sign trailing"></i>
            <input type="text" id="rangoFinAdelante" class="form-control" name="rangoFinAdelante" value="En adelante" disabled required />
            <label class="form-label" for="rangoFin">Hasta</label>
        </div>
        <div class="form-helper text-end">
            <span id="btnFlgCancelar" class="badge rounded-pill bg-secondary" style="cursor: pointer;">
                <i class="fas fa-times-circle"></i> Cancelar
            </span>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="form-outline">
            <i class="fas fa-percent trailing"></i>
            <input type="number" id="porcentajeDescuento" class="form-control" name="porcentajeDescuento" min ="0" max ="100"required />
            <label class="form-label" for="porcentajeDescuento">Porcentaje</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline">
            <i class="fas fa-dollar-sign trailing"></i>
            <input type="number" id="montoExceso" class="form-control" name="montoExceso" min="0.00" step="0.01" required />
            <label class="form-label" for="montoExceso">Exceso</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline">
            <i class="fas fa-dollar-sign trailing"></i>
            <input type="number" id="cuotaFija" class="form-control" name="cuotaFija" min="0.00" step="0.01" required />
            <label class="form-label" for="cuotaFija">Cuota fija</label>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#rangoInicio").keyup(function(e) {
            $("#rangoFin").prop("min", $(this).val());
        });

        $("#divEnAdelante").hide();

        $("#btnFlgEnAdelante").click(function(e) {
            $("#divEnAdelante").show();
            $("#divHasta").hide();
            $("#flgEnAdelante").val('Sí');
        });

        $("#btnFlgCancelar").click(function(e) {
            $("#divEnAdelante").hide();
            $("#divHasta").show();
            $("#flgEnAdelante").val('No');
        });

        $("#frmModal").validate({
            messages: {
                rangoInicio: {
                    step: "Ingrese un numero de 2 decimales",
                },
                rangoFin: {
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
                                'Tramo de renta agregado con exito',
                                "success"
                            );
                            $(`#tblRenta`).DataTable().ajax.reload(null, false);
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