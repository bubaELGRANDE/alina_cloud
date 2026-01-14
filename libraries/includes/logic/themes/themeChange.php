<?php
@session_start();
include("../mgc/datos94.php");

$themeSelected = $_POST["tema"];

$updateTheme = [
    'custom' => $themeSelected
];
$where = [
    'usuarioId' => $_SESSION["usuarioId"],
    'tipoCustom' => 'Tema'
];

$cloud->update('mip_perfil_custom', $updateTheme, $where);

echo "succes";