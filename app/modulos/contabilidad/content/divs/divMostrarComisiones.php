<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

	$mesesAnio = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

	if($_POST['mes'] == '') {
		echo '<h5 class="text-center">Seleccione el mes para cargar las comisiones</h5>';
	} else if($_POST['anio'] == '') {
		if($_POST['flgMensajeEspere'] == "1") {
			echo '<h5 class="text-center">Por favor espere...</h5>';
		} else {
			echo '<h5 class="text-center">Seleccione el año para cargar las comisiones</h5>';
		}
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
	            <button type="button" class="btn btn-secondary ttip" onclick="comisionCompartidaCalculo(`<?php echo $dataPeriodo->comisionPagarPeriodoId; ?>`);">
	            	<i class="fas fa-sync-alt"></i> Comisión compartida
	            	<span class="ttiptext">Calcular comisión compartida entre vendedores</span>
	            </button>
				<button type="button" class="btn btn-secondary ttip" onclick="changePage(`<?php echo $_SESSION['currentRoute'] ?>`, `comisiones-revision`, `mes=<?php echo $_POST['mes']; ?>&anio=<?php echo $_POST['anio']; ?>&periodoId=<?php echo $dataPeriodo->comisionPagarPeriodoId; ?>`);">
					<i class="fas fa-folder-open"></i> Revisión de Comisiones
					<span class="ttiptext">Ver comisiones a cero, editadas, entre otros</span>
				</button>
			</div>
			<hr>
			<div class="row justify-content-center">
				<div class="col-6">
		            <div class="form-select-control mb-4">
		            	<input type="hidden" id="comisionPagarPeriodoId" name="comisionPagarPeriodoId" value="<?php echo $dataPeriodo->comisionPagarPeriodoId; ?>">
		                <select id="vendedorIdInterfaz" name="vendedorIdInterfaz[]" style="width: 100%;" multiple="multiple" required>
		                    <option></option>
		                    <?php 
		                        $dataVendedoresComision = $cloud->rows("
		                            SELECT 
		                                comisionPagarCalculoId,
		                                codEmpleado,
		                                nombreEmpleado
		                            FROM conta_comision_pagar_calculo
		                            WHERE comisionPagarPeriodoId = ? AND flgDelete = ?
		                            GROUP BY nombreEmpleado
		                            ORDER BY nombreEmpleado
		                        ", [$dataPeriodo->comisionPagarPeriodoId, 0]);
		                        foreach ($dataVendedoresComision as $dataVendedoresComision) {
		                            // el value es la PK porque codEmpleado y nombreEmpleado vienen de magic y no se podrá usar "IN" de SQL para consultar rápidamente sus valores
		                            echo '<option value="'.$dataVendedoresComision->comisionPagarCalculoId.'">'.$dataVendedoresComision->nombreEmpleado.'</option>';
		                        }
		                    ?>
		                </select>
		            </div>
		            <div class="text-end">
			            <button type="button" class="btn btn-primary ttip" onclick="cargarComisionesVendedor();">
			            	<i class="fas fa-search"></i> Buscar
			            	<span class="ttiptext">Buscar comisiones de los vendedores seleccionados</span>
			            </button>
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
						<tr><td colspan='4' class='text-center fs-5'>Seleccione el vendedor(es)</td></tr>
					</tbody>
				</table>
			</div>
			<script>
			    $(document).ready(function() {
			        $("#vendedorIdInterfaz").select2({
			            placeholder: 'Vendedor(es)'
			        });   
			    });
			</script>
<?php
		} else {
			echo '<h5 class="text-center">No se ha generado el cálculo de comisiones para el periodo seleccionado</h5>';
		}
	} // Cierre else
?>