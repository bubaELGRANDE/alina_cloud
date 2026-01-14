<?php 
    @session_start();
    include("../../../libraries/includes/logic/mgc/datos94.php");
    $fhActual = date("Y-m-d H:i:s");
    
    /*
        POST:
        file = archivo del reporte
        extension = pdf, xls
    */

    if(isset($_SESSION["usuarioId"])) {
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
        if($dataEstadoPersona->estadoPersona == "Activo" && $dataEstadoUsuario->estadoUsuario == "Activo") {
            // Validar permisos de reportes

            // Ruta del reporte en el módulo
            $route = $_SESSION['currentRoute'] . 'reports/' . $_POST['extension'] . '/' . $_POST['file'] . '?';
            $flgWarning = 0; $flgScript = 0;
            switch($_POST['file']) {
                case 'case-ejemplo':
                    // Se apunta a este archivo (utilizar el route creado arriba), solamente agregar a los array los nombres de los POST que se necesitan
                    $arrayPost = array("nombreInputSelect");
                    $arrayPostMultiple = array("nombreSelectMultiple");        
                break;

                case 'ficha-actualizacion-datos':
                    /*
                        POST:
                        extension
                        file
                        filtroEmpleados (radio)
                        selectEmpleados (multiple)
                    */
                   $arrayPost = array("filtroEmpleados");
                   $arrayPostMultiple = array("selectEmpleados");
                   $cloud->writeBitacora("movInterfaces", "($fhActual) Generó la ficha de actualización de datos del empleado: $_POST[filtroEmpleados]");
                break;

                case 'ficha-empleado':
                    /*
                        POST:
                        extension
                        file
                        filtroEmpleados (radio)
                        selectEmpleados (multiple)
                        flgFirmaEmpleado
                    */
                   $arrayPost = array("filtroEmpleados", "flgFirmaEmpleado");
                   $arrayPostMultiple = array("selectEmpleados");
                   $cloud->writeBitacora("movInterfaces", "($fhActual) Generó la ficha del empleado: $_POST[filtroEmpleados]");
                break;

                case 'listado-empleados':
                    $arrayPostMultiple = array("columnasDatos");
                    $output = '
                        <script>
                            asyncDoDataReturn(
                                "'.$_SESSION['currentRoute'].'content/divs/getReporteListaEmpleados", 
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
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó un listado de empleados (reporte con campos dinámicos)");
                break;

                case 'contrato-expediente':
                    /*
                        POST:
                        extension
                        file
                        selectFiltroExpediente (select)
                        selectEmpleadoSimple (select)
                        selectEmpleadoExpediente (select)
                        filtroEmpleados (radio)
                        selectEmpleados (multiple)
                        selectApoderadoLegal (select)
                        fechaContrato
                        filtroSalario
                        salarioContrato
                        salarioContratoLetra
                    */
                    //$arrayPost = array("selectFiltroExpediente", "filtroEmpleados", "selectApoderadoLegal", "fechaContrato", "filtroSalario", "salarioContrato", "salarioContratoLetra");
                    $arrayPost = array("selectEmpleadoSimple", "selectEmpleadoExpediente", "selectApoderadoLegal", "fechaContrato", "filtroSalario", "salarioContrato", "salarioContratoLetra");
                    //$arrayPostMultiple = array("selectEmpleados");
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó un contrato de empleado con fecha: $_POST[fechaContrato], filtro de empleados: $_POST[filtroEmpleados]");
                break;

                case 'incapacidades-por-riesgo':
                    /*
                        POST:
                    */
                    $arrayPost = array("filtroIncapacidades","filtroSucursal","filtroEmpleado","fechaInicio","fechaFin","selectEstadoEmpleado");
                    $arrayPostMultiple = array("selectSucursalesEspecificas","selectEmpleadosEspecificos");
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de incapacidades por riesgo del $_POST[fechaInicio] al $_POST[fechaFin]");
                break;

                case 'capacitaciones-por-cursos':

                    $arrayPost = array("filtroCapacitacionesCursos","fechaInicio", "fechaFin");
                    $arrayPostMultiple = array("selectCursosEspecificos");
                break;
                case 'capacitaciones-por-empleado':
                    
                    $arrayPost = array("filtroCapacitacionesEmpleados","fechaInicio", "fechaFin");
                    $arrayPostMultiple = array("selectEmpleadosEspecificos");
                break;

                case 'vacaciones-individuales':
                    $output = '
                        <script>
                            asyncDoDataReturn(
                                "'.$_SESSION['currentRoute'].'content/divs/getReporteVacacionesIndividuales", 
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
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de vacaciones individuales (programa de vacaciones individuales)");
                break;

                case 'domicilios-empleados':
                    $output = '
                        <script>
                            asyncDoDataReturn(
                                "'.$_SESSION['currentRoute'].'content/divs/getReporteDomiciliosEmpleados", 
                                $("#frmModal").serialize(),
                                function(data) {
                                    $("#divReporte").html(data);
                                }
                            );
                        </script>
                    ';
                    $flgScript = 1;
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó un listado de domicilios de empleados");
                break;

                case 'nomina-empleados-fotos':
                    /*
                        POST:
                        extension
                        file
                        selectFiltroSucursal
                        filtroSucursalDepartamento (radio)
                        selectSucursalDepartamentos (múltiple)
                    */
                    $arrayPost = array("selectFiltroSucursal", "filtroSucursalDepartamento");
                    $arrayPostMultiple = array("selectSucursalDepartamentos");
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de nómina de empleados con fotografía (Sucursal: $_POST[selectFiltroSucursal]");
                break;

                case 'mes-cumpleanios-laboral':
                    $output = '
                        <script>
                            asyncDoDataReturn(
                                "'.$_SESSION['currentRoute'].'reports/excel/cumpleanios-laboral", 
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
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de cumpleaños del mes: laboral");
                break;

                case 'mes-cumpleanios-personal':
                    $output = '
                        <script>
                            asyncDoDataReturn(
                                "'.$_SESSION['currentRoute'].'reports/excel/cumpleanios-personal", 
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
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de cumpleaños del mes: laboral");
                break;

                case 'empleados-con-hijos':
                    $output = '
                        <script>
                            asyncDoDataReturn(
                                "'.$_SESSION['currentRoute'].'reports/excel/empleados-hijos", 
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
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de cumpleaños del mes: laboral");
                break;

                case 'formato-amonestacion':
                    /*
                        POST:
                        filtroAnio
                        amonestacionReporte
                    */
                   $arrayPost = array("filtroAnio", "amonestacionReporte");
                   $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el formato de amonestación N°: $_POST[amonestacionReporte]");
                break;

                case 'listado-amonestacion':
                    /*
                    POST:
                        expedienteAmonestacionId: 2
                        extension: pdf
                        numCargaInterfaz: 
                        file: listado-amonestacion
                        filtroAnio: 
                        amonestacionReporte: 
                        fechaInicio: 
                        fechaFin:
                        estadoAmonestacion:
                    */
                   $arrayPost = array("filtroAnio", "fechaInicio", "fechaFin", "amonestacionReporte", "estadoAmonestacion");
                   $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el formato de amonestación N°: $_POST[amonestacionReporte]");
                break;
                case 'empleados-beneficiarios':
                    $output = '
                        <script>
                            asyncData(
                                "'.$_SESSION['currentRoute'].'reports/excel/empleados-beneficiarios", 
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
                    $cloud->writeBitacora("movInterfaces", "($fhActual) Generó el reporte de empleados y sus beneficiarios");
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

            

            if($flgWarning == 0 && $flgScript == 0) {
                // Para avisarle al otro for que ya se concatenó un elemento y agregue el &
                $n = 0;
                // Iterar los POST normales
                if(isset($arrayPost)) {
                    for ($i=0; $i < count($arrayPost); $i++) { 
                        // Esta validación es por si es un select o input que depende de otro pero por el filtro de reporte NO se seleccionó
                        if(isset($_POST[$arrayPost[$i]])) {
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
                if(isset($arrayPostMultiple)) {
                    // Iterar los POST multiples
                    for ($i=0; $i < count($arrayPostMultiple); $i++) { 
                        // Esta validación es por si es un select o input que depende de otro pero por el filtro de reporte NO se seleccionó
                        if(isset($_POST[$arrayPostMultiple[$i]])) {
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
                        <object class="report" data="'.$route.'"></object>
                    </div>
                ';
            } else {
                // El output será un aviso o un script
            }
            echo $output;
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