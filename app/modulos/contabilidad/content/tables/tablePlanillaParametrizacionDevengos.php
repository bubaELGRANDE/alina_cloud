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
    ", [$_POST["tipoDevengo"], 0, 0]);
    foreach($dataDevengos as $devengo) {
        $n++;

        $columnaDevengo = "
            <b> <i class='fas fa-list-ol trailing'></i> Código contable:</b> $devengo->codigoContable<br>
            <b> <i class='fas fa-money-check-alt trailing'></i> Devengo:</b> $devengo->nombreDevengo
        ";

        $jsonEditar = array(
            "typeOperation"                 => "update",
            "tipoDevengo"                   => $_POST["tipoDevengo"],
            "tituloModal"                   => "Devengo - $_POST[tipoDevengo]: $devengo->nombreDevengo",
            "catPlanillaDevengoId"          => $devengo->catPlanillaDevengoId,
            "catPlanillaDevengoIdSuperior"  => $devengo->catPlanillaDevengoIdSuperior,
            "tblDevengo"                    => $_POST["tblDevengo"]
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));

        $jsonEliminar = array(
            "typeOperation" => "delete",
            "operation"     => "parametrizacion-devengo",
            "catPlanillaDevengoId" => $devengo->catPlanillaDevengoId,
            "nombreDevengo" => $devengo->nombreDevengo,
            "tipoDevengo"   => $_POST["tipoDevengo"],
            "tblDevengo"    => $_POST["tblDevengo"],
            "subDevengo"    => 0
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $totalSubDevengos = $cloud->count("
            SELECT catPlanillaDevengoId FROM cat_planilla_devengos
            WHERE catPlanillaDevengoIdSuperior = ? AND flgDelete = ?
        ", [$devengo->catPlanillaDevengoId, 0]);

        $jsonSubDevengo = array(
            "catPlanillaDevengoId" => 0,
            "catPlanillaDevengoIdSuperior"  => $devengo->catPlanillaDevengoId,
            "tituloModal"           => "Devengo - $_POST[tipoDevengo]: $devengo->nombreDevengo",
            "nombreDevengo"         => $devengo->nombreDevengo,
            "tipoDevengo"           => $_POST["tipoDevengo"],
            "tblDevengo"            => $_POST["tblDevengo"]
        );
        $funcionSubDevengo = htmlspecialchars(json_encode($jsonSubDevengo));

        $columnaAcciones = '
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalDevengo('.$funcionEditar.');">
                <i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarDevengo('.$funcionEliminar.');">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalSubDevengo('.$funcionSubDevengo.');">
                <span class="badge rounded-pill bg-light" style="color: black;">'.$totalSubDevengos.'</span>
                <i class="fas fa-list-ul"></i>
                <span class="ttiptext">Subdevengos</span>
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