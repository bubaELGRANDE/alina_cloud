<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataOrganizaciones = $cloud->rows("
        SELECT
			nombreOrganizacionId, 
			tipoOrganizacion, 
			nombreOrganizacion, 
			abreviaturaOrganizacion
        FROM cat_nombres_organizaciones
        WHERE flgDelete = ?
        ORDER BY tipoOrganizacion, nombreOrganizacion
    ", ['0']);
    $n = 0;
    foreach ($dataOrganizaciones as $dataOrganizaciones) {
    	$n += 1;

    	$organizacion = '
    		<b><i class="fas fa-tag"></i> Tipo: </b> '.$dataOrganizaciones->tipoOrganizacion.'<br>
    		<b><i class="fas fa-university"></i> Nombre: </b> '.$dataOrganizaciones->nombreOrganizacion.' ('.$dataOrganizaciones->abreviaturaOrganizacion.')
    	';

	    $acciones = '
			<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalOrganizacion(`editar`,`'.$dataOrganizaciones->nombreOrganizacionId.'`);">
				<i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
			</button>
			<button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarOrganizacion(`'.$dataOrganizaciones->nombreOrganizacionId.'`);">
				<i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
			</button>
	    ';

	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $organizacion,
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