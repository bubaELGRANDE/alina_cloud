<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataEstadoProveedor = $cloud->row("
        SELECT estadoProveedor FROM comp_proveedores
        WHERE proveedorId = ? AND flgDelete = ?
    ",[$_POST["proveedorId"], 0]);

    $dataProveedorCuentasBanco = $cloud->rows("
        SELECT
            pb.proveedorCBancariaId AS proveedorCBancariaId, 
            pb.nombreOrganizacionId AS nombreOrganizacionId, 
            org.nombreOrganizacion AS nombreOrganizacion,
            org.abreviaturaOrganizacion AS abreviaturaOrganizacion,
            pb.numeroCuenta AS numeroCuenta, 
            pb.tipoCuenta AS tipoCuenta,
            pb.descripcionCuenta AS descripcionCuenta
        FROM comp_proveedores_cbancaria pb
        JOIN cat_nombres_organizaciones org ON org.nombreOrganizacionId = pb.nombreOrganizacionId
        WHERE pb.proveedorId = ? AND pb.flgDelete = ?
        ORDER BY pb.nombreOrganizacionId, pb.numeroCuenta
    ", [$_POST["proveedorId"], 0]);
    $n = 0;
    foreach ($dataProveedorCuentasBanco as $proveedorCuentaBancaria) {
    	$n += 1;

        if($proveedorCuentaBancaria->descripcionCuenta == "" || is_null($proveedorCuentaBancaria->descripcionCuenta)) {
            $descripcionCuenta = '<br><b><i class="fas fa-edit"></i> Descripción de la cuenta: </b> -';
        } else {
            $descripcionCuenta = '<br><b><i class="fas fa-edit"></i> Descripción de la cuenta: </b>' . $proveedorCuentaBancaria->descripcionCuenta;
        }

    	$cuentaBanco = '
    		<b><i class="fas fa-university"></i> Banco: </b>' . $proveedorCuentaBancaria->nombreOrganizacion . ' (' . $proveedorCuentaBancaria->abreviaturaOrganizacion . ') <br>
            <b><i class="fas fa-money-check-alt"></i> Número de cuenta: </b>' . $proveedorCuentaBancaria->numeroCuenta . '<br>
            <b><i class="fas fa-list-ul"></i> Tipo de cuenta: </b>'.$proveedorCuentaBancaria->tipoCuenta.'
            '.$descripcionCuenta.'
        ';	

        if($dataEstadoProveedor->estadoProveedor == "Inactivo") { 
            // Estado de proveedor Inactivo, no permitir ninguna acción para no perder historial
            $disabledEdit = "disabled";
            $disabledDelete = "disabled";
        } else {
            $disabledEdit = 'onclick="editarProveedorCBancaria(`'. $proveedorCuentaBancaria->proveedorCBancariaId .'`)"';
            $disabledDelete = 'onclick="eliminarProveedorCBancaria(`'. $proveedorCuentaBancaria->proveedorCBancariaId .'`)"';
        }

	    $acciones = '';
        if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(141, $_SESSION["arrayPermisos"])) {
            $acciones .='
                <button type="button" class="btn btn-primary btn-sm ttip" '.$disabledEdit.'>
                    <i class="fas fa-pencil-alt"></i>
                    <span class="ttiptext">Editar</span>
                </button>
            ';
        } else {
            // No tiene permiso de editar
        }

        if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(142, $_SESSION["arrayPermisos"])) {
            $acciones .= '
                <button type="button" class="btn btn-danger btn-sm ttip" '.$disabledDelete.'>
                    <i class="fas fa-trash-alt"></i>
                    <span class="ttiptext">Eliminar</span>
                </button>
            ';
        } else {
            // No tiene permiso de editar
        }

	    $output['data'][] = array(
	        $n, 
	        $cuentaBanco,
	        $acciones
	    );
	} // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>