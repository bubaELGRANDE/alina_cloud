<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$periodoInicio = $_POST['fechaMayorizacionInicio'] ?? 0;
$periodoFinal = $_POST['fechaMayorizacionFin'] ?? 0;

$year = date('Y');

$contador = $cloud->row("SELECT COUNT(contaMayorizacionId) AS total
FROM conta_mayorizacion_" . $year . " 
WHERE partidaContaPeriodoId BETWEEN ? AND ?", [$periodoInicio, $periodoFinal]);

if ($contador->total > 0) {
    echo json_encode(["status" => false]);
} else {
    echo json_encode(["status" => true]);
}

?>