<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
	// arrayFormData = personaId ^ nombrePersona
	$arrayFormData = explode("^", $_POST["arrayFormData"]);

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
    ", [$arrayFormData[0]]);
?>

<div class="table-responsive">
	<table id="tblContactosEmpleado" class="table table-hover" style="width: 100%;">
	    <thead>
	    	<tr id="filterboxrow-permisos">
	    		<th>#</th>
		        <th>Contacto</th>
	    	</tr>
	    </thead>
	    <tbody>
            <?php
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
                echo '<tr>
                        <td>'.$n.'</td>
                        <td>'.$empleado.'</td>
                    </tr>';
            }
            ?>
        </tbody>
	</table>
</div>