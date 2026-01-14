<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $n = 0;

    $dataDevengos = $cloud->rows("
        SELECT
            catPlanillaDevengoId, 
            nombreDevengo, 
            codigoContable, 
            catPlanillaDevengoIdSuperior
        FROM cat_planilla_devengos
        WHERE tipoDevengo = ? AND catPlanillaDevengoIdSuperior = ? AND flgDelete = ?
        ORDER BY nombreDevengo
    ", [$_POST["tipoDevengo"], $_POST["catPlanillaDevengoIdSuperior"], 0]);
    foreach($dataDevengos as $devengo) {
        $n++;

        $columnaDevengo = "
            <b> <i class='fas fa-list-ol trailing'></i> Código contable:</b> $devengo->codigoContable<br>
            <b> <i class='fas fa-money-check-alt trailing'></i> Devengo:</b> $devengo->nombreDevengo
        ";

        $jsonEditar = array(
            "nombreDevengo"     => $devengo->nombreDevengo,
            "codigoContable"    => $devengo->codigoContable,
            "catPlanillaDevengoId" => $devengo->catPlanillaDevengoId
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));

        $jsonEliminar = array(
            "typeOperation" => "delete",
            "operation"     => "parametrizacion-devengo",
            "catPlanillaDevengoId" => $devengo->catPlanillaDevengoId,
            "nombreDevengo" => $devengo->nombreDevengo,
            "tipoDevengo"   => $_POST["tipoDevengo"],
            "tblDevengo"    => $_POST["tblDevengo"],
            "subDevengo"    => 1
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $columnaAcciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="editarSubdevengo('.$funcionEditar.');">
                <i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarDevengo('.$funcionEliminar.');">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
        ';

        $output['data'][] = array(
            $n,
            $columnaDevengo,
            $columnaAcciones
        );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>