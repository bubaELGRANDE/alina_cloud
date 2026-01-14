<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$partidaContaPeriodoId = $_POST['partidaContaPeriodoId'];
$tipoPartidaId = $_POST['tipoPartidaId'];

$last = $cloud->row("
SELECT MAX(numPartida) AS ultimo
FROM conta_partidas_contables
WHERE partidaContaPeriodoId = ? AND tipoPartidaId = ? AND flgDelete = ?", 
[$partidaContaPeriodoId,$tipoPartidaId,0]);

$ultimoNum = $last && $last->ultimo ? (int)$last->ultimo : 0;
$siguiente = str_pad($ultimoNum + 1, 8, "0", STR_PAD_LEFT);
echo $siguiente;
