<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $getBodegasEmpleado = $cloud->rows("
        SELECT 
            s.bodegaId AS bodegaId, 
            s.sucursalId AS sucursalId,
            s.codSucursalBodega AS codSucursalBodega,
            s.bodegaSucursal AS bodegaSucursal,
            cps.personaSucursalId AS personaSucursalId,
            cps.personaId AS personaId,
            cs.sucursal AS sucursal,
            cpb.personaSucursalBodegaId AS personaSucursalBodegaId
        FROM cat_sucursales_bodegas s
        JOIN conf_personas_sucursales cps ON s.sucursalId = cps.sucursalId
        JOIN cat_sucursales cs ON s.sucursalId = cs.sucursalId
        JOIN conf_personas_sucursales_bodegas cpb ON s.bodegaId = cpb.bodegaId AND cps.personaSucursalId = cpb.personaSucursalId
        WHERE cpb.flgDelete = ? AND s.flgDelete = ? AND cps.personaId = ? 
        AND s.bodegaId IN (
                SELECT cpb2.bodegaId 
                FROM conf_personas_sucursales_bodegas cpb2
                WHERE cpb2.personaSucursalId = cps.personaSucursalId 
                AND cpb2.flgDelete = ?
            )
        ORDER BY s.bodegaSucursal
    ", [0, 0, $_POST["personaId"], 0]);

    $n = 0;
    foreach ($getBodegasEmpleado as $getBodegasEmpleado) {
    	$n += 1;
        $jsonEliminar = array(
            'typeOperation'             => "delete",
            'operation'                 => "eliminar-bodega-persona",
            'bodegaId'                  => $getBodegasEmpleado->bodegaId,
            'personaSucursalBodegaId'   => $getBodegasEmpleado->personaSucursalBodegaId,
            "personaId"                 => $_POST["personaId"],
            'personaSucursalId'         => $getBodegasEmpleado->personaSucursalId
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

    	$sucursal = '
    		<b><i class="fas fa-tag"></i> Sucursal: </b>'.$getBodegasEmpleado->sucursal.'
    	';
        $bodega = '
        <b><i class="fas fa-tag"></i> Bodega: </b>  ('.$getBodegasEmpleado->codSucursalBodega.') '.$getBodegasEmpleado->bodegaSucursal.'
        ';
	    $acciones = '
			<button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarBodegaPersona('.$funcionEliminar.');">
				<i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
			</button>
	    ';

	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $sucursal,
            $bodega,
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