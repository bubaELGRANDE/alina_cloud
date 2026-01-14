<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

  /*  $dataProveedores = $cloud->rows("
        SELECT
            cop.proveedorId as proveedorId, 
            cop.tipoProveedor as tipoProveedor, 
            cop.nrcProveedor AS nrcProveedor,
            cop.tipoDocumento as tipoDocumento, 
            cop.numDocumento as numDocumento, 
            cop.nombreProveedor as nombreProveedor,
            cop.nombreComercial as nombreComercial, 
            cop.descripcionExtranjero AS descripcionExtranjero,
            cop.nombreCompletoRL as nombreCompletoRL, 
            cop.tipoDocumentoRL as tipoDocumentoRL, 
            cop.numDocumentoRL as numDocumentoRL, 
            cop.estadoProveedor as estadoProveedor,
            ae.actividadEconomica as actividadEconomica,
            ae.codigoMh AS codigoMh,
            cop.obsEstadoProveedor AS obsEstadoProveedor
        FROM comp_proveedores cop
        LEFT JOIN mh_019_actividad_economica ae ON ae.actividadEconomicaId = cop.actividadEconomicaId
        WHERE cop.flgDelete = ? AND cop.estadoProveedor = ?
    ", [0, $_POST['estadoProveedor']]);
*/

$flgFiltros = 0;
$proveedorTipo = $_POST["tipoProveedor"];

if ($proveedorTipo == "Local") {
    // Aplicar filtros solo si no es la tabla de extranjeros
    if(!($_POST["nrcProveedor"] == "")) {
        $whereNRC = "AND cop.nrcProveedor LIKE '$_POST[nrcProveedor]%'";
        $flgFiltros++;
    } else {
        // Está vacio, validar los otros filtros
        $whereNRC = "";
    }

    if(!($_POST["numeroDocumento"] == "")){
        $whereNumeroDocumento = "AND cop.numDocumento LIKE '%$_POST[numeroDocumento]%'";
        $flgFiltros++;
    }else{
        $whereNumeroDocumento = "";
    }

    if(!($_POST["nombreRazonSocial"] == "")){
        $whereNombreRazonSocial = "AND (cop.nombreProveedor LIKE '%$_POST[nombreRazonSocial]%' OR cop.nombreComercial LIKE '%$_POST[nombreRazonSocial]%')";
        $flgFiltros++;
    }else{
        $whereNombreRazonSocial = "";
    }

    if($flgFiltros == 0) {
        // No se utilizó ningún filtro, no cargar nada
        $whereCargarConsultas = "AND cop.proveedorId = 0";
    } else {
        $whereCargarConsultas = "";
    }
} else {
    // Ignorar filtros para la tabla de extranjeros
    $whereCargarConsultas = "";
    $whereNombreRazonSocial = "";
    $whereNumeroDocumento = "";
    $whereNRC = "";
}

    $whereTipo = "";
        if($_POST['tipoProveedor'] == "Extranjero") {
        $whereTipo = " AND cop.tipoProveedor IN ('Empresa extranjera', 'Persona extranjera')";
        } else {
        $whereTipo = " AND cop.tipoProveedor IN ('Empresa local', 'Persona local')";
        }

        $dataProveedores = $cloud->rows("
            SELECT
                cop.proveedorId as proveedorId,
                cop.tipoProveedor as tipoProveedor,
                cop.nrcProveedor AS nrcProveedor,
                cop.tipoDocumento as tipoDocumento,
                cop.numDocumento as numDocumento,
                cop.nombreProveedor as nombreProveedor,
                cop.nombreComercial as nombreComercial,
                cop.descripcionExtranjero AS descripcionExtranjero,
                cop.nombreCompletoRL as nombreCompletoRL,
                cop.tipoDocumentoRL as tipoDocumentoRL,
                cop.numDocumentoRL as numDocumentoRL,
                cop.estadoProveedor as estadoProveedor,
                ae.actividadEconomica as actividadEconomica,
                ae.codigoMh AS codigoMh,
                cop.cuentaContable AS cuentaContable,
                cop.obsEstadoProveedor AS obsEstadoProveedor,
                pa.pais AS paisExtranjero,
                cop.direccionProveedorUbicacion AS direccionProveedorUbicacion
            FROM comp_proveedores cop
            LEFT JOIN mh_019_actividad_economica ae ON ae.actividadEconomicaId = cop.actividadEconomicaId
            LEFT JOIN cat_paises pa ON pa.paisId = cop.paisId
            WHERE cop.flgDelete = ? AND cop.estadoProveedor = ? $whereTipo $whereCargarConsultas $whereNombreRazonSocial $whereNumeroDocumento $whereNRC
        ", [0, $_POST['estadoProveedor']]);

    // hacer if para recibir tipo proveedor y aplicar where  que corresponde 

    $n = 0;
    foreach ($dataProveedores as $dProveedor) {
        $n += 1;
        if($dProveedor->tipoProveedor == "Persona local" || $dProveedor->tipoProveedor == "Persona extranjera") {
            $datos    = '  
                <b><i class="fas fa-user-tie"></i> Tipo de proveedor: </b>' . $dProveedor->tipoProveedor .'
                <br><b><i class="fas fa-address-card"></i> NRC: </b>'.$dProveedor->nrcProveedor.'   
                <br><b><i class="fas fa-list-ol"></i> '.$dProveedor->tipoDocumento.': </b> '. $dProveedor->numDocumento . '
                <br><b><i class="fas fa-sort-numeric-up"></i> Cuenta contable:</b> '.$dProveedor->cuentaContable.'
            ';
        } else {
            $datos    = '  
                <b><i class="fas fa-building"></i> Tipo de proveedor: </b>' . $dProveedor->tipoProveedor .'
                <br><b><i class="fas fa-address-card"></i> NRC: </b>'.$dProveedor->nrcProveedor.'   
                <br><b><i class="fas fa-list-ol"></i> '.$dProveedor->tipoDocumento.': </b> '. $dProveedor->numDocumento . '
                <br><b><i class="fas fa-sort-numeric-up"></i> Cuenta contable:</b> '.$dProveedor->cuentaContable.'
            ';
        }
        $datosProveedor = '
            <b><i class="fas fa-user-tie"></i> Nombre del proveedor: </b>' . $dProveedor->nombreProveedor .'
            <br><b><i class="fas fa-address-card"></i> Nombre comercial: </b>'.$dProveedor->nombreComercial;

        if($dProveedor->tipoProveedor == "Empresa extranjera" || $dProveedor->tipoProveedor == "Persona extranjera") {
            $RepresentanteLegal = "<b><i class='fas fa-edit'></i> Descripción: </b> $dProveedor->descripcionExtranjero";
        } else {
            // Local
            $datosProveedor .= '<br><b><i class="fas fa-tags"></i> Giro/Actividad economica: </b> ('.$dProveedor->codigoMh.') '.$dProveedor->actividadEconomica;
            $RepresentanteLegal = '
                <b><i class="fas fa-user-tie"></i> Representante legal (RL): </b>' . $dProveedor->nombreCompletoRL .'
                <br><b><i class="fas fa-address-card"></i> Tipo de documento (RL): </b>'.$dProveedor->tipoDocumentoRL.'<br>
                <br><b><i class="fas fa-list-ol"></i> Numero de documento (RL): </b>'.$dProveedor->numDocumentoRL;
                    
        }

        $datUbicaciones = $cloud->row("
            SELECT
                ub.proveedorUbicacionId AS proveedorUbicacionId, 
                ub.proveedorId AS proveedorId,
                (SELECT pr.nombreProveedor FROM comp_proveedores pr WHERE pr.proveedorId = ub.proveedorId LIMIT 1) AS nombreProveedor,
                ub.paisMunicipioId AS paisMunicipioId,
                ub.nombreProveedorUbicacion AS nombreProveedorUbicacion,
                ub.direccionProveedorUbicacion AS direccionProveedorUbicacion,
                ub.estadoProveedorUbicacion AS estadoProveedorUbicacion,
                mu.paisDepartamentoId AS paisDepartamentoId,
                mu.municipioPais AS municipioPais,
                de.paisId AS paisId,
                de.departamentoPais AS departamentoPais,
                pa.pais AS pais,
                ub.obsEstadoUbicacion as obsEstadoUbicacion
            FROM comp_proveedores_ubicaciones ub
            LEFT JOIN cat_paises_municipios mu ON mu.paisMunicipioId = ub.paisMunicipioId
            LEFT JOIN cat_paises_departamentos de ON de.paisDepartamentoId = mu.paisDepartamentoId
            LEFT JOIN cat_paises pa ON pa.paisId = de.paisId
            WHERE ub.proveedorId = ?  AND ub.flgDelete = ? AND estadoProveedorUbicacion = 'Activo'
        ", [$dProveedor->proveedorId, 0]);

        $proveedorUbiId = '';
        $nombreUbicacion = '';
        $pais = '';
        $departamento = '';
        $municipio = '';
        $direccion = '';

        $contactosDisabled = 'disabled';
    if (is_object($datUbicaciones)){
        $proveedorUbiId = $datUbicaciones->proveedorUbicacionId;
        $nombreUbicacion = $datUbicaciones->nombreProveedorUbicacion;
        $pais = $datUbicaciones->pais;
        $departamento = $datUbicaciones->departamentoPais;
        $municipio = $datUbicaciones->municipioPais;
        $direccion = $datUbicaciones->direccionProveedorUbicacion;

        $contactosDisabled = '';
    }

    if ($proveedorUbiId !== ''){
        $contactosDisabled = '';
    }

    if($dProveedor->tipoProveedor == "Persona local" || $dProveedor->tipoProveedor == "Empresa local") {
        $ubicacion = '
            <b><i class="fas fa-map-marker-alt"></i> Nombre ubicación:</b> '.$nombreUbicacion.' <br>
            <b><i class="fas fa-map"></i> País: </b> '.$pais.', '.$departamento.', '.$municipio.'
            <br><b><i class="fas fa-route"></i> Dirección: </b> '.$direccion.'<br>
        ';
    }else{
        $ubicacion = '
            <b><i class="fas fa-map-marker-alt"></i> Nombre ubicación:</b> '.$nombreUbicacion.' <br>
            <b><i class="fas fa-map"></i> País: </b> '.$dProveedor->paisExtranjero.'
            <br><b><i class="fas fa-route"></i> Dirección: </b> '.$dProveedor->direccionProveedorUbicacion.'<br>
        ';
    }

        
        $jsonEditar = array(
            "typeOperation"    => "update",
            "proveedorId"      => $dProveedor->proveedorId
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));

        $jsonEliminar = array(
            "typeOperation"    => "delete",
            "operation"        => "proveedor",
            "proveedorId"      => $dProveedor->proveedorId,
            "nombreProveedor"  => $dProveedor->nombreProveedor
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $totalUbicaciones = $cloud->count("
            SELECT 
                proveedorUbicacionId
            FROM comp_proveedores_ubicaciones 
            WHERE proveedorId = ? AND flgDelete = ?
        ", [$dProveedor->proveedorId,0]);

        if($dProveedor->estadoProveedor == "Activo"){
            $estado = "Inactivo";
        }else{
            $estado = "Activo";
        }

        $jsonCambiarEstado = array(
            "typeOperation"     => "update",
            "proveedorId"       => $dProveedor->proveedorId,
            "operation"         => "proveedores-inactivos",
            "tituloModal"       => "Se le cambiara el estado al proveedor: <b>$dProveedor->nombreProveedor</b> a $estado",
            "nuevoEstado"       => $estado
        );
        $funcionCambiarEstado = htmlspecialchars(json_encode($jsonCambiarEstado));

        $btnEditar = ""; $btnEliminar = ""; $btnCambiarEstado = "";

        if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(139, $_SESSION["arrayPermisos"])) {
            $totalCuentasBancarias = $cloud->count("
                SELECT proveedorCBancariaId FROM comp_proveedores_cbancaria
                WHERE proveedorId = ? AND flgDelete = ?
            ", [$dProveedor->proveedorId, 0]);

            $jsonCuentasBancarias = array(
                "nombreProveedor"       => $dProveedor->nombreProveedor,
                "proveedorId"           => $dProveedor->proveedorId
            );

            $btnCuentasBancarias = "
                <button type='button' class='btn btn-primary btn-sm ttip' onclick='modalProveedorCuentasBanco(".htmlspecialchars(json_encode($jsonCuentasBancarias)).");'>
                    <span class='badge rounded-pill bg-light text-dark'>$totalCuentasBancarias</span>
                    <i class='fas fa-university'></i>
                    <span class='ttiptext'>Cuentas bancarias</span>
                </button>
            ";
        } else {
            // No tiene permisos de cuentas bancarias
        }


        if($_POST['estadoProveedor'] == "Activo") {
            if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(115, $_SESSION["arrayPermisos"])) {
                $btnEditar = '
                   <button type="button" class="btn btn-primary btn-sm ttip" onClick="modalProveedorN('.$funcionEditar.');">
                        <i class="fas fa-pen"></i>
                        <span class="ttiptext">Editar</span>
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
                    <button type="button" class="btn btn-danger btn-sm ttip" onClick="proveedorInactivo('.$funcionCambiarEstado.');">
                        <i class="fas fa-user-slash"></i>
                        <span class="ttiptext">Cambiar de estado a inactivo</span>
                    </button>
                ';
            } else {
                // No tiene permiso de cambiar estado
            }

            $jsonContactos = array(
                "typeOperation"             => "insert",
                "proveedorUbicacionId"      =>  $proveedorUbiId,
                "nombreProveedorUbicacion"  =>  $nombreUbicacion,
                "nombreProveedor"           =>  $dProveedor->nombreProveedor,
                "estadoProveedorUbicacion"  =>  $_POST['estadoProveedor']
            );
            $funcionContactos = htmlspecialchars(json_encode($jsonContactos));
            
            $ubicaciones = '<button type="button" class="btn btn-primary btn-sm ttip" onclick="changePage(`'.$_SESSION["currentRoute"].'`, `proveedor-ubicaciones`, `proveedorId='.$dProveedor->proveedorId.'&proveedor='.$dProveedor->nombreProveedor.'&estadoProveedor='.$dProveedor->estadoProveedor.'`);">
                <span class="badge rounded-pill bg-light text-dark">'.$totalUbicaciones.'</span>
                <i class="fas fa-globe"></i>
                <span class="ttiptext">Ubicaciones</span>
            </button>';

            $totalContactos = $cloud->count("
                SELECT 
                    proveedorContactoId
                FROM comp_proveedores_contactos
                WHERE proveedorUbicacionId = ? AND flgDelete = ?
            ", [$proveedorUbiId,0]);

            if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(118, $_SESSION["arrayPermisos"])) {
                
                $btnContactosProveedor = '
                    <button type="button" class="btn btn-primary btn-sm ttip" onclick="contactoUbicacion('. $funcionContactos.')" '.$contactosDisabled.'>
                        <span class="badge rounded-pill bg-light text-dark">'.$totalContactos.'</span>
                        <i class="fas fa-address-card"></i>
                        <span class="ttiptext">Contactos</span>
                    </button>
                ';
            } else {
                // No tiene permiso de ver contactos
                $btnContactosProveedor = '';
            }

            $acciones = 
                $btnContactosProveedor.'
                '.$btnEditar.'
                '.$btnCuentasBancarias.'
                '.$btnCambiarEstado.'
                '.$btnEliminar.'
            ';
        } else {
            if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(117, $_SESSION["arrayPermisos"])) {
                $btnCambiarEstado = '
                    <button type="button" class="btn btn-success btn-sm ttip" onClick="proveedorInactivo('.$funcionCambiarEstado.');">
                        <i class="fas fa-user"></i>
                        <span class="ttiptext">Cambiar de estado a activo</span>
                    </button>
                ';
            } else {
                // No tiene permiso de cambiar estado
            }
            $acciones = '
                '.$btnCambiarEstado.'
                <button type="button" class="btn btn-primary btn-sm ttip" onclick="changePage(`'.$_SESSION["currentRoute"].'`, `proveedor-ubicaciones`, `proveedorId='.$dProveedor->proveedorId.'&proveedor='.$dProveedor->nombreProveedor.'&estadoProveedor='.$dProveedor->estadoProveedor.'`);">
                    <span class="badge rounded-pill bg-light text-dark">'.$totalUbicaciones.'</span>
                    <i class="fas fa-globe"></i>
                    <span class="ttiptext">Ubicaciones</span>
                </button>
                '.$btnCuentasBancarias.'
            ';
        }

        if($dProveedor->estadoProveedor == "Activo") {
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $datos,
                $datosProveedor,
                $ubicacion,
                $acciones
            );
        } else {
            $justificacion = $dProveedor->obsEstadoProveedor;
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $datos,
                $datosProveedor,
                $ubicacion,
                $justificacion,
                $acciones
            );
        }
  
    } // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }