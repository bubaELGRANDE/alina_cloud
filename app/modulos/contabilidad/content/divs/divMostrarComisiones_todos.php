<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

	$parametrizacionIVA = 1.13;
	$mesesAnio = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

	if($_POST['mes'] == '') {
		echo '<h5 class="text-center">Seleccione el mes para cargar las comisiones</h5>';
	} else if($_POST['anio'] == '') {
		echo '<h5 class="text-center">Seleccione el año para cargar las comisiones</h5>';
	} else {
		// Validar si ya existen comisiones generadas en el mes y año solicitado
		$dataExisteCalculo = $cloud->count("
			SELECT
				comisionPagarPeriodoId, archivoCargado, userAdd, fhAdd
			FROM conta_comision_pagar_periodo
			WHERE numMes = ? AND anio = ? AND flgDelete = '0'
		", [$_POST['mes'], $_POST['anio']]);
		if($dataExisteCalculo != 0) {
			$dataPeriodo = $cloud->row("
				SELECT
					comisionPagarPeriodoId, archivoCargado
				FROM conta_comision_pagar_periodo
				WHERE numMes = ? AND anio = ? AND flgDelete = '0'
			", [$_POST['mes'], $_POST['anio']]);
?>
			<h5 class="text-center mb-4">
				Cálculo de Comisiones - Periodo: <?php echo $mesesAnio[$_POST['mes']] . " - " . $_POST['anio']; ?>	
			</h5>
			<hr>
			<div class="text-end">
	            <button type="button" class="btn btn-info ttip" onclick="modalReportes({'comisionPagarPeriodoId': `<?php echo $dataPeriodo->comisionPagarPeriodoId; ?>`, 'periodo': `<?php echo $mesesAnio[$_POST['mes']] . " - " . $_POST['anio']; ?>`});">
	                <i class="fas fa-print"></i> Reportes
	                <span class="ttiptext">Reportes de comisiones</span>
	            </button>
				<button type="button" class="btn btn-secondary ttip" onclick="changePage(`<?php echo $_SESSION['currentRoute'] ?>`, `comisiones-revision`, `mes=<?php echo $_POST['mes']; ?>&anio=<?php echo $_POST['anio']; ?>&periodoId=<?php echo $dataPeriodo->comisionPagarPeriodoId; ?>`);">
					<i class="fas fa-folder-open"></i> Revisión de Comisiones
					<span class="ttiptext">Ver comisiones a cero, editadas, entre otros</span>
				</button>
			</div>
			<hr>
			<div class="row justify-content-center">
				<div class="col-6">
			        <div class="form-outline mb-4">
			        	<i class="fas fa-search trailing"></i>
			            <input type="text" class="form-control" id="inputBusqueda" name="inputBusqueda">
			            <label class="form-label" for="inputBusqueda">Buscar: Vendedor, Totales</label>
			        </div>
				</div>
			</div>

			<div class="table-responsive">
				<table id="tblComisionVendedor" class="table">
					<thead>
						<tr>
							<th>#</th>
							<th>Vendedor</th>
							<th>Totales</th>
							<th></th>
						</tr>
					</thead>
					<tbody id="filtroBusqueda">
						<?php 
							$dataVendedoresComision = $cloud->rows("
								SELECT 
									codEmpleado,
								    nombreEmpleado
								FROM conta_comision_pagar_calculo
								WHERE comisionPagarPeriodoId = ? AND flgDelete = '0'
								GROUP BY nombreEmpleado
								ORDER BY nombreEmpleado
							", [$dataPeriodo->comisionPagarPeriodoId]);
							$n = 0;
							/*
								arrayTotales
								[0] = Ventas
								[1] = Comisión: Ventas
								[2] = Abonos
								[3] = Comisión: Abonos
								[4] = Total (Ventas + Abonos)
								[5] = Total Comisión
							*/
							$arrayTotales = array(0.00, 0.00, 0.00, 0.00, 0.00, 0.00);
							foreach($dataVendedoresComision as $dataVendedoresComision) {
								$n += 1;

								$totalVendedorVenta = 0; $totalVendedorComision = 0;

								$dataTotalesXVendedor = $cloud->row("
									SELECT 
									    SUM(
									        CASE 
									            WHEN codTipoFactura IN ('1', '8') THEN ROUND(totalVenta / $parametrizacionIVA, 2) 
									            ELSE totalVenta 
									        END
									    ) AS totalVendedorVenta
									FROM conta_comision_pagar_calculo
									WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = 'F' AND flgDelete = '0'
								", [$dataPeriodo->comisionPagarPeriodoId, $dataVendedoresComision->nombreEmpleado]);

								$totalVendedorVenta = $dataTotalesXVendedor->totalVendedorVenta;
								// Verificar si $totalVendedorVenta no es nulo y es un número antes de aplicar number_format
								if (isset($totalVendedorVenta) && is_numeric($totalVendedorVenta)) {
								    $totalVendedorVentaFormat = number_format($totalVendedorVenta, 2, '.', ',');
								} else {
								    // Manejo de casos donde $totalVendedorVenta es null o no es un número
								    $totalVendedorVenta = 0;
								    $totalVendedorVentaFormat = "0.00";
								}

								// Sumar las comisiones normales
								$dataComisionVendedorVenta = $cloud->row("
									SELECT 
										SUM(
									        CASE 
									            WHEN flgComisionEditar = '1' THEN comisionPagarEditar 
									            ELSE comisionPagar 
									        END
									    ) AS totalVendedorComision
									FROM conta_comision_pagar_calculo
									WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = 'F' AND flgDelete = '0'
								", [$dataPeriodo->comisionPagarPeriodoId, $dataVendedoresComision->nombreEmpleado]);

								$totalVendedorComision = $dataComisionVendedorVenta->totalVendedorComision;
								// Verificar si $totalVendedorVenta no es nulo y es un número antes de aplicar number_format
								if (isset($totalVendedorComision) && is_numeric($totalVendedorComision)) {
								    $totalVendedorComisionFormat = number_format($totalVendedorComision, 2, '.', ',');
								} else {
								    // Manejo de casos donde $totalVendedorVenta es null o no es un número
								    $totalVendedorComision = 0;
								    $totalVendedorComisionFormat = "0.00";
								}

								$dataTotalesXVendedorAbono = $cloud->rows("
									SELECT
										correlativoFactura,
										totalAbono,
										totalAbonoCalculo,
									    comisionPagar,
									    comisionAbonoPagar
									FROM conta_comision_pagar_calculo
									WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = 'A' AND flgDelete = '0'
									GROUP BY nombreEmpleado, correlativoFactura, tipoFactura, fechaAbono, totalAbono, flgRepetidoDiferente
								", [$dataPeriodo->comisionPagarPeriodoId, $dataVendedoresComision->nombreEmpleado]);

								$totalVendedorVentaAbono = 0; $totalVendedorComisionAbono = 0;
								// Primero se itera la suma ya que el GROUP BY devuelve un total X correlativo de factura
								foreach($dataTotalesXVendedorAbono as $dataTotalesXVendedorAbono) {
									$totalVendedorVentaAbono += $dataTotalesXVendedorAbono->totalAbonoCalculo;
								}

								$totalVendedorComisionAbono = 0;
								$dataComisionVendedorAbono = $cloud->rows("
									SELECT 
									    comisionAbonoPagar,
									    flgComisionEditar,
									    comisionPagarEditar
									FROM conta_comision_pagar_calculo
									WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = 'A' AND flgDelete = '0'
									GROUP BY nombreEmpleado, correlativoFactura, tipoFactura, fechaAbono, totalAbono
								", [$dataPeriodo->comisionPagarPeriodoId, $dataVendedoresComision->nombreEmpleado]);
								foreach ($dataComisionVendedorAbono as $dataComisionVendedorAbono) {
									$totalVendedorComisionAbono += ($dataComisionVendedorAbono->flgComisionEditar == '1') ? $dataComisionVendedorAbono->comisionPagarEditar : $dataComisionVendedorAbono->comisionAbonoPagar;
								}
								$arrayTotales[0] += $totalVendedorVenta;
								$arrayTotales[1] += $totalVendedorComision;
								$arrayTotales[2] += $totalVendedorVentaAbono;
								$arrayTotales[3] += $totalVendedorComisionAbono;
								$arrayTotales[4] += ($totalVendedorVenta + $totalVendedorVentaAbono);
								$arrayTotales[5] += ($totalVendedorComision + $totalVendedorComisionAbono);

								$tableRow = '
									<tr onclick="cargarDetalleVendedor('.$n.',`'.$dataVendedoresComision->nombreEmpleado.'`,'.$dataPeriodo->comisionPagarPeriodoId.');" style="cursor: pointer;">
										<td>'.$n.'</td>
										<td>'.$dataVendedoresComision->nombreEmpleado.'</td>
										<td>
											<div class="row">
												<div class="col-4">Ventas</div>
												<div class="col-8 text-end fw-bold">$ '.$totalVendedorVentaFormat.'</div>
											</div>
											<div class="row">
												<div class="col-4">Comisión: Ventas</div>
												<div class="col-8 text-end fw-bold">$ '.$totalVendedorComisionFormat.'</div>
											</div>
											<div class="row">
												<div class="col-4">Abonos</div>
												<div class="col-8 text-end fw-bold">$ '.number_format($totalVendedorVentaAbono, 2, '.', ',').'</div>
											</div>
											<div class="row">
												<div class="col-4">Comisión: Abonos</div>
												<div class="col-8 text-end fw-bold">$ '.number_format($totalVendedorComisionAbono, 2, '.', ',').'</div>
											</div>
											<hr>
											<div class="row fw-bold">
												<div class="col-4">Total (Ventas + Abonos)</div>
												<div class="col-8 text-end">$ '.number_format(($totalVendedorVenta + $totalVendedorVentaAbono), 2, '.', ',').'</div>
											</div>
											<div class="row fw-bold">
												<div class="col-4">Total Comisión</div>
												<div class="col-8 text-end">$ '.number_format(($totalVendedorComision + $totalVendedorComisionAbono), 2, '.', ',').'</div>
											</div>											
										</td>
										<td id="tdChevron'.$n.'"><i class="fas fa-chevron-down"></i></td>
									</tr>
							        <tr id="trCollapse'.$n.'">
							            <td id="divCollapseLoad'.$n.'" colspan="4">
							            	
							            </td>
							        </tr>
								';
								echo $tableRow;
							}
						?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="2"><b>Total General</b></td>
							<td>
								<div class="row fw-bold">
									<div class="col-4">Ventas</div>
									<div class="col-8 text-end">$ <?php echo number_format($arrayTotales[0], 2, '.', ','); ?></div>
								</div>
								<div class="row fw-bold">
									<div class="col-4">Comisión: Ventas</div>
									<div class="col-8 text-end">$ <?php echo number_format($arrayTotales[1], 2, '.', ','); ?></div>
								</div>
								<div class="row fw-bold">
									<div class="col-4">Abonos</div>
									<div class="col-8 text-end">$ <?php echo number_format($arrayTotales[2], 2, '.', ','); ?></div>
								</div>
								<div class="row fw-bold">
									<div class="col-4">Comisión: Abonos</div>
									<div class="col-8 text-end">$ <?php echo number_format($arrayTotales[3], 2, '.', ','); ?></div>
								</div>
								<hr>
								<div class="row fw-bold">
									<div class="col-4">Total (Ventas + Abonos)</div>
									<div class="col-8 text-end">$ <?php echo number_format($arrayTotales[4], 2, '.', ','); ?></div>
								</div>
								<div class="row fw-bold">
									<div class="col-4">Total Comisión</div>
									<div class="col-8 text-end">$ <?php echo number_format($arrayTotales[5], 2, '.', ','); ?></div>
								</div>	
							</td>
							<td></td>
						</tr>
					</tfoot>
				</table>
			</div>
			<script>
			    function cargarDetalleVendedor(n, vendedor, comisionPagarPeriodoId) {
			        if($(`#trCollapse${n}`).is(":visible")) {
			        	$(`#tdChevron${n}`).html('<i class="fas fa-chevron-down"></i>');
			        } else {
			        	$(`#tdChevron${n}`).html('<i class="fas fa-chevron-up"></i>');
			        	// Cargar el detalle del vendedor
					    asyncDoDataReturn(
					        "<?php echo $_SESSION['currentRoute']; ?>content/divs/divMostrarComisionesDetalle/", 
					        {
					        	comisionPagarPeriodoId: comisionPagarPeriodoId,
					        	vendedor: vendedor,
					        	n: n
					        },
					        function(data) {
					            $(`#divCollapseLoad${n}`).html(data);
					        }
					    );
			        }
			        $(`#trCollapse${n}`).toggle();
			    }
			    $(document).ready(function() {
			        $("[id^='trCollapse']").toggle(false);
			  		$("#inputBusqueda").on("keyup", function() {
			    		var value = $(this).val().toLowerCase();
			    		$("#filtroBusqueda tr").filter(function() {
			      			$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
			    		});
			  		});
	                document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                    new mdb.Input(formOutline).update();
	                });
			    });
			</script>
<?php
		} else {
			echo '<h5 class="text-center">No se ha generado el cálculo de comisiones para el periodo seleccionado</h5>';
		}
	} // Cierre else
?>