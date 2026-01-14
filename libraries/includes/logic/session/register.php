<?php
@session_start();
include("../mgc/datos94.php");
error_reporting(0);

$_SESSION["writeBitacora"] = "yes";

$nombre = $_POST["nameRegister"];
$dui = $_POST["dui"];
$fecha = date_create($_POST["fechaNacimiento"]);
$fecha_format = date_format($fecha, "Y-m-d");
$mail = $_POST["mailRegister"];
$user = explode("@", $mail);
$passwordEgg = base64_encode(md5("( ͡° ͜ʖ ͡°)"));

// quitar comentarios al subirlo al server
//$remoteIp = $_SERVER['REMOTE_ADDR'];
//$forwardIp = $_SERVER['HTTP_X_FORWARDED_FOR'];

$remoteIp = "localhost";
$forwardIp = "localhost";
$fhActual = date("Y-m-d H:i:s");

$personCheck = "SELECT personaId FROM th_personas WHERE numIdentidad = ? AND fechaNacimiento = ? AND estadoPersona = 'Activo'";
$existPerson = $cloud->count($personCheck, [$dui, $fecha_format]);

$userCheck = "SELECT correo FROM conf_usuarios WHERE correo = ?";
$existUser = $cloud->count($userCheck, [$mail]);

$externoCheck = "SELECT dui, correo FROM bit_solicitudes_acceso WHERE dui = ? OR correo = ?";
$existExterno = $cloud->count($externoCheck, [$dui, $mail]);

if($existPerson > 0 && $existUser < 1) {
    
    $arrayCorreo = explode("@", $mail);
    
    if($arrayCorreo[1] != "alina.jewelry"){
        echo "noalina";
    }else {
        $existPerson = $cloud->row($personCheck, [$dui, $fecha_format]);
        $crearUser = [
           'personaId'		=> $existPerson->personaId,
           'usuario'     	=> strtoupper($user[0]),
           'correo'         => $mail,
           'pass'			=> $passwordEgg,
           'estadoUsuario'  => 'Pendiente'
        ];

        $cloud->insert('conf_usuarios', $crearUser);

        // Envío de correo

        echo "success";
    }
    
} else if($existPerson > 0 && $existUser > 0){
    echo "existe";
} else if($existExterno < 1 ){
    $solicitarAcceso = [
        'nombreSolicitud'   => $nombre,
        'dui'               => $dui,
        'fechaNacimiento'   => $fecha_format,
        'correo'            => $mail,
        'fhSolicitud'       => $fhActual,
        'remoteIp'		    => $remoteIp,
        'forwardIp'         => $forwardIp,
        'navegador'	        => $_SERVER['HTTP_USER_AGENT']
    ];
    
    $cloud->insert('bit_solicitudes_acceso', $solicitarAcceso);
    
    //Envío de correo a admins
    
    echo "success";
} else {
    echo "existe";
}