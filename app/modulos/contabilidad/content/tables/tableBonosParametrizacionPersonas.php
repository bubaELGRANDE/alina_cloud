<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    
    if($_POST['tipoPersona'] == "Encargado") {
        if(in_array(272, $_SESSION["arrayPermisos"]) || in_array(278, $_SESSION["arrayPermisos"])) {
            // Todas las parametrizaciones
            $whereEncargado = "";
        } else {
            // Parametrización específica
            $whereEncargado = "AND exp.personaId = '".$_SESSION['personaId']."'";
        }

    	$dataBonosPersonas = $cloud->rows("
    		SELECT
    			bp.bonoPersonaId AS bonoPersonaId,
    			bp.personaId AS personaId,
    			exp.prsExpedienteId AS prsExpedienteId,
    			exp.nombreCompleto AS nombreCompleto,
    			exp.cargoPersona AS cargoPersona,
                bp.cuentaBonoId AS cuentaBonoId,
                cb.numCuentaContable AS numCuentaContable,
                cb.obsCuentaContable AS obsCuentaContable
    		FROM conf_bonos_personas bp
    		JOIN view_expedientes exp ON exp.personaId = bp.personaId
            LEFT JOIN conta_cuentas_bonos cb ON cb.cuentaBonoId = bp.cuentaBonoId
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

        if($_POST['tipoPersona'] == "Encargado") {
            $columnaEmpleado = "
                <b><i class='fas fa-user-tie'></i> Encargado: </b> {$bonoPersona->nombreCompleto}<br>
                <b><i class='fas fa-briefcase'></i> Cargo: </b> {$bonoPersona->cargoPersona}
            ";
            $columnaCuentaOCargo = "
                <b><i class='fas fa-list-ol'></i> Cuenta: </b> {$bonoPersona->obsCuentaContable}<br>
                <b><i class='fas fa-sort-numeric-up'></i> Número: </b> {$bonoPersona->numCuentaContable}
            "; 

        	$empleadosAsignados = $cloud->count("
        		SELECT bpd.bonoPersonaDetalleId 
                FROM conf_bonos_personas_detalle bpd
                JOIN view_expedientes exp ON exp.personaId = bpd.personaId
        		WHERE bpd.bonoPersonaId = ? AND exp.estadoPersona = ? AND exp.estadoExpediente = ? AND bpd.flgDelete = ?
        	", [$bonoPersona->bonoPersonaId, "Activo", "Activo", 0]);

            $jsonEncargadoCuenta = [
                "bonoPersonaId"             => $bonoPersona->bonoPersonaId,
                "nombreCompleto"            => $bonoPersona->nombreCompleto,
                "cuentaBonoId"              => $bonoPersona->cuentaBonoId
            ];

            $jsonEmpleadosAsignados = [
                "bonoPersonaId" 			=> $bonoPersona->bonoPersonaId,
                "nombreCompleto" 			=> $bonoPersona->nombreCompleto
            ];

            $jsonEliminarEncargado = [
                "typeOperation"             => "delete",
                "operation"                 => "bonos-encargados",
                "bonoPersonaId" 			=> $bonoPersona->bonoPersonaId,
                "nombreCompleto" 			=> $bonoPersona->nombreCompleto
            ];

	        $acciones = "
                <button type='button' class='btn btn-primary btn-sm ttip' onclick='modalEncargadoCuentaPrincipal(".htmlspecialchars(json_encode($jsonEncargadoCuenta)).");'>
                    <i class='fas fa-list-ol'></i>
                    <span class='ttiptext'>Cuenta principal del encargado</span>
                </button>

	        	<button type='button' class='btn btn-primary btn-sm ttip' onclick='modalBonoEmpleados(".htmlspecialchars(json_encode($jsonEmpleadosAsignados)).");'>
	        		<span class='badge rounded-pill bg-light text-dark'>{$empleadosAsignados}</span> <i class='fas fa-user-tie'></i> Empleados
	        		<span class='ttiptext'>Empleados asignados</span>
	        	</button>
	        ";
            /*
                Se quita botón eliminar porque si se borra al encargado, se pierde el historial en la interfaz de pagos
                <button type='button' class='btn btn-danger btn-sm ttip' onclick='eliminarBonoEncargado(".htmlspecialchars(json_encode($jsonEliminarEncargado)).");'>
                    <i class='fas fa-trash-alt'></i>
                    <span class='ttiptext'>Eliminar encargado</span>
                </button>
            */
        } else {
        	// Detalle
            $columnaEmpleado = $bonoPersona->nombreCompleto;
            $columnaCuentaOCargo = $bonoPersona->cargoPersona; 

            $jsonEliminarEncargado = [
                "typeOperation"             => "delete",
                "operation"                 => "bonos-encargados-detalle",
                "bonoPersonaDetalleId"      => $bonoPersona->bonoPersonaDetalleId,
                "nombreCompleto"            => $bonoPersona->nombreCompleto,
                "nombreEncargado"           => $_POST['nombreEncargado']
            ];

            $acciones = "
                <button type='button' class='btn btn-danger btn-sm ttip' onclick='eliminarBonoEmpleadoAsignado(".htmlspecialchars(json_encode($jsonEliminarEncargado)).");'>
                    <i class='fas fa-trash-alt'></i>
                    <span class='ttiptext'>Eliminar asignación</span>
                </button>
            ";
        }

        $output['data'][] = array(
            $n, 
            $columnaEmpleado,
            $columnaCuentaOCargo,
            $acciones
        );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        $output['data'] = '';
        // No retornar nada para evitar error "null"
        echo json_encode($output); 
    }
?>