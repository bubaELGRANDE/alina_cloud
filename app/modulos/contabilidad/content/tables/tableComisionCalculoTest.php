<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $n = 0;
	
	$filename = '../../../../../libraries/resources/files/txt/comisiones/COEXSA.TXT';
	$data = file($filename);

	foreach($data as $line_num=>$line) {
		$n += 1;

		$identificador = utf8_decode(substr($line, 0, 1));
		$codCliente = utf8_decode(substr($line, 2, 12));
		$nombreCliente =  utf8_decode(substr($line, 15, 20));
		$codVendedor = utf8_decode(substr($line, 36, 3));
		$nombreVendedor = utf8_decode(substr($line, 40, 50));
		$tipoFactura = utf8_decode(substr($line, 91, 20));
		$correlativoFactura = utf8_decode(substr($line, 112, 6));
		$fechaFactura = utf8_decode(substr($line, 119, 8));
		$sucursalFactura = utf8_decode(substr($line, 128, 20));
		$formaPago = utf8_decode(substr($line, 149, 10));
		$codProducto = utf8_decode(substr($line, 160, 14));
		$nombreProducto = utf8_decode(substr($line, 175, 40));
		$lineaProducto = utf8_decode(substr($line, 216, 2));
		$precioUnitario = utf8_decode(substr($line, 219, 13));
		$costo = utf8_decode(substr($line, 233, 13));
		$cantidad = utf8_decode(substr($line, 247, 9));
		$precioFacturado = utf8_decode(substr($line, 257, 13));
		$precioXcantidad = utf8_decode(substr($line, 271, 13));
		$montoFactura = utf8_decode(substr($line, 285, 13));
		$fechaAbono = utf8_decode(substr($line, 299, 8));
		$montoAbono = utf8_decode(substr($line, 308, 13));

		$output['data'][] = array(
			$n,
			$identificador,
			$codCliente,
			$nombreCliente,
			$codVendedor,
			$nombreVendedor,
			$tipoFactura,
			$correlativoFactura,
			$fechaFactura,
			$sucursalFactura,
			$formaPago,
			$codProducto,
			$nombreProducto,
			$fechaAbono,
			$precioUnitario,
			$costo,
			$cantidad,
			$precioFacturado,
			$precioXcantidad,
			$montoFactura,
			$lineaProducto,
			$montoAbono
		);
	}

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>