<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataClienteRelacion = $cloud->rows("
    SELECT c.nombreCliente, cl.clienteRelacionId, cl.tipoRelacion, cl.clienteId, cl.razonSocial, p.pais, p.iconBandera
    FROM fel_clientes_relacion cl
    JOIN cat_paises p ON cl.paisId = p.paisId
    JOIN fel_clientes c ON cl.clienteId = c.clienteId
    WHERE cl.tipoRelacion = ? AND cl.clienteId = ? AND cl.flgDelete = 0
    ", [$_POST['tipoRelacion'], $_POST['id']]);

    $n = 0;
    foreach ($dataClienteRelacion as $clienteRelacion) {
        $n += 1;
        $razonSocial = $clienteRelacion->razonSocial;

        $iconoBandera = "<img src='../libraries/resources/images/$clienteRelacion->iconBandera'> ";
        $nacionalidad = $iconoBandera.$clienteRelacion->pais;

        $jsonEditar = array(
            "typeOperation"         => "update",
            "clienteRelacionId"     => $clienteRelacion->clienteRelacionId,
            "idCliente"             => $clienteRelacion->clienteId,
            "nombreCliente"         => $clienteRelacion->nombreCliente,
            "tipoRelacion"          => $clienteRelacion->tipoRelacion,
            "tituloModal"           => "Editar " . $clienteRelacion->tipoRelacion
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));
        
        $jsonEliminar = array(
            "typeOperation"         => "delete",
            "operation"             => "datos-cliente-proveedor",
            "clienteRelacionId"     => $clienteRelacion->clienteRelacionId,
            "nombreCliente"         => $clienteRelacion->nombreCliente,
            "tipoRelacion"          => $clienteRelacion->tipoRelacion,
            "razonSocial"           => $clienteRelacion->razonSocial
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $editar = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalClienProv('.$funcionEditar.');">
            <i class="fas fa-pen"></i>
            <span class="ttiptext">Editar</span>
        </button> ';
        $eliminar = '
            <button type="button" class="btn btn-danger btn-sm ttip" onClick="delClienteRel('.$funcionEliminar.');">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar cliente</span>
            </button> 
        ';

        $acciones = $editar.$eliminar;

        $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $razonSocial,
	        $nacionalidad,
            $acciones
	    );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }