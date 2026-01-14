<?php 
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$tipoBitacora = $_POST["tipoBitacora"];
$usersArray = isset($_POST["idUsers"]) ? $_POST["idUsers"] : '';
$users = explode(",", $usersArray);
$columnas = isset($_POST["columnas"]) ? $_POST["columnas"] : '';
$fechaInicio = $_POST["fechaInicio"] . " 00:00:00";
$fechaFin = $_POST["fechaFin"] . " 23:59:59";

switch ($tipoBitacora){
    case "bitUsuarios":
        foreach ($users as $usuarioId) {
            $dataBitacora = $cloud->rows("
                SELECT 
                    b.fhLogin AS fhLogin, 
                    b.fhLogout AS fhLogout, 
                    $columnas, 
                    b.remoteIp AS remoteIp, 
                    b.navegador AS navegador,
                    CONCAT(
                        IFNULL(p.apellido1, '-'),
                        ' ',
                        IFNULL(p.apellido2, '-'),
                        ', ',
                        IFNULL(p.nombre1, '-'),
                        ' ',
                        IFNULL(p.nombre2, '-')
                    ) AS nombrePersona
                FROM bit_login_usuarios b
                JOIN conf_usuarios u ON u.usuarioId = b.usuarioId
                JOIN th_personas p ON p.personaId = u.personaId
                WHERE b.flgDelete = 0 AND b.usuarioId = ? AND b.fhLogin BETWEEN '$fechaInicio' AND '$fechaFin'
                ORDER BY b.fhLogin ASC
                ", [$usuarioId]);

            $n = 0;
            foreach($dataBitacora as $bitacora){
                $n += 1;

                if (is_null($bitacora->fhLogout)){
                    $logOut = "No finalizó sesión";
                } else {
                    $logOut = date('d/m/Y H:i:s', strtotime($bitacora->fhLogout));
                }

                $empleado = "
                    <b><i class='fas fa-user'></i> Usuario: </b> $bitacora->nombrePersona<br>
                    <b><i class='fas fa-user-clock'></i> Acceso:</b> ".date('d/m/Y H:i:s', strtotime($bitacora->fhLogin))."<br>
                    <b><i class='fas fa-user-clock'></i> Desconexión:</b> $logOut
                ";

                $arrayTabla = array($n, $empleado);

                $columna = explode(",", $columnas);
                $x = 0; 
                foreach ($columna as $col){
                    $arrayTabla[] = $bitacora->$col;
                }

                $output['data'][] = $arrayTabla;            
            }
        }

        if($n > 0) {
            echo json_encode($output);
        } else {
            // No retornar nada para evitar error "null"
            echo json_encode(array('data'=>'')); 
        }
    break;
    case "bitInactivos":
        $dataBitacora = $cloud->rows("
            SELECT remoteIp, correo, fhIntento, navegador FROM bit_login_inactivos WHERE flgDelete = 0 AND fhIntento BETWEEN '$fechaInicio' AND '$fechaFin'
            ORDER BY fhIntento ASC
        ");
        $n = 0;
        foreach($dataBitacora as $bitacora){
            $n += 1;

            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $bitacora->remoteIp,
                $bitacora->correo,
                date('d/m/Y H:i:s', strtotime($bitacora->fhIntento)),
                $bitacora->navegador
            );
        }
        if($n > 0) {
            echo json_encode($output);
        } else {
            // No retornar nada para evitar error "null"
            echo json_encode(array('data'=>'')); 
        }
    break;
    case "bitExterno":
        $dataBitacora = $cloud->rows("
            SELECT remoteIp, correo, fhIntento, navegador FROM bit_login_externos WHERE flgDelete = 0 AND fhIntento BETWEEN '$fechaInicio' AND '$fechaFin'
            ORDER BY fhIntento ASC
        ");
        $n = 0;
        foreach($dataBitacora as $bitacora){
            $n += 1;

            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $bitacora->remoteIp,
                $bitacora->correo,
                date('d/m/Y H:i:s', strtotime($bitacora->fhIntento)),
                $bitacora->navegador
            );
        }
        if($n > 0) {
            echo json_encode($output);
        } else {
            // No retornar nada para evitar error "null"
            echo json_encode(array('data'=>'')); 
        }
    break;
}