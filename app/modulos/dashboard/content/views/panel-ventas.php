<?php 
    @session_start();
    $arrayMeses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $yearData = "2021";
?>
<div class="row">
    <div class="col-9">
        <h2>
            Panel de ventas
        </h2>
    </div>
    <div class="col-3">
        <button type="button" id="btnParametrizacion" class="btn btn-primary btn-block">
            <i class="fas fa-users-cog"></i> Parametrización
        </button>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-9">
        <!--  
        <h4>Total de ventas del mes</h4>
        <hr>
        -->
        <h4>Total de ventas por sucursal</h4>
        <hr>
        <div class="row">
            <div class="col-3">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="ventasSucursalMes" name="ventasSucursalMes[]" style="width:100%;" multiple="multiple" required>
                        <option></option>
                        <?php 
                            for ($i=0; $i < count($arrayMeses); $i++) { 
                                echo '<option value="'.($i + 1).'" '.(date("n") == $i + 1 ? "selected" : "").'>'.$arrayMeses[$i].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="ventasSucursalAnio" name="ventasSucursalAnio[]" style="width:100%;" multiple="multiple" required>
                        <option></option>
                        <?php 
                            for ($i=date("Y"); $i >= $yearData; $i--) { 
                                echo '<option value="'.$i.'" '.(date("Y") == $i ? "selected" : "").'>'.$i.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div id="div-ventasSucursal" class="mb-4"></div>
        <h4>Total de ventas por unidad de negocio</h4>
        <hr>
        <div class="row">
            <div class="col-3">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="ventasUDNMes" name="ventasUDNMes[]" style="width:100%;" multiple="multiple" required>
                        <option></option>
                        <?php 
                            for ($i=0; $i < count($arrayMeses); $i++) { 
                                echo '<option value="'.($i + 1).'" '.(date("n") == $i + 1 ? "selected" : "").'>'.$arrayMeses[$i].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="ventasUDNAnio" name="ventasUDNAnio[]" style="width:100%;" multiple="multiple" required>
                        <option></option>
                        <?php 
                            for ($i=date("Y"); $i >= $yearData; $i--) { 
                                echo '<option value="'.$i.'" '.(date("Y") == $i ? "selected" : "").'>'.$i.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div id="div-ventasUDN" class="mb-4"></div>
        <div class="row">
            <div class="col-12 text-end">
                <button type="button" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-invoice-dollar"></i> Ver más
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <h4>Top de marcas más vendidas</h4>
        <hr>
        <div class="row">
            <div class="col-6">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="topMarcasMes" name="topMarcasMes[]" style="width:100%;" multiple="multiple" required>
                        <option></option>
                        <?php 
                            for ($i=0; $i < count($arrayMeses); $i++) { 
                                echo '<option value="'.($i + 1).'">'.$arrayMeses[$i].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-6">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="topMarcasAnio" name="topMarcasAnio[]" style="width:100%;" multiple="multiple" required>
                        <option></option>
                        <?php 
                            for ($i=date("Y"); $i >= $yearData; $i--) { 
                                echo '<option value="'.$i.'">'.$i.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div id="div-topMarcas" class="mb-4"></div>
        <h4>Top de clientes</h4>
        <hr>
        <div class="row">
            <div class="col-6">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="topClientesMes" name="topClientesMes[]" style="width:100%;" multiple="multiple" required>
                        <option></option>
                        <?php 
                            for ($i=0; $i < count($arrayMeses); $i++) { 
                                echo '<option value="'.($i + 1).'">'.$arrayMeses[$i].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-6">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="topClientesAnio" name="topClientesAnio[]" style="width:100%;" multiple="multiple" required>
                        <option></option>
                        <?php 
                            for ($i=date("Y"); $i >= $yearData; $i--) { 
                                echo '<option value="'.$i.'">'.$i.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div id="div-topClientes" class="mb-4"></div>
        <h4>Top de productos más vendidos</h4>
        <hr>
        <div class="row">
            <div class="col-6">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="topProductosMes" name="topProductosMes[]" style="width:100%;" multiple="multiple" required>
                        <option></option>
                        <?php 
                            for ($i=0; $i < count($arrayMeses); $i++) { 
                                echo '<option value="'.($i + 1).'">'.$arrayMeses[$i].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-6">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="topProductosAnio" name="topProductosAnio[]" style="width:100%;" multiple="multiple" required>
                        <option></option>
                        <?php 
                            for ($i=date("Y"); $i >= $yearData; $i--) { 
                                echo '<option value="'.$i.'">'.$i.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div id="div-topProductos" class="mb-4"></div>
    </div>
</div>
<div class="row mt-3">
    <div class="col-md-12">
        <h4 id="divResumenAnualTitle">Resumen anual</h4>
        <hr>
        <div class="row">
            <div class="col-3">
                <div class="form-select-control mb-4">
                    <select class="form-select" id="resumenAnualTipo" name="resumenAnualTipo" style="width:100%;" required>
                        <option></option>
                        <?php 
                            $arrayResumenAnual = array("Sucursal", "Marca", "Unidad de negocio");
                            for ($i=0; $i < count($arrayResumenAnual); $i++) { 
                                echo '<option value="'.$arrayResumenAnual[$i].'">'.$arrayResumenAnual[$i].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-select-control mb-4">
                    <input type="hidden" id="resumenAnualMes" name="resumenAnualMes" value="12">
                    <select class="form-select" id="resumenAnualAnio" name="resumenAnualAnio" style="width:100%;" required>
                        <option></option>
                        <?php 
                            for ($i=date("Y"); $i >= $yearData; $i--) { 
                                echo '<option value="'.$i.'" '.(date("Y") == $i ? "selected" : "").'>'.$i.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div id="div-resumenAnual"></div>
    </div>
</div>

<script>
    function chartVentas(chartName, tipoParam = '') {
        // tipoParam es para el resumenAnual de momento
        asyncDoDataReturn(
            '<?php echo $_SESSION["currentRoute"]; ?>content/divs/chartVentas', 
            {
                chartName: chartName,
                meses: $(`#${chartName}Mes`).val(),
                anios: $(`#${chartName}Anio`).val(),
                tipoParametrizacion: tipoParam
            },
            function(data) {
                $(`#div-${chartName}`).html(data);
            }
        );
    }

    function modalVerMas(chartName, tipoParam = '') {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'fullscreen',
                modalTitle: `Ver más`,
                modalForm: 'verMasVentas',
                formData: '',
                buttonAcceptShow: true,
                buttonAcceptText: 'Ver más',
                buttonAcceptIcon: 'file-invoice-dollar',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    $(document).ready(function() {
        // Prevenir error que ya fue declarado el grafico
        let chartVentasSucursal = '', chartVentasUDN = '', chartResumenAnual = '', chartTopMarcas = '', chartTopClientes = '', chartTopProductos = '';

        chartVentas('ventasSucursal');
        chartVentas('ventasUDN');

        $("#ventasSucursalMes, #ventasUDNMes, #topMarcasMes, #topClientesMes, #topProductosMes").select2({
            placeholder: 'Mes(es)'
        });

        $("#ventasSucursalAnio, #ventasUDNAnio, #resumenAnualAnio, #topMarcasAnio, #topClientesAnio, #topProductosAnio").select2({
            placeholder: 'Año(s)'
        });

        $("#resumenAnualTipo").select2({
            placeholder: 'Tipo de resumen'
        });

        $("#ventasSucursalMes").change(function(e) {
            chartVentas('ventasSucursal');
        });

        $("#ventasSucursalAnio").change(function(e) {
            chartVentas('ventasSucursal');
        });

        $("#ventasUDNMes").change(function(e) {
            chartVentas('ventasUDN');
        });

        $("#ventasUDNAnio").change(function(e) {
            chartVentas('ventasUDN');
        });

        $("#resumenAnualAnio").change(function(e) {
            chartVentas('resumenAnual', $("#resumenAnualTipo").val());
        });

        $("#resumenAnualTipo").change(function(e) {
            chartVentas('resumenAnual', $("#resumenAnualTipo").val());
        });

        $("#topMarcasMes").change(function(e) {
            chartVentas('topMarcas');
        });

        $("#topMarcasAnio").change(function(e) {
            chartVentas('topMarcas');
        });

        $("#topClientesMes").change(function(e) {
            chartVentas('topClientes');
        });

        $("#topClientesAnio").change(function(e) {
            chartVentas('topClientes');
        });

        $("#topProductosMes").change(function(e) {
            chartVentas('topProductos');
        });

        $("#topProductosAnio").change(function(e) {
            chartVentas('topProductos');
        });

        $("#btnParametrizacion").click(function(e) {
            changePage(`<?php echo $_SESSION["currentRoute"] ?>`, `parametrizacion-ventas`, ``);
        });
    });
</script>