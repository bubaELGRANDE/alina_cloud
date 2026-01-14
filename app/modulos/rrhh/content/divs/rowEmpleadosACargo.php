<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $txtTitulo = "Empleados a cargo:";

    $txtContenido = '';

    $dataEmpleadosACargo = $cloud->rows("
        SELECT 
            exp.estadoExpediente as estadoExpediente,
            per.estadoPersona as estadoPersona,
            CONCAT(
                IFNULL(per.apellido1, '-'),
                ' ',
                IFNULL(per.apellido2, '-'),
                ', ',
                IFNULL(per.nombre1, '-'),
                ' ',
                IFNULL(per.nombre2, '-')
            ) AS nombreCompleto,
            car.cargoPersona as cargoPersona,
            dep.departamentoSucursal as departamentoSucursal,
            s.sucursal as sucursal
        FROM th_expediente_jefaturas jef
        JOIN th_expediente_personas exp ON exp.prsExpedienteId = jef.prsExpedienteId
        JOIN th_personas per ON per.personaId = exp.personaId
        JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
        JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
        JOIN cat_sucursales s ON s.sucursalId = dep.sucursalId
        WHERE jef.jefeId = ? AND jef.flgDelete = ?
        ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
    ", [$_POST['jefeId'], 0]);
    $n = 0;
    foreach ($dataEmpleadosACargo as $dataEmpleadosACargo) {
        $n += 1;
        $txtContenido .= '
            <li>'.$dataEmpleadosACargo->nombreCompleto.' ('.$dataEmpleadosACargo->cargoPersona.')</li>
        ';
    }

    if($n == 0) {
        echo 'No se encontraron registros';
    } else {
        echo '
            <b>'.$txtTitulo.'</b><br>
            <ul>
                '.$txtContenido.'
            </ul>
        ';
    }
?>