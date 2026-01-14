<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $comisionPagarPeriodoId = $_POST['periodoId'];
    $parametrizacionIVA = 1.13;

    $n = 0;
	// Iterar primero las flgIdentificador F ya que las comisiones de abono tienen otra lógica
	$dataComisionRevision = $cloud->rows("
		SELECT
			comisionPagarCalculoId,
			nombreEmpleado,
			tipoFactura,
			correlativoFactura,
			fechaFactura,
			sucursalFactura,
			nombreCliente,
			totalFactura,
			comisionPagar,
			fechaAbono,
			flgComisionEditar,
			comisionPagarEditar,
			ivaPercibido,
			ivaRetenido
		FROM conta_comision_pagar_calculo
		WHERE comisionPagarPeriodoId = ? AND flgIdentificador = 'F' AND flgComisionEditar = '0' AND flgDelete = '0'
		GROUP BY correlativoFactura, fechaFactura
		ORDER BY fechaFactura, correlativoFactura
	", [$comisionPagarPeriodoId]);
    foreach($dataComisionRevision as $dataComisionRevision) {
    	// Sumar la comision total de esta factura para cada producto
		$dataSumaComisionFactura = $cloud->row("
			SELECT 
				SUM(comisionPagar) AS totalComisionFactura 
			FROM conta_comision_pagar_calculo
			WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND nombreCliente = ? AND correlativoFactura = ? AND tipoFactura = ? AND fechaFactura = ? AND sucursalFactura = ? AND flgIdentificador = ? AND fechaAbono = ? AND flgDelete = '0'
		", [$comisionPagarPeriodoId, $dataComisionRevision->nombreEmpleado, $dataComisionRevision->nombreCliente, $dataComisionRevision->correlativoFactura, $dataComisionRevision->tipoFactura, $dataComisionRevision->fechaFactura, $dataComisionRevision->sucursalFactura, 'F', $dataComisionRevision->fechaAbono]);

		$comisionFactura = $dataSumaComisionFactura->totalComisionFactura;

		$dataTotalFacturaFix = $cloud->rows("
            SELECT
                codTipoFactura,
                tipoFactura,
                correlativoFactura,
                fechaFactura,
                nombreCliente,
                lineaProducto,
                codProductoFactura,
                precioUnitario,
                precioFacturado,
                cantidadProducto,
                totalVenta,
                porcentajeDescuento,
                paramRangoPorcentajeInicio,
                paramRangoPorcentajeFin,
                paramPorcentajePago,
                comisionPagar
            FROM conta_comision_pagar_calculo
            WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND correlativoFactura = ? AND fechaFactura = ? AND tipoFactura = ? AND flgIdentificador = 'F' AND flgDelete = '0'
            ORDER BY lineaProducto
		", [$comisionPagarPeriodoId, $dataComisionRevision->nombreEmpleado, $dataComisionRevision->correlativoFactura, $dataComisionRevision->fechaFactura, $dataComisionRevision->tipoFactura]);

		$totalFactura = 0.00;
		
		foreach($dataTotalFacturaFix as $dataTotalFacturaFix) {
	        switch ($dataTotalFacturaFix->codTipoFactura) {
	            case '1':
	                $tipoFactura = "C. FINAL";
	                $flgTipoCalculo = "IVA"; // Precios CON IVA
	            break;
	            
	            case '2':
	                $tipoFactura = "C. FISCAL";
	                $flgTipoCalculo = "NIVA"; // Precio SIN IVA, el if de abajo se lo agregará
	            break;

	            case '3':
	                $tipoFactura = "EXPORTACIÓN";
	                $flgTipoCalculo = "IVA"; // Precio SIN IVA, pero aquí aplica para el if de abajo 
	            break;

	            case '4':
	                $tipoFactura = "EXENTA";
	                $flgTipoCalculo = "IVA"; // Precio SIN IVA, pero aquí aplica para el if de abajo 
	            break;

	            case '5':
	                $tipoFactura = "N. DÉBITO";
	                $flgTipoCalculo = "NIVA"; // Precio SIN IVA, el if de abajo se lo agregará
	            break;

	            case '8':
	                $tipoFactura = "TICKET";
	                $flgTipoCalculo = "IVA"; // Precios CON IVA
	            break;

	            default:
	                $tipoFactura = "N/A";
	                $flgTipoCalculo = "N/A";
	            break;
	        }

	        if($flgTipoCalculo == "IVA") { // CONSUMIDOR FINAL, EXPORTACIÓN, EXENTA
	            // Sumar sin IVA
	            $totalFactura += $dataTotalFacturaFix->totalVenta;
	        } else if($flgTipoCalculo == "NIVA") { // CRÉDITO FISCAL
	            // Los precios vienen sin IVA, agregárselo
	            $totalFactura += ($dataTotalFacturaFix->totalVenta * $parametrizacionIVA);
	        } else { 
	            // N/A = Otro tipo de documento no especificado
	            $totalFactura += 0.00;
	        }
		}

		// Quitar-agregar el retenido y percibido
		$totalFactura -= $dataComisionRevision->ivaRetenido;
		$totalFactura += $dataComisionRevision->ivaPercibido;

		// Comparar si son iguales, sino dibujar
		if(number_format($totalFactura, 2, ".", ",") == number_format($dataComisionRevision->totalFactura, 2, ".", ",")) {
			// Omitir, se le pagó comisión
		} else {
			$n += 1;
			// Mostrar la factura que no acumuló comisión
			$factura = '
				<b>Vendedor: </b> '.$dataComisionRevision->nombreEmpleado.'<br>
				<b>Tipo de factura: </b> '.$dataComisionRevision->tipoFactura.'<br>
				<b>N° Factura: </b> '.$dataComisionRevision->correlativoFactura.'<br>
				<b>Fecha de factura: </b> '.date("d/m/Y", strtotime($dataComisionRevision->fechaFactura)).'<br>
				<b>Cliente: </b> '.$dataComisionRevision->nombreCliente.'<br>
				<b>Factura: </b> Contado
			';

			$totales = '
				<div class="row">
					<div class="col-4 fw-bold">
						Total Calculado
					</div> 
					<div class="col-8 text-end">
						$ '.number_format($totalFactura, 2, ".", ",").'
					</div>
				</div>
				<div class="row">
					<div class="col-4 fw-bold">
						Total Factura 
					</div> 
					<div class="col-8 text-end">
						$ '.number_format($dataComisionRevision->totalFactura, 2, ".", ",").'
					</div>
				</div>
				<div class="row">
					<div class="col-4 fw-bold">
						Comisión
					</div> 
					<div class="col-8 text-end">
						$ '.number_format($comisionFactura, 2, ".", ",").'
					</div>
				</div>
			';

			$controles = '
				<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalVerFactura(`'.$dataComisionRevision->comisionPagarCalculoId.'`, `F`, `R`);">
					<i class="fas fa-folder-open"></i> 
					<span class="ttiptext">Ver Factura</span>
				</button>			
			';

			$output['data'][] = array(
		        $n,
		        $factura,
		        $totales,
		        $controles
		    );
		}
	}

	// Iterar después los abonos flgIdentificador A, ya que se muestran campos extras
	// Comentareado por ahorita por si se solicita después mostrar también los abonos
	/*
	$dataComisionRevision = $cloud->rows("
		SELECT
			comisionPagarCalculoId,
			nombreEmpleado,
			tipoFactura,
			correlativoFactura,
			fechaFactura,
			sucursalFactura,
			nombreCliente,
			totalFactura,
			comisionPagar,
			fechaAbono,
			totalAbono,
			totalAbonoCalculo,
			tasaComisionAbono,
			comisionAbonoPagar,
			flgComisionEditar,
			comisionPagarEditar,
			ivaRetenido,
			ivaPercibido
		FROM conta_comision_pagar_calculo
		WHERE comisionPagarPeriodoId = ? AND flgIdentificador = 'A' AND flgComisionEditar = '0' AND flgDelete = '0'
		GROUP BY nombreEmpleado, correlativoFactura, tipoFactura, fechaAbono, totalAbono
		ORDER BY fechaFactura, correlativoFactura
	", [$comisionPagarPeriodoId]);

	foreach ($dataComisionRevision as $dataComisionRevision) {
		$dataTotalFacturaFix = $cloud->rows("
            SELECT
                codTipoFactura,
                tipoFactura,
                correlativoFactura,
                fechaFactura,
                nombreCliente,
                lineaProducto,
                codProductoFactura,
                precioUnitario,
                precioFacturado,
                cantidadProducto,
                totalVenta,
                porcentajeDescuento,
                paramRangoPorcentajeInicio,
                paramRangoPorcentajeFin,
                paramPorcentajePago,
                comisionPagar
            FROM conta_comision_pagar_calculo
            WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND nombreCliente = ? AND correlativoFactura = ? AND tipoFactura = ? AND fechaFactura = ? AND sucursalFactura = ? AND fechaAbono = ? AND totalAbono = ? AND flgDelete = '0'
            ORDER BY lineaProducto
		", [$comisionPagarPeriodoId, $dataComisionRevision->nombreEmpleado, $dataComisionRevision->nombreCliente, $dataComisionRevision->correlativoFactura, $dataComisionRevision->tipoFactura, $dataComisionRevision->fechaFactura, $dataComisionRevision->sucursalFactura, $dataComisionRevision->fechaAbono, $dataComisionRevision->totalAbono]);

		$totalFactura = 0.00;
		
		foreach($dataTotalFacturaFix as $dataTotalFacturaFix) {
	        switch ($dataTotalFacturaFix->codTipoFactura) {
	            case '1':
	                $tipoFactura = "C. FINAL";
	                $flgTipoCalculo = "IVA"; // Precios CON IVA
	            break;
	            
	            case '2':
	                $tipoFactura = "C. FISCAL";
	                $flgTipoCalculo = "NIVA"; // Precio SIN IVA, el if de abajo se lo agregará
	            break;

	            case '3':
	                $tipoFactura = "EXPORTACIÓN";
	                $flgTipoCalculo = "IVA"; // Precio SIN IVA, pero aquí aplica para el if de abajo 
	            break;

	            case '4':
	                $tipoFactura = "EXENTA";
	                $flgTipoCalculo = "IVA"; // Precio SIN IVA, pero aquí aplica para el if de abajo 
	            break;

	            case '5':
	                $tipoFactura = "N. DÉBITO";
	                $flgTipoCalculo = "NIVA"; // Precio SIN IVA, el if de abajo se lo agregará
	            break;

	            case '8':
	                $tipoFactura = "TICKET";
	                $flgTipoCalculo = "IVA"; // Precios CON IVA
	            break;

	            default:
	                $tipoFactura = "N/A";
	                $flgTipoCalculo = "N/A";
	            break;
	        }

	        if($flgTipoCalculo == "IVA") { // CONSUMIDOR FINAL, EXPORTACIÓN, EXENTA
	            // Sumar sin IVA
	            $totalFactura += $dataTotalFacturaFix->totalVenta;
	        } else if($flgTipoCalculo == "NIVA") { // CRÉDITO FISCAL
	            // Los precios vienen sin IVA, agregárselo
	            $totalFactura += ($dataTotalFacturaFix->totalVenta * $parametrizacionIVA);
	        } else { 
	            // N/A = Otro tipo de documento no especificado
	            $totalFactura += 0.00;
	        }
		}

		// Quitar-agregar el retenido y percibido
		$totalFactura -= $dataComisionRevision->ivaRetenido;
		$totalFactura += $dataComisionRevision->ivaPercibido;

		// Comparar si son iguales, sino dibujar
		if(number_format($totalFactura, 2, ".", ",") == number_format($dataComisionRevision->totalFactura, 2, ".", ",")) {
			// Omitir, se le pagó comisión
		} else {
			$n += 1;

			$factura = '
				<b>Vendedor: </b> '.$dataComisionRevision->nombreEmpleado.'<br>
				<b>Tipo de factura: </b> '.$dataComisionRevision->tipoFactura.'<br>
				<b>N° Factura: </b> '.$dataComisionRevision->correlativoFactura.'<br>
				<b>Fecha de factura: </b> '.date("d/m/Y", strtotime($dataComisionRevision->fechaFactura)).'<br>
				<b>Cliente: </b> '.$dataComisionRevision->nombreCliente.'<br>
				<b>Fecha del abono: </b> '.date("d/m/Y", strtotime($dataComisionRevision->fechaAbono)).'<br>
				<b>Factura: </b> Abono
			';

			$comisionFactura = $dataComisionRevision->comisionAbonoPagar;

			$totales = '
				<div class="row">
					<div class="col-4 fw-bold">
						Total Calculado
					</div> 
					<div class="col-8 text-end">
						$ '.number_format($totalFactura, 2, ".", ",").'
					</div>
				</div>
				<div class="row">
					<div class="col-4 fw-bold">
						Total Factura
					</div> 
					<div class="col-8 text-end">
						$ '.number_format($dataComisionRevision->totalFactura, 2, ".", ",").'
					</div>
				</div>
				<div class="row">
					<div class="col-4 fw-bold">
						Abono
					</div> 
					<div class="col-8 text-end">
						$ '.number_format($dataComisionRevision->totalAbonoCalculo, 2, ".", ",").'
					</div>
				</div>
				<div class="row">
					<div class="col-4 fw-bold">
						Comisión
					</div> 
					<div class="col-8 text-end">
						$ '.number_format($comisionFactura, 2, ".", ",").'
					</div>
				</div>
				<div class="row">
					<div class="col-4 fw-bold">
						Tasa por abono
					</div> 
					<div class="col-8 text-end">
						'.number_format($dataComisionRevision->tasaComisionAbono, 2, ".", ",").'%
					</div>
				</div>
			';

			$controles = '
				<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalVerFactura(`'.$dataComisionRevision->comisionPagarCalculoId.'`, `A`, `R`);">
					<i class="fas fa-folder-open"></i> 
					<span class="ttiptext">Ver Factura</span>
				</button>
			';

			$output['data'][] = array(
		        $n,
		        $factura,
		        $totales,
		        $controles
		    );
		}
	}*/

	if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>