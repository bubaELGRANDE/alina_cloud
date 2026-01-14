<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();


$partidasPendiente = $cloud->row("SELECT 
    (
        SELECT COUNT(p.partidaContableId)
        FROM conta_partidas_contables p
        JOIN (
            SELECT 
                d.partidaContableId,
                SUM(d.cargos) AS totalCargos,
                SUM(d.abonos) AS totalAbonos
            FROM conta_partidas_contables_detalle d
            WHERE d.flgDelete = 0
            GROUP BY d.partidaContableId
        ) AS detalle ON detalle.partidaContableId = p.partidaContableId
        WHERE 
            p.flgDelete = 0
            AND (
                detalle.totalCargos <> detalle.totalAbonos
                OR detalle.totalCargos <> p.cargoPartida
                OR detalle.totalAbonos <> p.abonoPartida
            )
    ) AS partidasDescuadradas,
    
    (
        SELECT COUNT(partidaContableId)
        FROM conta_partidas_contables
        WHERE estadoPartidaContable = 'Pendiente' AND flgDelete = 0
    ) AS partidasPendientes;
");

$jsonPartidaPedientes = array(
    "tituloModal" => "Compras Pendientes",
    "yearBD" => "",
);

/*$jsonDCLPendientes = array(
    "tituloModal"       => "Compras Pendientes de Documento Contable de Liquidación",
    "yearBD"            => $yearBD,
);*/
?>

<button id="btnContingencia" type="button" class="btn btn-secondary" onclick="contingencias()">
    <span
        class="badge rounded-pill bg-light text-dark"><?php echo ($partidasPendiente->partidasPendientes); ?></span>
    Contingencias
</button>