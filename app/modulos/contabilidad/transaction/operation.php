<?php
include("../../../../libraries/includes/logic/mgc/datos94.php");
include("../../../../libraries/includes/logic/functions/funciones-generales.php");
include("../../../../libraries/includes/logic/functions/funciones-fel-magic.php");
include("../../../../libraries/includes/logic/functions/funciones-facturacion.php");
include_once("../../../../libraries/includes/logic/functions/funciones-conta.php");
include_once("../../../../libraries/includes/logic/functions/funciones-correos.php");
//include_once("../../../../libraries/includes/logic/functions/funciones-notificacion.php");

$yearBD = "_" . date("Y");
$fhActual = date("Y-m-d H:i:s");

$typeOperation = (isset($_POST["typeOperation"])) ? $_POST["typeOperation"] : "no-definida";
$operation = (isset($_POST["operation"])) ? $_POST["operation"] : "no-definida";

if (isset($_SESSION["usuarioId"])) {
	switch ($typeOperation) {

		case 'certificacion':
			// Definir variables / permisos
			include("certificacion.php");
			break;
		case 'insert':
			// Definir variables / permisos
			include("insert.php");
			break;

		case 'update':
			// Definir variables / permisos
			include("update.php");
			break;

		case 'delete':
			// Definir variables / permisos
			include("delete.php");
			break;

		default:
			echo "Operación no definida.";
			break;
	}
} else {
	header("Location: /cloud-lite/app/");
}
?>