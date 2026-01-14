<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

	$parametrizacionIVA = 1.13;

    if(isset($_POST['vendedorIdInterfaz'])) {
    	$vendedorId = implode(",", $_POST['vendedorIdInterfaz']);
    } else {
    	// No seleccionó vendedor
    	$vendedorId = 0;
    }

	$dataVendedoresComision = $cloud->rows("
		SELECT 
			codEmpleado,
		    nombreEmpleado
		FROM conta_comision_pagar_calculo
		WHERE comisionPagarPeriodoId = ? AND comisionPagarCalculoId IN ($vendedorId) AND flgDelete = '0'
		GROUP BY nombreEmpleado
		ORDER BY nombreEmpleado
	", [$_POST['comisionPagarPeriodoId']]);
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
	$totalVendedorVenta = 0; $totalVendedorComision = 0;
	if($vendedorId > 0) {
		foreach($dataVendedoresComision as $dataVendedoresComision) {
			$n += 1;

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
			", [$_POST['comisionPagarPeriodoId'], $dataVendedoresComision->nombreEmpleado]);

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
			", [$_POST['comisionPagarPeriodoId'], $dataVendedoresComision->nombreEmpleado]);

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
			", [$_POST['comisionPagarPeriodoId'], $dataVendedoresComision->nombreEmpleado]);

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
			", [$_POST['comisionPagarPeriodoId'], $dataVendedoresComision->nombreEmpleado]);
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
				<tr onclick="cargarDetalleVendedor('.$n.',`'.$dataVendedoresComision->nombreEmpleado.'`,'.$_POST['comisionPagarPeriodoId'].');" style="cursor: pointer;">
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
<script>
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
		echo "<tr><td colspan='4' class='text-center fs-5'>Seleccione el vendedor(es)</td></tr>";
	}
?>