<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    if($_POST['catalogo'] == "Descuentos") {
        $existeSubCatalogo = $cloud->count("
            SELECT
                catPlanillaDescuentoId
            FROM cat_planilla_descuentos
            WHERE catPlanillaDescuentoIdSuperior = ? AND flgDelete = ?
        ", [$_POST['superiorId'], 0]);
    } else {
        $existeSubCatalogo = $cloud->count("
            SELECT
                catPlanillaDevengoId
            FROM cat_planilla_devengos
            WHERE catPlanillaDevengoIdSuperior = ? AND flgDelete = ?
        ", [$_POST['superiorId'], 0]);
    }
    echo ($existeSubCatalogo == 0 ? false : true);
?>