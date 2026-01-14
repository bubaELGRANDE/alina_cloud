<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    
    if($_POST['tipoPersona'] == "Encargado") {
        if(in_array(273, $_SESSION["arrayPermisos"]) || in_array(275, $_SESSION["arrayPermisos"])) {
            // Todos los encargados
            $whereEncargado = "";
        } else {
            // Encargado específico
            $whereEncargado = "AND exp.personaId = '".$_SESSION['personaId']."'";
        }

    	$dataBonosPersonas = $cloud->rows("
    		SELECT
    			bp.bonoPersonaId AS bonoPersonaId,
    			bp.personaId AS personaId,
                bp.cuentaBonoId AS cuentaBonoId,
    			exp.prsExpedienteId AS prsExpedienteId,
    			exp.nombreCompleto AS nombreCompleto,
    			exp.cargoPersona AS cargoPersona
    		FROM conf_bonos_personas bp
    		JOIN view_expedientes exp ON exp.personaId = bp.personaId
    		WHERE exp.estadoPersona = ? AND exp.estadoExpediente = ? AND bp.flgDelete = ?
            $whereEncargado
    		ORDER BY exp.apellido1, exp.apellido2, exp.nombre1, exp.nombre2
    	", ["Activo", "Activo", 0]);
    } else {
    	// Detalle
        $dataBonosPersonas = $cloud->rows("
            SELECT
                bp.bonoPersonaDetalleId AS bonoPersonaDetalleId,
                bp.personaId AS personaId,
                exp.prsExpedienteId AS prsExpedienteId,
                exp.nombreCompleto AS nombreCompleto,
                exp.cargoPersona AS cargoPersona
            FROM conf_bonos_personas_detalle bp
            JOIN view_expedientes exp ON exp.personaId = bp.personaId
            WHERE exp.estadoPersona = ? AND exp.estadoExpediente = ? AND bp.bonoPersonaId = ? AND bp.flgDelete = ?
            ORDER BY exp.apellido1, exp.apellido2, exp.nombre1, exp.nombre2
        ", ["Activo", "Activo", $_POST['bonoPersonaId'], 0]);
    }
    $n = 0;
    foreach($dataBonosPersonas as $bonoPersona) {
        $n++;

        $columnaEmpleado = $bonoPersona->nombreCompleto;
        $columnaCargoActual = $bonoPersona->cargoPersona;

        if($_POST['tipoPersona'] == "Encargado") {
            $empleadosAsignados = $cloud->count("
                SELECT bpd.bonoPersonaDetalleId 
                FROM conf_bonos_personas_detalle bpd
                JOIN view_expedientes exp ON exp.personaId = bpd.personaId
                WHERE bpd.bonoPersonaId = ? AND exp.estadoPersona = ? AND exp.estadoExpediente = ? AND bpd.flgDelete = ?
            ", [$bonoPersona->bonoPersonaId, "Activo", "Activo", 0]);

	        $acciones = "
	        	<button type='button' class='btn btn-primary btn-sm ttip' onclick='changePage(`{$_SESSION['currentRoute']}`, `bonos-pagos`, `bonoPersonaId={$bonoPersona->bonoPersonaId}&periodoBonoId={$_POST['periodoBonoId']}&nombreCompleto={$bonoPersona->nombreCompleto}&cuentaBonoId={$bonoPersona->cuentaBonoId}`);'>
	        		<span class='badge rounded-pill bg-light text-dark'>{$empleadosAsignados}</span> <i class='fas fa-user-tie'></i> Empleados
	        		<span class='ttiptext'>Empleados asignados</span>
	        	</button>
	        ";

            $output['data'][] = array(
                $n, 
                $columnaEmpleado,
                $columnaCargoActual,
                $acciones
            );
        } else {
        	// Detalle
        
            $dataBonoTotal = $cloud->row("
                SELECT 
                    COUNT(cpb.planillaBonoId) AS numBonos,
                    SUM(cpb.montoBono) AS montoBono 
                FROM conta_planilla_bonos cpb
                JOIN conf_bonos_personas_detalle cbpd ON cbpd.bonoPersonaDetalleId = cpb.bonoPersonaDetalleId
                WHERE cpb.periodoBonoId = ? AND cbpd.personaId = ? AND cpb.flgDelete = ?
            ", [$_POST['periodoBonoId'], $bonoPersona->personaId, 0]);

            $bonoTotal = $dataBonoTotal->montoBono;

            $columnaBonoTotal = "
                <div class='simbolo-moneda'>
                    <span>{$_SESSION['monedaSimbolo']}</span>
                    <div>
                        ".number_format((float)$bonoTotal, 2, ".", ",")."
                    </div>
                </div>
            ";

            $jsonBonosEmpleado = [
                "bonoPersonaDetalleId"      => $bonoPersona->bonoPersonaDetalleId,
                "periodoBonoId"             => $_POST['periodoBonoId'],
                "personaId"                 => $bonoPersona->personaId,
                "nombreCompleto"            => $bonoPersona->nombreCompleto,
                "bonoPersonaId"             => $_POST['bonoPersonaId'],
                "nombreEncargado"           => $_POST['nombreEncargado'],
                "cuentaBonoId"              => $_POST['cuentaBonoId']
            ];

            $acciones = "
                <button type='button' class='btn btn-primary btn-sm ttip' onclick='modalAsignarBonosEmpleado(".htmlspecialchars(json_encode($jsonBonosEmpleado)).");'>
                    <span class='badge rounded-pill bg-light text-dark'>{$dataBonoTotal->numBonos}</span> <i class='fas fa-hand-holding-usd'></i> Bonos
                    <span class='ttiptext'>Bonos del empleado</span>
                </button>
            ";

            $output['data'][] = array(
                $n, 
                $columnaEmpleado,
                $columnaCargoActual,
                $columnaBonoTotal,
                $acciones
            );
        }
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        $output['data'] = '';
        // No retornar nada para evitar error "null"
        echo json_encode($output); 
    }
?>