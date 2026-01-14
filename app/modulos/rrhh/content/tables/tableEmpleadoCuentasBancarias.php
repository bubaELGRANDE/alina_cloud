<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataEstadoPersona = $cloud->row("
        SELECT estadoPersona FROM th_personas
        WHERE personaId = ?
    ",[$_POST["id"]]);

    $dataEmpleadoCuentasBanco = $cloud->rows("
        SELECT
            cb.prsCBancariaId AS prsCBancariaId, 
            cb.personaId AS personaId, 
            cb.nombreOrganizacionId AS nombreOrganizacionId, 
            org.nombreOrganizacion AS nombreOrganizacion,
            org.abreviaturaOrganizacion AS abreviaturaOrganizacion,
            cb.numeroCuenta AS numeroCuenta, 
            cb.descripcionCuenta AS descripcionCuenta,
            cb.flgCuentaPlanilla AS flgCuentaPlanilla
        FROM th_personas_cbancaria cb
        JOIN cat_nombres_organizaciones org ON org.nombreOrganizacionId = cb.nombreOrganizacionId
        WHERE cb.personaId = ? AND cb.flgDelete = ?
        ORDER BY cb.flgCuentaPlanilla DESC
    ", [$_POST["id"], '0']);
    $n = 0;
    foreach ($dataEmpleadoCuentasBanco as $dataEmpleadoCuentasBanco) {
    	$n += 1;

        if($dataEmpleadoCuentasBanco->flgCuentaPlanilla == 1) {
            $cuentaPlanilla = '<span class="badge bg-info"><i class="fas fa-money-check-alt"></i> Cuenta planillera</span><br>';
        } else {
            $cuentaPlanilla = "";
        }

        if($dataEmpleadoCuentasBanco->descripcionCuenta == "" || is_null($dataEmpleadoCuentasBanco->descripcionCuenta)) {
            $descripcionCuenta = '<br><b><i class="fas fa-edit"></i> Descripción de la cuenta: </b> -';
        } else {
            $descripcionCuenta = '<br><b><i class="fas fa-edit"></i> Descripción de la cuenta: </b>' . $dataEmpleadoCuentasBanco->descripcionCuenta;
        }

    	$cuentaBanco = '
            '.$cuentaPlanilla.'
    		<b><i class="fas fa-university"></i> Banco: </b>' . $dataEmpleadoCuentasBanco->nombreOrganizacion . ' (' . $dataEmpleadoCuentasBanco->abreviaturaOrganizacion . ') <br>
            <b><i class="fas fa-money-check-alt"></i> Número de cuenta: </b>' . $dataEmpleadoCuentasBanco->numeroCuenta . '
            '.$descripcionCuenta.'
        ';	

        if($dataEstadoPersona->estadoPersona == "Inactivo") { 
            // Correo agregado automáticamente cuando se creó el usuario, no poderlo modificar ni eliminar
            // O estado de persona Inactivo, no permitir ninguna acción para no perder historial
            $disabledEdit = "disabled";
            $disabledDelete = "disabled";
        } else {
            $disabledEdit = 'onclick="editarEmpleadoCuenta(`'. $dataEmpleadoCuentasBanco->prsCBancariaId .'`)"';
            $disabledDelete = 'onclick="delEmpleadoCuenta(`'. $dataEmpleadoCuentasBanco->prsCBancariaId .'`)"';
        }

	    $controles = '';
        //if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(25, $_SESSION["arrayPermisos"])) { // edit contacto sucursal
            $controles .='
                <button type="button" class="btn btn-primary btn-sm ttip" '.$disabledEdit.'>
                    <i class="fas fa-pencil-alt"></i>
                    <span class="ttiptext">Editar</span>
                </button>
            ';
        //}
        //if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(26, $_SESSION["arrayPermisos"])) { // del contacto sucursal
            $controles .= '
                <button type="button" class="btn btn-danger btn-sm ttip" '.$disabledDelete.'>
                    <i class="fas fa-trash-alt"></i>
                    <span class="ttiptext">Eliminar</span>
                </button>
            ';
        //}

	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $cuentaBanco,
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