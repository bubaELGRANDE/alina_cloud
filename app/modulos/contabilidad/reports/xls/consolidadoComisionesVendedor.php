<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
        POST:
        filtroVendedores
        vendedorId[]
    */
  	$parametrizacionIVA = 1.13;

    if($_POST['filtroVendedores'] == "Todos") {
        // Todos los vendedores
        $whereVendedores = "";
    } else {
        // Especifico
        $vendedorId = implode(',', $_POST['vendedorId']);
        $whereVendedores = "AND comisionPagarCalculoId IN ($vendedorId)";
    }
?>
<div class="text-center mb-3">
    <h3>Consolidado de comisiones por vendedor</h3>
</div>
<div class="row mb-4">
    <div class="col-9">
        <button type="button" id="btnReporteExcel" class="btn btn-success ttip">
            <i class="fas fa-file-excel"></i> Excel
            <span class="ttiptext">Descargar reporte en Excel</span>
        </button>
    </div>
</div>
<div class="table-responsive" tabindex="0">
	<?php
		$dataVendedoresComision = $cloud->rows("
			SELECT 
				codEmpleado,
				codVendedor,
			    nombreEmpleado
			FROM conta_comision_pagar_calculo
			WHERE comisionPagarPeriodoId = ? AND flgDelete = '0'
			$whereVendedores
			GROUP BY nombreEmpleado
			ORDER BY nombreEmpleado
		", [$_POST['comisionPagarPeriodoId']]);
		$n = 0;
		$totalGeneralContado = 0;
		$totalGeneralAbono = 0;
		$totalGeneralCompartida = 0;
		$totalGeneral = 0;
		$trComisionVendedores = "";
		foreach($dataVendedoresComision as $dataVendedoresComision) {
			$n++;

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

			$totalVendedorComisionAbono = 0;
			$dataComisionVendedorAbono = $cloud->rows("
				SELECT 
				    comisionAbonoPagar,
				    flgComisionEditar,
				    comisionPagarEditar
				FROM conta_comision_pagar_calculo
				WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = 'A' AND flgDelete = '0'
				GROUP BY nombreEmpleado, correlativoFactura, tipoFactura, fechaAbono, totalAbono, flgRepetidoDiferente
			", [$_POST['comisionPagarPeriodoId'], $dataVendedoresComision->nombreEmpleado]);
			foreach ($dataComisionVendedorAbono as $dataComisionVendedorAbono) {
				$totalVendedorComisionAbono += ($dataComisionVendedorAbono->flgComisionEditar == '1') ? $dataComisionVendedorAbono->comisionPagarEditar : $dataComisionVendedorAbono->comisionAbonoPagar;
			}

		    $dataComisionesCompartidas = $cloud->row("
		        SELECT 
		        	SUM(ccomp.comisionCompartidaPagar) AS comisionCompartidaPagar
		        FROM conta_comision_compartida_calculo ccomp
		        JOIN conta_comision_pagar_calculo cc ON cc.comisionPagarCalculoId = ccomp.comisionPagarCalculoId
		        JOIN conta_comision_compartida_parametrizacion_detalle cparamd ON cparamd.comisionCompartidaParamDetalleId = ccomp.comisionCompartidaParamDetalleId
		        WHERE cc.comisionPagarPeriodoId = ? AND cparamd.nombreEmpleado = ? AND ccomp.flgDelete = ?
		    ", [$_POST['comisionPagarPeriodoId'], $dataVendedoresComision->nombreEmpleado, 0]);

		    $totalComisionCompartida = $dataComisionesCompartidas->comisionCompartidaPagar;

		    $totalComisionVendedor = $totalVendedorComision + $totalVendedorComisionAbono + $totalComisionCompartida;

			$trComisionVendedores .= '
				<tr>
					<td>'.$n.'</td>
					<td>'.$dataVendedoresComision->nombreEmpleado.'</td>
					<td>'.$dataVendedoresComision->codEmpleado.'</td>
					<td>'.$dataVendedoresComision->codVendedor.'</td>
					<td class="text-end">$ '.number_format((float)$totalVendedorComision, 2, ".", ",").'</td>
					<td class="text-end">$ '.number_format((float)$totalVendedorComisionAbono, 2, ".", ",").'</td>
					<td class="text-end">$ '.number_format((float)$totalComisionCompartida, 2, ".", ",").'</td>
					<td class="text-end">$ '.number_format((float)$totalComisionVendedor, 2, ".", ",").'</td>
				</tr>
			';

			$totalGeneralContado += $totalVendedorComision;
			$totalGeneralAbono += $totalVendedorComisionAbono;
			$totalGeneralCompartida += $totalComisionCompartida;
			$totalGeneral += $totalComisionVendedor;
		}
	?>
    <table id="tblReporte" class="table table-hover table-sm">
    	<thead>
    		<tr>
    			<th>#</th>
    			<th>Empleado</th>
    			<th>Código de empleado (Magic)</th>
    			<th>Código de vendedor (Magic)</th>
    			<th>Comisión: Contado</th>
    			<th>Comisión: Abono</th>
    			<th>Comisión: Compartida</th>
    			<th>Total comisión</th>
    		</tr>
    	</thead>
    	<tbody>
    		<?php echo $trComisionVendedores; ?>
		</tbody>
		<tfoot>
			<tr class="fw-bold">
				<td colspan="4">Total general</td>
				<td class="text-end">$ <?php echo number_format((float)$totalGeneralContado, 2, ".", ","); ?></td>
				<td class="text-end">$ <?php echo number_format((float)$totalGeneralAbono, 2, ".", ","); ?></td>
				<td class="text-end">$ <?php echo number_format((float)$totalGeneralCompartida, 2, ".", ","); ?></td>
				<td class="text-end">$ <?php echo number_format((float)$totalGeneral, 2, ".", ","); ?></td>
			</tr>
		</tfoot>
    </table>
</div>
<script>
	$(document).ready(function() {
        $("#btnReporteExcel").click(function(e) {
            $("#tblReporte").table2excel({
                name: `Consolidado de comisiones por vendedor - Periodo: <?php echo $_POST['txtPeriodo']; ?>`,
                filename: `Consolidado de comisiones por vendedor - <?php echo $_POST['txtPeriodo']; ?>`
            });
        });
    });
</script>