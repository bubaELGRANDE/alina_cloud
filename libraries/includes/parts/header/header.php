<?php
@session_start();
include("../libraries/includes/logic/mgc/datos94.php");

// Verificar si las variables de sesión existen antes de acceder a ellas
$usuarioId = $_SESSION["usuarioId"] ?? null;
$flgPassword = $_SESSION["flgPassword"] ?? null;
$inactividad = $_SESSION["inactividad"] ?? null;

// Validación de sesión corregida
if (!isset($usuarioId) || $usuarioId == NULL) {
    if (isset($inactividad) && $inactividad == 0) {
        header("Location: /login");
        exit();
    } else {
        header("Location: /cierre-inactividad");
        exit();
    }
} else {
    if (isset($flgPassword) && $flgPassword == "1" && basename($_SERVER['PHP_SELF']) != "index.php") {
        header("Location: /alina-cloud/app/");
        exit();
    }
    // Mantener sesión si todo está bien
}

$_SESSION["pageActive"] = (isset($_SESSION["pageActive"])) ? $_SESSION["pageActive"] : "m1-1^1^N/A";
$_SESSION["currentRoute"] = (isset($_SESSION["currentRoute"])) ? $_SESSION["currentRoute"] : "modulos/escritorio/";
$fhActual = date("Y-m-d H:i:s"); // EN CASO QUE EL MOVIMIENTO SEA DELICADO, CONCATENAR LA FECHA EN PARÉNTESIS
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <?php
    if (isset($_SESSION["titlePage"])) {
        ?>
        <title><?php echo $_SESSION["titlePage"]; ?></title>
        <?php
    } else {
        ?>
        <title>Alina - Cloud</title>
        <?php
    }
    ?>
    <link rel="icon" href="../libraries/resources/images/logos/favicon.ico" type="image/ico" />-->

    <!-- Bootstrap 5.3 (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php
    $queryTema = "
	    	SELECT 
	    		custom
	    	FROM mip_perfil_custom 
	    	WHERE usuarioId = ? AND tipoCustom = 'Tema'
    	";
    $existeUsuario = $cloud->row($queryTema, [$_SESSION["usuarioId"]]);

    if ($existeUsuario->custom == "dark") {
        echo '<link rel="stylesheet" href="../libraries/packages/css/styles-dark.css" id="theme-link" />';
    } else {
        echo '<link rel="stylesheet" href="../libraries/packages/css/styles.css" id="theme-link" />';
    }
    ?>
    <link rel="stylesheet" href="../libraries/packages/css/datatables.min.css" />
</head>

<body>
    <div class="page-wrapper chiller-theme toggled">
        <?php
        include("../libraries/includes/parts/sidebar/sidebar.php");
        ?>
        <!-- sidebar-wrapper  -->
        <main class="page-content" onclick="mobileControl('check-mobile');">
            <div class="container-fluid">
                <div class="floatbar">
                    <div class="btn-group-vertical">
                        <button type="button" class="btn btn-primary btn-sm" id="toggle-sidebar">
                            <i id="menuShow" class="fas fa-bars"></i>
                            <i id="menuHide" class="fas fa-times"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="pin-sidebar">
                            <i id="pinCompress" class="fas fa-compress-alt"></i>
                            <i id="pinExpand" class="fas fa-expand-alt"></i>
                        </button>
                    </div>
                    <!-- <div id="side-carrito"></div> -->
                </div>
                <div id="container-page">