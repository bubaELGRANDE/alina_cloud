<?php
@session_start();
include("../../../libraries/includes/logic/mgc/datos94.php");
$fhActual = date("Y-m-d H:i:s");

/*
    POST:
    file = archivo del reporte
    extension = pdf, xls
*/
if (isset($_SESSION["usuarioId"])) {
    // Verificar si la persona continua activa (en caso que se dé de baja y tenga su sesión abierta)
    $dataEstadoPersona = $cloud->row("
            SELECT
                estadoPersona
            FROM th_personas
            WHERE personaId = ?
        ", [$_SESSION['personaId']]);
    // Verificar si su usuario sigue activo (en caso que se suspenda su acceso desde admin. usuarios)
    $dataEstadoUsuario = $cloud->row("
            SELECT
                estadoUsuario
            FROM conf_usuarios
            WHERE usuarioId = ?
        ", [$_SESSION['usuarioId']]);
    if ($dataEstadoPersona->estadoPersona == "Activo" && $dataEstadoUsuario->estadoUsuario == "Activo") {
        // Validar permisos de reportes

        // Ruta del reporte en el módulo
        $route = $_SESSION['currentRoute'] . 'reports/' . $_POST['extension'] . '/' . $_POST['file'] . '?';
        $flgWarning = 0;
        $flgScript = 0;
        switch ($_POST['file']) {
            case 'parametrizacionComisiones':
                // Se apunta a este archivo (utilizar el route creado arriba), solamente agregar a los array los nombres de los POST que se necesitan
                $arrayPost = array("filtroLineas");
                $arrayPostMultiple = array("lineaId");
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de parametrización de comisiones, filtro de líneas: $_POST[filtroLineas]");
                break;

            case 'dte':
                /*
                    POST:
                    extension
                    file
                    tipoDTEId
                    fechaEmision
                    facturaId
                */
                if (isset($_POST['facturaId'])) {
                    switch ($_POST['tipoDTEId']) {
                        case '1':
                            $route = $_SESSION['currentRoute'] . 'reports/' . $_POST['extension'] . '/facturaDTE?';
                            break;
                        case '2':
                            $route = $_SESSION['currentRoute'] . 'reports/' . $_POST['extension'] . '/creditoFiscalDTE?';
                            break;
                        case '3':
                            $route = $_SESSION['currentRoute'] . 'reports/' . $_POST['extension'] . '/notaRemisionDTE?';
                            break;
                        case '4':
                            $route = $_SESSION['currentRoute'] . 'reports/' . $_POST['extension'] . '/notaCreditoDTE?';
                            break;
                        case '6':
                            $route = $_SESSION['currentRoute'] . 'reports/' . $_POST['extension'] . '/comprobanteRetencionDTE?';
                            break;
                        case '9':
                            $route = $_SESSION['currentRoute'] . 'reports/' . $_POST['extension'] . '/facturaExportacionDTE?';
                            break;

                        default:
                            $output = '
                                    <h4 class="text-center mt-5">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i><br>
                                        DTE en desarrollo.<br>
                                        Por favor, comuníquese con el Equipo de Desarrollo.
                                    </h4>
                                ';
                            $flgWarning = 1;
                            $cloud->writeBitacora("movInterfaces", "($fhActual) Generó un DTE en desarrollo");
                            break;
                    }

                    if (isset($_POST['flgCorreo'])) {
                        $arrayPost = array("tipoDTEId", "fechaEmision", "facturaId", "flgCorreo", "tipoEnvioMail", "yearBD");
                    } else {
                        $arrayPost = array("tipoDTEId", "fechaEmision", "facturaId", "tipoEnvioMail", "yearBD");
                    }
                    $arrayPostMultiple = array("correoCliente");
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el DTE para el número de DTE interno: $_POST[facturaId]");
                } else {
                    $output = '
                            <h4 class="text-center mt-5">
                                <i class="fas fa-exclamation-triangle fa-2x"></i><br>
                                DTE no certificado.<br>
                                Por favor, comuníquese con el Equipo de Desarrollo.
                            </h4>
                        ';
                    $flgWarning = 1;
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó un DTE que no fue certificado");
                }
                break;

            case 'comprobanteComisionVendedor':
                // Cambiar el archivo al que se apunta
                if ($_POST['filtroFacturas'] == "F") {
                    // Contado
                    // comprobanteComisionVendedorContado
                    $route = $_SESSION['currentRoute'] . 'reports/' . $_POST['extension'] . '/' . $_POST['file'] . 'Contado?';
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de comprobante de comisión para vendedores, tipo: Contado");
                } else {
                    // Abonos
                    // comprobanteComisionVendedorAbonos
                    $route = $_SESSION['currentRoute'] . 'reports/' . $_POST['extension'] . '/' . $_POST['file'] . 'Abonos?';
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de comprobante de comisión para vendedores, tipo: Abonos");
                }
                // En este array se asigna el nombre de los POST que se quiere enviar al archivo de reportes
                $arrayPost = array("filtroVendedores", "comisionPagarPeriodoId");
                // En este array se asigna el nombre de los POST que son múltiples, ya que se les hace implode para que se interprete como string
                $arrayPostMultiple = array("vendedorId");
                break;

            case 'detalleComisionesFiltro':
                $_SESSION['currentRoute'] . 'reports/' . $_POST['extension'] . '/' . $_POST['file'];
                // En este array se asigna el nombre de los POST que se quiere enviar al archivo de reportes
                $arrayPost = array("comisionPagarPeriodoId", "filtroClientes", "filtroVendedores", "filtroLineas", "filtroPorcentajeComision", "porcentajeComision", "porcentajeComisionR1", "porcentajeComisionR2");
                // En este array se asigna el nombre de los POST que son múltiples, ya que se les hace implode para que se interprete como string
                $arrayPostMultiple = array("clienteId", "vendedorId", "lineaId");
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de comprobante de comisión por detalle para vendedores");
                break;

            case 'provisionComisiones':
                $output = '
                        <script>
                            asyncDoDataReturn(
                                "' . $_SESSION['currentRoute'] . 'content/divs/getReporteProvisionComisiones", 
                                $("#frmModal").serialize(),
                                function(data) {
                                    // Mantener el botón disabled para prevenir que generen más de uno sino carga
                                    button_icons("btnModalAccept", "fas fa-print", "Generar reporte", "enabled");
                                    $("#divReporte").html(data);
                                }
                            );
                        </script>
                    ';
                $flgScript = 1;
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de provisión de comisiones");
                break;

            case 'distribucionGastosComisiones':
                $output = '
                        <script>
                            asyncDoDataReturn(
                                "' . $_SESSION['currentRoute'] . 'content/divs/getReporteDistribucionGastosComisiones", 
                                $("#frmModal").serialize(),
                                function(data) {
                                    // Mantener el botón disabled para prevenir que generen más de uno sino carga
                                    button_icons("btnModalAccept", "fas fa-print", "Generar reporte", "enabled");
                                    $("#divReporte").html(data);
                                }
                            );
                        </script>
                    ';
                $flgScript = 1;
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de distribución de gastos de comisiones");
                break;

            case 'comisionCompartidaVendedores':
                $route = $_SESSION['currentRoute'] . 'reports/' . $_POST['extension'] . '/' . $_POST['file'] . '?';
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de comisiones compartidas entre vendedores");

                $arrayPost = array("comisionPagarPeriodoId");
                break;

            case 'salariosExpedientes':
                $arrayPost = array("filtroEmpleados", "filtroClasificacion", "flgClasificacion");
                $arrayPostMultiple = array("selectEmpleados", "selectClasificacion");
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de salarios de expedientes, filtro de empleados: $_POST[filtroEmpleados]");
                break;

            case 'consolidadoComisionesVendedor':
                if ($_POST['formatoReporte'] == "PDF") {
                    $route = $_SESSION['currentRoute'] . 'reports/pdf/consolidadoComisionesVendedor?';

                    $arrayPost = array("filtroVendedores", "comisionPagarPeriodoId", "txtPeriodo");
                    $arrayPostMultiple = array("vendedorId");

                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de consolidado de comisiones por vendedor (PDF)");
                } else {
                    // Excel
                    $output = '
                            <script>
                                asyncDoDataReturn(
                                    "' . $_SESSION['currentRoute'] . 'reports/xls/consolidadoComisionesVendedor", 
                                    $("#frmModal").serialize(),
                                    function(data) {
                                        // Mantener el botón disabled para prevenir que generen más de uno sino carga
                                        button_icons("btnModalAccept", "fas fa-print", "Generar reporte", "enabled");
                                        $("#divReporte").html(data);
                                    }
                                );
                            </script>
                        ';
                    $flgScript = 1;
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de consolidado de comisiones por vendedor (Excel)");
                }
                break;

            case 'pagos-transferencias-fecha':
                $arrayPost = array("fechaPagoTransferenciaReporte", "pagoTransferenciaIdReporte");

                $route = $_SESSION['currentRoute'] . 'reports/pdf/pagosTransferenciasXFecha?';

                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de transferencia de fecha {$_POST['fechaPagoTransferenciaReporte']} (Transferencia ID: {$_POST['pagoTransferenciaIdReporte']})");
                //$arrayPostMultiple = array("nombreSelectMultiple");        
                break;

            case "bonos-empleados":
                if ($_POST['formatoReporte'] == "Excel") {
                    $output = '
                            <script>
                                asyncData(
                                    "' . $_SESSION['currentRoute'] . 'reports/xls/bonosPagosEmpleadosXLS", 
                                    $("#frmModal").serialize(),
                                    function(data) {
                                        // Mantener el botón disabled para prevenir que generen más de uno sino carga
                                        $("#divReporte").html(data);
                                    }
                                );
                            </script>
                        ';
                    $flgScript = 1;
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de pago de bonos a empleados del periodo: {$_POST['txtPeriodo']} (Excel)");
                } else {
                    // PDF
                    $arrayPost = array("periodoBonoId", "txtPeriodo", "fechaPagoBono");
                    //$arrayPostMultiple = array("nombreSelectMultiple");  
                    $route = $_SESSION['currentRoute'] . 'reports/pdf/bonosPagosEmpleados?';
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de pago de bonos a empleados del periodo: {$_POST['txtPeriodo']}");
                }
                break;
            case 'partidaContable':
                $_SESSION['currentRoute'] . 'reports/pdf/reportePartidaContable.php';
                // En este array se asigna el nombre de los POST que se quiere enviar al archivo de reportes
                $arrayPost = array("partidaContableId");
                // En este array se asigna el nombre de los POST que son múltiples, ya que se les hace implode para que se interprete como string
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de partidaContable");
                break;
            case 'balanceComprobacionDetalle':
                if($_POST['extension'] == "xls"){
                    $output = '
                            <script>
                                asyncData(
                                    "' . $_SESSION['currentRoute'] . 'reports/xls/balanceComprobacionDetalle.php", 
                                    {
                                        fechaInicio: '.$_POST['fechaInicio'].'
                                    },
                                    function(data) {
                                        // Mantener el botón disabled para prevenir que generen más de uno sino carga
                                        $("#divReporte").html(data);
                                    }
                                );
                            </script>
                        ';
                    $flgScript = 1;
                }
                else{
                    //$arrayPost = array("fechaInicio");
                    $_SESSION['currentRoute'] . 'reports/pdf/balanceComprobacionDetalle.php';
                }
                 $arrayPost = array("fechaInicio");
                // En este array se asigna el nombre de los POST que son múltiples, ya que se les hace implode para que se interprete como string
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de balance de comprobación al detalle");
                break;
            case 'mayorContable':
                if($_POST['extension'] == "xls"){
                    $output = '
                            <script>
                                asyncData(
                                    "' . $_SESSION['currentRoute'] . 'reports/xls/mayorContable.php", 
                                    {
                                        fechaInicio: '.$_POST['fechaInicio'].'
                                    },
                                    function(data) {
                                        // Mantener el botón disabled para prevenir que generen más de uno sino carga
                                        $("#divReporte").html(data);
                                    }
                                );
                            </script>
                        ';
                    $flgScript = 1;
                }
                else{
                    //$arrayPost = array("fechaInicio");
                    $_SESSION['currentRoute'] . 'reports/pdf/mayorContable.php';
                }
                 $arrayPost = array("fechaInicio");
                // En este array se asigna el nombre de los POST que son múltiples, ya que se les hace implode para que se interprete como string
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de Mayor contable al detalle");
                break;
            case 'totalVenta':
                if($_POST['extension'] == "xls"){
                    $output = '
                            <script>
                                asyncData(
                                    "' . $_SESSION['currentRoute'] . 'reports/xls/totalVenta.php", 
                                    {
                                        fechaInicio: '.$_POST['fechaInicio'].'
                                    },
                                    function(data) {
                                        // Mantener el botón disabled para prevenir que generen más de uno sino carga
                                        $("#divReporte").html(data);
                                    }
                                );
                            </script>
                        ';
                    $flgScript = 1;
                }
                else{
                    //$arrayPost = array("fechaInicio");
                    $_SESSION['currentRoute'] . 'reports/pdf/totalVenta.php';
                }
                 $arrayPost = array("fechaInicio");
                // En este array se asigna el nombre de los POST que son múltiples, ya que se les hace implode para que se interprete como string
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de Mayor contable al detalle");
                break;
            case 'balanceComprobacion':
                if($_POST['extension'] == "xls"){
                    $output = '
                            <script>
                                asyncData(
                                    "' . $_SESSION['currentRoute'] . 'reports/xls/balanceComprobacion.php", 
                                    {
                                        fechaInicio: '.$_POST['fechaInicio'].'
                                    },
                                    function(data) {
                                        // Mantener el botón disabled para prevenir que generen más de uno sino carga
                                        $("#divReporte").html(data);
                                    }
                                );
                            </script>
                        ';
                    $flgScript = 1;
                }
                else{
                    $_SESSION['currentRoute'] . 'reports/pdf/balanceComprobacion.php';
                }
                 $arrayPost = array("fechaInicio");
                // En este array se asigna el nombre de los POST que son múltiples, ya que se les hace implode para que se interprete como string
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de balance de comprobación general");
                break;
            case 'libroDiario':
                if($_POST['extension'] == "xls"){
                    $output = '
                            <script>
                                asyncData(
                                    "' . $_SESSION['currentRoute'] . 'reports/xls/libroDiario.php", 
                                    {
                                        fechaInicio: '.$_POST['fechaInicio'].'
                                    },
                                    function(data) {
                                        // Mantener el botón disabled para prevenir que generen más de uno sino carga
                                        $("#divReporte").html(data);
                                    }
                                );
                            </script>
                        ';
                    $flgScript = 1;
                }
                else{
                    $_SESSION['currentRoute'] . 'reports/pdf/libroDiario.php';
                }
                 $arrayPost = array("fechaInicio");
                // En este array se asigna el nombre de los POST que son múltiples, ya que se les hace implode para que se interprete como string
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de Libro diario general.");
                break;
            case 'balanceGeneral':
                if($_POST['extension'] == "xls"){
                    $output = '
                            <script>
                                asyncData(
                                    "' . $_SESSION['currentRoute'] . 'reports/xls/balanceGeneral.php", 
                                    {
                                        fechaInicio: '.$_POST['fechaInicio'].'
                                    },
                                    function(data) {
                                        $("#divReporte").html(data);
                                    }
                                );
                            </script>
                        ';
                    $flgScript = 1;
                }
                else{
                    $_SESSION['currentRoute'] . 'reports/pdf/balanceGeneral.php';
                }
                 $arrayPost = array("fechaInicio");
                // En este array se asigna el nombre de los POST que son múltiples, ya que se les hace implode para que se interprete como string
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de Balance general.");
                break;
            case 'estadoResultadoCCosto':
                if($_POST['extension'] == "xls"){
                    $output = '
                            <script>
                                asyncData(
                                    "' . $_SESSION['currentRoute'] . 'reports/xls/estadoResultadoCCosto.php", 
                                    {
                                        fechaInicio: '.$_POST['fechaInicio'].'
                                    },
                                    function(data) {
                                        $("#divReporte").html(data);
                                    }
                                );
                            </script>
                        ';
                    $flgScript = 1;
                }
                else{
                    $_SESSION['currentRoute'] . 'reports/pdf/estadoResultadoCCosto.php';
                }
                 $arrayPost = array("fechaInicio");
                // En este array se asigna el nombre de los POST que son múltiples, ya que se les hace implode para que se interprete como string
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de Estado de resultado por centro de costo.");
                break;
            case 'diarioCuentas':
                if($_POST['extension'] == "xls"){
                    $output = '
                            <script>
                                asyncData(
                                    "' . $_SESSION['currentRoute'] . 'reports/xls/diarioCuentas.php", 
                                    {
                                        fechaInicio: '.$_POST['fechaInicio'].',
                                        numCuenta: '.$_POST['numCuenta'].'
                                    },
                                    function(data) {
                                        $("#divReporte").html(data);
                                    }
                                );
                            </script>
                        ';
                    $flgScript = 1;
                }
                else{
                    $_SESSION['currentRoute'] . 'reports/pdf/diarioCuentas.php';
                }
                 $arrayPost = array("fechaInicio","numCuenta");
                // En este array se asigna el nombre de los POST que son múltiples, ya que se les hace implode para que se interprete como string
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte Diario por cuentas.");
                break;
            case 'audiCCosto':
                if($_POST['extension'] == "xls"){
                    $output = '
                            <script>
                                asyncData(
                                    "' . $_SESSION['currentRoute'] . 'reports/xls/audiCCosto.php", 
                                    {
                                        fechaInicio: '.$_POST['fechaInicio'].',
                                        fechaFin: '.$_POST['fechaFin'].'
                                    },
                                    function(data) {
                                        $("#divReporte").html(data);
                                    }
                                );
                            </script>
                        ';
                    $flgScript = 1;
                }
                else{
                    $_SESSION['currentRoute'] . 'reports/pdf/audiCCosto.php';
                }
                 $arrayPost = array("fechaInicio","fechaFin");
                // En este array se asigna el nombre de los POST que son múltiples, ya que se les hace implode para que se interprete como string
                $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de Auditoria por centro de costo.");
                break;
            default:
                $output = '
                        <h4 class="text-center mt-5">
                            <i class="fas fa-exclamation-triangle fa-2x"></i><br>
                            Reporte no encontrado.<br>
                            Por favor, comuníquese con el Equipo de Desarrollo.
                        </h4>
                    ';
                $flgWarning = 1;
                $cloud->writeBitacora("movInterfaces", "($fhActual) Intentó generar un reporte que no se encuentra disponible");
                break;
        }

        if ($flgWarning == 0 && $flgScript == 0) {
            // Para avisarle al otro for que ya se concatenó un elemento y agregue el &
            $n = 0;
            // Iterar los POST normales
            if (isset($arrayPost)) {
                for ($i = 0; $i < count($arrayPost); $i++) {
                    // Esta validación es por si es un select o input que depende de otro pero por el filtro de reporte NO se seleccionó
                    if (isset($_POST[$arrayPost[$i]])) {
                        // Si es un input o select normal
                        $route .= ($i > 0) ? '&' . $arrayPost[$i] . '=' . urlencode(base64_encode($_POST[$arrayPost[$i]])) : $arrayPost[$i] . '=' . urlencode(base64_encode($_POST[$arrayPost[$i]]));
                        $n += 1;
                    } else {
                        // Omitir, en los filtros de la interfaz NO se asignó/seleccionó
                    }
                }
            } else {
                // No hay post normales que enviar
            }

            if (isset($arrayPostMultiple)) {
                // Iterar los POST multiples
                for ($i = 0; $i < count($arrayPostMultiple); $i++) {
                    // Esta validación es por si es un select o input que depende de otro pero por el filtro de reporte NO se seleccionó
                    if (isset($_POST[$arrayPostMultiple[$i]])) {
                        // Es un POST multiple, su trato es diferente
                        $route .= ($n > 0) ? '&' . $arrayPostMultiple[$i] . '=' . urlencode(base64_encode(implode(",", $_POST[$arrayPostMultiple[$i]]))) : $arrayPostMultiple[$i] . '=' . urlencode(base64_encode(implode(",", $_POST[$arrayPostMultiple[$i]])));
                    } else {
                        // Omitir, en los filtros de la interfaz NO se asignó/seleccionó
                    }
                }
            } else {
                // No hay post multiples que enviar
            }

            $output = '
                    <div class="report-container">
                        <div class="fake-loader">
                            <i class="fas fa-circle-notch fa-spin"></i> Por favor espere...
                        </div>
                        <object class="report" data="' . $route . '"></object>
                    </div>
                ';
        } else {
            // El output será un aviso o un script
        }

        if (isset($_POST['flgCorreo'])) {
            echo $route;
        } else {
            echo $output;
        }
    } else {
        // Cerrar su sesión
        echo '
                <script>
                    location.href = "../libraries/includes/logic/session/logout?flg=baja-emp";
                </script>
            ';
    }
} else {
    // Cerrar sesión por inactividad
    echo '
            <script>
                location.href = "../libraries/includes/logic/session/logout?flg=0";
            </script>
        ';
}
?>