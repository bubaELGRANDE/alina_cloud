<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataRamas = $cloud->rows("SELECT 
        a.organigramaRamaId as ramaId,
        a.organigramaRama as rama,
        a.ramaSuperiorId as ramaSuperiorId,
        a.organigramaRamaDescripcion as organigramaRamaDescripcion,
        b.organigramaRamaId as ramaSuperiorIdB,
        b.organigramaRama as ramaSuperior
    FROM cat_organigrama_ramas a, cat_organigrama_ramas b
    WHERE a.flgDelete = '0' AND a.ramaSuperiorId = b.organigramaRamaId 
    ORDER BY a.ramaSuperiorId");

$n = 0;

foreach ($dataRamas as $organigrama) {
    $n += 1;

    $rama = $organigrama->rama;
    $ramaDesc = '<b>'.$organigrama->rama.'</b><br>'.$organigrama->organigramaRamaDescripcion;
    $superior = $organigrama->ramaSuperior;

    $numPersonas = $cloud->count("
               		SELECT expedienteOrganigramaId FROM th_expediente_organigrama
               		WHERE organigramaRamaId = ? AND flgDelete = '0'
               	", [$organigrama->ramaId]);

    $acciones = '<button type="button" class="btn btn-primary btn-sm ttip"
    onclick="changePage(`'.$_SESSION["currentRoute"].'`, `organigrama-rama`, `ramaId='.$organigrama->ramaId.'&rama='.$rama.'`);">
            <span class="badge rounded-pill bg-light text-dark">'.$numPersonas.'</span> <i class="fas fa-users"></i>
            <span class="ttiptext">Empleados</span>
        </button>
        <button class="btn btn-primary btn-sm ttip" onclick="modalOrganigramaRama(`update`, '.$organigrama->ramaId.');">
            <i class="fas fa-pen"></i>
            <span class="ttiptext">Editar rama</span>
        </button>
        <button class="btn btn-danger btn-sm ttip" onclick="eliminarRama('.$organigrama->ramaId.', `'.$organigrama->rama.'`);">
            <i class="fas fa-trash-alt"></i>
            <span class="ttiptext">Eliminar rama</span>
        </button>
        ';

    $output['data'][] = array(
        $n, // es #, se dibuja solo en el JS de datatable
        $ramaDesc,
        $superior,
        $acciones
    );
}

if($n > 0) {
echo json_encode($output);
} else {
// No retornar nada para evitar error "null"
echo json_encode(array('data'=>'')); 
}