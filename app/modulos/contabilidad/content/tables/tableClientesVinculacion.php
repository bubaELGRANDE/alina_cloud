<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $dataCliente = $cloud->rows("SELECT 
    c.clienteId, c.nrcCliente, c.nombreCliente, c.nombreComercialCliente, c.categoriaCliente, d.tipoDocumentoCliente, c.numDocumento, p.tipoPersona, c.tipoPersonaMHId,
    c.nombreCompletoRL, rl.tipoDocumentoCliente, c.numDocumentoRL, a.actividadEconomica, c.estadoCliente, c.flgAPNFD, c.flgPEP, c.flgPEPFamiliar, c.flgPEPAccionista
    FROM fel_clientes c 
    JOIN mh_022_tipo_documento d ON d.tipoDocumentoClienteId = c.tipoDocumentoMHId
    JOIN mh_022_tipo_documento rl ON rl.tipoDocumentoClienteId = c.tipoDocumentoMHId
    JOIN mh_029_tipo_persona p ON p.tipoPersonaId = c.tipoPersonaMHId
    LEFT JOIN mh_019_actividad_economica a ON a.actividadEconomicaId = c.actividadEconomicaId
    WHERE c.flgDelete = 0 AND c.estadoCliente = 'Activo' AND c.tipoPersonaMHId = ?", [$_POST["tipoPersona"]]);
    
    $n = 0;
    foreach($dataCliente as $cliente){
        $n += 1;

        $client = '';

        if($cliente->tipoPersona == 'Persona Jurídica') {
            $client = '<b>Número de registro:</b> '.$cliente->nrcCliente.'<br>
            <b>Tipo de cliente: </b>'.$cliente->tipoPersona.'<br>
            <b>Actividad económica: </b>'.$cliente->actividadEconomica;
        } else {
            $client = '<b>Número de registro:</b> '.$cliente->nrcCliente.'<br>
            
            <b>Tipo de cliente: </b>'.$cliente->tipoPersona.'<br>';
        }
    
        $nombre = '<b>Nombre: </b>'.$cliente->nombreCliente.'<br>';
        if (!empty($cliente->nombreComercialCliente)){
            $nombre .= '<b>Nombre comercial: </b>' .$cliente->nombreComercialCliente.'<br>
            <b>'.$cliente->tipoDocumentoCliente.': </b>' .$cliente->numDocumento.'<br>';
        }
        
        $rl = '';
            $rl .= '<b>Nombre: </b>'. $cliente->nombreCompletoRL . '<br>
            <b>'.$cliente->tipoDocumentoCliente.':</b>'. $cliente->numDocumentoRL.'<br>
            <b>Categoría de cliente: </b>'.$cliente->categoriaCliente;

        // acciones

        $jsonEditar = array(
            "typeOperation"    => "update",
            "tituloModal"      => "Editar cliente: " .$cliente->nombreCliente,
            "clienteId"      => $cliente->clienteId
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));


        if($cliente->flgPEP == 'Si' || $cliente->flgPEPAccionista == 'Si' || $cliente->flgPEPFamiliar == 'Si'){
            if($cliente->tipoPersona == 'Persona Jurídica') {
                $tipo = "Empresarial";
            } else {
                $tipo = "Familiar";
            }
            $countPEP = $cloud->count("SELECT clientePEPId FROM fel_clientes_pep WHERE clienteId = ? AND flgDelete = 0", [$cliente->clienteId]);
            
            $pep = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="changePage(`'.$_SESSION["currentRoute"].'`, `vinculacion-clientes-pep`, `clienteId='.$cliente->clienteId.'&cliente='.$cliente->nombreCliente.'&flgTipo='.$tipo.'&tipoPersona='.$cliente->tipoPersonaMHId.'`);">
                <span class="badge rounded-pill bg-light text-dark">'.$countPEP.'</span>
                <i class="fas fa-user-tie"></i>
                <span class="ttiptext">Persona politicamente expuesta</span>
            </button> ';
        } else {
            $pep = '';
        }

        $jsonEliminar = array(
            "typeOperation"     => "delete",
            "operation"         => "datos-cliente",
            "clienteId"         => $cliente->clienteId,
            "nombreCliente"     => $cliente->nombreCliente
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $editar = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalClienteVin('.$funcionEditar.');">
            <i class="fas fa-pen"></i>
            <span class="ttiptext">Editar</span>
        </button> ';
        $eliminar = '
            <button type="button" class="btn btn-danger btn-sm ttip" onClick="delCliente('.$funcionEliminar.');">
                <i class="fas fa-trash-alt"></i>
                <span class="ttiptext">Eliminar cliente</span>
            </button> 
        ';

        $numUbicaciones = $cloud->count("SELECT clienteId FROM fel_clientes_ubicaciones WHERE clienteId = ? AND flgDelete = 0", [$cliente->clienteId]);

        $ubicaciones = '<button type="button" class="btn btn-primary btn-sm ttip" onclick="changePage(`'.$_SESSION["currentRoute"].'`, `cliente-ubicaciones-vinculacion`, `clienteId='.$cliente->clienteId.'&cliente='.$cliente->nombreCliente.'&estadoCliente='.$cliente->estadoCliente.'&tipoPersona='.$cliente->tipoPersonaMHId.'`);">
            <span class="badge rounded-pill bg-light text-dark">'.$numUbicaciones.'</span>
            <i class="fas fa-globe"></i>
            <span class="ttiptext">Direcciones</span>
        </button>  ';
        $clientesProv = '<button type="button" class="btn btn-primary btn-sm ttip" onclick="changePage(`'.$_SESSION["currentRoute"].'`, `clientes-proveedores`, `clienteId='.$cliente->clienteId.'&cliente='.$cliente->nombreCliente.'&estadoCliente='.$cliente->estadoCliente.'&tipoPersona='.$cliente->tipoPersonaMHId.'`);">
            <i class="fas fa-user-friends"></i>
            <span class="ttiptext">Clientes y Proveedores</span>
        </button> ';

        if($cliente->tipoPersona == 'Persona Jurídica') {
            $jsonAccionistas = array(
                "typeOperation"     => "insert",
                "tituloModal"       => "Accionistas de: " .$cliente->nombreCliente,
                "nombreCliente"     => $cliente->nombreCliente,
                "tipoPersona"       => $cliente->tipoPersonaMHId,
                "clienteId"         => $cliente->clienteId
            );
            $funcionAccionistas = htmlspecialchars(json_encode($jsonAccionistas));

            $numAccionistas = $cloud->count("SELECT clienteAccionistaid FROM fel_clientes_accionistas WHERE clienteId = ? AND flgDelete = 0",[$cliente->clienteId]);
            $accionistas = '<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalAccionistas('.$funcionAccionistas.');">
                <span class="badge rounded-pill bg-light text-dark">'.$numAccionistas.'</span>
                <i class="fas fa-user-check"></i>
                <span class="ttiptext">Accionistas</span>
            </button> ';
        } else {
            $accionistas = '';
        }

        $acciones = $editar.$ubicaciones.$clientesProv.$pep.$accionistas.$eliminar;

        if($cliente->tipoPersona == 'Persona Jurídica') {
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $nombre,
                $client,
                $rl,
                $acciones
            );
        } else {
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $nombre,
                $client,
                $acciones
            );
        }
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }