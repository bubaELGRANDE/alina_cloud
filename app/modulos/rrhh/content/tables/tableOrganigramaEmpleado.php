<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataEmpRamas = $cloud->rows(" SELECT
        expor.expedienteOrganigramaId,
        CONCAT(
            IFNULL(per.apellido1, '-'),
            ' ',
            IFNULL(per.apellido2, '-'),
            ', ',
            IFNULL(per.nombre1, '-'),
            ' ',
            IFNULL(per.nombre2, '-')
        ) AS nombreCompleto,
        pc.cargoPersona AS cargoPersona,
        ram.organigramaRama,
        ram.organigramaRamaId,
        exp.prsExpedienteId AS prsExpedienteId, 
        exp.personaId AS personaId, 
        exp.prsCargoId AS prsCargoId

        FROM th_expediente_organigrama expor
        JOIN cat_organigrama_ramas ram ON ram.organigramaRamaId = expor.organigramaRamaId
        JOIN th_expediente_personas exp ON exp.prsExpedienteId = expor.prsExpedienteId
        JOIN cat_personas_cargos pc ON pc.prsCargoId = exp.prsCargoId
        JOIN th_personas per ON per.personaId = exp.personaId
            
        WHERE expor.flgDelete = '0' AND exp.estadoExpediente = 'Activo' AND expor.organigramaRamaId = ?
        ORDER BY prsExpedienteId DESC",[$_POST["id"]]);

$n = 0;

foreach ($dataEmpRamas as $organigrama) {
    $n += 1;

    $empleado = $organigrama->nombreCompleto .'<br>'. $organigrama->cargoPersona ;

    $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalEditEmpleadoOrganigrama(`'.$organigrama->expedienteOrganigramaId.'`, `'.$organigrama->nombreCompleto.'`);">
            <i class="fas fa-pen"></i>
            <span class="ttiptext">Empleados</span>
        </button>
        <button class="btn btn-danger btn-sm ttip" onclick="eliminarEmpleadoOrganigrama('.$organigrama->expedienteOrganigramaId.', `'.$organigrama->nombreCompleto.'`);">
            <i class="fas fa-trash-alt"></i>
            <span class="ttiptext">Eliminar rama</span>
        </button>
        ';

    $output['data'][] = array(
        $n, // es #, se dibuja solo en el JS de datatable
        $empleado,
        $acciones
    );
}

if($n > 0) {
echo json_encode($output);
} else {
// No retornar nada para evitar error "null"
echo json_encode(array('data'=>'')); 
}