<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $getParamCompar = $cloud->rows("
        SELECT comisionCompartidaParamId, numRegistroCliente, nombreCliente, descripcionParametrizacion 
        FROM conta_comision_compartida_parametrizacion
        WHERE flgDelete = 0
        ");

    $n = 0;
    foreach ($getParamCompar as $paramCompar){
        $n += 1;

		$contador = $cloud->count("SELECT comisionCompartidaParamDetalleId FROM conta_comision_compartida_parametrizacion_detalle WHERE comisionCompartidaParamId = ? AND flgDelete = 0", [$paramCompar->comisionCompartidaParamId]);

        $jsonVendedores = array(
			"tituloModal"   	=> "Parametrización de vendedores - Cliente: $paramCompar->nombreCliente",
			"idDParam"   	    => $paramCompar->comisionCompartidaParamId
		);
		$funcionVendedores = htmlspecialchars(json_encode($jsonVendedores));

        $jsonDel = array(
			"typeOperation" 	=> "delete",
			"operation"       	=> "eliminar-parametrizacion-compartida",
			"idParam"          	=> $paramCompar->comisionCompartidaParamId,
            "nombreCliente"     => $paramCompar->nombreCliente
		);
		$funcionDel = htmlspecialchars(json_encode($jsonDel));

        $Cliente = "($paramCompar->numRegistroCliente) $paramCompar->nombreCliente";
        $descripcion = $paramCompar->descripcionParametrizacion;
        $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalParamVendedores('.$funcionVendedores.');">
						<span class="badge bg-light text-dark">'.$contador.'</span>
						<i class="fas fa-user-tie"></i>
						<span class="ttiptext">Vendedores</span>
					</button>
					<button type="button" class="btn btn-danger btn-sm ttip" onclick="delParamCompar('.$funcionDel.');">
						<i class="fas fa-trash-alt"></i> 
						<span class="ttiptext">Eliminar parametrización</span>
					</button>';

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $Cliente,
            $descripcion,
            $acciones
        );
    }

	if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>''));
    }