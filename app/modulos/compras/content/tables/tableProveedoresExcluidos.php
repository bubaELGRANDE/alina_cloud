<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();


        $dataProveedores = $cloud->rows("
        SELECT
        cop.sujetoExcluidoId as sujetoExcluidoId,
        cop.tipoDocumentoMHId as tipoDocumentoMHId,
        cop.numDocumento AS numDocumento,
        cop.nombreSujeto as nombreSujeto,
        cop.actividadEconomicaId as actividadEconomicaId,
        cop.telefonoSujeto as telefonoSujeto,
        cop.correoSujeto as correoSujeto,
        cop.direccionSujeto as direccionSujeto,
        ae.actividadEconomica as actividadEconomica,
        ae.codigoMh AS codigoMh
        FROM fel_sujeto_excluido cop
        LEFT JOIN mh_019_actividad_economica ae ON ae.actividadEconomicaId = cop.actividadEconomicaId
        WHERE cop.flgDelete = ? 
        ", [0]);

    // hacer if para recibir tipo proveedor y aplicar where  que corresponde 

    $n = 0;
    foreach ($dataProveedores as $dProveedor) {
        $n += 1;
        $datos    =   
                '
                <br><b><i class="fas fa-address-card"></i> Tipo de Documento: </b>'.$dProveedor->tipoDocumentoMHId;
 
        $datosProveedor = '
            <b><i class="fas fa-user-tie"></i> Nombre del sujeto: </b>' . $dProveedor->nombreSujeto;

            // Local
            $datosProveedor .= '<br><b><i class="fas fa-tags"></i> Giro/Actividad economica: </b> ('.$dProveedor->codigoMh.') '.$dProveedor->actividadEconomica;



        $datUbicaciones = $cloud->row("
	            SELECT
                ub.sujetoExcluidoId AS sujetoExcluidoId, 
                ub.direccionSujeto AS direccionSujeto,
                mu.paisDepartamentoId AS paisDepartamentoId,
                mu.municipioPais AS municipioPais,
                de.paisId AS paisId,
                de.departamentoPais AS departamentoPais,
                pa.pais AS pais
            FROM fel_sujeto_excluido ub
            LEFT JOIN cat_paises_municipios mu ON mu.paisMunicipioId = ub.paisMunicipioId
            LEFT JOIN cat_paises_departamentos de ON de.paisDepartamentoId = mu.paisDepartamentoId
            LEFT JOIN cat_paises pa ON pa.paisId = de.paisId
            WHERE ub.flgDelete = ?
        ", [ 0]);

        $nombreUbicacion = '';
        $pais = '';
        $departamento = '';
        $municipio = '';
        $direccion = '';

        $contactosDisabled = 'disabled';
    if (is_object($datUbicaciones)){
        $nombreUbicacion = $datUbicaciones->direccionSujeto;
        $pais = $datUbicaciones->pais;
        $departamento = $datUbicaciones->departamentoPais;
        $municipio = $datUbicaciones->municipioPais;
        $direccion = $datUbicaciones->direccionSujeto;

        $contactosDisabled = '';
    }


    $ubicacion = '
        <b><i class="fas fa-map-marker-alt"></i> Nombre ubicación:</b> '.$direccion.' <br>
        <b><i class="fas fa-map"></i> País: </b> '.$pais.', '.$departamento.', '.$municipio.'
        <br><b><i class="fas fa-route"></i> Dirección: </b> '.$direccion.'<br>
    ';
        
        $jsonEditar = array(
            "typeOperation"     => "update",
            "sujetoExcluidoId"  => $dProveedor->sujetoExcluidoId,
            "nombreSujeto"      => $dProveedor->nombreSujeto
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));

        $jsonEditarContacto = array(
            "typeOperation"    => "update",
            "sujetoExcluidoId"  => $dProveedor->sujetoExcluidoId,
            "nombreSujeto"      => $dProveedor->nombreSujeto
        );
        $funcionEditarContacto = htmlspecialchars(json_encode($jsonEditarContacto));

        $jsonEliminar = array(
            "typeOperation"     => "delete",
            "operation"         => "sujetoExcluido",
            "sujetoExcluidoId"  => $dProveedor->sujetoExcluidoId,
            "nombreSujeto"      => $dProveedor->nombreSujeto
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

       

        $btnEditar = "";  $btnEditarContacto = ""; $btnEliminar = ""; $btnCambiarEstado = "";


            if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(115, $_SESSION["arrayPermisos"])) {
                $btnEditar = '
                   <button type="button" class="btn btn-primary btn-sm ttip" onClick="modalProveedorExcluido('.$funcionEditar.');">
                        <i class="fas fa-pen"></i>
                        <span class="ttiptext">Editar</span>
                    </button>
                ';
            } else {
                // No tiene permiso de editar
            }

            if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(115, $_SESSION["arrayPermisos"])) {
                $btnEditarContacto = '
                   <button type="button" class="btn btn-primary btn-sm ttip" onClick="modalContactoExcluido('.$funcionEditarContacto.');">
                        <i class="fas fa-address-card trailing"></i>
                        <span class="ttiptext">Editar Contacto</span>
                    </button>
                ';
            } else {
                // No tiene permiso de editar
            }

            if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(116, $_SESSION["arrayPermisos"])) {
                $btnEliminar = '
                    <button type="button" class="btn btn-danger btn-sm ttip" onClick="eliminarProveedor('.$funcionEliminar.');">
                        <i class="fas fa-trash-alt"></i>
                        <span class="ttiptext">Eliminar proveedor</span>
                    </button>
                ';
            } else {
                // No tiene permiso de eliminar
            }
            if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(117, $_SESSION["arrayPermisos"])) {
                $btnCambiarEstado = '
                     ';
            } else {
                // No tiene permiso de cambiar estado
            }


            
            $ubicaciones = '';


            if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(118, $_SESSION["arrayPermisos"])) {
                
                $btnContactosProveedor = '
                   
                ';
            } else {
                // No tiene permiso de ver contactos
                $btnContactosProveedor = '';
            }

            $acciones = 
                $btnContactosProveedor.'
                '.$btnEditar.'
                 '.$btnEditarContacto.'
                '.$btnEliminar.'
            ';
 
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $datos,
                $datosProveedor,
                $ubicacion,
                $acciones
            );

  
    } // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }