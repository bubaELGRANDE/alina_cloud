<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataUbicaciones = $cloud->rows("
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
        JOIN cat_paises_municipios mu ON mu.paisMunicipioId = ub.paisMunicipioId
        JOIN cat_paises_departamentos de ON de.paisDepartamentoId = mu.paisDepartamentoId
        JOIN cat_paises pa ON pa.paisId = de.paisId
    	WHERE ub.proveedorId = ?  AND ub.flgDelete = ? AND estadoProveedorUbicacion = ?
    ", [$_POST["id"], 0, $_POST["estadoProveedorUbicacion"]]);
    $n = 0;
    foreach ($dataUbicaciones as $datUbicaciones) {
    	$n += 1;

        $estado = ($datUbicaciones->estadoProveedorUbicacion == "Activo") ? '<span class="text-success fw-bold">Activo</span>' : '<span class="text-danger fw-bold">Inactivo</span>';
        $nombre = '
            <b><i class="fas fa-map-marker-alt"></i> Nombre ubicación:</b> '.$datUbicaciones->nombreProveedorUbicacion.' 
        ';

        $ubicacion = '
            <b><i class="fas fa-map"></i> País: </b> '.$datUbicaciones->pais.', '.$datUbicaciones->departamentoPais.', '.$datUbicaciones->municipioPais.'
            <br><b><i class="fas fa-route"></i> Dirección: </b> '.$datUbicaciones->direccionProveedorUbicacion.'<br>
        ';

        $jsonEditar = array(
            "typeOperation"         => "update",
            "proveedorId"           => $datUbicaciones->proveedorId,
            "proveedorUbicacionId"  => $datUbicaciones->proveedorUbicacionId,
            "tituloModal"           => "Editar ubicacioón: $datUbicaciones->nombreProveedorUbicacion"  
        );
        $funcionEditar = htmlspecialchars(json_encode($jsonEditar));

        $jsonEliminar = array(
            "typeOperation"             => "delete",
            "operation"                 => "proveedor-ubicacion",
            "proveedorUbicacionId"      => $datUbicaciones->proveedorUbicacionId,
            "nombreProveedorUbicacion"  => $datUbicaciones->nombreProveedorUbicacion,
            "nombreProveedor"           => $datUbicaciones->nombreProveedor
        );
        $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

        $jsonContactos = array(
            "typeOperation"             => "insert",
            "proveedorUbicacionId"      =>  $datUbicaciones->proveedorUbicacionId,
            "nombreProveedorUbicacion"  =>  $datUbicaciones->nombreProveedorUbicacion,
            "nombreProveedor"           =>  $datUbicaciones->nombreProveedor,
            "estadoProveedorUbicacion"  =>  $_POST['estadoProveedorUbicacion']
        );
        $funcionContactos = htmlspecialchars(json_encode($jsonContactos));

        $totalContactos = $cloud->count("
            SELECT 
                proveedorContactoId
            FROM comp_proveedores_contactos
            WHERE proveedorUbicacionId = ? AND flgDelete = ?
        ", [$datUbicaciones->proveedorUbicacionId,0]);

        if($datUbicaciones->estadoProveedorUbicacion == "Activo"){
            $estado = "Inactivo";
        }else{
            $estado = "Activo";
        }

        $jsonCambiarEstadoContacto = array(
            "typeOperation"                 => "update",
            "proveedorUbicacionId"          => $datUbicaciones->proveedorUbicacionId,
            "operation"                     => "ubicacion-inactiva",
            "tituloModal"                   => "Se le cambiara el estado a la ubicación: <b>$datUbicaciones->nombreProveedorUbicacion</b> de el proveedor <b>$datUbicaciones->nombreProveedor</b> a $estado",
            "nuevoEstado"                   => $estado
        );
        $funcionCambiarEstadoContacto = htmlspecialchars(json_encode($jsonCambiarEstadoContacto));
        $btnEditar = ""; $btnEliminar = ""; $btnCambiarEstado = "";

        if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(118, $_SESSION["arrayPermisos"])) {
            $btnContactosProveedor = '
                <button type="button" class="btn btn-primary btn-sm ttip" onclick="contactoUbicacion('. $funcionContactos.')">
                    <span class="badge rounded-pill bg-light text-dark">'.$totalContactos.'</span>
                    <i class="fas fa-address-card"></i>
                    <span class="ttiptext">Contactos</span>
                </button>
            ';
        } else {
            // No tiene permiso de ver contactos
            $btnContactosProveedor = '';
        }

        if($_POST['estadoProveedorUbicacion'] == "Activo" ) {
            if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(115, $_SESSION["arrayPermisos"])) {
                $btnEditar = '
                    <button type="button" class="btn btn-primary btn-sm ttip" onClick="proveedorUbicacion('.$funcionEditar.');">
                        <i class="fas fa-pencil-alt"></i>
                        <span class="ttiptext">Editar</span>
                    </button>
                ';
            } else {
                // No tiene permiso de editar
            }
            if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(116, $_SESSION["arrayPermisos"])) {
                $btnEliminar = '
                    <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarUbicacion('.$funcionEliminar.');">
                        <i class="fas fa-trash-alt"></i>
                        <span class="ttiptext">Eliminar</span>
                    </button>
                ';
            } else {
                // No tiene permiso de eliminar
            }
            if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(117, $_SESSION["arrayPermisos"])) {
                $btnCambiarEstado = '
                    <button type="button" class="btn btn-danger btn-sm ttip" onclick="contactoProveedorInactivo('.$funcionCambiarEstadoContacto.');">
                        <i class="fas fa-user-slash"></i>
                        <span class="ttiptext">Deshabilitar</span>
                    </button>
                ';
            } else {
                // No tiene permiso de cambiar estado
            }
            $acciones ='
                '.$btnContactosProveedor.'
                '.$btnEditar.'
                '.$btnCambiarEstado.'
                '.$btnEliminar.'
            ';   
        }else{
            if(in_array(85, $_SESSION["arrayPermisos"]) || in_array(117, $_SESSION["arrayPermisos"])) {
                $btnCambiarEstado = '
                    <button type="button" class="btn btn-success btn-sm ttip" onclick="contactoProveedorInactivo('.$funcionCambiarEstadoContacto.');">
                        <i class="fas fa-user"></i>
                        <span class="ttiptext">Habilitar</span>
                    </button>
                ';
            } else {
                // No tiene permiso de cambiar estado
            }
            $acciones = '
                '.$btnContactosProveedor.'
                '.$btnCambiarEstado.'
            ';
        }
        
        if($datUbicaciones->estadoProveedorUbicacion == "Activo"){
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $nombre,
                $ubicacion,
                $acciones
            );
        }else{
            $justificacion = $datUbicaciones->obsEstadoUbicacion;
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $nombre,
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
?>