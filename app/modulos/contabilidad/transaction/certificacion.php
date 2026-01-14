<?php 
	if(isset($_SESSION["usuarioId"]) && isset($operation)) {
		/* // UUID V4
		function get_guid() {
		    if (function_exists('com_create_guid') === true)
		        return trim(com_create_guid(), '{}');
		    $data = PHP_MAJOR_VERSION < 7 ? openssl_random_pseudo_bytes(16) : random_bytes(16);
		    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // Set version to 0100
		    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // Set bits 6-7 to 10
		    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
		}

		// Firmar DTE
		function enviarSolicitudPOST($url, $datosJSON) {
		    // Inicializar la sesión cURL
		    $ch = curl_init($url);

		    // Configurar las opciones de la solicitud cURL
		    $agent = $_SERVER['HTTP_USER_AGENT'];
		    $opciones = array(
		        CURLOPT_RETURNTRANSFER => true,  // Devolver el resultado en lugar de imprimirlo
		        CURLOPT_POST => true,            // Realizar una solicitud POST
		        CURLOPT_POSTFIELDS => $datosJSON, // Datos a enviar en el cuerpo de la solicitud
		        CURLOPT_FOLLOWLOCATION => true,
		        CURLOPT_VERBOSE => true,
		        CURLOPT_HTTPHEADER => array(
		            'Content-Type: application/json', // Establecer la cabecera Content-Type
		        )
		    );

		    curl_setopt_array($ch, $opciones);

		    // Ejecutar la solicitud cURL y obtener la respuesta
		    $respuesta = curl_exec($ch);

		    // Verificar si hubo algún error durante la solicitud
		    if (curl_errno($ch)) {
		        echo 'Error en la solicitud cURL: ' . curl_error($ch);
		    }

		    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		    // echo 'Código de estado HTTP: ' . $http_code . '<br>';

		    // Cerrar la sesión cURL
		    curl_close($ch);

		    // Devolver la respuesta obtenida del servidor
		    return $respuesta;
		}

		function enviarDTEHacienda($url, $datosJSON, $token) {
		    // Inicializar la sesión cURL
		    $ch = curl_init($url);

		    // Configurar las opciones de la solicitud cURL
		    $agent = $_SERVER['HTTP_USER_AGENT'];
		    $opciones = array(
		        CURLOPT_RETURNTRANSFER => true,  // Devolver el resultado en lugar de imprimirlo
		        CURLOPT_POST => true,            // Realizar una solicitud POST
		        CURLOPT_POSTFIELDS => $datosJSON, // Datos a enviar en el cuerpo de la solicitud
		        CURLOPT_FOLLOWLOCATION => true,
		        // CURLOPT_POSTREDIR => 3,
		        // CURLOPT_SSL_VERIFYPEER => false,
		        CURLOPT_VERBOSE => true,
		        CURLOPT_HTTPHEADER => array(
		            'Content-Type: application/json', // Establecer la cabecera Content-Type
		            'User-Agent: ' . $agent,
		            'Authorization: ' . $token,
		        )
		    );

		    curl_setopt_array($ch, $opciones);

		    // Ejecutar la solicitud cURL y obtener la respuesta
		    $respuesta = curl_exec($ch);

		    // Verificar si hubo algún error durante la solicitud
		    if (curl_errno($ch)) {
		        echo 'Error en la solicitud cURL: ' . curl_error($ch);
		    }

		    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		    // echo 'Código de estado HTTP: ' . $http_code . '<br>';

		    // Cerrar la sesión cURL
		    curl_close($ch);

		    // Devolver la respuesta obtenida del servidor
		    return $respuesta;
		}

		function enviarSolicitudGET($url, $token) {
		    // Inicializar la sesión cURL
		    $ch = curl_init($url);

		    // Configurar las opciones de la solicitud cURL
		    $agent = $_SERVER['HTTP_USER_AGENT'];
			$opciones = array(
			    CURLOPT_RETURNTRANSFER => true,  // Devolver el resultado en lugar de imprimirlo
			    CURLOPT_FOLLOWLOCATION => true,
			    CURLOPT_VERBOSE => true,
			    CURLOPT_HTTPHEADER => array(
			        'Content-Type: application/json', // Establecer la cabecera Content-Type si es necesario
			        'User-Agent: ' . $agent,
			        'Authorization: ' . $token,
			    )
			);

		    curl_setopt_array($ch, $opciones);

		    // Ejecutar la solicitud cURL y obtener la respuesta
		    $respuesta = curl_exec($ch);

		    // Verificar si hubo algún error durante la solicitud
		    if (curl_errno($ch)) {
		        echo 'Error en la solicitud cURL: ' . curl_error($ch);
		    }

		    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		    // echo 'Código de estado HTTP: ' . $http_code . '<br>';

		    // Cerrar la sesión cURL
		    curl_close($ch);

		    // Devolver la respuesta obtenida del servidor
		    return $respuesta;
		} */

		switch($operation) {
			case "certificar-comprobante-retencion":
				$flgCertificar = "No";

				$dataTokenParametrizacion = $cloud->row("
					SELECT parametro FROM conf_parametrizacion
					WHERE tipoParametrizacion = ? AND flgDelete = ? 
					LIMIT 1
				", ["Token de certificación", 0]);

				$token = $dataTokenParametrizacion->parametro;

				$dataDTEGeneral = $cloud->row("
					SELECT
						f.facturaId AS facturaId,
						cat002.versionMH AS versionDTEMH,
						f.identificacionAmbiente AS identificacionAmbiente,
						cat002.codigoMH AS codigoDTEMH,
						cat003.codigoMH AS codigoModeloMH,
						f.fechaEmision AS fechaEmision,
						f.horaEmision AS horaEmision,
						f.tipoMoneda AS tipoMoneda,
						ffe.nitEmisor AS nitEmisor,
						ffe.nrcEmisor AS nrcEmisor,
						ffe.nombreEmisor AS nombreEmisor,
						ffe.nombreComercialEmisor AS nombreComercialEmisor,
						cat019emisor.codigoMh AS codigoActividadEmisorMH,
						cat019emisor.actividadEconomica AS actividadEconomicaEmisor,
						ffe.sucursalId AS sucursalId,
						s.codEstablecimientoMH AS codEstablecimientoMH,
						s.direccionSucursal AS direccionSucursal,
						cpds.codigoMH AS codigoDepartamentoSucursalMH,
						cpms.codigoMH AS codigoMunicipioSucursalMH,
						pro.proveedorId AS proveedorId,
						proubi.proveedorUbicacionId AS proveedorUbicacionId,
						pro.numDocumento AS numDocumentoProveedor,
						pro.nrcProveedor AS nrcProveedor,
			    		CASE
			    			WHEN pro.nombreProveedor = '' OR pro.nombreProveedor = NULL THEN pro.nombreComercial
			    			ELSE pro.nombreProveedor
			    		END AS nombreProveedor,
			    		pro.nombreComercial AS nombreComercialProveedor,
						cpdscliente.codigoMH AS codigoDepartamentoClienteMH,
						cpmscliente.codigoMH AS codigoMunicipioClienteMH,
						proubi.direccionProveedorUbicacion AS direccionProveedorUbicacion,
			    		ffr.ivaRetenido AS ivaRetenido,
			    		ffr.ivaPercibido AS ivaPercibido,
			    		ffr.rentaRetenido AS rentaRetenido,
			    		cat016.codigoMH AS codigoCondicionOperacion,
			    		f.periodoPlazo AS periodoPlazo,
			    		f.sujetoExcluidoId AS sujetoExcluidoId,
			    		ffe.tipoEstablecimientoMH AS tipoEstablecimientoMH,
			    		ffe.puntoVentaMH AS puntoVentaMH,
						f.tipoRemisionMHId AS tipoRemisionMHId,
						pro.tipoDocumento AS tipoDocumento,
						cat019receptor.codigoMh AS codigoActividadClienteMH,
						cat019receptor.actividadEconomica AS actividadEconomicaCliente
					FROM fel_factura f
					JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = f.tipoDTEId
					JOIN mh_003_tipo_modelo cat003 ON cat003.tipoModeloMHId = f.tipoModeloMHId
					JOIN fel_factura_emisor ffe ON ffe.facturaId = f.facturaId
					JOIN mh_019_actividad_economica cat019emisor ON cat019emisor.actividadEconomicaId = ffe.actividadEconomicaId
					JOIN cat_sucursales s ON s.sucursalId = ffe.sucursalId
					JOIN cat_paises_municipios cpms ON cpms.paisMunicipioId = s.paisMunicipioId
					JOIN cat_paises_departamentos cpds ON cpds.paisDepartamentoId = cpms.paisDepartamentoId
					LEFT JOIN comp_proveedores_ubicaciones proubi ON proubi.proveedorUbicacionId = f.proveedorUbicacionId
					LEFT JOIN comp_proveedores pro ON pro.proveedorId = proubi.proveedorId
					JOIN fel_factura_retenciones ffr ON ffr.facturaId = f.facturaId
					LEFT JOIN cat_paises_municipios cpmscliente ON cpmscliente.paisMunicipioId = proubi.paisMunicipioId
					LEFT JOIN cat_paises_departamentos cpdscliente ON cpdscliente.paisDepartamentoId = cpmscliente.paisDepartamentoId
					JOIN mh_016_condicion_factura cat016 ON cat016.condicionFacturaId = f.condicionFacturaId
					LEFT JOIN mh_019_actividad_economica cat019receptor ON cat019receptor.actividadEconomicaId = pro.actividadEconomicaId
					WHERE f.facturaId = ? AND f.flgDelete = ?
				", [$_POST['facturaId'], 0]);

				
				// LEFT en mh_019_actividad_economica porque hay clientes que no tienen giro
				// ponerle dos puntos al núm DTE en la table de la modal ok


				$dataCertificacionAnterior = $cloud->row("
					SELECT facturaCertificacionId, codigoGeneracion FROM fel_factura_certificacion
					WHERE facturaId = ? AND flgDelete = ?
					ORDER BY facturaCertificacionId DESC
					LIMIT 1
				", [$_POST['facturaId'], 0]);

				if($dataCertificacionAnterior) {
					$datosJSON = json_encode(array(
						"contentType" 			=> "application/JSON"
					));

					$url = "https://admin.factura.gob.sv/prod/consulta/consulta-ssc/dte/" . $dataCertificacionAnterior->codigoGeneracion;

					// Realizar la solicitud POST y obtener la respuesta
					$jsonAPI = enviarSolicitudGET($url, $token);
					$dteAPI = json_decode($jsonAPI, true);

					if((isset($dteAPI["estadoDocInc"]) && $dteAPI["estadoDocInc"] == "SR") || (isset($dteAPI["estadoDocInc"]) && $dteAPI["estadoDoc"] == "Transmitido satisfactoriamente")) {
						// Se certificó y se aceptó el anterior, actualizar el estado
						$flgCertificar = "No";
						$selloRecibido = $dteAPI["selloVal"];

						// cat_tipos_contacto que tienen que ver con correo
						$dataCorreoCliente = $cloud->row("
							SELECT contactoProveedor FROM comp_proveedores_contactos
							WHERE proveedorUbicacionId = ? AND tipoContactoId IN (1, 9, 13) AND flgContactoPrincipal = ? AND flgDelete = ?
							ORDER BY proveedorContactoId DESC
							LIMIT 1
						", [$dataDTEGeneral->proveedorUbicacionId, 1, 0]);

						if($dataCorreoCliente) {
							$correoCliente = $dataCorreoCliente->contactoProveedor;
						} else {
							// Default Casa Matriz
							$correoCliente = "ventas@indupal.com";
						}

						// Veríficar el operation para ver que se esta mandando por el Json

						
						$update = [
							"selloRecibido" 		=> $selloRecibido,
							"descripcionMsg" 		=> "Se certificó y recibió la respuesta del API",
							"estadoCertificacion" 	=> "Certificado"
						];
						$where = ["facturaCertificacionId" => $dataCertificacionAnterior->facturaCertificacionId];
						$cloud->update("fel_factura_certificacion", $update, $where);

						$jsonRespuesta = array(
							"respuesta" 			=> "success",
							"facturaId" 			=> $_POST['facturaId'],
							"estado" 				=> "PROCESADO",
							"selloRecibido" 		=> $selloRecibido,
							"codigoGeneracion" 		=> $dataCertificacionAnterior->codigoGeneracion,
							"correoCliente" 		=> $correoCliente,
							"proveedorUbicacionId"  => $dataDTEGeneral->proveedorUbicacionId,
							"tipoDTEId" 			=> $_POST['tipoDTEId'],
							"fechaEmision" 			=> $dataDTEGeneral->fechaEmision,
							"descripcionMsg" 		=> "Certificación anterior recibida con éxito",
							"observaciones" 		=> "Se certificó y recibió la respuesta del API",
							"jsonDepurar" 			=> $jsonAPI,
							"jsonDTE"				=> $dteAPI
						);
					} else {
						$flgCertificar = "Sí";
					}
				} else {					
					$flgCertificar = "Sí";
				}

				if($flgCertificar == "Sí") {
					$respuesta = "";

					// URL de destino
					$url = 'http://216.246.113.187:8113/firmardocumento/';
					$jsonDTE = "";
					$dteFirmado = "";

					// Dejar ya preparada la consulta del detalle y solo iterar para armar el cuerpoDocumento especifico para cada tipo de documento
					// Para no estar copiando y pegando las mismas consultas en cada case
					$dataDTEDetalle = $cloud->rows("
						SELECT
							fd.facturaDetalleId AS facturaDetalleId, 
							fd.productoId AS productoId, 
							fd.codProductoFactura AS codProductoFactura, 
							fd.nombreProductoFactura AS nombreProductoFactura, 
							fd.tipoItemMHId AS tipoItemMHId, 
							cat011.codigoMH AS codigoTipoItemMH,
							fd.costoPromedio AS costoPromedio, 
							fd.precioUnitario AS precioUnitario, 
							fd.precioUnitarioIVA AS precioUnitarioIVA, 
							fd.precioVenta AS precioVenta, 
							fd.precioVentaIVA AS precioVentaIVA, 
							fd.cantidadProducto AS cantidadProducto, 
							fd.ivaUnitario AS ivaUnitario, 
							fd.ivaTotal AS ivaTotal, 
							fd.porcentajeDescuento AS porcentajeDescuento, 
							fd.descuentoUnitario AS descuentoUnitario, 
							fd.descuentoTotal AS descuentoTotal, 
							fd.subTotalDetalle AS subTotalDetalle, 
							fd.subTotalDetalleIVA AS subTotalDetalleIVA, 
							fd.totalDetalle AS totalDetalle, 
							fd.totalDetalleIVA AS totalDetalleIVA, 
							fd.facturaRelacionadaId AS facturaRelacionadaId,
							prod.unidadMedidaId AS unidadMedidaId,
							udm.codigoMH AS codigoUDMMH,
							fd.ivaRetenidoDetalle AS ivaRetenidoDetalle
						FROM fel_factura_detalle fd
						JOIN mh_011_tipo_item cat011 ON cat011.tipoItemMHId = fd.tipoItemMHId
						JOIN inv_productos prod ON prod.productoId = fd.productoId
						LEFT JOIN cat_unidades_medida udm ON udm.unidadMedidaId = prod.unidadMedidaId
						WHERE fd.facturaId = ? AND fd.flgDelete = ?
					", [$dataDTEGeneral->facturaId, 0]);

					// Dejar ya la consulta del pago y cobro de la factura
					// Está por separado a pesar que solo hay 1 registro para recordar que las formas de pago son múltiples
					// Pero de momento, Magic solo envía una forma de pago
					$dataDTEPago = $cloud->row("
						SELECT
							ffp.montoPago AS totalFactura,
							cat017.codigoMH AS codigoFormaPagoMH
						FROM fel_factura_pago ffp 
						JOIN mh_017_forma_pago cat017 ON cat017.formaPagoId = ffp.formaPagoId
						WHERE ffp.facturaId = ? AND ffp.flgDelete = ?
					", [$dataDTEGeneral->facturaId, 0]);

					// Contactos de la sucursal
						$dataTelefonoSucursal = $cloud->row("
							SELECT contactoSucursal FROM cat_sucursales_contacto
							WHERE sucursalId = ? AND descripcionCSucursal = ? AND flgDelete = ?
							LIMIT 1
						", [$dataDTEGeneral->sucursalId, "Teléfono de sucursal", 0]);

						if($dataTelefonoSucursal) {
							$telefonoSucursal = $dataTelefonoSucursal->contactoSucursal;
						} else {
							// Default Casa Matriz
							$telefonoSucursal = "22312800";
						}

						$dataCorreoSucursal = $cloud->row("
							SELECT contactoSucursal FROM cat_sucursales_contacto
							WHERE sucursalId = ? AND descripcionCSucursal = ? AND flgDelete = ?
							LIMIT 1
						", [$dataDTEGeneral->sucursalId, "Correo de sucursal", 0]);

						if($dataCorreoSucursal) {
							$correoSucursal = $dataCorreoSucursal->contactoSucursal;
						} else {
							// Default Casa Matriz
							$correoSucursal = "ventas@indupal.com";
						}

					// Contactos del cliente
						// cat_tipos_contacto que tienen que ver con teléfonos
						$dataTelefonoCliente = $cloud->row("
							SELECT contactoProveedor FROM comp_proveedores_contactos
							WHERE proveedorUbicacionId = ? AND tipoContactoId IN (2, 3, 4, 6, 7, 8, 10, 11, 12) AND flgDelete = ?
							ORDER BY proveedorContactoId DESC
							LIMIT 1
						", [$dataDTEGeneral->proveedorUbicacionId, 0]);

						if($dataTelefonoCliente) {
							if($dataTelefonoCliente->contactoProveedor == "0") {
								$telefonoCliente = NULL;
							} else {
								if (strlen($dataTelefonoCliente->contactoProveedor) >= 8){
									$telefonoCliente = str_replace("-", "", $dataTelefonoCliente->contactoProveedor);
								} else {
									$telefonoCliente = NULL;
								}
							}
						} else {
							// Default Casa Matriz
							$telefonoCliente = NULL;
						}

						// cat_tipos_contacto que tienen que ver con correo
						$dataCorreoCliente = $cloud->row("
							SELECT contactoProveedor FROM comp_proveedores_contactos
							WHERE proveedorUbicacionId = ? AND tipoContactoId IN (1, 9, 13) AND flgContactoPrincipal = ? AND flgDelete = ?
							ORDER BY proveedorContactoId DESC
							LIMIT 1
						", [$dataDTEGeneral->proveedorUbicacionId, 1, 0]);

						if($dataCorreoCliente) {
							$correoCliente = $dataCorreoCliente->contactoProveedor;
						} else {
							// Default Casa Matriz
							$correoCliente = "ventas@indupal.com";
						}

					// Este se forma con código de establecimiento, entre otros campos (ver documentación técnica)
					$complementoNumControl = $dataDTEGeneral->tipoEstablecimientoMH . $dataDTEGeneral->puntoVentaMH;
					// Hacienda permite 15 caracteres, rellenar con cero
					$correlativoNumControl = str_pad($dataDTEGeneral->facturaId, 15, "0", STR_PAD_LEFT);
					$numeroControl = "DTE-$dataDTEGeneral->codigoDTEMH-$complementoNumControl-$correlativoNumControl";

					$uuidDTE = strtoupper(get_guid());

					// Variables para emisor
						$codEstableMH = $dataDTEGeneral->tipoEstablecimientoMH;
						$codEstable = $dataDTEGeneral->tipoEstablecimientoMH;
						$codPuntoVentaMH = $dataDTEGeneral->puntoVentaMH;
						$codPuntoVenta = $dataDTEGeneral->puntoVentaMH;

					// Variables para identificacion
						// mh_004_tipo_operacion donde 1 = Transmisión normal
						// Para la contingencia, en su respectivo case se cambiará este valor
						$tipoOperacionMH = 1;
						$tipoContingenciaMH = NULL;
						$motivoContingenciaMH = NULL;

					// Variable generales
					$documentoRelacionadoMH = NULL;
					$otrosDocumentos = NULL;
					$ventaTercero = NULL;

					// Comprobantes de retención

					// Los comprobantes de retención llevan documentoRelacionado
					$documentoRelacionadoMH = array();

					$dataFacturaRelacionada = $cloud->row("
						SELECT
							ffrel.facturaIdRelacionada AS facturaIdRelacionada,
							cat002.codigoMH AS codigoDTERelacionadoMH,
							cat002rel.codigoMH AS codigoDTEMagicMH,
							cat007.codigoMH AS codigoTipoGeneracionMH,
							ffrel.numeroDocumentoRelacionada AS numeroDocumentoRelacionada,
							ffrel.fechaEmisionRelacionada AS fechaEmisionRelacionada
						FROM fel_factura_relacionada ffrel
						LEFT JOIN fel_factura f ON f.facturaId = ffrel.facturaIdRelacionada
						LEFT JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = f.tipoDTEId
						LEFT JOIN mh_002_tipo_dte cat002rel ON cat002rel.tipoDTEId = ffrel.tipoDTEId
						JOIN mh_007_tipo_generacion_documento cat007 ON cat007.tipoGeneracionDocId = ffrel.tipoGeneracionDocId
						WHERE ffrel.facturaId = ? AND ffrel.flgDelete = ?
					", [$dataDTEGeneral->facturaId, 0]);

					if($dataFacturaRelacionada->facturaIdRelacionada == 0) {
						// Fue físico, asignar el código según magic
						$tipoDocumentoRelacionado = $dataFacturaRelacionada->codigoDTEMagicMH;
					} else {
						// Debería ser crédito fiscal también pero si más adelante una nota de crédito puede afectar otro tipo de documento, queda declarado
						$tipoDocumentoRelacionado = $dataFacturaRelacionada->codigoDTERelacionadoMH;
					}

					// El script que convierte asigna en automático, pero si es otro número, dejo la variable afuera para que se pueda validar y afectar
					$numeroDocumentoRelacionada = $dataFacturaRelacionada->numeroDocumentoRelacionada;
					// Lo mismo para la fecha
					$fechaEmisionRelacionada = $dataFacturaRelacionada->fechaEmisionRelacionada;

					// Puede haber más de un documento relacionado, pero en Magic actualmente solo se puede relacionar un Crédito fiscal, por eso queda declarado con [0]
					$documentoRelacionado[0] = array(
						'tipoDocumento' 			=> $tipoDocumentoRelacionado,
						'tipoGeneracion' 			=> (int)$dataFacturaRelacionada->codigoTipoGeneracionMH,
						'numeroDocumento' 			=> $numeroDocumentoRelacionada,
						'fechaEmision' 				=> $fechaEmisionRelacionada
					);
					$documentoRelacionadoMH = $documentoRelacionado;

					// Formar el cuerpoDocumento
						$cuerpoDocumento = array();
						$numItem = 0;

						// Variables del cuerpo
							$numeroDocumentoCuerpo = $numeroDocumentoRelacionada;
							$codTributoCuerpo = NULL;
							// No tenemos venta no sujeta
							$ventaNoSujetaCuerpo = 0;
							$tributosCuerpo = NULL;
							// No sé qué es
							$psvCuerpo = 0;
							// No tenemos ventas no gravadas
							$noGravadoCuerpo = 0;

						// Variables para el resumenCuerpo
							$subTotalVentasResumen = 0;
							$totalDescuentoResumen = 0;
							$totalIVAResumen = 0;

						foreach ($dataDTEDetalle as $dteDetalle) {
							// Notas de crédito se declara tributos
							$tributosCuerpo = array("20");

							if(stristr($dteDetalle->nombreProductoFactura, "servicio") || $dteDetalle->codigoUDMMH == "99" || $dteDetalle->codigoTipoItemMH == "4") {
								// Otra por ser un servicio, FEL daba error
								$unidadMedidaCuerpo = "99";
								// mh_015_tributos
								// Otras tasas casos especiales
								// Cuando la unidadMedida es 99
								$codTributoCuerpo = "D5";
							} else {
								$codTributoCuerpo = NULL;
								if($dteDetalle->codigoUDMMH == "" || is_null($dteDetalle->codigoUDMMH) || $dteDetalle->codigoUDMMH == NULL) {
									// Default unidad
									$unidadMedidaCuerpo = "59";
								} else {
									$unidadMedidaCuerpo = $dteDetalle->codigoUDMMH;
								}
							}

							// No hay venta exenta en notas de crédito
							// FEL necesita precios sin IVA
							if($dteDetalle->precioVenta > $dteDetalle->precioUnitario) {
								// Se vendió a un precio más alto que el de lista
								$precioUnitarioCuerpo = $dteDetalle->precioVenta;
							} else {
								$precioUnitarioCuerpo = $dteDetalle->precioUnitario;
							}
							$ventaExentaCuerpo = 0;
							$ventaGravadaCuerpo = $dteDetalle->totalDetalle;
							$subTotalVentasResumen += $dteDetalle->totalDetalle;
							$totalDescuentoResumen += $dteDetalle->descuentoTotal;
							$totalIVAResumen += $dteDetalle->ivaTotal;

							$dataFacturaQueRelaciona = $cloud->row("
								SELECT 
									ffr.tipoDTEId AS tipoDTEId,
									cat002.codigoMH AS tipoDTEMH,
									ffr.tipoGeneracionDocId AS tipoGeneracionDocId,
									cat007.codigoMH AS tipoGeneracionDoc,
									ffr.numeroDocumentoRelacionada AS numeroDocumentoRelacionada,
									ffr.fechaEmisionRelacionada AS fechaEmisionRelacionada
								FROM fel_factura_relacionada ffr 
								JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = ffr.tipoDTEId
								JOIN mh_007_tipo_generacion_documento cat007 ON cat007.tipoGeneracionDocId = ffr.tipoGeneracionDocId
								WHERE ffr.facturaRelacionadaId = ? AND ffr.flgDelete = ?
							", [$dteDetalle->facturaRelacionadaId, 0]);

							$cuerpoDocumento[$numItem] = array(
								"numItem" 					=> (int)($numItem + 1),
								"tipoDte" 					=> $dataFacturaQueRelaciona->tipoDTEMH,
								"tipoDoc" 					=> (int)$dataFacturaQueRelaciona->tipoGeneracionDoc,
								"numDocumento" 				=> $dataFacturaQueRelaciona->numeroDocumentoRelacionada,
								"fechaEmision" 				=> $dataFacturaQueRelaciona->fechaEmisionRelacionada,
								"montoSujetoGrav" 			=> (float)$dteDetalle->totalDetalle,
								"codigoRetencionMH" 		=> "22", // CAT-006 IVA Retenido
								"ivaRetenido" 				=> (float)$dteDetalle->ivaRetenidoDetalle,
								"descripcion" 				=> $dteDetalle->nombreProductoFactura,
							);
							$numItem++;
						} // foreach dteDetalle
					// Fin formar cuerpoDocumento
					
					// Formar resumenDocumento
						// Variables del cuerpo
							// No tenemos venta no sujeta
							$totalNoSujetoResumen = 0;
							$descuNoSujResumen = 0;
							$tributosResumen = NULL;
							$totalNoGravadoResumen = 0;
							$saldoFavorResumen = 0;
							$referenciaPagoResumen = NULL;
							$periodoPagoResumen = ($dataDTEGeneral->periodoPlazo == 0 ? NULL : (int)$dataDTEGeneral->periodoPlazo);
							// mh_018_plazo_pago 01 = Dias
							// De momento dejarlo como 01, porque Indupal solo da días de Crédito
							$plazoPagoResumen = '01';
							$numPagoElectronico = NULL;

							// mh_016_condicion_factura 1 = Contado, 2 = Crédito, 3 = Otro
							// Solicitud de Heidi Reyes para informar la operación si es contado o crédito
							$condicionOperacionResumen = $dataDTEGeneral->codigoCondicionOperacion;

							// No hay venta exenta en créditos fiscales
							// FEL necesita precios sin IVA
							$totalExentaResumen = 0;
							$totalGravadaResumen = $subTotalVentasResumen;
							$descuentoExentoResumen = 0;
							//$descuentoGravadoResumen = $totalDescuentoResumen;
							$descuentoGravadoResumen = 0;

							// Porcentaje de descueto global resumenDocumento por regla de 3
								$porcentajeDescuentoGlobal = ($totalDescuentoResumen * 100) / $dataDTEPago->totalFactura;

						// Si "subTotal" da error, restar totalDescuentoResumen porque probablemente asi lo interprete Hacienda
						// totalPagar se manda SIN IVA en Créditos fiscales
						// Crédito fiscal declara tributos
						$tributosResumen = array(
							0 			=> array(
								"codigo" 				=> "20",
								"descripcion" 			=> "Impuesto al Valor Agregado 13%",
								"valor" 				=> (float)round($totalIVAResumen, 2)
							)
						);
						$totalPagar = $dataDTEPago->totalFactura;
						$resumenDocumento = array(
							"totalSujetoRetencion" 		=> (float)round($subTotalVentasResumen, 2),
							"totalIVAretenido" 			=> (float)round($dataDTEGeneral->ivaRetenido, 2),
							"totalIVAretenidoLetras" 	=> dineroLetras($dataDTEGeneral->ivaRetenido, "decimal") . " USD"
					    );
					// Fin formar resumenDocumento

					// Variables generales
						$extensionDTE = NULL;
						$apendiceDTE = NULL;

					$nrcProveedor = ($dataDTEGeneral->nrcProveedor == "" ? NULL : str_replace("-", "", $dataDTEGeneral->nrcProveedor));
					$numDocumentoProveedor = str_replace("-", "", $dataDTEGeneral->numDocumentoProveedor);

					if($dataDTEGeneral->tipoDocumento == "NIT") {
						$tipoDocumentoReceptor = "36";
					} else if($dataDTEGeneral->tipoDocumento == "DUI") {
						//$tipoDocumentoReceptor = "13";
						// Teoria de Moises: 14-05-2024 Hacienda no acepta Retencion a DUI
						// Nueva teoria: 16-04-2025 Hcienda ya no necesita los 14 caracteres para el DUI
						$tipoDocumentoReceptor = "36";
						// $numDocumentoProveedor = str_pad($numDocumentoProveedor, 14, "0", STR_PAD_LEFT);
						$numDocumentoProveedor = $numDocumentoProveedor;
					} else {
						// Otro
						$tipoDocumentoReceptor = "37";
					}

					if($dataDTEGeneral->codigoActividadClienteMH == "" || is_null($dataDTEGeneral->codigoActividadClienteMH)) {
						// No se ha actualizado la inf. del cliente, reportar como Otros
						$codActividadEconomicaReceptor = "10005";
						$descripcionActividadEconomicaReceptor = "Otros";
					} else {
						$codActividadEconomicaReceptor = $dataDTEGeneral->codigoActividadClienteMH;
						$descripcionActividadEconomicaReceptor = $dataDTEGeneral->actividadEconomicaCliente;
					}

					$jsonDTE = array(
						'identificacion' 			=> array (
							'version' 					=> (int)$dataDTEGeneral->versionDTEMH,
							'ambiente' 					=> $dataDTEGeneral->identificacionAmbiente,
							'tipoDte' 					=> $dataDTEGeneral->codigoDTEMH,
							'numeroControl' 			=> $numeroControl,
							'codigoGeneracion' 			=> $uuidDTE,
							'tipoModelo' 				=> (int)$dataDTEGeneral->codigoModeloMH,
							'tipoOperacion' 			=> (int)$tipoOperacionMH,
							'tipoContingencia' 			=> $tipoContingenciaMH,
							'motivoContin' 				=> $motivoContingenciaMH,
							'fecEmi' 					=> $dataDTEGeneral->fechaEmision,
							'horEmi' 					=> $dataDTEGeneral->horaEmision,
							'tipoMoneda' 				=> $dataDTEGeneral->tipoMoneda
						),
					    //'documentoRelacionado' 		=> $documentoRelacionadoMH,
					    'emisor' 					=> array(
							'nit' 						=> str_replace("-", "", $dataDTEGeneral->nitEmisor),
							'nrc' 						=> str_replace("-", "", $dataDTEGeneral->nrcEmisor),
							'nombre' 					=> $dataDTEGeneral->nombreEmisor,
							'codActividad' 				=> $dataDTEGeneral->codigoActividadEmisorMH,
							'descActividad' 			=> $dataDTEGeneral->actividadEconomicaEmisor,
							'nombreComercial' 			=> $dataDTEGeneral->nombreComercialEmisor,
							'tipoEstablecimiento' 		=> $dataDTEGeneral->codEstablecimientoMH,
							'direccion' 			=> array (
								'departamento' 			=> $dataDTEGeneral->codigoDepartamentoSucursalMH,
								'municipio' 			=> $dataDTEGeneral->codigoMunicipioSucursalMH,
								'complemento' 			=> $dataDTEGeneral->direccionSucursal
							),
							'telefono' 					=> $telefonoSucursal,
							'correo' 					=> $correoSucursal,
							'codigoMH' 					=> $codEstableMH,
							'codigo' 					=> $codEstable,
							'puntoVentaMH' 				=> $codPuntoVentaMH,
							'puntoVenta' 				=> $codPuntoVenta
					    ),
					    'receptor' 					=> array(
							'tipoDocumento' 			=> $tipoDocumentoReceptor,
							'numDocumento' 				=> $numDocumentoProveedor,
							'nrc' 						=> $nrcProveedor,
							'nombre' 					=> substr($dataDTEGeneral->nombreProveedor, 0, 249),
							'codActividad' 				=> $codActividadEconomicaReceptor,
							'descActividad' 			=> $descripcionActividadEconomicaReceptor,
							'nombreComercial' 			=> substr(($dataDTEGeneral->nombreComercialProveedor == "" ? $dataDTEGeneral->nombreProveedor : $dataDTEGeneral->nombreComercialProveedor), 0, 149),
							'direccion' 			=> array(
								'departamento'			=> $dataDTEGeneral->codigoDepartamentoClienteMH,
								'municipio'				=> $dataDTEGeneral->codigoMunicipioClienteMH,
								'complemento'			=> ($dataDTEGeneral->direccionProveedorUbicacion == "" ? "------" : substr($dataDTEGeneral->direccionProveedorUbicacion, 0, 199))
							),
							'telefono' 					=> $telefonoCliente,
							'correo' 					=> $correoCliente
					    ),
					    // No aplica para comprobantes de retencion
					    //'otrosDocumentos' 			=> $otrosDocumentos,
					    //'ventaTercero' 				=> $ventaTercero,
					    'cuerpoDocumento' 			=> $cuerpoDocumento,
					    'resumen' 					=> $resumenDocumento,
					    'extension' 				=> $extensionDTE,
					    'apendice' 					=> $apendiceDTE
					);
					// var_dump(json_encode($cuerpoDocumento));
					// var_dump(json_encode($resumenDocumento));
					$datosJSON = json_encode(array(
						"contentType" 			=> "application/JSON",
						"nit" 					=> "06140101840022",
						"activo" 				=> "True",
						"passwordPri" 			=> '1ndup4l25@750STIHL$Karcher',
						"dteJson" 				=> $jsonDTE
					));

					// Realizar la solicitud POST y obtener la respuesta
					$respuesta = enviarSolicitudPOST($url, $datosJSON);
					$dteFirmado = json_decode($respuesta, true);

					// transmision a hacienda
					$urlHacienda = 'https://api.dtes.mh.gob.sv/fesv/recepciondte';
					// $urlHacienda = 'https://apitest.dtes.mh.gob.sv/fesv/recepciondte';

					$jsonFacturaFirmada = json_encode(array(
						"ambiente" 					=> $dataDTEGeneral->identificacionAmbiente,
						"idEnvio" 					=> $dataDTEGeneral->facturaId,
						"version" 					=> $dataDTEGeneral->versionDTEMH,
						"tipoDte" 					=> $dataDTEGeneral->codigoDTEMH,
						"documento" 				=> $dteFirmado['body'],
						"codigoGeneracion" 			=> $uuidDTE
					));

					$DTE = enviarDTEHacienda($urlHacienda, $jsonFacturaFirmada, $token);
					$dteJson = json_decode($DTE);

					// var_dump($jsonFacturaFirmada);
					// echo json_encode($jsonDTE);
					//
					if (isset($dteJson->selloRecibido) || !is_null($dteJson->selloRecibido)){
						$selloR = $dteJson->selloRecibido;
						$estado = "Certificado";
					} else {
						$selloR = NULL;
						$estado = "Rechazado";
					}
					$insert = [
						'facturaId'				=> $dataDTEGeneral->facturaId,
						'numeroControl'     	=> $numeroControl,
						'codigoGeneracion'     	=> $uuidDTE,
						'tipoOperacionMHId'     => $tipoOperacionMH,
						'tipoContingenciaId'    => $tipoContingenciaMH,
						'motivoContingencia'    => $motivoContingenciaMH,
						'selloRecibido'     	=> $selloR,
						'DTEfirmado'     		=> $dteFirmado['body'],
						'descripcionMsg' 		=> $dteJson->descripcionMsg,
						'estadoCertificacion'   => $estado
					];
					$DTEcertificado = $cloud->insert('fel_factura_certificacion', $insert);
					
					// errores
					foreach ($dteJson->observaciones as $observacion){
						$insert = [
							'facturaCertificacionId'	=> $DTEcertificado,
							'estadoCert'     			=> $dteJson->estado,
							'codigoMsg'     			=> $dteJson->codigoMsg,
							'descripcionMsg'     		=> $dteJson->descripcionMsg,
							'obsError'    				=> $observacion
						];
						$cloud->insert('fel_factura_certificacion_errores', $insert);
					}

					// Cambiar respuesta por el mensaje de error que se quiera mostrar
	                $jsonRespuesta = array(
	                    "respuesta"             => "success",
	                    "facturaId"             => $_POST['facturaId'],
	                    "estado" 				=> $dteJson->estado,
	                    "selloRecibido" 		=> $dteJson->selloRecibido,
						"codigoGeneracion"		=> $uuidDTE,
						"correoCliente" 		=> $correoCliente,
						"proveedorUbicacionId"  => $dataDTEGeneral->proveedorUbicacionId,
						"tipoDTEId" 			=> $_POST['tipoDTEId'],
						"fechaEmision" 			=> $dataDTEGeneral->fechaEmision,
						"descripcionMsg" 		=> $dteJson->descripcionMsg,
						"observaciones" 		=> $dteJson->observaciones,
						"jsonDepurar" 			=> $jsonDTE,
						"jsonDTE"				=> $dteJson
	                );
	                echo json_encode($jsonRespuesta);
				} else {
					// Ya se certificó y se validó con el API para actualizar el estado
					echo json_encode($jsonRespuesta);
				}

							//Aquí tienen que ir el update de los totales en la tabla fel_factura

								if ($tributosResumen === null) {
									$totalTributos = 0;
								} else {
									$totalTributos = $totalIVAResumen;
								}

								$update = [
									'totalDescuento'   => $totalDescuentoResumen,
									'totalIVA'         => (float)round($totalIVAResumen, 2),
									'totalNoSujeta'    => 0,
									'totalExenta'      => (float)round($totalExentaResumen, 2),
									'totalTributos'    => (float)round($totalTributos, 2),
									'subTotal'         => (float)round($subTotalVentasResumen, 2),
									'totalFactura'     => (float)round($subTotalVentasResumen, 2)
								];

								$where = ['facturaId' => $_POST['facturaId']];

								$cloud->update('fel_factura', $update, $where);
								$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Se actualizaron los totales del DTE");				
			break;

			case "invalidar-dte":
				$yearBD = $_POST['yearBD'];
				// URL de destino
				$url = 'http://216.246.113.187:8113/firmardocumento/';
				$jsonDTE = "";
				$dteFirmado = "";
				//encabezado DTE
				$dataDTEGeneral = $cloud->row("
				SELECT
					f.facturaId AS facturaId,
					cat002.versionMH AS versionDTEMH,
					f.identificacionAmbiente AS identificacionAmbiente,
					cat002.codigoMH AS codigoDTEMH,
					cat003.codigoMH AS codigoModeloMH,
					fc.codigoGeneracion as codigoGeneracion,
					fc.selloRecibido as selloRecibido,
					fc.numeroControl as numeroControl,
					f.fechaEmision AS fechaEmision,
					f.horaEmision AS horaEmision,
					f.tipoMoneda AS tipoMoneda,
					ffe.nitEmisor AS nitEmisor,
					ffe.nrcEmisor AS nrcEmisor,
					ffe.nombreEmisor AS nombreEmisor,
					ffe.nombreComercialEmisor AS nombreComercialEmisor,
					cat019emisor.codigoMh AS codigoActividadEmisorMH,
					cat019emisor.actividadEconomica AS actividadEconomicaEmisor,
					ffe.sucursalId AS sucursalId,
					s.codEstablecimientoMH AS codEstablecimientoMH,
					cat009.tipoEstablecimiento as tipoEstablecimiento,
					s.direccionSucursal AS direccionSucursal,
					cpds.codigoMH AS codigoDepartamentoSucursalMH,
					cpms.codigoMH AS codigoMunicipioSucursalMH,
					cli.clienteId AS clienteId,
					cliubi.clienteUbicacionId AS clienteUbicacionId,
					cat022cli.codigoMH AS codigoDocumentoClienteMH,
					cli.numDocumento AS numDocumentoCliente,
					cli.nrcCliente AS nrcCliente,
					CASE
						WHEN cli.nombreCliente = '' OR cli.nombreCliente = NULL THEN cli.nombreComercialCliente
						ELSE cli.nombreCliente
					END AS nombreCliente,
					cli.nombreComercialCliente AS nombreComercialCliente,
					cat019cliente.codigoMh AS codigoActividadClienteMH,
					cat019cliente.actividadEconomica AS actividadEconomicaCliente,
					cpdscliente.codigoMH AS codigoDepartamentoClienteMH,
					cpmscliente.codigoMH AS codigoMunicipioClienteMH,
					cliubi.direccionClienteUbicacion AS direccionClienteUbicacion,
					ffr.ivaRetenido AS ivaRetenido,
					ffr.ivaPercibido AS ivaPercibido,
					ffr.rentaRetenido AS rentaRetenido,
					cat016.codigoMH AS codigoCondicionOperacion,
					f.periodoPlazo AS periodoPlazo,
					f.tipoDTEId AS tipoDTEId,
		    		ffe.tipoEstablecimientoMH AS tipoEstablecimientoMH,
		    		ffe.puntoVentaMH AS puntoVentaMH,
		            cpubi.proveedorUbicacionId AS proveedorUbicacionId,
		            cpubi.nombreProveedorUbicacion AS nombreProveedorUbicacion
				FROM fel_factura f
				JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = f.tipoDTEId
				JOIN mh_003_tipo_modelo cat003 ON cat003.tipoModeloMHId = f.tipoModeloMHId
				JOIN fel_factura_emisor ffe ON ffe.facturaId = f.facturaId
				JOIN fel_factura_certificacion fc ON fc.facturaId = f.facturaId
				JOIN mh_019_actividad_economica cat019emisor ON cat019emisor.actividadEconomicaId = ffe.actividadEconomicaId
				JOIN cat_sucursales s ON s.sucursalId = ffe.sucursalId
				JOIN cat_paises_municipios cpms ON cpms.paisMunicipioId = s.paisMunicipioId
				JOIN cat_paises_departamentos cpds ON cpds.paisDepartamentoId = cpms.paisDepartamentoId
				LEFT JOIN fel_clientes_ubicaciones cliubi ON cliubi.clienteUbicacionId = f.clienteUbicacionId
				LEFT JOIN comp_proveedores_ubicaciones cpubi ON cpubi.proveedorUbicacionId = f.proveedorUbicacionId
				LEFT JOIN fel_clientes cli ON cli.clienteId = cliubi.clienteId
				LEFT JOIN comp_proveedores cp ON cp.proveedorId = cpubi.proveedorId
				LEFT JOIN mh_022_tipo_documento cat022cli ON cat022cli.tipoDocumentoClienteId = cli.tipoDocumentoMHId
				LEFT JOIN mh_019_actividad_economica cat019cliente ON cat019cliente.actividadEconomicaId = cli.actividadEconomicaId
				JOIN fel_factura_retenciones ffr ON ffr.facturaId = f.facturaId
				LEFT JOIN cat_paises_municipios cpmscliente ON cpmscliente.paisMunicipioId = cliubi.paisMunicipioId
				LEFT JOIN cat_paises_departamentos cpdscliente ON cpdscliente.paisDepartamentoId = cpmscliente.paisDepartamentoId
				JOIN mh_016_condicion_factura cat016 ON cat016.condicionFacturaId = f.condicionFacturaId
				JOIN mh_009_tipo_establecimiento cat009 ON cat009.codigoMH = s.codEstablecimientoMH
				WHERE f.facturaId = ? AND f.flgDelete = ? AND fc.estadoCertificacion = ?
				", [$_POST['facturaId'], 0, 'Certificado']);
				

				//validacion de declaracion de periodo
				$fecha = $dataDTEGeneral->fechaEmision;
				$partes = explode("-", $fecha);
				$anio = $partes[0];
				$mes = $partes[1];

				$checkDeclaracion = $cloud->count("SELECT cierreDeclaracionId FROM fel_cierre_declaracion WHERE mesNumero = ? AND anio = ? AND flgDelete = 0",
				[$mes, $anio]);

				if ($checkDeclaracion > 0){
					$jsonRespuesta = array(
						"respuesta"             => "Este DTE pertenece a un periodo ya declarado, no puede ser anulado.",
						"facturaId"             => $_POST['facturaId'],
					);
					echo json_encode($jsonRespuesta);
					// echo "Este DTE es de un periodo ya declarado, no puede ser anulado.";
				} else {
				//detalle DTE
				$dataDTEDetalle = $cloud->rows("
					SELECT
						fd.facturaDetalleId AS facturaDetalleId, 
						fd.productoId AS productoId, 
						fd.codProductoFactura AS codProductoFactura, 
						fd.nombreProductoFactura AS nombreProductoFactura, 
						fd.tipoItemMHId AS tipoItemMHId, 
						cat011.codigoMH AS codigoTipoItemMH,
						fd.costoPromedio AS costoPromedio, 
						fd.precioUnitario AS precioUnitario, 
						fd.precioUnitarioIVA AS precioUnitarioIVA, 
						fd.precioVenta AS precioVenta, 
						fd.precioVentaIVA AS precioVentaIVA, 
						fd.cantidadProducto AS cantidadProducto, 
						fd.ivaUnitario AS ivaUnitario, 
						fd.ivaTotal AS ivaTotal, 
						fd.porcentajeDescuento AS porcentajeDescuento, 
						fd.descuentoUnitario AS descuentoUnitario, 
						fd.descuentoTotal AS descuentoTotal, 
						fd.subTotalDetalle AS subTotalDetalle, 
						fd.subTotalDetalleIVA AS subTotalDetalleIVA, 
						fd.totalDetalle AS totalDetalle, 
						fd.totalDetalleIVA AS totalDetalleIVA, 
						fd.facturaRelacionadaId AS facturaRelacionadaId,
						prod.unidadMedidaId AS unidadMedidaId,
						udm.codigoMH AS codigoUDMMH
					FROM fel_factura_detalle fd
					JOIN mh_011_tipo_item cat011 ON cat011.tipoItemMHId = fd.tipoItemMHId
					JOIN inv_productos prod ON prod.productoId = fd.productoId
					LEFT JOIN cat_unidades_medida udm ON udm.unidadMedidaId = prod.unidadMedidaId
					WHERE fd.facturaId = ? AND fd.flgDelete = ?
				", [$dataDTEGeneral->facturaId, 0]);

				//
				$uuidDTE = strtoupper(get_guid());

				// Variables para emisor
					$codEstableMH = $dataDTEGeneral->tipoEstablecimientoMH;
					$codEstable = $dataDTEGeneral->tipoEstablecimientoMH;
					$codPuntoVentaMH = $dataDTEGeneral->puntoVentaMH;
					$codPuntoVenta = $dataDTEGeneral->puntoVentaMH;

				// Variables para identificacion
					// mh_004_tipo_operacion donde 1 = Transmisión normal
					// Para la contingencia, en su respectivo case se cambiará este valor
					$tipoOperacionMH = 1;
					$tipoContingenciaMH = NULL;
					$motivoContingenciaMH = NULL;
				// fh anulacion
				$fecha = date('Y-m-d');
				$hora = date("H:i:s");

				//datos persona responsable de invalidar
				$getDocumentoR = $cloud->row('SELECT docIdentidad, numIdentidad FROM th_personas WHERE personaId = ?', [$_SESSION["personaId"]]);

				if ($getDocumentoR->docIdentidad == 'DUI'){
					$tipoDocumentoR = '13';
				} else {
					$tipoDocumentoR = '02';
				}

				// datos de persona que solicita invalidacion
				if ($_POST['tipoDocumento'] == 'DUI'){
					$tipoDocumentoA = '13';
				} else {
					$tipoDocumentoA = '02';
				}
				$getNombreSolicita = $cloud->row('SELECT prsTipoId, nombre1, apellido1, fechaNacimiento, estadoPersona
				FROM th_personas
				WHERE personaId = ?', [$_POST['personaAnulacion']]);
				$personaSolicita = $getNombreSolicita->nombre1 . " " . $getNombreSolicita->apellido1;

				// Contactos de la sucursal
				$dataTelefonoSucursal = $cloud->row("
					SELECT contactoSucursal FROM cat_sucursales_contacto
					WHERE sucursalId = ? AND descripcionCSucursal = ? AND flgDelete = ?
					LIMIT 1
				", [$dataDTEGeneral->sucursalId, "Teléfono de sucursal", 0]);

				if($dataTelefonoSucursal) {
					$telefonoSucursal = $dataTelefonoSucursal->contactoSucursal;
				} else {
					// Default Casa Matriz
					$telefonoSucursal = "22312800";
				}

				$dataCorreoSucursal = $cloud->row("
					SELECT contactoSucursal FROM cat_sucursales_contacto
					WHERE sucursalId = ? AND descripcionCSucursal = ? AND flgDelete = ?
					LIMIT 1
				", [$dataDTEGeneral->sucursalId, "Correo de sucursal", 0]);

				if($dataCorreoSucursal) {
					$correoSucursal = $dataCorreoSucursal->contactoSucursal;
				} else {
					// Default Casa Matriz
					$correoSucursal = "ventas@indupal.com";
				}

				
					//total IVA
					$totalIVAResumen = 0;
					foreach ($dataDTEDetalle as $dteDetalle) {
						$totalIVAResumen += $dteDetalle->ivaTotal;
					} 
				
				$facturaIdR = ($_POST['listaDTE'] == "" ? $_POST['facturaId'] : $_POST['listaDTE']);

				// Contactos del cliente
				if ($dataDTEGeneral->proveedorUbicacionId > 0) {
					// anulacion DTE
					$FELr = $cloud->row('
					SELECT c.codigoGeneracion, f.proveedorUbicacionId, fc.tipoDocumento as tipoDocumentoCliente,
					fc.numDocumento as numDocumento, fcu.proveedorId
					FROM fel_factura_certificacion c 
	                JOIN fel_factura f ON f.facturaId = c.facturaId 
					LEFT JOIN comp_proveedores_ubicaciones fcu ON fcu.proveedorUbicacionId = f.proveedorUbicacionId
					LEFT JOIN comp_proveedores fc ON fc.proveedorId = fcu.proveedorId
					WHERE c.facturaId =  ? AND c.estadoCertificacion = ?
					ORDER BY c.facturaCertificacionId DESC
					LIMIT 1
					',[$facturaIdR, 'Certificado']);

					// Contactos del cliente
						// cat_tipos_contacto que tienen que ver con teléfonos
						$dataTelefonoCliente = $cloud->row("
							SELECT contactoProveedor FROM comp_proveedores_contactos
							WHERE proveedorUbicacionId = ? AND tipoContactoId IN (2, 3, 4, 6, 7, 8, 10, 11, 12) AND flgDelete = ?
							ORDER BY proveedorContactoId DESC
							LIMIT 1
						", [$dataDTEGeneral->proveedorUbicacionId, 0]);

						if($dataTelefonoCliente) {
							if($dataTelefonoCliente->contactoProveedor == "0") {
								$telefonoCliente = NULL;
							} else {
								if (strlen($dataTelefonoCliente->contactoProveedor) >= 8){
									$telefonoCliente = str_replace("-", "", $dataTelefonoCliente->contactoProveedor);
								} else {
									$telefonoCliente = NULL;
								}
							}
						} else {
							// Default Casa Matriz
							$telefonoCliente = NULL;
						}

						// cat_tipos_contacto que tienen que ver con correo
						$dataCorreoCliente = $cloud->row("
							SELECT contactoProveedor FROM comp_proveedores_contactos
							WHERE proveedorUbicacionId = ? AND tipoContactoId IN (1, 9, 13) AND flgContactoPrincipal = ? AND flgDelete = ?
							ORDER BY proveedorContactoId DESC
							LIMIT 1
						", [$dataDTEGeneral->proveedorUbicacionId, 1, 0]);

						if($dataCorreoCliente) {
							$correoCliente = $dataCorreoCliente->contactoProveedor;
						} else {
							// Default Casa Matriz
							$correoCliente = "ventas@indupal.com";
						}

					if($FELr->tipoDocumentoCliente == "DUI") {
					//if($dataDTEGeneral->codigoDocumentoClienteMH == "13") {
						$tipoDocumentoCliente = "13";
						$numDocumentoCliente = $FELr->numDocumento;
					} else {
						if($FELr->tipoDocumentoCliente == "NIT") {
							$tipoDocumentoCliente = "36";
						} else {
							$tipoDocumentoCliente = "37";
						}
						// Quitar guión para NIT
						$numDocumentoCliente = str_replace("-", "", $FELr->numDocumento);
						if($numDocumentoCliente == "") {
							$numDocumentoCliente = "-N/A-";
						} else {
							$numDocumentoCliente = $numDocumentoCliente;
						}
					}
				} else {
					// anulacion DTE
					$FELr = $cloud->row('
					SELECT c.codigoGeneracion, f.clienteUbicacionId, td.codigoMH as tipoDocumentoCliente,
					fc.numDocumento as numDocumento, fcu.clienteId
					FROM fel_factura_certificacion c 
	                JOIN fel_factura f ON f.facturaId = c.facturaId 
					LEFT JOIN fel_clientes_ubicaciones fcu ON fcu.clienteUbicacionId = f.clienteUbicacionId
					LEFT JOIN fel_clientes fc ON fc.clienteId = fcu.clienteId
					LEFT JOIN mh_022_tipo_documento td ON td.tipoDocumentoClienteId = fc.tipoDocumentoMHId
					WHERE c.facturaId =  ? AND c.estadoCertificacion = ?
					ORDER BY c.facturaCertificacionId DESC
					LIMIT 1
					',[$facturaIdR, 'Certificado']);

					// cat_tipos_contacto que tienen que ver con teléfonos
					$dataTelefonoCliente = $cloud->row("
						SELECT contactoCliente FROM fel_clientes_contactos
						WHERE clienteUbicacionId = ? AND tipoContactoId IN (2, 3, 4, 6, 7, 8, 10, 11, 12) AND flgDelete = ?
						ORDER BY clienteContactoId DESC
						LIMIT 1
					", [$FELr->clienteUbicacionId, 0]);

					if($dataTelefonoCliente) {
						if($dataTelefonoCliente->contactoCliente == "0") {
							$telefonoCliente = NULL;
						} else {
							if (strlen($dataTelefonoCliente->contactoCliente) >= 8){
								$telefonoCliente = str_replace("-", "", $dataTelefonoCliente->contactoCliente);
							} else {
								$telefonoCliente = NULL;
							}
						}
					} else {
						// Default Casa Matriz
						$telefonoCliente = NULL;
					}
					// cat_tipos_contacto que tienen que ver con correo
					$dataCorreoCliente = $cloud->row("
						SELECT contactoCliente FROM fel_clientes_contactos
						WHERE clienteUbicacionId = ? AND tipoContactoId IN (1, 9, 13) AND flgContactoPrincipal = ? AND flgDelete = ?
						ORDER BY clienteContactoId DESC
						LIMIT 1
					", [$FELr->clienteUbicacionId, 1, 0]);

					if($dataCorreoCliente) {
						$correoCliente = $dataCorreoCliente->contactoCliente;
					} else {
						// Default Casa Matriz
						$correoCliente = NULL;
					}

					if($FELr->tipoDocumentoCliente == "13") {
					//if($dataDTEGeneral->codigoDocumentoClienteMH == "13") {
						$numDocumentoCliente = $dataDTEGeneral->numDocumentoCliente;
					} else {
						// Quitar guión para NIT
						$numDocumentoCliente = str_replace("-", "", $dataDTEGeneral->numDocumentoCliente);
						if($numDocumentoCliente == "") {
							$numDocumentoCliente = "-N/A-";
						} else {
							$numDocumentoCliente = $numDocumentoCliente;
						}
					}
					$tipoDocumentoCliente = $FELr->tipoDocumentoCliente;
				}

				if ($_POST['tipoDoc'] == 4 || $_POST['tipoDoc'] == 7 || $_POST['tipoAnulacion'] == 2){
					$codGenR = NULL;
				} else {
					if($dataDTEGeneral->tipoDTEId == 4 || $dataDTEGeneral->tipoDTEId == 7 || $_POST['tipoAnulacion'] == 2) {
						$codGenR == NULL;
					} else {
						$codGenR = $FELr->codigoGeneracion;
					}
				}

				$jsonDTE = array (
					'identificacion' 			=> array (
						'version' 					=> 2,
						'ambiente' 					=> $dataDTEGeneral->identificacionAmbiente,
						'codigoGeneracion' 			=> $uuidDTE,
						'fecAnula' 					=> $fecha,
						'horAnula' 					=> $hora,
					),
					'emisor' 					=> array(
						'nit' 						=> str_replace("-", "", $dataDTEGeneral->nitEmisor),
						'nombre' 					=> $dataDTEGeneral->nombreEmisor,
						'tipoEstablecimiento' 		=> $dataDTEGeneral->codEstablecimientoMH,
						'nomEstablecimiento' 		=> $dataDTEGeneral->tipoEstablecimiento,
						'codEstableMH' 				=> $codEstableMH,
						'codEstable' 				=> $codEstable,
						'codPuntoVentaMH' 			=> $codPuntoVentaMH,
						'codPuntoVenta' 			=> $codPuntoVenta,
						'telefono' 					=> $telefonoSucursal,
						'correo' 					=> $correoSucursal,
					),
					'documento' 				=> array(
						'tipoDte' 					=> $dataDTEGeneral->codigoDTEMH,
						'codigoGeneracion' 			=> $_POST['codigoGeneracion'],
						'selloRecibido' 			=> $dataDTEGeneral->selloRecibido,
						'numeroControl' 			=> $dataDTEGeneral->numeroControl,
						'fecEmi' 					=> $dataDTEGeneral->fechaEmision,
						'montoIva' 					=> (float)round($totalIVAResumen, 2),
						'codigoGeneracionR' 		=> $codGenR,
						'tipoDocumento' 			=> $tipoDocumentoCliente,
						'numDocumento' 				=> $numDocumentoCliente,
						'nombre' 					=> $dataDTEGeneral->nombreCliente,
						'telefono' 					=> $telefonoCliente,
						'correo' 					=> $correoCliente,
					),
					'motivo' 					=> array(
						'tipoAnulacion' 			=> (int)$_POST['tipoAnulacion'],
						'motivoAnulacion' 			=> $_POST['motivoAnulacion'],
						'nombreResponsable' 		=> $_SESSION["nombrePersona"],
						'tipDocResponsable' 		=> $tipoDocumentoR,
						'numDocResponsable' 		=> $getDocumentoR->numIdentidad,
						'nombreSolicita' 			=> $personaSolicita,
						'tipDocSolicita' 			=> $tipoDocumentoA,
						'numDocSolicita' 			=> $_POST['numDocumento'],
						),
					);
					
					$datosJSON = json_encode(array(
						"contentType" 			=> "application/JSON",
						"nit" 					=> "06140101840022",
						"activo" 				=> "True",
						"passwordPri" 			=> '1ndup4l25@750STIHL$Karcher',
						"dteJson" 				=> $jsonDTE
					));
	
					// Realizar la solicitud POST y obtener la respuesta
					$respuesta = enviarSolicitudPOST($url, $datosJSON);
					$dteFirmado = json_decode($respuesta, true);
	
					// transmision a hacienda
					$urlHacienda = 'https://api.dtes.mh.gob.sv/fesv/anulardte';
					// $urlHacienda = 'https://apitest.dtes.mh.gob.sv/fesv/anulardte';
	
					$jsonFacturaFirmada = json_encode(array(
						"ambiente" 					=> $dataDTEGeneral->identificacionAmbiente,
						"idEnvio" 					=> $dataDTEGeneral->facturaId,
						"version" 					=> 2,
						"documento" 				=> $dteFirmado['body'],
					));
					
					$dataTokenParametrizacion = $cloud->row("
					SELECT parametro FROM conf_parametrizacion
					WHERE tipoParametrizacion = ? AND flgDelete = ? 
					LIMIT 1
					", ["Token de certificación", 0]);

					$token = $dataTokenParametrizacion->parametro;

					$DTE = enviarDTEHacienda($urlHacienda, $jsonFacturaFirmada, $token);
					$dteJson = json_decode($DTE);

					$insert = [
						'facturaId'					=> $_POST['facturaId'],
						'codigoGeneracion'     		=> $uuidDTE,
						'fechaAnulacion'     		=> $fecha,
						'horaAnulacion'     		=> $hora,
						'tipoAnulacionId'     		=> $_POST['tipoAnulacion'],
						'motivoAnulacion'     		=> $_POST['motivoAnulacion'],
						'nombreResponsable'     	=> $_SESSION["nombrePersona"],
						'tipDocResponsable'     	=> $tipoDocumentoR,
						'numDocResponsable'     	=> $getDocumentoR->numIdentidad,
						'personaSolicita'     		=> $personaSolicita,
						'tipoDocumentoMHIdSolicita'	=> $tipoDocumentoA,
						'numDocumentoSolicita'     	=> $_POST['numDocumento'],
					];
					$cloud->insert('fel_factura_anulacion', $insert);

					if (isset($dteJson->selloRecibido) || !is_null($dteJson->selloRecibido)){
						$selloR = $dteJson->selloRecibido;
						$estado = "Invalidado";

						// cambio de estado si se anula correctamente
						$update = [
							'estadoFactura'		=> "Anulado",
						];
						$where = ['facturaId' => $_POST['facturaId']]; 
						
						$cloud->update('fel_factura', $update, $where);
					} else {
						$selloR = NULL;
						$estado = "Rechazado";
					}
					$insert = [
						'facturaId'				=> $_POST['facturaId'],
						'numeroControl'     	=> $dataDTEGeneral->numeroControl,
						'codigoGeneracion'     	=> $dataDTEGeneral->codigoGeneracion,
						'tipoOperacionMHId'     => $tipoOperacionMH,
						'tipoContingenciaId'    => $tipoContingenciaMH,
						'motivoContingencia'    => $motivoContingenciaMH,
						'selloRecibido'     	=> $selloR,
						'DTEfirmado'     		=> $dteFirmado['body'],
						'descripcionMsg' 		=> $dteJson->descripcionMsg,
						'estadoCertificacion'   => $estado
					];
					$DTEcertificado = $cloud->insert('fel_factura_certificacion', $insert);
					
					// errores
					foreach ($dteJson->observaciones as $observacion){
						$insert = [
							'facturaCertificacionId'	=> $DTEcertificado,
							'estadoCert'     			=> $dteJson->estado,
							'codigoMsg'     			=> $dteJson->codigoMsg,
							'descripcionMsg'     		=> $dteJson->descripcionMsg,
							'obsError'    				=> $observacion
						];
						$cloud->insert('fel_factura_certificacion_errores', $insert);
					}

					// var_dump($dteJson);

					$jsonRespuesta = array(
	                    "respuesta"             => "success",
	                    "facturaId"             => $_POST['facturaId'],
						"codigoGeneracion"		=> $dteJson->codigoGeneracion,
						"selloRecibido"			=> $dteJson->selloRecibido,
						"estado"				=> $dteJson->estado
	                );
	                echo json_encode($jsonRespuesta);
				}
			break;

			default:
				echo "No se encontró la operación.";
			break;
		}
    } else {
    	header("Location: /indupal-cloud/app/");
    }
?>