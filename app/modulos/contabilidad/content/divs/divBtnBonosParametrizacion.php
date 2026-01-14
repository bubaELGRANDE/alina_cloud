<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
        personaId:
        47 Iván Guerrero
        3 Marcelo Romero
        106 Karim Rojas
        103 Karla Maldonado
        176 Wandha Baires
        164 Juan Carlos Engelhard
        222 Werner Poppel
        236 Willian Aquino
        48 David Ferrer
    */

    $empleadosNoParametrizados = $cloud->count("
        SELECT
            exp.personaId AS personaId,
            exp.nombreCompleto AS nombreCompleto
        FROM view_expedientes exp
        WHERE exp.estadoPersona = ? AND exp.estadoExpediente = ? AND (exp.personaId NOT IN (
            SELECT bpd.personaId FROM conf_bonos_personas_detalle bpd
            WHERE bpd.personaId = exp.personaId AND bpd.flgDelete = 0
        ) AND exp.personaId NOT IN (164, 222))
    ", ["Activo", "Activo"]);
?>
<button type="button" class="btn btn-secondary ttip" onclick="modalBonoParametrizacionSinAsignar();">
    <span class="badge rounded-pill bg-light text-dark"><?php echo $empleadosNoParametrizados; ?></span>
    <i class="fas fa-user-tie"></i> Sin asignar
    <span class="ttiptext">Empleados sin asignar</span>
</button>