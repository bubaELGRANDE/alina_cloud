<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataPaises = $cloud->rows("
        SELECT
            paisId, pais, abreviaturaPais, telefonoCodPais, iconBandera, codigoMH
        FROM cat_paises
        WHERE flgDelete = ?
    ", [0]);

    $n = 0;
    foreach ($dataPaises as $paises) {
        $n += 1;

        if($paises->iconBandera == "") {
            $iconoBandera = "<i class='fas fa-flag'></i>";
        } else {
            $iconoBandera = "<img src='../libraries/resources/images/$paises->iconBandera'>";
        }

        $pais = "
            <b><i class='fas fa-globe-americas'></i> País: </b> $iconoBandera $paises->pais ($paises->abreviaturaPais)<br>
            <b><i class='fas fa-phone-alt'></i> Cód. teléfono: </b> $paises->telefonoCodPais <br>
            <b><i class='fas fa-list-ol'></i> Cód. Hacienda: </b> $paises->codigoMH <br>
        
        ";

        $jsonEditarPais = array(
            "typeOperation" => "update",
            "tituloModal"   => "Editar país: $paises->pais ($paises->abreviaturaPais)",
            "paisId"        =>  $paises->paisId
        );

        $jsonEliminarPais = array(
            "typeOperation" => "delete",
            "operation"     => "pais",
            "paisId"        => $paises->paisId,
            "pais"          => $paises->pais
        );

        $totalEstados = $cloud->count("
            SELECT paisDepartamentoId FROM cat_paises_departamentos
            WHERE paisId = ? AND flgDelete = ?
        ", [$paises->paisId, 0]);

        $acciones = "
            <button type='button' class='btn btn-primary btn-sm ttip' onclick='modalPais(".htmlspecialchars(json_encode($jsonEditarPais)).");'>
                <i class='fas fa-pencil-alt'></i>
                <span class='ttiptext'>Editar país</span>
            </button>
            <button type='button' class='btn btn-danger btn-sm ttip' onclick='eliminarPais(".htmlspecialchars(json_encode($jsonEliminarPais)).");'>
                <i class='fas fa-trash-alt'></i>
                <span class='ttiptext'>Eliminar país</span>
            </button>
            <button type='button' class='btn btn-primary btn-sm ttip' onclick='changePage(`$_SESSION[currentRoute]`, `paises-departamentos`, `paisId=$paises->paisId&pais=$paises->pais`);'>
                <span class='badge rounded-pill bg-light text-dark'>$totalEstados</span>
                <i class='fas fa-map-marked-alt'></i>
                <span class='ttiptext'>Ver departamentos (estados) del país</span>
            </button>
        ";

        $output['data'][] = array(
            $n,
            $pais,
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