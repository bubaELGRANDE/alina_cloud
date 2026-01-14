<?php
require_once("../../../../libraries/includes/logic/mgc/datos94.php");
require_once("../../../../libraries/includes/logic/mgc/datos24.php");
@session_start();

$arrayMeses = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

$dataBitExportacionPersonas = $cloud->row("
        SELECT 
            expo.fhExportacion AS fhExportacion,
            exp.nombreCompletoNA AS nombreCompletoNA
        FROM bit_exportaciones_magic expo
        JOIN view_expedientes exp ON exp.personaId = expo.personaId
        WHERE expo.descripcionExportacion = ? AND expo.flgDelete = ?
        LIMIT 1
    ", ["th_personas", 0]);

if ($dataBitExportacionPersonas) {
    $actualizacionPersonas = date("d/m/Y H:i:s", strtotime($dataBitExportacionPersonas->fhExportacion)) . " por {$dataBitExportacionPersonas->nombreCompletoNA}";
} else {
    $actualizacionPersonas = "-";
}

$dataBitPagoBonos = $cloud->row("
        SELECT 
            expo.fhExportacion AS fhExportacion,
            exp.nombreCompletoNA AS nombreCompletoNA
        FROM bit_exportaciones_magic expo
        JOIN view_expedientes exp ON exp.personaId = expo.personaId
        WHERE expo.descripcionExportacion = ? AND expo.flgDelete = ?
        LIMIT 1
    ", ["conta_planilla_bonos", 0]);

if ($dataBitPagoBonos) {
    $actualizacionPagoBonos = date("d/m/Y H:i:s", strtotime($dataBitPagoBonos->fhExportacion)) . " por {$dataBitPagoBonos->nombreCompletoNA}";
} else {
    $actualizacionPagoBonos = "-";
}

$dataBitPagoComisiones = $cloud->row("
        SELECT 
            expo.fhExportacion AS fhExportacion,
            exp.nombreCompletoNA AS nombreCompletoNA
        FROM bit_exportaciones_magic expo
        JOIN view_expedientes exp ON exp.personaId = expo.personaId
        WHERE expo.descripcionExportacion = ? AND expo.flgDelete = ?
        LIMIT 1
    ", ["conta_comision_pagar_calculo", 0]);

if ($dataBitPagoComisiones) {
    $actualizacionPagoComisiones = date("d/m/Y H:i:s", strtotime($dataBitPagoComisiones->fhExportacion)) . " por {$dataBitPagoComisiones->nombreCompletoNA}";
} else {
    $actualizacionPagoComisiones = "-";
}

$dataBitPagoBonosCuentas = $cloud->row("
        SELECT 
            expo.fhExportacion AS fhExportacion,
            exp.nombreCompletoNA AS nombreCompletoNA
        FROM bit_exportaciones_magic expo
        JOIN view_expedientes exp ON exp.personaId = expo.personaId
        WHERE expo.descripcionExportacion = ? AND expo.flgDelete = ?
        LIMIT 1
    ", ["conta_planilla_bonos - cuenta contable", 0]);

if ($dataBitPagoBonosCuentas) {
    $actualizacionPagoBonosCuentas = date("d/m/Y H:i:s", strtotime($dataBitPagoBonosCuentas->fhExportacion)) . " por {$dataBitPagoBonosCuentas->nombreCompletoNA}";
} else {
    $actualizacionPagoBonosCuentas = "-";
}

$dataBitPagoComisionesCuentas = $cloud->row("
        SELECT 
            expo.fhExportacion AS fhExportacion,
            exp.nombreCompletoNA AS nombreCompletoNA
        FROM bit_exportaciones_magic expo
        JOIN view_expedientes exp ON exp.personaId = expo.personaId
        WHERE expo.descripcionExportacion = ? AND expo.flgDelete = ?
        LIMIT 1
    ", ["conta_comision_pagar_calculo - cuenta contable", 0]);

if ($dataBitPagoComisionesCuentas) {
    $actualizacionPagoComisionesCuentas = date("d/m/Y H:i:s", strtotime($dataBitPagoComisionesCuentas->fhExportacion)) . " por {$dataBitPagoComisionesCuentas->nombreCompletoNA}";
} else {
    $actualizacionPagoComisionesCuentas = "-";
}

$testConexion = $magic->row("
        SELECT COUNT(idProductos) as idProductos FROM Productos
    ");

if ($testConexion) {
    $conexionMagic = "<p class='text-success fw-bold'><i class='fas fa-thumbs-up'></i> Conectado</p>";
} else {
    $conexionMagic = "<p class='text-danger fw-bold'><i class='fas fa-heart-broken'></i> No establecida</p>";
}
?>
<h2>Sincronización de bases de datos</h2>
<br>
<h5>Conexión a BD Magic: </h5> <?php echo $conexionMagic; ?>
<hr>
<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1" role="tab" aria-controls="ntab-content-1" aria-selected="true">
            Exportación: Cloud a Magic
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2" role="tab" aria-controls="ntab-content-3" aria-selected="false">
            Importación: Magic a Cloud
        </a>
    </li>
</ul>
<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">
        <!-- Exportación de th_personas -->
        <div class="alert alert-primary " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Exportación de empleados a Magic</b></div>
                <div>
                    <button type="button" id="btnExportarPersonas" class="btn btn-outline-primary btn-sm" onclick="exportEmpleados();">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> <?php echo $actualizacionPersonas; ?></div>
        </div>
        <!-- Exportación de conta_planilla_bonos -->
        <div class="alert alert-primary " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Exportación de bonificaciones de empleados (pagos de bonos)</b></div>
                <div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-select-control">
                                <select id="periodoBonoIdPagosBonos" name="periodoBonoId" style="width:100%;" class="form-select" required>
                                    <option></option>
                                    <?php
                                    $dataPeriodos = $cloud->rows("
                                            SELECT periodoBonoId, mes, anio FROM conta_periodos_bonos
                                            WHERE estadoPeriodoBono IN ('Finalizado') AND flgDelete = ?
                                            ORDER BY anio, mes
                                        ", [0]);
                                    foreach ($dataPeriodos as $periodo) {
                                        echo "<option value='$periodo->periodoBonoId'>{$arrayMeses[$periodo->mes]} - $periodo->anio</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <button type="button" id="btnExportarPagosBonos" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-file-export"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> <?php echo $actualizacionPagoBonos; ?></div>
        </div>
        <!-- Exportación de conta_comision_pagar_calculo -->
        <div class="alert alert-primary " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Exportación de comisiones a vendedores (pago de comisiones)</b></div>
                <div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-select-control">
                                <select id="comisionPagarPeriodoIdComision" name="comisionPagarPeriodoId" style="width:100%;" class="form-select" required>
                                    <option></option>
                                    <?php
                                    $dataPeriodoComisiones = $cloud->rows("
                                            SELECT 
                                                comisionPagarPeriodoId, 
                                                numMes, 
                                                mes, 
                                                anio 
                                            FROM conta_comision_pagar_periodo
                                            WHERE flgDelete = ?
                                            ORDER BY comisionPagarPeriodoId DESC
                                        ", [0]);
                                    foreach ($dataPeriodoComisiones as $periodoComision) {
                                        echo "<option value='{$periodoComision->comisionPagarPeriodoId}'>{$periodoComision->mes} - $periodoComision->anio</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <button type="button" id="btnExportarPagosComisiones" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-file-export"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> <?php echo $actualizacionPagoComisiones; ?></div>
        </div>
        <!-- Exportación de conta_planilla_bonos - cuentas contables -->
        <div class="alert alert-primary " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Exportación de bonificaciones de empleados por cuenta contable</b></div>
                <div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-select-control">
                                <select id="periodoBonoIdPagosBonosCuentas" name="periodoBonoId" style="width:100%;" class="form-select" required>
                                    <option></option>
                                    <?php
                                    $dataPeriodos = $cloud->rows("
                                            SELECT periodoBonoId, mes, anio FROM conta_periodos_bonos
                                            WHERE estadoPeriodoBono IN ('Finalizado') AND flgDelete = ?
                                            ORDER BY anio, mes
                                        ", [0]);
                                    foreach ($dataPeriodos as $periodo) {
                                        echo "<option value='$periodo->periodoBonoId'>{$arrayMeses[$periodo->mes]} - $periodo->anio</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <br>
                            <div class="form-outline mb-4">
                                <input type="date" id="fechaCuentaPagosBonosCuentas" name="fechaCuenta" class="form-control" required />
                                <label class="form-label" for="fechaCuentaPagosBonosCuentas">Fecha cuentas</label>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <button type="button" id="btnExportarPagosBonosCuentas" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-file-export"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> <?php echo $actualizacionPagoBonosCuentas; ?></div>
        </div>
        <!-- Exportación de conta_comision_pagar_calculo - cuentas contables -->
        <div class="alert alert-primary " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Exportación de comisiones a vendedores por cuenta contable</b></div>
                <div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-select-control">
                                <select id="comisionPagarPeriodoIdComisionCuentas" name="comisionPagarPeriodoId" style="width:100%;" class="form-select" required>
                                    <option></option>
                                    <?php
                                    $dataPeriodoComisiones = $cloud->rows("
                                            SELECT 
                                                comisionPagarPeriodoId, 
                                                numMes, 
                                                mes, 
                                                anio 
                                            FROM conta_comision_pagar_periodo
                                            WHERE flgDelete = ?
                                            ORDER BY comisionPagarPeriodoId DESC
                                        ", [0]);
                                    foreach ($dataPeriodoComisiones as $periodoComision) {
                                        echo "<option value='{$periodoComision->comisionPagarPeriodoId}'>{$periodoComision->mes} - $periodoComision->anio</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <br>
                            <div class="form-outline mb-4">
                                <input type="date" id="fechaCuentaComisionesCuentas" name="fechaCuenta" class="form-control" required />
                                <label class="form-label" for="fechaCuentaComisionesCuentas">Fecha cuentas</label>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <button type="button" id="btnExportarPagosComisionesCuentas" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-file-export"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> <?php echo $actualizacionPagoComisionesCuentas; ?></div>
        </div>
    </div>

    <div class="tab-pane fade" id="ntab-content-2" role="tabpanel" aria-labelledby="ntab-2">
        <!-- Importación a prod_productos -->
        <div class="alert alert-info " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Importación de productos a Cloud</b></div>
                <div>
                    <button type="button" id="btnImportarProductos" class="btn btn-outline-info btn-sm" onclick="importarProductos();">
                        <i class="fas fa-file-export"></i> Importar
                    </button>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> </div>
        </div>

        <div class="alert alert-info " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Importación precios de productos a Cloud</b></div>
                <div>
                    <button type="button" id="btnImportarProductos" class="btn btn-outline-info btn-sm" onclick="importarPreciosProductos();">
                        <i class="fas fa-file-export"></i> Importar
                    </button>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> </div>
        </div>
        <div class="alert alert-info " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Importación de Existencias a Cloud</b></div>
                <div>
                    <button type="button" id="btnImportarProductos" class="btn btn-outline-info btn-sm" onclick="importarExistencias();">
                        <i class="fas fa-file-export"></i> Importar
                    </button>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> </div>
        </div>
        <div class="alert alert-info " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Importación de historial de retaceos a Cloud</b></div>
                <div>
                    <button type="button" id="btnImportarProductos" class="btn btn-outline-info btn-sm" onclick="importarHistorialRetaceo();">
                        <i class="fas fa-file-export"></i> Importar
                    </button>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> </div>
        </div>
        <div class="alert alert-info " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Importación de Cuentas contables a Cloud</b></div>
                <div>
                    <button type="button" id="btnImportarProductos" class="btn btn-outline-info btn-sm" onclick="importarCuentaCon();">
                        <i class="fas fa-file-export"></i> Importar
                    </button>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> </div>
        </div>
        <div class="alert alert-info " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Importación encabezado de partidas contables a Cloud</b></div>
                <div>
                    <button type="button" id="btnImportarProductos" class="btn btn-outline-info btn-sm" onclick="importarEncabezadoPartidasCon();">
                        <i class="fas fa-file-export"></i> Importar
                    </button>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> </div>
        </div>
        <div class="alert alert-info " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Importación detalles de partidas contables a Cloud</b></div>
                <div>
                    <button type="button" id="btnImportarProductos" class="btn btn-outline-info btn-sm" onclick="importarPartidasCon();">
                        <i class="fas fa-file-export"></i> Importar
                    </button>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> </div>
        </div>
        <div class="alert alert-info " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Importación Saldos finales e iniciales a cloud</b></div>
                <div>
                    <button type="button" id="btnImportarProductos" class="btn btn-outline-info btn-sm" onclick="importarSaldosPartidasCon();">
                        <i class="fas fa-file-export"></i> Importar
                    </button>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> </div>
        </div>
        <div class="alert alert-info " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Importación de proveedores a Cloud</b></div>
                <div>
                    <button type="button" id="btnImportarProductos" class="btn btn-outline-info btn-sm" onclick="importarProveedor();">
                        <i class="fas fa-file-export"></i> Importar
                    </button>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> </div>
        </div>
        <div class="alert alert-info " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Importación de compras a Cloud</b></div>
                <div>
                    <button type="button" id="btnImportarCompras" class="btn btn-outline-info btn-sm" onclick="importarCompras();">
                        <i class="fas fa-file-export"></i> Importar
                    </button>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> </div>
        </div>
        <div class="alert alert-info " role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div><b>Importación de Ubicaciones y existencias a Cloud</b></div>
                <div>
                    <button type="button" id="btnImportarCompras" class="btn btn-outline-info btn-sm" onclick="importarUbicaciones();">
                        <i class="fas fa-file-export"></i> Importar
                    </button>
                </div>
            </div>
            <hr>
            <div><b>Última actualización:</b> </div>
        </div>
    </div>
</div>
<script>
    function exportEmpleados() {
        let datos = {
            typeOperation: 'update',
            operation: 'exportar-empleados-magic',
        };

        mensaje_confirmacion(
            `¿Está seguro que desea actualizar la base de datos de empleados en Magic?`,
            `Se verificará la información, estados de empleado y empleados recientes.`,
            `warning`,
            (param) => {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    datos,
                    function(data) {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se exportaron los datos de empleados a las tablas de magic.`,
                                "success",
                                function() {
                                    asyncPage(119, 'submenu', '');
                                }
                            );

                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Exportar`,
            `Cancelar`
        );
    }

    function importarProductos() {
        let datos = {
            typeOperation: 'update',
            operation: 'importar-productos-magic',
        };

        mensaje_confirmacion(
            `¿Está seguro que desea actualizar la base de datos de productos de Magic?`,
            `Se actualizarán los datos de los productos o se crearán productos nuevos.`,
            `warning`,
            (param) => {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    datos,
                    function(data) {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se importaron los datos de productos a las tablas de Cloud.`,
                                "success",
                                function() {
                                    asyncPage(119, 'submenu', '');
                                }
                            );

                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Importar`,
            `Cancelar`
        );
    }

    function importarPreciosProductos() {
        let datos = {
            typeOperation: 'update',
            operation: 'importar-precios-magic',
        };

        mensaje_confirmacion(
            `¿Está seguro que desea actualizar la base de datos de precios de productos desde Magic?`,
            `Se actualizarán los datos de losprecios de productos o se crearán precios de roductos nuevos.`,
            `warning`,
            (param) => {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    datos,
                    function(data) {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se importaron los datos de precios de productos a las tablas de Cloud.`,
                                "success",
                                function() {
                                    asyncPage(119, 'submenu', '');
                                }
                            );

                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Importar`,
            `Cancelar`
        );
    }

    function importarHistorialRetaceo() {
        let datos = {
            typeOperation: 'insert',
            operation: 'importar-historiales-retaceos-magic',
        };

        mensaje_confirmacion(
            `¿Está seguro que desea insertar la base de datos de historiales de Magic?`,
            `Se insertaran los historiales de los retaceos pasados.`,
            `warning`,
            (param) => {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    datos,
                    function(data) {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se importaron los datos de productos a las tablas de Cloud.`,
                                "success",
                                function() {
                                    asyncPage(119, 'submenu', '');
                                }
                            );

                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Importar`,
            `Cancelar`
        );
    }

    function importarExistencias() {
        let datos = {
            typeOperation: 'update',
            operation: 'importar-existencia-magic',
        };

        mensaje_confirmacion(
            `¿Está seguro que desea actualizar la base de datos de existencias de productos de Magic?`,
            `Se actualizarán los datos de las existencias de productos.`,
            `warning`,
            (param) => {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    datos,
                    function(data) {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se importaron los datos de productos a las tablas de Cloud.`,
                                "success",
                                function() {
                                    asyncPage(119, 'submenu', '');
                                }
                            );

                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Importar`,
            `Cancelar`
        );
    }
    function importarUbicaciones() {
        let datos = {
            typeOperation: 'update',
            operation: 'importar-ubicaciones-magic',
        };

        mensaje_confirmacion(
            `¿Está seguro que desea actualizar la base de datos de ubicaciones de productos de Magic?`,
            `Se actualizarán los datos de las Ubicaciones de productos.`,
            `warning`,
            (param) => {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    datos,
                    function(data) {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se importaron los datos de productos a las tablas de Cloud.`,
                                "success",
                                function() {
                                    asyncPage(119, 'submenu', '');
                                }
                            );

                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Importar`,
            `Cancelar`
        );
    }

    function importarCuentaCon() {
        let datos = {
            typeOperation: 'insert',
            operation: 'importar-cuentasContables-magic',
        };

        mensaje_confirmacion(
            `¿Está seguro que desea actualizar las cuentas contables de Magic?`,
            `Se importará el catálogo de cuentas desde Magic.`,
            `warning`,
            (param) => {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    datos,
                    function(data) {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se importaron cuentas contables a las tablas de Cloud.`,
                                "success",
                                function() {
                                    asyncPage(119, 'submenu', '');
                                }
                            );

                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Importar`,
            `Cancelar`
        );
    }

    function importarPartidasCon() {
        let datos = {
            typeOperation: 'insert',
            operation: 'importar-partidasContables-magic',
        };

        mensaje_confirmacion(
            `¿Está seguro que desea actualizar las partidas contables de Magic?`,
            `Se importarán las partidas contables  desde Magic.`,
            `warning`,
            (param) => {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    datos,
                    function(data) {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se importaron partidas contables a las tablas de Cloud.`,
                                "success",
                                function() {
                                    asyncPage(119, 'submenu', '');
                                }
                            );

                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Importar`,
            `Cancelar`
        );
    }

    function importarSaldosPartidasCon() {
        let datos = {
            typeOperation: 'update',
            operation: 'importar-saldosPartidasContables-magic',
        };

        mensaje_confirmacion(
            `¿Está seguro que desea actualizar los saldos de las partidas contables ?`,
            `Se importarán las partidas contables  desde Magic.`,
            `warning`,
            (param) => {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    datos,
                    function(data) {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se importaron los saldos de partidas contables a las tablas de Cloud.`,
                                "success",
                                function() {
                                    asyncPage(119, 'submenu', '');
                                }
                            );

                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Importar`,
            `Cancelar`
        );
    }

    function importarEncabezadoPartidasCon() {
        let datos = {
            typeOperation: 'insert',
            operation: 'importar-partidas-contables-encabezado',
        };

        mensaje_confirmacion(
            `¿Está seguro que desea actualizar las partidas contables de Magic?`,
            `Se importarán las partidas contables  desde Magic.`,
            `warning`,
            (param) => {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    datos,
                    function(data) {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se importaron partidas contables a las tablas de Cloud.`,
                                "success",
                                function() {
                                    asyncPage(119, 'submenu', '');
                                }
                            );

                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Importar`,
            `Cancelar`
        );
    }

    function importarProveedor() {
        let datos = {
            typeOperation: 'insert',
            operation: 'importar-proveedores-magic',
        };

        mensaje_confirmacion(
            `¿Está seguro que desea actualizar los proveedores desde Magic?`,
            `Se importará el catálogo de proveedores desde Magic.`,
            `warning`,
            (param) => {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    datos,
                    function(data) {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se importaron cuentas contables a las tablas de Cloud.`,
                                "success",
                                function() {
                                    asyncPage(119, 'submenu', '');
                                }
                            );

                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Importar`,
            `Cancelar`
        );
    }

    function importarCompras() {
        let datos = {
            typeOperation: 'insert',
            operation: 'importar-compras-magic',
        };

        mensaje_confirmacion(
            `¿Está seguro que desea actualizar los proveedores desde Magic?`,
            `Se importarán las compras correspondientes al mes de enero 2025.`,
            `warning`,
            (param) => {
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    datos,
                    function(data) {
                        if (data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                `Se importaron las compras a las tablas de Cloud.`,
                                "success",
                                function() {
                                    asyncPage(119, 'submenu', '');
                                }
                            );

                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Importar`,
            `Cancelar`
        );
    }
    $(document).ready(function() {
        $("#periodoBonoIdPagosBonos").select2({
            placeholder: "Periodo"
        });

        $("#comisionPagarPeriodoIdComision").select2({
            placeholder: "Periodo"
        });

        $("#periodoBonoIdPagosBonosCuentas").select2({
            placeholder: "Periodo"
        });

        $("#comisionPagarPeriodoIdComisionCuentas").select2({
            placeholder: "Periodo"
        });

        $("#btnExportarPagosBonos").click(function(e) {
            if ($("#periodoBonoIdPagosBonos").val() == "") {
                mensaje(
                    "Aviso:",
                    "Debe seleccionar el periodo a exportar",
                    "warning"
                );
            } else {
                mensaje_confirmacion(
                    `¿Está seguro que desea enviar el pago de bonos de empleados a la planilla de Magic?`,
                    `Se enviarán los bonos del periodo seleccionado.`,
                    `warning`,
                    (param) => {
                        asyncData(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", {
                                typeOperation: 'insert',
                                operation: 'exportar-pagos-bonos-magic',
                                periodoBonoId: $("#periodoBonoIdPagosBonos").val()
                            },
                            function(data) {
                                if (data == "success") {
                                    mensaje_do_aceptar(
                                        "Operación completada:",
                                        `Pagos de bonos a empleados exportados a la planilla de Magic con éxito.`,
                                        "success",
                                        function() {
                                            asyncPage(119, 'submenu', '');
                                        }
                                    );
                                } else {
                                    mensaje(
                                        "Aviso:",
                                        data,
                                        "warning"
                                    );
                                }
                            }
                        );
                    },
                    `Exportar`,
                    `Cancelar`
                );
            }
        });

        $("#btnExportarPagosComisiones").click(function(e) {
            if ($("#comisionPagarPeriodoIdComision").val() == "") {
                mensaje(
                    "Aviso:",
                    "Debe seleccionar el periodo a exportar",
                    "warning"
                );
            } else {
                mensaje_confirmacion(
                    `¿Está seguro que desea enviar el pago de comisiones de vendedores a la planilla de Magic?`,
                    `Se enviarán las comisiones del periodo seleccionado.`,
                    `warning`,
                    (param) => {
                        asyncData(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", {
                                typeOperation: 'insert',
                                operation: 'exportar-pagos-comisiones-magic',
                                comisionPagarPeriodoId: $("#comisionPagarPeriodoIdComision").val()
                            },
                            function(data) {
                                if (data == "success") {
                                    mensaje_do_aceptar(
                                        "Operación completada:",
                                        `Pagos de comisiones a vendedores exportados a la planilla de Magic con éxito.`,
                                        "success",
                                        function() {
                                            asyncPage(119, 'submenu', '');
                                        }
                                    );
                                } else {
                                    mensaje(
                                        "Aviso:",
                                        data,
                                        "warning"
                                    );
                                }
                            }
                        );
                    },
                    `Exportar`,
                    `Cancelar`
                );
            }
        });

        $("#btnExportarPagosBonosCuentas").click(function(e) {
            if ($("#periodoBonoIdPagosBonosCuentas").val() == "") {
                mensaje(
                    "Aviso:",
                    "Debe seleccionar el periodo a exportar",
                    "warning"
                );
            } else if ($("#fechaCuentaPagosBonosCuentas").val() == "") {
                mensaje(
                    "Aviso:",
                    "Debe seleccionar la fecha a aplicar en las cuentas contables",
                    "warning"
                );
            } else {
                mensaje_confirmacion(
                    `¿Está seguro que desea enviar el pago de bonos de empleados a las cuentas contables de Magic?`,
                    `Se enviarán los bonos del periodo seleccionado.`,
                    `warning`,
                    (param) => {
                        asyncData(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", {
                                typeOperation: 'insert',
                                operation: 'exportar-pagos-bonos-cuentas-magic',
                                periodoBonoId: $("#periodoBonoIdPagosBonosCuentas").val(),
                                fechaCuenta: $("#fechaCuentaPagosBonosCuentas").val()
                            },
                            function(data) {
                                if (data == "success") {
                                    mensaje_do_aceptar(
                                        "Operación completada:",
                                        `Pagos de bonos a empleados exportados a las cuentas contables de Magic con éxito.`,
                                        "success",
                                        function() {
                                            asyncPage(119, 'submenu', '');
                                        }
                                    );
                                } else {
                                    mensaje(
                                        "Aviso:",
                                        data,
                                        "warning"
                                    );
                                }
                            }
                        );
                    },
                    `Exportar`,
                    `Cancelar`
                );
            }
        });

        $("#btnExportarPagosComisionesCuentas").click(function(e) {
            if ($("#comisionPagarPeriodoIdComisionCuentas").val() == "") {
                mensaje(
                    "Aviso:",
                    "Debe seleccionar el periodo a exportar",
                    "warning"
                );
            } else if ($("#fechaCuentaComisionesCuentas").val() == "") {
                mensaje(
                    "Aviso:",
                    "Debe seleccionar la fecha a aplicar en las cuentas contables",
                    "warning"
                );
            } else {
                mensaje_confirmacion(
                    `¿Está seguro que desea enviar el pago de comisiones de vendedores a las cuentas contables de Magic?`,
                    `Se enviarán las comisiones del periodo seleccionado.`,
                    `warning`,
                    (param) => {
                        asyncData(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", {
                                typeOperation: 'insert',
                                operation: 'exportar-pagos-comisiones-cuentas-magic',
                                comisionPagarPeriodoId: $("#comisionPagarPeriodoIdComisionCuentas").val(),
                                fechaCuenta: $("#fechaCuentaComisionesCuentas").val()
                            },
                            function(data) {
                                if (data == "success") {
                                    mensaje_do_aceptar(
                                        "Operación completada:",
                                        `Comisiones a vendedores exportados a las cuentas contables de Magic con éxito.`,
                                        "success",
                                        function() {
                                            asyncPage(119, 'submenu', '');
                                        }
                                    );
                                } else {
                                    mensaje(
                                        "Aviso:",
                                        data,
                                        "warning"
                                    );
                                }
                            }
                        );
                    },
                    `Exportar`,
                    `Cancelar`
                );
            }
        });
    });
</script>