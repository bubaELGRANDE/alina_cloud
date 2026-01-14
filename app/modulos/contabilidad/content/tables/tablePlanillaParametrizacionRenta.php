<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $n = 0;

    $dataRenta = $cloud->rows("
        SELECT
            descuentoRentaId, 
            tramoRenta, 
            rangoInicio, 
            rangoFin,
            montoExceso,
            porcentajeDescuento,
            cuotaFija,
            flgEnAdelante,
            estadoDescuentoRenta
        FROM cat_planilla_descuentos_renta
        WHERE flgDelete = ?
        ORDER BY tramoRenta
    ", [0]);
    foreach($dataRenta as $descuentoRenta) {
        $n++;

        $checkedEstado = ""; $nuevoEstado = "";
        if($descuentoRenta->estadoDescuentoRenta == "Activo") {
            $checkedEstado = "checked";
            $nuevoEstado = "Inactivo";
        } else {
            $nuevoEstado = "Activo";
        }

        $jsonEditar = array(
            "typeOperation"                     => "update",
            "operation"                         => "parametrizacion-renta",
            "descuentoRentaId"                  => $descuentoRenta->descuentoRentaId,
            "tramoRenta"                        => $descuentoRenta->tramoRenta,
            "nuevoEstado"                       => $nuevoEstado
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));

        if($descuentoRenta->flgEnAdelante == "Sí") {
            $hasta= "En adelante";
        } else {
            $hasta = "$ " . number_format($descuentoRenta->rangoFin, 2, '.', ',');
        }

        $columnaTramo = "
            <b> <i class='fas fa-edit trailing'></i> Tramo:</b> $descuentoRenta->tramoRenta<br>
            <b> <i class='fas fa-greater-than-equal trailing'></i> Desde:</b> $ ".number_format($descuentoRenta->rangoInicio, 2, '.', ',')."<br>
            <b> <i class='fas fa-less-than-equal trailing'></i> Hasta:</b> ".$hasta."
        ";
        $columnaPorcentaje  ="<div class='text-end'>".number_format($descuentoRenta->porcentajeDescuento, 2, '.', ',')." %</div>";
        $columnaExeso       ="<div class='text-end'> $ " .number_format($descuentoRenta->montoExceso, 2, '.', ',')."</div>";
        $columnaCuotaFija   ="<div class='text-end'> $ ". number_format($descuentoRenta->cuotaFija, 2, '.', ',')."</div>";
        $columnaAcciones = '                        
            <div class="d-flex">
                <label class="form-check-label me-2 text-danger" for="estadoRenta'.$descuentoRenta->descuentoRentaId.'"><b>Inactivo</b></label>
                    <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="estadoRenta'.$descuentoRenta->descuentoRentaId.'" name="estadoRenta" onclick="cambiarEstadoRenta('.$funcionEditar.');" '.$checkedEstado.' /">
                    </div>
                <label class="form-check-label text-success" for="estadoRenta'.$descuentoRenta->descuentoRentaId.'"><b>Activo</b></label>
            </div>
        ';

        $output['data'][] = array(
            $n,
            $columnaTramo,
            $columnaPorcentaje,
            $columnaExeso,
            $columnaCuotaFija,
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