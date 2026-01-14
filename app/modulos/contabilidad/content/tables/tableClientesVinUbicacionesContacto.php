<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();


    $dataContacto = $cloud->rows("SELECT
    c.clienteContactoId,
    c.clienteUbicacionId,
    c.contactoCliente,
    c.descripcionContactoCliente, 
    tc.tipoContacto
    FROM fel_clientes_contactos c
    JOIN cat_tipos_contacto tc ON c.tipoContactoId = tc.tipoContactoId
    WHERE c.flgDelete = 0 AND c.clienteUbicacionId =  ?
    ", [$_POST['id']]);
    $x = 0;
    foreach ($dataContacto as $dataContacto) {
        $x += 1;
    
        $contacto = '
        <b><i class="fas fa-tag"></i> Tipo de contacto: </b> '.$dataContacto->tipoContacto.'<br>
        <b><i class="fas fa-address-book"></i> Contacto: </b> '.$dataContacto->contactoCliente.'<br>
        <b><i class="fas fa-edit"></i> Descripción de contacto: </b> '.$dataContacto->descripcionContactoCliente.'
    ';
        $jsonEliminar = array(
            "typeOperation"         => 'delete',
            "operation"             => 'contacto-cliente',
            "clienteUbicacionId"    => $dataContacto->clienteUbicacionId,
            "clienteContactoId"     => $dataContacto->clienteContactoId,
            "nombreCliente"         => $_POST["nombreCliente"],
            "nombreUbicacion"       => $_POST["nombreUbicacion"],
            "tipoContacto"          => $dataContacto->tipoContacto,
            "contactoCliente"       => $dataContacto->contactoCliente

        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $eliminar = '<button type="button" class="btn btn-danger btn-sm ttip" onclick="delContactoUbicacion('.$funcionEliminar.');">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button> ';
        $editar = '<button type="button" class="btn btn-primary btn-sm ttip" onclick="editUbicacion(`'.$dataContacto->clienteContactoId.'`);">
        <i class="fas fa-pen"></i>
        <span class="ttiptext">Editar</span>
    </button> ';

        $acciones = $editar . $eliminar;
    
        $output['data'][] = array(
            $x, // es #, se dibuja solo en el JS de datatable
            $contacto,
            $acciones
        );
    }


if($x > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data'=>'')); 
}
