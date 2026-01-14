<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();


$productoId = $_POST["productoId"] ?? 0;

$adjuntos = $cloud->rows("
    SELECT 
        productoAdjuntoId,
        tipoProductoAdjunto,
        descripcionProductoAdjunto,
        urlProductoAdjunto,
        fhAdd
    FROM prod_productos_adjuntos
    WHERE productoId = ? AND flgDelete = 0
    ORDER BY productoAdjuntoId DESC
", [$productoId]);

?>

<?php if (count($adjuntos) == 0): ?>
    <p class="text-muted">No hay archivos cargados para este producto.</p>
<?php endif; ?>

<?php foreach ($adjuntos as $a): ?>
    <div class="col-md-3" id="item-<?= $a->productoAdjuntoId ?>">
        <div class="card shadow-sm border-0">

            <?php
            $ext = strtolower(pathinfo($a->urlProductoAdjunto, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ["jpg", "jpeg", "png"]);
            ?>

            <?php if ($isImage): ?>
                <img src="../libraries/resources/images/<?= $a->urlProductoAdjunto ?>" class="card-img-top"
                    style="height:160px;object-fit:cover;">
            <?php else: ?>
                <div class="p-4 text-center bg-light" style="height:160px;">
                    <i class="fa-solid fa-file fa-3x text-secondary"></i>
                    <p class="mt-2 small"><?= strtoupper($ext) ?></p>
                </div>
            <?php endif; ?>

            <div class="card-body">
                <h6 class="fw-bold mb-1"><?= ucfirst($a->tipoProductoAdjunto) ?></h6>
                <span class="tag-type mb-2 d-inline-block"><?= $a->tipoProductoAdjunto ?></span>
                <small class="text-muted d-block mb-2">Subido: <?= $a->fhAdd ?></small>

                <button class="btn btn-sm btn-outline-danger w-100" onclick="eliminarAdjunto(<?= $a->productoAdjuntoId ?>)">
                    <i class="fa fa-trash me-1"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
<?php endforeach; ?>