<?php
$dataUserImg = $cloud->row("
        SELECT 
            custom
        FROM mip_perfil_custom 
        WHERE usuarioId = ? AND tipoCustom = 'Avatar' AND flgDelete = '0'
    ", [$_SESSION["usuarioId"]]);
?>
<div class="user-pic">
    <img class="img-responsive img-rounded" src="../libraries/resources/images/<?php echo $dataUserImg->custom; ?>"
        alt="<?php echo $_SESSION['nombrePersona']; ?>">
</div>
<div class="user-info">
    <span class="user-name">
        <b>
            <?php
            // Se crea en loginCheck
            echo $_SESSION["nombrePersonaSide"];
            ?>
        </b>
    </span>
    <!--<span class="user-role">Desarrollador</span>-->
    <span id="sideStatusClass" class="user-status-online">
        <i class="fas fa-circle"></i>
        <span id="sideStatus">
            En línea
        </span>
    </span>
</div>