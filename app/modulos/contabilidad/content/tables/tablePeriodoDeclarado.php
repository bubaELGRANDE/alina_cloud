<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

	$getDeclaracion = $cloud->rows('SELECT 
    s.sucursal,
    t.ventasContribuyentes, t.ventasContribuyentesIVA, t.ventasConsumidores, t.ventasConsumidoresIVA, t.ventasConsumidoresExentos, t.ventasExportaciones, t.notasCredito, t.notasRemision, 
    t.notasRemisionIVA, t.comprobantesRetencion, t.comprobantesRetencionIVAR, t.sujetosExcluidos, t.sujetosExcluidosRenta, t.invalidaciones, t.invalidacionesIVA, t.ivaPercibido, t.ivaRetenido, 
    t.ivaRetenidoProveedores
    FROM fel_cierre_declaraciones_totales t
    JOIN cat_sucursales s ON t.sucursalId = s.sucursalId
    WHERE t.flgDelete = 0 AND t.cierreDeclaracionId = ? ', [$_POST['idDeclaracion']]);

	$n = 0;
	foreach ($getDeclaracion as $declaracion){
		$n += 1;
		$sucursal = $declaracion->sucursal;
		$contribuyente = '<div class="text-end">$' . number_format($declaracion->ventasContribuyentes, 2, '.', ',') . '<br>' . '(IVA) $' . number_format($declaracion->ventasContribuyentesIVA, 2, '.', ',') . '</div>';
		$consumidores = '<div class="text-end">$' . number_format($declaracion->ventasConsumidores, 2, '.', ',') . '<br>' . '(IVA) $' . number_format($declaracion->ventasConsumidoresIVA, 2, '.', ',') . '</div>';
		$consumidoresExentos = '<div class="text-end">$' . number_format($declaracion->ventasConsumidoresExentos, 2, '.', ',') . '</div>';
		$exportaciones = '<div class="text-end">$' . number_format($declaracion->ventasExportaciones, 2, '.', ',') . '</div>';
		$notasCredito = '<div class="text-end">$' . number_format($declaracion->notasCredito, 2, '.', ',') . '</div>';
		$notasRemision = '<div class="text-end">$' . number_format($declaracion->notasRemision, 2, '.', ',') . '<br>' . '(IVA) $' . number_format($declaracion->notasRemisionIVA, 2, '.', ',') . '</div>';
		$comprobantesRetencion = '<div class="text-end">$' . number_format($declaracion->comprobantesRetencion, 2, '.', ',') . '<br>' . '(IVA) $' . number_format($declaracion->comprobantesRetencion, 2, '.', ',') . '</div>';
		$sujetosExcluidos = '<div class="text-end">$' . number_format($declaracion->sujetosExcluidos, 2, '.', ',') . '<br>' . '(IVA) $' . number_format($declaracion->sujetosExcluidosRenta, 2, '.', ',') . '</div>';
		$invalidaciones = '<div class="text-end">$' . number_format($declaracion->invalidaciones, 2, '.', ',') . '<br>' . '(IVA) $' . number_format($declaracion->invalidacionesIVA, 2, '.', ',') . '</div>';
		$iva = '<div class="text-end">(Percibido) $' . number_format($declaracion->ivaPercibido, 2, '.', ',') . '<br>' . '(Retenido) $' . number_format($declaracion->ivaRetenido, 2, '.', ',') . '</div>';



		$output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $sucursal,
            $contribuyente,
            $consumidores,
            $consumidoresExentos,
            $exportaciones,
            $notasCredito,
            $notasRemision,
            $comprobantesRetencion,
            $sujetosExcluidos,
            $invalidaciones,
            $iva
        );
	}
    
	if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>''));
    }