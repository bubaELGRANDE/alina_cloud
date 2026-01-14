<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $getParamCompar = $cloud->rows("
        SELECT comisionCompartidaParamDetalleId, codEmpleado, codVendedor, nombreEmpleado, porcentajeComisionCompartida 
        FROM conta_comision_compartida_parametrizacion_detalle
        WHERE flgDelete = 0 AND comisionCompartidaParamId = ?
        ", [$_POST['id']]);

    $n = 0;
    foreach ($getParamCompar as $paramCompar){
        $n += 1;


        $jsonDel = array(
			"typeOperation" 	=> "delete",
			"operation"       	=> "eliminar-parametrizacion-compartida-detalle",
			"idParam"          	=> $paramCompar->comisionCompartidaParamDetalleId,
            "vendedor"          => $paramCompar->nombreEmpleado
		);
		$funcionDel = htmlspecialchars(json_encode($jsonDel));

        $vendedor = "($paramCompar->codVendedor) $paramCompar->nombreEmpleado";
        $porcentaje = $paramCompar->porcentajeComisionCompartida;
        $acciones = '
					<button type="button" class="btn btn-danger btn-sm ttip" onclick="delParamComparDet('.$funcionDel.');">
						<i class="fas fa-trash-alt"></i> 
						<span class="ttiptext">Eliminar parametrización</span>
					</button>';

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $vendedor,
            $porcentaje,
            $acciones
        );
    }

	if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>''));
    }