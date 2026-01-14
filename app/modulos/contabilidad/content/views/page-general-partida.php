<?php
require_once ('../../../../../libraries/includes/logic/mgc/datos94.php');
require_once ('../../../../../libraries/includes/logic/functions/funciones-conta.php');
@session_start();

$jsonString = $_POST['data'] ?? '{}';
$data = json_decode($jsonString, true);
$partidaContableId = $data['partidaContableId'] ?? 0;
$tipoPartidaId = $data['tipoPartidaId'] ?? 0;

$dataPartida = null;

$jsonFinalizar = array(
    'typeOperation' => 'update',
    'operation' => 'finalizar-partida-contable',
    'partidaContableId' => $partidaContableId
);

$funcionFinalizar = htmlspecialchars(json_encode($jsonFinalizar));

$centrosCosto = $cloud->rows('
    SELECT centroCostoId,nombreCentroCosto
    FROM conta_centros_costo
    WHERE flgDelete = 0
    ORDER BY centroCostoId
');

if ($partidaContableId > 0) {
    $dataPartida = $cloud->row('
    SELECT partidaContableId,estadoPartidaContable,tipoPartidaId,partidaContaPeriodoId,descripcionPartida,fechaPartida,numPartida,flgFilter
    FROM conta_partidas_contables
    WHERE partidaContableId = ? AND flgDelete = ?', [$partidaContableId, 0]);

    $jsonImprimir = array(
        'tituloModal' => 'Reportes: Partida Contable - ' . str_pad($dataPartida->numPartida, 8, '0', STR_PAD_LEFT),
        'partidaContableId' => $partidaContableId
    );

    $funcionImprimir = htmlspecialchars(json_encode($jsonImprimir));

    $jsonCargarExel = array(
        'tituloModal' => 'Cargar al detalle de partida ',
        'partidaContableId' => $partidaContableId,
        'partidaContaPeriodoId' => $dataPartida->partidaContaPeriodoId
    );

    $funcionCargarExel = htmlspecialchars(json_encode($jsonCargarExel));
}

?>
<!-- Boton para regresar -->
<div class="row mb-4">
    <div class="col-6">
        <button type="button" class="btn btn-secondary ttip" onclick="asyncPage(151, 'submenu', '');">
            <i class="fas fa-chevron-circle-left"></i>
            Volver
            <span class="ttiptext"> Volver </span>
        </button>
    </div>
</div>

<!-- Formulario de encabezado -->
<form id="frmPartidaContable">
    <?php
    if ($partidaContableId > 0) {
        echo '<input type="hidden" id="typeOperation" name="typeOperation" value="update">
            <input type="hidden" id="operation" name="operation" value="partida-contable">
            <input type="hidden" id="partidaContableId" name="partidaContableId" value="' . $partidaContableId . '">
            <input type="hidden" id="partidaContableId" name="partidaContableId" value="' . $partidaContableId . '">
            <input type="hidden" name="tipoPartidas" value="' . $dataPartida->tipoPartidaId . '">';
    } else {
        echo '<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
            <input type="hidden" id="operation" name="operation" value="nueva-partida-contable">';
    }
    ?>
    <div class="row align-items-center mb-3">
        <!-- Título a la izquierda -->
        <div class="col-12 col-md-6">
            <h2 class="mb-3 mb-md-0">
                <?php
                if ($partidaContableId && $dataPartida != null) {
                    echo 'Partida Contable - ' . str_pad($dataPartida->numPartida, 8, '0', STR_PAD_LEFT);
                } else {
                    echo 'Nueva Partida Contable';
                }
                ?>
            </h2>
        </div>
        <div class="col-12 col-md-6 d-flex flex-column flex-md-row align-items-center justify-content-md-end gap-2">
            <div class="mb-1 card border shadow-sm w-100" style="max-width: 290px;">
                <div class="card-body d-flex align-items-center justify-content-left py-2 px-3">
                    <span class="mb-0 me-2 fw-bold">Estado de Partida:</span>
                    <?php
                    if ($partidaContableId > 0) {
                        if ($dataPartida->estadoPartidaContable == 'Pendiente') {
                            echo '
                        <div class="text-danger fw-bold ">
                            <i class="fas fa-exclamation-circle"></i> Pendiente
                        </div>';
                        } else {
                            echo '
                        <div class="text-success fw-bold ">
                            <i class="fas fa-check-circle"></i> Finalizada
                        </div>';
                        }
                    } else {
                        echo '
                        <div class="text-info fw-bold ">
                            <i class="fas fa-info-circle"></i> Nueva
                        </div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="form-select-control">
                <select class="form-select" value="<?= $dataPartida ? $dataPartida->partidaContaPeriodoId : '' ?>"
                    <?= $partidaContableId > 0 ? 'readonly' : '' ?> <?= $partidaContableId > 0 ? 'disabled' : '' ?>
                    id="tipoPartidas" name="tipoPartidas" style="width:100%;" required>
                    <option></option>
                    <?php
                    $valorSeleccionado = $dataPartida ? $dataPartida->tipoPartidaId : null;

                    $dataTipoPartida = $cloud->rows('
                        SELECT tipoPartidaId, descripcionPartida 
                        FROM cat_tipo_partida_contable
                        ');

                    foreach ($dataTipoPartida as $tipo) {
                        $selected = ($tipo->tipoPartidaId == $valorSeleccionado) ? 'selected' : '';
                        echo "<option value='$tipo->tipoPartidaId' $selected>$tipo->descripcionPartida</option>";
                    }
                    ?>
                </select>

            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="form-select-control">
                <select class="form-select" style="width:100%;" <?= $partidaContableId > 0 ? 'disabled' : '' ?>
                    value="<?= $dataPartida ? $dataPartida->tipoPartidaId : '' ?>" id="periodoPartidas"
                    name="periodoPartidas" required>
                    <option></option>
                    <?php
                    $dataPeriodos = $cloud->rows("
                    SELECT partidaContaPeriodoId,mes,anio,CONCAT(mesNombre, ' ', anio) AS periodoNombre
                    FROM conta_partidas_contables_periodos 
                    WHERE (anio < YEAR(CURDATE())) OR (anio = YEAR(CURDATE()) AND mes <= MONTH(CURDATE()))
                    AND flgDelete = ? AND estadoPeriodoPartidas = ?
                    ORDER BY  anio DESC,mes DESC", [0, 'Activo']);

                    $valorSeleccionado = $dataPartida ? $dataPartida->partidaContaPeriodoId : null;

                    foreach ($dataPeriodos as $periodo) {
                        $selected = ($periodo->partidaContaPeriodoId == $valorSeleccionado) ? 'selected' : '';
                        echo '<option data-anio="' . $periodo->anio . '" data-mes="' . $periodo->mes . '" value="' . $periodo->partidaContaPeriodoId . '" ' . $selected . '>' . $periodo->periodoNombre . '</option>';
                    }
                    ?>
                </select>

            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="form-outline">
                <i class="fas fa-list-ol trailing"></i>
                <input type="text" id="numPartidaDisplay" class="form-control" readonly value="<?php if ($dataPartida) {
    echo (str_pad($dataPartida->numPartida, 8, '0', STR_PAD_LEFT));
} ?>" />
                <input type="hidden" id="numPartida" name="numPartida" value="<?php if ($dataPartida) {
    echo ($dataPartida->numPartida);
} ?>" />
                <label class="form-label" for="numPartida">Número de partida</label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="form-outline">
                <input type="date" id="fechaPartida" class="form-control" name="fechaPartida" required
                    value="<?= $dataPartida ? $dataPartida->fechaPartida : '' ?>"
                    <?= $partidaContableId > 0 ? 'readonly' : '' ?>>
                <label class="form-label" for="fechaPartida">Fecha de partida</label>
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-outline">
                <i class="fas fa-edit trailing"></i>
                <input <?php if (isset($dataPartida->estadoPartidaContable)) {
    if ($dataPartida->estadoPartidaContable == 'Finalizada') {
        echo 'readonly';
    }
} ?> type="text" id="descripcionPartida" class="form-control" name="descripcionPartida" required value="<?php if ($dataPartida) {
    echo ($dataPartida->descripcionPartida);
} ?>">
                <label class="form-label" for="descripcionPartida">Concepto general</label>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap flex-column flex-md-row justify-content-md-end align-items-center gap-2">
                <!-- Botón Actualizar / Generar -->
                <?php if ($partidaContableId > 0 && $dataPartida->estadoPartidaContable == 'Pendiente'): ?>
                <button disabled type="submit" class="btn btn-primary ttip">
                    <i class="fas fa-sync-alt"></i> Actualizar
                    <span class="ttiptext">Actualizar información</span>
                </button>
                <?php elseif (!$partidaContableId): ?>
                <button type="submit" class="btn btn-primary ttip">
                    <i class="fas fa-plus-circle"></i> Generar
                    <span class="ttiptext">Generar</span>
                </button>
                <?php endif; ?>

                <!-- Botón Finalizar -->
                <?php if ($partidaContableId > 0 && $dataPartida->estadoPartidaContable == 'Pendiente'): ?>
                <button type="button" disabled id="btnFinalizar" onclick="finalizarPartida(<?= $funcionFinalizar ?>)"
                    class="btn btn-primary ttip">
                    <i class="fas fa-check-circle me-1"></i> Finalizar
                    <span class="ttiptext">Finalizar Partida</span>
                </button>
                <?php endif; ?>

                <!-- Botón Imprimir -->
                <?php if ($partidaContableId > 0): ?>
                <button type="button" id="btnImprimir" onclick="modalImprimirPartida(<?= $funcionImprimir ?>)"
                    class="btn btn-primary ttip">
                    <i class="fas fa-print me-1"></i> Imprimir
                    <span class="ttiptext">Imprimir</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>
<?php if ($partidaContableId > 0 && $dataPartida) { ?>
<hr>
<h5>Detalle de partida contable</h5>
<!-- Formulario de detalle -->
<form id="frmDetallePartida" class="mb-3">
    <input type="hidden" id="typeOperationDet" name="typeOperation" value="insert">
    <input type="hidden" id="operationDet" name="operation" value="nueva-partida-contable-detalle">
    <input type="hidden" id="partidaContableDetalleId" name="partidaContableDetalleId" value="0">
    <input type="hidden" name="partidaContableId" value="<?php echo $partidaContableId ?>">
    <input type="hidden" id="tipoDTEId" name="tipoDTEId" value="0">
    <?php if ($partidaContableId > 0 && $dataPartida) {
        echo ' <input type="hidden" name="partidaContaPeriodoId" value="' . $dataPartida->partidaContaPeriodoId . '">';
    }; ?>
    <?php
    $readonlyOrDisabled = (isset($dataPartida->estadoPartidaContable) && $partidaContableId > 0 && $dataPartida->estadoPartidaContable != 'Pendiente') ? true : false;
    ?>

    <div class="row g-3">
        <div class="col-md-12">
            <div class="row align-items-end">
                <!-- Cuenta -->
                <div class="col-md-3">
                    <div class="form-select-control">
                        <select class="form-select" style="width:100%;" <?= $readonlyOrDisabled ? 'disabled' : '' ?>
                            required id="cuentaId" name="cuentaId">
                            <option>Numero de cuenta</option>
                        </select>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="col-md-3">
                    <div class="form-outline">
                        <input type="text" id="descripcion" name="descripcion" class="form-control"
                            placeholder="Descripción" <?= $readonlyOrDisabled ? 'readonly' : '' ?> tabindex="2">
                        <label for="descripcion" class="form-label">Descripción</label>
                    </div>
                </div>

                <!-- Cargos -->
                <div class="col-md-3">
                    <div class="form-outline">
                        <input type="text" id="cargos" name="cargos" class="form-control" placeholder="0.00"
                            <?= $readonlyOrDisabled ? 'readonly' : '' ?> tabindex="3">
                        <label for="cargos" class="form-label">Cargos</label>
                    </div>
                </div>

                <!-- Abonos -->
                <div class="col-md-3">
                    <div class="form-outline">
                        <input type="text" id="abonos" name="abonos" class="form-control" placeholder="0.00"
                            <?= $readonlyOrDisabled ? 'readonly' : '' ?> tabindex="4">
                        <label for="abonos" class="form-label">Abonos</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row ">
                <div class="col-md-6">
                    <div class="form-select-control">
                        <select class="form-select" style="width:100%;" id="documentoId" name="documentoId"
                            <?= $readonlyOrDisabled ? 'disabled' : '' ?> tabindex="5">
                            <option></option>
                        </select>
                    </div>
                </div>

                <!-- Subcentro de Costo 
                <div class="col-md-6">
                    <div class="form-select-control">
                        <select class="form-select" style="width:100%;" id="subCentroCostoId" name="subCentroCostoId"
                            <?= $readonlyOrDisabled ? 'disabled' : '' ?> tabindex="6">
                            <option></option>
                        </select>
                    </div>
                </div>-->
            </div>

            <!-- Botones -->
            <?php if ($partidaContableId > 0 && $dataPartida->estadoPartidaContable == 'Pendiente'): ?>
            <div class="row mt-3">
                <div class="col">
                    <div class="d-flex gap-2">
                        <button id="btnSumitDet" type="submit" class="btn btn-primary btn-sm ttip" tabindex="7">
                            <i class="fas fa-plus-circle"></i> Agregar detalle
                            <span class="ttiptext">Generar</span>
                        </button>
                        <a onclick="clearAllDet();" class="btn btn-warning btn-sm ttip">
                            <i class="fas fa-eraser"></i> Limpiar Campos
                            <span class="ttiptext">Limpiar Campos</span>
                        </a>
                        <a onclick="modalCargarExel(<?= $funcionCargarExel ?>);" class="btn btn-success btn-sm ttip">
                            <i class="fas fa-file-excel"></i> Agregar por archivo
                            <span class="ttiptext">Agregar por archivo</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>
<?php
    $labelFiltro = '';
    if ($partidaContableId && $dataPartida != null) {
        if ($dataPartida->flgFilter != 0) {
            switch ($dataPartida->tipoPartidaId) {
                case 10:
                case 27:
                case 3:
                    $labelFiltro = 'Fecha de Pago';
                    break;
                default:
                    $labelFiltro = 'Filtro';
                    break;
            }
        }
    }

?>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6 mb-2 mb-md-0">
                <h6 class="fw-bold mb-1">Balance de Partida:</h6>
                <div id="balanceEstado" class="fs-6 text-secondary">—</div>
            </div>

            <?php if ($partidaContableId && $dataPartida != null && $dataPartida->flgFilter != 0): ?>
            <div class="col-md-2 mb-2 mb-md-0 text-md-center text-start">
                <h6 class="fw-bold mb-1">Filtro:</h6>
                <div id="totalFiltro" class="fs-6 text-secondary">—</div>
            </div>
            <?php endif; ?>

            <div class="col-md-2 mb-2 mb-md-0 text-md-end text-start">
                <h6 class="fw-bold mb-1">Total Cargos:</h6>
                <div id="totalCargos" class="fs-5">$4.00</div>
            </div>

            <div class="col-md-2 text-md-end text-start">
                <h6 class="fw-bold mb-1">Total Abonos:</h6>
                <div id="totalAbonos" class="fs-5">$4.00</div>
            </div>
        </div>
    </div>
</div>
<!-- Tabla de detalle -->
<div class="table-responsive">
    <table id="tblPartidasDetalle" class="table table-hover" style="width: 100%;">
        <thead>
            <tr>
                <th>#</th>
                <th>#</th>
                <th>Número de cuenta</th>
                <th>Centro de Costos</th>
                <th>Documento</th>
                <th>Descripción</th>
                <?php if ($partidaContableId && $dataPartida != null) {
                    if ($dataPartida->flgFilter != 0): ?>
                <th><?= $labelFiltro ?></th>
                <?php endif;
                } ?>
                <th>Cargo</th>
                <th>Abono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<?php } ?>

<?php if (in_array(365, $_SESSION['arrayPermisos'])) { ?>
<div id="balanceEstado" class="mt-2"></div>
<!-- Card técnica para desarrollador o contabilidad -->
<div id="detalleTecnico"></div>
<?php } ?>
<script>
function modalImprimirPartida(frmData) {
    loadModal(
        "modal-container", {
            modalDev: "-1",
            modalSize: 'fullscreen',
            modalTitle: frmData.tituloModal,
            modalForm: 'imprimirPartida',
            formData: frmData,
            buttonCancelShow: true,
            buttonCancelText: 'Cerrar'
        }
    );
}
// 1078
function traladarDetalle(frmData) {
    loadModal(
        "modal-container", {
            modalDev: "-1",
            modalSize: 'md',
            modalTitle: "Trasladar partida a partida",
            modalForm: 'trasladosPartidaToPartida',
            formData: frmData,
            buttonAcceptShow: true,
            buttonAcceptText: 'Guardar',
            buttonAcceptIcon: 'success',
            buttonCancelShow: true,
            buttonCancelText: 'Cerrar'
        }
    );
}

function modalCargarExel(frmData) {
    loadModal(
        "modal-container", {
            modalDev: "-1",
            modalSize: 'lg',
            modalTitle: frmData.tituloModal,
            modalForm: 'cargarPartidaContable',
            formData: frmData,
            buttonAcceptShow: true,
            buttonAcceptText: 'Guardar',
            buttonAcceptIcon: 'success',
            buttonCancelShow: true,
            buttonCancelText: 'Cerrar'
        }
    );
}

//TODOS: Funciones generales

function limitarTexto(texto, max = 25) {
    return texto.length > max ? texto.substring(0, max) + "..." : texto;
}

function clearAllDet() {

    $("#descripcion").val(null).trigger('change');
    $("#documentoId").val(null).trigger('change');
    $("#tipoDTEId").val(0).trigger('change');
    $("#abonos").val(null).trigger('change');
    $("#cargos").val(null).trigger('change');
    calculoDeBalance();
    changeUpdateDet(false);
    $("#cuentaId").val(null).trigger('change').focus();
}




function changeUpdateDet(value) {
    if (value) {
        $("#typeOperationDet").val("update").trigger('change')
        $("#operationDet").val("partida-contable-detalle").trigger('change')
        $("#btnSumitDet").html(
            '<i class="fas fa-sync-alt"></i> Modificar detalle<span class="ttiptext">Modificar</span>')
    } else {
        $("#typeOperationDet").val("insert").trigger('change')
        $("#operationDet").val("nueva-partida-contable-detalle").trigger('change')
        $("#btnSumitDet").html(
            '<i class="fas fa-plus-circle"></i> Agregar detalle<span class="ttiptext">Generar</span>')
        $('#tblPartidasDetalle tbody tr').removeClass('table-info');
    }
}

function formatCurrency(value) {
    let number = parseFloat(value.replace(/[^\d.-]/g, ''));
    if (isNaN(number)) return '';
    return new Intl.NumberFormat('es-SV', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    }).format(number);
}

function unformatCurrency(value) {
    return value.replace(/[^0-9.-]+/g, '');
}

// Al perder el foco, aplica formato
$('#cargos, #abonos').on('blur', function() {
    let val = $(this).val();
    $(this).val(formatCurrency(val));
});

// Al enviar el form, limpia el formato para enviar solo el número
$('form').on('submit', function() {

});

//TODOS: Funciones de balance

function calculoDeBalance() {
    asyncData(
        "<?php echo $_SESSION['currentRoute']; ?>content/divs/calculoBalancePartidaContable", {
            partidaContableId: <?php echo $partidaContableId; ?>
        },
        function(response) {
            if (response && response.status === 'ok') {
                // Card general (usuario común)
                $("#totalCargos").html(`$${Number(response.totalCargos).toFixed(2)}`);
                $("#totalAbonos").html(`$${Number(response.totalAbonos).toFixed(2)}`);
                mostrarBalance(response.balance);

                // Card técnica (detalle exacto)
                mostrarDetalleTecnico(response);
            } else {
                mensaje("Aviso:", response?.message || "No se pudo calcular el balance", "warning");
            }
        }
    );
}

function mostrarBalance(balance) {
    const estado = document.getElementById("balanceEstado");
    const tolerancia = <?= getToteranciaPartidas(); ?> //! Tolerancia de error aceptable

    let clase = '';
    let icono = '';
    let texto = '';

    if (Math.abs(balance) <= tolerancia) {
        clase = 'text-secondary';
        icono = '<i class="fas fa-equals"></i>';
        texto = `Balance
    en equilibrio`;
        $("#btnFinalizar").attr('disabled', false);
    } else if (balance > 0) {
        clase = 'text-success';
        icono = '<i class="fas fa-arrow-up"></i>';
        texto = `Balance positivo: $${balance.toFixed(2)}`;
        $("#btnFinalizar").attr('disabled', true);
    } else {
        clase = 'text-danger';
        icono = '<i class="fas fa-arrow-down"></i>';
        texto = `Balance negativo: $${Math.abs(balance).toFixed(2)}`;
        $("#btnFinalizar").attr('disabled', true);
    }

    estado.innerHTML = `
    <div class="${clase} small fw-bold">
        ${icono} ${texto}
    </div>
    `;
}

function mostrarDetalleTecnico(data) {
    const tolerancia = <?= getToteranciaPartidas(); ?>;
    const diferencia = Math.abs(data.totalCargos - data.totalAbonos);
    const dentroTolerancia = diferencia <= tolerancia;

    let botonForzar = '';
    if (diferencia > 0) {
        botonForzar = `
                <button id="btnForzarCuadre" class="btn btn-sm btn-warning mt-2">
                    <i class="fas fa-tools"></i> Forzar cuadrar esta partida
                </button>
            `;
    }

    const detalle = `
    <div class="card border-info mt-3">
        <div class="card-header bg-info text-white py-1 px-2 d-flex justify-content-between align-items-center">
            <span><i class="fas fa-microscope"></i> Detalle técnico del cálculo</span>
            ${botonForzar}
        </div>
        <div class="card-body small text-monospace">
            <p><b>Total Cargos Detalle:</b> ${data.totalCargos}</p>
            <p><b>Total Abonos Detalle:</b> ${data.totalAbonos}</p>
            <p><b>Cargo Encabezado:</b> ${data.cargoPartida}</p>
            <p><b>Abono Encabezado:</b> ${data.abonoPartida}</p>
            <p><b>Diferencia exacta (Δ):</b> ${diferencia}</p>
            <p><b>Tolerancia aplicada:</b> ±${tolerancia}</p>
            <p><b>Estado técnico:</b> ${data.descuadrada ? 'Descuadrada' : 'Cuadrada'}</p>
        </div>
    </div>`;

    $("#detalleTecnico").html(detalle);

    // Acción del botón con asyncData
    $("#btnForzarCuadre").on("click", function() {
        asyncData(
            '<?php echo $_SESSION['currentRoute']; ?>/transaction/operation', {
                typeOperation: 'update',
                operation: 'cuadratura-partidas',
                partidaContableId: data.partidaContableId
            },
            function(response) {
                if (response.status === "success") {

                    mensaje("Éxito", response.message, "success");
                    //calculoDeBalance(); // recargar balance visual
                } else {
                    mensaje("Aviso", response.message || "No se pudo cuadrar la partida.", "warning");
                }
            }
        );
    });
}



//TODOS: Metodo para buscar Documento

function buscarFacturas(valor) {
    valor = valor.trim();
    if (valor.length > 0) {
        asyncData(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getFacturasClientes", {
                facturaId: valor
            },
            function(data) {
                let opciones = `<option value="">Seleccione una opción</option>`;
                let hayResultados = Array.isArray(data) && data.length > 0;

                if (hayResultados) {
                    data.forEach(factura => {
                        opciones += `
    <option value="${factura.facturaId}">
        ${factura.facturaId} - ${limitarTexto(factura.nombreCliente)}
    </option>`;
                    });
                    $("#facturaSelect").prop("disabled", false).html(opciones);
                } else {
                    $("#facturaSelect").html('<option value="">Sin resultados</option>').prop("disabled", true);
                }
            }
        );
    } else {
        $("#facturaSelect").html('<option value="">Sin resultados</option>').prop("disabled", true);
    }
}

var typingTimer;

// Disparar por pausa
$("#numDoc").on("input", function() {
    clearTimeout(typingTimer);
    const valor = $(this).val();
    typingTimer = setTimeout(() => buscarFacturas(valor), 600);
});

// Disparar por Enter
$("#numDoc").on("keypress", function(e) {
    if (e.which === 13) {
        e.preventDefault();
        clearTimeout(typingTimer);
        buscarFacturas($(this).val());
    }
});

$("#periodoPartidas").on("change", function() {
    const periodoId = $(this).val();
    const texto = $('#tipoPartidas option:selected').text();
    const tipoDocumentoId = $("#tipoPartidas").val();
    var selected = $(this).find('option:selected');
    var anio = selected.data('anio');
    var mes = selected.data('mes');
    mes = mes.toString().padStart(2, '0');
    if (periodoId && anio && mes) {

        const ultimoDia = new Date(anio, mes, 0).getDate();
        $("#fechaPartida").attr({
            "min": `${anio}-${mes}-01`,
            "max": `${anio}-${mes}-${ultimoDia}`
        });
    }

    if (periodoId && tipoDocumentoId) {
        $.post("<?= $_SESSION['currentRoute']; ?>content/divs/getNumPartida", {
            partidaContaPeriodoId: periodoId,
            tipoPartidaId: tipoDocumentoId
        }, function(nuevoNum) {
            $("#numPartida").val(nuevoNum);
            $("#numPartidaDisplay").val(nuevoNum);
            $("#descripcionPartida").val(`PARA CONTABILIZAR ${texto}`).trigger('change').addClass(
                'active');
        });
    }
});

$("#tipoPartidas").on("change", function() {
    const tipoDocumentoId = $(this).val();
    const periodoId = $("#periodoPartidas").val();
    const texto = $('#tipoPartidas option:selected').text();

    if (periodoId && tipoDocumentoId) {
        $.post("<?= $_SESSION['currentRoute']; ?>content/divs/getNumPartida", {
            partidaContaPeriodoId: periodoId,
            tipoPartidaId: tipoDocumentoId
        }, function(nuevoNum) {
            $("#numPartida").val(nuevoNum);
            $("#numPartidaDisplay").val(nuevoNum).addClass('active');
            $("#descripcionPartida").val(`PARA CONTABILIZAR ${texto}`).trigger('change').addClass(
                'active');
        });
    }
});

$("#cargos").on("change", function() {
    $("#abonos").val(null)
});

$("#abonos").on("change", function() {
    $("#cargos").val(null)
});

$(`#documentoId`).on('select2:select', function(e) {
    let data = e.params.data;
    if (data.tipoDTEId == 12) {
        $("#tipoDTEId").val(5);
    } else {
        $("#tipoDTEId").val(4);
    }
});

$("#cuentaId").on("change", function() {
    if ($("#typeOperationDet").val() === "insert") {
        const id = $(this).val();
        const texto = $('#cuentaId option:selected').text();
        const soloNombre = texto.replace(/^\s*\d+\s*-\s*/, '');
        const tipoPartidaId = <?= $tipoPartidaId ?>;

        let prefijoDesc = "";

        /*switch (tipoPartidaId) {
            case 1: // VENTAS CREDITO
                prefijoDesc = "VENTA A ";
                break;
            case 2: // REMESAS
                prefijoDesc = "REMESA DEL DÍA ";
                break;
            case 3: // GASTOS
                prefijoDesc = "GASTO POR ";
                break;
            case 5: // VENTAS CONTADO
                prefijoDesc = "VENTA A ";
                break;
            case 8: // INGRESO 
                prefijoDesc = "INGRESO POR ";
                break;
            case 9: // EGRESO
                prefijoDesc = "EGRESO POR ";
                break;
            case 10: // DIARIO
                prefijoDesc = "REGISTRO DIARIO ";
                break;
            default:
                prefijoDesc = "";
                break;
        }

        $("#descripcion").val(`${prefijoDesc}${soloNombre.trim()}`).trigger('change').addClass('active');*/
    }
});

//TODOS: Codigo Eliminar detalle

function deleteDetalle(frmData) {
    event.stopPropagation();
    mensaje_confirmacion(
        `¿Está seguro de que desea eliminar el registro?`,
        `Esta acción eliminará el registro de forma permanente.`,
        `warning`,
        (param) => {
            asyncData(
                '<?php echo $_SESSION['currentRoute']; ?>/transaction/operation',
                frmData,
                (data) => {
                    if (data == "success") {
                        $(`#tblPartidasDetalle`).DataTable().ajax.reload(null, false);

                    } else {
                        mensaje(
                            "Aviso:",
                            data,
                            "warnig"
                        );
                        $(`#tblPartidasDetalle`).DataTable().ajax.reload(null, false);

                    }
                }
            );
        },
        `Eliminar`,
        `Cancelar`
    )
}


//TODOS: Codigo para duplicar item

function duplicateItem(frmData) {
    asyncData(
        '<?php echo $_SESSION['currentRoute']; ?>/transaction/operation',
        frmData,
        (data) => {
            if (data.status == "success") {
                $(`#tblPartidasDetalle`).DataTable().ajax.reload(null, false);

            } else {
                mensaje("Aviso:", data, "warning");

            }
        }
    );
}

//TODOS: Finalizar partida

function finalizarPartida(frmData) {
    mensaje_confirmacion(
        `¿Está seguro de guardar esta partida contable?`,
        `Importante: una vez guardada, la partida contable no podrá ser modificada.`,
        `warning`,
        (param) => {
            asyncData(
                '<?php echo $_SESSION['currentRoute']; ?>/transaction/operation',
                frmData,
                (data) => {
                    if (data == "success") {
                        mensaje_do_aceptar(
                            `Operación completada`,
                            ``,
                            `success`,
                            () => {
                                let jsonDetalle = {
                                    partidaContableId: <?php echo $partidaContableId ?>,
                                    tipoPartidaId: <?php echo $tipoPartidaId ?>
                                };
                                changePage(`<?php echo $_SESSION['currentRoute']; ?>`, `general-partida`,
                                    `data=${JSON.stringify(jsonDetalle)}`);
                            });
                    } else {
                        mensaje(
                            "Aviso:",
                            data,
                            "warnig"
                        );
                    }
                }
            );
        },
        `Aceptar`,
        `Cancelar`
    )
}

//TODOS: Codigo para editar el detalle

$('#tblPartidasDetalle tbody').on('click', 'td', function() {
    const table = $('#tblPartidasDetalle').DataTable();
    var $row = $(this).closest('tr');

    $('#tblPartidasDetalle tbody tr').removeClass('table-info');
    $row.addClass('table-info');

    var rowData = table.row($row).data();

    asyncData(
        "<?php echo $_SESSION['currentRoute']; ?>content/divs/getPartidaContableDetalle", {
            partidaContableDetalleId: rowData[0]
        },
        function(data) {

            if (data) {
                changeUpdateDet(true);
                if (data.cuentaContaId) {
                    let newOption = new Option(
                        data.numeroCuenta + " - " + data.descripcionCuenta,
                        data.cuentaContaId,
                        true,
                        true
                    );
                    $("#cuentaId").prop("disabled", false).append(newOption);
                    $("#cuentaId").val(data.cuentaContaId).trigger('change');
                }

                $("#partidaContableDetalleId").val(data.partidaContableDetalleId);


                if (data.documentoId) {
                    let numDoc;

                    if (data.tipoDTEId == 12) {
                        numDoc = data.numFactura || "";
                    } else {
                        numDoc = data.numeroControl || data.numFactura || "";
                    }

                    const optionText = `${numDoc} - ${data.nombreProveedor || "N/A"}`;
                    const newOptionD = new Option(optionText, data.documentoId, true, true);
                    const $docSelect = $("#documentoId");
                    $docSelect.append(newOptionD).val(data.documentoId).trigger("change");
                }

                $("#abonos").val(parseFloat(data.abonos)).addClass('active');
                $("#cargos").val(parseFloat(data.cargos)).addClass('active');
                $("#descripcion").val(data.descripcionPartidaDetalle).addClass('active').trigger("change");
            }
        }
    );
});

$(document).ready(function() {

    $('#modal-container').modal("hide")

    $("#btnFinalizar").attr('disabled', true)

    $("#periodoPartidas").select2({
        placeholder: "Periodo"
    });

    $("#cuentaId").select2({
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
    }).on('select2:open', function() {
        // Espera a que Select2 inserte su input interno y luego le pone el tabindex
        setTimeout(() => {
            const input = document.querySelector(
                '.select2-container--open .select2-search__field');
            if (input) input.setAttribute('tabindex', '1');
        }, 0);
    });

    $("#documentoId").select2({
        placeholder: "Numero de control o proveedor",
        ajax: {
            type: "POST",
            url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectCompras",
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

    $("#tipoPartidas").select2({
        placeholder: "Tipo de partida"
    });

    //TODOS: Calculo de balance
    $('#tblPartidasDetalle thead tr')
        .clone(true)
        .addClass('filters')
        .appendTo('#tblPartidasDetalle thead');
    //TODOS: llenado de tabla


    let isSearching = false;

    let tblPartidaDet = $('#tblPartidasDetalle').DataTable({
        keys: true,
        "dom": '<"d-flex justify-content-end"B> rt<"row align-items-center"<"col-md-6"l><"col-md-6 text-end"p>>',
        "buttons": [{
            extend: 'excelHtml5',
            text: '<i class="fas fa-file-excel"></i> descargar Excel',
            className: 'btn btn-success',
            title: function() {
                return `PARTIDA CONTABLE`;
            },
            exportOptions: {
                columns: [2, 4, 5, 6, 7, 8]
            }
        }],
        "ajax": {
            "method": "POST",
            "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePartidasContablesDetalle",
            "data": {
                "partidaContableId": <?php echo $partidaContableId; ?>
            }
        },
        "autoWidth": false,
        "columns": [{
                visible: false
            },
            {
                "width": "6%"
            },
            null,
            {
                visible: false
            },
            null,
            {
                "width": "25%"
            }
            <?php if (isset($dataPartida->flgFilter) && $dataPartida->flgFilter == 1) echo ', null'; ?>,
            null,
            null,
            null
        ],
        "columnDefs": [{
                "targets": 0,
                "visible": false,
                "searchable": false
            },
            {
                "orderable": [1, 2]
            }
        ],
        "language": {
            "url": "../libraries/packages/js/spanish_dt.json"
        },
        "initComplete": function() {
            let api = this.api();

            api.columns().eq(0).each(function(colIdx) {
                let cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                let title = $(cell).text();

                <?php
                $cols = (isset($dataPartida->flgFilter) && $dataPartida->flgFilter == 1)
                    ? '[2, 3, 4, 5, 6, 7, 8]'
                    : '[2, 3, 4, 5, 6, 7]';
                ?>

                if (<?= $cols ?>.includes(colIdx)) {
                    $(cell).html(
                        '<input type="text" class="form-control form-control-sm" placeholder="Buscar" style="width:100%;"/>'
                    );

                    let typingTimer;
                    const typingDelay = 500;

                    $('input', cell).off('keyup change').on('keyup change', function() {
                        clearTimeout(typingTimer);
                        const input = this;

                        typingTimer = setTimeout(function() {

                            isSearching = true;

                            if (api.column(colIdx).search() !== input
                                .value) {
                                api.column(colIdx).search(input.value).draw(
                                    false);
                            }
                            setTimeout(() => {
                                isSearching = false;
                            }, 500);

                        }, typingDelay);
                    });
                } else {
                    $(cell).html('');
                }
            });

            if (tblPartidaDet.data().count() === 0) {
                $("#btnFinalizar").attr('disabled', true);
            }

            // Solo calcular balance en carga inicial
            calculoDeBalance();
        }
    });


    $('#tblPartidasDetalle tfoot').insertBefore('#tblPartidasDetalle thead');

    // Ejecutar balance solo si no es búsqueda
    tblPartidaDet.on('draw', function() {
        if (tblPartidaDet.data().count() === 0) {
            $("#btnFinalizar").attr('disabled', true);
        } else {
            $("#btnFinalizar").attr('disabled', false);
        }


        // Evita cálculo mientras el usuario filtra
        if (!isSearching) {
            calculoDeBalance();
        }
    });


    let msjHeader = ''
    let msjBoby = ''

    if ($("#typeOperation").val() === "update") {
        msjHeader = "Modificación de partida";
        msjBoby = "¿Está seguro de que desea modificar esta partida contable?";
    } else {
        msjHeader = "Creación de partida";
        msjBoby = "¿Está seguro de que desea crear una nueva partida contable?";
    }


    //TODOS: envio de formulario general
    $("#frmPartidaContable").validate({
        submitHandler: function(form) {
            mensaje_confirmacion(
                msjBoby,
                msjHeader,
                `warning`,
                function(param) {
                    button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...",
                        "disabled");
                    asyncData(
                        "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                        $("#frmPartidaContable").serialize(),
                        function(data) {
                            button_icons("btnModalAccept", "fas fa-save", "Guardar",
                                "enabled");
                            if (data.status == "success") {
                                mensaje_do_aceptar(
                                    "Operación completada",
                                    'La partida contable se creó con éxito.',
                                    "success",
                                    function() {
                                        let jsonDetalle = {
                                            partidaContableId: data
                                                .partidaContableId,
                                            tipoPartidaId: data.tipoPartidaId
                                        };
                                        changePage(
                                            `<?php echo $_SESSION['currentRoute']; ?>`,
                                            `general-partida`,
                                            `data=${JSON.stringify(jsonDetalle)}`);
                                    }
                                );
                            } else {
                                mensaje("Aviso:", data, "warning");
                            }
                        }
                    );
                },
                'Sí, Crear',
                `Cancelar`
            );
        }
    });

    // Permitir enviar con Enter desde cualquier campo
    $("#frmDetallePartida input, #frmDetallePartida select").on("keypress", function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $("#frmDetallePartida").submit();
        }
    });


    //TODOS: envio de formulario detalle
    $("#frmDetallePartida").validate({
        submitHandler: function(form) {
            let abonosVal = unformatCurrency($("#abonos").val());
            let cargosVal = unformatCurrency($("#cargos").val());

            let abonos = parseFloat(abonosVal) || 0;
            let cargos = parseFloat(cargosVal) || 0;

            $("#abonos").val(abonosVal);
            $("#cargos").val(cargosVal)
            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                $(form).serialize(),
                function(data) {
                    button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                    if (data.status == "success") {
                        tblPartidaDet.ajax.reload();
                        clearAllDet();
                        setTimeout(() => {
                            $('#cuentaId').select2('open');
                        }, 500);
                    } else {
                        mensaje("Aviso:", data, "warning");
                    }
                }
            );

        }
    });
});
</script>