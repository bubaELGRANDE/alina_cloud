<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>

<div class="organigramaDisplay table-responsive">
    <?php // Organigrama

    $data = array();
    $index = array();    

    $dataRamas = $cloud->rows("SELECT 
        organigramaRamaId,
        organigramaRama,
        ramaSuperiorId,
        organigramaRamaDescripcion
    FROM cat_organigrama_ramas 
    WHERE flgDelete = '0'");


    foreach ($dataRamas as $organigrama) {
        
        //crear los arryas
        $id = $organigrama->organigramaRamaId;
        $parent_id = $organigrama->ramaSuperiorId === NULL ? "NULL" : $organigrama->ramaSuperiorId;
        $data[$id] = $organigrama;
        $index[$parent_id][] = $id;
    }


    function display_child_nodes($parent_id, $level) {
        //agregar hijos+datos
        global $cloud, $data, $index;
        
        $parent_id = $parent_id === NULL ? "NULL" : $parent_id;

        if (isset($index[$parent_id])) {

            echo '<ul id="org-'.$level.'" style="display:none">';
            foreach ($index[$parent_id] as $id) {

                $numPersonas = $cloud->count("
                    SELECT expedienteOrganigramaId FROM th_expediente_organigrama
                    WHERE organigramaRamaId = ? AND flgDelete = '0'
                ", [$data[$id]->organigramaRamaId]);

                echo    '<li class="orgItem">
                            <div class="orgCard">
                                <span class="orgTitulo d-block text-center">' . $data[$id]->organigramaRama . '</span> 
                                <hr>
                                <p class="text-center">' . $data[$id]->organigramaRamaDescripcion . '</p>
                                <hr>
                                <div class="acciones">
                                    <button type="button" class="btn btn-light btn-sm ttip" onclick="changePage(`'.$_SESSION["currentRoute"].'`, `organigrama-rama`, `ramaId='.$data[$id]->organigramaRamaId.'&rama='.$data[$id]->organigramaRama.'`);">
                                        <i class="fas fa-users"></i> <span class="badge rounded-pill bg-secondary"> '.$numPersonas.'</span>
                                        <span class="ttiptext">Empleados</span>
                                    </button>
                                    <button class="btn btn-light btn-sm ttip" onclick="modalOrganigramaRama(`update`, '.$data[$id]->organigramaRamaId.');">
                                        <i class="fas fa-pen"></i>
                                        <span class="ttiptext">Editar rama</span>
                                    </button>
                                    <button class="btn btn-danger btn-sm ttip" onclick="eliminarRama('.$data[$id]->organigramaRamaId.', `'.$data[$id]->organigramaRama.'`);">
                                        <i class="fas fa-trash-alt"></i>
                                        <span class="ttiptext">Eliminar rama</span>
                                    </button>
                                </div>
                            </div>';
                            display_child_nodes($id, $level + 1);
                            echo '</li>';
            }
            echo '</ul>';
        }
    }
    display_child_nodes(NULL, 0);
?>
</div>

<script>
    $(document).ready(function() {
        $("#org-0").jOrgChart({
            "chartElement": ".organigramaDisplay"
        });
    });
</script>