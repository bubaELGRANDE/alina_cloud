<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    
    $dataTransferenciaDetalle = $cloud->rows("
        SELECT 
            pt.estadoPago AS estadoPago,
            ptd.pagoTransferenciaDetalleId AS pagoTransferenciaDetalleId, 
            ptd.proveedorCBancariaId AS proveedorCBancariaId, 
            ptd.conceptoTransferencia AS conceptoTransferencia,
            ptd.montoTransferencia AS montoTransferencia, 
            ptd.tablaDetalleId AS tablaDetalleId,
            p.nombreProveedor AS nombreProveedor,
            p.nombreComercial AS nombreComercial,
            org.abreviaturaOrganizacion AS abreviaturaOrganizacion,
            pcb.numeroCuenta AS numeroCuenta,
            pcb.tipoCuenta AS tipoCuenta
        FROM conta_pagos_transferencias_detalle ptd
        JOIN conta_pagos_transferencias pt ON pt.pagoTransferenciaId = ptd.pagoTransferenciaId
        JOIN comp_proveedores_cbancaria pcb ON pcb.proveedorCBancariaId = ptd.proveedorCBancariaId
        JOIN comp_proveedores p ON p.proveedorId = pcb.proveedorId
        JOIN cat_nombres_organizaciones org ON org.nombreOrganizacionId = pcb.nombreOrganizacionId
        WHERE ptd.pagoTransferenciaId = ? AND ptd.tablaDetalle = ? AND ptd.flgDelete = ?
        ORDER BY ptd.tablaDetalleId DESC
    ", [$_POST['pagoTransferenciaId'], 'conta_pagos_transferencias_detalle', 0]);
    $n = 0;
    $totalOtrosPagos = 0;
    foreach($dataTransferenciaDetalle as $transferenciaDetalle) {
        $n++;

        $columnaConcepto = $transferenciaDetalle->conceptoTransferencia;
        $columnaProveedor = "
            <b><i class='fas fa-building'></i> Nombre comercial: </b>$transferenciaDetalle->nombreComercial<br>
            <b><i class='fas fa-user-tie'></i> Razón social: </b>$transferenciaDetalle->nombreProveedor
        ";
        $columnaCuenta = "
            <b><i class='fas fa-university'></i> $transferenciaDetalle->abreviaturaOrganizacion: </b>$transferenciaDetalle->numeroCuenta<br>
            <b><i class='fas fa-piggy-bank'></i> Tipo de cuenta: </b>$transferenciaDetalle->tipoCuenta
        ";
        $columnaMonto = "
            <div class='simbolo-moneda'>
                <span>$_SESSION[monedaSimbolo]</span>
                <div>
                    ".number_format($transferenciaDetalle->montoTransferencia, 2, '.', ',')."
                </div>
            </div>
        ";

        if($transferenciaDetalle->estadoPago == "Pendiente") {
            $jsonEliminarOtrosPagos = [
                "typeOperation"                     => "delete",
                "operation"                         => "pagos-transferencias-otros-pagos",
                "pagoTransferenciaDetalleId"        => $transferenciaDetalle->pagoTransferenciaDetalleId
            ];

            $acciones = "
                <button type='button' class='btn btn-danger btn-sm ttip' onclick='eliminarDetalleOtrosPagos(".htmlspecialchars(json_encode($jsonEliminarOtrosPagos)).");'>
                    <i class='fas fa-trash-alt'></i>
                    <span class='ttiptext'>Eliminar pago de la transferencia</span>
                </button>
            ";
        } else {
            // Sin acciones para otro estado
            $acciones = "";
        }

        $totalOtrosPagos += $transferenciaDetalle->montoTransferencia;

        $output['data'][] = array(
            $n, 
            $columnaConcepto,
            $columnaProveedor,
            $columnaCuenta,
            $columnaMonto,
            $acciones
        );
    }

    $output['footer'] = array(
        '',
        '',
        '<b>Total Otros Pagos</b>',
        '',
        "
            <div class='simbolo-moneda fw-bold'>
                <span>$_SESSION[monedaSimbolo]</span>
                <div>
                    ".number_format($totalOtrosPagos, 2, '.', ',')."
                </div>
            </div>
        ",
        ''  
    );

    if($n > 0) {
        echo json_encode($output);
    } else {
        $output['data'] = '';
        // No retornar nada para evitar error "null"
        echo json_encode($output); 
    }
?>