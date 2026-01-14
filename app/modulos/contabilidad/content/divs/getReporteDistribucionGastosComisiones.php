<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
        POST:
        extension
        comisionPagarPeriodoId
        periodo
        file
        filtroVendedores
        vendedorId[]
        filtroClasificacionLineas
        comisionClasificacionLineaId[]
    */
   $parametrizacionIVA = 1.13;
    if($_POST['filtroVendedores'] == "Todos") {
        // Todos los vendedores
        $dataNombresVendedores = $cloud->rows("
            SELECT
                nombreEmpleado
            FROM conta_comision_pagar_calculo
            WHERE comisionPagarPeriodoId = ? AND flgDelete = ?
            GROUP BY nombreEmpleado
            ORDER BY nombreEmpleado
        ", [$_POST['comisionPagarPeriodoId'], 0]);
    } else {
        // Especifico
        $vendedorId = implode(',', $_POST['vendedorId']);
        // Traer el nombre especifico de cada vendedor, ya que vendedorId trae comisionPagarCalculoId
        // Porque la información viene de magic y codEmpleado o codVendedor vienen en ceros repetidos
        $dataNombresVendedores = $cloud->rows("
            SELECT
                nombreEmpleado
            FROM conta_comision_pagar_calculo
            WHERE comisionPagarCalculoId IN ($vendedorId) AND flgDelete = ?
            GROUP BY nombreEmpleado
            ORDER BY nombreEmpleado
        ", [0]);
    }

    if($_POST['filtroClasificacionLineas'] == "Todos") {
        $dataClasificacionLineas = $cloud->rows("
            SELECT 
                comisionClasificacionId, tituloClasificacion
            FROM conta_comision_reporte_clasificacion
            WHERE tipoClasificacion = ? AND flgDelete = ?
            ORDER BY tituloClasificacion
        ", ['Línea', 0]);
    } else {
        // Para pasar un select multiple al wrapper
        $comisionClasificacionId = implode(',', $_POST['comisionClasificacionLineaId']);
        $dataClasificacionLineas = $cloud->rows("
            SELECT 
                comisionClasificacionId, tituloClasificacion
            FROM conta_comision_reporte_clasificacion
            WHERE comisionClasificacionId IN ($comisionClasificacionId) AND tipoClasificacion = ? AND flgDelete = ?
            ORDER BY tituloClasificacion
        ", ['Línea', 0]);
    }

    $whereVendedoresCredito = " AND nombreEmpleado NOT IN (";
    // Inicializar array
    foreach ($dataNombresVendedores as $vendedor) {
        foreach($dataClasificacionLineas as $clasificacionLinea) {
            $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"] = 0;
            $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Creditos"] = 0;
            $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["NotasCredito"] = 0;
            $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["TotalClasificacion"] = 0;
            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"] = 0;
            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Creditos"] = 0;
            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["NotasCredito"] = 0;
        }
        $arrayVendedorTotal[$vendedor->nombreEmpleado] = 0;
        $whereVendedoresCredito .= "'$vendedor->nombreEmpleado',";
    }
    $whereClean = rtrim($whereVendedoresCredito, ",");
    $whereVendedoresCredito = $whereClean . ")";

    $dataNombresVendedoresCredito = $cloud->rows("
        SELECT
            nombreEmpleado
        FROM cred_facturas_notas_creditos
        WHERE facturaNotaPeriodoId = ? AND flgDelete = ?
        $whereVendedoresCredito
        GROUP BY nombreEmpleado
        ORDER BY nombreEmpleado
    ", [$_POST['facturaNotaPeriodoId'], 0]);

    foreach ($dataNombresVendedoresCredito as $vendedor) {
        foreach($dataClasificacionLineas as $clasificacionLinea) {
            $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"] = 0;
            $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Creditos"] = 0;
            $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["NotasCredito"] = 0;
            $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["TotalClasificacion"] = 0;
            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"] = 0;
            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Creditos"] = 0;
            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["NotasCredito"] = 0;
        }
        $arrayVendedorTotal[$vendedor->nombreEmpleado] = 0;
    }
    $dataVendedores = array_merge($dataNombresVendedores, $dataNombresVendedoresCredito);

    $dataNombresVendedores = $dataVendedores;

    $arrayTotalesFooter["totalVendedorHorizontalVentas"] = 0;
    $arrayTotalesFooter["totalVendedorHorizontalNotasCredito"] = 0;
    $arrayTotalesFooter["totalVendedorHorizontal"] = 0;
?>
<ul class="nav nav-tabs nav-justified mb-3" id="ex1" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="tab1" data-mdb-toggle="tab" href="#tab1-content" role="tab" aria-controls="tab1-content" aria-selected="true">Distribución</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="tab2" data-mdb-toggle="tab" href="#tab2-content" role="tab" aria-controls="tab2-content" aria-selected="false">Porcentajes</a>
    </li>
</ul>
<div class="tab-content" id="ex2-content">
    <div class="tab-pane fade show active"  id="tab1-content" role="tabpanel" aria-labelledby="tab1-content">
        <div class="text-center mb-3">
            <h3>Distribución de gastos<br>
                <small>Periodo: <?php echo $_POST['txtPeriodo']; ?></small>
            </h3>
        </div>
        <div class="row mb-4">
            <div class="col-9">
                <button type="button" id="btnReporteExcel" class="btn btn-success ttip">
                    <i class="fas fa-file-excel"></i> Excel
                    <span class="ttiptext">Descargar reporte en Excel</span>
                </button>
            </div>
        </div>
        <div class="table-responsive" tabindex="0">
            <table id="tblReporte" class="table table-hover table-sm">
                <thead>
                    <tr id="filterboxrow-reporte">
                        <th style="font-weight: bolder;" rowspan="3">#</th>
                        <th style="font-weight: bolder;" rowspan="3">Vendedores</th>
                        <?php
                            foreach($dataClasificacionLineas as $clasificacionLinea) {
                                echo "<th class='text-center' style='font-weight: bolder;' colspan='6'>$clasificacionLinea->tituloClasificacion</th>";
                            }
                        ?>
                        <th class="text-center" style="font-weight: bolder;" colspan="3">Totales</th>
                    </tr>
                    <tr>
                        <?php
                            foreach($dataClasificacionLineas as $clasificacionLinea) {
                                echo "
                                    <th class='text-center' colspan='3'><b>Ventas</b></th>
                                    <th class='text-center' rowspan='2'><b>Notas de Crédito</b></th>
                                    <th class='text-center' rowspan='2'><b>Total</b></th>
                                    <th class='text-center' rowspan='2'><b>Porcentaje</b></th>
                                ";
                            }
                        ?>
                        <th class="text-center" rowspan="2"><b>Ventas</b></th>
                        <th class="text-center" rowspan="2"><b>Notas de Crédito</b></th>
                        <th class="text-center" rowspan="2"><b>Total</b></th>
                    </tr>
                    <tr>
                        <?php
                            foreach($dataClasificacionLineas as $clasificacionLinea) {
                                echo "
                                    <th class='text-center'><b>Contado</b></th>
                                    <th class='text-center'><b>Créditos</b></th>
                                    <th class='text-center'><b>Total ventas</b></th>
                                ";
                            }
                        ?>
                    </tr>
                </thead>
                <tbody class="filtroBusqueda">
                    <?php
                        $n = 0;
                        foreach($dataClasificacionLineas as $clasificacionLinea) {
                            // Detalle de clasificaciones
                            $dataClasificacionLineasDetalle = $cloud->rows("
                                SELECT
                                    comisionClasificacionDetalleId, valorClasificacion
                                FROM conta_comision_reporte_clasificacion_detalle
                                WHERE comisionClasificacionId = ? AND flgDelete = ?
                            ", [$clasificacionLinea->comisionClasificacionId, 0]);
                            foreach($dataClasificacionLineasDetalle as $clasificacionLineaDetalle) {
                                $lineaProducto = substr($clasificacionLineaDetalle->valorClasificacion, 1, 2);
                                // Venta: Contados
                                /*
                                    codTipoFactura Magic
                                    1 = Consumidor Final = Lleva IVA
                                    2 = Crédito Fiscal   = No IVA
                                    3 = Exportación      = No IVA
                                    4 = Factura Exenta   = No IVA
                                    5 = Nota de débito   = No IVA
                                    8 = Ticket           = Lleva IVA
                                */
                                $dataVentasContado = $cloud->rows("
                                    SELECT
                                        SUM(
                                            CASE
                                                WHEN codTipoFactura IN (1, 8) THEN totalVenta / $parametrizacionIVA
                                                WHEN codTipoFactura IN (2, 3, 4, 5) THEN totalVenta
                                                ELSE 0
                                            END
                                        ) AS totalVenta,
                                        nombreEmpleado
                                    FROM conta_comision_pagar_calculo
                                    WHERE comisionPagarPeriodoId = ? AND flgIdentificador = ? AND lineaProducto = ? AND flgDelete = ?
                                    GROUP BY nombreEmpleado
                                    ORDER BY nombreEmpleado
                                ", [$_POST['comisionPagarPeriodoId'], 'F', $lineaProducto, 0]);
                                foreach($dataVentasContado as $ventasContado) {
                                    if(isset($arrayDistribucionGastos[$ventasContado->nombreEmpleado][$clasificacionLinea->tituloClasificacion])) {
                                        $arrayDistribucionGastos[$ventasContado->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"] += $ventasContado->totalVenta;
                                    } else {
                                        // Vendedores filtrados
                                    }
                                } // dataVentasContado

                                $dataVentasCredito = $cloud->rows("
                                    SELECT
                                        SUM(
                                            CASE
                                                WHEN codTipoFactura IN (1, 8) THEN totalVenta / $parametrizacionIVA
                                                WHEN codTipoFactura IN (2, 3, 4, 5) THEN totalVenta
                                                ELSE 0
                                            END
                                        ) AS totalVenta,
                                        nombreEmpleado
                                    FROM cred_facturas_notas_creditos
                                    WHERE facturaNotaPeriodoId = ? AND flgIdentificador = ? AND lineaProducto = ? AND flgDelete = ?
                                    GROUP BY nombreEmpleado
                                    ORDER BY nombreEmpleado
                                ", [$_POST['facturaNotaPeriodoId'], 'C', $lineaProducto, 0]);
                                foreach($dataVentasCredito as $ventasCredito) {
                                    if(isset($arrayDistribucionGastos[$ventasCredito->nombreEmpleado][$clasificacionLinea->tituloClasificacion])) {
                                        $arrayDistribucionGastos[$ventasCredito->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Creditos"] += $ventasCredito->totalVenta;
                                    } else {
                                        // Vendedores filtrados
                                    }
                                } // dataVentasCredito

                                $dataVentasNotasCredito = $cloud->rows("
                                    SELECT
                                        SUM(
                                            CASE
                                                WHEN codTipoFactura IN (1, 8) THEN totalVenta / $parametrizacionIVA
                                                WHEN codTipoFactura IN (2, 3, 4, 5) THEN totalVenta
                                                ELSE 0
                                            END
                                        ) AS totalVenta,
                                        nombreEmpleado
                                    FROM cred_facturas_notas_creditos
                                    WHERE facturaNotaPeriodoId = ? AND flgIdentificador = ? AND lineaProducto = ? AND flgDelete = ?
                                    GROUP BY nombreEmpleado
                                    ORDER BY nombreEmpleado
                                ", [$_POST['facturaNotaPeriodoId'], 'NC', $lineaProducto, 0]);
                                foreach($dataVentasNotasCredito as $ventasNotasCredito) {
                                    if(isset($arrayDistribucionGastos[$ventasNotasCredito->nombreEmpleado][$clasificacionLinea->tituloClasificacion])) {
                                        $arrayDistribucionGastos[$ventasNotasCredito->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["NotasCredito"] += $ventasNotasCredito->totalVenta;
                                    } else {
                                        // Vendedores filtrados
                                    }
                                } // dataVentasNotasCredito
                            } // dataClasificacionDetalle
                        } // dataClasificacionLineas

                        // Dibujar tabla
                        $iteracionVendedor = 0; $iteracionClasificacion = 0;
                        foreach ($dataNombresVendedores as $vendedor) {
                            $n++;
                            $iteracionVendedor++;
                            echo "
                                <tr>
                                    <td>$n</td>
                                    <td>$vendedor->nombreEmpleado</td>
                            ";
                            $totalVendedorHorizontalVentas = 0;
                            $totalVendedorHorizontalNotasCredito = 0;
                            $totalVendedorHorizontal = 0;
                            foreach($dataClasificacionLineas as $clasificacionLinea) {
                                $iteracionClasificacion++;

                                $totalVentas = $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"] + $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Creditos"];
                                $totalClasificacion = $totalVentas - $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["NotasCredito"];
                                $totalVendedorHorizontalVentas += $totalVentas;
                                $totalVendedorHorizontalNotasCredito += $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["NotasCredito"];
                                $totalVendedorHorizontal = $totalVendedorHorizontalVentas - $totalVendedorHorizontalNotasCredito;

                                $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["TotalClasificacion"] = $totalClasificacion;
                                echo '
                                    <td class="text-end">$ '.number_format($arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"], 2, '.', ',').'</td>
                                    <td class="text-end">$ '.number_format($arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Creditos"], 2, '.', ',').'</td>
                                    <td class="text-end" style="background-color: #feff99;">$ '.number_format($totalVentas, 2, '.', ',').'</td>
                                    <td class="text-end" style="background-color: #cdffcc;">$ '.number_format($arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["NotasCredito"], 2, '.', ',').'</td>
                                    <td class="text-end" style="background-color: #d1faff;">$ '.number_format($totalClasificacion, 2, '.', ',').'</td>
                                    <td id="porcentaje'.$iteracionVendedor.$iteracionClasificacion.'" class="text-end" style="background-color: #d1faff;">% Porcentaje</td>
                                ';
                                $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"] += $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"];
                                $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Creditos"] += $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Creditos"];
                                $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["NotasCredito"] += $arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["NotasCredito"];
                            }
                            $arrayTotalesFooter["totalVendedorHorizontalVentas"] += $totalVendedorHorizontalVentas;
                            $arrayTotalesFooter["totalVendedorHorizontalNotasCredito"] += $totalVendedorHorizontalNotasCredito;
                            $arrayTotalesFooter["totalVendedorHorizontal"] += $totalVendedorHorizontal;

                            $arrayVendedorTotal[$vendedor->nombreEmpleado] = $totalVendedorHorizontal;
                            echo '
                                    <td class="text-end">$ '.number_format($totalVendedorHorizontalVentas, 2, '.', ',').'</td>
                                    <td class="text-end">$ '.number_format($totalVendedorHorizontalNotasCredito, 2, '.', ',').'</td>
                                    <td class="text-end">$ '.number_format($totalVendedorHorizontal, 2, '.', ',').'</td>
                                </tr>
                            ';
                        }
                    ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bolder;">
                        <td class="text-end" colspan="2">Totales</td>
                        <?php 
                            $iteracionClasificacion = 0;
                            foreach($dataClasificacionLineas as $clasificacionLinea) {
                                $iteracionClasificacion++;
                                $totalVentas = $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"] + $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Creditos"];
                                $totalClasificacion = $totalVentas - $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["NotasCredito"];
                                echo '
                                    <td class="text-end">$ '.number_format($arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"], 2, '.', ',').'</td>
                                    <td class="text-end">$ '.number_format($arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Creditos"], 2, '.', ',').'</td>
                                    <td class="text-end" style="background-color: #feff99;">$ '.number_format($totalVentas, 2, '.', ',').'</td>
                                    <td class="text-end" style="background-color: #cdffcc;">$ '.number_format($arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["NotasCredito"], 2, '.', ',').'</td>
                                    <td class="text-end" style="background-color: #d1faff;">$ '.number_format($totalClasificacion, 2, '.', ',').'</td>
                                    <td id="porcentajeFooter'.$iteracionClasificacion.'" class="text-end" style="background-color: #d1faff;">% Porcentaje</td>
                                ';
                            }
                        ?>
                        <td class="text-end">$ <?php echo number_format($arrayTotalesFooter["totalVendedorHorizontalVentas"], 2, '.', ','); ?></td>
                        <td class="text-end">$ <?php echo number_format($arrayTotalesFooter["totalVendedorHorizontalNotasCredito"], 2, '.', ','); ?></td>
                        <td class="text-end">$ <?php echo number_format($arrayTotalesFooter["totalVendedorHorizontal"], 2, '.', ','); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="tab2-content" role="tabpanel" aria-labelledby="tab2-content">
        <div class="text-center mb-3">
            <h3>Distribución de gastos: Porcentajes por vendedor<br>
                <small>Periodo: <?php echo $_POST['txtPeriodo']; ?></small>
            </h3>
        </div>
        <div class="row mb-4">
            <div class="col-9">
                <button type="button" id="btnReporteExcel2" class="btn btn-success ttip">
                    <i class="fas fa-file-excel"></i> Excel
                    <span class="ttiptext">Descargar reporte en Excel</span>
                </button>
            </div>
        </div>
        <div class="table-responsive" tabindex="1">
            <table id="tblReporte2" class="table table-hover">
                <thead>
                    <tr>
                        <th style="font-weight: bolder;" rowspan="2">#</th>
                        <th style="font-weight: bolder;" rowspan="2">Vendedor</th>
                        <th style="font-weight: bolder;" rowspan="2">Distribución</th>
                        <?php 
                            foreach($dataClasificacionLineas as $clasificacionLinea) {
                                echo "<th class='text-center' style='font-weight: bolder;' colspan='2'>$clasificacionLinea->tituloClasificacion</th>";
                            }
                        ?>
                        <th style="font-weight: bolder;" rowspan="2">Total distribución</th>
                    </tr>
                    <tr>
                        <?php 
                            foreach($dataClasificacionLineas as $clasificacionLinea) {
                                echo "
                                    <th class='text-center' style='font-weight: bolder;'>Porcentaje</th>
                                    <th class='text-center' style='font-weight: bolder;'>Total</th>
                                ";
                            }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $n = 0;
                        $iteracionVendedor = 0; $iteracionClasificacion = 0;
                        foreach ($dataNombresVendedores as $vendedor) {
                            $n++;
                            $iteracionVendedor++;
                            echo "
                                <tr>
                                    <td>$n</td>
                                    <td>$vendedor->nombreEmpleado</td>
                                    <td class='text-end'>$ 0.00</td>
                            ";
                            foreach($dataClasificacionLineas as $clasificacionLinea) {
                                $iteracionClasificacion++;
                                echo '
                                    <td id="porcentajeH2'.$iteracionVendedor.$iteracionClasificacion.'" class="text-end" style="background-color: #d1faff;">% Porcentaje</td>
                                    <td class="text-end">$ 0.00</td>
                                ';
                            }
                            echo "
                                    <td class='text-end'>$ 0.00</td>
                                </tr>
                            ";
                        }
                    ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bolder;">
                        <td class="text-end" colspan="2">Totales</td>
                        <td class="text-end">$ 0.00</td>
                        <?php 
                            $iteracionClasificacion = 0;
                            foreach($dataClasificacionLineas as $clasificacionLinea) {
                                $iteracionClasificacion++;
                                echo '
                                    <td id="porcentajeFooterH2'.$iteracionClasificacion.'" class="text-end" style="background-color: #d1faff;">% Porcentaje</td>
                                    <td class="text-end">$ 0.00</td>
                                ';
                            }
                        ?>
                        <td class="text-center">$ 0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        <?php 
            $totalVendedorHorizontalVentas = 0;
            $totalVendedorHorizontalNotasCredito = 0;
            $totalVendedorHorizontal = 0;

            $iteracionVendedor = 0; $iteracionClasificacion = 0;
            foreach ($dataNombresVendedores as $vendedor) {
                $iteracionVendedor++;
                foreach($dataClasificacionLineas as $clasificacionLinea) {
                    $iteracionClasificacion++;
                    $tdPorcentaje = 'porcentaje'.$iteracionVendedor.$iteracionClasificacion;
                    $tdPorcentajeH2 = 'porcentajeH2'.$iteracionVendedor.$iteracionClasificacion;

                    if($arrayVendedorTotal[$vendedor->nombreEmpleado] == 0) {
                        $porcentajeClasificacion = 0;
                    } else {
                        $porcentajeClasificacion = ($arrayDistribucionGastos[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["TotalClasificacion"] * 100) / $arrayVendedorTotal[$vendedor->nombreEmpleado];
                    }

                    echo '$("#'.$tdPorcentaje.'").html(`'.number_format($porcentajeClasificacion, 2, '.', ',').' %`);';
                    echo '$("#'.$tdPorcentajeH2.'").html(`'.number_format($porcentajeClasificacion, 2, '.', ',').' %`);';

                    // Para footer
                    $tdPorcentajeFooter = "porcentajeFooter$iteracionClasificacion";
                    $tdPorcentajeFooterH2 = "porcentajeFooterH2$iteracionClasificacion";

                    $totalVentas = $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"] + $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Creditos"];
                    $totalClasificacion = $totalVentas - $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["NotasCredito"];

                    if($arrayTotalesFooter["totalVendedorHorizontal"] == 0) {
                        $porcentajeClasificacionFooter = 0;
                    } else {
                        $porcentajeClasificacionFooter = ($totalClasificacion * 100) / $arrayTotalesFooter["totalVendedorHorizontal"];
                    }

                    echo '$("#'.$tdPorcentajeFooter.'").html(`'.number_format($porcentajeClasificacionFooter, 2, '.', ',').' %`);';
                    echo '$("#'.$tdPorcentajeFooterH2.'").html(`'.number_format($porcentajeClasificacionFooter, 2, '.', ',').' %`);';
                }
            }
        ?>

        $("#btnReporteExcel").click(function(e) {
            $("#tblReporte").table2excel({
                name: `Distribución de gastos - Periodo: <?php echo $_POST['txtPeriodo']; ?>`,
                filename: `Distribución de gastos - <?php echo $_POST['txtPeriodo']; ?>`
            });

            /*
                Esto es para la librería SheetJS
                Si se quiere aplicar formatos de número y porcentaje a las celdas se utilizan las propiedades:
                data-t="n" data-v="100" data-z="$0.00"
                Donde:
                t = formato (n number)
                v = valor que se exportará a excel (debe colocarse el valor en HTML también para que se muestre)
                z = formato del número, es decir, exportar en forma $0.00
                Queda pendiente definir como se agrega color, según GPT:
                date-s="clase" y se debe crear una clase con backgroud-color y color

                No se utilizó en este reporte, ya que se solicitó con colores ya definidos
                // Se crea el libro de excel
                let xlsxBook = XLSX.utils.book_new();

                // Obtener las tablas que se van a unir
                let hoja1 = XLSX.utils.table_to_sheet($("#tblReporte")[0]);
                let hoja2 = XLSX.utils.table_to_sheet($("#tblReporte2")[0]);

                // Agregar las tablas al libro en diferentes hojas
                XLSX.utils.book_append_sheet(xlsxBook, hoja1, 'Distribución');
                XLSX.utils.book_append_sheet(xlsxBook, hoja2, 'Porcentajes');

                // Generar el archivo de Excel
                XLSX.writeFile(xlsxBook, `Distribución de gastos - <?php echo $_POST['txtPeriodo']; ?>.xlsx`);
            */
        });
        $("#btnReporteExcel2").click(function(e) {
            $("#tblReporte2").table2excel({
                name: `Distribución de gastos - Porcentajes - Periodo: <?php echo $_POST['txtPeriodo']; ?>`,
                filename: `Distribución de gastos - Porcentajes - <?php echo $_POST['txtPeriodo']; ?>`
            });
        });
    });
</script>