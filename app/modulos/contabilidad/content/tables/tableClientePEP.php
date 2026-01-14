<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataPEP = $cloud->rows("SELECT 
        p.clientePEPId, p.nombreCompletoPEP, p.cargoPublico, date_format(p.fechaNombramiento, '%d-%m-%Y') as fechaIni, date_format(p.fechaFinNombramiento, '%d-%m-%Y') as fechaFin, t.tipoDocumentoCliente, p.numDocumentoPEP, p.institucionCargoPublico, p.tipoPEP, p.tipoRelacionPEP
        FROM fel_clientes_pep p 
        JOIN mh_022_tipo_documento t ON t.tipoDocumentoClienteId = p.tipoDocumentoMHId
        WHERE p.flgDelete = 0 AND p.clienteId = ? ", [$_POST["id"]]);

    $n = 0;

    foreach ($dataPEP as $pep){
        $n += 1;

        $cliente = '<b>Nombre:</b> '.$pep->nombreCompletoPEP.'<br>
        <b>Tipo de persona: </b> '.$pep->tipoPEP.'<br>
        <b>'.$pep->tipoDocumentoCliente.': </b>' .$pep->numDocumentoPEP;

        $fechaFinCargo = ($pep->fechaFin == '') ? '-' : $pep->fechaFin;
        $cargo =  '<b>Cargo público:</b> '.$pep->cargoPublico.'<br>
        <b>Institución: </b>'.$pep->institucionCargoPublico.'<br>
        <b>Nombramiento: </b>'.$pep->fechaIni.' - <b>Finalización: </b>' .$fechaFinCargo;

        if($pep->tipoPEP == 'Persona Jurídica') {
            $tipo = "Empresarial";
        } else {
            $tipo = "Familiar";
        }
        $jsonEditar = array(
            "typeOperation"     => "update",
            "tituloModal"       => "Editar Personas politicamente expuesta: " .$pep->nombreCompletoPEP,
            "flgTipo"           => $tipo,
            "clienteId"         => $pep->clientePEPId
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));
        $jsonEliminar = array(
            "typeOperation"     => "delete",
            "operation"         => "datos-cliente-PEP",
            "clientePEPId"      => $pep->clientePEPId,
            "nombreCliente"     => $pep->nombreCompletoPEP
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $jsonFam = array(
            "typeOperation"     => "insert",
            "tituloModal"       => "Familiares de persona politicamente expuesta: " .$pep->nombreCompletoPEP,
            "nombrePEP"         => $pep->nombreCompletoPEP,
            "PEPId"             => $pep->clientePEPId
        );
        $funcionFamiliares = htmlspecialchars(json_encode($jsonFam));
        
        $jsonSoc = array(
            "typeOperation"     => "insert",
            "tituloModal"       => "Sociedades en las que tiene relación patrimonial: " .$pep->nombreCompletoPEP,
            "nombrePEP"         => $pep->nombreCompletoPEP,
            "PEPId"             => $pep->clientePEPId
        );
        $funcionSociedades = htmlspecialchars(json_encode($jsonSoc));

        $numFamiliares = $cloud->count("SELECT clientePEPFamiliaId FROM fel_clientes_pep_familia WHERE clientePEPId = ? AND flgDelete = 0", [$pep->clientePEPId]);

        $familiares = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalPEPFam('.$funcionFamiliares.');">
                <span class="badge rounded-pill bg-light text-dark">'.$numFamiliares.'</span>
                <i class="fas fa-users"></i>
                <span class="ttiptext">Información de familiares</span>
            </button> ';

        $numSociedades = $cloud->count("SELECT clientePEPPatrimonialId FROM fel_clientes_pep_relpatrimonial WHERE clientePEPId = ? AND flgDelete = 0", [$pep->clientePEPId]);
        
        $sociedades = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalPEPSoc('.$funcionSociedades.');">
                <span class="badge rounded-pill bg-light text-dark">'.$numSociedades.'</span>
                <i class="fas fa-suitcase"></i>
                <span class="ttiptext">Sociedades con relación patrimonial</span>
            </button> ';
        $editar = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalPEP('.$funcionEditar.');">
            <i class="fas fa-pen"></i>
            <span class="ttiptext">Editar</span>
        </button> ';
        $eliminar = '
            <button type="button" class="btn btn-danger btn-sm ttip" onClick="delPEP('.$funcionEliminar.');">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar cliete</span>
            </button> 
        ';
        $acciones = $familiares.$sociedades.$editar.$eliminar;
        
        $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $cliente,
	        $cargo,
            $acciones
	    );
    }
    
    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }