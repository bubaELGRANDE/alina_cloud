<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataPersonasSucursales = $cloud->rows("
        SELECT
            nombreCompleto,
            personaId,
            prsCargoId,
            cargoPersona,
            sucursalId, 
            sucursal,
            departamentoSucursal,
            codSucursalDepartamento
        FROM view_expedientes
        WHERE  estadoPersona = ? AND estadoExpediente = ?
    ",['Activo', 'Activo']);

    $n = 0;
    foreach ($dataPersonasSucursales as $dataPersonasSucursales) {
        $n += 1;


        $jsonPermisos = array(
            'typeOperation'     =>"insert",
            'operation'         =>"sucursales-empleado",
            "tituloModal"       => "Sucursal del empleado: $dataPersonasSucursales->nombreCompleto ",
            'personaId'         => $dataPersonasSucursales->personaId
        );
        $funcionPermisos = htmlspecialchars(json_encode($jsonPermisos));


        $jsonBodegas = array(
            'typeOperation'             =>"insert",
            'operation'                 =>"bodegas-empleado",
            "tituloModal"               => "Bodega del empleado: $dataPersonasSucursales->nombreCompleto ",
            'personaId'                 => $dataPersonasSucursales->personaId
        );
        $funcionBodegas = htmlspecialchars(json_encode($jsonBodegas));

        $totalSucursales = $cloud->count("
            SELECT personaSucursalId,personaId,sucursalId FROM conf_personas_sucursales
            WHERE personaId = ? AND flgDelete = ?
        ", [$dataPersonasSucursales->personaId, 0]);

        $totalBodegas = $cloud->count("
        SELECT 
            psb.personaSucursalBodegaId, 
            psb.personaSucursalId, 
            psb.bodegaId,
            ps.personaId
        FROM conf_personas_sucursales_bodegas psb
        JOIN conf_personas_sucursales ps ON psb.personaSucursalId = ps.personaSucursalId
        WHERE psb.flgDelete = ? AND  ps.personaId = ?
    ", [0,$dataPersonasSucursales->personaId]);

        

        $empleado = '<b><i class="fas fa-user-tie"></i> Nombre completo: </b>'.$dataPersonasSucursales->nombreCompleto
        . '<br><b><i class="fas fa-user-tie"></i> Cargo: </b>' .$dataPersonasSucursales->cargoPersona
        . '<br><b><i class="fas fa-user-tie"></i> Sucursal: </b>' .$dataPersonasSucursales->sucursal
        . '<br><b><i class="fas fa-user-tie"></i> Departamento: </b>' .$dataPersonasSucursales->departamentoSucursal;

        $acciones = ' 
                    <button type="button" class="btn btn-primary btn-sm ttip" onclick="sucursalesDTE('.$funcionPermisos.');">
                        <span class="badge rounded-pill bg-light text-dark">' . $totalSucursales . '</span>
                        <i class="fas fa-map-marked-alt"></i>
                        <span class="ttiptext">Sucursales</span>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm ttip" onclick="bodegasEmpleado('.$funcionBodegas.');">
                        <span class="badge rounded-pill bg-light text-dark">' . $totalBodegas . '</span>
                        <i class="fas fa-box"></i>
                        <span class="ttiptext">Bodegas</span>
                    </button>' ;
        
        $output['data'][] = array(
            $n, 
            $empleado,
            $acciones
        );
    } // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }