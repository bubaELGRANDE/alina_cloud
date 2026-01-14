<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    
    /*
        POST:
        periodoBonoId
        personaId
    */

    $dataPeriodo = $cloud->row("
        SELECT mes, anio, estadoPeriodoBono, fechaPagoBono FROM conta_periodos_bonos
        WHERE periodoBonoId = ? AND flgDelete = ?
    ", [$_POST['periodoBonoId'], 0]);

    $dataBonosEmpleado = $cloud->rows("
        SELECT 
            cpb.planillaBonoId AS planillaBonoId,
            cbpd.bonoPersonaId AS bonoPersonaId,
            ccb.numCuentaContable AS numCuentaContable,
            ccb.obsCuentaContable AS obsCuentaContable,
            cpb.montoBono AS montoBono,
            cpb.obsBono AS obsBono
        FROM conta_planilla_bonos cpb
        JOIN conf_bonos_personas_detalle cbpd ON cbpd.bonoPersonaDetalleId = cpb.bonoPersonaDetalleId
        JOIN conta_cuentas_bonos ccb ON ccb.cuentaBonoId = cpb.cuentaBonoId
        WHERE cpb.periodoBonoId = ? AND cbpd.personaId = ? AND cpb.flgDelete = ?
    ", [$_POST['periodoBonoId'], $_POST['personaId'], 0]);

    $n = 0;
    foreach($dataBonosEmpleado as $bonoEmpleado) {
        $n++;

        $dataEncargado = $cloud->row("
            SELECT 
                encarg.personaId AS personaIdEncargado,
                encarg.nombreCompleto AS nombreEncargado
            FROM conf_bonos_personas cbp
            JOIN view_expedientes encarg ON encarg.personaId = cbp.personaId 
            WHERE cbp.bonoPersonaId = ? AND cbp.flgDelete = ?
        ", [$bonoEmpleado->bonoPersonaId, 0]);

        $columnaEncargado = $dataEncargado->nombreEncargado;
        $columnaCuenta = "
            <b><i class='fas fa-list-ol'></i> Cuenta: </b> {$bonoEmpleado->obsCuentaContable}<br>
            <b><i class='fas fa-sort-numeric-up'></i> Número: </b> {$bonoEmpleado->numCuentaContable}
        ";
        $columnaObservaciones = $bonoEmpleado->obsBono;
        $columnaBono = "
            <div class='simbolo-moneda'>
                <span>{$_SESSION['monedaSimbolo']}</span>
                <div>
                    ".number_format($bonoEmpleado->montoBono, 2, ".", ",")."
                </div>
            </div>
        ";

        $jsonEliminarBono = [
            "typeOperation"                 => "delete",
            "operation"                     => "bonos-pagos-empleado",
            "planillaBonoId"                => $bonoEmpleado->planillaBonoId,
            "montoBono"                     => $bonoEmpleado->montoBono,
            "nombreCompleto"                => $_POST['nombreCompleto'],
            "nombreEncargado"               => $dataEncargado->nombreEncargado
        ];

        if($dataPeriodo->estadoPeriodoBono == "Pendiente") {
            if($dataEncargado->personaIdEncargado == $_SESSION['personaId']) {
                $acciones = "
                   <button type='button' class='btn btn-danger btn-sm ttip' onclick='eliminarBonoEmpleado(".htmlspecialchars(json_encode($jsonEliminarBono)).");'>
                        <i class='fas fa-trash-alt'></i>
                        <span class='ttiptext'>Eliminar bono</span>
                    </button>
                ";
            } else {
                $acciones = "
                   <button type='button' class='btn btn-danger btn-sm ttip' disabled>
                        <i class='fas fa-trash-alt'></i>
                        <span class='ttiptext'>Eliminar bono</span>
                    </button>
                ";
            }
        } else {
            $acciones = "
               <button type='button' class='btn btn-danger btn-sm ttip' disabled>
                    <i class='fas fa-trash-alt'></i>
                    <span class='ttiptext'>Eliminar bono</span>
                </button>
            ";
        }

        $output['data'][] = array(
            $n, 
            $columnaEncargado,
            $columnaCuenta,
            $columnaObservaciones,
            $columnaBono,
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