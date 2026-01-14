<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

	$arrayMeses = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

	$dataQuincena = $cloud->row("
		SELECT
			numQuincena, mes, anio, estadoQuincena
		FROM cat_quincenas
		WHERE quincenaId = ? AND flgDelete = ?
	", [$_POST['quincenaId'], 0]);
	$mes = $arrayMeses[$dataQuincena->mes];

	$btnQuincena = ""; $btnCerrarQuincena = "";
	if($dataQuincena->estadoQuincena == "Pendiente") {
		$btnQuincena = "
			<button type='button' class='btn btn-primary ttip' onclick='calcularQuincena(`Calculo`);'>
				<i class='fas fa-calculator'></i> Calcular quincena
				<span class='ttiptext'>Calcular la quincena</span>
			</button>
		";
		$btnCerrarQuincena = "
			<button type='button' class='btn btn-secondary ttip' style='cursor: not-allowed;' disabled>
				<i class='fas fa-user-lock'></i> Finalizar quincena
				<span class='ttiptext'>Se habilitará hasta que calcule la quincena</span>
			</button>
		";
	} else {
		$btnQuincena = "
			<button type='button' class='btn btn-primary ttip'>
				<i class='fas fa-sync'></i> Re-calcular quincena
				<span class='ttiptext'>Re-calcular la quincena</span>
			</button>
		";
		$btnCerrarQuincena = "
			<button type='button' class='btn btn-secondary ttip'>
				<i class='fas fa-user-lock'></i> Finalizar quincena
				<span class='ttiptext'>Finalizar la quincena</span>
			</button>
		";
	}
?>
<h3 class="text-center mb-4">
	<?php echo ($dataQuincena->numQuincena == 1 ? 'Primera' : 'Segunda') . " quincena de $mes - $dataQuincena->anio"; ?>
</h3>
<div class="btn-group shadow-0 mb-4" style="width: 100%;" role="group">
	<?php 
		echo $btnQuincena; 

        $jsonOtrosDescuentosMultiple = array(
            "nombreCompleto"        => "Todos los empleados",
            "prsExpedienteId"       => 0,
            "quincenaId"            => $_POST['quincenaId'],
            "Multiple" 				=> true
        );
        $jsonOtrosDescuentosMultiple = htmlspecialchars(json_encode($jsonOtrosDescuentosMultiple));

        $jsonDevengosGravados = array(
            "nombreCompleto"        => "Todos los empleados",
            "prsExpedienteId"       => 0,
            "quincenaId"            => $_POST['quincenaId'],
            "tipoDevengo"           => "Gravado",
            "Multiple" 				=> true
        );
        $jsonDevengosGravados = htmlspecialchars(json_encode($jsonDevengosGravados));

        $jsonDevengosNoGravados = array(
            "nombreCompleto"        => "Todos los empleados",
            "prsExpedienteId"       => 0,
            "quincenaId"            => $_POST['quincenaId'],
            "tipoDevengo"           => "No Gravado",
            "Multiple" 				=> true
        );
        $jsonDevengosNoGravados = htmlspecialchars(json_encode($jsonDevengosNoGravados));
	?>
	<div class="btn-group" role="group">
		<button type="button" id="btnDevengosMultiple" class="btn btn-outline-secondary dropdown-toggle" data-mdb-toggle="dropdown" aria-expanded="false">
	  		<i class="fas fa-user-plus"></i>
	  		Devengos
		</button>
		<ul class="dropdown-menu" aria-labelledby="btnDevengosMultiple">
	  		<li>
	  			<a class="dropdown-item" role="button" onclick="modalDevengos(<?php echo $jsonDevengosGravados; ?>);">
	  				<i class="fas fa-hand-holding-usd"></i>
	  				Gravados
	  			</a>
	  		</li>
	  		<li>
	  			<a class="dropdown-item" role="button" onclick="modalDevengos(<?php echo $jsonDevengosNoGravados; ?>);">
	  				<i class="fas fa-user-plus"></i>
	  				No Gravados
	  			</a>
	  		</li>
	  		<li>
	  			<a class="dropdown-item" role="button">
	  				<i class="fas fa-user-clock"></i>
	  				Horas extras
	  			</a>
	  		</li>
	  		<li>
	  			<a class="dropdown-item" role="button">
	  				<i class="fas fa-umbrella-beach"></i>
	  				Vacaciones
	  			</a>
	  		</li>
		</ul>
	</div>
	<div class="btn-group" role="group">
		<button type="button" id="btnDescuentosMultiple" class="btn btn-outline-secondary dropdown-toggle" data-mdb-toggle="dropdown" aria-expanded="false">
			<i class="fas fa-user-minus"></i>
			Descuentos
		</button>
		<ul class="dropdown-menu" aria-labelledby="btnDescuentosMultiple">
	  		<li>
	  			<a class="dropdown-item" role="button" onclick="modalOtrosDescuentos(<?php echo $jsonOtrosDescuentosMultiple; ?>);">
	  				<i class="fas fa-folder-minus"></i>
	  				Otros descuentos
	  			</a>
	  		</li>
		</ul>
	</div>
	<div class="btn-group" role="group">
		<button type="button" id="btnEmpleadosPlanilla" class="btn btn-outline-secondary dropdown-toggle" data-mdb-toggle="dropdown" aria-expanded="false">
			<i class="fas fa-user-tie"></i>
			Empleados
		</button>
		<ul class="dropdown-menu" aria-labelledby="btnEmpleadosPlanilla">
	  		<li>
	  			<a class="dropdown-item" role="button">
	  				<i class="fas fa-user-clock"></i>
	  				Planilla: Pendientes
	  			</a>
	  		</li>
	  		<li>
	  			<a class="dropdown-item" role="button">
	  				<i class="fas fa-user-times"></i>
	  				Planilla: Excluidos
	  			</a>
	  		</li>
		</ul>
	</div>
	<?php echo $btnCerrarQuincena; ?>
</div>

<div class="row">
	<div class="col-4">
        <div class="form-select-control mb-4">
            <select id="clasifExpediente" name="clasifExpediente" style="width: 100%;">
                <option value="Todos">Todos los empleados</option>
                <?php 
                    $dataClasificacionGasto = $cloud->rows("
                        SELECT
                            clasifGastoSalarioId,
                            nombreGastoSalario
                        FROM cat_clasificacion_gastos_salario
                        WHERE flgDelete = ?
                    ", [0]);
                    foreach($dataClasificacionGasto as $clasificacionGasto) {
                        echo "<option value='$clasificacionGasto->clasifGastoSalarioId'>$clasificacionGasto->nombreGastoSalario</option>";
                    }
                ?>
            </select>
        </div>
		<div id="divEmpleadosQuincena" class="table-responsive">
		</div>
	</div>
	<div id="divPlanillaEmpleado" class="col-8">
		<div class="d-flex justify-content-center align-items-center" style="height: 100%;">
			<h5>Seleccione al empleado para cargar su planilla</h5>
		</div>
	</div>
</div>
<script>
	$(document).ready(function() {
		$("#estadoQuincena").val('<?php echo $dataQuincena->estadoQuincena; ?>');
		
        $("#clasifExpediente").select2({
            placeholder: 'Clasificación de gasto'
        });

        $("#clasifExpediente").change(function(e) {
            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePlanillaQuincenaExpedientes", 
                {
                	estadoQuincena: `<?php echo $dataQuincena->estadoQuincena; ?>`,
                	clasifGastoSalarioId: $(this).val()
                },
                function(data) {
                    $("#divEmpleadosQuincena").html(data);
                }
            );
        });

        $("#clasifExpediente").trigger("change");
    });
</script>