<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $arrayMeses = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

    $dataPeriodo = $cloud->row("
        SELECT mes, anio, estadoPeriodoBono, fechaPagoBono FROM conta_periodos_bonos
        WHERE periodoBonoId = ? AND flgDelete = ?
    ", [$_POST['periodoBonoId'], 0]);
    
    $txtPeriodo = $arrayMeses[$dataPeriodo->mes] . " - " . $dataPeriodo->anio;

    $jsonCierrePeriodo = [
        "periodoBonoId"         => $_POST['periodoBonoId'],
        "txtPeriodo"            => $txtPeriodo
    ];

    if($dataPeriodo->estadoPeriodoBono == "Pendiente") {
        if(in_array(273, $_SESSION["arrayPermisos"]) || in_array(277, $_SESSION["arrayPermisos"])) {
            $btnCierre = "
                <button type='button' class='btn btn-primary' onclick='modalCierrePeriodo(".htmlspecialchars(json_encode($jsonCierrePeriodo)).");'>
                    <i class='fas fa-user-lock'></i> Cerrar periodo
                </button>
            ";
        } else {
            $btnCierre = "";
        }

    	echo "
    		<div class='row align-items-center'>
    			<div class='col-6'>
    				<b><i class='fas fa-user-clock'></i> Estado: </b> <font class='text-warning fw-bold'>Pendiente de pago</font>
    			</div>
    			<div class='col-6'>
                    {$btnCierre}
    			</div>
    		</div>
    	";
    } else {
        if(in_array(273, $_SESSION["arrayPermisos"]) || in_array(277, $_SESSION["arrayPermisos"])) {
            $btnCierre = "
                <button type='button' class='btn btn-info' onclick='modalCierrePeriodo(".htmlspecialchars(json_encode($jsonCierrePeriodo)).");'>
                    <i class='fas fa-print'></i> Reportes
                </button>
            ";
        } else {
            $btnCierre = "";
        }

    	echo "
    		<div class='row align-items-center'>
    			<div class='col-6 text-start'>
    				<b><i class='fas fa-user-clock'></i> Estado: </b> <font class='text-success fw-bold'>Pagado</font><br>
					<b><i class='fas fa-calendar-day'></i> Fecha de pago: </b> ".date("d/m/Y", strtotime($dataPeriodo->fechaPagoBono))."
    			</div>
    			<div class='col-6'>
                    {$btnCierre}
    			</div>
    		</div>
    	";
    }
?>