<?php 
    @session_start();
	date_default_timezone_set('America/El_Salvador');

    $queryUltimaSesion = "
		SELECT 
			fhLogin,
			fhLogout
		FROM bit_login_usuarios
		WHERE usuarioId = ? AND loginUsuarioId <> ?
		ORDER BY fhLogin DESC
		LIMIT 1
   	";
   	$existeSesion = $cloud->count($queryUltimaSesion,[$_SESSION["usuarioId"], $_SESSION["loginUsuarioId"]]);

   	if($existeSesion > 0) {
   		$dataUltimaSesion = $cloud->row($queryUltimaSesion,[$_SESSION["usuarioId"], $_SESSION["loginUsuarioId"]]);
   		$fhLogin = date("d/m/Y H:i:s", strtotime($dataUltimaSesion->fhLogin));
   		$fhLogout = (is_null($dataUltimaSesion->fhLogout) || $dataUltimaSesion->fhLogout == "") ? "No cerró sesión" : date("d/m/Y H:i:s", strtotime($dataUltimaSesion->fhLogout));
   	} else {
   		$fhLogin = "-";
   		$fhLogout = "-";
   	}

    
    $hora = date("H"); $toastSaludo = "";
    if($hora >= 5 && $hora < 12) {
        $Saludo = '<i class="fas fa-cloud-sun"></i>Buenos días, ' . $_SESSION["nombrePersona"];
    } else if($hora >= 12 && $hora < 18) {
        $Saludo = '<i class="fas fa-sun"></i> Buenas tardes, ' . $_SESSION["nombrePersona"];
    } else if($hora >= 18 && $hora <= 23) {
        $Saludo = '<i class="fas fa-moon"></i> Buenas noches, ' . $_SESSION["nombrePersona"];
    } else {
        $Saludo = '<i class="fas fa-cloud-moon"></i> ¿Buenas madrugadas, ' . $_SESSION["nombrePersona"] . "?";
    }


	$meses = array("cero","Enero","Febrero","Marzo","Abril","Mayo","Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
	$mesActual = $meses[date("n")];
?>

<div class="welcome" style="background-image: url('../libraries/resources/images/home.jpg');">
	<div class="titulo">
		<h2>
			<i class="fas fa-house-user"></i> Escritorio - Cloud
		</h2>		
	</div>
	<div class="inferior">
		<div class="persona"><h3><?php echo $Saludo; ?>.</h3> </div>
		<div class="log">
			<b><i class="far fa-clock"></i> Último inicio de sesión: </b> <?php echo $fhLogin; ?>
			<br>
			<b><i class="fas fa-sign-out-alt"></i> Cierre de sesión: </b> <?php echo $fhLogout; ?>
		</div>
	</div>
</div>
<hr>
<div class="row">
	<div class="col-md-4">
		<h4>Nuestros Valores</h4>
		<div class="alert alert-secondary">
			<div class="row mb-2 pb-2 align-items-center border-bottom border-light">
				<div class="col-3">
					<span class="fa-stack fa-2x">
						<i class="fas fa-circle fa-stack-2x"></i>
						<i class="fas fa-balance-scale fa-stack-1x fa-inverse"></i>
					</span>
				</div>
				<div class="col-9">
					<h5>Integridad</h5>
				</div>
			</div>
			<div class="row mb-2 pb-2 align-items-center border-bottom border-light">
				<div class="col-3">
					<span class="fa-stack fa-2x">
						<i class="fas fa-circle fa-stack-2x"></i>
						<i class="fas fa-handshake fa-stack-1x fa-inverse"></i>
					</span>
				</div>
				<div class="col-9">
					<h5>Respeto</h5>
				</div>
			</div>
			<div class="row mb-2 pb-2 align-items-center border-bottom border-light">
				<div class="col-3">
					<span class="fa-stack fa-2x">
						<i class="fas fa-circle fa-stack-2x"></i>
						<i class="fas fa-hands fa-stack-1x fa-inverse"></i>
					</span>
				</div>
				<div class="col-9">
					<h5>Responsabilidad</h5>
				</div>
			</div>
			<div class="row align-items-center">
				<div class="col-3">
					<span class="fa-stack fa-2x">
						<i class="fas fa-circle fa-stack-2x"></i>
						<i class="fas fa-hands-helping fa-stack-1x fa-inverse"></i>
					</span>
				</div>
				<div class="col-9">
					<h5>Solidaridad</h5>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<h4><i class="fas fa-calendar-alt"></i> Cumpleaños de <?php echo $mesActual; ?></h4>
		<div class="cumpleaños"></div>
	</div>
	<div class="col-md-4">
		<h4><i class="fas fa-bell"></i> Notificaciones del sistema</h4>
		<div class="alert alert-secondary" role="alert">
			Accesos rápidos, Dashboard estadísticos según los menús y permisos asignados, entre otros.
		</div>
		<!-- <h4><i class="fab fa-bitcoin"></i> Bitcoin</h4>
		<div id="divBTC" class="card mb-3">
			
		</div> -->
	</div>
</div>

<script>
	function getCumpleanos(data) {
        asyncDoDataReturn(
            '<?php echo $_SESSION["currentRoute"]; ?>content/divs/getCumpleaños/',
			{data:data},
            function(data) {
                $(".cumpleaños").html(data);
            }
        );  
    }

/*     function getBTC() {
        asyncDoDataReturn(
            '<?php echo $_SESSION["currentRoute"]; ?>content/divs/getBTC/',
			{data: ''},
            function(data) {
                $("#divBTC").html(data);
            }
        );      	
    } */

	$(document).ready(function() {
		//getCumpleanos('data');
		//getBTC();
	});
</script>