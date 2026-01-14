<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>

<div class="bodegaDisplay">
    <?php
        $data = array();
        $index = array();    
    
        $dataUbi = $cloud->rows("SELECT 
            inventarioUbicacionId,
            bodegaId,
            nombreUbicacion,
            codigoUbicacion,
            ubicacionSuperiorId
    	FROM inv_ubicaciones
    	WHERE bodegaId = ?  AND flgDelete = '0' ", [$_POST["bodegaId"]]);

        
    foreach ($dataUbi as $ubicaciones) {
        
        //crear los arryas
        $id = $ubicaciones->inventarioUbicacionId;
        $parent_id = $ubicaciones->ubicacionSuperiorId === NULL ? "NULL" : $ubicaciones->ubicacionSuperiorId;
        $data[$id] = $ubicaciones;
        $index[$parent_id][] = $id;
        
    }

    function display_child_nodes($parent_id, $level) {
        //agregar hijos+datos
        global $cloud, $data, $index;
        
        $parent_id = $parent_id === NULL ? "NULL" : $parent_id;

        if (isset($index[$parent_id])) {

            if ($parent_id == 0){
                echo '<ul class="ubicaciones" id="ubi-'.$level.'">';
            } else {
                echo '<ul id="ubi-'.$level.'">';
            }

            foreach ($index[$parent_id] as $id) {

                echo    '<li class="ubiItem">
                            <div class="ubiCard">
                                <span class="ubiTitulo text-center"><i class="fas fa-qrcode"></i> '.$data[$id]->codigoUbicacion.' - ' . $data[$id]->nombreUbicacion . '</span> 
                                
                                <div class="acciones">
                                <a role="button" class="badge bg-primary ttip" onClick="ubicacionBodega(`insert^'.$data[$id]->bodegaId.'^'.$data[$id]->inventarioUbicacionId.'`,`ubicacion`,`md`);">
                                    <i class="fas fa-boxes"></i>
                                    <span class="ttiptext">Agregar subnivel</span>
                                </a>
                                <a role="button" class="badge bg-primary ttip" onClick="ubicacionBodega(`update^'.$data[$id]->bodegaId.'^'.$data[$id]->inventarioUbicacionId.'`,`ubicacion`,`md`);">
                                    <i class="fas fa-pencil-alt"></i>
                                    <span class="ttiptext">Editar</span>
                                </a>
                                <a role="button" class="badge bg-danger ttip" onclick="eliminar(`'.$data[$id]->inventarioUbicacionId.'^'.$data[$id]->nombreUbicacion.'`);">
                                    <i class="fas fa-trash-alt"></i>
                                    <span class="ttiptext">Eliminar</span>
                                </a>
                                </div>
                            </div>';
                            display_child_nodes($id, $level + 1);
                        echo '</li>';
            }
            echo '</ul>';
        }
    }
    display_child_nodes(0, 1);
    //var_dump($index);
    ?>

</div>