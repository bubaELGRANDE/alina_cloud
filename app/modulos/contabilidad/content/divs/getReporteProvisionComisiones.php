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
            $arrayProvisionComisiones[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"] = 0;
            $arrayProvisionComisiones[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Abonos"] = 0;
            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"] = 0;
            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Abonos"] = 0;
        }
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
            $arrayProvisionComisiones[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"] = 0;
            $arrayProvisionComisiones[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Abonos"] = 0;
            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"] = 0;
            $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Abonos"] = 0;
        }
    }
    $dataVendedores = array_merge($dataNombresVendedores, $dataNombresVendedoresCredito);

    $dataNombresVendedores = $dataVendedores;

    $arrayTotalesFooter["totalVendedorHorizontal"] = 0;
    $arrayTotalesFooter["totalVendedorHorizontalCalculo"] = 0;
    $arrayTotalesFooter["totalVendedorHorizontalAbonos"] = 0;
    $arrayTotalesFooter["totalVendedorAbonosCalculo"] = 0;
?>
<div class="text-center mb-3">
    <h3>Provisión de Comisiones<br>
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
                <th style="font-weight: bolder;" rowspan="2">#</th>
                <th style="font-weight: bolder;" rowspan="2">Vendedores</th>
                <?php
                    foreach($dataClasificacionLineas as $clasificacionLinea) {
                        echo "<th class='text-center' style='font-weight: bolder;' colspan='3'>$clasificacionLinea->tituloClasificacion</th>";
                    }
                ?>
                <th style="font-weight: bolder;" colspan="5">Total comisión</th>
                <th style="font-weight: bolder;" rowspan="2">Bonificaciones</th>
                <th style="font-weight: bolder;" rowspan="2">Total: Comisión y Bonificaciones</th>
                <th style="font-weight: bolder;" rowspan="2">Total descuentos</th>
                <th style="font-weight: bolder;" rowspan="2">Liquido a recibir</th>
            </tr>
            <tr>
                <?php
                    foreach($dataClasificacionLineas as $clasificacionLinea) {
                        echo "
                            <th class='text-center'><b>Ventas</b></th>
                            <th class='text-center'><b>Cobros</b></th>
                            <th class='text-center'><b>Total</b></th>
                        ";
                    }
                ?>
                <th style="font-weight: bolder;">Ventas</th>
                <th style="font-weight: bolder;">Ventas (Cálculo)</th>
                <th style="font-weight: bolder;">Cobros</th>
                <th style="font-weight: bolder;">Cobros (Cálculo)</th>
                <th style="font-weight: bolder;">Total</th>
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
                        // Provisión: Contados
                        $dataProvisionComision = $cloud->rows("
                            SELECT
                                SUM(
                                    CASE
                                        WHEN flgComisionEditar = 1 THEN comisionPagarEditar
                                        ELSE comisionPagar
                                    END
                                ) AS totalComision,
                                nombreEmpleado
                            FROM conta_comision_pagar_calculo
                            WHERE comisionPagarPeriodoId = ? AND flgIdentificador = ? AND lineaProducto = ? AND flgDelete = ?
                            GROUP BY nombreEmpleado
                            ORDER BY nombreEmpleado
                        ", [$_POST['comisionPagarPeriodoId'], 'F', $lineaProducto, 0]);
                        foreach($dataProvisionComision as $provisionComision) {
                            if(isset($arrayProvisionComisiones[$provisionComision->nombreEmpleado][$clasificacionLinea->tituloClasificacion])) {
                                $arrayProvisionComisiones[$provisionComision->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"] += $provisionComision->totalComision;
                            } else {
                                // Vendedores filtrados
                            }
                        } // dataProvisionComision
                        // Provisión: Abonos
                        $dataProvisionComisionAbonos = $cloud->rows("
                            SELECT
                                SUM(
                                    CASE
                                        WHEN flgComisionEditar = 1 AND comisionAbonoTotal = 0 THEN comisionPagarEditar / (
                                                SELECT COUNT(correlativoFactura)
                                                FROM conta_comision_pagar_calculo sub
                                                WHERE sub.flgComisionEditar = 1 AND sub.comisionAbonoTotal = 0 AND sub.correlativoFactura = pagar.correlativoFactura AND sub.fechaAbono = pagar.fechaAbono
                                            )
                                        WHEN flgComisionEditar = 1 THEN ((((comisionPagarEditar * 100) / comisionAbonoTotal) / 100) * comisionPagar)
                                        ELSE ((ROUND((comisionAbonoPagar * 100) / comisionAbonoTotal , 2) / 100) * comisionPagar)
                                    END
                                ) AS totalComision,
                                nombreEmpleado
                            FROM conta_comision_pagar_calculo pagar
                            WHERE comisionPagarPeriodoId = ? AND flgIdentificador = ? AND lineaProducto = ? AND flgDelete = ?
                            GROUP BY nombreEmpleado
                            ORDER BY nombreEmpleado
                        ", [$_POST['comisionPagarPeriodoId'], 'A', $lineaProducto, 0]);
                        foreach($dataProvisionComisionAbonos as $provisionComision) {
                            if(isset($arrayProvisionComisiones[$provisionComision->nombreEmpleado][$clasificacionLinea->tituloClasificacion])) {
                                $arrayProvisionComisiones[$provisionComision->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Abonos"] += $provisionComision->totalComision;
                            } else {
                                // Vendedores filtrados
                            }
                        } // dataProvisionComisionAbonos
                    } // dataClasificacionLineasDetalle
                } // dataClasificacionLineas

                // Dibujar tabla
                foreach ($dataNombresVendedores as $vendedor) {
                    $n++;
                    echo "
                        <tr>
                            <td>$n</td>
                            <td>$vendedor->nombreEmpleado</td>
                    ";
                    $totalVendedorHorizontal = 0; $totalVendedorHorizontalAbonos = 0;
                    foreach($dataClasificacionLineas as $clasificacionLinea) {
                        $provisionComision = $arrayProvisionComisiones[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Contado"];
                        $provisionComisionAbonos = $arrayProvisionComisiones[$vendedor->nombreEmpleado][$clasificacionLinea->tituloClasificacion]["Abonos"];
                        echo '
                            <td class="text-end">$ '.number_format($provisionComision, 2, '.', ',').'</td>
                            <td class="text-end">$ '.number_format($provisionComisionAbonos, 2, '.', ',').'</td>
                            <td class="text-end" style="background-color: #c5e7ff;">$ '.number_format($provisionComision + $provisionComisionAbonos, 2, '.', ',').'</td>
                        ';
                        $totalVendedorHorizontal += $provisionComision;
                        $totalVendedorHorizontalAbonos += $provisionComisionAbonos;
                        $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"] += $provisionComision;
                        $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Abonos"] += $provisionComisionAbonos;
                    }
                    $arrayTotalesFooter["totalVendedorHorizontal"] += $totalVendedorHorizontal;
                    $arrayTotalesFooter["totalVendedorHorizontalAbonos"] += $totalVendedorHorizontalAbonos;
                    // Calculo seguno el sistema: Contado
                    // Sumar las comisiones normales
                    $dataComisionVendedorVenta = $cloud->row("
                        SELECT 
                            SUM(
                                CASE
                                    WHEN flgComisionEditar = 1 THEN comisionPagarEditar
                                    ELSE comisionPagar
                                END
                            ) AS totalVendedorHorizontal 
                        FROM conta_comision_pagar_calculo
                        WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = ? AND flgDelete = ?
                    ", [$_POST['comisionPagarPeriodoId'], $vendedor->nombreEmpleado, 'F', 0]);
                    $totalVendedorHorizontalCalculo = $dataComisionVendedorVenta->totalVendedorHorizontal;
                    $arrayTotalesFooter["totalVendedorHorizontalCalculo"] += $totalVendedorHorizontalCalculo;

                    $colorContadoCalculo = "";
                    $tolerancia  = 0.05;
                    $diferencia = abs($totalVendedorHorizontal - $totalVendedorHorizontalCalculo);
                    if($diferencia <= $tolerancia) {
                        $colorContadoCalculo = 'style="background-color: #cccecc;"';
                    } else {
                        $colorContadoCalculo = 'style="background-color: #f4355c;"';
                    }

                    // Calculo segun el sistema: Abonos
                    $dataComisionVendedorAbono = $cloud->rows("
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
                            ivaRetenido,
                            comisionAbonoPagar,
                            flgComisionEditar,
                            comisionPagarEditar
                        FROM conta_comision_pagar_calculo
                        WHERE comisionPagarPeriodoId = ? AND nombreEmpleado = ? AND flgIdentificador = 'A' AND flgDelete = '0'
                        GROUP BY nombreEmpleado, correlativoFactura, tipoFactura, fechaAbono, totalAbono
                    ", [$_POST['comisionPagarPeriodoId'], $vendedor->nombreEmpleado]);
                    $totalVendedorAbonosCalculo = 0;
                    foreach ($dataComisionVendedorAbono as $dataComisionVendedorAbono) {
                        $totalVendedorAbonosCalculo += ($dataComisionVendedorAbono->flgComisionEditar == '1') ? $dataComisionVendedorAbono->comisionPagarEditar : $dataComisionVendedorAbono->comisionAbonoPagar;
                    }
                    $arrayTotalesFooter["totalVendedorAbonosCalculo"] += $totalVendedorAbonosCalculo;

                    $colorAbonosCalculo = "";
                    $tolerancia  = 0.05;
                    $diferencia = abs($totalVendedorHorizontalAbonos - $totalVendedorAbonosCalculo);
                    if($diferencia <= $tolerancia) {
                        $colorAbonosCalculo = 'style="background-color: #cccecc;"';
                    } else {
                        $colorAbonosCalculo = 'style="background-color: #f4355c;"';
                    }

                    echo '
                            <td class="text-end">$ '.number_format($totalVendedorHorizontal, 2, '.', ',').'</td>
                            <td class="text-end" '.$colorContadoCalculo.'>$ '.number_format($totalVendedorHorizontalCalculo, 2, '.', ',').'</td>
                            <td class="text-end">$ '.number_format($totalVendedorHorizontalAbonos, 2, '.', ',').'</td>
                            <td class="text-end" '.$colorAbonosCalculo.'>$ '.number_format($totalVendedorAbonosCalculo, 2, '.', ',').'</td>
                            <td class="text-end" style="background-color: #c5e7ff;">$ '.number_format($totalVendedorHorizontal + $totalVendedorHorizontalAbonos, 2, '.', ',').'</td>
                            <td class="text-end">$ 0.00</td>
                            <td class="text-end" style="background-color: #c5e7ff;">$ '.number_format($totalVendedorHorizontal + $totalVendedorHorizontalAbonos, 2, '.', ',').'</td>
                            <td class="text-end">$ 0.00</td>
                            <td class="text-end" style="background-color: #c5e7ff;">$ 0.00</td>
                        </tr>
                    ';
                }
            ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bolder;">
                <td class="text-end" colspan="2">Totales</td>
                <?php 
                    foreach($dataClasificacionLineas as $clasificacionLinea) {
                        echo '
                            <td class="text-end">$ '.number_format($arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"], 2, '.', ',').'</td>
                            <td class="text-end">$ '.number_format($arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Abonos"], 2, '.', ',').'</td>
                            <td class="text-end" style="background-color: #c5e7ff;">$ '.number_format($arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Contado"] + $arrayTotalesFooter[$clasificacionLinea->tituloClasificacion]["Abonos"], 2, '.', ',').'</td>
                        ';
                    }
                ?>
                <td class="text-end">$ <?php echo number_format($arrayTotalesFooter["totalVendedorHorizontal"], 2, '.', ','); ?></td>
                <td class="text-end">$ <?php echo number_format($arrayTotalesFooter["totalVendedorHorizontalCalculo"], 2, '.', ','); ?></td>
                <td class="text-end">$ <?php echo number_format($arrayTotalesFooter["totalVendedorHorizontalAbonos"], 2, '.', ','); ?></td>
                <td class="text-end" style="background-color: #cccecc;">$ <?php echo number_format($arrayTotalesFooter["totalVendedorAbonosCalculo"], 2, '.', ','); ?></td>
                <td class="text-end" style="background-color: #c5e7ff;">$ <?php echo number_format(0, 2, '.', ','); ?></td>
                <td class="text-end">$ 0.00</td>
                <td class="text-end" style="background-color: #c5e7ff;">$ <?php echo number_format(0, 2, '.', ','); ?></td>
                <td class="text-end">$ 0.00</td>
                <td class="text-end" style="background-color: #c5e7ff;">$ 0.00</td>
            </tr>
        </tfoot>
    </table>
</div>
<script>
    $(document).ready(function() {
        $("#btnReporteExcel").click(function(e) {
            $("#tblReporte").table2excel({
                name: `Provisión de Comisiones - Periodo: <?php echo $_POST['txtPeriodo']; ?>`,
                filename: `Provision de Comisiones - <?php echo $_POST['txtPeriodo']; ?>`
            });
        });
    });
</script>