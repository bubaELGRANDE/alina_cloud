<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataDepartamentos = $cloud->rows("
        SELECT
        	sucursalDepartamentoId,
			departamentoSucursal,
			codSucursalDepartamento
        FROM cat_sucursales_departamentos
        WHERE sucursalId = ? AND flgDelete = '0'
        ORDER BY codSucursalDepartamento, departamentoSucursal
    ", [$_POST["id"]]);
    $n = 0;
    foreach ($dataDepartamentos as $dataDepartamentos) {
    	$n += 1;

    	$departamento = '
    		<b><i class="fas fa-building"></i> Departamento: </b> '.$dataDepartamentos->departamentoSucursal.'<br>
    		<b><i class="fas fa-tag"></i> Código del Departamento: </b> '.$dataDepartamentos->codSucursalDepartamento.'
    	';

	    $acciones = '
			<button type="button" class="btn btn-primary btn-sm ttip" onclick="editDepartamento(`'. $dataDepartamentos->sucursalDepartamentoId .'`);">
				<i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
			</button>
			<button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarDepartamento(`'.$dataDepartamentos->sucursalDepartamentoId.'`);">
				<i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
			</button>
	    ';

	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $departamento,
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