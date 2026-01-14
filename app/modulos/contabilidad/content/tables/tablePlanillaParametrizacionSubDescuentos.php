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
    ", [$_POST["catPlanillaDescuentoIdSuperior"], 0]);
    foreach($dataDescuentos as $descuentos) {
        $n++;

        $columnaDescuento = "
            <b><i class='fas fa-list-ol trailing'></i> Código contable:</b> $descuentos->codigoContable<br>
            <b><i class='fas fa-money-check-alt trailing'></i> Descuento:</b> $descuentos->nombreDescuento
        ";

        $jsonEditar = array(
            "nombreDescuento"     => $descuentos->nombreDescuento,
            "codigoContable"    => $descuentos->codigoContable,
            "catPlanillaDescuentoId" => $descuentos->catPlanillaDescuentoId
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));

        $jsonEliminar = array(
            "typeOperation" => "delete",
            "operation"     => "parametrizacion-descuento",
            "catPlanillaDescuentoId" => $descuentos->catPlanillaDescuentoId,
            "nombreDescuento" => $descuentos->nombreDescuento,
            "subDescuento"    => 1
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $columnaAcciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="editarSubdescuento('.$funcionEditar.');">
                <i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarDescuento('.$funcionEliminar.');">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
        ';

        $output['data'][] = array(
            $n,
            $columnaDescuento,
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