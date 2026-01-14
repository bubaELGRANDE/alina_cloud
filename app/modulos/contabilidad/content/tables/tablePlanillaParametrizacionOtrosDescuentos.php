<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $n = 0;

    $dataDescuentos = $cloud->rows("
        SELECT
            catPlanillaDescuentoId, 
            nombreDescuento, 
            codigoContable, 
            catPlanillaDescuentoIdSuperior
        FROM cat_planilla_descuentos
        WHERE catPlanillaDescuentoIdSuperior = ? AND flgDelete = ?
        ORDER BY nombreDescuento
    ", [ 0, 0]);
    foreach($dataDescuentos as $descuento) {
        $n++;

        $columnaDescuentos = "
            <b><i class='fas fa-list-ol trailing'></i> Código contable:</b> $descuento->codigoContable<br>
            <b><i class='fas fa-money-check-alt trailing'></i> Descuento:</b> $descuento->nombreDescuento
        ";
        $jsonEditar = array(
            "typeOperation"                     => "update",
            "tituloModal"                       => "Descuento - $descuento->nombreDescuento",
            "catPlanillaDescuentoId"            => $descuento->catPlanillaDescuentoId,
            "catPlanillaDescuentoIdSuperior"    => $descuento->catPlanillaDescuentoIdSuperior,
  
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));

        $jsonEliminar = array(
            "typeOperation"             => "delete",
            "operation"                 => "parametrizacion-descuento",
            "catPlanillaDescuentoId"    => $descuento->catPlanillaDescuentoId,
            "nombreDescuento"           => $descuento->nombreDescuento,
            "subDescuento"              => 0
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));
        
        $totalSubDescuentos = $cloud->count("
            SELECT catPlanillaDescuentoId FROM cat_planilla_descuentos
            WHERE catPlanillaDescuentoIdSuperior = ? AND flgDelete = ?
        ", [$descuento->catPlanillaDescuentoId, 0]);

        $jsonSubDescuentos = array(
            "catPlanillaDescuentoId" => 0,
            "catPlanillaDescuentoIdSuperior"    => $descuento->catPlanillaDescuentoId,
            "tituloModal"                       => "Descuento - $descuento->nombreDescuento",
            "nombreDescuento"                   => $descuento->nombreDescuento
        );
        $funcionSubDescuentos = htmlspecialchars(json_encode($jsonSubDescuentos));

        $columnaAcciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalDescuento('.$funcionEditar.')">
                <i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarDescuento('.$funcionEliminar.')">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalSubDescuento('.$funcionSubDescuentos.')">
                <span class="badge rounded-pill bg-light" style="color: black;">'.$totalSubDescuentos.'</span>
                <i class="fas fa-list-ul"></i>
                <span class="ttiptext">SubDescuentos</span>
            </button>
            ';

        $output['data'][] = array(
            $n,
            $columnaDescuentos,
            $columnaAcciones
        );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>