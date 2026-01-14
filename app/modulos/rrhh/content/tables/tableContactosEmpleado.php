<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataEstadoPersona = $cloud->row("
        SELECT
            estadoPersona
        FROM th_personas
        WHERE personaId = ?
    ",[$_POST["id"]]);

    $dataEmpleadoContactos = $cloud->rows("
        SELECT
            pc.prsContactoId AS prsContactoId, 
            pc.tipoContactoId AS tipoContactoId, 
            tc.tipoContacto AS tipoContacto,
            pc.contactoPersona AS contactoPersona, 
            pc.descripcionPrsContacto AS descripcionPrsContacto, 
            pc.visibilidadContacto AS visibilidadContacto,
            pc.estadoContacto AS estadoContacto,
            pc.flgContactoEmergencia AS flgContactoEmergencia
        FROM th_personas_contacto pc
        JOIN cat_tipos_contacto tc ON tc.tipoContactoId = pc.tipoContactoId
        WHERE pc.personaId = ? AND pc.flgDelete = '0'
        ORDER BY pc.flgContactoEmergencia DESC
    ", [$_POST["id"]]);
    $n = 0;
    foreach ($dataEmpleadoContactos as $dataEmpleadoContactos) {
    	$n += 1;

        $estadoContacto = ($dataEmpleadoContactos->estadoContacto == "Activo") ? '<span class="text-success"><b>Activo</b></span>' : '<span class="text-danger"><b>Inactivo</b></span>';

        if($dataEmpleadoContactos->flgContactoEmergencia == 1) {
            $contactoEmergencia = '<span class="badge bg-danger"><i class="fas fa-phone-alt"></i> Contacto de emergencia</span><br>';
        } else {
            $contactoEmergencia = "";
        }

    	$empleado = '
            '.$contactoEmergencia.'
    		<b><i class="fas fa-list-ul"></i> Tipo de contacto: </b>' . $dataEmpleadoContactos->tipoContacto . '<br>
            <b><i class="fas fa-address-book"></i> Contacto: </b>'.$dataEmpleadoContactos->contactoPersona.'<br>
            <b><i class="fas fa-edit"></i> Descripción: </b>'.$dataEmpleadoContactos->descripcionPrsContacto.'<br>
            <b><i class="fas fa-user-lock"></i> Privacidad: </b>'.$dataEmpleadoContactos->visibilidadContacto.'<br>
            <b><i class="fas fa-info-circle"></i> Estado: </b> '.$estadoContacto.'
        ';	

        if($dataEmpleadoContactos->descripcionPrsContacto == "Correo institucional" || $dataEstadoPersona->estadoPersona == "Inactivo") { 
            // Correo agregado automáticamente cuando se creó el usuario, no poderlo modificar ni eliminar
            // O estado de persona Inactivo, no permitir ninguna acción para no perder historial
            $disabledEdit = "disabled";
            $disabledDelete = "disabled";
        } else {
            $disabledEdit = 'onclick="editarContactoEmpleado(`'. $dataEmpleadoContactos->prsContactoId .'`)"';
            $disabledDelete = 'onclick="delContactoEmpleado(`'. $dataEmpleadoContactos->prsContactoId .'`)"';
        }

	    $controles = '';
        //if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(25, $_SESSION["arrayPermisos"])) { // edit contacto sucursal
            $controles .='<button type="button" class="btn btn-primary btn-sm ttip" '.$disabledEdit.'><i class="fas fa-pencil-alt"></i><span class="ttiptext">Editar</span></button> ';
        //}
        //if(in_array(9, $_SESSION["arrayPermisos"]) || in_array(26, $_SESSION["arrayPermisos"])) { // del contacto sucursal
            $controles .= '<button type="button" class="btn btn-danger btn-sm ttip" '.$disabledDelete.'><i class="fas fa-trash-alt"></i><span class="ttiptext">Eliminar</span></button>';
        //}

	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $empleado,
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