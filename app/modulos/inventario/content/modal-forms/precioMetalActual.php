<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

// Obtener el último precio del oro
$precioActual = $cloud->row("
    SELECT 
        id,
        metal,
        precioBid,
        precioAsk,
        precioOpen,
        precioClose,
        precioHigh,
        precioLow,
        fuente,
        fhRegistro
    FROM bit_historial_precios_metal
    WHERE metal = 'ORO' AND flgDelete = 0
    ORDER BY fhRegistro DESC
    LIMIT 1
");

// Obtener historial de precios (últimos 10 registros)
$historialPrecios = $cloud->rows("
    SELECT 
        id,
        metal,
        precioBid,
        precioAsk,
        precioOpen,
        precioClose,
        precioHigh,
        precioLow,
        fuente,
        fhRegistro
    FROM bit_historial_precios_metal
    WHERE metal = 'ORO' AND flgDelete = 0
    ORDER BY fhRegistro DESC
    LIMIT 10
");
?>

<div class="card mb-4 shadow-sm card-opcional">
    <div class="card-body">
        <h5 class="card-title mb-3 title-opcional">
            <span class="title-label">
                <i class="fa-solid fa-coins text-warning title-icon"></i>
                <span>Precio Actual del Oro (USD/Onza Troy)</span>
            </span>
        </h5>
        
        <?php if ($precioActual) { ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Precio Bid</h6>
                            <h3 class="text-success fw-bold">$<?= number_format($precioActual->precioBid, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Precio Ask</h6>
                            <h3 class="text-primary fw-bold">$<?= number_format($precioActual->precioAsk, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <small class="text-muted">Open</small>
                            <p class="mb-0 fw-semibold">$<?= number_format($precioActual->precioOpen, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <small class="text-muted">Close</small>
                            <p class="mb-0 fw-semibold">$<?= number_format($precioActual->precioClose, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <small class="text-muted">High</small>
                            <p class="mb-0 fw-semibold text-success">$<?= number_format($precioActual->precioHigh, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <small class="text-muted">Low</small>
                            <p class="mb-0 fw-semibold text-danger">$<?= number_format($precioActual->precioLow, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between text-muted small">
                        <span><i class="fas fa-globe me-1"></i>Fuente: <?= htmlspecialchars($precioActual->fuente); ?></span>
                        <span><i class="fas fa-clock me-1"></i>Última actualización: <?= date('d/m/Y H:i:s', strtotime($precioActual->fhRegistro)); ?></span>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No hay registro de precios del oro disponible.
            </div>
        <?php } ?>
    </div>
</div>

<!-- Historial de Precios -->
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-3">
            <span class="title-label">
                <i class="fa-solid fa-history text-info title-icon"></i>
                <span>Historial de Precios</span>
            </span>
        </h5>
        
        <?php if (!empty($historialPrecios)) { ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th class="text-end">Bid</th>
                            <th class="text-end">Ask</th>
                            <th class="text-end">High</th>
                            <th class="text-end">Low</th>
                            <th>Fuente</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historialPrecios as $precio) { ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($precio->fhRegistro)); ?></td>
                                <td class="text-end">$<?= number_format($precio->precioBid, 2); ?></td>
                                <td class="text-end">$<?= number_format($precio->precioAsk, 2); ?></td>
                                <td class="text-end text-success">$<?= number_format($precio->precioHigh, 2); ?></td>
                                <td class="text-end text-danger">$<?= number_format($precio->precioLow, 2); ?></td>
                                <td><small><?= htmlspecialchars($precio->fuente); ?></small></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="text-muted mb-0">No hay historial de precios disponible.</p>
        <?php } ?>
    </div>
</div>

<script>
    $(document).ready(function () {
        // No requiere validación de formulario ya que es solo visualización
    });
</script>
