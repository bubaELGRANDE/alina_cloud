<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /* 
        POST:
        comisionPagarPeriodoId
        periodo
    */
    $arrayPeriodo = explode("-", $_POST['periodo']);
    $mesPeriodo = trim($arrayPeriodo[0]);
    $anioPeriodo = trim($arrayPeriodo[1]);

    $existePeriodoCreditos = $cloud->count("
        SELECT facturaNotaPeriodoId FROM cred_facturas_notas_periodo
        WHERE mes = ? AND anio = ? AND flgDelete = ?
    ", [$mesPeriodo, $anioPeriodo, 0]);

    $txtCreditos = ""; $facturaNotaPeriodoId = 0;
    if($existePeriodoCreditos > 0) {
        $dataPeriodoCreditos = $cloud->row("
            SELECT facturaNotaPeriodoId FROM cred_facturas_notas_periodo
            WHERE mes = ? AND anio = ? AND flgDelete = ?
        ", [$mesPeriodo, $anioPeriodo, 0]);
        $facturaNotaPeriodoId = $dataPeriodoCreditos->facturaNotaPeriodoId;
    } else {
        $txtCreditos = "No se han cargado las Facturas y Notas de Créditos del periodo: $_POST[periodo]";
    }
?>
<!-- extension puede ser un select en un futuro, por si se necesita cambiar el formato -->
<input type="hidden" id="extension" name="extension" value="pdf">
<input type="hidden" id="comisionPagarPeriodoId" name="comisionPagarPeriodoId" value="<?php echo $_POST['comisionPagarPeriodoId']; ?>">
<input type="hidden" id="facturaNotaPeriodoId" name="facturaNotaPeriodoId" value="<?php echo $facturaNotaPeriodoId; ?>">
<input type="hidden" id="txtPeriodo" name="txtPeriodo" value="<?php echo $_POST['periodo']; ?>">
<div class="row">
    <div class="col-md-3">
        <div class="form-select-control mb-4">
            <select id="file" name="file" style="width: 100%;" required>
                <option></option>
                <option value="comprobanteComisionVendedor">Comprobante de comisiones por vendedor</option>
                <option value="detalleComisionesFiltro">Detalle de comisiones por filtro</option>
                <option value="comisionCompartidaVendedores">Comisiones compartidas entre vendedores</option>
                <option value="provisionComisiones">Provisión de comisiones</option>
                <option value="distribucionGastosComisiones">Distribución de gastos</option>
                <option value="consolidadoComisionesVendedor">Consolidado de comisiones por vendedor</option>
            </select>
        </div>
        <div id="divFiltroFacturas" class="form-select-control mb-4">
            <select id="filtroFacturas" name="filtroFacturas" style="width: 100%;" required>
                <option></option>
                <option value="F">Contado</option>
                <option value="A">Abonos</option>
            </select>            
        </div>
        <div id="divFiltroClientes">
            <div class="form-select-control mb-4 d-flex justify-content-end">
                <span class="me-auto">Clientes:</span>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="filtroClientes" id="filtroClientesSi" value="Todos" checked>
                    <label class="form-check-label" for="filtroClientes">Todos</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="filtroClientes" id="filtroClientesNo" value="Especifico">
                    <label class="form-check-label" for="filtroClientes">Filtrar</label>
                </div>
            </div>
            <div id="divClienteId" class="form-select-control mb-4">
                <select id="clienteId" name="clienteId[]" style="width: 100%;" multiple="multiple" required>
                    <option></option>
                    <?php 
                        $dataClientesComision = $cloud->rows("
                            SELECT 
                                comisionPagarCalculoId,
                                numRegistroCliente,
                                nombreCliente
                            FROM conta_comision_pagar_calculo
                            WHERE comisionPagarPeriodoId = ? AND flgDelete = '0'
                            GROUP BY nombreCliente
                            ORDER BY nombreCliente
                        ", [$_POST['comisionPagarPeriodoId']]);
                        foreach ($dataClientesComision as $dataClientesComision) {
                            // el value es la PK porque el nombre del cliente viene de magic y no se podrá usar "IN" de SQL para consultar rápidamente sus valores ya que no hay un clienteId
                            echo '<option value="'.$dataClientesComision->comisionPagarCalculoId.'">('.($dataClientesComision->numRegistroCliente == "" || is_null($dataClientesComision->numRegistroCliente) ? 'N/A' : $dataClientesComision->numRegistroCliente).') '.$dataClientesComision->nombreCliente.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <div id="divFiltroVendedores">
            <div class="form-select-control mb-4 d-flex justify-content-end">
                <span class="me-auto">Vendedores:</span>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="filtroVendedores" id="filtroVendedoresSi" value="Todos" checked>
                    <label class="form-check-label" for="filtroVendedores">Todos</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="filtroVendedores" id="filtroVendedoresNo" value="Especifico">
                    <label class="form-check-label" for="filtroVendedores">Filtrar</label>
                </div>
            </div>
            <div id="divVendedorId" class="form-select-control mb-4">
                <select id="vendedorId" name="vendedorId[]" style="width: 100%;" multiple="multiple" required>
                    <option></option>
                    <?php 
                        $dataVendedoresComision = $cloud->rows("
                            SELECT 
                                comisionPagarCalculoId,
                                codEmpleado,
                                nombreEmpleado
                            FROM conta_comision_pagar_calculo
                            WHERE comisionPagarPeriodoId = ? AND flgDelete = ?
                            GROUP BY nombreEmpleado
                            ORDER BY nombreEmpleado
                        ", [$_POST['comisionPagarPeriodoId'], 0]);
                        foreach ($dataVendedoresComision as $dataVendedoresComision) {
                            // el value es la PK porque codEmpleado y nombreEmpleado vienen de magic y no se podrá usar "IN" de SQL para consultar rápidamente sus valores
                            echo '<option value="'.$dataVendedoresComision->comisionPagarCalculoId.'">'.$dataVendedoresComision->nombreEmpleado.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <div id="divFiltroLineas">
            <div class="form-select-control mb-4 d-flex justify-content-end">
                <span class="me-auto">Líneas:</span>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="filtroLineas" id="filtroLineasSi" value="Todos" checked>
                    <label class="form-check-label" for="filtroLineas">Todas</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="filtroLineas" id="filtroLineasNo" value="Especifico">
                    <label class="form-check-label" for="filtroLineas">Filtrar</label>
                </div>
            </div>
            <div id="divLineaId" class="form-select-control mb-4">
                <select id="lineaId" name="lineaId[]" style="width: 100%;" multiple="multiple" required>
                    <option></option>
                    <?php 
                        $dataLineas = $cloud->rows("
                            SELECT 
                                l.lineaId AS lineaId,
                                CONCAT('(', l.abreviatura, ') ', l.linea) AS nombreLinea
                            FROM conta_comision_porcentaje_lineas pl
                            JOIN temp_cat_lineas l ON pl.lineaId = l.lineaId
                            WHERE pl.flgDelete = ?
                            GROUP BY pl.lineaId
                            ORDER BY l.abreviatura
                        ", ['0']);
                        foreach ($dataLineas as $dataLineas) {
                            echo '<option value="'.$dataLineas->lineaId.'">'.$dataLineas->nombreLinea.'</option>';
                        }
                    ?>
                </select>
                <div class="form-helper text-end">
                    <span id="btnLineaBombas" class="badge rounded-pill bg-secondary" style="cursor: pointer;">
                        <i class="fas fa-faucet"></i> Bombas
                    </span>
                </div>
            </div>
        </div>
        <div id="divFiltroPorcentajeComision" class="form-select-control mb-4">
            <select id="filtroPorcentajeComision" name="filtroPorcentajeComision" style="width: 100%;" required>
                <option></option>
                <option value="No especificar">No especificar porcentaje</option>
                <option value="Especifico">Porcentaje específico</option>
                <option value="Rango">Rango de porcentajes</option>
            </select>
            <div id="divPorcentajeEspecifico" class="form-outline mt-4 mb-4">
                <i class="fas fa-percentage trailing"></i>
                <input type="number" id="porcentajeComision" class="form-control" name="porcentajeComision" min="0.01" max="100" step="0.01" required />
                <label class="form-label" for="porcentajeComision">Porcentaje de comisión</label>
            </div>
            <div id="divRangoPorcentaje" class="row justify-content-around mt-4 mb-4">
                <div class="col-5 form-outline">
                    <i class="fas fa-percentage trailing"></i>
                    <input type="number" id="porcentajeComisionR1" class="form-control" name="porcentajeComisionR1" min="0.01" max="100" step="0.01" required />
                    <label class="form-label" for="porcentajeComisionR1">% de comisión inicio</label>
                </div>
                <div class="col-5 form-outline">
                    <i class="fas fa-percentage trailing"></i>
                    <input type="number" id="porcentajeComisionR2" class="form-control" name="porcentajeComisionR2" min="0.01" max="100" step="0.01" required />
                    <label class="form-label" for="porcentajeComisionR2">% de comisión fin</label>
                </div>
            </div>
        </div>
        <div id="divClasificacionLineas">
            <div class="form-select-control mb-4 d-flex justify-content-end">
                <span class="me-auto">Clasificación de Líneas:</span>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="filtroClasificacionLineas" id="filtroClasificacionLineasSi" value="Todos" checked>
                    <label class="form-check-label" for="filtroClasificacionLineas">Todas</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="filtroClasificacionLineas" id="filtroClasificacionLineasNo" value="Especifico">
                    <label class="form-check-label" for="filtroClasificacionLineas">Filtrar</label>
                </div>
            </div>
            <div id="divClasificacionLineasId" class="form-select-control mb-4">
                <select id="comisionClasificacionLineaId" name="comisionClasificacionLineaId[]" style="width: 100%;" multiple="multiple" required>
                    <option></option>
                    <?php 
                        $dataClasificacionLineas = $cloud->rows("
                            SELECT
                                comisionClasificacionId, tituloClasificacion
                            FROM conta_comision_reporte_clasificacion
                            WHERE tipoClasificacion = ? AND flgDelete = ?
                        ", ['Línea', 0]);
                        foreach ($dataClasificacionLineas as $dataClasificacionLineas) {
                            echo '<option value="'.$dataClasificacionLineas->comisionClasificacionId.'">'.$dataClasificacionLineas->tituloClasificacion.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <div id="divTxtCreditos" class="text-danger fw-bold">
            <?php echo $txtCreditos; ?>
        </div>
        <div id="divFormatoReporte">
            <div class="form-select-control mb-4 d-flex justify-content-end">
                <span class="me-auto">Formato del reporte:</span>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="formatoReporte" id="formatoReporteExcel" value="Excel" checked>
                    <label class="form-check-label" for="formatoReporte">
                        <i class="fas fa-file-excel"></i> Excel
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="formatoReporte" id="formatoReportePDF" value="PDF">
                    <label class="form-check-label" for="formatoReporte">
                        <i class="fas fa-file-pdf"></i> PDF
                    </label>
                </div>
            </div>
        </div>
    </div>
	<div id="divReporte" class="col-md-9">
	</div>
</div>
<script>
	$(document).ready(function() {
        $("#divFiltroFacturas").hide();
        $("#divFiltroClientes").hide();
        $("#divClienteId").hide();
        $("#divFiltroVendedores").hide();
        $("#divVendedorId").hide();
        $("#divFiltroLineas").hide();
        $("#divLineaId").hide();
        $("#divFiltroPorcentajeComision").hide();
        $("#divPorcentajeEspecifico").hide();
        $("#divRangoPorcentaje").hide();
        $("#divClasificacionLineas").hide();
        $("#divClasificacionLineasId").hide();
        $("#divTxtCreditos").hide();
        $("#divFormatoReporte").hide();

        $("#file").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de reporte'
        });

        $("#filtroFacturas").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de facturación'
        });

        $("#clienteId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Cliente(s)'
        });   

        $("#vendedorId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Vendedor(es)'
        });   

        $("#lineaId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Línea(s)'
        });

        $("#filtroPorcentajeComision").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Filtro porcentaje de comisión'
        });

        $("#comisionClasificacionLineaId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Clasificación de Líneas'
        });

        $("#file").change(function(e) {
            if($(this).val() == "comprobanteComisionVendedor") {
                $("#divFiltroFacturas").show();
                $("#divFiltroClientes").hide();
                $("#divFiltroVendedores").show();
                $("#divFiltroLineas").hide();
                $("#divFiltroPorcentajeComision").hide();
                $("#divClasificacionLineas").hide();
                $("#divTxtCreditos").hide();
                $("#divFormatoReporte").hide();
            } else if($(this).val() == "detalleComisionesFiltro") {
                $("#divFiltroFacturas").hide();
                $("#divFiltroClientes").show();
                $("#divFiltroVendedores").show();
                $("#divFiltroLineas").show();
                $("#divFiltroPorcentajeComision").show();
                $("#divClasificacionLineas").hide();
                $("#divTxtCreditos").hide();
                $("#divFormatoReporte").hide();
            } else if($(this).val() == "provisionComisiones" || $(this).val() == "distribucionGastosComisiones") {
                // Son los mismos filtros para ambos casos
                $("#divFiltroFacturas").hide();
                $("#divFiltroClientes").hide();
                $("#divFiltroVendedores").show();
                $("#divFiltroLineas").hide();
                $("#divFiltroPorcentajeComision").hide();
                $("#divClasificacionLineas").show();
                if($(this).val() == "distribucionGastosComisiones") {
                    $("#divTxtCreditos").show();
                } else {
                    // No se utilizan creditos
                    $("#divTxtCreditos").hide();
                }
                $("#divFormatoReporte").hide();
            } else if($(this).val() == "comisionCompartidaVendedores") {
                $("#divFiltroFacturas").hide();
                $("#divFiltroClientes").hide();
                $("#divFiltroVendedores").hide();
                $("#divFiltroLineas").hide();
                $("#divFiltroPorcentajeComision").hide();
                $("#divClasificacionLineas").hide();
                $("#divTxtCreditos").hide();
                $("#divFormatoReporte").hide();
            } else if($(this).val() == "consolidadoComisionesVendedor") {
                $("#divFiltroFacturas").hide();
                $("#divFiltroClientes").hide();
                $("#divFiltroVendedores").show();
                $("#divFiltroLineas").hide();
                $("#divFiltroPorcentajeComision").hide();
                $("#divClasificacionLineas").hide();
                $("#divTxtCreditos").hide();   
                $("#divFormatoReporte").show();             
            } else {
                $("#divFiltroFacturas").hide();
                $("#divFiltroClientes").hide();
                $("#divFiltroVendedores").hide();
                $("#divFiltroLineas").hide();
                $("#divFiltroPorcentajeComision").hide();
                $("#divClasificacionLineas").hide();
                $("#divTxtCreditos").hide();
                $("#divFormatoReporte").hide();
            }
        });

        $("input[type=radio][name=filtroClientes]").change(function(e) {
            if($(this).val() == "Especifico") {
                $("#divClienteId").show();
            } else {
                $("#divClienteId").hide();
            }
        });

        $("input[type=radio][name=filtroVendedores]").change(function(e) {
            if($(this).val() == "Especifico") {
                $("#divVendedorId").show();
            } else {
                $("#divVendedorId").hide();
            }
        });

        $("input[type=radio][name=filtroLineas]").change(function(e) {
            if($(this).val() == "Especifico") {
                $("#divLineaId").show();
            } else {
                $("#divLineaId").hide();
            }
        });

        $("input[type=radio][name=filtroClasificacionLineas]").change(function(e) {
            if($(this).val() == "Especifico") {
                $("#divClasificacionLineasId").show();
            } else {
                $("#divClasificacionLineasId").hide();
            }
        });

        $("#filtroPorcentajeComision").change(function(e) {
            if($(this).val() == "Especifico") {
                $("#divPorcentajeEspecifico").show();
                $("#divRangoPorcentaje").hide();
            } else if($(this).val() == "Rango") {
                $("#divPorcentajeEspecifico").hide();
                $("#divRangoPorcentaje").show();
            } else {
                $("#divPorcentajeEspecifico").hide();
                $("#divRangoPorcentaje").hide();
            }
        });

        $("#btnLineaBombas").click(function(e) {
            // 'AD','AQ','BI','DU','DY','FE','FT','FV','HD','HG','KL','LD','PD','PR','TC','TS','TY'
            // 2, 110, 13, 28, 109, 38, 44, 45, 49, 50, 58, 65, 75, 80, 93, 97, 98
            $("#lineaId").val(null).trigger('change');
            $("#lineaId").val([2, 110, 13, 28, 109, 38, 44, 45, 49, 50, 58, 65, 75, 80, 93, 97, 98]).trigger('change'); 
        });

        $("#porcentajeComisionR1").change(function(e) {
            $("#porcentajeComisionR2").prop("min", $(this).val());
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>reportes", 
                    $("#frmModal").serialize(),
                    function(data) {
                        // Mantener el botón disabled para prevenir que generen más de uno sino carga
                        button_icons("btnModalAccept", "fas fa-print", "Generar reporte", "enabled");
                        $("#divReporte").html(data);
                    }
                );
            }
        });

        $("#btnModalReset").click(function(e) {
            if($("#file").val() == "comprobanteComisionVendedor") {
                $("#filtroFacturas").val(null).trigger('change');
                $("#filtroVendedoresSi").prop("checked", true);
                $("#filtroVendedoresNo").prop("checked", false);
                $("#divVendedorId").hide();
                $("#vendedorId").val([]).trigger('change');
            } else if($("#file").val() == "detalleComisionesFiltro") {
                $("#filtroClientesSi").prop("checked", true);
                $("#filtroClientesNo").prop("checked", false);
                $("#divClienteId").hide();
                $("#clienteId").val([]).trigger('change');
                $("#filtroVendedoresSi").prop("checked", true);
                $("#filtroVendedoresNo").prop("checked", false);
                $("#divVendedorId").hide();
                $("#vendedorId").val([]).trigger('change');
                $("#filtroLineasSi").prop("checked", true);
                $("#filtroLineasNo").prop("checked", false);
                $("#divLineaId").hide();
                $("#lineaId").val([]).trigger('change');
                $("#filtroPorcentajeComision").val(null).trigger('change');
            } else if($("#file").val() == "provisionComisiones" || $("#file").val() == "distribucionGastosComisiones") {
                $("#filtroVendedoresSi").prop("checked", true);
                $("#filtroVendedoresNo").prop("checked", false);
                $("#divVendedorId").hide();
                $("#vendedorId").val([]).trigger('change');
                $("#filtroClasificacionLineasSi").prop("checked", true);
                $("#filtroClasificacionLineasNo").prop("checked", false);
                $("#divClasificacionLineasId").hide();
                $("#comisionClasificacionLineaId").val([]).trigger('change');
            } else {
                $("#divFiltroFacturas").hide();
                $("#divFiltroClientes").hide();
                $("#divClienteId").hide();
                $("#divFiltroVendedores").hide();
                $("#divVendedorId").hide();
                $("#divFiltroLineas").hide();
                $("#divLineaId").hide();
                $("#divFiltroPorcentajeComision").hide();
                $("#divPorcentajeEspecifico").hide();
                $("#divRangoPorcentaje").hide();
                $("#divClasificacionLineas").hide();
                $("#frmModal").reset();
            }
        });
	});
</script>