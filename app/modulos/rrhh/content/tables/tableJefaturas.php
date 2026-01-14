<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    // Group by para que solo muestre 1 jefe, lo demás se agregará con los botones de acción
    $dataJefaturas = $cloud->rows("
        SELECT
            jef.expedienteJefaturaId AS expedienteJefaturaId, 
            jef.jefeId AS jefeId,
            jef.flgHeredarPersonal AS flgHeredarPersonal,
            CONCAT(
                IFNULL(pers.apellido1, '-'),
                ' ',
                IFNULL(pers.apellido2, '-'),
                ', ',
                IFNULL(pers.nombre1, '-'),
                ' ',
                IFNULL(pers.nombre2, '-')
            ) AS nombreCompleto,
            car.cargoPersona as cargoPersona,
            dep.departamentoSucursal as departamentoSucursal,
            s.sucursal as sucursal
        FROM th_expediente_jefaturas jef
        LEFT JOIN th_expediente_personas exp ON exp.prsExpedienteId = jef.jefeId
        LEFT JOIN th_personas pers ON pers.personaId = exp.personaId
        LEFT JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
        LEFT JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
        LEFT JOIN cat_sucursales s ON s.sucursalId = dep.sucursalId
        WHERE jef.flgDelete = ?
        GROUP BY jefeId
    ", ['0']);
    $n = 0;
    foreach ($dataJefaturas as $dataJefaturas) {
    	$n += 1;

        $jefatura = '
            <b><i class="fas fa-user-tie"></i> Jefe: </b>'.$dataJefaturas->nombreCompleto.'<br>
            <b><i class="fas fa-briefcase"></i> Cargo:</b> '.$dataJefaturas->cargoPersona.'<br>
            <b><i class="fas fa-building"></i> Sucursal:</b> '.$dataJefaturas->sucursal.'<br>
            <b><i class="fas fa-building"></i> Departamento:</b> '.$dataJefaturas->departamentoSucursal.'
        ';

	    $controles = '';

        $empleadosACargo = $cloud->count("
            SELECT expedienteJefaturaId FROM th_expediente_jefaturas
            WHERE jefeId = ? AND flgDelete = ?
        ", [$dataJefaturas->jefeId, 0]);

        $controles .='
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalJefatura(`editar^'.$dataJefaturas->jefeId.'`);">
                <span class="badge rounded-pill bg-light" style="color: black;">'.$empleadosACargo.'</span>
                <i class="fas fa-users"></i>
                <span class="ttiptext">Empleados a cargo</span>
            </button>
        ';

        $controles .= '
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarJefatura(`'.$dataJefaturas->jefeId.'`, `jefe`, `0`);">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
        ';
	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $jefatura,
	        $controles
	    );
	} // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>