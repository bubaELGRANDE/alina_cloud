<?php 
	@session_start();
	ini_set('memory_limit', '-1');
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    require_once('../../../../../libraries/packages/php/vendor/phpqrcode/qrlib.php');
    require_once('../../../../../libraries/includes/logic/functions/funciones-generales.php');
	require_once('../../../../../libraries/packages/php/vendor/autoload.php');
	$tipoDTEId = base64_decode(urldecode($_REQUEST['tipoDTEId']));
    $fechaEmision = base64_decode(urldecode($_REQUEST['fechaEmision']));
    $facturaId = base64_decode(urldecode($_REQUEST['facturaId']));
    $tipoEnvioMail = base64_decode(urldecode($_REQUEST['tipoEnvioMail']));
    $yearBD = base64_decode(urldecode($_REQUEST['yearBD']));
	$flgCorreo = 0;
	$correoClienteEnvio = '';
	if (isset($_REQUEST['flgCorreo'])){
		$flgCorreo = base64_decode(urldecode($_REQUEST['flgCorreo']));
		$correoClienteEnvio = base64_decode(urldecode($_REQUEST['correoCliente']));
	}

	include('enviarDTECorreo.php');

	ob_start();

	$dataProveedorCliente = $cloud->row("
		SELECT proveedorUbicacionId FROM fel_factura$yearBD
		WHERE facturaId = ? AND flgDelete = ?
	", [$facturaId, 0]);

	if($dataProveedorCliente->proveedorUbicacionId > 0) {
		$dataDTEGeneral = $cloud->row("	SELECT
			f.facturaId AS facturaId,
			cat002.versionMH AS versionDTEMH,
			f.identificacionAmbiente AS identificacionAmbiente,
			cat002.codigoMH AS codigoDTEMH,
			cat002.tipoDTE as tipoDTE,
			cat003.codigoMH AS codigoModeloMH,
			cat003.tipoModeloFacturacion AS tipoModeloFacturacion,
			DATE_FORMAT(f.fechaEmision, '%d/%m/%Y') AS fechaEmisionFormat,
			f.fechaEmision AS fechaEmision,
			f.horaEmision AS horaEmision,
			f.tipoMoneda AS tipoMoneda,
			ffe.nitEmisor AS nitEmisor,
			ffe.nrcEmisor AS nrcEmisor,
			ffe.nombreEmisor AS nombreEmisor,
			ffe.nombreComercialEmisor AS nombreComercialEmisor,
			ffe.tipoEstablecimientoMH as tipoEstablecimientoMH,
			cat019emisor.codigoMh AS codigoActividadEmisorMH,
			cat019emisor.actividadEconomica AS actividadEconomicaEmisor,
			ffe.sucursalId AS sucursalId,
			s.codEstablecimientoMH AS codEstablecimientoMH,
			s.direccionSucursal AS direccionSucursal,
			s.sucursal as sucursal,
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
			cat016.condicionFactura as condicionFactura,
			f.periodoPlazo AS periodoPlazo,
			f.sujetoExcluidoId AS sujetoExcluidoId,
			cat029cliente.codigoMH AS codigoTipoPersonaMH,
			ffe.tipoEstablecimientoMH AS tipoEstablecimientoMH,
			ffe.puntoVentaMH AS puntoVentaMH,
			CASE
					WHEN fv.tipoVendedor = 'Empleado' THEN (
						SELECT nombreCompleto FROM view_expedientes vexp
						WHERE vexp.personaId = fv.personaId
						LIMIT 1
					)
					ELSE fv.mgNombreVendedor
				END AS nombreVendedor,
				(
					SELECT cat017.formaPago FROM fel_factura_pago$yearBD ffp 
					JOIN mh_017_forma_pago cat017 ON cat017.formaPagoId = ffp.formaPagoId
					WHERE ffp.facturaId = f.facturaId
					LIMIT 1
				) AS formaPagoMH
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
		LEFT JOIN mh_022_tipo_documento cat022cli ON cat022cli.tipoDocumentoClienteId = (
			CASE
				WHEN cli.tipoDocumento = 'NIT' THEN 1
				WHEN cli.tipoDocumento = 'DUI' THEN 2
				ELSE 3
			END
		)
		JOIN mh_019_actividad_economica cat019cliente ON cat019cliente.actividadEconomicaId = cli.actividadEconomicaId
		LEFT JOIN fel_factura_retenciones$yearBD ffr ON ffr.facturaId = f.facturaId
		JOIN cat_paises_municipios cpmscliente ON cpmscliente.paisMunicipioId = cliubi.paisMunicipioId
		JOIN cat_paises_departamentos cpdscliente ON cpdscliente.paisDepartamentoId = cpmscliente.paisDepartamentoId
		LEFT JOIN mh_016_condicion_factura cat016 ON cat016.condicionFacturaId = f.condicionFacturaId
		LEFT JOIN mh_029_tipo_persona cat029cliente ON cat029cliente.tipoPersonaId = (
			CASE
				WHEN cli.tipoProveedor = 'Empresa local' THEN 2
				ELSE 1
			END
		)
		LEFT JOIN fel_vendedores fv ON fv.vendedorId = f.vendedorId
			WHERE f.facturaId = ? AND f.flgDelete = ?
		", [$facturaId, 0]);

		// LEFT en mh_019_actividad_economica porque hay clientes que no tienen giro
		//Información del receptor
		// Dejar ya preparada la consulta del detalle y solo iterar para armar el cuerpoDocumento especifico para cada tipo de documento
		// Para no estar copiando y pegando las mismas consultas en cada case

		    $dataDTEDetalle = $cloud->rows("
		    	SELECT
				    pd.facturaDetalleId AS facturaDetalleId, 
				    pd.facturaId AS facturaId, 
				    ped.fechaEmision AS fechaEmision,
				    pd.productoId AS productoId, 
				    pd.codProductoFactura AS codProductoFactura,
				    pd.nombreProductoFactura AS nombreProductoFactura,
					pd.tipoItemMHId AS tipoItemMHId, 
					cat011.codigoMH AS codigoTipoItemMH,
				    pd.costoPromedio AS costoPromedio, 
				    pd.precioUnitario AS precioUnitario, 
				    pd.precioUnitarioIVA AS precioUnitarioIVA, 
				    pd.precioVenta AS precioVenta, 
				    pd.precioVentaIVA AS precioVentaIVA, 
				    pd.cantidadProducto AS cantidadProducto, 
				    pd.ivaUnitario AS ivaUnitario, 
				    pd.ivaTotal AS ivaTotal, 
				    pd.porcentajeDescuento AS porcentajeDescuento, 
				    pd.descuentoUnitario AS descuentoUnitario, 
				    pd.descuentoTotal AS descuentoTotal, 
				    pd.subTotalDetalle AS subTotalDetalle, 
				    pd.subTotalDetalleIVA AS subTotalDetalleIVA, 
				    pd.totalDetalle AS totalDetalle, 
				    pd.totalDetalleIVA AS totalDetalleIVA, 
				    pd.ivaRetenidoDetalle AS ivaRetenidoDetalle, 
				    pd.ivaPercibidoDetalle AS ivaPercibidoDetalle,
				    fr.tipoDTEId AS tipoDTEId,
				    fr.tipoGeneracionDocId AS tipoGeneracionDocId,
				    fr.numeroDocumentoRelacionada AS numeroDocumentoRelacionada,
				    fr.fechaEmisionRelacionada AS fechaEmisionRelacionada,
				    DATE_FORMAT(fr.fechaEmisionRelacionada, '%d/%m/%Y') AS fechaEmisionFormat,
				    dte.tipoDTE AS tipoDTE,
				    tg.tipoGeneracionDoc AS tipoGeneracionDoc,
				    pd.facturaRelacionadaId AS facturaRelacionadaId,
					p.unidadMedidaId AS unidadMedidaId,
					udm.codigoMH AS codigoUDMMH,
					udm.abreviaturaUnidadMedida as abreviaturaUnidadMedida
				FROM fel_factura_detalle$yearBD pd
			    JOIN fel_factura$yearBD ped ON ped.facturaId = pd.facturaId
				JOIN mh_011_tipo_item cat011 ON cat011.tipoItemMHId = pd.tipoItemMHId
			    JOIN inv_productos p ON p.productoId = pd.productoId
				LEFT JOIN cat_unidades_medida udm ON udm.unidadMedidaId = p.unidadMedidaId
			    JOIN fel_factura_relacionada$yearBD fr ON fr.facturaRelacionadaId = pd.facturaRelacionadaId
			    JOIN mh_002_tipo_dte dte ON dte.tipoDTEId = fr.tipoDTEId
			    JOIN mh_007_tipo_generacion_documento tg ON tg.tipoGeneracionDocId = fr.tipoGeneracionDocId
			    WHERE pd.facturaId = ? AND pd.flgDelete = ?
		    ", [$dataDTEGeneral->facturaId, 0]);
		// Dejar ya la consulta del pago y cobro de la factura
		// Está por separado a pesar que solo hay 1 registro para recordar que las formas de pago son múltiples
		// Pero de momento, Magic solo envía una forma de pago
		$dataDTEPago = $cloud->row("SELECT
				ffp.montoPago AS totalFactura,
				cat017.codigoMH AS codigoFormaPagoMH
			FROM fel_factura_pago$yearBD ffp 
			JOIN mh_017_forma_pago cat017 ON cat017.formaPagoId = ffp.formaPagoId
			WHERE ffp.facturaId = ? AND ffp.flgDelete = ?
		", [$dataDTEGeneral->facturaId, 0]);

		// Contactos de la sucursal
			$dataTelefonoSucursal = $cloud->row("SELECT contactoSucursal FROM cat_sucursales_contacto
				WHERE sucursalId = ? AND descripcionCSucursal = ? AND flgDelete = ?
				LIMIT 1
			", [$dataDTEGeneral->sucursalId, "Teléfono de sucursal", 0]);

			if($dataTelefonoSucursal) {
				$telefonoSucursal = $dataTelefonoSucursal->contactoSucursal;
			} else {
				// Default Casa Matriz
				$telefonoSucursal = "22312800";
			}

			$dataCorreoSucursal = $cloud->row("SELECT contactoSucursal FROM cat_sucursales_contacto
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
			$dataTelefonoCliente = $cloud->row("SELECT contactoCliente FROM fel_clientes_contactos
				WHERE clienteUbicacionId = ? AND tipoContactoId IN (2, 3, 4, 6, 7, 8, 10, 11, 12) AND flgDelete = ?
				ORDER BY clienteContactoId DESC
				LIMIT 1
			", [$dataDTEGeneral->clienteUbicacionId, 0]);

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
			$dataCorreoCliente = $cloud->row("SELECT contactoCliente FROM fel_clientes_contactos
				WHERE clienteUbicacionId = ? AND tipoContactoId IN (1, 9, 13) AND flgContactoPrincipal = ? AND flgDelete = ?
				ORDER BY clienteContactoId DESC
				LIMIT 1
			", [$dataDTEGeneral->clienteUbicacionId, 1, 0]);

			if($dataCorreoCliente) {
				$correoCliente = $dataCorreoCliente->contactoCliente;
			} else {
				// Default Casa Matriz
				$correoCliente = "-";
			}

			$datosCert = $cloud->row("SELECT facturaCertificacionId, numeroControl, codigoGeneracion, estadoCertificacion, selloRecibido, DTEfirmado
			FROM fel_factura_certificacion$yearBD 
			WHERE facturaId = ? AND estadoCertificacion = ? AND flgDelete = 0
			ORDER BY facturaCertificacionId DESC
			LIMIT 1", [$facturaId, "Certificado"]);

		 if (empty($dataDTEGeneral->tipoDocumentoCliente)){
			$documentoCliente = '<span class="labelsDTE">Número de documento:</span> ' . $dataDTEGeneral->numDocumentoCliente.'<br>';
		} else {
			$documentoCliente = '<span class="labelsDTE">'. $dataDTEGeneral->tipoDocumentoCliente .':</span> '. $dataDTEGeneral->numDocumentoCliente.'<br>';
		}
		$uuidDTE = $datosCert->codigoGeneracion;
		//$qrCode = "http://chart.googleapis.com/chart?chs=100x100&cht=qr&chl=https://admin.factura.gob.sv/consultaPublica?" . urlencode("ambiente=$dataDTEGeneral->identificacionAmbiente&codGen=$uuidDTE&fechaEmi=$dataDTEGeneral->fechaEmision");
		// $qrCode = 'http://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=https://admin.factura.gob.sv/consultaPublica?ambiente='.$dataDTEGeneral->identificacionAmbiente.'&codGen='.$datosCert->codigoGeneracion.'&fechaEmi='.$dataDTEGeneral->fechaEmision;
		// Texto para el código QR
		$url = "https://admin.factura.gob.sv/consultaPublica?ambiente=$dataDTEGeneral->identificacionAmbiente&codGen=$uuidDTE&fechaEmi=$dataDTEGeneral->fechaEmision";

		// Nombre del archivo de imagen del código QR
		$qrCode = "QR-$dataDTEGeneral->facturaId.png";

		// Generar el código QR
		QRcode::png($url, $qrCode);
		$html ='';

		$documentoRelacionadoMH = array();

				$dataFacturaRelacionada = $cloud->row("
		        SELECT
		        ffrel.facturaIdRelacionada AS facturaIdRelacionada,
		        cat002.codigoMH AS codigoDTERelacionadoMH,
		        cat002.tipoDTE as tipoDTE,
		        cat007.codigoMH AS codigoTipoGeneracionMH,
		        ffrel.numeroDocumentoRelacionada AS numeroDocumentoRelacionada,
		        ffrel.fechaEmisionRelacionada AS fechaEmisionRelacionada
		    FROM fel_factura_relacionada$yearBD ffrel
		    LEFT JOIN fel_factura$yearBD f ON f.facturaId = ffrel.facturaIdRelacionada
		    LEFT JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = ffrel.tipoDTEId
		    JOIN mh_007_tipo_generacion_documento cat007 ON cat007.tipoGeneracionDocId = ffrel.tipoGeneracionDocId
					WHERE ffrel.facturaId = ? AND ffrel.flgDelete = ?
				", [$dataDTEGeneral->facturaId, 0]);

				if($dataFacturaRelacionada->facturaIdRelacionada == 0) {
					// Fue físico, dejar crédito fiscal
					$tipoDocumentoRelacionado = "03";
					$numeroR = $dataFacturaRelacionada->numeroDocumentoRelacionada;
				} else {
					// Debería ser crédito fiscal también pero si más adelante una nota de crédito puede afectar otro tipo de documento, queda declarado
					$tipoDocumentoRelacionado = $dataFacturaRelacionada->codigoDTERelacionadoMH;
					$dataCodGeneracionR = $cloud->row("
						SELECT codigoGeneracion FROM fel_factura_certificacion$yearBD
						WHERE facturaId = ? AND (estadoCertificacion = ? OR descripcionMsg = 'RECIBIDO') AND flgDelete = ?
						ORDER BY facturaCertificacionId DESC
						LIMIT 1
					",[$dataFacturaRelacionada->facturaIdRelacionada, "Certificado", 0]);
					$numeroR = $dataCodGeneracionR->codigoGeneracion;
				}

				// El script que convierte asigna en automático, pero si es otro número, dejo la variable afuera para que se pueda validar y afectar
				$numeroDocumentoRelacionada = $numeroR;
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

		        if (is_null($dataFacturaRelacionada)){
		            $docRel = '-';
		        } else {
		            $docRel = '<b>Tipo de documento:</b> <br>' . $tipoDocumentoRelacionado . '<br>
		                    <b>N° de documento: </b> <br>'. $numeroDocumentoRelacionada .'<br>
		                    <b>Fecha de documento: </b> <br>'. $fechaEmisionRelacionada;
		        }



		$html .='<!DOCTYPE html>
		                <html lang="es">
		                    <head>
		                        <meta charset="utf-8">
		                        <title>'.$datosCert->codigoGeneracion.'</title>
		                        <style>
		                            body {
		                                margin: 0;
		                                font-family: system-ui, -apple-system, "Segoe UI", "Helvetica Neue", sans-serif";
		                                font-size: 0.7rem;
		                                font-weight: normal;
		                                line-height: 1.5;
		                                text-align: left;
		                                background-color: #fff;
		                            }
		                         
									table {
		                               // border: 1px solid #004a87;
		                                width: 100%;
		                                vertical-align: top;
		                            }
									/* table tr td {
		                                border: 1px solid #000;
		                            } */
									  .titulo-DTE{
										font-size: 1.2rem;
										font-weight: bold;
									  }
									  .labelsDTE{
										font-weight: bold;
									  }
									  .header-fact{
										background: #004a87;
										color: #fff;
										font-size: 1.5rem;
										text-align: center;
										font-weight: bold;
										padding: 0 0 .5rem;
										width: 100%;
										
									  }
									  .header-fact span{
										font-size: 0.9rem;
									  }
									  .header-fact-comp{
										background: #ced4da;
										padding: 0.5rem;
										width: 100%;
									  }
		                              .headerSM{
		                                font-size: 0.7rem;
		                              }
		                        </style>
		                    </head>
		                    <body>
		                        <htmlpageheader name="firstpage" style="display:none">
		                        </htmlpageheader>

		                        <htmlpageheader name="otherpages" style="display:none">
		                            <table>
		                                <tbody>
		                                <tr>
		                                    <td></td>
		                                    <td align="center">
		                                        <span>DOCUMENTO TRIBUTARIO ELECTRÓNICO</span><br>
		                                        <b>'.$dataDTEGeneral->tipoDTE.'</b>
		                                    </td>
		                                    <td></td>
		                                </tr>
		                                </tbody>
		                            </table>
		                            <table>
		                                <tr>
		                                    <td class="headerSM" width="50%">
		                                        <b>Código generación:</b> '.$datosCert->codigoGeneracion.'<br>
		                                        <b>Sello recepción:</b> '.$datosCert->selloRecibido.'<br>
		                                        <b>Número de control:</b> '.$datosCert->numeroControl.'<br>
		                                    </td>
		                                    <td class="headerSM" align="right" width="50%">
		                                        <b>'.$dataDTEGeneral->nombreEmisor.'</b><br>
		                                        <b>Fecha de emisión:</b> '.$dataDTEGeneral->fechaEmisionFormat.'<br>
		                                        <b>Hora de emisión:</b> '.$dataDTEGeneral->horaEmision.'<br>
		                                    </td>
		                                </tr>
		                                </tbody>
		                            </table>
		                        </htmlpageheader>
		                        <sethtmlpageheader name="firstpage" value="on" show-this-page="1" />
		                        <sethtmlpageheader name="otherpages" value="on" />
								
		                        <table>
									<tbody>
									<tr>
										<td width="49%">
									  		<p>
											  <img width="180" src="../../../../../libraries/resources/images/logos/indupal-logo.png" alt="Indupal">
											</p>
											<div class="titulo-DTE text-center">'.$dataDTEGeneral->nombreEmisor.'</div>
											<div class="subtitulo-DTE">'.$dataDTEGeneral->actividadEconomicaEmisor.'</div>
											<table>
												<tbody>
												<tr>
													<td>
													<div class="labelsDTE">
														NIT: <br>
														NRC: <br>
														Sucursal: <br>
														Dirección: <br>
														Teléfono: <br>
														Correo: <br>
														Tipo establecimiento: <br>
														Sitio web: <br>
													</div>
													</td>
													<td>
														'.$dataDTEGeneral->nitEmisor.'<br>
														'.$dataDTEGeneral->nrcEmisor.'<br>
														'.$dataDTEGeneral->sucursal.'<br>
														'.$dataDTEGeneral->direccionSucursal.'<br>
														'.$telefonoSucursal.'<br>
														'.$correoSucursal.'<br>
														'.$dataDTEGeneral->tipoEstablecimientoMH.'<br>
														www.indupal.com
													</td>
												</tr>
												</tbody>
											</table>
										</td>
										<td width="2%"></td>
										<td width="49%">
											<table>
												<tbody>
												<tr>
													<td class="header-fact">
														<span>DOCUMENTO TRIBUTARIO ELECTRÓNICO</span><br>
														'.$dataDTEGeneral->tipoDTE.'
													</td>
												</tr>
												</tbody>
											</table>
											<table>
												<tbody>
												<tr>
													<td width="40%">
														<div class="labelsDTE">
															Código generación: <br>
															Sello recepción: <br>
															Número de control: <br>
														</div>
													</td>
													<td width="60%">
														'.$datosCert->codigoGeneracion.'<br>
														'.$datosCert->selloRecibido.'<br>
														'.$datosCert->numeroControl.'<br>
													</td>
												</tr>
												</tbody>
											</table>
											<table>
												<tbody>
												<tr>
													<td width="40%">
														<img src="'.$qrCode.'">
													</td>
													<td width="60%">
		                                                <b>Fecha de emisión:</b> '.$dataDTEGeneral->fechaEmisionFormat.'<br>
		                                                <b>Hora de emisión:</b> '.$dataDTEGeneral->horaEmision.'<br>
														<b>Modelo facturación</b>: '.$dataDTEGeneral->tipoModeloFacturacion.'<br>
														<b>Tipo transmisión:</b> Normal<br>
														<b>Moneda:</b> '.$dataDTEGeneral->tipoMoneda.'<br>
		                                                <b>Forma de pago:</b> '.$dataDTEGeneral->formaPagoMH.'
													</td>
												</tr>
												</tbody>
											</table>
										</td>
									</tr>
									</tbody>
								</table>
							<div class="header-fact-comp labelsDTE mb-2">
								Información del receptor
							</div>
							<table>
							<tbody>
								<tr>
									<td>
										<span class="labelsDTE">Nombre:</span> '.$dataDTEGeneral->nombreProveedor.'<br>
										<span class="labelsDTE">Actividad:</span> '.$dataDTEGeneral->actividadEconomicaCliente.'<br>
										<span class="labelsDTE">Dirección:</span> '.$dataDTEGeneral->direccionProveedorUbicacion.'<br>
									</td>
									<td>
									  	'.$documentoCliente.'
										<span class="labelsDTE">NRC:</span> '.$dataDTEGeneral->nrcProveedor.'<br>
										<span class="labelsDTE">Teléfono:</span> '.$telefonoCliente.'<br>
										<span class="labelsDTE">Correo:</span> '.$correoCliente.'
									</td>
								</tr>
							</tbody>
							</table>
							<div class="header-fact-comp labelsDTE mb-2">Cuerpo del documento</div>
							<table class="table table-borderless">
								<thead>
								<tr>
									<th>#</th>
									<th>Tipo de documento relacionado</th>
									<th>N° de documento</th>
									<th>Fecha de emisión</th>
									<th>Descripción</th>
									<th>Monto sujeto a retención</th>
									<th>IVA retenido<br></th>
								</tr>
								</thead>
								<tbody>
							';

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

		                    $codigoTipoItemMH = 1;
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
												cat002.tipoDTE AS tipoDTE,
												ffr.tipoGeneracionDocId AS tipoGeneracionDocId,
												cat007.codigoMH AS tipoGeneracionDoc,
												ffr.numeroDocumentoRelacionada AS numeroDocumentoRelacionada,
												ffr.fechaEmisionRelacionada AS fechaEmisionRelacionada
											FROM fel_factura_relacionada$yearBD ffr 
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

		                        $html .='<tr>
									<td align="center">' . (int)($numItem + 1) . '</td>
									<td>'.$dataFacturaQueRelaciona->tipoDTE.'</td>
									<td>'.$dataFacturaQueRelaciona->numeroDocumentoRelacionada.'</td>
									<td>'.$dataFacturaQueRelaciona->fechaEmisionRelacionada.'</td>
									<td>' . $dteDetalle->nombreProductoFactura . '</td>
									<td align="right">' . number_format((float)$dteDetalle->totalDetalle, 2, ".", ",") . '</td>
									<td align="right">' . number_format((float)$dteDetalle->ivaRetenidoDetalle, 2, ".", ",") . '</td>
								</tr>';
		                        $numItem++;
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
							
		                            if ($dataDTEGeneral->codigoCondicionOperacion == 2){
		                                $condicionFact = 'Crédito a';
		                                $periodoPlazo = $dataDTEGeneral->periodoPlazo . ' días';
		                            } else {
		                                $condicionFact = 'Contado';
		                                $periodoPlazo = '';
		                            }
						$html .= '	
		                <tr>
		                <tr>
		                    <td colspan="5"></td>
		                    <td colspan="2"><hr></td>
		                </tr>
						<tr>
								<td colspan="5">
		                            <b>Total monto sujeto a retención en letras:</b> '.dineroLetras($totalPagar, "decimal") . " dólares".'
		                        </td>
								<td class="labelsDTE">
		                            Total monto sujeto a retención:
								</td>
								<td align="right">
									'.number_format((float)round($subTotalVentasResumen + $totalIVAResumen, 2), 2, ".", ",").'
								</td>
							</tr>
		                    <tr>
								<td colspan="5"><b>Total IVA retenido en letras:</b> '.dineroLetras((float)number_format($dataDTEGeneral->ivaRetenido, 2), "decimal") . " dólares".'</td>
								<td class="labelsDTE">Total IVA retenido: </td>
								<td align="right">'.number_format((float)round((float)round($dataDTEGeneral->ivaRetenido, 2), 2), 2, ".", ",").'</td>
							</tr>
							</table>
							</body>
							</html>';

							$nrcCliente = ($dataDTEGeneral->nrcProveedor == "" ? NULL : str_replace("-", "", $dataDTEGeneral->nrcProveedor));

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
									'correo' 					=> $correoSucursal
									// No aplica para notas de crédito
									//'codEstableMH' 			=> $codEstableMH,
									//'codEstable' 				=> $codEstable,
									//'codPuntoVentaMH' 		=> $codPuntoVentaMH,
									//'codPuntoVenta' 			=> $codPuntoVenta
								),
								'receptor' 					=> array(
									'nit' 						=> $nitCliente,
									'nrc' 						=> $nrcProveedor,
									'nombre' 					=> substr($dataDTEGeneral->nombreProveedor, 0, 249),
									'codActividad' 				=> $codActividadEconomicaReceptor,
									'descActividad' 			=> $descripcionActividadEconomicaReceptor,
									'nombreComercial' 			=> substr(($dataDTEGeneral->nombreComercialCliente == "" ? $dataDTEGeneral->nombreProveedor : $dataDTEGeneral->nombreComercialCliente), 0, 149),
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
								"selloRecibido"				=> $datosCert->selloRecibido,
								"firmaElectronica"			=> $datosCert->DTEfirmado
							);
			$json =	json_encode($jsonDTE, JSON_PRETTY_PRINT);

			$mpdf = new \Mpdf\Mpdf([
				'setAutoTopMargin' => 'stretch',
				'autoMarginPadding' => 5,
				'format' => 'Letter',
			]);

		    // $mpdf->SetHTMLHeader('<div style="text-align: center;">Encabezado Personalizado</div>', 'O');
		    $mpdf->setFooter('|{PAGENO} de {nbpg}|');
			
			$mpdf->shrink_tables_to_fit = 1;
			$mpdf->WriteHTML($html);
			ob_end_clean();
			unlink($qrCode);

			if (isset($_REQUEST['flgCorreo'])){
				$pdf = $mpdf->Output($datosCert->codigoGeneracion.'.pdf', 'S');
				$nombrePDF = $uuidDTE.'.pdf';
				$nombreJson = $uuidDTE.'.json';
				// AVISO: Concatenar las variables afuera de las comillas, sino no se dibujan
				$asuntoCorreo = "Documento tributario electrónico - " . $dataDTEGeneral->nombreEmisor;
				$textoCorreo = "
					<p>
						Estimado ".$dataDTEGeneral->nombreProveedor.",<br><br>
						Le compartimos su documento tributario electrónico:<br>
						$uuidDTE
					</p>
					<p>
						Si no es necesario, no imprima este documento, de esta forma contribuye con el medio ambiente.
					</p>
					<p>
						Por favor no responda a este correo ya que fue generado de forma automática y no recibirá respuesta. Cualquier duda o consulta puede hacerla comunicándose con el Equipo de Desarrollo al correo: desarrollo@indupal.com.
					</p>
				";
				// var_dump($correoClienteEnvio);

				$correo = explode(",", $correoClienteEnvio);

			    if($yearBD == "") {
			        // FEL actual
			        $anioTxt = date("Y");
			    } else {
			        $anioTxt = str_replace("_", "", $yearBD);
			    }

				foreach($correo as $correoE) {
					enviarCorreo($asuntoCorreo, $textoCorreo, $correoE, $pdf, $nombrePDF, $json, $nombreJson);
					$insert = [
						"facturaId" 			=> $facturaId,
						"tipoEnvio" 			=> $tipoEnvioMail,
						"correo" 				=> $correoE,
						"anio" 					=> $anioTxt
					];
					$bitFELCorreoId = $cloud->insert("bit_fel_correos", $insert);
					//echo "success";
				} 
				
			} else {
				
				$mpdf->Output($datosCert->codigoGeneracion.'.pdf', 'I');
			}
	} else {
		$dataDTEGeneral = $cloud->row("	SELECT
			f.facturaId AS facturaId,
			cat002.versionMH AS versionDTEMH,
			f.identificacionAmbiente AS identificacionAmbiente,
			cat002.codigoMH AS codigoDTEMH,
			cat002.tipoDTE as tipoDTE,
			cat003.codigoMH AS codigoModeloMH,
			cat003.tipoModeloFacturacion AS tipoModeloFacturacion,
			DATE_FORMAT(f.fechaEmision, '%d/%m/%Y') AS fechaEmisionFormat,
			f.fechaEmision AS fechaEmision,
			f.horaEmision AS horaEmision,
			f.tipoMoneda AS tipoMoneda,
			ffe.nitEmisor AS nitEmisor,
			ffe.nrcEmisor AS nrcEmisor,
			ffe.nombreEmisor AS nombreEmisor,
			ffe.nombreComercialEmisor AS nombreComercialEmisor,
			ffe.tipoEstablecimientoMH as tipoEstablecimientoMH,
			cat019emisor.codigoMh AS codigoActividadEmisorMH,
			cat019emisor.actividadEconomica AS actividadEconomicaEmisor,
			ffe.sucursalId AS sucursalId,
			s.codEstablecimientoMH AS codEstablecimientoMH,
			s.direccionSucursal AS direccionSucursal,
	        s.sucursal as sucursal,
			cpds.codigoMH AS codigoDepartamentoSucursalMH,
			cpms.codigoMH AS codigoMunicipioSucursalMH,
			cli.clienteId AS clienteId,
			cliubi.clienteUbicacionId AS clienteUbicacionId,
			cat022cli.tipoDocumentoCliente AS tipoDocumentoCliente,
			cli.numDocumento AS numDocumentoCliente,
			cli.nrcCliente AS nrcCliente,
			f.nombreClienteFactura as nombreClienteFactura,
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
	        cat016.condicionFactura as condicionFactura,
			f.periodoPlazo AS periodoPlazo,
			f.sujetoExcluidoId AS sujetoExcluidoId,
			cat029cliente.codigoMH AS codigoTipoPersonaMH,
			ffe.tipoEstablecimientoMH AS tipoEstablecimientoMH,
			ffe.puntoVentaMH AS puntoVentaMH,
	        CASE
	                WHEN fv.tipoVendedor = 'Empleado' THEN (
	                    SELECT nombreCompleto FROM view_expedientes vexp
	                    WHERE vexp.personaId = fv.personaId
	                    LIMIT 1
	                )
	                ELSE fv.mgNombreVendedor
	            END AS nombreVendedor,
	            (
	                SELECT cat017.formaPago FROM fel_factura_pago$yearBD ffp 
	                JOIN mh_017_forma_pago cat017 ON cat017.formaPagoId = ffp.formaPagoId
	                WHERE ffp.facturaId = f.facturaId
	                LIMIT 1
	            ) AS formaPagoMH
		FROM fel_factura$yearBD f
		JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = f.tipoDTEId
		JOIN mh_003_tipo_modelo cat003 ON cat003.tipoModeloMHId = f.tipoModeloMHId
		JOIN fel_factura_emisor$yearBD ffe ON ffe.facturaId = f.facturaId
		JOIN mh_019_actividad_economica cat019emisor ON cat019emisor.actividadEconomicaId = ffe.actividadEconomicaId
		JOIN cat_sucursales s ON s.sucursalId = ffe.sucursalId
		JOIN cat_paises_municipios cpms ON cpms.paisMunicipioId = s.paisMunicipioId
		JOIN cat_paises_departamentos cpds ON cpds.paisDepartamentoId = cpms.paisDepartamentoId
		LEFT JOIN fel_clientes_ubicaciones cliubi ON cliubi.clienteUbicacionId = f.clienteUbicacionId
		LEFT JOIN fel_clientes cli ON cli.clienteId = cliubi.clienteId
		LEFT JOIN mh_022_tipo_documento cat022cli ON cat022cli.tipoDocumentoClienteId = cli.tipoDocumentoMHId
		LEFT JOIN mh_019_actividad_economica cat019cliente ON cat019cliente.actividadEconomicaId = cli.actividadEconomicaId
		JOIN fel_factura_retenciones$yearBD ffr ON ffr.facturaId = f.facturaId
		JOIN cat_paises_municipios cpmscliente ON cpmscliente.paisMunicipioId = cliubi.paisMunicipioId
		JOIN cat_paises_departamentos cpdscliente ON cpdscliente.paisDepartamentoId = cpmscliente.paisDepartamentoId
		JOIN mh_016_condicion_factura cat016 ON cat016.condicionFacturaId = f.condicionFacturaId
		LEFT JOIN mh_029_tipo_persona cat029cliente ON cat029cliente.tipoPersonaId = cli.tipoPersonaMHId
	    LEFT JOIN fel_vendedores fv ON fv.vendedorId = f.vendedorId
		WHERE f.facturaId = ? AND f.flgDelete = ?
	", [$facturaId, 0]);
	// LEFT en mh_019_actividad_economica porque hay clientes que no tienen giro

	// Dejar ya preparada la consulta del detalle y solo iterar para armar el cuerpoDocumento especifico para cada tipo de documento
	// Para no estar copiando y pegando las mismas consultas en cada case
	$dataDTEDetalle = $cloud->rows("SELECT
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
			udm.abreviaturaUnidadMedida as abreviaturaUnidadMedida
		FROM fel_factura_detalle$yearBD fd
		JOIN mh_011_tipo_item cat011 ON cat011.tipoItemMHId = fd.tipoItemMHId
		JOIN inv_productos prod ON prod.productoId = fd.productoId
		LEFT JOIN cat_unidades_medida udm ON udm.unidadMedidaId = prod.unidadMedidaId
		WHERE fd.facturaId = ? AND fd.flgDelete = ?
	", [$dataDTEGeneral->facturaId, 0]);

	// Dejar ya la consulta del pago y cobro de la factura
	// Está por separado a pesar que solo hay 1 registro para recordar que las formas de pago son múltiples
	// Pero de momento, Magic solo envía una forma de pago
	$dataDTEPago = $cloud->row("SELECT
			ffp.montoPago AS totalFactura,
			cat017.codigoMH AS codigoFormaPagoMH
		FROM fel_factura_pago$yearBD ffp 
		JOIN mh_017_forma_pago cat017 ON cat017.formaPagoId = ffp.formaPagoId
		WHERE ffp.facturaId = ? AND ffp.flgDelete = ?
	", [$dataDTEGeneral->facturaId, 0]);

	// Contactos de la sucursal
		$dataTelefonoSucursal = $cloud->row("SELECT contactoSucursal FROM cat_sucursales_contacto
			WHERE sucursalId = ? AND descripcionCSucursal = ? AND flgDelete = ?
			LIMIT 1
		", [$dataDTEGeneral->sucursalId, "Teléfono de sucursal", 0]);

		if($dataTelefonoSucursal) {
			$telefonoSucursal = $dataTelefonoSucursal->contactoSucursal;
		} else {
			// Default Casa Matriz
			$telefonoSucursal = "22312800";
		}

		$dataCorreoSucursal = $cloud->row("SELECT contactoSucursal FROM cat_sucursales_contacto
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
		$dataTelefonoCliente = $cloud->row("SELECT contactoCliente FROM fel_clientes_contactos
			WHERE clienteUbicacionId = ? AND tipoContactoId IN (2, 3, 4, 6, 7, 8, 10, 11, 12) AND flgDelete = ?
			ORDER BY clienteContactoId DESC
			LIMIT 1
		", [$dataDTEGeneral->clienteUbicacionId, 0]);

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
		$dataCorreoCliente = $cloud->row("SELECT contactoCliente FROM fel_clientes_contactos
			WHERE clienteUbicacionId = ? AND tipoContactoId IN (1, 9, 13) AND flgContactoPrincipal = ? AND flgDelete = ?
			ORDER BY clienteContactoId DESC
			LIMIT 1
		", [$dataDTEGeneral->clienteUbicacionId, 1, 0]);

		if($dataCorreoCliente) {
			$correoCliente = $dataCorreoCliente->contactoCliente;
		} else {
			// Default Casa Matriz
			$correoCliente = "-";
		}

		$datosCert = $cloud->row("SELECT facturaCertificacionId, numeroControl, codigoGeneracion, estadoCertificacion, selloRecibido, DTEfirmado
		FROM fel_factura_certificacion$yearBD 
		WHERE facturaId = ? AND estadoCertificacion = ? AND flgDelete = 0
		ORDER BY facturaCertificacionId DESC
		LIMIT 1", [$facturaId, "Certificado"]);

	 if (empty($dataDTEGeneral->tipoDocumentoCliente)){
		$documentoCliente = '<span class="labelsDTE">Número de documento:</span> ' . $dataDTEGeneral->numDocumentoCliente.'<br>';
	} else {
		$documentoCliente = '<span class="labelsDTE">'. $dataDTEGeneral->tipoDocumentoCliente .':</span> '. $dataDTEGeneral->numDocumentoCliente.'<br>';
	}
	$uuidDTE = $datosCert->codigoGeneracion;
	//$qrCode = "http://chart.googleapis.com/chart?chs=100x100&cht=qr&chl=https://admin.factura.gob.sv/consultaPublica?" . urlencode("ambiente=$dataDTEGeneral->identificacionAmbiente&codGen=$uuidDTE&fechaEmi=$dataDTEGeneral->fechaEmision");
	// $qrCode = 'http://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=https://admin.factura.gob.sv/consultaPublica?ambiente='.$dataDTEGeneral->identificacionAmbiente.'&codGen='.$datosCert->codigoGeneracion.'&fechaEmi='.$dataDTEGeneral->fechaEmision;
	// Texto para el código QR
	$url = "https://admin.factura.gob.sv/consultaPublica?ambiente=$dataDTEGeneral->identificacionAmbiente&codGen=$uuidDTE&fechaEmi=$dataDTEGeneral->fechaEmision";

	// Nombre del archivo de imagen del código QR
	$qrCode = "QR-$dataDTEGeneral->facturaId.png";

	// Generar el código QR
	QRcode::png($url, $qrCode);
	$html ='';

	$documentoRelacionadoMH = array();

			$dataFacturaRelacionada = $cloud->row("
	        SELECT
	        ffrel.facturaIdRelacionada AS facturaIdRelacionada,
	        cat002.codigoMH AS codigoDTERelacionadoMH,
	        cat002.tipoDTE as tipoDTE,
	        cat007.codigoMH AS codigoTipoGeneracionMH,
	        ffrel.numeroDocumentoRelacionada AS numeroDocumentoRelacionada,
	        ffrel.fechaEmisionRelacionada AS fechaEmisionRelacionada
	    FROM fel_factura_relacionada$yearBD ffrel
	    LEFT JOIN fel_factura$yearBD f ON f.facturaId = ffrel.facturaIdRelacionada
	    LEFT JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = ffrel.tipoDTEId
	    JOIN mh_007_tipo_generacion_documento cat007 ON cat007.tipoGeneracionDocId = ffrel.tipoGeneracionDocId
				WHERE ffrel.facturaId = ? AND ffrel.flgDelete = ?
			", [$dataDTEGeneral->facturaId, 0]);

			if($dataFacturaRelacionada->facturaIdRelacionada == 0) {
				// Fue físico, dejar crédito fiscal
				$tipoDocumentoRelacionado = "03";
				$numeroR = $dataFacturaRelacionada->numeroDocumentoRelacionada;
			} else {
				// Debería ser crédito fiscal también pero si más adelante una nota de crédito puede afectar otro tipo de documento, queda declarado
				$tipoDocumentoRelacionado = $dataFacturaRelacionada->codigoDTERelacionadoMH;
				$dataCodGeneracionR = $cloud->row("
					SELECT codigoGeneracion FROM fel_factura_certificacion$yearBD
					WHERE facturaId = ? AND (estadoCertificacion = ? OR descripcionMsg = 'RECIBIDO') AND flgDelete = ?
					ORDER BY facturaCertificacionId DESC
					LIMIT 1
				",[$dataFacturaRelacionada->facturaIdRelacionada, "Certificado", 0]);
				$numeroR = $dataCodGeneracionR->codigoGeneracion;
			}

			// El script que convierte asigna en automático, pero si es otro número, dejo la variable afuera para que se pueda validar y afectar
			$numeroDocumentoRelacionada = $numeroR;
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

	        if (is_null($dataFacturaRelacionada)){
	            $docRel = '-';
	        } else {
	            $docRel = '<b>Tipo de documento:</b> <br>' . $tipoDocumentoRelacionado . '<br>
	                    <b>N° de documento: </b> <br>'. $numeroDocumentoRelacionada .'<br>
	                    <b>Fecha de documento: </b> <br>'. $fechaEmisionRelacionada;
	        }

			$nombreCliente = '';
			if (!empty($dataDTEGeneral->nombreClienteFactura)){
				$nombreCliente = $dataDTEGeneral->nombreClienteFactura;
			} else {
				$nombreCliente = $dataDTEGeneral->nombreCliente;
			}


	$html .='<!DOCTYPE html>
	                <html lang="es">
	                    <head>
	                        <meta charset="utf-8">
	                        <title>'.($datosCert ? $datosCert->codigoGeneracion : 'DTE-'.$facturaId).'</title>
	                        <style>
	                            body {
	                                margin: 0;
	                                font-family: system-ui, -apple-system, "Segoe UI", "Helvetica Neue", sans-serif";
	                                font-size: 0.7rem;
	                                font-weight: normal;
	                                line-height: 1.5;
	                                text-align: left;
	                                background-color: #fff;
	                            }
	                         
								table {
	                               // border: 1px solid #004a87;
	                                width: 100%;
	                                vertical-align: top;
	                            }
								/* table tr td {
	                                border: 1px solid #000;
	                            } */
								  .titulo-DTE{
									font-size: 1.2rem;
									font-weight: bold;
								  }
								  .labelsDTE{
									font-weight: bold;
								  }
								  .header-fact{
									background: #004a87;
									color: #fff;
									font-size: 1.5rem;
									text-align: center;
									font-weight: bold;
									padding: 0 0 .5rem;
									width: 100%;
									
								  }
								  .header-fact span{
									font-size: 0.9rem;
								  }
								  .header-fact-comp{
									background: #ced4da;
									padding: 0.5rem;
									width: 100%;
								  }
	                              .headerSM{
	                                font-size: 0.7rem;
	                              }
	                        </style>
	                    </head>
	                    <body>
	                        <htmlpageheader name="firstpage" style="display:none">
	                        </htmlpageheader>

	                        <htmlpageheader name="otherpages" style="display:none">
	                            <table>
	                                <tbody>
	                                <tr>
	                                    <td></td>
	                                    <td align="center">
	                                        <span>DOCUMENTO TRIBUTARIO ELECTRÓNICO</span><br>
	                                        <b>'.$dataDTEGeneral->tipoDTE.'</b>
	                                    </td>
	                                    <td></td>
	                                </tr>
	                                </tbody>
	                            </table>
	                            <table>
	                                <tr>
	                                    <td class="headerSM" width="50%">
	                                        <b>Código generación:</b> '.$datosCert->codigoGeneracion.'<br>
	                                        <b>Sello recepción:</b> '.$datosCert->selloRecibido.'<br>
	                                        <b>Número de control:</b> '.$datosCert->numeroControl.'<br>
	                                    </td>
	                                    <td class="headerSM" align="right" width="50%">
	                                        <b>'.$dataDTEGeneral->nombreEmisor.'</b><br>
	                                        <b>Fecha de emisión:</b> '.$dataDTEGeneral->fechaEmisionFormat.'<br>
	                                        <b>Hora de emisión:</b> '.$dataDTEGeneral->horaEmision.'<br>
	                                    </td>
	                                </tr>
	                                </tbody>
	                            </table>
	                        </htmlpageheader>
	                        <sethtmlpageheader name="firstpage" value="on" show-this-page="1" />
	                        <sethtmlpageheader name="otherpages" value="on" />
							
	                        <table>
								<tbody>
								<tr>
									<td width="49%">
								  		<p>
										  <img width="180" src="../../../../../libraries/resources/images/logos/indupal-logo.png" alt="Indupal">
										</p>
										<div class="titulo-DTE text-center">'.$dataDTEGeneral->nombreEmisor.'</div>
										<div class="subtitulo-DTE">'.$dataDTEGeneral->actividadEconomicaEmisor.'</div>
										<table>
											<tbody>
											<tr>
												<td>
												<div class="labelsDTE">
													NIT: <br>
													NRC: <br>
													Sucursal: <br>
													Dirección: <br>
													Teléfono: <br>
													Correo: <br>
													Tipo establecimiento: <br>
													Sitio web: <br>
												</div>
												</td>
												<td>
													'.$dataDTEGeneral->nitEmisor.'<br>
													'.$dataDTEGeneral->nrcEmisor.'<br>
													'.$dataDTEGeneral->sucursal.'<br>
													'.$dataDTEGeneral->direccionSucursal.'<br>
													'.$telefonoSucursal.'<br>
													'.$correoSucursal.'<br>
													'.$dataDTEGeneral->tipoEstablecimientoMH.'<br>
													www.indupal.com
												</td>
											</tr>
											</tbody>
										</table>
									</td>
									<td width="2%"></td>
									<td width="49%">
										<table>
											<tbody>
											<tr>
												<td class="header-fact">
													<span>DOCUMENTO TRIBUTARIO ELECTRÓNICO</span><br>
													'.$dataDTEGeneral->tipoDTE.'
												</td>
											</tr>
											</tbody>
										</table>
										<table>
											<tbody>
											<tr>
												<td width="40%">
													<div class="labelsDTE">
														Código generación: <br>
														Sello recepción: <br>
														Número de control: <br>
													</div>
												</td>
												<td width="60%">
													'.$datosCert->codigoGeneracion.'<br>
													'.$datosCert->selloRecibido.'<br>
													'.$datosCert->numeroControl.'<br>
												</td>
											</tr>
											</tbody>
										</table>
										<table>
											<tbody>
											<tr>
												<td width="40%">
													<img src="'.$qrCode.'">
												</td>
												<td width="60%">
	                                                <b>Fecha de emisión:</b> '.$dataDTEGeneral->fechaEmisionFormat.'<br>
	                                                <b>Hora de emisión:</b> '.$dataDTEGeneral->horaEmision.'<br>
													<b>Modelo facturación</b>: '.$dataDTEGeneral->tipoModeloFacturacion.'<br>
													<b>Tipo transmisión:</b> Normal<br>
													<b>Moneda:</b> '.$dataDTEGeneral->tipoMoneda.'<br>
	                                                <b>Forma de pago:</b> '.$dataDTEGeneral->formaPagoMH.'
												</td>
											</tr>
											</tbody>
										</table>
									</td>
								</tr>
								</tbody>
							</table>
						<div class="header-fact-comp labelsDTE mb-2">
							Información del receptor
						</div>
						<table>
						<tbody>
							<tr>
								<td width="50%">
									<span class="labelsDTE">Nombre:</span> '.$nombreCliente.'<br>
									<span class="labelsDTE">Actividad:</span> '.$dataDTEGeneral->actividadEconomicaCliente.'<br>
									<span class="labelsDTE">Dirección:</span> '.$dataDTEGeneral->direccionClienteUbicacion.'<br>
								</td>
								<td>
								  	'.$documentoCliente.'
									<span class="labelsDTE">NRC:</span> '.$dataDTEGeneral->nrcCliente.'<br>
									<span class="labelsDTE">Teléfono:</span> '.$telefonoCliente.'<br>
									<span class="labelsDTE">Correo:</span> '.$correoCliente.'
								</td>
							</tr>
						</tbody>
						</table>
						<div class="header-fact-comp labelsDTE mb-2">Cuerpo del documento</div>
						<table class="table table-borderless">
							<thead>
							<tr>
								<th>#</th>
								<th>Tipo de documento relacionado</th>
								<th>N° de documento</th>
								<th>Fecha de emisión</th>
								<th>Descripción</th>
								<th>Monto sujeto a retención</th>
								<th>IVA retenido<br></th>
							</tr>
							</thead>
							<tbody>
						';

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

	                    $codigoTipoItemMH = 1;
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

	                        $html .='<tr>
								<td align="center">' . (int)($numItem + 1) . '</td>
								<td>'.$dataFacturaRelacionada->tipoDTE.'</td>
								<td>'.$numeroDocumentoRelacionada.'</td>
								<td>'.$fechaEmisionRelacionada.'</td>
								<td>' . $dteDetalle->nombreProductoFactura . '</td>
								<td align="right">' . number_format((float)$dteDetalle->totalDetalle, 2, ".", ",") . '</td>
								<td align="right">' . number_format((float)$dataDTEGeneral->ivaRetenido, 2, ".", ",") . '</td>
							</tr>';
	                        $numItem++;
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
					$dataComplementos = $cloud->rows("
						SELECT 
							tipoComplemento, complementoFactura 
						FROM fel_factura_complementos$yearBD
						WHERE facturaId = ? AND flgDelete = ?
					", [$facturaId, 0]);

					$apendiceDTE = array();
					$numApendice = 0;

					foreach ($dataComplementos as $complemento) {
						$apendiceDTE[$numApendice] = array(
							"campo" 				=> $complemento->tipoComplemento,
							"etiqueta" 				=> $complemento->tipoComplemento,
							"valor" 				=> $complemento->complementoFactura
						);
						$numApendice++;
					}

					if($numApendice == 0) {
						// No hubo complemento o anexo
						// Setear NULL para que permita certificar
						$apendiceDTE = NULL;
					} else {
						// Se agregó apendice al DTE y se va a certificar
					}
			
	                            if ($dataDTEGeneral->codigoCondicionOperacion == 2){
	                                $condicionFact = 'Crédito a';
	                                $periodoPlazo = $dataDTEGeneral->periodoPlazo . ' días';
	                            } else {
	                                $condicionFact = 'Contado';
	                                $periodoPlazo = '';
	                            }
					$html .= '	
	                <tr>
	                <tr>
	                    <td colspan="5"></td>
	                    <td colspan="2"><hr></td>
	                </tr>
					<tr>
							<td colspan="5">
	                            <b>Total monto sujeto a retención en letras:</b> '.dineroLetras($totalPagar, "decimal") . " dólares".'
	                        </td>
							<td class="labelsDTE">
	                            Total monto sujeto a retención:
							</td>
							<td align="right">
								'.number_format((float)round($subTotalVentasResumen + $totalIVAResumen, 2), 2, ".", ",").'
							</td>
						</tr>
	                    <tr>
							<td colspan="5"><b>Total IVA retenido en letras:</b> '.dineroLetras((float)number_format($dataDTEGeneral->ivaRetenido, 2), "decimal") . " dólares".'</td>
							<td class="labelsDTE">Total IVA retenido: </td>
							<td align="right">'.number_format((float)round((float)round($dataDTEGeneral->ivaRetenido, 2), 2), 2, ".", ",").'</td>
						</tr>
						</table>
						</body>
						</html>';

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

						// mh_004_tipo_operacion donde 1 = Transmisión normal
						// Para la contingencia, en su respectivo case se cambiará este valor
						$tipoOperacionMH = 1;
						$tipoContingenciaMH = NULL;
						$motivoContingenciaMH = NULL;

						// Variable generales
						$documentoRelacionadoMH = NULL;
						$otrosDocumentos = NULL;
						$ventaTercero = NULL;
						
						$jsonDTE = array(
							'identificacion' 			=> array (
								'version' 					=> (int)$dataDTEGeneral->versionDTEMH,
								'ambiente' 					=> $dataDTEGeneral->identificacionAmbiente,
								'tipoDte' 					=> $dataDTEGeneral->codigoDTEMH,
								'numeroControl' 			=> $dataDTEGeneral->$numeroControl,
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
							"selloRecibido"				=> $datosCert->selloRecibido,
							"firmaElectronica"			=> $datosCert->DTEfirmado
						);
		$json =	json_encode($jsonDTE, JSON_PRETTY_PRINT);

		$mpdf = new \Mpdf\Mpdf([
			'setAutoTopMargin' => 'stretch',
			'autoMarginPadding' => 5,
			'format' => 'Letter',
		]);

	    // $mpdf->SetHTMLHeader('<div style="text-align: center;">Encabezado Personalizado</div>', 'O');
	    $mpdf->setFooter('|{PAGENO} de {nbpg}|');
		
		$mpdf->shrink_tables_to_fit = 1;
		$mpdf->WriteHTML($html);
		ob_end_clean();
		unlink($qrCode);

		if (isset($_REQUEST['flgCorreo'])){
			$pdf = $mpdf->Output($datosCert->codigoGeneracion.'.pdf', 'S');
			$nombrePDF = $uuidDTE.'.pdf';
			$nombreJson = $uuidDTE.'.json';
			// AVISO: Concatenar las variables afuera de las comillas, sino no se dibujan
			$asuntoCorreo = "Documento tributario electrónico - " . $dataDTEGeneral->nombreEmisor;
			$textoCorreo = "
				<p>
					Estimado ".$dataDTEGeneral->nombreCliente.",<br><br>
					Le compartimos su documento tributario electrónico:<br>
					$uuidDTE
				</p>
				<p>
					Si no es necesario, no imprima este documento, de esta forma contribuye con el medio ambiente.
				</p>
				<p>
					Por favor no responda a este correo ya que fue generado de forma automática y no recibirá respuesta. Cualquier duda o consulta puede hacerla comunicándose con el Equipo de Desarrollo al correo: desarrollo@indupal.com.
				</p>
			";
			// var_dump($correoClienteEnvio);

			$correo = explode(",", $correoClienteEnvio);

		    if($yearBD == "") {
		        // FEL actual
		        $anioTxt = date("Y");
		    } else {
		        $anioTxt = str_replace("_", "", $yearBD);
		    }
			
			foreach($correo as $correoE) {
				enviarCorreo($asuntoCorreo, $textoCorreo, $correoE, $pdf, $nombrePDF, $json, $nombreJson);
				$insert = [
					"facturaId" 			=> $facturaId,
					"tipoEnvio" 			=> $tipoEnvioMail,
					"correo" 				=> $correoE,
					"anio" 					=> $anioTxt
				];
				$bitFELCorreoId = $cloud->insert("bit_fel_correos", $insert);
				//echo "success";
			} 
			
		} else {
			if($datosCert) {
				$mpdf->Output($datosCert->codigoGeneracion.'.pdf', 'I');
			} else {
				$mpdf->Output('DTE-'.$facturaId.'.pdf', 'I');
			}
		}

	}
?>