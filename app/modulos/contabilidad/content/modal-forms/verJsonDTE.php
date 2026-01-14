<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	include("../../../../../libraries/includes/logic/functions/funciones-generales.php");

	$yearBD = $_POST['yearBD'];

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
			cli.proveedorId AS proveedorId,
			cliubi.proveedorUbicacionId AS proveedorUbicacionId,
			cat022cli.tipoDocumentoCliente AS tipoDocumentoCliente,
			cli.numDocumento AS numDocumentoCliente,
			cli.nrcProveedor AS nrcProveedor,
			CASE
				WHEN cli.nombreProveedor = '' OR cli.nombreProveedor = NULL THEN cli.nombreComercial
				ELSE cli.nombreProveedor
			END AS nombreProveedor,
			cli.nombreComercial AS nombreComercial,
    		cat019cliente.codigoMh AS codigoActividadClienteMH,
    		cat019cliente.actividadEconomica AS actividadEconomicaCliente,
			cpdscliente.codigoMH AS codigoDepartamentoClienteMH,
			cpmscliente.codigoMH AS codigoMunicipioClienteMH,
			cliubi.direccionProveedorUbicacion AS direccionProveedorUbicacion,
    		ffr.ivaRetenido AS ivaRetenido,
    		ffr.ivaPercibido AS ivaPercibido,
    		ffr.rentaRetenido AS rentaRetenido,
    		cat016.codigoMH AS codigoCondicionOperacion,
    		f.periodoPlazo AS periodoPlazo,
    		f.sujetoExcluidoId AS sujetoExcluidoId,
    		cat029cliente.codigoMH AS codigoTipoPersonaMH,
			f.tipoDTEId AS tipoDTEId,
			f.tipoRemisionMHId AS tipoRemisionMHId
		FROM fel_factura$yearBD f
		JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = f.tipoDTEId
		JOIN mh_003_tipo_modelo cat003 ON cat003.tipoModeloMHId = f.tipoModeloMHId
		JOIN fel_factura_emisor$yearBD ffe ON ffe.facturaId = f.facturaId
		JOIN mh_019_actividad_economica cat019emisor ON cat019emisor.actividadEconomicaId = ffe.actividadEconomicaId
		JOIN cat_sucursales s ON s.sucursalId = ffe.sucursalId
		JOIN cat_paises_municipios cpms ON cpms.paisMunicipioId = s.paisMunicipioId
		JOIN cat_paises_departamentos cpds ON cpds.paisDepartamentoId = cpms.paisDepartamentoId
		LEFT JOIN comp_proveedores_ubicaciones cliubi ON cliubi.proveedorUbicacionId = f.proveedorUbicacionId
		LEFT JOIN comp_proveedores cli ON cli.proveedorId = cliubi.proveedorId
		LEFT JOIN mh_022_tipo_documento cat022cli ON cat022cli.tipoDocumentoClienteId  = (
			CASE
				WHEN cli.tipoDocumento = 'NIT' THEN 1
				WHEN cli.tipoDocumento = 'DUI' THEN 2
				ELSE 3
			END
		)
		LEFT JOIN mh_019_actividad_economica cat019cliente ON cat019cliente.actividadEconomicaId = cli.actividadEconomicaId
		JOIN fel_factura_retenciones$yearBD ffr ON ffr.facturaId = f.facturaId
		LEFT JOIN cat_paises_municipios cpmscliente ON cpmscliente.paisMunicipioId = cliubi.paisMunicipioId
		LEFT JOIN cat_paises_departamentos cpdscliente ON cpdscliente.paisDepartamentoId = cpmscliente.paisDepartamentoId
		JOIN mh_016_condicion_factura cat016 ON cat016.condicionFacturaId = f.condicionFacturaId
		LEFT JOIN mh_029_tipo_persona cat029cliente ON cat029cliente.tipoPersonaId = (
			CASE
				WHEN cli.tipoProveedor = 'Empresa local' THEN 2
				ELSE 1
			END
		)
		WHERE f.facturaId = ? AND f.flgDelete = ?
	", [$_POST['facturaId'], 0]);

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
		FROM fel_factura_detalle$yearBD fd
		JOIN mh_011_tipo_item cat011 ON cat011.tipoItemMHId = fd.tipoItemMHId
		JOIN inv_productos prod ON prod.productoId = fd.productoId
		LEFT JOIN cat_unidades_medida udm ON udm.unidadMedidaId = prod.unidadMedidaId
		WHERE fd.facturaId = ? AND fd.flgDelete = ?
	", [$_POST['facturaId'], 0]);

	$dataDTEPago = $cloud->row("
		SELECT
			ffp.montoPago AS totalFactura,
			cat017.codigoMH AS codigoFormaPagoMH
		FROM fel_factura_pago$yearBD ffp 
		JOIN mh_017_forma_pago cat017 ON cat017.formaPagoId = ffp.formaPagoId
		WHERE ffp.facturaId = ? AND ffp.flgDelete = ?
	", [$_POST['facturaId'], 0]);

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
			SELECT contactoCliente FROM fel_clientes_contactos
			WHERE clienteUbicacionId = ? AND tipoContactoId IN (2, 3, 4, 6, 7, 8, 10, 11, 12) AND flgDelete = ?
			ORDER BY clienteContactoId DESC
			LIMIT 1
		", [$dataDTEGeneral->proveedorUbicacionId, 0]);

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
		", [$dataDTEGeneral->proveedorUbicacionId, 1, 0]);

		if($dataCorreoCliente) {
			$correoCliente = $dataCorreoCliente->contactoCliente;
		} else {
			// Default Casa Matriz
			$correoCliente = NULL;
		}

		$getNumerosDTE = $cloud->row("SELECT numeroControl, codigoGeneracion,selloRecibido, DTEfirmado  FROM fel_factura_certificacion$yearBD WHERE facturaId = ? AND estadoCertificacion = ? AND flgDelete = ? ORDER BY facturaCertificacionId DESC LIMIT 1", [$_POST['facturaId'], "Certificado", 0]);

		if ($getNumerosDTE !== false) {
			// La consulta SQL devolvió un resultado válido, ahora puedes acceder a sus propiedades
			$numeroControl = $getNumerosDTE->numeroControl;
			$uuidDTE = $getNumerosDTE->codigoGeneracion;
			$selloRecibido = $getNumerosDTE->selloRecibido;
			$firmadoDTE = $getNumerosDTE->DTEfirmado;
		} else {
			// Manejar el caso donde la consulta SQL no devuelve resultados válidos
			
		}
		

	// Variables para emisor
		$codEstableMH = NULL;
		$codEstable = NULL;
		$codPuntoVentaMH = NULL;
		$codPuntoVenta = NULL;

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
?>
<a role="button" href="<?php echo $_SESSION['currentRoute']; ?>content/modal-forms/verJsonDTE" class="btn btn-primary" id="btnDescargarJSON"  download="<?php echo $uuidDTE; ?>.json" target="_blank">
	<i class="fas fa-file-download"></i> Descargar archivo
</a>
<?php
	switch($dataDTEGeneral->tipoDTEId) {
		case '1':
			// Factura
			
			// Formar el cuerpoDocumento
				$cuerpoDocumento = array();
				$numItem = 0;

				// Variables del cuerpo
					$numeroDocumentoCuerpo = NULL;
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

				// Variables Cloud
					$subTotalGeneral = 0;

				$codigoTipoItemMH = 1;

				foreach ($dataDTEDetalle as $dteDetalle) {
					if(stristr($dteDetalle->nombreProductoFactura, "servicio")) {
						$nombreProductoFactura = str_replace("servicio", "SERV.", $dteDetalle->nombreProductoFactura);
						$codTributoCuerpo = NULL;
						$codigoTipoItemMH = 3;
					} else {							
						if(stristr($dteDetalle->nombreProductoFactura, "servicio") || $dteDetalle->codigoUDMMH == "99" || $dteDetalle->codigoTipoItemMH == "4") {
							// Otra por ser un servicio, FEL daba error
							//$unidadMedidaCuerpo = "99";
							// mh_015_tributos
							// Otras tasas casos especiales
							// Cuando la unidadMedida es 99
							//$codTributoCuerpo = "D5";
							$unidadMedidaCuerpo = "59";
							$codigoTipoItemMH = 3;
							$codTributoCuerpo = NULL;
						} else {
							$codTributoCuerpo = NULL;
							if($dteDetalle->codigoUDMMH == "" || is_null($dteDetalle->codigoUDMMH) || $dteDetalle->codigoUDMMH == NULL) {
								// Default unidad
								$unidadMedidaCuerpo = "59";
							} else {
								$unidadMedidaCuerpo = $dteDetalle->codigoUDMMH;
							}
						}
						$nombreProductoFactura = $dteDetalle->nombreProductoFactura;
						$codigoTipoItemMH = $dteDetalle->codigoTipoItemMH;
					}

					// Verificar si lleva mantenimiento
					if(stristr($dteDetalle->nombreProductoFactura, "mantenimiento")) {
						$nombreProductoFactura = str_replace("mantenimiento", "MANTO.", $nombreProductoFactura);
						$unidadMedidaCuerpo = "59";
						$codTributoCuerpo = NULL;
					} else {							
						$nombreProductoFactura = $nombreProductoFactura;
						$codigoTipoItemMH = 3;
					}

					// Verificar si lleva reparacion
					if(stristr($dteDetalle->nombreProductoFactura, "reparacion")) {
						$nombreProductoFactura = str_replace("reparacion", "REPAR.", $nombreProductoFactura);
						$unidadMedidaCuerpo = "59";
						$codTributoCuerpo = NULL;
					} else {							
						$nombreProductoFactura = $nombreProductoFactura;
						$codigoTipoItemMH = 3;
					}

					if($dteDetalle->ivaUnitario == 0 || $dteDetalle->ivaTotal == 0) {
						// Venta exenta
						if($dteDetalle->precioVenta > $dteDetalle->precioUnitario) {
							// Se vendió a un precio más alto que el de lista
							$precioUnitarioCuerpo = $dteDetalle->precioVenta;
						} else {
							$precioUnitarioCuerpo = $dteDetalle->precioUnitario;
						}
						$ventaExentaCuerpo = $dteDetalle->totalDetalle;
						$ventaGravadaCuerpo = 0;
						//$subTotalVentasResumen += $dteDetalle->subTotalDetalle;
						$subTotalVentasResumen += $dteDetalle->totalDetalle;
					} else {
						// Venta gravada
						if($dteDetalle->precioVentaIVA > $dteDetalle->precioUnitarioIVA) {
							// Se vendió a un precio más alto que el de lista
							$precioUnitarioCuerpo = $dteDetalle->precioVentaIVA;
						} else {
							$precioUnitarioCuerpo = $dteDetalle->precioUnitarioIVA;
						}
						$ventaExentaCuerpo = 0;
						$ventaGravadaCuerpo = $dteDetalle->totalDetalleIVA;
						//$subTotalVentasResumen += $dteDetalle->subTotalDetalleIVA;
						$subTotalVentasResumen += $dteDetalle->totalDetalleIVA;
					}
					$totalDescuentoResumen += $dteDetalle->descuentoTotal;
					$totalIVAResumen += $dteDetalle->ivaTotal;
					$subTotalGeneral += $dteDetalle->subTotalDetalle;

					$cuerpoDocumento[$numItem] = array(
						"numItem" 					=> (int)($numItem + 1),
						"tipoItem" 					=> (int)$codigoTipoItemMH,
						"numeroDocumento" 			=> $numeroDocumentoCuerpo,
						"cantidad" 					=> (float)$dteDetalle->cantidadProducto,
						"codigo" 					=> $dteDetalle->codProductoFactura,
						"codTributo" 				=> $codTributoCuerpo,
						"uniMedida" 				=> (int)$unidadMedidaCuerpo,
						"descripcion" 				=> $dteDetalle->nombreProductoFactura,
						"precioUni" 				=> (float)$precioUnitarioCuerpo,
						"montoDescu" 				=> (float)$dteDetalle->descuentoTotal,
						"ventaNoSuj" 				=> (float)$ventaNoSujetaCuerpo,
						"ventaExenta" 				=> (float)$ventaExentaCuerpo,
						"ventaGravada" 				=> (float)$ventaGravadaCuerpo,
						"tributos" 					=> $tributosCuerpo,
						"psv" 						=> (float)$psvCuerpo,
						"noGravado" 				=> (float)$noGravadoCuerpo,
						"ivaItem" 					=> (float)$dteDetalle->ivaTotal
					);
					$numItem++;
				} // foreach dteDetalle
			// Fin formar cuerpoDocumento
			//var_dump($cuerpoDocumento);

			if($totalIVAResumen == 0) {
				$flgFacturaExenta = "Sí";
			} else {
				$flgFacturaExenta = "No";
			}

			// Formar resumenDocumento
				// Variables del cuerpo
					// No tenemos venta no sujeta
					$totalNoSujetoResumen = 0;
					$descuNoSujResumen = 0;
					$tributosResumen = NULL;
					$totalNoGravadoResumen = 0;
					$saldoFavorResumen = 0;
					$referenciaPagoResumen = NULL;
					// mh_018_plazo_pago 01 = Dias
					// De momento dejarlo como 01, porque Indupal solo da días de Crédito
					$plazoPagoResumen = '01';
					$numPagoElectronico = NULL;

					// mh_016_condicion_factura 1 = Contado, 2 = Crédito, 3 = Otro
					// Solicitud de Heidi Reyes para informar la operación si es contado o crédito
					$condicionOperacionResumen = $dataDTEGeneral->codigoCondicionOperacion;

					if($condicionOperacionResumen == 1) {
						$periodoPagoResumen = NULL;
					} else {
						// Validar si es a cero días revertir, actualmente se elaboran así si hay más de una forma de pago, para liquidarla con abonos
						if($dataDTEGeneral->periodoPlazo == 0) {
							$condicionOperacionResumen = (int)1;
							$periodoPagoResumen = NULL;
						} else {
							$periodoPagoResumen = (int)$dataDTEGeneral->periodoPlazo;
						}
					}

					if($flgFacturaExenta == "Sí") {
						// Factura exenta
						$totalExentaResumen = $subTotalVentasResumen;
						$totalGravadaResumen = 0;
						//$descuentoExentoResumen = $totalDescuentoResumen;
						$descuentoExentoResumen = 0;
						$descuentoGravadoResumen = 0;
					} else {
						// Factura gravada
						$totalExentaResumen = 0;
						$totalGravadaResumen = $subTotalVentasResumen;
						$descuentoExentoResumen = 0;
						//$descuentoGravadoResumen = $totalDescuentoResumen;
						$descuentoGravadoResumen = 0;
					}

					// Porcentaje de descueto global resumenDocumento por regla de 3
						//$porcentajeDescuentoGlobal = ($totalDescuentoResumen * 100) / $dataDTEPago->totalFactura;
						$porcentajeDescuentoGlobal = ($totalDescuentoResumen * 100) / $subTotalGeneral;

				// Si "subTotal" da error, restar totalDescuentoResumen porque probablemente asi lo interprete Hacienda
				$resumenDocumento = array(
					'totalNoSuj' 				=> (float)round($totalNoSujetoResumen, 2),
					'totalExenta' 				=> (float)round($totalExentaResumen, 2),
					'totalGravada' 				=> (float)round($totalGravadaResumen, 2),
					'subTotalVentas' 			=> (float)round($subTotalVentasResumen, 2),
					'descuNoSuj' 				=> (float)round($descuNoSujResumen, 2),
					'descuExenta' 				=> (float)round($descuentoExentoResumen, 2),
					'descuGravada' 				=> (float)round($descuentoGravadoResumen, 2),
					'porcentajeDescuento' 		=> (float)round($porcentajeDescuentoGlobal, 2),
					'totalDescu' 				=> (float)round($totalDescuentoResumen, 2),
					'tributos' 					=> $tributosResumen,
					'subTotal' 					=> (float)round($subTotalVentasResumen, 2),
					'ivaRete1' 					=> (float)round($dataDTEGeneral->ivaRetenido, 2),
					'reteRenta' 				=> (float)round($dataDTEGeneral->rentaRetenido, 2),
					'montoTotalOperacion' 		=> (float)round($subTotalVentasResumen, 2),
					'totalNoGravado' 			=> (float)round($totalNoGravadoResumen, 2),
					'totalPagar' 				=> (float)round($dataDTEPago->totalFactura, 2),
					'totalLetras' 				=> dineroLetras($dataDTEPago->totalFactura, "decimal") . " USD",
					'totalIva' 					=> (float)round($totalIVAResumen, 2),
					'saldoFavor' 				=> (float)round($saldoFavorResumen, 2),
					'condicionOperacion' 		=> (int)$condicionOperacionResumen,
					'pagos' 				=> array (
						0 						=> array (
							  'codigo' 				=> $dataDTEPago->codigoFormaPagoMH,
							  'montoPago' 			=> (float)round($dataDTEPago->totalFactura, 2),
							  'referencia' 			=> null, //$referenciaPagoResumen, <- ese null no lo quiere :v
							  'plazo' 				=> $plazoPagoResumen,
							  'periodo' 				=> $periodoPagoResumen
						),
					  ),
					  'numPagoElectronico' => $numPagoElectronico
				);
			// Fin formar resumenDocumento

			// Variables generales
				$extensionDTE = NULL;
				$apendiceDTE = NULL;

			//$nrcCliente = ($dataDTEGeneral->nrcCliente == "" ? NULL : str_replace("-", "", $dataDTEGeneral->nrcCliente));
			$nrcCliente = NULL;

			// Enviar DUI con guion
			if($dataDTEGeneral->codigoDocumentoClienteMH == "13") {
				$codigoDocumentoClienteMH = "13";
				$numDocumentoCliente = $dataDTEGeneral->numDocumentoCliente;
			} else {
				// Quitar guión para NIT
				$numDocumentoCliente = str_replace("-", "", $dataDTEGeneral->numDocumentoCliente);
				if($numDocumentoCliente == "") {
					$codigoDocumentoClienteMH = "37";
					$numDocumentoCliente = "-N/A-";
				} else {
					$codigoDocumentoClienteMH = $dataDTEGeneral->codigoDocumentoClienteMH;
					$numDocumentoCliente = $numDocumentoCliente;
				}
			}

			$jsonDTE = array (
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
				'documentoRelacionado' 		=> $documentoRelacionadoMH,
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
					'codEstableMH' 				=> $codEstableMH,
					'codEstable' 				=> $codEstable,
					'codPuntoVentaMH' 			=> $codPuntoVentaMH,
					'codPuntoVenta' 			=> $codPuntoVenta
				),
				'receptor' 					=> array(
					'tipoDocumento' 			=> $codigoDocumentoClienteMH,
					'numDocumento' 				=> $numDocumentoCliente,
					'nrc' 						=> $nrcCliente,
					'nombre' 					=> substr($dataDTEGeneral->nombreCliente, 0, 249),
					'codActividad' 				=> $dataDTEGeneral->codigoActividadClienteMH,
					'descActividad' 			=> $dataDTEGeneral->actividadEconomicaCliente,
					'direccion' 			=> array(
						'departamento'			=> $dataDTEGeneral->codigoDepartamentoClienteMH,
						'municipio'				=> $dataDTEGeneral->codigoMunicipioClienteMH,
						'complemento'			=> ($dataDTEGeneral->direccionClienteUbicacion == "" ? "------" : substr($dataDTEGeneral->direccionClienteUbicacion, 0, 199))
					),
					'telefono' 					=> $telefonoCliente,
					'correo' 					=> $correoCliente
				),
				'otrosDocumentos' 			=> $otrosDocumentos,
				'ventaTercero' 				=> $ventaTercero,
				'cuerpoDocumento' 			=> $cuerpoDocumento,
				'resumen' 					=> $resumenDocumento,
				'extension' 				=> $extensionDTE,
				'apendice' 					=> $apendiceDTE,
				"selloRecibido"				=> $getNumerosDTE->selloRecibido,
				"firmaElectronica"			=> $getNumerosDTE->DTEfirmado
				
			);
		break;
		
		case '2':
			// Comprobante de crédito fiscal

			// Formar el cuerpoDocumento
				$cuerpoDocumento = array();
				$numItem = 0;

				// Variables del cuerpo
					$numeroDocumentoCuerpo = NULL;
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

				// Variables Cloud
					$subTotalGeneral = 0;

				$codigoTipoItemMH = 1;
				foreach ($dataDTEDetalle as $dteDetalle) {
					// Créditos fiscales se declara tributos
					$tributosCuerpo = array("20");
					if(stristr($dteDetalle->nombreProductoFactura, "servicio")) {
						$nombreProductoFactura = str_replace("servicio", "SERV.", $dteDetalle->nombreProductoFactura);
						$codTributoCuerpo = NULL;
						$codigoTipoItemMH = 3;
					} else {							
						if(stristr($dteDetalle->nombreProductoFactura, "servicio") || $dteDetalle->codigoUDMMH == "99" || $dteDetalle->codigoTipoItemMH == "4") {
							// Otra por ser un servicio, FEL daba error
							//$unidadMedidaCuerpo = "99";
							// mh_015_tributos
							// Otras tasas casos especiales
							// Cuando la unidadMedida es 99
							//$codTributoCuerpo = "D5";
							$unidadMedidaCuerpo = "59";
							$codigoTipoItemMH = 3;
							$codTributoCuerpo = NULL;
						} else {
							$codTributoCuerpo = NULL;
							if($dteDetalle->codigoUDMMH == "" || is_null($dteDetalle->codigoUDMMH) || $dteDetalle->codigoUDMMH == NULL) {
								// Default unidad
								$unidadMedidaCuerpo = "59";
							} else {
								$unidadMedidaCuerpo = $dteDetalle->codigoUDMMH;
							}
						}
						$nombreProductoFactura = $dteDetalle->nombreProductoFactura;
						$codigoTipoItemMH = $dteDetalle->codigoTipoItemMH;
					}

					// Verificar si lleva mantenimiento
					if(stristr($dteDetalle->nombreProductoFactura, "mantenimiento")) {
						$nombreProductoFactura = str_replace("mantenimiento", "MANTO.", $nombreProductoFactura);
						$unidadMedidaCuerpo = "59";
						$codTributoCuerpo = NULL;
					} else {							
						$nombreProductoFactura = $nombreProductoFactura;
						$codigoTipoItemMH = 3;
					}

					// Verificar si lleva reparacion
					if(stristr($dteDetalle->nombreProductoFactura, "reparacion")) {
						$nombreProductoFactura = str_replace("reparacion", "REPAR.", $nombreProductoFactura);
						$unidadMedidaCuerpo = "59";
						$codTributoCuerpo = NULL;
					} else {							
						$nombreProductoFactura = $nombreProductoFactura;
						$codigoTipoItemMH = 3;
					}

					// No hay venta exenta en créditos fiscales
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

					$subTotalGeneral += $dteDetalle->subTotalDetalle;

					$cuerpoDocumento[$numItem] = array(
						"numItem" 					=> (int)($numItem + 1),
						"tipoItem" 					=> (int)$dteDetalle->codigoTipoItemMH,
						"numeroDocumento" 			=> $numeroDocumentoCuerpo,
						"cantidad" 					=> (float)$dteDetalle->cantidadProducto,
						"codigo" 					=> $dteDetalle->codProductoFactura,
						"codTributo" 				=> $codTributoCuerpo,
						"uniMedida" 				=> (int)$unidadMedidaCuerpo,
						"descripcion" 				=> $nombreProductoFactura,
						"precioUni" 				=> (float)$precioUnitarioCuerpo,
						"montoDescu" 				=> (float)$dteDetalle->descuentoTotal,
						"ventaNoSuj" 				=> (float)$ventaNoSujetaCuerpo,
						"ventaExenta" 				=> (float)$ventaExentaCuerpo,
						"ventaGravada" 				=> (float)$ventaGravadaCuerpo,
						"tributos" 					=> $tributosCuerpo,
						"psv" 						=> (float)$psvCuerpo,
						"noGravado" 				=> (float)$noGravadoCuerpo
						// Créditos fiscales no llevan IVA
						//"ivaItem" 					=> (float)$dteDetalle->ivaTotal
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
					// mh_018_plazo_pago 01 = Dias
					// De momento dejarlo como 01, porque Indupal solo da días de Crédito
					$plazoPagoResumen = '01';
					$numPagoElectronico = NULL;

					// mh_016_condicion_factura 1 = Contado, 2 = Crédito, 3 = Otro
					// Solicitud de Heidi Reyes para informar la operación si es contado o crédito
					$condicionOperacionResumen = $dataDTEGeneral->codigoCondicionOperacion;

					if($condicionOperacionResumen == 1) {
						$periodoPagoResumen = NULL;
					} else {
						// Validar si es a cero días revertir, actualmente se elaboran así si hay más de una forma de pago, para liquidarla con abonos
						if($dataDTEGeneral->periodoPlazo == 0) {
							$condicionOperacionResumen = (int)1;
							$periodoPagoResumen = NULL;
						} else {
							$periodoPagoResumen = (int)$dataDTEGeneral->periodoPlazo;
						}
					}
					// No hay venta exenta en créditos fiscales
					// FEL necesita precios sin IVA
					$totalExentaResumen = 0;
					$totalGravadaResumen = $subTotalVentasResumen;
					$descuentoExentoResumen = 0;
					//$descuentoGravadoResumen = $totalDescuentoResumen;
					$descuentoGravadoResumen = 0;

					// Porcentaje de descueto global resumenDocumento por regla de 3
						//$porcentajeDescuentoGlobal = ($totalDescuentoResumen * 100) / $dataDTEPago->totalFactura;
						$porcentajeDescuentoGlobal = ($totalDescuentoResumen * 100) / $subTotalGeneral;

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
					'totalNoSuj' 				=> (float)round($totalNoSujetoResumen, 2),
					'totalExenta' 				=> (float)round($totalExentaResumen, 2),
					'totalGravada' 				=> (float)round($totalGravadaResumen, 2),
					'subTotalVentas' 			=> (float)round($subTotalVentasResumen, 2),
					'descuNoSuj' 				=> (float)round($descuNoSujResumen, 2),
					'descuExenta' 				=> (float)round($descuentoExentoResumen, 2),
					'descuGravada' 				=> (float)round($descuentoGravadoResumen, 2),
					'porcentajeDescuento' 		=> (float)round($porcentajeDescuentoGlobal, 2),
					'totalDescu' 				=> (float)round($totalDescuentoResumen, 2),
					'tributos' 					=> $tributosResumen,
					'subTotal' 					=> (float)round($subTotalVentasResumen, 2),
					'ivaPerci1' 				=> (float)round($dataDTEGeneral->ivaPercibido, 2),
					'ivaRete1' 					=> (float)round($dataDTEGeneral->ivaRetenido, 2),
					'reteRenta' 				=> (float)round($dataDTEGeneral->rentaRetenido, 2),
					'montoTotalOperacion' 		=> (float)round($subTotalVentasResumen + $totalIVAResumen, 2),
					'totalNoGravado' 			=> (float)round($totalNoGravadoResumen, 2),
					'totalPagar' 				=> (float)round($totalPagar, 2),
					'totalLetras' 				=> dineroLetras($totalPagar, "decimal") . " USD",
					// No se detalla el campo IVA en Créditos fiscales
					//'totalIva' 					=> (float)round($totalIVAResumen, 2),
					'saldoFavor' 				=> (float)round($saldoFavorResumen, 2),
					'condicionOperacion' 		=> (int)$condicionOperacionResumen,
					'pagos' 				=> array (
						0 						=> array (
							  'codigo' 				=> $dataDTEPago->codigoFormaPagoMH,
							  'montoPago' 			=> (float)round($totalPagar, 2),
							  'referencia' 			=> null, //$referenciaPagoResumen, <- ese null no lo quiere :v
							  'plazo' 				=> $plazoPagoResumen,
							  'periodo' 				=> $periodoPagoResumen
						),
					  ),
					  'numPagoElectronico' => $numPagoElectronico
				);
			// Fin formar resumenDocumento

			// Variables generales
				$extensionDTE = NULL;
				$apendiceDTE = NULL;

			$nrcCliente = ($dataDTEGeneral->nrcCliente == "" ? NULL : str_replace("-", "", $dataDTEGeneral->nrcCliente));

			// Es obligación el NIT, buscar exactamente solo ese tipo de documento para evitar errores
			// mh_022_tipo_documento tipoDocumentoClienteId = 1
			// Acepta homologación DUI tipoDocumentoClienteId = 2
			$dataNITCliente = $cloud->row("
				SELECT
					numDocumento
				FROM fel_clientes
				WHERE clienteId = ? AND (tipoDocumentoMHId = ? OR tipoDocumentoMHId = ?) AND flgDelete = ?
			", [$dataDTEGeneral->clienteId, 1, 2, 0]);

			if($dataNITCliente) {
				$nitCliente = str_replace("-", "", $dataNITCliente->numDocumento);
			} else {
				// Intentar con el presentante legal
				$dataNITCliente = $cloud->row("
					SELECT
						numDocumentoRL
					FROM fel_clientes
					WHERE clienteId = ? AND (tipoDocumentoRL = ? OR tipoDocumentoRL = ?) AND flgDelete = ?
				", [$dataDTEGeneral->clienteId, 1, 2, 0]);
				if($dataNITCliente) {
					$nitCliente = str_replace("-", "", $dataNITCliente->numDocumentoRL);
				} else {
					// Los créditos fiscales son más estrictos y en este caso dará error y no permitirá enviarlo si no se tiene ningún documento del cliente
					$nitCliente = NULL;
				}
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
				'documentoRelacionado' 		=> $documentoRelacionadoMH,
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
					'codEstableMH' 				=> $codEstableMH,
					'codEstable' 				=> $codEstable,
					'codPuntoVentaMH' 			=> $codPuntoVentaMH,
					'codPuntoVenta' 			=> $codPuntoVenta
				),
				'receptor' 					=> array(
					'nit' 						=> $nitCliente,
					'nrc' 						=> $nrcCliente,
					'nombre' 					=> substr($dataDTEGeneral->nombreCliente, 0, 249),
					'codActividad' 				=> $codActividadEconomicaReceptor,
					'descActividad' 			=> $descripcionActividadEconomicaReceptor,
					'nombreComercial' 			=> substr(($dataDTEGeneral->nombreComercialCliente == "" ? $dataDTEGeneral->nombreCliente : $dataDTEGeneral->nombreComercialCliente), 0, 149),
					'direccion' 			=> array(
						'departamento'			=> $dataDTEGeneral->codigoDepartamentoClienteMH,
						'municipio'				=> $dataDTEGeneral->codigoMunicipioClienteMH,
						'complemento'			=> ($dataDTEGeneral->direccionClienteUbicacion == "" ? "------" : substr($dataDTEGeneral->direccionClienteUbicacion, 0, 199))
					),
					'telefono' 					=> $telefonoCliente,
					'correo' 					=> $correoCliente
				),
				'otrosDocumentos' 			=> $otrosDocumentos,
				'ventaTercero' 				=> $ventaTercero,
				'cuerpoDocumento' 			=> $cuerpoDocumento,
				'resumen' 					=> $resumenDocumento,
				'extension' 				=> $extensionDTE,
				'apendice' 					=> $apendiceDTE,
				"selloRecibido"				=> $getNumerosDTE->selloRecibido,
				"firmaElectronica"			=> $getNumerosDTE->DTEfirmado
			);
		break;
		
		case '3':
			$cuerpoDocumento = array();
							$numItem = 0;

							// Variables del cuerpo
								$numeroDocumentoCuerpo = NULL;
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
									//$unidadMedidaCuerpo = "99";
									// mh_015_tributos
									// Otras tasas casos especiales
									// Cuando la unidadMedida es 99
									//$codTributoCuerpo = "D5";
									$unidadMedidaCuerpo = "59";
									$codigoTipoItemMH = 3;
									$codTributoCuerpo = NULL;
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

								$cuerpoDocumento[$numItem] = array(
									"numItem" 					=> (int)($numItem + 1),
									"tipoItem" 					=> (int)$dteDetalle->codigoTipoItemMH,
									"numeroDocumento" 			=> $numeroDocumentoCuerpo,
									"cantidad" 					=> (float)$dteDetalle->cantidadProducto,
									"codigo" 					=> $dteDetalle->codProductoFactura,
									"codTributo" 				=> $codTributoCuerpo,
									"uniMedida" 				=> (int)$unidadMedidaCuerpo,
									"descripcion" 				=> $dteDetalle->nombreProductoFactura,
									"precioUni" 				=> (float)$precioUnitarioCuerpo,
									"montoDescu" 				=> (float)$dteDetalle->descuentoTotal,
									"ventaNoSuj" 				=> (float)$ventaNoSujetaCuerpo,
									"ventaExenta" 				=> (float)$ventaExentaCuerpo,
									"ventaGravada" 				=> (float)$ventaGravadaCuerpo,
									"tributos" 					=> $tributosCuerpo,
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
								'totalNoSuj' 				=> (float)round($totalNoSujetoResumen, 2),
								'totalExenta' 				=> (float)round($totalExentaResumen, 2),
								'totalGravada' 				=> (float)round($totalGravadaResumen, 2),
								'subTotalVentas' 			=> (float)round($subTotalVentasResumen, 2),
								'descuNoSuj' 				=> (float)round($descuNoSujResumen, 2),
								'descuExenta' 				=> (float)round($descuentoExentoResumen, 2),
								'descuGravada' 				=> (float)round($descuentoGravadoResumen, 2),
								'porcentajeDescuento' 		=> (float)round($porcentajeDescuentoGlobal, 2),
								'totalDescu' 				=> (float)round($totalDescuentoResumen, 2),
								'tributos' 					=> $tributosResumen,
								'subTotal' 					=> (float)round($subTotalVentasResumen, 2),
								'montoTotalOperacion' 		=> (float)round($totalPagar, 2),
								'totalLetras' 				=> dineroLetras($totalPagar, "decimal") . " USD",
						    );
						// Fin formar resumenDocumento

						// Variables generales
							$extensionDTE = NULL;
							$apendiceDTE = NULL;

						$nrcCliente = ($dataDTEGeneral->nrcCliente == "" ? NULL : str_replace("-", "", $dataDTEGeneral->nrcCliente));

						// Es obligación el NIT, buscar exactamente solo ese tipo de documento para evitar errores
						// mh_022_tipo_documento tipoDocumentoClienteId = 1
						// Acepta homologación DUI tipoDocumentoClienteId = 2
						$dataNITCliente = $cloud->row("
							SELECT
								numDocumento
							FROM fel_clientes
							WHERE clienteId = ? AND (tipoDocumentoMHId = ? OR tipoDocumentoMHId = ?) AND flgDelete = ?
						", [$dataDTEGeneral->clienteId, 1, 2, 0]);

						if($dataNITCliente) {
							$nitCliente = str_replace("-", "", $dataNITCliente->numDocumento);
						} else {
							// Intentar con el presentante legal
							$dataNITCliente = $cloud->row("
								SELECT
									numDocumentoRL
								FROM fel_clientes
								WHERE clienteId = ? AND (tipoDocumentoRL = ? OR tipoDocumentoRL = ?) AND flgDelete = ?
							", [$dataDTEGeneral->clienteId, 1, 2, 0]);
							if($dataNITCliente) {
								$nitCliente = $dataNITCliente->numDocumentoRL;
							} else {
								// Los créditos fiscales son más estrictos y en este caso dará error y no permitirá enviarlo si no se tiene ningún documento del cliente
								$nitCliente = NULL;
							}
						}

						if($dataDTEGeneral->codigoActividadClienteMH == "" || is_null($dataDTEGeneral->codigoActividadClienteMH)) {
							// No se ha actualizado la inf. del cliente, reportar como Otros
							$codActividadEconomicaReceptor = "10005";
							$descripcionActividadEconomicaReceptor = "Otros";
						} else {
							$codActividadEconomicaReceptor = $dataDTEGeneral->codigoActividadClienteMH;
							$descripcionActividadEconomicaReceptor = $dataDTEGeneral->actividadEconomicaCliente;
						}

						// Enviar DUI con guion
						if($dataDTEGeneral->codigoDocumentoClienteMH == "13") {
							$codigoDocumentoClienteMH = "13";
							$numDocumentoCliente = $dataDTEGeneral->numDocumentoCliente;
						} else {
							// Quitar guión para NIT
							$numDocumentoCliente = str_replace("-", "", $dataDTEGeneral->numDocumentoCliente);
							if($numDocumentoCliente == "") {
								$codigoDocumentoClienteMH = "37";
								$numDocumentoCliente = "-N/A-";
							} else {
								$codigoDocumentoClienteMH = $dataDTEGeneral->codigoDocumentoClienteMH;
								$numDocumentoCliente = $numDocumentoCliente;
							}
						}

						$getTipoRemision = $cloud->row("SELECT codigoMH FROM mh_025_tipo_remision WHERE tipoRemisionMHId = ?", [$dataDTEGeneral->tipoRemisionMHId]);
						$tipoRemision = $getTipoRemision->codigoMH;

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
						    'documentoRelacionado' 		=> NULL,
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
								'codEstableMH' 				=> $codEstableMH,
								'codEstable' 				=> $codEstable,
								'codPuntoVentaMH' 			=> $codPuntoVentaMH,
								'codPuntoVenta' 			=> $codPuntoVenta
						    ),
						    'receptor' 					=> array(
								'tipoDocumento' 			=> $codigoDocumentoClienteMH,
								'numDocumento' 				=> $numDocumentoCliente,
								'nrc' 						=> NULL,
								'nombre' 					=> substr($dataDTEGeneral->nombreCliente, 0, 249),
								'codActividad' 				=> $codActividadEconomicaReceptor,
								'descActividad' 			=> $descripcionActividadEconomicaReceptor,
								'nombreComercial' 			=> substr(($dataDTEGeneral->nombreComercialCliente == "" ? $dataDTEGeneral->nombreCliente : $dataDTEGeneral->nombreComercialCliente), 0, 149),
								'direccion' 			=> array(
									'departamento'			=> $dataDTEGeneral->codigoDepartamentoClienteMH,
									'municipio'				=> $dataDTEGeneral->codigoMunicipioClienteMH,
									'complemento'			=> ($dataDTEGeneral->direccionClienteUbicacion == "" ? "------" : substr($dataDTEGeneral->direccionClienteUbicacion, 0, 199))
								),
								'telefono' 					=> $telefonoCliente,
								'correo' 					=> $correoCliente,
								'bienTitulo'				=> $tipoRemision //mh_025_tipo_remision
						    ),
						    // No aplica para notas de crédito
						    //'otrosDocumentos' 			=> $otrosDocumentos,
						    'ventaTercero' 				=> $ventaTercero,
						    'cuerpoDocumento' 			=> $cuerpoDocumento,
						    'resumen' 					=> $resumenDocumento,
						    'extension' 				=> $extensionDTE,
						    'apendice' 					=> $apendiceDTE
						);
						
		break;
		
		case '4':
			// Nota de crédito

			// Las notas de crédito llevan documentoRelacionado
			$documentoRelacionadoMH = array();

			$dataFacturaRelacionada = $cloud->row("
				SELECT
					ffrel.facturaIdRelacionada AS facturaIdRelacionada,
					cat002.codigoMH AS codigoDTERelacionadoMH,
					cat007.codigoMH AS codigoTipoGeneracionMH,
					ffrel.numeroDocumentoRelacionada AS numeroDocumentoRelacionada,
					ffrel.fechaEmisionRelacionada AS fechaEmisionRelacionada
				FROM fel_factura_relacionada$yearBD ffrel
				LEFT JOIN fel_factura$yearBD f ON f.facturaId = ffrel.facturaIdRelacionada
				LEFT JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = f.tipoDTEId
				JOIN mh_007_tipo_generacion_documento cat007 ON cat007.tipoGeneracionDocId = ffrel.tipoGeneracionDocId
				WHERE ffrel.facturaId = ? AND ffrel.flgDelete = ?
			", [$dataDTEGeneral->facturaId, 0]);

			if($dataFacturaRelacionada->facturaIdRelacionada == 0) {
				// Fue físico, dejar crédito fiscal
				$tipoDocumentoRelacionado = "03";
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
						//$unidadMedidaCuerpo = "99";
						// mh_015_tributos
						// Otras tasas casos especiales
						// Cuando la unidadMedida es 99
						//$codTributoCuerpo = "D5";
						$unidadMedidaCuerpo = "59";
						$codigoTipoItemMH = 3;
						$codTributoCuerpo = NULL;
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

					$cuerpoDocumento[$numItem] = array(
						"numItem" 					=> (int)($numItem + 1),
						"tipoItem" 					=> (int)$dteDetalle->codigoTipoItemMH,
						"numeroDocumento" 			=> $numeroDocumentoCuerpo,
						"cantidad" 					=> (float)$dteDetalle->cantidadProducto,
						"codigo" 					=> $dteDetalle->codProductoFactura,
						"codTributo" 				=> $codTributoCuerpo,
						"uniMedida" 				=> (int)$unidadMedidaCuerpo,
						"descripcion" 				=> $dteDetalle->nombreProductoFactura,
						"precioUni" 				=> (float)$precioUnitarioCuerpo,
						"montoDescu" 				=> (float)$dteDetalle->descuentoTotal,
						"ventaNoSuj" 				=> (float)$ventaNoSujetaCuerpo,
						"ventaExenta" 				=> (float)$ventaExentaCuerpo,
						"ventaGravada" 				=> (float)$ventaGravadaCuerpo,
						"tributos" 					=> $tributosCuerpo,
						// No aplica para notas de crédito
						//"psv" 					=> (float)$psvCuerpo,
						//"noGravado" 				=> (float)$noGravadoCuerpo
						//"ivaItem" 				=> (float)$dteDetalle->ivaTotal
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
					'totalNoSuj' 				=> (float)round($totalNoSujetoResumen, 2),
					'totalExenta' 				=> (float)round($totalExentaResumen, 2),
					'totalGravada' 				=> (float)round($totalGravadaResumen, 2),
					'subTotalVentas' 			=> (float)round($subTotalVentasResumen, 2),
					'descuNoSuj' 				=> (float)round($descuNoSujResumen, 2),
					'descuExenta' 				=> (float)round($descuentoExentoResumen, 2),
					'descuGravada' 				=> (float)round($descuentoGravadoResumen, 2),
					// No aplica para notas de crédito
					//'porcentajeDescuento' 		=> (float)round($porcentajeDescuentoGlobal, 2),
					'totalDescu' 				=> (float)round($totalDescuentoResumen, 2),
					'tributos' 					=> $tributosResumen,
					'subTotal' 					=> (float)round($subTotalVentasResumen, 2),
					'ivaPerci1' 				=> (float)round($dataDTEGeneral->ivaPercibido, 2),
					'ivaRete1' 					=> (float)round($dataDTEGeneral->ivaRetenido, 2),
					'reteRenta' 				=> (float)round($dataDTEGeneral->rentaRetenido, 2),
					'montoTotalOperacion' 		=> (float)round($totalPagar, 2),
					// No aplica para notas de crédito
					//'totalNoGravado' 			=> (float)round($totalNoGravadoResumen, 2),
					//'totalPagar' 				=> (float)round($totalPagar, 2),
					'totalLetras' 				=> dineroLetras($totalPagar, "decimal") . " USD",
					// No aplica para notas de crédito
					//'totalIva' 					=> (float)round($totalIVAResumen, 2),
					//'saldoFavor' 				=> (float)round($saldoFavorResumen, 2),
					'condicionOperacion' 		=> (int)$condicionOperacionResumen
					/*
					No aplica para notas de crédito
					'pagos' 				=> array (
						0 						=> array (
							  'codigo' 				=> $dataDTEPago->codigoFormaPagoMH,
							  'montoPago' 			=> (float)round($totalPagar, 2),
							  'referencia' 			=> null, //$referenciaPagoResumen, <- ese null no lo quiere :v
							  'plazo' 				=> $plazoPagoResumen,
							  'periodo' 				=> $periodoPagoResumen
						),
					  ),
					  'numPagoElectronico' => $numPagoElectronico
					  */
				);
			// Fin formar resumenDocumento

			// Variables generales
				$extensionDTE = NULL;
				$apendiceDTE = NULL;

			$nrcCliente = ($dataDTEGeneral->nrcCliente == "" ? NULL : str_replace("-", "", $dataDTEGeneral->nrcCliente));

			// Es obligación el NIT, buscar exactamente solo ese tipo de documento para evitar errores
			// mh_022_tipo_documento tipoDocumentoClienteId = 1
			// Acepta homologación DUI tipoDocumentoClienteId = 2
			$dataNITCliente = $cloud->row("
				SELECT
					numDocumento
				FROM fel_clientes
				WHERE clienteId = ? AND (tipoDocumentoMHId = ? OR tipoDocumentoMHId = ?) AND flgDelete = ?
			", [$dataDTEGeneral->clienteId, 1, 2, 0]);

			if($dataNITCliente) {
				$nitCliente = str_replace("-", "", $dataNITCliente->numDocumento);
			} else {
				// Intentar con el presentante legal
				$dataNITCliente = $cloud->row("
					SELECT
						numDocumentoRL
					FROM fel_clientes
					WHERE clienteId = ? AND (tipoDocumentoRL = ? OR tipoDocumentoRL = ?) AND flgDelete = ?
				", [$dataDTEGeneral->clienteId, 1, 2, 0]);
				if($dataNITCliente) {
					$nitCliente = $dataNITCliente->numDocumentoRL;
				} else {
					// Los créditos fiscales son más estrictos y en este caso dará error y no permitirá enviarlo si no se tiene ningún documento del cliente
					$nitCliente = NULL;
				}
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
				'documentoRelacionado' 		=> $documentoRelacionadoMH,
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
					'correo' 					=> $correoSucursal
					// No aplica para notas de crédito
					//'codEstableMH' 			=> $codEstableMH,
					//'codEstable' 				=> $codEstable,
					//'codPuntoVentaMH' 		=> $codPuntoVentaMH,
					//'codPuntoVenta' 			=> $codPuntoVenta
				),
				'receptor' 					=> array(
					'nit' 						=> $nitCliente,
					'nrc' 						=> $nrcCliente,
					'nombre' 					=> substr($dataDTEGeneral->nombreCliente, 0, 249),
					'codActividad' 				=> $codActividadEconomicaReceptor,
					'descActividad' 			=> $descripcionActividadEconomicaReceptor,
					'nombreComercial' 			=> substr(($dataDTEGeneral->nombreComercialCliente == "" ? $dataDTEGeneral->nombreCliente : $dataDTEGeneral->nombreComercialCliente), 0, 149),
					'direccion' 			=> array(
						'departamento'			=> $dataDTEGeneral->codigoDepartamentoClienteMH,
						'municipio'				=> $dataDTEGeneral->codigoMunicipioClienteMH,
						'complemento'			=> ($dataDTEGeneral->direccionClienteUbicacion == "" ? "------" : substr($dataDTEGeneral->direccionClienteUbicacion, 0, 199))
					),
					'telefono' 					=> $telefonoCliente,
					'correo' 					=> $correoCliente
				),
				// No aplica para notas de crédito
				//'otrosDocumentos' 			=> $otrosDocumentos,
				'ventaTercero' 				=> $ventaTercero,
				'cuerpoDocumento' 			=> $cuerpoDocumento,
				'resumen' 					=> $resumenDocumento,
				'extension' 				=> $extensionDTE,
				'apendice' 					=> $apendiceDTE,
				"selloRecibido"				=> $getNumerosDTE->selloRecibido,
				"firmaElectronica"			=> $getNumerosDTE->DTEfirmado
			);
		break;

		case '5':
			// Nota de débito

			// Las notas de débito llevan documentoRelacionado
			$documentoRelacionadoMH = array();

			$dataFacturaRelacionada = $cloud->row("
				SELECT
					ffrel.facturaIdRelacionada AS facturaIdRelacionada,
					cat002.codigoMH AS codigoDTERelacionadoMH,
					cat007.codigoMH AS codigoTipoGeneracionMH,
					ffrel.numeroDocumentoRelacionada AS numeroDocumentoRelacionada,
					ffrel.fechaEmisionRelacionada AS fechaEmisionRelacionada
				FROM fel_factura_relacionada$yearBD ffrel
				LEFT JOIN fel_factura$yearBD f ON f.facturaId = ffrel.facturaIdRelacionada
				LEFT JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = f.tipoDTEId
				JOIN mh_007_tipo_generacion_documento cat007 ON cat007.tipoGeneracionDocId = ffrel.tipoGeneracionDocId
				WHERE ffrel.facturaId = ? AND ffrel.flgDelete = ?
			", [$dataDTEGeneral->facturaId, 0]);

			if($dataFacturaRelacionada->facturaIdRelacionada == 0) {
				// Fue físico, dejar crédito fiscal
				$tipoDocumentoRelacionado = "03";
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
						//$unidadMedidaCuerpo = "99";
						// mh_015_tributos
						// Otras tasas casos especiales
						// Cuando la unidadMedida es 99
						//$codTributoCuerpo = "D5";
						$unidadMedidaCuerpo = "59";
						$codigoTipoItemMH = 3;
						$codTributoCuerpo = NULL;
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

					$cuerpoDocumento[$numItem] = array(
						"numItem" 					=> (int)($numItem + 1),
						"tipoItem" 					=> (int)$dteDetalle->codigoTipoItemMH,
						"numeroDocumento" 			=> $numeroDocumentoCuerpo,
						"cantidad" 					=> (float)$dteDetalle->cantidadProducto,
						"codigo" 					=> $dteDetalle->codProductoFactura,
						"codTributo" 				=> $codTributoCuerpo,
						"uniMedida" 				=> (int)$unidadMedidaCuerpo,
						"descripcion" 				=> $dteDetalle->nombreProductoFactura,
						"precioUni" 				=> (float)$precioUnitarioCuerpo,
						"montoDescu" 				=> (float)$dteDetalle->descuentoTotal,
						"ventaNoSuj" 				=> (float)$ventaNoSujetaCuerpo,
						"ventaExenta" 				=> (float)$ventaExentaCuerpo,
						"ventaGravada" 				=> (float)$ventaGravadaCuerpo,
						"tributos" 					=> $tributosCuerpo,
						// No aplica para notas de crédito
						//"psv" 					=> (float)$psvCuerpo,
						//"noGravado" 				=> (float)$noGravadoCuerpo
						//"ivaItem" 				=> (float)$dteDetalle->ivaTotal
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
					'totalNoSuj' 				=> (float)round($totalNoSujetoResumen, 2),
					'totalExenta' 				=> (float)round($totalExentaResumen, 2),
					'totalGravada' 				=> (float)round($totalGravadaResumen, 2),
					'subTotalVentas' 			=> (float)round($subTotalVentasResumen, 2),
					'descuNoSuj' 				=> (float)round($descuNoSujResumen, 2),
					'descuExenta' 				=> (float)round($descuentoExentoResumen, 2),
					'descuGravada' 				=> (float)round($descuentoGravadoResumen, 2),
					// No aplica para notas de crédito
					//'porcentajeDescuento' 		=> (float)round($porcentajeDescuentoGlobal, 2),
					'totalDescu' 				=> (float)round($totalDescuentoResumen, 2),
					'tributos' 					=> $tributosResumen,
					'subTotal' 					=> (float)round($subTotalVentasResumen, 2),
					'ivaPerci1' 				=> (float)round($dataDTEGeneral->ivaPercibido, 2),
					'ivaRete1' 					=> (float)round($dataDTEGeneral->ivaRetenido, 2),
					'reteRenta' 				=> (float)round($dataDTEGeneral->rentaRetenido, 2),
					'montoTotalOperacion' 		=> (float)round($totalPagar, 2),
					// No aplica para notas de crédito
					//'totalNoGravado' 			=> (float)round($totalNoGravadoResumen, 2),
					//'totalPagar' 				=> (float)round($totalPagar, 2),
					'totalLetras' 				=> dineroLetras($totalPagar, "decimal") . " USD",
					// No aplica para notas de crédito
					//'totalIva' 					=> (float)round($totalIVAResumen, 2),
					//'saldoFavor' 				=> (float)round($saldoFavorResumen, 2),
					'condicionOperacion' 		=> (int)$condicionOperacionResumen,
					/*
					No aplica para notas de crédito
					'pagos' 				=> array (
						0 						=> array (
							  'codigo' 				=> $dataDTEPago->codigoFormaPagoMH,
							  'montoPago' 			=> (float)round($totalPagar, 2),
							  'referencia' 			=> null, //$referenciaPagoResumen, <- ese null no lo quiere :v
							  'plazo' 				=> $plazoPagoResumen,
							  'periodo' 				=> $periodoPagoResumen
						),
					  ),
					  */
					  'numPagoElectronico' => $numPagoElectronico
				);
			// Fin formar resumenDocumento

			// Variables generales
				$extensionDTE = NULL;
				$apendiceDTE = NULL;

			$nrcCliente = ($dataDTEGeneral->nrcCliente == "" ? NULL : str_replace("-", "", $dataDTEGeneral->nrcCliente));

			// Es obligación el NIT, buscar exactamente solo ese tipo de documento para evitar errores
			// mh_022_tipo_documento tipoDocumentoClienteId = 1
			// Acepta homologación DUI tipoDocumentoClienteId = 2
			$dataNITCliente = $cloud->row("
				SELECT
					numDocumento
				FROM fel_clientes
				WHERE clienteId = ? AND (tipoDocumentoMHId = ? OR tipoDocumentoMHId = ?) AND flgDelete = ?
			", [$dataDTEGeneral->clienteId, 1, 2, 0]);

			if($dataNITCliente) {
				$nitCliente = str_replace("-", "", $dataNITCliente->numDocumento);
			} else {
				// Intentar con el presentante legal
				$dataNITCliente = $cloud->row("
					SELECT
						numDocumentoRL
					FROM fel_clientes
					WHERE clienteId = ? AND (tipoDocumentoRL = ? OR tipoDocumentoRL = ?) AND flgDelete = ?
				", [$dataDTEGeneral->clienteId, 1, 2, 0]);
				if($dataNITCliente) {
					$nitCliente = $dataNITCliente->numDocumentoRL;
				} else {
					// Los créditos fiscales son más estrictos y en este caso dará error y no permitirá enviarlo si no se tiene ningún documento del cliente
					$nitCliente = NULL;
				}
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
				'documentoRelacionado' 		=> $documentoRelacionadoMH,
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
					'correo' 					=> $correoSucursal
					// No aplica para notas de crédito
					//'codEstableMH' 			=> $codEstableMH,
					//'codEstable' 				=> $codEstable,
					//'codPuntoVentaMH' 		=> $codPuntoVentaMH,
					//'codPuntoVenta' 			=> $codPuntoVenta
				),
				'receptor' 					=> array(
					'nit' 						=> $nitCliente,
					'nrc' 						=> $nrcCliente,
					'nombre' 					=> substr($dataDTEGeneral->nombreCliente, 0, 249),
					'codActividad' 				=> $codActividadEconomicaReceptor,
					'descActividad' 			=> $descripcionActividadEconomicaReceptor,
					'nombreComercial' 			=> substr(($dataDTEGeneral->nombreComercialCliente == "" ? $dataDTEGeneral->nombreCliente : $dataDTEGeneral->nombreComercialCliente), 0, 149),
					'direccion' 			=> array(
						'departamento'			=> $dataDTEGeneral->codigoDepartamentoClienteMH,
						'municipio'				=> $dataDTEGeneral->codigoMunicipioClienteMH,
						'complemento'			=> ($dataDTEGeneral->direccionClienteUbicacion == "" ? "------" : substr($dataDTEGeneral->direccionClienteUbicacion, 0, 199))
					),
					'telefono' 					=> $telefonoCliente,
					'correo' 					=> $correoCliente
				),
				// No aplica para notas de crédito
				//'otrosDocumentos' 			=> $otrosDocumentos,
				'ventaTercero' 				=> $ventaTercero,
				'cuerpoDocumento' 			=> $cuerpoDocumento,
				'resumen' 					=> $resumenDocumento,
				'extension' 				=> $extensionDTE,
				'apendice' 					=> $apendiceDTE,
				"selloRecibido"				=> $getNumerosDTE->selloRecibido,
				"firmaElectronica"			=> $getNumerosDTE->DTEfirmado
			);
		break;

		case '6':
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
				FROM fel_factura_relacionada$yearBD ffrel
				LEFT JOIN fel_factura$yearBD f ON f.facturaId = ffrel.facturaIdRelacionada
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

					$cuerpoDocumento[$numItem] = array(
						"numItem" 					=> (int)($numItem + 1),
						"tipoDte" 					=> $tipoDocumentoRelacionado,
						"tipoDoc" 					=> (int)$dataFacturaRelacionada->codigoTipoGeneracionMH,
						"numDocumento" 				=> $numeroDocumentoRelacionada,
						"fechaEmision" 				=> $fechaEmisionRelacionada,
						"montoSujetoGrav" 			=> (float)$dteDetalle->totalDetalle,
						"codigoRetencionMH" 		=> "22", // CAT-006 IVA Retenido
						"ivaRetenido" 				=> (float)$dataDTEGeneral->ivaRetenido,
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
					//	$porcentajeDescuentoGlobal = ($totalDescuentoResumen * 100) / $dataDTEPago->totalFactura;

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

			$nrcCliente =  0;//($dataDTEGeneral->nrcCliente == "" ? NULL : str_replace("-", "", $dataDTEGeneral->nrcCliente));
			$numDocumentoCliente = 0; //str_replace("-", "", $dataDTEGeneral->numDocumentoCliente);

			if($numDocumentoCliente == "") {
				$tipoDocumentoReceptor = "37";
				$numDocumentoCliente = $nrcCliente;
			} else {
				// Se queda el numDocumento normal
				$tipoDocumentoReceptor = 0;
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
					//'numeroControl' 			=> $numeroControl,
					//'codigoGeneracion' 			=> $uuidDTE,
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
					'tipoDocumento' 			=>  $dataDTEGeneral->tipoDocumentoCliente,
					'numDocumento' 				=>  $dataDTEGeneral->numDocumentoCliente,
					'nrc' 						=>  $dataDTEGeneral->nrcProveedor,
					'nombre' 					=> substr($dataDTEGeneral->nombreProveedor, 0, 249),
					'codActividad' 				=> $codActividadEconomicaReceptor,
					'descActividad' 			=> $descripcionActividadEconomicaReceptor,
					'nombreComercial' 			=> substr(($dataDTEGeneral->nombreComercial == "" ? $dataDTEGeneral->nombreProveedor : $dataDTEGeneral->nombreComercial), 0, 149),
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
				'apendice' 					=> $apendiceDTE,
				"selloRecibido"				=> $getNumerosDTE->selloRecibido,
				"firmaElectronica"			=> $getNumerosDTE->DTEfirmado
			);
		break;

		case '9':
			// Factura de exportación
			
			// Formar el cuerpoDocumento
				$cuerpoDocumento = array();
				$numItem = 0;

				// Variables del cuerpo
					$numeroDocumentoCuerpo = NULL;
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

				// Variables Cloud
					$subTotalGeneral = 0;

				foreach ($dataDTEDetalle as $dteDetalle) {
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

					// Sujeto excluido es exento
					if($dteDetalle->precioVenta > $dteDetalle->precioUnitario) {
						// Se vendió a un precio más alto que el de lista
						$precioUnitarioCuerpo = $dteDetalle->precioVenta;
					} else {
						$precioUnitarioCuerpo = $dteDetalle->precioUnitario;
					}
					$ventaExentaCuerpo = $dteDetalle->totalDetalle;
					$ventaGravadaCuerpo = 0;
					//$subTotalVentasResumen += $dteDetalle->subTotalDetalle;
					$subTotalVentasResumen += $dteDetalle->totalDetalle;

					$totalDescuentoResumen += $dteDetalle->descuentoTotal;
					$totalIVAResumen += $dteDetalle->ivaTotal;
					$subTotalGeneral += $dteDetalle->subTotalDetalle;

					$cuerpoDocumento[$numItem] = array(
						"numItem" 					=> (int)($numItem + 1),
						//"tipoItem" 					=> (int)$dteDetalle->codigoTipoItemMH,
						//"numeroDocumento" 			=> $numeroDocumentoCuerpo,
						"codigo" 					=> $dteDetalle->codProductoFactura,
						"descripcion" 				=> $dteDetalle->nombreProductoFactura,
						"cantidad" 					=> (float)$dteDetalle->cantidadProducto,
						//"codTributo" 				=> $codTributoCuerpo,
						"uniMedida" 				=> (int)$unidadMedidaCuerpo,
						"precioUni" 				=> (float)$precioUnitarioCuerpo,
						"montoDescu" 				=> (float)$dteDetalle->descuentoTotal,
						//"ventaNoSuj" 				=> (float)$ventaNoSujetaCuerpo,
						//"ventaExenta" 				=> (float)$ventaExentaCuerpo,
						"ventaGravada" 				=> (float)$ventaExentaCuerpo,
						"tributos" 					=> $tributosCuerpo,
						//"psv" 						=> (float)$psvCuerpo,
						"noGravado" 				=> (float)$noGravadoCuerpo,
						//"ivaItem" 					=> (float)$dteDetalle->ivaTotal
					);
					$numItem++;
				} // foreach dteDetalle
			// Fin formar cuerpoDocumento
			//var_dump($cuerpoDocumento);

			$flgFacturaExenta = "Sí";

			// Formar resumenDocumento
				// Variables del cuerpo
					// No tenemos venta no sujeta
					$totalNoSujetoResumen = 0;
					$descuNoSujResumen = 0;
					$tributosResumen = NULL;
					$totalNoGravadoResumen = 0;
					$saldoFavorResumen = 0;
					$referenciaPagoResumen = NULL;
					// mh_018_plazo_pago 01 = Dias
					// De momento dejarlo como 01, porque Indupal solo da días de Crédito
					$plazoPagoResumen = '01';
					$numPagoElectronico = NULL;
					$observacionesResumen = NULL;

					// mh_016_condicion_factura 1 = Contado, 2 = Crédito, 3 = Otro
					// Solicitud de Heidi Reyes para informar la operación si es contado o crédito
					$condicionOperacionResumen = $dataDTEGeneral->codigoCondicionOperacion;

					if($condicionOperacionResumen == 1) {
						$periodoPagoResumen = NULL;
					} else {
						// Validar si es a cero días revertir, actualmente se elaboran así si hay más de una forma de pago, para liquidarla con abonos
						if($dataDTEGeneral->periodoPlazo == 0) {
							$condicionOperacionResumen = (int)1;
							$periodoPagoResumen = NULL;
						} else {
							$periodoPagoResumen = (int)$dataDTEGeneral->periodoPlazo;
						}
					}

					// Sujeto excluido es exento, solo refleja renta
					$totalExentaResumen = $subTotalVentasResumen;
					$totalGravadaResumen = 0;
					$descuentoExentoResumen = $totalDescuentoResumen;
					//$descuentoExentoResumen = 0;
					$descuentoGravadoResumen = 0;

					// Porcentaje de descueto global resumenDocumento por regla de 3
						//$porcentajeDescuentoGlobal = ($totalDescuentoResumen * 100) / $dataDTEPago->totalFactura;
						$porcentajeDescuentoGlobal = ($totalDescuentoResumen * 100) / $subTotalGeneral;

				$fleteResumen = 0;
				$seguroResumen = 0;
				$montoTotalOperacion = $dataDTEPago->totalFactura + $fleteResumen + $seguroResumen;
				$resumenDocumento = array(
					//'totalExenta' 				=> (float)round($totalExentaResumen, 2),
					'totalGravada' 				=> (float)round($totalExentaResumen, 2),
					'descuento' 				=> (float)0,
					'porcentajeDescuento' 		=> (float)round($porcentajeDescuentoGlobal, 2),
					'totalDescu' 				=> (float)round($totalDescuentoResumen, 2),
					'montoTotalOperacion' 		=> (float)round($montoTotalOperacion , 2),
					'totalNoGravado' 			=> (float)round($totalNoGravadoResumen, 2),
					'totalPagar' 				=> (float)round($dataDTEPago->totalFactura, 2),
					'totalLetras' 				=> dineroLetras($dataDTEPago->totalFactura, "decimal") . " USD",
					'condicionOperacion' 		=> (int)$condicionOperacionResumen,
					'pagos' 				=> array (
						0 						=> array (
							  'codigo' 				=> $dataDTEPago->codigoFormaPagoMH,
							  'montoPago' 			=> (float)round($dataDTEPago->totalFactura, 2),
							  'referencia' 			=> null, //$referenciaPagoResumen, <- ese null no lo quiere :v
							  'plazo' 				=> $plazoPagoResumen,
							  'periodo' 				=> $periodoPagoResumen
						),
					  ),
					'codIncoterms' 			=> '01',
					'descIncoterms' 		=> 'EXW-En fábrica',
					'observaciones'			=> NULL,
					'flete' 				=> (float)round($fleteResumen,2),
					'numPagoElectronico' 	=> NULL,
					'seguro' 				=> (float)round($seguroResumen,2)
				);
			// Fin formar resumenDocumento

			// Variables generales
				$extensionDTE = NULL;
				$apendiceDTE = NULL;

			//$nrcCliente = ($dataDTEGeneral->nrcCliente == "" ? NULL : str_replace("-", "", $dataDTEGeneral->nrcCliente));
			$nrcCliente = NULL;

			$dataExportacion = "";

			$numDocumentoCliente = str_replace("-", "", $dataDTEGeneral->numDocumentoCliente);

			if($numDocumentoCliente == "") {
				$numDocumentoCliente = $dataDTEGeneral->nrcCliente;
			} else {
				// Se mantiene numDocumento
			}

			if($dataDTEGeneral->codigoActividadClienteMH == "" || is_null($dataDTEGeneral->codigoActividadClienteMH)) {
				// No se ha actualizado la inf. del cliente, reportar como Otros
				$codActividadEconomicaReceptor = "10005";
				$descripcionActividadEconomicaReceptor = "Otros";
			} else {
				$codActividadEconomicaReceptor = $dataDTEGeneral->codigoActividadClienteMH;
				$descripcionActividadEconomicaReceptor = $dataDTEGeneral->actividadEconomicaCliente;
			}

			$jsonDTE = array (
				'identificacion' 			=> array (
					'version' 					=> (int)$dataDTEGeneral->versionDTEMH,
					'ambiente' 					=> $dataDTEGeneral->identificacionAmbiente,
					'tipoDte' 					=> $dataDTEGeneral->codigoDTEMH,
					'numeroControl' 			=> $numeroControl,
					'codigoGeneracion' 			=> $uuidDTE,
					'tipoModelo' 				=> (int)$dataDTEGeneral->codigoModeloMH,
					'tipoOperacion' 			=> (int)$tipoOperacionMH,
					'tipoContingencia' 			=> $tipoContingenciaMH,
					'motivoContigencia' 		=> $motivoContingenciaMH,
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
					'codEstableMH' 				=> $codEstableMH,
					'codEstable' 				=> $codEstable,
					'codPuntoVentaMH' 			=> $codPuntoVentaMH,
					'codPuntoVenta' 			=> $codPuntoVenta,
					'tipoItemExpor' 			=> 1, //(1: bienes, 2: servicios, 3:ambos)
					'recintoFiscal' 			=> '04', // mh_027_recinto_fiscal
					'regimen' 					=> 'EX-1.1000.000' //(NULL si tipoItemExpor es = 2) mh_028_regimen_exportacion
				),
				'receptor' 					=> array(
					'nombre' 					=> substr($dataDTEGeneral->nombreCliente, 0, 249),
					'codPais' 					=> "9483", // cat_paises Guatemala
					'nombrePais' 				=> "Guatemala",
					'complemento'			=> ($dataDTEGeneral->direccionClienteUbicacion == "" ? "------" : substr($dataDTEGeneral->direccionClienteUbicacion, 0, 199)),
					'tipoDocumento' 			=> "37", // "Otros" mh_022_tipo_documento
					'numDocumento' 				=> $numDocumentoCliente,
					'nombreComercial' 			=> substr(($dataDTEGeneral->nombreComercialCliente == "" ? $dataDTEGeneral->nombreCliente : $dataDTEGeneral->nombreComercialCliente), 0, 149),
					'tipoPersona' 				=> (int)($dataDTEGeneral->codigoTipoPersonaMH == "" ? 2 : $dataDTEGeneral->codigoTipoPersonaMH),
					'descActividad' 			=> $descripcionActividadEconomicaReceptor,
					'telefono' 					=> $telefonoCliente,
					'correo' 					=> $correoCliente
				),
				'otrosDocumentos' 			=> $otrosDocumentos,
				'ventaTercero' 				=> $ventaTercero,
				'cuerpoDocumento' 			=> $cuerpoDocumento,
				'resumen' 					=> $resumenDocumento,
				//'extension' 				=> $extensionDTE,
				'apendice' 					=> $apendiceDTE,
				"selloRecibido"				=> $getNumerosDTE->selloRecibido,
				"firmaElectronica"			=> $getNumerosDTE->DTEfirmado
			);
		break;

		case '10':
			// Factura de sujeto excluido
			
			// Formar el cuerpoDocumento
				$cuerpoDocumento = array();
				$numItem = 0;

				// Variables del cuerpo
					$numeroDocumentoCuerpo = NULL;
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

				// Variables Cloud
					$subTotalGeneral = 0;

				foreach ($dataDTEDetalle as $dteDetalle) {
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

					// Sujeto excluido es exento
					if($dteDetalle->precioVenta > $dteDetalle->precioUnitario) {
						// Se vendió a un precio más alto que el de lista
						$precioUnitarioCuerpo = $dteDetalle->precioVenta;
					} else {
						$precioUnitarioCuerpo = $dteDetalle->precioUnitario;
					}
					$ventaExentaCuerpo = $dteDetalle->totalDetalle;
					$ventaGravadaCuerpo = 0;
					//$subTotalVentasResumen += $dteDetalle->subTotalDetalle;
					$subTotalVentasResumen += $dteDetalle->totalDetalle;

					$totalDescuentoResumen += $dteDetalle->descuentoTotal;
					$totalIVAResumen += $dteDetalle->ivaTotal;
					$subTotalGeneral += $dteDetalle->subTotalDetalle;

					$cuerpoDocumento[$numItem] = array(
						"numItem" 					=> (int)($numItem + 1),
						"tipoItem" 					=> (int)$dteDetalle->codigoTipoItemMH,
						//"numeroDocumento" 			=> $numeroDocumentoCuerpo,
						"cantidad" 					=> (float)$dteDetalle->cantidadProducto,
						"codigo" 					=> $dteDetalle->codProductoFactura,
						//"codTributo" 				=> $codTributoCuerpo,
						"uniMedida" 				=> (int)$unidadMedidaCuerpo,
						"descripcion" 				=> $dteDetalle->nombreProductoFactura,
						"precioUni" 				=> (float)$precioUnitarioCuerpo,
						"montoDescu" 				=> (float)$dteDetalle->descuentoTotal,
						"compra" 					=> (float)$ventaExentaCuerpo
						/*
						"ventaNoSuj" 				=> (float)$ventaNoSujetaCuerpo,
						"ventaExenta" 				=> (float)$ventaExentaCuerpo,
						"ventaGravada" 				=> (float)$ventaGravadaCuerpo,
						"tributos" 					=> $tributosCuerpo,
						"psv" 						=> (float)$psvCuerpo,
						"noGravado" 				=> (float)$noGravadoCuerpo,
						"ivaItem" 					=> (float)$dteDetalle->ivaTotal
						*/
					);
					$numItem++;
				} // foreach dteDetalle
			// Fin formar cuerpoDocumento
			//var_dump($cuerpoDocumento);

			$flgFacturaExenta = "Sí";

			// Formar resumenDocumento
				// Variables del cuerpo
					// No tenemos venta no sujeta
					$totalNoSujetoResumen = 0;
					$descuNoSujResumen = 0;
					$tributosResumen = NULL;
					$totalNoGravadoResumen = 0;
					$saldoFavorResumen = 0;
					$referenciaPagoResumen = NULL;
					// mh_018_plazo_pago 01 = Dias
					// De momento dejarlo como 01, porque Indupal solo da días de Crédito
					$plazoPagoResumen = '01';
					$numPagoElectronico = NULL;
					$observacionesResumen = NULL;

					// mh_016_condicion_factura 1 = Contado, 2 = Crédito, 3 = Otro
					// Solicitud de Heidi Reyes para informar la operación si es contado o crédito
					$condicionOperacionResumen = $dataDTEGeneral->codigoCondicionOperacion;

					if($condicionOperacionResumen == 1) {
						$periodoPagoResumen = NULL;
					} else {
						// Validar si es a cero días revertir, actualmente se elaboran así si hay más de una forma de pago, para liquidarla con abonos
						if($dataDTEGeneral->periodoPlazo == 0) {
							$condicionOperacionResumen = (int)1;
							$periodoPagoResumen = NULL;
						} else {
							$periodoPagoResumen = (int)$dataDTEGeneral->periodoPlazo;
						}
					}

					// Sujeto excluido es exento, solo refleja renta
					$totalExentaResumen = $subTotalVentasResumen;
					$totalGravadaResumen = 0;
					$descuentoExentoResumen = $totalDescuentoResumen;
					//$descuentoExentoResumen = 0;
					$descuentoGravadoResumen = 0;

					// Porcentaje de descueto global resumenDocumento por regla de 3
						//$porcentajeDescuentoGlobal = ($totalDescuentoResumen * 100) / $dataDTEPago->totalFactura;
						$porcentajeDescuentoGlobal = ($totalDescuentoResumen * 100) / $subTotalGeneral;

				// Si "subTotal" da error, restar totalDescuentoResumen porque probablemente asi lo interprete Hacienda
				$resumenDocumento = array(
					'totalCompra' 				=> (float)round($subTotalVentasResumen, 2),
					'descu' 					=> (float)round($descuentoExentoResumen, 2),
					//'totalNoSuj' 				=> (float)round($totalNoSujetoResumen, 2),
					//'totalExenta' 				=> (float)round($totalExentaResumen, 2),
					//'totalGravada' 				=> (float)round($totalGravadaResumen, 2),
					//'subTotalVentas' 			=> (float)round($subTotalVentasResumen, 2),
					//'descuNoSuj' 				=> (float)round($descuNoSujResumen, 2),
					//'descuExenta' 				=> (float)round($descuentoExentoResumen, 2),
					//'descuGravada' 				=> (float)round($descuentoGravadoResumen, 2),
					//'porcentajeDescuento' 		=> (float)round($porcentajeDescuentoGlobal, 2),
					'totalDescu' 				=> (float)round($totalDescuentoResumen, 2),
					//'tributos' 					=> $tributosResumen,
					'subTotal' 					=> (float)round($subTotalVentasResumen, 2),
					'ivaRete1' 					=> (float)round($dataDTEGeneral->ivaRetenido, 2),
					'reteRenta' 				=> (float)round($dataDTEGeneral->rentaRetenido, 2),
					//'montoTotalOperacion' 		=> (float)round($subTotalVentasResumen, 2),
					//'totalNoGravado' 			=> (float)round($totalNoGravadoResumen, 2),
					'totalPagar' 				=> (float)round($dataDTEPago->totalFactura, 2),
					'totalLetras' 				=> dineroLetras($dataDTEPago->totalFactura, "decimal") . " USD",
					//'totalIva' 					=> (float)round($totalIVAResumen, 2),
					//'saldoFavor' 				=> (float)round($saldoFavorResumen, 2),
					'condicionOperacion' 		=> (int)$condicionOperacionResumen,
					'pagos' 				=> array (
						0 						=> array (
							  'codigo' 				=> $dataDTEPago->codigoFormaPagoMH,
							  'montoPago' 			=> (float)round($dataDTEPago->totalFactura, 2),
							  'referencia' 			=> null, //$referenciaPagoResumen, <- ese null no lo quiere :v
							  'plazo' 				=> $plazoPagoResumen,
							  'periodo' 				=> $periodoPagoResumen
						),
					  ),
					  'observaciones' => $observacionesResumen
				);
			// Fin formar resumenDocumento

			// Variables generales
				$extensionDTE = NULL;
				$apendiceDTE = NULL;

			//$nrcCliente = ($dataDTEGeneral->nrcCliente == "" ? NULL : str_replace("-", "", $dataDTEGeneral->nrcCliente));
			$nrcCliente = NULL;

			$dataSujetoExcluido = $cloud->row("
				SELECT
					cat022se.codigoMH AS codigoTipoDocumentoPersonaMH,
					cat022se.tipoDocumentoCliente AS tipoDocumentoPersona,
					fse.numDocumento AS numDocumentoPersona,
					fse.nombreSujeto AS nombreSujeto,
					cat019cliente.codigoMh AS codigoActividadPersonaMH,
					cat019cliente.actividadEconomica AS actividadEconomicaPersona,
					cpdcliente.codigoMH AS codigoDepartamentoPersona,
					cpmcliente.codigoMH AS codigoMunicipioPersona,
					fse.direccionSujeto AS direccionSujeto,
					fse.telefonoSujeto AS telefonoSujeto,
					fse.correoSujeto AS correoSujeto
				FROM fel_sujeto_excluido fse
				JOIN mh_022_tipo_documento cat022se ON cat022se.tipoDocumentoClienteId = fse.tipoDocumentoMHId
				JOIN cat_paises_municipios cpmcliente ON cpmcliente.paisMunicipioId = fse.paisMunicipioId
				JOIN cat_paises_departamentos cpdcliente ON cpdcliente.paisDepartamentoId = cpmcliente.paisDepartamentoId
				JOIN mh_019_actividad_economica cat019cliente ON cat019cliente.actividadEconomicaId = fse.actividadEconomicaId
				WHERE fse.sujetoExcluidoId = ? AND fse.flgDelete = ?
			", [$dataDTEGeneral->sujetoExcluidoId, 0]);

			$numDocumentoCliente = str_replace("-", "", $dataSujetoExcluido->numDocumentoPersona);

			$jsonDTE = array (
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
					//'nombreComercial' 			=> $dataDTEGeneral->nombreComercialEmisor,
					//'tipoEstablecimiento' 		=> $dataDTEGeneral->codEstablecimientoMH,
					'direccion' 			=> array (
						'departamento' 			=> $dataDTEGeneral->codigoDepartamentoSucursalMH,
						'municipio' 			=> $dataDTEGeneral->codigoMunicipioSucursalMH,
						'complemento' 			=> $dataDTEGeneral->direccionSucursal
					),
					'telefono' 					=> $telefonoSucursal,
					'correo' 					=> $correoSucursal,
					'codEstableMH' 				=> $codEstableMH,
					'codEstable' 				=> $codEstable,
					'codPuntoVentaMH' 			=> $codPuntoVentaMH,
					'codPuntoVenta' 			=> $codPuntoVenta
				),
				'sujetoExcluido' 			=> array(
					'tipoDocumento' 			=> $dataSujetoExcluido->codigoTipoDocumentoPersonaMH,
					'numDocumento' 				=> $numDocumentoCliente,
					//'nrc' 						=> $nrcCliente,
					'nombre' 					=> substr($dataSujetoExcluido->nombreSujeto, 0, 249),
					'codActividad' 				=> $dataSujetoExcluido->codigoActividadPersonaMH,
					'descActividad' 			=> $dataSujetoExcluido->actividadEconomicaPersona,
					'direccion' 			=> array(
						'departamento'			=> $dataSujetoExcluido->codigoDepartamentoPersona,
						'municipio'				=> $dataSujetoExcluido->codigoMunicipioPersona,
						'complemento'			=> ($dataSujetoExcluido->direccionSujeto == "" ? "------" : substr($dataSujetoExcluido->direccionSujeto, 0, 199))
					),
					'telefono' 					=> $dataSujetoExcluido->telefonoSujeto,
					'correo' 					=> $dataSujetoExcluido->correoSujeto
				),
				//'otrosDocumentos' 			=> $otrosDocumentos,
				//'ventaTercero' 				=> $ventaTercero,
				'cuerpoDocumento' 			=> $cuerpoDocumento,
				'resumen' 					=> $resumenDocumento,
				//'extension' 				=> $extensionDTE,
				'apendice' 					=> $apendiceDTE,
				"selloRecibido"				=> $getNumerosDTE->selloRecibido,
				"firmaElectronica"			=> $getNumerosDTE->DTEfirmado
			);
		break;

		default:
			$respuesta = "No se encontró el tipo de DTE $_POST[tipoDTE]";
		break;
	}
	

	?>

<pre id="displayJson"><?php echo json_encode($jsonDTE, JSON_PRETTY_PRINT);?></pre>

<script>
    // Función para descargar el JSON
    function descargarJSON() {
        var jsonData = <?php echo json_encode($jsonDTE, JSON_PRETTY_PRINT); ?>;

        // Convertir el objeto JSON a una cadena
        var jsonString = JSON.stringify(jsonData, null, 2);

        // Crear un objeto Blob con el JSON
        var blob = new Blob([jsonString], { type: "application/json" });

        // Crear una URL para el Blob
        var url = window.URL.createObjectURL(blob);

        // Simular un clic en el enlace para descargar el JSON
        var link = document.getElementById('btnDescargarJSON');
        link.href = url;

        // Liberar el objeto URL creado después de un pequeño retraso para garantizar la descarga
        setTimeout(function() {
            window.URL.revokeObjectURL(url);
        }, 100);
    }

    // Agrega un evento click al enlace para llamar a la función de descarga
    document.getElementById('btnDescargarJSON').addEventListener('click', descargarJSON);
</script>