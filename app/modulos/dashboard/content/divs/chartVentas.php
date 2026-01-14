<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();
	/*
		POST:
		chartName
		meses = multiple
		anios = multiple
	*/
	$parametrizacionIVA = 1.13;
	// Para los gráficos que no son parametrizados
	// Rojo, verde, azul, amarillo y magenta
	$arrayColores = array("#FF0000", "#00FF00", "#0000FF", "#FFFF00", "#FF00FF");
	$arrayMeses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
?>
<?php 
	if(isset($_POST['meses']) && isset($_POST['anios'])) {
		// Variables generales para todos los top según filtros
		$whereRangoFechas = "AND (";
		$numAnio = 0; $numMes = 0;
		if($_POST['chartName'] == 'resumenAnual') {
			// Se iteran los 12 meses más abajo con su propio where
			// Solo se puede seleccionar 1 año, no es multiple
			$whereRangoFechas = '';
		} else {
			foreach($_POST['anios'] as $anio) {
				$numAnio += 1;
				foreach($_POST['meses'] as $mes) {
					$numMes += 1;
					$whereRangoFechas .= ($numMes > 1 ? ' OR ' : '') . "fechaFactura BETWEEN '$anio-$mes-01' AND '$anio-$mes-".date("t", strtotime(date($anio.'-'.$mes.'-01'))) . "'";
				}
			}
		}
		$whereRangoFechas .= ")";
		$numTop = 5;

		if($_POST['chartName'] == 'ventasSucursal') {
			// Ventas Sucursal
				$dataParametrizacion = $cloud->rows("
					SELECT
						dashParamId, tituloParametrizacion, colorParametrizacion
					FROM dash_parametrizacion
					WHERE tipoParametrizacion = ? AND flgDelete = ?
					ORDER BY tituloParametrizacion
				", ['Sucursal', 0]);
				$arraySucursalTotal = array();

				$numSucursal = 0;
				foreach($dataParametrizacion as $dataParametrizacion) {
					$dataParametrizacionDetalle = $cloud->rows("
						SELECT 
							dashParamDetalleId, valorParametrizacion 
						FROM dash_parametrizacion_detalle
						WHERE dashParamId = ? AND flgDelete = ?
					", [$dataParametrizacion->dashParamId, 0]);

					$totalGeneralSucursal = 0;
					foreach($dataParametrizacionDetalle as $dataParametrizacionDetalle) {
						// F = Contado, Abonos no son tomados en cuenta ya que se abona varias veces en diferentes periodos
						$dataVentaSucursal = $cloud->row("
							SELECT
								SUM(
									CASE 
								    	WHEN codTipoFactura IN (1, 8) 
								    		THEN (totalVenta / $parametrizacionIVA) 
								    	ELSE totalVenta 
									END
								) AS totalVenta
							FROM conta_comision_pagar_calculo
							WHERE flgIdentificador = ? AND sucursalFactura = ? AND flgDelete = ? $whereRangoFechas
						", ['F', $dataParametrizacionDetalle->valorParametrizacion, 0]);
						$totalGeneralSucursal += $dataVentaSucursal->totalVenta;
					} // dataParametrizacionDetalle
					$arraySucursalTotal[] = [
						"Sucursal" 			=> $dataParametrizacion->tituloParametrizacion,
						"totalVenta" 		=> $totalGeneralSucursal,
						"color" 			=> $dataParametrizacion->colorParametrizacion
					];
					$numSucursal += 1;
				} // dataParametrizacion

				$chartLabels = array(); $chartDataset = array(); $dataset = array();

				$x = 0;
				foreach($arraySucursalTotal as $sucursal) {
					$chartLabels[] = $sucursal["Sucursal"];
					// Otro for para llenar de "cero" las demás posiciones
					for ($i=0; $i < $numTop; $i++) { 
						$dataset[] = ($i == $x ? $sucursal["totalVenta"] : 0);
					}
					$chartDataset[] = array(
						"label" 				=> $sucursal["Sucursal"],
						"backgroundColor" 		=> $sucursal["color"],
						"data" 					=> $dataset,
						"borderWidth" 			=> 3,
						"borderColor" 			=> $sucursal["color"]
					);
					unset($dataset);
					$x += 1;
				}
			// Fin Ventas Sucursal
			if($numSucursal > 0) {
?>
				<canvas id="ventasSucursal"></canvas>
				<script>
					chartVentasSucursal = new Chart(document.getElementById("ventasSucursal"), {
				        type: "bar",
				        data: {
					        labels: <?php echo json_encode($chartLabels); ?>,
					        datasets: <?php echo json_encode($chartDataset); ?>
				        },
				        options: {
				            indexAxis: 'x',
				            elements: {
				                bar: {
				                    borderWidth: 3,
				                },
				                responsive: true,
				                plugins: {
				                    legend: {
				                        position: 'right',
				                    }
				                }
				            },
							scales: {
								y: {
									ticks: {
										callback: function(value, index, values) {
											return '$ ' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
										}
									}
								}
							},
				            plugins: {
				                tooltip: {
				                    callbacks: {
				                        label: function(context) {
				                            var label = context.dataset.label || '';
				                            if (label) {
				                                label += ': ';
				                            }
				                            label += '$ ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
				                            return label;
				                        }
				                    }
				                }
				            }
				        }
				    });
				</script>
<?php
			} else {
				echo "No se han parametrizado sucursales";
			}
		} else if($_POST['chartName'] == 'ventasUDN') {
			// Ventas Unidad de negocio
				$dataParametrizacion = $cloud->rows("
					SELECT
						dashParamId, tituloParametrizacion, colorParametrizacion
					FROM dash_parametrizacion
					WHERE tipoParametrizacion = ? AND flgDelete = ?
					ORDER BY tituloParametrizacion
				", ['Unidad de negocio', 0]);
				$arrayUDNTotal = array();

				$numUDN = 0;
				foreach($dataParametrizacion as $dataParametrizacion) {
					$dataParametrizacionDetalle = $cloud->rows("
						SELECT 
							dashParamDetalleId, valorParametrizacion 
						FROM dash_parametrizacion_detalle
						WHERE dashParamId = ? AND flgDelete = ?
					", [$dataParametrizacion->dashParamId, 0]);

					$totalGeneralUDN = 0;
					foreach($dataParametrizacionDetalle as $dataParametrizacionDetalle) {
						// F = Contado, Abonos no son tomados en cuenta ya que se abona varias veces en diferentes periodos
						$dataVentaSucursal = $cloud->row("
							SELECT
								SUM(
									CASE 
								    	WHEN codTipoFactura IN (1, 8) 
								    		THEN (totalVenta / $parametrizacionIVA) 
								    	ELSE totalVenta 
									END
								) AS totalVenta
							FROM conta_comision_pagar_calculo
							WHERE flgIdentificador = ? AND nombreEmpleado = ? AND flgDelete = ? $whereRangoFechas
						", ['F', $dataParametrizacionDetalle->valorParametrizacion, 0]);
						$totalGeneralUDN += $dataVentaSucursal->totalVenta;
					} // dataParametrizacionDetalle
					$arrayUDNTotal[] = [
						"UDN" 				=> $dataParametrizacion->tituloParametrizacion,
						"totalVenta" 		=> $totalGeneralUDN,
						"color" 			=> $dataParametrizacion->colorParametrizacion
					];
					$numUDN += 1;
				} // dataParametrizacion

				$chartLabels = array(); $chartDataset = array(); $dataset = array();

				$x = 0;
				foreach($arrayUDNTotal as $udn) {
					$chartLabels[] = $udn["UDN"];
					// Otro for para llenar de "cero" las demás posiciones
					for ($i=0; $i < $numTop; $i++) { 
						$dataset[] = ($i == $x ? $udn["totalVenta"] : 0);
					}
					$chartDataset[] = array(
						"label" 				=> $udn["UDN"],
						"backgroundColor" 		=> $udn["color"],
						"data" 					=> $dataset,
						"borderWidth" 			=> 3,
						"borderColor" 			=> $udn["color"]
					);
					unset($dataset);
					$x += 1;
				}
			// Fin Ventas Unidad de negocio
			if($numUDN > 0) {
?>
				<canvas id="ventasUDN"></canvas>
				<script>
					chartVentasUDN = new Chart(document.getElementById("ventasUDN"), {
				        type: "bar",
				        data: {
					        labels: <?php echo json_encode($chartLabels); ?>,
					        datasets: <?php echo json_encode($chartDataset); ?>
				        },
				        options: {
				            indexAxis: 'x',
				            elements: {
				                bar: {
				                    borderWidth: 3,
				                },
				                responsive: true,
				                plugins: {
				                    legend: {
				                        position: 'right',
				                    }
				                }
				            },
							scales: {
								y: {
									ticks: {
										callback: function(value, index, values) {
											return '$ ' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
										}
									}
								}
							},
				            plugins: {
				                tooltip: {
				                    callbacks: {
				                        label: function(context) {
				                            var label = context.dataset.label || '';
				                            if (label) {
				                                label += ': ';
				                            }
				                            label += '$ ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
				                            return label;
				                        }
				                    }
				                }
				            }
				        }
				    });
				</script>
<?php
			} else {
				echo "No se han parametrizado unidades de negocio";
			}
		} else if($_POST['chartName'] == 'resumenAnual') {
			// Ventas Anual
				$dataParametrizacion = $cloud->rows("
					SELECT
						dashParamId, tituloParametrizacion, colorParametrizacion
					FROM dash_parametrizacion
					WHERE tipoParametrizacion = ? AND flgDelete = ?
					ORDER BY tituloParametrizacion
				", [$_POST['tipoParametrizacion'], 0]);
				$arrayTotalesMes = array();

				$campoVentaMes = "";
				switch($_POST['tipoParametrizacion']) {
					case 'Marca':
						$campoVentaMes = "lineaProducto";
					break;
					
					case 'Unidad de negocio':
						$campoVentaMes = "nombreEmpleado";
					break;

					default:
						$campoVentaMes = "sucursalFactura";
					break;
				}

				$numAnual = 0;
				foreach($dataParametrizacion as $dataParametrizacion) {
					// Iterar los 12 meses
					for ($i=0; $i < count($arrayMeses); $i++) { 
						$fechaInicioMes = date('Y-m-01', strtotime($_POST['anios'] . "-" . ($i + 1) . "-01")); 
						$fechaFinMes = date('Y-m-t', strtotime($fechaInicioMes)); 
						$whereRangoFechas = " AND (fechaFactura BETWEEN '$fechaInicioMes' AND '$fechaFinMes')";

						// Si la consulta queda fuera del bucle da error
						$dataParametrizacionDetalle = $cloud->rows("
							SELECT 
								dashParamDetalleId, valorParametrizacion 
							FROM dash_parametrizacion_detalle
							WHERE dashParamId = ? AND flgDelete = ?
						", [$dataParametrizacion->dashParamId, 0]);

						$totalGeneralMes = 0; $valorParametrizacion = "";
						foreach($dataParametrizacionDetalle as $dataParametrizacionDetalle) {
							if($_POST['tipoParametrizacion'] == 'Marca') {
								$valorParametrizacion = substr($dataParametrizacionDetalle->valorParametrizacion, 1, 2);
							} else {
								$valorParametrizacion = $dataParametrizacionDetalle->valorParametrizacion;
							}
							// F = Contado, Abonos no son tomados en cuenta ya que se abona varias veces en diferentes periodos
							$dataVentaMes = $cloud->row("
								SELECT
									SUM(
										CASE 
									    	WHEN codTipoFactura IN (1, 8) 
									    		THEN (totalVenta / $parametrizacionIVA) 
									    	ELSE totalVenta 
										END
									) AS totalVenta
								FROM conta_comision_pagar_calculo
								WHERE flgIdentificador = ? AND $campoVentaMes = ? AND flgDelete = ? $whereRangoFechas
							", ['F', $valorParametrizacion, 0]);

							$totalGeneralMes += $dataVentaMes->totalVenta;
						} // dataParametrizacionDetalle
						$arrayTotalesMes[$i] = $totalGeneralMes;
					} // for arrayMeses
					$chartDataset[] = [
						"data" 					=> $arrayTotalesMes,
						"label" 				=> $dataParametrizacion->tituloParametrizacion,
						"borderColor" 			=> $dataParametrizacion->colorParametrizacion,
						"backgroundColor" 		=> $dataParametrizacion->colorParametrizacion,
						"fill" 					=> false
					];
					$numAnual += 1;
				} // dataParametrizacion
				$chartLabels = $arrayMeses;
			// Fin Ventas Anual
			if($numAnual > 0) {
?>
				<canvas id="resumenAnual" heigth="300"></canvas>
				<script>
					document.getElementById("divResumenAnualTitle").innerHTML = "Resumen anual: " + "<?php echo $_POST['tipoParametrizacion']; ?>";

				    chartResumenAnual = new Chart(document.getElementById("resumenAnual"), {
				        type: 'line',
				        data: {
				            labels: <?php echo json_encode($chartLabels); ?>,
				            datasets: <?php echo json_encode($chartDataset); ?>
				        },
				        options: {
				            scales: {
				                y: {
				                    ticks: {
				                        callback: function(value, index, values) {
				                            return '$ ' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
				                        }
				                    }
				                }
				            },
				            plugins: {
				                tooltip: {
				                    callbacks: {
				                        label: function(context) {
				                            var label = context.dataset.label || '';
				                            if (label) {
				                                label += ': ';
				                            }
				                            label += '$ ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
				                            return label;
				                        }
				                    }
				                }
				            }
				        }
				    });
				</script>
<?php
			} else {
				echo "No se han parametrizado " . strtolower($_POST['tipoParametrizacion']);
			}
		} else if($_POST['chartName'] == 'topMarcas') {
			// Top de marcas
				$dataParametrizacion = $cloud->rows("
					SELECT
						dashParamId, tituloParametrizacion, colorParametrizacion
					FROM dash_parametrizacion
					WHERE tipoParametrizacion = ? AND flgDelete = ?
				", ['Marca', 0]);
				$arrayMarcaTotal = array();

				$numMarca = 0;
				foreach($dataParametrizacion as $dataParametrizacion) {
					$dataParametrizacionDetalle = $cloud->rows("
						SELECT 
							dashParamDetalleId, valorParametrizacion 
						FROM dash_parametrizacion_detalle
						WHERE dashParamId = ? AND flgDelete = ?
					", [$dataParametrizacion->dashParamId, 0]);

					$totalGeneralMarca = 0;
					foreach($dataParametrizacionDetalle as $dataParametrizacionDetalle) {
						$marcaProducto = substr($dataParametrizacionDetalle->valorParametrizacion, 1, 2);
						// F = Contado, Abonos no son tomados en cuenta ya que se abona varias veces en diferentes periodos
						$dataVentaMarca = $cloud->row("
							SELECT
								SUM(
									CASE 
								    	WHEN codTipoFactura IN (1, 8) 
								    		THEN (totalVenta / $parametrizacionIVA) 
								    	ELSE totalVenta 
									END
								) AS totalVenta
							FROM conta_comision_pagar_calculo
							WHERE flgIdentificador = ? AND lineaProducto = ? AND flgDelete = ? $whereRangoFechas
						", ['F', $marcaProducto, 0]);
						$totalGeneralMarca += $dataVentaMarca->totalVenta;
					} // dataParametrizacionDetalle
					$arrayMarcaTotal[] = [
						"Marca" 			=> $dataParametrizacion->tituloParametrizacion,
						"totalVenta" 		=> $totalGeneralMarca,
						"color" 			=> $dataParametrizacion->colorParametrizacion
					];
					$numMarca += 1;
				} // dataParametrizacion

				usort($arrayMarcaTotal, "comparar_totales");

				$top5Marcas = array_slice($arrayMarcaTotal, 0, $numTop);
				$chartLabels = array(); $chartDataset = array(); $dataset = array();

				$x = 0;
				foreach($top5Marcas as $top) {
					$chartLabels[] = $top["Marca"];
					// Otro for para llenar de "cero" las demás posiciones
					for ($i=0; $i < $numTop; $i++) { 
						$dataset[] = ($i == $x ? $top["totalVenta"] : 0);
					}
					$chartDataset[] = array(
						"label" 				=> $top["Marca"],
						"backgroundColor" 		=> $top["color"],
						"data" 					=> $dataset,
						"borderWidth" 			=> 3,
						"borderColor" 			=> $top["color"]
					);
					unset($dataset);
					$x += 1;
				}
			// Fin Top de Marcas

			if($numMarca > 0) {
?>
				<canvas id="topMarcas" height="500"></canvas>
				<script>
				    chartTopMarcas = new Chart(document.getElementById("topMarcas"), {
					    type: "bar",
					    data: {
					        labels: <?php echo json_encode($chartLabels); ?>,
					        datasets: <?php echo json_encode($chartDataset); ?>
					    },
					    options: {
					        indexAxis: 'y',
					        elements: {
					            bar: {
					                borderWidth: 3,
					            },
					            responsive: true,
					            plugins: {
					                legend: {
					                    position: 'right',
					                }
					            }
					        },
							scales: {
								x: {
									ticks: {
										callback: function(value, index, values) {
											return '$ ' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
										}
									}
								}
							},
							plugins: {
								tooltip: {
									callbacks: {
										label: function(context) {
							                var label = context.dataset.label || '';
							                if (label) {
							                    label += ': ';
							                }
							                label += '$ ' + context.parsed.x.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
							                return label;
										}
									}
								}
							}
					    }
				    });
				</script>
<?php 
			} else {
				echo "No se han parametrizado marcas";
			}
		} else if($_POST['chartName'] == 'topClientes') {
			// Top de clientes
				// Filtrar los clientes del rango de mes/año seleccionado
				$dataClientesFiltro = $cloud->rows("
					SELECT 
						DISTINCT(nombreCliente) AS cliente 
					FROM conta_comision_pagar_calculo
					WHERE flgIdentificador = ? AND flgDelete = ? $whereRangoFechas
				", ['F', 0]);
				$arrayClienteTotal = array();

				$numCliente = 0;
				foreach($dataClientesFiltro as $dataClientesFiltro) {
					// F = Contado, Abonos no son tomados en cuenta ya que se abona varias veces en diferentes periodos
					$dataVentaCliente = $cloud->row("
						SELECT
							SUM(
								CASE 
							    	WHEN codTipoFactura IN (1, 8) 
							    		THEN (totalVenta / $parametrizacionIVA) 
							    	ELSE totalVenta 
								END
							) AS totalVenta
						FROM conta_comision_pagar_calculo
						WHERE flgIdentificador = ? AND nombreCliente = ? AND flgDelete = ? $whereRangoFechas
					", ['F', $dataClientesFiltro->cliente, 0]);
					$arrayClienteTotal[] = [
						"Cliente" 			=> $dataClientesFiltro->cliente,
						"totalVenta" 		=> $dataVentaCliente->totalVenta
					];
					$numCliente += 1;
				} // dataClientesFiltro
				usort($arrayClienteTotal, "comparar_totales");

				$top5Clientes = array_slice($arrayClienteTotal, 0, $numTop);
				$chartLabels = array(); $chartDataset = array(); $dataset = array();

				$x = 0;
				foreach($top5Clientes as $top) {
					$chartLabels[] = $top["Cliente"];
					// Otro for para llenar de "cero" las demás posiciones
					for ($i=0; $i < $numTop; $i++) { 
						$dataset[] = ($i == $x ? $top["totalVenta"] : 0);
					}
					$chartDataset[] = array(
						"label" 				=> $top["Cliente"],
						"backgroundColor" 		=> $arrayColores[$x],
						"data" 					=> $dataset,
						"borderWidth" 			=> 3,
						"borderColor" 			=> $arrayColores[$x]
					);
					unset($dataset);
					$x += 1;
				}
			// Fin Top de Clientes

			if($numCliente > 0) {
?>
				<canvas id="topClientes" height="500"></canvas>
				<script>
				   	chartTopClientes = new Chart(document.getElementById("topClientes"), {
				        type: "bar",
					    data: {
					        labels: <?php echo json_encode($chartLabels); ?>,
					        datasets: <?php echo json_encode($chartDataset); ?>
					    },
				        options: {
				            indexAxis: 'y',
				            elements: {
				                bar: {
				                    borderWidth: 3
				                },
				                responsive: true,
				                plugins: {
				                    legend: {
				                        position: 'right',
				                    }
				                }
				            },
							scales: {
								x: {
									ticks: {
										callback: function(value, index, values) {
											return '$ ' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
										}
									}
								}
							},
							plugins: {
								tooltip: {
									callbacks: {
										label: function(context) {
							                var label = context.dataset.label || '';
							                if (label) {
							                    label += ': ';
							                }
							                label += '$ ' + context.parsed.x.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
							                return label;
										}
									}
								}
							}
				        }
				    });
				</script>
<?php
			} else {
				echo "No se encontraron clientes";
			}
		} else if($_POST['chartName'] == "topProductos") {
			// Top de Productos
				// Filtrar los productos del rango de mes/año seleccionado
				$dataProductosFiltro = $cloud->rows("
					SELECT 
						DISTINCT(codProductoFactura) AS codProducto,
						nombreProducto
					FROM conta_comision_pagar_calculo
					WHERE flgIdentificador = ? AND flgDelete = ? $whereRangoFechas
				", ['F', 0]);
				$arrayProductoTotal = array();

				$numProductos = 0;
				foreach($dataProductosFiltro as $dataProductosFiltro) {
					// F = Contado, Abonos no son tomados en cuenta ya que se abona varias veces en diferentes periodos
					$dataVentaProducto = $cloud->row("
						SELECT
							SUM(
								CASE 
							    	WHEN codTipoFactura IN (1, 8) 
							    		THEN (totalVenta / $parametrizacionIVA) 
							    	ELSE totalVenta 
								END
							) AS totalVenta
						FROM conta_comision_pagar_calculo
						WHERE flgIdentificador = ? AND codProductoFactura = ? AND flgDelete = ? $whereRangoFechas
					", ['F', $dataProductosFiltro->codProducto, 0]);

					$arrayProductoTotal[] = [
						"Producto" 			=> "(" . $dataProductosFiltro->codProducto . ") " . $dataProductosFiltro->nombreProducto,
						"totalVenta" 		=> $dataVentaProducto->totalVenta
					];
					$numProductos += 1;
				} // dataProductosFiltro
				usort($arrayProductoTotal, "comparar_totales");

				$top5Productos = array_slice($arrayProductoTotal, 0, $numTop);
				$chartLabels = array(); $chartDataset = array(); $dataset = array();

				$x = 0;
				foreach($top5Productos as $top) {
					// En el grafico de donut los labels y dataset se asignan de manera distinta
					$chartLabels[] = $top["Producto"];
					$chartDataset[] = $top["totalVenta"];
					$x += 1;
				}
			// Fin Top de Productos

			if($numProductos > 0) {
?>
				<canvas id="topProductos" height="500"></canvas>
				<script>
					chartTopProductos = new Chart(document.getElementById("topProductos"), {
						type: "doughnut",
					    data: {
					        labels: <?php echo json_encode($chartLabels); ?>,
					        datasets: [{
					        	backgroundColor: <?php echo json_encode($arrayColores); ?>,
					        	data: <?php echo json_encode($chartDataset); ?>
					        }]
					    }
					});
				</script>
<?php
			} else {
				// Esto nunca deberia suceder, pero igual queda el else
				echo "No se encontraron productos";
			}
		} else {
			// Esto nunca debería suceder, solo si se maneja la function manualmente
			echo "No se ha definido el gráfico";
		}
	} else { // isset meses y anio
		if($_POST['chartName'] == 'resumenAnual') {
			echo "Seleccione el año(s) para generar la gráfica";
		} else {
			echo 'Seleccione el mes(es) y año(s) para generar la gráfica';
		}
	}

    // Definimos una función de comparación para usort (ChatGPT)
    function comparar_totales($a, $b) {
        if ($a["totalVenta"] == $b["totalVenta"]) {
            return 0;
        } else {
            //
        }
        return ($a["totalVenta"] > $b["totalVenta"]) ? -1 : 1;
    }
?>