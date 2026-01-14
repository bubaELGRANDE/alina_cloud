<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataUbicaciones = $cloud->rows("SELECT
            ub.clienteUbicacionId,
            ub.clienteId,
            ub.nombreClienteUbicacion,
            ub.tipoUbicacion,
            ub.direccionClienteUbicacion,
            mu.municipioPais AS municipioPais,
            de.departamentoPais AS departamentoPais,
            pa.pais AS pais,
            pa.iconBandera
        FROM fel_clientes_ubicaciones ub
        JOIN cat_paises_municipios mu ON mu.paisMunicipioId = ub.paisMunicipioId
        JOIN cat_paises_departamentos de ON de.paisDepartamentoId = mu.paisDepartamentoId
        JOIN cat_paises pa ON pa.paisId = de.paisId
    	WHERE ub.clienteId = ?  AND ub.flgDelete = '0'
    ", [$_POST["id"]]);

    $n = 0;
    foreach ($dataUbicaciones as $datUbicaciones) {
        $n += 1;
        $nombre = '
            <b><i class="fas fa-map-marker-alt"></i> Nombre ubicación:</b> '.$datUbicaciones->nombreClienteUbicacion.'<br>
            <b><i class="fas fa-map-pin"></i> Tipo de ubicación:</b> '.$datUbicaciones->tipoUbicacion;

        $iconoBandera = "<img src='../libraries/resources/images/$datUbicaciones->iconBandera'>";
        $ubicacion = '
            <b>'.$iconoBandera.' País: </b> '.$datUbicaciones->pais.'
            <br><b><i class="fas fa-route"></i> Dirección: </b> '.$datUbicaciones->direccionClienteUbicacion.', '.$datUbicaciones->municipioPais.', '.$datUbicaciones->departamentoPais.'<br>
        ';
        $jsonContacto = array(
            "typeOperation"         => "insert",
            "tituloModal"           => "Contactos del cliente: " .$datUbicaciones->nombreClienteUbicacion,
            "clienteUbicacionId"    => $datUbicaciones->clienteUbicacionId,
            "clienteId"             => $datUbicaciones->clienteId,
            "nombreCliente"         => $_POST["nombreCliente"],
            "nombreUbicacion"       => $datUbicaciones->nombreClienteUbicacion
        );
        $funcionContacto = htmlspecialchars(json_encode($jsonContacto));
        
        $jsonEdit = array(
            "typeOperation"         => "update",
            "tituloModal"           => "Editar ubicación: " .$datUbicaciones->nombreClienteUbicacion,
            "clienteUbicacionId"    => $datUbicaciones->clienteUbicacionId,
            "idCliente"             => $datUbicaciones->clienteId,
            "nombreCliente"         => $_POST["nombreCliente"]
        );
        $funcionUpdate = htmlspecialchars(json_encode($jsonEdit));
        $jsonDelete = array(
            "typeOperation"         => "delete",
            "operation"             => "direccion-cliente",
            "nombreUbicacion"       => $datUbicaciones->nombreClienteUbicacion,
            "clienteUbicacionId"    => $datUbicaciones->clienteUbicacionId,
            "idCliente"             => $datUbicaciones->clienteId,
            "nombreCliente"         => $_POST["nombreCliente"]
        );
        $funcionDelete = htmlspecialchars(json_encode($jsonDelete));

        $numUbicaciones = $cloud->count("SELECT clienteContactoId FROM fel_clientes_contactos WHERE clienteUbicacionId = ? AND flgDelete = 0", [$datUbicaciones->clienteUbicacionId]);

        $acciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="contactoUbicacion('.$funcionContacto.');">
                <span class="badge d-pill bg-light text-dark">'.$numUbicaciones.'</span>
                <i class="fas fa-id-card"></i>
                <span class="ttiptext">Contactos</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalClienteUbicacion('.$funcionUpdate.');">
                <i class="fas fa-pen"></i>
                <span class="ttiptext">Editar</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="delUbicacion('.$funcionDelete.');">
                <i class="fas fa-trash"></i>
                <span class="ttiptext">Eliminar ubicación</span>
            </button>';
        $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $nombre,
	        $ubicacion,
            $acciones
	    );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }