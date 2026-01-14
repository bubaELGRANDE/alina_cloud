<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

        $n = 0;
        $permisoDTE = $cloud->rows("
            SELECT
                psd.personaSucursalDTEId AS personaSucursalDTEId,
                psd.personaSucursalId AS personaSucursalId,
                td.tipoDTE AS tipoDTE
            FROM conf_personas_sucursales_dte psd
            JOIN mh_002_tipo_dte td ON td.tipoDTEId = psd.tipoDTEId
            WHERE psd.flgDelete = ?
        ",[0]);

foreach($permisoDTE as $permisoDTE){
        $n++;

        $jsonEliminar = array(
            'typeOperation'         =>"delete",
            'operation'             =>"permisos-DTE",
            'personaSucursalDTEId'  => $permisoDTE->personaSucursalDTEId,
            'personaSucursalId'     => $permisoDTE->personaSucursalId,
            'tipoDTE'               => $permisoDTE->tipoDTE
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $tipoDTE = '<b><i class="fas fa-building"></i> Tipo DTE: </b>'.$permisoDTE->tipoDTE ;

        $acciones = '   <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarPermisoDTE('.$funcionEliminar.')">
                            <i class="fas fa-trash"></i>
                            <span class="ttiptext">Eliminar</span>
                        </button>';

        $output['data'][] = array(
            $n, 
            $tipoDTE,
            $acciones
        );

}

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>