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