<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$cotizacionId = isset($_POST['cotizacionId']) ? (int) $_POST['cotizacionId'] : 0;
$cot = null;

if($cotizacionId > 0) {
    $cot = $cloud->row(
        "SELECT
            c.*,
            s.sucursal,
            cl.nombreCliente,
            cl.nombreComercialCliente,
            ub.nombreClienteUbicacion,
            ub.direccionClienteUbicacion,
            con.contactoCliente,
            tc.tipoContacto
        FROM fel_cotizacion c
        LEFT JOIN cat_sucursales s ON s.sucursalId = c.sucursalId
        LEFT JOIN fel_clientes cl ON cl.clienteId = c.clienteId
        LEFT JOIN fel_clientes_ubicaciones ub ON ub.clienteUbicacionId = c.clienteUbicacionId
        LEFT JOIN fel_clientes_contactos con ON con.clienteContactoId = c.clienteContactoId
        LEFT JOIN cat_tipos_contacto tc ON tc.tipoContactoId = con.tipoContactoId
        WHERE c.cotizacionId = ? AND c.flgDelete = 0",
        [$cotizacionId]
    );
}

$esNueva = ($cotizacionId <= 0);
$estado = $cot->estadoCotizacion ?? 'Borrador';
$fechaEmision = $cot->fechaEmision ?? date('Y-m-d');
$fechaVencimiento = $cot->fechaVencimiento ?? '';
$observaciones = $cot->observaciones ?? '';

$clienteNombre = '';
if($cot) {
    if(((int)($cot->clienteId ?? 0)) === 0) {
        $clienteNombre = $cot->nombreClienteSala ?? '';
    } else {
        $clienteNombre = $cot->nombreCliente;
        if($clienteNombre == '' || is_null($clienteNombre)) {
            $clienteNombre = $cot->nombreComercialCliente;
        }
    }
}

$ubicacionText = '';
if($cot && !is_null($cot->clienteUbicacionId)) {
    $ubicacionText = $cot->nombreClienteUbicacion;
    if(!is_null($cot->direccionClienteUbicacion) && $cot->direccionClienteUbicacion !== '') {
        $ubicacionText .= ' | ' . $cot->direccionClienteUbicacion;
    }
}

$contactoText = '';
if($cot && !is_null($cot->clienteContactoId)) {
    $contactoText = ($cot->tipoContacto ? '(' . $cot->tipoContacto . ') ' : '') . ($cot->contactoCliente ?? '');
}
?>

<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-0"><?php echo $esNueva ? 'Nueva cotización' : ('Cotización #' . $cotizacionId); ?></h3>
            <?php if(!$esNueva) { ?>
                <small class="text-muted">Estado: <span id="lblEstadoCotizacion" class="fw-bold"><?php echo $estado; ?></span></small>
            <?php } ?>
        </div>
        <div class="text-end">
            <button type="button" class="btn btn-secondary" onclick="volverListadoCotizaciones()">
                <i class="fas fa-chevron-circle-left"></i> Volver
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <input type="hidden" id="cotizacionId" value="<?php echo $cotizacionId; ?>">

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sucursal</label>
                    <select id="sucursalId" class="form-control" style="width:100%" required></select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tipo de cliente</label>
                    <select id="tipoCliente" class="form-control" style="width:100%">
                        <option value="registrado" selected>Cliente registrado</option>
                        <option value="sala">Cliente en sala (sin registro)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3" id="divClienteRegistrado">
                    <label class="form-label">Cliente</label>
                    <select id="clienteId" class="form-control" style="width:100%"></select>
                </div>
                <div class="col-md-6 mb-3 d-none" id="divClienteSala">
                    <label class="form-label">Nombre (cliente en sala)</label>
                    <input type="text" id="nombreClienteSala" class="form-control" maxlength="150" placeholder="Nombre del cliente">
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Ubicación</label>
                    <select id="clienteUbicacionId" class="form-control" style="width:100%"></select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Contacto</label>
                    <select id="clienteContactoId" class="form-control" style="width:100%"></select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Fecha emisión</label>
                    <input type="date" id="fechaEmision" class="form-control" value="<?php echo $fechaEmision; ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Fecha vencimiento</label>
                    <input type="date" id="fechaVencimiento" class="form-control" value="<?php echo $fechaVencimiento; ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea id="observaciones" class="form-control" rows="3"><?php echo htmlspecialchars($observaciones); ?></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <?php if($esNueva) { ?>
                    <button type="button" class="btn btn-primary" onclick="crearCotizacion()">
                        <i class="fas fa-save"></i> Crear
                    </button>
                <?php } else { ?>
                    <button type="button" class="btn btn-primary" onclick="guardarCotizacion()">
                        <i class="fas fa-save"></i> Guardar cambios
                    </button>
                    <?php if($estado !== 'Anulada') { ?>
                        <button type="button" class="btn btn-success" onclick="emitirCotizacion()">
                            <i class="fas fa-check-circle"></i> Emitir
                        </button>
                        <button type="button" class="btn btn-danger" onclick="anularCotizacionActual()">
                            <i class="fas fa-ban"></i> Anular
                        </button>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php if(!$esNueva) { ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="fw-bold">Totales</h5>
                <div class="row">
                    <div class="col-md-3 mb-2"><b>SubTotal:</b> <span id="tSubTotal">0.00</span></div>
                    <div class="col-md-3 mb-2"><b>Descuento:</b> <span id="tDescuento">0.00</span></div>
                    <div class="col-md-3 mb-2"><b>IVA:</b> <span id="tIVA">0.00</span></div>
                    <div class="col-md-3 mb-2"><b>Total:</b> <span id="tTotal" class="fw-bold">0.00</span></div>
                </div>
            </div>
        </div>

        <?php if($estado !== 'Anulada') { ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="fw-bold">Agregar producto</h5>
                <div class="row align-items-end">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Producto</label>
                        <select id="productoId" class="form-control" style="width:100%"></select>
                        <small class="text-muted">Precio: <span id="lblPrecioProducto">0.00</span></small>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" id="cantidadProducto" class="form-control" value="1" min="0.01" step="0.01">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">% Desc.</label>
                        <input type="number" id="porcentajeDescuento" class="form-control" value="0" min="0" max="100" step="0.01">
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="button" class="btn btn-primary w-100" onclick="agregarDetalleCotizacion()">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="card">
            <div class="card-body">
                <h5 class="fw-bold">Detalle</h5>
                <div class="table-responsive">
                    <table id="tblCotizacionDetalle" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Cant.</th>
                                <th>Precio</th>
                                <th>Descuento</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<script>
    function volverListadoCotizaciones() {
        asyncPage(<?php echo (int)($_SESSION['currentPage'] ?? 0); ?>, '<?php echo $_SESSION['currentToken'] ?? 'submenu'; ?>');
    }

    function money(v) {
        const symbol = '<?php echo $_SESSION['monedaSimbolo'] ?? '$'; ?>';
        const n = Number(v || 0);
        return symbol + ' ' + n.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function cargarTotales() {
        const cotizacionId = $('#cotizacionId').val();
        asyncData(
            `<?php echo $_SESSION['currentRoute']; ?>content/divs/getCotizacionTotales`,
            { cotizacionId: cotizacionId },
            (data) => {
                if(!data || typeof data !== 'object') return;
                $('#tSubTotal').text(money(data.subTotal));
                $('#tDescuento').text(money(data.totalDescuento));
                $('#tIVA').text(money(data.totalIVA));
                $('#tTotal').text(money(data.totalCotizacion));
                $('#lblEstadoCotizacion').text(data.estadoCotizacion || '');
            }
        );
    }

    function crearCotizacion() {
        const tipo = $('#tipoCliente').val();
        const sucursalId = $('#sucursalId').val();
        const clienteId = (tipo === 'sala' ? 0 : $('#clienteId').val());
        const nombreSala = $('#nombreClienteSala').val();

        if(!sucursalId) {
            mensaje('Aviso:', 'Debe seleccionar una sucursal.', 'warning');
            return;
        }

        if(tipo === 'sala') {
            if(!nombreSala || nombreSala.trim() === '') {
                mensaje('Aviso:', 'Debe ingresar el nombre del cliente en sala.', 'warning');
                return;
            }
        } else {
            if(!clienteId) {
                mensaje('Aviso:', 'Debe seleccionar un cliente.', 'warning');
                return;
            }
        }

        asyncData(
            `<?php echo $_SESSION['currentRoute']; ?>transaction/operation`,
            {
                typeOperation: 'insert',
                operation: 'cotizacion',
                sucursalId: sucursalId,
                clienteId: clienteId,
                nombreClienteSala: (tipo === 'sala' ? nombreSala : null),
                clienteUbicacionId: $('#clienteUbicacionId').val(),
                clienteContactoId: $('#clienteContactoId').val(),
                fechaEmision: $('#fechaEmision').val(),
                fechaVencimiento: $('#fechaVencimiento').val(),
                observaciones: $('#observaciones').val(),
                monedaId: 1,
                tasaIVA: 0.13
            },
            (res) => {
                if(res && typeof res === 'object' && res.status === 'success') {
                    changePage(`<?php echo $_SESSION['currentRoute']; ?>`, 'cotizacion', `cotizacionId=${res.cotizacionId}`);
                } else {
                    mensaje('Aviso:', (res?.msg || res), 'warning');
                }
            }
        );
    }

    function guardarCotizacion() {
        const tipo = $('#tipoCliente').val();
        const cotizacionId = $('#cotizacionId').val();
        const sucursalId = $('#sucursalId').val();
        const clienteId = (tipo === 'sala' ? 0 : $('#clienteId').val());
        const nombreSala = $('#nombreClienteSala').val();

        if(!sucursalId) {
            mensaje('Aviso:', 'Debe seleccionar una sucursal.', 'warning');
            return;
        }

        if(tipo === 'sala') {
            if(!nombreSala || nombreSala.trim() === '') {
                mensaje('Aviso:', 'Debe ingresar el nombre del cliente en sala.', 'warning');
                return;
            }
        } else {
            if(!clienteId) {
                mensaje('Aviso:', 'Debe seleccionar un cliente.', 'warning');
                return;
            }
        }

        asyncData(
            `<?php echo $_SESSION['currentRoute']; ?>transaction/operation`,
            {
                typeOperation: 'update',
                operation: 'cotizacion',
                cotizacionId: cotizacionId,
                sucursalId: sucursalId,
                clienteId: clienteId,
                nombreClienteSala: (tipo === 'sala' ? nombreSala : null),
                clienteUbicacionId: $('#clienteUbicacionId').val(),
                clienteContactoId: $('#clienteContactoId').val(),
                fechaEmision: $('#fechaEmision').val(),
                fechaVencimiento: $('#fechaVencimiento').val(),
                observaciones: $('#observaciones').val()
            },
            (data) => {
                if(data === 'success') {
                    mensaje('Operación completada:', 'Cotización actualizada.', 'success');
                    cargarTotales();
                } else {
                    mensaje('Aviso:', data, 'warning');
                }
            }
        );
    }

    function emitirCotizacion() {
        const cotizacionId = $('#cotizacionId').val();
        mensaje_confirmacion(
            '¿Emitir cotización?',
            `Se cambiará el estado a Emitida para la cotización #${cotizacionId}.`,
            'question',
            () => {
                asyncData(
                    `<?php echo $_SESSION['currentRoute']; ?>transaction/operation`,
                    { typeOperation: 'update', operation: 'cotizacion-emitir', cotizacionId },
                    (data) => {
                        if(data === 'success') {
                            mensaje('Operación completada:', 'Cotización emitida.', 'success');
                            cargarTotales();
                        } else {
                            mensaje('Aviso:', data, 'warning');
                        }
                    }
                );
            },
            'Emitir',
            'Cancelar'
        );
    }

    function anularCotizacionActual() {
        const cotizacionId = $('#cotizacionId').val();
        mensaje_confirmacion(
            '¿Anular cotización?',
            `Se anulará la cotización #${cotizacionId}.`,
            'warning',
            () => {
                asyncData(
                    `<?php echo $_SESSION['currentRoute']; ?>transaction/operation`,
                    { typeOperation: 'update', operation: 'cotizacion-anular', cotizacionId },
                    (data) => {
                        if(data === 'success') {
                            mensaje_do_aceptar('Operación completada', 'Cotización anulada.', 'success', () => {
                                changePage(`<?php echo $_SESSION['currentRoute']; ?>`, 'cotizacion', `cotizacionId=${cotizacionId}`);
                            });
                        } else {
                            mensaje('Aviso:', data, 'warning');
                        }
                    }
                );
            },
            'Anular',
            'Cancelar'
        );
    }

    function agregarDetalleCotizacion() {
        const cotizacionId = $('#cotizacionId').val();
        asyncData(
            `<?php echo $_SESSION['currentRoute']; ?>transaction/operation`,
            {
                typeOperation: 'insert',
                operation: 'cotizacion-detalle',
                cotizacionId: cotizacionId,
                productoId: $('#productoId').val(),
                cantidadProducto: $('#cantidadProducto').val(),
                porcentajeDescuento: $('#porcentajeDescuento').val()
            },
            (res) => {
                if(res && typeof res === 'object' && res.status === 'success') {
                    $('#tblCotizacionDetalle').DataTable().ajax.reload(null, false);
                    cargarTotales();
                    $('#productoId').val(null).trigger('change');
                    $('#cantidadProducto').val(1);
                    $('#porcentajeDescuento').val(0);
                    $('#lblPrecioProducto').text('0.00');
                } else {
                    mensaje('Aviso:', (res?.msg || res), 'warning');
                }
            }
        );
    }

    function eliminarDetalleCotizacion(frmData) {
        mensaje_confirmacion(
            '¿Eliminar producto?',
            'Se eliminará el registro del detalle de la cotización.',
            'warning',
            () => {
                asyncData(
                    `<?php echo $_SESSION['currentRoute']; ?>transaction/operation`,
                    {
                        typeOperation: 'delete',
                        operation: 'cotizacion-detalle',
                        cotizacionId: frmData.cotizacionId,
                        cotizacionDetalleId: frmData.cotizacionDetalleId
                    },
                    (res) => {
                        if(res && typeof res === 'object' && res.status === 'success') {
                            $('#tblCotizacionDetalle').DataTable().ajax.reload(null, false);
                            cargarTotales();
                        } else {
                            mensaje('Aviso:', (res?.msg || res), 'warning');
                        }
                    }
                );
            },
            'Eliminar',
            'Cancelar'
        );
    }

    $(document).ready(function() {
        // Sucursal
        $('#sucursalId').select2({
            placeholder: 'Seleccione una sucursal',
            ajax: {
                type: 'POST',
                url: `<?php echo $_SESSION['currentRoute']; ?>content/divs/selectSucursales`,
                dataType: 'json',
                delay: 250,
                data: function(params) { return { busquedaSelect: params.term }; },
                processResults: function(data) { return { results: data }; },
                cache: true
            }
        });

        // Cliente
        $('#clienteId').select2({
            placeholder: 'Seleccione un cliente',
            ajax: {
                type: 'POST',
                url: `<?php echo $_SESSION['currentRoute']; ?>content/divs/selectClientes`,
                dataType: 'json',
                delay: 250,
                data: function(params) { return { busquedaSelect: params.term }; },
                processResults: function(data) { return { results: data }; },
                cache: true
            }
        });

        // Ubicación
        $('#clienteUbicacionId').select2({
            placeholder: 'Seleccione una ubicación',
            allowClear: true,
            ajax: {
                type: 'POST',
                url: `<?php echo $_SESSION['currentRoute']; ?>content/divs/selectClienteUbicaciones`,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { clienteId: $('#clienteId').val(), busquedaSelect: params.term };
                },
                processResults: function(data) { return { results: data }; },
                cache: true
            }
        });

        // Contacto
        $('#clienteContactoId').select2({
            placeholder: 'Seleccione un contacto',
            allowClear: true,
            ajax: {
                type: 'POST',
                url: `<?php echo $_SESSION['currentRoute']; ?>content/divs/selectUbicacionContactos`,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { clienteUbicacionId: $('#clienteUbicacionId').val(), busquedaSelect: params.term };
                },
                processResults: function(data) { return { results: data }; },
                cache: true
            }
        });

        function setModoClienteSala(isSala) {
            if(isSala) {
                $('#divClienteSala').removeClass('d-none');
                $('#divClienteRegistrado').addClass('d-none');

                // limpiar selects dependientes
                $('#clienteId').val(null).trigger('change');
                $('#clienteUbicacionId').val(null).trigger('change');
                $('#clienteContactoId').val(null).trigger('change');

                // deshabilitar ubicación/contacto
                $('#clienteUbicacionId').prop('disabled', true);
                $('#clienteContactoId').prop('disabled', true);
            } else {
                $('#divClienteSala').addClass('d-none');
                $('#divClienteRegistrado').removeClass('d-none');

                $('#nombreClienteSala').val('');

                $('#clienteUbicacionId').prop('disabled', false);
                $('#clienteContactoId').prop('disabled', false);
            }
        }

        $('#tipoCliente').on('change', function() {
            setModoClienteSala($(this).val() === 'sala');
        });

        // Estado inicial
        setModoClienteSala($('#tipoCliente').val() === 'sala');

        // Limpieza dependiente (solo aplica a cliente registrado)
        $('#clienteId').on('change', function() {
            if($('#tipoCliente').val() === 'sala') return;
            $('#clienteUbicacionId').val(null).trigger('change');
            $('#clienteContactoId').val(null).trigger('change');
        });
        $('#clienteUbicacionId').on('change', function() {
            if($('#tipoCliente').val() === 'sala') return;
            $('#clienteContactoId').val(null).trigger('change');
        });

        <?php if(!$esNueva && $cot) { ?>
            // Set defaults en Select2 (AJAX)
            const optSucursal = new Option('<?php echo addslashes($cot->sucursal ?? 'Sucursal'); ?>', '<?php echo (int)$cot->sucursalId; ?>', true, true);
            $('#sucursalId').append(optSucursal).trigger('change');

            // Cliente en sala
            if(Number('<?php echo (int)($cot->clienteId ?? 0); ?>') === 0) {
                $('#tipoCliente').val('sala').trigger('change');
                $('#nombreClienteSala').val('<?php echo addslashes($cot->nombreClienteSala ?? ''); ?>');
            } else {
                const optCliente = new Option('<?php echo addslashes($clienteNombre); ?>', '<?php echo (int)$cot->clienteId; ?>', true, true);
                $('#clienteId').append(optCliente).trigger('change');

                <?php if(!is_null($cot->clienteUbicacionId)) { ?>
                    const optUbi = new Option('<?php echo addslashes($ubicacionText); ?>', '<?php echo (int)$cot->clienteUbicacionId; ?>', true, true);
                    $('#clienteUbicacionId').append(optUbi).trigger('change');
                <?php } ?>

                <?php if(!is_null($cot->clienteContactoId)) { ?>
                    const optCont = new Option('<?php echo addslashes($contactoText); ?>', '<?php echo (int)$cot->clienteContactoId; ?>', true, true);
                    $('#clienteContactoId').append(optCont).trigger('change');
                <?php } ?>
            }
        <?php } ?>

        <?php if(!$esNueva) { ?>
            // Producto (solo cuando ya existe cotización)
            $('#productoId').select2({
                placeholder: 'Buscar producto',
                ajax: {
                    type: 'POST',
                    url: `<?php echo $_SESSION['currentRoute']; ?>content/divs/selectProductos`,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) { return { busquedaSelect: params.term }; },
                    processResults: function(data) { return { results: data }; },
                    cache: true
                }
            });

            $('#productoId').on('select2:select', function(e) {
                const d = e.params.data || {};
                $('#lblPrecioProducto').text(money(d.precioVenta || 0));
            });

            let tblDetalle = $('#tblCotizacionDetalle').DataTable({
                dom: 'lrtip',
                ajax: {
                    method: 'POST',
                    url: `<?php echo $_SESSION['currentRoute']; ?>content/tables/tableCotizacionDetalle`,
                    data: { cotizacionId: $('#cotizacionId').val() }
                },
                autoWidth: false,
                columns: [null, null, null, null, null, null, null],
                columnDefs: [{ orderable: false, targets: [6] }],
                language: { url: '../libraries/packages/js/spanish_dt.json' }
            });

            cargarTotales();
        <?php } ?>
    });
</script>
