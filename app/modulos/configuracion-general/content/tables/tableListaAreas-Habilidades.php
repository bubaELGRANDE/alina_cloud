<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$solicitud = $_POST["tipoSolicitud"];

switch($solicitud){
    case "Experiencia":
        $dataAreasExp = $cloud->rows("
            SELECT prsArExperienciaId, areaExperiencia FROM cat_personas_ar_experiencia WHERE flgDelete = 0
        ");
        $n = 0;
        foreach ($dataAreasExp as $dataArea) {
            $n += 1;
            
            $controles = '';
            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(16, $_SESSION["arrayPermisos"])) { // edit area
                $controles .= '<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalAreaHabilidad(`editar`, `experiencia`, `'. $dataArea->prsArExperienciaId .'`)"><i class="fas fa-pencil-alt"></i><span class="ttiptext">Editar</span></button> ';
            }
            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(17, $_SESSION["arrayPermisos"])) { // del area
                $controles .= '<button type="button" class="btn btn-danger btn-sm ttip" onclick="delArea(`experiencia^'. $dataArea->prsArExperienciaId .'`)"><i class="fas fa-trash-alt"></i><span class="ttiptext">Eliminar</span></button>';
            }
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $dataArea->areaExperiencia,
                $controles
            );
        }
        break;
    case "Estudio":
        $dataAreasEst = $cloud->rows("
            SELECT prsArEstudioId, areaEstudio FROM cat_personas_ar_estudio WHERE flgDelete = 0
        ");
        $n = 0;
        foreach ($dataAreasEst as $dataArea) {
            $n += 1;
            $controles = '';
            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(16, $_SESSION["arrayPermisos"])) { // edit area
                $controles .= '<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalAreaHabilidad(`editar`, `estudio`, `'. $dataArea->prsArEstudioId .'`)"><i class="fas fa-pencil-alt"></i><span class="ttiptext">Editar</span></button> ';
            }
            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(17, $_SESSION["arrayPermisos"])) { // del area
                $controles .= '<button type="button" class="btn btn-danger btn-sm ttip" onclick="delArea(`estudio^'. $dataArea->prsArEstudioId .'`)"><i class="fas fa-trash-alt"></i><span class="ttiptext">Eliminar</span></button>';
            }
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $dataArea->areaEstudio,
                $controles
            );
        }
        break;
    case "Software":
        $dataSoftware = $cloud->rows("
            SELECT prsSoftwareId, nombreSoftware FROM cat_personas_software WHERE flgDelete = 0
        ");
        $n = 0;
        foreach ($dataSoftware as $dataSoft) {
            $n += 1;
            
            $controles = '';
            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(16, $_SESSION["arrayPermisos"])) { // edit area
                $controles .= '<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalAreaHabilidad(`editar`, `software` ,`'. $dataSoft->prsSoftwareId .'`)"><i class="fas fa-pencil-alt"></i><span class="ttiptext">Editar</span></button> ';
            }
            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(17, $_SESSION["arrayPermisos"])) { // del area
                $controles .= '<button type="button" class="btn btn-danger btn-sm ttip" onclick="delArea(`software^'. $dataSoft->prsSoftwareId .'`)"><i class="fas fa-trash-alt"></i><span class="ttiptext">Eliminar</span></button>';
            }
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $dataSoft->nombreSoftware,
                $controles
            );
        }
        break;
    case "HerraEqu":
        $dataHerraEqu = $cloud->rows("
            SELECT prsHerrEquipoId, nombreHerrEquipo FROM cat_personas_herr_equipos WHERE flgDelete = 0
        ");
        $n = 0;
        foreach ($dataHerraEqu as $dataHerraEqu) {
            $n += 1;
            
            $controles = '';
            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(16, $_SESSION["arrayPermisos"])) { // edit area
                $controles .= '<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalAreaHabilidad(`editar`, `herraEqu` ,`'. $dataHerraEqu->prsHerrEquipoId .'`)"><i class="fas fa-pencil-alt"></i><span class="ttiptext">Editar</span></button> ';
            }
            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(17, $_SESSION["arrayPermisos"])) { // del area
                $controles .= '<button type="button" class="btn btn-danger btn-sm ttip" onclick="delArea(`herraEqu^'. $dataHerraEqu->prsHerrEquipoId .'`)"><i class="fas fa-trash-alt"></i><span class="ttiptext">Eliminar</span></button>';
            }
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $dataHerraEqu->nombreHerrEquipo,
                $controles
            );
        }
        break;

    default:
        echo json_encode(array('data'=>'')); 
        break;
}

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }