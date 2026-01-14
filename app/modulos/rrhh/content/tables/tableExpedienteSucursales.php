<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataExpedienteSucursales = $cloud->rows("
        SELECT
            es.bitExpedienteSucursalId AS bitExpedienteSucursalId, 
            es.sucursalDepartamentoIdAnterior AS sucursalDepartamentoIdAnterior, 
            s.sucursal AS sucursalAnterior,
            sd.departamentoSucursal AS departamentoAnterior,
            es.sucursalDepartamentoIdNuevo AS sucursalDepartamentoIdNuevo,
            sn.sucursal AS sucursalCambio,
            sdn.departamentoSucursal AS departamentoCambio,
            DATE_FORMAT(es.fhAdd, '%d/%m/%Y %H:%i:%s') AS fhAddFormat
        FROM bit_expediente_sucursales es
        JOIN cat_sucursales_departamentos sd ON sd.sucursalDepartamentoId = es.sucursalDepartamentoIdAnterior
        JOIN cat_sucursales s ON s.sucursalId = sd.sucursalId
        JOIN cat_sucursales_departamentos sdn ON sdn.sucursalDepartamentoId = es.sucursalDepartamentoIdNuevo
        JOIN cat_sucursales sn ON sn.sucursalId = sdn.sucursalId
        WHERE es.prsExpedienteId = ? AND es.flgDelete = ?
        ORDER BY es.bitExpedienteSucursalId DESC
    ", [$_POST['prsExpedienteId'], 0]);
    $n = 0;
    foreach ($dataExpedienteSucursales as $expedienteSucursal) {
    	$n += 1;

        $columnaSucursalAnterior = "
            <b><i class='fas fa-building'></i> Sucursal: </b> $expedienteSucursal->sucursalAnterior<br>
            <b><i class='far fa-building'></i> Departamento: </b> $expedienteSucursal->departamentoAnterior
        ";

        $columnaSucursalCambio = "
            <b><i class='fas fa-building'></i> Sucursal: </b> $expedienteSucursal->sucursalCambio<br>
            <b><i class='far fa-building'></i> Departamento: </b> $expedienteSucursal->departamentoCambio
        ";

        $columnaFechaCambio = $expedienteSucursal->fhAddFormat;

	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $columnaSucursalAnterior,
            $columnaSucursalCambio,
	        $columnaFechaCambio
	    );
	} // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>