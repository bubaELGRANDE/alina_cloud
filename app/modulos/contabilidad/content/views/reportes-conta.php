<?php
require_once("../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataPeriodos = $cloud->rows("SELECT partidaContaPeriodoId,mes,anio,concat(mesNombre,' ',anio) as periodoNombre
FROM conta_partidas_contables_periodos
WHERE flgDelete = ?
ORDER BY  anio ASC, mes ASC ", [0]);
?>
<form id="frmModal">
    <div class="container-fluid">
        <div class="row">
            <!-- Columna izquierda -->
            <div class="col-md-2">
                <h4 class="mb-4">Seleccione un reporte</h4>

                <!-- Select de tipo de reporte -->
                <div class="mb-4">
                    <label for="tipoReporte" class="form-label">Tipo de reporte</label>
                    <select class="form-select" id="tipoReporte" name="file" required>
                        <option disable selected>Seleccione el tipo de reporte </option>
                        <option value="estadoResultadoCCosto">Estado de resultado por centro de costo</option>
                        <option value="balanceGeneral">Balance general</option>
                        <option value="balanceComprobacion">Balance de comprobación</option>
                        <option value="balanceComprobacionDetalle">Balance de comprobación Anexo</option>
                        <option value="libroDiario">Libro diario</option>
                        <option value="mayorContable">Mayor Contable</option>
                        <option value="diarioCuentas">Reporte Diario de cuentas</option>
                        <option value="audiCCosto">Detalle de cuentas para auditoria</option>
                        <option value="totalVenta">Total de ventas (Robin)</option>
                    </select>
                </div>
                <div id="reportType" class="mb-4">
                    <h6 class="fw-bold">Formato de Reporte</h6>
                    <div class="form-select-control mb-2">
                        <select class="form-select" id="extencion" name="extension" required>
                            <option disabled selected>Seleccione el formato del reporte</option>
                            <option id="pdf" value="pdf">PDF</option>
                            <option id="xls" value="xls">Exel</option>
                        </select>
                    </div>
                </div>
                <div id="rangoPeriodo" class="mb-4">
                    <h6 class="fw-bold">Rango de periodos contables</h6>
                    <p class="text-muted small mb-2">Filtrar información por un periodo contable de inicio y fin.</p>
                    <div class="form-select-control mb-2">
                        <select class="form-select" id="fechaInicioA" name="fechaInicio" required>
                            <option value="0">Seleccione el periodo inicial del rango</option>
                            <?php foreach ($dataPeriodos as $periodo): ?>
                                <option value="<?= $periodo->partidaContaPeriodoId ?>">
                                    <?= $periodo->periodoNombre ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-select-control">
                        <select class="form-select" id="fechaFinA" name="fechaFin" required>
                            <option value="0">Seleccione el periodo final del rango</option>
                            <?php foreach ($dataPeriodos as $periodo): ?>
                                <option value="<?= $periodo->partidaContaPeriodoId ?>">
                                    <?= $periodo->periodoNombre ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div id="tipoCuenta" class="mb-4">
                    <h6 class="fw-bold">Clasificación de cuenta</h6>
                    <p class="text-muted small mb-2">Seleccionar la clasificación de la cuentas.</p>
                    <div class="form-select-control mb-2">
                        <select class="form-select" id="numCuenta" name="numCuenta" required>
                            <option value="0">Seleccione el periodo inicial del rango</option>
                            <option value="10">TODAS LA CUENTAS</option>
                            <option value="1">1 - ACTIVOS</option>
                            <option value="2">2 - PASIVOS</option>
                            <option value="3">3 - PATRIMONIO</option>
                            <option value="4">4 - PERDIDAS Y GANANCIAS</option>
                            <option value="5">5 - CUENTAS DE RESULTADO DEUDOR</option>
                            <option value="6">6 - CUENTAS DE RESULTADO ACREEDOR</option>
                            <option value="7">7 - OTROS INGRESOS</option>
                            <option value="8">8 - INGRESO POR REVERSION DE DETERIORO ACTIV</option>
                            <option value="9">9 - INGRESOS NO OPERACIONALES</option>
                        </select>
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary w-100" id="btnReporte">
                        <i class="fas fa-print me-2"></i>Generar reporte
                    </button>
                </div>
            </div>
            <div class="col-md-10" id="divReporte">
                <!-- Aquí se puede mostrar el resultado del reporte -->
            </div>
        </div>
    </div>
</form>


<script>
    $(document).ready(function() {
        $('#rangoPeriodo').hide();
        $('#reportType').hide();
        $('#tipoCuenta').hide();
    });

    $("#tipoReporte").on("change", function() {
        switch ($(this).val()) {
            case "balanceGeneral":
                $('#rangoPeriodo').show();
                $('#fechaFinA').val(0).prop('disabled', true).hide();
                $('#reportType').show();
                $('#pdf').css("display", "block");
                $('#extencion').val('pdf');
                $('#tipoCuenta').hide();
                break;
            case "balanceComprobacion":
                $('#rangoPeriodo').show();
                $('#fechaFinA').val(0).prop('disabled', true).hide();
                $('#reportType').show();
                $('#pdf').css("display", "block");
                $('#extencion').val('pdf');
                $('#tipoCuenta').hide();
                break;
            case "balanceComprobacionDetalle":
                $('#rangoPeriodo').show();
                $('#fechaFinA').val(0).prop('disabled', true).hide();
                $('#reportType').show();
                $('#pdf').css("display", "block");
                $('#extencion').val('pdf');
                $('#tipoCuenta').hide();
                break;
            case "mayorContable":
                $('#rangoPeriodo').show();
                $('#fechaFinA').val(0).prop('disabled', true).hide();
                $('#reportType').show();
                $('#pdf').css("display", "block");
                $('#extencion').val('pdf');
                $('#tipoCuenta').hide();
                break;

            case "estadoResultadoCCosto":
                $('#rangoPeriodo').show();
                $('#fechaFinA').val(0).prop('disabled', true).hide();
                $('#reportType').show();
                $('#extencion').val('xls').trigger('change');
                $('#pdf').css("display", "none");
                $('#tipoCuenta').hide();
                break;
            case "totalVenta":
                $('#rangoPeriodo').show();
                $('#fechaFinA').val(0).prop('disabled', true).hide();
                $('#reportType').show();
                $('#extencion').val('xls').trigger('change');
                $('#pdf').css("display", "none");
                $('#tipoCuenta').hide();
                break;
            case "diarioCuentas":
                $('#rangoPeriodo').show();
                $('#fechaFinA').val(0).prop('disabled', true).hide();
                $('#reportType').show();
                $('#extencion').val('xls').trigger('change');
                $('#pdf').css("display", "none");
                $('#tipoCuenta').show();
                break;
            case "libroDiario":
                $('#rangoPeriodo').show();
                $('#fechaFinA').val(0).prop('disabled', true).hide();
                $('#reportType').show();
                $('#tipoCuenta').hide();
                $('#pdf').css("display", "block");
                $('#extencion').val('pdf');
                break;
            case "audiCCosto":
                $('#rangoPeriodo').show();
                $('#tipoCuenta').hide();
                $('#reportType').show();
                $('#fechaFinA').val(0).prop('disabled', false).show();
                $('#extencion').val('xls').trigger('change');
                $('#pdf').css("display", "none");
                break;
            default:
                $('#rangoPeriodo').hide();
                break;
        }
    });
    1 -


        $("#frmModal").validate({
            submitHandler: function(form) {

                let fechaInicio = parseInt($("#fechaInicioA").val(), 10);
                let fechaFin = parseInt($("#fechaFinA").val(), 10);

                // Validar que ambas fechas sean seleccionadas
                if (isNaN(fechaInicio) || fechaInicio === 0) {
                    mensaje(
                        "Aviso:",
                        "Debe seleccionar el periodo inicial.",
                        "warning"
                    );
                    return false;
                }

                // Si el campo fechaFinA está visible y habilitado, validar también
                if ($("#fechaFinA").is(":visible") && !$("#fechaFinA").prop("disabled")) {
                    if (isNaN(fechaFin) || fechaFin === 0) {
                        mensaje(
                            "Aviso:",
                            "Debe seleccionar el periodo final.",
                            "warning"
                        );
                        return false;
                    }

                    // Validar que fechaFin no sea menor que fechaInicio
                    if (fechaFin < fechaInicio) {
                        mensaje(
                            "Aviso:",
                            "El periodo final no puede ser anterior al inicial.",
                            "warning"
                        );
                        return false;
                    }
                }


                button_icons("btnReporte", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>reportes",
                    $("#frmModal").serialize(),
                    function(data) {
                        // Mantener el botón disabled para prevenir que generen más de uno sino carga

                        $("#divReporte").html(data);
                        setTimeout(function() {
                            button_icons("btnReporte", "fas fa-print me-2", "Generar reporte", "enabled");
                        }, 400);
                    }
                );
            }
        });
</script>