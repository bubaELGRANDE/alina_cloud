<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $yearBD = $_POST['yearBD'];

    $dataComplemento = $cloud->rows("
    SELECT
        facturaComplementoId,
        facturaId,
        complementoFactura
    FROM fel_factura_complementos$yearBD
    WHERE flgDelete = ? AND facturaId =  ?
    ", [0,$_POST['facturaId']]);
    $n = 0;
    foreach ($dataComplemento as $dataComplemento) {
        $n += 1;

        $jsonEliminar = array(
            'typeOperation'         => "delete",
            'operation'             => "complemento-DTE",
            'facturaComplementoId'  => $dataComplemento->facturaComplementoId,
            'yearBD'                => $yearBD
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $complemento = '<b>Complemento: </b> '.$dataComplemento->complementoFactura;

        $acciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="editComplemento('.$dataComplemento->facturaComplementoId.');">
                <i class="fas fa-edit"></i>
                <span class="ttiptext">Editar</span>
            </button> 
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarComplemento('.$funcionEliminar.');">
                <i class="fas fa-trash"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
        ';
    
        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $complemento,
            $acciones
        );
    }


if($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data'=>'')); 
}
