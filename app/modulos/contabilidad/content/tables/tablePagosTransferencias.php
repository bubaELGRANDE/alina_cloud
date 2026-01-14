<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataBancoTransferencias = $cloud->rows("
        SELECT
            pagoTransferenciaId, 
            fechaPagoTransferencia, 
            tipoTransferencia, 
            estadoPago
        FROM conta_pagos_transferencias
        WHERE nombreOrganizacionId = ? AND flgDelete = ?
        ORDER BY fechaPagoTransferencia DESC
    ",[$_POST['bancoId'], 0]);
    
    $n = 0;
    foreach($dataBancoTransferencias as $bancoTransferencia) {
        $n++;

        $dataTransferenciaQuedan = $cloud->row("
            SELECT
                COUNT(pagoTransferenciaDetalleId) AS numRegistros,
                SUM(montoTransferencia) AS montoQuedan
            FROM conta_pagos_transferencias_detalle
            WHERE pagoTransferenciaId = ? AND tablaDetalle = ? AND flgDelete = ?
        ", [$bancoTransferencia->pagoTransferenciaId, 'comp_quedan', 0]);

        $montoQuedan = $dataTransferenciaQuedan->montoQuedan;

        $dataTransferenciaOtrosPagos = $cloud->row("
            SELECT
                COUNT(pagoTransferenciaDetalleId) AS numRegistros,
                SUM(montoTransferencia) AS montoOtrosPagos
            FROM conta_pagos_transferencias_detalle
            WHERE pagoTransferenciaId = ? AND tablaDetalle = ? AND flgDelete = ?
        ", [$bancoTransferencia->pagoTransferenciaId, 'conta_pagos_transferencias_detalle', 0]);

        $montoOtrosPagos = $dataTransferenciaOtrosPagos->montoOtrosPagos;

        $totalGeneral = $montoQuedan + $montoOtrosPagos;

        $columnaTipoTransferencia = $bancoTransferencia->tipoTransferencia;
        $columnaTotal = "
            <div class='row'>
                <div class='col-6'>
                    (+) Quedan
                </div>
                <div class='col-6'>
                    <div class='simbolo-moneda'>
                        <span>$_SESSION[monedaSimbolo]</span>
                        <div>
                            ". number_format((float)$montoQuedan, 2, ".", ",") ."
                        </div>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-6'>
                    (+) Otros pagos
                </div>
                <div class='col-6'>
                    <div class='simbolo-moneda'>
                        <span>$_SESSION[monedaSimbolo]</span>
                        <div>
                            ". number_format((float)$montoOtrosPagos, 2, ".", ",") ."
                        </div>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-6 fw-bold'>
                    (=) Total
                </div>
                <div class='col-6 fw-bold'>
                    <div class='simbolo-moneda'>
                        <span>$_SESSION[monedaSimbolo]</span>
                        <div>
                            ". number_format((float)$totalGeneral, 2, ".", ",") ."
                        </div>
                    </div>
                </div>
            </div>
        ";

        $jsonQuedan = array(
            'pagoTransferenciaId'       => $bancoTransferencia->pagoTransferenciaId,
            'bancoId'                   => $_POST['bancoId'],
            'tituloModal'               => "Quedan en la transferencia: " . date("d/m/Y", strtotime($bancoTransferencia->fechaPagoTransferencia)) . " ($bancoTransferencia->tipoTransferencia)",
            'tipoTransferencia'         => $bancoTransferencia->tipoTransferencia,
            'estadoPago'                => $bancoTransferencia->estadoPago
        );

        $jsonOtrosPagos = array(
            'pagoTransferenciaId'       => $bancoTransferencia->pagoTransferenciaId,
            'bancoId'                   => $_POST['bancoId'],
            'tituloModal'               => "Otros pagos en la transferencia: " . date("d/m/Y", strtotime($bancoTransferencia->fechaPagoTransferencia)) . " ($bancoTransferencia->tipoTransferencia)",
            'tipoTransferencia'         => $bancoTransferencia->tipoTransferencia,
            'estadoPago'                => $bancoTransferencia->estadoPago
        );

        $jsonFinalizar = array(
            'typeOperation'             => "update",
            'operation'                 => "pagos-transferencias-finalizar",
            'pagoTransferenciaId'       => $bancoTransferencia->pagoTransferenciaId,
            'bancoId'                   => $_POST['bancoId'],
            'txtTitulo'                 => "¿Está seguro que desea finalizar el pago por transferencia: " . date("d/m/Y", strtotime($bancoTransferencia->fechaPagoTransferencia)) . " ($bancoTransferencia->tipoTransferencia)?"
        );

        $jsonReporte = array(
            "file"                          => "pagos-transferencias-fecha",
            "fechaPagoTransferencia"        => $bancoTransferencia->fechaPagoTransferencia,
            "pagoTransferenciaId"           => $bancoTransferencia->pagoTransferenciaId
        );

        // Cambiar archivoDescarga si más adelante se programan otros bancos
        switch($_POST['bancoId']) {
            case '5':
                $archivoDescarga = "descargarPagosTransferenciasAgricola";
            break;
            
            default:
                $archivoDescarga = "definir-archivo-banco";
            break;
        }

        $nombreArchivoBanco = date("d-m-Y", strtotime($bancoTransferencia->fechaPagoTransferencia)) . "-$bancoTransferencia->tipoTransferencia";

        $jsonArchivoBanco = array(
            'archivoDescarga'               => $archivoDescarga,
            'nombreArchivoBanco'            => $nombreArchivoBanco,
            'pagoTransferenciaId'           => $bancoTransferencia->pagoTransferenciaId,
            'tipoTransferencia'             => $bancoTransferencia->tipoTransferencia
        );

        if($bancoTransferencia->estadoPago == "Finalizado") {
            $columnaFechaPago = "<b><i class='fas fa-calendar-day'></i> Fecha: </b>" . date("d/m/Y", strtotime($bancoTransferencia->fechaPagoTransferencia)) . "<br>
                <b><i class='fas fa-tasks'></i> Estado: </b><span class='badge rounded-pill bg-success'>Finalizado</span>
            ";

            $acciones = "
                <button type='button' class='btn btn-secondary btn-sm ttip' onclick='modalQuedanTransferencia(".htmlspecialchars(json_encode($jsonQuedan)).");'>
                    <span class='badge rounded-pill bg-light text-dark'>$dataTransferenciaQuedan->numRegistros</span> Quedan
                    <span class='ttiptext'>Ver Quedan</span>
                </button>
                <button type='button' class='btn btn-secondary btn-sm ttip' onclick='modalOtrosPagosTransferencia(".htmlspecialchars(json_encode($jsonOtrosPagos)).");'>
                    <span class='badge rounded-pill bg-light text-dark'>$dataTransferenciaOtrosPagos->numRegistros</span> Otros pagos
                    <span class='ttiptext'>Ver Otros pagos</span>
                </button>
                <button type='button' class='btn btn-primary btn-sm ttip' onclick='descargarArchivoBanco(".htmlspecialchars(json_encode($jsonArchivoBanco)).");'>
                    <i class='fas fa-file-download'></i>
                    <span class='ttiptext'>Descargar archivo de banco para transferencia de pagos</span>
                </button>
                <button type='button' class='btn btn-info btn-sm ttip' onclick='modalReportesTransferencias(".htmlspecialchars(json_encode($jsonReporte)).");'>
                    <i class='fas fa-print'></i>
                    <span class='ttiptext'>Reporte de pagos por transferencia</span>
                </button>
            ";
        } else {
            // Pendiente
            $columnaFechaPago = "<b><i class='fas fa-calendar-day'></i> Fecha: </b>" . date("d/m/Y", strtotime($bancoTransferencia->fechaPagoTransferencia)) . "<br>
                <b><i class='fas fa-tasks'></i> Estado: </b><span class='badge rounded-pill bg-warning'>Pendiente</span>
            ";
            $acciones = "
                <button type='button' class='btn btn-primary btn-sm ttip' onclick='modalQuedanTransferencia(".htmlspecialchars(json_encode($jsonQuedan)).");'>
                    <span class='badge rounded-pill bg-light text-dark'>$dataTransferenciaQuedan->numRegistros</span> Quedan
                    <span class='ttiptext'>Gestionar Quedan</span>
                </button>
                <button type='button' class='btn btn-primary btn-sm ttip' onclick='modalOtrosPagosTransferencia(".htmlspecialchars(json_encode($jsonOtrosPagos)).");'>
                    <span class='badge rounded-pill bg-light text-dark'>$dataTransferenciaOtrosPagos->numRegistros</span> Otros pagos
                    <span class='ttiptext'>Gestionar Otros pagos</span>
                </button>
                <button type='button' class='btn btn-success btn-sm ttip' onclick='finalizarPagoTransferencia(".htmlspecialchars(json_encode($jsonFinalizar)).");'>
                    <i class='fas fa-user-lock'></i>
                    <span class='ttiptext'>Finalizar gestión de pagos y generar archivos de banco</span>
                </button>
            ";
        }

        $output['data'][] = array(
            $n, 
            $columnaFechaPago,
            $columnaTipoTransferencia,
            $columnaTotal,
            $acciones
        );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>