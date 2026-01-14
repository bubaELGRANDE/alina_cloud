<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataAccionista = $cloud->rows("SELECT 
        a.clienteAccionistaid, a.nombreAccionista, p.pais, p.iconBandera, a.nitAccionista, a.porcentajeParticipacion
        FROM fel_clientes_accionistas a 
        JOIN cat_paises p ON a.paisId = p.paisId
        WHERE a.flgDelete = 0 AND a.clienteId = ? ", [$_POST["clienteId"]]);

    $n = 0;

    foreach ($dataAccionista as $accionista){
        $n += 1;

        $nombre = $accionista->nombreAccionista;

        $iconoBandera = "<img src='../libraries/resources/images/$accionista->iconBandera'> ";
        $nacionalidad = $iconoBandera.$accionista->pais;
        $nit = $accionista->nitAccionista;
        $porcentaje = '<div class="text-end">'.$accionista->porcentajeParticipacion.'%</div>';

       /*  $jsonEditar = array(
            "typeOperation"         => "update",
            "tituloModal"           => "Editar accionista: " .$accionista->nombreAccionista,
            "nombreAccionista"      => $accionista->nombreAccionista,
            "clienteAccionistaid"   => $accionista->clienteAccionistaid
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar)); */
        $jsonEliminar = array(
            "typeOperation"         => "delete",
            "operation"             => "clientes-accionistas",
            "clienteAccionistaid"   => $accionista->clienteAccionistaid,
            "nombreAccionista"         => $accionista->nombreAccionista
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $editar = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="updtAccionista('.$accionista->clienteAccionistaid.');">
            <i class="fas fa-pen"></i>
            <span class="ttiptext">Editar</span>
        </button> ';
        $eliminar = '
            <button type="button" class="btn btn-danger btn-sm ttip" onClick="delAccionista('.$funcionEliminar.');">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar cliete</span>
            </button> 
        ';
        $acciones = $editar.$eliminar;

        $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $nombre,
	        $nacionalidad,
	        $nit,
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