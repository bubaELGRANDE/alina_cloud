<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $n = 0;

    $dataDescLey = $cloud->rows("
        SELECT
            descuentoLeyId, 
            nombreDescuentoLey, 
            tipoDescuento, 
            tipoValorDescuento,
            montoMaximo,
            cuotaExcesoMaximo,
            valorDescuento,
            estadoDescuentoLey
        FROM cat_planilla_descuentos_ley
        WHERE tipoDescuento =? AND flgDelete = ?
        ORDER BY nombreDescuentoLey
    ", [$_POST["tipoDescuento"], 0]);
    foreach($dataDescLey as $descuentoLey) {
        $n++;

        $checkedEstado = ""; $nuevoEstado = "";
        if($descuentoLey->estadoDescuentoLey == "Activo") {
            $checkedEstado = "checked";
            $nuevoEstado = "Inactivo";
        } else {
            $nuevoEstado = "Activo";
        }

        $jsonEditar = array(
            "typeOperation"                     => "update",
            "operation"                         => "parametrizacion-desc-ley",
            "descuentoLeyId"                    => $descuentoLey->descuentoLeyId,
            "nombreDescuentoLey"                => $descuentoLey->nombreDescuentoLey,
            "tipoDescuento"                     => $_POST["tipoDescuento"],
            "nuevoEstado"                       => $nuevoEstado
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));

        $columnaNombreDescLey = "
            <b><i class='fas fa-user-minus trailing'></i> Descuento:</b> $descuentoLey->nombreDescuentoLey
        ";
        $columnatipoValorDescuento  = $descuentoLey->tipoValorDescuento;
        if($descuentoLey->montoMaximo == "") {
            $columnaMaximoDescuento = "
                <div class='row'>
                    <div class='col-7'>
                        <b> <i class='fas fa-dollar-sign trailing'></i> Monto máximo:</b>
                    </div>
                    <div class='col-5 text-end'>
                        No aplica
                    </div>
                </div>
                <div class='row'>
                    <div class='col-7'>
                        <b> <i class='fas fa-dollar-sign trailing'></i> Cuota exceso:</b>
                    </div>
                    <div class='col-5 text-end'>
                        No aplica
                    </div>
                </div>
            ";
        } else {
            $columnaMaximoDescuento = "
                <div class='row'>
                    <div class='col-7'>
                        <b> <i class='fas fa-dollar-sign trailing'></i> Monto máximo:</b>
                    </div>
                    <div class='col-5 text-end'>
                        $ ".number_format($descuentoLey->montoMaximo, 2, '.', ',')."
                    </div>
                </div>
                <div class='row'>
                    <div class='col-7'>
                        <b> <i class='fas fa-dollar-sign trailing'></i> Cuota exceso:</b>
                    </div>
                    <div class='col-5 text-end'>
                    $ ".number_format($descuentoLey->cuotaExcesoMaximo, 2, '.', ',')."
                    </div>
                </div>
            ";
        }
        if($descuentoLey->tipoValorDescuento == "Porcentaje"){
            $columnavalorDescuento      ="<div class='text-end'> ". number_format($descuentoLey->valorDescuento, 2, '.', ',')." % </div>";
        }else{
            $columnavalorDescuento      ="<div class='text-end'> $ ". number_format($descuentoLey->valorDescuento, 2, '.', ',')."</div>";
        }
        $columnaAcciones = '                        
            <div class="d-flex">
                <label class="form-check-label me-2 text-danger" for="estadoDescuentoLey'.$descuentoLey->descuentoLeyId.'"><b>Inactivo</b></label>
                    <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="estadoDescuentoLey'.$descuentoLey->descuentoLeyId.'" name="estadoDescuentoLey" onclick="cambiarEstadoDescLey('.$funcionEditar.');" '.$checkedEstado.'/">
                    </div>
                <label class="form-check-label text-success" for="estadoDescuentoLey'.$descuentoLey->descuentoLeyId.'"><b>Activo</b></label>
            </div>
        ';

        $output['data'][] = array(
            $n,
            $columnaNombreDescLey,
            $columnavalorDescuento,
            $columnaMaximoDescuento,
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