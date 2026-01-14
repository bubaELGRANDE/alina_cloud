<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="generado-partida">
<div class="container-fluid">
    <div class="row">

        <!-- Bloque selección de partida -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-book me-2"></i>Partida Contable</h5>
            </div>

            <div class="card-body bg-light">
                <h6 class="fw-bold">Seleccione un tipo de partida</h6>
                <p class="text-muted small mb-2">
                    Escoge el <strong>tipo de partida contable</strong> con el cual se procesará la operación.
                </p>
                <select class="form-select mb-3" id="tipoPartida" style="width: 100%;" name="tipoPartida">
                    <option value="" disabled selected>Seleccione un tipo de partida...</option>
                    <?php if (in_array(359, $_SESSION["arrayPermisos"])) { ?>
                        <option value="100">GENERADOR DE PARTIDA AUTOMATICA</option>
                        <option value="101">PARTIDA DE LIQUIDACIÓN RETACEO</option>
                    <?php } ?>

                    <?php if (in_array(387, $_SESSION["arrayPermisos"])) { ?>
                        <option value="1">GENERAR PATIDA VENTAS CREDITO</option>
                        <option value="5">GENERAR PATIDA VENTAS CONTADO</option>
                    <?php } ?>
                    <?php
                    $dataTipo = $cloud->rows("SELECT tipoPartidaId, descripcionPartida FROM cat_tipo_partida_contable WHERE modulo = ?", ["AUTO"]);
                    foreach ($dataTipo as $tipo) {
                        echo '<option value="' . $tipo->tipoPartidaId . '">' . $tipo->descripcionPartida . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Liquidación de Compras Diarias -->
        <div class="card shadow-sm mb-4" id="liquidacionCompras">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Liquidación de Compras Diarias</h5>
            </div>
            <div class="card-body">

                <!-- Paso 1 -->
                <div class="mb-4">
                    <h6 class="fw-bold">Paso 1: Seleccionar fecha de compras</h6>
                    <p class="text-muted small mb-2">
                        Indica la <strong>fecha</strong> de las compras diarias que deseas liquidar.
                    </p>
                    <input type="date" class="form-control" name="fechaLiquidacion" required>
                </div>

                <hr>

                <!-- Paso 2 -->
                <div class="mb-3">
                    <h6 class="fw-bold">Paso 2: Seleccionar cuenta de pagos</h6>
                    <p class="text-muted small mb-2">
                        Elige una o varias <strong>cuentas de pago</strong> que se utilizarán para la liquidación.
                    </p>
                    <select class="form-select" id="cuentaContable" style="width: 100%;" name="cuentaContablePago">
                        <option>Seleccione una cuenta de pago...</option>
                    </select>
                </div>

            </div>
        </div>

        <?php if (in_array(359, $_SESSION["arrayPermisos"])) { ?>
            <div class="card shadow-sm mb-4" id="repetitivas">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Generado de repetitivas</h5>
                </div>
                <div class="card-body">

                    <!-- Paso 1 -->
                    <div class="mb-4">
                        <h6 class="fw-bold">Paso 1: Seleccionar fecha</h6>
                        <p class="text-muted small mb-2">
                            Mes a generar partidas.
                        </p>
                        <input type="date" class="form-control" name="fechaRepetitiva" required>
                    </div>
                    <hr>
                </div>
            </div>
            <div class="card shadow-sm mb-4" id="retaceo">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Liquidación de retaceo</h5>
                </div>
                <div class="card-body">

                    <!-- Paso 1 -->
                    <div class="mb-4">
                        <h6 class="fw-bold">Paso 1: Selecciona un retaceo</h6>
                        <p class="text-muted small mb-2">
                            Ingresa el retaceoId
                        </p>
                        <input type="number" class="form-control" name="retaceoId" required>

                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold">Paso 1: Selecciona un mes a liquidar</h6>
                        <p class="text-muted small mb-2">
                            Puede ser cualquier dia del mes, solo se indica el mes del periodoContaId
                        </p>
                        <input type="date" class="form-control" name="fechaLiquidacionRe" required>
                    </div>


                    <hr>
                </div>
            </div>
        <?php } ?>
        <?php if (in_array(387, $_SESSION["arrayPermisos"])) { ?>
            <div class="card shadow-sm mb-4" id="pagoPost">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Agregar comisión de uso de post</h5>
                </div>
                <div class="card-body">
                    <!-- Paso 1 -->
                    <div class="mb-4">
                        <h6 class="fw-bold">Paso 1: Seleccionar fecha de compras de comisión bancaria</h6>
                        <p class="text-muted small mb-2">
                            Indica la <strong>fecha</strong> de las partida de ventas contado.
                        </p>
                        <input type="date" class="form-control" name="fechaLiquidacion" required>
                    </div>
                </div>
            </div>
        <?php } ?>
        <!-- Botón -->
        <div class="d-grid">
            <button type="submit" class="btn btn-success btn-lg" id="btnGenerar">
                <i class="fas fa-check-circle me-2"></i>Generar Partida
            </button>
        </div>
        <!-- Columna izquierda 
        <div class="col-md-4"></div>
        Columna derecha: resultado 
        <div class="col-md-8" id="divReporte">
            <div class="card shadow-sm">
                <div class="card-body text-center"
                    style="background: #f8f9fa; min-height: 300px; display: flex; align-items: center; justify-content: center;">
                    <div>
                        <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aquí aparecerá la <strong>partida generada</strong></h5>
                        <p class="text-muted small">Una vez que completes los pasos de la izquierda, verás el resultado
                            en este espacio.</p>
                    </div>
                </div>
            </div>
        </div>
        -->
    </div>
</div>


<script>
    $("#tipoPartida").select2({
        dropdownParent: $('#modal-container'),
        placeholder: "Tipo de partida contable"
    });

    $("#cuentaContable").select2({
        dropdownParent: $('#modal-container'),
        placeholder: "Número de cuenta contable",
        ajax: {
            type: "POST",
            url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectContaCuentaById",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    busquedaSelect: params.term,
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });

    function reset() {
        $("#repetitivas").hide();
        $("#liquidacionCompras").hide();
        $("#retaceo").hide();
        $("#btnGenerar").hide();
        $("#pagoPost").hide();
    }


    $(document).ready(function() {
        reset();

        $("#tipoPartida").on("change", function() {
            switch ($(this).val()) {
                case "18":
                    reset();
                    $("#liquidacionCompras").show();
                    $("#btnGenerar").show();
                    break;
                case "100":
                    reset();
                    $("#repetitivas").show();
                    $("#btnGenerar").show();
                    break;
                case "101":
                    reset();
                    $("#retaceo").show();
                    $("#btnGenerar").show();
                    break;
                case "1":
                    reset();
                    $("#pagoPost").show();
                    $("#btnGenerar").show();
                    break;
                case "5":
                    reset();
                    $("#pagoPost").show();
                    $("#btnGenerar").show();
                    break;
                default:
                    reset();
                    break;
            }
        });
    });


    $("#frmModal").validate({
        submitHandler: function(form) {
            button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                $("#frmModal").serialize(),
                function(data) {
                    button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                    if (data.status === "success") {
                        const partidaInfo = {
                            partidaContableId: data.partidaContableId,
                            tipoPartidaId: data.tipoPartidaId
                        };
                        mensaje_do_aceptar(
                            "Operación completada:",
                            "La partida contable se generó correctamente.",
                            "success",
                            function() {
                                if (data.partidaContableId) {
                                    changePage(
                                        `<?php echo $_SESSION["currentRoute"]; ?>`,
                                        `general-partida`,
                                        `data=${encodeURIComponent(JSON.stringify(partidaInfo))}`
                                    );
                                }
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
        }
    });
</script>