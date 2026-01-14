<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
    	POST:
    	archivoDescarga
    	nombreArchivoBanco
    	pagoTransferenciaId
    */
    $contenido = "";

    $dataTransferenciaDetalle = $cloud->rows("
        SELECT 
            pt.estadoPago AS estadoPago,
            ptd.pagoTransferenciaDetalleId AS pagoTransferenciaDetalleId, 
            ptd.proveedorCBancariaId AS proveedorCBancariaId, 
            ptd.conceptoTransferencia AS conceptoTransferencia,
            ptd.montoTransferencia AS montoTransferencia, 
            ptd.tablaDetalleId AS tablaDetalleId,
            p.nombreProveedor AS nombreProveedor,
            p.nombreComercial AS nombreComercial,
            org.abreviaturaOrganizacion AS abreviaturaOrganizacion,
            org.codOrganizacion AS codOrganizacion,
            pcb.numeroCuenta AS numeroCuenta,
            pcb.tipoCuenta AS tipoCuenta,
            p.tipoProveedor AS tipoProveedor,
            pcb.proveedorId AS proveedorId
        FROM conta_pagos_transferencias_detalle ptd
        JOIN conta_pagos_transferencias pt ON pt.pagoTransferenciaId = ptd.pagoTransferenciaId
        JOIN comp_proveedores_cbancaria pcb ON pcb.proveedorCBancariaId = ptd.proveedorCBancariaId
        JOIN comp_proveedores p ON p.proveedorId = pcb.proveedorId
        JOIN cat_nombres_organizaciones org ON org.nombreOrganizacionId = pcb.nombreOrganizacionId
        WHERE ptd.pagoTransferenciaId = ? AND ptd.flgDelete = ?
        ORDER BY ptd.pagoTransferenciaDetalleId
    ", [$_POST['pagoTransferenciaId'], 0]);

    $n = 0;
    foreach($dataTransferenciaDetalle as $transferenciaDetalle) {
    	$n++;

    	if($transferenciaDetalle->tipoCuenta == "Corriente") {
    		$tipoCuenta = "C";
    	} else {
    		// Ahorro
    		$tipoCuenta = "A";
    	}

    	if($transferenciaDetalle->tipoProveedor == "Empresa local") {
    		$tipoProveedor = "J"; // Juridico
    	} else {
    		$tipoProveedor = "N"; // Natural
    	}

    	$codBanco = $transferenciaDetalle->codOrganizacion;
        $numeroCuenta = $transferenciaDetalle->numeroCuenta;
        $nombreProveedor = str_replace(';', ',', $transferenciaDetalle->nombreProveedor);
        $nombreProveedor = str_replace("'", ' ', $nombreProveedor);
        $nombreProveedor = str_replace('"', ' ', $nombreProveedor);
        $nombreProveedor = str_replace(',', ' ', $nombreProveedor);
        $nombreProveedor = str_replace('.', ' ', $nombreProveedor);
        $nombreProveedor = str_replace('&', ' ', $nombreProveedor);
        $nombreProveedor = str_replace(':', '', $nombreProveedor);
        $montoCargar = ""; // Este campo se deja vacio según requerimiento de banco agrícola
        $montoAbonar = $transferenciaDetalle->montoTransferencia;
        $numeroFactura = ""; // Este campo es opcional
        $descripcion = str_replace(';', ',', $transferenciaDetalle->conceptoTransferencia);
        $descripcion = str_replace("'", ' ', $descripcion);
        $descripcion = str_replace('"', ' ', $descripcion);
        $descripcion = str_replace(',', ' ', $descripcion);
        $descripcion = str_replace('.', ' ', $descripcion);
        $descripcion = str_replace('&', ' ', $descripcion);
        $descripcion = str_replace(':', '', $descripcion);

        // No se agrega la ubicación en la consulta, porque se pueden duplicar las transferencias si tienen más de una ubicación
        $dataProveedorContacto = $cloud->row("
            SELECT
                pc.contactoProveedor AS contactoProveedor
            FROM comp_proveedores_ubicaciones pu
            JOIN comp_proveedores_contactos pc ON pc.proveedorUbicacionId = pu.proveedorUbicacionId
            WHERE pc.tipoContactoId IN (1, 9, 13) AND pu.proveedorId = ? AND pu.flgDelete = ? AND pc.flgDelete = ?
            LIMIT 1
        ", [$transferenciaDetalle->proveedorId, 0, 0]);

        if($dataProveedorContacto) {
            $correoElectronico = $dataProveedorContacto->contactoProveedor;
        } else {
            $correoElectronico = "mcortez@indupal.com";
        }

        if($_POST['tipoTransferencia'] == "Local") {
   			// Campos = número_de_cuenta;nombre_del_proveedor;;monto_a_abonar;número_factura(opcional);descripción;correo_electrónico
        	$contenido .= "$numeroCuenta;$nombreProveedor;;$montoAbonar;$numeroFactura;$descripcion;$correoElectronico\n";
        } else {
        	// Transfer365
        	// Campos = número_de_cuenta;Código_del_banco;Tipo_de_cuenta_destino;Titular_de_cuenta_destino;Tipo_de_cliente;Monto_a_abonar;Correo;Descripción
        	$contenido .= "$numeroCuenta;$codBanco;$tipoCuenta;$nombreProveedor;$tipoProveedor;$montoAbonar;$correoElectronico;$descripcion\n";
        }
    }

    // Nombre del archivo
    $nombreArchivo = "$_POST[nombreArchivoBanco].txt";

    // Configurar las cabeceras para forzar la descarga
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Content-Length: ' . strlen($contenido));

    echo $contenido;

    exit;
?>