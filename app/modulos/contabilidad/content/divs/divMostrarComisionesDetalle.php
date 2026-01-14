<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();
	$parametrizacionIVA = 1.13;
	$mesesAnio = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
	/*
		POST:
		comisionPagarPeriodoId
		vendedor (No se envía codEmpleado o codVendedor porque algunos vienen en cero y se totalizan juntos)
		n = Para que los id de acá no choquen
	*/
?>
<h5 class="fw-bold mb-4">Detalle de facturas - Vendedor: <?php echo $_POST["vendedor"]; ?></h5>
<ul class="nav nav-tabs mb-3" id="ntab<?php echo $_POST['n']; ?>" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="ntab<?php echo $_POST['n']; ?>-1" data-mdb-toggle="pill" href="#ntab<?php echo $_POST['n']; ?>-content-1" role="tab" aria-controls="ntab<?php echo $_POST['n']; ?>-content-1" aria-selected="true">
            Facturas
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab<?php echo $_POST['n']; ?>-2" data-mdb-toggle="pill" href="#ntab<?php echo $_POST['n']; ?>-content-2" role="tab" aria-controls="ntab<?php echo $_POST['n']; ?>-content-2" aria-selected="false">
            Abonos
        </a>
    </li>
</ul>
<div class="tab-content" id="ntab-content">
    <div class="tab-pane fade show active" id="ntab<?php echo $_POST['n']; ?>-content-1" role="tabpanel" aria-labelledby="ntab<?php echo $_POST['n']; ?>-1">
	    <div class="table-responsive">
			<table id="tblDetalleFacturas<?php echo $_POST["n"]; ?>" class="table table-hover">
				<thead>
					<tr id="filterboxrow-detalle-factura<?php echo $_POST['n']; ?>">
						<th>#</th>
						<th>Factura</th>
						<th>Totales</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
					<?php 
						// Iterar primero las flgIdentificador F
						$dataDetalleVendedor = $cloud->rows("
							SELECT
								comisionPagarCalculoId,
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
							WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = 'F' AND flgDelete = '0'
							GROUP BY correlativoFactura, fechaFactura
							ORDER BY fechaFactura, correlativoFactura
						", [$_POST['comisionPagarPeriodoId'], $_POST['vendedor']]);
						$n = 0;
						/*
							arrayTotalesFactura
							[0] = Ventas
							[1] = Comisión
						*/
						$arrayTotalesFactura = array(0.00, 0.00); $totalFactura = 0.00;
						foreach ($dataDetalleVendedor as $dataDetalleVendedor) {
							$n += 1;
							$dataComisionVendedorVenta = $cloud->row("
								SELECT 
									SUM(comisionPagar) AS totalVendedorComision 
								FROM conta_comision_pagar_calculo
								WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND nombreCliente = ? AND correlativoFactura = ? AND tipoFactura = ? AND fechaFactura = ? AND sucursalFactura = ? AND flgIdentificador = ? AND fechaAbono = ? AND flgDelete = '0'
							", [$_POST['comisionPagarPeriodoId'], $_POST['vendedor'], $dataDetalleVendedor->nombreCliente, $dataDetalleVendedor->correlativoFactura, $dataDetalleVendedor->tipoFactura, $dataDetalleVendedor->fechaFactura, $dataDetalleVendedor->sucursalFactura, 'F', $dataDetalleVendedor->fechaAbono]);
							$comisionFactura = $dataComisionVendedorVenta->totalVendedorComision;
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
					            WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND correlativoFactura = ? AND fechaFactura = ? AND flgIdentificador = 'F' AND flgDelete = '0'
					            ORDER BY lineaProducto
							", [$_POST['comisionPagarPeriodoId'], $_POST['vendedor'], $dataDetalleVendedor->correlativoFactura, $dataDetalleVendedor->fechaFactura]);
							$totalFactura = 0.00;
							
							foreach($dataTotalFacturaFix as $dataTotalFacturaFix) {
						        switch ($dataTotalFacturaFix->codTipoFactura) {
						            case '1':
						                $tipoFactura = "C. FINAL";
						                $flgTipoCalculo = "IVA"; // Precios CON IVA
						            break;
						            
						            case '2':
						                $tipoFactura = "C. FISCAL";
						                $flgTipoCalculo = "NIVA"; // Precio SIN IVA
						            break;
						            case '3':
						                $tipoFactura = "EXPORTACIÓN";
						                $flgTipoCalculo = "NIVA"; // Precio SIN IVA
						            break;
						            case '4':
						                $tipoFactura = "EXENTA";
						                $flgTipoCalculo = "NIVA"; // Precio SIN IVA
						            break;
						            case '5':
						                $tipoFactura = "N. DÉBITO";
						                $flgTipoCalculo = "NIVA"; // Precio SIN IVA
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
						        if($flgTipoCalculo == "IVA") { // CONSUMIDOR FINAL
						            // Quitar el IVA
						            $totalFactura += round(($dataTotalFacturaFix->totalVenta / $parametrizacionIVA), 2);
						        } else if($flgTipoCalculo == "NIVA") { // CRÉDITO FISCAL, EXPORTACIÓN, EXENTA
						            // Los precios ya vienen sin iva
						            $totalFactura += $dataTotalFacturaFix->totalVenta;
						        } else { 
						            // N/A = Otro tipo de documento no especificado
						            $totalFactura += 0.00;
						        }
							}
							if($dataDetalleVendedor->flgComisionEditar == 0) {
								$divComisionFactura = '
									<div class="row">
										<div class="col-4 fw-bold">
											Comisión
										</div> 
										<div class="col-8 text-end">
											$ '.number_format($comisionFactura, 2, ".", ",").'
										</div>
									</div>
								';
							} else {
								$dataJustificacion = $cloud->row("
									SELECT 
										motivoEditar 
									FROM bit_comisiones_editar
									WHERE comisionPagarCalculoId = ?
								", [$dataDetalleVendedor->comisionPagarCalculoId]);
								$divComisionFactura = '
									<div class="row">
										<div class="col-4 fw-bold">
											Comisión: Calculada
										</div> 
										<div class="col-8 text-end">
											$ '.number_format($comisionFactura, 2, ".", ",").'
										</div>
									</div>
									<div class="row">
										<div class="col-4 fw-bold">
											Comisión: Editada
										</div> 
										<div class="col-8 text-end">
											$ '.number_format($dataDetalleVendedor->comisionPagarEditar, 2, ".", ",").'
										</div>
									</div>
									<div class="row">
										<div class="col-12">
											<b>Justificación/Motivo: </b> '.$dataJustificacion->motivoEditar.'
										</div> 
									</div>
								';								
							}
							echo '
								<tr>
									<td>'.$n.'</td>
									<td>
										<b>Tipo de factura: </b> '.$dataDetalleVendedor->tipoFactura.'<br>
										<b>N° Factura: </b> '.$dataDetalleVendedor->correlativoFactura.'<br>
										<b>Fecha de factura: </b> '.date("d/m/Y", strtotime($dataDetalleVendedor->fechaFactura)).'<br>
										<b>Cliente: </b> '.$dataDetalleVendedor->nombreCliente.'
									</td>
									<td>
										<div class="row">
											<div class="col-4 fw-bold">
												Venta
											</div> 
											<div class="col-8 text-end">
												$ '.number_format($totalFactura, 2, ".", ",").'
											</div>
										</div>
										'.$divComisionFactura.'
									</td>
									<td>
										<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalVerFactura(`'.$dataDetalleVendedor->comisionPagarCalculoId.'`, `F`, `'.$_POST['n'].'`);">
											<i class="fas fa-folder-open"></i> 
											<span class="ttiptext">Ver Factura</span>
										</button>
									</td>
								</tr>
							';
							$arrayTotalesFactura[0] += $totalFactura;
							if($dataDetalleVendedor->flgComisionEditar == 0) {
								$arrayTotalesFactura[1] += $comisionFactura;
							} else {
								$arrayTotalesFactura[1] += $dataDetalleVendedor->comisionPagarEditar;
							}
						}
					?>
				</tbody>
				<tfoot>
					<tr>
						<td></td>
						<td><b>Total General: Facturas</b></td>
						<td>
							<?php 
								echo '
									<div class="row fw-bold">
										<div class="col-4">
											Ventas
										</div> 
										<div class="col-8 text-end">
											$ '.number_format($arrayTotalesFactura[0], 2, ".", ",").'
										</div>
									</div>
									<div class="row fw-bold">
										<div class="col-4">
											Comisión
										</div> 
										<div class="col-8 text-end">
											$ '.number_format($arrayTotalesFactura[1], 2, ".", ",").'
										</div>
									</div>									
								';
							?>
						</td>
						<td></td>
					</tr>
				</tfoot>
			</table>
	    </div>
    </div>
    <div class="tab-pane fade" id="ntab<?php echo $_POST['n']; ?>-content-2" role="tabpanel" aria-labelledby="ntab<?php echo $_POST['n']; ?>-2">
	    <div class="table-responsive">
			<table id="tblDetalleAbonos<?php echo $_POST["n"]; ?>" class="table table-hover">
				<thead>
					<tr id="filterboxrow-detalle-abono<?php echo $_POST['n']; ?>">
						<th>#</th>
						<th>Factura</th>
						<th>Totales</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
					<?php 
						// Iterar después los abonos flgIdentificador A, ya que se muestran campos extras
						$dataDetalleVendedor = $cloud->rows("
							SELECT
								comisionPagarCalculoId,
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
								comisionPagarEditar
							FROM conta_comision_pagar_calculo
							WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = 'A' AND flgDelete = '0'
							GROUP BY nombreEmpleado, correlativoFactura, tipoFactura, fechaAbono, totalAbono, flgRepetidoDiferente
							ORDER BY fechaFactura, correlativoFactura
						", [$_POST['comisionPagarPeriodoId'], $_POST['vendedor']]);
						$n = 0;
						/*
							arrayTotalesAbono
							[0] = Ventas
							[1] = Abonos
							[2] = Comisión
						*/
						$arrayTotalesAbono = array(0.00, 0.00, 0.00);
						foreach ($dataDetalleVendedor as $dataDetalleVendedor) {
							$n += 1;
							/*
								Esta data sobra ya que son abonos, pero si pide ver el total de la venta acá está comentareado		
								<div class="row">
									<div class="col-4 fw-bold">
										Venta
									</div> 
									<div class="col-8 text-end">
										$ '.number_format($dataDetalleVendedor->totalFactura, 2, ".", ",").'
									</div>
								</div>
							*/
							if($dataDetalleVendedor->flgComisionEditar == "1") {
								$dataJustificacion = $cloud->row("
									SELECT 
										motivoEditar 
									FROM bit_comisiones_editar
									WHERE comisionPagarCalculoId = ?
								", [$dataDetalleVendedor->comisionPagarCalculoId]);

								$comisionFactura = $dataDetalleVendedor->comisionPagarEditar;
								$divComisionFactura = '
									<div class="row">
										<div class="col-4 fw-bold">
											Comisión: Calculada
										</div> 
										<div class="col-8 text-end">
											$ '.number_format($dataDetalleVendedor->comisionAbonoPagar, 2, ".", ",").'
										</div>
									</div>
									<div class="row">
										<div class="col-4 fw-bold">
											Comisión: Editada
										</div> 
										<div class="col-8 text-end">
											$ '.number_format($comisionFactura, 2, ".", ",").'
										</div>
									</div>
								';
								$divJustificacion = '
									<div class="row">
										<div class="col-12">
											<b>Justificación/Motivo: </b> '.$dataJustificacion->motivoEditar.'
										</div> 
									</div>
								';
							} else {
								$comisionFactura = $dataDetalleVendedor->comisionAbonoPagar;
								$divComisionFactura = '
									<div class="row">
										<div class="col-4 fw-bold">
											Comisión
										</div> 
										<div class="col-8 text-end">
											$ '.number_format($comisionFactura, 2, ".", ",").'
										</div>
									</div>
								';
								$divJustificacion = '';
							}


							echo '
								<tr>
									<td>'.$n.'</td>
									<td>
										<b>Tipo de factura: </b> '.$dataDetalleVendedor->tipoFactura.'<br>
										<b>N° Factura: </b> '.$dataDetalleVendedor->correlativoFactura.'<br>
										<b>Fecha de factura: </b> '.date("d/m/Y", strtotime($dataDetalleVendedor->fechaFactura)).'<br>
										<b>Cliente: </b> '.$dataDetalleVendedor->nombreCliente.'<br>
										<b>Fecha del abono: </b> '.date("d/m/Y", strtotime($dataDetalleVendedor->fechaAbono)).'
									</td>
									<td>
										<div class="row">
											<div class="col-4 fw-bold">
												Abono
											</div> 
											<div class="col-8 text-end">
												$ '.number_format($dataDetalleVendedor->totalAbonoCalculo, 2, ".", ",").'
											</div>
										</div>
										'.$divComisionFactura.'
										<div class="row">
											<div class="col-4 fw-bold">
												Tasa por abono
											</div> 
											<div class="col-8 text-end">
												'.number_format($dataDetalleVendedor->tasaComisionAbono, 2, ".", ",").'%
											</div>
										</div>
										'.$divJustificacion.'
									</td>
									<td>
										<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalVerFactura(`'.$dataDetalleVendedor->comisionPagarCalculoId.'`, `A`, `'.$_POST['n'].'`);">
											<i class="fas fa-folder-open"></i> 
											<span class="ttiptext">Ver Factura</span>
										</button>
									</td>
								</tr>
							';
							$arrayTotalesAbono[0] += $dataDetalleVendedor->totalFactura;
							$arrayTotalesAbono[1] += $dataDetalleVendedor->totalAbonoCalculo;
							$arrayTotalesAbono[2] += $comisionFactura;
						}
					?>
				</tbody>
				<tfoot>
					<tr>
						<td></td>
						<td><b>Total General: Abonos</b></td>
						<td>
							<?php 
								/*
									Esta data sobra ya que son abonos, pero si pide ver el total de la venta acá está comentareado									
									<div class="row fw-bold">
										<div class="col-4">
											Ventas
										</div> 
										<div class="col-8 text-end">
											$ '.number_format($arrayTotalesAbono[0], 2, ".", ",").'
										</div>
									</div>
								*/
								echo '
									<div class="row fw-bold">
										<div class="col-4">
											Abonos
										</div> 
										<div class="col-8 text-end">
											$ '.number_format($arrayTotalesAbono[1], 2, ".", ",").'
										</div>
									</div>
									<div class="row fw-bold">
										<div class="col-4">
											Comisión
										</div> 
										<div class="col-8 text-end">
											$ '.number_format($arrayTotalesAbono[2], 2, ".", ",").'
										</div>
									</div>									
								';
							?>
						</td>
						<td></td>
					</tr>
				</tfoot>
			</table>
	    </div>
    </div>
</div>
<script>
	$(document).ready(function() {
        $('#tblDetalleFacturas<?php echo $_POST["n"]; ?> thead tr#filterboxrow-detalle-factura<?php echo $_POST["n"]; ?> th').each(function(index) {
            if(index == 1 || index == 2) {
                var title = $('#tblDetalleFacturas<?php echo $_POST["n"]; ?> thead tr#filterboxrow-detalle-factura<?php echo $_POST["n"]; ?> th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}detalle-factura<?php echo $_POST["n"]; ?>" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}detalle-factura<?php echo $_POST["n"]; ?>">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblDetalleFacturas.column($(this).index()).search($(`#input${$(this).index()}detalle-factura<?php echo $_POST["n"]; ?>`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblDetalleFacturas = $('#tblDetalleFacturas<?php echo $_POST["n"]; ?>').DataTable({
            "dom": 'lrtip',
            "autoWidth": false,
            "columns": [
                {"width": "10%"},
                {"width": "35%"},
                {"width": "45%"},
                {"width": "10%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        $('#tblDetalleAbonos<?php echo $_POST["n"]; ?> thead tr#filterboxrow-detalle-abono<?php echo $_POST["n"]; ?> th').each(function(index) {
            if(index == 1 || index == 2) {
                var title = $('#tblDetalleAbonos<?php echo $_POST["n"]; ?> thead tr#filterboxrow-detalle-abono<?php echo $_POST["n"]; ?> th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}detalle-abono<?php echo $_POST["n"]; ?>" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}detalle-abono<?php echo $_POST["n"]; ?>">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblDetalleAbonos.column($(this).index()).search($(`#input${$(this).index()}detalle-abono<?php echo $_POST["n"]; ?>`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblDetalleAbonos = $('#tblDetalleAbonos<?php echo $_POST["n"]; ?>').DataTable({
            "dom": 'lrtip',
            "autoWidth": false,
            "columns": [
                {"width": "10%"},
                {"width": "35%"},
                {"width": "45%"},
                {"width": "10%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>