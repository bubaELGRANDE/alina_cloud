<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataDepartamentos = $cloud->rows("
        SELECT
        paisDepartamentoId,departamentoPais, codigoMH
        FROM cat_paises_departamentos
        WHERE paisId = ? AND flgDelete = ?
    ", [$_POST["paisId"], 0]);

    $n = 0;
    foreach ($dataDepartamentos as $departamentos) {
        $n += 1;


        $departamentoPais = "
            <b><i class='fas fa-globe-americas'></i> Departamento:     </b> $departamentos->departamentoPais <br>
            <b><i class='fas fa-list-ol'></i> Cód. Hacienda: </b> $departamentos->codigoMH <br>
           
        ";
        $jsonEditarDepartamento = array(
            "paisId"                    => $_POST["paisId"],
            "typeOperation"             => "update",
            "tituloModal"               => "Editar Departamento: $departamentos->departamentoPais",
            "paisDepartamentoId"        =>  $departamentos->paisDepartamentoId
        );

        $totalMunicipios = $cloud->count("
            SELECT paisMunicipioId FROM cat_paises_municipios
            WHERE paisDepartamentoId = ? AND flgDelete = ?
        ", [$departamentos->paisDepartamentoId, 0]);

        $jsonEliminarDepartamento = array(
            "typeOperation" => "delete",
            "operation"     => "departamento",
            "paisDepartamentoId"        => $departamentos->paisDepartamentoId,
            "departamentoPais"          => $departamentos->departamentoPais
        );

        $acciones = "
        <button type='button' class='btn btn-primary btn-sm ttip' onclick='modalDepartamento(".htmlspecialchars(json_encode($jsonEditarDepartamento)).");'>
        <i class='fas fa-pencil-alt'></i>
        <span class='ttiptext'>Editar país</span>
         </button>

         <button type='button' class='btn btn-danger btn-sm ttip' onclick='eliminarDepartamento(".htmlspecialchars(json_encode($jsonEliminarDepartamento)).");'>
                <i class='fas fa-trash-alt'></i>
                <span class='ttiptext'>Eliminar país</span>
            </button>

        <button type='button' class='btn btn-primary btn-sm ttip' onclick='changePage(`$_SESSION[currentRoute]`, `paises-municipios`, `paisDepartamentoId=$departamentos->paisDepartamentoId&            departamentoPais=$departamentos->departamentoPais&paisId=$_POST[paisId]&pais=$_POST[pais]`);'>
                    <span class='badge rounded-pill bg-light text-dark'>$totalMunicipios</span>
                    <i class='fas fa-map-marked-alt'></i>
                    <span class='ttiptext'>Ver municipios del departamento</span>
                </button>
        ";

        $output['data'][] = array(
            $n,
            $departamentoPais,
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