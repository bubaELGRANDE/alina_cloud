<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $n = 0;

	if($_POST['tipo'] == "Familia") {
		$dataRelaciones = $cloud->rows("
			SELECT 
				pf.prsFamiliaId AS prsFamiliaId, 
				pf.catPrsRelacionId AS catPrsRelacionId,
				rel.tipoPrsRelacion AS parentesco, 
				pf.nombreFamiliar AS nombreFamiliar, 
				pf.apellidoFamiliar AS apellidoFamiliar, 
				pf.fechaNacimiento AS fechaNacimiento, 
				pf.flgBeneficiario AS flgBeneficiario, 
				pf.porcentajeBeneficiario AS porcentajeBeneficiario,
				pf.flgDependeEconomicamente AS flgDependeEconomicamente,
				pf.flgVivenJuntos AS flgVivenJuntos,
				pf.direccionVivenJuntos AS direccionVivenJuntos,
				per.estadoPersona AS estadoPersona,
				per.zonaResidenciaActual AS zonaResidenciaEmpleado
			FROM th_personas_familia pf
			JOIN th_personas per ON per.personaId = pf.personaId
			JOIN cat_personas_relacion rel ON rel.catPrsRelacionId = pf.catPrsRelacionId
			WHERE pf.personaId = ? AND pf.flgDelete = ?
		", [$_POST["id"], '0']);

	    foreach ($dataRelaciones as $dataRelaciones) {
	    	$n += 1;

	    	$relacionFamiliar = '
	    		<b><i class="fas fa-user"></i> Nombre del familiar: </b> '.$dataRelaciones->nombreFamiliar.' '.$dataRelaciones->apellidoFamiliar.'<br>
	    		<b><i class="fas fa-people-arrows"></i> Parentesco: </b> '.$dataRelaciones->parentesco.'<br>
	    		<b><i class="fas fa-calendar"></i> F. nacimiento: </b> '.date("d/m/Y", strtotime($dataRelaciones->fechaNacimiento)).'
	    	';

	    	if($dataRelaciones->flgBeneficiario == "Sí") {
		    	$beneficiario = '
		    		<b><i class="fas fa-user-circle"></i> Edad: </b> '.date_diff(date_create($dataRelaciones->fechaNacimiento), date_create(date("Y-m-d")))->format("%y").' años<br>
		    		<b><i class="fas fa-hand-holding-heart"></i> Beneficiario: </b> '.$dataRelaciones->flgBeneficiario.'<br>
		    		<b><i class="fas fa-hand-holding-usd"></i> Porcentaje del beneficiario: </b> '.number_format($dataRelaciones->porcentajeBeneficiario, 2, '.', ',').'%
		    	';
	    	} else {
		    	$beneficiario = '
		    		<b><i class="fas fa-user-circle"></i> Edad: </b> '.date_diff(date_create($dataRelaciones->fechaNacimiento), date_create(date("Y-m-d")))->format("%y").' años<br>
		    		<b><i class="fas fa-hand-holding-heart"></i> Beneficiario: </b> '.$dataRelaciones->flgBeneficiario.'
		    	';
	    	}

	    	if($dataRelaciones->flgVivenJuntos == "Sí") {
	    		$direccionVivenJuntos = $dataRelaciones->zonaResidenciaEmpleado;
	    	} else {
	    		$direccionVivenJuntos = $dataRelaciones->direccionVivenJuntos;
	    	}

	    	if($dataRelaciones->flgDependeEconomicamente == "Sí") {
	    		$dependeEconomicamente = "
	    			<b><i class='fas fa-people-arrows'></i> Depende económicamente: </b> Sí<br>
	    			<b><i class='fas fa-user-friends'></i> Viven juntos: </b> {$dataRelaciones->flgVivenJuntos}<br>
	    			<b><i class='fas fa-map-marker-alt'></i> Dirección: </b> {$direccionVivenJuntos}
	    		";
	    	} else {
	    		$dependeEconomicamente = "
	    			<b><i class='fas fa-people-arrows'></i> Depende económicamente: </b> No<br>
	    			<b><i class='fas fa-user-friends'></i> Viven juntos: </b> {$dataRelaciones->flgVivenJuntos}<br>
	    			<b><i class='fas fa-map-marker-alt'></i> Dirección: </b> {$direccionVivenJuntos}
	    		";
	    	}

	        if($dataRelaciones->estadoPersona == "Inactivo") { 
	            // Correo agregado automáticamente cuando se creó el usuario, no poderlo modificar ni eliminar
	            // O estado de persona Inactivo, no permitir ninguna acción para no perder historial
	            $disabledEdit = "disabled";
	            $disabledDelete = "disabled";
	        } else {
	            $disabledEdit = 'onclick="editarNucleoFamilia(`'. $dataRelaciones->prsFamiliaId .'`)"';
	            $disabledDelete = 'onclick="delNucleoFamilia(`'. $dataRelaciones->prsFamiliaId .'`)"';
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
	        //
			$output['data'][] = array(
		        $n,
		        $relacionFamiliar,
		        $beneficiario,
		        $dependeEconomicamente,
		        $controles
		    );
		}
	} else {
		// Empresa
		$dataRelaciones = $cloud->rows("
		SELECT 
			thpr.prsRelacionId as prsRelacionId,
	        thpr.personaId2 as personaId2,
			CONCAT(
				IFNULL(per.apellido1, '-'),
				' ',
				IFNULL(per.apellido2, '-'),
				', ',
				IFNULL(per.nombre1, '-'),
				' ',
				IFNULL(per.nombre2, '-')
			) AS nombreCompleto,
			per.estadoPersona AS estadoPersona,
			pr.tipoPrsRelacion as personaRelacion
			FROM ((th_personas_relacion thpr
			JOIN th_personas per ON per.personaId = thpr.personaId2)
			JOIN cat_personas_relacion pr ON pr.catPrsRelacionId = thpr.catPrsRelacionId)
			WHERE thpr.personaId1 = ? AND thpr.flgDelete = '0'
		", [$_POST["id"]]);

	    foreach ($dataRelaciones as $dataRelaciones) {
	    	$n += 1;

	    	if($dataRelaciones->estadoPersona == "Inactivo") {
	    		$disabledInactivo = "disabled";
	    	} else {
	    		$disabledInactivo = "";
	    	}

			$controles ='<button type="button" class="btn btn-primary btn-sm ttip" onclick="editarNucleoEmpresa(`'. $_POST["id"] .'`, `'. $dataRelaciones->personaId2 .'`)" '.$disabledInactivo.'><i class="fas fa-pencil-alt"></i><span class="ttiptext">Editar</span></button>';

			$output['data'][] = array(
		        $n,
		        $dataRelaciones->nombreCompleto,
		        $dataRelaciones->personaRelacion,
		        $controles
		    );
		}
	}

	if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }