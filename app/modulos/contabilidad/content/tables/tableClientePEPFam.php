<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataPEPFam = $cloud->rows("SELECT 
        f.clientePEPFamiliaId,f.nombreFamiliar, r.tipoPrsRelacion, r.gradoParentesco 
        FROM fel_clientes_pep_familia f
        JOIN cat_personas_relacion r ON f.catPrsRelacionId = r.catPrsRelacionId
        WHERE f.clientePEPId = ? AND f.flgDelete = 0
    ",[$_POST['PEPId']]);

    $n = 0;
    foreach ($dataPEPFam as $pepFam){
        $n += 1;
        $nombre = $pepFam->nombreFamiliar;
        $parentesco = $pepFam->tipoPrsRelacion;
        $grado = $pepFam->gradoParentesco;


        $editar = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="updtPEPFam('.$pepFam->clientePEPFamiliaId.');">
            <i class="fas fa-pen"></i>
            <span class="ttiptext">Editar</span>
        </button> ';

        $jsonEliminar = array(
            "typeOperation"     => "delete",
            "operation"         => "datos-PEP-nucleoFamiliar",
            "clientePEPFamId"   => $pepFam->clientePEPFamiliaId,
            "nombreFam"         => $pepFam->nombreFamiliar
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $eliminar = '
            <button type="button" class="btn btn-danger btn-sm ttip" onClick="delPEPFam('.$funcionEliminar.');">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar familiar de PEP</span>
            </button> 
        ';

        $acciones = $editar.$eliminar;

        $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $nombre,
	        $parentesco,
	        $grado,
            $acciones
	    );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }