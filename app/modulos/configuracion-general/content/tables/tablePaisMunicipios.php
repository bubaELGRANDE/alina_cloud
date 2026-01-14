<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataMunicipios = $cloud->rows("
        SELECT  paisMunicipioId, municipioPais,codigoMH
        FROM cat_paises_municipios 
        WHERE paisDepartamentoId = ?  AND flgDelete = ?
    
    ", [$_POST["paisDepartamentoId"],  0]);

    $n = 0;
    foreach ($dataMunicipios as $municipios) {
        $n += 1;


        $municipioPais = "
             <b><i class='fas fa-globe-americas'></i> Municipio: </b>  $municipios->municipioPais <br>
             <b><i class='fas fa-list-ol'></i> Cód. Hacienda: </b> $municipios->codigoMH <br>
           
        ";
        $jsonEditarMunicipio = array(
            "paisDepartamentoId"        => $_POST["paisDepartamentoId"],
            "typeOperation"             => "update",
            "tituloModal"               => "Editar Municipio: $municipios->municipioPais",
            "paisMunicipioId"        =>  $municipios->paisMunicipioId
        );

        $jsonEliminarMunicipio = array(
            "typeOperation" => "delete",
            "operation"     => "municipio",
            "paisMunicipioId"        => $municipios->paisMunicipioId,
            "departamentoPais"          => $municipios->municipioPais
        );

        $acciones = "
        <button type='button' class='btn btn-primary btn-sm ttip' onclick='modalMunicipio(".htmlspecialchars(json_encode($jsonEditarMunicipio)).");'>
        <i class='fas fa-pencil-alt'></i>
        <span class='ttiptext'>Editar Municiipio</span>
         </button>

         <button type='button' class='btn btn-danger btn-sm ttip' onclick='eliminarMunicipio(".htmlspecialchars(json_encode($jsonEliminarMunicipio)).");'>
         <i class='fas fa-trash-alt'></i>
         <span class='ttiptext'>Eliminar país</span>
     </button>


        ";

        $output['data'][] = array(
            $n,
            $municipioPais,
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