<?php
/*
	$insert = [
		'campo1'		=> "hola xd",
		'campo2'     => "hola 2222222",
	];
	$cloud->insert('nombre_tabla', $insert);
*/
if (isset($_SESSION["usuarioId"]) && isset($operation)) {
	switch ($operation) {
		case "periodo-contable":
			/*
				POST:
				mes
				anio
			*/
			$mesesAnio = array(
				"Enero",
				"Febrero",
				"Marzo",
				"Abril",
				"Mayo",
				"Junio",
				"Julio",
				"Agosto",
				"Septiembre",
				"Octubre",
				"Noviembre",
				"Diciembre"
			);

			$mes = isset($_POST["mes"]) ? intval($_POST["mes"]) : 0;
			$mesNombre = "";
			if ($mes >= 1 && $mes <= 12) {
				$mesNombre = $mesesAnio[$mes - 1];
			} elseif ($mes == 13) {
				$mesNombre = "Cierre";
			} else {
				echo "Mes inválido";
			}

			$existeMunicipio = $cloud->count(
				"
					SELECT mes,mesNombre,anio
					FROM desarrollo_cloud.conta_partidas_contables_periodos 
					WHERE mes =? AND anio= ? AND flgDelete = ? ",
				[$_POST["mes"], $_POST["anio"], 0]
			);

			if ($existeMunicipio > 0) {
				echo "El período ya esta disponible en el año (" . $_POST['anio'] . ")";
			} else {
				$insert = [
					"mes" => $mes,
					"mesNombre" => $mesNombre,
					"anio" => $_POST['anio'],
					"estadoPeriodoPartidas" => "Activo"
				];
				$cloud->insert("conta_partidas_contables_periodos", $insert);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Agregó un nuevo período contable:" . $mesNombre . "-" . $_POST['anio']);
				echo "success";
			}
			break;
		case "parametrizacion-comision":
			/*
				POST:
				hiddenFormData
				typeOperation
				operation
				correlativoWrapper = Es el "id" en el que se quedó
				conteoWrappers = Es el número de filas que se crearon
				linea = array
				rangoInicio = array
				rangoFin = array
				porcentajePagar = array
			*/
			// Iterar cada linea que se seleccionó
			foreach ($_POST["linea"] as $lineaId) {
				$dataNombreLinea = $cloud->row("
						SELECT
							CONCAT('(', abreviatura, ') ', linea) AS nombreLinea
						FROM temp_cat_lineas
						WHERE lineaId = ?
					", [$lineaId]);

				$i = 0; // Para obtener los rangos en la posicion especifica del array
				foreach ($_POST["rangoInicio"] as $rangoInicio) {
					$rangoPorcentajeInicio = $_POST['rangoInicio'][$i];
					$rangoPorcentajeFin = $_POST['rangoFin'][$i];
					$porcentajePago = $_POST['porcentajePagar'][$i];

					$insert = [
						'lineaId' => $lineaId,
						'rangoPorcentajeInicio' => $rangoPorcentajeInicio,
						'rangoPorcentajeFin' => $rangoPorcentajeFin,
						'porcentajePago' => $porcentajePago
					];
					$cloud->insert('conta_comision_porcentaje_lineas', $insert);
					$i += 1;
				}
				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó la parametrización de porcentajes de comisión de la Linea: " . $dataNombreLinea->nombreLinea . ", ");
			}
			echo "success";
			break;

		case "calcular-comision":
			/*
				POST:					
				hiddenFormData
				typeOperation
				operation
				mes
				anio
				adjunto
				flgRecalculo
			*/
			$mesesAnio = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

			$archivoNombre = "(" . $mesesAnio[$_POST['mes']] . "-" . $_POST['anio'] . ") " . $_FILES['adjunto']['name'];

			// Validar si ya existe el periodo que se está intentando cargar
			$existePeriodo = $cloud->count("
					SELECT
						numMes, 
						mes, 
						anio, 
						archivoCargado
					FROM conta_comision_pagar_periodo
					WHERE numMes = ? AND anio = ? AND flgDelete = '0'
				", [$_POST['mes'], $_POST['anio']]);
			if (($existePeriodo == 0 && $_POST['flgRecalculo'] == 0) || $_POST['flgRecalculo'] == 1) {
				if ($_POST['flgRecalculo'] == 1) {
					// Eliminar el periodo anterior y el calculo anterior completo
					$dataPeriodoAnterior = $cloud->row("
							SELECT
								comisionPagarPeriodoId
							FROM conta_comision_pagar_periodo
							WHERE numMes = ? AND anio = ? AND flgDelete = '0'
						", [$_POST['mes'], $_POST['anio']]);

					// Delete periodo
					$cloud->deleteById('conta_comision_pagar_periodo', "comisionPagarPeriodoId ", $dataPeriodoAnterior->comisionPagarPeriodoId);
					// Delete calculo de comisiones
					$cloud->deleteById('conta_comision_pagar_calculo', 'comisionPagarPeriodoId', $dataPeriodoAnterior->comisionPagarPeriodoId);
				} else {
					// No es recalculo
				}
				$filename = "../../../../libraries/resources/files/txt/comisiones/";

				$ubicacion = $filename . $archivoNombre;
				$flgSubir = 1;
				$archivoFormato = pathinfo($ubicacion, PATHINFO_EXTENSION);

				$formatosPermitidos = array("txt");

				if (!in_array(strtolower($archivoFormato), $formatosPermitidos)) {
					$flgSubir = 0;
				} else {
					$flgSubir = 1;
				}

				if ($flgSubir == 0) {
					// Validación de formato nuevamente por si se evade la de Javascript
					echo "El archivo seleccionado no coincide con un formato válido. Por favor vuelva a seleccionar un archivo con formato válido.";
				} else {
					// Verificar si existe
					$n = 1;
					$originalNombre = $archivoNombre;
					while ($n > 0) {
						if (file_exists($ubicacion)) {
							$archivoNombre = "(" . $n . ") " . $originalNombre;
							$ubicacion = $filename . $archivoNombre;
							$n += 1;
						} else {
							// No existe, se mantiene el flujo normal
							$n = 0;
						}
					}

					/* Upload file */
					if (move_uploaded_file($_FILES['adjunto']['tmp_name'], $ubicacion)) {
						$n = 0;
						$index = 0;
						$numAbonos = 0;
						$arrayInsert = [];
						$parametrizacionIVA = 1.13;

						// Iterar cada FILA del archivo
						$file = new SplFileObject($ubicacion);

						while (!$file->eof()) {
							$n += 1;

							$row = $file->fgets();
							if ($row <> '') {
								// Substring de las posiciones específicas para cada columna
								$identificador = trim(utf8_decode(substr($row, 0, 1)));
								$codCliente = trim(utf8_decode(substr($row, 2, 12)));
								$nombreCliente = str_replace("'", " ", trim(utf8_decode(substr($row, 15, 20))));
								$codEmpleado = trim(utf8_decode(substr($row, 36, 3)));
								$nombreVendedor = trim(utf8_decode(substr($row, 40, 50)));
								$tipoFactura = trim(utf8_decode(substr($row, 91, 20)));
								$correlativoFactura = trim(utf8_decode(substr($row, 112, 6)));
								$fechaFactura = trim(utf8_decode(substr($row, 119, 8)));
								$sucursalFactura = trim(utf8_decode(substr($row, 128, 20)));
								$formaPago = trim(utf8_decode(substr($row, 149, 10)));
								$codProducto = trim(utf8_decode(substr($row, 160, 14)));
								$nombreProducto = str_replace("'", " ", trim(utf8_decode(substr($row, 175, 40))));
								$lineaProducto = trim(utf8_decode(substr($row, 216, 2)));
								$precioUnitario = trim(utf8_decode(substr($row, 219, 13)));
								$costo = trim(utf8_decode(substr($row, 233, 13)));
								$cantidad = trim(utf8_decode(substr($row, 247, 9)));
								$precioFacturado = trim(utf8_decode(substr($row, 257, 13)));
								$precioXcantidad = trim(utf8_decode(substr($row, 271, 13)));
								$montoFactura = trim(utf8_decode(substr($row, 285, 13)));
								$fechaAbono = trim(utf8_decode(substr($row, 299, 8)));
								$montoAbono = trim(utf8_decode(substr($row, 308, 13)));
								$tipoCliente = trim(utf8_decode(substr($row, 322, 1)));
								$codVendedor = trim(utf8_decode(substr($row, 324, 3)));
								$codTipoFactura = trim((utf8_decode(substr($row, 328, 4))));
								$ivaPercibido = trim((utf8_decode(substr($row, 333, 9))));
								$ivaRetenido = trim((utf8_decode(substr($row, 343, 9))));

								// Calcular las comisiones
								// Insertar a la tabla general con todo y el calculo de comisiones ya realizado
								// Omitiendo las sucursales
								// Solicitud Heidi 23-12-2022: quitar oficina linea industrial
								// Solicitud Heidi 06-07-2023: Calcular también para sucursales
								//if(!($nombreVendedor == "OFICINA CASA MATRIZ" || $nombreVendedor == "OFICINA SUC. KARCHER CENTER" || $nombreVendedor == "DISTRIBUCION KARCHER" || $nombreVendedor == "TALLER HIDROPAL" || $nombreVendedor == "OFICINA SUC. HIDROPAL" || $nombreVendedor == "OFICINA SUC. SANTA ANA" || $nombreVendedor == "OFICINA SUC. SAN MIGUEL" || $nombreVendedor == "OFICINA SUC. NOGUEIRA" || $nombreVendedor == "MAYOREO KARCHER" || $nombreVendedor == "TALLER" || $nombreVendedor == "OFICINA AGROPAL SAN MIGUEL")) {
								// Format fecha MAGIC = dd/mm/yy a MYSQL yyyy-mm-dd
								$arrayFechaFactura = explode('/', $fechaFactura);
								/*
									0 = dia
									1 = mes
									2 = anio
									pero reemplazar 2 por $_POST anio
								*/
								$fechaFactura = $_POST['anio'] . '-' . $arrayFechaFactura[1] . '-' . $arrayFechaFactura[0];

								$arrayFechaAbono = explode('/', $fechaAbono);
								/*
									0 = dia
									1 = mes
									2 = anio
									pero reemplazar 2 por $_POST anio
								*/

								if (isset($arrayFechaAbono[1]) || isset($arrayFechaAbono[2])) {
									$fechaAbono = $_POST['anio'] . '-' . $arrayFechaAbono[1] . '-' . $arrayFechaAbono[0];
								} else {
									die('
												No se encontró la fecha de abono
												<br>
												(' . $mesesAnio[$_POST['mes']] . '-' . $_POST['anio'] . ').
												<br><br>
												N° Factura: ' . $correlativoFactura . '<br>
												Fecha de la factura: ' . date("d/m/Y", strtotime($fechaFactura)) . '<br>
												Fecha del abono: ' . date("d/m/Y", strtotime($fechaAbono)) . '<br>
												Cliente: ' . $nombreCliente . '<br>
												Vendedor: ' . $nombreVendedor . '<br>
												Producto: ' . $nombreProducto . '
											');
								}

								// Verificar que las fechas sean del periodo correcto
								if (!($arrayFechaFactura[1] == $_POST['mes']) && $identificador == "F") {
									// La fecha de la factura no coincide con el periodo seleccionado
									die('
												La fecha de la factura que se ha exportado de MAGIC no coincide con el periodo seleccionado
												<br>
												(' . $mesesAnio[$_POST['mes']] . '-' . $_POST['anio'] . ').
												<br><br>
												N° Factura: ' . $correlativoFactura . '<br>
												Fecha de la factura: ' . date("d/m/Y", strtotime($fechaFactura)) . '<br>
												Cliente: ' . $nombreCliente . '<br>
												Vendedor: ' . $nombreVendedor . '
											');
								} else if (!($arrayFechaAbono[1] == $_POST['mes']) && $identificador == "A") {
									// La fecha del abono no coincide con el periodo seleccionado
									die('
												La fecha del abono que se ha exportado de MAGIC no coincide con el periodo seleccionado
												<br>
												(' . $mesesAnio[$_POST['mes']] . '-' . $_POST['anio'] . ').
												<br><br>
												N° Factura: ' . $correlativoFactura . '<br>
												Fecha del abono: ' . date("d/m/Y", strtotime($fechaAbono)) . '<br>
												Cliente: ' . $nombreCliente . '<br>
												Vendedor: ' . $nombreVendedor . '
											');
								} else {
									// Verificar que la linea esté parametrizada
									$existeParametrizacionLinea = $cloud->count("
												SELECT
													pl.comisionPorcentajeLineaId
												FROM conta_comision_porcentaje_lineas pl
												JOIN temp_cat_lineas l ON l.lineaId = pl.lineaId
												WHERE abreviatura = ? AND pl.flgDelete = '0'
											", [$lineaProducto]);
									if ($existeParametrizacionLinea == 0) {
										$existeLineaCatalogo = $cloud->count("
													SELECT
														linea
													FROM temp_cat_lineas
													WHERE abreviatura = ? AND flgDelete = '0'
												", [$lineaProducto]);

										if ($existeLineaCatalogo == 0) {
											die('
														La linea ' . $lineaProducto . ' no ha sido agregada al catálogo de líneas de Cloud, por favor notifique al departamento de Informática para agregarla y luego se le notificará cuando ya pueda realizar la parametrización de línea (condiciones).
														<br><br>
														N° Factura: ' . $correlativoFactura . '<br>
														Fecha de la factura: ' . date("d/m/Y", strtotime($fechaFactura)) . '<br>
														Cliente: ' . $nombreCliente . '<br>
														Vendedor: ' . $nombreVendedor . '
													');
										} else {
											$dataNombreLinea = $cloud->row("
														SELECT
															linea
														FROM temp_cat_lineas
														WHERE abreviatura = ? AND flgDelete = '0'
													", [$lineaProducto]);
											die('
														La línea ' . $dataNombreLinea->linea . ' (' . $lineaProducto . ') no ha sido parametrizada, por lo que el cálculo de comisiones de este periodo no ha sido realizado.<br><br>
														Por favor, realice la parametrización de esta línea desde el menú:<br>
														1. Contabilidad > Comisiones > Parametrización.<br>
														2. Clic en el botón "+ PARAMETRIZACIÓN DE LÍNEAS".
													');
										}
									} else {
										/*
											CÓDIGOS DE TIPO DE DOCUMENTO EN MAGIC										
											01 FACTURA CONSUMIDOR FINAL = TRAE IVA
											02 CREDITOS FISCAL
											03 EXPORTACION
											04 FACTURAS EXENTAS
											05 NOTAS DE DEBITO
											00 NOTAS DE CREDITO

											CÓDIGOS DE TIPOS DE CLIENTE EN MAGIC
											C CON RETENCIÓN
											E ESTADO ?????
											F FINAL
											G GRANDE
											M MEDIANO
											P PEQUEÑO
											S SIN PERSONA ????
											T TICKET ????
											X EXENTO
										*/

										$flgTipoCalculo = 0;
										switch ($codTipoFactura) {
											case '1':
												$tipoFactura = "FACTURA DE CONSUMIDOR FINAL";
												$flgTipoCalculo = "IVA"; // Precios CON IVA
												break;

											case '2':
												$tipoFactura = "CRÉDITO FISCAL";
												$flgTipoCalculo = "NIVA"; // Precio SIN IVA
												break;

											case '3':
												$tipoFactura = "FACTURA DE EXPORTACIÓN";
												$flgTipoCalculo = "NIVA"; // Precio SIN IVA
												break;

											case '4':
												$tipoFactura = "FACTURA EXENTA";
												$flgTipoCalculo = "NIVA"; // Precio SIN IVA
												break;

											case '5':
												$tipoFactura = "NOTA DE DÉBITO";
												$flgTipoCalculo = "NIVA"; // Precio SIN IVA
												break;

											case '8':
												$tipoFactura = "TICKET";
												$flgTipoCalculo = "IVA";
												break;

											default:
												$tipoFactura = "N/A";
												break;
										}

										$porcentajeDescuento = 0.00;
										$paramLineaId = 0;
										$paramRangoPorcentajeInicio = 0.00;
										$paramRangoPorcentajeFin = 0.00;
										$paramPorcentajePago = 0.00;
										$calculoPrecioUnitario = 0.00;
										$calculoPrecioFacturado = 0.00;
										$calculoPrecioXCantidad = 0.00;

										if ($flgTipoCalculo == "IVA") { // CONSUMIDOR FINAL
											// Quitar el IVA
											$calculoPrecioUnitario = $precioUnitario / $parametrizacionIVA;
											//$precioUnitario = $calculoPrecioUnitario;
											$calculoPrecioFacturado = $precioFacturado / $parametrizacionIVA;
											$calculoPrecioXCantidad = $precioXcantidad / $parametrizacionIVA;
										} else if ($flgTipoCalculo == "NIVA") { // CRÉDITO FISCAL, EXPORTACIÓN, EXENTA
											// Los precios ya vienen sin iva
											// Estos se están mandando con iva, fix en quemado...
											if ($tipoFactura == "CRÉDITO FISCAL" || $tipoFactura == "NOTA DE DÉBITO" || $tipoFactura == "FACTURA EXENTA" || $tipoFactura == "FACTURA DE EXPORTACIÓN") {
												// EL precio unitario en creditos fiscales lo está mandando con iva, fix...
												$calculoPrecioUnitario = $precioUnitario / $parametrizacionIVA;
												$precioUnitario = $calculoPrecioUnitario;
											} else {
												$calculoPrecioUnitario = $precioUnitario;
											}

											$calculoPrecioFacturado = $precioFacturado;
											$calculoPrecioXCantidad = $precioXcantidad;
										} else {
											// N/A
										}
										if ($calculoPrecioUnitario == $calculoPrecioFacturado) {
											// No tuvo descuento
											$porcentajeDescuento = 0.00;
										} else {
											// Esta condición es porque hay servicios de reparación, mantenimiento que tienen precioUnitario 0.00 y da error al dividir entre cero
											if ($calculoPrecioUnitario == 0) {
												// Asi que se asume que NO tuvo porcentaje de descuento y se le pagará comisión por el servicio completo que facturó
												$porcentajeDescuento = 0.00;
											} else {
												// Calcular el % de descuento
												$porcentajeDescuento = round(((($calculoPrecioUnitario - $calculoPrecioFacturado) / $calculoPrecioUnitario) * 100), 2);
											}
										}

										if ($calculoPrecioFacturado == 0) {
											// Si se facturó en cero son regalias y no hay porque iterar las condiciones, setear todo a cero
											$comisionPagar = 0.00;
										} else if ($nombreVendedor == "OFICINA CASA MATRIZ" || $nombreVendedor == "OFICINA SUC. KARCHER CENTER" || $nombreVendedor == "DISTRIBUCION KARCHER" || $nombreVendedor == "TALLER HIDROPAL" || $nombreVendedor == "OFICINA SUC. HIDROPAL" || $nombreVendedor == "OFICINA SUC. SANTA ANA" || $nombreVendedor == "OFICINA SUC. SAN MIGUEL" || $nombreVendedor == "OFICINA SUC. NOGUEIRA" || $nombreVendedor == "MAYOREO KARCHER" || $nombreVendedor == "TALLER" || $nombreVendedor == "OFICINA AGROPAL SAN MIGUEL" || $nombreVendedor == 'OFICINA INDUPAL 25' || $nombreVendedor == 'OFICINA LINEA INDUSTRIAL') {
											// Solicitud 06-07-2023: Calcular también para sucursales a 0.00
											$comisionPagar = 0.00;
										} else {
											// Traer las condiciones parametrizadas de la linea
											$dataCondicionesLinea = $cloud->rows("
														SELECT
															pl.comisionPorcentajeLineaId AS comisionPorcentajeLineaId,
															l.lineaId AS lineaId,
														    pl.rangoPorcentajeInicio AS rangoPorcentajeInicio,
														    pl.rangoPorcentajeFin AS rangoPorcentajeFin,
														    pl.porcentajePago AS porcentajePago
														FROM conta_comision_porcentaje_lineas pl
														JOIN temp_cat_lineas l ON l.lineaId = pl.lineaId
														WHERE abreviatura = ? AND pl.flgDelete = '0'
													", [$lineaProducto]);
											// El break no detiene el bucle, así que uso variable
											$flgEncontroParametrizacion = 0;
											foreach ($dataCondicionesLinea as $dataCondicionesLinea) {
												if ($porcentajeDescuento < 0 && $flgEncontroParametrizacion == 0) {
													// TOMAR LA PRIMER CONDICIÓN SI EL PORCENTAJE DE DESCUENTO ES NEGATIVO
													// PORCENTAJE DE DESCUENTO NEGATIVO = LO VENDIÓ A PRECIO MAYOR QUE EL UNITARIO
													$paramLineaId = $dataCondicionesLinea->lineaId;
													$paramRangoPorcentajeInicio = $dataCondicionesLinea->rangoPorcentajeInicio;
													$paramRangoPorcentajeFin = $dataCondicionesLinea->rangoPorcentajeFin;
													$paramPorcentajePago = $dataCondicionesLinea->porcentajePago;
													$flgEncontroParametrizacion = 1;
												} else {
													if (($porcentajeDescuento >= $dataCondicionesLinea->rangoPorcentajeInicio && $porcentajeDescuento <= $dataCondicionesLinea->rangoPorcentajeFin) && $flgEncontroParametrizacion == 0) {
														$paramLineaId = $dataCondicionesLinea->lineaId;
														$paramRangoPorcentajeInicio = $dataCondicionesLinea->rangoPorcentajeInicio;
														$paramRangoPorcentajeFin = $dataCondicionesLinea->rangoPorcentajeFin;
														$paramPorcentajePago = $dataCondicionesLinea->porcentajePago;
														$flgEncontroParametrizacion = 1;
													} else {
														// Pasar a la otra condición
													}
												}
											}
											if ($flgEncontroParametrizacion == 0) {
												// No se encontró condición, no pagar comisión
												$comisionPagar = 0.00;
											} else {
												// Dividir el porcentaje "entero" entre 100 para su equivalente %
												$comisionPagar = $calculoPrecioXCantidad * ($paramPorcentajePago / 100);
												$calculoMontoAbono = 0.00;
											}
										}

										if ($nombreVendedor == "JUVEN CORDOVA" || $nombreVendedor == "EVER RAMOS" || $nombreVendedor == "REYNALDO RODRIGUEZ" || $nombreVendedor == "ALEXANDER HERNANDEZ" || $nombreVendedor == "EDGARDO OCAMPO") {
											if ($nombreVendedor == "EDGARDO OCAMPO") {
												if ($nombreCliente == "FERRETERIA LA PALMA," || $codCliente == "9231-2") {
													if ($porcentajeDescuento < 50) {
														$paramRangoPorcentajeInicio = 0.00;
														$paramRangoPorcentajeFin = 49.99;
														$paramPorcentajePago = 2;
														$comisionPagar = $calculoPrecioXCantidad * ($paramPorcentajePago / 100);
													} else {
														// No aplica a comisión, condiciones normales (si aplica)
													}
												} else if ($lineaProducto == "ST" || $lineaProducto == "KA" || $lineaProducto == "CR" || $lineaProducto == "AD" || $lineaProducto == "AQ" || $lineaProducto == "BI" || $lineaProducto == "BR" || $lineaProducto == "DG" || $lineaProducto == "DU" || $lineaProducto == "DY" || $lineaProducto == "FE" || $lineaProducto == "FT" || $lineaProducto == "FV" || $lineaProducto == "HD" || $lineaProducto == "KL" || $lineaProducto == "LD" || $lineaProducto == "PD" || $lineaProducto == "PR" || $lineaProducto == "SA" || $lineaProducto == "TC" || $lineaProducto == "TS" || $lineaProducto == "TY" || $lineaProducto == "VZ" || $lineaProducto == "AE" || $lineaProducto == "AF" || $lineaProducto == "AG" || $lineaProducto == "AM" || $lineaProducto == "AS" || $lineaProducto == "BA" || $lineaProducto == "FG" || $lineaProducto == "FM" || $lineaProducto == "FQ" || $lineaProducto == "GB" || $lineaProducto == "HE" || $lineaProducto == "HG" || $lineaProducto == "KG" || $lineaProducto == "KI" || $lineaProducto == "KM" || $lineaProducto == "KO" || $lineaProducto == "KP" || $lineaProducto == "MP" || $lineaProducto == "MQ" || $lineaProducto == "MT" || $lineaProducto == "NG" || $lineaProducto == "NK" || $lineaProducto == "PA" || $lineaProducto == "TR") {
													if ($porcentajeDescuento < 6) {
														$paramRangoPorcentajeInicio = 0.00;
														$paramRangoPorcentajeFin = 5.00;
														$paramPorcentajePago = 10;
														$comisionPagar = $calculoPrecioXCantidad * ($paramPorcentajePago / 100);
													} else if ($porcentajeDescuento >= 6 && $porcentajeDescuento <= 10) {
														$paramRangoPorcentajeInicio = 6.01;
														$paramRangoPorcentajeFin = 10.00;
														$paramPorcentajePago = 5;
														$comisionPagar = $calculoPrecioXCantidad * ($paramPorcentajePago / 100);
													} else if ($porcentajeDescuento > 10 && $porcentajeDescuento < 50) {
														$paramRangoPorcentajeInicio = 10.01;
														$paramRangoPorcentajeFin = 49.99;
														$paramPorcentajePago = 2;
														$comisionPagar = $calculoPrecioXCantidad * ($paramPorcentajePago / 100);
													} else {
														// Condiciones de linea normal
													}
												} else {
													// Condiciones normales
												}
											} else if ($nombreCliente == "CORPORACION DE TIEND" || $codCliente == "161107-2" || $nombreCliente == "FERRETERIA EPA, S.A." || $codCliente == "190088-8" || $nombreCliente == "ALMACENES VIDRI, S.A" || $codCliente == "2-7" || $nombreCliente == "FREUND DE EL SALVADO" || $codCliente == "41-8") {
												if ($lineaProducto == "ST") {
													if ($porcentajeDescuento <= 50) {
														$paramRangoPorcentajeInicio = 0.00;
														$paramRangoPorcentajeFin = 50.00;
														$paramPorcentajePago = 1;
														$comisionPagar = $comisionPagar = $calculoPrecioXCantidad * ($paramPorcentajePago / 100);
													} else {
														// No aplica a comisión, condiciones normales (si aplica)
													}
												} else {
													// Vendió otra linea, condiciones normales
												}
											} else {
												// Es otro cliente, aplicar condiciones normales
											}
										} else {
											// No son condiciones especiales
										}

										if (($formaPago == "CONTADO" && $identificador == "F") || $identificador == "A") {
											$insert = [
												'flgIdentificador' => $identificador,
												'numRegistroCliente' => $codCliente,
												'nombreCliente' => $nombreCliente,
												'tipoCliente' => $tipoCliente,
												'codEmpleado' => $codEmpleado,
												'codVendedor' => $codVendedor,
												'nombreEmpleado' => $nombreVendedor,
												'codTipoFactura' => $codTipoFactura,
												'tipoFactura' => $tipoFactura,
												'correlativoFactura' => $correlativoFactura,
												'fechaFactura' => $fechaFactura,
												'sucursalFactura' => $sucursalFactura,
												'formaPago' => $formaPago,
												'codProductoFactura' => $codProducto,
												'nombreProducto' => $nombreProducto,
												'lineaProducto' => $lineaProducto,
												'precioUnitario' => $precioUnitario,
												'precioCosto' => $costo,
												'cantidadProducto' => $cantidad,
												'precioFacturado' => $precioFacturado,
												'totalVenta' => $precioXcantidad,
												'totalFactura' => $montoFactura,
												'ivaPercibido' => $ivaPercibido,
												'ivaRetenido' => $ivaRetenido,
												'fechaAbono' => $fechaAbono,
												'totalAbono' => $montoAbono,
												'totalAbonoCalculo' => $calculoMontoAbono,
												'porcentajeDescuento' => $porcentajeDescuento,
												'paramLineaId' => $paramLineaId,
												'paramRangoPorcentajeInicio' => $paramRangoPorcentajeInicio,
												'paramRangoPorcentajeFin' => $paramRangoPorcentajeFin,
												'paramPorcentajePago' => $paramPorcentajePago,
												'comisionPagar' => $comisionPagar
											];

											// No guardar todavía ya que se deben evaluar las demás condiciones, lineas, parametrización
											$arrayInsert[$index] = $insert;
											$index += 1;
										} else {
											// ES VENTA AL CREDITO, NO GUARDAR
										}
									}
								}
								// Solicitud 06-07-2023: Calcular también para sucursales
								// Heidi Reyes
								//} else {
								// Era una sucursal
								//}
							} else {
								// Última línea
							}
						}

						// No hubo ningún die, guardar el periodo que se creó
						$insert = [
							'numMes' => $_POST['mes'],
							'mes' => $mesesAnio[$_POST['mes']],
							'anio' => $_POST['anio'],
							'archivoCargado' => $archivoNombre
						];
						$comisionPagarPeriodoId = $cloud->insert('conta_comision_pagar_periodo', $insert);

						for ($i = 0; $i < count($arrayInsert); $i++) {
							// Agregar al array el id del periodo creado
							$arrayInsert[$i] += ['comisionPagarPeriodoId' => $comisionPagarPeriodoId];
							$comisionPagarCalculoId = $cloud->insert('conta_comision_pagar_calculo', $arrayInsert[$i]);
						}

						// CALCULAR LOS ABONOS
						$dataIterarAbonos = $cloud->rows("
								SELECT 
									nombreEmpleado,
								    nombreCliente,
								    correlativoFactura,
								    tipoFactura, 
								    fechaFactura, 
								    sucursalFactura, 
								    fechaAbono, 
								    totalAbono,
								    totalAbonoCalculo,
								    ivaPercibido,
								    ivaRetenido
								FROM conta_comision_pagar_calculo
								WHERE comisionPagarPeriodoId = ? AND flgIdentificador = 'A' AND flgDelete = '0'
								GROUP BY nombreEmpleado, correlativoFactura, tipoFactura, fechaAbono, totalAbono
							", [$comisionPagarPeriodoId]);
						foreach ($dataIterarAbonos as $dataIterarAbonos) {
							$totalProductosAbonoEspecifico = $cloud->row("
									SELECT
										SUM(totalVenta) AS totalVenta,
										SUM(comisionPagar) AS comisionPagar
									FROM conta_comision_pagar_calculo
									WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND nombreCliente = ? AND correlativoFactura = ? AND tipoFactura = ? AND fechaFactura = ? AND sucursalFactura = ? AND fechaAbono = ? AND totalAbono = ? AND flgDelete = '0'
								", [
								$comisionPagarPeriodoId,
								$dataIterarAbonos->nombreEmpleado,
								$dataIterarAbonos->nombreCliente,
								$dataIterarAbonos->correlativoFactura,
								$dataIterarAbonos->tipoFactura,
								$dataIterarAbonos->fechaFactura,
								$dataIterarAbonos->sucursalFactura,
								$dataIterarAbonos->fechaAbono,
								$dataIterarAbonos->totalAbono
							]);
							if ($dataIterarAbonos->tipoFactura == "FACTURA DE CONSUMIDOR FINAL") { // CONSUMIDOR FINAL
								// Quitar IVA
								$totalVenta = $totalProductosAbonoEspecifico->totalVenta / $parametrizacionIVA;
							} else { // CRÉDITO FISCAL, EXPORTACIÓN, EXENTA
								// Ya viene sin IVA
								$totalVenta = $totalProductosAbonoEspecifico->totalVenta;
							}
							$comisionAbonoTotal = $totalProductosAbonoEspecifico->comisionPagar;
							// aproximar al entero
							$tasaComisionAbono = round(($comisionAbonoTotal / $totalVenta) * 100, 2);

							// Si la factura es exenta NO trae IVA el abono
							if ($dataIterarAbonos->tipoFactura == "FACTURA EXENTA" || $dataIterarAbonos->tipoFactura == "FACTURA DE EXPORTACIÓN") {
								$calculoMontoAbono = $dataIterarAbonos->totalAbono;
							} else {
								if ($dataIterarAbonos->ivaPercibido > 0.00) {
									$calculoMontoAbono = $dataIterarAbonos->totalAbono / 1.14;
								} else {
									$calculoMontoAbono = $dataIterarAbonos->totalAbono / 1.13;
								}
							}

							$comisionAbonoPagar = $calculoMontoAbono * ($tasaComisionAbono / 100);

							$update = [
								'totalAbonoCalculo' => $calculoMontoAbono,
								'tasaComisionAbono' => $tasaComisionAbono,
								'comisionAbonoTotal' => $comisionAbonoTotal,
								'comisionAbonoPagar' => $comisionAbonoPagar
							];
							$where = [
								'comisionPagarPeriodoId' => $comisionPagarPeriodoId,
								'nombreEmpleado' => $dataIterarAbonos->nombreEmpleado,
								'nombreCliente' => $dataIterarAbonos->nombreCliente,
								'correlativoFactura' => $dataIterarAbonos->correlativoFactura,
								'tipoFactura' => $dataIterarAbonos->tipoFactura,
								'fechaFactura' => $dataIterarAbonos->fechaFactura,
								'sucursalFactura' => $dataIterarAbonos->sucursalFactura,
								'fechaAbono' => $dataIterarAbonos->fechaAbono,
								'totalAbono' => $dataIterarAbonos->totalAbono,
								'flgDelete' => 0
							];
							$cloud->update('conta_comision_pagar_calculo', $update, $where);
						}

						// Agregar bitacora e incluir leyenda de recalculo
						echo "success";
					} else {
						echo "Problema al cargar el archivo. Por favor comuniquese con el departamento de Informática.";
					}
				}
			} else {
				echo "recalculo";
			}
			break;

		case 'comision-clasificacion':
			/*
				POST:
				typeOperation
				operation
				comisionClasificacionId
				tipoClasificacion
				tituloClasificacion
			*/
			$existeClasificacion = $cloud->count("
					SELECT comisionClasificacionId FROM conta_comision_reporte_clasificacion
					WHERE tipoClasificacion = ? AND tituloClasificacion = ? AND flgDelete = ?
				", [$_POST['tipoClasificacion'], $_POST['tituloClasificacion'], 0]);

			if ($existeClasificacion == 0) {
				$insert = [
					'tipoClasificacion' => $_POST['tipoClasificacion'],
					'tituloClasificacion' => $_POST['tituloClasificacion']
				];
				$cloud->insert("conta_comision_reporte_clasificacion", $insert);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Insertó una nueva clasificación de " . $_POST['tipoClasificacion'] . ": " . $_POST['tituloClasificacion'] . " (Comisiones), ");

				echo "success";
			} else {
				echo 'La clasificación de la ' . $_POST['tipoClasificacion'] . ': ' . $_POST['tituloClasificacion'] . ' ya fue creada.';
			}
			break;

		case 'comision-clasificacion-detalle':
			/*
				POST:
				typeOperation
				operation
				comisionClasificacionId
				tipoClasificacion
				valorClasificacion (multiple)
			*/
			$n = 0;
			// Iterar la parametrizacion
			foreach ($_POST["valorClasificacion"] as $valorClasificacion) {
				$existeClasificacionDetalle = $cloud->count("
						SELECT valorClasificacion FROM conta_comision_reporte_clasificacion_detalle
						WHERE valorClasificacion = ? AND comisionClasificacionId = ? AND flgDelete = ?
					", [$valorClasificacion, $_POST['comisionClasificacionId'], 0]);

				if ($existeClasificacionDetalle == 0) {
					$n += 1;
					$insert = [
						'comisionClasificacionId' => $_POST['comisionClasificacionId'],
						'valorClasificacion' => $valorClasificacion
					];
					$cloud->insert("conta_comision_reporte_clasificacion_detalle", $insert);
				} else {
					// Ya se agregó esta parametrización, omitirla para no duplicar
				}
			}
			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Insertó " . $n . " clasificaciones para la " . $_POST['tipoClasificacion'] . " ID: " . $_POST['comisionClasificacionId'] . ", ");
			echo "success";
			break;

		case 'clasificacion-gasto':
			$ExisteClasificacionGastos = $cloud->count("
					SELECT clasifGastoSalarioId FROM cat_clasificacion_gastos_salario
					WHERE nombreGastoSalario = ? AND flgDelete = ?
				", [$_POST['descGasto'], 0]);

			if ($ExisteClasificacionGastos == 0) {
				$insert = [
					'nombreGastoSalario' => $_POST['descGasto']
				];
				$cloud->insert("cat_clasificacion_gastos_salario", $insert);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Insertó una nueva clasificación de gasto: " . $_POST['descGasto']);

				echo "success";
			} else {
				echo "El nombre: $_POST[descGasto] ya fue creado";
			}
			break;

		case 'clasificacion-gasto-empleado':
			foreach ($_POST['expedientes'] as $prsExpedienteId) {
				$update = [
					'clasifGastoSalarioId' => $_POST['clasifGastoSalarioId']
				];
				$where = ['prsExpedienteId' => $prsExpedienteId];
				$cloud->update("th_expediente_personas", $update, $where);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Insertó una nueva clasificación de empleado(s)");
			}
			echo "success";

			/*$x = 0;
			foreach ($_POST['expedientes'] as $expedientes){

				$existeExpediente = $cloud->count("
				SELECT prsExpedienteId FROM conta_clasificacion_gastos_salario_detalle
				WHERE prsExpedienteId = ? AND flgDelete = ?
				", [$_POST['expedientes'][$x], 0]);

				if ($existeExpediente == 0){
					$insert = [
						'clasifGastoSalarioId' 	=> $_POST['clasEmp'],
						'prsExpedienteId' 	=> $_POST['expedientes'][$x]
					];
					$cloud->insert("conta_clasificacion_gastos_salario_detalle", $insert);
					$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Insertó un nuevo expediente (".$_POST['expedientes'][$x].") en la clasificación: " . $_POST['clasEmp']);
				}

				$x++;
			}
			echo "success";*/
			break;

		case 'parametrizacion-devengo':
			/*
				POST:
				typeOperation
				operation
				catPlanillaDevengoId
				catPlanillaDevengoIdSuperior
				tipoDevengo
				nombreDevengo
				codigoContable
			*/
			// catPlanillaDevengoId es 0 si viene de "Nuevo Devengo" y tiene valor si viene de "SubDevengos" ya que referencia al padre del registro
			$existeDevengoGravado = $cloud->count("
					SELECT catPlanillaDevengoId FROM cat_planilla_devengos
					WHERE tipoDevengo = ? AND codigoContable = ? AND flgDelete = ?
				", [$_POST['tipoDevengo'], $_POST['codigoContable'], 0]);

			if ($existeDevengoGravado == 0) {
				$insert = [
					'tipoDevengo' => $_POST['tipoDevengo'],
					'nombreDevengo' => $_POST['nombreDevengo'],
					'codigoContable' => $_POST['codigoContable'],
					'catPlanillaDevengoIdSuperior' => $_POST['catPlanillaDevengoIdSuperior']
				];
				$cloud->insert("cat_planilla_devengos", $insert);
				$cloud->writeBitacora("movInsert", "($fhActual) Insertó un nuevo devengo: $_POST[nombreDevengo]: $_POST[codigoContable] ($_POST[tipoDevengo]), ");

				echo "success";
			} else {
				echo "El código contable: $_POST[codigoContable] ya fue creado.";
			}
			break;

		case 'parametrizacion-descuento':
			/*
				POST:
				typeOperation
				tituloModal
				catPlanillaDescuentoId
				catPlanillaDescuentoIdSuperior
				tblOtrosDescuentos			
			*/
			$existeDescuento = $cloud->count("
					SELECT catPlanillaDescuentoId FROM cat_planilla_descuentos
					WHERE codigoContable = ? AND flgDelete = ?
				", [$_POST['codigoContable'], 0]);

			if ($existeDescuento == 0) {
				$insert = [
					'nombreDescuento' => $_POST['nombreDescuento'],
					'codigoContable' => $_POST['codigoContable'],
					'catPlanillaDescuentoIdSuperior' => $_POST['catPlanillaDescuentoIdSuperior']
				];
				$cloud->insert("cat_planilla_descuentos", $insert);
				$cloud->writeBitacora("movInsert", "($fhActual) Insertó un nuevo descuento: $_POST[nombreDescuento]: $_POST[codigoContable] ");

				echo "success";
			} else {
				echo "El código contable: $_POST[codigoContable] ya fue creado.";
			}
			break;

		case 'parametrizacion-renta':
			/*
				POST:
				tituloModal
				descuentoRentaId	
				flgEnAdelante
				tramoRenta
				rangoInicio
				rangoFin
				porcentajeDescuento
				montoExceso
				cuotaFija
			*/
			$insert = [
				'tramoRenta' => $_POST['tramoRenta'],
				'rangoInicio' => $_POST['rangoInicio'],
				'rangoFin' => $_POST['rangoFin'],
				'porcentajeDescuento' => $_POST['porcentajeDescuento'],
				'montoExceso' => $_POST['montoExceso'],
				'cuotaFija' => $_POST['cuotaFija'],
				'flgEnAdelante' => $_POST['flgEnAdelante'],
				'estadoDescuentoRenta' => 'Activo'
			];
			$cloud->insert("cat_planilla_descuentos_renta", $insert);
			$cloud->writeBitacora("movInsert", "($fhActual) Insertó un nuevo tramo: $_POST[tramoRenta]");

			echo "success";

			break;

		case 'parametrizacion-desc-ley':
			/*
				POST:
					descuentoLeyId, 
					nombreDescuentoLey, 
					tipoDescuento, 
					tipoValorDescuento,
					montoMaximo,
					cuotaExcesoMaximo,
					valorDescuento,
					estadoDescuentoLey
			*/
			$existeDescuentoLey = $cloud->count("
					SELECT descuentoLeyId FROM cat_planilla_descuentos_ley
					WHERE nombreDescuentoLey = ? AND tipoDescuento = ? AND flgDelete = ?
				", [$_POST['nombreDescuentoLey'], $_POST['tipoDescuento'], 0]);
			if ($existeDescuentoLey == 0) {
				if (isset($_POST['estadoDescuentoLey'])) {
					$insert = [
						'nombreDescuentoLey' => $_POST['nombreDescuentoLey'],
						'tipoDescuento' => $_POST['tipoDescuento'],
						'tipoValorDescuento' => $_POST['tipoValorDescuento'],
						'montoMaximo' => $_POST['montoMaximo'],
						'cuotaExcesoMaximo' => $_POST['cuotaExcesoMaximo'],
						'valorDescuento' => $_POST['valorDescuento'],
						'estadoDescuentoLey' => 'Activo'
					];
					$cloud->insert("cat_planilla_descuentos_ley", $insert);
					$cloud->writeBitacora("movInsert", "($fhActual) Insertó un nuevo descuento de ley: $_POST[nombreDescuentoLey]");
				} else {
					$insert = [
						'nombreDescuentoLey' => $_POST['nombreDescuentoLey'],
						'tipoDescuento' => $_POST['tipoDescuento'],
						'tipoValorDescuento' => $_POST['tipoValorDescuento'],
						'valorDescuento' => $_POST['valorDescuento'],
						'estadoDescuentoLey' => 'Activo'
					];
					$cloud->insert("cat_planilla_descuentos_ley", $insert);
					$cloud->writeBitacora("movInsert", "($fhActual) Insertó un nuevo descuento de ley: $_POST[nombreDescuentoLey]");
				}
				echo "success";
			} else {
				echo "El descuento $_POST[nombreDescuentoLey] ya fue creado";
			}
			break;

		case "historial-salario":
			/*
				POST:
					typeOperation
					operation
					salarioActual
					salario
					prsExpedienteId
					estadoExpediente
					fechaInicioVigencia
					descripcionSalario
			*/
			$fechaActual = strtotime(date("d-m-Y"));
			$fechaInicioVigencia = strtotime($_POST["fechaInicioVigencia"]);
			$fechaFinalizacionVigencia = date("Y-m-d", strtotime($_POST["fechaInicioVigencia"] . "-1 days"));

			$existeSalario = $cloud->count("
			        SELECT
			            expedienteSalarioId, 
			            prsExpedienteId, 
			            tipoSalario, 
			            fechaInicioVigencia, 
			            salario, 
			            descripcionSalario
			        FROM th_expediente_salarios
			        WHERE prsExpedienteId = ? AND estadoSalario = 'Activo' AND flgDelete = '0'
			        LIMIT 1
			    ", [$_POST["prsExpedienteId"]]);

			$flgUpdateEstadoSalario = 0;
			if ($existeSalario > 0) {
				if ($fechaActual == $fechaInicioVigencia) { // Hoy le dieron el aumento, actualizar a Salario actual
					$flgUpdateEstadoSalario = 1;
				} else if ($fechaInicioVigencia > $fechaActual) { // Es fecha futura, todavia no es su salario actual
					$flgUpdateEstadoSalario = 0;
				} else { // Es fecha menor, actualizar a Salario actual
					$flgUpdateEstadoSalario = 1;
				}

				// Lo coloco acá porque si lo ponía después del insert me actualizaba este nuevo salario
				if ($flgUpdateEstadoSalario == 1) { // El que se ingresó es su nuevo salario (según fechas), establecer los anteriores como inactivos para que el nuevo insert sea el actual
					$update = [
						'estadoSalario' => "Inactivo",
						'fechaFinalizacionVigencia' => $fechaFinalizacionVigencia
					];
					$where = [
						'prsExpedienteId' => $_POST["prsExpedienteId"]
					];
					$cloud->update('th_expediente_salarios', $update, $where);
				} else {
					// No actualizar estados, se mantiene su salario activo actual, ya que el insert quedará como Pendiente para actualizarlo en un futuro en un cronjob
					$update = [
						'fechaFinalizacionVigencia' => $fechaFinalizacionVigencia
					];
					$where = [
						'prsExpedienteId' => $_POST["prsExpedienteId"]
					];
					$cloud->update('th_expediente_salarios', $update, $where);
				}
				$tipoSalario = ($_POST["salario"] > $_POST['salarioActual']) ? "Aumento" : "Reducción";
			} else {
				// No existe salario anterior, es un nuevo salario, solamente insertar
				$tipoSalario = "Inicial";
				$flgUpdateEstadoSalario = 1;
			}

			$estadoSalario = ($flgUpdateEstadoSalario == 0) ? 'Pendiente' : 'Activo';

			$insert = [
				'prsExpedienteId' => $_POST["prsExpedienteId"],
				'salarioTipoRemuneracionId' => $_POST["tipoRemuneracion"],
				'tipoSalario' => $tipoSalario,
				'fechaInicioVigencia' => date("Y-m-d", strtotime($_POST["fechaInicioVigencia"])),
				'salario' => $_POST["salario"],
				'descripcionSalario' => $_POST["descripcionSalario"],
				'estadoSalario' => $estadoSalario
			];
			$expedienteSalarioId = $cloud->insert('th_expediente_salarios', $insert);

			// Bitácora de usuario final / jefes
			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo salario al expediente del empleado: " . $_POST["nombreCompleto"] . "(Cargo: " . $_POST["cargoPersona"] . " Salario anterior: $ " . $_POST['salarioActual'] . " Salario nuevo: $ " . $_POST['salario'] . "), ");

			echo "success";
			break;

		case 'planilla-otros-descuentos':
			/*
				POST:
				descuentoId = Si es cero es insert
				planillaId = Si es cero no se ha generado el cálculo
				quincenaId
				prsExpedienteId
				flgSubdescuento = Sí/No si el select del descuento a aplicar es subdescuento
				nombreCompleto
				catPlanillaDescuentoId
				subCatPlanillaDescuentoId
				montoDescuento
				descripcionDescuento
				flgMultiple
				expedienteMultiple
			*/
			// Validar el estadoPlanilla si es Cerrada no permitir insertar
			$catPlanillaDescuentoId = ($_POST['flgSubdescuento'] == "Sí" ? $_POST['subCatPlanillaDescuentoId'] : $_POST['catPlanillaDescuentoId']);

			if ($_POST['planillaId'] == 0) {
				$tablaDescuentos = "conta_planilla_programado_descuentos";
				if (isset($_POST['flgMultiple'])) {
					// El select en la modal ya tiene validado para que cambie planillaId por prsExpediente
					foreach ($_POST['expedienteMultiple'] as $prsExpedienteId) {
						$insert = [
							'quincenaId' => $_POST['quincenaId'],
							'prsExpedienteId' => $prsExpedienteId,
							'tipoDescuentoProgramado' => 'Otros descuentos',
							'idDescuentoProgramado' => $catPlanillaDescuentoId,
							'descripcionDescuentoProgramado' => $_POST['descripcionDescuento'],
							'montoDescuentoProgramado' => $_POST['montoDescuento'],
							'estadoDescuentoProgramado' => 'Programado'
						];
						$planillaDescuentoId = $cloud->insert($tablaDescuentos, $insert);
						// Bitácora de usuario final / jefes
						$cloud->writeBitacora("movInsert", "($fhActual) Ingresó otros descuentos de los empleados, formato: Múltiple (ID: $planillaDescuentoId, Quincena: $_POST[quincenaId]), ");
					}
				} else {
					$insert = [
						'quincenaId' => $_POST['quincenaId'],
						'prsExpedienteId' => $_POST['prsExpedienteId'],
						'tipoDescuentoProgramado' => 'Otros descuentos',
						'idDescuentoProgramado' => $catPlanillaDescuentoId,
						'descripcionDescuentoProgramado' => $_POST['descripcionDescuento'],
						'montoDescuentoProgramado' => $_POST['montoDescuento'],
						'estadoDescuentoProgramado' => 'Programado'
					];
				}
			} else {
				$tablaDescuentos = "conta_planilla_descuentos";
				if (isset($_POST['flgMultiple'])) {
					// El select en la modal ya tiene validado para que cambie prsExpediente por planillaId
					foreach ($_POST['expedienteMultiple'] as $planillaId) {
						$insert = [
							"planillaId" => $planillaId,
							"tipoDescuento" => 'Otros descuentos',
							"idDescuento" => $catPlanillaDescuentoId,
							"descripcionDescuento" => $_POST['descripcionDescuento'],
							"montoDescuento" => $_POST['montoDescuento']
						];
						$planillaDescuentoId = $cloud->insert($tablaDescuentos, $insert);
						// Bitácora de usuario final / jefes
						$cloud->writeBitacora("movInsert", "($fhActual) Ingresó otros descuentos de los empleados, formato: Múltiple (ID: $planillaDescuentoId, Quincena: $_POST[quincenaId]), ");
					}
				} else {
					$insert = [
						"planillaId" => $_POST['planillaId'],
						"tipoDescuento" => 'Otros descuentos',
						"idDescuento" => $catPlanillaDescuentoId,
						"descripcionDescuento" => $_POST['descripcionDescuento'],
						"montoDescuento" => $_POST['montoDescuento']
					];
				}
			}

			if (!isset($_POST['flgMultiple'])) {
				$planillaDescuentoId = $cloud->insert($tablaDescuentos, $insert);
				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movInsert", "($fhActual) Ingresó otros descuentos del empleado: $_POST[nombreCompleto] (ID: $planillaDescuentoId, Quincena: $_POST[quincenaId]), ");
			} else {
				// Fue multiple, ya se ejecutó el insert en cada iteración
			}
			echo "success";
			break;

		case 'planilla-devengos':
			/*
				POST:
				devengoId
				tipoDevengo
				planillaId
				quincenaId
				prsExpedienteId
				flgSubdevengo
				nombreCompleto
				catPlanillaDevengoId
				subCatPlanillaDevengoId
				montoDevengo
				descripcionDevengo
				flgMultiple
				expedienteMultiple
			*/
			// Validar el estadoPlanilla si es Cerrada no permitir insertar
			$catPlanillaDevengoId = ($_POST['flgSubdevengo'] == "Sí" ? $_POST['subCatPlanillaDevengoId'] : $_POST['catPlanillaDevengoId']);

			if ($_POST['planillaId'] == 0) {
				$tablaDevengos = "conta_planilla_programado_devengos";
				if (isset($_POST['flgMultiple'])) {
					// El select en la modal ya tiene validado para que cambie planillaId por prsExpediente
					foreach ($_POST['expedienteMultiple'] as $prsExpedienteId) {
						$insert = [
							'quincenaId' => $_POST['quincenaId'],
							'prsExpedienteId' => $prsExpedienteId,
							'catPlanillaDevengoId' => $catPlanillaDevengoId,
							'descripcionDevengoProgramado' => $_POST['descripcionDevengo'],
							'montoDevengoProgramado' => $_POST['montoDevengo'],
							'estadoDevengoProgramado' => 'Programado'
						];
						$planillaDevengoId = $cloud->insert($tablaDevengos, $insert);
						// Bitácora de usuario final / jefes
						$cloud->writeBitacora("movInsert", "($fhActual) Ingresó devengo $_POST[tipoDevengo] de los empleados, formato: Múltiple (ID: $planillaDevengoId, Quincena: $_POST[quincenaId]), ");
					}
				} else {
					$insert = [
						'quincenaId' => $_POST['quincenaId'],
						'prsExpedienteId' => $_POST['prsExpedienteId'],
						'catPlanillaDevengoId' => $catPlanillaDevengoId,
						'descripcionDevengoProgramado' => $_POST['descripcionDevengo'],
						'montoDevengoProgramado' => $_POST['montoDevengo'],
						'estadoDevengoProgramado' => 'Programado'
					];
				}
			} else {
				$tablaDevengos = "conta_planilla_devengos";
				if (isset($_POST['flgMultiple'])) {
					// El select en la modal ya tiene validado para que cambie prsExpedienteId por planillaId
					foreach ($_POST['expedienteMultiple'] as $planillaId) {
						$insert = [
							"planillaId" => $planillaId,
							"catPlanillaDevengoId" => $catPlanillaDevengoId,
							"descripcionDevengo" => $_POST['descripcionDevengo'],
							"montoDevengo" => $_POST['montoDevengo']
						];
						$planillaDevengoId = $cloud->insert($tablaDevengos, $insert);
						// Bitácora de usuario final / jefes
						$cloud->writeBitacora("movInsert", "($fhActual) Ingresó devengo $_POST[tipoDevengo] de los empleados, formato: Múltiple (ID: $planillaDevengoId, Quincena: $_POST[quincenaId]), ");
					}
				} else {
					$insert = [
						"planillaId" => $_POST['planillaId'],
						"catPlanillaDevengoId" => $catPlanillaDevengoId,
						"descripcionDevengo" => $_POST['descripcionDevengo'],
						"montoDevengo" => $_POST['montoDevengo']
					];
				}
			}

			if (!isset($_POST['flgMultiple'])) {
				$planillaDevengoId = $cloud->insert($tablaDevengos, $insert);
				// Bitácora de usuario final / jefes
				$cloud->writeBitacora("movInsert", "($fhActual) Ingresó devengo $_POST[tipoDevengo] del empleado: $_POST[nombreCompleto] (ID: $planillaDevengoId, Quincena: $_POST[quincenaId]), ");
			} else {
				// Fue multiple, ya se ejecutó el insert en cada iteración
			}

			echo "success";
			break;

		case "calcular-quincena":
			/*
				POST:
				quincenaId
			*/
			// Validar que todos los empleados tengan asignado salario

			break;

		case "datos-cliente":
			/*
				hiddenFormData
				typeOperation
				operation
				idCliente
				tipoPersona
				tipoDoc
				numeroDocumento
				nombreCliente
				nombreComercial
				nrc
				giro
				categoria
				nombreRL
				tipoDocRL
				duiRL
			 */

			if ($_POST["numeroDocumento"] != "") {
				$numDocumento = $_POST["numeroDocumento"];
			} else {
				$numDocumento = $_POST["nitPJ"];
			}

			$checkCliente = $cloud->count("SELECT clienteId FROM fel_clientes WHERE (numDocumento = ? OR nrcCliente = ? OR numDocumento = ?) AND flgDelete = 0", [$_POST["numeroDocumento"], $_POST["nrc"], $numDocumento]);
			if ($checkCliente == 0) {

				$insert = [
					'nrcCliente' => $_POST["nrc"],
					'tipoPersonaMHId' => $_POST["tipoPersona"],
					'nombreCliente' => $_POST["nombreCliente"],
					'nombreComercialCliente' => $_POST["nombreComercial"],
					'estadoCivilNat' => $_POST["estadoCivil"],
					'sexoNat' => $_POST["sexo"],
					'estadoCivilNat' => $_POST["estadoCivil"],
					'categoriaCliente' => $_POST["categoria"],

					'estadoCliente' => "Activo",
				];
				if ($_POST["tipoDoc"] != "") {
					$insert += [
						'tipoDocumentoMHId' => $_POST["tipoDoc"],
						'numDocumento' => $_POST["numeroDocumento"],
					];
				} else if ($_POST["nitPJ"] != "") {
					$insert += [
						'tipoDocumentoMHId' => '1',
						'numDocumento' => $_POST["nitPJ"],
					];
				}

				if ($_POST["municipioId"] != "") {
					$insert += [
						'paisMunicipioIdNacimientoNat' => $_POST["municipioId"]
					];
				}
				if ($_POST["nacionalidad"] != "") {
					$insert += [
						'paisIdNacionalidad' => $_POST["nacionalidad"]
					];
				}
				if ($_POST["fechaNacimientoNat"] != "") {
					$insert += [
						'fechaNacimientoNat' => $_POST["fechaNacimientoNat"]
					];
				}
				if ($_POST["nombreRL"] != "") {
					$insert += [
						'nombreCompletoRL' => $_POST["nombreRL"]
					];
				}
				if ($_POST["tipoDocRL"] != "") {
					$insert += [
						'tipoDocumentoRL' => $_POST["tipoDocRL"]
					];
				}
				if ($_POST["numeroDocumentoRL"] != "") {
					$insert += [
						'numDocumentoRL' => $_POST["numeroDocumentoRL"]
					];
				}
				if ($_POST["fechaNacimientoRL"] != "") {
					$insert += [
						'fechaNacimientoRL' => $_POST["fechaNacimientoRL"]
					];
				}
				if ($_POST["sexoRL"] != "") {
					$insert += [
						'sexoRL' => $_POST["sexoRL"]
					];
				}
				if ($_POST["profesionRL"] != "") {
					$insert += [
						'profesionRL' => $_POST["profesionRL"]
					];
				}
				if ($_POST["estadoCivilRL"] != "") {
					$insert += [
						'estadoCivilRL' => $_POST["estadoCivilRL"]
					];
				}
				if ($_POST["muniNacimientoRL"] != "") {
					$insert += [
						'paisMunicipioIdNacimientoRL' => $_POST["muniNacimientoRL"]
					];
				}
				if ($_POST["giro"] != "") {
					$insert += [
						'actividadEconomicaId' => $_POST["giro"]
					];
				}
				if ($_POST["giroSec"] != "") {
					$insert += [
						'actividadEconomicaIdSecundaria' => $_POST["giroSec"]
					];
				}
				if ($_POST["profesion"] != "") {
					$insert += [
						'profesionNat' => $_POST["profesion"]
					];
				}
				if (isset($_POST["pepPN"])) {
					$insert += [
						'flgPEP' => $_POST["pepPN"]
					];
				} else if (isset($_POST["pepPJ"])) {
					$insert += [
						'flgPEP' => $_POST["pepPJ"]
					];
				}
				if (isset($_POST["pepPJAc"])) {
					$insert += [
						'flgPEPAccionista' => $_POST["pepPJAc"]
					];
				}
				if (isset($_POST["pepPNFam"])) {
					$insert += [
						'flgPEPFamiliar' => $_POST["pepPNFam"]
					];
				}
				if (isset($_POST["apnfd"])) {
					$insert += [
						'flgAPNFD' => $_POST["apnfd"],
					];
				}
				$cliente = $cloud->insert('fel_clientes', $insert);

				$respuesta = array("resultado" => "success", "idCliente" => $cliente);

				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó datos los datos del cliente: " . $_POST["nombreCliente"] . ", ");

				echo json_encode($respuesta);
			} else {
				echo "El cliente que esta intentando crear ya existe, por favor verifique la información ingresada.";
			}
			break;

		case "datos-cliente-PEP":
			/*
				typeOperation
				operation
				idCliente
				flgTipo
				nombreClientePEP
				cargoPublico
				fechaNombramiento
				fechaNombramientoFin
				periodo
				tipoDoc
				numeroDocumento
				tipoPEP
				tipoRelacionPEP
			*/
			$insert = [
				'clienteId' => $_POST["idCliente"],
				'nombreCompletoPEP' => $_POST["nombreClientePEP"],
				'cargoPublico' => $_POST["cargoPublico"],
				'fechaNombramiento' => $_POST["fechaNombramiento"],
				'tipoDocumentoMHId' => $_POST["tipoDoc"],
				'numDocumentoPEP' => $_POST["numeroDocumento"],
				'institucionCargoPublico' => $_POST["institucion"],
				'tipoPEP' => $_POST["tipoPEP"],
				'tipoRelacionPEP' => $_POST["flgTipo"],
			];
			if ($_POST["fechaNombramientoFin"] != '') {
				$insert += [
					'fechaFinNombramiento' => $_POST["fechaNombramientoFin"],
				];
			}

			$cliente = $cloud->insert('fel_clientes_pep', $insert);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó datos los datos del cliente: " . $_POST["nombreClientePEP"] . ", ");

			echo "success";
			break;

		case "direccion-cliente":
			$insert = [
				'clienteId' => $_POST["idCliente"],
				'paisMunicipioId' => $_POST["municipio"],
				'tipoUbicacion' => $_POST["tipoUbicacion"],
				'nombreClienteUbicacion' => $_POST["nombreSuc"],
				'direccionClienteUbicacion' => $_POST["direccionCli"],
				'estadoClienteUbicacion' => 'Activo'
			];
			$cloud->insert('fel_clientes_ubicaciones', $insert);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó la ubicación: " . $_POST["nombreSuc"] . " del cliente: " . $_POST["nombreCliente"] . ", ");

			echo "success";
			break;

		case "contacto-cliente":
			$insert = [
				'clienteUbicacionId' => $_POST["idClienteC"],
				'tipoContactoId' => $_POST["tipoContacto"],
				'contactoCliente' => $_POST["contacto"],
				'descripcionContactoCliente' => $_POST["descripcion"],
			];
			$cloud->insert('fel_clientes_contactos', $insert);

			$getTipoContacto = $cloud->row("SELECT tipoContacto FROM cat_tipos_contacto WHERE tipoContactoId = ?", [$_POST["tipoContacto"]]);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó el contacto: " . $getTipoContacto->tipoContacto . " del cliente: " . $_POST["nombreCliente"] . ", ");

			echo "success";
			break;

		case "datos-cliente-proveedor":
			$insert = [
				'clienteId' => $_POST["idCliente"],
				'tipoRelacion' => $_POST["tipoRelacion"],
				'razonSocial' => $_POST["razonSocial"],
				'paisId' => $_POST["pais"],
			];
			$cloud->insert('fel_clientes_relacion', $insert);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó el cliente principal: " . $_POST["razonSocial"] . " del cliente: " . $_POST["nombreCliente"] . ", ");

			echo "success";
			break;

		case "datos-PEP-nucleoFamiliar":
			$insert = [
				'clientePEPId' => $_POST["PEPId"],
				'catPrsRelacionId' => $_POST["parentesco"],
				'nombreFamiliar' => $_POST["nombreFam"],
			];
			$cloud->insert('fel_clientes_pep_familia', $insert);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó el familiar: " . $_POST["nombreFam"] . " de la persona politicamente expuesta: " . $_POST["nombrePEP"] . ", ");

			echo "success";
			break;

		case "datos-PEP-sociedades":
			$insert = [
				'clientePEPId' => $_POST["PEPId"],
				'razonSocial' => $_POST["razonSocial"],
				'porcentajeParticipacion' => $_POST["participacion"],
			];
			$cloud->insert('fel_clientes_pep_relpatrimonial', $insert);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó la empresa: " . $_POST["razonSocial"] . " en la que la persona politicamente expuesta: " . $_POST["nombrePEP"] . " es accionista, ");

			echo "success";
			break;

		case "clientes-accionistas":
			$insert = [
				'clienteId' => $_POST["clienteId"],
				'nombreAccionista' => $_POST["nombreAccionista"],
				'paisId' => $_POST["pais"],
				'nitAccionista' => $_POST["nitAccionista"],
				'porcentajeParticipacion' => $_POST["participacion"],
			];
			$cloud->insert('fel_clientes_accionistas', $insert);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó al accionista: " . $_POST["nombreAccionista"] . " de la empresa: " . $_POST["nombreCliente"] . ", ");

			echo "success";
			break;

		case 'proveedor':
			/*
			   POST:
				hiddenFormData: insert
				typeOperation
				operation
			*/
			$queryExist = "
	        		SELECT 
	        			nombreProveedor
	        		FROM comp_proveedores
	        		WHERE tipoProveedor = ? AND tipoDocumento = ? AND (nrcProveedor = ? OR numDocumento = ?)  AND flgDelete = 0
	        	";
			$existe = $cloud->count($queryExist, [$_POST["tipoProveedor"], $_POST['tipoDocumento'], $_POST['nrcProveedor'], $_POST['numDocumento']]);
			if ($existe == 0) {
				$insert = [
					'tipoProveedor' => $_POST['tipoProveedor'],
					'nrcProveedor' => $_POST['nrcProveedor'],
					'tipoDocumento' => $_POST['tipoDocumento'],
					'numDocumento' => $_POST['numDocumento'],
					'nombreProveedor' => $_POST['nombreProveedor'],
					'nombreComercial' => $_POST['nombreComercial']
				];

				if ($_POST['tipoProveedor'] == "Empresa extranjera" || $_POST['tipoProveedor'] == "Persona extranjera") {
					$insert += [
						'descripcionExtranjero' => $_POST['descripcionExtranjero']
					];
				} else {
					// Local
					$insert += [
						'nombreCompletoRL' => $_POST['nombreCompletoRL'],
						'tipoDocumentoRL' => $_POST['tipoDocumentoRL'],
						'numDocumentoRL' => $_POST['numDocumentoRL'],
						'actividadEconomicaId' => $_POST['actividadEconomicaId']
					];
				}

				$cloud->insert('comp_proveedores', $insert);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo Proveedor: " . $_POST["nombreProveedor"] . ", con número de documento: " . $_POST['numDocumento'] . ", ");

				echo "success";
			} else {
				echo "El proveedor: " . $_POST["nombreProveedor"] . " con NRC: " . $_POST['nrcProveedor'] . " y número de documento " . $_POST['numDocumento'] . ", ya existe en el catálogo.";
			}
			break;

		case 'proveedor-ubicacion':
			/*
				 POST:
				hiddenFormData: insert
				typeOperation
				operation
			*/
			$queryExist = "
					SELECT 
						nombreProveedorUbicacion
					FROM comp_proveedores_ubicaciones
					WHERE proveedorId = ? AND paisMunicipioId = ? AND nombreProveedorUbicacion = ? AND flgDelete = 0
				";
			$existe = $cloud->count($queryExist, [$_POST["proveedorId"], $_POST['paisMunicipioId'], $_POST['nombreProveedorUbicacion']]);
			if ($existe == 0) {
				$insert = [
					'proveedorId' => $_POST['proveedorId'],
					'paisMunicipioId' => $_POST['paisMunicipioId'],
					'nombreProveedorUbicacion' => $_POST['nombreProveedorUbicacion'],
					'direccionProveedorUbicacion' => $_POST['direccionProveedorUbicacion']
				];

				$cloud->insert('comp_proveedores_ubicaciones', $insert);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó una nueva ubicación " . $_POST["nombreProveedorUbicacion"] . " del Proveedor: " . $_POST["nombreProveedor"] . ", ");

				echo "success";
			} else {
				echo "La ubicación " . $_POST["nombreProveedorUbicacion"] . " del proveedor: " . $_POST["nombreProveedor"] . ", ya existe en el catálogo.";
			}
			break;

		case "comprobante-retencion":
			/*
			operation: comprobante-retencion
			facturaId: 
			fechaEmision: 2024-02-14
			proveedor: 33
			proveedorUbicacionId: 33
			*/
			$insert = [
				//'fechaEmision'				=> $_POST["fechaEmision"],
				'fechaEmision' => date('Y-m-d'),
				'proveedorUbicacionId' => $_POST["proveedorUbicacionId"],
				'proveedorId' => $_POST["proveedor"],
				'tipoDTEId' => 6,
				'estadoFactura' => 'Pendiente',
				'horaEmision' => date("H:i:s"),
				'identificacionAmbiente' => "01",
				'tipoModeloMHId' => 1,
				'tipoMoneda' => "USD"
			];
			$facturaId = $cloud->insert('fel_factura', $insert);

			// Datos del emisor (Indupal)
			$dataEmisor = getInfoEmisor($cloud);

			$dataSucursalMH = $cloud->row("
					SELECT tipoEstablecimientoMH, puntoVentaMH FROM cat_sucursales
					WHERE sucursalId = ? AND flgDelete = ?
				", [$_POST["sucursalId"], 0]);

			$insert = [
				"facturaId" => $facturaId,
				"nitEmisor" => $dataEmisor["nit"],
				"nrcEmisor" => $dataEmisor["nrc"],
				"nombreEmisor" => $dataEmisor["nombreCompleto"],
				"actividadEconomicaId" => $dataEmisor["actividadEconomicaId"],
				"actividadEconomicaIdSecundaria" => $dataEmisor["actividadEconomicaIdSecundaria"],
				"sucursalId" => $_POST["sucursalId"],
				"tipoEstablecimientoMH" => $dataSucursalMH->tipoEstablecimientoMH,
				"puntoVentaMH" => $dataSucursalMH->puntoVentaMH
			];
			$facturaEmisorId = $cloud->insert("fel_factura_emisor", $insert);

			// Insert a fel_factura_retenciones
			/*
			Al crear un nuevo comprobante de retención, en el case que inserta a fel_factura, se realice un insert
			a fel_factura_retenciones
			facturaId = $facturaId
			porcentajeIVARetenido = obtener de la parametrizacion
			ivaRetenido = 0
			porcentajeIVAPercibido = obtener de la parametrizacion con otra consulta
			ivaPercibido = 0
			porcentajeRenta = obtener de la parametrizacion con una tercer consulta
			rentaRetenido = 0
			*/
			$dataPorcentajeIVARetenido = $cloud->row("
					SELECT tipoParametrizacion, descripcionParametrizacion, parametro FROM conf_parametrizacion
					WHERE parametrizacionId = ? AND flgDelete = ?
				", [2, 0]);

			$dataPorcentajeIVAPercibido = $cloud->row("
					SELECT tipoParametrizacion, descripcionParametrizacion, parametro FROM conf_parametrizacion
					WHERE parametrizacionId = ? AND flgDelete = ?
				", [3, 0]);

			$dataPorcentajeRenta = $cloud->row("
					SELECT tipoParametrizacion, descripcionParametrizacion, parametro FROM conf_parametrizacion
					WHERE parametrizacionId = ? AND flgDelete = ?
				", [10, 0]);

			$insert = [
				'facturaId' => $facturaId,
				'porcentajeIVARetenido' => $dataPorcentajeIVARetenido->parametro,
				'ivaRetenido' => 0,
				'porcentajeIVAPercibido' => $dataPorcentajeIVAPercibido->parametro,
				'ivaPercibido' => 0,
				'porcentajeRenta' => $dataPorcentajeRenta->parametro,
				'rentaRetenido' => 0
			];
			$cloud->insert("fel_factura_retenciones", $insert);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó al proveedor: " . $_POST["proveedor"] . " y la dirección: " . $_POST["proveedorUbicacionId"] . ", ");

			$jsonRespuesta = array(
				"respuesta" => "success",
				"facturaId" => $facturaId
			);

			echo json_encode($jsonRespuesta);
			break;

		case "agregar-documento-retencion":
			/*
			typeOperation: insert
			operation: agregar-documento
			facturaId: 1623
			tipoDTEId: 1
			tipoGeneracionDocId: 1
			fechaEmisionRelacionada: 2024-02-20
			numDoc: 123145453
			descripcion: sdsdsdsdsd
			valorNeto: 12
			ivaRetenido: 1 

			*/
			$insert = [
				'facturaId' => $_POST["facturaId"],
				'tipoDTEId' => $_POST["tipoDTEId"],
				'tipoGeneracionDocId' => $_POST["tipoGeneracionDocId"],
				'fechaEmisionRelacionada' => $_POST["fechaEmisionRelacionada"],
				'numeroDocumentoRelacionada' => $_POST["numDoc"],
				'horaEmisionRelacionada' => date("H:i:s")
			];
			$facturaRelacionadaId = $cloud->insert('fel_factura_relacionada', $insert);

			$insert = [
				'facturaId' => $_POST["facturaId"],
				'productoId' => 25911,
				'codProductoFactura' => "-RETENCION",
				'nombreProductoFactura' => $_POST["descripcion"],
				'tipoItemMHId' => 1,
				'precioUnitario' => $_POST["valorNeto"],
				'precioVenta' => $_POST["valorNeto"],
				'subTotalDetalle' => $_POST["valorNeto"],
				'totalDetalle' => $_POST["valorNeto"],
				'ivaRetenidoDetalle' => $_POST["ivaRetenido"],
				'costoPromedio' => 0,
				'precioUnitarioIVA' => 0,
				'precioVentaIVA' => 0,
				'cantidadProducto' => 1,
				'facturaRelacionadaId' => $facturaRelacionadaId
			];
			$facturaDetalleId = $cloud->insert('fel_factura_detalle', $insert);


			// Consultar el nuevo iva retenido
			$ivaRetenido = $cloud->row("
					SELECT SUM(ivaRetenidoDetalle) AS totalIva
					FROM fel_factura_detalle
					WHERE facturaId = ? AND flgDelete = ?
				", [$_POST["facturaId"], 0]);

			// Insertar el nuevo registro de iva retenido
			$update = [
				'ivaRetenido' => $ivaRetenido->totalIva
			];
			$where = ['facturaId' => $_POST["facturaId"]];

			$cloud->update('fel_factura_retenciones', $update, $where);
			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un detalle al comprobante de retención: $_POST[facturaId]");

			echo "success";
			break;

		case "complemento-DTE":
			$insert = [
				'facturaId' => $_POST["facturaId"],
				'complementoFactura' => $_POST["descripcionComplemento"]
			];
			$cloud->insert('fel_factura_complementos' . $_POST['yearBD'], $insert);
			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó un nuevo complemento " . $_POST["facturaId"]);

			echo "success";
			break;

		case "declarar-periodo":
			$mesesAnio = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
			$mesNombre = '';
			if (isset($mesesAnio[$_POST["mes"]])) {
				$mesNombre = $mesesAnio[$_POST["mes"]];
			}


			$checkDeclaracion = $cloud->count(
				"SELECT cierreDeclaracionId FROM fel_cierre_declaracion WHERE mesNumero = ? AND anio = ? AND flgDelete = 0",
				[$_POST["mes"], $_POST["anio"]]
			);

			if ($checkDeclaracion > 0) {
				echo "La declaración del mes de $mesNombre, ya ha sido realizada.";
			} else {
				$insert = [
					'mesNumero' => $_POST["mes"],
					'mesNombre' => $mesNombre,
					'anio' => $_POST["anio"],
					'obsCierreDelaracion' => $_POST["obsDeclaracion"]
				];
				$cierreDeclaracion = $cloud->insert('fel_cierre_declaracion', $insert);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Finalizo el periodo para el mes de: " . $mesNombre);

				// totales del periodo

				/* $getSucursal = $cloud->rows("SELECT 
					sucursalId,
					sucursal 
					FROM cat_sucursales
					WHERE flgDelete = ? AND sucursalId IN (1,4,5,8,6,7,9)
					ORDER BY FIELD(sucursalId, 1,4,5,8,6,7,9);
				",[0]);

				$ventasContribuyentes = 0;
				$ventasContribuyentesIVA = 0;
				$ventasConsumidores = 0;
				$ventasConsumidoresIVA = 0;
				$ventasExentas = 0;
				$ventasExportaciones = 0;
				$notasCredito = 0;
				$notasCreditoIVA = 0;
				$notasRemision = 0;
				$notasRemisionIVA = 0;
				$comprobantesRetencion = 0;
				$comprobantesRetencionIVAR = 0;
				$sujetosExcluidos = 0;
				$invalidaciones = 0;
				$invalidacionesIVA = 0;
				$ivaPercibido = 0;
				$ivaRetenido = 0;
				$rentaRetenido = 0;
				$ivaRetenidoDetalle = 0;

				foreach ($getSucursal as $sucursal){
					$sucursalId = $sucursal->sucursalId;
					$nombreSucursal = $sucursal->sucursal;

					switch ($sucursalId) {
						case '4':
							$whereEstaciones = "AND fm.mgEstacionId IN (5)";
							$nombreSucursal = "Sucursal Santa Ana";
						break;

						case '5':
							$whereEstaciones = "AND fm.mgEstacionId IN (9)";
							$nombreSucursal = "Sucursal Santa Miguel";
						break;

						case '8':
							$whereEstaciones = "AND fm.mgEstacionId IN (12)";
							$nombreSucursal = "Sucursal Karcher";
						break;

						case '6':
							$whereEstaciones = "AND fm.mgEstacionId IN (13)";
							$nombreSucursal = "Sucursal Hidropal";
						break;

						case '7':
							$whereEstaciones = "AND fm.mgEstacionId IN (14)";
							$nombreSucursal = "Sucursal Nogueira";
						break;

						case '9':
							$whereEstaciones = "AND fm.mgEstacionId IN (15)";
							$nombreSucursal = "Sucursal Nogueire SM";
						break;

						default:
							// Casa matriz
							$whereEstaciones = "AND fm.mgEstacionId IN (1, 2, 3)";
							$nombreSucursal = "Casa Matriz";
						break;
					}

					$getFacturas = $cloud->rows("SELECT 
							f.facturaId AS facturaId,
							f.fechaEmision AS fechaEmision,
							DATE_FORMAT(f.fechaEmision, '%d/%m/%Y') AS fechaEmisionFormat,
							td.codigoMH AS codigoDocumentoMH,
							f.tipoDTEId AS tipoDTEId,
							td.tipoDTE AS tipoDTE,
							c.numDocumento AS numDocumento,
							c.tipoDocumentoMHId AS tipoDocumentoMHId,
							c.nrcCliente AS nrcCliente,
							CASE
								WHEN c.nombreCliente = '' OR c.nombreCliente = NULL THEN c.nombreComercialCliente
								ELSE c.nombreCliente
							END AS nombreCliente,
							fp.montoPago AS montoPago,
							mfe.mgCodEstacion AS mgCodEstacion,
							mfe.mgNombreEstacion AS mgNombreEstacion,
							fm.mgCorrelativoFactura AS mgCorrelativoFactura,
							fm.mgNumRequisicion AS mgNumRequisicion,
							fr.ivaPercibido AS ivaPercibido,
							fr.rentaRetenido AS rentaRetenido,
							fr.ivaRetenido AS ivaRetenido
						FROM fel_factura f
						JOIN mh_002_tipo_dte td ON td.tipoDTEId = f.tipoDTEId
						JOIN fel_clientes_ubicaciones cu ON cu.clienteUbicacionId = f.clienteUbicacionId
						JOIN fel_clientes c ON c.clienteId = cu.clienteId
						JOIN fel_factura_pago fp ON fp.facturaId = f.facturaId
						JOIN fel_factura_magic fm ON fm.facturaId = f.facturaId
						JOIN magic_facturacion_estaciones mfe ON mfe.mgEstacionId = fm.mgEstacionId
						JOIN fel_factura_retenciones fr ON fr.facturaId = f.facturaId
						WHERE MONTH(f.fechaEmision) = ? AND YEAR(f.fechaEmision) = ? AND f.estadoFactura = 'Finalizado' AND f.flgDelete = 0 $whereEstaciones
					",[$_POST["mes"], $_POST["anio"]]);

					foreach ($getFacturas as $factura){

						$dataCertificacion = $cloud->row("
							SELECT 
								ffcex.facturaCertificacionId AS facturaCertificacionId,
								ffcex.numeroControl AS numeroControl,
								ffcex.selloRecibido AS selloRecibido,
								ffcex.codigoGeneracion AS codigoGeneracion
							FROM fel_factura_certificacion ffcex 
							WHERE (ffcex.estadoCertificacion = 'Certificado' OR ffcex.descripcionMsg LIKE 'RECIBIDO%') AND ffcex.facturaId = ? AND ffcex.flgDelete = ?
							ORDER BY ffcex.facturaCertificacionId DESC 
							LIMIT 1
						", [$factura->facturaId, 0]);

						if($dataCertificacion) {
							$dataTotalDTE = $cloud->row("
								SELECT 
									SUM(totalDetalle) AS total,
									SUM(ivaTotal) AS ivaTotal,
									SUM(ivaRetenidoDetalle) as ivaRetenido,
									SUM(ivaPercibidoDetalle) as ivaPercibido
								FROM fel_factura_detalle
								WHERE facturaId = ? AND flgDelete = ?
							", [$factura->facturaId, 0]);

						switch($factura->tipoDTEId){
							case 1:
								//factura
								$ventasConsumidores += $dataTotalDTE->total;
								$ventasConsumidoresIVA += $dataTotalDTE->ivaTotal;
								$ivaPercibido += $factura->ivaPercibido;
								$ivaRetenido += $factura->ivaRetenido; 
							break;
							case 2:
								//credito fiscal
								$ventasContribuyentes += $dataTotalDTE->total;
								$ventasContribuyentesIVA += $dataTotalDTE->ivaTotal;
								$ivaPercibido += $factura->ivaPercibido;
								$ivaRetenido += $factura->ivaRetenido; 
							break;
							case 3:
								//nota remision
								$notasRemision += $dataTotalDTE->total;
								$notasRemisionIVA += $dataTotalDTE->ivaTotal;
								$ivaPercibido += 0;
								$ivaRetenido += 0; 
							break;
							case 4:
								$ventasContribuyentesIVA -= $dataTotalDTE->ivaTotal;
								//nota credito
								$notasCredito += $dataTotalDTE->total;
								$notasCreditoIVA += $dataTotalDTE->ivaTotal;
								$ivaPercibido -= $factura->ivaPercibido;
								$ivaRetenido -= $factura->ivaRetenido; 
							break;
							case 6:
								//comprobante retencion
								$comprobantesRetencion = $dataTotalDTE->total;
								$comprobantesRetencionIVAR = $dataTotalDTE->ivaTotal;
								$ivaPercibido += $factura->ivaPercibido;
								$ivaRetenido += $factura->ivaRetenido; 
							break;
							case 9:
								//exportacion
								$ventasExportaciones += $dataTotalDTE->total;
								$ivaPercibido += $factura->ivaPercibido;
								$ivaRetenido += $factura->ivaRetenido; 
							break;
							case 10:
								//sujetos excluidos
								$sujetosExcluidos += $dataTotalDTE->total;
								$ivaPercibido += $factura->ivaPercibido;
								$ivaRetenido += $factura->ivaRetenido; 
								// 
							break;
						} 

						// 	$dataTotaRetenciones = $cloud->row("
						// 		SELECT 
						// 			rentaRetenido AS rentaRetenido,
						// 			ivaPercibido as ivaPercibido,
						// 			ivaRetenido AS ivaRetenido
						// 		FROM fel_factura_retenciones
						// 		WHERE facturaId = ? AND flgDelete = ?
						// 	", [$factura->facturaId, 0]);

						// $rentaRetenido += $dataTotaRetenciones->rentaRetenido;
						// 
						//$ivaPercibido += $factura->ivaPercibido;
						//$ivaRetenido += $factura->ivaRetenido; 
					}
				} 
				//ventas exentas
				/* $getFactexc = $cloud->rows("SELECT f.facturaId FROM fel_factura f
				JOIN fel_factura_emisor e ON f.facturaId = e.facturaId 
				WHERE  tipoDTEId = 1 AND MONTH(f.fechaEmision) = ? AND YEAR(f.fechaEmision) = ? AND e.sucursalId = ? AND f.estadoFactura = 'Finalizado'",
				[$_POST["mes"], $_POST["anio"], $sucursalId]);

					foreach ($getFactexc as $fact){
						$dataTotalDTE = $cloud->row("SELECT 
								SUM(ivaTotal) AS debitoFiscal,
								SUM(totalDetalle) AS ventasGravadasLocales,
								SUM(totalDetalleIVA) AS totalDetalleIVA
							FROM fel_factura_detalle
							WHERE facturaId = ? AND flgDelete = ?
						", [$fact->facturaId, 0]);

						if($dataTotalDTE->debitoFiscal == 0) {
							$ventasExentas += $dataTotalDTE->ventasGravadasLocales;
							$ventasConsumidores -= $ventasExentas;

						}
					} 
					// invalidaciones

				$getFacturasInv = $cloud->rows("
					SELECT sum(d.totalDetalle) as total, sum(d.totalDetalleIVA) as totalIva, sum(d.ivaTotal) as iva, f.tipoDTEId as tipoDTEId
					FROM fel_factura f
					JOIN fel_factura_detalle d ON f.facturaId = d.facturaId
					JOIN fel_factura_emisor e ON f.facturaId = e.facturaId 
					JOIN fel_factura_certificacion fc ON f.facturaId = fc.facturaId
					WHERE (fc.estadoCertificacion = 'Certificado' OR fc.descripcionMsg LIKE 'RECIBIDO%') AND MONTH(f.fechaEmision) = ? AND YEAR(f.fechaEmision) = ? AND e.sucursalId = ? AND f.estadoFactura = 'Anulado'
					GROUP BY f.tipoDTEId 
				",[$_POST["mes"], $_POST["anio"], $sucursalId]);
				foreach ($getFacturasInv as $totales){
					$invalidaciones += $totales->total;
					$invalidacionesIVA += $totales->iva;
				}

				$insertTot = [
					'cierreDeclaracionId' 			=> $cierreDeclaracion,
					'sucursalId' 					=> $sucursalId,
					'ventasContribuyentes' 			=> $ventasContribuyentes,
					'ventasContribuyentesIVA' 		=> $ventasContribuyentesIVA,
					'ventasConsumidores' 			=> $ventasConsumidores,
					'ventasConsumidoresIVA' 		=> $ventasConsumidoresIVA,
					'ventasConsumidoresExentos' 	=> $ventasExentas,
					'ventasExportaciones' 			=> $ventasExportaciones,
					'notasCredito' 					=> $notasCredito,
					'notasCreditoIVA' 				=> $notasCreditoIVA,
					'notasRemision' 				=> $notasRemision,
					'notasRemisionIVA' 				=> $notasRemisionIVA,
					'comprobantesRetencion' 		=> $comprobantesRetencion,
					'comprobantesRetencionIVAR' 	=> $comprobantesRetencionIVAR,
					'sujetosExcluidos' 				=> $sujetosExcluidos,
					'sujetosExcluidosRenta' 		=> $rentaRetenido,
					'invalidaciones' 				=> $invalidaciones,
					'invalidacionesIVA' 			=> $invalidacionesIVA,
					'ivaPercibido' 					=> $ivaPercibido,
					'ivaRetenido' 					=> $ivaRetenido,
					'ivaRetenidoProveedores' 		=> $ivaRetenidoDetalle, 
				];

				$cloud->insert('fel_cierre_declaraciones_totales', $insertTot);

					$ventasContribuyentes = 0;
					$ventasContribuyentesIVA = 0;
					$ventasConsumidores = 0;
					$ventasConsumidoresIVA = 0;
					$ventasExentas = 0;
					$ventasExportaciones = 0;
					$notasCredito = 0;
					$notasCreditoIVA = 0;
					$notasRemision = 0;
					$notasRemisionIVA = 0;
					$comprobantesRetencion = 0;
					$comprobantesRetencionIVAR = 0;
					$sujetosExcluidos = 0;
					$rentaRetenido = 0;
					$invalidaciones = 0;
					$invalidacionesIVA = 0;
					$ivaPercibido = 0;
					$ivaRetenido = 0;
					$ivaRetenidoDetalle = 0;
				} */

				echo "success";
			}
			break;

		case "invalidacion-extraordinaria":
			/* 
				cierreDeclaracionId
				sucursalId[]
				tipoDTEId
				yearBD
				listaDTE[]
				motivoAnulacion
			*/
			$yearBD = $_POST['yearBD'];
			$anioActual = "_" . date("Y");
			$anioTxt = str_replace("_", "", $yearBD);
			if ($yearBD == $anioActual) {
				$yearBD = '';
			}

			foreach ($_POST["listaDTE"] as $facturaId) {
				$dataTotal = $cloud->row("
						SELECT
							ivaTotal,
							totalFactura,
							totalFacturaIVA
						FROM view_factura_total$yearBD
						WHERE facturaId = ?
					", [$facturaId]);

				$insert = [
					'cierreDeclaracionId' => $_POST["cierreDeclaracionId"],
					'ivaFactura' => 0,
					'facturaId' => $facturaId,
					'anio' => $anioTxt,
					'fechaPeriodoAplica' => $_POST['fechaPeriodo'],
					'totalFactura' => ($dataTotal->totalFactura),
					'ivaFactura' => ($dataTotal->ivaTotal),
					'totalFacturaIVA' => ($dataTotal->totalFacturaIVA),
					'obsCierreAnulacion' => $_POST["motivoAnulacion"]
				];
				$cloud->insert('fel_cierre_declaracion_anulacion', $insert);
			}

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Se Realizó una nueva anulación pasada");

			echo "success";
			break;

		case "correo-proveedor":
			if ($_POST['tipoDTEId'] == 10) {
				$dataSujeto = $cloud->row("
                        SELECT sujetoExcluidoId FROM fel_factura
                        WHERE facturaId = ? AND flgDelete = ?
                    ", [$_POST['facturaId'], 0]);

				$update = [
					"correoSujeto" => $_POST['nuevoCorreoCliente']
				];
				$where = ["sujetoExcluidoId" => $dataSujeto->sujetoExcluidoId];
				$cloud->update("fel_sujeto_excluido", $update, $where);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Se actualizó el correo electrónico al sujeto excluido: $dataSujeto->sujetoExcluidoId");
			} else {
				$existeContactoCliente = $cloud->count("
                        SELECT proveedorContactoId FROM comp_proveedores_contactos
                        WHERE proveedorUbicacionId = ? AND flgDelete = ?
                    ", [$_POST['proveedorUbicacionId'], 0]);

				if ($existeContactoCliente == 0) {
					// No tiene contacto
					$flgContactoPrincipal = 1;
				} else {
					// Ya tiene contacto
					$flgContactoPrincipal = 0;
				}

				$insert = [
					'proveedorUbicacionId' => $_POST['proveedorUbicacionId'],
					'tipoContactoId' => "13",
					'contactoProveedor' => $_POST['nuevoCorreoCliente'],
					'descripcionProveedorContacto' => "Correo electrónico principal",
					'flgContactoPrincipal' => $flgContactoPrincipal
				];
				$cloud->insert("comp_proveedores_contactos", $insert);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Se agregó el correo electrónico a: $_POST[proveedorUbicacionId]");
			}
			echo "success";
			break;

		case "parametrizacion-compartida":

			$getCliente = $cloud->row("SELECT 
					comisionPagarCalculoId,
					numRegistroCliente,
					nombreCliente
				FROM conta_comision_pagar_calculo
				WHERE flgDelete = ? AND comisionPagarCalculoId = ?", [0, $_POST['clienteId']]);

			$insert = [
				'numRegistroCliente' => $getCliente->numRegistroCliente,
				'nombreCliente' => $getCliente->nombreCliente,
				'descripcionParametrizacion' => $_POST['descParam'],
			];

			$cloud->insert("conta_comision_compartida_parametrizacion", $insert);
			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Se agregó una nueva parametrización para el cliente: $getCliente->nombreCliente");

			echo "success";
			break;

		case "parametrizacion-compartida-vendedores":
			$getCliente = $cloud->row("SELECT 
					comisionPagarCalculoId,
					codEmpleado,
					codVendedor,
					nombreEmpleado
				FROM conta_comision_pagar_calculo
				WHERE flgDelete = ? AND comisionPagarCalculoId = ?", [0, $_POST['vendedorId']]);

			$checkPorcent = $cloud->row("SELECT SUM(porcentajeComisionCompartida) AS totalPorcent FROM conta_comision_compartida_parametrizacion_detalle WHERE flgDelete = ? AND comisionCompartidaParamId = ?", [0, $_POST['idDParam']]);

			$totalPorcent = $checkPorcent->totalPorcent + $_POST['porcentaje'];
			if ($checkPorcent->totalPorcent > 100.00 || $totalPorcent > 100.00) {
				echo "La repartición del porcentaje no debe superar el 100%";
			} else {
				// Validar que el cliente y vendedor no se duplique
				$insert = [
					'comisionCompartidaParamId' => $_POST['idDParam'],
					'codEmpleado' => $getCliente->codEmpleado,
					'codVendedor' => $getCliente->codVendedor,
					'nombreEmpleado' => $getCliente->nombreEmpleado,
					'porcentajeComisionCompartida' => $_POST['porcentaje'],
				];

				$cloud->insert("conta_comision_compartida_parametrizacion_detalle", $insert);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Se agregó un nuevo porcentaje para el vendedor: $getCliente->nombreEmpleado");

				echo "success";
			}
			break;

		case "pagos-transferencias":
			/*
				POST:
				nombreOrganizacionId
				fechaPagoTransferencia
				tipoTransferencia
			*/

			$dataExisteTransferencia = $cloud->row("
					SELECT pagoTransferenciaId FROM conta_pagos_transferencias
					WHERE nombreOrganizacionId = ? AND fechaPagoTransferencia = ? AND tipoTransferencia = ? AND flgDelete = ? 
				", [$_POST['nombreOrganizacionId'], $_POST['fechaPagoTransferencia'], $_POST['tipoTransferencia'], 0]);

			if ($dataExisteTransferencia) {
				echo "El pago por transferencia en la fecha " . date("d/m/Y", strtotime($_POST['fechaPagoTransferencia'])) . " para el banco y tipo de transferencia seleccionado ya fue generado, por favor verifique la información";
			} else {
				$insert = [
					"nombreOrganizacionId" => $_POST['nombreOrganizacionId'],
					"fechaPagoTransferencia" => $_POST['fechaPagoTransferencia'],
					"tipoTransferencia" => $_POST['tipoTransferencia'],
					"estadoPago" => "Pendiente"
				];
				$pagoTransferenciaId = $cloud->insert("conta_pagos_transferencias", $insert);

				echo "success";
			}
			break;

		case "pagos-transferencias-quedan":
			/*
				POST:
				pagoTransferenciaId
				tipoTransferencia
				bancoId
				fechaInicioQuedan
				fechaFinQuedan
			*/
			$pagoTransferenciaId = $_POST['pagoTransferenciaId'];
			// El campo es DATETIME, por eso se concatenan las horas
			$fechaInicio = $_POST['fechaInicioQuedan'] . " 00:00:00";
			$fechaFin = $_POST['fechaFinQuedan'] . "23:59:59";

			if ($_POST['tipoTransferencia'] == "Local") {
				$whereBanco = "AND pcq.nombreOrganizacionId = $_POST[bancoId]";
			} else {
				$whereBanco = "AND pcq.nombreOrganizacionId <> $_POST[bancoId]";
			}

			$dataQuedanPagados = $cloud->rows("
					SELECT
						q.quedanId AS quedanId,
						q.proveedorCBancariaId AS proveedorCBancariaId
					FROM comp_quedan q
					JOIN comp_proveedores_cbancaria pcq ON pcq.proveedorCBancariaId = q.proveedorCBancariaId
					WHERE q.estadoTransferencia = ? AND q.estadoQuedan = ? AND q.formaPago = ? AND q.flgDelete = ? AND q.fhPagoQuedan BETWEEN ? AND ?
					$whereBanco
				", ["Pendiente", "Pagado", "Transferencia", 0, $_POST['fechaInicioQuedan'], $_POST['fechaFinQuedan']]);
			$numRegistros = 0;
			foreach ($dataQuedanPagados as $quedanPagado) {
				$numRegistros++;

				$dataQuedanMonto = $cloud->row("
			        	SELECT 
			        		SUM(totalDocumentoCompra) as totalQuedan, 
			        		SUM(ivaRetenido) AS totalRetencion 
			        	FROM comp_quedan_detalle 
			        	WHERE quedanId = ? AND flgDelete = ?
			        ", [$quedanPagado->quedanId, 0]);

				if ($dataQuedanMonto) {
					$montoTransferencia = $dataQuedanMonto->totalQuedan - $dataQuedanMonto->totalRetencion;
				} else {
					$montoTransferencia = 0;
				}

				$conceptoTransferencia = "Quedan: $quedanPagado->quedanId";

				$insert = [
					"pagoTransferenciaId" => $pagoTransferenciaId,
					"proveedorCBancariaId" => $quedanPagado->proveedorCBancariaId,
					"conceptoTransferencia" => $conceptoTransferencia,
					"montoTransferencia" => $montoTransferencia,
					"tablaDetalle" => "comp_quedan",
					"tablaDetalleId" => $quedanPagado->quedanId
				];

				$pagoTransferenciaDetalleId = $cloud->insert("conta_pagos_transferencias_detalle", $insert);

				$update = [
					"estadoTransferencia" => "Pagado"
				];
				$where = ["quedanId" => $quedanPagado->quedanId];
				$cloud->update("comp_quedan", $update, $where);
			}

			$jsonRespuesta = array(
				"respuesta" => "success",
				"numRegistros" => $numRegistros
			);
			echo json_encode($jsonRespuesta);
			break;

		case "pagos-transferencias-otros-pagos":
			/*
				POST:
				pagoTransferenciaId
				tipoTransferencia
				bancoId
				proveedorId
				proveedorCBancariaId
				conceptoTransferencia
				montoTransferencia
			*/
			$insert = [
				"pagoTransferenciaId" => $_POST['pagoTransferenciaId'],
				"proveedorCBancariaId" => $_POST['proveedorCBancariaId'],
				"conceptoTransferencia" => $_POST['conceptoTransferencia'],
				"montoTransferencia" => $_POST['montoTransferencia'],
				"tablaDetalle" => "conta_pagos_transferencias_detalle"
			];
			$pagoTransferenciaDetalleId = $cloud->insert("conta_pagos_transferencias_detalle", $insert);

			$update = [
				"tablaDetalleId" => $pagoTransferenciaDetalleId
			];
			$where = ["pagoTransferenciaDetalleId" => $pagoTransferenciaDetalleId];
			$cloud->update("conta_pagos_transferencias_detalle", $update, $where);

			echo "success";
			break;

		case "bonos-encargados":
			/*
				POST:
				personaIdEncargado
			*/
			$n = 0;
			foreach ($_POST['personaIdEncargado'] as $personaId) {
				$n++;

				$insert = [
					"personaId" => $personaId
				];
				$bonoPersonaId = $cloud->insert("conf_bonos_personas", $insert);
			}

			$jsonRespuesta = array(
				"respuesta" => "success",
				"registros" => $n
			);

			echo json_encode($jsonRespuesta);
			break;

		case "bonos-encargados-detalle":
			/*
				POST:
				bonoPersonaId
				personaIdDetalle
			*/
			$n = 0;
			foreach ($_POST['personaIdDetalle'] as $personaId) {
				$n++;

				// Validación por si fue insert y no se actualizó con ajax el select para que no se repitan
				$existeEmpleadoEncargado = $cloud->count("
						SELECT bonoPersonaDetalleId FROM conf_bonos_personas_detalle
						WHERE bonoPersonaId = ? AND personaId = ? AND flgDelete = ?
					", [$_POST['bonoPersonaId'], $personaId, 0]);

				if ($existeEmpleadoEncargado == 0) {
					$insert = [
						"bonoPersonaId" => $_POST['bonoPersonaId'],
						"personaId" => $personaId
					];
					$bonoPersonaDetalleId = $cloud->insert("conf_bonos_personas_detalle", $insert);
				} else {
					// Omitir este insert
					$n--;
				}
			}

			$jsonRespuesta = array(
				"respuesta" => "success",
				"registros" => $n
			);

			echo json_encode($jsonRespuesta);
			break;

		case "bonos-pagos-empleado":
			/*
				POST:
				periodoBonoId
				bonoPersonaDetalleId
				prsExpedienteId
				nombreCompleto
				nombreEncargado
				cuentaBonoId
				montoBono
				obsBono 
			*/

			if ($_POST['montoBono'] > 0) {
				$insert = [
					"periodoBonoId" => $_POST['periodoBonoId'],
					"bonoPersonaDetalleId" => $_POST['bonoPersonaDetalleId'],
					"prsExpedienteId" => $_POST['prsExpedienteId'],
					"cuentaBonoId" => $_POST['cuentaBonoId'],
					"montoBono" => $_POST['montoBono'],
					"obsBono" => $_POST['obsBono']
				];
				$planillaBonoId = $cloud->insert("conta_planilla_bonos", $insert);

				$cloud->writeBitacora("movInsert", "( $fhActual ) Asignó bono de planilla al empleado: $_POST[nombreCompleto] de la parametrización de bonos del encargado: $_POST[nombreEncargado]");

				echo "success";
			} else {
				echo "Por favor, ingrese un monto del bono válido y mayor a $ 0.00";
			}
			break;

		case "bonos-periodo-anterior":
			/*
				POST:
				bonoPersonaId
				periodoBonoId
				nombreEncargado
			*/
			$periodoBonoId = $_POST['periodoBonoId'];
			$periodoBonoIdAnterior = $periodoBonoId - 1;

			$dataBonosPeriodoAnterior = $cloud->rows("
					SELECT
						pb.bonoPersonaDetalleId AS bonoPersonaDetalleId,
						bpd.personaId AS personaId,
					    exp.prsExpedienteId AS prsExpedienteId,
					    pb.cuentaBonoId AS cuentaBonoId,
					    pb.montoBono AS montoBono,
					    pb.obsBono AS obsBono
					FROM conta_planilla_bonos pb
					JOIN conf_bonos_personas_detalle bpd ON bpd.bonoPersonaDetalleId = pb.bonoPersonaDetalleId
					JOIN view_expedientes exp ON exp.personaId = bpd.personaId
					WHERE pb.periodoBonoId = ? AND bpd.bonoPersonaId = ? AND exp.estadoPersona = ? AND exp.estadoExpediente = ? AND pb.flgDelete = ? AND bpd.flgDelete = ?
				", [$periodoBonoIdAnterior, $_POST['bonoPersonaId'], 'Activo', 'Activo', 0, 0]);
			$numBonos = 0;
			foreach ($dataBonosPeriodoAnterior as $bonoPeriodoAnterior) {
				$existeBonoPeriodoActual = $cloud->count("
						SELECT
							pb.bonoPersonaDetalleId AS bonoPersonaDetalleId,
						    exp.prsExpedienteId AS prsExpedienteId,
						    pb.cuentaBonoId AS cuentaBonoId,
						    pb.montoBono AS montoBono,
						    pb.obsBono AS obsBono
						FROM conta_planilla_bonos pb
						JOIN conf_bonos_personas_detalle bpd ON bpd.bonoPersonaDetalleId = pb.bonoPersonaDetalleId
						JOIN view_expedientes exp ON exp.personaId = bpd.personaId
						WHERE pb.periodoBonoId = ? AND bpd.personaId = ? AND exp.estadoPersona = ? AND exp.estadoExpediente = ? AND pb.flgDelete = ? AND bpd.flgDelete = ?
					", [$periodoBonoId, $bonoPeriodoAnterior->personaId, 'Activo', 'Activo', 0, 0]);

				if ($existeBonoPeriodoActual == 0) {
					$numBonos++;

					$insert = [
						"periodoBonoId" => $periodoBonoId,
						"bonoPersonaDetalleId" => $bonoPeriodoAnterior->bonoPersonaDetalleId,
						"prsExpedienteId" => $bonoPeriodoAnterior->prsExpedienteId,
						"cuentaBonoId" => $bonoPeriodoAnterior->cuentaBonoId,
						"montoBono" => $bonoPeriodoAnterior->montoBono,
						"obsBono" => $bonoPeriodoAnterior->obsBono
					];
					$planillaBonoId = $cloud->insert("conta_planilla_bonos", $insert);
				} else {
					// Ya tiene bono en este periodo, no agregar ni duplicar información
				}
			}

			$cloud->writeBitacora("movInsert", "( $fhActual ) Asignó bonos de periodo anterior, encargado: $_POST[nombreEncargado], periodo actual: $periodoBonoId, periodo anterior: $periodoBonoIdAnterior, bonos agregados: $numBonos");

			$jsonRespuesta = array(
				"respuesta" => "success",
				"numBonos" => $numBonos
			);

			echo json_encode($jsonRespuesta);
			break;

		case "sincronizacion-magic-compras":
			/*
				POST:
				bitExportacionMagicId
				descripcionExportacion
				fechaInicioCompras
				fechaFinCompras
			*/
			$fechaInicio = $_POST['fechaInicioCompras'];
			$fechaFin = $_POST['fechaFinCompras'];

			if (!empty($fechaInicio) && !empty($fechaFin)) {
				if ($_POST['bitExportacionMagicId'] == 0) {
					// Insert al encabezado
					$insert = [
						"descripcionExportacion" => $_POST['descripcionExportacion'],
						"personaId" => $_SESSION['personaId'],
						"fhExportacion" => date("Y-m-d H:i:s"),
						"estadoExportacion" => "Pendiente"
					];
					$bitExportacionMagicId = $cloud->insert("bit_exportaciones_magic", $insert);
				} else {
					// Ya está el Insert, solo es repetir y volver a sincronizar las compras por si quedó una suelta
					$bitExportacionMagicId = $_POST['bitExportacionMagicId'];
				}

				$fechaInicioComprasFormat = date("d-m-Y", strtotime($_POST["fechaInicioCompras"]));
				$fechaFinComprasFormat = date("d-m-Y", strtotime($_POST["fechaFinCompras"]));
				$numComprasSincronizadas = 0;

				// Lógica de yearBD de FEL, porque también las Compras van por año
				$anioInicio = "_" . date("Y", strtotime($fechaInicio));
				$anioInicioTxt = date("Y", strtotime($fechaInicio));

				$anioFin = "_" . date("Y", strtotime($fechaFin));
				$anioFinTxt = date("Y", strtotime($fechaFin));

				// Iterar desde anioInicio hasta anioFin (se seguirá usando esta lógica de aquí en adelante)
				for ($anio = (int) $anioInicioTxt; $anio <= (int) $anioFinTxt; $anio++) {
					if ($anio < 2024) {
						// No tenemos tablas menores a 2024, ponerle un alto a eso
						$arrayAnios[] = "_2024";
					} else {
						$arrayAnios[] = "_" . $anio;
					}
				}

				foreach ($arrayAnios as $yearBDBucle) {
					$yearBD = "_" . date("Y");

					$tablaExportacion = "comp_compras" . $yearBD;

					$dataCompras = $cloud->rows("
		                	SELECT
		                		compraId
		                	FROM $tablaExportacion
		                	WHERE tipoCompra = ? AND estadoCompra = ? AND flgDelete = ? AND fechaDeclaracion BETWEEN ? AND ?
		                ", ["Local", "Finalizado", 0, $fechaInicio, $fechaFin]);

					foreach ($dataCompras as $compra) {
						// Si después se necesita validar el detalle, iterar aquí y prevenir el insert si no aplica
						// De momento, se deja sin validación

						// Solo validar si la compra ya se encuentra en esta sincronización y en cualquier otra, para omitirla y no mandar duplicados
						$existeCompra = $cloud->count("
		                		SELECT bitExportacionMagicDetalleId FROM bit_exportaciones_magic_detalle
		                		WHERE tablaExportacion = ? AND tablaExportacionId = ? AND flgDelete = ?
		                	", [$tablaExportacion, $compra->compraId, 0]);

						if ($existeCompra == 0) {
							$insertDetalle = [
								"bitExportacionMagicId" => $bitExportacionMagicId,
								"tablaExportacion" => $tablaExportacion,
								"tablaExportacionId" => $compra->compraId
							];
							$bitExportacionMagicDetalleId = $cloud->insert("bit_exportaciones_magic_detalle", $insertDetalle);

							$numComprasSincronizadas++;
						} else {
							// Ya fue sincronizada la compra
						}
					}
				}

				$cloud->writeBitacora("movInsert", "({$fhActual}) Sincronizó las compras de Cloud hacia Magic, en el rango de fechas del: {$fechaInicioComprasFormat} al {$fechaFinComprasFormat}, {$numComprasSincronizadas} registros de compras.");

				$jsonRespuesta = array(
					"respuesta" => "success",
					"numRegistros" => $numComprasSincronizadas,
					"bitExportacionMagicId" => $bitExportacionMagicId
				);

				echo json_encode($jsonRespuesta);
			} else {
				echo "Las fechas seleccionadas no son válidas.";
			}
			break;

		case "sincronizar-compras-magic-bd":
			/*
				POST:
				bitExportacionMagicId
				descripcionExportacion
				estadoExportacion
			*/
			require_once("../../../../libraries/includes/logic/mgc/datos24.php");

			if ($_POST['estadoExportacion'] == "Pendiente") {
				$dataSincronizacionDetalle = $cloud->rows("
				    	SELECT
				    		bitExportacionMagicDetalleId, tablaExportacion, tablaExportacionId
				    	FROM bit_exportaciones_magic_detalle
				    	WHERE bitExportacionMagicId = ? AND flgDelete = ?
				    ", [$_POST["bitExportacionMagicId"], 0]);

				$dataCorrelativoMagic = $magic->row("
				    	SELECT 
							Tipo_docto,
  							No_Corr_Real,
      						No_Corr_Temp
				    	FROM CorrelCompras
				    	WHERE Tipo_docto = ?
				    ", ['CL']);

				$numPedidoMagic = $dataCorrelativoMagic->No_Corr_Temp;

				$n = 0;
				$arrayInsert = [];
				$arrayMagicId = [];
				$indexInsert = 0;
				foreach ($dataSincronizacionDetalle as $sincronizacionDetalle) {
					$tablaExportacion = $sincronizacionDetalle->tablaExportacion;

					// Últimos 5 caracteres para saber el año
					$yearBDDetalle = substr($tablaExportacion, -5);

					$dataCompra = $cloud->row("
		                    SELECT 
		                        tdte.codigoMH AS codigoMHDTE,
		                        tdte.tipoDTE AS tipoDTE,
		                        tgd.codigoMH AS codigoMHGeneracion,
		                        c.tipoGeneracionDocId AS tipoGeneracionDocId,
		                        tgd.tipoGeneracionDoc AS tipoGeneracionDoc,
		                        c.numFactura AS numFactura,
		                        p.nrcProveedor AS nrcProveedor,
		                        p.tipoDocumento AS tipoDocumentoProveedor,
		                        p.numDocumento AS numDocumentoProveedor,
		                        p.nombreProveedor AS nombreProveedor,
		                        p.codProveedorMagic AS codProveedorMagic,
		                        c.fechaFactura AS fechaFactura,
		                        c.fechaDeclaracion AS fechaDeclaracion,
		                        c.semanaDeclaracion AS semanaDeclaracion,
		                        c.tipoCombustible AS tipoCombustible,
		                        c.selloRecibido AS selloRecibido,
		                        c.numeroControl AS numeroControl,
		                        c.compraCuentaContableId AS compraCuentaContableId,
		                        claf.codigoMH AS clasificacionAnexoT,
		                        claf.compraClasificacion AS descripcionAnexoT,
		                        clafd.codigoMH AS clasificacionAnexoV,
		                        clafd.compraClasificacionDetalle AS descripcionAnexoV
		                    FROM $tablaExportacion c
		                    JOIN mh_002_tipo_dte tdte ON tdte.tipoDTEId = c.tipoDTEId
		                    JOIN mh_007_tipo_generacion_documento tgd ON tgd.tipoGeneracionDocId = c.tipoGeneracionDocId
		                    JOIN comp_proveedores_ubicaciones pu ON pu.proveedorUbicacionId = c.proveedorUbicacionId
		                    JOIN comp_proveedores p ON p.proveedorId = pu.proveedorId
		                    JOIN mh_compras_clasificacion_detalle clafd ON clafd.compraClasificacionDetalleId = c.compraClasificacionDetalleId
		                    JOIN mh_compras_clasificacion claf ON claf.compraClasificacionId = clafd.compraClasificacionId
		                    WHERE c.compraId = ? AND c.estadoCompra = ? AND c.flgDelete = ?
		                ", [$sincronizacionDetalle->tablaExportacionId, "Finalizado", 0]);

					// Comisiones bancarias
					if ($dataCompra->compraCuentaContableId == 1) {
						// Validar si la compra es de Comisiones bancarias, para ver si tiene o no DCL
						$dataDCLComision = $cloud->row("
								SELECT
									compraDCLId
								FROM comp_compras_dcl$yearBDDetalle
								WHERE compraId = ? AND flgDelete = ?
							", [$sincronizacionDetalle->tablaExportacionId, 0]);

						if ($dataDCLComision) {
							// Se agregó DCL, está todou bem
						} else {
							// No tiene DCL, avisar
							/*
							die(
								"
									Se encontró una Compra que corresponde a Comisiones bancarias que no posee Documento Contable de Liquidación, por favor verifique la información:
									<br>
									Núm. documento: {$dataCompra->numFactura}<br>
									Proveedor: {$dataCompra->nombreProveedor}
								"
							);
							*/
						}
					} else {
						// No es necesario validarle el DCL a esta compra
					}

					$dataTotalesCompra = $cloud->row("
		                	SELECT
		                		SUM(ivaTotal) AS ivaTotal,
		                		SUM(costoDetalleTotal) AS totalFactura,
		                		SUM(costoDetalleTotalIVA) AS totalFacturaIVA
		                	FROM comp_compras_detalle$yearBDDetalle
		                	WHERE compraId = ? AND flgDelete = ?
		                ", [$sincronizacionDetalle->tablaExportacionId, 0]);

					// C = Crédito Fiscal, D = DTE, i = Importación
					if ($dataCompra->tipoGeneracionDocId == 2) {
						$documentoMagic = "D";
					} else {
						$documentoMagic = "C";
					}
					$numFacturaMagic = $dataCompra->numFactura;
					$vendedorMagic = "0"; // Según indicaciones
					if ($dataCompra->codProveedorMagic == "" || is_null($dataCompra->codProveedorMagic)) {
						die("
		                			No se ha asignado código de Magic al Proveedor:
		                			<br>
		                			ID: {$sincronizacionDetalle->tablaExportacionId}<br>
		                			Proveedor: {$dataCompra->nombreProveedor}<br>
		                			NRC: {$dataCompra->nrcProveedor}<br>
		                			{$dataCompra->tipoDocumentoProveedor}: {$dataCompra->numDocumentoProveedor}
		                		");
					} else {
						$codProveedorMagic = $dataCompra->codProveedorMagic;
					}
					$fechaDeclaracionMagic = date("d/m/y", strtotime($dataCompra->fechaDeclaracion)); // Salida: dd/mm/YY. 
					$fechaDocumentoMagic = date("d/m/y", strtotime($dataCompra->fechaFactura));
					$ivaMagic = number_format($dataTotalesCompra->ivaTotal, 2, ".", "");

					if ($dataCompra->semanaDeclaracion == "" || is_null($dataCompra->semanaDeclaracion)) {
						$semanaContabilidadMagic = 0;
					} else {
						$semanaContabilidadMagic = $dataCompra->semanaDeclaracion;
					}
					$totalGravadasMagic = number_format($dataTotalesCompra->totalFactura, 2, ".", ""); // Sin IVA
					$totalExentasMagic = "0"; // No aplica para compras locales

					// Son diferentes Tributos, pero para que cuadre, consolidarlo todo en la columna de Fovial de Magic
					$dataTotalTributos = $cloud->row("
		                	SELECT SUM(montoTributo) AS total FROM comp_compras_tributos$yearBDDetalle
		                	WHERE compraId = ? AND flgDelete = ?
		                ", [$sincronizacionDetalle->tablaExportacionId, 0]);

					$dataRetencionesCompras = $cloud->row("
		                	SELECT ivaRetenido, ivaPercibido, rentaRetenido 
		                	FROM comp_compras_retenciones$yearBDDetalle 
		                	WHERE compraId = ? AND flgDelete = ?
		                ", [$sincronizacionDetalle->tablaExportacionId, 0]);

					$totalTributos = $dataTotalTributos->total;
					$totalIVARetenido = $dataRetencionesCompras->ivaRetenido;
					$totalPagarCalculo = $dataTotalesCompra->totalFacturaIVA + $totalTributos - $dataRetencionesCompras->ivaRetenido;

					$fovialMagic = number_format($totalTributos, 2, ".", "");
					$totalPagarMagic = number_format($totalPagarCalculo, 2, ".", "");
					$saldoFacturaMagic = "0"; // No aplica de momento o no lo han mencionado

					if ($dataCompra->tipoCombustible == "Vendedores") {
						$condicionesMagic = "V";
					} else {
						$condicionesMagic = ""; // Vacío según indicaciones
					}

					$diasCreditoMagic = "0"; // No aplica de momento o no lo han mencionado
					$fechaVencimientoMagic = ""; // No aplica de momento o no lo han mencionado
					$estatusMagic = "C"; // Según indicaciones
					$estatus2Magic = "endiente"; // Según indicaciones
					$ultimaLineaMagic = "0"; // Según indicaciones
					$cuentaCargarMagic = ""; // Según indicaciones
					$cuentaAbonarMagic = ""; // Según indicaciones
					$chequeMagic = ""; // Según indicaciones
					$valorPagadoMagic = 0; // Según indicaciones
					$fechaPagadoMagic = ""; // Según indicaciones
					$fechaQuedanMagic = ""; // Según indicaciones
					$numImportacionMagic = ""; // Según indicaciones
					$numRegistroMagic = $dataCompra->nrcProveedor;
					// Verificar si el NRC contiene un guion
					if (!strpos($numRegistroMagic, '-')) {
						// Insertar el guion antes del último dígito
						$numRegistroMagic = substr($numRegistroMagic, 0, -1) . '-' . substr($numRegistroMagic, -1);
					} else {
						$numRegistroMagic = $dataCompra->nrcProveedor;
					}
					$fechaReclamoMagic = ""; // Según indicaciones
					$proximoPagoMagic = 0; // Según indicaciones
					$aNombreDeMagic = ""; // Según indicaciones
					$tipoDocumentoMagic = "2"; // Según indicaciones
					$documentoTipoMagic = str_replace("-", "", $dataCompra->numDocumentoProveedor);
					$totalExcluidosMagic = "0"; // Según indicaciones

					if ($dataCompra->selloRecibido == "" || is_null($dataCompra->selloRecibido)) {
						$serieMagic = "-";
						if ($dataCompra->tipoGeneracionDocId == 2) {
							die("
			                			Se encontró una Compra que no posee sello de recepción:
			                			<br>
			                			Núm. documento: {$dataCompra->numFactura}<br>
			                			Proveedor: {$dataCompra->nombreProveedor}
			                		");
						} else {
							// Es factura fisica, dejar pasar de momento a no ser que lo mencionen
							die("
			                			Se encontró una Compra que no posee número de serie o resolución:
			                			<br>
			                			Núm. documento: {$dataCompra->numFactura}<br>
			                			Proveedor: {$dataCompra->nombreProveedor}
			                		");
						}
					} else {
						$serieMagic = $dataCompra->selloRecibido;
					}

					if ($dataCompra->numeroControl == "" || is_null($dataCompra->numeroControl)) {
						$numeroControlMagic = "-";
						$unicoMagic = "0";
						if ($dataCompra->tipoGeneracionDocId == 2) {
							die("
			                			Se encontró una Compra que no posee número de control:
			                			<br>
			                			Núm. documento: {$dataCompra->numFactura}<br>
			                			Proveedor: {$dataCompra->nombreProveedor}
			                		");
						} else {
							// Es factura fisica, dejar pasar de momento a no ser que lo mencionen
							die("
			                			Se encontró una Compra que no posee número de formulario único:
			                			<br>
			                			Núm. documento: {$dataCompra->numFactura}<br>
			                			Proveedor: {$dataCompra->nombreProveedor}
			                		");
						}
					} else {
						if ($dataCompra->tipoGeneracionDocId == 2) {
							$numeroControlMagic = $dataCompra->numeroControl;
							// DTE-03-S020P004-000000000002532
							$arrayUnico = explode("-", $dataCompra->numeroControl);
							$unicoMagic = (int) $arrayUnico[3]; // Para los últimos digitos del "DTE-"
							if ($unicoMagic >= 999999999) {
								// Por los Proveedores que usan el 2025 al inicio, quitar
								$unicoMagic = (int) substr($unicoMagic, -9); // Últimos 9 caracteres por espacios Magic
							} else {
								// Entero normal
							}
						} else {
							// Para facturación fisica es diferente
							$numeroControlMagic = "";
							$unicoMagic = $dataCompra->numeroControl;
						}
					}

					$insertEncabezado = [
						"No_Pedido" => $numPedidoMagic,
						"Documento" => $documentoMagic,
						"No_Factura" => $numFacturaMagic,
						"Vendedor" => $vendedorMagic,
						"Proveedor" => $codProveedorMagic,
						"Fecha_Declaracion" => $fechaDeclaracionMagic,
						"Fecha_documento" => $fechaDocumentoMagic,
						"IVA" => $ivaMagic,
						"Semana" => $semanaContabilidadMagic,
						"Total_Gravadas" => $totalGravadasMagic,
						"Total_Exentas" => $totalExentasMagic,
						"Fovial" => $fovialMagic,
						"Total_a_Pagar" => $totalPagarMagic,
						"Saldo_Factura" => $saldoFacturaMagic,
						"Condiciones" => $condicionesMagic,
						"Dias_Credito" => $diasCreditoMagic,
						"Fecha_Vencimiento" => $fechaVencimientoMagic,
						"Estatus" => $estatusMagic,
						"Estatus2" => $estatus2Magic,
						"Ultima_Linea" => $ultimaLineaMagic,
						"Cuenta_a_Cargar" => $cuentaCargarMagic,
						"Cuenta_a_Abonar" => $cuentaAbonarMagic,
						"Cheque_No" => $chequeMagic,
						"Valor_Pagado" => $valorPagadoMagic,
						"Fecha_Pagado" => $fechaPagadoMagic,
						"Fecha_Quedan" => $fechaQuedanMagic,
						"No_Importacion" => $numImportacionMagic,
						"NS_de_Registro" => $numRegistroMagic,
						"Fecha_Reclamo_1" => $fechaReclamoMagic,
						"Proximo_Pago" => $proximoPagoMagic,
						"A_nombre_de" => $aNombreDeMagic,
						"Tipo_Documento" => $tipoDocumentoMagic,
						"Documento_tipo" => $documentoTipoMagic,
						"Total_Excluidos" => $totalExcluidosMagic,
						"Serie" => $serieMagic,
						"Unico" => $unicoMagic,
						"Numero_Control" => $numeroControlMagic
					];

					$arrayInsert[$indexInsert] = $insertEncabezado;
					$arrayMagicId[$indexInsert] = [
						"bitExportacionMagicDetalleId" => $sincronizacionDetalle->bitExportacionMagicDetalleId,
						"numPedidoMagic" => $numPedidoMagic,
						"tablaExportacion" => $sincronizacionDetalle->tablaExportacion,
						"tablaExportacionId" => $sincronizacionDetalle->tablaExportacionId,
						"clasificacionAnexoT" => $dataCompra->clasificacionAnexoT,
						"descripcionAnexoT" => $dataCompra->descripcionAnexoT,
						"clasificacionAnexoV" => $dataCompra->clasificacionAnexoV,
						"descripcionAnexoV" => $dataCompra->descripcionAnexoV
					];
					$indexInsert++;
					$numPedidoMagic++;
				}
				//var_dump($arrayInsert);
				for ($i = 0; $i < count($arrayInsert); $i++) {
					// Estas tablas no tienen auditoria, cambiar de momento la variable para evitar error en el wrapper
					$_SESSION["writeBitacora"] = "no";
					$magicInsertId = $magic->insert("magic_enca_compras", $arrayInsert[$i]);

					$bitExportacionMagicDetalleId = $arrayMagicId[$i]["bitExportacionMagicDetalleId"];
					$numPedidoMagicUpdate = $arrayMagicId[$i]["numPedidoMagic"];
					$tablaExportacion = $arrayMagicId[$i]["tablaExportacion"];
					$tablaExportacionId = $arrayMagicId[$i]["tablaExportacionId"];

					$numeroControlEntero = $arrayInsert[$i]["Unico"];
					$fechaDeclaracionContable = $arrayInsert[$i]["Fecha_Declaracion"];
					$nrcProveedor = $arrayInsert[$i]["NS_de_Registro"];
					$numFacturaCCF = $arrayInsert[$i]["No_Factura"];
					$fechaCCFCompra = $arrayInsert[$i]["Fecha_documento"];

					$clasificacionAnexoT = $arrayMagicId[$i]["clasificacionAnexoT"];
					$descripcionAnexoT = $arrayMagicId[$i]["descripcionAnexoT"];
					$clasificacionAnexoV = $arrayMagicId[$i]["clasificacionAnexoV"];
					$descripcionAnexoV = $arrayMagicId[$i]["descripcionAnexoV"];

					// Últimos 5 caracteres para saber el año
					$yearBDDetalle = substr($tablaExportacion, -5);

					// Actualizar el ID de Magic con el que cada compra quedó relacionada
					$updateMagicId = [
						"magicId" => $numPedidoMagicUpdate
					];
					$whereMagicId = ["bitExportacionMagicDetalleId" => $bitExportacionMagicDetalleId];
					$cloud->update("bit_exportaciones_magic_detalle", $updateMagicId, $whereMagicId);

					// Insertar el detalle de la compra en Magic, para los productos que SÍ afectan Inventario
					$dataComprasDetalle = $cloud->rows("
							SELECT
								cd.compraDetalleId AS compraDetalleId, 
								m.abreviaturaMarca AS abreviaturaMarca,
								prod.codMagic AS codMagic,
								cd.cantidadProducto AS cantidadProducto,
								cd.costoUnitario AS costoUnitario, 
								cd.costoUnitarioIVA AS costoUnitarioIVA, 
								cd.montoDescuento AS montoDescuento, 
								cd.ivaUnitario AS ivaUnitario, 
								cd.ivaTotal AS ivaTotal, 
								cd.costoDetalleTotal AS costoDetalleTotal, 
								cd.costoDetalleTotalIVA AS costoDetalleTotalIVA, 
								cd.flgRevisionDetalle AS flgRevisionDetalle,
								bod.codSucursalBodega AS codSucursalBodega
							FROM comp_compras_detalle$yearBDDetalle cd
							JOIN prod_productos prod ON prod.productoId = cd.productoId
							JOIN cat_inventario_marcas m ON m.marcaId = prod.marcaId
							JOIN cat_sucursales_bodegas bod ON bod.bodegaId = cd.bodegaId
							WHERE cd.compraId = ? AND cd.flgDelete = ? AND cd.productoId IS NOT NULL
						", [$tablaExportacionId, 0]);

					$numLineaMagic = 1;
					foreach ($dataComprasDetalle as $compraDetalle) {
						$departamentoMagic = $compraDetalle->codSucursalBodega;
						$claseMagic = ""; // No aplica
						$modeloMagic = ""; // No aplica
						$diferenciaPrecioMagic = 0;

						$insertDetalle = [
							"No_Pedido" => $numPedidoMagicUpdate,
							"No_Linea" => $numLineaMagic,
							"Departamento" => $departamentoMagic,
							"Linea" => $compraDetalle->abreviaturaMarca,
							"Clase" => $claseMagic,
							"Modelo" => $modeloMagic,
							"Codigo_Producto" => $compraDetalle->codMagic,
							"Cantidad" => number_format($compraDetalle->cantidadProducto, 2, ".", ""),
							"Precio_Compra" => number_format($compraDetalle->costoUnitario, 2, ".", ""),
							"Importe" => number_format($compraDetalle->costoDetalleTotal, 2, ".", ""),
							"Diferencial_Precio" => $diferenciaPrecioMagic
						];

						// Estas tablas no tienen auditoria, cambiar de momento la variable para evitar error en el wrapper
						$_SESSION["writeBitacora"] = "no";
						$magicDetalleId = $magic->insert("magic_lineas_compras", $insertDetalle);

						// Validacion temporal ya que numeroControl no se puede digitar en Cloud de momento
						if ($numeroControlEntero == 0) {
							$documentoKardexMagic = "D." . substr($numFacturaCCF, 0, 8);
						} else {
							// Esta es la variable ya correcta cuando se corrija esa situacion
							$documentoKardexMagic = "D.{$numeroControlEntero}";
						}

						$insertKardex = [
							"Departamento" => $departamentoMagic,
							"Codigo" => $compraDetalle->codMagic,
							"Fecha_Movimiento" => $fechaDeclaracionContable,
							"Documento" => $documentoKardexMagic,
							"Ingreso_Egreso" => "I",
							"Unidades" => number_format($compraDetalle->cantidadProducto, 2, ".", ""),
							"Valor_venta_compra" => 0,
							"alfa" => ""
						];
						// Estas tablas no tienen auditoria, cambiar de momento la variable para evitar error en el wrapper
						$_SESSION["writeBitacora"] = "no";
						$magicKadexId = $magic->insert("magic_kardex", $insertKardex);
						// Falta campo ID, que lo tiene Autoincremental en BD magic_cloud SQL Server

						$numLineaMagic++;
					}

					// Validar e ingresar el detalle contable (si aplica, no es para todas las Compras)
					$dataDetalleContable = $cloud->rows("
							SELECT
								cc.compraContaId AS compraContaId, 
								cc.cuentaContableId AS cuentaContableId, 
								conta.numCuenta AS numCuentaContable,
								cc.concepto AS concepto, 
								cc.valorCargo AS valorCargo, 
								cc.valorAbono AS valorAbono
							FROM comp_compras_contabilidad$yearBDDetalle cc
							JOIN conta_cuentas conta ON conta.cuentaContableId = cc.cuentaContableId
							WHERE cc.compraId = ? AND cc.flgDelete = ?
						", [$tablaExportacionId, 0]);

					foreach ($dataDetalleContable as $detalleContable) {
						// Validacion temporal ya que numeroControl no se puede digitar en Cloud de momento
						if ($numeroControlEntero == 0) {
							$documentoDCC = "CF." . substr($numFacturaCCF, 0, 8);
						} else {
							// Esta es la variable ya correcta cuando se corrija esa situacion
							$documentoDCC = "CF.{$numeroControlEntero}";
						}

						$insertDetalleContable = [
							"No_pedido_dcc" => $numPedidoMagicUpdate,
							"Fecha_declaracion_dcc" => $fechaDeclaracionContable,
							"Cuenta_contable_dcc" => $detalleContable->numCuentaContable,
							"Concepto_dcc" => $detalleContable->concepto,
							"Documento_dcc" => substr($documentoDCC, 0, 9),
							"Vendedor" => 0
						];

						if ($detalleContable->valorCargo > 0) {
							$insertDetalleContable += [
								"Valor_cargo_dcc" => $detalleContable->valorCargo
							];
						} else {
							// Se deja vacío
						}

						if ($detalleContable->valorAbono > 0) {
							$insertDetalleContable += [
								"Valor_abono_dcc" => $detalleContable->valorAbono
							];
						} else {
							// Se deja vacío
						}

						// Estas tablas no tienen auditoria, cambiar de momento la variable para evitar error en el wrapper
						$_SESSION["writeBitacora"] = "no";
						$magicDetalleContableId = $magic->insert("magic_DetalleContableCompras", $insertDetalleContable);
					}

					// Validar e ingresar el documento contable de liquidación (si aplica, no todas las compras llevan)
					$dataDCL = $cloud->row("
							SELECT
								compraDCLId, 
								tipoGeneracionDocId, 
								numeroControl, 
								selloRecibido, 
								numFactura, 
								proveedorId, 
								fechaFactura, 
								estadoDCL, 
								obsAnularDCL, 
								cantidadDoc, 
								codLiquidacion, 
								descripcionSinPercepcion, 
								comision, 
								iva, 
								ivaComision, 
								ivaPercibido, 
								liquidoPagar, 
								montoSinPercepcion, 
								montoSujetoPercepcion, 
								obsJson, 
								periodoLiquidacionInicio, 
								periodoLiquidacionFin, 
								porcentajeComision, 
								subtotal, 
								valorOperacion
							FROM comp_compras_dcl$yearBDDetalle
							WHERE compraId = ? AND flgDelete = ?
						", [$tablaExportacionId, 0]);

					if ($dataDCL) {
						// DTE-03-S020P004-000000000002532
						$arrayUnico = explode("-", $dataDCL->numeroControl);
						$numeroDCLMagic = (int) $arrayUnico[3]; // Para los últimos digitos del "DTE-"
						$numeroControlMagic = $dataCompra->numeroControl;

						$fechaDCL = date("d/m/y", strtotime($dataDCL->fechaFactura));
						// No_DCL es compraId porque "0" se repite y tira error de relaciones
						$insertDCL = [
							"No_DCL" => $tablaExportacionId,
							"Control" => "0",
							"Codigo_documento" => "2",
							"Numero_Factura" => $numeroControlEntero,
							"Vendedor" => "0",
							"No_Registro" => $nrcProveedor,
							"FechaDeclaracion" => $fechaDeclaracionContable,
							"Partida" => NULL,
							"Valor_documento" => number_format($dataDCL->valorOperacion, 2, ".", ""),
							"Descuento" => 0,
							"Valor_neto" => number_format($dataDCL->montoSujetoPercepcion, 2, ".", ""),
							"Retencion" => number_format($dataDCL->ivaPercibido, 2, ".", ""),
							"Estacion" => "11",
							"Division" => "1",
							"Serie" => $dataDCL->numFactura,
							"FechaDocumento" => $fechaDCL,
							"DTE" => $dataDCL->numeroControl
						];

						// Estas tablas no tienen auditoria, cambiar de momento la variable para evitar error en el wrapper
						$_SESSION["writeBitacora"] = "no";
						$magicDCLId = $magic->insert("magic_Enca_DCL", $insertDCL);
					} else {
						// La compra no tenía DCL ingresada
					}

					// Validar e ingresar los anexos de compras a la tabla que corresponde en Magic
					$arrayColumnaAnexoMagic = array("S", "T", "U", "V");
					for ($x = 0; $x < count($arrayColumnaAnexoMagic); $x++) {
						$codigoAnexoMagic = "";
						$descripcionAnexoMagic = "";
						switch ($arrayColumnaAnexoMagic[$x]) {
							case 'S':
								// Según explicación de Marvin, son valores fijos 20-02-2025
								$codigoAnexoMagic = "1";
								$descripcionAnexoMagic = "GRAVADA";
								break;

							case 'U':
								// Según explicación de Marvin, son valores fijos 20-02-2025
								$codigoAnexoMagic = "2";
								$descripcionAnexoMagic = "COMERCIO";
								break;

							case 'T':
								$codigoAnexoMagic = $clasificacionAnexoT;
								$descripcionAnexoMagic = mb_strtoupper($descripcionAnexoT);
								break;

							case 'V':
								$codigoAnexoMagic = $clasificacionAnexoV;
								$descripcionAnexoMagic = mb_strtoupper($descripcionAnexoV);
								break;

							default:
								// Columna no definida o columna nueva, no aplica
								break;
						}
						$insertAnexo = [
							"Columna" => $arrayColumnaAnexoMagic[$x],
							"Codigo" => $codigoAnexoMagic,
							"Descripcion" => substr($descripcionAnexoMagic, 0, 49),
							"NumeroPedidoACC" => $numPedidoMagicUpdate,
							"Fecha" => $fechaDeclaracionContable
						];
						// Estas tablas no tienen auditoria, cambiar de momento la variable para evitar error en el wrapper
						$_SESSION["writeBitacora"] = "no";
						$magicAnexoId = $magic->insert("magic_Anexo_complemento_compras2", $insertAnexo);
					}
				}
				$updateCorrelativo = [
					"No_Corr_Temp" => $numPedidoMagic
				];
				$whereCorrelativo = ["Tipo_docto" => "CL"];
				// Estas tablas no tienen auditoria, cambiar de momento la variable para evitar error en el wrapper
				$_SESSION["writeBitacora"] = "no";
				$magic->update("CorrelCompras", $updateCorrelativo, $whereCorrelativo);

				$updateEstado = [
					"estadoExportacion" => "Finalizado"
				];
				$whereEstado = ["bitExportacionMagicId" => $_POST["bitExportacionMagicId"]];
				$cloud->update("bit_exportaciones_magic", $updateEstado, $whereEstado);

				echo "success";
			} else {
				echo "Las Compras en esta sincronización ya fueron enviadas a Magic.";
			}
			break;
		case "nueva-cuenta-contable":


			$numeroCuenta = $_POST['numeroCuenta'];

			$existe = $cloud->row("SELECT cuentaContaId FROM conta_cuentas_contables WHERE numeroCuenta = ? AND flgDelete = 0", [$numeroCuenta]);
			if ($existe) {
				echo "El número de cuenta ya existe. Por favor ingrese otro.";
				exit;
			}

			$cuentaPadreId = empty($_POST['cuentaPadreId']) ? 0 : $_POST['cuentaPadreId'];
			$flgCentroCostos = $_POST['flgCentroCostos'];
			$centroCostoId = ($flgCentroCostos === "Si" && !empty($_POST['centroCostoId'])) ? $_POST['centroCostoId'] : null;
			$subCentroCostoId = ($flgCentroCostos === "Si" && !empty($_POST['subCentroCostoId'])) ? $_POST['subCentroCostoId'] : null;

			// Calcular nivelCuenta
			$nivelCuenta = 1;
			$padreActual = $cuentaPadreId;

			while (!empty($padreActual) && $padreActual != 0) {
				$cuentaPadre = $cloud->row("SELECT cuentaPadreId FROM conta_cuentas_contables WHERE cuentaContaId = ? AND flgDelete = 0", [$padreActual]);
				if ($cuentaPadre && !empty($cuentaPadre->cuentaPadreId)) {
					$nivelCuenta++;
					$padreActual = $cuentaPadre->cuentaPadreId;
				} else {
					break;
				}
			}

			$insert = [
				"numeroCuenta" => $numeroCuenta,
				"descripcionCuenta" => $_POST['descripcionCuenta'],
				"tipoCuenta" => $_POST['tipoCuenta'],
				"tipoMayoreo" => $_POST['tipoMayoreo'],
				"categoriaCuenta" => $_POST['categoriaCuenta'],
				"cuentaPadreId" => $cuentaPadreId,
				"nivelCuenta" => $nivelCuenta,
				"flgCentroCostos" => $flgCentroCostos,
				"centroCostoId" => $centroCostoId,
				"centroCostoDetalleId" => $subCentroCostoId
			];


			$cloud->insert("conta_cuentas_contables", $insert);

			$cloud->writeBitacora("movInsert", "($fhActual) Agregó una nueva cuenta contable: $_POST[tipoCuenta] con número de cuenta: $numeroCuenta");

			echo "success";
			break;

		case 'centros-costos':
			/*
				POST:
					typeOperation: insert
					operation: centros-costos
					cuentaContaId: 0
					codigoCentroCosto: as
					nombreCentroCosto: sasa
			*/
			$existeCentroCosto = $cloud->count("
						SELECT codigoCentroCosto, nombreCentroCosto FROM conta_centros_costo
						WHERE nombreCentroCosto = ? AND codigoCentroCosto = ? AND flgDelete = ?
					", [$_POST['nombreCentroCosto'], $_POST['codigoCentroCosto'], 0]);

			if ($existeCentroCosto == 0) {
				$insert = [
					'nombreCentroCosto' => $_POST['nombreCentroCosto'],
					'codigoCentroCosto' => $_POST['codigoCentroCosto'],
					'estadoCentroCosto' => "Activo"
				];
				$cloud->insert("conta_centros_costo", $insert);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Insertó una nuevo centro de costo " . ": " . $_POST['nombreCentroCosto'] . " , ");

				echo "success";
			} else {
				echo 'El centro de costo ' . $_POST['nombreCentroCosto'] . ' ya fue creado.';
			}
			break;
		case 'sub-centros-costos':
			/*
				POST:
					typeOperation: insert
					operation: sub-centros-costos
					subCentroCostoId: 0
					codigoSubcentroCosto: ln
					nombreSubcentroCosto: xd
			*/
			$existeCentroCosto = $cloud->count("
						SELECT codigoSubcentroCosto, nombreSubcentroCosto FROM conta_subcentros_costo
						WHERE nombreSubcentroCosto = ? AND codigoSubcentroCosto = ? AND flgDelete = ?
					", [$_POST['nombreSubcentroCosto'], $_POST['codigoSubcentroCosto'], 0]);

			if ($existeCentroCosto == 0) {
				$insert = [
					'nombreSubcentroCosto' => $_POST['nombreSubcentroCosto'],
					'codigoSubcentroCosto' => $_POST['codigoSubcentroCosto'],
					'estadoSubCentroCosto' => "Activo"
				];
				$cloud->insert("conta_subcentros_costo", $insert);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Insertó una nuevo sub centro de costo " . ": " . $_POST['nombreSubcentroCosto'] . " , ");

				echo "success";
			} else {
				echo 'El Sub centro de costo ' . $_POST['nombreSubcentroCosto'] . ' ya fue creado.';
			}
			break;

		case 'subcentros-costos-detalle':
			/*
				POST:
					typeOperation: insert
					operation: subcentros-costos-detalle
					centroCostoId: 5
					subCentroCostoId: 2
					valorSubCentroTexto: (LN01) SHTILL
					tblsubCentroDetalle_length: esta tabla de detalles
			*/
			$insert = [
				'centroCostoId' => $_POST['centroCostoId'],
				'subCentroCostoId' => $_POST['subCentroCostoId'],
				'nombreCentroCostoDetalle' => $_POST['valorSubCentroTexto']
			];
			$cloud->insert("conta_centros_costo_detalle", $insert);
			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Insertó una nuevo detalle de centro de costo ");

			echo "success";

			break;
		case 'nueva-partida-contable':
			/*
				POST:
					typeOperation: insert
					operation: nueva-partida-contable
					numPartida: 00000003
					periodoPartidas: 5
					tipoPartidas: 1
					fechaPartida: 2025-05-21
					descripcionPartida: ESTE ES UNA PARTIDA DE PRUEBA 
			*/
			$insert = [
				'tipoPartidaId' => $_POST['tipoPartidas'],
				'partidaContaPeriodoId' => $_POST['periodoPartidas'],
				'numPartida' => $_POST['numPartida'],
				'estadoPartidaContable' => 'Pendiente',
				'descripcionPartida' => $_POST['descripcionPartida'],
				'fechaPartida' => $_POST['fechaPartida']
			];
			$partidaContableId = $cloud->insert("conta_partidas_contables", $insert);
			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Insertó una nueva partida contable");
			echo json_encode([
				'status' => 'success',
				'partidaContableId' => $partidaContableId,
				'tipoPartidaId' => $_POST['tipoPartidas']
			]);

			break;
		case 'nueva-partida-contable-detalle':

			$centroDeCostoId = (int) ($_POST['centroCostoId'] ?? 0);
			$subCentroCostoId = (int) ($_POST['subCentroCostoId'] ?? 0);

			if (isset($_POST['documentoId']) && $_POST['documentoId']) {
				$dataCompra = $cloud->row("SELECT numeroControl AS numFactura FROM comp_compras$yearBD WHERE compraId = ? AND flgDelete = 0", [$_POST['documentoId']]);
				$insert = [
					'partidaContableId' => $_POST['partidaContableId'],
					'centroCostoId' => $centroDeCostoId,
					'subCentroCostoId' => $subCentroCostoId,
					'tipoDTEId' => $_POST['tipoDTEId'] ?? 0,
					'documentoId' => $_POST['documentoId'],
					'numDocumento' => $dataCompra->numFactura ?? 0,
					'partidaContaPeriodoId' => $_POST['partidaContaPeriodoId'],
					'cuentaContaId' => $_POST['cuentaId'],
					'descripcionPartidaDetalle' => $_POST['descripcion'],
					'cargos' => abs((float) str_replace(',', '', $_POST['cargos'])),
					'abonos' => abs((float) str_replace(',', '', $_POST['abonos']))
				];
			} else {
				$insert = [
					'partidaContableId' => $_POST['partidaContableId'],
					'centroCostoId' => $centroDeCostoId,
					'subCentroCostoId' => $subCentroCostoId,
					'partidaContaPeriodoId' => $_POST['partidaContaPeriodoId'],
					'cuentaContaId' => $_POST['cuentaId'],
					'descripcionPartidaDetalle' => $_POST['descripcion'],
					'cargos' => abs((float) str_replace(',', '', $_POST['cargos'])),
					'abonos' => abs((float) str_replace(',', '', $_POST['abonos']))
				];
			}

			$inserted = $cloud->insert("conta_partidas_contables_detalle", $insert);

			if (!empty($inserted)) {
				$result = $cloud->row("
            SELECT 
                COALESCE(SUM(cargos), 0) AS cargos,
                COALESCE(SUM(abonos), 0) AS abonos
            FROM conta_partidas_contables_detalle
            WHERE partidaContableId = ? AND flgDelete = 0
        ", [$_POST['partidaContableId']]);

				if ($result) {
					$params = [
						'cargoPartida' => (float) $result->cargos,
						'abonoPartida' => (float) $result->abonos
					];
					$whereP = ['partidaContableId' => $_POST['partidaContableId']];
					$cloud->update("conta_partidas_contables", $params, $whereP);
				}

				$cloud->writeBitacora("movInsert", "($fhActual) Insertó nuevo detalle y actualizó totales de partida #" . $_POST['partidaContableId']);

				echo json_encode([
					'status' => 'success',
					'partidaContableId' => $_POST['partidaContableId']
				]);
			}
			break;
		case 'generado-partida':

			// Validar tipoPartida
			if (!isset($_POST['tipoPartida']) || empty($_POST['tipoPartida'])) {
				echo 'No se recibió un tipo de partida válido.';
				exit;
			}
			$tipoPartida = (int) $_POST['tipoPartida'];

			if ($tipoPartida > 0) {
				switch ($tipoPartida) {
					case 5:
						if (!isset($_POST['fechaLiquidacion']) || empty($_POST['fechaLiquidacion'])) {
							echo 'Debe especificar la fecha a generar partida.';
							exit;
						}

						$response = partidaAutomaticasVentasContado($cloud, $_POST['fechaLiquidacion']);
						$responseData = json_decode($response);

						if ($responseData && isset($responseData->status) && $responseData->status === "success") {
							echo json_encode([
								'status' => 'success',
								'partidaContableId' => $responseData->partidaContableId,
								'tipoPartidaId' => $responseData->tipoPartidaId,
							]);
						} else {
							echo $response; // imprime el mensaje de error que venga de la función
						}
						break;
					case 1:
						if (!isset($_POST['fechaLiquidacion']) || empty($_POST['fechaLiquidacion'])) {
							echo 'Debe especificar la fecha a generar partida.';
							exit;
						}

						$response = partidaAutomaticasVentasCredito($cloud, $_POST['fechaLiquidacion']);
						$responseData = json_decode($response);

						if ($responseData && isset($responseData->status) && $responseData->status === "success") {
							echo json_encode([
								'status' => 'success',
								'partidaContableId' => $responseData->partidaContableId,
								'tipoPartidaId' => $responseData->tipoPartidaId,
							]);
						} else {
							echo $response; // imprime el mensaje de error que venga de la función
						}
						break;
					case 18:
						// Validar fechaLiquidacion
						if (!isset($_POST['fechaLiquidacion']) || empty($_POST['fechaLiquidacion'])) {
							echo 'Debe especificar la fecha de liquidación de compras.';
							exit;
						}

						// Validar cuentaContablePago
						if (!isset($_POST['cuentaContablePago']) || empty($_POST['cuentaContablePago'])) {
							echo 'Debe seleccionar una cuenta contable de pago.';
							exit;
						}

						// Si pasa todas las validaciones ejecutamos
						$partidaId = buscarComprasDiariasPartida($_POST['fechaLiquidacion'], $cloud);
						if ($partidaId > 0) {
							$responseFinalizada = buscarComprasDiariasPartidaFinalizada($_POST['fechaLiquidacion'], $cloud);
							if ($responseFinalizada == "success") {
								$response = liquidacionComprasDiariasPartida($partidaId, $_POST['cuentaContablePago'], $cloud);
								$responseData = json_decode($response);

								if ($responseData && $responseData->status === "success") {
									echo json_encode([
										'status' => 'success',
										"partidaContableId" => $responseData->partidaContableId,
										"tipoPartidaId" => $responseData->tipoPartidaId,
									]);
								} else {
									echo $response;
								}
							} else {
								echo $responseFinalizada;
							}
						} else {
							echo "No se encontró ninguna partida de <b>compras diarias</b> registrada en la fecha ingresada.";
						}
						break;
					case 101:
						if (!isset($_POST['retaceoId']) || empty($_POST['retaceoId'])) {
							echo 'Debe especificar un retaceoId.';
							exit;
						}
						$response = liquidacionRetaceoPartida($_POST['retaceoId'], $cloud, $_POST['fechaLiquidacionRe']);

						if ($response == 'success') {
							echo json_encode([
								'status' => 'success',
							]);
						} else {
							echo $response;
						}
						break;
					default:
						$res = partidasRepetitivas($cloud, $_POST['fechaRepetitiva']);
						if ($res == "success") {
							echo json_encode([
								'status' => 'success'
							]);
						} else {
							echo $res;
						}
						//echo "El tipo de partida seleccionado no está autorizado para su registro.";
						break;
				}
			} else {
				echo "El tipo de partida seleccionado no está autorizado para su registro.";
			}
			break;
		case 'bit-mayorizacion':
			/*
				POST:
					typeOperation: insert
					operation: bit-mayorizacion
					fechaMayorizacionInicio: partidaContaPeriodoId
					fechaMayorizacionFin: partidaContaPeriodoId
			*/
			$periodoInicio = $_POST['fechaMayorizacionInicio'] ?? 0;
			$periodoFinal = $_POST['fechaMayorizacionFin'] ?? 0;

			$periodoCerrado = $cloud->row("SELECT COUNT(partidaContaPeriodoId) AS total 
					FROM conta_partidas_contables_periodos 
					WHERE estadoPeriodoPartidas = ?
					AND partidaContaPeriodoId BETWEEN ? AND ?", ['Finalizado', $periodoInicio, $periodoFinal]);

			if ($periodoCerrado->total == 0) {

				$result = $cloud->run("CALL sp_mayorizar_rango_periodos_completo_final_v24(?,?)", [$periodoInicio, $periodoFinal]);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") se genero una mayorización desde el periodo de " . $_POST["fechaMayorizacionInicio"]);
				if ($result) {
					$insert = [
						"personaId" => $_POST['personaId'],
						"descripcionMayorizacion" => $_POST['desc'],
						"fechaMayorizacionIncio" => $periodoInicio,
						"fechaMayorizacionFinal" => $periodoFinal
					];
					$cloud->insert("bit_mayorizacion", $insert);
					$cloud->writeBitacora("movInsert", "(" . $fhActual . ") se genero una bitacora de mayorización mayorización desde el periodo de " . $periodoInicio);
					echo json_encode(['status' => 'success']);
				} else {
					echo "Se produjo un error al ejecutar la mayorización. Por favor, intente nuevamente más tarde.";
				}

				/*$partidaPendiente = $cloud->row("SELECT COUNT(partidaContaPeriodoId) AS total 
						FROM conta_partidas_contables
						WHERE estadoPartidaContable = ?
						AND partidaContaPeriodoId BETWEEN ? AND ?;", ['Pendiente', $periodoInicio, $periodoFinal]);

				if ($partidaPendiente->total == 0) {

				} else {
					echo "No es posible realizar la mayorización en el rango de periodos seleccionado. Existen partidas pendientes o con información incompleta.";
				}*/
			} else {
				echo "No es posible realizar la mayorización en el rango de periodos seleccionado, ya que existen periodos cerrados dentro del intervalo.";
			}
			break;

		case 'traspasar-partida-contable-detalle':
			/*
				POST:
					operation: traspasar-partida-contable-detalle
					partidaIdOrigen: 10
					detalleId: 55
					partidaIdDestino: 20
			*/

			$partidaIdOrigen = (int) ($_POST['partidaIdOrigen'] ?? 0);
			$detalleId = (int) ($_POST['detalleId'] ?? 0);
			$partidaIdDestino = (int) ($_POST['partidaIdDestino'] ?? 0);

			// 1. Obtener detalle origen

			$partidaTipo = $cloud->row("
				SELECT tipoPartidaId
				FROM conta_partidas_contables
				WHERE partidaContableId = ?
			", [$partidaIdOrigen]);

			$periodoDestino = $cloud->row("
				SELECT partidaContaPeriodoId
				FROM conta_partidas_contables
				WHERE partidaContableId = ?
			", [$partidaIdDestino]);

			$detalle = $cloud->row("
				SELECT *
				FROM conta_partidas_contables_detalle
				WHERE partidaContableDetalleId = ? AND partidaContableId = ?
			", [$detalleId, $partidaIdOrigen]);

			if (!$detalle) {
				echo "Detalle no encontrado";
				break;
			}

			// 2. Preparar insert con nuevo partidaIdDestino
			$insert = [
				'partidaContableId' => $partidaIdDestino,
				'idDetPartida' => $detalle->idDetPartida,
				'cuentaContaId' => $detalle->cuentaContaId,
				'partidaContaPeriodoId' => $periodoDestino->partidaContaPeriodoId,
				'tipoDTEId' => $detalle->tipoDTEId,
				'documentoId' => $detalle->documentoId,
				'numDocumento' => $detalle->numDocumento,
				'descripcionPartidaDetalle' => $detalle->descripcionPartidaDetalle,
				'filtroBusqueda' => $detalle->filtroBusqueda,
				'cargos' => $detalle->cargos,
				'abonos' => $detalle->abonos
			];

			$nuevoId = $cloud->insert("conta_partidas_contables_detalle", $insert);

			if ($nuevoId) {
				// 3. Eliminar detalle origen (puedes usar soft delete si prefieres)
				$cloud->deleteById("conta_partidas_contables_detalle", "partidaContableDetalleId", $detalleId);

				// 4. Recalcular sumas del origen
				$resOrigen = $cloud->row("
            SELECT SUM(cargos) AS cargos, SUM(abonos) AS abonos
            FROM conta_partidas_contables_detalle
            WHERE partidaContableId = ? AND flgDelete = 0
        ", [$partidaIdOrigen]);

				$updateOrigen = [
					'cargoPartida' => floatval($resOrigen->cargos ?? 0),
					'abonoPartida' => floatval($resOrigen->abonos ?? 0)
				];
				$cloud->update("conta_partidas_contables", $updateOrigen, ['partidaContableId' => $partidaIdOrigen]);

				// 5. Recalcular sumas del destino
				$resDestino = $cloud->row("
            SELECT SUM(cargos) AS cargos, SUM(abonos) AS abonos
            FROM conta_partidas_contables_detalle
            WHERE partidaContableId = ? AND flgDelete = 0
        ", [$partidaIdDestino]);

				$updateDestino = [
					'cargoPartida' => floatval($resDestino->cargos ?? 0),
					'abonoPartida' => floatval($resDestino->abonos ?? 0)
				];
				$cloud->update("conta_partidas_contables", $updateDestino, ['partidaContableId' => $partidaIdDestino]);

				// Bitácora
				$cloud->writeBitacora("movInsert", "Traspasó el detalle $detalleId de partida $partidaIdOrigen a $partidaIdDestino");

				echo json_encode([
					'status' => 'success',
					"partidaContableId" => $partidaIdDestino,
					"tipoPartidaId" => $partidaTipo->tipoPartidaId,
				]);
			} else {
				echo "El tipo de partida seleccionado no está autorizado para su registro.";
			}

			break;
	}
} else {
	header("Location: /indupal-cloud/app/");
}
