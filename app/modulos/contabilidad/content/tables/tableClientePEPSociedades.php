<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    $dataPEPRelPat = $cloud->rows("SELECT 
    clientePEPPatrimonialId, clientePEPId, razonSocial, porcentajeParticipacion
    FROM fel_clientes_pep_relpatrimonial
    WHERE clientePEPId = ? AND flgDelete = 0 ", [$_POST["PEPId"]]);

    $n = 0;

    foreach ($dataPEPRelPat as $RelPat){
        $n += 1;
        $razonSocial = $RelPat->razonSocial;
        $porcentaje = '<div class="text-end">%'.$RelPat->porcentajeParticipacion.'</div>';


        $editar = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="updtPEPSoc('.$RelPat->clientePEPPatrimonialId.');">
            <i class="fas fa-pen"></i>
            <span class="ttiptext">Editar</span>
        </button> ';

        $jsonEliminar = array(
            "typeOperation"     => "delete",
            "operation"         => "datos-PEP-sociedades",
            "clientePEPSocId"      => $RelPat->clientePEPPatrimonialId,
            "razonSocial"         => $RelPat->razonSocial
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
	        $razonSocial,
	        $porcentaje,
            $acciones
	    );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }